<?php

namespace Bitrix\Im\Update;
use Bitrix\Im\Chat;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;

final class OpenLinesIndex extends Stepper
{
	public const OPTION_NAME = "im_index_open_lines";
	public const LIMIT = 500;
	protected static $moduleId = "im";

	public function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}
		$return = false;

		$params = Option::get(self::$moduleId, self::OPTION_NAME, "");
		$params = ($params !== "" ? @unserialize($params, ['allowed_classes' => false]) : []);
		$params = (is_array($params) ? $params : []);

		if (empty($params))
		{
			$lastIdQuery =
				ChatTable::query()
					->addSelect('ID')
					->where('TYPE', Chat::TYPE_OPEN_LINE)
					->addOrder('ID', 'DESC')
					->setLimit(1)
					->fetch()
			;
			$params = [
				"lastId" => (int)$lastIdQuery['ID'] + 1,
				"number" => 0,
				"count" => ChatTable::getCount([
					'=TYPE' => [Chat::TYPE_OPEN_LINE],
				]),
			];
		}

		if ($params["count"] > 0)
		{
			$query =
				ChatTable::query()
					->setSelect(['ID', 'ENTITY_TYPE'])
					->where('ID', '<', $params['lastId'])
					->where('TYPE', Chat::TYPE_OPEN_LINE)
					->addOrder('ID', 'DESC')
					->setLimit(self::LIMIT)
			;

			$found = false;
			foreach ($query->exec() as $row)
			{
				\CIMChat::index($row['ID']);

				$params["lastId"] = $row['ID'];
				$found = true;
			}

			if ($found)
			{
				Option::set(self::$moduleId, self::OPTION_NAME, serialize($params));
				$return = true;
			}
			else
			{
				Option::delete(self::$moduleId, ["name" => self::OPTION_NAME]);
			}
		}

		return $return;
	}
}