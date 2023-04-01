<?php
namespace Bitrix\Bizproc\Automation\Target;

use Bitrix\Bizproc\Automation\Engine\ConditionGroup;
use Bitrix\Bizproc\Automation\Engine\Runtime;
use Bitrix\Bizproc\Automation\Engine\TemplatesScheme;
use Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable;

abstract class BaseTarget
{
	private const CACHE_TTL = 7200;

	protected $runtime;
	protected $appliedTrigger;
	protected $documentId;
	protected $documentType;
	protected array $appliedTriggerConditionResults = [];

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

	public function getDocumentCategory(): int
	{
		return 0;
	}

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
			),
			'cache' => [
				'ttl' => self::CACHE_TTL
			]
		));

		while ($row = $iterator->fetch())
		{
			$row['DOCUMENT_TYPE'] = $documentType;
			$result[] = $row;
		}

		return $result;
	}

	public function prepareTriggersToSave(array &$triggers)
	{
		foreach ($triggers as $i => $trigger)
		{
			if (isset($trigger['DELETED']) && $trigger['DELETED'] === 'Y')
			{
				continue;
			}

			$triggers[$i]['APPLY_RULES'] = $this->prepareApplyRules($trigger['APPLY_RULES']);
		}
	}

	public function prepareTriggersToShow(array &$triggers)
	{
		foreach ($triggers as $i => $trigger)
		{
			$triggers[$i]['APPLY_RULES'] = $this->prepareApplyRules($trigger['APPLY_RULES'], true);
		}
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

	public function extractTemplateParameters(array $triggers): array
	{
		$params = [];
		foreach ($triggers as $trigger)
		{
			$triggerDescription = $this->getAvailableTriggerByCode($trigger['CODE']);
			$status = $trigger['DOCUMENT_STATUS'];

			if ($triggerDescription && isset($triggerDescription['RETURN']))
			{
				if (!isset($params[$status]))
				{
					$params[$status] = [];
				}
				foreach ($triggerDescription['RETURN'] as $property)
				{
					$params[$status][$property['Id']] = $property;
				}
			}
		}
		return $params;
	}

	private function prepareApplyRules($rules, $external = false): ?array
	{
		if (!is_array($rules))
		{
			return null;
		}

		if (isset($rules['Condition']))
		{
			$condition = new ConditionGroup($rules['Condition']);
			if ($external)
			{
				$condition->externalizeValues($this->getDocumentType());
			}
			else
			{
				$condition->internalizeValues($this->getDocumentType());
			}
			$rules['Condition'] = $condition->toArray();
		}

		if (isset($rules['ExecuteBy']))
		{
			if ($external)
			{
				$rules['ExecuteBy'] = \CBPHelper::UsersArrayToString(
					$rules['ExecuteBy'],
					null,
					$this->getDocumentType()
				);
			}
			else
			{
				$rules['ExecuteBy'] = \CBPHelper::UsersStringToArray(
					$rules['ExecuteBy'],
					$this->getDocumentType(),
					$errors
				);
			}
		}

		return $rules;
	}

	/**
	 * @return array Triggers list.
	 */
	public function getAvailableTriggers()
	{
		return [];
	}

	public function canTriggerSetExecuteBy(): bool
	{
		return false;
	}

	/**
	 * @param $code
	 * @return array|null
	 */
	public function getAvailableTriggerByCode($code): ?array
	{
		foreach ($this->getAvailableTriggers() as $availableTrigger)
		{
			if ($code === $availableTrigger['CODE'])
			{
				return $availableTrigger;
			}
		}
		return null;
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

	public function getComplexDocumentId(): array
	{
		$type = $this->getDocumentType();

		return [$type[0], $type[1], $this->getDocumentId()];
	}

	public function getTemplatesScheme(): ?TemplatesScheme
	{
		return null;
	}

	public function setAppliedTriggerConditionResults(array $log)
	{
		$this->appliedTriggerConditionResults = $log;
	}

	public function getAppliedTriggerConditionResults(): array
	{
		return $this->appliedTriggerConditionResults;
	}

	public function getDocumentCategoryCode(): string
	{
		return '';
	}
}