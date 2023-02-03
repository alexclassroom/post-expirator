<?php
/**
 * Copyright (c) 2023. PublishPress, All rights reserved.
 */

namespace PublishPressFuture\Modules\Expirator\Models;

use PublishPressFuture\Core\HookableInterface;
use PublishPressFuture\Modules\Expirator\ExpirationActionsAbstract;
use PublishPressFuture\Modules\Expirator\HooksAbstract;

class ExpirationActionsModel
{
    const ACTION_NAME_ATTRIBUTE = 'name';

    const ACTION_LABEL_ATTRIBUTE = 'label';

    /**
     * @var \PublishPressFuture\Core\HookableInterface
     */
    private $hooks;

    /**
     * @var array
     */
    private $actions;

    /**
     * @var array
     */
    private $actionsAsOptions;

    public function __construct(HookableInterface $hooks)
    {
        $this->hooks = $hooks;
    }

    /**
     * @return string[]
     */
    public function getActions()
    {
        if (empty($this->actions)) {
            $actions = [
                ExpirationActionsAbstract::POST_STATUS_TO_DRAFT => __('Draft', 'post-expirator'),
                ExpirationActionsAbstract::POST_STATUS_TO_PRIVATE => __('Private', 'post-expirator'),
                ExpirationActionsAbstract::POST_STATUS_TO_TRASH => __('Trash', 'post-expirator'),
                ExpirationActionsAbstract::DELETE_POST => __('Delete', 'post-expirator'),
                ExpirationActionsAbstract::STICK_POST => __('Stick', 'post-expirator'),
                ExpirationActionsAbstract::UNSTICK_POST => __('Unstick', 'post-expirator'),
                ExpirationActionsAbstract::POST_CATEGORY_SET => __('Taxonomy: Replace', 'post-expirator'),
                ExpirationActionsAbstract::POST_CATEGORY_ADD => __('Taxonomy: Add', 'post-expirator'),
                ExpirationActionsAbstract::POST_CATEGORY_REMOVE => __('Taxonomy: Remove', 'post-expirator'),
            ];

            $this->actions = $this->hooks->applyFilters(
                HooksAbstract::FILTER_EXPIRATION_ACTIONS,
                $actions
            );
        }

        return $this->actions;
    }

    public function getActionsAsOptions()
    {
        if (empty($this->actionsAsOptions)) {
            $options = [];

            $actions = $this->getActions();

            foreach ($actions as $name => $label) {
                $options[] = [
                    'value' => $name,
                    'label' => $label,
                ];
            }

            $this->actionsAsOptions = $options;
        }

        return $this->actionsAsOptions;
    }
}