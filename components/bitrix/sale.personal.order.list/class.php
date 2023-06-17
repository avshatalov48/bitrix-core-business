<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Localization;
use Bitrix\Main\Loader;
use Bitrix\Main\Data;
use Bitrix\Sale;
use Bitrix\Sale\Cashbox\CheckManager;

class CBitrixPersonalOrderListComponent extends CBitrixComponent
{
	public const E_SALE_MODULE_NOT_INSTALLED 		= 10000;
	public const E_CANNOT_COPY_ORDER_NOT_FOUND		= 10001;
	public const E_CANNOT_COPY_CANT_ADD_BASKET		= 10002;
	public const E_CATALOG_MODULE_NOT_INSTALLED	= 10003;
	public const E_NOT_AUTHORIZED					= 10004;

	/**
	 * Fatal error list. Any fatal error makes useless further execution of a component code.
	 * In most cases, there will be only one error in a list according to the scheme "one shot - one dead body"
	 *
	 * @var string[] Array of fatal errors.
	 */

	protected $errorsFatal = array();
	/**
	 * Non-fatal error list. Some non-fatal errors may occur during component execution, so certain functions of the component
	 * may became defunct. Still, user should stay informed.
	 * There may be several non-fatal errors in a list.
	 *
	 * @var string[] Array of non-fatal errors.
	 */
	protected $errorsNonFatal = array();

	/**
	 * Contains some valuable info from $_REQUEST
	 *
	 * @var object request info
	 */
	protected $requestData = array();

	/**
	 * Gathered options that are required
	 *
	 * @var string[] options
	 */
	protected $options = array();

	protected $useIblock = true;

	/**
	 * A value of current date format
	 *
	 * @var string format
	 */
	private $dateFormat = '';

	/**
	 * Filter used when select orders
	 *
	 * @var mixed[] filter
	 */
	protected $filter = array();

	/**
	 * Sort field for query
	 *
	 * @var string field
	 */
	protected $sortBy = false;

	/**
	 * Sort direction for query
	 *
	 * @var string order: asc or desc
	 */
	protected $sortOrder = false;

	/**
	 * @var Sale\Registry registry
	 */
	protected $registry = null;

	protected $dbResult = array();
	private $dbQueryResult = array();

	/**@var Data\Cache $this->currentCache */
	protected $currentCache = null;

	/**
	 * A convert map for method self::formatDate()
	 *
	 * @var string[] keys
	 */
	protected $orderDateFields2Convert = array(
		'DATE_INSERT',
		'DATE_STATUS',
		'PAY_VOUCHER_DATE',
		'DATE_DEDUCTED',
		'DATE_UPDATE',
		'PS_RESPONSE_DATE',
		'DATE_PAY_BEFORE',
		'DATE_BILL',
		'DATE_CANCELED'
	);

	/**
	 * A convert map for method self::formatDate()
	 *
	 * @var string[] keys
	 */
	protected $basketDateFields2Convert = array(
		'DATE_INSERT',
		'DATE_UPDATE'
	);

	public function __construct($component = null)
	{
		parent::__construct($component);

		CPageOption::SetOptionString("main", "nav_page_in_session", "N");

		$this->dateFormat = Main\Context::getCurrent()->getCulture()->getDateTimeFormat();

		Localization\Loc::loadMessages(__FILE__);
	}

	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function checkRequiredModules()
	{
		if (!Loader::includeModule('sale'))
			throw new Main\SystemException(Localization\Loc::getMessage("SPOL_SALE_MODULE_NOT_INSTALL"), self::E_SALE_MODULE_NOT_INSTALLED);

		if (!Loader::includeModule('iblock'))
			$this->useIblock = false;

	}

	/**
	 * Function checks if user is authorized or not. If not, auth form will be shown.
	 * @return void
	 * @throws Main\SystemException
	 */
	protected function checkAuthorized()
	{
		global $USER;
		global $APPLICATION;

		if (!$USER->IsAuthorized())
		{
			$msg = Localization\Loc::getMessage("SPOL_ACCESS_DENIED");

			// for compatibility reasons: by default AuthForm() is shown in class.php, as it used to be.
			// BUT the better way is to show it in template.php, as it required by MVC paradigm
			if(!$this->arParams['AUTH_FORM_IN_TEMPLATE'])
			{
				$APPLICATION->AuthForm($msg, false, false, 'N', false);
			}

			throw new Main\SystemException($msg, self::E_NOT_AUTHORIZED);
		}
	}

	/**
	 * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
	 * @param mixed[] $arParams List of unchecked parameters
	 * @return mixed[] Checked and valid parameters
	 */
	public function onPrepareComponentParams($arParams)
	{
		global $APPLICATION;

		$arParams['CACHE_TIME'] = (int)($arParams['CACHE_TIME'] ?? 3600);
		$arParams['CACHE_GROUPS'] = (string)($arParams['CACHE_GROUPS'] ?? 'Y');

		self::tryParseString($arParams["PATH_TO_DETAIL"], $APPLICATION->GetCurPage()."?"."ID=#ID#");
		self::tryParseString($arParams["PATH_TO_COPY"], $APPLICATION->GetCurPage()."?"."ID=#ID#");
		self::tryParseString($arParams["PATH_TO_CANCEL"], $APPLICATION->GetCurPage()."?"."ID=#ID#");
		self::tryParseString($arParams["PATH_TO_BASKET"], "basket.php");
		self::tryParseString($arParams["PATH_TO_PAYMENT"], "/personal/order/payment/");

		if ($arParams["SAVE_IN_SESSION"] != "N")
			$arParams["SAVE_IN_SESSION"] = "Y";

		if (!is_array($arParams['HISTORIC_STATUSES']) || empty($arParams['HISTORIC_STATUSES']))
			$arParams['HISTORIC_STATUSES'] = array('F');

		$arParams["NAV_TEMPLATE"] = ($arParams["NAV_TEMPLATE"] <> ''? $arParams["NAV_TEMPLATE"] : "");

		self::tryParseInt($arParams["ORDERS_PER_PAGE"], 20);
		self::tryParseString($arParams["ACTIVE_DATE_FORMAT"], "d.m.Y");

		self::tryParseBoolean($arParams['AUTH_FORM_IN_TEMPLATE']);

		if (empty($arParams['REFRESH_PRICES']))
		{
			$arParams['REFRESH_PRICES'] = "N";
		}

		if (empty($arParams['ALLOW_INNER']))
		{
			$arParams['ALLOW_INNER'] = "N";
		}

		if (empty($arParams['ONLY_INNER_FULL']))
		{
			$arParams['ONLY_INNER_FULL'] = "Y";
		}

		if (!CBXFeatures::IsFeatureEnabled('SaleAccounts'))
		{
			$arParams['ALLOW_INNER'] = "N";
		}

		if (empty($arParams['DEFAULT_SORT']))
		{
			$arParams['DEFAULT_SORT'] = 'STATUS';
		}

		return $arParams;
	}

	/**
	 * Function reduces input value to integer type, and, if gets null, passes the default value
	 * @param mixed $fld Field value
	 * @param int $default Default value
	 * @param int $allowZero Allows zero-value of the parameter
	 * @return int Parsed value
	 */
	public static function tryParseInt(&$fld, $default, $allowZero = null)
	{
		$fld = intval($fld);
		if(!$allowZero && !$fld && isset($default))
			$fld = $default;

		return $fld;
	}

	/**
	 * Function processes string value and, if gets null, passes the default value to it
	 * @param mixed $fld Field value
	 * @param string $default Default value
	 * @return string parsed value
	 */
	public static function tryParseString(&$fld, $default)
	{
		$fld = trim((string)$fld);
		if(!mb_strlen($fld) && isset($default))
			$fld = htmlspecialcharsbx($default);

		return $fld;
	}

	/**
	 * Function forces 'Y'/'N' value to boolean
	 * @param mixed $fld Field value
	 * @return string parsed value
	 */
	public static function tryParseBoolean(&$fld)
	{
		$fld = $fld == 'Y';
		return $fld;
	}

	/**
	 * Function sets page title, if required
	 * @return void
	 */
	protected function setTitle()
	{
		global $APPLICATION;

		if ($this->arParams["SET_TITLE"] == 'Y')
			$APPLICATION->SetTitle(Localization\Loc::getMessage("SPOL_DEFAULT_TITLE"));
	}

	/**
	 * Function gets all options required for component
	 * @return void
	 */
	protected function getOptions()
	{
		$this->options['USE_ACCOUNT_NUMBER'] = Sale\Integration\Numerator\NumeratorOrder::isUsedNumeratorForOrder();
	}

	/**
	 * Function processes and corrects $_REQUEST. Everything about $_REQUEST lies here.
	 * @return void
	 */
	protected function processRequest()
	{
		$this->requestData["COPY_ORDER"] = (($_REQUEST["COPY_ORDER"] ?? '') === "Y");
		$this->requestData["ID"] = urldecode(urldecode($this->arParams["ID"]));

		if(($_REQUEST["del_filter"] ?? '') !== '')
		{
			unset($_REQUEST["filter_id"]);
			unset($_REQUEST["filter_date_from"]);
			unset($_REQUEST["filter_date_to"]);
			unset($_REQUEST["filter_status"]);
			unset($_REQUEST["filter_payed"]);
			unset($_REQUEST["filter_canceled"]);
			$_REQUEST["filter_history"] = "Y";
			if($this->arParams["SAVE_IN_SESSION"] == "Y")
			{
				unset($_SESSION["spo_filter_id"]);
				unset($_SESSION["spo_filter_date_from"]);
				unset($_SESSION["spo_filter_date_to"]);
				unset($_SESSION["spo_filter_status"]);
				unset($_SESSION["spo_filter_payed"]);
				unset($_SESSION["spo_filter_canceled"]);
				$_SESSION["spo_filter_history"] = "Y";
			}
		}

		$this->filterRestore();
		$this->filterStore();

		$orderClassName = $this->registry->getOrderClassName();
		$tableFieldNameList = $orderClassName::getAllFields();

		if (isset($_REQUEST["by"]) && (string)$_REQUEST['by'] !== '')
		{
			if (!in_array($_REQUEST['by'], $tableFieldNameList))
				$_REQUEST["by"] = $this->arParams['DEFAULT_SORT'];
		}

		$this->sortBy = (($_REQUEST["by"] ?? '') !== ''? $_REQUEST["by"] : $this->arParams['DEFAULT_SORT']);
		$this->sortOrder = (($_REQUEST["order"] ?? '') === "ASC" ? "ASC": "DESC");

		$this->prepareFilter();
	}

	/**
	 * Read filter from session (or anywhere else), if required
	 * @return void
	 */
	protected function filterRestore()
	{
		if (
			$this->arParams["SAVE_IN_SESSION"] === "Y"
			&& ($_REQUEST["filter"] ?? '') === ''
		)
		{
			$session = Main\Application::getInstance()->getSession();
			$filterId = (int)($session['spo_filter_id'] ?? 0);
			if ($filterId > 0)
			{
				$_REQUEST["filter_id"] = $filterId;
			}
			$filterDateFrom = (string)($session['spo_filter_date_from'] ?? '');
			if ($filterDateFrom !== '')
			{
				$_REQUEST["filter_date_from"] = $filterDateFrom;
			}
			$filterDateTo = (string)($session['spo_filter_date_to'] ?? '');
			if ($filterDateTo !== '')
			{
				$_REQUEST["filter_date_to"] = $filterDateTo;
			}
			$filterStatus = (string)($session['spo_filter_status'] ?? '');
			if ($filterStatus !== '')
			{
				$_REQUEST["filter_status"] = $filterStatus;
			}
			$filterPayed = (string)($session['spo_filter_payed'] ?? '');
			if ($filterPayed !== '')
			{
				$_REQUEST["filter_payed"] = $filterPayed;
			}
			$filterCanceled = (string)($session['spo_filter_canceled'] ?? '');
			if ($filterCanceled !== '')
			{
				$_REQUEST["filter_canceled"] = $filterCanceled;
			}
			$filterHistory = (string)($session['spo_filter_history'] ?? '');
			if ($filterHistory === "Y")
			{
				$_REQUEST["filter_history"] = "Y";
			}
			unset($session);
		}
	}

	/**
	 * Store filter in session (or anywhere else), if required.
	 * @return void
	 */
	protected function filterStore()
	{
		if ($this->arParams["SAVE_IN_SESSION"] === "Y" && ($_REQUEST["filter"] ?? '') !== '')
		{
			$session = Main\Application::getInstance()->getSession();
			$session['spo_filter_id'] = (string)($_REQUEST['filter_id'] ?? '');
			$session['spo_filter_date_from'] = (string)($_REQUEST['filter_date_from'] ?? '');
			$session['spo_filter_date_to'] = (string)($_REQUEST['filter_date_to'] ?? '');
			$session['spo_filter_status'] = (string)($_REQUEST['filter_status'] ?? '');
			$session['spo_filter_payed'] = (string)($_REQUEST['filter_payed'] ?? '');
			$session['spo_filter_history'] = (string)($_REQUEST['filter_history'] ?? '');
			unset($session);
		}
	}

	/**
	 * Creates filter for CSaleOrder::GetList() based on $_REQUEST and other parameters
	 * @return void
	 */
	protected function prepareFilter()
	{
		global $USER;
		global $DB;

		$arFilter = array();
		$arFilter["USER_ID"] = $USER->GetID();
		$arFilter["LID"] = SITE_ID;

		$filterId = (string)($_REQUEST['filter_id'] ?? '');
		if ($filterId !== '')
		{
			if($this->options['USE_ACCOUNT_NUMBER'])
			{
				$arFilter["ACCOUNT_NUMBER"] = $filterId;
			}
			else
			{
				$arFilter["ID"] = (int)$filterId;
			}
		}

		$filterDateFrom = trim((string)($_REQUEST['filter_date_from'] ?? ''));
		if ($filterDateFrom !== '')
		{
			$arFilter[">=DATE_INSERT"] = $filterDateFrom;
		}

		$filterDateTo = trim((string)($_REQUEST['filter_date_to'] ?? ''));
		if ($filterDateTo !== '')
		{
			$arFilter["<=DATE_INSERT"] = $filterDateTo;

			$arDate = ParseDateTime($filterDateTo, $this->dateFormat);
			if ($arDate)
			{
				if (mb_strlen($filterDateTo) < 11)
				{
					$arDate["HH"] = 23;
					$arDate["MI"] = 59;
					$arDate["SS"] = 59;
				}

				$arFilter["<=DATE_INSERT"] = date(
					$DB->DateFormatToPHP($this->dateFormat),
					mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"])
				);
			}
		}

		$filterStatus = trim((string)($_REQUEST['filter_status'] ?? ''));
		if ($filterStatus !== '')
		{
			$arFilter["STATUS_ID"] = $filterStatus;
		}

		$filterPayed = trim((string)($_REQUEST['filter_payed'] ?? ''));
		if ($filterPayed !== '')
		{
			$arFilter["PAYED"] = $filterPayed;
		}

		$showAll = (string)($_REQUEST['show_all'] ?? 'N');
		if ($showAll === 'N')
		{
			$filterHistory = (string)($_REQUEST['filter_history'] ?? 'N');
			if ($filterHistory === "Y")
			{
				if (($_REQUEST['show_canceled'] ?? '') === 'Y')
				{
					$arFilter['CANCELED'] = 'Y';
				}
				else
				{
					$arFilter[] = [
						'@STATUS_ID' => $this->arParams['HISTORIC_STATUSES']
					];
				}
			}
			else
			{
				$arFilter[] = [
					'!@STATUS_ID' => $this->arParams['HISTORIC_STATUSES'],
					'CANCELED' => 'N',
				];
			}
		}

		$filterCanceled = trim((string)($_REQUEST['filter_canceled'] ?? ''));
		if ($filterCanceled !== '')
		{
			$arFilter["CANCELED"] = $filterCanceled;
		}

		$this->filter = $arFilter;
	}

	/**
	 * Function wraps action list evaluation into try-catch block.
	 * @return void
	 */
	private function performActions()
	{
		try
		{
			$this->performActionList();
		}
		catch (Exception $e)
		{

			$this->errorsNonFatal[$e->getCode()] = $e->getMessage();
		}
	}

	/**
	 * Function perform pre-defined list of actions based on current state of $_REQUEST and parameters.
	 * @return void
	 */
	protected function performActionList()
	{
		// copy order
		$this->performActionCopyOrder();

		// some other ...
	}

	/**
	 * Function checks if order with supplied id is really exists.
	 * @param int|string $id Order id
	 * @return int Order id
	 */
	private function getRealId($id)
	{
		global $USER;

		$orderResult = false;

		$filter = array(
			'select' => array("ID"),
			'filter' => array("USER_ID" => $USER->GetID(), "LID" => SITE_ID),
			'order' => array("ID"=>"DESC")
		);

		/** @var Sale\Order $orderClass */
		$orderClass = $this->registry->getOrderClassName();

		if ($this->options['USE_ACCOUNT_NUMBER'])
		{
			$filter['filter']['ACCOUNT_NUMBER'] = $id;
			$orderList = $orderClass::getList($filter);
			$orderResult = $orderList->fetch();
		}

		if (!$orderResult)
		{
			$filter['filter']['ID'] = $id;
			$orderList = $orderClass::getList($filter);
			$orderResult = $orderList->fetch();
		}

		if (empty($orderResult))
		{
			return false;
		}

		return $orderResult['ID'];
	}

	/**
	 * Perform the following action: copy order
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function performActionCopyOrder()
	{
		if (mb_strlen($this->requestData["ID"]) && $this->requestData["COPY_ORDER"])
		{
			if($id = $this->getRealId($this->requestData["ID"]))
				$this->copyOrder2CustomerBasket($id);
			else
				throw new Main\SystemException(Localization\Loc::getMessage('SPOL_CANNOT_COPY_ORDER'), self::E_CANNOT_COPY_ORDER_NOT_FOUND);
		}
	}

	/**
	 * Function obtains all properties of a basket item
	 * @param int $id Basket item Id to search for
	 * @return mixed[] List of basket item properties
	 */
	protected function getBasketItemProps($id)
	{

		$basketProperties = array();

		$filter = array(
			'select' => array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT"),
			'filter' => array("BASKET_ID" => $id),
			'order' => array("SORT" => "ASC")
		);

		/** @var Sale\Basket $basketClass */
		$basketClass = $this->registry->getBasketClassName();

		$basketList = $basketClass::getList($filter);

		while ($basket = $basketList->fetch())
		{
			$basketProperties[] = array(
				"NAME" => $basket["NAME"],
				"CODE" => $basket["CODE"],
				"VALUE" => $basket["VALUE"]
			);
		}

		return $basketProperties;
	}

	/**
	 * The default action in case of success copying order
	 * @return void
	 */
	protected function doAfterOrderCopyed()
	{
		LocalRedirect($this->arParams["PATH_TO_BASKET"], true);
	}

	/**
	 * Function performs moving entire basket content of a certain order into client`s basket. It implements "copy order" action.
	 * @param int $id Order id
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function copyOrder2CustomerBasket($id)
	{
		$orderClassName = $this->registry->getOrderClassName();
		$basketClassName = $this->registry->getBasketClassName();

		if ($id)
		{
			/** @var Sale\Basket $basket */
			$basket = $basketClassName::loadItemsForFUser(Sale\Fuser::getId(), Main\Context::getCurrent()->getSite());

			$filterFields = array(
				'SET_PARENT_ID', 'TYPE',
				'PRODUCT_ID', 'PRODUCT_PRICE_ID', 'PRICE', 'CURRENCY', 'WEIGHT', 'QUANTITY', 'LID',
				'NAME', 'CALLBACK_FUNC', 'NOTES', 'PRODUCT_PROVIDER_CLASS', 'CANCEL_CALLBACK_FUNC',
				'ORDER_CALLBACK_FUNC', 'PAY_CALLBACK_FUNC', 'DETAIL_PAGE_URL', 'CATALOG_XML_ID', 'PRODUCT_XML_ID',
				'VAT_RATE', 'MEASURE_NAME', 'MEASURE_CODE', 'BASE_PRICE', 'VAT_INCLUDED'
			);
			$filterFields = array_flip($filterFields);
			/** @var Sale\Order $oldOrder */
			$oldOrder = $orderClassName::load($id);

			$oldBasket = $oldOrder->getBasket();
			$refreshStrategy = Sale\Basket\RefreshFactory::create(Sale\Basket\RefreshFactory::TYPE_FULL);
			$oldBasket->refresh($refreshStrategy);
			$oldBasketItems = $oldBasket->getOrderableItems();

			/** @var Sale\BasketItem $oldBasketItem*/
			foreach ($oldBasketItems as $oldBasketItem)
			{
				$propertyList = array();
				if ($oldPropertyCollection = $oldBasketItem->getPropertyCollection())
				{
					$propertyList = $oldPropertyCollection->getPropertyValues();
				}

				$item = $basket->getExistsItem($oldBasketItem->getField('MODULE'), $oldBasketItem->getField('PRODUCT_ID'), $propertyList);

				if ($item)
				{
					$item->setField('QUANTITY', $item->getQuantity() + $oldBasketItem->getQuantity());
				}
				else
				{
					$item = $basket->createItem($oldBasketItem->getField('MODULE'), $oldBasketItem->getField('PRODUCT_ID'));
					$oldBasketValues = array_intersect_key($oldBasketItem->getFieldValues(), $filterFields);
					$item->setField('NAME', $oldBasketValues['NAME']);
					$resultItem = $item->setFields($oldBasketValues);
					if (!$resultItem->isSuccess())
						continue;
					/** @var Sale\PropertyValueCollection $newPropertyCollection*/
					$newPropertyCollection = $item->getPropertyCollection();

					/** @var Sale\BasketPropertyItem $oldProperty*/
					foreach ($propertyList as $oldPropertyFields)
					{
						$propertyItem = $newPropertyCollection->createItem($oldPropertyFields);
						unset($oldPropertyFields['ID'], $oldPropertyFields['BASKET_ID']);

						/** @var Sale\BasketPropertyItem $propertyItem*/
						$propertyItem->setFields($oldPropertyFields);
					}
				}
			}

			$result = $basket->save();
			if (!$result->isSuccess())
			{
				$errorList = $result->getErrors();
				foreach ($errorList as $key => $error)
				{
					$this->errorsNonFatal[$error->getCode()."_".$key] = $error->getMessage();
				}

				throw new Main\SystemException(Localization\Loc::getMessage('SPOL_CANNOT_COPY_ORDER'), self::E_CANNOT_COPY_CANT_ADD_BASKET);
			}

			$this->doAfterOrderCopyed();
		}
	}

	/**
	 * Read some data from database, using cache. Under some info we mean status list, delivery system list and so on.
	 * This will be a shared cache between sale.personal.order.list and sale.personal.order.detail, so beware of collisions.
	 * @return void
	 * @throws Exception
	 * @throws Main\SystemException
	 */
	protected function obtainDataReferences()
	{
		if ($this->startCache(array('spo-shared')))
		{
			try
			{
				$cachedData = array();

				/////////////////////
				/////////////////////

				// Person type
				$cachedData['PERSON_TYPE'] = array();

				$personTypeClassName = $this->registry->getPersonTypeClassName();
				$cachedData['PERSON_TYPE'] = $personTypeClassName::load(SITE_ID);

				// Save statuses for Filter form
				$cachedData['STATUS'] = array();

				$orderStatusClassName = $this->registry->getOrderStatusClassName();
				$listStatusNames = $orderStatusClassName::getAllStatusesNames(LANGUAGE_ID);

				foreach($listStatusNames as $key => $data)
				{
					$cachedData['STATUS'][$key] = array('ID'=>$key,'NAME'=>$data);
				}

				$cachedData['PAYSYS'] = array();

				$paySystemsList = Sale\PaySystem\Manager::getList(array());

				while ($paySystem = $paySystemsList->fetch())
				{
					$paySystem['NAME'] = htmlspecialcharsbx($paySystem['NAME']);
					$cachedData['PAYSYS'][$paySystem["ID"]] = $paySystem;
				}

				$cachedData['DELIVERY'] = array();
				$dbDelivery = Sale\Delivery\Services\Table::getList();

				$deliveryService = array();
				while ($delivery = $dbDelivery->fetch())
					$deliveryService[$delivery['ID']] = $delivery;

				foreach ($deliveryService as $delivery)
				{
					$cachedData['DELIVERY'][$delivery["ID"]] = $delivery;

					if ($delivery['PARENT_ID'])
					{
						$cachedData['DELIVERY'][$delivery["ID"]]['NAME'] = htmlspecialcharsbx($deliveryService[$delivery['PARENT_ID']]['NAME'].':'.$delivery['NAME']);
						if (empty($delivery['LOGOTIP']))
							$cachedData['DELIVERY'][$delivery["ID"]]['LOGOTIP'] = $deliveryService[$delivery['PARENT_ID']]['LOGOTIP'];
					}
					else
					{
						$cachedData['DELIVERY'][$delivery["ID"]]['NAME'] = htmlspecialcharsbx($delivery['NAME']);
					}
				}

				/////////////////////
				/////////////////////

			}
			catch (Exception $e)
			{
				$this->abortCache();
				throw $e;
			}

			$this->endCache($cachedData);

		}
		else
			$cachedData = $this->getCacheData();

		$this->dbResult = array_merge($this->dbResult, $cachedData);
	}

	/**
	 * Perform reading main data from database, no cache is used
	 * @return void
	 */
	protected function obtainDataOrders()
	{
		$listOrders = array();
		$orderIdList = array();
		$listOrderBasket = array();
		$listOrderShipment = array();
		$listOrderPayment = array();

		$select = array(
				'ID',
				'LID',
				'PERSON_TYPE_ID',

				'PAYED',
				'DATE_PAYED',
				'EMP_PAYED_ID',

				'CANCELED',
				'DATE_CANCELED',
				'EMP_CANCELED_ID',
				'REASON_CANCELED',

				'MARKED',
				'DATE_MARKED',
				'EMP_MARKED_ID',
				'REASON_MARKED',

				'STATUS_ID',
				'DATE_STATUS',

				'PAY_VOUCHER_NUM',
				'PAY_VOUCHER_DATE',
				'EMP_STATUS_ID',

				'PRICE_DELIVERY',
				'ALLOW_DELIVERY',
				'DATE_ALLOW_DELIVERY',
				'EMP_ALLOW_DELIVERY_ID',

				'DEDUCTED',
				'DATE_DEDUCTED',
				'EMP_DEDUCTED_ID',

				'REASON_UNDO_DEDUCTED',

				'RESERVED',
				'PRICE',
				'CURRENCY',
				'DISCOUNT_VALUE',

				'SUM_PAID',
				'USER_ID',
				'PAY_SYSTEM_ID',
				'DELIVERY_ID',

				'DATE_INSERT',
				'DATE_UPDATE',

				'USER_DESCRIPTION',
				'ADDITIONAL_INFO',

				'TAX_VALUE',
				'STAT_GID',
				'RECURRING_ID',
				'RECOUNT_FLAG',

				'DELIVERY_DOC_NUM',
				'DELIVERY_DOC_DATE',
				'STORE_ID',
				'ORDER_TOPIC',

				'RESPONSIBLE_ID',
				'DATE_PAY_BEFORE',
				'DATE_BILL',
				'ACCOUNT_NUMBER',
				'TRACKING_NUMBER',
				'XML_ID'
		);

		$getListParams = array(
			'filter' => $this->filter,
			'select' => $select
		);

		if ($this->sortBy == 'STATUS')
		{
			$getListParams['runtime'] = array(
				new Main\Entity\ReferenceField(
					'STATUS',
					'\Bitrix\Sale\Internals\StatusTable',
					array(
						'=this.STATUS_ID' => 'ref.ID',
					),
					array(
						"join_type" => 'inner'
					)
				)
			);
			$getListParams['order'] = array("STATUS.SORT" => 'ASC', 'ID' => $this->sortOrder);
		}
		else
		{
			$getListParams['order'] = array($this->sortBy => $this->sortOrder);
		}

		if (isset($this->arParams['CONTEXT_SITE_ID']) && $this->arParams['CONTEXT_SITE_ID'] > 0)
		{
			$code = \Bitrix\Sale\TradingPlatform\Landing\Landing::getCodeBySiteId($this->arParams['CONTEXT_SITE_ID']);
			$platformId = \Bitrix\Sale\TradingPlatform\Landing\Landing::getInstanceByCode($code)->getId();
			if ((int)$platformId > 0)
			{
				$getListParams['runtime'][] = new Main\ORM\Fields\Relations\Reference(
					'TRADING_BINDING',
					'\Bitrix\Sale\TradingPlatform\OrderTable',
					array(
						'=this.ID' => 'ref.ORDER_ID',
						'=ref.TRADING_PLATFORM_ID' => new Main\DB\SqlExpression('?i', $platformId)
					),
					array(
						"join_type" => 'inner'
					)
				);
				$getListParams['runtime'][] = new Main\ORM\Fields\Relations\Reference(
					'TRADING',
					'\Bitrix\Sale\TradingPlatformTable',
					array(
						'=this.TRADING_BINDING.TRADING_PLATFORM_ID' => 'ref.ID',
						'=ref.CLASS' => new Main\DB\SqlExpression('?', "\\".Sale\TradingPlatform\Landing\Landing::class)
					),
					array(
						"join_type" => 'inner'
					)
				);
			}
		}

		$usePageNavigation = true;

		$totalPages = 0;
		$totalCount = 0;

		$orderClassName = $this->registry->getOrderClassName();

		\CPageOption::SetOptionString("main", "nav_page_in_session", "N");
		$navyParams = \CDBResult::GetNavParams();

		if ($navyParams['SHOW_ALL'])
		{
			$usePageNavigation = false;
		}
		else
		{
			$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
			$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
			if (isset($this->arParams["ORDERS_PER_PAGE"]) && intval($this->arParams["ORDERS_PER_PAGE"]) > 0)
			{
				$navyParams['SIZEN'] = $this->arParams["ORDERS_PER_PAGE"];
			}

			$getListParams['limit'] = $navyParams['SIZEN'];
			$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);

			$countParams = [
				"filter"=>$getListParams['filter'],
				"select"=> [new Main\ORM\Fields\ExpressionField('CNT', 'COUNT(1)')]
			];

			if (!empty($getListParams['runtime']))
			{
				$countParams["runtime"] = $getListParams['runtime'];
			}

			/** @var Main\DB\Result $countQuery */
			$countQuery = $orderClassName::getList($countParams);

			$totalCount = $countQuery->fetch();
			$totalCount = (int)$totalCount['CNT'];
			unset($countQuery);

			if ($totalCount > 0)
			{
				$totalPages = ceil($totalCount/$navyParams['SIZEN']);

				if ($navyParams['PAGEN'] > $totalPages)
					$navyParams['PAGEN'] = $totalPages;

				$getListParams['limit'] = $navyParams['SIZEN'];
				$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
			}
			else
			{
				$navyParams['PAGEN'] = 1;
				$getListParams['limit'] = $navyParams['SIZEN'];
				$getListParams['offset'] = 0;
			}
		}

		$this->dbQueryResult['ORDERS'] = new \CDBResult($orderClassName::getList($getListParams));

		if ($usePageNavigation)
		{
			$this->dbQueryResult['ORDERS']->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
			$this->dbQueryResult['ORDERS']->NavRecordCount = $totalCount;
			$this->dbQueryResult['ORDERS']->NavPageCount = $totalPages;
			$this->dbQueryResult['ORDERS']->NavPageNomer = $navyParams['PAGEN'];
		}
		else
		{
			if ((int)($this->arParams["ORDERS_PER_PAGE"]))
			{
				$this->dbQueryResult['ORDERS']->NavStart($this->arParams["ORDERS_PER_PAGE"], false);
			}
		}

		if (empty($this->dbQueryResult['ORDERS']))
		{
			return;
		}

		while ($arOrder = $this->dbQueryResult['ORDERS']->GetNext())
		{
			$arOrder['LOCK_CHANGE_PAYSYSTEM'] = 'N';
			if (
				is_array($this->arParams['RESTRICT_CHANGE_PAYSYSTEM'])
				&& in_array($arOrder['STATUS_ID'], $this->arParams['RESTRICT_CHANGE_PAYSYSTEM'])
			)
			{
				$arOrder['LOCK_CHANGE_PAYSYSTEM'] = 'Y';
			}

			$listOrders[$arOrder["ID"]] = $arOrder;
			$orderIdList[] = $arOrder["ID"];
		}

		$basketClassName = $this->registry->getBasketClassName();
		/** @var Main\DB\Result $listBaskets */
		$listBaskets = $basketClassName::getList(array(
			'select' => array("*"),
			'filter' => array("ORDER_ID" => $orderIdList),
			'order' => array('NAME' => 'asc')
		));

		while ($basket = $listBaskets->fetch())
		{
			if (CSaleBasketHelper::isSetItem($basket))
				continue;

			$listOrderBasket[$basket['ORDER_ID']][$basket['ID']] = $basket;
		}

		$trackingManager = Sale\Delivery\Tracking\Manager::getInstance();

		$deliveryStatusClassName = $this->registry->getDeliveryStatusClassName();
		$deliveryStatuses = $deliveryStatusClassName::getAllStatusesNames(LANGUAGE_ID);

		$shipmentClassName = $this->registry->getShipmentClassName();
		/** @var Main\DB\Result $listShipments */
		$listShipments = $shipmentClassName::getList(array(
			'select' => array(
				'STATUS_ID',
				'DELIVERY_NAME',
				'SYSTEM',
				'DELIVERY_ID',
				'ACCOUNT_NUMBER',
				'PRICE_DELIVERY',
				'DATE_DEDUCTED',
				'CURRENCY',
				'DEDUCTED',
				'TRACKING_NUMBER',
				'ORDER_ID'
			),
			'filter' => array('ORDER_ID' => $orderIdList)
		));

		while ($shipment = $listShipments->fetch())
		{
			if ($shipment['SYSTEM'] == 'Y')
				continue;

			$shipment['DELIVERY_NAME'] = htmlspecialcharsbx($shipment['DELIVERY_NAME']);
			$shipment["FORMATED_DELIVERY_PRICE"] = SaleFormatCurrency(floatval($shipment["PRICE_DELIVERY"]), $shipment["CURRENCY"]);
			$shipment["DELIVERY_STATUS_NAME"] = $deliveryStatuses[$shipment["STATUS_ID"]];
			$shipment['TRACKING_URL'] = '';
			if ($shipment["DELIVERY_ID"] > 0 && (string)$shipment["TRACKING_NUMBER"] !== '')
			{
				$shipment["TRACKING_URL"] = $trackingManager->getTrackingUrl($shipment["DELIVERY_ID"], $shipment["TRACKING_NUMBER"]);
			}
			$listOrderShipment[$shipment['ORDER_ID']][] = $shipment;
		}

		$paymentClassName = $this->registry->getPaymentClassName();
		/** @var Main\DB\Result $listPayments */
		$listPayments = $paymentClassName::getList(array(
			'select' => array('ID', 'PAY_SYSTEM_NAME', 'PAY_SYSTEM_ID', 'ACCOUNT_NUMBER', 'ORDER_ID', 'PAID', 'SUM', 'CURRENCY', 'DATE_BILL'),
			'filter' => array('ORDER_ID' => $orderIdList)
		));

		$paymentIdList = array();
		$paymentList = array();

		while ($payment = $listPayments->fetch())
		{
			$paySystemFields = $this->dbResult['PAYSYS'][$payment['PAY_SYSTEM_ID']];
			$payment['PAY_SYSTEM_NAME'] = htmlspecialcharsbx($payment['PAY_SYSTEM_NAME']);
			$payment["FORMATED_SUM"] = SaleFormatCurrency($payment["SUM"], $payment["CURRENCY"]);
			$payment['IS_CASH'] = $paySystemFields['IS_CASH'];
			$payment['NEW_WINDOW'] = $paySystemFields['NEW_WINDOW'];
			$payment['ACTION_FILE'] = $paySystemFields['ACTION_FILE'];
			$payment["PSA_ACTION_FILE"] =  htmlspecialcharsbx($this->arParams["PATH_TO_PAYMENT"]).'?ORDER_ID='.urlencode(urlencode($listOrders[$payment["ORDER_ID"]]['ACCOUNT_NUMBER'])).'&PAYMENT_ID='.$payment['ACCOUNT_NUMBER'];
			$paymentList[$payment['ID']] = $payment;
			$paymentIdList[] = $payment['ID'];
		}

		$checkList = CheckManager::collectInfo(
			array(
				"PAYMENT_ID" => $paymentIdList,
				"ENTITY_REGISTRY_TYPE" => Sale\Registry::REGISTRY_TYPE_ORDER
			)
		);

		if (!empty($checkList))
		{
			foreach ($checkList as $check)
			{
				$paymentList[$check['PAYMENT_ID']]['CHECK_DATA'][] = $check;
			}
		}

		foreach ($paymentList as $payment)
		{
			$listOrderPayment[$payment['ORDER_ID']][] = $payment;
		}

		/** @var Bitrix\Sale\OrderStatus $orderStatusClassName */
		$orderStatusClassName = $this->registry->getOrderStatusClassName();
		$allowStatusList = array_fill_keys(
			$orderStatusClassName::getAllowPayStatusList(),
			true
		);

		foreach ($orderIdList as $orderId)
		{
			$statusId = $listOrders[$orderId]['STATUS_ID'];
			$listOrders[$orderId]['IS_ALLOW_PAY'] = isset($allowStatusList[$statusId]) ? 'Y' : 'N';

			$this->dbResult['ORDERS'][] = array(
				"ORDER" => $listOrders[$orderId],
				"BASKET_ITEMS" => $listOrderBasket[$orderId] ?? [],
				"SHIPMENT" => $listOrderShipment[$orderId] ?? [],
				"PAYMENT" => $listOrderPayment[$orderId] ?? [],
			);
		}
	}

	/**
	 * Fetches all required data from database. Everything that connected with data fetch is here.
	 * @return void
	 */
	protected function obtainData()
	{
		$this->obtainDataReferences();
		$this->obtainDataOrders();
	}

	/**
	 * Move data read from database to a specially formatted $arResult
	 * @return void
	 */
	protected function formatResult()
	{
		global $APPLICATION;

		$arResult = array();

		// references
		$arResult["INFO"]["STATUS"] = $this->dbResult['STATUS'];
		$arResult["INFO"]["PAY_SYSTEM"] = $this->dbResult['PAYSYS'];
		$arResult["INFO"]["DELIVERY"] = $this->dbResult['DELIVERY'];

		$arResult["CURRENT_PAGE"] = $APPLICATION->GetCurPage();
		$arResult["NAV_STRING"] = $this->dbQueryResult['ORDERS']->GetPageNavString(Localization\Loc::getMessage("SPOL_PAGES"), $this->arParams["NAV_TEMPLATE"]);

		// bug walkaround
		$this->arParams["PATH_TO_CANCEL"] .= (mb_strpos($this->arParams["PATH_TO_CANCEL"], "?") === false ? "?" : "&");
		if (empty($this->arParams["PATH_TO_CATALOG"]))
		{
			$this->arParams["PATH_TO_CATALOG"] = '/catalog/';
		}

		if (!empty($this->dbResult['ORDERS']) && is_array($this->dbResult['ORDERS']))
		{
			foreach ($this->dbResult['ORDERS'] as $k => $orderInfo)
			{
				$arOrder =& $this->dbResult['ORDERS'][$k]['ORDER'];

				$arOBasket =& $this->dbResult['ORDERS'][$k]['BASKET_ITEMS'];

				$arOrder["FORMATED_PRICE"] = SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]);

				$this->formatDate($arOrder, $this->orderDateFields2Convert);

				if (is_array($this->dbResult['ORDERS'][$k]['SHIPMENT']))
				{
					$formattedShipments = [];
					foreach ($this->dbResult['ORDERS'][$k]['SHIPMENT'] as $i => $shipment)
					{
						$this->formatDate($shipment, $this->orderDateFields2Convert);
						$formattedShipments[$i] = $shipment;
					}
					$this->dbResult['ORDERS'][$k]['SHIPMENT'] = $formattedShipments;
				}

				if (is_array($this->dbResult['ORDERS'][$k]['PAYMENT']))
				{
					$formattedPayments = [];
					foreach ($this->dbResult['ORDERS'][$k]['PAYMENT'] as $i => $payment)
					{
						$this->formatDate($payment, $this->orderDateFields2Convert);
						$formattedPayments[$i] = $payment;
					}
					$this->dbResult['ORDERS'][$k]['PAYMENT'] = $formattedPayments;
				}

				if ($this->arParams['DISALLOW_CANCEL'] === 'Y')
				{
					$arOrder["CAN_CANCEL"] = 'N';
				}
				else
				{
					$arOrder["CAN_CANCEL"] = ($arOrder["CANCELED"] != "Y" && $arOrder["STATUS_ID"] != "F" && $arOrder["PAYED"] != "Y") ? "Y" : "N";
				}

				$arOrder["URL_TO_DETAIL"] = CComponentEngine::makePathFromTemplate($this->arParams["PATH_TO_DETAIL"], array("ID" => urlencode(urlencode($arOrder["ACCOUNT_NUMBER"]))));
				if(mb_strpos($this->arParams["PATH_TO_COPY"], "COPY_ORDER"))
				{
					$arOrder["URL_TO_COPY"] = CComponentEngine::makePathFromTemplate($this->arParams["PATH_TO_COPY"], array("ID" => urlencode(urlencode($arOrder["ACCOUNT_NUMBER"]))));
				}
				else
				{
					$arOrder["URL_TO_COPY"] = CComponentEngine::makePathFromTemplate($arResult["CURRENT_PAGE"]."?ID=#ID#&COPY_ORDER=Y", array("ID" => urlencode(urlencode($arOrder["ACCOUNT_NUMBER"]))));
				}
				$arOrder["URL_TO_CANCEL"] = CComponentEngine::makePathFromTemplate($this->arParams["PATH_TO_CANCEL"], array("ID" => urlencode(urlencode($arOrder["ACCOUNT_NUMBER"]))))."CANCEL=Y";

				if(self::isNonemptyArray($arOBasket))
				{
					foreach ($arOBasket as $n => $basketInfo)
					{
						$arBasket =& $arOBasket[$n];

						$arBasket["NAME~"] = $arBasket["NAME"];
						$arBasket["NOTES~"] = $arBasket["NOTES"];
						$arBasket["NAME"] = htmlspecialcharsEx($arBasket["NAME"]);
						$arBasket["NOTES"] = htmlspecialcharsEx($arBasket["NOTES"]);
						$arBasket["QUANTITY"] = doubleval($arBasket["QUANTITY"]);

						// backward compatibility
						$arBasket["MEASURE_TEXT"] = $arBasket["MEASURE_NAME"];

						$this->formatDate($arBasket, $this->basketDateFields2Convert);
					}
				}
			}

			$arResult["ORDERS"] = $this->dbResult['ORDERS'];
		}
		else
		{
			$arResult["ORDERS"] = array();
		}
		$arResult['SORT_TYPE'] = $this->sortBy;

		$arResult["RETURN_URL"] = (new Sale\PaySystem\Context())->getUrl();

		$this->arResult = $arResult;
	}

	/**
	 * Move all errors to $arResult, if there were any
	 * @return void
	 */
	protected function formatResultErrors()
	{
		$errors = array();
		if (!empty($this->errorsFatal))
			$errors['FATAL'] = $this->errorsFatal;
		if (!empty($this->errorsNonFatal))
			$errors['NONFATAL'] = $this->errorsNonFatal;

		if (!empty($errors))
			$this->arResult['ERRORS'] = $errors;

		// backward compatiblity
		$error = current($this->errorsFatal);
		if (!empty($error))
			$this->arResult['ERROR_MESSAGE'] = $error;
	}

	/**
	 * Function implements all the life cycle of our component
	 * @return void
	 */
	public function executeComponent()
	{
		try
		{
			$this->setFrameMode(false);
			$this->checkRequiredModules();
			$this->checkAuthorized();
			$this->setTitle();
			$this->getOptions();
			$this->setRegistry();
			$this->processRequest();

			$this->performActions();

			$this->obtainData();
			$this->formatResult();

		}

		catch (Exception $e)
		{
			$this->errorsFatal[$e->getCode()] = $e->getMessage();
		}

		$this->formatResultErrors();

		$this->includeComponentTemplate();
	}

	/**
	 * Return current class registry
	 *
	 * @param mixed[] array that date conversion performs in
	 * @return void
	 */
	protected function setRegistry()
	{
		$this->registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
	}

	/**
	 * Convert dates if date template set
	 * @param mixed[] $arr data array to be converted
	 * @param string[] $conversion contains sublist of keys of $arr, that will be converted
	 * @return void
	 */
	protected function formatDate(&$arr, $conversion)
	{
		if (!$this->useIblock)
			return;
		if (mb_strlen($this->arParams['ACTIVE_DATE_FORMAT']) && self::isNonemptyArray($conversion))
			foreach ($conversion as $fld)
			{
				if (!empty($arr[$fld]))
					$arr[$fld."_FORMATED"] = CIBlockFormatProperties::DateFormat($this->arParams['ACTIVE_DATE_FORMAT'], MakeTimeStamp($arr[$fld]));
			}
	}

	/**
	 * Function checks if it`s argument is a legal array for foreach() construction
	 * @param mixed $arr data to check
	 * @return boolean
	 */
	protected static function isNonemptyArray($arr)
	{
		return is_array($arr) && !empty($arr);
	}

	////////////////////////
	// Cache functions
	////////////////////////
	/**
	 * Function checks if cacheing is enabled in component parameters
	 * @return boolean
	 */
	final protected function getCacheNeed()
	{
		return	intval($this->arParams['CACHE_TIME']) > 0 &&
				$this->arParams['CACHE_TYPE'] != 'N' &&
				Config\Option::get("main", "component_cache_on", "Y") == "Y";
	}

	/**
	 * Function perform start of cache process, if needed
	 * @param mixed[]|string $cacheId An optional addition for cache key
	 * @return boolean True, if cache content needs to be generated, false if cache is valid and can be read
	 */
	final protected function startCache($cacheId = array())
	{
		if(!$this->getCacheNeed())
			return true;

		$this->currentCache = Data\Cache::createInstance();

		return $this->currentCache->startDataCache(intval($this->arParams['CACHE_TIME']), $this->getCacheKey($cacheId));
	}

	/**
	 * Function perform start of cache process, if needed
	 * @throws Main\SystemException
	 * @param mixed[] $data Data to be stored in the cache
	 * @return void
	 */
	final protected function endCache($data = null)
	{
		if(!$this->getCacheNeed())
			return;

		if($this->currentCache == 'null')
			throw new Main\SystemException('Cache were not started');

		$this->currentCache->endDataCache($data);
		$this->currentCache = null;
	}

	/**
	 * Function discard cache generation
	 * @throws Main\SystemException
	 * @return void
	 */
	final protected function abortCache()
	{
		if(!$this->getCacheNeed())
			return;

		if($this->currentCache == 'null')
			throw new Main\SystemException('Cache were not started');

		$this->currentCache->abortDataCache();
		$this->currentCache = null;
	}

	/**
	 * Function return data stored in cache
	 * @throws Main\SystemException
	 * @return bool|mixed[] Data from cache
	 */
	final protected function getCacheData()
	{
		if(!$this->getCacheNeed())
		{
			return false;
		}

		if($this->currentCache == 'null')
			throw new Main\SystemException('Cache were not started');

		return $this->currentCache->getVars();
	}

	/**
	 * Function leaves the ability to modify cache key in future.
	 * @param array $cacheId
	 * @return string Cache key to be used in CPHPCache()
	 */
	final protected function getCacheKey($cacheId = array())
	{
		if(!is_array($cacheId))
			$cacheId = array((string) $cacheId);

		$cacheId['SITE_ID'] = SITE_ID;
		$cacheId['LANGUAGE_ID'] = LANGUAGE_ID;
		// if there are two or more caches with the same id, but with different cache_time, make them separate
		$cacheId['CACHE_TIME'] = intval($this->arParams['CACHE_TIME']);

		if(defined("SITE_TEMPLATE_ID"))
			$cacheId['SITE_TEMPLATE_ID'] = SITE_TEMPLATE_ID;

		return implode('|', $cacheId);
	}
}
