<?php
namespace Bitrix\Sale\Archive\Recovery;

use Bitrix\Main;

/**
 * @package Bitrix\Sale\Archive\Recovery
 */
class JsonField extends PackedField
{
	public function tryUnpack()
	{
		$result = new Main\Result();
		try
		{
			$value = Main\Web\Json::decode($this->packedValue);
			if (!$value)
			{
				$result->addError(new Main\Error('Unavailable value for unpacking'));
			}
		}
		catch (\Exception $e)
		{
			$result->addError(new Main\Error('Unavailable value for unpacking'));
		}

		return $result;
	}

	public function unpack()
	{
		try
		{
			$unpacked = Main\Web\Json::decode($this->packedValue);
			if (!is_array($unpacked))
				return null;

			return $this->formatResult($unpacked);
		}
		catch (\Exception $e)
		{
			return null;
		}
	}

	private function formatResult(array $fields)
	{
		foreach ($fields as &$field)
		{
			if (is_array($field))
			{
				$field = $this->formatResult($field);
			}
			elseif (CheckDateTime($field))
			{
				$field = new Main\Type\DateTime($field);
			}
		}

		return $fields;
	}
}	