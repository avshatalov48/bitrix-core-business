<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc;
use Bitrix\Im\Integration\Bizproc\Message;

/**
 * @property $ChatId
 * @property $FromMember
 * @property $MessageTemplate
 * @property $MessageFields
 */
class CBPImAddMessageToGroupChatActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'ChatId' => '',
			'FromMember' => '',
			'MessageTemplate' => '',
			'MessageFields' => [],
		];

		$this->setPropertiesTypes([
			'ChatId' => [
				'Type' => Bizproc\FieldType::INT,
			],
			'FromMember' => [
				'Type' => Bizproc\FieldType::USER,
			],
			'MessageTemplate' => [
				'Type' => Bizproc\FieldType::SELECT,
			],
		]);
	}

	public function execute()
	{
		if (!Loader::includeModule('im'))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$chatId = $this->getChatId();
		$from = $this->getFromMember();

		if ($this->workflow->isDebug())
		{
			$this->writeDebugInfo(
				$this->getDebugInfo([
					'ChatId' => $chatId,
					'FromMember' => !empty($from) ? 'user_' . $from : null,
					'MessageTemplate' => $this->MessageTemplate,
				])
			);
			$this->logMessageFields();
		}

		if ($chatId === null || $chatId === 0)
		{
			return $this->closeWithError(
				Loc::getMessage('IM_ACTIVITIES_ADD_MESSAGE_TO_GROUP_CHAT_ACTIVITY_ERROR_EMPTY_CHAT_ID') ?? ''
			);
		}
		if (empty($from))
		{
			return $this->closeWithError(
				Loc::getMessage('IM_ACTIVITIES_ADD_MESSAGE_TO_GROUP_CHAT_ACTIVITY_ERROR_EMPTY_FROM_MEMBER') ?? ''
			);
		}

		$chatData = CIMChat::GetChatData(['ID' => $chatId, 'SKIP_PRIVATE' => 'Y']);
		if (!$chatData || empty($chatData['chat']))
		{
			return $this->closeWithError(
				Loc::getMessage('IM_ACTIVITIES_ADD_MESSAGE_TO_GROUP_CHAT_ACTIVITY_ERROR_NO_CHAT') ?? ''
			);
		}

		$formatResult = $this->formatMessage([
			'FROM_USER_ID' => $from,
			'TO_CHAT_ID' => $chatId,
		]);

		if (!$formatResult->isSuccess())
		{
			return $this->closeWithError(implode(', ', $formatResult->getErrorMessages()));
		}
		$result = CIMChat::AddMessage($formatResult->getData());

		if ($result === false)
		{
			global $APPLICATION;

			$exception = $APPLICATION->GetException();
			if ($exception)
			{
				return $this->closeWithError($exception->GetString() ?? '');
			}

			return CBPActivityExecutionStatus::Closed;
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

	private function getChatId(): ?int
	{
		$chatId = $this->ChatId;
		if (is_scalar($chatId))
		{
			return (int)$chatId;
		}

		return null;
	}

	private function getFromMember()
	{
		return CBPHelper::extractFirstUser($this->FromMember, $this->getDocumentId());
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$workflowTemplate,
		$workflowParameters,
		$workflowVariables,
		$currentValues = null,
		$formName = '',
		$popupWindow = null,
		$siteId = ''
	)
	{
		if (!Loader::includeModule('im'))
		{
			return '';
		}

		$dialog = new Bizproc\Activity\PropertiesDialog(
			__FILE__,
			[
				'documentType' => $documentType,
				'activityName' => $activityName,
				'workflowTemplate' => $workflowTemplate,
				'workflowParameters' => $workflowParameters,
				'workflowVariables' => $workflowVariables,
				'currentValues' => $currentValues,
				'formName' => $formName,
				'siteId' => $siteId,
			]
		);

		$dialog->setMap(static::getPropertiesMap($documentType));

		return $dialog;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		if (!Loader::includeModule('im'))
		{
			return [];
		}

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		$templateList = Message\Collection::getTemplateList();

		$map = [
			'ChatId' => [
				'Name' => Loc::getMessage('IM_ACTIVITIES_ADD_MESSAGE_TO_GROUP_CHAT_ACTIVITY_FIELD_CHAT_ID'),
				'FieldName' => 'chat_id',
				'Type' => Bizproc\FieldType::INT,
				'Required' => true,
			],
			'FromMember' => [
				'Name' => Loc::getMessage('IM_ACTIVITIES_ADD_MESSAGE_TO_GROUP_CHAT_ACTIVITY_FIELD_FROM_MEMBER'),
				'FieldName' => 'from_member',
				'Type' => Bizproc\FieldType::USER,
				'Required' => true,
				'Default' => $user->getBizprocId(),
			],
			'MessageTemplate' => [
				'Name' => Loc::getMessage('IM_ACTIVITIES_ADD_MESSAGE_TO_GROUP_CHAT_ACTIVITY_FIELD_MESSAGE_TEMPLATE'),
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

		return $map;
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
						'IM_ACTIVITIES_ADD_MESSAGE_TO_GROUP_CHAT_ACTIVITY_EMPTY_FIELD',
						['#FIELD_NAME#' => $property['Name']]
					),
				];
			}
		}

		return array_merge($errors, parent::validateProperties($arTestProperties, $user));
	}
}
