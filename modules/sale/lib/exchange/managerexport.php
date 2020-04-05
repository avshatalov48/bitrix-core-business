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
	 * @param $typeId
	 * @return ImportBase
	 */
	static public function create($typeId)
	{
		$config = static::getImportByType($typeId);

		$entity = Entity\EntityImportFactory::create($typeId);

		$entity->loadSettings($config->settings);
		$entity->loadLogger($config->logger);

		return $entity;
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