<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPHandleExternalEventActivity
	extends CBPActivity
	implements IBPEventActivity, IBPActivityExternalEventListener, IBPEventDrivenActivity
{
	private $isInEventActivityMode = false;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "", "Permission" => array(), "SenderUserId" => null);

		$this->SetPropertiesTypes(array(
			'SenderUserId' => array(
				'Type' => 'user'
			)
		));
	}

	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$this->isInEventActivityMode = true;

		$v = array();
		$arPermissionTmp = $this->Permission;
		if (is_array($arPermissionTmp))
			foreach ($arPermissionTmp as $val)
				$v[] = (strpos($val, "{=") === 0 ? $val : "{=user:".$val."}");

		if (count($v) > 0)
			$this->WriteToTrackingService(str_replace(array("#EVENT#", "#VAL#"), array($this->name, implode(", ", $v)), GetMessage("BPHEEA_TRACK")));

		$stateService = $this->workflow->GetService("StateService");
		$stateService->AddStateParameter(
			$this->GetWorkflowInstanceId(),
			array(
				"NAME" => $this->name,
				"TITLE" => $this->Title,
				"PERMISSION" => $this->Permission,
			)
		);

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}

	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$stateService = $this->workflow->GetService("StateService");
		$stateService->DeleteStateParameter($this->GetWorkflowInstanceId(), $this->name);

		$this->workflow->RemoveEventHandler($this->name, $eventHandler);
	}

	public function Execute()
	{
		if ($this->isInEventActivityMode)
			return CBPActivityExecutionStatus::Closed;

		$this->Subscribe($this);

		$this->isInEventActivityMode = false;
		return CBPActivityExecutionStatus::Executing;
	}

	public function Cancel()
	{
		if (!$this->isInEventActivityMode)
			$this->Unsubscribe($this);

		return CBPActivityExecutionStatus::Closed;
	}

	public function HandleFault(Exception $exception)
	{
		if ($exception == null)
			throw new Exception("exception");

		$status = $this->Cancel();
		if ($status == CBPActivityExecutionStatus::Canceling)
			return CBPActivityExecutionStatus::Faulting;

		return $status;
	}

	public function OnExternalEvent($arEventParameters = array())
	{
		if (count($this->Permission) > 0)
		{
			$arSenderGroups = (array_key_exists("Groups", $arEventParameters) ? $arEventParameters["Groups"] : array());
			if (!is_array($arSenderGroups))
				$arSenderGroups = array($arSenderGroups);
			if (array_key_exists("User", $arEventParameters))
				$arSenderGroups[] = "user_".$arEventParameters["User"];
			if (count($arSenderGroups) <= 0)
				return;

			$bHavePerms = false;

			$cnti = count($this->Permission);
			$cntj = count($arSenderGroups);
			for ($i = 0; $i < $cnti; $i++)
			{
				for ($j = 0; $j < $cntj; $j++)
				{
					if ($this->Permission[$i] == $arSenderGroups[$j])
					{
						$bHavePerms = true;
						break 2;
					}
				}
			}

			if (!$bHavePerms)
				return;
		}

		if ($this->executionStatus != CBPActivityExecutionStatus::Closed)
		{
			if (array_key_exists("User", $arEventParameters))
				$this->SenderUserId = "user_".$arEventParameters["User"];

			$this->Unsubscribe($this);
			$this->workflow->CloseActivity($this);
		}
	}

	public function OnStateExternalEvent($arEventParameters = array())
	{
		if ($this->executionStatus != CBPActivityExecutionStatus::Closed && array_key_exists("User", $arEventParameters))
		{
			$this->SenderUserId = "user_".$arEventParameters["User"];
		}
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$currentParent = &CBPWorkflowTemplateLoader::FindParentActivityByName($arWorkflowTemplate, $activityName);

		$c = count($currentParent['Children']);
		$allowSetStatus = ($c == 1 || $currentParent['Children'][$c - 1]["Type"] == 'SetStateActivity');

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]) && array_key_exists("Permission", $arCurrentActivity["Properties"]))
			{
				$arCurrentValues["permission"] = CBPHelper::UsersArrayToString(
					$arCurrentActivity["Properties"]["Permission"],
					$arWorkflowTemplate,
					$documentType
				);
			}

			if ($c > 1 && $currentParent['Children'][$c - 1]["Type"] == 'SetStateActivity')
				$arCurrentValues["setstate"] = $currentParent['Children'][$c - 1]["Properties"]["TargetStateName"];
		}

		$arStates = array();
		if ($allowSetStatus)
			$arStates = CBPWorkflowTemplateLoader::GetStatesOfTemplate($arWorkflowTemplate);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				"allowSetStatus" => $allowSetStatus,
				"arStates" => $arStates,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arProperties = array();

		$arProperties["Permission"] = CBPHelper::UsersStringToArray($arCurrentValues["permission"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;
		$currentParent = &CBPWorkflowTemplateLoader::FindParentActivityByName($arWorkflowTemplate, $activityName);

		$c = count($currentParent['Children']);
		if ($c == 1)
		{
			if ($arCurrentValues["setstate"] != '')
			{
				$currentParent['Children'][] = array(
					'Type' => 'SetStateActivity',
					'Name' => md5(uniqid(mt_rand(), true)),
					'Properties' => array('TargetStateName' => $arCurrentValues["setstate"]),
					'Children' => array()
				);
			}
		}
		elseif ($currentParent['Children'][$c - 1]["Type"] == 'SetStateActivity')
		{
			if ($arCurrentValues["setstate"] != '')
				$currentParent['Children'][$c - 1]["Properties"]['TargetStateName'] = $arCurrentValues["setstate"];
			else
				unset($currentParent['Children'][$c - 1]);
		}

		return true;
	}
}
?>