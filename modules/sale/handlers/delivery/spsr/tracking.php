<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Sale;
use Bitrix\Main\Error;
use Bitrix\Sale\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
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
	/**
	 * @inheritDoc
	 */
	public function getClassTitle()
	{
		return Loc::getMessage("SALE_DLV_SRV_SPSR_T_TITLE");
	}

	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function getStatusShipment($shipmentData)
	{
		return (new StatusResult())->addError(new Error('The company no longer exists'));
	}

	/**
	 * @inheritDoc
	 */
	public function getStatusesShipment(array $shipmentsData)
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}

	public function requestStatuses($sid, $icn, $trackingNumbers)
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}

	/**
	 * @inheritDoc
	 */
	public function getParamsStructure()
	{
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function getTrackingUrl($trackingNumber = '')
	{
		return 'http://www.spsr.ru/ru/service/monitoring';
	}
}
