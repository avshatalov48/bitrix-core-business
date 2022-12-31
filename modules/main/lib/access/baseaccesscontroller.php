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
use Bitrix\Main\Access\Filter\Factory\FilterControllerFactory;
use Bitrix\Main\Access\Filter\FilterFactory;
use Bitrix\Main\Access\Rule\Factory\RuleControllerFactory;
use Bitrix\Main\Access\Rule\RuleFactory;
use Bitrix\Main\Access\User\AccessibleUser;
use Bitrix\Main\Engine\CurrentUser;

abstract class BaseAccessController
	implements AccessibleController
{
	/**
	 * @deprecated use ::$ruleFactory property
	 */
	protected const RULE_SUFFIX = 'Rule';

	protected static $register = [];

	/* @var AccessibleUser $user */
	protected $user;

	protected RuleFactory $ruleFactory;

	protected FilterFactory $filterFactory;

	public static function getInstance($userId)
	{
		if (!isset(static::$register[static::class][$userId]))
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
		$this->ruleFactory = new RuleControllerFactory();
		$this->filterFactory = new FilterControllerFactory();
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
		$rule = $this->ruleFactory->createFromAction($action, $this);
		if (!$rule)
		{
			throw new UnknownActionException($action);
		}

		$event = $this->sendEvent(EventDictionary::EVENT_ON_BEFORE_CHECK, $action, $item, $params);
		$isAccess = $event->isAccess();

		if (!is_null($isAccess))
		{
			return $isAccess;
		}

		$isAccess = $rule->execute($item, $params);

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

	/**
	 * @deprecated use ::$ruleFactory property
	 */
	protected function getRuleName(string $action): ?string
	{
		$action = explode('_', $action);
		$action = array_map(function($el) {
			return ucfirst(strtolower($el));
		}, $action);
		return $this->getRuleNamespace() . implode($action) . static::RULE_SUFFIX;
	}

	/**
	 * @deprecated use ::$ruleFactory property
	 */
	protected function getRuleNamespace(): string
	{
		$class = new \ReflectionClass($this);
		$namespace = $class->getNamespaceName();
		return $namespace.'\\'.static::RULE_SUFFIX.'\\';
	}

	protected function sendEvent(string $eventName, string $action, AccessibleItem $item = null, $params = null, bool $isAccess = null)
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

	/**
	 * @inheritDoc
	 */
	public function getEntityFilter(string $action, string $entityName, $params = null): ?array
	{
		$filter = $this->filterFactory->createFromAction($action, $this);
		if (!$filter)
		{
			return null;
		}

		$params = (array)($params ?? []);
		$params['action'] = $action;

		return $filter->getFilter($entityName, $params);
	}
}
