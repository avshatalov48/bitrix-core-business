<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPTerminateActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'StateTitle' => '',
			'KillWorkflow' => 'N',
		];
	}

	public function Execute()
	{
		$killWorkflow = ($this->KillWorkflow === 'Y');

		if ($killWorkflow)
		{
			CBPDocument::killWorkflow($this->GetWorkflowInstanceId());
		}
		else
		{
			CBPDocument::TerminateWorkflow(
				$this->GetWorkflowInstanceId(),
				$this->GetDocumentId(),
				$arErrorsTmp,
				(string)$this->StateTitle
			);
		}

		throw new Exception('TerminateActivity');

		//No effect
		//return CBPActivityExecutionStatus::Closed;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = '', $popupWindow = null, $siteId = '')
	{
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
			'siteId' => $siteId
		]);

		$dialog->setMap([
			'StateTitle' => [
				'Name' => GetMessage('BPTA1_STATE_TITLE_NAME'),
				'FieldName' => 'state_title',
				'Type' => 'string',
				'Default' => GetMessage('BPTA1_STATE_TITLE'),
			],
			'KillWorkflow' => [
				'Name' => GetMessage('BPTA1_KILL_WF_NAME'),
				'FieldName' => 'kill_workflow',
				'Type' => 'bool',
				'Default' => 'Y',
			],
		]);

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = [];

		$properties = [
			'StateTitle' => $arCurrentValues['state_title'],
			'KillWorkflow' => 'N',
		];

		if (!empty($arCurrentValues['kill_workflow']))
		{
			$properties['KillWorkflow'] = CBPHelper::getBool($arCurrentValues['kill_workflow']) ? 'Y' : 'N';
		}
		elseif (
			!empty($arCurrentValues['kill_workflow_text'])
			&& static::isExpression($arCurrentValues['kill_workflow_text'])
		)
		{
			$properties['KillWorkflow'] = $arCurrentValues['kill_workflow_text'];
		}

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		$errors = self::ValidateProperties($properties, $user);

		if ($errors)
		{
			return false;
		}

		$activity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$activity['Properties'] = $properties;

		return true;
	}
}
