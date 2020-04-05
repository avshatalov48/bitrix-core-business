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
			"ModifiedBy" => null
		);
	}

	public function Execute()
	{
		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$fieldValue = $this->FieldValue;

		if (!is_array($fieldValue) || count($fieldValue) <= 0)
			return CBPActivityExecutionStatus::Closed;

		$documentService = $this->workflow->GetService("DocumentService");

		if ($documentService->IsDocumentLocked($documentId, $this->GetWorkflowInstanceId()))
		{
			$this->workflow->AddEventHandler($this->name, $this);
			$documentService->SubscribeOnUnlockDocument($documentId, $this->GetWorkflowInstanceId(), $this->name);
			return CBPActivityExecutionStatus::Executing;
		}

		$documentFields = $documentService->GetDocumentFields($documentService->GetDocumentType($documentId));
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($documentFields);
		if ($documentFieldsAliasesMap)
		{
			$fixedFields = array();
			foreach ($fieldValue as $key => $value)
			{
				if (!isset($documentFields[$key]) && isset($documentFieldsAliasesMap[$key]))
				{
					$fixedFields[$documentFieldsAliasesMap[$key]] = $value;
					continue;
				}
				$fixedFields[$key] = $value;
			}
			$fieldValue = $fixedFields;
		}

		$documentService->UpdateDocument($documentId, $fieldValue, $this->ModifiedBy);

		return CBPActivityExecutionStatus::Closed;
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

			if (count($this->FieldValue) > 0)
				$documentService->UpdateDocument($documentId, $this->FieldValue);

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

				if (strpos($key, 'document_field_') !== 0)
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
			if (strlen($defaultFieldValue) <= 0)
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
			if (strpos($key, 'document_field_') !== 0)
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

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}
}