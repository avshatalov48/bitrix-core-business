<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSequenceActivity
	extends CBPCompositeActivity
	implements IBPActivityEventListener
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "");
	}

	public function Execute()
	{
		if (count($this->arActivities) == 0)
			return CBPActivityExecutionStatus::Closed;

		$this->arActivities[0]->AddStatusChangeHandler(self::ClosedEvent, $this);

		$this->workflow->ExecuteActivity($this->arActivities[0]);

		return CBPActivityExecutionStatus::Executing;
	}

	protected function OnSequenceComplete()
	{
	}

	public function OnEvent(CBPActivity $sender, $arEventParameters = array())
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);

		if (($this->executionStatus == CBPActivityExecutionStatus::Canceling) || ($this->executionStatus == CBPActivityExecutionStatus::Faulting))
		{
			$this->workflow->CloseActivity($this);
		}
		else if (($this->executionStatus == CBPActivityExecutionStatus::Executing) && !$this->TryScheduleNextChild())
		{
			$this->OnSequenceComplete();
			$this->workflow->CloseActivity($this);
		}
	}

	private function TryScheduleNextChild()
	{
		if (count($this->arActivities) == 0)
			return false;

		$num = 0;
		for ($i = count($this->arActivities) - 1; $i >= 0; $i--)
		{
			if ($this->arActivities[$i]->executionStatus == CBPActivityExecutionStatus::Closed)
			{
				if ($i == (count($this->arActivities) - 1))
					return false;

				$num = $i + 1;
				break;
			}
		}

		$this->arActivities[$num]->AddStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->ExecuteActivity($this->arActivities[$num]);

		return true;
	}

	public function Cancel()
	{
		for ($i = count($this->arActivities) - 1; $i >= 0; $i--)
		{
			$activity = $this->arActivities[$i];
			if ($activity->executionStatus == CBPActivityExecutionStatus::Executing)
			{
				$this->workflow->CancelActivity($activity);
				return CBPActivityExecutionStatus::Canceling;
			}

			if (($activity->executionStatus == CBPActivityExecutionStatus::Canceling) 
				|| ($activity->executionStatus == CBPActivityExecutionStatus::Faulting))
				return CBPActivityExecutionStatus::Canceling;

			if ($activity->executionStatus == CBPActivityExecutionStatus::Closed)
				return CBPActivityExecutionStatus::Closed;
		}
		return CBPActivityExecutionStatus::Closed;
	}

	public function HandleFault(Exception $exception)
	{
		$status = parent::HandleFault($exception);
		return $status;
	}
}
?>