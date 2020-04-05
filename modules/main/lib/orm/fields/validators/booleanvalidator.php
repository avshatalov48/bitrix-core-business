<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Fields\Validators;

use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\Field;

/**
 * @package    bitrix
 * @subpackage main
 */
class BooleanValidator extends Validator
{
	public function validate($value, $primary, array $row, Field $field)
	{
		/** @var BooleanField $field */
		if (in_array($field->normalizeValue($value), $field->getValues(), true))
		{
			return true;
		}

		return $this->getErrorMessage($value, $field);
	}
}
