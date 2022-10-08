<?php

abstract class CBPCompositeActivity extends CBPActivity
{
	protected $arActivities = array();
	protected $readOnlyData = [];

	public function setWorkflow(CBPWorkflow $workflow)
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

	protected function reInitialize()
	{
		parent::ReInitialize();
		/** @var CBPActivity $activity */
		foreach ($this->arActivities as $activity)
			$activity->ReInitialize();
	}

	public function collectNestedActivities()
	{
		return $this->arActivities;
	}

	public function fixUpParentChildRelationship(CBPActivity $nestedActivity)
	{
		parent::FixUpParentChildRelationship($nestedActivity);

		if (!is_array($this->arActivities))
			$this->arActivities = array();

		$this->arActivities[] = $nestedActivity;
	}

	protected function clearNestedActivities()
	{
		$this->arActivities = array();
	}

	public function initialize()
	{
		foreach ($this->arActivities as $activity)
			$this->workflow->InitializeActivity($activity);
	}

	public function finalize()
	{
		foreach ($this->arActivities as $activity)
			$this->workflow->FinalizeActivity($activity);
	}

	public static function validateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		return parent::ValidateProperties($arTestProperties, $user);
	}
}
