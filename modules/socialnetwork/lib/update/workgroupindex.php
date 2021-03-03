<?
namespace Bitrix\Socialnetwork\Update;

use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Socialnetwork\WorkgroupTable;
use \Bitrix\Socialnetwork\Item\Workgroup;

Loc::loadMessages(__FILE__);

final class WorkgroupIndex extends Stepper
{
	protected static $moduleId = "socialnetwork";

	public function execute(array &$result)
	{
		if (!(
			Loader::includeModule("socialnetwork")
			&& Option::get('socialnetwork', 'needWorkgroupIndex', 'Y') == 'Y'
		))
		{
			return false;
		}

		$return = false;

		$params = Option::get("socialnetwork", "workgroupindex", "");
		$params = ($params !== "" ? @unserialize($params, [ 'allowed_classes' => false ]) : array());
		$params = (is_array($params) ? $params : array());
		if (empty($params))
		{
			$params = array(
				"lastId" => 0,
				"number" => 0,
				"count" => WorkgroupTable::getCount()
			);
		}

		if ($params["count"] > 0)
		{
			$result["title"] = Loc::getMessage("FUPD_WORKGROUP_INDEX_TITLE");
			$result["progress"] = 1;
			$result["steps"] = "";
			$result["count"] = $params["count"];

			$res = WorkgroupTable::getList(array(
				'order' => array('ID' => 'ASC'),
				'filter' => array(
					'>ID' => $params["lastId"]
				),
				'select' => array_merge(array('ID'), Workgroup::getContentFieldsList()),
				'offset' => 0,
				'limit' => 100
			));

			$found = false;
			while ($record = $res->fetch())
			{
				Workgroup::setIndex(array(
					'fields' => $record
				));

				$params["lastId"] = $record['ID'];
				$params["number"]++;
				$found = true;
			}

			if ($found)
			{
				Option::set("socialnetwork", "workgroupindex", serialize($params));
				$return = true;
			}

			$result["progress"] = intval($params["number"] * 100/ $params["count"]);
			$result["steps"] = $params["number"];

			if ($found === false)
			{
				Option::delete("socialnetwork", array("name" => "workgroupindex"));
				Option::set('socialnetwork', 'needWorkgroupIndex', 'N');
			}
		}
		return $return;
	}
}
?>