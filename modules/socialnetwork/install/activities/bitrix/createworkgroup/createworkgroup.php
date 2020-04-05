<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPCreateWorkGroup
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"GroupName" => "",
			"OwnerId" => "",
			'Users' => "",
			"GroupSite" => "",
			"GroupId" => null,
			"Fields" => null
		);
	}

	public function Execute()
	{
		global $USER_FIELD_MANAGER;

		if (!CModule::IncludeModule("socialnetwork"))
			return CBPActivityExecutionStatus::Closed;

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$ownerId = CBPHelper::ExtractUsers($this->OwnerId, $documentId, true);
		$users = array_unique(CBPHelper::ExtractUsers($this->Users, $documentId, false));

		$dbSubjects = CSocNetGroupSubject::GetList(
			array("SORT"=>"ASC", "NAME" => "ASC"),
			array("SITE_ID" => SITE_ID),
			false,
			false,
			array("ID")
		);
		$row = $dbSubjects->fetch();
		if (!$row)
		{
			$this->WriteToTrackingService(GetMessage("BPCWG_ERROR_SUBJECT_ID"));
			return CBPActivityExecutionStatus::Closed;
		}

		$subjectId = $row['ID'];
		unset($dbSubjects, $row);

		$options = array(
			"SITE_ID" => $this->GroupSite ? $this->GroupSite : SITE_ID,
			"NAME" => $this->GroupName,
			"VISIBLE" => "Y",
			"OPENED" => "N",
			"CLOSED" => "N",
			"SUBJECT_ID" => $subjectId,
			"INITIATE_PERMS" => SONET_ROLES_OWNER,
			"SPAM_PERMS" => SONET_ROLES_USER,
		);

		$userFieldsList = $USER_FIELD_MANAGER->getUserFields("SONET_GROUP", 0, LANGUAGE_ID);
		foreach($userFieldsList as $field => $arUserField)
		{
			if (array_key_exists($field, $this->Fields))
			{
				$options[$field] = $this->Fields[$field];
			}
		}

		$groupId = CSocNetGroup::CreateGroup($ownerId, $options);
		if (!$groupId)
		{
			$this->WriteToTrackingService(GetMessage("BPCWG_ERROR_CREATE_GROUP"));
			return CBPActivityExecutionStatus::Closed;
		}

		$features = array();
		$allowedFeatures = CSocNetAllowed::GetAllowedFeatures();
		foreach ($allowedFeatures as $feature => $arFeature)
		{
			if (is_array($arFeature["allowed"]) && in_array(SONET_ENTITY_GROUP, $arFeature["allowed"]))
				$features[] = $feature;
		}

		foreach ($features as $feature)
		{
			CSocNetFeatures::SetFeature(
				SONET_ENTITY_GROUP,
				$groupId,
				$feature,
				true
			);
		}

		$this->GroupId = $groupId;

		foreach ($users AS $user)
		{
			if ($user == $ownerId)
				continue;
			CSocNetUserToGroup::Add(
				array(
					"USER_ID" => $user,
					"GROUP_ID" => $groupId,
					"ROLE" => SONET_ROLES_USER,
					"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
					"INITIATED_BY_USER_ID" => $ownerId,
					"MESSAGE" => false,
				)
			);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();
		if (!array_key_exists("GroupName", $arTestProperties) || strlen($arTestProperties["GroupName"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "GroupName", "message" => GetMessage("BPCWG_EMPTY_GROUP_NAME"));
		if (!array_key_exists("OwnerId", $arTestProperties) || count($arTestProperties["OwnerId"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "OwnerId", "message" => GetMessage("BPCWG_EMPTY_OWNER"));
		if (!array_key_exists("Users", $arTestProperties) || count($arTestProperties["Users"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "Users", "message" => GetMessage("BPCWG_EMPTY_USERS"));

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null, $currentSiteId = null)
	{
		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"GroupName" => "group_name",
			"OwnerId" => "owner_id",
			"Users" => 'users',
			"GroupSite" => "group_site",
			"Fields" => '',
		);

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);

			if (is_array($arCurrentActivity["Properties"]))
			{
				foreach ($arMap as $k => $v)
				{
					if (array_key_exists($k, $arCurrentActivity["Properties"]))
					{
						if ($k == "OwnerId" || $k == "Users")
						{
							$arCurrentValues[$arMap[$k]] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"][$k], $arWorkflowTemplate, $documentType);
						}
						elseif (
							$k == "Fields"
							&& is_array($arCurrentActivity["Properties"]["Fields"])
						)
						{
							foreach($arCurrentActivity["Properties"]["Fields"] as $field => $value)
							{
								$arCurrentValues[$field] = $value;
							}
						}
						else
						{
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
						}
					}
					else
					{
						$arCurrentValues[$arMap[$k]] = "";
					}
				}
			}
			else
			{
				foreach ($arMap as $k => $v)
					$arCurrentValues[$arMap[$k]] = "";
			}
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				"arDocumentFields" => self::__GetFields(),
				"currentSiteId" => $currentSiteId
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		global $USER_FIELD_MANAGER;

		$arErrors = array();

		$arMap = array(
			"group_name" => "GroupName",
			"owner_id" => "OwnerId",
			"users" => "Users"
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "owner_id" || $key == "users")
				continue;
			$arProperties[$value] = $arCurrentValues[$key];
		}

		if (strlen($arProperties["GroupSite"]) <= 0)
			$arProperties["GroupSite"] = $arCurrentValues["group_site_x"];

		$userFieldsList = $USER_FIELD_MANAGER->getUserFields("SONET_GROUP", 0, LANGUAGE_ID);
		foreach ($userFieldsList as $field)
		{
			$r = $arCurrentValues[$field["FIELD_NAME"]];

			if($field["MANDATORY"] == "Y")
			{
				if(($field["MULTIPLE"] == "Y" && (!$r || is_array($r) && count($r) <= 0)) || ($field["MULTIPLE"] == "N" && empty($r)))
				{
					$arErrors[] = array(
						"code" => "emptyRequiredField",
						"message" => str_replace("#FIELD#", $field["EDIT_FORM_LABEL"], GetMessage("BPCWG_FIELD_REQUIED")),
					);
				}
			}

			$arProperties["Fields"][$field["FIELD_NAME"]] = $r;
		}

		$arProperties["OwnerId"] = CBPHelper::UsersStringToArray($arCurrentValues["owner_id"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arProperties["Users"] = CBPHelper::UsersStringToArray($arCurrentValues["users"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}

	private static function __GetFields()
	{
		global $USER_FIELD_MANAGER;

		$userFields = $USER_FIELD_MANAGER->getUserFields("SONET_GROUP", 0, LANGUAGE_ID);
		$fieldsList = array();

		foreach($userFields as $field)
		{
			$fieldsList[$field["FIELD_NAME"]] = array(
				"Name" => $field["EDIT_FORM_LABEL"],
				"Type" => $field["USER_TYPE_ID"],
				"Filterable" => true,
				"Editable" => true,
				"Required" => ($field["MANDATORY"] == "Y"),
				"Multiple" => ($field["MULTIPLE"] == "Y"),
				"BaseType" => $field["USER_TYPE_ID"],
				"UserField" => $field
			);
		}

		return $fieldsList;
	}
}
?>