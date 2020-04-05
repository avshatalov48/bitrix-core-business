<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPCalendarActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"CalendarUser" => "",
			"CalendarName" => "",
			"CalendarDesrc" => "",
			"CalendarFrom" => "",
			"CalendarTo" => "",
		);
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("intranet"))
			return CBPActivityExecutionStatus::Closed;

		$calendarIblockId = COption::GetOptionInt("intranet", 'iblock_calendar', 0);
		if ($calendarIblockId <= 0)
			return CBPActivityExecutionStatus::Closed;

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$arCalendarUser = CBPHelper::ExtractUsers($this->CalendarUser, $documentId);

		foreach ($arCalendarUser as $calendarUser)
		{
			$Params = array(
				'iblockId' => $calendarIblockId,
				'ownerType' => "USER",
				'ownerId' => $calendarUser,
				'cacheTime' => 0,
				'pageUrl' => false,
				'allowSuperpose' => false,
				'allowResMeeting' => false,
				'allowVideoMeeting' => false,
				'userIblockId' => $calendarIblockId
			);

			$EC = new CEventCalendar;
			$EC->Init($Params);

			$sectionId = $EC->GetSectionIDByOwnerId($calendarUser, 'USER', $calendarIblockId);
			if ($sectionId <= 0)
				$sectionId = CEventCalendar::CreateSectionForOwner($calendarUser, "USER", $calendarIblockId);

			$arGuestCalendars = $EC->GetCalendars(array(
				'sectionId' => $sectionId,
				'iblockId' => $calendarIblockId,
				'ownerType' => 'USER',
				'ownerId' => $calendarUser,
				'bOwner' => true,
				'forExport' => true,
				'bOnlyID' => true
			));

			$arParams = array(
				'iblockId' => $calendarIblockId,
				'ownerType' => "USER",
				'ownerId' => $calendarUser,
				'sectionId' => $sectionId,
				'bNew' => true,
				'name' => $this->CalendarName,
				'desc' => $this->CalendarDesrc,
				'dateFrom' => cutZeroTime($this->CalendarFrom),
				'dateTo' => cutZeroTime($this->CalendarTo),
				'isMeeting' => false,
				'prop' => array(
					'PERIOD_TYPE' => 'NONE',
					'ACCESSIBILITY' => 'busy', //'quest', 'free','absent'
					'IMPORTANCE' => 'normal', // 'high', 'low'
					'PRIVATE' => false,
				),
				'userId' => $calendarUser,
				'userIblockId' => $calendarIblockId,
				'location' => array('new' => '', 'change' => true)
			);

			if (count($arGuestCalendars) > 0)
				$arParams["calendarId"] = $arGuestCalendars[0];

			$EC->SaveEvent($arParams);
		}
		if (isset($EC))
			$EC->ClearCache($EC->cachePath.'events/'.$calendarIblockId.'/');

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!array_key_exists("CalendarUser", $arTestProperties) || count($arTestProperties["CalendarUser"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "CalendarUser", "message" => GetMessage("BPSNMA_EMPTY_CALENDARUSER"));
		if (!array_key_exists("CalendarName", $arTestProperties) || count($arTestProperties["CalendarName"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "CalendarName", "message" => GetMessage("BPSNMA_EMPTY_CALENDARNAME"));
		if (!array_key_exists("CalendarFrom", $arTestProperties) || strlen($arTestProperties["CalendarFrom"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "CalendarFrom", "message" => GetMessage("BPSNMA_EMPTY_CALENDARFROM"));
		if (!array_key_exists("CalendarTo", $arTestProperties) || strlen($arTestProperties["CalendarTo"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "CalendarTo", "message" => GetMessage("BPSNMA_EMPTY_CALENDARTO"));

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"CalendarUser" => "calendar_user",
			"CalendarName" => "calendar_name",
			"CalendarDesrc" => "calendar_desrc",
			"CalendarFrom" => "calendar_from",
			"CalendarTo" => "calendar_to",
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
						if ($k == "CalendarUser")
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

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"calendar_user" => "CalendarUser",
			"calendar_name" => "CalendarName",
			"calendar_desrc" => "CalendarDesrc",
			"calendar_from" => "CalendarFrom",
			"calendar_to" => "CalendarTo",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "calendar_user")
				continue;
			$arProperties[$value] = $arCurrentValues[$key];
		}

		$arProperties["CalendarUser"] = CBPHelper::UsersStringToArray($arCurrentValues["calendar_user"], $documentType, $arErrors);
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