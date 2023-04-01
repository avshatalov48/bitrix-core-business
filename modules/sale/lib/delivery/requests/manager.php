<?php

namespace Bitrix\Sale\Delivery\Requests;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use Bitrix\Sale\ResultWarning;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Internals;
use Bitrix\Sale\EntityMarker;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Services;

Loc::loadMessages(__FILE__);

/**
 * Class Manager
 * @package Bitrix\Sale\Delivery\Requests
 * Manages the lifecycle of delivery requests items
 */
final class Manager
{
	public const STATUS_PREPARED = 0;
	public const STATUS_SENT = 10;
	public const STATUS_PROCESSED = 20;

	const FORM_FIELDS_TYPE_CREATE = 10;
	const FORM_FIELDS_TYPE_ADD = 20;
	const FORM_FIELDS_TYPE_ACTION = 30;

	public const REQUEST_CREATED_EVENT_CODE = 'OnDeliveryRequestCreated';
	public const REQUEST_DELETED_EVENT_CODE = 'OnDeliveryRequestDeleted';
	public const REQUEST_UPDATED_EVENT_CODE = 'OnDeliveryRequestUpdated';
	public const REQUEST_ACTION_EXECUTED_EVENT_CODE = 'OnDeliveryRequestActionExecuted';

	public const MESSAGE_RECEIVED_EVENT_CODE = 'OnDeliveryRequestMessageReceived';
	public const MESSAGE_MANAGER_ADDRESSEE = 'MANAGER';
	public const MESSAGE_RECIPIENT_ADDRESSEE = 'RECIPIENT';

	public const EXTERNAL_STATUS_SEMANTIC_SUCCESS = 'success';
	public const EXTERNAL_STATUS_SEMANTIC_PROCESS = 'process';

	protected static $isChangedShipmentNeedsMark = true;

	/**
	 * @param int $shipmentId
	 * @param int $requestId
	 * @return Result
	 * @throws Main\ArgumentException
	 */
	public static function getDeliveryRequestShipmentContent($requestId, $shipmentId)
	{
		$result = new Result();

		if (intval($shipmentId) <= 0)
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHIPMENT_ID')));
			return $result;
		}

		$res = ShipmentTable::getList(array(
			'filter' => array(
				'=SHIPMENT_ID' => $shipmentId
			),
			'select' => array(
				'*',
				'DELIVERY_ID' => 'SHIPMENT.DELIVERY_ID'
			)
		));

		if (!($row = $res->fetch()))
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						'SALE_DLVR_REQ_MNGR_ERROR_SHIPMENT_NOT_IN_REQUEST',
						array('#SHIPMENT_ID#' => $shipmentId)
			)));

			return $result;
		}

		$deliveryId = intval($row['DELIVERY_ID']);

		if ($deliveryId <= 0)
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						'SALE_DLVR_REQ_MNGR_ERROR_DELIVERY_NOT_FOUND',
						array('#SHIPMENT_LINK#' => Helper::getShipmentEditLink($shipmentId)))));

			return $result;
		}

		$deliveryRequestHandler = self::getDeliveryRequestHandlerByDeliveryId($deliveryId);

		if (!$deliveryRequestHandler)
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						'SALE_DLVR_REQ_MNGR_ERROR_DELIVERY_NOT_SUPPORT',
						array('#DELIVERY_LINK#' => Helper::getDeliveryEditLink($deliveryId)))));

			return $result;
		}

		return $deliveryRequestHandler->getShipmentContent($requestId, $shipmentId);
	}

	/**
	 * @param Shipment $shipment
	 * @return array Shipment actions
	 * @throws Main\ArgumentNullException
	 */
	public static function getDeliveryRequestShipmentActions(Shipment $shipment)
	{
		$deliveryId = $shipment->getDeliveryId();

		if ($deliveryId <= 0)
			return array();

		if (!($delivery = Services\Manager::getObjectById($deliveryId)))
			return array();

		if (!($deliveryRequestHandler = $delivery->getDeliveryRequestHandler()))
			return array();

		return $deliveryRequestHandler->getShipmentActions($shipment);
	}

	/**
	 * @param int $requestId
	 * @return array Request actions
	 * @throws Main\ArgumentNullException
	 */
	public static function getDeliveryRequestActions($requestId)
	{
		$result = array();
		$deliveryRequestHandler = self::getDeliveryRequestHandlerByRequestId($requestId);

		if ($deliveryRequestHandler)
			$result = $deliveryRequestHandler->getActions($requestId);

		return $result;
	}

	/**
	 * @param int $requestId
	 * @return HandlerBase|null  Delivery request handler
	 * @throws Main\ArgumentNullException
	 */
	protected static function getDeliveryRequestHandlerByRequestId($requestId)
	{
		if (intval($requestId) <= 0)
			return null;

		if (!($requestFields = RequestTable::getById($requestId)->fetch()))
			return null;

		if (intval($requestFields['DELIVERY_ID']) <= 0)
			return null;

		return self::getDeliveryRequestHandlerByDeliveryId($requestFields['DELIVERY_ID']);
	}

	/**
	 * @param int $deliveryId
	 * @return HandlerBase|null Delivery request handler
	 * @throws Main\ArgumentNullException
	 * @throws Main\SystemException
	 */

	public static function getDeliveryRequestHandlerByDeliveryId($deliveryId)
	{
		if (intval($deliveryId) <= 0)
			return null;

		if (!($delivery = Services\Manager::getObjectById($deliveryId)))
			return null;

		return $delivery->getDeliveryRequestHandler();
	}

	/**
	 * @param int $deliveryId
	 * @param int[] $shipmentIds
	 * @param array $additional Additional info required for creation. Depends on delivery service.
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */

	public static function createDeliveryRequest($deliveryId, array $shipmentIds, array $additional = array())
	{
		$result = new Result();

		if (!($deliveryRequestHandler = self::getDeliveryRequestHandlerByDeliveryId($deliveryId)))
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						'SALE_DLVR_REQ_MNGR_ERROR_NOT_SUPPORT',
						array('#DELIVERY_LINK#' => Helper::getDeliveryEditLink($deliveryId))
			)));
			self::sendOnCreateDeliveryRequestEvent($result, $deliveryId, $shipmentIds, $additional);

			return $result;
		}

		/** @var ShipmentResult[] $checkResults */
		$checkResults = self::checkShipmentIdsBeforeAdd($shipmentIds);

		foreach ($checkResults as $res)
		{
			if ($res->isSuccess())
				continue;

			$result->addResult(
				self::processShipmentResult(
					$res
			));

			unset($shipmentIds[array_search($res->getInternalId(), $shipmentIds)]);
		}

		if (empty($shipmentIds))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHP_ABSENT')));
			self::sendOnCreateDeliveryRequestEvent($result, $deliveryId, $shipmentIds, $additional);

			return $result;
		}

		$handlerResult = $deliveryRequestHandler->create($shipmentIds, $additional);
		if ($handlerResult->isSuccess())
		{
			$result->addResults($handlerResult->getResults());
		}
		else
		{
			$result->addErrors($handlerResult->getErrors());

			foreach ($handlerResult->getShipmentResults() as $sRes)
			{
				$result->addResult(self::processShipmentResult($sRes));
			}
			self::sendOnCreateDeliveryRequestEvent($result, $deliveryId, $shipmentIds, $additional);

			return $result;
		}

		$results = $result->getResults();

		if (!is_array($results) || empty($results))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_RES_UNKNOWN')));

			self::sendOnCreateDeliveryRequestEvent($result, $deliveryId, $shipmentIds, $additional);

			return $result;
		}

		/** @var  ShipmentResult|RequestResult $requestResult */
		foreach ($results as $resId => $requestResult)
		{
			if ($requestResult instanceof ShipmentResult)
			{
				$requestResult = self::processShipmentResult($requestResult);
			}
			elseif ($requestResult instanceof RequestResult)
			{
				$requestId = 0;
				/** @var RequestResult  $requestResult*/
				if ($requestResult->isSuccess())
				{
					$handlerResultData = $handlerResult->getData();

					$res = RequestTable::add(array(
						'DELIVERY_ID' => $deliveryRequestHandler->getHandlingDeliveryServiceId(),
						'EXTERNAL_ID' => $requestResult->getExternalId(),
						'CREATED_BY' => ($GLOBALS['USER'] instanceof \CUser && (int)$GLOBALS['USER']->GetID() > 0)
							? (int)$GLOBALS['USER']->GetID()
							: null,
						'STATUS' => Manager::STATUS_SENT,
						'EXTERNAL_STATUS' => $handlerResultData['STATUS'] ?? null,
						'EXTERNAL_STATUS_SEMANTIC' => $handlerResultData['STATUS_SEMANTIC'] ?? null,
						'EXTERNAL_PROPERTIES' => (isset($handlerResultData['PROPERTIES']) && is_array($handlerResultData['PROPERTIES']))
							? $handlerResultData['PROPERTIES']
							: [],
					));

					if (!$res->isSuccess())
					{
						$requestResult->addErrors($res->getErrors());
						continue;
					}

					$requestId = $res->getId();

					if ($requestId > 0)
					{
						$requestResult->setInternalId($requestId);
					}
				}

				$shipmentsResults = $requestResult->getShipmentResults();

				if (empty($shipmentsResults))
					continue;

				foreach ($shipmentsResults as $sResIdx => $shipmentResult)
				{
					$shipmentsResults[$sResIdx] = self::processShipmentResult($shipmentResult, $requestId);
				}

				$requestResult->setResults($shipmentsResults);
			}
			else
			{
				$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_ANSW_TYPE').' "'.get_class($requestResult).'"'));
				continue;
			}

			$results[$resId] = $requestResult;
		}

		$result->setResults($results);
		self::sendOnCreateDeliveryRequestEvent($result, $deliveryId, $shipmentIds, $additional);

		return $result;
	}

	private static function sendOnCreateDeliveryRequestEvent(
		Result $result,
		int $deliveryId,
		array $shipmentIds,
		array $additional
	)
	{
		(new Main\Event(
			'sale',
			self::REQUEST_CREATED_EVENT_CODE,
			[
				'DELIVERY_ID' => $deliveryId,
				'SHIPMENT_IDS' => $shipmentIds,
				'ADDITIONAL' => $additional,
				'RESULT' => $result,
			]
		))->send();
	}

	/**
	 * @param ShipmentResult $result
	 * @param int $requestId
	 * @return ShipmentResult
	 */
	protected static function processShipmentResult($result, $requestId = 0)
	{
		if (!($result instanceof ShipmentResult))
			return $result;

		$shipmentId = $result->getInternalId();

		if (intval($shipmentId) <= 0)
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SAVE_SHIPMENT_ID')));
			return $result;
		}

		$extShipmentId = $result->getExternalId();

		if ($result->isSuccess())
		{
			if (intval($requestId) > 0)
			{
				$res = ShipmentTable::setShipment(array(
					'REQUEST_ID' => $requestId,
					'SHIPMENT_ID' => $shipmentId,
					'EXTERNAL_ID' => $extShipmentId,
					'ERROR_DESCRIPTION' => ''
				));

				if (!$res->isSuccess())
					$result->addErrors($res->getErrors());

				$res = self::saveShipmentResult($shipmentId, $result);

				if (!$res->isSuccess())
					$result->addErrors($res->getErrors());
			}
		}
		else
		{
			ShipmentTable::setShipment(array(
				'SHIPMENT_ID' => $shipmentId,
				'ERROR_DESCRIPTION' => implode("\n", $result->getErrorMessages())
			));
		}

		return $result;
	}

	/**
	 * @param int $deliveryId
	 * @param int $formFieldsType FORM_FIELDS_TYPE_ACTION | FORM_FIELDS_TYPE_ADD | FORM_FIELDS_TYPE_CREATE
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return array Form fields
	 * @throws Main\ArgumentNullException
	 */
	public static function getDeliveryRequestFormFields($deliveryId, $formFieldsType, array $shipmentIds, array $additional = array())
	{
		if (!$deliveryRequestHandler = self::getDeliveryRequestHandlerByDeliveryId($deliveryId))
			return array();

		return $deliveryRequestHandler->getFormFields($formFieldsType, $shipmentIds, $additional);
	}

	/**
	 * @param int $requestId
	 * @return Result
	 * @throws \Exception
	 */
	public static function deleteDeliveryRequest($requestId)
	{
		$result = new Result();
		$shipmentIds = [];

		if (empty($requestId))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_REQ_ID')));
			return $result;
		}

		if ($deliveryRequestHandler = self::getDeliveryRequestHandlerByRequestId($requestId))
		{
			$res = $deliveryRequestHandler->delete($requestId);

			if (!$res->isSuccess())
				$result->addErrors($res->getErrors());
		}

		if ($result->isSuccess())
		{
			$requestShipmentList = ShipmentTable::getList(array('filter' => array('=REQUEST_ID' => $requestId)));
			while ($requestShipment = $requestShipmentList->fetch())
			{
				$shipmentIds[] = $requestShipment['SHIPMENT_ID'];
			}

			$con = Main\Application::getConnection();
			$con->queryExecute("DELETE FROM ".ShipmentTable::getTableName()." WHERE REQUEST_ID=".intval($requestId));
			$res = RequestTable::delete($requestId);

			if (!$res->isSuccess())
				$result->addErrors($res->getErrors());
		}

		(new Main\Event(
			'sale',
			self::REQUEST_DELETED_EVENT_CODE,
			[
				'REQUEST_ID' => $requestId,
				'SHIPMENT_IDS' => $shipmentIds,
				'RESULT' => $result
			]
		))->send();

		if ($result->isSuccess() && Loader::includeModule('pull'))
		{
			\CPullWatch::AddToStack(
				'SALE_DELIVERY_REQUEST',
				[
					'module_id' => 'sale',
					'command' => 'onDeliveryRequestDelete',
					'params' => [
						'ID' => $requestId,
					]
				]
			);
		}

		return $result;
	}

	/**
	 * @param $requestId
	 * @param array $fields
	 * @param bool $overwriteProperties
	 * @return Result
	 */
	public static function updateDeliveryRequest($requestId, array $fields, bool $overwriteProperties = false): Result
	{
		$result = new Result();

		if (empty($requestId))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_REQ_ID')));
			return $result;
		}

		if (isset($fields['EXTERNAL_PROPERTIES']) && !is_array($fields['EXTERNAL_PROPERTIES']))
		{
			unset($fields['EXTERNAL_PROPERTIES']);
		}

		if (
			$overwriteProperties === false
			&& isset($fields['EXTERNAL_PROPERTIES'])
			&& ($existingRequestFields = RequestTable::getById($requestId)->fetch())
		)
		{
			$existingProperties = array_column(
				is_null($existingRequestFields['EXTERNAL_PROPERTIES'])
					? []
					: $existingRequestFields['EXTERNAL_PROPERTIES'],
				null,
				'NAME'
			);
			$newProperties = array_column(
				$fields['EXTERNAL_PROPERTIES'],
				null,
				'NAME'
			);

			$fields['EXTERNAL_PROPERTIES'] = array_values(array_merge($existingProperties, $newProperties));
		}

 		$updateResult = RequestTable::update(
			$requestId,
			array_intersect_key(
				$fields,
				array_flip([
					'STATUS',
					'EXTERNAL_STATUS',
					'EXTERNAL_STATUS_SEMANTIC',
					'EXTERNAL_PROPERTIES',
				])
			)
		);
		if (!$updateResult->isSuccess())
		{
			$result->addErrors($updateResult->getErrors());
		}

		(new Main\Event(
			'sale',
			self::REQUEST_UPDATED_EVENT_CODE,
			[
				'REQUEST_ID' => $requestId,
				'FIELDS' => $fields,
				'RESULT' => $result,
			]
		))->send();

		if ($result->isSuccess() && Loader::includeModule('pull'))
		{
			\CPullWatch::AddToStack(
				'SALE_DELIVERY_REQUEST',
				[
					'module_id' => 'sale',
					'command' => 'onDeliveryRequestUpdate',
					'params' => [
						'ID' => $requestId,
					]
				]
			);
		}

		return $result;
	}

	/**
	 * @param int $requestId
	 * @param int[] $shipmentIds
	 * @return Result
	 */
	public static function deleteShipmentsFromDeliveryRequest($requestId, array $shipmentIds)
	{
		$result = new Result();

		if (empty($requestId))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_REQ_ID')));
			return $result;
		}

		if (empty($shipmentIds))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHP_ID_LIST_EMPTY')));
			return $result;
		}

		if (!($deliveryRequestHandler = self::getDeliveryRequestHandlerByRequestId($requestId)))
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						'SALE_DLVR_REQ_MNGR_ERROR_REQ_OBJ'
			)));

			return $result;
		}

		$checkResults = self::checkShipmentIdsBeforeDelete($shipmentIds);

		foreach ($checkResults as $res)
		{
			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
				unset($shipmentIds[array_search($res->getInternalId(), $shipmentIds)]);
			}
		}

		if (empty($shipmentIds))
			return $result;

		$res = $deliveryRequestHandler->deleteShipments($requestId, $shipmentIds);
		$result->setResults($res->getResults());

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
			return $result;
		}

		$results = $result->getResults();

		if (!is_array($results) || empty($results))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_RES_UNKNOWN')));
			return $result;
		}

		$con = Main\Application::getConnection();

		/** @var  ShipmentResult $shpRes */
		foreach ($results as $resId => $shpRes)
		{
			if (!($shpRes instanceof ShipmentResult))
			{
				$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_ANSW_TYPE').' "'.get_class($shpRes).'"'));
				continue;
			}

			if ($shpRes->isSuccess())
			{
				$shpId = intval($shpRes->getInternalId());

				if ($shpId <= 0)
				{
					$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_SHP_ID_LESS_ZERO')));
					continue;
				}

				$con->queryExecute("DELETE FROM ".ShipmentTable::getTableName()." WHERE REQUEST_ID=".intval($requestId)." AND SHIPMENT_ID=".intval($shpId));
				//Unset mark about changed shipment
				self::unSetMarkerShipmentChanged($shpId);

				//If there is no more shipments in this request
				if (!ShipmentTable::getList(array('filter' => array('=REQUEST_ID' => $requestId)))->fetch())
				{
					$res = RequestTable::delete($requestId);

					if ($res->isSuccess())
					{
						$result->addMessage(
							new Message(
								Loc::getMessage(
									'SALE_DLVR_REQ_MNGR_EMPTY_REQ_DELETED',
									array('#REQUEST_ID#' => $requestId)
						)));
					}
					else
					{
						$result->addError(
							new Main\Error(
								Loc::getMessage(
									'SALE_DLVR_REQ_MNGR_EMPTY_REQ_NOT_DELETED',
									array('#REQUEST_LINK#' => Helper::getRequestViewLink($requestId))
								).implode('; ',$result->getErrorMessages())
						));
					}
				}
			}

			$results[$resId] = $shpRes;
		}

		$result->setResults($results);
		return $result;
	}

	/**
	 * @param int $requestId
	 * @return Result
	 */
	public static function getDeliveryRequestContent($requestId)
	{
		$deliveryRequestHandler = self::getDeliveryRequestHandlerByRequestId($requestId);

		if (!$deliveryRequestHandler)
		{
			$result= new Result();
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_REQ_OBJ')));
			return $result;
		}

		return $deliveryRequestHandler->getContent($requestId);
	}

	/**
	 * @param int $requestId
	 * @param string $actionType
	 * @param array $additional
	 * @return Result
	 */
	public static function executeDeliveryRequestAction($requestId, $actionType, array $additional = array())
	{
		$deliveryRequestHandler = self::getDeliveryRequestHandlerByRequestId($requestId);

		if (!$deliveryRequestHandler)
		{
			$result = new Result();
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_ACTION_EXEC').'. '.Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_REQ_OBJ')));
			return $result;
		}

		$result = $deliveryRequestHandler->executeAction($requestId, $actionType, $additional);

		(new Main\Event(
			'sale',
			self::REQUEST_ACTION_EXECUTED_EVENT_CODE,
			[
				'REQUEST_ID' => $requestId,
				'ACTION_TYPE' => $actionType,
				'ADDITIONAL' => $additional,
				'RESULT' => $result,
				'DELIVERY_REQUEST_HANDLER' => $deliveryRequestHandler,
			]
		))->send();

		return $result;
	}

	/**
	 * @param int $requestId
	 * @param int $shipmentId
	 * @param string $actionType
	 * @param array $additional
	 * @return Result
	 */
	public static function executeDeliveryRequestShipmentAction($requestId, $shipmentId, $actionType, array $additional = array())
	{
		$deliveryRequestHandler = self::getDeliveryRequestHandlerByRequestId($requestId);

		if (!$deliveryRequestHandler)
		{
			$result = new Result();
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_ACTION_EXEC').'. '.Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_REQ_OBJ')));
			return $result;
		}

		return $deliveryRequestHandler->executeShipmentAction($requestId, $shipmentId, $actionType, $additional);
	}

	/**
	 * @param int[] $shipmentIds
	 * @return ShipmentResult[]
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected static function checkShipmentIdsBeforeAdd(array $shipmentIds)
	{
		$result = array();
		$positiveIds = self::filterPositiveIds($shipmentIds);

		foreach (array_diff($shipmentIds, $positiveIds) as $id)
		{
			$shpRes = new ShipmentResult($id);
			$shpRes->addError(
				new Main\Error(
					Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHIPMENT_ID').' "'.$id.'"',
					$id
			));
			$result[] = $shpRes;
		}

		$addedIds = self::filterAddedIds($positiveIds);

		foreach (array_intersect($positiveIds, $addedIds) as $id)
		{
			$shpRes = new ShipmentResult($id);
			$shpRes->addError(
				new Main\Error(
					Loc::getMessage('SALE_DLVR_REQ_MNGR_ALREADY_ADDED')
			));
			$result[] = $shpRes;
		}

		$existingIds = self::filterExistIds($addedIds);

		foreach (array_diff($addedIds, $existingIds) as $id)
		{
			$shpRes = new ShipmentResult($id);
			$shpRes->addError(
				new Main\Error(
					Loc::getMessage('SALE_DLVR_REQ_MNGR_SHP_NOT_FOUND', array('#SHIPMENT_ID#' => $id)
			)));
			$result[] = $shpRes;
		}

		return $result;
	}

	/**
	 * @param int[] $shipmentIds
	 * @return int[] Choose ids only for existing shipments.
	 */
	protected static function filterExistIds(array $shipmentIds)
	{
		$result = array();

		$res = Internals\ShipmentTable::getList(array(
			'filter' => array(
				'=ID' => $shipmentIds,
			)
		));

		while ($row = $res->fetch())
			$result[] = $row['ID'];

		return $result;
	}

	/**
	 * @param int[] $shipmentIds
	 * @return int[]
	 */
	protected static function filterAddedIds(array $shipmentIds)
	{
		$result = array();

		$res = ShipmentTable::getList(array(
			'filter' => array(
				'=SHIPMENT_ID' => $shipmentIds,
				'!=REQUEST_ID' => false
			)
		));

		while ($row = $res->fetch())
			$result[] = $row['SHIPMENT_ID'];

		return $result;
	}

	/**
	 * @param int[] $shipmentIds
	 * @return int[]
	 */
	protected static function filterPositiveIds(array $shipmentIds)
	{
		$result = array();

		foreach ($shipmentIds as $id)
			if (intval($id) > 0)
				$result[] = $id;

		return $result;
	}

	/**
	 * @param int[] $shipmentIds
	 * @return ShipmentResult[]
	 */
	protected static function checkShipmentIdsBeforeDelete(array $shipmentIds)
	{
		return self::checkShipmentIdsBeforeUpdate($shipmentIds);
	}

	/**
	 * @param int[] $shipmentIds
	 * @return ShipmentResult[]
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected static function checkShipmentIdsBeforeUpdate(array $shipmentIds)
	{
		$result = array();
		$positiveIds = self::filterPositiveIds($shipmentIds);

		foreach (array_diff($shipmentIds, $positiveIds) as $id)
		{
			$shpRes = new ShipmentResult($id);
			$shpRes->addError(
				new Main\Error(
					Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHIPMENT_ID').' "'.$id.'"')
			);
			$result[] = $shpRes;
		}

		$addedIds = self::filterAddedIds($positiveIds);

		foreach (array_diff($positiveIds, $addedIds) as $id)
		{
			$shpRes = new ShipmentResult($id);
			$shpRes->addError(
				new Main\Error(
					Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHIPMENT_NOT_IN_REQUEST', array('#SHIPMENT_ID#' => $id)
			)));
			$result[] = $shpRes;
		}

		$existingIds = self::filterExistIds($addedIds);

		foreach (array_diff($addedIds, $existingIds) as $id)
		{
			$shpRes = new ShipmentResult($id);
			$shpRes->addError(
				new Main\Error(
					Loc::getMessage('SALE_DLVR_REQ_MNGR_SHP_NOT_FOUND', array('#SHIPMENT_ID#' => $id)
			)));
			$result[] = $shpRes;
		}

		return $result;
	}

	/**
	 * @param int $shipmentId
	 * @return bool
	 */
	public static function isShipmentSent($shipmentId)
	{
		return intval(self::getRequestIdByShipmentId($shipmentId)) > 0;
	}

	/**
	 * @param int $shipmentId
	 * @return int Request ID
	 */
	public static function getRequestIdByShipmentId($shipmentId)
	{
		$result = 0;

		$res = ShipmentTable::getList(array(
			'filter' => array(
				'=SHIPMENT_ID' => $shipmentId,
			)
		));

		if ($row = $res->fetch())
			$result = $row['REQUEST_ID'];

		return $result;
	}

	/**
	 * @param int $requestId
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Result
	 */
	public static function addShipmentsToDeliveryRequest($requestId, array $shipmentIds, array $additional = [])
	{
		$result = new Result();

		if (empty($requestId))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_REQ_ID')));
			return $result;
		}

		if (empty($shipmentIds))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHP_ID_LIST_EMPTY')));
			return $result;
		}

		if (!($deliveryRequestHandler = self::getDeliveryRequestHandlerByRequestId($requestId)))
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHP_ADD2').'. '.
					Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_REQ_OBJ',
						"",
						$requestId
			)));
			return $result;
		}

		$checkResults = self::checkShipmentIdsBeforeAdd($shipmentIds);

		foreach ($checkResults as $res)
		{
			if ($res->isSuccess())
				continue;

			$result->addResult(
				self::processShipmentResult(
					$res
				));

			unset($shipmentIds[array_search($res->getInternalId(), $shipmentIds)]);
		}

		if (empty($shipmentIds))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHP_ABSENT')));
			return $result;
		}

		$res = $deliveryRequestHandler->addShipments($requestId, $shipmentIds, $additional);

		if ($res->isSuccess())
		{
			$result->addResults($res->getResults());
		}
		else
		{
			$result->addErrors($res->getErrors());

			foreach ($res->getShipmentResults() as $sRes)
				$result->addResult(self::processShipmentResult($sRes));

			return $result;
		}

		$results = $result->getResults();

		if (!is_array($results) || empty($results))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_RES_UNKNOWN')));
			return $result;
		}

		$successResCount = 0;

		/** @var RequestResult $reqRes */
		foreach ($results as $resId => $reqRes)
		{
			if ($reqRes instanceof ShipmentResult)
			{
				$results[$resId] = self::processShipmentResult($reqRes, $requestId);
			}
			elseif ($reqRes instanceof RequestResult)
			{
				$reqShpResults = $reqRes->getShipmentResults();

				foreach ($reqShpResults as $id => $shpRes)
				{
					$reqShpResults[$id] = self::processShipmentResult($shpRes, $requestId);

					if ($shpRes->isSuccess())
						$successResCount++;
				}

				$reqRes->setResults($reqShpResults);
				$results[$resId] = $reqRes;
			}
			else
			{
				$result->addError(
					new Main\Error(
						Loc::getMessage(
							'SALE_DLVR_REQ_MNGR_RES_WRONG',
							array(
								'#CLASS_NAME#' => get_class($reqRes),
								'#REQUEST_ID#' => $requestId
							)
						)
					)
				);

				continue;
			}
		}

		if ($successResCount <= 0)
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHP_ABSENT2')));

		$result->setResults($results);
		return $result;
	}

	/**
	 * @param int $requestId
	 * @param int [] $shipmentIds
	 * @return Result
	 * @throws \Exception
	 */
	public static function updateShipmentsFromDeliveryRequest($requestId, array $shipmentIds)
	{
		$result = new Result();

		if (empty($requestId))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_REQ_ID')));
			return $result;
		}

		if (empty($shipmentIds))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SHP_ID_LIST_EMPTY')));
			return $result;
		}

		$checkResults = self::checkShipmentIdsBeforeUpdate($shipmentIds);

		foreach ($checkResults as $res)
		{
			if ($res->isSuccess())
				continue;

			$result->addResult(
				self::processShipmentResult($res, $requestId)
			);

			unset($shipmentIds[array_search($res->getInternalId(), $shipmentIds)]);
		}

		if (empty($shipmentIds))
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						'SALE_DLVR_REQ_MNGR_ERROR_SHP_UPD',
						array('#REQUEST_LINK#' => Helper::getRequestViewLink($requestId))
			)));
			return $result;
		}

		$deliveryRequestHandler = self::getDeliveryRequestHandlerByRequestId($requestId);

		if (!$deliveryRequestHandler)
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						'SALE_DLVR_REQ_MNGR_ERROR_SHP_UPD',
						array('#REQUEST_LINK#' => Helper::getRequestViewLink($requestId))
					).'. '.
					Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_REQ_OBJ')
			));

			return $result;
		}

		$res = $deliveryRequestHandler->updateShipments($requestId, $shipmentIds);
		$result->addResults($res->getResults());

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
			return $result;
		}

		$results = $res->getResults();

		if (empty($results))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_RES_EMPTY')));
			return $result;
		}

		$resultsFinal = array();

		foreach ($results as $res)
		{
			if ($res instanceof ShipmentResult)
			{
				$resultsFinal[] = self::processShipmentResult($res, $requestId);
			}
			elseif ($res instanceof RequestResult)
			{
				$reqShpResults = $res->getShipmentResults();

				foreach ($reqShpResults as $id => $shpRes)
				{
					$shpRes = self::processShipmentResult($shpRes, $requestId);

					if ($shpRes->isSuccess())
					{
						$shpInternalId = intval($shpRes->getInternalId());
						//Unset mark about changed shipments
						self::unSetMarkerShipmentChanged($shpInternalId);

						$dbRes = self::saveShipmentResult($shpRes->getInternalId(), $shpRes);

						if (!$dbRes->isSuccess())
							$shpRes->addErrors($dbRes->getErrors());
					}

					$resultsFinal[] = $shpRes;
				}
			}
			else
			{
				$result->addError(
					new Main\Error(
						Loc::getMessage('SALE_DLVR_REQ_MNGR_RES_WRONG_UPD')
				));

				continue;
			}
		}

		$result->setResults($resultsFinal);
		return $result;
	}

	/**
	 * @param $shipmentId
	 * @param ShipmentResult $shipmentResult
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	protected static function saveShipmentResult($shipmentId, ShipmentResult $shipmentResult)
	{
		$result = new Result();
		$shipments = Helper::getShipmentsByIds(array($shipmentId));

		if ($shipments[$shipmentId])
		{
			$shipments[$shipmentId]->setFields(array(
				'TRACKING_NUMBER' => $shipmentResult->getTrackingNumber(),
				'DELIVERY_DOC_NUM' => $shipmentResult->getDeliveryDocNum(),
				'DELIVERY_DOC_DATE' => $shipmentResult->getDeliveryDocDate()
			));

			static::$isChangedShipmentNeedsMark = false;
			$res = $shipments[$shipmentId]->getOrder()->save();
			static::$isChangedShipmentNeedsMark = true;

			if (!$res->isSuccess())
				$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_MNGR_ERROR_SAVE_SHIPMENT').'"'.$shipmentId.'"'));
		}
		else
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						'SALE_DLVR_REQ_MNGR_SHP_NOT_FOUND',
						array('#SHIPMENT_ID#' => $shipmentId)
			)));
		}

		return $result;
	}

	/**
	 * @param Order $order
	 * @param Shipment $shipment
	 */
	public static function onBeforeShipmentSave(&$order, &$shipment)
	{
		if (static::$isChangedShipmentNeedsMark && self::isShipmentSent($shipment->getId()))
		{
			self::setMarkerShipmentChanged($order, $shipment);
		}
	}

	/**
	 * @param Shipment $shipment
	 */
	public static function onBeforeShipmentDelete(&$shipment)
	{
		$shipmentId = $shipment->getId();

		if (self::isShipmentSent($shipmentId))
		{
			self::deleteShipmentsFromDeliveryRequest(
				self::getRequestIdByShipmentId($shipmentId),
				array($shipmentId)
			);
		}
	}

	/**
	 * @param Order $order
	 * @param Shipment $shipment
	 */
	protected static function setMarkerShipmentChanged(&$order, &$shipment)
	{
		$r = new \Bitrix\Sale\Result();

		$r->addWarning(
			new ResultWarning(
				Loc::getMessage(
					'SALE_DLVR_REQ_MNGR_NOT_UPDATED'
				),
				'DELIVERY_REQUEST_NOT_UPDATED'
		));

		EntityMarker::addMarker($order, $shipment, $r);
		$shipment->setField('MARKED', 'Y');
	}

	/**
	 * @param int  $shipmentId
	 */
	protected static function unSetMarkerShipmentChanged($shipmentId)
	{
		EntityMarker::deleteByFilter(array(
			'=ENTITY_TYPE' => EntityMarker::ENTITY_TYPE_SHIPMENT,
			'=ENTITY_ID' => $shipmentId,
			'=CODE' => 'DELIVERY_REQUEST_NOT_UPDATED'
		));
	}

	/**
	 * @param string $addressee
	 * @param Message\Message $message
	 * @param int $requestId
	 * @param int $shipmentId
	 */
	public static function sendMessage(
		string $addressee,
		Message\Message $message,
		int $requestId,
		int $shipmentId
	): void
	{
		(new Main\Event(
			'sale',
			self::MESSAGE_RECEIVED_EVENT_CODE,
			[
				'ADDRESSEE' => $addressee,
				'REQUEST_ID' => $requestId,
				'SHIPMENT_ID' => $shipmentId,
				'MESSAGE' => $message,
			]
		))->send();
	}

	/**
	 * @return string[]
	 */
	public static function getMessageAddressees(): array
	{
		return [
			self::MESSAGE_MANAGER_ADDRESSEE,
			self::MESSAGE_RECIPIENT_ADDRESSEE,
		];
	}

	/**
	 * @return string[]
	 */
	public static function getRequestStatusSemantics(): array
	{
		return [
			self::EXTERNAL_STATUS_SEMANTIC_SUCCESS,
			self::EXTERNAL_STATUS_SEMANTIC_PROCESS,
		];
	}

	public static function initJs()
	{
		\CJSCore::RegisterExt('sale_delivery_requests', array(
			'js' => '/bitrix/js/sale/delivery_request.js',
			'lang' => '/bitrix/modules/sale/lang/' . LANGUAGE_ID . '/admin/js/sale_delivery_requests.php',
			'rel' => array('core', 'ajax')
		));

		\CUtil::InitJSCore(array('sale_delivery_requests'));
	}
}
