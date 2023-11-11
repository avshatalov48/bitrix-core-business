<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc;

/**
 * @property $ChatId
 * @property $Members
 * @property $ShowHistory
 */
class CBPImAddMemberToGroupChatActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'ChatId' => '',
			'Members' => '',
			'ShowHistory' => false,
		];

		$this->setPropertiesTypes([
			'ChatId' => [
				'Type' => Bizproc\FieldType::INT,
			],
			'Members' => [
				'Type' => Bizproc\FieldType::USER,
				'Multiple' => true,
			],
			'ShowHistory' => [
				'Type' => Bizproc\FieldType::BOOL,
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

		if ($this->workflow->isDebug())
		{
			$this->writeDebugInfo($this->getDebugInfo(['ChatId' => $chatId]));
		}

		if ($chatId === null || $chatId === 0)
		{
			return $this->closeWithError(
				Loc::getMessage('IM_ACTIVITIES_ADD_MEMBER_TO_GROUP_CHAT_ACTIVITY_ERROR_NO_CHAT') ?? ''
			);
		}

		$members = $this->getMembers();
		if (empty($members))
		{
			return $this->closeWithError(
				Loc::getMessage('IM_ACTIVITIES_ADD_MEMBER_TO_GROUP_CHAT_ACTIVITY_ERROR_EMPTY_MEMBERS') ?? ''
			);
		}

		$hideHistory = !CBPHelper::getBool($this->ShowHistory);

		$chatData = CIMChat::GetChatData(['ID' => $chatId, 'SKIP_PRIVATE' => 'Y']);
		if (!$chatData || empty($chatData['chat']))
		{
			return $this->closeWithError(
				Loc::getMessage('IM_ACTIVITIES_ADD_MEMBER_TO_GROUP_CHAT_ACTIVITY_ERROR_NO_CHAT') ?? ''
			);
		}

		$chatData = $chatData['chat'][$chatId] ?? [];
		if (!$this->canAddMembers($chatData))
		{
			return $this->closeWithError(
				Loc::getMessage('IM_ACTIVITIES_ADD_MEMBER_TO_GROUP_CHAT_ACTIVITY_ERROR_CANT_ADD') ?? ''
			);
		}

		$chat = new CIMChat(0);
		$result = $chat->AddUser($chatId, $members, $hideHistory);

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

	private function getMembers()
	{
		return CBPHelper::extractUsers($this->Members, $this->getDocumentId());
	}

	private function canAddMembers(array $chatData): bool
	{
		$moduleId = $this->getDocumentType()[0];
		$entityType = $chatData['entity_type'] ?? null;

		return $entityType === 'BP_ACTIVITY_' . mb_strtoupper($moduleId);
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
		return [
			'ChatId' => [
				'Name' => Loc::getMessage('IM_ACTIVITIES_ADD_MEMBER_TO_GROUP_CHAT_ACTIVITY_FIELD_CHAT_ID_NAME'),
				'FieldName' => 'chat_id',
				'Type' => Bizproc\FieldType::INT,
				'Required' => true,
			],
			'Members' => [
				'Name' => Loc::getMessage('IM_ACTIVITIES_ADD_MEMBER_TO_GROUP_CHAT_ACTIVITY_FIELD_MEMBERS_NAME'),
				'FieldName' => 'chat_members',
				'Type' => Bizproc\FieldType::USER,
				'Multiple' => true,
				'Required' => true,
			],
			'ShowHistory' => [
				'Name' => Loc::getMessage('IM_ACTIVITIES_ADD_MEMBER_TO_GROUP_CHAT_ACTIVITY_FIELD_SHOW_HISTORY_NAME'),
				'FieldName' => 'show_history',
				'Type' => Bizproc\FieldType::BOOL,
				'Default' => 'N',
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
		if (!Loader::includeModule('im'))
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
		$map = self::getPropertiesMap([]);

		foreach ($map as $id => $property)
		{
			$isRequired = $property['Required'] ?? false;
			if ($isRequired && empty($arTestProperties[$id]))
			{
				$errors[] = [
					'code' => 'empty' . $id,
					'message' => Loc::getMessage(
						'IM_ACTIVITIES_ADD_MEMBER_TO_GROUP_CHAT_ACTIVITY_ERROR_EMPTY_FIELD',
						['#FIELD_NAME#' => $property['Name']]
					),
				];
			}
		}

		return array_merge($errors, parent::validateProperties($arTestProperties, $user));
	}
}