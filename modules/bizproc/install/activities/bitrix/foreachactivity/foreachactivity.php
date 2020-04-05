<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPForEachActivity
	extends CBPCompositeActivity
	implements IBPActivityEventListener
{
	private $values;
	private $valuesKeys;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'Variable' => null,

			//return
			'Key' => null,
			'Value' => null
		];
	}

	public function Execute()
	{
		if ($this->TryNextIteration())
		{
			return CBPActivityExecutionStatus::Executing;
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public function Cancel()
	{
		if (count($this->arActivities) == 0)
			return CBPActivityExecutionStatus::Closed;

		$activity = $this->arActivities[0];
		if ($activity->executionStatus == CBPActivityExecutionStatus::Executing)
			$this->workflow->CancelActivity($activity);

		return CBPActivityExecutionStatus::Canceling;
	}

	public function OnEvent(CBPActivity $sender, $arEventParameters = array())
	{
		if ($sender == null)
			throw new Exception("sender");

		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);

		if (!$this->TryNextIteration())
			$this->workflow->CloseActivity($this);
	}

	private function TryNextIteration()
	{
		if (($this->executionStatus == CBPActivityExecutionStatus::Canceling) || ($this->executionStatus == CBPActivityExecutionStatus::Faulting))
		{
			return false;
		}

		if ($this->values === null)
		{
			$this->values = [];
			$variableValues = $this->GetVariable($this->Variable);
			if ($variableValues)
			{
				$this->values = (array) $variableValues;
				$this->valuesKeys = array_keys($this->values);

				$varType = $this->getVariableType($this->Variable);
				if ($varType && isset($varType['Type']))
				{
					$this->SetPropertiesTypes(['Value' => ['Type' => $varType['Type']]]);
				}
			}
		}

		if (!count($this->values))
		{
			return false;
		}

		$this->Key = array_shift($this->valuesKeys);;
		$this->Value = array_shift($this->values);

		if (count($this->arActivities) > 0)
		{
			$activity = $this->arActivities[0];
			$activity->ReInitialize();
			$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
			$this->workflow->ExecuteActivity($activity);
		}
		return true;
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();
		$this->values = null;
		$this->Key = null;
		$this->Value = null;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				$arCurrentValues = [
					'variable' => $arCurrentActivity["Properties"]['Variable']
				];
			}
		}

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = ['variable' => null];
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				'workflowVariables' => $arWorkflowVariables
			)
		);
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];
		if (strlen($arTestProperties["Variable"]) <= 0)
		{
			$errors[] = array(
				"code" => "emptyVariable",
				"message" => GetMessage("BPFEA_NO_VARIABLE"),
			);
		}

		return array_merge($errors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		if (!is_array($arWorkflowVariables))
		{
			$arWorkflowVariables = [];
		}

		$properties = array(
			"Variable" => $arCurrentValues["variable"] && array_key_exists($arCurrentValues["variable"], $arWorkflowVariables)
				? $arCurrentValues["variable"] : null
		);

		if ($arErrors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)))
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $properties;

		return true;
	}
}