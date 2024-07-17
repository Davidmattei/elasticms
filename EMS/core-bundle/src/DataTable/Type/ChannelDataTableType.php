<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Entity\Channel;
use EMS\CoreBundle\Form\Data\BoolTableColumn;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Service\Channel\ChannelService;
use function Symfony\Component\Translation\t;

class ChannelDataTableType extends AbstractEntityTableType
{
    public function __construct(ChannelService $entityService)
    {
        parent::__construct($entityService);
    }

    public function build(EntityTable $table): void
    {
        $table->setDefaultOrder('orderKey');

        $table->addColumn(t('table.loop_count', [], 'emsco-core'), 'orderKey');
        $table->addColumn(t('field.label', [], 'emsco-core'), 'label');

        $column = $table->addColumn(t('field.name', [], 'emsco-core'), 'name');
        $column->setPathCallback(fn (Channel $channel, string $baseUrl = '') => $baseUrl.$channel->getEntryPath(), '_blank');

        $table->addColumn(t('field.alias', [], 'emsco-core'), 'alias');
        $table->addColumnDefinition(new BoolTableColumn(t('admin.channel.field.public', [], 'emsco-core'), 'public'));

        $table->addItemGetAction(
            route: 'ems_core_channel_edit',
            labelKey: t('action.edit', [], 'emsco-core'),
            icon: 'pencil'
        );
        $table->addItemPostAction(
            route: 'ems_core_channel_delete',
            labelKey: t('action.delete', [], 'emsco-core'),
            icon:'trash',
            messageKey: t('action.delete_confirm', ['type' => 'channel'], 'emsco-core')
        );

        $table->addToolbarAction(
            label: t('action.add', [], 'emsco-core'),
            icon: 'fa fa-plus',
            routeName: 'ems_core_channel_add'
        );
        $table->addTableAction(
            name: TableAbstract::DELETE_ACTION,
            icon: 'fa fa-trash',
            labelKey: 'channel.actions.delete_selected',
            confirmationKey: 'channel.actions.delete_selected_confirm'
        );
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_ADMIN];
    }
}
