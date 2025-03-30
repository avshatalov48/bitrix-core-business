<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Cashbox\CheckManager;
use Bitrix\Sale\Cashbox\Internals\CashboxCheckTable;
use Bitrix\Sale\Cashbox\Manager;
use Bitrix\Sale\Compatible;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Helpers;
use Bitrix\Sale\TradingPlatform\Platform;

Main\Localization\Loc::loadMessages(__FILE__);

class Notify
{

	const EVENT_ORDER_STATUS_SEND_EMAIL = "OnOrderStatusSendEmail";
	const EVENT_ORDER_STATUS_EMAIL = "OnSaleStatusEMail";

	const EVENT_ON_ORDER_NEW_SEND_EMAIL = "OnOrderNewSendEmail";
	const EVENT_ORDER_NEW_SEND_EMAIL_EVENT_NAME = "SALE_NEW_ORDER";

	const EVENT_ON_SHIPMENT_DELIVER_SEND_EMAIL = "OnOrderDeliverSendEmail";
	const EVENT_SHIPMENT_DELIVER_SEND_EMAIL_EVENT_NAME = "SALE_ORDER_DELIVERY";

	const EVENT_ON_CHECK_PRINT_SEND_EMAIL = "SALE_CHECK_PRINT";
	const EVENT_ON_CHECK_PRINT_ERROR_SEND_EMAIL = "SALE_CHECK_PRINT_ERROR";
	const EVENT_ON_CHECK_VALIDATION_ERROR_SEND_EMAIL = "SALE_CHECK_VALIDATION_ERROR";

	const EVENT_ON_ORDER_PAID_SEND_EMAIL = "OnOrderPaySendEmail";

	const EVENT_ON_ORDER_CANCEL_SEND_EMAIL = "OnOrderCancelSendEmail";

	const EVENT_ORDER_PAID_SEND_EMAIL_EVENT_NAME = "SALE_ORDER_PAID";
	const EVENT_ORDER_CANCEL_SEND_EMAIL_EVENT_NAME = "SALE_ORDER_CANCEL";
	const EVENT_ORDER_TRACKING_NUMBER_SEND_EMAIL_EVENT_NAME = "SALE_ORDER_TRACKING_NUMBER";
	const EVENT_ORDER_STATUS_CHANGED_SEND_EMAIL_EVENT_NAME = "SALE_STATUS_CHANGED";
	const EVENT_SHIPMENT_TRACKING_NUMBER_SEND_EMAIL_EVENT_NAME = "SALE_ORDER_TRACKING_NUMBER";


	const EVENT_DEFAULT_STATUS_CHANGED_ID = "SALE_STATUS_CHANGED_";
	const EVENT_SHIPMENT_STATUS_SEND_EMAIL = "OnSaleShipmentStatusSendEmail";
	const EVENT_SHIPMENT_STATUS_EMAIL =	"OnSaleShipmentStatusEMail";

	const EVENT_ORDER_ALLOW_PAY_SEND_EMAIL_EVENT_NAME = "SALE_ORDER_ALLOW_PAY";
	const EVENT_ON_ORDER_ALLOW_PAY_STATUS_EMAIL =	"OnSaleOrderAllowPayStatusEMail";

	const EVENT_MOBILE_PUSH_ORDER_CREATED = "ORDER_CREATED";
	const EVENT_MOBILE_PUSH_ORDER_STATUS_CHANGE = "ORDER_STATUS_CHANGED";
	const EVENT_MOBILE_PUSH_ORDER_CANCELED = "ORDER_CANCELED";
	const EVENT_MOBILE_PUSH_ORDER_PAID = "ORDER_PAYED";
	const EVENT_MOBILE_PUSH_ORDER_CHECK_ERROR = "ORDER_CHECK_ERROR";
	const EVENT_MOBILE_PUSH_SHIPMENT_ALLOW_DELIVERY = "ORDER_DELIVERY_ALLOWED";

	protected static $cacheUserData = array();

	protected static $sentEventList = array();

	protected static $disableNotify = false;

	protected function __construct() {}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @param Internals\Entity $entity
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendOrderNew(Internals\Entity $entity)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}

		if (!$entity instanceof Order)
		{
			throw new Main\ArgumentTypeException('entity', '\Bitrix\Sale\Order');
		}

		if (static::hasSentEvent($entity->getId(), static::EVENT_ORDER_NEW_SEND_EMAIL_EVENT_NAME))
		{
			return $result;
		}

		if (!$entity->isNew())
		{
			return $result;
		}

		$separator = "<br/>";

		$eventName = static::EVENT_ORDER_NEW_SEND_EMAIL_EVENT_NAME;

		$filter = array(
			"EVENT_NAME" => $eventName,
			"EVENT_NAME_EXACT_MATCH" => 'Y',
			'ACTIVE' => 'Y',
		);

		if ($entity instanceof OrderBase)
		{
			$filter['SITE_ID'] = $entity->getSiteId();
		}
		elseif (defined('SITE_ID') && SITE_ID != '')
		{
			$filter['SITE_ID'] = SITE_ID;
		}

		$res = \CEventMessage::GetList('', '', $filter);
		if ($eventMessage = $res->Fetch())
		{
			if ($eventMessage['BODY_TYPE'] == 'text')
			{
				$separator = "\n";
			}
		}

		$basketList = '';
		/** @var Basket $basket */
		$basket = $entity->getBasket();
		if ($basket)
		{
			$basketTextList = $basket->getListOfFormatText();
			if (!empty($basketTextList))
			{
				foreach ($basketTextList as $basketItemCode => $basketItemData)
				{
					$basketList .= $basketItemData.$separator;
				}
			}
		}

		$fields = Array(
			"ORDER_ID" => $entity->getField("ACCOUNT_NUMBER"),
			"ORDER_REAL_ID" => $entity->getField("ID"),
			"ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($entity->getField("ACCOUNT_NUMBER"))),
			"ORDER_DATE" => $entity->getDateInsert()->toString(),
			"ORDER_USER" => static::getUserName($entity),
			"PRICE" => SaleFormatCurrency($entity->getPrice(), $entity->getCurrency()),
			"BCC" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
			"EMAIL" => static::getUserEmail($entity),
			"ORDER_LIST" => $basketList,
			"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
			"DELIVERY_PRICE" => $entity->getDeliveryPrice(),
			"ORDER_PUBLIC_URL" => Helpers\Order::isAllowGuestView($entity) ? Helpers\Order::getPublicLink($entity) : ""
		);

		$send = true;

		foreach(GetModuleEvents("sale", static::EVENT_ON_ORDER_NEW_SEND_EMAIL, true) as $oldEvent)
		{
			if (ExecuteModuleEventEx($oldEvent, array($entity->getId(), &$eventName, &$fields)) === false)
			{
				$send = false;
			}
		}

		if($send)
		{
			$event = new \CEvent;
			$event->Send($eventName, $entity->getField('LID'), $fields, "Y", "", array(),static::getOrderLanguageId($entity));
		}

		static::addSentEvent($entity->getId(), static::EVENT_ORDER_NEW_SEND_EMAIL_EVENT_NAME);

		\CSaleMobileOrderPush::send(static::EVENT_MOBILE_PUSH_ORDER_CREATED, array("ORDER" => static::getOrderFields($entity)));

		return $result;
	}

	/**
	 * @param Internals\Entity $entity
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendOrderCancel(Internals\Entity $entity)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}


		if (!$entity instanceof Order)
		{
			throw new Main\ArgumentTypeException('entity', '\Bitrix\Sale\Order');
		}

		if (static::hasSentEvent($entity->getId(), static::EVENT_ORDER_CANCEL_SEND_EMAIL_EVENT_NAME))
		{
			return $result;
		}

		if (!$entity->isCanceled())
		{
			return $result;
		}

		$fields = Array(
			"ORDER_ID" => $entity->getField("ACCOUNT_NUMBER"),
			"ORDER_REAL_ID" => $entity->getField("ID"),
			"ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($entity->getField("ACCOUNT_NUMBER"))),
			"ORDER_DATE" => '',
			"EMAIL" => static::getUserEmail($entity),
			"ORDER_CANCEL_DESCRIPTION" => $entity->getField('REASON_CANCELED'),
			"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
			"ORDER_PUBLIC_URL" => Helpers\Order::isAllowGuestView($entity) ? Helpers\Order::getPublicLink($entity) : ""
		);

		$dateInsert = $entity->getDateInsert();
		if (isset($dateInsert) && $dateInsert instanceof Main\Type\Date)
		{
			$fields['ORDER_DATE'] = $dateInsert->toString();
		}


		$eventName = static::EVENT_ORDER_CANCEL_SEND_EMAIL_EVENT_NAME;
		$send = true;

		foreach(GetModuleEvents("sale", static::EVENT_ON_ORDER_CANCEL_SEND_EMAIL, true) as $oldEvent)
		{
			if (ExecuteModuleEventEx($oldEvent, array($entity->getId(), &$eventName, &$fields)) === false)
			{
				$send = false;
			}
		}

		if($send)
		{
			$event = new \CEvent;
			$event->Send($eventName, $entity->getField('LID'), $fields, "Y", "", array(), static::getOrderLanguageId($entity));
		}

		\CSaleMobileOrderPush::send(static::EVENT_MOBILE_PUSH_ORDER_CANCELED, array("ORDER" => static::getOrderFields($entity)));

		static::addSentEvent($entity->getId(), static::EVENT_ORDER_CANCEL_SEND_EMAIL_EVENT_NAME);

		return $result;
	}

	/**
	 * @param Internals\Entity $entity
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendOrderPaid(Internals\Entity $entity)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}

		if (!$entity instanceof Order)
		{
			throw new Main\ArgumentTypeException('entity', '\Bitrix\Sale\Order');
		}

		if (static::hasSentEvent($entity->getId(), static::EVENT_ORDER_PAID_SEND_EMAIL_EVENT_NAME))
		{
			return $result;
		}

		if (!$entity->isPaid())
		{
			return $result;
		}



		$fields = Array(
			"ORDER_ID" => $entity->getField("ACCOUNT_NUMBER"),
			"ORDER_REAL_ID" => $entity->getField("ID"),
			"ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($entity->getField("ACCOUNT_NUMBER"))),
			"ORDER_DATE" => '',
			"EMAIL" => static::getUserEmail($entity),
			"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
			"ORDER_PUBLIC_URL" => Helpers\Order::isAllowGuestView($entity) ? Helpers\Order::getPublicLink($entity) : ""
		);
		$dateInsert = $entity->getDateInsert();
		if (isset($dateInsert) && $dateInsert instanceof Main\Type\Date)
		{
			$fields['ORDER_DATE'] = $dateInsert->toString();
		}

		$eventName = static::EVENT_ORDER_PAID_SEND_EMAIL_EVENT_NAME;
		$send = true;

		foreach(GetModuleEvents("sale", static::EVENT_ON_ORDER_PAID_SEND_EMAIL, true) as $oldEvent)
		{
			if (ExecuteModuleEventEx($oldEvent, array($entity->getId(), &$eventName, &$fields)) === false)
			{
				$send = false;
			}
		}

		if($send)
		{
			$event = new \CEvent;
			$event->Send($eventName, $entity->getField('LID'), $fields, "Y", "", array(), static::getOrderLanguageId($entity));
		}

		\CSaleMobileOrderPush::send(static::EVENT_MOBILE_PUSH_ORDER_PAID, array("ORDER" => static::getOrderFields($entity)));

		static::addSentEvent($entity->getId(), static::EVENT_ORDER_PAID_SEND_EMAIL_EVENT_NAME);

		return $result;
	}

	/**
	 * @param Internals\Entity $entity
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendOrderStatusChange(Internals\Entity $entity)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}

		if (!$entity instanceof Order)
		{
			throw new Main\ArgumentTypeException('entity', '\Bitrix\Sale\Order');
		}

		$statusEventName = static::EVENT_DEFAULT_STATUS_CHANGED_ID.$entity->getField("STATUS_ID");

		if (static::hasSentEvent($entity->getId(), $statusEventName))
		{
			return $result;
		}

		/** @var Internals\Fields $fields */
		$fields = $entity->getFields();
		$originalValues = $fields->getOriginalValues();

		if (array_key_exists('STATUS_ID', $originalValues) && $originalValues['STATUS_ID'] == $entity->getField("STATUS_ID"))
		{
			return $result;
		}

		static $cacheSiteData = array();

		if (!isset($cacheSiteData[$entity->getSiteId()]))
		{
			$siteRes = \CSite::GetByID($entity->getSiteId());
			$siteData = $siteRes->Fetch();
		}
		else
		{
			$siteData = $cacheSiteData[$entity->getSiteId()];
		}

		if (($statusData = \CSaleStatus::GetByID($entity->getField("STATUS_ID"), $siteData['LANGUAGE_ID'])) && $statusData['NOTIFY'] == "Y")
		{
			$fields = Array(
				"ORDER_ID" => $entity->getField("ACCOUNT_NUMBER"),
				"ORDER_REAL_ID" => $entity->getField("ID"),
				"ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($entity->getField("ACCOUNT_NUMBER"))),
				"ORDER_STATUS" => $statusData["NAME"],
				"EMAIL" => static::getUserEmail($entity),
				"ORDER_DESCRIPTION" => $statusData["DESCRIPTION"],
				"TEXT" => "",
				"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
				"ORDER_PUBLIC_URL" => Helpers\Order::isAllowGuestView($entity) ? Helpers\Order::getPublicLink($entity) : ""
			);

			if ($entity->getField("DATE_INSERT") instanceof Main\Type\Date)
			{
				$fields['ORDER_DATE'] = $entity->getField("DATE_INSERT")->toString();
			}

			foreach(GetModuleEvents("sale", static::EVENT_ORDER_STATUS_EMAIL, true) as $oldEvent)
			{
				$fields["TEXT"] = ExecuteModuleEventEx($oldEvent, array($entity->getId(), $statusData["ID"]));
			}

			$eventName = $statusEventName;

			$isSend = true;
			foreach(GetModuleEvents("sale", static::EVENT_ORDER_STATUS_SEND_EMAIL, true) as $oldEvent)
			{
				if (ExecuteModuleEventEx($oldEvent, Array($entity->getId(), &$eventName, &$fields, $entity->getField("STATUS_ID")))===false)
				{
					$isSend = false;
				}
			}

			if($isSend)
			{
				$eventMessage = new \CEventMessage;
				$eventMessageRes = $eventMessage->GetList(
					'',
					'',
					array(
						"EVENT_NAME" => $eventName,
						"EVENT_NAME_EXACT_MATCH" => 'Y',
						"SITE_ID" => $entity->getSiteId(),
						'ACTIVE' => 'Y'
					)
				);
				if (!($eventMessageData = $eventMessageRes->Fetch()))
				{
					$eventName = static::EVENT_DEFAULT_STATUS_CHANGED_ID.$entity->getField("STATUS_ID");
				}

				unset($o, $b);
				$event = new \CEvent;
				$event->Send($eventName, $entity->getSiteId(), $fields, "Y", "", array(),  $siteData['LANGUAGE_ID']);
			}
		}

		\CSaleMobileOrderPush::send(static::EVENT_MOBILE_PUSH_ORDER_STATUS_CHANGE, array("ORDER" => static::getOrderFields($entity)));

		static::addSentEvent($entity->getId(), $statusEventName);

		return $result;
	}

	/**
	 * @param Internals\Entity $entity
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendShipmentStatusChange(Internals\Entity $entity)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}

		if (!$entity instanceof Shipment)
		{
			throw new Main\ArgumentTypeException('entity', '\Bitrix\Sale\Shipment');
		}

		$statusEventName = static::EVENT_DEFAULT_STATUS_CHANGED_ID.$entity->getField("STATUS_ID");

		if (static::hasSentEvent('s'.$entity->getId(), $statusEventName))
		{
			return $result;
		}

		/** @var Internals\Fields $fields */
		$fields = $entity->getFields();
		$originalValues = $fields->getOriginalValues();

		if (array_key_exists('STATUS_ID', $originalValues) && $originalValues['STATUS_ID'] == $entity->getField("STATUS_ID"))
		{
			return $result;
		}

		static $cacheSiteData = array();

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $entity->getCollection())
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_SHIPMENT_COLLECTION_NOT_FOUND")));
			return $result;
		}

		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_ORDER_NOT_FOUND")));
			return $result;
		}


		if (!isset($cacheSiteData[$order->getSiteId()]))
		{
			$siteRes = \CSite::GetByID($order->getSiteId());
			$siteData = $siteRes->Fetch();
		}
		else
		{
			$siteData = $cacheSiteData[$order->getSiteId()];
		}

		$statusData = Internals\StatusTable::getList([
			'select' => [
				'ID',
				'NOTIFY',
				'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
			],
			'filter' => [
				'=ID' => $entity->getField("STATUS_ID"),
				'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => $siteData['LANGUAGE_ID'],
				'=TYPE' => DeliveryStatus::TYPE,
			],
			'limit'  => 1,
		])->fetch();

		if (!empty($statusData) && $statusData['NOTIFY'] == "Y")
		{
			$isSend = true;

			$fields = array(
				"ORDER_ID" => $order->getField("ACCOUNT_NUMBER"),
				"ORDER_REAL_ID" => $order->getField("ID"),
				"ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($order->getField("ACCOUNT_NUMBER"))),
				"ORDER_DATE" => $order->getDateInsert()->toString(),
				"SHIPMENT_ID" => $entity->getId(),
				"SHIPMENT_DATE" => $entity->getField("DATE_INSERT")->toString(),
				"SHIPMENT_STATUS" => $statusData["NAME"],
				"EMAIL" => static::getUserEmail($order),
				"TEXT" => "",
				"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
				"ORDER_PUBLIC_URL" => Helpers\Order::isAllowGuestView($order) ? Helpers\Order::getPublicLink($order) : ""
			);

			$eventManager = Main\EventManager::getInstance();
			if ($eventsList = $eventManager->findEventHandlers('sale', static::EVENT_SHIPMENT_STATUS_EMAIL))
			{
				$event = new Main\Event('sale', static::EVENT_SHIPMENT_STATUS_EMAIL, array(
					'EVENT_NAME' => $statusEventName,
					'VALUES' => $fields
				));
				$event->send();

				if ($event->getResults())
				{
					/** @var Main\EventResult $eventResult */
					foreach($event->getResults() as $eventResult)
					{
						if($eventResult->getType() === Main\EventResult::ERROR)
						{
							$isSend = false;
						}
						elseif($eventResult->getType() == Main\EventResult::SUCCESS)
						{
							if ($eventResultParams = $eventResult->getParameters())
							{
								/** @var Result $eventResultData */
								if (!empty($eventResultParams) && is_array($eventResultParams))
								{
									if (!empty($eventResultParams['EVENT_NAME']))
									{
										$statusEventName = $eventResultParams['EVENT_NAME'];
									}

									if (!empty($eventResultParams['VALUES']) && is_array($eventResultParams['VALUES']))
									{
										$fields = $eventResultParams['VALUES'];
									}
								}
							}
						}
					}
				}
			}

			if($isSend)
			{
				$eventMessage = new \CEventMessage;
				$eventMessageRes = $eventMessage->GetList(
					'',
					'',
					array(
						"EVENT_NAME" => $statusEventName,
						"EVENT_NAME_EXACT_MATCH" => 'Y',
						"SITE_ID" => $order->getSiteId(),
						'ACTIVE' => 'Y'
					)
				);
				if (!($eventMessageData = $eventMessageRes->Fetch()))
				{
					$statusEventName = static::EVENT_DEFAULT_STATUS_CHANGED_ID.$entity->getField("STATUS_ID");
				}

				unset($o, $b);
				$event = new \CEvent;
				$event->Send($statusEventName, $order->getSiteId(), $fields, "Y", "", array(),  $siteData['LANGUAGE_ID']);
			}
		}

		static::addSentEvent('s'.$entity->getId(), $statusEventName);

		return $result;
	}

	/**
	 * @param Internals\Entity $entity
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendOrderAllowPayStatusChange(Internals\Entity $entity)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}

		if (!$entity instanceof Order)
		{
			throw new Main\ArgumentTypeException('entity', '\Bitrix\Sale\Order');
		}

		$statusEventName = static::EVENT_ORDER_ALLOW_PAY_SEND_EMAIL_EVENT_NAME;

		if (static::hasSentEvent($entity->getId(), $statusEventName))
		{
			return $result;
		}

		/** @var Internals\Fields $fields */
		$fields = $entity->getFields();
		$originalValues = $fields->getOriginalValues();

		if (array_key_exists('STATUS_ID', $originalValues) && $originalValues['STATUS_ID'] == $entity->getField("STATUS_ID"))
		{
			return $result;
		}

		static $cacheSiteData = array();

		if (!isset($cacheSiteData[$entity->getSiteId()]))
		{
			$siteRes = \CSite::GetByID($entity->getSiteId());
			$siteData = $siteRes->Fetch();
		}
		else
		{
			$siteData = $cacheSiteData[$entity->getSiteId()];
		}

		$statusData = Internals\StatusTable::getList([
			'select' => [
				'ID',
				'NOTIFY',
				'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
			],
			'filter' => [
				'=ID' => $entity->getField("STATUS_ID"),
				'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => $siteData['LANGUAGE_ID'],
				'=TYPE' => OrderStatus::TYPE,
			],
			'limit'  => 1,
		])->fetch();

		if (!empty($statusData) && $statusData['NOTIFY'] == "Y")
		{
			$isSend = true;

			$fields = array(
				"ORDER_ID" => $entity->getField("ACCOUNT_NUMBER"),
				"ORDER_REAL_ID" => $entity->getField("ID"),
				"ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($entity->getField("ACCOUNT_NUMBER"))),
				"ORDER_DATE" => $entity->getDateInsert()->toString(),
				"ORDER_STATUS" => $statusData["NAME"],
				"EMAIL" => static::getUserEmail($entity),
				"TEXT" => "",
				"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
				"ORDER_PUBLIC_URL" => Helpers\Order::isAllowGuestView($entity) ? Helpers\Order::getPublicLink($entity) : ""
			);

			$eventManager = Main\EventManager::getInstance();
			if ($eventsList = $eventManager->findEventHandlers('sale', static::EVENT_ON_ORDER_ALLOW_PAY_STATUS_EMAIL))
			{
				$event = new Main\Event('sale', static::EVENT_ON_ORDER_ALLOW_PAY_STATUS_EMAIL, array(
					'EVENT_NAME' => $statusEventName,
					'VALUES' => $fields
				));
				$event->send();

				if ($event->getResults())
				{
					/** @var Main\EventResult $eventResult */
					foreach($event->getResults() as $eventResult)
					{
						if($eventResult->getType() === Main\EventResult::ERROR)
						{
							$isSend = false;
						}
						elseif($eventResult->getType() == Main\EventResult::SUCCESS)
						{
							if ($eventResultParams = $eventResult->getParameters())
							{
								/** @var Result $eventResultData */
								if (!empty($eventResultParams) && is_array($eventResultParams))
								{
									if (!empty($eventResultParams['EVENT_NAME']))
									{
										$statusEventName = $eventResultParams['EVENT_NAME'];
									}

									if (!empty($eventResultParams['VALUES']) && is_array($eventResultParams['VALUES']))
									{
										$fields = $eventResultParams['VALUES'];
									}
								}
							}
						}
					}
				}
			}

			if($isSend)
			{
				$eventMessage = new \CEventMessage;
				$eventMessageRes = $eventMessage->GetList(
					'',
					'',
					array(
						"EVENT_NAME" => $statusEventName,
						"EVENT_NAME_EXACT_MATCH" => 'Y',
						"SITE_ID" => $entity->getSiteId(),
						'ACTIVE' => 'Y'
					)
				);
				if (!($eventMessageData = $eventMessageRes->Fetch()))
				{
					$statusEventName = static::EVENT_ORDER_ALLOW_PAY_SEND_EMAIL_EVENT_NAME;
				}

				unset($o, $b);
				$event = new \CEvent;
				$event->Send($statusEventName, $entity->getSiteId(), $fields, "Y", "", array(), $siteData['LANGUAGE_ID']);
			}
		}

		static::addSentEvent($entity->getId(), $statusEventName);

		return $result;
	}

	/**
	 * @param Internals\Entity $entity
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendShipmentTrackingNumberChange(Internals\Entity $entity)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}

		if (!$entity instanceof Shipment)
		{
			throw new Main\ArgumentTypeException('entity', '\Bitrix\Sale\Shipment');
		}

		if (static::hasSentEvent('s'.$entity->getId(), static::EVENT_SHIPMENT_TRACKING_NUMBER_SEND_EMAIL_EVENT_NAME))
		{
			return $result;
		}

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $entity->getCollection())
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_SHIPMENT_COLLECTION_NOT_FOUND")));
			return $result;
		}

		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_ORDER_NOT_FOUND")));
			return $result;
		}

		/** @var Internals\Fields $fields */
		$fields = $order->getFields();
		$originalValues = $fields->getOriginalValues();

		if (array_key_exists('ACCOUNT_NUMBER', $originalValues) && $originalValues['ACCOUNT_NUMBER'] == $order->getField("ACCOUNT_NUMBER"))
		{
			return $result;
		}

		$accountNumber = $order->getField("ACCOUNT_NUMBER");

		$emailFields = Array(
			"ORDER_ID" => $accountNumber,
			"ORDER_REAL_ID" => $order->getField("ID"),
			"ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($order->getField("ACCOUNT_NUMBER"))),
			"ORDER_DATE" => $order->getDateInsert()->toString(),
			"ORDER_USER" => static::getUserName($order),
			"ORDER_TRACKING_NUMBER" => $entity->getField('TRACKING_NUMBER'),
			"BCC" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER['SERVER_NAME']),
			"EMAIL" => static::getUserEmail($order),
			"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER['SERVER_NAME']),
			"ORDER_PUBLIC_URL" => Helpers\Order::isAllowGuestView($order) ? Helpers\Order::getPublicLink($order) : ""
		);

		$event = new \CEvent;
		$event->send(static::EVENT_SHIPMENT_TRACKING_NUMBER_SEND_EMAIL_EVENT_NAME, $order->getField("LID"), $emailFields, "Y", "", array(), static::getOrderLanguageId($order));

		static::addSentEvent('s'.$entity->getId(), static::EVENT_SHIPMENT_TRACKING_NUMBER_SEND_EMAIL_EVENT_NAME);

		return $result;
	}

	/**
	 * @param Internals\Entity $entity
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendShipmentAllowDelivery(Internals\Entity $entity)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}

		if (!$entity instanceof Shipment)
		{
			throw new Main\ArgumentTypeException('entity', '\Bitrix\Sale\Shipment');
		}

		if (static::hasSentEvent('s'.$entity->getId(), static::EVENT_SHIPMENT_DELIVER_SEND_EMAIL_EVENT_NAME))
		{
			return $result;
		}

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $entity->getCollection())
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_SHIPMENT_COLLECTION_NOT_FOUND")));
			return $result;
		}

		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_ORDER_NOT_FOUND")));
			return $result;
		}

		if (!$order->isAllowDelivery())
		{
			return $result;
		}


		$fields = Array(
			"ORDER_ID" => $order->getField("ACCOUNT_NUMBER"),
			"ORDER_REAL_ID" => $order->getField("ID"),
			"ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($order->getField("ACCOUNT_NUMBER"))),
			"ORDER_DATE" => $order->getDateInsert()->toString(),
			"SHIPMENT_ID" => $entity->getId(),
			"SHIPMENT_DATE" => $entity->getField("DATE_INSERT")->toString(),
			"EMAIL" => static::getUserEmail($order),
			"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
			"ORDER_PUBLIC_URL" => Helpers\Order::isAllowGuestView($order) ? Helpers\Order::getPublicLink($order) : ""
		);

		$eventName = static::EVENT_SHIPMENT_DELIVER_SEND_EMAIL_EVENT_NAME;
		$send = true;

		foreach(GetModuleEvents("sale", static::EVENT_ON_SHIPMENT_DELIVER_SEND_EMAIL, true) as $oldEvent)
		{
			if (ExecuteModuleEventEx($oldEvent, array($order->getId(), &$eventName, &$fields)) === false)
			{
				$send = false;
			}
		}

		if($send)
		{
			$event = new \CEvent;
			$event->Send($eventName, $order->getField('LID'), $fields, "Y", "", array(), static::getOrderLanguageId($order));
		}

		\CSaleMobileOrderPush::send(static::EVENT_MOBILE_PUSH_SHIPMENT_ALLOW_DELIVERY, array("ORDER" => static::getOrderFields($order)));

		static::addSentEvent('s'.$entity->getId(), static::EVENT_SHIPMENT_DELIVER_SEND_EMAIL_EVENT_NAME);

		return $result;
	}

	/**
	 * @param Internals\Entity $entity
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendPrintableCheck(Internals\Entity $entity)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}

		if (!($entity instanceof Payment)
			&& !($entity instanceof Shipment)
		)
		{
			return $result;
		}

		/** @var PaymentCollection|ShipmentCollection $collection */
		if (!$collection = $entity->getCollection())
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_ENTITY_COLLECTION_NOT_FOUND")));
			return $result;
		}

		/** @var Order $order */
		if (!$order = $collection->getOrder())
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_ORDER_NOT_FOUND")));
			return $result;
		}

		$check = CheckManager::getLastPrintableCheckInfo($entity);
		if (!empty($check['LINK']))
		{
			$fields = array(
				"ORDER_ID" => $order->getField("ACCOUNT_NUMBER"),
				"ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($order->getField("ACCOUNT_NUMBER"))),
				"ORDER_USER" => static::getUserName($order),
				"ORDER_DATE" => $order->getDateInsert()->toString(),
				"EMAIL" => static::getUserEmail($order),
				"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
				"CHECK_LINK" => $check['LINK'],
				"ORDER_PUBLIC_URL" => Helpers\Order::isAllowGuestView($order) ? Helpers\Order::getPublicLink($order) : "",
				"LINK_URL" => static::getOrderPersonalDetailLink($order)
			);

			$info = static::getSiteInfo($order);
			if ($info)
			{
				$fields["SITE_NAME"] = $info['TITLE'];
				$fields["SERVER_NAME"] = $info['PUBLIC_URL'];
			}

			$eventName = static::EVENT_ON_CHECK_PRINT_SEND_EMAIL;
			$event = new \CEvent;
			$event->Send($eventName, $order->getField('LID'), $fields, "N");

			if ($entity instanceof Payment)
			{
				static::addSentEvent('p'.$entity->getId(), $eventName);
			}
			elseif ($entity instanceof Shipment)
			{
				static::addSentEvent('s'.$entity->getId(), $eventName);
			}
		}

		return $result;
	}

	/**
	 * @param Order $order
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected static function getSiteInfo(Order $order)
	{
		$collection = $order->getTradeBindingCollection();
		/** @var TradeBindingEntity $tradeBinding */
		foreach ($collection as $tradeBinding)
		{
			$platform = $tradeBinding->getTradePlatform();
			if ($platform !== null)
			{
				return $platform->getInfo();
			}
		}

		return [];
	}

	/**
	 * @param Order $order
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected static function getOrderPersonalDetailLink(Order $order)
	{
		$context = Main\Context::getCurrent();
		$server = $context->getServer();

		$accountNumberEncode = urlencode(urlencode($order->getField("ACCOUNT_NUMBER")));
		$result = 'http://'.$server->getServerName().'/personal/order/detail/'.$accountNumberEncode.'/';

		$collection = $order->getTradeBindingCollection();
		/** @var TradeBindingEntity $tradeBinding */
		foreach ($collection as $tradeBinding)
		{
			$platform = $tradeBinding->getTradePlatform();
			if ($platform === null)
			{
				continue;
			}

			$link = $platform->getExternalLink(Platform::LINK_TYPE_PUBLIC_DETAIL_ORDER, $order);
			if ($link)
			{
				$result = $link;
			}

			break;
		}

		return $result;
	}

	/**
	 * @param Internals\Entity $entity
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function sendCheckError(Internals\Entity $entity)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}

		if (!($entity instanceof Payment)
			&& !($entity instanceof Shipment)
		)
		{
			throw new Main\ArgumentTypeException('entity', '\Bitrix\Sale\Payment or \Bitrix\Sale\Shipment');
		}

		/** @var PaymentCollection|ShipmentCollection $collection */
		if (!$collection = $entity->getCollection())
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_ENTITY_COLLECTION_NOT_FOUND")));
			return $result;
		}

		/** @var Order $order */
		if (!$order = $collection->getOrder())
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_ORDER_NOT_FOUND")));
			return $result;
		}

		$filter = array('STATUS' => 'E');
		if ($entity instanceof Payment)
		{
			$filter['PAYMENT_ID'] = $entity->getId();
		}
		elseif ($entity instanceof Shipment)
		{
			$filter['SHIPMENT_ID'] = $entity->getId();
		}

		$dbRes = CashboxCheckTable::getList(
			array(
				'select' => array('*'),
				'filter' => $filter,
				'order' => array('DATE_PRINT_END' => 'DESC'),
				'limit' => 1
			)
		);
		$check = $dbRes->fetch();
		if (!$check)
		{
			$result->addError(new ResultError(Main\Localization\Loc::getMessage("SALE_NOTIFY_ORDER_CHECK_NOT_FOUND")));
			return $result;
		}

		$cashbox = Manager::getCashboxFromCache($check['CASHBOX_ID']);
		if ($cashbox['EMAIL'])
		{
			$cashbox = Manager::getCashboxFromCache($check['CASHBOX_ID']);

			$fields = array(
				"ORDER_ACCOUNT_NUMBER" => $order->getField("ACCOUNT_NUMBER"),
				"CHECK_ID" => $check['ID'],
				"ORDER_ID" => $order->getId(),
				"ORDER_DATE" => $order->getDateInsert()->toString(),
				"EMAIL" => $cashbox['EMAIL'],
				"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
			);

			$context = Main\Context::getCurrent();
			$server = $context->getServer();

			if (IsModuleInstalled('crm'))
			{
				$fields['LINK_URL'] = 'http://'.$server->getServerName().'/shop/orders/details/'.$order->getId().'/';
			}
			else
			{
				$fields['LINK_URL'] = 'http://'.$server->getServerName().'/bitrix/admin/sale_order_view.php?ID='.$order->getId();
			}

			$eventName = static::EVENT_ON_CHECK_PRINT_ERROR_SEND_EMAIL;
			$event = new \CEvent;
			$event->Send($eventName, $order->getField('LID'), $fields, "N");

			if ($entity instanceof Payment)
			{
				static::addSentEvent('p'.$entity->getId(), $eventName);
			}
			elseif ($entity instanceof Shipment)
			{
				static::addSentEvent('s'.$entity->getId(), $eventName);
			}
		}

		\CSaleMobileOrderPush::send(
			static::EVENT_MOBILE_PUSH_ORDER_CHECK_ERROR,
			array(
				'ORDER' => static::getOrderFields($order),
				'CHECK' => $check
			)
		);

		return $result;
	}

	/**
	 * @param Internals\Entity $order
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function sendCheckValidationError(Internals\Entity $order)
	{
		$result = new Result();

		if (static::isNotifyDisabled())
		{
			return $result;
		}

		$context = Main\Context::getCurrent();
		$server = $context->getServer();

		$fields = array(
			"ORDER_ID" => $order->getId(),
			"ORDER_ACCOUNT_NUMBER" => $order->getField("ACCOUNT_NUMBER"),
			"ORDER_DATE" => $order->getDateInsert()->toString(),
			"EMAIL" => Main\Config\Option::get("main", "email_from"),
			"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$server->getServerName()),
		);

		if (IsModuleInstalled('crm'))
		{
			$fields['LINK_URL'] = 'http://'.$server->getServerName().'/shop/orders/details/'.$order->getId().'/';
		}
		else
		{
			$fields['LINK_URL'] = 'http://'.$server->getServerName().'/bitrix/admin/sale_order_view.php?ID='.$order->getId();
		}

		$eventName = static::EVENT_ON_CHECK_VALIDATION_ERROR_SEND_EMAIL;
		$event = new \CEvent;
		$event->Send($eventName, $order->getField('LID'), $fields, "N");

		return $result;
	}

	/**
	 * @param Order $order
	 *
	 * @return null|string
	 * @throws Main\ArgumentException
	 */
	protected static function getUserEmail(Order $order)
	{
		$userEmail = "";

		if (!empty(static::$cacheUserData[$order->getUserId()]))
		{
			$userData = static::$cacheUserData[$order->getUserId()];
			if (!empty($userData['EMAIL']))
			{
				$userEmail = $userData['EMAIL'];
			}
		}

		if (empty($userEmail))
		{
			/** @var PropertyValueCollection $propertyCollection */
			if ($propertyCollection = $order->getPropertyCollection())
			{
				if ($propUserEmail = $propertyCollection->getUserEmail())
				{
					$userEmail = $propUserEmail->getValue();
					static::$cacheUserData[$order->getUserId()]['EMAIL'] = $userEmail;
				}
			}
		}

		if (empty($userEmail))
		{
			$userData = self::getUserById($order->getUserId());
			if ($userData)
			{
				$userEmail = $userData['EMAIL'];
				static::$cacheUserData[$order->getUserId()]['EMAIL'] = $userData['EMAIL'];
			}
		}

		return $userEmail;
	}

	/**
	 * @param Order $order
	 *
	 * @return mixed|null|string
	 * @throws Main\ArgumentException
	 */
	protected static function getUserName(Order $order)
	{
		$userName = "";

		if (!empty(static::$cacheUserData[$order->getUserId()]))
		{
			$userData = static::$cacheUserData[$order->getUserId()];
			if (!empty($userData['PAYER_NAME']))
			{
				$userName = $userData['PAYER_NAME'];
			}
		}

		if (empty($userName))
		{
			/** @var PropertyValueCollection $propertyCollection */
			if ($propertyCollection = $order->getPropertyCollection())
			{
				if ($propPayerName = $propertyCollection->getPayerName())
				{
					$userName = $propPayerName->getValue();
					static::$cacheUserData[$order->getUserId()]['PAYER_NAME'] = $userName;
				}
			}
		}

		if (empty($userName))
		{
			$userData = self::getUserById($order->getUserId());
			if ($userData)
			{
				$userName = \CUser::FormatName(\CSite::GetNameFormat(null, $order->getSiteId()), $userData, true);
				static::$cacheUserData[$order->getUserId()]['PAYER_NAME'] = $userName;
			}
		}

		return $userName;
	}

	private static function getUserById(int $userId)
	{
		static $userData = array();

		if (isset($userData[$userId]))
		{
			return $userData[$userId];
		}

		$row = Main\UserTable::getRow([
			'select' => [
				'ID',
				'LOGIN',
				'NAME',
				'LAST_NAME',
				'SECOND_NAME',
				'EMAIL',
			],
			'filter' => [
				'=ID' => $userId,
			],
		]);

		$userData[$userId] = $row ?: [];

		return $userData[$userId];
	}

	/**
	 * @param Order $order
	 *
	 * @return Result
	 */
	protected static function getAllFieldsFromOrder(Order $order)
	{
		$result = new Result();

		$paymentSystemId = false;
		$deliveryId = false;

		/** @var PaymentCollection $paymentCollection */
		if ($paymentCollection = $order->getPaymentCollection())
		{
			/** @var Payment $payment */
			if ($payment = $paymentCollection->rewind())
			{
				$paymentSystemId = $payment->getPaymentSystemId();
			}
		}

		/** @var ShipmentCollection $shipe */
		if ($shipmentCollection = $order->getShipmentCollection())
		{
			/** @var Shipment $shipment */
			foreach ($shipmentCollection as $shipment)
			{
				if ($shipment->getDeliveryId() > 0)
				{
					$deliveryId = $shipment->getDeliveryId();
					break;
				}
			}
		}

		$fields = array(
			"SITE_ID" => $order->getSiteId(),
			"LID" => $order->getSiteId(),
			"PERSON_TYPE_ID" => $order->getPersonTypeId(),
			"PRICE" => $order->getPrice(),
			"CURRENCY" => $order->getCurrency(),
			"USER_ID" => $order->getUserId(),
			"PAY_SYSTEM_ID" => $paymentSystemId,
			"PRICE_DELIVERY" => $order->getDeliveryPrice(),
			"DELIVERY_ID" => $deliveryId,
			"DISCOUNT_VALUE" => $order->getDiscountPrice(),
			"TAX_VALUE" => $order->getTaxValue(),
			"TRACKING_NUMBER" => $order->getField('TRACKING_NUMBER'),
			"PAYED" => $order->getField('PAYED'),
			"CANCELED" => $order->getField('CANCELED'),
			"STATUS_ID" => $order->getField('STATUS_ID'),
			"RESERVED" => $order->getField('RESERVED'),
		);

		$orderFields = static::getOrderFields($order);
		if (is_array($orderFields))
		{
			$orderFields = $fields + $orderFields;
			$orderFields = static::convertDateFieldsToOldFormat($orderFields);
		}

		$result->setData([
			'FIELDS' => $fields,
			'ORDER_FIELDS' => $orderFields,
		]);

		return $result;
	}

	/**
	 * @param Order $order
	 *
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	protected static function getOrderFields(Order $order)
	{
		$fields = $order->getFieldValues();
		$fields = array_merge(
			$fields,
				[
					'ORDER_ID' => $order->getId(),
					'ORDER_WEIGHT' => 0,
					'BASKET_ITEMS' => [],
					'ORDER_PROP' => [],
					'DISCOUNT_LIST' => [],
					'TAX_LIST' => [],
					'VAT_RATE' => $order->getVatRate(),
					'VAT_SUM' => $order->getVatSum(),
				]);

		/** @var Basket $basket */
		if ($basket = $order->getBasket())
		{
			/** @var BasketItem $basketItem */
			foreach ($basket as $basketItem)
			{
				$fields['BASKET_ITEMS'][] = static::getBasketItemFields($basketItem);
			}

			$fields['ORDER_WEIGHT'] = $basket->getWeight();
		}

		/** @var PropertyValueCollection $basket */
		if ($propertyCollection = $order->getPropertyCollection())
		{
			/** @var PropertyValue $property */
			foreach ($propertyCollection as $property)
			{
				$fields['ORDER_PROP'][$property->getPropertyId()] = $property->getValue();
			}
		}


		if ($propProfileName = $propertyCollection->getProfileName())
		{
			$fields['PROFILE_NAME'] = $propProfileName->getValue();
		}

		if ($propPayerName = $propertyCollection->getPayerName())
		{
			$fields['PAYER_NAME'] = $propPayerName->getValue();
		}

		if ($propUserEmail = $propertyCollection->getUserEmail())
		{
			$fields['USER_EMAIL'] = $propUserEmail->getValue();
		}

		if ($propDeliveryLocationZip = $propertyCollection->getDeliveryLocationZip())
		{
			$fields['DELIVERY_LOCATION_ZIP'] = $propDeliveryLocationZip->getValue();
		}

		if ($propDeliveryLocation = $propertyCollection->getDeliveryLocation())
		{
			$fields['DELIVERY_LOCATION'] = $propDeliveryLocation->getValue();
		}

		if ($propTaxLocation = $propertyCollection->getTaxLocation())
		{
			$fields['TAX_LOCATION'] = $propTaxLocation->getValue();
		}

		$fields['DISCOUNT_LIST'] = Compatible\DiscountCompatibility::getOldDiscountResult();

		/** @var Tax $tax */
		if ($tax = $order->getTax())
		{
			$fields['TAX_LIST'] = $tax->getTaxList();
		}

		return $fields;
	}

	/**
	 * @param BasketItem $basketItem
	 *
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	protected static function getBasketItemFields(BasketItem $basketItem)
	{
		$fields = $basketItem->getFieldValues();

		/** @var Basket $basket */
		if (!$basket = $basketItem->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		if (empty($fields['LID']))
		{
			$fields['LID'] = $basket->getSiteId();
		}

		if (empty($fields['LID']))
		{
			if ($order = $basket->getOrder())
			{
				$fields['LID'] = $order->getField('LID');
			}
		}

		if (empty($fields['FUSER_ID']))
		{
			$fields['FUSER_ID'] = $basket->getFUserId(true);
		}


		/** @var BasketPropertiesCollection $propertyCollection */
		if ($propertyCollection = $basketItem->getPropertyCollection())
		{
			$fields['PROPS'] = $propertyCollection->getPropertyValues();
		}

		return $fields;
	}

	/**
	 * @param Order $order
	 *
	 * @return mixed
	 */
	public static function getOrderLanguageId(Order $order)
	{
		$siteData = Main\SiteTable::GetById($order->getSiteId())->fetch();
		return $siteData['LANGUAGE_ID'];
	}

	/**
	 * Convert an array of dates from the object to a string
	 *
	 * @param array $fields   The array of dates
	 * @return array
	 */
	public static function convertDateFieldsToOldFormat(array $fields)
	{
		$resultList = array();
		foreach ($fields as $k => $value)
		{
			$valueString = static::convertDateFieldToOldFormat($value);
			$resultList[$k] = $valueString;
		}

		return $resultList;
	}

	/**
	 * Convert date object to a string
	 *
	 * @param string $value    Field value
	 * @return string
	 */
	protected static function convertDateFieldToOldFormat($value)
	{
		$setValue = $value;

		if ($value instanceof Main\Type\Date)
		{
			$setValue = $value->toString();
		}

		return $setValue;
	}

	/**
	 * @param $code
	 * @param $event
	 *
	 * @return bool
	 */
	private static function hasSentEvent($code, $event)
	{
		if (!array_key_exists($code, static::$sentEventList))
		{
			return false;
		}

		if (in_array($event, static::$sentEventList[$code]))
		{
			return true;
		}

		return false;
	}

	/**
	 * @param $code
	 * @param $event
	 *
	 * @return bool
	 */
	private static function addSentEvent($code, $event)
	{
		if (!static::hasSentEvent($code, $event))
		{
			static::$sentEventList[$code][] = $event;

			return true;
		}

		return false;
	}

	/**
	 * @param Internals\Entity $entity
	 * @param $eventName
	 */
	public static function callNotify(Internals\Entity $entity, $eventName)
	{
		$eventNotifyMap = EventActions::getEventNotifyMap();

		if (isset($eventNotifyMap[$eventName]))
		{
			if ($entity instanceof $eventNotifyMap[$eventName]['ENTITY'])
			{
				call_user_func_array($eventNotifyMap[$eventName]['METHOD'], [$entity]);
			}
		}
	}

	/**
	 * @param $value
	 */
	public static function setNotifyDisable($value)
	{
		static::$disableNotify = ($value === true);
		Compatible\EventCompatibility::setDisableMailSend($value);
	}

	/**
	 * @return bool
	 */
	public static function isNotifyDisabled()
	{
		return static::$disableNotify;
	}
}