<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSetStateActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "", "TargetStateName" => "", 'CancelCurrentState' => 'N');
	}

	public function Execute()
	{
		$stateActivity = $this;
		while ($stateActivity != null && !is_a($stateActivity, "CBPStateActivity"))
			$stateActivity = $stateActivity->parent;

		if ($stateActivity != null && ($stateActivity instanceof CBPStateActivity))
		{
			$stateActivity->SetNextStateName($this->TargetStateName);
			if ($this->CancelCurrentState == 'Y')
			{
				$this->WriteToTrackingService(GetMessage("BPSSA_EXECUTE_CANCEL"));
				$this->workflow->CancelActivity($stateActivity);
				return CBPActivityExecutionStatus::Executing;
			}
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (strlen($arTestProperties["TargetStateName"]) <= 0)
		{
			$arErrors[] = array("code" => "emptyState", "parameter" => "TargetStateName", "message" => GetMessage('BPSSA_ERROR_EMPTY_STATE'));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$arStates = CBPWorkflowTemplateLoader::GetStatesOfTemplate($arWorkflowTemplate);

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]) && array_key_exists("TargetStateName", $arCurrentActivity["Properties"]))
				$arCurrentValues["target_state_name"] = $arCurrentActivity["Properties"]["TargetStateName"];
			if (is_array($arCurrentActivity["Properties"]) && array_key_exists("CancelCurrentState", $arCurrentActivity["Properties"]))
				$arCurrentValues["cancel_current_state"] = $arCurrentActivity["Properties"]["CancelCurrentState"];
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arStates" => $arStates,
				"arCurrentValues" => $arCurrentValues,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$state = ((strlen($arCurrentValues["target_state_name_1"]) > 0) ? $arCurrentValues["target_state_name_1"] : $arCurrentValues["target_state_name"]);
		$cancelCurrentState = isset($arCurrentValues['cancel_current_state']) && $arCurrentValues['cancel_current_state'] == 'Y' ? 'Y' : 'N';
		$arProperties = array('TargetStateName' => $state, 'CancelCurrentState' => $cancelCurrentState);

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}

}
?>