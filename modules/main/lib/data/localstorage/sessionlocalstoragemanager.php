<?php
namespace Bitrix\Main\Data\LocalStorage;

use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\Session\CompositeSessionManager;

final class SessionLocalStorageManager
{
	private const POINTER_STORE = '__store';

	/** @var SessionLocalStorage[]  */
	private array $collection = [];
	private string $uniqueId;
	private Storage\StorageInterface $storage;
	private int $ttl = 86400;

	public function __construct(Storage\StorageInterface $storage)
	{
		$this->storage = $storage;

		EventManager::getInstance()->addEventHandler('main', 'OnAfterEpilog', [$this, 'save']);
		EventManager::getInstance()->addEventHandler(
			'main',
			CompositeSessionManager::EVENT_REGENERATE_SESSION_ID,
			[$this, 'handleRegenerateSessionId']
		);
	}

	public function save(): void
	{
		foreach ($this->collection as $item)
		{
			$this->storage->write($item->getUniqueName(), $item->getData(), $this->getTtl());
		}
	}

	public function handleRegenerateSessionId(Event $event): void
	{
		$oldSessionId = $this->getUniqueId();
		$newSessionId = $event->getParameter('newSessionId');
		if (!$newSessionId)
		{
			return;
		}

		$pointerStore = $this->getPointerStore();
		$list = $pointerStore['list'] ?? [];
		foreach ($list as $registeredName)
		{
			$this->load($registeredName);
		}

		$oldLocalStorageManager = new self($this->storage);
		$oldLocalStorageManager
			->setUniqueId($oldSessionId)
			->clearAll()
		;

		$this->setUniqueId($newSessionId);
		foreach ($this->collection as $item)
		{
			$item->setUniqueName($this->buildUniqueKey($item->getName()));
		}
	}

	public function getUniqueId(): string
	{
		return $this->uniqueId;
	}

	public function setUniqueId(string $uniqueId): self
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

	private function getPointerStore(): SessionLocalStorage
	{
		return $this->get(self::POINTER_STORE);
	}

	private function registerInStore(SessionLocalStorage $localStorage): void
	{
		if ($localStorage->getName() === self::POINTER_STORE)
		{
			return;
		}

		$pointerStore = $this->getPointerStore();
		if (empty($pointerStore['list']))
		{
			$pointerStore['list'] = [];
		}
		if (!\in_array($localStorage->getName(), $pointerStore['list'], true))
		{
			$pointerStore['list'][] = $localStorage->getName();
		}
	}

	private function load(string $name): void
	{
		$this->get($name);
	}

	public function get(string $name): SessionLocalStorage
	{
		if (!$this->exists($name))
		{
			$item = new SessionLocalStorage($this->buildUniqueKey($name), $name);
			$data = $this->storage->read($item->getUniqueName(), $this->getTtl());
			if (isset($data) && is_array($data))
			{
				$item->setData($data);
			}

			$this->registerInStore($item);
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
		$this->get($name)->clear();
	}

	public function clearAll(): void
	{
		$pointerStore = $this->getPointerStore();
		$list = $pointerStore['list'] ?? [];
		foreach ($list as $registeredName)
		{
			$this->clear($registeredName);
		}
		$pointerStore->clear();
	}

	protected function buildUniqueKey(string $name): string
	{
		return $this->uniqueId . '_' . $name;
	}

	public function isReady()
	{
		return Application::getInstance()->getKernelSession()->isStarted();
	}
}
