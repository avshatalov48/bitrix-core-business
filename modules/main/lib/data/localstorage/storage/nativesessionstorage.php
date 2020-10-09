<?php
namespace Bitrix\Main\Data\LocalStorage\Storage;

use Bitrix\Main\Session\SessionInterface;

class NativeSessionStorage implements StorageInterface
{
	/** @var SessionInterface */
	private $session;

	public function __construct(SessionInterface $session)
	{
		$this->session = $session;
	}

	public function read(string $key, int $ttl)
	{
		return $this->session->get($key);
	}

	public function write(string $key, $value, int $ttl)
	{
		$this->session->set($key, $value);
	}
}