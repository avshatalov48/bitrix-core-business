<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPGetUserActivity extends CBPActivity
{
	private const USER_TYPE_RANDOM = 'random';
	private const USER_TYPE_BOSS = 'boss';
	private const USER_TYPE_SEQUENT = 'sequent';

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			"Title" => "",
			"UserType" => null,
			"UserParameter" => null,
			"ReserveUserParameter" => null,
			"MaxLevel" => null,
			"GetUser" => null,
			"SkipAbsent" => "",
			"SkipAbsentReserve" => "Y",
			"SkipTimeman" => "",
			"SkipTimemanReserve" => "N",
		];

		$this->SetPropertiesTypes([
			'GetUser' => [
				'Type' => 'user',
				'Multiple' => true
			]
		]);
	}

	private function GetUsersList($arUsersList, $bSkipAbsent = true, $bSkipTimeman = false)
	{
		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		if ($bSkipAbsent && !CModule::IncludeModule("intranet"))
		{
			$bSkipAbsent = false;
		}
		if ($bSkipTimeman && !CModule::IncludeModule("timeman"))
		{
			$bSkipTimeman = false;
		}

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
					if($tmUser->State() === 'CLOSED')
					{
						unset($arUsers[$key]);
					}
				}
			}
		}

		return array_values($arUsers);
	}

	protected function getActiveUsers(array $users) : array
	{
		if (empty($users))
		{
			return [];
		}

		$dbUsers = CUser::GetList(
			'id', 'asc',
			[
				'ID' => implode(' | ', $users),
				'ACTIVE' => 'Y'
			]
		);

		$activeUsers = [];
		while($user = $dbUsers->Fetch())
		{
			$activeUsers[] = $user["ID"];
		}

		// unset non-active users, keep the original order
		foreach (array_diff($users, $activeUsers) as $key => $value)
		{
			unset($users[$key]);
		}

		return $users;
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("intranet"))
		{
			$this->UserType = self::USER_TYPE_RANDOM;
			$this->SkipAbsent = "N";
		}
		if (!CModule::IncludeModule('timeman'))
		{
			$this->SkipTimeman = 'N';
		}

		$skipAbsent = ($this->SkipAbsent != 'N');
		$skipTimeman = ($this->SkipTimeman == 'Y');

		$this->GetUser = null;
		$user = false;

		if ($this->UserType === self::USER_TYPE_BOSS)
		{
			$user = $this->getBossUser($skipAbsent);
		}
		elseif ($this->UserType === self::USER_TYPE_RANDOM)
		{
			$user = $this->getRandomUser($skipAbsent, $skipTimeman);
		}
		elseif ($this->UserType === self::USER_TYPE_SEQUENT)
		{
			$user = $this->getNextUser($skipAbsent, $skipTimeman);
		}

		if ($user !== false)
		{
			$this->GetUser = $user;

			return CBPActivityExecutionStatus::Closed;
		}

		//check reserve skipping rule
		if ($this->SkipAbsentReserve === 'N')
		{
			$skipAbsent = false;
		}

		if ($this->SkipTimemanReserve === 'N')
		{
			$skipTimeman = false;
		}

		$user = $this->getReserveUser($skipAbsent, $skipTimeman);
		$this->GetUser = ($user !== false) ? $user : null;

		return CBPActivityExecutionStatus::Closed;
	}

	private function getBossUser(bool $skipAbsent)
	{
		$arUsers = $this->getActiveUsers(
			$this->GetUsersList($this->UserParameter, false)
		);

		if (count($arUsers) <= 0)
		{
			return null;
		}

		$userService = $this->workflow->GetRuntime()->getUserService();
		$userId = (int)$arUsers[0];
		$userDepartments = $userService->getUserDepartmentChains($userId);

		$heads = [];
		foreach ($userDepartments as $arV)
		{
			$maxLevel = $this->MaxLevel;
			foreach ($arV as $level => $deptId)
			{
				if ($maxLevel > 0 && $level + 1 > $maxLevel)
				{
					break;
				}

				$departmentHead = $userService->getDepartmentHead($deptId);

				if (
					!$departmentHead
					|| ($departmentHead === $userId)
					|| ($skipAbsent && CIntranetUtils::IsUserAbsent($departmentHead))
				)
				{
					$maxLevel++;
					continue;
				}
				if (!in_array($departmentHead, $heads, true))
				{
					$heads[] = $departmentHead;
				}
			}
		}

		$ar = [];
		foreach ($heads as $v)
		{
			$ar[] = "user_" . $v;
		}

		if (count($ar) == 0)
		{
			$ar = null;
		}
		elseif (count($ar) == 1)
		{
			$ar = $ar[0];
		}

		if ($ar !== null)
		{
			return $ar;
		}

		return false;
	}

	private function getRandomUser(bool $skipAbsent, bool $skipTimeman)
	{
		$arUsers = $this->getActiveUsers(
			$this->GetUsersList($this->UserParameter, $skipAbsent, $skipTimeman)
		);

		if (count($arUsers) > 0)
		{
			$rnd = mt_rand(0, count($arUsers) - 1);

			return "user_" . $arUsers[$rnd];
		}

		return false;
	}

	private function getReserveUser(bool $skipAbsent, bool $skipTimeman)
	{
		$arReserveUsers = $this->getActiveUsers(
			$this->GetUsersList($this->ReserveUserParameter, $skipAbsent, $skipTimeman)
		);

		if (count($arReserveUsers) > 0)
		{
			if ($this->UserType === self::USER_TYPE_RANDOM)
			{
				return 'user_' . $arReserveUsers[0];
			}
			else
			{
				foreach ($arReserveUsers as &$user)
				{
					$user = 'user_' . $user;
				}
				unset($user);

				return $arReserveUsers;
			}
		}

		return false;
	}

	private function getNextUser(bool $skipAbsent, bool $skipTimeman)
	{
		$allSpecifiedUsers = $this->GetUsersList($this->UserParameter, false, false);

		$lastUserId = $this->getStorage()->getValue('lastUserId');
		if ($lastUserId !== null)
		{
			$searchKey = array_search($lastUserId, $allSpecifiedUsers);
			if ($searchKey !== false)
			{
				$allSpecifiedUsers = array_merge(
					array_slice($allSpecifiedUsers, $searchKey + 1),
					array_slice($allSpecifiedUsers, 0, $searchKey + 1),
				);
			}
		}

		$allSpecifiedUsers = array_map(
			function($userId) {
				return 'user_' . $userId;
			},
			$allSpecifiedUsers
		);

		$availableUsers = $this->getActiveUsers(
			$this->GetUsersList($allSpecifiedUsers, $skipAbsent, $skipTimeman)
		);

		$nextUserId = $availableUsers[0];
		if ($nextUserId !== null)
		{
			$this->getStorage()->setValue('lastUserId', $nextUserId);

			return 'user_' . $nextUserId;
		}

		return false;
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = ""
	)
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = [];
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = [];

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = [
				"user_type" => "",
				"user_parameter" => "",
				"reserve_user_parameter" => "",
				"max_level" => 1,
				"skip_absent" => "Y",
				'skip_absent_reserve' => 'Y',
				"skip_timeman" => "N",
				"skip_timeman_reserve" => "N"
			];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				$arCurrentValues["user_type"] = $arCurrentActivity["Properties"]["UserType"];
				$arCurrentValues["max_level"] = $arCurrentActivity["Properties"]["MaxLevel"];
				$arCurrentValues["user_parameter"] = CBPHelper::UsersArrayToString(
					$arCurrentActivity["Properties"]["UserParameter"],
					$arWorkflowTemplate,
					$documentType
				);
				$arCurrentValues["reserve_user_parameter"] = CBPHelper::UsersArrayToString(
					$arCurrentActivity["Properties"]["ReserveUserParameter"],
					$arWorkflowTemplate,
					$documentType
				);
				$arCurrentValues["skip_absent"] = (
					array_key_exists("SkipAbsent", $arCurrentActivity["Properties"])
						? $arCurrentActivity["Properties"]["SkipAbsent"]
						: (($arCurrentActivity["Properties"]["UserType"] == self::USER_TYPE_BOSS) ? "N" : "Y")
				);
				$arCurrentValues["skip_absent_reserve"] = (
					array_key_exists("SkipAbsentReserve", $arCurrentActivity["Properties"])
						? $arCurrentActivity["Properties"]["SkipAbsentReserve"]
						: $arCurrentValues["skip_absent"]
				);
				$arCurrentValues["skip_timeman"] =
					(array_key_exists("SkipTimeman", $arCurrentActivity["Properties"]))
					? $arCurrentActivity["Properties"]["SkipTimeman"]
					: 'N'
				;
				$arCurrentValues["skip_timeman_reserve"] =
					(array_key_exists("SkipTimemanReserve", $arCurrentActivity["Properties"]))
						? $arCurrentActivity["Properties"]["SkipTimemanReserve"]
						: $arCurrentValues["skip_timeman"]
				;
			}
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			[
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
			]
		);
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	)
	{
		$arErrors = [];
		$arProperties = [];

		if (
			!isset($arCurrentValues["user_type"])
			|| !in_array(
				$arCurrentValues["user_type"],
				[self::USER_TYPE_BOSS, self::USER_TYPE_RANDOM, self::USER_TYPE_SEQUENT]
			)
		)
		{
			$arCurrentValues["user_type"] = self::USER_TYPE_RANDOM;
		}
		$arProperties["UserType"] = $arCurrentValues["user_type"];

		if (
			!isset($arCurrentValues["max_level"])
			|| $arCurrentValues["max_level"] < 1
			|| $arCurrentValues["max_level"] > 10
		)
		{
			$arCurrentValues["max_level"] = 1;
		}
		$arProperties["MaxLevel"] = $arCurrentValues["max_level"];

		$arProperties["UserParameter"] = CBPHelper::UsersStringToArray(
			$arCurrentValues["user_parameter"],
			$documentType,
			$arErrors
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arProperties["ReserveUserParameter"] = CBPHelper::UsersStringToArray(
			$arCurrentValues["reserve_user_parameter"],
			$documentType,
			$arErrors
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		if (!isset($arCurrentValues["skip_absent"]) || !in_array($arCurrentValues["skip_absent"], ["Y", "N"]))
		{
			$arCurrentValues["skip_absent"] = "Y";
		}
		$arProperties["SkipAbsent"] = $arCurrentValues["skip_absent"];
		$arProperties["SkipAbsentReserve"] = ($arCurrentValues["skip_absent_reserve"] !== 'N') ? 'Y' : 'N';

		if (!isset($arCurrentValues["skip_timeman"]) || !in_array($arCurrentValues["skip_timeman"], ["Y", "N"]))
		{
			$arCurrentValues["skip_timeman"] = "N";
		}
		$arProperties["SkipTimeman"] = $arCurrentValues["skip_timeman"];
		$arProperties["SkipTimemanReserve"] = ($arCurrentValues["skip_timeman_reserve"] !== 'N') ? 'Y' : 'N';

		$arErrors = self::ValidateProperties(
			$arProperties,
			new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}

	public static function ValidateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = [];

		if (!array_key_exists("UserParameter", $arTestProperties))
		{
			$bUsersFieldEmpty = true;
		}
		else
		{
			if (!is_array($arTestProperties["UserParameter"]))
			{
				$arTestProperties["UserParameter"] = [$arTestProperties["UserParameter"]];
			}

			$bUsersFieldEmpty = true;
			foreach ($arTestProperties["UserParameter"] as $userId)
			{
				if (!is_array($userId) && (trim($userId) !== '') || is_array($userId) && (count($userId) > 0))
				{
					$bUsersFieldEmpty = false;
					break;
				}
			}
		}

		if ($bUsersFieldEmpty)
		{
			$arErrors[] = [
				"code" => "NotExist",
				"parameter" => "UserParameter",
				"message" => GetMessage("BPARGUA_ACT_PROP_EMPTY1"),
			];
		}

		if (!array_key_exists("ReserveUserParameter", $arTestProperties))
		{
			$bUsersFieldEmpty = true;
		}
		else
		{
			if (!is_array($arTestProperties["ReserveUserParameter"]))
			{
				$arTestProperties["ReserveUserParameter"] = [$arTestProperties["ReserveUserParameter"]];
			}

			$bUsersFieldEmpty = true;
			foreach ($arTestProperties["ReserveUserParameter"] as $userId)
			{
				if (!is_array($userId) && (trim($userId) !== '') || is_array($userId) && (count($userId) > 0))
				{
					$bUsersFieldEmpty = false;
					break;
				}
			}
		}

		if ($bUsersFieldEmpty)
		{
			$arErrors[] = [
				"code" => "NotExist",
				"parameter" => "ReserveUserParameter",
				"message" => GetMessage("BPARGUA_ACT_PROP_EMPTY2"),
			];
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}
}