<?
namespace Bitrix\Main\Composite\Internals;

use Bitrix\Main\Composite\Helper;
use Bitrix\Main\Composite\Internals\Model\PageTable;
use Bitrix\Main\Type\DateTime;

class PageManager
{
	public static function register($cacheKey, array $params = array())
	{
		$options = Helper::getOptions();
		if ($options["WRITE_STATISTIC"] === "N")
		{
			return null;
		}

		if (!is_string($cacheKey) || mb_strlen($cacheKey) < 1)
		{
			return null;
		}

		$pageTitle = $params["TITLE"] ?? $GLOBALS["APPLICATION"]->GetTitle();
		$pageTitle = mb_substr($pageTitle, 0, 250);

		$pageHost = isset($params["HOST"]) && mb_strlen($params["HOST"]) ? $params["HOST"] : Helper::getHttpHost();
		$pageHost = mb_substr($pageHost, 0, 100);

		$pageUri = isset($params["URI"]) && mb_strlen($params["URI"]) ? $params["URI"] : Helper::getRequestUri();
		$pageUri = mb_substr($pageUri, 0, 2000);

		$pageSize = isset($params["SIZE"]) ? intval($params["SIZE"]) : 0;

		$data = array(
			"TITLE" => $pageTitle,
			"URI" => $pageUri,
			"HOST" => $pageHost
		);

		$GLOBALS["DB"]->StartUsingMasterOnly();

		$page = static::getByCacheKey($cacheKey);
		$result = null;
		if ($page)
		{
			$data["LAST_VIEWED"] = new DateTime();
			$data["VIEWS"] = $page["VIEWS"] + 1;

			if (isset($params["CHANGED"]) && $params["CHANGED"] === true)
			{
				$data["CHANGED"] = new DateTime();
				$data["REWRITES"] = $page["REWRITES"] + 1;
				$data["SIZE"] = $pageSize;
			}

			$result = PageTable::update($page["ID"], $data);
		}
		else
		{
			$data["SIZE"] = $pageSize;
			$data["CACHE_KEY"] = $cacheKey;
			$result = PageTable::add($data);
		}

		$GLOBALS["DB"]->StopUsingMasterOnly();

		return $result !== null ? $result->getId() : null;
	}

	public static function getByCacheKey($cacheKey)
	{
		$records = PageTable::getList(array(
			"filter" => array(
				"=CACHE_KEY" => $cacheKey
			),
			"order" => array(
				"ID" => "ASC"
			)
	  	));

		$result = null;
		while ($record = $records->fetch())
		{
			if ($result === null)
			{
				$result = $record;
			}
			else
			{
				//delete duplicate records just in case
				PageTable::delete($record["ID"]);
			}
		}

		return $result;
	}

	public static function deleteByCacheKey($cacheKey)
	{
		$records = PageTable::getList(array(
			"select" => array("ID"),
			"filter" => array(
				"=CACHE_KEY" => $cacheKey
			)
	  	));

		$result = null;
		while ($record = $records->fetch())
		{
			$result = PageTable::delete($record["ID"]);
		}

		return $result;
	}
}