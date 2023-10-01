<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPListenActivity extends CBPCompositeActivity implements IBPActivityEventListener
{
	public $isListenTrigerred = false;
	public $arActivityState = [];

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = ['Title' => ''];
	}

	protected function reInitialize()
	{
		parent::reInitialize();

		$this->isListenTrigerred = false;
		$this->arActivityState = [];
	}

	public function ToString()
	{
		return $this->name.
			" [".get_class($this)."] (status=".
			CBPActivityExecutionStatus::Out($this->executionStatus).
			", count(arActivityState)=".
			count($this->arActivityState).
			")";
	}

	public function execute()
	{
		for ($i = 0, $s = count($this->arActivities); $i < $s; $i++)
		{
			$eventDriven = $this->arActivities[$i];

			$activity = $eventDriven->GetEventActivity();
			if ($activity === null)
			{
				continue;
			}

			$l = new CBPListenEventActivitySubscriber($eventDriven);
			$this->arActivityState[] = $l;

			$activity->Subscribe($l);
		}

		return CBPActivityExecutionStatus::Executing;
	}

	public function onEvent(CBPActivity $sender, $arEventParameters = [])
	{
		$sender->removeStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->closeActivity($this);
	}

	public function cancel()
	{
		if (count($this->arActivityState) > 0)
		{
			if ($this->isListenTrigerred)
			{
				for ($i = 0, $s = count($this->arActivities); $i < $s; $i++)
				{
					$activity = $this->arActivities[$i];
					if ($activity->executionStatus == CBPActivityExecutionStatus::Executing)
					{
						$this->workflow->cancelActivity($activity);

						return CBPActivityExecutionStatus::Canceling;
					}
					if ($activity->executionStatus == CBPActivityExecutionStatus::Faulting)
					{
						return CBPActivityExecutionStatus::Canceling;
					}
				}
			}
			else
			{
				for ($i = 0, $s = count($this->arActivities); $i < $s; $i++)
				{
					$activity = $this->arActivities[$i];
					$eventHandler = $this->arActivityState[$i];

					$activity2 = $activity->GetEventActivity();
					if ($activity2 && $eventHandler)
					{
						$activity2->Unsubscribe($eventHandler);
					}
				}
			}
			$this->arActivityState = [];
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function validateChild($childActivity, $bFirstChild = false)
	{
		$arErrors = [];

		$child = 'CBP' . $childActivity;

		$bCorrect = false;
		while ($child != '')
		{
			if ($child == 'CBPEventDrivenActivity')
			{
				$bCorrect = true;
				break;
			}
			$child = get_parent_class($child);
		}

		if (!$bCorrect)
		{
			$arErrors[] = ['code' => 'WrongChildType', 'message' => Loc::getMessage('BPLA_INVALID_ACTIVITY_1')];
		}

		return array_merge($arErrors, parent::validateChild($childActivity, $bFirstChild));
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = ''
	)
	{
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	)
	{
		return true;
	}
}

final class CBPListenEventActivitySubscriber implements IBPActivityExternalEventListener
{
	private $eventDrivenActivity;

	public function __construct(CBPEventDrivenActivity $eventDriven)
	{
		$this->eventDrivenActivity = $eventDriven;
	}

	public function ToString()
	{
		return 'eventDrivenActivity = ' . $this->eventDrivenActivity->ToString();
	}

	public function onExternalEvent($arEventParameters = array())
	{
		$listenActivity = $this->eventDrivenActivity->parent;

		$firedActivity = $this->eventDrivenActivity->GetEventActivity();

		if (
			method_exists($firedActivity, 'OnExternalDrivenEvent')
			&&
			$firedActivity->OnExternalDrivenEvent($arEventParameters) !== true
		)
		{
			return;
		}

		if (
			!$listenActivity->isListenTrigerred
			&& ($listenActivity->executionStatus != CBPActivityExecutionStatus::Canceling)
			&& ($listenActivity->executionStatus != CBPActivityExecutionStatus::Closed)
		)
		{
			$listenActivity->isListenTrigerred = true;

			$arActivities = $listenActivity->CollectNestedActivities();
			for ($i = 0, $s = count($arActivities); $i < $s; $i++)
			{
				$activity2 = $arActivities[$i];
				$parentEventHandler = $listenActivity->arActivityState[$i];

				$activity3 = $activity2->GetEventActivity();
				if ($activity3 && $parentEventHandler)
				{
					$activity3->Unsubscribe($parentEventHandler);
				}
			}

			$this->eventDrivenActivity->addStatusChangeHandler(CBPListenActivity::ClosedEvent, $listenActivity);
			$listenActivity->workflow->ExecuteActivity($this->eventDrivenActivity);
		}
	}
}