<?
namespace Bitrix\Main\Update;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

final class UserSelectorStepper extends Stepper
{
	const OPTION_NAME = "main_index_user_selector";
	protected static $moduleId = "main";

	/**
	 * @inheritdoc
	 */
	public function execute(array &$result)
	{
		$return = false;

		$params = Option::get(self::$moduleId, self::OPTION_NAME, "");
		$params = ($params !== "" ? @unserialize($params) : array());
		$params = (is_array($params) ? $params : array());
		if (empty($params))
		{
			$params = array(
				"lastId" => 0,
				"number" => 0,
				"count" => \Bitrix\Main\UserTable::getCount(),
			);
		}

		if ($params["count"] > 0)
		{
			$result["title"] = Loc::getMessage("MAIN_UPDATE_USER_INDEX_SELECTOR");
			$result["progress"] = 1;
			$result["steps"] = "";
			$result["count"] = $params["count"];

			$cursor = \Bitrix\Main\UserTable::getList(array(
				'order' => array('ID' => 'ASC'),
				'filter' => array(
					'>ID' => $params["lastId"],
				),
				'select' => array('ID'),
				'offset' => 0,
				'limit' => 100
			));

			$found = false;
			while ($row = $cursor->fetch())
			{
				\Bitrix\Main\UserTable::indexRecordSelector($row['ID']);

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
				Option::set("main", "user_selector_content_indexed", "Y");
			}
		}
		return $return;
	}
}
?>