<?php

namespace Bitrix\Main\Grid\Pagination\Storage;

use Bitrix\Main\Grid\Pagination\PageNavigationStorage;

trait StorageSupporter
{
	private ?PageNavigationStorage $paginationStorage = null;

	final protected function getPaginationStorage(): ?PageNavigationStorage
	{
		$this->paginationStorage ??= $this->createStorage();

		return $this->paginationStorage;
	}

	protected function createStorage(): ?PageNavigationStorage
	{
		return new SessionPageNavigationStorage();
	}
}
