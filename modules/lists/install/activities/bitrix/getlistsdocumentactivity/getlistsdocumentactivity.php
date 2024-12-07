<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPGetListsDocumentActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			"Title" => "",
			"DocumentType" => null,
			'ElementId' => null,
			"Fields" => null,
			"FieldsMap" => null,
		];
	}

	public function ReInitialize()
	{
		parent::ReInitialize();

		$fields = $this->Fields;
		if ($fields && is_array($fields))
		{
			foreach ($fields as $field)
			{
				$this->{$field} = null;
			}
		}
	}

	public function Execute()
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

		if (!$documentType || !$elementId)
		{
			$this->WriteToTrackingService(GetMessage('BPGLDA_ERROR_DT'), 0, CBPTrackingType::Error);

			return CBPActivityExecutionStatus::Closed;
		}

		$documentId = [$documentType[0], $documentType[1], $elementId];

		$documentService = $this->workflow->GetService("DocumentService");

		$this->logDebug($elementId, $documentType);

		$realDocumentType = null;
		$map = $this->FieldsMap;

		try
		{
			$realDocumentType = $documentService->GetDocumentType($documentId);
		}
		catch (Exception $e)
		{
		}

		if (!$realDocumentType || $realDocumentType !== $documentType)
		{
			$this->WriteToTrackingService(GetMessage('BPGLDA_ERROR_DT'), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$document = $documentService->GetDocument($documentId, $documentType);

		if (!$document || !is_array($map))
		{
			$this->WriteToTrackingService(GetMessage('BPGLDA_ERROR_EMPTY_DOCUMENT'), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$this->SetPropertiesTypes($map);
		$values = [];

		foreach ($map as $id => $field)
		{
			$values[$id] = $document[$id];
			$this->arProperties[$id] = $document[$id];
		}

		$this->logDebugFields($map, $values);

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($testProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		try
		{
			CBPHelper::ParseDocumentId($testProperties['DocumentType']);
		}
		catch (Exception $e)
		{
			$errors[] = [
				"code" => "NotExist",
				"parameter" => "DocumentType",
				"message" => GetMessage("BPGLDA_ERROR_DT"),
			];
		}

		if (empty($testProperties['ElementId']))
		{
			$errors[] = [
				"code" => "NotExist",
				"parameter" => "ElementId",
				"message" => GetMessage("BPGLDA_ERROR_ELEMENT_ID"),
			];
		}

		if (empty($testProperties['Fields']))
		{
			$errors[] = ["code" => "NotExist", "parameter" => "Fields", "message" => GetMessage("BPGLDA_ERROR_FIELDS")];
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	public static function GetPropertiesDialog($paramDocumentType, $activityName, $arWorkflowTemplate,
		$arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null)
	{
		if (!CModule::IncludeModule('lists'))
		{
			return null;
		}

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = [];
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);

			if (!empty($arCurrentActivity["Properties"]['ElementId']))
			{
				$arCurrentValues['lists_element_id'] = $arCurrentActivity["Properties"]['ElementId'];
			}
			if (!empty($arCurrentActivity["Properties"]['DocumentType']))
			{
				$arCurrentValues['lists_document_type'] = implode('@',
					$arCurrentActivity["Properties"]['DocumentType']);
			}
			if (!empty($arCurrentActivity["Properties"]['Fields']))
			{
				$arCurrentValues['fields'] = $arCurrentActivity["Properties"]['Fields'];
			}
		}

		$documentType = (!empty($arCurrentValues['lists_document_type']))
			? explode('@', $arCurrentValues['lists_document_type']) : null;

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $paramDocumentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
		]);

		$dialog->setMap(static::getPropertiesMap($paramDocumentType, ['listsDocumentType' => $documentType]));

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate,
		&$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		if (!CModule::IncludeModule('lists'))
		{
			return false;
		}

		$properties = [
			'DocumentType' => $arCurrentValues['lists_document_type']
				? explode('@', $arCurrentValues['lists_document_type']) : null,
			'ElementId' => $arCurrentValues['lists_element_id'],
			'Fields' => $arCurrentValues['fields'],
		];

		$errors = self::ValidateProperties($properties,
			new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));

		if ($errors)
		{
			return false;
		}

		$properties['FieldsMap'] = self::buildFieldsMap($properties['DocumentType'], $properties['Fields']);

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $properties;

		return true;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		$fieldList = isset($context['listsDocumentType']) ? self::getDocumentFieldsOptions($context['listsDocumentType']) : [];

		return [
			'ElementId' => [
				'Name' => GetMessage('BPGLDA_ELEMENT_ID'),
				'FieldName' => 'lists_element_id',
				'Type' => 'string',
				'Required' => true,
			],
			'DocumentType' => self::getDocumentTypeField(),
			'Fields' => [
				'Name' => GetMessage('BPGLDA_FIELDS_LABEL'),
				'FieldName' => 'fields',
				'Type' => 'select',
				'Required' => true,
				'Multiple' => true,
				'Options' => $fieldList,
			],
		];
	}

	public static function getAjaxResponse($request)
	{
		if (!empty($request['lists_document_type']) && !empty($request['form_name']))
		{
			$documentType = explode('@', $request['lists_document_type']);

			$options = [];
			foreach (self::getDocumentFieldsOptions($documentType) as $value => $text)
			{
				$options[] = ['value' => $value, 'text' => $text];
			}

			return ['options' => $options];
		}

		return null;
	}

	private static function getDocumentTypeField()
	{
		$field = [
			'Name' => GetMessage('BPGLDA_DOC_TYPE'),
			'FieldName' => 'lists_document_type',
			'Type' => 'select',
			'Required' => true,
		];

		$options = $groups = [];

		$processesType = COption::getOptionString("lists", "livefeed_iblock_type_id", 'bitrix_processes');
		$groups = [
			'lists' => ['name' => GetMessage('BPGLDA_DT_LISTS'), 'items' => []],
			$processesType => ['name' => GetMessage('BPGLDA_DT_PROCESSES'), 'items' => []],
			'lists_socnet' => ['name' => GetMessage('BPGLDA_DT_LISTS_SOCNET'), 'items' => []],
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
			$value = 'lists@' . ($row['IBLOCK_TYPE_ID'] === $processesType ? 'BizprocDocument'
					: 'Bitrix\Lists\BizprocDocumentLists') . '@iblock_' . $row['ID'];
			$name = '[' . $row['LID'] . '] ' . $row['NAME'];

			$options[$value] = $name;
			$groups[$row['IBLOCK_TYPE_ID']]['items'][$value] = $name;
		}

		$field['Options'] = $options;
		$field['Settings'] = ['Groups' => $groups];

		return $field;
	}

	private static function getDocumentFieldsOptions(array $documentType)
	{
		$documentService = CBPRuntime::GetRuntime(true)->GetService("DocumentService");
		$fields = $documentService->GetDocumentFields($documentType);

		$listFields = static::getVisibleFieldsList(mb_substr($documentType[2], 7));

		$options = [];

		foreach ($fields as $fieldKey => $fieldValue)
		{
			if (in_array($fieldKey, $listFields))
			{
				$options[$fieldKey] = $fieldValue['Name'];
			}
		}

		return $options;
	}

	private static function getVisibleFieldsList($iblockId)
	{
		$list = new CList($iblockId);
		$listFields = $list->getFields();
		$result = [];
		foreach ($listFields as $key => $field)
		{
			if (mb_strpos($key, 'PROPERTY_') === 0)
			{
				if (!empty($field['CODE']))
				{
					$key = 'PROPERTY_' . $field['CODE'];
				}
			}
			$result[] = $key;
			$result[] = $key . '_PRINTABLE';
			$result[] = $key . '_printable';
		}
		return $result;
	}

	private static function buildFieldsMap(array $documentType, $fields)
	{
		$documentService = CBPRuntime::GetRuntime()->GetService("DocumentService");
		$documentFields = $documentService->GetDocumentFields($documentType);

		$listFields = static::getVisibleFieldsList(mb_substr($documentType[2], 7));
		$map = [];
		foreach ($fields as $field)
		{
			if (in_array($field, $listFields) && isset($documentFields[$field]))
			{
				$map[$field] = \Bitrix\Bizproc\FieldType::normalizeProperty($documentFields[$field]);
			}
		}
		return $map;
	}

	private function logDebug($id, $type)
	{
		if (!$this->workflow->isDebug())
		{
			return;
		}

		$debugInfo = $this->getDebugInfo([
			'ElementId' => $id,
			'DocumentType' => implode('@', $type),
		]);

		unset($debugInfo['Fields']);

		$this->writeDebugInfo($debugInfo);
	}

	private function logDebugFields(array $fields, array $values)
	{
		if (!$this->workflow->isDebug())
		{
			return;
		}

		$debugInfo = $this->getDebugInfo($values, $fields);
		$this->writeDebugInfo($debugInfo);
	}
}
