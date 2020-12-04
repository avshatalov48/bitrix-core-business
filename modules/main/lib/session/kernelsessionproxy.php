<?php

namespace Bitrix\Main\Session;

final class KernelSessionProxy extends KernelSession
{
	/** @var Session */
	protected $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function isActive(): bool
	{
		return $this->session->isActive();
	}

	public function getId(): string
	{
		return $this->session->getId();
	}

	public function setId($id)
	{
		$this->session->setId($id);
	}

	public function getName(): string
	{
		return $this->session->getName();
	}

	public function setName($name)
	{
		$this->session->setName($name);
	}

	public function start(): bool
	{
		return $this->session->start();
	}

	public function regenerateId(): bool
	{
		return $this->session->regenerateId();
	}

	public function destroy()
	{
		$this->session->destroy();
	}

	public function save()
	{
		$this->session->save();
	}

	public function has($name)
	{
		return $this->session->has($name);
	}

	public function &get($name)
	{
		return $this->session->get($name);
	}

	public function set($name, $value)
	{
		$this->session->set($name, $value);
	}

	public function remove($name)
	{
		$this->session->remove($name);
	}

	public function delete($name)
	{
		$this->session->remove($name);
	}

	public function clear()
	{
		$this->session->clear();
	}

	public function isStarted()
	{
		return $this->session->isStarted();
	}

	public function offsetExists($offset)
	{
		return $this->session->offsetExists($offset);
	}

	public function offsetSet($offset, $value)
	{
		$this->session->offsetSet($offset, $value);
	}
}