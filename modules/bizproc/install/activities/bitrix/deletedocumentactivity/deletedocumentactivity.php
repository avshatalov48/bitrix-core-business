<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPDeleteDocumentActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'TerminateCurrentWorkflow' => 'N',
		];
	}

	public function Execute()
	{
		$documentId = $this->GetDocumentId();

		$documentService = $this->workflow->GetService('DocumentService');
		$result = $documentService->DeleteDocument($documentId);

		if ($result instanceof \Bitrix\Main\Result && !$result->isSuccess())
		{
			$this->writeToTrackingService(
				implode(', ', $result->getErrorMessages()),
				0,
				CBPTrackingType::Error
			);

			return CBPActivityExecutionStatus::Closed;
		}

		if ($this->workflow->isDebug())
		{
			$map = $this->getDebugInfo(
				[
					'DeletedId' => preg_replace('/\D+/', '', $documentId[2]),
				],
				[
					'DeletedId' => Loc::getMessage('BPDDA_DELETED_ID'),
				],
			);
			$this->writeDebugInfo($map);
		}

		if ($this->TerminateCurrentWorkflow === 'Y')
		{
			$this->workflow->Terminate();

			throw new Exception('TerminateActivity');
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = '',
		$popupWindow = null,
		$siteId = ''
	)
	{
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(
			__FILE__,
			[
				'documentType' => $documentType,
				'activityName' => $activityName,
				'workflowTemplate' => $arWorkflowTemplate,
				'workflowParameters' => $arWorkflowParameters,
				'workflowVariables' => $arWorkflowVariables,
				'currentValues' => $arCurrentValues,
				'formName' => $formName,
				'siteId' => $siteId
			]
		);

		$dialog->setMap([
			'TerminateCurrentWorkflow' => [
				'Name' => Loc::getMessage('BPDDA_TERMINATE_CURRENT_WORKFLOW'),
				'FieldName' => 'TerminateCurrentWorkflow',
				'Type' => 'bool',
				'Default' => 'Y',
				'Required' => true,
			]
		]);

		return $dialog;
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
		$properties = [
			'TerminateCurrentWorkflow' => (string)($arCurrentValues['TerminateCurrentWorkflow'] ?? ''),
		];

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		$errors = self::ValidateProperties($properties, $user);
		if ($errors)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity['Properties'] = $properties;

		return true;
	}
}
