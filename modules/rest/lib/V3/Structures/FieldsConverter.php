<?php

namespace Bitrix\Rest\V3\Structures;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class FieldsConverter
{
	public static function convertValueByType(?string $fieldType, mixed $value)
	{
		try
		{
			if ($fieldType === DateTime::class)
			{
				$correctDateTime = \DateTime::createFromFormat(DATE_ATOM, $value);
				if ($correctDateTime !== false)
				{
					return DateTime::createFromPhp($correctDateTime);
				}
			}
			else if ($fieldType === Date::class)
			{
				$correctDate = \DateTime::createFromFormat('Y-m-d', $value);
				if ($correctDate !== false)
				{
					return Date::createFromPhp($correctDate);
				}
			}
		}
		catch (\Exception)
		{
		}

		return $value;
	}
}