<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

CBPRuntime::getRuntime()->includeActivityFile('SetFieldActivity');

/**
 * @property-write string|null ErrorMessage
 * @property-read $DocumentType
 * @property-read $ElementId
 * @property-read $Fields
 */
class CBPUpdateListsDocumentActivity extends CBPSetFieldActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties['DocumentType'] = null;
		$this->arProperties['Fields'] = null;
		$this->arProperties['ElementId'] = null;
		// return
		$this->arProperties['ErrorMessage'] = null;

		$this->setPropertiesTypes([
			'ErrorMessage' => ['Type' => 'string'],
		]);
	}

	public function execute()
	{
		if (!\Bitrix\Main\Loader::includeModule('lists'))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$documentType = $this->DocumentType;
		$elementId = $this->ElementId;

		//check for Multiple values
		if (is_array($elementId))
		{
			$elementId = array_shift($elementId);
		}

		$documentId = [$documentType[0], $documentType[1], $elementId];
		$fields = $this->Fields;
		if (!$fields || !is_array($fields))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$this->logDebug($elementId, $documentType);

		$documentService = $this->workflow->getRuntime()->getDocumentService();
		$realDocumentType = null;
		try
		{
			$realDocumentType = $documentService->getDocumentType($documentId);
		}
		catch (Exception $e) {}

		if (!$realDocumentType || $realDocumentType !== $documentType)
		{
			$this->writeToTrackingService(
				Loc::getMessage('BPULDA_ERROR_DT'),
				0,
				CBPTrackingType::Error
			);
			$this->ErrorMessage = Loc::getMessage('BPULDA_ERROR_DT');

			return CBPActivityExecutionStatus::Closed;
		}

		$fields = $this->prepareFieldsValues($documentId, $documentType, $fields, false);
		try
		{
			$this->logDebugFields($documentType, $fields);
			$documentService->updateDocument($documentId, $fields);
		}
		catch (Exception $e)
		{
			$this->writeToTrackingService($e->getMessage(), 0, CBPTrackingType::Error);
			$this->ErrorMessage = $e->getMessage();
		}

		return CBPActivityExecutionStatus::Closed;
	}

	protected function reInitialize()
	{
		parent::reInitialize();
		$this->ErrorMessage = null;
	}

	public static function validateProperties($testProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		try
		{
			CBPHelper::ParseDocumentId($testProperties['DocumentType']);
		}
		catch (Exception $e)
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'DocumentType',
				'message' => Loc::getMessage('BPULDA_ERROR_DT'),
			];
		}

		if (empty($testProperties['ElementId']))
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'ElementId',
				'message' => Loc::getMessage('BPULDA_ERROR_ELEMENT_ID'),
			];
		}

		if (empty($testProperties['Fields']))
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'Fields',
				'message' => Loc::getMessage('BPULDA_ERROR_FIELDS'),
			];
		}

		$parentValidateErrors = parent::validateProperties($testProperties, $user);
		foreach ($parentValidateErrors as $error)
		{
			if ($error['parameter'] === 'FieldValue' && $error['code'] === 'NotExist')
			{
				continue;
			}

			$errors[] = $error;
		}

		return $errors;
	}

	public static function getPropertiesDialog(
		$paramDocumentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = '',
		$popupWindow = null
	)
	{
		if (!CModule::IncludeModule('lists'))
		{
			return null;
		}

		$documentType = null;
		if (!empty($arCurrentValues['lists_document_type']))
		{
			$documentType = explode('@', $arCurrentValues['lists_document_type']);
		}

		$documentService = CBPRuntime::getRuntime()->getDocumentService();

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = ['lists_element_id' => null, 'fields' => []];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (
				!empty($arCurrentActivity['Properties']['Fields'])
				&& is_array($arCurrentActivity['Properties']['Fields'])
			)
			{
				$arCurrentValues['fields'] = $arCurrentActivity['Properties']['Fields'];
			}
			if (!empty($arCurrentActivity['Properties']['ElementId']))
			{
				$arCurrentValues['lists_element_id'] = $arCurrentActivity['Properties']['ElementId'];
			}
			if (!empty($arCurrentActivity['Properties']['DocumentType']))
			{
				$documentType = $arCurrentActivity['Properties']['DocumentType'];
				$arCurrentValues['lists_document_type'] = implode('@', $documentType);
			}
		}
		elseif ($documentType)
		{
			$arCurrentValues['fields'] = [];
			$listsDocumentFields = self::getDocumentFields($documentType);
			foreach ($arCurrentValues as $key => $value)
			{
				if (mb_strpos($key, 'fields__') !== 0)
				{
					continue;
				}
				$key = mb_substr($key, 8);

				$property = $listsDocumentFields[$key];

				if (!$property || !$property['Editable'] || $key == 'IBLOCK_ID' || $key == 'CREATED_BY')
				{
					continue;
				}

				$arErrors = [];
				$arCurrentValues['fields'][$key] = $documentService->GetFieldInputValue(
					$documentType,
					$property,
					'fields__'.$key,
					$arCurrentValues,
					$arErrors
				);
			}
		}

		if (!array_key_exists('fields', $arCurrentValues))
		{
			$arCurrentValues['fields'] = [];
		}

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(
			__FILE__,
			[
				'documentType' => $paramDocumentType,
				'activityName' => $activityName,
				'workflowTemplate' => $arWorkflowTemplate,
				'workflowParameters' => $arWorkflowParameters,
				'workflowVariables' => $arWorkflowVariables,
				'currentValues' => $arCurrentValues,
				'formName' => $formName,
			]
		);

		$dialog->setMap(static::getPropertiesMap($paramDocumentType));

		$listsDocumentFields = $documentType ? self::getDocumentFields($documentType) : [];
		$listsDocumentFieldTypes = $documentType ? $documentService->GetDocumentFieldTypes($documentType) : [];

		$dialog->setRuntimeData([
			'documentFields' => $listsDocumentFields,
			'documentFieldsJs' => self::prepareFieldsToJs($listsDocumentFields),
			'documentService' => $documentService,
			'listsDocumentType' => $documentType,
			'javascriptFunctions' => $documentService->GetJSFunctionsForFields(
				$paramDocumentType,
				'objFieldsULDA',
				$listsDocumentFields,
				$listsDocumentFieldTypes
			),
		]);

		return $dialog;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		return [
			'ElementId' => [
				'Name' => Loc::getMessage('BPULDA_ELEMENT_ID'),
				'FieldName' => 'lists_element_id',
				'Type' => 'string',
				'Required' => true
			],
			'DocumentType' => self::getDocumentTypeField()
		];
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

		$realDocumentType = null;
		if (!empty($arCurrentValues['lists_document_type']))
		{
			$realDocumentType = explode('@', $arCurrentValues['lists_document_type']);
		}

		$arProperties = [
			'Fields' => [],
			'DocumentType' => $realDocumentType,
			'ElementId' => $arCurrentValues['lists_element_id'],
		];

		$documentService = CBPRuntime::getRuntime()->getDocumentService();
		$arDocumentFields = $realDocumentType ? $documentService->GetDocumentFields($realDocumentType) : [];

		$iblockId = $realDocumentType? mb_substr($realDocumentType[2], 7) : null;
		$listFields = $iblockId ? static::getVisibleFieldsList($iblockId) : [];

		foreach ($arCurrentValues as $fieldKey => $fieldValue)
		{
			if (mb_strpos($fieldKey, 'fields__') !== 0)
			{
				continue;
			}
			$fieldKey = mb_substr($fieldKey, 8);

			if (mb_substr($fieldKey, -5) === '_text')
			{
				$fieldKey = mb_substr($fieldKey, 0, -5);
				if (isset($arCurrentValues['fields__' . $fieldKey]))
				{
					continue;
				}
			}

			$property = $arDocumentFields[$fieldKey];

			if (!$property["Editable"] || $fieldKey == 'IBLOCK_ID' || $fieldKey == 'CREATED_BY' || !in_array($fieldKey, $listFields))
				continue;

			$arFieldErrors = [];
			$r = $documentService->GetFieldInputValue(
				$realDocumentType,
				$property,
				'fields__'.$fieldKey,
				$arCurrentValues,
				$arFieldErrors
			);

			if(is_array($arFieldErrors) && !empty($arFieldErrors))
			{
				$errors = array_merge($errors, $arFieldErrors);
			}

			if ($property["Required"] && ($r == null))
			{
				$errors[] = array(
					"code" => "emptyRequiredField",
					"message" => str_replace("#FIELD#", $property["Name"], Loc::getMessage("BPULDA_FIELD_REQUIED")),
				);
			}

			if (!CBPHelper::isEmptyValue($r))
			{
				$arProperties["Fields"][$fieldKey] = $r;
			}
		}

		if (!$errors)
		{
			$errors = self::validateProperties(
				$arProperties,
				new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)
			);
		}

		if ($errors)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $arProperties;

		return true;
	}

	public static function getAjaxResponse($request)
	{
		if (!empty($request['lists_document_type']) && !empty($request['form_name']))
		{
			$documentType = explode('@', $request['lists_document_type']);
			return [
				'fields' => self::prepareFieldsToJs(self::getDocumentFields($documentType)),
			];
		}

		return null;
	}

	private static function prepareFieldsToJs(array $fields)
	{
		foreach ($fields as $key => $field)
		{
			$field = \Bitrix\Bizproc\FieldType::normalizeProperty($field);
			$field['Id'] = $key;
			$field['FieldName'] = 'fields__'.$key;
			$fields[$key] = $field;
		}

		return array_values($fields);
	}

	private static function getDocumentFields(array $documentType)
	{
		$documentService = CBPRuntime::getRuntime()->getDocumentService();
		$fields = $documentService->getDocumentFields($documentType);

		$listFields = static::getVisibleFieldsList(mb_substr($documentType[2], 7));

		foreach ($fields as $fieldKey => $fieldValue)
		{
			if (
				!$fieldValue['Editable']
				|| $fieldKey == 'IBLOCK_ID'
				|| $fieldKey == 'CREATED_BY'
				||!in_array($fieldKey, $listFields)
			)
			{
				unset($fields[$fieldKey]);
			}
		}

		return $fields;
	}

	private static function getVisibleFieldsList($iblockId)
	{
		$list = new CList($iblockId);
		$listFields = $list->getFields();
		$result = [];
		foreach ($listFields as $key => $field)
		{
			if (!empty($field['CODE']) && (mb_strpos($key, 'PROPERTY_') === 0))
			{
				$key = 'PROPERTY_' . $field['CODE'];
			}
			$result[] = $key;
		}

		return $result;
	}

	private static function getDocumentTypeField()
	{
		$field = [
			'Name' => Loc::getMessage('BPULDA_DOC_TYPE'),
			'FieldName' => 'lists_document_type',
			'Type' => 'select',
			'Required' => true,
		];

		$options = [];

		$processesType = COption::getOptionString('lists', 'livefeed_iblock_type_id', 'bitrix_processes');
		$groups = [
			'lists' => ['name' => Loc::getMessage('BPULDA_DT_LISTS'), 'items' => []],
			$processesType => ['name' => Loc::getMessage('BPULDA_DT_PROCESSES'), 'items' => []],
			'lists_socnet' => ['name' => Loc::getMessage('BPULDA_DT_LISTS_SOCNET'), 'items' => []],
		];
		// other lists
		$typesResult = CLists::GetIBlockTypes();
		while ($typeRow = $typesResult->fetch())
		{
			$groups[$typeRow['IBLOCK_TYPE_ID']] = ['name' => $typeRow['NAME'], 'items' => []];
		}

		$iterator = CIBlock::GetList(['SORT' => 'ASC', 'NAME' => 'ASC'], [
			'ACTIVE' => 'Y',
			'TYPE' => array_keys($groups),
			'CHECK_PERMISSIONS' => 'N',
		]);

		while ($row = $iterator->fetch())
		{
			$value =
				'lists@'
				. ($row['IBLOCK_TYPE_ID'] === $processesType ? 'BizprocDocument' : 'Bitrix\Lists\BizprocDocumentLists')
				.'@iblock_'
				.$row['ID']
			;
			$name = '['.$row['LID'].'] '.$row['NAME'];

			$options[$value] = $name;
			$groups[$row['IBLOCK_TYPE_ID']]['items'][$value] = $name;
		}

		$field['Options'] = $options;
		$field['Settings'] = ['Groups' => $groups];

		return $field;
	}

	private function logDebug($id, $type)
	{
		if (!$this->workflow->isDebug())
		{
			return;
		}

		$debugInfo = $this->getDebugInfo(
			[
				'ElementId' => $id,
				'DocumentType' => implode('@', $type),
			],
			static::getPropertiesMap($this->getDocumentType()),
		);
		unset($debugInfo['ModifiedBy']);

		$this->writeDebugInfo($debugInfo);
	}

	private function logDebugFields(array $docType, array $values)
	{
		if (!$this->workflow->isDebug())
		{
			return;
		}

		$fields = array_filter(
			static::getDocumentFields($docType),
			static fn($fieldId) => array_key_exists($fieldId, $values),
			ARRAY_FILTER_USE_KEY
		);

		$debugInfo = $this->getDebugInfo($values, $fields);
		unset($debugInfo['ModifiedBy'], $debugInfo['ElementId'], $debugInfo['DocumentType']);
		$this->writeDebugInfo($debugInfo);
	}

	public function collectUsages()
	{
		$usages = [];
		$this->collectUsagesRecursive($this->arProperties, $usages);

		return $usages;
	}
}
