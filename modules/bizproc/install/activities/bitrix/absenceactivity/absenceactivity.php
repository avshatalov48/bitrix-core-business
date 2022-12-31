<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPAbsenceActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"AbsenceUser" => "",
			"AbsenceName" => "",
			"AbsenceDesrc" => "",
			"AbsenceState" => "",
			"AbsenceFinishState" => "",
			"AbsenceType" => "",
			"AbsenceFrom" => "",
			"AbsenceTo" => "",
			"AbsenceSiteId" => "",
		);
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("intranet"))
			return CBPActivityExecutionStatus::Closed;

		/* TODO: hotfix, should be fixed late. SiteId should be the ordinary parameter */
		$sss = $this->AbsenceSiteId;
		if (empty($sss))
			$sss = false;

		$absenceIblockId = COption::GetOptionInt("intranet", 'iblock_absence', 0, $sss);
		if ($absenceIblockId <= 0)
			return CBPActivityExecutionStatus::Closed;

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$documentService = $this->workflow->GetService("DocumentService");

		$arAbsenceUserTmp = $this->AbsenceUser;
		$arAbsenceUser = CBPHelper::ExtractUsers($arAbsenceUserTmp, $documentId, false);

		$arAbsenceTypes = array();
		$dbTypeRes = CIBlockPropertyEnum::GetList(
			array("SORT" => "ASC", "VALUE" => "ASC"),
			array('IBLOCK_ID' => $absenceIblockId, 'PROPERTY_ID' => 'ABSENCE_TYPE')
		);
		while ($arTypeValue = $dbTypeRes->GetNext())
			$arAbsenceTypes[$arTypeValue['XML_ID']] = $arTypeValue['ID'];

		$name = $this->AbsenceName;
		// if multiple or list element
		if (is_array($name))
		{
			$name = implode(', ', CBPHelper::makeArrayFlat($name));
		}

		$absenceDescription = $this->AbsenceDesrc;
		// if multiple or list element
		if (is_array($absenceDescription))
		{
			$absenceDescription = implode(', ', CBPHelper::makeArrayFlat($absenceDescription));
		}

		$activeFrom = $this->AbsenceFrom;
		$activeTo = $this->AbsenceTo;
		$enableTimeZone = false;

		//if $activeFrom and $activeTo without Time, turn off TimeZone
		if (mb_strlen($activeFrom) <= 10 && mb_strlen($activeTo) <= 10 && CTimeZone::Enabled())
		{
			CTimeZone::Disable();
			$enableTimeZone = true;
		}

		foreach ($arAbsenceUser as $absenceUser)
		{
			$arFields = Array(
				"ACTIVE" => "Y",
				"IBLOCK_ID" => $absenceIblockId,
				'ACTIVE_FROM' => $activeFrom,
				'ACTIVE_TO' => $activeTo,
				"NAME" => $name,
				"PREVIEW_TEXT" => $absenceDescription,
				"PREVIEW_TEXT_TYPE" => "text",
				"PROPERTY_VALUES" => array(
					"USER" => $absenceUser,
					"STATE" => $this->AbsenceState,
					"FINISH_STATE" => $this->AbsenceFinishState,
					"ABSENCE_TYPE" => $arAbsenceTypes[$this->AbsenceType],
				)
			);

			$el = new CIBlockElement();
			$el->Add($arFields);
		}

		if ($enableTimeZone)
			CTimeZone::Enable();

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!array_key_exists("AbsenceUser", $arTestProperties) || empty($arTestProperties["AbsenceUser"]))
			$arErrors[] = array("code" => "NotExist", "parameter" => "AbsenceUser", "message" => GetMessage("BPSNMA_EMPTY_ABSENCEUSER"));
		if (!array_key_exists("AbsenceName", $arTestProperties) || empty($arTestProperties["AbsenceName"]))
			$arErrors[] = array("code" => "NotExist", "parameter" => "AbsenceName", "message" => GetMessage("BPSNMA_EMPTY_ABSENCENAME"));
		if (!array_key_exists("AbsenceFrom", $arTestProperties) || $arTestProperties["AbsenceFrom"] == '')
			$arErrors[] = array("code" => "NotExist", "parameter" => "AbsenceFrom", "message" => GetMessage("BPSNMA_EMPTY_ABSENCEFROM"));
		if (!array_key_exists("AbsenceTo", $arTestProperties) || $arTestProperties["AbsenceTo"] == '')
			$arErrors[] = array("code" => "NotExist", "parameter" => "AbsenceTo", "message" => GetMessage("BPSNMA_EMPTY_ABSENCETO"));

		if (!$user || !$user->isAdmin())
		{
			$absenceIblockId = COption::GetOptionInt("intranet", 'iblock_absence', 0);
			$iblockPerm = CIBlock::GetPermission($absenceIblockId);
			if ($iblockPerm < "W")
			{
				$arErrors[] = array("code" => "perm", "message" => GetMessage("BPAA2_NO_PERMS"));
			}
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $form = null, $siteId = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"AbsenceUser" => "absence_user",
			"AbsenceName" => "absence_name",
			"AbsenceDesrc" => "absence_desrc",
			"AbsenceFrom" => "absence_from",
			"AbsenceTo" => "absence_to",
			"AbsenceState" => "absence_state",
			"AbsenceFinishState" => "absence_finish_state",
			"AbsenceType" => "absence_type",
		);

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				foreach ($arMap as $k => $v)
				{
					if (array_key_exists($k, $arCurrentActivity["Properties"]))
					{
						if ($k == "AbsenceUser")
							$arCurrentValues[$arMap[$k]] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"][$k], $arWorkflowTemplate, $documentType);
						else
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
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

		$absenceIblockId = COption::GetOptionInt("intranet", 'iblock_absence', 0, $siteId);
		$arAbsenceTypes = array();
		$dbTypeRes = CIBlockPropertyEnum::GetList(
			array("SORT" => "ASC", "VALUE" => "ASC"),
			array('IBLOCK_ID' => $absenceIblockId, 'PROPERTY_ID' => 'ABSENCE_TYPE')
		);
		while ($arTypeValue = $dbTypeRes->GetNext())
			$arAbsenceTypes[$arTypeValue['XML_ID']] = $arTypeValue['VALUE'];

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				"arAbsenceTypes" => $arAbsenceTypes,
				"absenceSiteId" => $siteId,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"absence_user" => "AbsenceUser",
			"absence_name" => "AbsenceName",
			"absence_desrc" => "AbsenceDesrc",
			"absence_from" => "AbsenceFrom",
			"absence_to" => "AbsenceTo",
			"absence_state" => "AbsenceState",
			"absence_finish_state" => "AbsenceFinishState",
			"absence_type" => "AbsenceType",
			"absence_site_id" => "AbsenceSiteId",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "absence_user")
				continue;
			$arProperties[$value] = $arCurrentValues[$key];
		}

		$arProperties["AbsenceUser"] = CBPHelper::UsersStringToArray($arCurrentValues["absence_user"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}
?>