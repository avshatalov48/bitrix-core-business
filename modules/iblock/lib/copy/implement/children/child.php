<?php
namespace Bitrix\Iblock\Copy\Implement\Children;

use Bitrix\Main\Result;

interface Child
{
	/**
	 * Copies iblock child.
	 * @param int $iblockId Iblock id.
	 * @param int $copiedIblockId Copied iblock id.
	 * @return Result
	 */
	public function copy($iblockId, $copiedIblockId): Result;
}