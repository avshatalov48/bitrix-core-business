<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPParallelActivity
	extends CBPCompositeActivity
	implements IBPActivityEventListener
{
	private $isExecuting = false;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "");
	}

	public function Cancel()
	{
		$flag = true;
		for ($i = 0, $s = count($this->arActivities); $i < $s; $i++)
		{
			$activity = $this->arActivities[$i];
			if ($activity->executionStatus == CBPActivityExecutionStatus::Executing)
			{
				$this->workflow->CancelActivity($activity);
				$flag = false;
			}
			else if (($activity->executionStatus == CBPActivityExecutionStatus::Canceling) || ($activity->executionStatus == CBPActivityExecutionStatus::Faulting))
			{
				$flag = false;
			}
		}

		if (!$flag)
			return CBPActivityExecutionStatus::Canceling;

		return CBPActivityExecutionStatus::Closed;
	}

	public function Execute()
	{
		$this->isExecuting = true;
		for ($i = 0, $s = count($this->arActivities); $i < $s; $i++)
		{
			$activity = $this->arActivities[$i];
			$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
			$this->workflow->ExecuteActivity($activity);
		}

		if (count($this->arActivities) != 0)
			return CBPActivityExecutionStatus::Executing;

		return CBPActivityExecutionStatus::Closed;
	}

	public function OnEvent(CBPActivity $sender, $arEventParameters = array())
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);

		$flag = true;
		for ($i = 0, $s = count($this->arActivities); $i < $s; $i++)
		{
			$activity = $this->arActivities[$i];
			if (($activity->executionStatus != CBPActivityExecutionStatus::Initialized) && ($activity->executionStatus != CBPActivityExecutionStatus::Closed))
			{
				$flag = false;
				break;
			}
		}

		if ($flag)
			$this->workflow->CloseActivity($this);
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		return true;
	}
}
?>