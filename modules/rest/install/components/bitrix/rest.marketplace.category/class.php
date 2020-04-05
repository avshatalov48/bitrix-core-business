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

class CRestMarketplaceCategoryComponent extends \CBitrixComponent
{
	private $curPage = "";
	private $titleName = "";
	private $items = array();
	private $topItems = array();
	private $newItems = array();

	public function onPrepareComponentParams($arParams)
	{
		$arParams['SET_TITLE'] = isset($arParams['SET_TITLE']) ? $arParams['SET_TITLE'] : 'Y';
		$arParams['SHOW_FILTER'] = isset($arParams['SHOW_FILTER']) ? $arParams['SHOW_FILTER'] : 'Y';
		$arParams['NO_BACKGROUND'] = isset($arParams['NO_BACKGROUND']) ? $arParams['NO_BACKGROUND'] : 'Y';

		return parent::onPrepareComponentParams($arParams);
	}

	private function prepareUiFilter()
	{
		$categoryList = \Bitrix\Rest\Marketplace\Client::getCategories();
		$categoryItems = array();

		if (isset($this->arParams['CATEGORY']))
		{
			foreach ($categoryList as $id => $category)
			{
				if ($category["CODE"] == $this->arParams['CATEGORY'])
				{
					$categoryItems[""] = $category["NAME"];
					//$categoryItems[$category["CODE"]] = $category["NAME"];
					break;
				}
			}
		}
		else
		{
			$categoryItems = array(
				"all" => Loc::getMessage("MARKETPLACE_ALL_APPS")
			);
			foreach ($categoryList as $id => $category)
			{
				$categoryItems[$category["CODE"]] = $category["NAME"];
			}
		}

		$this->arResult["FILTER"]["FILTER"] = array(
			array(
				"id"      => "CATEGORY",
				"name"    => Loc::getMessage("MARKETPLACE_FILTER_CATEGORY"),
				"type"    => "list",
				"items"   => $categoryItems,
				"default" => true,
				"required" => isset($this->arParams['CATEGORY']) ? true : false,
				"strict" => isset($this->arParams['CATEGORY']) ? true : false
			),
			array(
				"id"    => "PAID",
				"name"  => Loc::getMessage("MARKETPLACE_FILTER_PAID"),
				"type"  => "list",
				"default" => true,
				"items" => array(
					"Y" => Loc::getMessage("MARKETPLACE_FILTER_PAID"),
					"N" => Loc::getMessage("MARKETPLACE_FILTER_FREE")
				)
			),
			array(
				"id"    => "INSTALLS",
				"name"  => Loc::getMessage("MARKETPLACE_FILTER_INSTALLS"),
				"type"  => "list",
				"default" => true,
				"items" => array(
					"100" => "1-100",
					"500" => "100-500",
					"5000" => "500-5000",
					"10000" => "5000-10000",
					"10000+" => Loc::getMessage("MARKETPLACE_FILTER_INSTALLS_10000"),
				)
			),
			array(
				"id"    => "HIDDEN_BUY",
				"name"  => Loc::getMessage("MARKETPLACE_FILTER_HIDDEN_BUY"),
				"type"  => "checkbox",
				"default" => true
			),
			array(
				"id"    => "PRICE",
				"name"  => Loc::getMessage("MARKETPLACE_FILTER_PRICE"),
				"type"  => "number",
				"default" => true
			),
			array(
				"id"    => "MOBILE_COMPATIBLE",
				"name"  => Loc::getMessage("MARKETPLACE_FILTER_MOBILE_COMPATIBLE"),
				"type"  => "checkbox",
				"default" => true
			),

			array(
				"id"    => "DATE",
				"name"  => Loc::getMessage("MARKETPLACE_FILTER_DATE_PUBLIC"),
				"type"  => "date"
			),
		);
	}

	private function prepareUiFilterPresets()
	{
		\Bitrix\Main\UI\Filter\Options::calcDates(
			'DATE',
			array('DATE_datesel' => \Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS),
			$sevenDayBefore
		);

		$this->arResult["FILTER"]["FILTER_PRESETS"] = array(
			"new" => array(
				"name" => Loc::getMessage("MARKETPLACE_APP_NEW"),
				'default' => false,
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
			$filterQuery["free"] = $filterData["PAID"] == "Y" ? "N" : "Y";
		}
		if (isset($filterData["MOBILE_COMPATIBLE"]))
		{
			$filterQuery["mobile_compatible"] = $filterData["MOBILE_COMPATIBLE"] == "Y" ? "Y" : "N";
		}

		return $filterQuery;
	}

	private function getItemsByTag()
	{
		if (isset($_GET['placement']))
		{
			$tag = \Bitrix\Rest\Marketplace\Client::getTagByPlacement($_GET['placement']);
		}
		else
		{
			$tag = $this->arParams['TAG'];
		}

		if(count($tag) > 0)
		{
			$this->items = \Bitrix\Rest\Marketplace\Client::getByTag($tag, $this->curPage);
		}
	}

	private function getItems()
	{
		$this->arResult["FILTRED"] = false;

		if (isset($this->arParams['CATEGORY']))
		{
			$this->arResult["FILTER"]["FILTER_ID"] = "marketplace_list_".$this->arParams['CATEGORY'];
			$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->arResult["FILTER"]["FILTER_ID"]);
			$filterData = $filterOptions->getFilter();
			$filterData["CATEGORY"] = $this->arParams['CATEGORY'];
		}
		else
		{
			$this->arResult["FILTER"]["FILTER_ID"] = "marketplace_list";
			$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->arResult["FILTER"]["FILTER_ID"]);
			$filterData = $filterOptions->getFilter();
		}

		if (!empty($filterData))
		{
			$this->arResult["FILTRED"] = true;
		}

		if ($this->arResult["FILTRED"])
		{
			$filterQuery = $this->getFilterQuery($filterData);
			$this->items = \Bitrix\Rest\Marketplace\Client::filterApp($filterQuery, $this->curPage);
		}
		else
		{
			$this->topItems = \Bitrix\Rest\Marketplace\Client::getTop(\Bitrix\Rest\Marketplace\Transport::METHOD_GET_BEST, array("onPageSize" => 8));
			$this->newItems = \Bitrix\Rest\Marketplace\Client::getTop(\Bitrix\Rest\Marketplace\Transport::METHOD_GET_LAST, array("onPageSize" => 8));
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
			$listAppCode[] = $item['CODE'];
		}

		if(count($listAppCode) > 0)
		{
			$dbRes = \Bitrix\Rest\AppTable::getList(array(
						'filter' => array(
							'@CODE' => $listAppCode,
							'=ACTIVE' => \Bitrix\Rest\AppTable::ACTIVE
						),
						'select' => array('CODE')
					));
			while($installedApp = $dbRes->fetch())
			{
				$installedItems[] = $installedApp['CODE'];
			}
		}

		foreach($items as $key => $app)
		{
			$items[$key]["URL"] = str_replace(
				array("#app#"),
				array(urlencode($app['CODE'])),
				$this->arParams['DETAIL_URL_TPL']
			);

			$items[$key]["INSTALLED"] = in_array($app['CODE'], $installedItems) ? "Y" : "N";

			if (is_array($app["PRICE"]) && !empty($app["PRICE"][1]))
				$items[$key]["PRICE"] = Loc::getMessage("MARKETPLACE_APP_PRICE", array("#PRICE#" => $app["PRICE"][1]));
			else
				$items[$key]["PRICE"] = Loc::getMessage("MARKETPLACE_APP_FREE");
		}
	}

	public function executeComponent()
	{
		global $APPLICATION;

		$nav = new \Bitrix\Main\UI\PageNavigation("nav-apps");
		$nav->allowAllRecords(false)
			->setPageSize(20)
			->initFromUri();

		$this->curPage = $nav->getCurrentPage();
		$this->titleName = Loc::getMessage("MARKETPLACE_ALL_APPS");

		$this->arResult["AJAX_MODE"] = false;
		if (isset($_POST["action"]) && $_POST["action"] == "setFilter" && check_bitrix_sessid())
		{
			$this->arResult["AJAX_MODE"] = true;
		}

		if (isset($_GET['placement']) || isset($this->arParams['TAG']) && is_array($this->arParams['TAG'])) //by tag
		{
			$this->getItemsByTag();

			$this->arParams['SHOW_FILTER'] = "N";
			$this->titleName = Loc::getMessage('MARKETPLACE_CAT_PLACEMENT');
		}
		else
		{
			$this->prepareUiFilter();
			$this->prepareUiFilterPresets();
			$this->getItems();
		}

		if(!empty($this->items))
		{
			$this->arResult["ITEMS"] = $this->items["ITEMS"];

			if(is_array($this->arResult['ITEMS']))
			{
				$this->prepareItems($this->arResult["ITEMS"]);
			}

			$nav->setRecordCount(intval($this->items["PAGES"]) * 20);

			$this->arResult['NAV'] = $nav;
			$this->arResult["PAGE_COUNT"] = $nav->getPageCount();
			$this->arResult["PAGE_SIZE"] = $nav->getPageSize();
			$this->arResult["CURRENT_PAGE"] = $nav->getCurrentPage();

			if (isset($_REQUEST["nav-apps"]) && check_bitrix_sessid())
			{
				$APPLICATION->RestartBuffer();
				echo \Bitrix\Main\Web\Json::encode($this->arResult["ITEMS"]);
				CMain::FinalActions();
				die();
			}
		}

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

		if($this->arParams['SET_TITLE'] !== 'N')
		{
			$APPLICATION->SetTitle(htmlspecialcharsbx($this->titleName));
		}

		\CJSCore::Init(array("marketplace"));
		$this->includeComponentTemplate();
	}
}

