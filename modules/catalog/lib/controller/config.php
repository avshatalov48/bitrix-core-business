<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\Store\EnableWizard\Manager;
use Bitrix\Catalog\Store\EnableWizard\ModeList;
use Bitrix\Catalog\Store\EnableWizard\TariffChecker;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Catalog\Config\Feature;

final class Config extends \Bitrix\Main\Engine\Controller
{
	/**
	 * @param Action $action
	 * @return bool|null
	 * @throws NotImplementedException
	 * @throws SystemException
	 */
	protected function processBeforeAction(Action $action): ?bool
	{
		$r = $this->checkPermission($action->getName(), $action->getArguments());
		if ($r->isSuccess())
		{
			//do nothing
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		return parent::processBeforeAction($action);
	}

	/**
	 * @throws NotImplementedException
	 */
	protected function checkPermission($name, $arguments = [])
	{
		$name = strtolower($name);

		if (
			$name === strtolower('inventoryManagementEnable')
			|| $name === strtolower('inventoryManagementDisable')
			|| $name === strtolower('unRegisterOnProlog')
		)
		{
			$r = $this->checkModifyPermissionEntity($name, $arguments);
		}
		else if ($name === strtolower('isUsedInventoryManagement'))
		{
			$r = $this->checkReadPermissionEntity($name, $arguments);
		}
		else
		{
			$r = $this->checkPermissionEntity($name, $arguments);
		}

		return $r;
	}

	/**
	 * @param $name
	 * @param array $arguments
	 * @return Result
	 */
	protected function checkReadPermissionEntity($name, $arguments = [])
	{
		$r = new Result();
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ))
		{
			$r->addError(new Error('Access denied!', 200040300010));
		}
		return $r;
	}

	protected function checkModifyPermissionEntity($name, $arguments = []): Result
	{
		$r = new Result();
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_VIEW))
		{
			$r->addError(new Error('Access denied!', 200040300011));
		}
		return $r;
	}

	/**
	 * @param $name
	 * @param array $arguments
	 * @throws NotImplementedException
	 */
	protected function checkPermissionEntity($name, $arguments = [])
	{
		throw new NotImplementedException('Check permission entity. The method '.$name.' is not implemented.');
	}

	public function inventoryManagementEnableAction(string $mode, array $options = []): void
	{
		if (
			(
				$mode === ModeList::B24
				&& !Feature::isInventoryManagementEnabled()
			)
			|| (
				$mode === ModeList::ONEC
				&& TariffChecker::isOnecInventoryManagementRestricted()
			)
		)
		{
			$this->addError(
				new Error(
					Loc::getMessage('CATALOG_CONTROLLER_CONFIG_INVENTORY_MANAGEMENT_ENABLE_DEFAULT_ERROR')
				)
			);

			return;
		}

		$enableResult = Manager::enable($mode, $options);
		if (!$enableResult->isSuccess())
		{
			$this->addErrors($enableResult->getErrors());
		}
	}

	public function inventoryManagementDisableAction(): void
	{
		$disableResult = Manager::disable();

		if (!$disableResult->isSuccess())
		{
			$this->addErrors($disableResult->getErrors());
		}
	}

	public function isUsedInventoryManagementAction(): bool
	{
		return State::isUsedInventoryManagement();
	}
}
