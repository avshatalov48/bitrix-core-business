<?
namespace Bitrix\Wiki\Update;

use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Socialnetwork\Item\LogIndex;
use \Bitrix\Socialnetwork\LogCommentTable;
use \Bitrix\Socialnetwork\LogIndexTable;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

final class LivefeedIndexComment extends Stepper
{
	protected static $moduleId = "wiki";

	public function execute(array &$result)
	{
		if (!(
			Loader::includeModule("forum")
			&& Loader::includeModule("socialnetwork")
			&& Option::get('wiki', 'needLivefeedIndexComment', 'Y') == 'Y'
		))
		{
			return false;
		}

		$return = false;

		$params = Option::get("wiki", "livefeedindexcomment", "");
		$params = ($params !== "" ? @unserialize($params) : array());
		$params = (is_array($params) ? $params : array());
		if (empty($params))
		{
			$params = array(
				"lastId" => 0,
				"number" => 0,
				"count" => LogCommentTable::getCount(
					array(
						'=EVENT_ID' => "wiki_comment",
						'!SOURCE_ID' => false
					)
				)
			);
		}

		if ($params["count"] > 0)
		{
			$result["title"] = Loc::getMessage("FUPD_LF_WIKI_COMMENT_EVENT_INDEX_TITLE");
			$result["progress"] = 1;
			$result["steps"] = "";
			$result["count"] = $params["count"];

			$res = LogCommentTable::getList(array(
				'order' => array('ID' => 'ASC'),
				'filter' => array(
					'>ID' => $params["lastId"],
					'=EVENT_ID' => "wiki_comment",
					'!SOURCE_ID' => false
				),
				'select' => array('ID', 'EVENT_ID', 'SOURCE_ID'),
				'offset' => 0,
				'limit' => 100
			));

			$found = false;
			while ($record = $res->fetch())
			{
				LogIndex::setIndex(array(
					'itemType' => LogIndexTable::ITEM_TYPE_COMMENT,
					'itemId' => $record['ID'],
					'fields' => $record
				));

				$params["lastId"] = $record['ID'];
				$params["number"]++;
				$found = true;
			}

			if ($found)
			{
				Option::set("wiki", "livefeedindexcomment", serialize($params));
				$return = true;
			}

			$result["progress"] = intval($params["number"] * 100/ $params["count"]);
			$result["steps"] = $params["number"];

			if ($found === false)
			{
				Option::delete("wiki", array("name" => "livefeedindexcomment"));
				Option::set('wiki', 'needLivefeedIndexComment', 'N');
			}
		}
		return $return;
	}
}
?>