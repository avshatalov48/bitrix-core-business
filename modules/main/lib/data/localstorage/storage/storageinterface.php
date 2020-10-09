<?php
namespace Bitrix\Main\Data\LocalStorage\Storage;

interface StorageInterface
{
	/**
	 * @param string   $key
	 * @param int|null $ttl
	 * @return array
	 */
	public function read(string $key, int $ttl);

	public function write(string $key, $value, int $ttl);
}