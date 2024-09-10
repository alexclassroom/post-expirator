<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions;

use Exception;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\WordPress\Facade\ErrorFacade;
use PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostTermsSet as NodeTypeCorePostTermsSet;
use PublishPress\Future\Modules\Workflows\Interfaces\NodeRunnerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\NodeRunnerProcessorInterface;

class CorePostTermsSet implements NodeRunnerInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var NodeRunnerProcessorInterface
     */
    private $nodeRunnerProcessor;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var ErrorFacade
     */
    private $errorFacade;

    public function __construct(
        HookableInterface $hooks,
        NodeRunnerProcessorInterface $nodeRunnerProcessor,
        \Closure $expirablePostModelFactory,
        ErrorFacade $errorFacade
    ) {
        $this->hooks = $hooks;
        $this->nodeRunnerProcessor = $nodeRunnerProcessor;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->errorFacade = $errorFacade;
    }

    public static function getNodeTypeName(): string
    {
        return NodeTypeCorePostTermsSet::getNodeTypeName();
    }

    public function setup(array $step, array $contextVariables = []): void
    {
        $this->nodeRunnerProcessor->setup($step, [$this, 'actionCallback'], $contextVariables);
    }

    public function actionCallback(int $postId, array $nodeSettings, array $step, array $contextVariables)
    {
        $postModel = call_user_func($this->expirablePostModelFactory, $postId);

        $taxonomy = $nodeSettings['taxonomyTerms']['taxonomy'];
        $updatedTerms = $nodeSettings['taxonomyTerms']['terms'] ?? [];

        $originalTerms = $postModel->getTermIDs($taxonomy);

        $result = $postModel->setTerms($updatedTerms, $taxonomy);
    }
}
