<?
namespace Bitrix\Im\Update;

use Bitrix\Im\Model\RelationTable;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

final class ChatStartingCount extends Stepper
{
	private const OPTION_NAME = "im_chat_starting_counter";

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

		$return = false;

		$params = Option::get(self::$moduleId, self::OPTION_NAME, "");
		$params = ($params !== "" ? @unserialize($params, ["allowed_classes" => false]) : []);
		$params = (is_array($params) ? $params : []);
		if (empty($params))
		{
			$params = [
				"lastId" => 0,
				"number" => 0,
				"count" => RelationTable::getCount(),
			];
		}

		if ($params["count"] > 0)
		{
			$result["steps"] = "";
			$result["count"] = $params["count"];

			$ids = RelationTable::getList(
				[
					'order' => ['ID' => 'ASC'],
					'filter' => [
						'>ID' => $params["lastId"],
						'>START_ID' => 0
					],
					'select' => ['ID'],
					'offset' => 0,
					'limit' => 100
				]
			)->fetchAll();

			$ids = array_map(function($item){
				return $item['ID'];
			}, $ids);

			$idsCount = count($ids);
			if ($idsCount > 0)
			{
				$params["lastId"] = $ids[$idsCount - 1];
				$params["number"] += $idsCount;

				$implodedIds = implode(',', $ids);
				$DB->Query("
					UPDATE b_im_relation R
					INNER JOIN b_im_chat C ON R.CHAT_ID = C.ID
					SET R.START_COUNTER = (
					    SELECT COUNT(1)
					    FROM b_im_message M
					    WHERE M.CHAT_ID = R.CHAT_ID AND M.ID < R.START_ID
					)
					WHERE R.ID IN (" .$implodedIds. ")
				");

				Option::set(self::$moduleId, self::OPTION_NAME, serialize($params));
				$return = true;
			}
			else
			{
				Option::delete(self::$moduleId, ["name" => self::OPTION_NAME]);
			}

			$result["steps"] = $params["number"];
		}

		return $return;
	}
}