<?php
namespace Bitrix\Sale\Archive\Recovery;

use Bitrix\Sale\Archive;

/**
 * @package Bitrix\Sale\Archive\Recovery
 */
interface Buildable
{
	/**
	 * @return Archive\Order
	 */
	public function buildOrder();

	public function setEntityFields($entityName, array $fields = null);
}