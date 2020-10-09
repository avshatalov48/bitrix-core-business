<?php
namespace Bitrix\Main\Data\LocalStorage;

use Bitrix\Main\Data\LocalStorage\Storage;
use Bitrix\Main\EventManager;

final class SessionLocalStorageManager
{
	/** @var SessionLocalStorage[]  */
	private $collection = [];
	/** @var string */
	private $uniqueId;
	/** @var Storage\StorageInterface */
	private $storage;
	/** @var int */
	private $ttl = 86400;

	public function __construct(Storage\StorageInterface $storage)
	{
		$this->storage = $storage;

		EventManager::getInstance()->addEventHandler("main", "OnAfterEpilog", [$this, 'save']);
	}

	public function save()
	{
		foreach ($this->collection as $item)
		{
			$this->storage->write($item->getUniqueName(), $item->getData(), $this->getTtl());
		}
	}

	public function getUniqueId(): string
	{
		return $this->uniqueId;
	}

	public function setUniqueId($uniqueId)
	{
		$this->uniqueId = $uniqueId;

		return $this;
	}

	public function getTtl(): int
	{
		return $this->ttl;
	}

	public function setTtl(int $ttl): self
	{
		$this->ttl = $ttl;

		return $this;
	}

	public function get(string $name): SessionLocalStorage
	{
		if (!$this->exists($name))
		{
			$item = new SessionLocalStorage($this->buildUniqueKey($name));
			$data = $this->storage->read($item->getUniqueName(), $this->getTtl());
			if (isset($data) && is_array($data))
			{
				$item->setData($data);
			}

			$this->collection[$name] = $item;
		}

		return $this->collection[$name];
	}

	public function exists(string $name): bool
	{
		return isset($this->collection[$name]);
	}

	public function clear(string $name): void
	{
		$localStorage = $this->get($name);
		if ($localStorage)
		{
			$localStorage->clear();
		}
	}

	protected function buildUniqueKey(string $name): string
	{
		return $this->uniqueId . '_' . $name;
	}
}