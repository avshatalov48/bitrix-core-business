<?php

namespace Bitrix\Main\Session;

interface SessionInterface
{
	public function start();

	public function destroy();

	public function getId();

	public function setId($id);

	public function regenerateId(): bool;

	public function getName();

	public function setName($name);

	public function save();

	public function has($name);

	public function get($name);

	public function set($name, $value);

	public function remove($name);

	public function clear();

	public function isStarted();

	public function isAccessible();

	public function getSessionHandler(): ?\SessionHandlerInterface;
}