<?
namespace Bitrix\Im\Update;

use Bitrix\Im\Chat;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

final class ChatActiveUserCount extends Stepper
{
	private const OPTION_NAME = "im_chat_active_user_counter";

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
				"count" => ChatTable::getCount(),
			];
		}

		if ($params["count"] > 0)
		{
			$result["steps"] = "";
			$result["count"] = $params["count"];

			$ids = ChatTable::getList(
				[
					'order' => ['ID' => 'ASC'],
					'filter' => [
						'>ID' => $params["lastId"],
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
					UPDATE b_im_chat C
					SET C.USER_COUNT = (
					    SELECT COUNT(1)
					    FROM b_im_relation R
					    LEFT JOIN b_user U ON R.USER_ID = U.ID
					    WHERE R.CHAT_ID = C.ID AND U.ACTIVE = 'Y'
					)
					WHERE C.ID IN (" .$implodedIds. ")
					ORDER BY C.ID ASC
					LIMIT 100
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