<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPStateActivity
	extends CBPCompositeActivity
	implements IBPActivityEventListener
{
	public $isListenTrigerred = false;
	public $arActivityState = array();

	protected $nextStateName = "";

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "", "Permission" => array());
	}

	public function ToString()
	{
		return $this->name.
			" [".get_class($this)."] (status=".
			CBPActivityExecutionStatus::Out($this->executionStatus).
			", isListenTrigerred=".
			$this->isListenTrigerred.
			", count(arActivityState)=".
			count($this->arActivityState).
			")";
	}

	public function SetNextStateName($name)
	{
		$this->nextStateName = $name;
	}

	public function Execute()
	{
		$this->nextStateName = "";
		$this->arActivityState = array();
		$this->isListenTrigerred = false;

		$s = array();
		$arPermissionTmp = $this->Permission;
		if (is_array($arPermissionTmp))
		{
			foreach ($arPermissionTmp as $k1 => $v1)
			{
				$v2 = array();
				foreach ($v1 as $v3)
					$v2[] = (strpos($v3, "{=") === 0 ? $v3 : "{=user:".$v3."}");
				if (count($v2) > 0)
					$s[] = $k1.": ".implode(", ", $v2);
			}
		}

		if (count($s) > 0)
			$this->WriteToTrackingService(str_replace("#VAL#", implode(";", $s), GetMessage("BPSA_TRACK1")));

		$stateService = $this->workflow->GetService("StateService");

		$stateInitialization = null;
		for ($i = 0, $sz = sizeof($this->arActivities); $i < $sz; $i++)
		{
			if (is_a($this->arActivities[$i], "CBPStateInitializationActivity"))
				$stateInitialization = $this->arActivities[$i];
		}

		if ($stateInitialization != null)
		{
			$stateService->SetState(
				$this->GetWorkflowInstanceId(),
				array(
					"STATE" => $this->name,
					"TITLE" => $this->Title,
					"PARAMETERS" => array(),
				),
				$this->Permission
			);

			$stateInitialization->AddStatusChangeHandler(self::ClosedEvent, $this);
			$this->workflow->ExecuteActivity($stateInitialization);

			return CBPActivityExecutionStatus::Executing;
		}
		else
		{
			$stateService->SetState(
				$this->GetWorkflowInstanceId(),
				array(
					"STATE" => $this->name,
					"TITLE" => $this->Title,
					"PARAMETERS" => array(),//$this->GetAvailableStateEvents(),
				),
				$this->Permission
			);

			return $this->ExecuteState();
		}
	}

	private function ExecuteState()
	{
		for ($i = 0, $s = sizeof($this->arActivities); $i < $s; $i++)
		{
			$eventDriven = $this->arActivities[$i];
			if (!is_a($eventDriven, "CBPEventDrivenActivity"))
				continue;

			$l = new CBPStateEventActivitySubscriber($eventDriven);
			$this->arActivityState[$i] = $l;

			$activity = $eventDriven->GetEventActivity();
			$activity->Subscribe($l);
		}

		if (count($this->arActivityState) > 0)
			return CBPActivityExecutionStatus::Executing;

		return CBPActivityExecutionStatus::Closed;
	}

	public function OnEvent(CBPActivity $sender, $arEventParameters = array())
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);

		$bCloseActivity = false;

		if (is_a($sender, "CBPStateInitializationActivity"))
		{
			if (strlen($this->nextStateName) > 0)
			{
				$stateFinalization = null;
				for ($i = 0, $s = sizeof($this->arActivities); $i < $s; $i++)
				{
					if (is_a($this->arActivities[$i], "CBPStateFinalizationActivity"))
						$stateFinalization = $this->arActivities[$i];
				}
				if ($stateFinalization != null)
				{
					$stateFinalization->AddStatusChangeHandler(self::ClosedEvent, $this);
					$this->workflow->ExecuteActivity($stateFinalization);
				}
				else
				{
					$bCloseActivity = true;
				}
			}
			else
			{
				//$stateService = $this->workflow->GetService("StateService");
				//$stateService->SetStateParameters($this->GetWorkflowInstanceId(), $this->GetAvailableStateEvents());

				$status = $this->ExecuteState();
				if ($status != CBPActivityExecutionStatus::Executing)
					$bCloseActivity = true;
			}
		}
		elseif (is_a($sender, "CBPStateFinalizationActivity"))
		{
			$bCloseActivity = true;
		}
		else
		{
			$stateFinalization = null;
			if (strlen($this->nextStateName) > 0)
			{
				for ($i = 0, $s = sizeof($this->arActivities); $i < $s; $i++)
				{
					if (is_a($this->arActivities[$i], "CBPStateFinalizationActivity"))
						$stateFinalization = $this->arActivities[$i];
				}
			}

			if ($stateFinalization != null)
			{
				$stateFinalization->AddStatusChangeHandler(self::ClosedEvent, $this);
				$this->workflow->ExecuteActivity($stateFinalization);
			}
			else
			{
				$bCloseActivity = true;
			}
		}

		if ($bCloseActivity)
		{
			$this->arActivityState = array();
			$this->isListenTrigerred = false;

			$arEP = array();
			if (strlen($this->nextStateName) > 0)
				$arEP["NextStateName"] = $this->nextStateName;

			$this->workflow->CloseActivity($this, $arEP);
		}
	}

	public function Cancel()
	{
		$flag = true;
		for ($i = 0, $s = sizeof($this->arActivities); $i < $s; $i++)
		{
			$activity2 = $this->arActivities[$i];
			if (is_a($activity2, "CBPEventDrivenActivity") && isset($this->arActivityState[$i]))
			{
				$parentEventHandler = $this->arActivityState[$i];
				$activity3 = $activity2->GetEventActivity();
				$activity3->Unsubscribe($parentEventHandler);
			}

			$flag = false;
			if ($activity2->executionStatus == CBPActivityExecutionStatus::Executing)
				$this->workflow->CancelActivity($activity2);
		}

		if (!$flag)
			return $this->executionStatus;

		return CBPActivityExecutionStatus::Closed;
	}

	/**
	* Returns available events for current state
	*
	*/
	private function GetAvailableStateEvents()
	{
		$ar = array();

		for ($i = 0, $cnt = count($this->arActivities); $i < $cnt; $i++)
		{
			$activity2 = $this->arActivities[$i];
			if (is_a($activity2, "CBPEventDrivenActivity"))
			{
				$activity3 = $activity2->GetEventActivity();

				if (is_a($activity3, "CBPHandleExternalEventActivity"))
				{
					$ar[] = array(
						"NAME" => $activity3->name,
						"TITLE" => $activity3->Title,
						"PERMISSION" => $activity3->Permission,
					);
				}
			}
		}

		return $ar;
	}

	public static function ValidateChild($childActivity, $bFirstChild = false)
	{
		$arErrors = array();

		$child = "CBP".$childActivity;

		$bCorrect = false;
		while (strlen($child) > 0)
		{
			if (in_array($child, array("CBPStateInitializationActivity", "CBPStateFinalizationActivity", "CBPEventDrivenActivity")))
			{
				$bCorrect = true;
				break;
			}
			$child = get_parent_class($child);
		}

		if (!$bCorrect)
			$arErrors[] = array("code" => "WrongChildType", "message" => GetMessage("BPSA_INVALID_CHILD"));

		return array_merge($arErrors, parent::ValidateChild($childActivity, $bFirstChild));
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

//		if (!array_key_exists("Permission", $arTestProperties) || count($arTestProperties["Permission"]) <= 0)
//		{
//			$arErrors[] = array("code" => "NotExist", "parameter" => "Permission", "message" => GetMessage("BPSA_EMPTY_PERMS"));
//		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$documentService = $runtime->GetService("DocumentService");
		$arAllowableOperations = $documentService->GetAllowableOperations($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]) && array_key_exists("Permission", $arCurrentActivity["Properties"]))
			{
				foreach ($arAllowableOperations as $operationKey => $operationValue)
				{
					$current = $documentService->toExternalOperations($documentType, $arCurrentActivity["Properties"]["Permission"]);

					$arCurrentValues["permission_".$operationKey] = CBPHelper::UsersArrayToString(
						$current[$operationKey],
						$arWorkflowTemplate,
						$documentType
					);
				}
			}
		}
		
		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arAllowableOperations" => $arAllowableOperations,
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arProperties = array("Permission" => array());

		$documentService = $runtime->GetService("DocumentService");
		$arAllowableOperations = $documentService->GetAllowableOperations($documentType);

		foreach ($arAllowableOperations as $operationKey => $operationValue)
		{
			$arProperties["Permission"][$operationKey] = CBPHelper::UsersStringToArray($arCurrentValues["permission_".$operationKey], $documentType, $arErrors);
			if (count($arErrors) > 0)
				return false;
		}

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}

final class CBPStateEventActivitySubscriber
	implements IBPActivityExternalEventListener
{
	private $eventDrivenActivity;

	public function __construct(CBPEventDrivenActivity $eventDriven)
	{
		$this->eventDrivenActivity = $eventDriven;
	}

	public function ToString()
	{
		return "eventDrivenActivity = ".$this->eventDrivenActivity->ToString();
	}

	public function OnExternalEvent($arEventParameters = array())
	{
		$stateActivity = $this->eventDrivenActivity->parent;

		if (!$stateActivity->isListenTrigerred
			&& ($stateActivity->executionStatus != CBPActivityExecutionStatus::Canceling)
			&& ($stateActivity->executionStatus != CBPActivityExecutionStatus::Closed))
		{
			$stateActivity->isListenTrigerred = true;

			$arActivities = $stateActivity->CollectNestedActivities();
			for ($i = 0, $s = sizeof($arActivities); $i < $s; $i++)
			{
				$activity2 = $arActivities[$i];
				if (!is_a($activity2, "CBPEventDrivenActivity"))
					continue;

				$parentEventHandler = $stateActivity->arActivityState[$i];

				$activity3 = $activity2->GetEventActivity();
				if (method_exists($activity3, 'OnStateExternalEvent'))
					$activity3->OnStateExternalEvent($arEventParameters);
				$activity3->Unsubscribe($parentEventHandler);
			}

			$this->eventDrivenActivity->AddStatusChangeHandler(CBPStateActivity::ClosedEvent, $stateActivity);
			$stateActivity->workflow->ExecuteActivity($this->eventDrivenActivity);
		}
	}
}
?>