<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Exchange\Internals\ExchangeLogTable;

abstract class ManagerBase
{
	const EXCHANGE_DIRECTION_IMPORT = 'I';
	const EXCHANGE_DIRECTION_EXPORT = 'E';

	/**
	 * @return string
	 */
	abstract static public function getDirectionType();

	/**
	 * @param $typeId
	 * @return ImportBase
	 */
	abstract static public function create($typeId);

	static public function deleteLoggingDate()
	{
		ExchangeLogTable::deleteOldRecords(static::getDirectionType());
	}
}