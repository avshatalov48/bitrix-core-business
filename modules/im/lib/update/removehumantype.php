<?php

namespace Bitrix\Im\Update;

use Bitrix\Im\Model\BotTable;
use Bitrix\Im\V2\Entity\User\Data\BotData;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;

class RemoveHumanType extends Stepper
{
	public const OPTION_NAME = 'remove_human_type_stepper_params';
	private const MODULE_ID = 'im';

	private int $queryCount = 50;

	function execute(array &$result)
	{
		if (!Loader::includeModule(self::MODULE_ID))
		{
			return false;
		}

		$params = Option::get(self::MODULE_ID, self::OPTION_NAME, "");
		$params = ($params !== "" ? @unserialize($params, ['allowed_classes' => false]) : []);
		$params = (is_array($params) ? $params : []);

		if (empty($params))
		{
			$lastId = 0;
		}

		$lastId = $lastId ?? $params['lastId'];

		$query = BotTable::query()
			->setSelect(['BOT_ID', 'TYPE'])
			->setLimit($this->queryCount)
			->setOrder('BOT_ID', 'ASC')
			->where('BOT_ID' ,'>', $lastId)
			->exec()
		;

		$found = false;
		$botIds = [];
		while ($row = $query->fetch())
		{
			$lastId = (int)$row['BOT_ID'];
			$params['lastId'] = $lastId;

			if ($row['TYPE'] === 'H')
			{
				$botIds[] = $lastId;
			}

			$found = true;
		}

		if (!empty($botIds))
		{
			BotTable::updateByFilter(
				[
					'=BOT_ID' => $botIds,
					'=TYPE' => 'H',
				],
				['TYPE' => 'B']
			);
		}

		foreach ($botIds as $botId)
		{
			BotData::cleanCache($botId);
		}

		if ($found)
		{
			Option::set(self::MODULE_ID, self::OPTION_NAME, serialize($params));

			return true;
		}

		Option::delete(self::MODULE_ID, ["name" => self::OPTION_NAME]);

		\Bitrix\Im\Bot::clearCache();

		return false;
	}
}