<?php
namespace Bitrix\Calendar\Sync\Connection;

interface ServerInterface
{
	/**
	 * @return string
	 */
	public function getHost(): string;

	/**
	 * @return string
	 */
	public function getScheme(): string;

	/**
	 * @return string
	 */
	public function getPort(): string;

	/**
	 * @return string
	 */
	public function getBasePath(): string;

	/**
	 * @return string|null
	 */
	public function getUserName(): ?string;

	/**
	 * @return string|null
	 */
	public function getPassword(): ?string;

	/**
	 * @return string
	 */
	public function getFullPath(): string;

}
