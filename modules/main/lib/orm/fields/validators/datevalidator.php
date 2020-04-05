<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2013 Bitrix
 */

namespace Bitrix\Main\ORM\Fields\Validators;

use Bitrix\Main\ORM;
use Bitrix\Main\Type;

class DateValidator extends Validator
{
	public function validate($value, $primary, array $row, ORM\Fields\Field $field)
	{
		if (empty($value))
		{
			return true;
		}

		if ($value instanceof Type\Date)
		{
			// self-validating object
			return true;
		}

		if (\CheckDateTime($value, FORMAT_DATE))
		{
			return true;
		}

		return $this->getErrorMessage($value, $field);
	}
}
