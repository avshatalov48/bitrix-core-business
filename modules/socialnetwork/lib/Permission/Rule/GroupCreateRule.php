<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Permission\Rule;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Main\Authentication\Internal\ModuleGroupTable;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Permission\Model\GroupModel;
use Bitrix\Socialnetwork\Permission\User\UserModel;
use COption;
use CSocNetUser;
use CUser;

class GroupCreateRule extends AbstractRule
{
	protected const MIN_RIGHT = 'D';
	protected const ENOUGH_RIGHT = 'K';

	/** @var GroupAccessController */
	protected $controller;

	/** @var UserModel */
	protected $user;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof GroupModel)
		{
			$this->controller->addError(static::class, 'Wrong instance');

			return false;
		}

		if ($this->user->isCollaber())
		{
			$this->controller->addError(static::class, 'Access denied by collaber role');

			return false;
		}

		if ($this->user->isAdmin())
		{
			return true;
		}

		if (CSocNetUser::IsUserModuleAdmin($this->user->getUserId(), $item->getSiteIds()))
		{
			return true;
		}

		if ($this->hasPermissions($item))
		{
			return true;
		}

		$this->controller->addError(static::class, 'Access denied by permissions');

		return false;
	}

	/**
	 * @see \CAllMain::GetUserRight
	 */
	protected function hasPermissions(GroupModel $item): bool
	{
		$siteIds = $item->getSiteIds();
		$siteIds[] = false;

		$groups = CUser::GetUserGroup($this->user->getUserId());

		$modulePermissions = ModuleGroupTable::query()
			->setSelect(['*'])
			->where('MODULE_ID', 'socialnetwork')
			->where('GROUP.ACTIVE', 'Y')
			->setCacheTtl(86400)
			->cacheJoins(true)
			->fetchAll();

		$right = '';
		foreach ($modulePermissions as $permission)
		{
			// site filter
			// group filter
			// max
			if (
				in_array($permission['SITE_ID'], $siteIds)
				&& in_array($permission['GROUP_ID'], $groups)
				&& $permission['G_ACCESS'] > $right
			)
			{
				$right = $permission['G_ACCESS'];
			}
		}

		if ($right === '')
		{
			$right = COption::GetOptionString('socialnetwork', 'GROUP_DEFAULT_RIGHT', static::MIN_RIGHT);
		}

		return $right >= static::ENOUGH_RIGHT;
	}
}