<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPLogActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Text" => "",
			"SetVariable" => false,
			"Report" => ""
		);

		$this->SetPropertiesTypes(array(
			'Report' => array(
				'Type' => 'string'
			)
		));
	}

	public function Execute()
	{
		$message = $this->Text;
		if (is_array($message))
		{
			$message = implode(', ', \CBPHelper::MakeArrayFlat($message));
		}

		$this->WriteToTrackingService($message, 0, CBPTrackingType::Report);

		if ($this->SetVariable)
		{
			$rootActivity = $this->GetRootActivity();
			$trackingService = $rootActivity->workflow->GetService("TrackingService");

			$report = "";
			$arReport = $trackingService->LoadReport($rootActivity->GetWorkflowInstanceId(), 50);
			foreach ($arReport as $value)
				$report .= $value["MODIFIED"]."\n".$value["ACTION_NOTE"]."\n\n";

			$this->Report = $report;
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if ($arTestProperties["Text"] == '')
		{
			$arErrors[] = array(
				"code" => "emptyText",
				"message" => GetMessage("BPCAL_EMPTY_TEXT"),
			);
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array("text" => "", "set_variable" => "N");

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				$arCurrentValues["text"] = $arCurrentActivity["Properties"]["Text"] ?? null;
				$arCurrentValues["set_variable"] = (
					isset($arCurrentActivity['Properties']['SetVariable']) && $arCurrentActivity['Properties']['SetVariable']
						? 'Y'
						: 'N'
				);
			}
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = [];
		$properties = array(
			"Text" => $arCurrentValues["text"],
			"SetVariable" => (mb_strtoupper($arCurrentValues["set_variable"]) === 'Y')
		);

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}
}