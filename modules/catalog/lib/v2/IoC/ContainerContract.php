<?php

namespace Bitrix\Catalog\v2\IoC;

/**
 * Interface ContainerContract
 * Describes the interface of a container that exposes methods to read its entries.
 *
 * @package Bitrix\Catalog\v2\IoC
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface ContainerContract
{
	public function has(string $id): bool;

	public function get(string $id, array $args = []);

	public function make(string $id, array $args = []);

	public function inject(string $id, $dependency);
}