<?php
namespace Bitrix\Sale\Exchange;
/** @depricate */

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Sale\Exchange\Internals\LoggerDiag;
use Bitrix\Sale\Exchange\OneC\ImportCollision;
use Bitrix\Sale\Exchange\OneC\ImportCriterionBase;

final class Manager
{
    private static $instance = null;
    /** @var ISettings $settings */
    protected $settings = null;
    /** @var ICollision $collision */
    protected $collision = null;
    /** @var ICriterion $criterion */
    protected $criterion = null;
    /** @var LoggerDiag $logger */
    protected $logger = null;

    /**
     * @return static
     */
    private static function getInstance()
    {
        if(self::$instance === null)
        {
            self::$instance = new static();
        }
        return self::$instance;
    }

	/**
	 * @param $typeId
	 * @return Entity\OrderImport|Entity\PaymentCardImport|Entity\PaymentCashImport|Entity\PaymentCashLessImport|Entity\ShipmentImport|Entity\UserProfileImport|ProfileImport
	 */
	public static function createImport($typeId)
    {
        $config = static::getImportByType($typeId);

        $import = Entity\EntityImportFactory::create($typeId);

        $import->loadSettings($config->settings);
        $import->loadCollision($config->collision);
        $import->loadCriterion($config->criterion);
        $import->loadLogger($config->logger);

        return $import;
    }

    /**
     * Add instance of this manager to collection
     * @param $typeId
     * @param ISettings $settings
     * @param ICollision $collision
     * @param ICriterion $criterion
     * @return mixed
     * @throws ArgumentOutOfRangeException
	 * @internal
     */
	static public function registerInstance($typeId, ISettings $settings, ICollision $collision = null, ICriterion $criterion = null)
    {
        if(!is_int($typeId))
        {
            $typeId = (int)$typeId;
        }

        if(!EntityType::IsDefined($typeId))
        {
            throw new ArgumentOutOfRangeException('Is not defined', EntityType::FIRST, EntityType::LAST);
        }

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
     * Get import by Type ID.
     * @param $typeId
     * @return null|static
     * @throws ArgumentOutOfRangeException
     */
    private static function getImportByType($typeId)
    {
        if(!is_int($typeId))
        {
            $typeId = (int)$typeId;
        }

        if(!EntityType::IsDefined($typeId))
        {
            throw new ArgumentOutOfRangeException('Is not defined', EntityType::FIRST, EntityType::LAST);
        }

        $import = static::getInstance();
        return isset($import[$typeId]) ? $import[$typeId] : null;
    }

	/**
	 * @param $typeId
	 * @return ISettings
	 * @throws ArgumentOutOfRangeException
	 */
	public static function getSettingsByType($typeId)
	{
		if(!is_int($typeId))
		{
			$typeId = (int)$typeId;
		}

		if(!EntityType::IsDefined($typeId))
		{
			throw new ArgumentOutOfRangeException('Is not defined', EntityType::FIRST, EntityType::LAST);
		}

		$config = static::getImportByType($typeId);

		return $config->settings;
	}
}