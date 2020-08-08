<?php


namespace Bitrix\Sale\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale\Delivery\Tracking\Manager;


class Tracking extends \Bitrix\Main\Engine\Controller
{
	private $manager;

	const PERMISSION_READ = 'D';
	const PERMISSION_WRITE = 'W';

	public function __construct(Request $request = null)
	{
		$this->manager = Manager::getInstance();
		parent::__construct($request);
	}

	public function getStatusByShipmentIdAction(int $shipmentId, string $trackingNumber = '')
	{
		if($shipmentId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SALE_CONTROLLER_TRACKING_ERROR_SHIPMENT_ZERO')));
			return null;
		}

		if($trackingNumber == '')
		{
			$this->addError(new Error(Loc::getMessage('SALE_CONTROLLER_TRACKING_ERROR_DELETE_STATUS')));
			return null;
		}

		if(!$this->checkPermission(self::PERMISSION_READ))
		{
			return null;
		}

		$result = null;
		$res = $this->manager->getStatusByShipmentId($shipmentId, $trackingNumber);

		if($res->isSuccess())
		{
			$lastChange = '';

			if($res->lastChangeTimestamp > 0)
			{
				$lastChange = \Bitrix\Main\Type\DateTime::createFromTimestamp(
					$res->lastChangeTimestamp
				)->toString();
			}

			$result = [
				'status' => $res->status,
				'statusName' => Manager::getStatusName($res->status),
				'description' => $res->description,
				'lastChange' => $lastChange
			];

			if($this->checkPermission(self::PERMISSION_WRITE))
			{
				$res = $this->manager->updateShipment($shipmentId, $res);

				if(!$res->isSuccess())
				{
					$this->addErrors($res->getErrors());
				}
			}
		}
		else
		{
			$this->addErrors($res->getErrors());
		}

		return $result;
	}

	protected function checkPermission($permissionType)
	{
		$result =  self::getApplication()->GetGroupRight("sale") >= $permissionType;

		if(!$result)
		{
			$this->addError(new Error('Access denied'));
		}

		return $result;
	}

	protected static function getApplication()
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		return $APPLICATION;
	}
}