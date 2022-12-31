<?php

namespace Bitrix\Catalog\v2\Contractor\Provider;

/**
 * Interface IContractor
 *
 * @package Bitrix\Catalog\v2\Contractor\Provider
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface IContractor
{
	/**
	 * @return int
	 */
	public function getId(): int;

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string|null
	 */
	public function getContactPersonFullName(): ?string;

	/**
	 * @return string|null
	 */
	public function getPhone(): ?string;

	/**
	 * @return string|null
	 */
	public function getInn(): ?string;

	/**
	 * @return string|null
	 */
	public function getKpp(): ?string;

	/**
	 * @return string|null
	 */
	public function getAddress(): ?string;
}
