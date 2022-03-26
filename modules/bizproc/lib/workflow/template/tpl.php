<?php
namespace Bitrix\Bizproc\Workflow\Template;

use Bitrix\Main\ArgumentException;
use CBPWorkflowTemplateLoader;

class Tpl extends Entity\EO_WorkflowTemplate
{
	protected $tpl;

	public function getRootActivity()
	{
		return $this->getActivities()[0];
	}

	public function findActivity($activityName)
	{
		return CBPWorkflowTemplateLoader::FindActivityByName($this->getActivities(), $activityName);
	}

	public function getDocumentComplexType()
	{
		return [$this->getModuleId(), $this->getEntity(), $this->getDocumentType()];
	}

	public function getActivities()
	{
		return $this->getTemplate();
	}

	/**
	 * @return Collection\Usages
	 * @throws \CBPArgumentOutOfRangeException
	 */
	public function collectUsages()
	{
		/** @var \CBPActivity $rootActivity */
		if ($this->getId())
		{
			$rootActivity = CBPWorkflowTemplateLoader::GetLoader()->LoadWorkflow($this->getId())[0];
		}
		else
		{
			$rootActivity = CBPWorkflowTemplateLoader::GetLoader()->loadWorkflowFromArray([
				'ID' => '0',
				'TEMPLATE' => $this->getTemplate(),
				'VARIABLES' => $this->getVariables(),
				'PARAMETERS' => $this->getParameters(),
			])[0];
		}

		$rootActivity->SetProperties($this->getParameters());
		$rootActivity->SetVariablesTypes($this->getVariables());

		$usages = new Collection\Usages();
		$this->findActivityUsagesRecursive($rootActivity, $usages);

		return $usages;
	}

	public function findUsedSourceKeys($sourceType)
	{
		if (!SourceType::isType($sourceType))
		{
			throw new ArgumentException('Incorrect $sourceType', 'sourceType');
		}

		$usages = $this->collectUsages();
		return array_unique(array_column($usages->getBySourceType($sourceType), 1));
	}

	private function findActivityUsagesRecursive(\CBPActivity $activity, Collection\Usages $usages)
	{
		$sources = $activity->collectUsages();
		$usages->addOwnerSources($activity->GetName(), $sources);

		$children = $activity->CollectNestedActivities();
		if (is_array($children))
		{
			foreach ($children as $child)
			{
				$this->findActivityUsagesRecursive($child, $usages);
			}
		}
		return $usages;
	}

	public function getUsedActivityTypes()
	{
		return array_unique($this->getActivityTypes($this->getTemplate()));
	}

	private function getActivityTypes(array $activities)
	{
		$types = [];
		foreach ($activities as $activity)
		{
			$types[] = $activity['Type'];

			if (!empty($activity['Children']))
			{
				$types = array_merge($types, $this->getActivityTypes($activity['Children']));
			}
		}
		return $types;
	}
}