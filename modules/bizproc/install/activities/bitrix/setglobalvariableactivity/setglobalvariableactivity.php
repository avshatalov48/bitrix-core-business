<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPSetGlobalVariableActivity extends CBPActivity
{
	private array $logMap = [];
	private array $logValues = [];

	private static array $visibilityMessages = [];

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'GlobalVariableValue' => null,
		];
	}

	public function execute(): int
	{
		$changeVariables = $this->getRawProperty('GlobalVariableValue');
		if (!$changeVariables)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		foreach ($changeVariables as $systemExpression => $changeTo)
		{
			[$groupId, $variableId] = static::getGroupIdAndIdFromSystemExpression($systemExpression);
			$variableId = $variableId ?? $systemExpression;

			$property = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getById($variableId);
			if ($property === null)
			{
				if ($this->workflow->isDebug())
				{
					$this->addToDebugLog($systemExpression, [], $changeTo);
				}

				continue;
			}

			$documentService = $this->workflow->GetService("DocumentService");
			$fieldType = $documentService->getFieldTypeObject($this->getDocumentType(), $property);
			$fieldType->setValue($this->parseValue($changeTo, $property['Type']));
			$property['Default'] = $fieldType->getValue();

			\Bitrix\Bizproc\Workflow\Type\GlobalVar::upsert($variableId, $property);

			if ($this->workflow->isDebug())
			{
				$this->addToDebugLog($systemExpression, $property);
			}
		}

		if ($this->workflow->isDebug())
		{
			$this->writeDebugInfo($this->getDebugInfo($this->logValues, $this->logMap));
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function addToDebugLog($systemExpression, $property, $changeTo = '')
	{
		[$groupId, $variableId] = static::getGroupIdAndIdFromSystemExpression($systemExpression);
		$variableId = $variableId ?? $systemExpression;

		if (empty($property))
		{
			$this->logMap[$systemExpression] = [
				'Name' => $groupId . ':' . $variableId,
				'Type' => 'string',
				'Multiple' => true,
			];
			$this->logValues[$systemExpression] = $this->parseValue($changeTo, 'string');

			return;
		}

		$visibilityMessages = static::getVisibilityMessages($this->getDocumentType());
		$fullName = $visibilityMessages[$groupId][$property['Visibility']] . ': ' . $property['Name'];

		$this->logMap[$systemExpression] = [
			'Name' => $fullName,
			'Type' => $property['Type'],
			'Multiple' => $property['Multiple'],
			'Options' => $property['Options'],
		];
		$this->logValues[$systemExpression] = $property['Default'];
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters = [],
		$arWorkflowVariables = [],
		$arCurrentValues = [],
		$formName = ''
	): \Bitrix\Bizproc\Activity\PropertiesDialog
	{
		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService('DocumentService');
		$documentFields = $documentService->GetDocumentFields($documentType);
		$fieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		$variables = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getAll($documentType);

		$javascriptFunctions = $documentService->GetJSFunctionsForFields(
			$documentType,
			'objFieldsGlobalVar',
			$variables,
			$fieldTypes
		);

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(
			__FILE__,
			[
				'documentType' => $documentType,
				'activityName' => $activityName,
				'workflowTemplate' => $arWorkflowTemplate,
				'formName' => $formName,
			]
		);

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$variableValuesTmp = [];
		if (
			isset($currentActivity['Properties']['GlobalVariableValue'])
			&& is_array($currentActivity['Properties']['GlobalVariableValue'])
		)
		{
			$variableValuesTmp = $currentActivity['Properties']['GlobalVariableValue'];
		}

		$variableValues = [];
		foreach ($variableValuesTmp as $varId => $value)
		{
			if (array_key_exists($varId, $variables))
			{
				$varId = '{=GlobalVar:' . $varId . '}';
			}
			$id = \Bitrix\Bizproc\Automation\Helper::unConvertExpressions($varId, $documentType);
			[$object, $gVar] = static::getGroupIdAndIdFromSystemExpression($id);
			if (!array_key_exists($gVar, $variables))
			{
				continue;
			}
			if (is_array($value))
			{
				$result = [];
				foreach ($value as $valueItem)
				{
					$unconvertedValue = \Bitrix\Bizproc\Automation\Helper::unConvertExpressions($valueItem, $documentType);
					if (!static::getGroupIdAndIdFromSystemExpression($unconvertedValue))
					{
						$result[] = $valueItem;
					}
					else
					{
						$result[] = $unconvertedValue;
					}
				}
			}
			else
			{
				$result = \Bitrix\Bizproc\Automation\Helper::unConvertExpressions($value, $documentType);
				if (!static::getGroupIdAndIdFromSystemExpression($result))
				{
					$result = $value;
				}
			}
			$variableValues[$id] = $result;
		}

		$dialog->setRuntimeData([
			'currentValues' => $variableValues,
			'variables' => static::getVariables($documentType),
			'constants' => static::getConstants($documentType),
			'documentFields' => static::getDocumentFields($documentType),
			'javascriptFunctions' => $javascriptFunctions,
			'visibilityMessages' => static::getVisibilityMessages($documentType),
		]);

		return $dialog;
	}

	private static function getVariables(array $parameterDocumentType): array
	{
		$variables = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getAll($parameterDocumentType);

		return static::prepareGlobalsForMenu(
			$variables,
			\Bitrix\Bizproc\Workflow\Type\GlobalVar::getObjectNameForExpressions()
		);
	}

	private static function getConstants(array $parameterDocumentType): array
	{
		$constants = \Bitrix\Bizproc\Workflow\Type\GlobalConst::getAll($parameterDocumentType);

		return static::prepareGlobalsForMenu(
			$constants,
			\Bitrix\Bizproc\Workflow\Type\GlobalConst::getObjectNameForExpressions()
		);
	}

	private static function prepareGlobalsForMenu(array $globals, string $objectName):array
	{
		$result = [];
		foreach ($globals as $id => $property)
		{
			$visibility = $property['Visibility'];
			if (!isset($result[$visibility]))
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

	private static function getDocumentFields(array $parameterDocumentType): array
	{
		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$documentFields = $documentService->GetDocumentFields($parameterDocumentType);

		return static::prepareDocumentFieldsForMenu(
			$documentFields,
			$documentService->getEntityName($parameterDocumentType[0], $parameterDocumentType[1])
		);
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
				$names = explode(': ', $fieldName);
				$groupName = array_shift($names);
				$fieldName = join(': ', $names);
			}

			$posAssignedBy = mb_strpos($fieldName, 'ASSIGNED_BY_');
			if ($posAssignedBy !== false && !in_array($fieldName, ['ASSIGNED_BY_ID', 'ASSIGNED_BY_PRINTABLE']))
			{
				$groupKey = 'ASSIGNED_BY';
				$names = explode(' ', $fieldName);
				$groupName = array_shift($names);
				$fieldName = join(' ', $names);
				$fieldName = mb_ereg_replace('(', '', $fieldName);
				$fieldName = mb_ereg_replace(')', '', $fieldName);
			}

			if (!isset($result[$groupKey]))
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

	private static function getVisibilityMessages(array $documentType): array
	{
		if (isset(self::$visibilityMessages[implode('@', $documentType)]))
		{
			return self::$visibilityMessages[implode('@', $documentType)];
		}

		$variables = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getVisibilityFullNames($documentType);
		$constants = \Bitrix\Bizproc\Workflow\Type\GlobalConst::getVisibilityFullNames($documentType);

		self::$visibilityMessages[implode('@', $documentType)] = [
			\Bitrix\Bizproc\Workflow\Type\GlobalVar::getObjectNameForExpressions() => $variables,
			\Bitrix\Bizproc\Workflow\Type\GlobalConst::getObjectNameForExpressions() => $constants,
			'Document' => ['Document' => \Bitrix\Main\Localization\Loc::getMessage('BPSGVA_DOCUMENT')],
		];

		return self::$visibilityMessages[implode('@', $documentType)];
	}

	private static function getGroupIdAndIdFromSystemExpression(string $text): array
	{
		$result = CBPActivity::parseExpression($text);
		if ($result === null || $result['modifiers'])
		{
			return [];
		}

		return [$result['object'], $result['field']];
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	): bool
	{
		$arErrors = [];
		$properties = ['GlobalVariableValue' => []];

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService('DocumentService');

		$allVariables = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getAll($documentType);
		if (!$allVariables)
		{
			$arErrors[] = [
				'code' => 'EmptyGlobalVariables',
				'parameter' => '',
				'message' => GetMessage('BPSGCA_EMPTY_VARIABLES'),
			];

			return false;
		}

		$htmlVariableCode = 'bp_sgva_variable_';
		$htmlValueCode = 'bp_sgva_value_';
		$lenHtmlVariableCode = mb_strlen($htmlVariableCode);
		foreach ($arCurrentValues as $htmlCode => $variableId)
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

			$var = $documentService->GetFieldInputValue(
				$documentType,
				'string',
				$htmlCode,
				$arCurrentValues,
				$arErrors
			);
			[$groupId, $varId] = static::getGroupIdAndIdFromSystemExpression($var);

			if ($arErrors)
			{
				return false;
			}

			if (!$allVariables[$varId])
			{
				$arErrors[] = [
					'code' => 'NotExist',
					'parameter' => 'GlobalVariableValue',
					'message' => GetMessage('BPSGCA_EMPTY_VARIABLES'),
				];

				return false;
			}

			$value = $documentService->GetFieldInputValue(
				$documentType,
				$allVariables[$varId],
				$htmlValueCode . $index,
				$arCurrentValues,
				$arErrors
			);

			if ($arErrors)
			{
				return false;
			}

			if ($value === null || $value === [])
			{
				$value = $documentService->GetFieldInputValue(
					$documentType,
					$allVariables[$varId],
					$varId,
					$arCurrentValues,
					$arErrors
				);
			}

			if ($arErrors)
			{
				return false;
			}

			if ($var === null || $var === '')
			{
				continue;
			}

			$properties['GlobalVariableValue'][$var] = $value;
		}

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		$arErrors = static::validateProperties($properties, $user);
		if ($arErrors)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $properties;

		return true;
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null): array
	{
		$errors = [];

		if (
			!is_array($arTestProperties)
			|| !array_key_exists('GlobalVariableValue', $arTestProperties)
			|| !is_array($arTestProperties['GlobalVariableValue'])
			|| count($arTestProperties['GlobalVariableValue']) <= 0
		)
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'GlobalVariableValue',
				'message' => GetMessage('BPSGCA_EMPTY_VARIABLES'),
			];
		}

		return array_merge($errors, parent::validateProperties($arTestProperties, $user));
	}

	public function collectUsages(): array
	{
		$usages = parent::collectUsages();
		if (is_array($this->arProperties['GlobalVariableValue']))
		{
			foreach (array_keys($this->arProperties['GlobalVariableValue']) as $variable)
			{
				[$groupId, $varId] = static::getGroupIdAndIdFromSystemExpression($variable);
				$varId = $varId ?? $variable;
				$groupId = $groupId ?? 'GlobalVar';

				$usages[] = $this->getObjectSourceType($groupId, $varId);
			}
		}

		return $usages;
	}
}
