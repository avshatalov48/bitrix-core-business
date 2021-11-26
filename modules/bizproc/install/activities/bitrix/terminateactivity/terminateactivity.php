<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPTerminateActivity extends CBPActivity
{
	private const TERMINATE_CURRENT = 'current';
	private const TERMINATE_ALL_BY_DOC_AND_TMP = 'allByDocumentAndTemplate';
	private const TERMINATE_ALL_EXCEPT_CURRENT_BY_DOC_AND_TMP = 'allExceptCurrentByDocumentAndTemplate';

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'StateTitle' => '',
			'KillWorkflow' => 'N',
			'TerminateType' => self::TERMINATE_CURRENT,
		];
	}

	public function Execute()
	{
		$killWorkflow = ($this->KillWorkflow === 'Y');

		if ($killWorkflow)
		{
			$this->killWorkflows();
		}
		else
		{
			$this->terminateWorkflows();
		}

		return CBPActivityExecutionStatus::Closed;
	}

	/**
	 * @throws Exception
	 */
	private function killWorkflows()
	{
		$terminateType = $this->TerminateType;

		switch ($terminateType)
		{
			case self::TERMINATE_ALL_EXCEPT_CURRENT_BY_DOC_AND_TMP:
				$workflowsIds = self::getWFIdsByDocumentAndTemplate(
					$this->GetDocumentId(),
					$this->GetWorkflowTemplateId()
				);
				$this->killWorkflowsByIdsExceptCurrent($workflowsIds);
				break;

			case self::TERMINATE_ALL_BY_DOC_AND_TMP:
				$workflowsIds = self::getWFIdsByDocumentAndTemplate(
					$this->GetDocumentId(),
					$this->GetWorkflowTemplateId()
				);
				$this->killWorkflowsByIdsExceptCurrent($workflowsIds);
				$this->killCurrentWFAndThrowException();
				break;

			default:
				$this->killCurrentWFAndThrowException();
				break;
		}
	}

	/**
	 * @throws Exception
	 */
	private function terminateWorkflows()
	{
		$terminateType = $this->TerminateType;
		$stateTitle = (string)$this->StateTitle;

		switch ($terminateType)
		{
			case self::TERMINATE_ALL_EXCEPT_CURRENT_BY_DOC_AND_TMP:
				$workflowsIds = self::getWFIdsByDocumentAndTemplate(
					$this->GetDocumentId(),
					$this->GetWorkflowTemplateId()
				);
				$this->terminateWorkflowsByIdsExceptCurrent($workflowsIds, $stateTitle);
				break;

			case self::TERMINATE_ALL_BY_DOC_AND_TMP:
				$workflowsIds = self::getWFIdsByDocumentAndTemplate(
					$this->GetDocumentId(),
					$this->GetWorkflowTemplateId()
				);
				$this->terminateWorkflowsByIdsExceptCurrent($workflowsIds, $stateTitle);
				$this->terminateCurrentWFAndThrowException($stateTitle);
				break;

			default:
				$this->terminateCurrentWFAndThrowException($stateTitle);
				break;
		}
	}

	/**
	 * @throws Exception
	 */
	private function killCurrentWFAndThrowException()
	{
		self::killWorkflow($this->GetWorkflowInstanceId());
		self::closedException();
	}

	/**
	 * @throws Exception
	 */
	private function terminateCurrentWFAndThrowException($stateTitle)
	{
		self::terminateWorkflow($this->GetWorkflowInstanceId(), $stateTitle);
		self::closedException();
	}

	/**
	 * @throws Exception
	 */
	private static function closedException()
	{
		throw new Exception('TerminateActivity');
	}

	private function killWorkflowsByIdsExceptCurrent(array $workflowsId)
	{
		$workflowsId = $this->deleteCurrentIdFromWFIds($workflowsId);
		foreach ($workflowsId as $id)
		{
			self::killWorkflow($id);
		}
	}

	private function terminateWorkflowsByIdsExceptCurrent($workflowsIds, $stateTitle)
	{
		$workflowsIds = $this->deleteCurrentIdFromWFIds($workflowsIds);
		foreach ($workflowsIds as $id)
		{
			self::terminateWorkflow($id, $stateTitle);
		}
	}

	private function deleteCurrentIdFromWFIds(array $workflowsId): array
	{
		$currentId = $this->GetWorkflowInstanceId();
		while (($key = array_search($currentId, $workflowsId)) !== false)
		{
			unset($workflowsId[$key]);
		}

		return $workflowsId;
	}

	private static function killWorkflow($id)
	{
		CBPDocument::killWorkflow($id);
	}

	private static function terminateWorkflow($workflowId, $stateTitle)
	{
		CBPDocument::TerminateWorkflow(
			$workflowId,
			null,
			$arErrorsTmp,
			$stateTitle
		);
	}

	private static function getWFIdsByDocumentAndTemplate($documentId, $workflowTemplateId): array
	{
		$documentId = \CBPHelper::ParseDocumentId($documentId);
		$result = \Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=DOCUMENT_ID' => $documentId[2],
				'=WORKFLOW_TEMPLATE_ID' => $workflowTemplateId
			],
		]);
		$rows = $result ? $result->fetchAll() : [];

		return array_column($rows, 'ID');
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
			'TerminateType' => [
				'Name' => GetMessage('BPTA1_TERMINATE'),
				'FieldName' => 'terminate_type',
				'Type' => 'select',
				'Options' => self::getTerminateTypeOptions(),
				'Default' => self::TERMINATE_CURRENT,
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

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$errors
	)
	{
		$errors = [];

		$properties = [
			'StateTitle' => $arCurrentValues['state_title'],
			'KillWorkflow' => 'N',
			'TerminateType' => self::TERMINATE_CURRENT,
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

		if (!empty($arCurrentValues['terminate_type']))
		{
			$properties['TerminateType'] = $arCurrentValues['terminate_type'];
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

	private static function getTerminateTypeOptions(): array
	{
		$options = [];
		$options[self::TERMINATE_CURRENT] = GetMessage('BPTA1_TERMINATE_CURRENT');
		$options[self::TERMINATE_ALL_BY_DOC_AND_TMP] = GetMessage('BPTA1_TERMINATE_ALL');
		$options[self::TERMINATE_ALL_EXCEPT_CURRENT_BY_DOC_AND_TMP] =
			GetMessage('BPTA1_TERMINATE_ALL_EXCEPT_CURRENT')
		;

		return $options;
	}
}
