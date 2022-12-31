<?php

namespace Bitrix\Calendar\Internals;

abstract class Dto
{
	/**
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		$this->initComplexProperties($data);
		foreach ($data as $key => $value)
		{
			if($this->checkConstructException($key, $value))
			{
				continue;
			}
			if (property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
	}

	/**
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	protected function checkConstructException($key, $value): bool
	{
		return false;
	}

	/**
	 * @param bool $filterEmptyValue
	 * @return array|bool|float|int|string|void
	 */
	public function toArray(bool $filterEmptyValue = false)
	{
		return $this->prepareValue($this, $filterEmptyValue);
	}

	/**
	 * @param $value
	 * @param bool $filterEmptyValue
	 * @return array|bool|float|int|string|void
	 */
	protected function prepareValue($value, bool $filterEmptyValue)
	{
		if (is_scalar($value))
		{
			return $value;
		}

		if (is_array($value) || is_object($value))
		{
			$result = [];
			foreach ($value as $index => $item)
			{
				if ($filterEmptyValue && $item === null)
				{
					continue;
				}
				if ($this->checkPrepareToArrayException($index, $item))
				{
					continue;
				}
				$result[$index] = $this->prepareValue($item, $filterEmptyValue);
			}
			return $result;
		}
	}

	/**
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	protected function checkPrepareToArrayException($key, $value): bool
	{
		return false;
	}

	/**
	 * @param array $data
	 * @return void
	 */
	private function initComplexProperties(array &$data)
	{
		$map = $this->getComplexPropertyMap();
		foreach ($map as $key => $item)
		{
			if (!empty($item['isArray']) && !empty($data[$key]) && is_array($data[$key]))
			{
				$this->$key = [];
				foreach ($data[$key] as $property)
				{
					$this->$key[] = $this->prepareComplexProperty(
						$property,
						$item['class'],
						$item['isMandatory'] ?? false
					);
				}
			}
			elseif (empty($data[$key]))
			{
				$this->$key = null;
			}
			else
			{
				$this->$key = $this->prepareComplexProperty(
					$data[$key],
					$item['class'],
					$item['isMandatory'] ?? false
				);
			}
			unset($data[$key]);
		}
	}

	/**
	 * @return array
	 */
	protected function getComplexPropertyMap(): array
	{
		return [];
	}

	/**
	 * @param array $data
	 * @param $className
	 * @param $isMandatory
	 * @return mixed
	 */
	private function prepareComplexProperty(array $data, $className, $isMandatory = false)
	{
		if ($isMandatory)
		{
			return new $className($data);
		}
		else
		{
			return new $className($data ?? []);
		}
	}
}
