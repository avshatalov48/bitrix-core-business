<?php
namespace Bitrix\Sale\Update;

use Bitrix\Main,
	Bitrix\Sale,
	Bitrix\Crm\Order,
	Bitrix\Crm\Settings,
	Bitrix\Crm\Timeline,
	Bitrix\Crm\Order\Matcher,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Update\Stepper,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class CrmEntityCreator
 * Create crm entities: contact and company
 *
 * @package Bitrix\Sale\Update
 */
final class CrmEntityCreator
{
	/** @var Order\Order $order */
	private $order;

	/**
	 * CrmEntityCreator constructor
	 *
	 * @param Order\Order $order
	 */
	public function __construct(Order\Order $order)
	{
		$this->order = $order;
	}

	/**
	 * @return Sale\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws \Exception
	 */
	public function create()
	{
		$result = new Sale\Result();

		$contactCompanyCollection = $this->order->getContactCompanyCollection();
		if ($contactCompanyCollection->isEmpty())
		{
			$this->addContactCompany();
		}

		$this->setContactCompanyRequisites();

		if (!$this->isSetResponsible())
		{
			$this->setResponsible();
		}

		$saveOrderResult = $this->order->save();
		if (!$saveOrderResult->isSuccess())
		{
			$result->addErrors($saveOrderResult->getErrors());
		}

		$this->addTimeLines();

		return $result;
	}

	/**
	 * @return bool
	 */
	private function isSetResponsible()
	{
		return $this->order->getField("RESPONSIBLE_ID") ? true : false;
	}

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	private function setResponsible()
	{
		$this->order->setFieldNoDemand(
			"RESPONSIBLE_ID",
			Settings\OrderSettings::getCurrent()->getDefaultResponsibleId()
		);
	}

	/**
	 * @return void
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	private function addContactCompany()
	{
		$matches = Matcher\EntityMatchManager::getInstance()->match($this->order);
		if ($matches)
		{
			/** @var Order\ContactCompanyCollection $communication */
			$communication = $this->order->getContactCompanyCollection();
			if (isset($matches[\CCrmOwnerType::Contact]))
			{
				/** @var Order\Contact $contact */
				$contact = Order\Contact::create($communication);
				$contact->setField("ENTITY_ID", $matches[\CCrmOwnerType::Contact]);
				$contact->setField("IS_PRIMARY", "Y");

				$communication->addItem($contact);
			}

			if (isset($matches[\CCrmOwnerType::Company]))
			{
				/** @var Order\Company $company */
				$company = Order\Company::create($communication);
				$company->setField("ENTITY_ID", $matches[\CCrmOwnerType::Company]);
				$company->setField("IS_PRIMARY", "Y");

				$communication->addItem($company);
			}
		}
	}

	/**
	 * @return void
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	private function setContactCompanyRequisites()
	{
		$collection = $this->order->getContactCompanyCollection();

		$entity = $collection->getPrimaryCompany();
		if ($entity === null)
		{
			$entity = $collection->getPrimaryContact();
		}

		if ($entity === null)
		{
			return;
		}

		$result = [
			"MC_REQUISITE_ID" => 0,
			"MC_BANK_DETAIL_ID" => 0
		];

		$requisiteList = $entity->getRequisiteList();
		if ($requisiteList)
		{
			$result["REQUISITE_ID"] = current($requisiteList)["ID"];
		}

		$bankRequisiteList = $entity->getBankRequisiteList();
		if ($bankRequisiteList)
		{
			$result["BANK_DETAIL_ID"] = current($bankRequisiteList)["ID"];
		}

		$this->order->setRequisiteLink($result);
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function addTimeLines()
	{
		// add
		$this->addTimelineEntryOnCreate();

		$historyChanges = $this->getHistoryChanges();
		foreach ($historyChanges as $historyChange)
		{
			// status
			if ($historyChange["TYPE"] == "ORDER_STATUS_CHANGED")
			{
				$this->addTimelineEntryOnStatusModify($historyChange["DATA"]["OLD"], $historyChange["DATA"]["CURRENT"]);
			}
			elseif ($historyChange["TYPE"] == "ORDER_PRICE_CHANGED")
			{
				// update (price)
				$this->updateTimelineCreationEntity($historyChange["DATA"]["CURRENT"]);
			}
		}

		// cancel
		$this->addTimelineEntryOnCancel();
	}

	/**
	 * @throws Main\ArgumentException
	 * @return void;
	 */
	private function addTimelineEntryOnCreate()
	{
		Timeline\OrderController::getInstance()->onCreate(
			(int)$this->order->getId(),
			[
				"FIELDS" => [
					"ID" => (int)$this->order->getId(),
					"CREATED_BY" => $this->order->getField("CREATED_BY"),
					"RESPONSIBLE_ID" => $this->order->getField("RESPONSIBLE_ID"),
					"DATE_INSERT" => $this->order->getField("DATE_INSERT"),
					"PRICE" => $this->order->getField("PRICE"),
					"CURRENCY" => $this->order->getField("CURRENCY")
				]
			]
		);
	}

	/**
	 * @throws Main\ArgumentException
	 * @return void;
	 */
	private function addTimelineEntryOnCancel()
	{
		if ($this->order->getField("CANCELED") !== "Y")
			return;

		$fields = [
			"ID" => $this->order->getId(),
			"CANCELED" => $this->order->getField("CANCELED"),
		];

		$fields["REASON_CANCELED"] = $this->order->getField("REASON_CANCELED");
		$fields["EMP_CANCELED_ID"] = $this->order->getField("EMP_CANCELED_ID");

		Timeline\OrderController::getInstance()->onCancel($this->order->getId(), ["FIELDS" => $fields]);
	}

	/**
	 * @param $prevStatus
	 * @param $currentStatus
	 * @return void;
	 * @throws Main\ArgumentException
	 */
	private function addTimelineEntryOnStatusModify($prevStatus, $currentStatus)
	{
		$modifyParams = [
			"PREVIOUS_FIELDS" => ["STATUS_ID" => $prevStatus],
			"CURRENT_FIELDS" => [
				"STATUS_ID" => $currentStatus,
				"EMP_STATUS_ID" => $this->order->getField("EMP_STATUS_ID") // ?
			],
		];

		Timeline\OrderController::getInstance()->onModify($this->order->getId(), $modifyParams);
	}

	/**
	 * @param $currentPrice
	 * @return void;
	 * @throws Main\ArgumentException
	 */
	private function updateTimelineCreationEntity($currentPrice)
	{
		$fields = $this->order->getFields();
		$selectedFields =[
			"DATE_INSERT_TIMESTAMP" => $fields["DATE_INSERT"]->getTimestamp(),
			"PRICE" => $currentPrice,
			"CURRENCY" => $fields["CURRENCY"]
		];

		Timeline\OrderController::getInstance()->updateSettingFields(
			$this->order->getId(),
			Timeline\TimelineType::CREATION,
			$selectedFields
		);
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getHistoryChanges()
	{
		$arHistoryData = [];
		$bUseOldHistory = false;

		// collect records from old history to show in the new order changes list
		$dbHistory = (new \CSaleOrder)->GetHistoryList(
			["H_DATE_INSERT" => "ASC"],
			["H_ORDER_ID" => $this->order->getId()],
			false,
			false,
			["*"]
		);

		if ($dbHistory->SelectedRowsCount())
		{
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");
		}

		while ($arHistory = $dbHistory->Fetch())
		{
			$res = \convertHistoryToNewFormat($arHistory);
			if ($res)
			{
				$arHistoryData[] = $res;
				$bUseOldHistory = true;
			}
		}

		$arFilterHistory = ["ORDER_ID" => $this->order->getId()];
		$arFilterHistory["@TYPE"] = ["ORDER_STATUS_CHANGED", "ORDER_PRICE_CHANGED"];

		// new order history data
		$dbOrderChange = Sale\Internals\OrderChangeTable::getList([
			"select" => ["*"],
			"filter" => $arFilterHistory,
			"order" => [
				"DATE_CREATE" => "ASC",
				"ID" => "ASC"
			]
		]);
		while ($arChangeRecord = $dbOrderChange->fetch())
		{
			$arHistoryData[] = $arChangeRecord;
		}

		// advancing sorting is necessary if old history results are mixed with new order changes
		if ($bUseOldHistory)
		{
			$arData = [];
			foreach ($arHistoryData as $index => $arHistoryRecord)
				$arData[$index]  = $arHistoryRecord["DATE_CREATE"];

			$arIds = [];
			foreach ($arHistoryData as $index => $arHistoryRecord)
				$arIds[$index]  = $arHistoryRecord["ID"];

			array_multisort($arData, constant("SORT_ASC"), $arIds, constant("SORT_ASC"), $arHistoryData);
		}

		$dbRes = new \CDBResult;
		$dbRes->InitFromArray($arHistoryData);

		$result = [];
		while ($arRes = $dbRes->Fetch())
		{
			$changes = [];
			$data = unserialize($arRes["DATA"]);

			if ($arRes["TYPE"] == "ORDER_STATUS_CHANGED")
			{
				$changes = [
					"CURRENT" => $data["STATUS_ID"],
					"OLD" => $data["OLD_STATUS_ID"]
				];
			}
			elseif ($arRes["TYPE"] == "ORDER_PRICE_CHANGED")
			{
				$changes = [
					"CURRENT" => $data["PRICE"],
					"OLD" => $data["OLD_PRICE"]
				];
			}

			$result[] = [
				"TYPE" => $arRes["TYPE"],
				"DATA" => $changes
			];
		}

		return $result;
	}
}

/**
 * Class CrmEntityCreatorStepper
 * Stepper for creating crm entities
 *
 * @package Bitrix\Sale\Update
 */
final class CrmEntityCreatorStepper extends Stepper
{
	const CONTINUE_EXECUTING = true;
	const STOP_EXECUTING = false;

	/** @var string */
	const IS_SALE_CRM_SITE_MASTER_STUB = "~IS_SALE_CRM_SITE_MASTER_STUB";

	/** @var string */
	const ORDER_CONVERT_IS_FINISH = "~ORDER_CONVERT_IS_FINISH";

	/** @var string */
	const IS_SALE_CRM_SITE_MASTER_FINISH = "~IS_SALE_CRM_SITE_MASTER_FINISH";

	const PREFIX_OPTION_ADMIN_PANEL_IS_ENABLED = "~ADMIN_PANEL_IS_ENABLED_FOR_";

	const IS_CRM_SITE_MASTER_OPENED = "~IS_CRM_SITE_MASTER_OPENED";

	/** @var string */
	const WIZARD_SITE_ID = "~CRM_WIZARD_SITE_ID";

	/** @var string */
	const STEPPER_PARAMS = "~CRM_ENTITY_CREATOR_STEPPER_PARAMS";

	const UPDATE_ORDER_CONVERTER_CRM_ERROR_TABLE = "~UPDATE_ORDER_CONVERTER_CRM_ERROR_TABLE";

	const ORDER_CONVERTER_CRM_ERROR_COUNT = "~ORDER_CONVERTER_CRM_ERROR_COUNT";

	/** @var int max executing time in sec */
	const MAX_EXECUTION_TIME = 5;

	/** @var int max orders of iteration */
	const MAX_ORDERS = 100;

	protected static $moduleId = "sale";

	private $orderList =  [];

	private $params = [];

	/**
	 * @param array &$result
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	public function execute(array &$result)
	{
		if (!Main\Loader::includeModule("crm"))
		{
			return self::STOP_EXECUTING;
		}

		$this->initParams();

		$this->orderList = self::isUpdateOrder() ? $this->getErrorOrders() : $this->getOrders();
		if (!$this->orderList)
		{
			self::unregisterEventHandler();
			self::unregisterOrderUpdateEventHandler();

			self::setFinishStatus();

			if ((boolean)self::getErrors()->fetch())
			{
				$this->addAdminErrorNotify(Loc::getMessage("CRM_ENTITY_CREATOR_STEPPER_ERROR_NOTIFY"));
			}
			else
			{
				$this->addAdminNormalNotify(
					Loc::getMessage("CRM_ENTITY_CREATOR_STEPPER_SUCCESS_NOTIFY", [
						"#ORDER_LINK#" => $this->getPathToOrderList()
					])
				);
			}

			return self::STOP_EXECUTING;
		}

		$this->createCrmEntity();

		$result = [
			"count" => self::isUpdateOrder() ? $this->getErrorOrderCount() : $this->getOrderCount(),
			"steps" => $this->params["updated_order_count"],
		];

		return self::CONTINUE_EXECUTING;
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function createCrmEntity()
	{
		$timeStart = Main\Diag\Helper::getCurrentMicrotime();
		foreach ($this->orderList as $order)
		{
			try
			{
				$crmEntity = new CrmEntityCreator($order);
				$resultAdd = $crmEntity->create();
				if (!$resultAdd->isSuccess())
				{
					$errorMessages = $resultAdd->getErrorMessages();
					$this->setError($order->getId(), implode(" ", $errorMessages));
				}
				else
				{
					if (self::isUpdateOrder())
					{
						$this->deleteError($order->getId());
					}
				}
			}
			catch (\Exception $ex)
			{
				$this->setError($order->getId(), $ex->getMessage());
			}

			$this->updateParams($order->getId());

			$timeEnd = Main\Diag\Helper::getCurrentMicrotime();
			if ($timeEnd - $timeStart > self::MAX_EXECUTION_TIME)
			{
				break;
			}
		}
	}

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function initParams()
	{
		$params = Option::get(self::$moduleId, self::STEPPER_PARAMS, "");
		$params = ($params !== "" ? @unserialize($params) : []);
		$this->params = (is_array($params) ? $params : []);

		if (empty($this->params))
		{
			$this->params = [
				"last_order_id" => null,
				"updated_order_count" => 0
			];
		}
	}

	/**
	 * @param $orderId
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function updateParams($orderId)
	{
		$this->params["last_order_id"] = $orderId;
		$this->params["updated_order_count"]++;

		Option::set(self::$moduleId, self::STEPPER_PARAMS, serialize($this->params));
	}

	/**
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	private function getOrders()
	{
		$parameters = [
			"order" => ["ID" => "ASC"],
			"limit" => self::MAX_ORDERS,
		];
		if ($this->params["last_order_id" ] !== null)
		{
			$parameters["filter"] = [">ID" => $this->params["last_order_id"]];
		}

		return Order\Order::loadByFilter($parameters);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getErrorOrders()
	{
		$parameters = [
			"order" => ["ORDER_ID" => "ASC"],
			"limit" => self::MAX_ORDERS,
		];
		if ($this->params["last_order_id" ] !== null)
		{
			$parameters["filter"] = [">ORDER_ID" => $this->params["last_order_id"]];
		}

		$errorOrderIdList = [];
		$orderErrorIterator = self::getErrors($parameters);
		while($orderError = $orderErrorIterator->fetch())
		{
			$errorOrderIdList[] = $orderError["ORDER_ID"];
		}

		if ($errorOrderIdList)
		{
			$parameters = [
				"filter" => ["ID" => $errorOrderIdList]
			];

			$orders = Order\Order::loadByFilter($parameters);
			$ordersIdList = [];
			foreach ($orders as $order)
			{
				$ordersIdList[] = $order->getId();
			}

			$diffOrderListId = array_diff($errorOrderIdList, $ordersIdList);
			foreach ($diffOrderListId as $diffOrderId)
			{
				self::deleteError($diffOrderId);
			}

			return $orders;
		}

		return [];
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	private function getOrderCount()
	{
		return Order\Order::getList([
			"select" => ["CNT"],
			"runtime" => [
				new Main\Entity\ExpressionField("CNT", "COUNT(*)")
			]
		])->fetch()["CNT"];
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getErrorOrderCount()
	{
		$optionValue = Option::get(self::$moduleId, self::ORDER_CONVERTER_CRM_ERROR_COUNT, false);
		if ($optionValue === false)
		{
			$optionValue = Sale\Internals\OrderConverterCrmErrorTable::getList([
				"select" => ["CNT"],
				"runtime" => [
					new Main\Entity\ExpressionField("CNT", "COUNT(*)")
				]
			])->fetch()["CNT"];

			Option::set(self::$moduleId, self::ORDER_CONVERTER_CRM_ERROR_COUNT, $optionValue);
		}

		return $optionValue;
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function setFinishStatus()
	{
		Option::set(self::$moduleId, self::ORDER_CONVERT_IS_FINISH, "Y");
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function isFinished()
	{
		return (Option::get(self::$moduleId, self::ORDER_CONVERT_IS_FINISH, "N") === "Y");
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private static function isUpdateOrder()
	{
		return (Option::get(self::$moduleId, self::UPDATE_ORDER_CONVERTER_CRM_ERROR_TABLE, "N") === "Y");
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function isNeedStub()
	{
		$isShow = false;
		if (Option::get("sale", self::IS_CRM_SITE_MASTER_OPENED, "N") === "Y")
		{
			if
			(
				Option::get("sale", self::IS_SALE_CRM_SITE_MASTER_STUB, "N") === "Y"
				&&
				Option::get("sale", self::IS_SALE_CRM_SITE_MASTER_FINISH, "N") === "Y"
			)
			{
				$isShow = true;
			}
		}
		else
		{
			$isShow = Main\ModuleManager::isModuleInstalled("crm");
		}

		if ($isShow)
		{
			global $USER;
			if (Option::get('sale', self::PREFIX_OPTION_ADMIN_PANEL_IS_ENABLED.$USER->GetID()) !== 'Y')
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public static function isAgent()
	{
		/** @noinspection PhpUndefinedClassInspection */
		return (bool)\CAgent::GetList(
			[],
			[
				"NAME" => __CLASS__."::execAgent();"
			]
		)->Fetch();
	}

	/**
	 * Show progress bar in crm and shop section
	 *
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function showProgressBar(): void
	{
		if (defined("ADMIN_SECTION")
			|| (defined("SITE_TEMPLATE_ID") && SITE_TEMPLATE_ID !== "bitrix24")
		)
		{
			return;
		}

		if (self::getCrmSiteId() !== SITE_ID)
		{
			return;
		}

		/** @noinspection PhpVariableNamingConventionInspection */
		global $APPLICATION;

		$currentPage = $APPLICATION->getCurPage();
		if ((mb_strpos($currentPage, "/crm/") !== false) || (mb_strpos($currentPage, "/shop/") !== false))
		{
			$ids = ["sale" => __CLASS__];
			$content = self::getHtml($ids, Loc::getMessage("CRM_ENTITY_CREATOR_STEPPER_TITLE"));
			if ($content)
			{
				$APPLICATION->AddViewContent("above_pagetitle", $content);
			}

			unset($content);
		}
	}

	/**
	 * @return string
	 */
	public static function getTitle()
	{
		return Loc::getMessage("CRM_ENTITY_CREATOR_STEPPER_TITLE");
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function bindAgent(): void
	{
		if (defined("ADMIN_SECTION")
			|| (defined("SITE_TEMPLATE_ID") && SITE_TEMPLATE_ID !== "bitrix24")
			|| (!Main\Loader::includeModule("crm"))
		)
		{
			return;
		}

		if (self::getCrmSiteId() !== SITE_ID)
		{
			return;
		}

		if (!\CAllCrmInvoice::installExternalEntities())
		{
			return;
		}

		if (!self::isAgent()
			&& !self::isFinished()
		)
		{
			include_once $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/components/bitrix/sale.crm.site.master/tools/sitepatcher.php";
			$sitePatcher = \Bitrix\Sale\CrmSiteMaster\Tools\SitePatcher::getInstance();
			$sitePatcher->setCrmUserGroups();
			$sitePatcher->setCrmGroupRights();

			// delete options
			\Bitrix\Sale\CrmSiteMaster\Tools\SitePatcher::deleteEmployeesGroupId();
			\Bitrix\Sale\CrmSiteMaster\Tools\SitePatcher::deleteCompanyDepartmentId();
			\Bitrix\Sale\CrmSiteMaster\Tools\SitePatcher::retrieveConfig1C();

			// create agent
			self::bind(5);
		}
	}

	/**
	 * Register event handler for show progressbar
	 */
	public static function registerEventHandler()
	{
		RegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "showProgressBar", 500);

		RegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "bindAgent", 500);
	}

	/**
	 * Unregister event handler for show progressbar
	 */
	public static function unregisterEventHandler()
	{
		UnRegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "showProgressBar");

		UnRegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "bindAgent");
	}

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function bindAgentOrderUpdate()
	{
		if (!self::isAgent())
		{
			Option::delete(self::$moduleId, ["name" => self::STEPPER_PARAMS]);
			Option::delete(self::$moduleId, ["name" => self::ORDER_CONVERT_IS_FINISH]);

			Option::set(self::$moduleId, self::UPDATE_ORDER_CONVERTER_CRM_ERROR_TABLE, "Y");

			// create agent
			self::bind(5);
		}
	}

	/**
	 * Register event handler for show progressbar
	 *
	 * @throws Main\ArgumentNullException
	 */
	public static function registerOrderUpdateEventHandler()
	{
		Option::delete(self::$moduleId, ["name" => self::ORDER_CONVERT_IS_FINISH]);
		Option::delete(self::$moduleId, ["name" => self::ORDER_CONVERTER_CRM_ERROR_COUNT]);

		RegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "showProgressBar", 500);

		RegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "bindAgentOrderUpdate", 500);
	}

	/**
	 * Unregister event handler for show progressbar
	 */
	public static function unregisterOrderUpdateEventHandler()
	{
		UnRegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "showProgressBar");

		UnRegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "bindAgentOrderUpdate");
	}

	/**
	 * @param $orderId
	 * @param $errorMessage
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	private function setError($orderId, $errorMessage): void
	{
		$orderRow = Sale\Internals\OrderConverterCrmErrorTable::getList([
			'filter' => ["ORDER_ID" => $orderId],
		])->fetch();
		if (!$orderRow)
		{
			$this->addError($orderId, $errorMessage);
		}
		else
		{
			$this->updateError($orderId, $errorMessage);
		}
	}
	/**
	 * @param $orderId
	 * @param $errorMessage
	 * @throws \Exception
	 */
	private function addError($orderId, $errorMessage)
	{
		Sale\Internals\OrderConverterCrmErrorTable::add([
			"ORDER_ID" => $orderId,
			"ERROR" => $errorMessage
		]);
	}

	/**
	 * @param $orderId
	 * @param $errorMessage
	 * @throws \Exception
	 */
	private function updateError($orderId, $errorMessage)
	{
		Sale\Internals\OrderConverterCrmErrorTable::update($orderId, [
			"ERROR" => $errorMessage
		]);
	}

	/**
	 * @param $orderId
	 * @throws \Exception
	 */
	private function deleteError($orderId)
	{
		$orderRow = Sale\Internals\OrderConverterCrmErrorTable::getList([
			"select" => ["ID"],
			"filter" => ["ORDER_ID" => $orderId],
		])->fetch();
		if ($orderRow)
		{
			Sale\Internals\OrderConverterCrmErrorTable::delete($orderRow["ID"]);
		}
	}

	/**
	 * @param array $parameters
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getErrors(array $parameters = [])
	{
		return Sale\Internals\OrderConverterCrmErrorTable::getList($parameters);
	}

	/**
	 * @param $message
	 */
	private function addAdminNormalNotify($message)
	{
		$this->addAdminNotify($message, \CAdminNotify::TYPE_NORMAL);
	}

	/**
	 * @param $message
	 */
	private function addAdminErrorNotify($message)
	{
		$this->addAdminNotify($message, \CAdminNotify::TYPE_ERROR);
	}

	/**
	 * Add notification in admin section
	 * @param $message
	 * @param $notifyType
	 */
	private function addAdminNotify($message, $notifyType)
	{
		\CAdminNotify::Add([
			"MODULE_ID" => "sale",
			"TAG" => "crm_entity_stepper",
			"MESSAGE" => $message,
			"NOTIFY_TYPE" => $notifyType,
			"PUBLIC_SECTION" => "N",
		]);
	}

	/**
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private static function getCrmSiteId()
	{
		return Option::get(self::$moduleId, self::WIZARD_SITE_ID);
	}

	/**
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getPathToOrderList()
	{
		$site = Main\SiteTable::getList([
			"select" => ["SERVER_NAME"],
			"filter" => ["=LID" => self::getCrmSiteId()]
		])->fetch();

		$siteUrl = (Main\Context::getCurrent()->getRequest()->isHttps() ? "https://" : "http://").$site["SERVER_NAME"];
		$pathToOderList = Option::get("crm", "path_to_order_list", "/shop/orders/");

		return $siteUrl.$pathToOderList;
	}

	/**
	 * @param $params
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function OnAfterUserLogin($params)
	{
		$value = Option::get('sale', self::PREFIX_OPTION_ADMIN_PANEL_IS_ENABLED.$params['USER_ID'], '');
		if ($value !== '' && $value !== 'N')
		{
			Option::set('sale', self::PREFIX_OPTION_ADMIN_PANEL_IS_ENABLED.$params['USER_ID'], 'N');
		}
	}
}