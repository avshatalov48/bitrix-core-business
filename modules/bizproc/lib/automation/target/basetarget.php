<?php
namespace Bitrix\Bizproc\Automation\Target;

use Bitrix\Bizproc\Automation\Engine\Runtime;

abstract class BaseTarget
{
	protected $runtime;
	protected $appliedTrigger;
	protected $documentId;

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
		return [];
	}

	public function setTriggers(array $triggers)
	{
		return $triggers;
	}

	public function getAvailableTriggers()
	{
		return [];
	}

	abstract public function getDocumentType();

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