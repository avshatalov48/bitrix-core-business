<?php
namespace Bitrix\Bizproc\Automation\Target;

use Bitrix\Bizproc\Automation\Engine\Runtime;
use Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable;

abstract class BaseTarget
{
	protected $runtime;
	protected $appliedTrigger;
	protected $documentId;
	protected $documentType;

	public function isAvailable()
	{
		return true;
	}

	/**
	 * Set applied trigger data.
	 * @param array $trigger
	 * @return $this
	 */
	public function setAppliedTrigger(array $trigger)
	{
		$this->appliedTrigger = $trigger;

		return $this;
	}

	/**
	 * Returns applied trigger data.
	 * @return array|null
	 */
	public function getAppliedTrigger()
	{
		return $this->appliedTrigger;
	}

	/**
	 * @return \Bitrix\Bizproc\Automation\Engine\Runtime
	 */
	public function getRuntime()
	{
		if ($this->runtime === null)
		{
			$this->runtime = new Runtime();
			$this->runtime->setTarget($this);
		}

		return $this->runtime;
	}

	abstract public function getDocumentStatus();
	abstract public function setDocumentStatus($statusId);

	abstract public function getDocumentStatusList($categoryId = 0);

	public function getTriggers(array $statuses)
	{
		$result = [];
		$documentType = $this->getDocumentType();

		$iterator = TriggerTable::getList(array(
			'filter' => array(
				'=MODULE_ID' => $documentType[0],
				'=ENTITY' => $documentType[1],
				'=DOCUMENT_TYPE' => $documentType[2],
				'@DOCUMENT_STATUS' => $statuses
			)
		));

		while ($row = $iterator->fetch())
		{
			$row['DOCUMENT_TYPE'] = $documentType;
			$result[] = $row;
		}

		return $result;
	}

	public function setTriggers(array $triggers)
	{
		$updatedTriggers = [];
		foreach ($triggers as $trigger)
		{
			$triggerId = isset($trigger['ID']) ? (int)$trigger['ID'] : 0;

			if (isset($trigger['DELETED']) && $trigger['DELETED'] === 'Y')
			{
				if ($triggerId > 0)
				{
					//TODO: check document type
					TriggerTable::delete($triggerId);
				}
				continue;
			}

			if ($triggerId > 0)
			{
				TriggerTable::update($triggerId, array(
					'NAME' => $trigger['NAME'],
					'DOCUMENT_STATUS' => $trigger['DOCUMENT_STATUS'],
					'APPLY_RULES' => is_array($trigger['APPLY_RULES']) ? $trigger['APPLY_RULES'] : null
				));
			}
			elseif (isset($trigger['CODE']) && isset($trigger['DOCUMENT_STATUS']))
			{
				$documentType = $this->getDocumentType();
				$addResult = TriggerTable::add(array(
					'NAME' => $trigger['NAME'],
					'MODULE_ID' => $documentType[0],
					'ENTITY' => $documentType[1],
					'DOCUMENT_TYPE' => $documentType[2],
					'DOCUMENT_STATUS' => $trigger['DOCUMENT_STATUS'],
					'CODE' => $trigger['CODE'],
					'APPLY_RULES' => is_array($trigger['APPLY_RULES']) ? $trigger['APPLY_RULES'] : null
				));

				if ($addResult->isSuccess())
				{
					$trigger['ID'] = $addResult->getId();
				}
			}
			$updatedTriggers[] = $trigger;
		}

		return $updatedTriggers;
	}

	public function getAvailableTriggers()
	{
		return [];
	}

	public function setDocumentType(array $documentType)
	{
		return $this->documentType = $documentType;
	}

	public function getDocumentType()
	{
		return $this->documentType;
	}

	public function getDocumentId()
	{
		return $this->documentId;
	}

	public function setDocumentId($documentId)
	{
		$this->documentId = $documentId;
		return $this;
	}
}