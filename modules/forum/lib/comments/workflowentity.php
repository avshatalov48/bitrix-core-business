<?php

namespace Bitrix\Forum\Comments;

use Bitrix\Main\Loader;

final class WorkflowEntity extends Entity
{
	const ENTITY_TYPE = 'wf';
	const MODULE_ID = 'lists';
	const XML_ID_PREFIX = 'WF_';

	private $hasAccess;

	protected static $permissions = array();

	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canRead($userId)
	{
		// you are not allowed to view the task, so you can not read messages
		if(!$this->checkHasAccess($userId))
		{
			return false;
		}

		return true;
	}
	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canAdd($userId)
	{
		// you are not allowed to view the task, so you can not add new messages
		if(!$this->checkHasAccess($userId))
		{
			return false;
		}

		return true;
	}

	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canEdit($userId)
	{
		global $USER;
		if(
			$USER->isAdmin()
			|| $USER->canDoOperation('bitrix24_config')
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * @param integer $userId User id.
	 * @return bool
	 */
	private function checkHasAccess($userId)
	{
		global $USER;

		if($this->hasAccess === null)
		{
			if (Loader::includeModule("bizproc"))
			{
				if(
					$USER->isAdmin()
					|| $USER->canDoOperation('bitrix24_config')
				)
				{
					$this->hasAccess = true;
				}
				else
				{
					$workflowId = false;
					$workflowIntegerId = intval($this->getId());
					if ($workflowIntegerId > 0)
					{
						$workflowId = \CBPStateService::getWorkflowByIntegerId($workflowIntegerId);
					}

					if ($workflowId)
					{
						$currentUserId = (int) $USER->getId();
						$participants = \CBPTaskService::getWorkflowParticipants($workflowId);
						if (in_array($currentUserId, $participants))
						{
							$this->hasAccess = true;
						}
						else
						{
							$state = \CBPStateService::getWorkflowStateInfo($workflowId);
							$this->hasAccess = (
								$state
								&& $currentUserId === (int) $state['STARTED_BY']
							);
						}
					}
					else
					{
						$this->hasAccess = false;
					}

					if (!$this->hasAccess && Loader::includeModule("iblock"))
					{
						$documentId = \CBPStateService::GetStateDocumentId($workflowId);
						$elementQuery = \CIBlockElement::getList(
							array(), array("ID" => $documentId[2]), false, false, array("IBLOCK_ID"));
						$element = $elementQuery->fetch();
						if ($element['IBLOCK_ID'])
						{
							$this->hasAccess = \CIBlockElementRights::userHasRightTo(
								$element["IBLOCK_ID"], $documentId[2], "element_read");
						}
					}
				}
			}
			else
			{
				$this->hasAccess = false;
			}
		}

		return $this->hasAccess;
	}
}