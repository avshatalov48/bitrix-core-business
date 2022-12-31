<?php

namespace Bitrix\Catalog\Component\GridVariation;

/**
 * State object of variation grid.
 */
class GridState
{
	private string $gridId;
	private int $productId;
	private int $currentPage;

	/**
	 * @param int $productId
	 * @param string $gridId
	 */
	public function __construct(int $productId, string $gridId)
	{
		$this->productId = $productId;
		$this->gridId = $gridId;
	}

	/**
	 * Product id.
	 *
	 * @return int
	 */
	public function getProductId(): int
	{
		return $this->productId;
	}

	/**
	 * Grid id.
	 *
	 * @return string
	 */
	public function getGridId(): string
	{
		return $this->gridId;
	}

	/**
	 * Current grid page.
	 *
	 * @return int
	 */
	public function getCurrentPage(): int
	{
		return $this->currentPage ?? 1;
	}

	/**
	 * Current grid page.
	 *
	 * @param int $page
	 *
	 * @return void
	 */
	public function setCurrentPage(int $page): void
	{
		$this->currentPage = $page;
	}

	/**
	 * Reset state.
	 *
	 * @return void
	 */
	public function reset(): void
	{
		$this->currentPage = 1;
	}

	/**
	 * Save state.
	 *
	 * @return void
	 */
	public function save(): void
	{
		(new GridStateStorage)->save($this);
	}
}
