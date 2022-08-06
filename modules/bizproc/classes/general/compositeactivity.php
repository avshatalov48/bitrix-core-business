<?php

abstract class CBPCompositeActivity extends CBPActivity
{
	protected $arActivities = array();
	protected $readOnlyData = [];

	public function SetWorkflow(CBPWorkflow $workflow)
	{
		parent::SetWorkflow($workflow);
		foreach ($this->arActivities as $activity)
		{
			if (!method_exists($activity, 'SetWorkflow'))
			{
				throw new Exception('ActivitySetWorkflow');
			}
			$activity->SetWorkflow($workflow);
		}
	}

	public function unsetWorkflow()
	{
		parent::unsetWorkflow();
		foreach ($this->arActivities as $activity)
		{
			if (method_exists($activity, 'SetWorkflow'))
			{
				$activity->unsetWorkflow();
			}
		}
	}

	public function setReadOnlyData(array $data)
	{
		$this->readOnlyData = $data;
	}

	public function getReadOnlyData(): array
	{
		return $this->readOnlyData;
	}

	public function pullReadOnlyData()
	{
		$data = $this->readOnlyData;
		$this->readOnlyData = [];

		return $data;
	}

	public function pullProperties(): array
	{
		$result = parent::pullProperties();

		/** @var CBPActivity $activity */
		foreach ($this->arActivities as $activity)
		{
			$result = array_merge($result, $activity->pullProperties());
		}

		return $result;
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();
		/** @var CBPActivity $activity */
		foreach ($this->arActivities as $activity)
			$activity->ReInitialize();
	}

	public function CollectNestedActivities()
	{
		return $this->arActivities;
	}

	public function FixUpParentChildRelationship(CBPActivity $nestedActivity)
	{
		parent::FixUpParentChildRelationship($nestedActivity);

		if (!is_array($this->arActivities))
			$this->arActivities = array();

		$this->arActivities[] = $nestedActivity;
	}

	protected function ClearNestedActivities()
	{
		$this->arActivities = array();
	}

	public function Initialize()
	{
		foreach ($this->arActivities as $activity)
			$this->workflow->InitializeActivity($activity);
	}

	public function Finalize()
	{
		foreach ($this->arActivities as $activity)
			$this->workflow->FinalizeActivity($activity);
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		return parent::ValidateProperties($arTestProperties, $user);
	}
}