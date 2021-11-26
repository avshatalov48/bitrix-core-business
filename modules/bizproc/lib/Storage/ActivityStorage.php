<?php

namespace Bitrix\Bizproc\Storage;

use Bitrix\Main\Web\Json;

class ActivityStorage
{
	private static $instances = [];

	private $tplId;
	private $name;
	private $values;

	public static function getInstance(int $tplId, string $name): self
	{
		$cacheKey = $tplId . '|' . $name;

		if (!isset(self::$instances[$cacheKey]))
		{
			self::$instances[$cacheKey] = new self($tplId, $name);
		}

		return self::$instances[$cacheKey];
	}

	private function __construct(int $tplId, string $name)
	{
		$this->tplId = $tplId;
		$this->name = $name;
	}

	private function __clone()
	{
	}

	public function getValue(string $key)
	{
		$row = $this->getAll()[$key] ?? null;

		return $row ? $row['value'] : null;
	}

	public function setValue(string $key, $value): self
	{
		$row = $this->getAll()[$key] ?? null;

		if ($row)
		{
			if ($value === null)
			{
				Entity\ActivityStorageTable::delete($row['id']);
				unset($this->values[$key]);
			}
			else
			{
				$this->values[$key]['value'] = $value;
				Entity\ActivityStorageTable::update($row['id'], ['KEY_VALUE' => Json::encode($value)]);
			}
		}
		else
		{
			$this->addValue($key, $value);
		}

		return $this;
	}

	protected function getAll()
	{
		if ($this->values === null)
		{
			$this->values = [];
			$listResult = Entity\ActivityStorageTable::getList([
				'filter' => [
					'=WORKFLOW_TEMPLATE_ID' => $this->tplId,
					'=ACTIVITY_NAME' => $this->name
				]
			]);

			foreach ($listResult as $item)
			{
				$this->values[$item['KEY_ID']] = [
					'id' => $item['ID'],
					'value' => Json::decode($item['KEY_VALUE'])
				];
			}
		}

		return $this->values;
	}

	protected function addValue(string $key, $value)
	{
		$result = Entity\ActivityStorageTable::add([
			'WORKFLOW_TEMPLATE_ID' => $this->tplId,
			'ACTIVITY_NAME' => $this->name,
			'KEY_ID' => $key,
			'KEY_VALUE' => Json::encode($value)
		]);

		$id = $result->getId();

		if (is_array($this->values))
		{
			$this->values[$key] = [
				'id' => $id,
				'value' => $value
			];
		}

		return $id;
	}

	public static function onAfterTemplateDelete(int $id)
	{
		$listResult = Entity\ActivityStorageTable::getList([
			'filter' => [
				'=WORKFLOW_TEMPLATE_ID' => $id,
			],
			'select' => ['ID']
		]);

		foreach ($listResult as $item)
		{
			Entity\ActivityStorageTable::delete($item['ID']);
		}
	}
}
