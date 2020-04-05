<?php
namespace Bitrix\Sale\Archive\Recovery;

use Bitrix\Main\Result;

/**
 * @package Bitrix\Sale\Archive\Recovery
 */
interface ArchivePackable
{
	/**
	 * @return Result
	 */
	public function tryUnpack();

	public function setPackedOrder(PackedField $field);

	public function addPackedBasketItem($id, PackedField $field);
}