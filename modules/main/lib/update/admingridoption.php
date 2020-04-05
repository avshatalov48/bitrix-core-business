<?
namespace Bitrix\Main\Update;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;

/**
 * Class GridOption
 * The class is designed to convert the settings of the old administrative grid into a new one.
 *
 * An example of how this miracle works can be seen here: sale,18.5.7; iblock,18.5.5; catalog,18.5.6;
 *
 * @package Bitrix\Main\Update
 */
class AdminGridOption extends Stepper
{
	protected static $moduleId = "main";

	protected $limit = 100;

	/**
	 * The method records the necessary data for conversion into an option.
	 * @param string $tableId Grid id.
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function setGridToConvert($tableId)
	{
		$listGrid = Option::get(self::$moduleId, "listGridToConvert", "");
		if ($listGrid !== "")
		{
			$listGrid = unserialize($listGrid);
		}
		$listGrid = is_array($listGrid) ? $listGrid : [];

		if (!array_key_exists($tableId, $listGrid))
		{
			$listGrid[$tableId] = [
				"offset" => 0,
				"tableId"=> $tableId,
			];
			Option::set(self::$moduleId, "listGridToConvert", serialize($listGrid));
		}
	}

	public function execute(array &$option)
	{
		$listGrid = Option::get(self::$moduleId, "listGridToConvert", "");
		if ($listGrid !== "" )
		{
			$listGrid = unserialize($listGrid);
		}
		$listGrid = is_array($listGrid) ? $listGrid : [];
		if (empty($listGrid))
		{
			Option::delete(self::$moduleId, ["name" => "listGridToConvert"]);
			$GLOBALS["CACHE_MANAGER"]->cleanDir("user_option");
			return false;
		}

		$connection = Application::getInstance()->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		foreach ($listGrid as $tableId => $table)
		{
			$queryObject = $connection->query("SELECT * FROM `b_user_option` WHERE `CATEGORY` = 'list' AND `NAME` = '".
				$sqlHelper->forSql($table["tableId"])."' ORDER BY ID ASC LIMIT ".$this->limit." OFFSET ".$table["offset"]);
			$selectedRowsCount = $queryObject->getSelectedRowsCount();
			while ($optionOldGrid = $queryObject->fetch())
			{
				$oldGridData = (!empty($optionOldGrid["VALUE"]) ? unserialize($optionOldGrid["VALUE"]) : []);

				if (!$oldGridData)
				{
					continue;
				}

				$queryResultObject = $connection->query(
					"SELECT ID FROM `b_user_option` WHERE `CATEGORY` = 'main.interface.grid' AND `NAME` = '".
					$sqlHelper->forSql($table["tableId"])."' AND `USER_ID` = '".$optionOldGrid["USER_ID"]."'");
				if (!$queryResultObject->fetch())
				{
					if (!array_diff_key(array_flip(["page_size", "by", "order", "columns"]), $oldGridData))
					{
						$gridOptions = new \CGridOptions($tableId);
						$gridOptions->setSorting($oldGridData["by"], $oldGridData["order"]);
						$gridOptions->setColumns($oldGridData["columns"]);
						$options = $gridOptions->getOptions();
						$options["views"]["default"]["page_size"] = intval($oldGridData["page_size"]);
						\CUserOptions::setOption(
							"main.interface.grid", $tableId, $options, false, $optionOldGrid["USER_ID"]);
					}
				}
			}

			if ($selectedRowsCount < $this->limit)
			{
				unset($listGrid[$tableId]);
			}
			else
			{
				$listGrid[$tableId]["offset"] = $listGrid[$tableId]["offset"] + $selectedRowsCount;
			}
		}

		$GLOBALS["CACHE_MANAGER"]->cleanDir("user_option");

		if (!empty($listGrid))
		{
			Option::set(self::$moduleId, "listGridToConvert", serialize($listGrid));
			return true;
		}
		else
		{
			Option::delete(self::$moduleId, ["name" => "listGridToConvert"]);
			return false;
		}
	}
}