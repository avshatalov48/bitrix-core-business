<?php

namespace Bitrix\Bizproc\Debugger\Workflow;

use Bitrix\Bizproc\Debugger\Listener;
use Bitrix\Bizproc\Debugger\Session\DebuggerState;
use Bitrix\Bizproc\Debugger\Session\Manager;
use CBPActivityExecutorOperationType;
use IBPActivityExternalEventListener;

class DebugWorkflow extends \CBPWorkflow
{
	public function __construct($instanceId,\CBPRuntime $runtime)
	{
		parent::__construct($instanceId, $runtime);
		$this->persister = Persister::GetPersister();
	}

	/**
	 * Remove workflow object from serialized data
	 * @return array
	 */
	public function __sleep()
	{
		$this->toggleQueueExceptions();

		return ['activitiesQueue', 'eventsQueue'];
	}

	public function __wakeup()
	{
		$this->toggleQueueExceptions();
	}

	private function toggleQueueExceptions()
	{
		foreach ($this->activitiesQueue as $index => $item)
		{
			if ($item[1] === CBPActivityExecutorOperationType::HandleFault && isset($item[2]))
			{
				if ($item[2] instanceof \Exception)
				{
					$this->activitiesQueue[$index][2] = [$item[2]->getMessage(), $item[2]->getCode()];
				}
				elseif (is_array($item[2]))
				{
					$this->activitiesQueue[$index][2] = new \Exception($item[2][0], $item[2][1]);
				}
			}
		}
	}

	protected function setWorkflowStatus($newStatus)
	{
		parent::setWorkflowStatus($newStatus);
		Listener::getInstance()->onWorkflowStatusChanged($this->getInstanceId(), $newStatus);
	}

	public function getDebugEventIds(): array
	{
		$ids = [];

		foreach ($this->getEventsMap() as $id => $handlers)
		{
			foreach ($handlers as $handler)
			{
				if ($handler instanceof \IBPActivityDebugEventListener)
				{
					$ids[] = $id;
					break;
				}
			}
		}

		return $ids;
	}

	public function addEventHandler($eventName, IBPActivityExternalEventListener $eventHandler)
	{
		parent::addEventHandler($eventName, $eventHandler);

		if ($eventHandler instanceof \IBPActivityDebugEventListener)
		{
			Listener::getInstance()->onWorkflowEventAdded($this->getInstanceId(), $eventName);
		}
	}

	public function removeEventHandler($eventName, IBPActivityExternalEventListener $eventHandler)
	{
		parent::removeEventHandler($eventName, $eventHandler);

		if ($eventHandler instanceof \IBPActivityDebugEventListener)
		{
			Listener::getInstance()->onWorkflowEventRemoved($this->getInstanceId(), $eventName);
		}
	}

	public function getService($name)
	{
		$service = $this->runtime->getDebugService($name);

		if (is_null($service))
		{
			$service = $this->runtime->GetService($name);
		}

		return $service;
	}

	protected function runQueue()
	{
		$debuggerState = Manager::getDebuggerState();

		if ($debuggerState->is(DebuggerState::NEXT_STEP))
		{
			$this->runStep();
		}
		elseif ($debuggerState->is(DebuggerState::RUN))
		{
			parent::runQueue();
		}
	}

	public function sendDebugEvent($eventName, array $eventParameters = [])
	{
		$eventParameters['DebugEvent'] = true;

		$this->sendExternalEvent($eventName, $eventParameters);
	}

	public function reload(\CBPActivity $rootActivity)
	{
		$this->activitiesQueue = $rootActivity->workflow->activitiesQueue;
		$this->eventsQueue = $rootActivity->workflow->eventsQueue;

		parent::reload($rootActivity);
	}

	public function isDebug(): bool
	{
		return true;
	}
}
