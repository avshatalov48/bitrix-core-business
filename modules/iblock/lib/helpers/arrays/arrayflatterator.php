<?php

namespace Bitrix\Iblock\Helpers\Arrays;

class ArrayFlatterator
{
	private array $processedKeys = [];

	public function __construct(array $processedKeys = [])
	{
		$this->processedKeys = $processedKeys;
	}

	public function flatten(array $input): array
	{
		if (!empty($this->processedKeys))
		{
			return $this->flattenWithCheckKeys($input);
		}

		foreach ($input as $key => $value)
		{
			if (is_array($value))
			{
				$newItems = $this->getFlattenFields($value, $key);
				foreach ($newItems as $newKey => $newValue)
				{
					$input[$newKey] = $newValue;
				}
			}
		}

		return $input;
	}

	private function flattenWithCheckKeys(array $input): array
	{
		foreach ($input as $key => $value)
		{
			if (is_array($value) && in_array($key, $this->processedKeys, true))
			{
				$newItems = $this->getFlattenFields($value, $key);
				foreach ($newItems as $newKey => $newValue)
				{
					$input[$newKey] = $newValue;
				}
			}
		}

		return $input;
	}

	private function getFlattenFields(array $fields, string $prefix): array
	{
		$result = [];

		foreach ($fields as $name => $value)
		{
			$key = "{$prefix}[{$name}]";
			if (is_array($value))
			{
				array_push($result, ...$this->getFlattenFields($value, $key));
			}
			else
			{
				$result[$key] = $value;
			}
		}

		return $result;
	}
}
