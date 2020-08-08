<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access;

use Bitrix\Main\Access\Event\Event;
use Bitrix\Main\Access\Event\EventDictionary;
use Bitrix\Main\Access\Exception\UnknownActionException;
use Bitrix\Main\Access\User\AccessibleUser;

abstract class BaseAccessController
	implements AccessibleController
{
	protected const RULE_SUFFIX = 'Rule';

	protected static $register = [];

	/* @var AccessibleUser $user */
	protected $user;

	public static function getInstance($userId)
	{
		if (!array_key_exists($userId, static::$register))
		{
			static::$register[static::class][$userId] = new static($userId);
		}
		return static::$register[static::class][$userId];
	}

	public static function can($userId, string $action, $itemId = null, $params = null): bool
	{
		$userId = (int) $userId;
		$itemId = (int) $itemId;

		$controller = static::getInstance($userId);
		return $controller->checkByItemId($action, $itemId, $params);
	}

	public function __construct(int $userId)
	{
		$this->user = $this->loadUser($userId);
	}

	public function getUser(): AccessibleUser
	{
		return $this->user;
	}

	public function checkByItemId(string $action, int $itemId = null, $params = null): bool
	{
		$item = $this->loadItem($itemId);
		return $this->check($action, $item, $params);
	}

	public function check(string $action, AccessibleItem $item = null, $params = null): bool
	{
		$ruleName = $this->getRuleName($action);

		if (!$ruleName || !class_exists($ruleName))
		{
			throw new UnknownActionException('Unknown action '. $action);
		}

		$event = $this->sendEvent(EventDictionary::EVENT_ON_BEFORE_CHECK, $action, $item, $params);
		$isAccess = $event->isAccess();

		if (!is_null($isAccess))
		{
			return $isAccess;
		}

		$isAccess = (new $ruleName($this))->execute($item, $params);

		$event = $this->sendEvent(EventDictionary::EVENT_ON_AFTER_CHECK, $action, $item, $params, $isAccess);

		$isAccess = $event->isAccess() ?? $isAccess;

		return $isAccess;
	}

	/**
	 * @param AccessibleItem $item
	 * @param array $request
	 * 	[
	 * 		actionId => params
	 * 	]
	 * @return array
	 * 	[
	 * 		actionId => true|false
	 * 	]
	 * @throws UnknownActionException
	 */
	public function batchCheck(array $request, AccessibleItem $item): array
	{
		$result = [];
		foreach ($request as $actionId => $params)
		{
			$result[$actionId] = $this->check($actionId, $item, $params);
		}
		return $result;
	}

	abstract protected function loadItem(int $itemId = null): ?AccessibleItem;

	abstract protected function loadUser(int $userId): AccessibleUser;

	protected function getRuleName(string $action): ?string
	{
		$action = explode('_', $action);
		$action = array_map(function($el) {
			return ucfirst(strtolower($el));
		}, $action);
		return $this->getRuleNamespace() . implode($action) . static::RULE_SUFFIX;
	}

	protected function getRuleNamespace(): string
	{
		$class = new \ReflectionClass($this);
		$namespace = $class->getNamespaceName();
		return $namespace.'\\'.static::RULE_SUFFIX.'\\';
	}

	protected function sendEvent(string $eventName, string $action, AccessibleItem $item = null, $params = null, bool
	$isAccess = null)
	{
		$event = new Event(
			static::class,
			$eventName,
			[
				'user' 		=> $this->user,
				'item' 		=> $item,
				'action' 	=> $action,
				'params' 	=> $params,
				'isAccess' 	=> $isAccess
			]
		);
		$event->send();

		return $event;
	}
}