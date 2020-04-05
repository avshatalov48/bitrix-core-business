<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPListenActivity
	extends CBPCompositeActivity
	implements IBPActivityEventListener
{
	public $isListenTrigerred = false;
	public $arActivityState = array();

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "");
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();

		$this->isListenTrigerred = false;
		$this->arActivityState = array();
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

	public function Execute()
	{
		for ($i = 0, $s = sizeof($this->arActivities); $i < $s; $i++)
		{
			$eventDriven = $this->arActivities[$i];

			$activity = $eventDriven->GetEventActivity();
			if (is_null($activity))
				continue;

			$l = new CBPListenEventActivitySubscriber($eventDriven);
			$this->arActivityState[] = $l;

			$activity->Subscribe($l);
		}

		return CBPActivityExecutionStatus::Executing;
	}

	public function OnEvent(CBPActivity $sender, $arEventParameters = array())
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->CloseActivity($this);
	}

	public function Cancel()
	{
		if (count($this->arActivityState) > 0)
		{
			if ($this->isListenTrigerred)
			{
				for ($i = 0, $s = sizeof($this->arActivities); $i < $s; $i++)
				{
					$activity = $this->arActivities[$i];
					if ($activity->executionStatus == CBPActivityExecutionStatus::Executing)
					{
						$this->workflow->CancelActivity($activity);
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
				for ($i = 0, $s = sizeof($this->arActivities); $i < $s; $i++)
				{
					$activity = $this->arActivities[$i];
					$eventHandler = $this->arActivityState[$i];

					$activity2 = $activity->GetEventActivity();
					if ($activity2)
						$activity2->Unsubscribe($eventHandler);
				}
			}
			$this->arActivityState = array();
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateChild($childActivity, $bFirstChild = false)
	{
		$arErrors = array();

		$child = "CBP".$childActivity;

		$bCorrect = false;
		while (strlen($child) > 0)
		{
			if ($child == "CBPEventDrivenActivity")
			{
				$bCorrect = true;
				break;
			}
			$child = get_parent_class($child);
		}

		if (!$bCorrect)
			$arErrors[] = array("code" => "WrongChildType", "message" => GetMessage("BPLA_INVALID_ACTIVITY"));

		return array_merge($arErrors, parent::ValidateChild($childActivity, $bFirstChild));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		return true;
	}
}

final class CBPListenEventActivitySubscriber
	implements IBPActivityExternalEventListener
{
	private $eventDrivenActivity;

	public function __construct(CBPEventDrivenActivity $eventDriven)
	{
		$this->eventDrivenActivity = $eventDriven;
	}

	public function ToString()
	{
		return "eventDrivenActivity = ".$this->eventDrivenActivity->ToString();
	}

	public function OnExternalEvent($arEventParameters = array())
	{
		$listenActivity = $this->eventDrivenActivity->parent;

		if (!$listenActivity->isListenTrigerred
			&& ($listenActivity->executionStatus != CBPActivityExecutionStatus::Canceling)
			&& ($listenActivity->executionStatus != CBPActivityExecutionStatus::Closed))
		{
			$listenActivity->isListenTrigerred = true;

			$arActivities = $listenActivity->CollectNestedActivities();
			for ($i = 0, $s = sizeof($arActivities); $i < $s; $i++)
			{
				$activity2 = $arActivities[$i];
				$parentEventHandler = $listenActivity->arActivityState[$i];

				$activity3 = $activity2->GetEventActivity();
				if ($activity3 && $parentEventHandler)
					$activity3->Unsubscribe($parentEventHandler);
			}

			$this->eventDrivenActivity->AddStatusChangeHandler(CBPListenActivity::ClosedEvent, $listenActivity);
			$listenActivity->workflow->ExecuteActivity($this->eventDrivenActivity);
		}
	}
}
?>