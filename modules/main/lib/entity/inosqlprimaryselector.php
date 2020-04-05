<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2013 Bitrix
 */

namespace Bitrix\Main\Entity;

interface INosqlPrimarySelector
{
	public function getEntityByPrimary(Base $entity, $primary, $select);
}
