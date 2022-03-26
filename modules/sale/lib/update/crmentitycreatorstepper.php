<?php
namespace Bitrix\Sale\Update;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Crm\Order;
use Bitrix\Crm\Settings;
use Bitrix\Crm\Timeline;
use Bitrix\Crm\Order\Matcher;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;

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
	 */
	public function create(): Sale\Result
	{
		$result = new Sale\Result();

		$contactCompanyCollection = $this->order->getContactCompanyCollection();
		if ($contactCompanyCollection && $contactCompanyCollection->isEmpty())
		{
			$this->addContactCompany();
		}

		$this->setContactCompanyRequisites();

		if (!$this->isSetResponsible())
		{
			$this->setResponsible();
		}

		$saveOrderResult = $this->order->save();
		if ($saveOrderResult->isSuccess())
		{
			$this->addTimeLines();
		}
		else
		{
			$result->addErrors($saveOrderResult->getErrors());
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	private function isSetResponsible(): bool
	{
		return (bool)$this->order->getField("RESPONSIBLE_ID");
	}

	private function setResponsible(): void
	{
		$this->order->setFieldNoDemand(
			"RESPONSIBLE_ID",
			Settings\OrderSettings::getCurrent()->getDefaultResponsibleId()
		);
	}

	/**
	 * @return void
	 */
	private function addContactCompany(): void
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
	 */
	private function setContactCompanyRequisites(): void
	{
		$collection = $this->order->getContactCompanyCollection();
		if (!$collection)
		{
			return;
		}

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

	private function addTimeLines(): void
	{
		// add
		$this->addTimelineEntryOnCreate();

		$historyChanges = $this->getHistoryChanges();
		foreach ($historyChanges as $historyChange)
		{
			// status
			if ($historyChange["TYPE"] === "ORDER_STATUS_CHANGED")
			{
				$this->addTimelineEntryOnStatusModify($historyChange["DATA"]["OLD"], $historyChange["DATA"]["CURRENT"]);
			}
		}

		// cancel
		$this->addTimelineEntryOnCancel();
	}

	/**
	 * @return void;
	 */
	private function addTimelineEntryOnCreate(): void
	{
		Timeline\OrderController::getInstance()->onCreate(
			$this->order->getId(),
			[
				"ORDER_FIELDS" => [
					"ID" => (int)$this->order->getId(),
					"CREATED_BY" => $this->order->getField("CREATED_BY"),
					"RESPONSIBLE_ID" => $this->order->getField("RESPONSIBLE_ID"),
					"DATE_INSERT" => $this->order->getField("DATE_INSERT"),
					"PRICE" => $this->order->getField("PRICE"),
					"CURRENCY" => $this->order->getField("CURRENCY")
				],
				"BINDINGS" => [
					[
						"ENTITY_TYPE_ID" => \CCrmOwnerType::Order,
						"ENTITY_ID" => $this->order->getId()
					]
				],
			]
		);
	}

	/**
	 * @return void;
	 */
	private function addTimelineEntryOnCancel(): void
	{
		if ($this->order->getField("CANCELED") !== "Y")
		{
			return;
		}

		$fields = [
			"ID" => $this->order->getId(),
			"CANCELED" => $this->order->getField("CANCELED"),
		];

		$fields["REASON_CANCELED"] = $this->order->getField("REASON_CANCELED");
		$fields["EMP_CANCELED_ID"] = $this->order->getField("EMP_CANCELED_ID");

		Timeline\OrderController::getInstance()->onCancel(
			$this->order->getId(),
			[
				"FIELDS" => $fields,
				"BINDINGS" => [
					[
						"ENTITY_TYPE_ID" => \CCrmOwnerType::Order,
						"ENTITY_ID" => $this->order->getId()
					]
				],
			]
		);
	}

	/**
	 * @param $prevStatus
	 * @param $currentStatus
	 * @return void;
	 */
	private function addTimelineEntryOnStatusModify($prevStatus, $currentStatus): void
	{
		$modifyParams = [
			"PREVIOUS_FIELDS" => ["STATUS_ID" => $prevStatus],
			"CURRENT_FIELDS" => [
				"STATUS_ID" => $currentStatus,
				"EMP_STATUS_ID" => $this->order->getField("EMP_STATUS_ID")
			],
			"BINDINGS" => [
				[
					"ENTITY_TYPE_ID" => \CCrmOwnerType::Order,
					"ENTITY_ID" => $this->order->getId()
				]
			],
		];

		Timeline\OrderController::getInstance()->onModify($this->order->getId(), $modifyParams);
	}

	/**
	 * @return array
	 */
	private function getHistoryChanges(): array
	{
		$arHistoryData = [];

		$arFilterHistory = ["ORDER_ID" => $this->order->getId()];
		$arFilterHistory["@TYPE"] = ["ORDER_STATUS_CHANGED"];

		$dbOrderChange = Sale\Internals\OrderChangeTable::getList([
			"select" => ["*"],
			"filter" => $arFilterHistory,
			"order" => [
				"DATE_CREATE" => "DESC",
				"ID" => "ASC"
			],
			'limit' => 10,
		]);
		while ($arChangeRecord = $dbOrderChange->fetch())
		{
			$arHistoryData[] = $arChangeRecord;
		}

		Main\Type\Collection::sortByColumn($arHistoryData, ['ID' => SORT_ASC]);

		$dbRes = new \CDBResult();
		$dbRes->InitFromArray($arHistoryData);

		$result = [];
		while ($arRes = $dbRes->Fetch())
		{
			if (\CheckSerializedData($arRes["DATA"]))
			{
				$data = unserialize($arRes["DATA"], ['allowed_classes' => false]);
				if ($arRes["TYPE"] === "ORDER_STATUS_CHANGED")
				{
					$result[] = [
						"TYPE" => $arRes["TYPE"],
						"DATA" => [
							"CURRENT" => $data["STATUS_ID"],
							"OLD" => $data["OLD_STATUS_ID"],
						],
					];
				}
			}
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
	public const CONTINUE_EXECUTING = true;
	public const STOP_EXECUTING = false;

	/** @var string */
	public const IS_SALE_CRM_SITE_MASTER_STUB = "~IS_SALE_CRM_SITE_MASTER_STUB";

	/** @var string */
	public const ORDER_CONVERT_IS_FINISH = "~ORDER_CONVERT_IS_FINISH";

	/** @var string */
	public const IS_SALE_CRM_SITE_MASTER_FINISH = "~IS_SALE_CRM_SITE_MASTER_FINISH";

	public const PREFIX_OPTION_ADMIN_PANEL_IS_ENABLED = "~ADMIN_PANEL_IS_ENABLED_FOR_";

	public const IS_CRM_SITE_MASTER_OPENED = "~IS_CRM_SITE_MASTER_OPENED";

	/** @var string */
	public const WIZARD_SITE_ID = "~CRM_WIZARD_SITE_ID";

	/** @var string */
	public const STEPPER_PARAMS = "~CRM_ENTITY_CREATOR_STEPPER_PARAMS";

	public const UPDATE_ORDER_CONVERTER_CRM_ERROR_TABLE = "~UPDATE_ORDER_CONVERTER_CRM_ERROR_TABLE";

	public const ORDER_CONVERTER_CRM_ERROR_COUNT = "~ORDER_CONVERTER_CRM_ERROR_COUNT";

	/** @var int max executing time in sec */
	public const MAX_EXECUTION_TIME = 5;

	/** @var int max orders of iteration */
	public const MAX_ORDERS = 100;

	protected static $moduleId = "sale";

	private $orderList =  [];

	private $params = [];

	/**
	 * @param array &$result
	 * @return bool
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

			if (self::getErrors()->fetch())
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

	private function createCrmEntity(): void
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

	private function initParams(): void
	{
		$params = Option::get(self::$moduleId, self::STEPPER_PARAMS, "");
		if ($params !== "" && \CheckSerializedData($params))
		{
			$params = unserialize($params, ['allowed_classes' => false]);
		}

		$this->params = (\is_array($params) ? $params : []);
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
	 */
	private function updateParams($orderId): void
	{
		$this->params["last_order_id"] = $orderId;
		$this->params["updated_order_count"]++;

		Option::set(self::$moduleId, self::STEPPER_PARAMS, serialize($this->params));
	}

	/**
	 * @return array|null
	 */
	private function getOrders(): ?array
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
				$this->deleteError($diffOrderId);
			}

			return $orders;
		}

		return [];
	}

	private function getOrderCount()
	{
		return Order\Order::getList([
			"select" => ["CNT"],
			"runtime" => [
				new Main\Entity\ExpressionField("CNT", "COUNT(*)")
			]
		])->fetch()["CNT"];
	}

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

	public static function setFinishStatus(): void
	{
		Option::set(self::$moduleId, self::ORDER_CONVERT_IS_FINISH, "Y");
	}

	/**
	 * @return bool
	 */
	public static function isFinished(): bool
	{
		return (Option::get(self::$moduleId, self::ORDER_CONVERT_IS_FINISH, "N") === "Y");
	}

	/**
	 * @return bool
	 */
	private static function isUpdateOrder(): bool
	{
		return (Option::get(self::$moduleId, self::UPDATE_ORDER_CONVERTER_CRM_ERROR_TABLE, "N") === "Y");
	}

	/**
	 * @return bool
	 */
	public static function isNeedStub(): bool
	{
		$isShow = false;
		if (Option::get("sale", self::IS_CRM_SITE_MASTER_OPENED, "N") === "Y")
		{
			if
			(
				Option::get("sale", self::IS_SALE_CRM_SITE_MASTER_STUB, "N") === "Y"
				&& Option::get("sale", self::IS_SALE_CRM_SITE_MASTER_FINISH, "N") === "Y"
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
	public static function isAgent(): bool
	{
		return (bool)\CAgent::GetList(
			[],
			[
				"NAME" => __CLASS__."::execAgent();"
			]
		)->Fetch();
	}

	/**
	 * Show progress bar in crm and shop section
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

	public static function bindAgent(): void
	{
		if (
			defined("ADMIN_SECTION")
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

		if (
			!self::isAgent()
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
	public static function registerEventHandler(): void
	{
		RegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "showProgressBar", 500);
		RegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "bindAgent", 500);
	}

	/**
	 * Unregister event handler for show progressbar
	 */
	public static function unregisterEventHandler(): void
	{
		UnRegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "showProgressBar");
		UnRegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "bindAgent");
	}

	public static function bindAgentOrderUpdate(): void
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
	 */
	public static function registerOrderUpdateEventHandler(): void
	{
		Option::delete(self::$moduleId, ["name" => self::ORDER_CONVERT_IS_FINISH]);
		Option::delete(self::$moduleId, ["name" => self::ORDER_CONVERTER_CRM_ERROR_COUNT]);

		RegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "showProgressBar", 500);
		RegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "bindAgentOrderUpdate", 500);
	}

	/**
	 * Unregister event handler for show progressbar
	 */
	public static function unregisterOrderUpdateEventHandler(): void
	{
		UnRegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "showProgressBar");
		UnRegisterModuleDependences("main", "OnEpilog", self::$moduleId, __CLASS__, "bindAgentOrderUpdate");
	}

	/**
	 * @param $orderId
	 * @param $errorMessage
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
	 */
	private function addError($orderId, $errorMessage): void
	{
		Sale\Internals\OrderConverterCrmErrorTable::add([
			"ORDER_ID" => $orderId,
			"ERROR" => $errorMessage
		]);
	}

	/**
	 * @param $orderId
	 * @param $errorMessage
	 */
	private function updateError($orderId, $errorMessage): void
	{
		Sale\Internals\OrderConverterCrmErrorTable::update($orderId, [
			"ERROR" => $errorMessage
		]);
	}

	/**
	 * @param $orderId
	 */
	private function deleteError($orderId): void
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
	 */
	public static function getErrors(array $parameters = [])
	{
		return Sale\Internals\OrderConverterCrmErrorTable::getList($parameters);
	}

	/**
	 * @param $message
	 */
	private function addAdminNormalNotify($message): void
	{
		$this->addAdminNotify($message, \CAdminNotify::TYPE_NORMAL);
	}

	/**
	 * @param $message
	 */
	private function addAdminErrorNotify($message): void
	{
		$this->addAdminNotify($message, \CAdminNotify::TYPE_ERROR);
	}

	/**
	 * Add notification in admin section
	 * @param $message
	 * @param $notifyType
	 */
	private function addAdminNotify($message, $notifyType): void
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
	 */
	private static function getCrmSiteId(): string
	{
		return Option::get(self::$moduleId, self::WIZARD_SITE_ID);
	}

	/**
	 * @return string
	 */
	public function getPathToOrderList(): string
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
	 */
	public static function OnAfterUserLogin($params): void
	{
		$value = Option::get('sale', self::PREFIX_OPTION_ADMIN_PANEL_IS_ENABLED.$params['USER_ID'], '');
		if ($value !== '' && $value !== 'N')
		{
			Option::set('sale', self::PREFIX_OPTION_ADMIN_PANEL_IS_ENABLED.$params['USER_ID'], 'N');
		}
	}
}