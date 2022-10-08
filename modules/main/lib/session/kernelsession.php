<?php

namespace Bitrix\Main\Session;

use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Session\Handlers\CookieSessionHandler;

class KernelSession implements SessionInterface, \ArrayAccess
{
	use ArrayAccessWithReferences;

	private const COOKIE_NAME = 'kernel';

	/** @var bool */
	protected $started;
	/** @var \SessionHandlerInterface */
	protected $sessionHandler;
	/** @var int */
	private $lifetime = 0;
	/** @var string */
	private $id;
	/** @var string */
	private $hash;

	public function __construct(int $lifetime = 0)
	{
		$this->lifetime = $lifetime;
	}

	public function isActive(): bool
	{
		return $this->isStarted();
	}

	public function isAccessible(): bool
	{
		return true;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setId($id)
	{
		throw new NotSupportedException();
	}

	public function getName(): string
	{
		throw new NotSupportedException();
	}

	public function setName($name)
	{
		throw new NotSupportedException();
	}

	/**
	 * @return \SessionHandlerInterface|CookieSessionHandler
	 */
	public function getSessionHandler(): \SessionHandlerInterface
	{
		return $this->sessionHandler;
	}

	final protected function hashData(string $data): string
	{
		return hash('sha512', $data);
	}

	public function start(): bool
	{
		if ($this->isStarted())
		{
			return true;
		}

		$this->started = true;
		$this->sessionHandler = new CookieSessionHandler($this->lifetime);
		$data = $this->getSessionHandler()->read(self::COOKIE_NAME);
		$this->hash = $this->hashData($data);
		$this->sessionData = unserialize($data, ['allowed_classes' => false]) ?: [];
		if (!isset($this->sessionData['_id']))
		{
			$this->sessionData['_id'] = Random::getString(32, true);
		}
		$this->id = $this->sessionData['_id'];

		return true;
	}

	public function regenerateId(): bool
	{
		return true;
	}

	public function destroy()
	{
		if ($this->isActive())
		{
			$this->clear();
		}
	}

	public function save()
	{
		$this->refineReferencesBeforeSave();
		$data = serialize($this->sessionData);

		if ($this->hashData($data) !== $this->hash)
		{
			$this->getSessionHandler()->write(self::COOKIE_NAME, $data);
		}
		$this->started = false;
	}

	public function clear()
	{
		$this->sessionData = [];
		$this->nullPointers = [];
	}

	public function isStarted()
	{
		return (bool)$this->started;
	}
}