<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc;
use Bitrix\Main\Localization\Loc;

/**
 * @property $ChatName
 * @property $Members
 * @property $ChatId
 */
class CBPImCreateGroupChatActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'ChatName' => '',
			'Members' => '',

			// return property
			'ChatId' => null,
		];

		$this->setPropertiesTypes([
			'ChatName' => [
				'Type' => Bizproc\FieldType::STRING,
			],
			'Members' => [
				'Type' => Bizproc\FieldType::USER,
				'Multiple' => true,
			],
			'ChatId' => [
				'Type' => Bizproc\FieldType::INT,
			],
		]);
	}

	public function execute()
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$chatName = $this->getChatName();
		$members = $this->getMembers();

		if ($this->workflow->isDebug())
		{
			$this->writeDebugInfo($this->getDebugInfo(['ChatName' => $chatName]));
		}

		if (empty($members))
		{
			$this->trackError(
				Loc::getMessage('IM_ACTIVITIES_CREATE_GROUP_CHAT_ACTIVITY_ERROR_EMPTY_MEMBERS') ?? ''
			);

			return CBPActivityExecutionStatus::Closed;
		}
		if (count($members) < 2)
		{
			$this->trackError(
				Loc::getMessage('IM_ACTIVITIES_CREATE_GROUP_CHAT_ACTIVITY_ERROR_COUNT_MEMBERS') ?? ''
			);

			return CBPActivityExecutionStatus::Closed;
		}

		$moduleId = $this->getDocumentType()[0];

		$chat = new CIMChat(0);
		$chatId = $chat->Add([
			'TITLE' => $chatName,
			'USERS' => $members,
			'MESSAGE' => Loc::getMessage('IM_ACTIVITIES_CREATE_GROUP_CHAT_ACTIVITY_WELCOME_MESSAGE'),
			'ENTITY_TYPE' => 'BP_ACTIVITY_' . mb_strtoupper($moduleId),
		]);

		if ($chatId === false)
		{
			global $APPLICATION;

			$exception = $APPLICATION->GetException();
			if ($exception)
			{
				$this->trackError($exception->GetString() ?? '');
			}

			return CBPActivityExecutionStatus::Closed;
		}

		$this->ChatId = $chatId;
		$this->writeToTrackingService(
			Loc::getMessage(
				'IM_ACTIVITIES_CREATE_GROUP_CHAT_ACTIVITY_NEW_CHAT_CREATED',
				['#CHAT_ID#' => $chatId]
			),
			0,
			CBPTrackingType::AttachedEntity
		);

		return CBPActivityExecutionStatus::Closed;
	}

	private function getChatName(): string
	{
		$chatName = $this->ChatName;
		if (is_scalar($chatName) || (is_object($chatName) && method_exists($chatName, '__toString')))
		{
			return (string)$chatName;
		}

		return '';
	}

	private function getMembers()
	{
		return CBPHelper::extractUsers($this->Members, $this->getDocumentId());
	}

	protected function reInitialize()
	{
		parent::reInitialize();
		$this->ChatId = null;
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
		if (!\Bitrix\Main\Loader::includeModule('im'))
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
		return [
			'ChatName' => [
				'Name' => Loc::getMessage('IM_ACTIVITIES_CREATE_GROUP_CHAT_ACTIVITY_FIELD_CHAT_NAME'),
				'FieldName' => 'chat_name',
				'Type' => Bizproc\FieldType::STRING,
				'Required' => false,
			],
			'Members' => [
				'Name' => Loc::getMessage('IM_ACTIVITIES_CREATE_GROUP_CHAT_ACTIVITY_FIELD_MEMBERS'),
				'FieldName' => 'chat_members',
				'Type' => Bizproc\FieldType::USER,
				'Multiple' => true,
				'Required' => true,
			],
		];
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
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return false;
		}

		$documentService = CBPRuntime::getRuntime()->getDocumentService();

		$errors = [];
		$properties = [];
		foreach (static::getPropertiesMap($documentType) as $id => $property)
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

		if (empty($arTestProperties['Members']))
		{
			$errors[] = [
				'code' => 'emptyMembers',
				'message' => Loc::getMessage('IM_ACTIVITIES_CREATE_GROUP_CHAT_ACTIVITY_ERROR_EMPTY_MEMBERS'),
			];
		}

		if (
			is_array($arTestProperties['Members'])
			&& count($arTestProperties['Members']) === 1
			&& strpos(current($arTestProperties['Members']), 'user_') === 0
		)
		{
			$errors[] = [
				'code' => 'countMembers',
				'message' => Loc::getMessage('IM_ACTIVITIES_CREATE_GROUP_CHAT_ACTIVITY_ERROR_COUNT_MEMBERS'),
			];
		}

		return array_merge($errors, parent::validateProperties($arTestProperties, $user));
	}
}
