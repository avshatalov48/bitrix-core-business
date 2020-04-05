<?
namespace Bitrix\Im\Update;

use Bitrix\Im\Chat;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;


Loc::loadMessages(__FILE__);

final class ChatIndex extends Stepper
{
	const OPTION_NAME = "im_index_chat";
	protected static $moduleId = "im";

	/**
	 * @inheritdoc
	 */
	public function execute(array &$result)
	{
		if (!Loader::includeModule(self::$moduleId))
			return false;

		$return = false;

		$params = Option::get(self::$moduleId, self::OPTION_NAME, "");
		$params = ($params !== "" ? @unserialize($params) : array());
		$params = (is_array($params) ? $params : array());
		if (empty($params))
		{
			$params = array(
				"lastId" => 0,
				"number" => 0,
				"count" => ChatTable::getCount(array(
					'=TYPE' => Array(Chat::TYPE_OPEN, Chat::TYPE_GROUP),
				)),
			);
		}

		if ($params["count"] > 0)
		{
			$result["title"] = Loc::getMessage("IM_UPDATE_CHAT_INDEX");
			$result["progress"] = 1;
			$result["steps"] = "";
			$result["count"] = $params["count"];

			$cursor = ChatTable::getList(array(
				'order' => array('ID' => 'ASC'),
				'filter' => array(
					'>ID' => $params["lastId"],
					'=TYPE' => Array(Chat::TYPE_OPEN, Chat::TYPE_GROUP),
				),
				'select' => array('ID', 'ENTITY_TYPE'),
				'offset' => 0,
				'limit' => 500
			));

			$found = false;
			while ($row = $cursor->fetch())
			{
				if ($row['ENTITY_TYPE'] != 'LIVECHAT')
				{
					\CIMChat::index($row['ID']);
				}

				$params["lastId"] = $row['ID'];
				$params["number"]++;
				$found = true;
			}

			if ($found)
			{
				Option::set(self::$moduleId, self::OPTION_NAME, serialize($params));
				$return = true;
			}

			$result["progress"] = intval($params["number"] * 100/ $params["count"]);
			$result["steps"] = $params["number"];

			if ($found === false)
			{
				Option::delete(self::$moduleId, array("name" => self::OPTION_NAME));
			}
		}
		return $return;
	}
}
?>