<?php

namespace Bitrix\Sale\Exchange;



use Bitrix\Sale\Exchange\Internals\LoggerDiag;

final class ManagerExport extends ManagerBase
{
	const SALE_MODE = 1;
	const B24_MODE = 2;
	const SALE_B24_MODE = 3;
	const B24_SALE_MODE = 4;

	/**
	 * @return string
	 */
	static public function getDirectionType()
	{
		return self::EXCHANGE_DIRECTION_EXPORT;
	}

	/**
	 * @param ImportBase $entity
	 */
	static public function configure(ImportBase $entity)
	{
		$config = static::getImportByType($entity->getOwnerTypeId());

		$entity->loadSettings($config->settings);
		$entity->loadLogger($config->logger);
	}

	/**
	 * Add instance of this manager to collection
	 * @param $typeId
	 * @param ISettingsExport $settings
	 * @return mixed
	 * @internal
	 */
	static public function registerInstance($typeId, ISettingsExport $settings)
	{
		static::IsDefinedTypeId($typeId);

		if(self::$instance[$typeId] === null)
		{
			$manager = new static();
			$manager->settings = $settings;
			$manager->logger = new LoggerDiag();

			self::$instance[$typeId] = $manager;
		}
		return self::$instance[$typeId];
	}

	static public function isSaleMode()
	{
		return true;
	}

	static public function isB24Mode()
	{
		return \CModule::IncludeModule('CRM');
	}

	static public function isSaleB24Mode()
	{
		return \Bitrix\Main\Config\Option::get("sale", "~IS_SALE_CRM_SITE_MASTER_FINISH", "N") == "Y";
	}

	static public function isB24SaleMode()
	{
		return \Bitrix\Main\Config\Option::get("sale", "~IS_SALE_BSM_SITE_MASTER_FINISH", "N")=="Y";
	}

	static public function getMode()
	{
		//B24 -> +BUS.wizard
		if(\Bitrix\Sale\Exchange\ManagerExport::isB24SaleMode())
		{
			return static::B24_SALE_MODE;
		}
		//BUS -> +B24.wizard
		elseif(\Bitrix\Sale\Exchange\ManagerExport::isSaleB24Mode())
		{
			return static::SALE_B24_MODE;
		}
		elseif(\Bitrix\Sale\Exchange\ManagerExport::isB24Mode())
		{
			return static::B24_MODE;
		}
		else
		{
			return static::SALE_MODE;
		}
	}

	static public function isCRMCompatibility()
	{
		//B24 -> +BUS.wizard
		if(static::getMode() ==  static::B24_SALE_MODE)
		{
			return true;
		}
		//BUS -> +B24.wizard
		elseif(static::getMode() ==  static::SALE_B24_MODE)
		{
			return false;
		}
		elseif(static::getMode() ==  static::B24_MODE)
		{
			\CModule::IncludeModule('CRM');
			return true;
		}

		return false;
	}
}