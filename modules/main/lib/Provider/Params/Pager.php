<?php

declare(strict_types=1);

namespace Bitrix\Main\Provider\Params;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\UI\PageNavigation;

/**
 * Page params for providers GridParams
 */
class Pager implements PagerInterface
{
	/**
	 * @throws ArgumentException
	 */
	public function __construct(protected int $limit = 50, protected int $offset = 0)
	{
		$this
			->setLimit($this->limit)
			->setOffset($this->offset)
		;
	}

	/**
	 * @throws ArgumentException
	 */
	public static function buildFromPageNavigation(PageNavigation $pageNavigation): static
	{
		return new static(limit: $pageNavigation->getLimit() ?: 50, offset: $pageNavigation->getOffset() ?: 0);
	}

	public function getLimit(): int
	{
		return $this->limit;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setLimit(int $limit): static
	{
		if ($limit < 1)
		{
			throw new ArgumentException('The limit should be a positive number');
		}

		$this->limit = $limit;

		return $this;
	}

	public function getOffset(): int
	{
		return $this->offset;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOffset(int $offset): static
	{
		if ($offset < 0)
		{
			throw new ArgumentException('The offset should be a not negative number');
		}

		$this->offset = $offset;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPage(int $page): static
	{
		if ($page < 1)
		{
			throw new ArgumentException('The page should be a positive number');
		}

		$this->offset = ($page - 1) * $this->limit;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSize(int $size): PagerInterface
	{
		return $this->setLimit($size);
	}
}
