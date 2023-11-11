<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\Automation\Helper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc;
use Bitrix\Im\Integration\Bizproc\Message;

/**
 * @property $MessageUserFrom
 * @property $MessageUserTo
 * @property $MessageTemplate
 * @property $MessageFields
 */
class CBPImMessageActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'MessageUserFrom' => '',
			'MessageUserTo' => '',
			'MessageTemplate' => '',
			'MessageFields' => [],
		];
	}

	public static function getPropertiesDialog(
		$documentType,
		$activityName,
		$workflowTemplate,
		$workflowParameters,
		$workflowVariables,
		$currentValues = null,
		$formName = "",
		$popupWindow = null,
		$siteId = ''
	)
	{
		if (!Loader::includeModule('im'))
		{
			return '';
		}

		$dialog = new PropertiesDialog(__FILE__, [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $workflowTemplate,
			'workflowParameters' => $workflowParameters,
			'workflowVariables' => $workflowVariables,
			'currentValues' => $currentValues,
			'formName' => $formName,
			'siteId' => $siteId,
		]);

		$dialog->setMap(static::getPropertiesMap($documentType));

		return $dialog;
	}

	public static function getPropertiesMap(array $documentType, array $context = []): array
	{
		if (!Loader::includeModule('im'))
		{
			return [];
		}

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		$templateList = Message\Collection::getTemplateList();

		return [
			'MessageUserFrom' => [
				'Name' => Loc::getMessage('BPIMMA_PD_TO'),
				'FieldName' => 'from_user_id',
				'Type' => 'user',
				'Required' => true,
				'Default' => $user->getBizprocId(),
			],
			'MessageUserTo' => [
				'Name' => Loc::getMessage('BPIMMA_PD_FROM'),
				'FieldName' => 'to_user_id',
				'Type' => 'user',
				'Required' => true,
				'Multiple' => true,
				'Default' => $documentType ? Helper::getResponsibleUserExpression($documentType) : null,
			],
			'MessageTemplate' => [
				'Name' => Loc::getMessage('BPIMMA_PD_MESSAGE'),
				'FieldName' => 'message_template',
				'Type' => Bizproc\FieldType::SELECT,
				'Required' => true,
				'Options' => $templateList,
				'Default' => array_key_first($templateList),
				'Settings' => [
					'AllowSelection' => false,
					'ShowEmptyValue' => false,
				],
			],
			'MessageFields' => [
				'FieldName' => 'message_fields',
				'Map' => static::getMessageFieldsMap(),
				'Getter' => function($dialog, $property, $currentActivity, $compatible) {
					return $currentActivity['Properties']['MessageFields'];
				},
			],
		];
	}

	private static function getMessageFieldsMap(): array
	{
		if (!Loader::includeModule('im'))
		{
			return [];
		}

		$types = array_keys(Message\Collection::getTemplateList());
		$map = [];

		foreach ($types as $type)
		{
			$template = Message\Collection::makeTemplate($type);
			$map[$type] = $template::getFieldsMap();
		}

		return $map;
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$workflowTemplate,
		&$workflowParameters,
		&$workflowVariables,
		$currentValues,
		&$errors
	)
	{
		if (!Loader::includeModule('im'))
		{
			return false;
		}

		$documentService = CBPRuntime::getRuntime()->getDocumentService();

		$errors = [];
		$properties = [];
		$map = static::getPropertiesMap($documentType);
		foreach ($map as $id => $property)
		{
			$value = $documentService->getFieldInputValue(
				$documentType,
				$property,
				$property['FieldName'],
				$currentValues,
				$errors
			);
			if ($errors)
			{
				return false;
			}

			$properties[$id] = $value;
		}

		$properties['MessageFields'] = [];
		foreach ($map['MessageFields']['Map'][$properties['MessageTemplate']] as $id => $property)
		{
			$value = $documentService->getFieldInputValue(
				$documentType,
				$property,
				$property['FieldName'],
				$currentValues,
				$errors
			);
			if ($errors)
			{
				return false;
			}

			$properties['MessageFields'][$id] = $value;
		}

		$workflowTemplateUser = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		$errors = self::validateProperties($properties, $workflowTemplateUser);

		if ($errors)
		{
			return false;
		}

		$currentActivity = &self::findActivityInTemplate($workflowTemplate, $activityName);
		$currentActivity['Properties'] = $properties;

		return true;
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];
		$map = self::getPropertiesMap([]);

		foreach ($map as $id => $property)
		{
			$isRequired = $property['Required'] ?? false;
			if ($isRequired && empty($arTestProperties[$id]))
			{
				$errors[] = [
					'code' => 'empty' . $id,
					'message' => Loc::getMessage(
						'BPIMMA_EMPTY_FIELD',
						['#FIELD_NAME#' => $property['Name']]
					),
				];
			}
		}

		return array_merge($errors, parent::validateProperties($arTestProperties, $user));
	}

	public function execute()
	{
		if (!CModule::IncludeModule('im'))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$messageUserFrom = CBPHelper::extractFirstUser($this->MessageUserFrom, $this->getDocumentId());
		$messageUserTo = CBPHelper::ExtractUsers($this->MessageUserTo, $this->getDocumentId());

		if ($this->workflow->isDebug())
		{
			$this->writeDebugInfo(
				$this->getDebugInfo([
					'MessageUserFrom' => !empty($messageUserFrom) ? 'user_' . $messageUserFrom : null,
					'MessageTemplate' => $this->MessageTemplate,
				])
			);
			$this->logMessageFields();
		}

		if (empty($messageUserFrom))
		{
			return $this->closeWithError(
				Loc::getMessage('BPIMMA_ERROR_EMPTY_FROM_MEMBER') ?? ''
			);
		}

		$formatResult = $this->formatMessage([
			'FROM_USER_ID' => $messageUserFrom,
			'MESSAGE_TYPE' => IM_MESSAGE_PRIVATE,
		]);

		if (!$formatResult->isSuccess())
		{
			return $this->closeWithError(implode(', ', $formatResult->getErrorMessages()));
		}

		$messageFields = $formatResult->getData();

		foreach ($messageUserTo as $userTo)
		{
			$messageFields['TO_USER_ID'] = $userTo;
			$result = CIMMessenger::Add($messageFields);
			if ($result === false)
			{
				global $APPLICATION;

				$exception = $APPLICATION->GetException();
				if ($exception)
				{
					$this->trackError($exception->GetString() ?? '');
				}
			}
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function logMessageFields(): self
	{
		$map = static::getPropertiesMap($this->getDocumentType());
		$messageFieldsMap = $map['MessageFields']['Map'][$this->MessageTemplate] ?? [];
		$this->writeDebugInfo($this->getDebugInfo($this->MessageFields, $messageFieldsMap));

		return $this;
	}

	private function formatMessage(array $messageFields): \Bitrix\Main\Result
	{
		$template = \CBPHelper::stringify($this->MessageTemplate);
		$formatter = Message\Collection::makeTemplate($template);

		$formatterFields = $this->MessageFields;
		$documentService = CBPRuntime::getRuntime()->getDocumentService();
		$documentType = $this->getDocumentType();

		$formatterFields['EntityTypeName'] = $documentService->getEntityName($documentType[0], $documentType[1]);
		$formatterFields['EntityName'] = $documentService->getDocumentName($this->getDocumentId());
		$formatterFields['EntityLink'] = (string)$documentService->getDocumentAdminPage($this->getDocumentId());

		$formatter->setFields($formatterFields);
		$formatter->markAsRobotMessage();
		$formatter->enablePushMessage();

		return $formatter->formatMessage($messageFields);
	}

	private function closeWithError(string $errorMessage): int
	{
		if (!empty($errorMessage))
		{
			$this->trackError($errorMessage);
		}

		return CBPActivityExecutionStatus::Closed;
	}
}
