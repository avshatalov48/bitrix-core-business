<?php

namespace Bitrix\Bizproc\Internal\Repository\Mapper;

use Bitrix\Bizproc\Internal\Entity\WorkflowState\WorkflowState;
use Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Main\Type\DateTime;

class WorkflowStateMapper
{
	public function convertFromOrm(EO_WorkflowState $ormModel): WorkflowState
	{
		return
			(new WorkflowState())
				->setId($ormModel->getId())
				->setModuleId($ormModel->getModuleId())
				->setEntity($ormModel->getEntity())
				->setDocumentId($ormModel->getDocumentId())
				->setDocumentIdInt($ormModel->getDocumentIdInt())
				->setWorkflowTemplateId($ormModel->getWorkflowTemplateId())
				->setState($ormModel->getState())
				->setStateTitle($ormModel->getStateTitle())
				->setStateParameters($ormModel->getStateParameters())
				->setModified($ormModel->getModified()?->getTimestamp())
				->setStarted($ormModel->getStarted()?->getTimestamp())
				->setStartedBy($ormModel->getStartedBy())
			;
	}

	public function convertToOrm(WorkflowState $entity): EO_WorkflowState
	{
		$ormModel = $entity->getId()
			? EO_WorkflowState::wakeUp($entity->getId())
			: WorkflowStateTable::createObject();

		$ormModel
			->setModuleId($entity->getModuleId())
			->setEntity($entity->getEntity())
			->setDocumentId($entity->getDocumentId())
			->setDocumentIdInt($entity->getDocumentIdInt())
			->setWorkflowTemplateId($entity->getWorkflowTemplateId())
			->setState($entity->getState())
			->setStateTitle($entity->getStateTitle())
			->setStateParameters($entity->getStateParameters())
			->setModified(new DateTime())
			->setStartedBy($entity->getStartedBy())
		;

		return $ormModel;
	}
}
