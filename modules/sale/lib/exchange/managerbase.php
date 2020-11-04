<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\Exchange\Internals\ExchangeLogTable;
use Bitrix\Sale\Exchange\Internals\LoggerDiag;
use Bitrix\Sale\Exchange\Logger\Exchange;
use Bitrix\Sale\Exchange\ProviderType\ProviderType;

abstract class ManagerBase
{
	const EXCHANGE_DIRECTION_IMPORT = 'I';
	const EXCHANGE_DIRECTION_EXPORT = 'E';

	protected static $instance = null;
	/** @var ISettingsImport|ISettingsExport $settings */
	protected $settings = null;
	/** @var LoggerDiag $logger */
	protected $logger = null;

	/**
	 * @return string
	 * @throws NotImplementedException
	 */
	static public function getDirectionType()
	{
		throw new NotImplementedException('The method is not implemented.');
	}

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
	 * Get import by Type ID.
	 * @param $typeId
	 * @return null|static
	 * @throws ArgumentOutOfRangeException
	 */
	static protected function getImportByType($typeId)
	{
		static::IsDefinedTypeId($typeId);

		$import = static::getInstance();
		return isset($import[$typeId]) ? $import[$typeId] : null;
	}

	/**
	 * @param ImportBase $entity
	 * @throws NotImplementedException
	 */
	static public function configure(ImportBase $entity)
	{
		throw new NotImplementedException('The method is not implemented.');
	}

	/**
	 * @throws ArgumentOutOfRangeException
	 * @throws NotImplementedException
	 */
	static public function deleteLoggingDate()
	{
		(new Exchange(Logger\ProviderType::ONEC_NAME))
			->deleteOldRecords(static::getDirectionType(), LoggerDiag::getInterval());
	}

	static protected function IsDefinedTypeId($typeId)
	{
		if(!is_int($typeId))
		{
			$typeId = (int)$typeId;
		}

		if(!EntityType::IsDefined($typeId))
		{
			throw new ArgumentOutOfRangeException('Is not defined', EntityType::FIRST, EntityType::LAST);
		}
	}
}