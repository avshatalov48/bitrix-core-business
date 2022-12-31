<?php
namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Component\PresetHandler;
use Bitrix\Catalog\Component\UseStore;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Error;
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
	protected function checkPermission($name, $arguments=[])
	{
		$name = strtolower($name);

		if(
			$name == strtolower('onceInventoryManagementY')
			|| $name == strtolower('onceInventoryManagementN')
			|| $name == strtolower('inventoryManagementN')
			|| $name == strtolower('inventoryManagementY')
			|| $name == strtolower('inventoryManagementYAndResetQuantity')
			|| $name == strtolower('inventoryManagementYAndResetQuantityWithDocuments')
			|| $name == strtolower('inventoryManagementInstallPreset')
			|| $name == strtolower('unRegisterOnProlog')
		)
		{
			$r = $this->checkModifyPermissionEntity($name, $arguments);
		}
		else if(
			$name == strtolower('isUsedInventoryManagement')
			|| $name == strtolower('conductedDocumentsExist')
			|| $name == strtolower('checkEnablingConditions')
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
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ))
		{
			$r->addError(new Error('Access denied!', 200040300010));
		}
		return $r;
	}

	protected function checkModifyPermissionEntity($name, $arguments=[]): Result
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
		UseStore::resetPreset();

		return $result;
	}

	/**
	 * Enable and reset store documents.
	 *
	 * @param mixed $preset
	 *
	 * @return bool
	 */
	public function inventoryManagementYAndResetQuantityWithDocumentsAction($preset)
	{
		if (UseStore::isPlanRestricted())
		{
			return false;
		}

		if (UseStore::enable())
		{
			UseStore::resetDocuments();
			UseStore::installPreset($preset);
		}

		return true;
	}

	/**
	 * Enable and reset product quantities.
	 *
	 * @param mixed $preset
	 *
	 * @return bool
	 */
	public function inventoryManagementYAndResetQuantityAction($preset): bool
	{
		return UseStore::enableWithPreset($preset);
	}

	/**
	 * Enable without resetting documents or quantities.
	 *
	 * @param mixed $preset
	 *
	 * @return bool
	 */
	public function inventoryManagementYAction($preset): bool
	{
		if (UseStore::isPlanRestricted())
		{
			return false;
		}

		if (UseStore::enableWithoutResetting())
		{
			UseStore::installPreset($preset);
		}

		return true;
	}

	public function inventoryManagementInstallPresetAction($preset): bool
	{
		UseStore::installPreset($preset);

		return true;
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

		if (UseStore::isQuantityInconsistent())
		{
			$result[] = self::QUANTITY_INCONSISTENCY_EXISTS;
		}

		if (UseStore::conductedDocumentsExist())
		{
			$result[] = self::CONDUCTED_DOCUMENTS_EXIST;
		}

		return $result;
	}
}
