<?php

namespace Bitrix\Catalog\v2;

/**
 * Interface IblockElementRepositoryContract
 *
 * @package Bitrix\Catalog\v2
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface IblockElementRepositoryContract extends RepositoryContract
{
	public function setDetailUrlTemplate(?string $template);

	public function getDetailUrlTemplate(): ?string;

	public function setAutoloadDetailUrl(bool $state);

	public function checkAutoloadDetailUrl(): bool;
}
