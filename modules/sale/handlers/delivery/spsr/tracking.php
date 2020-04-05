<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Sale\Order;
use Bitrix\Main\Error;
use Bitrix\Sale\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Localization\Loc;
use Sale\Handlers\Delivery\Spsr\Request;
use Bitrix\Sale\Delivery\Tracking\Statuses;
use Bitrix\Sale\Delivery\Tracking\StatusResult;

Loc::loadMessages(__FILE__);

Loader::registerAutoLoadClasses(
	'sale',
	array(
		'Sale\Handlers\Delivery\SpsrHandler' => 'handlers/delivery/spsr/handler.php',
		'Sale\Handlers\Delivery\Spsr\Request' => 'handlers/delivery/spsr/request.php',
	)
);
/**
 * Class SpsrTracking
 * @package \Sale\Handlers\Delivery;
 */
class SpsrTracking extends \Bitrix\Sale\Delivery\Tracking\Base
{
	/** @var  \Sale\Handlers\Delivery\SpsrHandler */
	protected $deliveryService;

	/**
	 * @return string
	 */
	public function getClassTitle()
	{
		return Loc::getMessage("SALE_DLV_SRV_SPSR_T_TITLE");
	}

	/**
	 * @return string
	 */
	public function getClassDescription()
	{
		return Loc::getMessage(
			"SALE_DLV_SRV_SPSR_T_DESCR",
			array(
				'#A1#' => '<a href="http://www.spsr.ru/">',
				'#A2#' => '</a>'
			)
		);
	}

	/**
	 * @param array $shipmentData
	 * @return StatusResult.
	 */
	public function getStatusShipment($shipmentData)
	{
		$results = $this->getStatusesShipment(array($shipmentData));
		$result = new StatusResult();

		if($results->isSuccess())
		{
			/** @var  StatusResult $statusResult */
			foreach($results->getData() as $statusResult)
					if($statusResult->trackingNumber == $shipmentData['TRACKING_NUMBER'])
						return $statusResult;
		}
		else
		{
			$result->addErrors($results->getErrors());
		}

		return $result;
	}

	/**
	 * @param int $orderId
	 * @param int $shipmentId
	 * @return \Bitrix\Sale\Internals\CollectableEntity|bool|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	private function getShipment($orderId, $shipmentId)
	{
		if($orderId <= 0)
			return null;

		$order = Order::load($orderId);

		if(!$order)
			return null;

		$sCollect = $order->getShipmentCollection();

		if(!$sCollect)
			return null;

		return $sCollect->getItemById($shipmentId);
	}

	/**
	 * @param array $shipmentsData
	 * @return Result
	 */
	public function getStatusesShipment(array $shipmentsData)
	{
		$result = new Result();

		if(empty($shipmentsData))
			return $result;

		/** @var SpsrHandler $parentService */
		$parentService = $this->deliveryService->getParentService();

		if(!$parentService)
			return $result;

		$reqParams = array();

		foreach($shipmentsData as $shipmentFields)
		{
			$shipment = $this->getShipment($shipmentFields['ORDER_ID'], $shipmentFields['SHIPMENT_ID']);

			/** @var \Bitrix\Sale\Result $res */
			$res = $parentService->getSidResult($shipment);

			if($res->isSuccess())
			{
				$data = $res->getData();
				$sid = $data[0];
			}
			else
			{
				$sid = "";
			}

			$icn = $parentService->getICN($shipment);

			if(!is_array($reqParams[$sid.'_'.$icn]))
				$reqParams[$sid.'_'.$icn] = array( 'SID' => $sid, 'ICN' => $icn, 'TRACK_NUMBERS' => array());

			$reqParams[$sid.'_'.$icn]['TRACK_NUMBERS'][] = $shipmentFields['TRACKING_NUMBER'];
		}

		foreach($reqParams as $params)
		{
			$res = $this->requestStatuses($params['SID'], $params['ICN'], $params['TRACK_NUMBERS']);

			if(!$res->isSuccess())
				$result->addErrors($res->getErrors());
			
			$result->setData($res->getData());
		}

		return $result;
	}

	public function requestStatuses($sid, $icn, $trackingNumbers)
	{
		$result = new Result();
		$request = new Request();
		$resultData = array();
		$reqRes = $request->getInvoicesInfo(
			$sid,
			$icn,
			LANGUAGE_ID,
			$trackingNumbers
		);

		/** @var \Bitrix\Sale\Result $reqRes */
		if($reqRes->isSuccess())
		{
			$invoicesInfo = $reqRes->getData();

			if(!empty($invoicesInfo['root']['#']['Invoices'][0]['#']['Invoice']) && is_array($invoicesInfo['root']['#']['Invoices'][0]['#']['Invoice']))
			{
				foreach($invoicesInfo['root']['#']['Invoices'][0]['#']['Invoice'] as $invoice)
				{
					if(!in_array($invoice['@']['InvoiceNumber'], $trackingNumbers))
						continue;

					$r = new StatusResult();

					if(!empty($invoice['#']['events'][0]['#']['event']) && is_array($invoice['#']['events'][0]['#']['event']))
					{
						$lastEvent = end($invoice['#']['events'][0]['#']['event']);
						$r->status = $this->translateStatus($lastEvent['@']['EventNumCode']);
						$r->description = $lastEvent['@']['EventName'];
						$r->lastChangeTimestamp = $this->translateDate($lastEvent['@']['Date']);
						$r->trackingNumber = $invoice['@']['InvoiceNumber'];
					}
					else
					{
						$r->addError(new Error(Loc::getMessage('SALE_DLV_SRV_SPSR_T_ERROR_DATA')));
					}

					$resultData[] = $r;
				}
			}
			elseif(!empty($invoicesInfo['root']['#']['NotFound'][0]['#']['Invoice']) && is_array($invoicesInfo['root']['#']['NotFound'][0]['#']['Invoice']))
			{
				foreach($invoicesInfo['root']['#']['NotFound'][0]['#']['Invoice'] as $invoice)
				{
					$r = new StatusResult();
					$r->trackingNumber = $invoice['@']['InvoiceNumber'];
					$r->addError(
						new Error(
							self::utfDecode(
								$invoice['@']['ErrorMessage']
							)
						)
					);
					$resultData[] = $r;
				}
			}
			else
			{
				$result->addError(new Error(Loc::getMessage('SALE_DLV_SRV_SPSR_T_ERROR_DATA')));
			}
		}
		else
		{
			$result->addErrors($reqRes->getErrors());

		}

		if(!empty($resultData))
			$result->setData($resultData);

		return $result;
	}

	protected static function translateStatus($externalStatus)
	{
		if(strlen($externalStatus) <= 0)
			return Statuses::UNKNOWN;

		$statusMaps = array(
			Statuses::WAITING_SHIPMENT => array(),
			Statuses::ON_THE_WAY => array(2, 4, 6, 12, 13, 14, 17, 29, 30, 33, 34, 35, 39, 40, 41, 42, 43, 44, 45, 46,
				47, 48, 49, 50, 51, 53, 54, 105, 106, 107, 108, 109, 110, 111, 115, 119, 120, 122, 100, 32, 63, 64, 66,
				67, 74, 75, 76, 78, 79, 81, 84, 85, 86, 96),
			Statuses::ARRIVED => array(1, 8, 26, 31, ),
			Statuses::HANDED => array(15, 16, 27, 37, 55, 56, 57, 58, 59, 60, 61, 62, 92, 93, 112, 114, 116	),
			Statuses::PROBLEM => array(5, 7, 9, 10, 11, 18, 19, 20, 21, 22, 23, 24, 25, 28, 36, 38, 52, 113, 117, 123,
				124, 125, 126, 127, 128,129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 65, 68, 69,
				70, 71, 72, 73, 77, 80, 82, 83, 87, 88, 89, 90, 91, 94, 95, 97, 98, 99, 101, 102, 103, 104, 143, 144,
				145, 146, 147, 148, 150, 175),
		);

		foreach($statusMaps as $internalStatus => $map)
			if(in_array($externalStatus, $map))
				return $internalStatus;

		return Statuses::UNKNOWN;
	}

	protected static function translateDate($externalDate)
	{
		$date = new \DateTime($externalDate);
		return $date->getTimestamp();
	}

	/**
	 * @return array
	 */
	public function getParamsStructure()
	{
		return array();
	}

	protected static function utfDecode($str)
	{
		if(strtolower(SITE_CHARSET) != 'utf-8')
			$str = Encoding::convertEncoding($str, 'UTF-8', SITE_CHARSET);

		return $str;
	}

	/**
	 * @param string $trackingNumber
	 * @return string Url were we can see tracking information
	 */
	public function getTrackingUrl($trackingNumber = '')
	{
		return 'http://www.spsr.ru/ru/service/monitoring';
	}
}