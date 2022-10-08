<?php

namespace Bitrix\Location\Repository\Location\Capability;

use Bitrix\Location\Entity\Generic\Collection;

/**
 * Interface IFindByText
 * @package Bitrix\Location\Repository
 */
interface IFindByText
{
	/**
	 * @param string $text
	 * @return Collection|bool
	 */
	public function findByText(string $text, string $languageId);
}