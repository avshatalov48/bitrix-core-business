<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPGetUserActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"UserType" => null,
			"UserParameter" => null,
			"ReserveUserParameter" => null,
			"MaxLevel" => null,
			"GetUser" => null,
			"SkipAbsent" => "",
			"SkipTimeman" => "",
		);

		$this->SetPropertiesTypes(array(
			'GetUser' => array(
				'Type' => 'user',
				'Multiple' => true
			)
		));
	}

	private function GetUsersList($arUsersList, $bSkipAbsent = true, $bSkipTimeman = false)
	{
		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		if ($bSkipAbsent && !CModule::IncludeModule("intranet"))
			$bSkipAbsent = false;
		if ($bSkipTimeman && !CModule::IncludeModule("timeman"))
			$bSkipTimeman = false;

		$arUsers = CBPHelper::ExtractUsers($arUsersList, $documentId, false);
		if ($bSkipAbsent || $bSkipTimeman)
		{
			$arKeys = array_keys($arUsers);
			foreach ($arKeys as $key)
			{
				if ($bSkipAbsent)
				{
					if (CIntranetUtils::IsUserAbsent($arUsers[$key]))
					{
						unset($arUsers[$key]);
						continue;
					}
				}
				if ($bSkipTimeman)
				{
					$tmUser = new CTimeManUser($arUsers[$key]);
					if($tmUser->State() == 'CLOSED')
					{
						unset($arUsers[$key]);
					}
				}

			}
		}

		return array_values($arUsers);
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("intranet"))
		{
			$this->UserType = "random";
			$this->SkipAbsent = "N";
		}
		if (!CModule::IncludeModule('timeman'))
		{
			$this->SkipTimeman = 'N';
		}

		$skipAbsent = ($this->SkipAbsent != 'N');
		$skipTimeman = ($this->SkipTimeman == 'Y');

		$this->GetUser = null;

		$arUsers = array();
		if ($this->UserType == "boss")
		{
			$arUsers = $this->GetUsersList($this->UserParameter, false);
			if (count($arUsers) <= 0)
			{
				$this->GetUser = null;
				return CBPActivityExecutionStatus::Closed;
			}

			$userId = $arUsers[0];

			$arUserDepartmentId = null;
			$dbUser = CUser::GetByID($userId);
			if ($arUser = $dbUser->Fetch())
			{
				if (isset($arUser["UF_DEPARTMENT"]))
				{
					if (!is_array($arUser["UF_DEPARTMENT"]))
						$arUser["UF_DEPARTMENT"] = array($arUser["UF_DEPARTMENT"]);

					foreach ($arUser["UF_DEPARTMENT"] as $v)
						$arUserDepartmentId[] = $v;
				}
			}

			$arUserDepartments = array();
			$departmentIBlockId = COption::GetOptionInt('intranet', 'iblock_structure');
			foreach ($arUserDepartmentId as $departmentId)
			{
				$ar = array();
				$dbPath = CIBlockSection::GetNavChain($departmentIBlockId, $departmentId);
				while ($arPath = $dbPath->GetNext())
					$ar[] = $arPath["ID"];

				$arUserDepartments[] = array_reverse($ar);
			}

			$arBoss = array();
			foreach ($arUserDepartments as $arV)
			{
				$maxLevel = $this->MaxLevel;
				foreach ($arV as $level => $deptId)
				{
					if ($maxLevel > 0 && $level + 1 > $maxLevel)
						break;

					$dbRes = CIBlockSection::GetList(
						array(),
						array(
							'IBLOCK_ID' => $departmentIBlockId,
							'ID' => $deptId,
						),
						false,
						array('ID', 'UF_HEAD')
					);
					while ($arRes = $dbRes->Fetch())
					{
						if (($arRes["UF_HEAD"] == $userId) || (intval($arRes["UF_HEAD"]) <= 0)
							|| ($skipAbsent && CIntranetUtils::IsUserAbsent($arRes["UF_HEAD"])))
						{
							$maxLevel++;
							continue;
						}
						if (!in_array($arRes["UF_HEAD"], $arBoss))
							$arBoss[] = $arRes["UF_HEAD"];
					}
				}
			}

			$ar = array();
			foreach ($arBoss as $v)
				$ar[] = "user_".$v;

			if (count($ar) == 0)
				$ar = null;
			elseif (count($ar) == 1)
				$ar = $ar[0];

			if ($ar !== null)
			{
				$this->GetUser = $ar;
				return CBPActivityExecutionStatus::Closed;
			}
		}
		if ($this->UserType == "random")
		{
			$arUsers = $this->GetUsersList($this->UserParameter, $skipAbsent, $skipTimeman);

			if (count($arUsers) > 0)
			{
				$rnd = mt_rand(0, count($arUsers) - 1);
				$this->GetUser = "user_".$arUsers[$rnd];

				return CBPActivityExecutionStatus::Closed;
			}
		}

		$arReserveUsers = $this->GetUsersList($this->ReserveUserParameter, $skipAbsent, $skipTimeman);
		if (count($arReserveUsers) > 0)
		{
			if ($this->UserType == 'random')
				$this->GetUser = 'user_'.$arReserveUsers[0];
			else
			{
				foreach($arReserveUsers as &$user)
					$user = 'user_'.$user;
				unset($user);
				$this->GetUser = $arReserveUsers;
			}
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array("user_type" => "", "user_parameter" => "", "reserve_user_parameter" => "", "max_level" => 1, "skip_absent" => "Y", "skip_timeman" => "N");

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				$arCurrentValues["user_type"] = $arCurrentActivity["Properties"]["UserType"];
				$arCurrentValues["max_level"] = $arCurrentActivity["Properties"]["MaxLevel"];
				$arCurrentValues["user_parameter"] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"]["UserParameter"], $arWorkflowTemplate, $documentType);
				$arCurrentValues["reserve_user_parameter"] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"]["ReserveUserParameter"], $arWorkflowTemplate, $documentType);
				$arCurrentValues["skip_absent"] = (array_key_exists("SkipAbsent", $arCurrentActivity["Properties"]) ? $arCurrentActivity["Properties"]["SkipAbsent"] : (($arCurrentActivity["Properties"]["UserType"] == "boss") ? "N" : "Y"));
				$arCurrentValues["skip_timeman"] = (array_key_exists("SkipTimeman", $arCurrentActivity["Properties"])) ? $arCurrentActivity["Properties"]["SkipTimeman"] : 'N';
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
		$arProperties = array();

		if (!isset($arCurrentValues["user_type"]) || !in_array($arCurrentValues["user_type"], array("boss", "random", "sequent")))
			$arCurrentValues["user_type"] = "random";
		$arProperties["UserType"] = $arCurrentValues["user_type"];

		if (!isset($arCurrentValues["max_level"]) || $arCurrentValues["max_level"] < 1 || $arCurrentValues["max_level"] > 10)
			$arCurrentValues["max_level"] = 1;
		$arProperties["MaxLevel"] = $arCurrentValues["max_level"];

		$arProperties["UserParameter"] = CBPHelper::UsersStringToArray($arCurrentValues["user_parameter"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arProperties["ReserveUserParameter"] = CBPHelper::UsersStringToArray($arCurrentValues["reserve_user_parameter"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		if (!isset($arCurrentValues["skip_absent"]) || !in_array($arCurrentValues["skip_absent"], array("Y", "N")))
			$arCurrentValues["skip_absent"] = "Y";
		$arProperties["SkipAbsent"] = $arCurrentValues["skip_absent"];
		
		if (!isset($arCurrentValues["skip_timeman"]) || !in_array($arCurrentValues["skip_timeman"], array("Y", "N")))
			$arCurrentValues["skip_timeman"] = "N";
		$arProperties["SkipTimeman"] = $arCurrentValues["skip_timeman"];

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!array_key_exists("UserParameter", $arTestProperties))
		{
			$bUsersFieldEmpty = true;
		}
		else
		{
			if (!is_array($arTestProperties["UserParameter"]))
				$arTestProperties["UserParameter"] = array($arTestProperties["UserParameter"]);

			$bUsersFieldEmpty = true;
			foreach ($arTestProperties["UserParameter"] as $userId)
			{
				if (!is_array($userId) && (strlen(trim($userId)) > 0) || is_array($userId) && (count($userId) > 0))
				{
					$bUsersFieldEmpty = false;
					break;
				}
			}
		}

		if ($bUsersFieldEmpty)
			$arErrors[] = array("code" => "NotExist", "parameter" => "UserParameter", "message" => GetMessage("BPARGUA_ACT_PROP_EMPTY1"));


		if (!array_key_exists("ReserveUserParameter", $arTestProperties))
		{
			$bUsersFieldEmpty = true;
		}
		else
		{
			if (!is_array($arTestProperties["ReserveUserParameter"]))
				$arTestProperties["ReserveUserParameter"] = array($arTestProperties["ReserveUserParameter"]);

			$bUsersFieldEmpty = true;
			foreach ($arTestProperties["ReserveUserParameter"] as $userId)
			{
				if (!is_array($userId) && (strlen(trim($userId)) > 0) || is_array($userId) && (count($userId) > 0))
				{
					$bUsersFieldEmpty = false;
					break;
				}
			}
		}

		if ($bUsersFieldEmpty)
			$arErrors[] = array("code" => "NotExist", "parameter" => "ReserveUserParameter", "message" => GetMessage("BPARGUA_ACT_PROP_EMPTY2"));

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}
}
?>