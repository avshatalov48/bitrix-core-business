<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @property-read array Permission */
/** @property-read int PermissionMode */
/** @property-read int PermissionScope */
class CBPStateActivity extends CBPCompositeActivity implements IBPActivityEventListener
{
	public $isListenTrigerred = false;
	public $arActivityState = [];

	protected $nextStateName = "";

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			"Title" => "",
			"Permission" => [],
			"PermissionMode" => null,
			"PermissionScope" => null,
		];
	}

	public function ToString()
	{
		return $this->name .
			" [" . get_class($this) . "] (status=" .
			CBPActivityExecutionStatus::Out($this->executionStatus) .
			", isListenTrigerred=" .
			$this->isListenTrigerred .
			", count(arActivityState)=" .
			count($this->arActivityState) .
			")";
	}

	public function SetNextStateName($name)
	{
		$this->nextStateName = $name;
	}

	public function Execute()
	{
		$this->nextStateName = "";
		$this->arActivityState = [];
		$this->isListenTrigerred = false;

		$permissionText = [];
		$permissions = $this->Permission;
		$permissionMode = $this->PermissionMode;
		$permissionScope = $this->PermissionScope;

		if (is_array($permissions))
		{
			foreach ($permissions as $k1 => $v1)
			{
				$v2 = [];
				foreach ($v1 as $v3)
				{
					$v2[] = (mb_strpos($v3, "{=") === 0 ? $v3 : "{=user:" . $v3 . "}");
				}
				if (count($v2) > 0)
				{
					$permissionText[] = $k1 . ": " . implode(", ", $v2);
				}
			}
		}

		if (!empty($permissionMode))
		{
			$permissions['__mode'] = $permissionMode;
		}
		if (!empty($permissionScope))
		{
			$permissions['__scope'] = $permissionScope;
		}

		if ($permissionText)
		{
			$this->WriteToTrackingService(
				GetMessage("BPSA_TRACK_1", ['#VAL#' => implode(";", $permissionText)])
			);
		}

		$stateService = $this->workflow->GetService("StateService");

		$stateInitialization = null;
		for ($i = 0, $sz = sizeof($this->arActivities); $i < $sz; $i++)
		{
			if (is_a($this->arActivities[$i], "CBPStateInitializationActivity"))
			{
				$stateInitialization = $this->arActivities[$i];
			}
		}

		if ($stateInitialization != null)
		{
			$stateService->SetState(
				$this->GetWorkflowInstanceId(),
				[
					"STATE" => $this->name,
					"TITLE" => $this->Title,
					"PARAMETERS" => [],
				],
				$permissions
			);

			$stateInitialization->AddStatusChangeHandler(self::ClosedEvent, $this);
			$this->workflow->ExecuteActivity($stateInitialization);

			return CBPActivityExecutionStatus::Executing;
		}
		else
		{
			$stateService->SetState(
				$this->GetWorkflowInstanceId(),
				[
					"STATE" => $this->name,
					"TITLE" => $this->Title,
					"PARAMETERS" => [],
				],
				$permissions
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
			{
				continue;
			}

			$l = new CBPStateEventActivitySubscriber($eventDriven);
			$this->arActivityState[$i] = $l;

			$activity = $eventDriven->GetEventActivity();
			$activity->Subscribe($l);
		}

		if (count($this->arActivityState) > 0)
		{
			return CBPActivityExecutionStatus::Executing;
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public function OnEvent(CBPActivity $sender, $arEventParameters = [])
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);

		$bCloseActivity = false;

		if (is_a($sender, "CBPStateInitializationActivity"))
		{
			if ($this->nextStateName <> '')
			{
				$stateFinalization = null;
				for ($i = 0, $s = sizeof($this->arActivities); $i < $s; $i++)
				{
					if (is_a($this->arActivities[$i], "CBPStateFinalizationActivity"))
					{
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
			else
			{
				$status = $this->ExecuteState();
				if ($status != CBPActivityExecutionStatus::Executing)
				{
					$bCloseActivity = true;
				}
			}
		}
		elseif (is_a($sender, "CBPStateFinalizationActivity"))
		{
			$bCloseActivity = true;
		}
		else
		{
			$stateFinalization = null;
			if ($this->nextStateName <> '')
			{
				for ($i = 0, $s = sizeof($this->arActivities); $i < $s; $i++)
				{
					if (is_a($this->arActivities[$i], "CBPStateFinalizationActivity"))
					{
						$stateFinalization = $this->arActivities[$i];
					}
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
			$this->arActivityState = [];
			$this->isListenTrigerred = false;

			$arEP = [];
			if ($this->nextStateName <> '')
			{
				$arEP["NextStateName"] = $this->nextStateName;
			}

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
			{
				$this->workflow->CancelActivity($activity2);
			}
		}

		if (!$flag)
		{
			return $this->executionStatus;
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateChild($childActivity, $bFirstChild = false)
	{
		$arErrors = [];

		$child = "CBP" . $childActivity;

		$bCorrect = false;
		while ($child <> '')
		{
			if (
				in_array($child,
					["CBPStateInitializationActivity", "CBPStateFinalizationActivity", "CBPEventDrivenActivity"])
			)
			{
				$bCorrect = true;
				break;
			}
			$child = get_parent_class($child);
		}

		if (!$bCorrect)
		{
			$arErrors[] = ["code" => "WrongChildType", "message" => GetMessage("BPSA_INVALID_CHILD_1")];
		}

		return array_merge($arErrors, parent::ValidateChild($childActivity, $bFirstChild));
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

		$documentService = $runtime->GetService("DocumentService");
		$arAllowableOperations = $documentService->GetAllowableOperations($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = [
				'perm_mode' => CBPSetPermissionsMode::Clear,
				'perm_scope' => CBPSetPermissionsMode::ScopeWorkflow,
			];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (
				is_array($arCurrentActivity["Properties"])
				&& array_key_exists("Permission", $arCurrentActivity["Properties"])
			)
			{
				foreach ($arAllowableOperations as $operationKey => $operationValue)
				{
					$current = $documentService->toExternalOperations(
						$documentType,
						$arCurrentActivity["Properties"]["Permission"]
					);

					$arCurrentValues["permission_" . $operationKey] = CBPHelper::UsersArrayToString(
						$current[$operationKey],
						$arWorkflowTemplate,
						$documentType
					);
				}
			}

			if (!empty($arCurrentActivity["Properties"]["PermissionMode"]))
			{
				$arCurrentValues["perm_mode"] = $arCurrentActivity["Properties"]["PermissionMode"];
			}
			if (!empty($arCurrentActivity["Properties"]["PermissionScope"]))
			{
				$arCurrentValues["perm_scope"] = $arCurrentActivity["Properties"]["PermissionScope"];
			}
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			[
				"arAllowableOperations" => $arAllowableOperations,
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				'isExtendedPermsSupported' => $documentService->isExtendedPermsSupported($documentType),
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
		&$errors
	)
	{
		$errors = [];
		$properties = ["Permission" => []];

		$documentService = CBPRuntime::GetRuntime()->GetService("DocumentService");
		$arAllowableOperations = $documentService->GetAllowableOperations($documentType);

		foreach ($arAllowableOperations as $operationKey => $operationValue)
		{
			$properties["Permission"][$operationKey] = CBPHelper::UsersStringToArray(
				$arCurrentValues["permission_" . $operationKey],
				$documentType,
				$errors
			);
			if ($errors)
			{
				return false;
			}
		}

		$properties["PermissionMode"] = $arCurrentValues["perm_mode"] ?? null;
		$properties["PermissionScope"] = $arCurrentValues["perm_scope"] ?? null;

		$errors = self::ValidateProperties(
			$properties,
			new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)
		);

		if ($errors)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $properties;

		return true;
	}
}

final class CBPStateEventActivitySubscriber implements IBPActivityExternalEventListener
{
	private $eventDrivenActivity;

	public function __construct(CBPEventDrivenActivity $eventDriven)
	{
		$this->eventDrivenActivity = $eventDriven;
	}

	public function ToString()
	{
		return "eventDrivenActivity = " . $this->eventDrivenActivity->ToString();
	}

	public function OnExternalEvent($arEventParameters = [])
	{
		$stateActivity = $this->eventDrivenActivity->parent;

		if (
			!$stateActivity->isListenTrigerred
			&& ($stateActivity->executionStatus != CBPActivityExecutionStatus::Canceling)
			&& ($stateActivity->executionStatus != CBPActivityExecutionStatus::Closed)
		)
		{
			$stateActivity->isListenTrigerred = true;

			$arActivities = $stateActivity->CollectNestedActivities();
			for ($i = 0, $s = sizeof($arActivities); $i < $s; $i++)
			{
				$activity2 = $arActivities[$i];
				if (!is_a($activity2, "CBPEventDrivenActivity"))
				{
					continue;
				}

				$parentEventHandler = $stateActivity->arActivityState[$i];

				$activity3 = $activity2->GetEventActivity();
				if (method_exists($activity3, 'OnStateExternalEvent'))
				{
					$activity3->OnStateExternalEvent($arEventParameters);
				}
				$activity3->Unsubscribe($parentEventHandler);
			}

			$this->eventDrivenActivity->AddStatusChangeHandler(CBPStateActivity::ClosedEvent, $stateActivity);
			$stateActivity->workflow->ExecuteActivity($this->eventDrivenActivity);
		}
	}
}
