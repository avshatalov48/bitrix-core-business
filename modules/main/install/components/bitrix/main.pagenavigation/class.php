<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class PageNavigationComponent extends CBitrixComponent
{
	public function __construct($component = null)
	{
		parent::__construct($component);
	}

	public function onPrepareComponentParams($arParams)
	{
		$arParams["PAGE_WINDOW"] = ((int)$arParams["PAGE_WINDOW"] > 0? (int)$arParams["PAGE_WINDOW"] : 5);
		$arParams["SHOW_ALWAYS"] = ($arParams["SHOW_ALWAYS"] === "Y" || $arParams["SHOW_ALWAYS"] === true? true : false);
		$arParams["SEF_MODE"] = ($arParams["SEF_MODE"] === "Y" || $arParams["SEF_MODE"] === true? true : false);
		$arParams["SHOW_COUNT"] = ($arParams["SHOW_COUNT"] === "N" || $arParams["SHOW_COUNT"] === false? false : true);

		return $arParams;
	}

	public function executeComponent()
	{
		if (!is_object($this->arParams["~NAV_OBJECT"]) || !($this->arParams["~NAV_OBJECT"] instanceof \Bitrix\Main\UI\PageNavigation))
		{
			return;
		}
		/** @var \Bitrix\Main\UI\PageNavigation $nav */
		$nav = $this->arParams["~NAV_OBJECT"];

		$this->arResult["RECORD_COUNT"] = $nav->getRecordCount();
		$this->arResult["PAGE_COUNT"] = $nav->getPageCount();
		$this->arResult["CURRENT_PAGE"] = $nav->getCurrentPage();
		$this->arResult["ALL_RECORDS"] = $nav->allRecordsShown();
		$this->arResult["PAGE_SIZE"] = $nav->getPageSize();
		$this->arResult["PAGE_SIZES"] = $nav->getPageSizes();
		$this->arResult["SHOW_ALL"] = $nav->allRecordsAllowed();
		$this->arResult["ID"] = $nav->getId();
		$this->arResult["REVERSED_PAGES"] = ($nav instanceof \Bitrix\Main\UI\ReversePageNavigation);

		if(!$this->arParams["SHOW_ALWAYS"])
		{
			if(($this->arResult["PAGE_COUNT"] <= 1 && $this->arResult["ALL_RECORDS"] == false))
			{
				return;
			}
		}

		$this->makeUrl();
		$this->calculatePages();
		$this->calculateRecords();

		$this->IncludeComponentTemplate();
	}

	protected function makeUrl()
	{
		/** @var \Bitrix\Main\UI\PageNavigation $nav */
		$nav = $this->arParams["~NAV_OBJECT"];

		if($this->arParams["~BASE_LINK"] <> '')
		{
			$uri = new \Bitrix\Main\Web\Uri($this->arParams["~BASE_LINK"]);
		}
		else
		{
			$uri = new \Bitrix\Main\Web\Uri($this->request->getRequestUri());
			$uri->deleteParams(\Bitrix\Main\HttpRequest::getSystemParameters());
			$nav->clearParams($uri, $this->arParams["SEF_MODE"]);
		}
		$this->arResult["URL"] = $uri->getUri();
		$this->arResult["URL_TEMPLATE"] = $nav->addParams($uri, $this->arParams["SEF_MODE"], "--page--", (count($this->arResult["PAGE_SIZES"]) > 1? "--size--" : null))->getUri();
	}

	public function replaceUrlTemplate($page, $size = "")
	{
		return str_replace(array("--page--", "--size--"), array($page, $size), $this->arResult["URL_TEMPLATE"]);
	}

	protected function calculatePages()
	{
		if ($this->arResult["REVERSED_PAGES"] === true)
		{
			if ($this->arResult["CURRENT_PAGE"] + floor($this->arParams["PAGE_WINDOW"]/2) >= $this->arResult["PAGE_COUNT"])
			{
				$startPage = $this->arResult["PAGE_COUNT"];
			}
			else
			{
				if ($this->arResult["CURRENT_PAGE"] + floor($this->arParams["PAGE_WINDOW"]/2) >= $this->arParams["PAGE_WINDOW"])
				{
					$startPage = $this->arResult["CURRENT_PAGE"] + floor($this->arParams["PAGE_WINDOW"]/2);
				}
				else
				{
					if($this->arResult["PAGE_COUNT"] >= $this->arParams["PAGE_WINDOW"])
					{
						$startPage = $this->arParams["PAGE_WINDOW"];
					}
					else
					{
						$startPage = $this->arResult["PAGE_COUNT"];
					}
				}
			}

			if ($startPage - $this->arParams["PAGE_WINDOW"] >= 0)
			{
				$endPage = $startPage - $this->arParams["PAGE_WINDOW"] + 1;
			}
			else
			{
				$endPage = 1;
			}
		}
		else
		{
			if ($this->arResult["CURRENT_PAGE"] > floor($this->arParams["PAGE_WINDOW"]/2) + 1 && $this->arResult["PAGE_COUNT"] > $this->arParams["PAGE_WINDOW"])
			{
				$startPage = $this->arResult["CURRENT_PAGE"] - floor($this->arParams["PAGE_WINDOW"]/2);
			}
			else
			{
				$startPage = 1;
			}

			if ($this->arResult["CURRENT_PAGE"] <= $this->arResult["PAGE_COUNT"] - floor($this->arParams["PAGE_WINDOW"]/2) && $startPage + $this->arParams["PAGE_WINDOW"]-1 <= $this->arResult["PAGE_COUNT"])
			{
				$endPage = $startPage + $this->arParams["PAGE_WINDOW"] - 1;
			}
			else
			{
				$endPage = $this->arResult["PAGE_COUNT"];
				if($endPage - $this->arParams["PAGE_WINDOW"] + 1 >= 1)
				{
					$startPage = $endPage - $this->arParams["PAGE_WINDOW"] + 1;
				}
			}
		}

		$this->arResult["START_PAGE"] = $startPage;
		$this->arResult["END_PAGE"] = $endPage;
	}

	protected function calculateRecords()
	{
		/** @var \Bitrix\Main\UI\PageNavigation $nav */
		$nav = $this->arParams["~NAV_OBJECT"];

		$this->arResult["FIRST_RECORD"] = $nav->getOffset() + 1;

		if($this->arResult["REVERSED_PAGES"] === true)
		{
			if ($this->arResult["PAGE_COUNT"] == 1)
			{
				$this->arResult["LAST_RECORD"] = $this->arResult["RECORD_COUNT"];
			}
			else
			{
				$this->arResult["LAST_RECORD"] = $this->arResult["FIRST_RECORD"] + $nav->getLimit() - 1;
			}
		}
		else
		{
			if ($this->arResult["CURRENT_PAGE"] == $this->arResult["PAGE_COUNT"])
			{
				$this->arResult["LAST_RECORD"] = $this->arResult["RECORD_COUNT"];
			}
			else
			{
				$this->arResult["LAST_RECORD"] = $this->arResult["FIRST_RECORD"] + $nav->getLimit() - 1;
			}
		}
	}
}
