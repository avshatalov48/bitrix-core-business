<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Sender\Access;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\BaseAccessController;
use Bitrix\Main\Access\Event\EventDictionary;
use Bitrix\Main\Access\User\AccessibleUser;
use Bitrix\Sender\Access\Exception\UnknownActionException;
use Bitrix\Sender\Access\Model\UserModel;

class AccessController extends BaseAccessController
{
	private const BASE_RULE      = 'Base';

	/**
	 * Checking access rights by action
	 * @param string $action
	 * @param AccessibleItem|null $item
	 * @param null $params
	 *
	 * @return bool
	 * @throws UnknownActionException
	 */
	public function check(string $action, AccessibleItem $item = null, $params = null): bool
	{
		$ruleName = $this->getRuleName($action);

		if (!$ruleName || !class_exists($ruleName))
		{
			throw new UnknownActionException('Unknown action '.$action);
		}

		$event    = $this->sendEvent(EventDictionary::EVENT_ON_BEFORE_CHECK, $action, null, $params);
		$isAccess = $event->isAccess();

		if (!is_null($isAccess))
		{
			return $isAccess;
		}

		$params['action'] = $action;
		$isAccess = (new $ruleName($this))->execute(null, $params);

		if($isAccess)
		{
			return $isAccess;
		}

		$event = $this->sendEvent(EventDictionary::EVENT_ON_AFTER_CHECK,  $action,null, $params, $isAccess);

		$isAccess = $event->isAccess() ?? $isAccess;

		return $isAccess;
	}

	public function isAdmin()
	{
		return $this->user->isAdmin();
	}

	protected function getRuleName(string $actionCode): ?string
	{
		$actionName = ActionDictionary::getActionName($actionCode);
		if (!$actionName)
		{
			return null;
		}

		$action = explode('_', $actionName);
		$action = array_map(
			function($el)
			{
				return ucfirst(mb_strtolower($el));
			}, $action
		);

		$ruleClass = $this->getRuleNamespace() .implode($action).self::RULE_SUFFIX;


		if(class_exists($ruleClass))
		{
			return $ruleClass;
		}

		return $this->getRuleNamespace().self::BASE_RULE.self::RULE_SUFFIX;
	}

	protected function loadItem(int $itemId = null): ?AccessibleItem
	{
		return null;
	}

	protected function loadUser(int $userId): AccessibleUser
	{
		return UserModel::createFromId($userId);
	}
}