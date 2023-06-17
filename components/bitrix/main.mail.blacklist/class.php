<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\PageNavigation;
use \Bitrix\Main\UI\Filter\DateType;
use \Bitrix\Main\Engine\Contract\Controllerable;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Error;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class MainMailBlacklistComponent extends CBitrixComponent implements Controllerable
{
	const DEFAULT_NAV_KEY = "main-blacklist-page";
	const DEFAULT_PAGE_SIZE = 10;
	const DEFAULT_FILTER_ID = "MAIN_MAIL_BLACKLIST_FILTER";
	const DEFAULT_GRID_ID = "MAIN_MAIL_BLACKLIST_GRID";
	const ERROR_ACCESS_DENIED = "Access Denied";

	/** @var ErrorCollection $errors */
	protected $errors;
	/* signed params*/
	protected function listKeysSignedParameters()
	{
		return [
			"HAS_ACCESS",
		];
	}

	public function executeComponent()
	{
		if(!$this->prepareParams())
		{
			$this->printErrors();
			return;
		}
		$this->prepareResult();
		$this->includeComponentTemplate();
	}

	/*prepare component params*/
	public function prepareParams()
	{
		$this->errors = new ErrorCollection();
		$this->arParams['SET_TITLE'] = (($this->arParams['SET_TITLE'] && $this->arParams['SET_TITLE'] !== 'N')? true : false);
		$this->prepareAccessParams();
		$this->prepareGridParams();
		$this->prepareFilterParams();
		$this->prepareNavigationParams();
		return $this->checkAccess();

	}
	protected function prepareNavigationParams()
	{
		$this->arParams["PAGE_SIZE"] =
			isset($this->arParams["PAGE_SIZE"]) && is_int($this->arParams["PAGE_SIZE"]) && $this->arParams["PAGE_SIZE"] > 0
				? $this->arParams["PAGE_SIZE"]
				: self::DEFAULT_PAGE_SIZE
		;
		$this->arParams["NAVIGATION_KEY"] = $this->arParams["NAVIGATION_KEY"] ?? self::DEFAULT_NAV_KEY;
	}
	protected function prepareFilterParams()
	{
		$this->arParams["FILTER_ID"] = $this->arParams["FILTER_ID"] ?? self::DEFAULT_FILTER_ID;
		$this->arParams["FILTERS"] = $this->arParams["FILTERS"] ?? $this->getFilters();
	}
	protected function prepareGridParams()
	{
		$this->arParams["GRID_ID"] = $this->arParams["GRID_ID"] ?? self::DEFAULT_GRID_ID;
		$this->arParams["COLUMNS"] = $this->arParams["COLUMNS"] ?? $this->getGridColumns();
	}
	protected function prepareNavigation()
	{
		if(!isset($this->arResult["NAVIGATION_OBJECT"]))
		{
			$this->arResult["NAVIGATION_OBJECT"] = new PageNavigation($this->arParams["NAVIGATION_KEY"]);
			$this->arResult["NAVIGATION_OBJECT"]->setPageSize($this->arParams["PAGE_SIZE"])->allowAllRecords(true)->initFromUri();
		}
		return $this->arResult["NAVIGATION_OBJECT"];
	}
	protected function prepareAccessParams()
	{
		$this->arParams["HAS_ACCESS"] = (bool)( \Bitrix\Main\Loader::includeModule("bitrix24") &&
			\CBitrix24::IsPortalAdmin(\Bitrix\Main\Engine\CurrentUser::get()->getId())
			|| \Bitrix\Main\Engine\CurrentUser::get()->isAdmin());
	}

	/* end prepare_component_params*/
	protected function checkAccess()
	{
		if (!$this->arParams["HAS_ACCESS"])
		{
			$this->errors->setError(new Error(self::ERROR_ACCESS_DENIED));
		}
		return $this->arParams["HAS_ACCESS"];
	}


	/* data options */
	protected function getDataFields()
	{
		return ["ID","CODE","DATE_INSERT"];
	}
	protected function getDataFilters()
	{
		$filterOptions = new FilterOptions($this->arParams["FILTER_ID"]);
		$requestFilter = $filterOptions->getFilter($this->arParams["FILTERS"]);
		$searchString = trim($filterOptions->getSearchString());

		$filter = [];
		if ($searchString)
		{
			$filter["CODE"] = "%" . $searchString . "%";
		}
		if(isset($requestFilter["DATE_INSERT_from"]) && $requestFilter["DATE_INSERT_from"])
		{
			try
			{
				$filter[">=DATE_INSERT"] = new Main\Type\DateTime($requestFilter["DATE_INSERT_from"]);
			}
			catch (\Exception $e)
			{
				unset($filter[">=DATE_INSERT"]);
			}
		}
		if (isset($requestFilter["DATE_INSERT_to"]) && $requestFilter["DATE_INSERT_to"])
		{
			try
			{
				$filter["<=DATE_INSERT"] = new Main\Type\DateTime($requestFilter["DATE_INSERT_to"]);
			}
			catch (\Exception $e)
			{
				unset($filter["<=DATE_INSERT"]);
			}
		}
		return $filter;
	}
	protected function getOrder()
	{
		$gridOptions = new GridOptions($this->arParams["GRID_ID"]);
		$sorting = $gridOptions->getSorting(["sort" => $this->getGridDefaultSort()]);

		$field = key($sorting["sort"]);
		$order = mb_strtoupper(current($sorting["sort"])) === "ASC" ? "ASC" : "DESC";
		if (!in_array($field, $this->getGridSortList()))
		{
			return $this->getGridDefaultSort();
		}
		return array($field => $order);

	}
	protected function getFilters()
	{
		return array(
			array(
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage("MAIN_MAIL_BLACKLIST_PERIOD"),
				"default" => true,
				"type" => "date",
				"time"=>false,
				"exclude"=>[
					DateType::NEXT_MONTH,
					DateType::NEXT_WEEK,
					DateType::RANGE,
					DateType::NEXT_DAYS,
					DateType::TOMORROW,
				]
			),
		);
	}
	protected function getOffset()
	{
		return $this->prepareNavigation()->getOffset();
	}
	protected function getLimit()
	{
		return $this->prepareNavigation()->getLimit();
	}
	/* end data options */

	/*prepare_data*/
	protected function prepareResult()
	{
		if($this->request->isPost() && check_bitrix_sessid())
		{
			$this->preparePost();
		}

		$this->prepareNavigation();

		$result = Bitrix\Main\Mail\Internal\BlacklistTable::getList([
			"select"=> $this->getDataFields(),
			"filter"=> $this->getDataFilters(),
			"limit"=>  $this->getLimit(),
			"offset"=> $this->getOffset(),
			"order"=>  $this->getOrder(),
			"count_total" => true,
		]);

		$this->arResult["BLACKLIST"] = $result->fetchAll();
		$this->arResult["TOTAL_COUNT"] = $result->getCount();
		$this->arResult["NAVIGATION_OBJECT"]->setRecordCount($this->arResult["TOTAL_COUNT"]);
	}

	/*grid options */
	protected function getGridColumns()
	{

		return array(
			array(
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			),
			array(
				"id" => "CODE",
				"name" => Loc::getMessage("MAIN_MAIL_BLACKLIST_COLUMN_CODE"),
				"sort" => "CODE",
				"default" => true
			),
			array(
				"id" => "DATE_INSERT",
				"name" =>  Loc::getMessage("MAIN_MAIL_BLACKLIST_COLUMN_DATE"),
				"sort" => "DATE_INSERT",
				"default" => true
			),
		);
	}
	protected function getGridDefaultSort()
	{
		return array("ID" => "DESC");
	}
	protected function getGridSortList()
	{
		$result = [];
		foreach ($this->getGridColumns() as $column)
		{
			if (!isset($column["sort"]) || !$column["sort"])
			{
				continue;
			}
			$result[] = $column["sort"];
		}
		return $result;
	}
	/* end_grid_options */

	protected function executeDelete(array $ids)
	{
		$result = false;
		if($this->checkAccess())
		{
			$items = Main\Mail\Internal\BlacklistTable::getList([
				"select"=>["ID"],
				"filter" => [
					"@ID" => $ids,
				],
			])->fetchAll();

			foreach ($items as $item)
			{
				Main\Mail\Internal\BlacklistTable::delete($item["ID"]);
			}
			$result = true;
		}
		return $result;
	}
	protected function preparePost()
	{
		$ids = $this->request->get("ID");
		$action = $this->request->get("action_button_" . $this->arParams["GRID_ID"]);
		switch ($action)
		{
			case "delete":
				if(is_array($ids) && $ids)
				{
					$this->executeDelete($ids);
				}
				break;

			default:
				break;
		}
	}

	/* Controllerable */
	public function configureActions()
	{
		return [];
	}
	public function removeAction($id)
	{
		$result = false;
		$this->prepareAccessParams();
		if(intval($id) > 0 && check_bitrix_sessid())
		{
			$result = $this->executeDelete([intval($id)]);
		}
		return $result;

	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}
}

