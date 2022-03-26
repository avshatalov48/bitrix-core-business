<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPMathOperationActivity extends CBPActivity
{
	private const NUMERIC_TYPES = ['int', 'integer', 'double'];

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'Conditions' => [],
		];
	}

	public function execute(): int
	{
		$conditions = $this->getRawProperty('Conditions');
		if (!$conditions)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		foreach ($conditions as $varId => $condition)
		{
			$parameter1 = $condition[0];
			$operation = $condition[1];
			$parameter2 = $condition[2];

			// ={=Document:ID}+{=GlobalConst:Constant1}
			$calcCondition = '=' . $parameter1 . $operation .$parameter2;

			[$groupId, $id] = static::getGroupIdAndIdFromSystemExpression($varId);
			$varProperty = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getById($id);
			if ($varProperty !== null)
			{
				if (!in_array($varProperty['Type'], self::NUMERIC_TYPES))
				{
					break;
				}
				$varProperty['Default'] = $this->parseValue($calcCondition, $varProperty['Type']);
				\Bitrix\Bizproc\Workflow\Type\GlobalVar::upsert($id, $varProperty);
			}
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate
	): \Bitrix\Bizproc\Activity\PropertiesDialog
	{
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(
			__FILE__,
			[
				'documentType' => $documentType,
				'activityName' => $activityName,
				'workflowTemplate' => $arWorkflowTemplate,
			]
		);

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentValuesTmp =
			is_array($currentActivity['Properties']['Conditions'])
				? $currentActivity['Properties']['Conditions']
				: []
		;

		$currentValues = [];
		foreach ($currentValuesTmp as $varId => $condition)
		{
			$id = \Bitrix\Bizproc\Automation\Helper::unConvertExpressions($varId, $documentType);
			$parameter1 = \Bitrix\Bizproc\Automation\Helper::unConvertExpressions($condition[0], $documentType);
			$parameter2 = \Bitrix\Bizproc\Automation\Helper::unConvertExpressions($condition[2], $documentType);
			$currentValues[$id] = [$parameter1, $condition[1], $parameter2];
		}

		$dialog->setRuntimeData([
			'currentValues' => $currentValues,
			'variables' => static::getVariables($documentType),
			'constants' => static::getConstants($documentType),
			'operations' => static::getOperations(),
			'documentFields' => static::getDocumentFields($documentType),
			'visibilityMessages' => static::getVisibilityMessages($documentType),
		]);

		return $dialog;
	}

	private static function getVisibilityMessages(array $documentType): array
	{
		$variables = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getVisibilityFullNames($documentType);
		$constants = \Bitrix\Bizproc\Workflow\Type\GlobalConst::getVisibilityFullNames($documentType);

		return [
			\Bitrix\Bizproc\Workflow\Type\GlobalVar::getObjectNameForExpressions() => $variables,
			\Bitrix\Bizproc\Workflow\Type\GlobalConst::getObjectNameForExpressions() => $constants,
			'Document' => ['Document' => \Bitrix\Main\Localization\Loc::getMessage('BPMOA_DOCUMENT')],
		];
	}

	private static function getVariables($parameterDocumentType): array
	{
		$numericVariables = static::getAllNumericVariables($parameterDocumentType);

		return static::prepareGlobalsForMenu(
			$numericVariables,
			\Bitrix\Bizproc\Workflow\Type\GlobalVar::getObjectNameForExpressions()
		);
	}

	private static function getAllNumericVariables($parameterDocumentType): array
	{
		return array_filter(
			\Bitrix\Bizproc\Workflow\Type\GlobalVar::getAll($parameterDocumentType),
			'self::filterNumericTypes'
		);
	}

	private static function getConstants($parameterDocumentType): array
	{
		$numericConstants = static::getAllNumericConstants($parameterDocumentType);

		return static::prepareGlobalsForMenu(
			$numericConstants,
			\Bitrix\Bizproc\Workflow\Type\GlobalConst::getObjectNameForExpressions()
		);
	}

	private static function getAllNumericConstants($documentType): array
	{
		return array_filter(
			\Bitrix\Bizproc\Workflow\Type\GlobalConst::getAll($documentType),
			'self::filterNumericTypes'
		);
	}

	private static function prepareGlobalsForMenu(array $globals, string $objectName): array
	{
		// TODO: combine prepareGlobalsForMenu and prepareDocumentFieldsForMenu
		$result = [];
		foreach ($globals as $id => $property)
		{
			$visibility = $property['Visibility'];
			if (!$result[$visibility])
			{
				$result[$visibility] = [];
			}

			$result[$visibility][] = [
				'title' => $property['Name'],
				'entityId' => 'bp',
				'tabs' => 'recents',
				'id' => '{='. $objectName . ':' . $id . '}',
				'customData' => [
					'property' => $property,
					'groupId' => $objectName . ':' . $visibility,
					'title' => $property['Name'],
				],
			];
		}

		return $result;
	}

	private static function getDocumentFields($parameterDocumentType): array
	{
		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$documentFields = $documentService->GetDocumentFields($parameterDocumentType);

		$numericDocumentFields = static::getAllNumericDocumentFields($parameterDocumentType);

		return static::prepareDocumentFieldsForMenu(
			$numericDocumentFields,
			$documentService->getEntityName($parameterDocumentType[0], $parameterDocumentType[1])
		);
	}

	private static function getAllNumericDocumentFields($documentType): array
	{
		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$documentFields = $documentService->GetDocumentFields($documentType);

		return array_filter($documentFields, 'self::filterNumericTypes');
	}

	private static function filterNumericTypes(array $properties): bool
	{
		if (in_array($properties['Type'], self::NUMERIC_TYPES, true))
		{
			return true;
		}

		return false;
	}

	private static function prepareDocumentFieldsForMenu(array $documentFields, string $entityName): array
	{
		$result = [
			'ROOT' => [
				'title' => $entityName,
				'entityId' => 'bp',
				'tabs' => 'recents',
				'id' => 'ROOT',
				'children' => [],
			],
		];

		foreach ($documentFields as $id => $property)
		{
			$posDot = mb_strpos($id, '.');
			$groupKey = ($posDot === false) ? 'ROOT' : mb_substr($id, 0, $posDot);

			$groupName = '';
			$fieldName = $property['Name'];
			$posColon = mb_strpos($fieldName, ': ');
			if ($fieldName && $groupKey !== 'ROOT' && $posColon !== false)
			{
				$names = mb_split(': ', $fieldName);
				$groupName = array_shift($names);
				$fieldName = join(': ', $names);
			}

			$posAssignedBy = mb_strpos($fieldName, 'ASSIGNED_BY_');
			if ($posAssignedBy !== false && !in_array($fieldName, ['ASSIGNED_BY_ID', 'ASSIGNED_BY_PRINTABLE']))
			{
				$groupKey = 'ASSIGNED_BY';
				$names = mb_split(' ', $fieldName);
				$groupName = array_shift($names);
				$fieldName = join(' ', $names);
				$fieldName = mb_ereg_replace('(', '', $fieldName);
				$fieldName = mb_ereg_replace(')', '', $fieldName);
			}

			if (!$result[$groupKey])
			{
				$result[$groupKey] = [
					'title' => $groupName,
					'entityId' => 'bp',
					'tabs' => 'recents',
					'id' => $groupName,
					'children' => [],
				];
			}

			$systemExpression = '{=Document:'. $id . '}';
			$absoluteName = ($groupKey === 'ROOT') ? $entityName . ': ' . $property['Name'] : $property['Name'];

			$result[$groupKey]['children'][] = [
				'title' => $fieldName ?? $id,
				'entityId' => 'bp',
				'tabs' => 'recents',
				'id' => $systemExpression,
				'customData' => [
					'property' => $property,
					'groupId' => 'Document:Document',
					'title' => $absoluteName,
				],
			];
		}

		return $result;
	}

	private static function getOperations(): array
	{
		return ['+', '-', '*', '/'];
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	) : bool
	{
		$arErrors = [];
		$properties = ['Conditions' => []];

		$allVariables = static::getAllNumericVariables($documentType);
		if (!$allVariables)
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => '',
				'message' => GetMessage('BPMOA_EMPTY_VARIABLE'),
			];

			return false;
		}

		$allParameters = array_merge(
			$allVariables,
			static::getAllNumericConstants($documentType),
			static::getAllNumericDocumentFields($documentType)
		);

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService('DocumentService');

		$htmlVariableCode = 'bp_moa_variable_';
		$htmlOperationCode = 'bp_moa_operation_';
		$htmlParameter1Code = 'bp_moa_common1_';
		$htmlParameter2Code = 'bp_moa_common2_';

		$lenHtmlVariableCode = mb_strlen($htmlVariableCode);

		foreach ($arCurrentValues as $htmlCode => $id)
		{
			if (mb_strpos($htmlCode, $htmlVariableCode) !== 0)
			{
				continue;
			}

			$index = mb_substr($htmlCode, $lenHtmlVariableCode);
			if ($index . '!' !== intval($index) . '!')
			{
				continue;
			}

			$variable = $documentService->GetFieldInputValue(
				$documentType,
				'string',
				$htmlCode,
				$arCurrentValues,
				$arErrors
			);
			if ($arErrors)
			{
				return false;
			}

			$parameter1 = $documentService->GetFieldInputValue(
				$documentType,
				'double',
				$htmlParameter1Code . $index,
				$arCurrentValues,
				$arErrors
			);
			if ($arErrors)
			{
				return false;
			}

			$operation = $documentService->GetFieldInputValue(
				$documentType,
				'string',
				$htmlOperationCode . $index,
				$arCurrentValues,
				$arErrors
			);

			$parameter2 = $documentService->GetFieldInputValue(
				$documentType,
				'double',
				$htmlParameter2Code . $index,
				$arCurrentValues,
				$arErrors
			);
			if ($arErrors)
			{
				return false;
			}

			if (!static::isFieldValueCorrect($variable, $allVariables, false)
				|| !static::isFieldValueCorrect($parameter1, $allParameters, true)
				|| !static::isFieldValueCorrect($parameter2, $allParameters, true)
			)
			{
				$arErrors[] = [
					'code' => 'NotExist',
					'parameter' => '',
					'message' => GetMessage('BPMOA_INVALID_VALUE'),
				];

				return false;
			}

			$properties['Conditions'][$variable] = [$parameter1, $operation, $parameter2];
		}

		$arErrors = static::validateProperties($properties);
		if ($arErrors)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $properties;

		return true;
	}

	private static function isFieldValueCorrect($value, $source, bool $checkNumeric = true): bool
	{
		if ($value === null || $value === '')
		{
			return false;
		}

		[$groupId, $fieldId] = static::getGroupIdAndIdFromSystemExpression($value);

		$isExistField= array_key_exists($fieldId, $source);
		if ($checkNumeric)
		{
			return is_numeric($value) || $isExistField;
		}

		return $isExistField;
	}

	private static function getGroupIdAndIdFromSystemExpression(string $text): array
	{
		$result = CBPActivity::parseExpression($text);
		if ($result === null)
		{
			return [];
		}

		return [$result['object'], $result['field']];
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null): array
	{
		$errors = [];

		if (
			!is_array($arTestProperties)
			|| !array_key_exists('Conditions', $arTestProperties)
			|| !is_array($arTestProperties['Conditions'])
			|| count($arTestProperties['Conditions']) <= 0
		)
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'Conditions',
				'message' => GetMessage('BPMOA_EMPTY_VARIABLE'),
			];

			return array_merge($errors, parent::validateProperties($arTestProperties, $user));
		}

		foreach ($arTestProperties['Conditions'] as $var => $condition)
		{
			if (!static::getGroupIdAndIdFromSystemExpression($var))
			{
				$errors[] = [
					'code' => 'NotExist',
					'parameter' => '',
					'message' => GetMessage('BPMOA_EMPTY_VARIABLE'),
				];

				break;
			}

			if (!static::getGroupIdAndIdFromSystemExpression($condition[0]) && !is_numeric($condition[0]))
			{
				$errors[] = [
					'code' => 'NotExist',
					'parameter' => '',
					'message' => GetMessage('BPMOA_INVALID_VALUE'),
				];

				break;
			}

			if (!static::getGroupIdAndIdFromSystemExpression($condition[2]) && !is_numeric($condition[2]))
			{
				$errors[] = [
					'code' => 'NotExist',
					'parameter' => '',
					'message' => GetMessage('BPMOA_INVALID_VALUE'),
				];

				break;
			}

			if (!in_array($condition[1], static::getOperations()))
			{
				$errors[] = [
					'code' => 'NotExist',
					'parameter' => '',
					'message' => GetMessage('BPMOA_EMPTY_OPERATION'),
				];

				break;
			}
		}

		return array_merge($errors, parent::validateProperties($arTestProperties, $user));
	}
}