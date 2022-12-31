<?php

namespace Bitrix\Catalog\Component\GridVariation;

use Bitrix\Main\Application;

/**
 * Object for save/load grid state operations.
 */
class GridStateStorage
{
	private const FIELD_CURRENT_PAGE = 'currentPage';
	private const FIELD_DEADLINE = 'deadline';

	private const DEADLINE_SEC = 60;

	/**
	 * Storage key.
	 *
	 * @param int $productId
	 * @param string $gridId
	 *
	 * @return string
	 */
	private function getStorageKey(int $productId, string $gridId): string
	{
		return "{$productId}_{$gridId}";
	}

	/**
	 * Load state.
	 *
	 * @param int $productId
	 * @param string $gridId
	 *
	 * @return GridState
	 */
	public function load(int $productId, string $gridId): GridState
	{
		$state = new GridState($productId, $gridId);
		$key = $this->getStorageKey($productId, $gridId);
		$cache = Application::getInstance()->getLocalSession($key);

		// postponed every minute so that the pages do not stick forever.
		$deadline = (int)$cache->get(self::FIELD_DEADLINE);
		if ($deadline < time())
		{
			$cache->set(self::FIELD_DEADLINE, time() + self::DEADLINE_SEC);
		}
		else
		{
			$currentPage = $cache->get(self::FIELD_CURRENT_PAGE);
			if (isset($currentPage))
			{
				$state->setCurrentPage((int)$currentPage);
			}
		}

		return $state;
	}

	/**
	 * Save state.
	 *
	 * @param GridState $state
	 *
	 * @return void
	 */
	public function save(GridState $state): void
	{
		$key = $this->getStorageKey($state->getProductId(), $state->getGridId());
		$cache = Application::getInstance()->getLocalSession($key);
		$cache->set(self::FIELD_CURRENT_PAGE, $state->getCurrentPage());
		$cache->set(self::FIELD_DEADLINE, time() + self::DEADLINE_SEC);
	}
}
