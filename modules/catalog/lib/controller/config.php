<?php
namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Component\StoreMaster;
use Bitrix\Catalog\Component\UseStore;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

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
		if($r->isSuccess())
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
	protected function checkPermission($name, $arguments=[])
	{
		$name = strtolower($name);

		if(
			$name == strtolower('onceInventoryManagementY')
			|| $name == strtolower('onceInventoryManagementN')
			|| $name == strtolower('inventoryManagementN')
			|| $name == strtolower('inventoryManagementYAndResetQuantity')
			|| $name == strtolower('inventoryManagementInstallPreset')
		)
		{
			$r = $this->checkModifyPermissionEntity($name, $arguments);
		}
		else if(
			$name == strtolower('isUsedInventoryManagement')
			|| $name == strtolower('conductedDocumentsExist')
		)
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
	protected function checkReadPermissionEntity($name, $arguments=[])
	{
		$r = new Result();
		if (!CurrentUser::get()->canDoOperation(Controller::CATALOG_READ))
		{
			$r->addError(new Error('Access denied!', 200040300010));
		}
		return $r;
	}

	protected function checkModifyPermissionEntity($name, $arguments=[]): Result
	{
		$r = new Result();
		if (!CurrentUser::get()->canDoOperation(Controller::CATALOG_STORE))
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
	protected function checkPermissionEntity($name, $arguments=[])
	{
		throw new NotImplementedException('Check permission entity. The method '.$name.' is not implemented.');
	}

	public function onceInventoryManagementYAction(): bool
	{
		return UseStore::enableOnec();
	}

	public function onceInventoryManagementNAction(): bool
	{
		return UseStore::disableOnec();
	}

	public function isUsedInventoryManagementAction(): bool
	{
		return UseStore::isUsed();
	}

	public function inventoryManagementNAction(): bool
	{
		$result = UseStore::disable();
		StoreMaster::setIsUsed();
		UseStore::resetPreset();

		return $result;
	}

	public function inventoryManagementYAndResetQuantityAction($preset): bool
	{
		if (UseStore::isPlanRestricted())
		{
			return false;
		}

		UseStore::enable();
		StoreMaster::setIsUsed();
		UseStore::installPreset($preset);

		return true;
	}

	public function inventoryManagementInstallPresetAction($preset): bool
	{
		UseStore::installPreset($preset);

		return true;
	}

	public function conductedDocumentsExistAction(): bool
	{
		return UseStore::conductedDocumentsExist();
	}
}
