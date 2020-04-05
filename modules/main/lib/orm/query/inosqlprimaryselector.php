<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2013 Bitrix
 */

namespace Bitrix\Main\ORM\Query;

use Bitrix\Main\ORM\Entity;

interface INosqlPrimarySelector
{
	public function getEntityByPrimary(Entity $entity, $primary, $select);
}
