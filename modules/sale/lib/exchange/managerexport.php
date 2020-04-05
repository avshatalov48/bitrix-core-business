<?php

namespace Bitrix\Sale\Exchange;



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
		// TODO: Implement create() method.
	}
}