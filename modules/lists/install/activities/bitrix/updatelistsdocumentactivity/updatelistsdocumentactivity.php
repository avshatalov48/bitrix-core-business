<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPUpdateListsDocumentActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"DocumentType" => null,
			"Fields" => null,
			'ElementId' => null,
		);
	}

	public function Execute()
	{
		$documentType = $this->DocumentType;
		$elementId = $this->ElementId;

		//check for Multiple values
		if (is_array($elementId))
		{
			$elementId = array_shift($elementId);
		}

		$documentId = [$documentType[0], $documentType[1], $elementId];
		$fields = $this->Fields;

		$documentService = $this->workflow->GetService("DocumentService");

		$realDocumentType = null;
		try
		{
			$realDocumentType = $documentService->GetDocumentType($documentId);
		}
		catch (Exception $e) {}

		if (!$realDocumentType || $realDocumentType !== $documentType)
		{
			$this->WriteToTrackingService(GetMessage('BPULDA_ERROR_DT'), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$documentService->UpdateDocument($documentId, $fields);

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($testProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		try
		{
			CBPHelper::ParseDocumentId($testProperties['DocumentType']);
		}
		catch (Exception $e)
		{
			$errors[] = array("code" => "NotExist", "parameter" => "DocumentType", "message" => GetMessage("BPULDA_ERROR_DT"));
		}

		if (empty($testProperties['ElementId']))
		{
			$errors[] = array("code" => "NotExist", "parameter" => "ElementId", "message" => GetMessage("BPULDA_ERROR_ELEMENT_ID"));
		}

		if (empty($testProperties['Fields']))
		{
			$errors[] = array("code" => "NotExist", "parameter" => "Fields", "message" => GetMessage("BPULDA_ERROR_FIELDS"));
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	public static function GetPropertiesDialog($paramDocumentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null)
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

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = ['lists_element_id' => null, 'fields' => []];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (!empty($arCurrentActivity["Properties"]['Fields']) && is_array($arCurrentActivity["Properties"]["Fields"]))
			{
				$arCurrentValues['fields'] = $arCurrentActivity["Properties"]["Fields"];
			}
			if (!empty($arCurrentActivity["Properties"]['ElementId']))
			{
				$arCurrentValues['lists_element_id'] = $arCurrentActivity["Properties"]['ElementId'];
			}
			if (!empty($arCurrentActivity["Properties"]['DocumentType']))
			{
				$documentType = $arCurrentActivity["Properties"]['DocumentType'];
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

				if (!$property || !$property["Editable"] || $key == 'IBLOCK_ID' || $key == 'CREATED_BY')
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

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, array(
			'documentType' => $paramDocumentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName
		));

		$dialog->setMap([
			'ElementId' => [
				'Name' => GetMessage('BPULDA_ELEMENT_ID'),
				'FieldName' => 'lists_element_id',
				'Type' => 'string',
				'Required' => true
			],
			'DocumentType' => self::getDocumentTypeField()
		]);

		$listsDocumentFields = $documentType ? self::getDocumentFields($documentType) : [];
		$listsDocumentFieldTypes = $documentType ? $documentService->GetDocumentFieldTypes($documentType) : [];

		$dialog->setRuntimeData(array(
			"documentFields" => $listsDocumentFields,
			"documentFieldsJs" => self::prepareFieldsToJs($listsDocumentFields),
			"documentService" => $documentService,
			'listsDocumentType' => $documentType,
			"javascriptFunctions" => $documentService->GetJSFunctionsForFields(
				$paramDocumentType,
				"objFieldsULDA",
				$listsDocumentFields,
				$listsDocumentFieldTypes
			),
		));

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = [];

		$runtime = CBPRuntime::GetRuntime();

		$documentType = null;
		if (!empty($arCurrentValues['lists_document_type']))
		{
			$documentType = explode('@', $arCurrentValues['lists_document_type']);
		}

		$arProperties = array(
			"Fields" => [],
			'DocumentType' => $documentType,
			'ElementId' => $arCurrentValues['lists_element_id']
		);

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFields = $documentType ? $documentService->GetDocumentFields($documentType) : [];

		$iblockId = $documentType? mb_substr($documentType[2], 7) : null;
		$listFields = $iblockId? static::getVisibleFieldsList($iblockId) : [];

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
				if (isset($arCurrentValues['fields__'.$fieldKey]))
				{
					continue;
				}
			}

			$property = $arDocumentFields[$fieldKey];

			if (!$property["Editable"] || $fieldKey == 'IBLOCK_ID' || $fieldKey == 'CREATED_BY' || !in_array($fieldKey, $listFields))
				continue;

			$arFieldErrors = [];
			$r = $documentService->GetFieldInputValue(
				$documentType,
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
					"message" => str_replace("#FIELD#", $property["Name"], GetMessage("BPULDA_FIELD_REQUIED")),
				);
			}

			if ($r != null)
			{
				$arProperties["Fields"][$fieldKey] = $r;
			}
		}

		if (!$errors)
		{
			$errors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		}

		if ($errors)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}

	public static function getAjaxResponse($request)
	{
		if (!empty($request['lists_document_type']) && !empty($request['form_name']))
		{
			$documentType = explode('@', $request['lists_document_type']);
			return ['fields' => self::prepareFieldsToJs(
				self::getDocumentFields($documentType)
			)];
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
		$documentService = CBPRuntime::GetRuntime(true)->GetService("DocumentService");
		$fields = $documentService->GetDocumentFields($documentType);

		$listFields = static::getVisibleFieldsList(mb_substr($documentType[2], 7));

		foreach ($fields as $fieldKey => $fieldValue)
		{
			if (!$fieldValue["Editable"] || $fieldKey == 'IBLOCK_ID' || $fieldKey == 'CREATED_BY' ||!in_array($fieldKey, $listFields))
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
		$result = array();
		foreach ($listFields as $key => $field)
		{
			if (mb_strpos($key, 'PROPERTY_') === 0)
			{
				if (!empty($field['CODE']))
					$key = 'PROPERTY_'.$field['CODE'];
			}
			$result[] = $key;
		}
		return $result;
	}

	private static function getDocumentTypeField()
	{
		$field = [
			'Name' => GetMessage('BPULDA_DOC_TYPE'),
			'FieldName' => 'lists_document_type',
			'Type' => 'select',
			'Required' => true,
		];

		$options = $groups = [];

		$processesType = COption::getOptionString("lists", "livefeed_iblock_type_id", 'bitrix_processes');
		$groups = array(
			'lists' => ['name' => GetMessage('BPULDA_DT_LISTS'), 'items' => []],
			$processesType => ['name' => GetMessage('BPULDA_DT_PROCESSES'), 'items' => []],
			'lists_socnet' => ['name' => GetMessage('BPULDA_DT_LISTS_SOCNET'), 'items' => []],
		);
		// other lists
		$typesResult = CLists::GetIBlockTypes();
		while ($typeRow = $typesResult->fetch())
		{
			$groups[$typeRow['IBLOCK_TYPE_ID']] = ['name' => $typeRow['NAME'], 'items' => []];
		}

		$iterator = CIBlock::GetList(array('SORT'=>'ASC', 'NAME' => 'ASC'), array(
			'ACTIVE' => 'Y',
			'TYPE' => array_keys($groups),
			'CHECK_PERMISSIONS' => 'N',
		));

		while ($row = $iterator->fetch())
		{
			$value = 'lists@'.($row['IBLOCK_TYPE_ID'] === $processesType ? 'BizprocDocument' : 'Bitrix\Lists\BizprocDocumentLists').'@iblock_'.$row['ID'];
			$name = '['.$row['LID'].'] '.$row['NAME'];

			$options[$value] = $name;
			$groups[$row['IBLOCK_TYPE_ID']]['items'][$value] = $name;
		}

		$field['Options'] = $options;
		$field['Settings'] = ['Groups' => $groups];

		return $field;
	}
}
