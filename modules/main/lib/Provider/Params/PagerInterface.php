<?php

declare(strict_types=1);

namespace Bitrix\Main\Provider\Params;

use Bitrix\Main\ArgumentException;

interface PagerInterface
{
	public function getLimit(): int;

	/**
	 * @param int $limit
	 *
	 * @return self
	 *
	 * @throws ArgumentException If given limit value less than 1.
	 */
	public function setLimit(int $limit): self;

	public function getOffset(): int;

	/**
	 * @param int $offset
	 *
	 * @return self
	 *
	 * @throws ArgumentException If given offset value less than 0.
	 */
	public function setOffset(int $offset): self;

	/**
	 * Sets number of the selected page and automatically recalculate the offset.
	 *
	 * @param int $page Page number (min: 1)
	 *
	 * @return self
	 *
	 * @throws ArgumentException If given page value less than 1.
	 */
	public function setPage(int $page): self;

	/**
	 * An alias of the setLimit method.
	 *
	 * @param int $size
	 *
	 * @return self
	 *
	 * @throws ArgumentException If given size value less than 1.
	 */
	public function setSize(int $size): self;
}
