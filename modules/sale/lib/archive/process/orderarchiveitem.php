<?php
namespace Bitrix\Sale\Archive\Process;

use Bitrix\Main,
	Bitrix\Main\Type,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale,
	Bitrix\Sale\Internals,
	Bitrix\Sale\Archive\Manager;

Loc::loadMessages(__FILE__);

/**
 * @package Bitrix\Sale\Archive\Process
 */
class OrderArchiveItem
{
	private $order = null;
	private $orderDataFields = [];
	private $basketDataFields = [];

	/**
	 * OrderArchiveItem constructor.
	 *
	 * @param Sale\Order $order
	 */
	public function __construct(Sale\Order $order)
	{
		$this->order = $order;
		$this->orderDataFields['ORDER'] = $order->getFieldValues();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->order->getId();
	}

	/**
	 * @param $name
	 * @param array $value
	 */
	public function addOrderDataField($name, array $value)
	{
		if ($name !== 'ORDER')
		{
			$this->orderDataFields[$name] = $value;
		}
	}

	/**
	 * @param array $value
	 */
	public function addBasketDataFields(array $value)
	{
		$this->basketDataFields = $value;
	}

	/**
	 * @return Main\Result
	 */
	public function archive()
	{
		$r = $this->callEventOnBeforeOrderArchived($this->order);
		if (!$r->isSuccess())
		{
			return $r;
		}

		$result = $this->saveOrderArchive();
		if ($result->isSuccess())
		{
			$archivedOrderId = $result->getId();
			$basketItemIdList = array();
			foreach ($this->basketDataFields as $basketItem)
			{
				$additionBasketResult = $this->saveBasketItemArchive($basketItem, $archivedOrderId);
				if ($additionBasketResult->isSuccess())
				{
					$basketItemIdList[] = $additionBasketResult->getId();
				}
				else
				{
					$result->addErrors($additionBasketResult->getErrors());
					break;
				}
			}
		}

		if ($result->isSuccess())
		{
			$this->tryUnreserveShipments();

			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
			/** @var Sale\Order $orderClass */
			$orderClass = $registry->getOrderClassName();

			$orderClass::deleteNoDemand($this->getId());
		}
		else
		{
			if (!empty($archivedOrderId))
			{
				Internals\OrderArchiveTable::delete($archivedOrderId);
			}

			if (!empty($basketItemIdList) && is_array($basketItemIdList))
			{
				foreach ($basketItemIdList as $archivedBasketItemId)
				{
					Internals\BasketArchiveTable::delete($archivedBasketItemId);
				}
			}
		}

		return $result;
	}

	/**
	 * @param Sale\Order $order
	 *
	 * @return Main\Result
	 */
	private function callEventOnBeforeOrderArchived(Sale\Order $order)
	{
		$result = new Main\Result();

		$eventManager = Main\EventManager::getInstance();
		if ($eventsList = $eventManager->findEventHandlers('sale', Sale\EventActions::EVENT_ON_ORDER_BEFORE_ARCHIVED))
		{
			/** @var Main\Event $event */
			$event = new Main\Event('sale', Sale\EventActions::EVENT_ON_ORDER_BEFORE_ARCHIVED, array(
				'ENTITY' => $order
			));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() == Main\EventResult::ERROR)
					{
						$errorMsg = new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_ORDER_SAVED_ERROR'), 'SALE_EVENT_ON_BEFORE_ORDER_SAVED_ERROR');
						if ($eventResultData = $eventResult->getParameters())
						{
							if (isset($eventResultData) && $eventResultData instanceof Sale\ResultError)
							{
								/** @var ResultError $errorMsg */
								$errorMsg = $eventResultData;
							}
						}

						$result->addError($errorMsg);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @throws Main\ArgumentException
	 */
	private function tryUnreserveShipments()
	{
		$shipmentData = $this->getOrderDataField('SHIPMENT');
		foreach ($shipmentData as $shipment)
		{
			if ($shipment["RESERVED"] == "Y" &&	$shipment["DEDUCTED"] == "N")
			{
				if ($shipmentCollection = $this->order->getShipmentCollection())
				{
					foreach ($shipmentCollection as $shipmentItem)
					{
						$shipmentItem->tryUnreserve();
					}

					$this->order->save();
				}

				break;
			}
		}
	}

	/**
	 * @return Main\Entity\AddResult
	 */
	private function saveOrderArchive()
	{
		$preparedOrderData = array_intersect_key($this->getOrderDataField('ORDER'), array_flip(Manager::getOrderFieldNames()));
		$preparedOrderData['ORDER_ID'] = $this->getId();
		$preparedOrderData['DATE_ARCHIVED'] = new Type\DateTime();
		$preparedOrderData['VERSION'] = Manager::SALE_ARCHIVE_VERSION;
		$preparedFields = $this->prepareEncodeFields($this->orderDataFields);
		$preparedOrderData['ORDER_DATA'] = Main\Web\Json::encode($preparedFields);
		return Internals\OrderArchiveTable::add($preparedOrderData);
	}

	private function prepareEncodeFields(array $fields)
	{
		foreach ($fields as &$field)
		{
			if (is_array($field))
			{
				$field = $this->prepareEncodeFields($field);
			}
			elseif ($field instanceof Type\Date)
			{
				\CTimeZone::Disable();
				$field = $field->toString();
				\CTimeZone::Enable();
			}
		}

		return $fields;
	}

	/**
	 * @param array $item
	 * @param $archivedOrderId
	 *
	 * @return Main\Entity\AddResult
	 */
	private function saveBasketItemArchive(array $item, $archivedOrderId)
	{
		$preparedBasketItems = array_intersect_key($item, array_flip(Manager::getBasketFieldNames()));
		$preparedBasketItems['ARCHIVE_ID'] = (int)$archivedOrderId;
		$preparedFields = $this->prepareEncodeFields($item);
		$preparedBasketItems['BASKET_DATA'] = Main\Web\Json::encode($preparedFields);

		if (empty($preparedBasketItems['DATE_INSERT']))
		{
			$zeroDate = new \DateTime();
			$zeroDate->setDate(0,0,0);
			$zeroDate->setTime(0,0,0);
			$preparedBasketItems['DATE_INSERT'] = Type\DateTime::createFromPhp($zeroDate);
		}

		return Internals\BasketArchiveTable::add($preparedBasketItems);
	}

	private function getOrderDataField($name)
	{
		return $this->orderDataFields[$name];
	}

}
