<?php
namespace Opencart\System\Library\Extension\Mas\Sys;

use Opencart\System\Library\Extension\Mas\Sys\Segmentation\SegmentManager;
use Opencart\System\Library\Extension\Mas\Sys\Workflow\WorkflowEngine;

class Mas {
    private object $registry;
    private ?SegmentManager $segmentManager = null;
    private ?WorkflowEngine $workflowEngine = null;

    public function __construct(object $registry) {
        $this->registry = $registry;
    }

    public function __get(string $key): object {
        return $this->registry->get($key);
    }

    public function __set(string $key, object $value): void {
        $this->registry->set($key, $value);
    }

    public function getSegmentManager(): SegmentManager {
        if ($this->segmentManager === null) {
            require_once DIR_EXTENSION . 'mas/system/library/Sys/Segmentation/SegmentManager.php';
            $this->segmentManager = new SegmentManager($this->registry);
        }
        return $this->segmentManager;
    }

    public function getWorkflowEngine(): WorkflowEngine {
        if ($this->workflowEngine === null) {
            require_once DIR_EXTENSION . 'mas/system/library/Sys/Workflow/WorkflowEngine.php';
            $this->workflowEngine = new WorkflowEngine($this->registry);
        }
        return $this->workflowEngine;
    }
}