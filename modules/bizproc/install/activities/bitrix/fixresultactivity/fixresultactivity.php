<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Bizproc\Result\RenderedResult;
use Bitrix\Bizproc\Result\ResultDto;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\UserTable;

/**
 * @property-read string $DocumentId
 */
class CBPFixResultActivity extends BaseActivity
{
	protected int $resultPriority = 2;

	public const RESULT_POSITIVE = 1;
	public const RESULT_CREATED_ITEM = 2;

	public const ONLY_AUTHOR = 1;
	public const ALL_PARTICIPANTS = 2;
	public const SELECTED_USERS = 3;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'AccessType' => null,
			'AccessFields' => [],
			'ResultType' => null,
			'ResultFields' => [],
			// return
			'ErrorMessage' => null,
		];

		$this->setPropertiesTypes([
			'Title' => ['Type' => 'string'],
			'AccessType' => ['Type' => FieldType::INT],
			'ResultType' => ['Type' => FieldType::INT],
		]);
	}

	protected static function getFileName(): string
	{
		return __FILE__;
	}

	protected function reInitialize()
	{
		parent::reInitialize();
		$this->ErrorMessage = null;
	}


	public function execute()
	{
		$this->prepareProperties();
		$map = self::getPropertiesMap($this->getDocumentType());

		if ($this->workflow->isDebug())
		{
			$this->writeDebugInfo($this->getDebugInfo(
				['ResultType' => $this->ResultType],
				['ResultType' => $map['ResultType']]
			));
			$this->writeDebugInfo($this->getDebugInfo(
				['AccessType' => $this->AccessType],
				['AccessType' => $map['AccessType']]
			));
		}

		if (!in_array($this->ResultType, [self::RESULT_POSITIVE, self::RESULT_CREATED_ITEM]))
		{
			$this->trackError('Incorrect result type');

			return CBPActivityExecutionStatus::Closed;
		}

		$result = null;

		switch ($this->ResultType)
		{
			case self::RESULT_POSITIVE:
				$result = self::getResultPositive($this->ResultFields, $this->AccessType, $this->AccessFields);
				break;
			case self::RESULT_CREATED_ITEM:
				$result = self::getResultCreatedItem($this->ResultFields, $this->AccessType, $this->AccessFields);
				break;
		}

		if ($result)
		{
			$this->fixResult($result);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	protected function getAuthor(): ?int
	{
		return $this->workflow->getStartedBy();
	}

	protected static function extractAllowedUsers(array $values, array $documentId): array
	{
		if (!empty($values['AllowedUsers']))
		{

			return \CBPHelper::extractUsers($values['AllowedUsers'], $documentId);
		}

		return [];
	}

	protected static function checkResultViewRights(array $result, string $workflowId, int $userId): bool
	{
		$currentUser = new \CBPWorkflowTemplateUser($userId);
		if ($currentUser->isAdmin())
		{
			return true;
		}

		if (isset($result['ACCESS_TYPE']))
		{
			if ($result['ACCESS_TYPE'] === self::SELECTED_USERS)
			{
				return self::checkUserAccessWithSubordination($currentUser->getId(), $result['USERS'] ?? []);
			}

			if ($result['ACCESS_TYPE'] === self::ONLY_AUTHOR)
			{
				$authorId = (int)(CBPStateService::getWorkflowStateInfo($workflowId)['STARTED_BY'] ?? 0);

				return self::checkUserAccessWithSubordination($currentUser->getId(), [$authorId]);
			}

			if ($result['ACCESS_TYPE'] === self::ALL_PARTICIPANTS)
			{
				$participants = WorkflowUserTable::getUserIdsByWorkflowId($workflowId);
				if (!$participants)
				{
					$participants[] = (int)(CBPStateService::getWorkflowStateInfo($workflowId)['STARTED_BY'] ?? 0);
				}

				return self::checkUserAccessWithSubordination($currentUser->getId(), $participants);
			}
		}

		return false;
	}

	protected function getResultPositive(array $values, int $accessType, array $accessFields = []): ?ResultDto
	{
		if (!empty($values['ResultUser']))
		{
			$userId = \CBPHelper::extractFirstUser($values['ResultUser'], $this->getDocumentId());
			$resultValue = [
				'RESULT_TYPE' => self::RESULT_POSITIVE,
				'USER_ID' => $userId,
				'ACCESS_TYPE' => $accessType,
			];

			if ($accessType === self::SELECTED_USERS)
			{
				$resultValue['USERS'] = self::extractAllowedUsers($accessFields, $this->getDocumentId());
			}

			return new ResultDto(get_class($this), $resultValue);
		}

		return null;
	}

	protected function getResultCreatedItem(array $values, int $accessType, array $accessFields = []): ?ResultDto
	{
		if (!empty($values['ResultItem']))
		{
			$resultValue = null;
			if ($values['ResultItem']['object'] === 'Document')
			{
				$fieldComplexValue = $this->getRuntimeProperty($values['ResultItem']['object'], $values['ResultItem']['field'], $this);
				$fieldType = $fieldComplexValue[0] ?? null;
				$fieldValue = $fieldComplexValue[1] ?? null;
				if ($fieldType && $fieldValue)
				{
					$resultValue = [
						'RESULT_TYPE' => self::RESULT_CREATED_ITEM,
						'DOCUMENT_ID' => $this->getDocumentId(),
						'DOCUMENT_TYPE' => $this->getDocumentType(),
						'DOCUMENT_FIELD_TYPE' => $fieldType,
						'DOCUMENT_FIELD_VALUE' => $fieldValue,
					];
				}
			}
			else
			{
				$activity = $this->workflow->GetActivityByName($values['ResultItem']['object']);
				if ($activity && method_exists($activity, 'makeResultFromId'))
				{
					$resultId = $activity->__get($values['ResultItem']['field']);
					if ($resultId !== null)
					{
						$result = $activity->makeResultFromId($resultId);
						$resultValue = $result->data;
						$resultValue['RESULT_TYPE'] = self::RESULT_CREATED_ITEM;
					}
				}
			}

			if ($resultValue !== null)
			{
				$resultValue['ACCESS_TYPE'] = $accessType;
				if ($accessType === self::SELECTED_USERS)
				{
					$resultValue['USERS'] = self::extractAllowedUsers($accessFields, $this->getDocumentId());
				}
				if ($accessType === self::ONLY_AUTHOR)
				{
					$resultValue['USERS'] = [$this->getAuthor()];
				}

				return new ResultDto(get_class($this), $resultValue);
			}
		}

		return null;
	}


	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		$map = static::getPropertiesDialogMap();
		unset($map['ResultFields']);
		unset($map['AccessFields']);

		return $map;
	}

	public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
	{
		$accessTypeNames = [
			static::ONLY_AUTHOR => Loc::getMessage('BP_FRA_RESULT_ONLY_AUTHOR_CAN'),
			static::ALL_PARTICIPANTS => Loc::getMessage('BP_FRA_RESULT_ALL_PARTICIPANTS_CAN'),
			static::SELECTED_USERS => Loc::getMessage('BP_FRA_RESULT_SELECTED_USERS_CAN'),
		];

		$accessFields = [
			self::SELECTED_USERS => [
				'AllowedUsers' => [
					'Name' => GetMessage('BP_FRA_RESULT_SELECT_USERS'),
					'FieldName' => 'allowed_users',
					'Type' => FieldType::USER,
					'Multiple' => true,
					'Default' => '{=Document:CREATED_BY}',
				],
			],
		];

		$resultTypeNames = [
			static::RESULT_POSITIVE => Loc::getMessage('BP_FRA_RESULT_POSITIVE_RESULT'),
			static::RESULT_CREATED_ITEM => Loc::getMessage('BP_FRA_RESULT_CREATED_ITEM'),
		];

		$resultFields = [
			self::RESULT_POSITIVE => [
				'ResultUser' => [
					'Name' => GetMessage('BP_FRA_RESULT_USER'),
					'FieldName' => 'result_user',
					'Type' => FieldType::USER,
					'Default' => '{=Document:CREATED_BY}',
				],
			],
			self::RESULT_CREATED_ITEM => [
				'ResultItem' => [
					'Name' => GetMessage('BP_FRA_RESULT_SELECT_RESULT'),
					'FieldName' => 'result_item',
					'Type' => 'mixed',
				],
			],
		];

		return [
			'AccessType' => [
				'Name' => Loc::getMessage('BP_FRA_RESULT_WHO_CAN_VIEW_RESULT'),
				'FieldName' => 'access_type',
				'Type' => FieldType::SELECT,
				'Options' => $accessTypeNames,
				'Required' => true,
				'AllowSelection' => false,
			],

			'AccessFields' => [
				'FieldName' => 'access_fields_value',
				'Map' => $accessFields,
				'Getter' => function($dialog, $property, $currentActivity, $compatible) {
					return $currentActivity['Properties']['AccessFields'];
				},
			],

			'ResultType' => [
				'Name' => Loc::getMessage('BP_FRA_RESULT_TYPE'),
				'FieldName' => 'result_type',
				'Type' => FieldType::SELECT,
				'Options' => $resultTypeNames,
				'Required' => true,
				'AllowSelection' => false,
			],

			'ResultFields' => [
				'FieldName' => 'result_fields_value',
				'Map' => $resultFields,
				'Getter' => function($dialog, $property, $currentActivity, $compatible) {
					return $currentActivity['Properties']['ResultFields'];
				},
			],
		];
	}

	protected static function extractPropertiesValues(PropertiesDialog $dialog, array $fieldsMap): Result
	{
		$simpleMap = $fieldsMap;
		unset($simpleMap['ResultFields']);
		unset($simpleMap['AccessFields']);
		$result = parent::extractPropertiesValues($dialog, $simpleMap);

		if ($result->isSuccess())
		{
			$currentValues = $result->getData();
			$accessType = (int)$currentValues['AccessType'];
			$resultType = (int)$currentValues['ResultType'];

			if ($accessType === self::SELECTED_USERS)
			{
				$extractingAccessFields = parent::extractPropertiesValues(
					$dialog,
					$fieldsMap['AccessFields']['Map'][$accessType] ?? []
				);

				if ($extractingAccessFields->isSuccess())
				{
					$currentValues['AccessFields'] = $extractingAccessFields->getData();
					$result->setData($currentValues);
				}
				else
				{
					$result->addErrors($extractingAccessFields->getErrors());
				}
			}

			if ($resultType === self::RESULT_CREATED_ITEM)
			{
				$extractingResultFields = new Result();
				$resultItemObject = $dialog->getCurrentValue('result_item_object');
				$resultItemField = $dialog->getCurrentValue('result_item_field');
				if ($resultItemObject && $resultItemField)
				{
					$extractingResultFields->setData([
						'ResultItem' => [
							'object' => $resultItemObject,
							'field' => $resultItemField,
						],
					]);
				}
				else
				{
					$extractingResultFields->addError(
						new Error(
							Loc::getMessage('BP_FRA_RESULT_EXTRACT_ERROR'),
							1,
						)
					);
				}
			}
			else
			{
				$extractingResultFields = parent::extractPropertiesValues(
					$dialog,
					$fieldsMap['ResultFields']['Map'][$resultType] ?? []
				);
			}

			if ($extractingResultFields->isSuccess())
			{
				$currentValues['ResultFields'] = $extractingResultFields->getData();
				$result->setData($currentValues);
			}
			else
			{
				$result->addErrors($extractingResultFields->getErrors());
			}
		}

		return $result;
	}

	public static function renderResult(array $result, string $workflowId, int $userId): RenderedResult
	{
		if (!self::checkResultViewRights($result, $workflowId, $userId))
		{

			return RenderedResult::makeNoRights();
		}

		switch ($result['RESULT_TYPE'])
		{
			case self::RESULT_POSITIVE:
				$user = UserTable::getById($result['USER_ID'])->fetchObject();
				if (!$user)
				{

					return RenderedResult::makeNoResult();
				}

				return new RenderedResult(
					'[URL=/company/personal/user/' . $result['USER_ID'] . '/]'
					. CUser::FormatName(CSite::GetNameFormat(false), $user, false, true)
					. '[/URL]',
					RenderedResult::USER_RESULT,
				);

			case self::RESULT_CREATED_ITEM:
				if (isset($result['DOCUMENT_FIELD_VALUE']))
				{
					$documentService = CBPRuntime::getRuntime()->getDocumentService();

					$value = $documentService->getFieldInputValuePrintable(
						$result['DOCUMENT_TYPE'],
						$result['DOCUMENT_FIELD_TYPE'],
						$result['DOCUMENT_FIELD_VALUE'],
					);

					if (is_string($value))
					{

						return new RenderedResult($value, RenderedResult::BB_CODE_RESULT);
					}

					return RenderedResult::makeNoResult();
				}

				return parent::renderResult($result, $workflowId, $userId);
		}

		return RenderedResult::makeNoResult();
	}
}
