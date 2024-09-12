<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers;

use PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnAdminInit as NodeTypeCoreOnAdminInit;
use PublishPress\Future\Modules\Workflows\Interfaces\NodeTriggerRunnerInterface;

class CoreOnAdminInit implements NodeTriggerRunnerInterface
{
    public static function getNodeTypeName(): string
    {
        return NodeTypeCoreOnAdminInit::getNodeTypeName();
    }

    public function setup(int $workflowId, array $step, array $contextVariables = []): void
    {
        // This method is intentionally left empty.
        // The functionality is implemented in the Pro version of the plugin.
    }
}
