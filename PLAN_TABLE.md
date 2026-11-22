# EMS Table bundle — roadmap

## Context
Datatables in admin run on jQuery + DataTables.net (`EMS/admin-ui-bundle/assets/src/core/plugins/datatable.js`). Their PHP model is tied to core-bundle (`EMS/core-bundle/src/Form/Data/*Table*`, `EMS/core-bundle/src/Core/DataTable/*`, `EMS/core-bundle/src/Helper/DataTableRequest.php`). Goal: a new, independent table bundle with its own vanilla JS implementation. We first try it out in admin **alongside** the current implementation, and only in a later phase migrate and remove the old code. Later the bundle must also be usable in elasticms-web and in standalone Symfony projects.

Principles:
- Composer and npm are separate: the bundle contains only PHP and Twig, the JS is its own npm package (no `file:vendor/...`).
- No dependency on core-bundle or common-bundle. Core-bundle becomes a consumer later, through adapters.
- Everything must be configurable from Twig (columns, data, and later a source such as a query).
- v1 = plain array data.

---

## Phase 0 — Skeleton (no functionality)
1. Create `EMS/table-bundle/`, following `EMS/form-bundle/composer.json`:
   - `composer.json` (`elasticms/table-bundle`, `type: symfony-bundle`, require php ^8.5, symfony/twig-bundle, symfony/options-resolver, symfony/http-kernel 7.4.*).
   - `src/EMSTableBundle.php` (AbstractBundle, config via `configure()` / `loadExtension()`), `config/services.php`, `templates/`, `tests/`.
2. Create `EMS/table-js/`: `package.json` (`@elasticms/table`, `"type": "module"`, `exports` → `src/index.js` + `src/table.css`). Not published yet.
3. Root `composer.json`: psr-4 `EMS\\TableBundle\\` + `EMS\\TableBundle\\Tests\\`, `replace` entry. Monorepo-builder picks up `EMS/*` automatically (`config/monorepo-builder.php`).
4. Add paths to `config/phpstan.neon.dist`, `config/phpunit.xml.dist` and `config/php-cs-fixer.php` (like the other EMS packages).
5. `elasticms-admin/config/bundles.php`: `EMS\TableBundle\EMSTableBundle::class => ['all' => true]`.
6. Check: `composer monorepo-validate`, `composer phpstan`.

## Phase 1 — PHP v1: array table via Twig
Namespace `EMS\TableBundle\`:
1. **Model**
   - `Table\TableConfig`: built from an array via `OptionsResolver` (`id`, `columns`, `data`, `pageSize`, `searchable`, `selectable`, `sort`, `labels`, `attr`).
   - `Table\Column`: `key`, `label`, `sortable`, `searchable`, `template` (Twig block or template), `format` (e.g. `date`, `bool`, `bytes`; extensible later via `ColumnFormatterInterface` with a DI tag).
2. **Data**
   - `DataSource\DataSourceInterface`: `count(TableQuery): int`, `rows(TableQuery): iterable`.
   - `DataSource\ArrayDataSource`: search/sort/paging in PHP (take the idea from `EMS/core-bundle/src/Core/DataTable/ArrayDataSource.php`, don't reuse the class).
   - `Query\TableQuery`: value object (`page`, `size`, `sort`, `direction`, `search`).
   - `config.source` is already reserved (`{type: 'array'}` by default), so `dbal`/`elasticsearch` can be added later without a BC break.
3. **Rendering**
   - `Twig\TableExtension` → `ems_table(config)` and possibly `ems_table_payload(config)` for custom markup.
   - `templates/table.html.twig`: semantic `<table>`, first page rendered server-side (works without JS), payload in `data-ems-table='{json}'` with `"v": 1`.
   - Bootstrap 5 classes by default (admin-ui and the demo use BS5); classes can be overridden via `attr`.
   - Translations in `translations/emstable+intl-icu.*` (en, fr, nl, de).
4. **Tests** (`EMS/table-bundle/tests/`, namespace `EMS\TableBundle\Tests\`): config resolving, ArrayDataSource (sort/search/paging), Twig render snapshot.

Twig usage:
```twig
{{ ems_table({
    id: 'demo-users',
    columns: [
        {key: 'name', label: 'Name', sortable: true},
        {key: 'created', label: 'Created', format: 'date', sortable: true},
    ],
    data: rows,
    pageSize: 25,
}) }}
```

## Phase 2 — JS v1: `@elasticms/table`
In `EMS/table-js/src/`:
1. `index.js`: `initTables(root = document)` finds `[data-ems-table]` and creates an `EmsTable` per element. It is idempotent, so it can safely run again after ajax or modal loads.
2. `EmsTable`: client-side sort, search (debounced), paging, page size and selection checkboxes; reads the payload and checks `v`.
3. Events on the element: `ems-table:draw`, `ems-table:selection` (`detail.selection`), plus a public API `element.emsTable`. No `window` globals.
4. `table.css`: minimal styling on top of Bootstrap (sort icons, optional sticky header).
5. Tests: vitest + jsdom (sort/search/paging/events).

Integration in admin, alongside the existing code:
- `EMS/admin-ui-bundle/assets/package.json`: dependency `@elasticms/table` via an npm workspace or a relative path inside the monorepo (until it is published).
- `EMS/admin-ui-bundle/assets/src/core/core.js`: register a new plugin `plugins/emsTable.js` next to `new Datatable()`. It uses a different data attribute (`data-ems-table` vs `data-datatable`), so there is no conflict.
- Also call `initTables(element)` after `Core.load(element)` (for modals and ajax content).
- Verify via the Vite dev server, not via `npm run build`.

## Phase 3 — Try it out in admin (alongside the old implementation)
1. Demo dashboard `demo/skeleton/template_ems/dashboard/table_demo.twig` with an array table (e.g. from `emsco_search` results → `|map` to rows), so everything can be tested from Twig without touching core code.
2. Demo config: register the dashboard in `demo/configs/admin/dashboard` (like the existing dashboards).
3. One simple core page as a trial, behind a feature flag or a second route, e.g. the wysiwyg styles set list (`WysiwygStylesSetDataTableType`): same data via `ems_table`, the old page stays.
4. Feedback round: adjust the config/column API before phase 4. This is the moment to break things without BC concerns.

## Phase 4 — Server-side mode and sources
1. `Controller\TableDataController` + route `ems_table_data` (JSON `{total, rows}`), own protocol `TableRequest` (`page,size,sort,dir,q`). No DataTables.net protocol.
2. Store the config server-side (cache) and address it via a signed hash (idea from `DatatableService::generateDatatableFromHash`, `EMS/core-bundle/src/Service/DatatableService.php`). The client never sends the config or query.
3. `DataSourceFactoryInterface` + DI tag. Source types: `array` (phase 1) and `dbal` (Doctrine DBAL). `dbal` is opt-in per connection in the bundle config, because Twig templates in web are content.
4. JS: `mode: 'server'` → fetch with `AbortController`, loading state.

## Phase 5 — Export
1. `Export\ExporterInterface`; native CSV (streamed response).
2. XLSX via optional `phpoffice/phpspreadsheet` (`suggest`), not via common-bundle.
3. Export buttons in the Twig template and an `ems_table_export` route.

## Phase 6 — Migrate admin
1. Adapters in core-bundle: `ElasticaDataSource` (via `ElasticaService`), an entity service datasource (via `EntityServiceInterface`), and core columns (user, revision, roles, translation, data-links) as column formatters.
2. Move table actions (row/toolbar/mass) into the bundle with a generic API; core provides the EMS-specific actions.
3. Convert the `*DataTableType` classes (~45 in `EMS/core-bundle/src/DataTable/Type/`) one by one, a group per PR, with `composer phpunit-admin` as a safety net.
4. Make the Twig functions `emsco_datatable*` (`EMS/core-bundle/src/Twig/DatatableExtension.php`) delegate to the new bundle and mark them as deprecated. Convert the demo templates (`demo/skeleton/template_ems/view/*.twig`, `dashboard/*.twig`).

## Phase 7 — Clean up and broaden
1. Remove the old code: `plugins/datatable.js`, `EMS/core-bundle/assets/js/module/datatables.js`, `datatables.net*` from both `package.json` files, `DataTableRequest`, `Form/Data/*Table*`, `Core/DataTable/*`.
2. Upgrade notes in `docs/src` + `CHANGELOG-7.x.md` (BC break → agree on the target major/minor).
3. Register the bundle in `elasticms-web`; demo web page with a table.
4. npm publish step in `build/release` for `@elasticms/table`; version equal to the monorepo tag.

---

## Verification (per phase)
- PHP: `php vendor/bin/phpunit -c config/phpunit.xml.dist EMS/table-bundle/tests`, `composer phpstan`, `composer phpcs`, `composer monorepo-validate`.
- JS: `npm test` in `EMS/table-js`.
- Admin: `make start`, admin-ui Vite dev server, open the demo dashboard at http://localhost:8881. Check sort, search, paging, selection events and the no-JS rendering, and check that the existing DataTables.net pages still work unchanged.
