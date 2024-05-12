<?php

namespace Bitrix\Im\Update;

use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Main\Loader;

class DeleteTsParamAgent
{
	protected static $moduleId = 'im';
	private const LIMIT = 1000;

	public static function deleteTsParamAgent()
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return '';
		}

		$paramIds = self::getParamIds();
		if (empty($paramIds))
		{
			return '';
		}

		MessageParamTable::deleteBatch(['ID' => $paramIds]);

		return __METHOD__ . '();';
	}

	private static function getParamIds(): array
	{
		if (Loader::includeModule('bitrix24'))
		{
			$limit = 100;
		}

		$result = MessageParamTable::query()
			->setSelect(['ID'])
			->where('PARAM_NAME', 'TS')
			->setLimit($limit ?? self::LIMIT)
			->fetchAll()
		;

		if (empty($result))
		{
			return [];
		}

		return array_unique(array_map('intval', array_column($result, 'ID')));
	}
}