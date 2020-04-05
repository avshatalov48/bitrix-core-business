<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM\Fields;

use Bitrix\Main\ORM\Fields\Relations\Reference;

/**
 * @package    bitrix
 * @subpackage iblock
 */
class PropertyReference extends Reference
{
	use PropertyRelation;
}
