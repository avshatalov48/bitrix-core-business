<?
use Bitrix\Bizproc\FieldType;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSetFieldActivity
	extends CBPActivity
	implements IBPActivityExternalEventListener
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"FieldValue" => null,
			"MergeMultipleFields" => 'N',
			"ModifiedBy" => null
		);
	}

	public function Execute()
	{
		$documentId = $this->GetDocumentId();
		$documentType = $this->GetDocumentType();

		$fieldValue = $this->FieldValue;

		if (!is_array($fieldValue) || count($fieldValue) <= 0)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$documentService = $this->workflow->GetService("DocumentService");

		if ($documentService->IsDocumentLocked($documentId, $this->GetWorkflowInstanceId()))
		{
			$this->workflow->AddEventHandler($this->name, $this);
			$documentService->SubscribeOnUnlockDocument($documentId, $this->GetWorkflowInstanceId(), $this->name);
			return CBPActivityExecutionStatus::Executing;
		}

		$resultFields = $this->prepareFieldsValues($documentId, $documentType, $fieldValue);

		try
		{
			$documentService->UpdateDocument($documentId, $resultFields, $this->ModifiedBy);
		}
		catch (Exception $e)
		{
			$this->WriteToTrackingService($e->getMessage(), 0, CBPTrackingType::Error);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	protected function prepareFieldsValues(array $documentId, array $documentType, array $fields, $mergeValues = null): array
	{
		if (!is_bool($mergeValues))
		{
			$mergeValues = ($this->MergeMultipleFields === 'Y');
		}

		$documentService = $this->workflow->GetService("DocumentService");

		$documentFields = $documentService->GetDocumentFields($documentType);
		$documentValues = $documentService->GetDocument($documentId, $documentType);
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($documentFields);

		$resultFields = [];
		foreach ($fields as $key => $value)
		{
			if (!isset($documentFields[$key]) && isset($documentFieldsAliasesMap[$key]))
			{
				$key = $documentFieldsAliasesMap[$key];
			}

			if (($property = $documentFields[$key]) && ($value || $mergeValues))
			{
				$fieldTypeObject = $documentService->getFieldTypeObject($documentType, $property);
				if ($fieldTypeObject)
				{
					$fieldTypeObject->setDocumentId($documentId);

					if ($mergeValues && $fieldTypeObject->isMultiple())
					{
						$baseValue = $documentValues[$key] ?? [];
						$value = $fieldTypeObject->mergeValue($baseValue, $value);
					}

					if ($value)
					{
						$value = $fieldTypeObject->externalizeValue('Document', $value);
					}
				}
			}

			$resultFields[$key] = $value;
		}

		return $resultFields;
	}

	public function OnExternalEvent($arEventParameters = array())
	{
		if ($this->executionStatus != CBPActivityExecutionStatus::Closed)
		{
			$rootActivity = $this->GetRootActivity();
			$documentId = $rootActivity->GetDocumentId();

			$documentService = $this->workflow->GetService("DocumentService");
			if ($documentService->IsDocumentLocked($documentId, $this->GetWorkflowInstanceId()))
				return;

			$fieldValue = $this->FieldValue;
			if (is_array($fieldValue) && count($fieldValue) > 0)
			{
				$resultFields = $this->prepareFieldsValues($documentId, $this->GetDocumentType(), $fieldValue);
				$documentService->UpdateDocument($documentId, $resultFields);
			}

			$documentService->UnsubscribeOnUnlockDocument($documentId, $this->GetWorkflowInstanceId(), $this->name);
			$this->workflow->RemoveEventHandler($this->name, $this);
			$this->workflow->CloseActivity($this);
		}
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!is_array($arTestProperties)
			|| !array_key_exists("FieldValue", $arTestProperties)
			|| !is_array($arTestProperties["FieldValue"])
			|| count($arTestProperties["FieldValue"]) <= 0)
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "FieldValue", "message" => GetMessage("BPSFA_EMPTY_FIELDS"));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null)
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFieldsTmp = $documentService->GetDocumentFields($documentType);
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($arDocumentFieldsTmp);

		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);
		unset($arFieldTypes[FieldType::INTERNALSELECT]);
		$modifiedBy = null;

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]) 
				&& array_key_exists("FieldValue", $arCurrentActivity["Properties"])
				&& is_array($arCurrentActivity["Properties"]["FieldValue"]))
			{
				foreach ($arCurrentActivity["Properties"]["FieldValue"] as $k => $v)
				{
					if (!isset($arDocumentFieldsTmp[$k]) && isset($documentFieldsAliasesMap[$k]))
						$k = $documentFieldsAliasesMap[$k];

					$arCurrentValues[$k] = $v;
				}
			}

			if ($arCurrentActivity["Properties"]['ModifiedBy'])
			{
				$modifiedBy = $arCurrentActivity["Properties"]['ModifiedBy'];
			}
			if ($arCurrentActivity["Properties"]['MergeMultipleFields'])
			{
				$arCurrentValues['merge_multiple_fields'] = $arCurrentActivity["Properties"]['MergeMultipleFields'];
			}
		}
		else
		{
			$arErrors = array();
			foreach ($arCurrentValues as $key => $fieldKey)
			{
				if ($key === 'modified_by')
				{
					$modifiedBy = CBPHelper::UsersStringToArray($fieldKey, $documentType, $arErrors);
					continue;
				}

				if (mb_strpos($key, 'document_field_') !== 0)
					continue;

				if (!isset($arDocumentFieldsTmp[$fieldKey]) || !$arDocumentFieldsTmp[$fieldKey]["Editable"])
					continue;

				$r = $documentService->GetFieldInputValue(
					$documentType,
					$arDocumentFieldsTmp[$fieldKey],
					$fieldKey,
					$arCurrentValues,
					$arErrors
				);

				$arCurrentValues[$fieldKey] = $r;
			}
		}

		$arDocumentFields = array();
		$defaultFieldValue = "";
		foreach ($arDocumentFieldsTmp as $key => $value)
		{
			if (!$value["Editable"])
				continue;

			$arDocumentFields[$key] = $value;
			if ($defaultFieldValue == '')
				$defaultFieldValue = $key;
		}

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, array(
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName
		));

		$dialog->setMap([
			'MergeMultipleFields' => [
				'Name' => GetMessage('BPSFA_MERGE_MULTIPLE'),
				'FieldName' => 'merge_multiple_fields',
				'Type' => 'bool',
			]
		]);

		$dialog->setRuntimeData(array(
			"arCurrentValues" => $arCurrentValues,
			"arDocumentFields" => $arDocumentFields,
			"formName" => $formName,
			"defaultFieldValue" => $defaultFieldValue,
			"arFieldTypes" => $arFieldTypes,
			"javascriptFunctions" => $documentService->GetJSFunctionsForFields(
				$documentType,
				"objFields",
				$arDocumentFields,
				$arFieldTypes
			),
			"canSetModifiedBy" => $documentService->isFeatureEnabled($documentType, CBPDocumentService::FEATURE_SET_MODIFIED_BY),
			"modifiedBy" => $modifiedBy,
			"modifiedByString" => CBPHelper::UsersArrayToString($modifiedBy, $arWorkflowTemplate, $documentType),
			"documentType" => $documentType,
			"popupWindow" => &$popupWindow,
		));

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = [];
		$runtime = CBPRuntime::GetRuntime();
		$properties = ["FieldValue" => []];

		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService("DocumentService");

		$arNewFieldsMap = [];
		if (array_key_exists("new_field_name", $arCurrentValues) && is_array($arCurrentValues["new_field_name"]))
		{
			$arNewFieldKeys = array_keys($arCurrentValues["new_field_name"]);
			foreach ($arNewFieldKeys as $k)
			{
				$code = trim($arCurrentValues["new_field_code"][$k]);

				$arFieldsTmp = array(
					"name" => $arCurrentValues["new_field_name"][$k],
					"code" => $code,
					"type" => $arCurrentValues["new_field_type"][$k],
					"multiple" => $arCurrentValues["new_field_mult"][$k],
					"required" => $arCurrentValues["new_field_req"][$k],
					"options" => $arCurrentValues["new_field_options"][$k],
				);

				$newCode = $documentService->AddDocumentField($documentType, $arFieldsTmp);
				$property = FieldType::normalizeProperty($arFieldsTmp);
				$property['Code'] = $newCode;
				$property['Name'] = $arFieldsTmp['name'];
				$arNewFieldsMap[$code] = $property;
			}
		}

		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		foreach ($arCurrentValues as $key => $value)
		{
			if (mb_strpos($key, 'document_field_') !== 0)
				continue;

			$fieldKey = array_key_exists($value, $arNewFieldsMap) ? $arNewFieldsMap[$value]['Code'] : $value;
			if (!isset($arDocumentFields[$fieldKey]) || !$arDocumentFields[$fieldKey]["Editable"])
						continue;

			$property = array_key_exists($value, $arNewFieldsMap) ? $arNewFieldsMap[$value] : $arDocumentFields[$fieldKey];

			$r = $documentService->GetFieldInputValue(
				$documentType,
				$property,
				$value,
				$arCurrentValues,
				$errors
			);

			if (count($errors) > 0)
			{
				return false;
			}

			if (CBPHelper::getBool($property['Required']) && CBPHelper::isEmptyValue($r))
			{
				$errors[] = array(
					"code" => "NotExist",
					"parameter" => $fieldKey,
					"message" => GetMessage("BPSFA_ARGUMENT_NULL", array('#PARAM#' => $property['Name']))
				);
				return false;
			}

			$properties["FieldValue"][$fieldKey] = $r;
		}

		if (isset($arCurrentValues['modified_by']))
		{
			$properties['ModifiedBy'] = CBPHelper::UsersStringToArray(
				$arCurrentValues["modified_by"],
				$documentType,
				$errors
			);

			if (count($errors) > 0)
			{
				return false;
			}
		}

		if (isset($arCurrentValues['merge_multiple_fields']))
		{
			$properties['MergeMultipleFields'] = $arCurrentValues['merge_multiple_fields'] === 'Y' ? 'Y' : 'N';
		}

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}

	public function collectUsages()
	{
		$usages = parent::collectUsages();
		if (is_array($this->arProperties["FieldValue"]))
		{
			foreach (array_keys($this->arProperties["FieldValue"]) as $v)
			{
				$usages[] = $this->getObjectSourceType('Document', $v);
			}
		}
		return $usages;
	}
}