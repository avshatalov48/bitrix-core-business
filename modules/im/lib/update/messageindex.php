<?
namespace Bitrix\Im\Update;

use Bitrix\Im\Model\MessageTable;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

final class MessageIndex extends Stepper
{
	const OPTION_NAME = "im_index_message";
	const STATUS_ENABLED = 'enabled';
	const STATUS_DISABLED = 'disabled';
	const STATUS_DEFAULT = 'default';

	protected static $moduleId = "im";

	/**
	 * @inheritdoc
	 */
	public function execute(array &$result)
	{
		global $DB;

		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}

		$indexStatus = Option::get(self::$moduleId, self::OPTION_NAME.'_status', self::STATUS_DEFAULT);
		if ($indexStatus === self::STATUS_DEFAULT)
		{
			if (IsModuleInstalled('bitrix24'))
			{
				return false;
			}
		}
		elseif ($indexStatus === self::STATUS_DISABLED)
		{
			return false;
		}
		
		$return = false;

		$params = Option::get(self::$moduleId, self::OPTION_NAME, "");
		$params = ($params !== "" ? @unserialize($params, ["allowed_classes" => false]) : []);
		$params = (is_array($params) ? $params : []);
		if (empty($params))
		{
			$params = [
				"lastId" => 0,
				"number" => 0,
				"count" => MessageTable::getCount(),
			];
		}

		if ($params["count"] > 0)
		{
			$result["title"] = Loc::getMessage("IM_UPDATE_MESSAGE_INDEX");
			$result["progress"] = 1;
			$result["steps"] = "";
			$result["count"] = $params["count"];

			$cursor = MessageTable::getList(
				[
					'order' => ['ID' => 'ASC'],
					'filter' => [
						'>ID' => $params["lastId"],
					],
					'select' => ['ID'],
					'offset' => 0,
					'limit' => 5000
				]
			);

			$found = false;
			while ($row = $cursor->fetch())
			{
				MessageTable::indexRecord($row['ID']);

				$params["lastId"] = $row['ID'];
				$params["number"]++;
				$found = true;
			}

			if ($found)
			{
				Option::set(self::$moduleId, self::OPTION_NAME, serialize($params));
				$return = true;
			}

			$result["progress"] = (int)($params["number"] * 100 / $params["count"]);
			$result["steps"] = $params["number"];

			if ($found === false)
			{
				Option::delete(self::$moduleId, ["name" => self::OPTION_NAME]);

				if ($DB->IndexExists("b_im_message_index", array("SEARCH_CONTENT"), true))
				{
					\Bitrix\Im\Model\MessageIndexTable::getEntity()->enableFullTextIndex('SEARCH_CONTENT');
				}
			}
		}
		elseif ($params["count"] == 0)
		{
			Option::delete(self::$moduleId, ["name" => self::OPTION_NAME]);

			if ($DB->IndexExists("b_im_message_index", array("SEARCH_CONTENT"), true))
			{
				\Bitrix\Im\Model\MessageIndexTable::getEntity()->enableFullTextIndex('SEARCH_CONTENT');
			}
		}

		return $return;
	}
}