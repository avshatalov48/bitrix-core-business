<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

class CBPIMNotifyActivity extends \CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			"Title" => "",
			"MessageUserFrom" => "",
			"MessageUserTo" => "",
			"MessageSite" => "",
			"MessageOut" => "",
			"MessageType" => "",
		];
	}

	public function Execute()
	{
		if (!\Bitrix\Main\Loader::includeModule("im"))
		{
			return \CBPActivityExecutionStatus::Closed;
		}

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$arMessageUserFrom = \CBPHelper::ExtractUsers($this->MessageUserFrom, $documentId, true);
		$arMessageUserTo = \CBPHelper::ExtractUsers($this->MessageUserTo, $documentId, false);

		$notifyMessage = CBPHelper::stringify($this->MessageSite);
		$notifyMessageOut = CBPHelper::stringify($this->MessageOut);

		$arMessageFields = [
			'FROM_USER_ID' => (int)$this->MessageType === IM_NOTIFY_SYSTEM ? 0 : $arMessageUserFrom,
			'NOTIFY_TYPE' => (int)$this->MessageType,
			'NOTIFY_MESSAGE' => $notifyMessage,
			'NOTIFY_MESSAGE_OUT' => $notifyMessageOut,
			'NOTIFY_MODULE' => 'bizproc',
			'NOTIFY_EVENT' => 'activity',
			'PUSH_MESSAGE' => $this->getPushText($notifyMessage),
		];

		$ar = [];
		foreach ($arMessageUserTo as $userTo)
		{
			if (in_array($userTo, $ar))
			{
				continue;
			}

			$ar[] = $userTo;
			$arMessageFields["TO_USER_ID"] = $userTo;
			\CIMNotify::Add($arMessageFields);
		}

		return \CBPActivityExecutionStatus::Closed;
	}

	private function getPushText(string $htmlMessage): string
	{
		$text = mb_substr(\CTextParser::clearAllTags($htmlMessage), 0, 200);
		if (mb_strlen($text) === 200)
		{
			$text .= '...';
		}

		return $text;
	}

	public static function ValidateProperties($arTestProperties = [], \CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = [];

		if (empty($arTestProperties['MessageUserFrom']))
		{
			$arErrors[] = [
				"code" => "NotExist",
				"parameter" => "MessageUserFrom",
				"message" => Loc::getMessage("BPIMNA_EMPTY_FROM"),
			];
		}
		if (empty($arTestProperties['MessageUserTo']))
		{
			$arErrors[] = [
				"code" => "NotExist",
				"parameter" => "MessageUserTo",
				"message" => Loc::getMessage("BPIMNA_EMPTY_TO"),
			];
		}
		if (empty($arTestProperties['MessageSite']))
		{
			$arErrors[] = [
				"code" => "NotExist",
				"parameter" => "MessageText",
				"message" => Loc::getMessage("BPIMNA_EMPTY_MESSAGE"),
			];
		}

		$from = array_key_exists("MessageUserFrom", $arTestProperties) ? $arTestProperties["MessageUserFrom"] : null;
		if ($user && $from !== $user->getBizprocId() && !$user->isAdmin())
		{
			$arErrors[] = [
				"code" => "NotExist",
				"parameter" => "MessageUserFrom",
				"message" => Loc::getMessage("BPIMNA_EMPTY_FROM"),
			];
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters,
		$arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = \CBPRuntime::GetRuntime();

		$arMap = [
			"MessageUserFrom" => "from_user_id",
			"MessageUserTo" => "to_user_id",
			"MessageSite" => "message_site",
			"MessageOut" => "message_out",
			"MessageType" => "message_type",
		];

		if (!is_array($arWorkflowParameters))
		{
			$arWorkflowParameters = [];
		}
		if (!is_array($arWorkflowVariables))
		{
			$arWorkflowVariables = [];
		}

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &\CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				foreach ($arMap as $k => $v)
				{
					if (array_key_exists($k, $arCurrentActivity["Properties"]))
					{
						if ($k == "MessageUserFrom" || $k == "MessageUserTo")
						{
							$arCurrentValues[$arMap[$k]] = \CBPHelper::UsersArrayToString($arCurrentActivity["Properties"][$k],
								$arWorkflowTemplate, $documentType);
						}
						else
						{
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
						}
					}
					else
					{
						if ($k == "MessageType")
						{
							$arCurrentValues[$arMap[$k]] = 2;
						}
						else
						{
							$arCurrentValues[$arMap[$k]] = "";
						}
					}
				}
			}
			else
			{
				foreach ($arMap as $k => $v)
				{
					$arCurrentValues[$arMap[$k]] = "";
				}
			}
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			[
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				'user' => new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser),
			]
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate,
		&$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = [];
		$arMap = [
			"from_user_id" => "MessageUserFrom",
			"to_user_id" => "MessageUserTo",
			"message_site" => "MessageSite",
			"message_out" => "MessageOut",
			"message_type" => "MessageType",
		];

		$properties = [];
		foreach ($arMap as $key => $value)
		{
			if ($key == "from_user_id" || $key == "to_user_id")
			{
				continue;
			}

			$properties[$value] = $arCurrentValues[$key];
		}

		$user = new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser);
		if ($user->isAdmin())
		{
			$properties["MessageUserFrom"] = \CBPHelper::UsersStringToArray(
				$arCurrentValues["from_user_id"],
				$documentType,
				$errors
			);
			if ($errors)
			{
				return false;
			}
		}
		else
		{
			$properties["MessageUserFrom"] = $user->getBizprocId();
		}

		$properties["MessageUserTo"] = \CBPHelper::UsersStringToArray(
			$arCurrentValues["to_user_id"],
			$documentType,
			$errors
		);
		if ($errors)
		{
			return false;
		}

		$errors = self::ValidateProperties(
			$properties,
			new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser)
		);
		if ($errors)
		{
			return false;
		}

		$currentActivity = &\CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}
}
