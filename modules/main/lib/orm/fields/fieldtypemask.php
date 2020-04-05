<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

/**
 * @package    bitrix
 * @subpackage main
 */
abstract class FieldTypeMask
{
	const SCALAR = 1;
	const EXPRESSION = 2;
	const USERTYPE = 4;
	const REFERENCE = 8;
	const ONE_TO_MANY = 16;
	const MANY_TO_MANY = 32;

	const FLAT = 1|2;
	const RELATION = 8|16|32;

	const ALL = 63;
}
