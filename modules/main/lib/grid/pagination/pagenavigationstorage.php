<?php

namespace Bitrix\Main\Grid\Pagination;

use Bitrix\Main\UI\PageNavigation;

/**
 * Pagination storage.
 *
 * Used to save the grid pagination state, between user requests.
 * The storage period and location are determined by a implementation.
 *
 * @see \Bitrix\Main\Grid\Pagination\Storage\StorageSupporter for create and work with storage.
 */
interface PageNavigationStorage
{
	/**
	 * Filling the pagination object with data from the storage.
	 *
	 * @param PageNavigation $pagination
	 *
	 * @return void
	 */
	public function fill(PageNavigation $pagination): void;

	/**
	 * Saving pagination data to storage.
	 *
	 * @param PageNavigation $pagination
	 *
	 * @return void
	 */
	public function save(PageNavigation $pagination): void;
}
