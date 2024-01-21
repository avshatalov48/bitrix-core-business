<?php
namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Component\PresetHandler;
use Bitrix\Catalog\Component\UseStore;
use Bitrix\Catalog\Product\Store\CostPriceCalculator;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

final class Config extends \Bitrix\Main\Engine\Controller
{
	public const QUANTITY_INCONSISTENCY_EXISTS = 'QUANTITY_INCONSISTENCY_EXISTS';
	public const CONDUCTED_DOCUMENTS_EXIST = 'CONDUCTED_DOCUMENTS_EXIST';

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
			$name === strtolower('onceInventoryManagementY')
			|| $name === strtolower('onceInventoryManagementN')
			|| $name === strtolower('inventoryManagementN')
			|| $name === strtolower('inventoryManagementY')
			|| $name === strtolower('inventoryManagementYAndResetQuantity')
			|| $name === strtolower('inventoryManagementYAndResetQuantityWithDocuments')
			|| $name === strtolower('unRegisterOnProlog')
		)
		{
			$r = $this->checkModifyPermissionEntity($name, $arguments);
		}
		else if (
			$name === strtolower('isUsedInventoryManagement')
			|| $name === strtolower('conductedDocumentsExist')
			|| $name === strtolower('checkEnablingConditions')
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

	public function inventoryManagementNAction(): void
	{
		if (!UseStore::disable())
		{
			$this->addInventoryManagementDisableError();
		}
	}

	public function inventoryManagementYAndResetQuantityWithDocumentsAction(string $costPriceCalculationMethod): void
	{
		if (
			UseStore::isPlanRestricted()
			|| !UseStore::enable()
		)
		{
			$this->addInventoryManagementEnableError();

			return;
		}

		CostPriceCalculator::setMethod($costPriceCalculationMethod);

		UseStore::resetDocuments();
	}

	public function inventoryManagementYAndResetQuantityAction(): void
	{
		if (
			UseStore::isPlanRestricted()
			|| !UseStore::enable()
		)
		{
			$this->addInventoryManagementEnableError();
		}
	}

	public function inventoryManagementYAction(string $costPriceCalculationMethod): void
	{
		if (
			UseStore::isPlanRestricted()
			|| !UseStore::enableWithoutResetting()
		)
		{
			$this->addInventoryManagementEnableError();
		}

		CostPriceCalculator::setMethod($costPriceCalculationMethod);
	}

	public function unRegisterOnPrologAction(): bool
	{
		return PresetHandler::unRegister();
	}

	public function conductedDocumentsExistAction(): bool
	{
		return UseStore::conductedDocumentsExist();
	}

	public function checkEnablingConditionsAction(): array
	{
		$result = [];

		if (UseStore::doNonEmptyProductsExist())
		{
			$result[] = self::QUANTITY_INCONSISTENCY_EXISTS;
		}

		if (UseStore::conductedDocumentsExist())
		{
			$result[] = self::CONDUCTED_DOCUMENTS_EXIST;
		}

		return $result;
	}

	private function addInventoryManagementEnableError(): void
	{
		$this->addError(
			new Error(
				Loc::getMessage('CATALOG_CONTROLLER_CONFIG_INVENTORY_MANAGEMENT_ENABLE_DEFAULT_ERROR')
			)
		);
	}

	private function addInventoryManagementDisableError(): void
	{
		$this->addError(
			new Error(
				Loc::getMessage('CATALOG_CONTROLLER_CONFIG_INVENTORY_MANAGEMENT_DISABLE_DEFAULT_ERROR')
			)
		);
	}
}
