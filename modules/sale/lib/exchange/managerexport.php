<?php

namespace Bitrix\Sale\Exchange;



use Bitrix\Sale\Exchange\Internals\LoggerDiag;

final class ManagerExport extends ManagerBase
{
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
}