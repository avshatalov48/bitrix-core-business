<?php
namespace Bitrix\Main\Copy;

use Bitrix\Main\Result;

interface Copyable
{
	/**
	 * Copies entity.
	 *
	 * @param ContainerCollection $containerCollection
	 * @return Result
	 */
	public function copy(ContainerCollection $containerCollection);
}