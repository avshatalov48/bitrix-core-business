<?php

namespace Bitrix\Sale\Delivery\Tracking;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\Services;
use Bitrix\Sale\Internals\ShipmentTable;
use Bitrix\Sale\Order;
use Bitrix\Sale;
use Bitrix\Sale\PropertyValueCollection;
use Bitrix\Sale\Result;
use Bitrix\Sale\Shipment;

Loc::loadMessages(__FILE__);

/**
 * Class Statuses
 * @package Bitrix\Sale\Delivery\Tracking
 *
 * Possible internals variants of tracking statuses
 */
class Statuses
{
	const NO_INFORMATION 	= 0; //No tracking (may be yet)
	const WAITING_SHIPMENT	= 10;
	const ON_THE_WAY 		= 20;
	const ARRIVED 			= 30;
	const HANDED 			= 40; //shipping definitely finished
	const PROBLEM			= 50;
	const UNKNOWN			= 60; //Incorrect status mapping made by tracking handler.
	const RETURNED 			= 70;
}

/**
 * Class StatusResult
 * @package Bitrix\Sale\Delivery\Tracking
 */
class StatusResult extends Result
{
	/**	@var int */
	public $status;
	/**	@var string */
	public $description;
	/** @var  string */
	public $trackingNumber;
	/**	@var int */
	public $lastChangeTimestamp;
}

/**
 * Class StatusChangeEventParam
 * @package Bitrix\Sale\Delivery\Tracking
 */
class StatusChangeEventParam
{
	/**	@var int */
	public $status;
	/**	@var string */
	public $description;
	/** @var  string */
	public $trackingNumber;
	/**	@var int */
	public $lastChangeTimestamp;
	/**	@var int */
	public $orderId;
	/**	@var int */
	public $shipmentId;
	/**	@var int */
	public $deliveryId;
}

/**
 * Class Manager
 * @package Bitrix\Sale\Delivery\Tracking
 *
 * Singleton
 *
 * All job with tracking numbers must must be done through this class
 */
class Manager
{
	protected static $instance = null;
	protected static $classNames = null;
	//If status didn't changed for a long time let's stop update it.
	protected static $activeStatusLiveTime = 5184000;  //60 days

	/** @var bool  */
	protected $isClone = false;

	protected function __clone(){}

	protected function __construct()
	{
		self::initClassNames();
	}

	/**
	 * @return static
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
			self::$instance = new static();

		return self::$instance;
	}

	/**
	 * @param int $status
	 * @return string
	 */
	public static function getStatusName($status)
	{
		$statusesNames = self::getStatusesList();
		$status = intval($status);

		if(empty($statusesNames[$status]))
			return Loc::getMessage("SALE_DTM_STATUS_NAME_UNKNOWN");

		return $statusesNames[$status];
	}

	/**
	 * @return array
	 */
	public static function getStatusesList()
	{
		return array(
			Statuses::NO_INFORMATION => Loc::getMessage("SALE_DTM_STATUS_NAME_NO_INFORMATION"),
			Statuses::WAITING_SHIPMENT => Loc::getMessage("SALE_DTM_STATUS_NAME_WAITING_SHIPMENT"),
			Statuses::ON_THE_WAY => Loc::getMessage("SALE_DTM_STATUS_NAME_ON_THE_WAY"),
			Statuses::ARRIVED => Loc::getMessage("SALE_DTM_STATUS_NAME_ARRIVED"),
			Statuses::HANDED => Loc::getMessage("SALE_DTM_STATUS_NAME_HANDED"),
			Statuses::PROBLEM => Loc::getMessage("SALE_DTM_STATUS_NAME_PROBLEM"),
			Statuses::UNKNOWN => Loc::getMessage("SALE_DTM_STATUS_NAME_UNKNOWN"),
			Statuses::RETURNED => Loc::getMessage("SALE_DTM_STATUS_NAME_RETURNED"),
		);
	}

	/**
	 * @param int $deliveryId Delivery service id.
	 * @param string $trackingNumber Trcking number.
	 * @return string  Url were we can see tracking information.
	 * @throws ArgumentNullException
	 */
	public function getTrackingUrl($deliveryId, $trackingNumber = '')
	{
		if(!$deliveryId)
			return '';

		$trackingObject = $this->getTrackingObjectByDeliveryId($deliveryId);

		if(!$trackingObject)
			return '';

		return $trackingObject->getTrackingUrl($trackingNumber);
	}

	/**
	 * @param int $shipmentId
	 * @param string  $trackingNumber if changed
	 * @return StatusResult
	 * @throws ArgumentNullException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getStatusByShipmentId($shipmentId, $trackingNumber = '')
	{
		if(intval($shipmentId) <= 0)
			throw new ArgumentNullException('shipmentId');

		$result = new StatusResult();

		$res = ShipmentTable::getList(array(
			'filter' => array(
				'ID'=>$shipmentId
			),
			'select' => array(
				'ID', 'ORDER_ID', 'DELIVERY_ID', 'TRACKING_STATUS', 'TRACKING_NUMBER'
			)
		));

		if(!$shipment = $res->fetch())
		{
			$result->addError(new Error("Can't find shipment with id:\"".$shipmentId.'"'));
			return $result;
		}

		if($trackingNumber <> '' && $trackingNumber != $shipment['TRACKING_NUMBER'])
			$shipment['TRACKING_NUMBER'] = $trackingNumber;

		if($shipment['TRACKING_NUMBER'] == '')
			return $result;

		$result = $this->getStatus($shipment);

		if($result->isSuccess())
		{
			if($shipment['TRACKING_STATUS'] != $result->status)
			{
				$eventParams = new StatusChangeEventParam();
				$eventParams->orderId = $shipment['ORDER_ID'];
				$eventParams->shipmentId = $shipmentId;
				$eventParams->status = $result->status;
				$eventParams->trackingNumber = $shipment['TRACKING_NUMBER'];
				$eventParams->description = $result->description;
				$eventParams->lastChangeTimestamp = $result->lastChangeTimestamp;
				$eventParams->deliveryId = $shipment['DELIVERY_ID'];
				$res = $this->processStatusChange(array($eventParams));

				if(!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * Returns mapping tracking statuses to shipment statuses.
	 * @return array
	 * @throws ArgumentNullException
	 */
	protected static function getMappedStatuses()
	{
		$result = unserialize(Option::get('sale', 'tracking_map_statuses',''), ['allowed_classes' => false]);

		if(!is_array($result))
			$result = array();

		return $result;
	}

	protected static function getCheckPeriod()
	{
		return (int)Option::get('sale', 'tracking_check_period', '24');
	}

	/**
	 * @param array shipment
	 * @return StatusResult
	 * @throws ArgumentNullException
	 * @throws SystemException
	 */
	protected function getStatus($shipment)
	{
		$result = new StatusResult();

		if(intval($shipment['DELIVERY_ID']) <= 0)
			throw new ArgumentNullException('deliveryId');

		$trackingObject = $this->getTrackingObjectByDeliveryId($shipment['DELIVERY_ID']);

		if(!$trackingObject)
			return $result;

		return $trackingObject->getStatusShipment($shipment);
	}


	/**
	 * @param $deliveryId
	 * @return Base|null
	 * @throws ArgumentNullException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getTrackingObjectByDeliveryId($deliveryId)
	{
		if(intval($deliveryId) <= 0)
			throw new ArgumentNullException('deliveryId');

		$result = null;

		$deliveryService = Services\Manager::getObjectById($deliveryId);

		if(!$deliveryService)
			return null;

		$class = $deliveryService->getTrackingClass();

		if($class <> '')
		{
			$result = $this->createTrackingObject(
				$class,
				$deliveryService->getTrackingParams(),
				$deliveryService
			);
		}

		return $result;
	}

	/**
	 * @param string $className Class name delivered from \Bitrix\Sale\Delivery\Tracking\Base
	 * @param array $params
	 * @param Services\Base $deliveryService
	 * @return Base
	 * @throws ArgumentNullException
	 * @throws SystemException
	 */
	protected function createTrackingObject($className, array $params, Services\Base $deliveryService)
	{
		if($className == '')
			throw new ArgumentNullException('className');

		if(!class_exists($className))
			throw new SystemException('Class "'.$className.'" does not exist!');

		if(get_parent_class($className) != 'Bitrix\Sale\Delivery\Tracking\Base')
			throw new SystemException($className.' is not inherited from \"\Bitrix\Sale\Delivery\Tracking\Base\"!');

		return new $className($params, $deliveryService);
	}

	/**
	 * Starts statuses refreshing
	 */
	public static function startRefreshingStatuses()
	{
		$manager = self::getInstance();
		$result = $manager->updateStatuses();

		if(!$result->isSuccess())
		{
			$eventLog = new \CEventLog;

			$eventLog->Add(array(
				"SEVERITY" => \CEventLog::SEVERITY_ERROR,
				"AUDIT_TYPE_ID" => 'SALE_DELIVERY_TRACKING_REFRESHING_STATUS_ERROR',
				"MODULE_ID" => "sale",
				"ITEM_ID" => time(),
				"DESCRIPTION" => implode('\n', $result->getErrorMessages())
			));
		}

		$data = $result->getData();

		if(!empty($data))
		{
			$result = $manager->processStatusChange($data);

			if(!$result->isSuccess())
			{
				$eventLog = new \CEventLog;

				$eventLog->Add(array(
					"SEVERITY" => \CEventLog::SEVERITY_ERROR,
					"AUDIT_TYPE_ID" => 'SALE_DELIVERY_TRACKING_REFRESHING_STATUS_ERROR',
					"MODULE_ID" => "sale",
					"ITEM_ID" => time(),
					"DESCRIPTION" => implode('\n', $result->getErrorMessages())
				));
			}
		}

		return '\Bitrix\Sale\Delivery\Tracking\Manager::startRefreshingStatuses();';
	}

	/**
	 * @return Result
	 * todo: timelimit
	 */
	protected function updateStatuses()
	{
		$result = new Result();
		$checkPeriod = self::getCheckPeriod();

		if($checkPeriod <= 0)
			return $result;

		$lastChage = \Bitrix\Main\Type\DateTime::createFromTimestamp(time()-self::$activeStatusLiveTime);
		$lastUpdate = \Bitrix\Main\Type\DateTime::createFromTimestamp(time()-$checkPeriod*60*60);

		$dbRes = ShipmentTable::getList(array(
			'filter' => array(
				'!=TRACKING_NUMBER' => false,
				'!=DELIVERY_ID' => false,
				array(
					'LOGIC' => 'OR',
					array(
						'LOGIC' => 'AND',
						array('!=TRACKING_STATUS' => Statuses::HANDED),
						array('!=TRACKING_STATUS' => Statuses::RETURNED),
					),
					array('=TRACKING_STATUS' => false)
				),
				array(
					'LOGIC' => 'OR',
					array(
						'LOGIC' => 'AND',
						array('=TRACKING_LAST_CHANGE' => false),
						array('>=DATE_INSERT' => $lastChage),
					),
					array('>=TRACKING_LAST_CHANGE' => $lastChage)
				),
				array(
					'LOGIC' => 'OR',
					array('=TRACKING_LAST_CHECK' => false),
					array('<=TRACKING_LAST_CHECK' => $lastUpdate)
				)
			),
			'select' => array(
				'ID', 'ORDER_ID', 'DELIVERY_ID', 'TRACKING_STATUS', 'TRACKING_NUMBER'
			),
			'order' => array(
				'DELIVERY_ID' => 'ASC'
			)
		));

		$deliveryId = 0;
		$shipmentsData = array();

		while($shipment = $dbRes->fetch())
		{
			if(!isset($shipmentsData[$shipment['DELIVERY_ID']]))
				$shipmentsData[$shipment['DELIVERY_ID']] = array();

			if($shipment['TRACKING_NUMBER'] == '')
				continue;

			$shipmentsData[$shipment['DELIVERY_ID']][$shipment['TRACKING_NUMBER']] = array(
				'SHIPMENT_ID' => $shipment['ID'],
				'ORDER_ID' => $shipment['ORDER_ID'],
				'DELIVERY_ID' => $shipment['DELIVERY_ID'],
				'TRACKING_STATUS' => $shipment['TRACKING_STATUS']
			);

			if($shipment['DELIVERY_ID'] != $deliveryId && $deliveryId > 0)
			{
				$res = $this->processStatusesByDelivery($deliveryId, $shipmentsData[$deliveryId]);

				if($res->isSuccess())
				{
					$data = $res->getData();

					if(!empty($data))
						$result->setData(array_merge($result->getData(), $data));
				}
				else
				{
					$result->addErrors($res->getErrors());
				}

				$deliveryId = $shipment['DELIVERY_ID'];
			}

			if($deliveryId <= 0)
				$deliveryId = $shipment['DELIVERY_ID'];
		}

		if($deliveryId > 0)
		{
			$res = $this->processStatusesByDelivery($deliveryId, $shipmentsData[$deliveryId]);

			if($res->isSuccess())
			{
				$data = $res->getData();

				if(!empty($data))
					$result->setData(array_merge($result->getData(), $data));
			}
			else
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}

	protected function processStatusesByDelivery($deliveryId, $shipmentsData)
	{
		$result = new Result();
		$trackingObject = $this->getTrackingObjectByDeliveryId($deliveryId);

		if($trackingObject)
		{
			$statusResults = $trackingObject->getStatusesShipment($shipmentsData);
			$eventsParams = array();

			/** @var StatusResult $statusResult */
			foreach($statusResults as $number => $statusResult)
			{
				$eventParams = null;

				if(empty($shipmentsData[$number]))
					continue;

				if(!$statusResult->isSuccess())
				{
					$eventLog = new \CEventLog;

					$eventLog->Add(array(
						"SEVERITY" => \CEventLog::SEVERITY_ERROR,
						"AUDIT_TYPE_ID" => 'SALE_DELIVERY_TRACKING_STATUS_RESULT',
						"MODULE_ID" => "sale",
						"ITEM_ID" => $shipmentsData[$number]['SHIPMENT_ID'],
						"DESCRIPTION" => implode('\n', $statusResult->getErrorMessages())
					));

					continue;
				}

				if(($statusResult->status != $shipmentsData[$number]['TRACKING_STATUS']))
				{
					$eventParams = new StatusChangeEventParam();
					$eventParams->orderId = $shipmentsData[$number]['ORDER_ID'];
					$eventParams->shipmentId = $shipmentsData[$number]['SHIPMENT_ID'];
					$eventParams->status = $statusResult->status;
					$eventParams->trackingNumber = $number;
					$eventParams->description = $statusResult->description;
					$eventParams->lastChangeTimestamp = $statusResult->lastChangeTimestamp;
					$eventParams->deliveryId = $deliveryId;
					$eventsParams[] = $eventParams;
				}

				$res = $this->updateShipment(
					$shipmentsData[$number]['SHIPMENT_ID'],
					$statusResult
				);

				if(!$res->isSuccess())
				{
					$eventLog = new \CEventLog;

					$eventLog->Add(array(
						"SEVERITY" => \CEventLog::SEVERITY_ERROR,
						"AUDIT_TYPE_ID" => 'SALE_DELIVERY_TRACKING_UPDATE_SHIPMENT',
						"MODULE_ID" => "sale",
						"ITEM_ID" => $shipmentsData[$number]['SHIPMENT_ID'],
						"DESCRIPTION" => implode('\n', $res->getErrorMessages())
					));
				}
			}

			$result->setData($eventsParams);
		}

		return $result;
	}

	/**
	 * @param StatusChangeEventParam[] $params
	 * @return Result
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	protected function processStatusChange($params)
	{
		$result = new Result();

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		foreach($params as $param)
		{
			if(intval($param->status) <= 0 && $param->description == '')
				continue;

			$mappedStatuses = $this->getMappedStatuses();

			if(!empty($mappedStatuses[$param->status]))
			{
				/** @var Order $order */
				$order = $orderClass::load($param->orderId);
				$shipmentCollection = null;
				$oShipment = null;

				if($order)
				{
					/** @var \Bitrix\Sale\ShipmentCollection  $shipmentCollection */
					$shipmentCollection = $order->getShipmentCollection();
				}

				if($shipmentCollection)
				{
					/** @var Shipment $oShipment */
					$oShipment = $shipmentCollection->getItemById($param->shipmentId);
				}

				if($oShipment)
				{
					$res = $oShipment->setField('STATUS_ID', $mappedStatuses[$param->status]);

					if($res->isSuccess())
					{
						$res = $order->save();

						if(!$res->isSuccess())
							$result->addErrors($res->getErrors());
					}
					else
					{
						$result->addErrors($res->getErrors());
					}
				}
			}
		}

		$this->sendOnStatusesChangedEvent($params);
		$this->sendStatusChangedMail($params);
		return $result;
	}

	/**
	 * @param StatusChangeEventParam[] $params
	 * @return bool|int
	 */
	protected function sendStatusChangedMail($params)
	{
		if(empty($params))
			return true;

		/** @var  StatusChangeEventParam[] $paramsByShipmentId */
		$paramsByShipmentId = array();

		foreach($params as $status)
			$paramsByShipmentId[$status->shipmentId] = $status;

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$res = ShipmentTable::getList(array(
			'filter' => array(
				'=ID' => array_keys($paramsByShipmentId)
			),
			'select' => array(
				'DELIVERY_NAME',
				'SITE_ID' => 'ORDER.LID',
				'SITE_NAME' => 'SITE.NAME',
				'SHIPMENT_NO' => 'ID',
				'SHIPMENT_DATE' => 'DATE_INSERT',
				'ORDER_NO' => 'ORDER.ACCOUNT_NUMBER',
				'ORDER_DATE' => 'ORDER.DATE_INSERT',
				'USER_NAME' => 'ORDER.USER.NAME',
				'USER_LAST_NAME' => 'ORDER.USER.LAST_NAME',
				'EMAIL' => 'ORDER.USER.EMAIL'
			),
			'runtime' => array(
				'SITE' => array(
					'data_type' => 'Bitrix\Main\SiteTable',
					'reference' => array(
						'ref.LID' => 'this.ORDER.LID',
					),
					'join_type' => 'left'
				),
			)
		));

		$event = new \CEvent;

		while($data = $res->fetch())
		{
			$userEmail = '';
			$userName = '';
			$order = $orderClass::load($paramsByShipmentId[$data['SHIPMENT_NO']]->orderId);

			/** @var PropertyValueCollection $propertyCollection */
			if ($propertyCollection = $order->getPropertyCollection())
			{
				if ($propUserEmail = $propertyCollection->getUserEmail())
					$userEmail = $propUserEmail->getValue();

				if ($propPayerName = $propertyCollection->getPayerName())
					$userName = $propPayerName->getValue();
			}

			if(empty($userEmail))
				$userEmail = $data['EMAIL'];

			if(empty($userName))
				$userName = $data["USER_NAME"].(($data["USER_NAME"] == '' || $data["USER_LAST_NAME"] == '') ? "" : " ").$data["USER_LAST_NAME"];

			$siteFields = \CEvent::GetSiteFieldsArray($data['SITE_ID']);

			$fields = array(
				'SITE_NAME' => $data['SITE_NAME'],
				'ORDER_NO' => $data['ORDER_NO'],
				'ORDER_DATE' => $data['ORDER_DATE']->toString(),
				'ORDER_USER' => $userName,
				'SHIPMENT_NO' => $data['SHIPMENT_NO'],
				'SHIPMENT_DATE' => $data['SHIPMENT_DATE']->toString(),
				'EMAIL' => $userEmail,
				'STATUS_NAME' => self::getStatusName($paramsByShipmentId[$data['SHIPMENT_NO']]->status),
				'STATUS_DESCRIPTION' => $paramsByShipmentId[$data['SHIPMENT_NO']]->description,
				'TRACKING_NUMBER' => $paramsByShipmentId[$data['SHIPMENT_NO']]->trackingNumber,
				'DELIVERY_NAME' => $data['DELIVERY_NAME'],
				"ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($data['ORDER_NO'])),
				"SALE_EMAIL" => Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
			);

			$fields['ORDER_DETAIL_URL'] = Loc::getMessage(
				'SALE_DTM_ORDER_DETAIL_URL',
				array(
					'#A1#' => '<a href="http://'.$siteFields['SERVER_NAME'].'/personal/order/detail/'.$fields['ORDER_ACCOUNT_NUMBER_ENCODE'].'/">',
					'#A2#' => '</a>'
				)
			).'.';

			$trackingUrl = self::getTrackingUrl(
				$paramsByShipmentId[$data['SHIPMENT_NO']]->deliveryId,
				$paramsByShipmentId[$data['SHIPMENT_NO']]->trackingNumber
			);

			$deliveryTrackingUrl = '';

			if($trackingUrl <> '')
			{
				$deliveryTrackingUrl = Loc::getMessage(
					'SALE_DTM_SHIPMENT_STATUS_TRACKING_URL',
					array(
						'#A1#' => '<a href="'.$trackingUrl.'">',
						'#A2#' => '</a>'
					)
				).".<br><br>";
			}

			$fields['DELIVERY_TRACKING_URL'] = $deliveryTrackingUrl;
			$event->Send("SALE_ORDER_SHIPMENT_STATUS_CHANGED", $data['SITE_ID'], $fields, "N");
		}

		return true;
	}

	/**
	 * @param StatusChangeEventParam[] $params
	 */
	protected function sendOnStatusesChangedEvent(array $params)
	{
		$event = new Event('sale', 'onSaleShipmentsTrackingStatusesChanged', $params);
		$event->send();
	}

	/**
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 *
	 * For custom handlers use (for example in init.php)
	 * paste code like this:
	 *
	 * function addCustomDeliveryTrackingServices()
	 * {
	 *		return new \Bitrix\Main\EventResult(
	 *			\Bitrix\Main\EventResult::SUCCESS,
	 *			array(
	 *				'\Custom\Name\Space\TrackingHandlerClass' => '/custom/path/tracking_handler_class.php'
	 *			),
	 *			'sale'
	 *		);
	 * }
	 *
	 * $eventManager->addEventHandler('sale', 'onSaleDeliveryTrackingClassNamesBuildList', 'addCustomDeliveryTrackingServices');
	 */
	protected function initClassNames()
	{
		if(self::$classNames !== null)
			return true;

		Services\Manager::getHandlersList();

		$classes = array(
			'\Bitrix\Sale\Delivery\Tracking\RusPost' => 'lib/delivery/tracking/rus_post.php',
		);

		\Bitrix\Main\Loader::registerAutoLoadClasses('sale', $classes);

		$event = new Event('sale', 'onSaleDeliveryTrackingClassNamesBuildList');
		$event->send();
		$resultList = $event->getResults();

		if (is_array($resultList) && !empty($resultList))
		{
			$customClasses = array();

			foreach ($resultList as $eventResult)
			{
				$params = $eventResult->getParameters();

				if(!empty($params) && is_array($params))
					$customClasses = array_merge($customClasses, $params);
			}

			if(!empty($customClasses))
			{
				\Bitrix\Main\Loader::registerAutoLoadClasses(null, $customClasses);
				$classes = array_merge($customClasses, $classes);
			}
		}

		self::$classNames = array_keys($classes);
		return true;
	}

	/**
	 * Returns list of known class names
	 * @return array
	 */
	public function getClassNames()
	{
		return self::$classNames;
	}

	/**
	 * @param int $shipmentId
	 * @param StatusResult $params
	 * @return UpdateResult
	 * @throws ArgumentNullException
	 * @throws \Exception
	 */
	public function updateShipment($shipmentId, StatusResult $params)
	{
		if($shipmentId <= 0)
			throw new ArgumentNullException('id');

		$updParams = array();

		if($params->status !== null)
			$updParams['TRACKING_STATUS'] = $params->status;

		$updParams['TRACKING_LAST_CHECK'] = new \Bitrix\Main\Type\DateTime();

		if(intval($params->lastChangeTimestamp) > 0)
		{
			$updParams['TRACKING_LAST_CHANGE'] = \Bitrix\Main\Type\DateTime::createFromTimestamp(
				$params->lastChangeTimestamp
			);
		}
		else
		{
			$updParams['TRACKING_LAST_CHANGE'] = null;
		}

		if($params->trackingNumber !== null)
			$updParams['TRACKING_NUMBER'] = $params->trackingNumber;

		$updParams['TRACKING_DESCRIPTION'] = $params->description;

		if(!$params->isSuccess())
			$updParams['TRACKING_DESCRIPTION'] .= ' '.implode(', ', $params->getErrorMessages());

		return ShipmentTable::update($shipmentId, $updParams);
	}

	/**
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return Manager
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		$trackingClone = clone $this;
		$trackingClone->isClone = true;

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $trackingClone;
		}

		return $trackingClone;
	}

	/**
	 * @return bool
	 */
	public function isClone()
	{
		return $this->isClone;
	}
}