<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPIfElseActivity extends CBPCompositeActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = ["Title" => ""];
	}

	public function Execute()
	{
		$flag = true;
		foreach ($this->arActivities as $activity)
		{
			if (($activity->Condition == null) || $activity->Condition->Evaluate($activity))
			{
				$flag = false;
				$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
				$this->workflow->ExecuteActivity($activity);
				break;
			}
		}
		if (!$flag)
		{
			return CBPActivityExecutionStatus::Executing;
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public function Cancel()
	{
		$flag = true;
		foreach ($this->arActivities as $activity)
		{
			if ($activity->executionStatus == CBPActivityExecutionStatus::Executing)
			{
				$flag = false;
				$this->workflow->CancelActivity($activity);
				break;
			}
			if (
				$activity->executionStatus == CBPActivityExecutionStatus::Canceling
				|| $activity->executionStatus == CBPActivityExecutionStatus::Faulting
			)
			{
				$flag = false;
				break;
			}
		}
		if (!$flag)
		{
			return CBPActivityExecutionStatus::Canceling;
		}
		return CBPActivityExecutionStatus::Closed;
	}

	protected function OnEvent(CBPActivity $sender)
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->CloseActivity($this);
	}

	public static function ValidateChild($childActivity, $bFirstChild = false)
	{
		$arErrors = [];
		$child = "CBP" . $childActivity;

		$bCorrect = false;
		while ($child <> '')
		{
			if ($child == "CBPIfElseBranchActivity")
			{
				$bCorrect = true;
				break;
			}
			$child = get_parent_class($child);
		}

		if (!$bCorrect)
		{
			$arErrors[] = ["code" => "WrongChildType", "message" => GetMessage("BPIEA_INVALID_CHILD_1")];
		}

		return array_merge($arErrors, parent::ValidateChild($childActivity, $bFirstChild));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters,
		$arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate,
		&$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		return true;
	}

	public function collectUsages()
	{
		$usages = parent::collectUsages();
		foreach ($this->arActivities as $activity)
		{
			if ($activity->Condition instanceof CBPActivityCondition)
			{
				$usages = array_merge($usages, $activity->Condition->collectUsages($activity));
			}
		}
		return $usages;
	}
}
