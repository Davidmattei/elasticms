import type { Api, Config, ConfigLanguage } from 'datatables.net-bs'
import type { EmsModule } from '../types/emsModule'
import AddedDomEvent from '../events/addedDomEvent'

const SELECTOR = '[data-datatable]'
const BUTTON_SELECTOR = '[data-datatable-target][data-datatable-event]'

const languages: Record<string, () => Promise<{ default: ConfigLanguage }>> = {
    fr: () => import('datatables.net-plugins/i18n/fr-FR.mjs'),
    nl: () => import('datatables.net-plugins/i18n/nl-NL.mjs')
}

declare global {
    interface Window {
        dataTables?: Record<string, Api>
    }
}

export default class Datatable implements EmsModule {
    private readonly initialized = new WeakSet<HTMLElement>()
    private delegated = false

    public async load(target: Document | HTMLElement): Promise<void> {
        const elements = [...target.querySelectorAll<HTMLElement>(SELECTOR)].filter(
            (element) => !this.initialized.has(element)
        )

        if (elements.length === 0) {
            return
        }

        const { default: DataTable } = await import('datatables.net-bs')

        if (!this.delegated) {
            document.addEventListener('click', this.onClick)
            this.delegated = true
        }

        for (const element of elements) {
            this.initialized.add(element)
            this.init(new DataTable(element, await this.options(element)), element)
        }
    }

    private async options(element: HTMLElement): Promise<Config> {
        const options = JSON.parse(element.dataset.datatable ?? '{}') as Config
        const language = document.documentElement.lang.toLowerCase().split('-')[0]
        const loadLanguage = languages[language]

        if (!options.language && loadLanguage) {
            options.language = (await loadLanguage()).default
        }

        return options
    }

    private init(datatable: Api, element: HTMLElement): void {
        const togglePaging = (): void => {
            const paging = element.closest('.dt-container')?.querySelector('.dt-paging')
            paging?.classList.toggle('d-none', datatable.page.info().pages <= 1)
        }

        togglePaging()
        datatable.on('draw', () => {
            togglePaging()
            new AddedDomEvent(element).dispatch()
        })

        if (!window.dataTables) {
            window.dataTables = {}
        }

        window.dataTables[element.id] = datatable
    }

    private readonly onClick = (event: Event): void => {
        if (!(event.target instanceof Element)) {
            return
        }

        const button = event.target.closest<HTMLElement>(BUTTON_SELECTOR)
        const table = button ? document.getElementById(button.dataset.datatableTarget ?? '') : null

        if (!button || !table) {
            return
        }

        const checked = table.querySelectorAll<HTMLInputElement>(
            `input[name="${table.id}-select[]"]:checked`
        )

        table.dispatchEvent(
            new CustomEvent(button.dataset.datatableEvent as string, {
                detail: {
                    selection: [...checked].map((checkbox) => checkbox.value)
                }
            })
        )
    }
}
