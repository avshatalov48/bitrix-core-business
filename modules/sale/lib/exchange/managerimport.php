<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Sale\Exchange\Internals\LoggerDiag;
use Bitrix\Sale\Exchange\OneC\ImportCollision;
use Bitrix\Sale\Exchange\OneC\ImportCriterionBase;

final class ManagerImport extends ManagerBase
{
	/** @var ICollision $collision */
	protected $collision = null;
	/** @var ICriterion $criterion */
	protected $criterion = null;

	/**
	 * @return string
	 */
	static public function getDirectionType()
	{
		return self::EXCHANGE_DIRECTION_IMPORT;
	}

	/**
	 * @param ImportBase $entity
	 */
	static public function configure(ImportBase $entity)
	{
		$config = static::getImportByType($entity->getOwnerTypeId());

		$entity->loadSettings($config->settings);
		$entity->loadCollision($config->collision);
		$entity->loadCriterion($config->criterion);
		$entity->loadLogger($config->logger);
	}

	/**
	 * Add instance of this manager to collection
	 * @param $typeId
	 * @param ISettingsImport $settings
	 * @param ICollision $collision
	 * @param ICriterion $criterion
	 * @return mixed
	 * @throws ArgumentOutOfRangeException
	 * @internal
	 */
	static public function registerInstance($typeId, ISettingsImport $settings, ICollision $collision = null, ICriterion $criterion = null)
	{
		static::IsDefinedTypeId($typeId);

		if(self::$instance[$typeId] === null)
		{
			$manager = new static();
			$manager->settings = $settings;
			$manager->collision = $collision !== null ? $collision : new ImportCollision();
			$manager->criterion = $criterion !== null ? $criterion : new ImportCriterionBase();
			$manager->logger = new LoggerDiag();

			self::$instance[$typeId] = $manager;
		}
		return self::$instance[$typeId];
	}

	/**
	 * @param $typeId
	 * @return ISettingsImport
	 * @throws ArgumentOutOfRangeException
	 */
	static public function getSettingsByType($typeId)
	{
		static::IsDefinedTypeId($typeId);

		$config = static::getImportByType($typeId);

		return $config->settings;
	}
}