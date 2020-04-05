<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2013 Bitrix
 */

namespace Bitrix\Main\ORM\Fields\Validators;

use Bitrix\Main\ORM;

class EnumValidator extends Validator
{
	/**
	 * @param $value
	 * @param $primary
	 * @param array $row
	 * @param \Bitrix\Main\ORM\Fields\Field | \Bitrix\Main\ORM\Fields\EnumField | \Bitrix\Main\ORM\Fields\BooleanField $field
	 *
	 * @return bool|string
	 */
	public function validate($value, $primary, array $row, ORM\Fields\Field $field)
	{
		if (in_array($value, $field->getValues(), true) || $value == '')
		{
			return true;
		}

		return $this->getErrorMessage($value, $field);
	}
}
