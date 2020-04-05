<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2013 Bitrix
 */

namespace Bitrix\Main\ORM\Fields\Validators;

use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Fields\Field;

interface IValidator
{
	/**
	 * @param       $value
	 * @param       $primary
	 * @param array $row
	 * @param Field $field
	 *
	 * @return string|boolean|EntityError
	 */
	public function validate($value, $primary, array $row, Field $field);
}
