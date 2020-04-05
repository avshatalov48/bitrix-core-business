<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

if(!Loader::includeModule("rest"))
{
	return;
}

class CRestMarketplaceCategoryComponent extends \CBitrixComponent  implements \Bitrix\Main\Engine\Contract\Controllerable
{
	private $curPage = "";
	private $titleName = "";
	private $items = array();
	private $topItems = array();
	private $newItems = array();
	private $ajaxMode = false;

	public function onPrepareComponentParams($arParams)
	{
		$arParams["SET_TITLE"] = isset($arParams["SET_TITLE"]) ? $arParams["SET_TITLE"] : "Y";
		$arParams["NO_BACKGROUND"] = isset($arParams["NO_BACKGROUND"]) ? $arParams["NO_BACKGROUND"] : "Y";

		return parent::onPrepareComponentParams($arParams);
	}

	private function prepareUiFilter()
	{
		$categoryList = \Bitrix\Rest\Marketplace\Client::getCategories();
		$this->arResult["CATEGORIES"] = $categoryList = (is_array($categoryList) ? $categoryList : []);
		$categoryItems = array(
			"all" => Loc::getMessage("MARKETPLACE_ALL_APPS")
		);
		foreach ($categoryList as $id => $category)
		{
			$categoryItems[$category["CODE"]] = $category["NAME"];
		}
		$this->arResult["FILTER"] = [
			"FILTER_ID" => "marketplace_list".($this->arParams['FILTER_ID']?: ''),
			"FILTER"  => [
				[
					"id"      => "CATEGORY",
					"name"    => Loc::getMessage("MARKETPLACE_FILTER_CATEGORY"),
					"type"    => "list",
					"items"   => $categoryItems,
					"default" => true,
					"required" => isset($this->arParams["CATEGORY"]) ? true : false,
					"strict" => isset($this->arParams["CATEGORY"]) ? true : false
				],
				[
					"id"    => "PAID",
					"name"  => Loc::getMessage("MARKETPLACE_FILTER_PAID"),
					"type"  => "list",
					"default" => true,
					"items" => [
						"Y" => Loc::getMessage("MARKETPLACE_FILTER_PAID"),
						"N" => Loc::getMessage("MARKETPLACE_FILTER_FREE"),
						"BY_SUBSCRIPTION" => Loc::getMessage("MARKETPLACE_FILTER_BY_SUBSCRIPTION")
					]
				],
				[
					"id"    => "INSTALLS",
					"name"  => Loc::getMessage("MARKETPLACE_FILTER_INSTALLS"),
					"type"  => "list",
					"default" => true,
					"items" => [
						"100" => "1-100",
						"500" => "100-500",
						"5000" => "500-5000",
						"10000" => "5000-10000",
						"10000+" => Loc::getMessage("MARKETPLACE_FILTER_INSTALLS_10000"),
					]
				],
				[
					"id"    => "HIDDEN_BUY",
					"name"  => Loc::getMessage("MARKETPLACE_FILTER_HIDDEN_BUY"),
					"type"  => "checkbox",
					"default" => true
				],
				[
					"id"    => "PRICE",
					"name"  => Loc::getMessage("MARKETPLACE_FILTER_PRICE"),
					"type"  => "number",
					"default" => true
				],
				[
					"id"    => "MOBILE_COMPATIBLE",
					"name"  => Loc::getMessage("MARKETPLACE_FILTER_MOBILE_COMPATIBLE"),
					"type"  => "checkbox",
					"default" => true
				],
				[
					"id"    => "DATE",
					"name"  => Loc::getMessage("MARKETPLACE_FILTER_DATE_PUBLIC"),
					"type"  => "date"
				],
			],
			"DATA" => []
		];
	}

	private function prepareUiFilterPresets()
	{
		\Bitrix\Main\UI\Filter\Options::calcDates(
			"DATE",
			array("DATE_datesel" => \Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS),
			$sevenDayBefore
		);

		$this->arResult["FILTER"]["FILTER_PRESETS"] = array(
			"new" => array(
				"name" => Loc::getMessage("MARKETPLACE_APP_NEW"),
				"default" => false,
				"fields" => $sevenDayBefore
			)
		);
	}

	private function getFilterQuery($filterData)
	{
		global $DB;

		$filterQuery = array();

		if (isset($filterData["FIND"]) && !empty($filterData["FIND"]))
		{
			$filterQuery["q"] = trim($filterData["FIND"]);
		}
		if (isset($filterData["CATEGORY"]))
		{
			$filterQuery["category"] = $filterData["CATEGORY"];
		}
		if (isset($filterData["HIDDEN_BUY"]))
		{
			$filterQuery["hidden_buy"] = $filterData["HIDDEN_BUY"] == "Y" ? "Y" : "N";
		}
		if (isset($filterData["INSTALLS"]))
		{
			switch ($filterData["INSTALLS"])
			{
				case "100":
					$filterQuery["installs_from"] = 1;
					$filterQuery["installs_to"] = 100;
					break;
				case "500":
					$filterQuery["installs_from"] = 100;
					$filterQuery["installs_to"] = 500;
					break;
				case "5000":
					$filterQuery["installs_from"] = 500;
					$filterQuery["installs_to"] = 5000;
					break;
				case "10000":
					$filterQuery["installs_from"] = 5000;
					$filterQuery["installs_to"] = 10000;
					break;
				case "10000+":
					$filterQuery["installs_from"] = 10000;
					break;
			}
		}
		if (isset($filterData["PRICE_numsel"]))
		{
			$filterQuery["price_from"] = $filterData["PRICE_from"];
			$filterQuery["price_to"] = $filterData["PRICE_to"];
		}
		if (isset($filterData["DATE_datesel"]))
		{
			$currentFormat = \CSite::GetDateFormat();
			$filterQuery["date_public_from"] = $DB->FormatDate($filterData["DATE_from"], $currentFormat, "DD.MM.YYYY HH:MI:SS");
			$filterQuery["date_public_to"] = $DB->FormatDate($filterData["DATE_to"], $currentFormat, "DD.MM.YYYY HH:MI:SS");
		}
		if (isset($filterData["PAID"]))
		{
			if ($filterData["PAID"] === "BY_SUBSCRIPTION")
			{
				$filterQuery["by_subscription"] = "Y";
			}
			else
			{
				$filterQuery["free"] = $filterData["PAID"] == "Y" ? "N" : "Y";
			}
		}
		if (isset($filterData["MOBILE_COMPATIBLE"]))
		{
			$filterQuery["mobile_compatible"] = $filterData["MOBILE_COMPATIBLE"] == "Y" ? "Y" : "N";
		}

		return $filterQuery;
	}

	private function getItemsByTag($tag)
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->arResult["FILTER"]["FILTER_ID"]);
		$filterOptions->setFilterSettings(
			\Bitrix\Main\UI\Filter\Options::TMP_FILTER,
			[
				"fields" => [
					"CATEGORY" => "all" // because ["clear_filter" => "Y"] does not work
				]
			],
			true,
			false);
		$filterOptions->save();

		if(count($tag) > 0)
		{
			if(isset($this->arParams['SHOW_LAST_BLOCK']) && $this->arParams['SHOW_LAST_BLOCK'] == 'Y')
			{
				$count = (isset($this->arParams['BLOCK_COUNT']) && $this->arParams['BLOCK_COUNT'] > 0)
					? intVal($this->arParams['BLOCK_COUNT']) : 4;
				$this->items = \Bitrix\Rest\Marketplace\Client::getLastByTag($tag, $count);
			}
			else
			{
				$count = (isset($this->arParams['BLOCK_COUNT']) && $this->arParams['BLOCK_COUNT'] > 0)
					? intVal($this->arParams['BLOCK_COUNT']) : 18;
				$this->items = \Bitrix\Rest\Marketplace\Client::getByTag($tag, $this->curPage, $count);
			}

			//tmp
			if(empty($this->items['ITEMS']) && in_array('configuration', $tag))
			{
				if(Loader::includeModule('bitrix24') && CBitrix24::getLicensePrefix() === 'ru')
				{
					$filterQuery = [
						'category' => 'vertical_solutions'
					];
					$this->items = \Bitrix\Rest\Marketplace\Client::filterApp($filterQuery, $this->curPage);
					if(!empty($this->items['ITEMS']))
					{
						$this->items['ITEMS'] = array_slice($this->items['ITEMS'], 0, $count);
					}
				}
				else
				{
					$tag = [ 'crm' ];
					$this->items = \Bitrix\Rest\Marketplace\Client::getLastByTag($tag, $count);
				}

				if(is_array($this->items['ITEMS']))
				{
					$this->arResult['SHOW_COMING_SOON'] = 'Y';
					foreach ($this->items['ITEMS'] as $key => $item)
					{
						$this->items['ITEMS'][$key]['INFO_HELPER_CODE'] = 'extensions_coming_soon';
					}
				}
			}
		}
	}

	private function getItems()
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->arResult["FILTER"]["FILTER_ID"]);
		if (isset($this->arParams["CATEGORY"]))
		{
			$filterOptions->setFilterSettings(
				\Bitrix\Main\UI\Filter\Options::TMP_FILTER,
				[
					"fields" => [
						"CATEGORY" => $this->arParams["CATEGORY"]
					]
				],
				true,
				false);
			$filterOptions->save();
		}
		$filterData = $filterOptions->getFilter();

		if (!empty($filterData))
		{
			$this->arResult["FILTER"]["DATA"] = $filterData;
			$filterQuery = $this->getFilterQuery($filterData);
			$this->items = \Bitrix\Rest\Marketplace\Client::filterApp($filterQuery, $this->curPage);
		}
		else
		{
			$count = (isset($this->arParams['BLOCK_COUNT']) && $this->arParams['BLOCK_COUNT'] > 0)
				? intVal($this->arParams['BLOCK_COUNT']) : 8;
			$this->topItems = \Bitrix\Rest\Marketplace\Client::getTop(\Bitrix\Rest\Marketplace\Transport::METHOD_GET_BEST, array("onPageSize" => $count));
			$this->newItems = \Bitrix\Rest\Marketplace\Client::getTop(\Bitrix\Rest\Marketplace\Transport::METHOD_GET_LAST, array("onPageSize" => $count));
		}
	}

	private function prepareItems(&$items)
	{
		if (!is_array($items) || empty($items))
			return;

		$listAppCode = array();
		$installedItems = array();

		foreach($items as $item)
		{
			$listAppCode[] = $item["CODE"];
		}

		if(count($listAppCode) > 0)
		{
			$dbRes = \Bitrix\Rest\AppTable::getList(array(
						"filter" => array(
							"@CODE" => $listAppCode,
							"=ACTIVE" => \Bitrix\Rest\AppTable::ACTIVE
						),
						"select" => array("CODE")
					));
			while($installedApp = $dbRes->fetch())
			{
				$installedItems[] = $installedApp["CODE"];
			}
		}

		foreach($items as $key => $app)
		{
			$items[$key]["URL"] = str_replace(
				array("#app#",'#ID#'),
				urlencode($app["CODE"]),
				$this->arParams["DETAIL_URL_TPL"]
			);

			$items[$key]["INSTALLED"] = in_array($app["CODE"], $installedItems) ? "Y" : "N";

			if ($app["FREE"] == "N" && is_array($app["PRICE"]) && !empty($app["PRICE"][1]))
				$items[$key]["PRICE"] = Loc::getMessage("MARKETPLACE_APP_PRICE", array("#PRICE#" => $app["PRICE"][1]));
			else if ($app["FREE"] == "N" && $app["BY_SUBSCRIPTION"] == "Y")
				$items[$key]["PRICE"] = Loc::getMessage("MARKETPLACE_APP_SUBSCRIPTION");
			else
				$items[$key]["PRICE"] = Loc::getMessage("MARKETPLACE_APP_FREE");
		}
	}

	private function collectItems()
	{
		$nav = new \Bitrix\Main\UI\PageNavigation("nav");
		$nav->allowAllRecords(false)
			->setPageSize(20)
			->initFromUri();
		$this->curPage = $nav->getCurrentPage();

		$this->arResult["AJAX_MODE"] = ($this->ajaxMode === true);

		$this->prepareUiFilter();
		$this->prepareUiFilterPresets();

		if ($this->arResult["AJAX_MODE"] === false && $this->request->get("placement"))  //by placement
		{
			$this->arParams["PLACEMENT"] = $this->request->get("placement");
			$this->arParams["TAG"] = \Bitrix\Rest\Marketplace\Client::getTagByPlacement($this->request->get("placement"));
		}
		if (isset($this->arParams["TAG"]) && is_array($this->arParams["TAG"])) //by tag
		{
			$this->getItemsByTag($this->arParams["TAG"]);
		}
		else
		{
			unset($this->arParams["TAG"]);
			$this->getItems();
		}

		if(!empty($this->items))
		{
			$this->arResult["ITEMS"] = $this->items["ITEMS"];

			if(is_array($this->arResult["ITEMS"]))
			{
				$this->prepareItems($this->arResult["ITEMS"]);
			}

			$nav->setRecordCount(intval($this->items["PAGES"]) * 20);

			$this->arResult["NAV"] = $nav;
			$this->arResult["PAGE_COUNT"] = $nav->getPageCount();
			$this->arResult["PAGE_SIZE"] = $nav->getPageSize();
			$this->arResult["CURRENT_PAGE"] = $nav->getCurrentPage();
		}
	}
	public function executeComponent()
	{
		$this->titleName = Loc::getMessage("MARKETPLACE_ALL_APPS");

		$this->collectItems();

		if (!empty($this->topItems))
		{
			$this->prepareItems($this->topItems["ITEMS"]["PAID"]);
			$this->arResult["TOP_ITEMS_PAID"] = $this->topItems["ITEMS"]["PAID"];
			$this->prepareItems($this->topItems["ITEMS"]["FREE"]);
			$this->arResult["TOP_ITEMS_FREE"] = $this->topItems["ITEMS"]["FREE"];
		}

		if (!empty($this->newItems))
		{
			$this->prepareItems($this->newItems["ITEMS"]["PAID"]);
			$this->arResult["NEW_ITEMS_PAID"] = $this->newItems["ITEMS"]["PAID"];
			$this->prepareItems($this->newItems["ITEMS"]["FREE"]);
			$this->arResult["NEW_ITEMS_FREE"] = $this->newItems["ITEMS"]["FREE"];
		}

		if($this->arParams["SET_TITLE"] !== "N")
		{
			global $APPLICATION;
			$APPLICATION->SetTitle(htmlspecialcharsbx($this->titleName));
		}

		\CJSCore::Init(array("marketplace"));
		$this->includeComponentTemplate();
	}
	public function configureActions()
	{
		return [];
	}

	protected function listKeysSignedParameters()
	{
		return [
			"DETAIL_URL_TPL"
		];
	}

	public function getPageAction($filterMode = "default", $filterValue = null)
	{
		$this->ajaxMode = true;
		if ($filterMode === "placement")
		{
			$this->arParams["PLACEMENT"] = $filterValue;
			$this->arParams["TAG"] = \Bitrix\Rest\Marketplace\Client::getTagByPlacement($filterValue);
		}
		else if ($filterMode === "tag")
		{
			$this->arParams["TAG"] = $filterValue;
		}

		ob_start();
		$this->executeComponent();
		return ob_get_clean();
	}

	public function getNextPageAction($filterMode = "default", $filterValue = null)
	{
		$this->ajaxMode = true;

		if ($filterMode === "placement")
		{
			$this->arParams["PLACEMENT"] = $filterValue;
			$this->arParams["TAG"] = \Bitrix\Rest\Marketplace\Client::getTagByPlacement($filterValue);
		}
		else if ($filterMode === "tag")
		{
			$this->arParams["TAG"] = $filterValue;
		}

		$this->collectItems();

		return $this->arResult["ITEMS"];
	}
}

