<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Error;
use Bitrix\Sale\Cashbox\Internals\CashboxCheckTable;
use Bitrix\Sale\Cashbox\Internals\CashboxTable;
use Bitrix\Sale\EntityMarker;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Sale;
use Bitrix\Sale\Result;

Loc::loadLanguageFile(__FILE__);

/**
 * Class CheckManager
 * @package Bitrix\Sale\Cashbox
 */
final class CheckManager
{
	public const EVENT_ON_GET_CUSTOM_CHECK = 'OnGetCustomCheckList';
	public const EVENT_ON_CHECK_PRINT_SEND = 'OnPrintableCheckSend';
	public const EVENT_ON_BEFORE_CHECK_ADD_VERIFY = 'OnBeforeCheckAddVerify';
	public const EVENT_ON_CHECK_PRINT_ERROR = 'OnCheckPrintError';
	public const EVENT_ON_CHECK_COLLATE_DOCUMENTS = 'OnCheckCollateDocuments';
	public const EVENT_ON_CHECK_VALIDATION_ERROR = 'OnCheckValidationError';
	public const MIN_TIME_FOR_SWITCH_CASHBOX = 240;

	/** This is time re-sending a check print in minutes */
	const CHECK_RESENDING_TIME = 4;
	const CHECK_LIMIT_RECORDS = 5;

	/**
	 * @param CollectableEntity[] $entities
	 * @param $type
	 * @param CollectableEntity[] $relatedEntities
	 * @return Result
	 */
	public static function addByType(array $entities, $type, array $relatedEntities = array())
	{
		$result = new Result();

		if ($type === '')
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_EMPTY_CHECK_TYPE')));
			return $result;
		}

		$check = self::createByType($type);
		if (!$check instanceof Check)
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK')));
			return $result;
		}

		$check->setEntities($entities);
		$check->setRelatedEntities($relatedEntities);

		$cashboxList = Manager::getAvailableCashboxList($check);

		$entity = reset($entities);
		$order = self::getOrder($entity);

		if (!$cashboxList)
		{
			$dbRes = CashboxTable::getList(array('filter' => array('ACTIVE' => 'Y')));
			if ($dbRes->fetch())
				$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_NOT_FOUND')));

			return $result;
		}

		$check->setAvailableCashbox($cashboxList);

		$registry = Sale\Registry::getInstance($check->getField("ENTITY_REGISTRY_TYPE"));

		$validateResult = $check->validate();
		if (!$validateResult->isSuccess())
		{
			if (Main\Loader::includeModule('crm'))
			{
				\Bitrix\Crm\Timeline\OrderCheckController::getInstance()->onCheckFailure(
					[
						'ORDER_FIELDS' => $order->getFieldValues(),
						'SETTINGS' => [
							'FAILURE' => 'Y',
							'PRINTED' => 'N',
							'ERROR_TEXT' => Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_NOT_CREATED'),
						],
						'BINDINGS' => \Bitrix\Crm\Order\BindingsMaker\TimelineBindingsMaker::makeByOrder($order),
					]
				);
			}

			$notifyClassName = $registry->getNotifyClassName();
			$notifyClassName::callNotify($order, Sale\EventActions::EVENT_ON_CHECK_VALIDATION_ERROR);
			$result->addErrors($validateResult->getErrors());

			$event = new Main\Event('sale', self::EVENT_ON_CHECK_VALIDATION_ERROR, $check->getDataForCheck());
			$event->send();

			return $result;
		}

		$saveResult = $check->save();
		if ($saveResult->isSuccess())
		{
			$checkId = $saveResult->getId();
			$order->addPrintedCheck($check);

			$enabledImmediateCashboxList = array();
			foreach ($cashboxList as $item)
			{
				if ($item['ENABLED'] === 'Y')
				{
					$cashbox = Cashbox::create($item);
					if ($cashbox instanceof IPrintImmediately)
					{
						$enabledImmediateCashboxList[$item['ID']] = $cashbox;
					}
				}
			}

			if ($enabledImmediateCashboxList)
			{
				$cashboxId = Manager::chooseCashbox(array_keys($enabledImmediateCashboxList));
				/** @var Cashbox|IPrintImmediately $cashbox */
				$cashbox = $enabledImmediateCashboxList[$cashboxId];

				CashboxCheckTable::update(
					$checkId,
					array(
						'STATUS' => 'P',
						'DATE_PRINT_START' => new Type\DateTime(),
						'CASHBOX_ID' => $cashbox->getField('ID')
					)
				);

				$printResult = $cashbox->printImmediately($check);
				if ($printResult->isSuccess())
				{
					$data = $printResult->getData();
					if ($data)
					{
						CashboxCheckTable::update($checkId, ['EXTERNAL_UUID' => $data['UUID']]);
					}
				}
				else
				{
					self::savePrintResult(
						$checkId,
						[
							'ID' => $checkId,
							'ERROR' => [
								'TYPE' =>  Errors\Error::TYPE,
								'MESSAGE' => implode("\n", $printResult->getErrorMessages())
							]
						]
					);
				}

				$result->setId($checkId);

				return $result;
			}

			global $CACHE_MANAGER;
			foreach ($cashboxList as $cashbox)
			{
				$CACHE_MANAGER->Read(CACHED_b_sale_order, 'sale_checks_'.$cashbox['ID']);
				$CACHE_MANAGER->SetImmediate('sale_checks_'.$cashbox['ID'], true);
			}
		}
		else
		{
			$result->addErrors($saveResult->getErrors());
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public static function isAvailableCorrection() : bool
	{
		foreach (Manager::getListFromCache() as $item)
		{
			if ($item['ACTIVE'] !== 'Y')
			{
				continue;
			}

			$cashbox = Manager::getObjectById($item['ID']);
			if ($cashbox->isCorrection())
			{
				return true;
			}
		}

		return false;
	}

	public static function addCorrection($type, $cashboxId, array $correction)
	{
		$result = new Result();

		if (!self::isAvailableCorrection())
		{
			return $result->addError(
				new Error(
					Loc::getMessage('SALE_CASHBOX_CHECK_CORRECTION_NOT_AVAILABLE')
				)
			);
		}

		/** @var CorrectionCheck $check */
		$check = self::createByType($type);
		if (!$check instanceof CorrectionCheck)
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK')));
			return $result;
		}

		$check->setField('CASHBOX_ID', $cashboxId);
		$check->setAvailableCashbox([
			Manager::getCashboxFromCache($cashboxId)
		]);

		$check->setCorrectionFields($correction);

		$r = $check->save();
		if ($r->isSuccess())
		{
			$result->setId($check->getField('ID'));

			$cashbox = Manager::getObjectById($cashboxId);
			if ($cashbox->isCorrection())
			{
				CashboxCheckTable::update(
					$check->getField('ID'),
					[
						'STATUS' => 'P', 'DATE_PRINT_START' => new Type\DateTime()
					]
				);

				$printResult = $cashbox->printCorrectionImmediately($check);
				if ($printResult->isSuccess())
				{
					$data = $printResult->getData();
					CashboxCheckTable::update($check->getField('ID'), ['EXTERNAL_UUID' => $data['UUID']]);
				}
				else
				{
					self::savePrintResult(
						$check->getField('ID'),
						[
							'ID' => $check->getField('ID'),
							'ERROR' => [
								'TYPE' =>  Errors\Error::TYPE,
								'MESSAGE' => implode("\n", $printResult->getErrorMessages())
							]
						]
					);
				}

				global $CACHE_MANAGER;
				$CACHE_MANAGER->Read(CACHED_b_sale_order, 'sale_checks_'.$cashboxId);
				$CACHE_MANAGER->SetImmediate('sale_checks_'.$cashboxId, true);
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param $checkId
	 * @param array $data
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws \Exception
	 */
	public static function savePrintResult($checkId, array $data)
	{
		$result = new Result();

		if ($checkId <= 0)
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_ID')));
			return $result;
		}

		$order = null;
		$payment = null;
		$shipment = null;

		$dbRes = self::getList(array('select' => array('*'), 'filter' => array('ID' => $checkId)));
		$check = $dbRes->fetch();
		if (!$check)
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_NOT_FOUND', array('#CHECK_ID#' => $checkId))));
			return $result;
		}

		if ($check['STATUS'] === 'Y')
			return $result;

		$registry = Sale\Registry::getInstance($check['ENTITY_REGISTRY_TYPE']);

		if ($check['ORDER_ID'] > 0)
		{
			/** @var Sale\Order $orderClassName */
			$orderClassName = $registry->getOrderClassName();
			$order = $orderClassName::load($check['ORDER_ID']);
			if ($order === null)
			{
				$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_ORDER_LOAD')));
				return $result;
			}

			$paymentCollection = $order->getPaymentCollection();
			if ($check['PAYMENT_ID'] > 0)
			{
				$payment = $paymentCollection->getItemById($check['PAYMENT_ID']);
				if ($payment === null)
				{
					$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_PAYMENT_LOAD')));
					return $result;
				}
			}

			$shipmentCollection = $order->getShipmentCollection();
			if ($check['SHIPMENT_ID'] > 0)
			{
				$shipment = $shipmentCollection->getItemById($check['SHIPMENT_ID']);
				if ($shipment === null)
				{
					$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_SHIPMENT_LOAD')));
					return $result;
				}
			}
		}

		if (isset($data['ERROR']))
		{
			$errorMessage = Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_PRINT', array('#CHECK_ID#' => $checkId));
			if ($data['ERROR']['MESSAGE'])
				$errorMessage .= ': '.$data['ERROR']['MESSAGE'];

			if ($data['ERROR']['TYPE'] === Errors\Warning::TYPE)
			{
				if ($check['CNT_FAIL_PRINT'] >= 3)
				{
					$data['ERROR']['TYPE'] = Errors\Error::TYPE;
				}
				else
				{
					CashboxCheckTable::update($checkId, array('CNT_FAIL_PRINT' => $check['CNT_FAIL_PRINT'] + 1));
					$result->addError(new Errors\Warning($errorMessage));
					return $result;
				}
			}

			if ($data['ERROR']['TYPE'] === Errors\Error::TYPE)
			{
				$updatedFields = [
					'STATUS' => 'E',
					'DATE_PRINT_END' => new Main\Type\DateTime(),
					'ERROR_MESSAGE' => $data['ERROR']['MESSAGE']
				];
				if ((int)$check['CNT_FAIL_PRINT'] === 0)
					$updatedFields['CNT_FAIL_PRINT'] = 1;

				CashboxCheckTable::update($checkId, $updatedFields);

				/** @ToDO Will be removed after OrderCheckCollection is realized */
				self::addTimelineCheckEntryOnCreateToOrder($order, $checkId, ['PRINTED' => 'N']);

				if ($order !== null
					&& (
						$payment !== null
						|| $shipment !== null
					)
				)
				{
					$r = new Result();
					$errorCode = isset($data['ERROR']['CODE']) ? $data['ERROR']['CODE'] : 0;
					$r->addWarning(new Main\Error($errorMessage, $errorCode));

					/** @var EntityMarker $markerClassName */
					$markerClassName = $registry->getEntityMarkerClassName();

					if ($payment !== null)
					{
						$markerClassName::addMarker($order, $payment, $r);
						$payment->setField('MARKED', 'Y');

						/** @var Sale\Notify $notifyClassName */
						$notifyClassName = $registry->getNotifyClassName();
						$notifyClassName::callNotify($payment, Sale\EventActions::EVENT_ON_CHECK_PRINT_ERROR);
					}
					elseif ($shipment !== null)
					{
						$markerClassName::addMarker($order, $shipment, $r);
						$shipment->setField('MARKED', 'Y');

						/** @var Sale\Notify $notifyClassName */
						$notifyClassName = $registry->getNotifyClassName();
						$notifyClassName::callNotify($shipment, Sale\EventActions::EVENT_ON_CHECK_PRINT_ERROR);
					}

					$order->save();
				}

				$error = new Errors\Error($errorMessage);
				Logger::addError($error->getMessage(), $check['CASHBOX_ID']);
			}
			else
			{
				$error = new Errors\Warning($errorMessage);
				Logger::addWarning($error->getMessage(), $check['CASHBOX_ID']);
			}

			$event = new Main\Event('sale', self::EVENT_ON_CHECK_PRINT_ERROR, array($data));
			$event->send();

			$result->addError($error);
		}
		else
		{
			$updateResult = CashboxCheckTable::update(
				$checkId,
				array(
					'STATUS' => 'Y',
					'LINK_PARAMS' => $data['LINK_PARAMS'],
					'DATE_PRINT_END' => new Main\Type\DateTime()
				)
			);

			if ($updateResult->isSuccess())
			{
				self::addStatisticOnSuccessCheckPrint($checkId);

				self::addTimelineCheckEntryOnCreateToOrder($order, $checkId, ['PRINTED' => 'Y']);

				$isSend = false;
				$event = new Main\Event(
					'sale',
					self::EVENT_ON_CHECK_PRINT_SEND,
					array('PAYMENT' => $payment, 'SHIPMENT' => $shipment, 'CHECK' => $check)
				);
				$event->send();

				$eventResults = $event->getResults();
				/** @var Main\EventResult $eventResult */
				foreach($eventResults as $eventResult)
				{
					if($eventResult->getType() == Main\EventResult::SUCCESS)
						$isSend = true;
				}

				if (!$isSend)
				{
					if ($payment !== null)
					{
						/** @var Sale\Notify $notifyClassName */
						$notifyClassName = $registry->getNotifyClassName();
						$notifyClassName::callNotify($payment, Sale\EventActions::EVENT_ON_CHECK_PRINT);
					}
					elseif ($shipment !== null)
					{
						/** @var Sale\Notify $notifyClassName */
						$notifyClassName = $registry->getNotifyClassName();
						$notifyClassName::callNotify($shipment, Sale\EventActions::EVENT_ON_CHECK_PRINT);
					}
				}
			}
			else
			{
				$result->addErrors($updateResult->getErrors());
			}
		}

		return $result;
	}

	private static function addStatisticOnSuccessCheckPrint($checkId)
	{
		$check = self::getObjectById($checkId);

		$cashbox = Manager::getObjectById($check->getField('CASHBOX_ID'));
		if ($cashbox)
		{
			AddEventToStatFile('sale', 'checkPrint', $checkId, $cashbox::getCode());
		}
	}

	private static function addTimelineCheckEntryOnCreateToOrder($order, $checkId, $params)
	{
		if ($order && Main\Loader::includeModule('crm'))
		{
			\Bitrix\Crm\Timeline\OrderCheckController::getInstance()->onPrintCheck(
				$checkId,
				[
					'ORDER_FIELDS' => $order->getFieldValues(),
					'SETTINGS' => $params,
					'BINDINGS' => \Bitrix\Crm\Order\BindingsMaker\TimelineBindingsMaker::makeByOrder($order),
				]
			);
		}
	}

	/**
	 * @param $id
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function delete($id)
	{
		$r = CashboxCheckTable::delete($id);
		if ($r->isSuccess())
		{
			$dbRes = Sale\Cashbox\Internals\Check2CashboxTable::query()
				->addSelect('ID')
				->where('CHECK_ID', $id);

			while ($link = $dbRes->fetchObject())
			{
				$link->delete();
			}
		}
	}

	/**
	 * @param CollectableEntity[] $entities
	 * @return Result
	 */
	public static function addChecks(array $entities)
	{
		$result = new Result();

		$map = self::collateDocuments($entities);
		foreach ($map as $check)
		{
			$isCorrect = true;

			$event = new Main\Event('sale', self::EVENT_ON_BEFORE_CHECK_ADD_VERIFY, array($check));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach ($event->getResults() as $eventResult)
				{
					if ($eventResult->getType() !== Main\EventResult::ERROR)
					{
						$isCorrect = (bool)$eventResult->getParameters();
					}
				}
			}

			if ($isCorrect)
			{
				$addResult = self::addByType($check["ENTITIES"], $check["TYPE"], $check["RELATED_ENTITIES"]);
				if (!$addResult->isSuccess())
				{
					$result->addErrors($addResult->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param Sale\Internals\CollectableEntity $entity
	 * @throws Main\ArgumentTypeException
	 * @return Sale\Order
	 */
	public static function getOrder($entity)
	{
		$order = null;

		if ($entity instanceof Sale\Payment)
		{
			/** @var Sale\PaymentCollection $col */
			$col = $entity->getCollection();
			$order = $col->getOrder();
		}
		elseif ($entity instanceof Sale\Shipment)
		{
			/** @var Sale\ShipmentCollection $col */
			$col = $entity->getCollection();
			$order = $col->getOrder();
		}
		else
		{
			throw new Main\ArgumentTypeException("entities");
		}

		return $order;
	}

	/**
	 * @return array
	 */
	private static function getBuildInCheckList()
	{
		$checkList = array(
			'\Bitrix\Sale\Cashbox\SellCheck',
			'\Bitrix\Sale\Cashbox\SellReturnCashCheck',
			'\Bitrix\Sale\Cashbox\SellReturnCheck'
		);

		if (Manager::isSupportedFFD105())
		{
			$checkList = array_merge(
				$checkList,
				array(
					'\Bitrix\Sale\Cashbox\CorrectionSellCheck',
					'\Bitrix\Sale\Cashbox\CorrectionBuyCheck',
					'\Bitrix\Sale\Cashbox\AdvancePaymentCheck',
					'\Bitrix\Sale\Cashbox\AdvanceReturnCheck',
					'\Bitrix\Sale\Cashbox\AdvanceReturnCashCheck',
					'\Bitrix\Sale\Cashbox\CreditPaymentCheck',
					'\Bitrix\Sale\Cashbox\CreditPaymentReturnCheck',
					'\Bitrix\Sale\Cashbox\CreditPaymentReturnCashCheck',
					'\Bitrix\Sale\Cashbox\CreditCheck',
					'\Bitrix\Sale\Cashbox\CreditReturnCheck',
					'\Bitrix\Sale\Cashbox\PrepaymentCheck',
					'\Bitrix\Sale\Cashbox\PrepaymentReturnCheck',
					'\Bitrix\Sale\Cashbox\PrepaymentReturnCashCheck',
					'\Bitrix\Sale\Cashbox\FullPrepaymentCheck',
					'\Bitrix\Sale\Cashbox\FullPrepaymentReturnCheck',
					'\Bitrix\Sale\Cashbox\FullPrepaymentReturnCashCheck',
				)
			);
		}

		return $checkList;
	}

	/**
	 * @return array
	 */
	private static function getUserCheckList()
	{
		$checkList = array();

		$event = new Main\Event('sale', self::EVENT_ON_GET_CUSTOM_CHECK);
		$event->send();
		$resultList = $event->getResults();

		if (is_array($resultList) && !empty($resultList))
		{
			foreach ($resultList as $eventResult)
			{
				/** @var  Main\EventResult $eventResult */
				if ($eventResult->getType() === Main\EventResult::SUCCESS)
				{
					$params = $eventResult->getParameters();
					if (!empty($params) && is_array($params))
						$checkList = array_merge($checkList, $params);
				}
			}
		}

		return $checkList;
	}

	/**
	 * @return void
	 */
	public static function init()
	{
		static $isInit = false;

		if ($isInit === false)
		{
			$handlers = self::getUserCheckList();
			Main\Loader::registerAutoLoadClasses(null, $handlers);
			$isInit = true;
		}
	}

	/**
	 * @return array
	 */
	public static function getCheckList()
	{
		static $checkList = array();
		if (!$checkList)
		{
			$checkList = array_merge(
				self::getBuildInCheckList(),
				array_keys(self::getUserCheckList())
			);
		}

		return $checkList;
	}

	public static function getSalesCheckList()
	{
		return array_filter(
			self::getCheckList(),
			function ($value)
			{
				return is_subclass_of($value, Check::class);
			}
		);
	}

	/**
	 * @return array
	 */
	public static function getCheckTypeMap()
	{
		self::init();

		$result = array();
		$checkMap = self::getCheckList();

		/** @var Check $className */
		foreach ($checkMap as $className)
		{
			if (class_exists($className))
			{
				$result[$className::getType()] = $className;
			}
		}

		return $result;
	}

	/**
	 * @param string $type
	 * @return null|Check
	 */
	public static function createByType($type)
	{
		self::init();

		$typeMap = self::getCheckTypeMap();
		$handler = $typeMap[$type];

		return Check::create($handler);
	}

	/**
	 * @param array $entities
	 * @return Entity[]
	 * @throws Main\NotSupportedException
	 */
	public static function collateDocuments(array $entities)
	{
		$map = [];

		$event = new Main\Event('sale', self::EVENT_ON_CHECK_COLLATE_DOCUMENTS, [
			'ENTITIES' => $entities
		]);
		$event->send();
		$eventResults = $event->getResults();
		if ($eventResults != null)
		{
			foreach ($eventResults as $eventResult)
			{
				if ($eventResult->getType() === Main\EventResult::SUCCESS)
				{
					$d = $eventResult->getParameters();
					if (!is_array($d))
						throw new Main\NotSupportedException("OnCheckCollateDocuments event result");

					$map = array_merge($map, $d);
				}
				else if ($eventResult->getType() === Main\EventResult::ERROR)
				{
					return $map;
				}
			}

			if (count($map) > 0)
			{
				return $map;
			}
		}

		$existingChecks = null;
		$order = null;

		/** @var Sale\Payment|Sale\Shipment $entity */
		foreach ($entities as $entity)
		{
			// load existing checks
			if ($existingChecks === null)
			{
				$existingChecks = [];
				$order = self::getOrder($entity);

				$filter = [
					'ORDER_ID' => $order->getId(),
					'ENTITY_REGISTRY_TYPE' => $entity::getRegistryType()
				];
				if ($entity instanceof Sale\Payment)
				{
					$filter["PAYMENT_ID"] = $entity->getId();
				}
				elseif ($entity instanceof Sale\Shipment)
				{
					$filter["SHIPMENT_ID"] = $entity->getId();
				}

				$db = self::getList([
					"filter" => $filter,
					"select" => ["ID", "PAYMENT_ID", "SHIPMENT_ID", "TYPE", "STATUS"]
				]);
				while ($ar = $db->fetch())
				{
					if (intval($ar["PAYMENT_ID"]) > 0)
					{
						$existingChecks["P"][ $ar["PAYMENT_ID"] ][] = $ar;
					}

					if (intval($ar["SHIPMENT_ID"]) > 0)
					{
						$existingChecks["S"][ $ar["SHIPMENT_ID"] ][] = $ar;
					}
				}
			}

			// analysing
			// we should allow users to implement their own algorithms
			if (count($existingChecks) <= 0)
			{
				if (self::isAutomaticEnabled($order))
				{
					if (Manager::isSupportedFFD105())
					{
						if (Manager::isEnabledPaySystemPrint())
						{
							$result = self::collatePaySystemWithFFD105($entity);
						}
						else
						{
							$result = self::collateWithFFD105($entity);
						}
					}
					else
					{
						$result = self::collate($entity);
					}

					if ($result)
					{
						$map = array_merge($map, $result);
					}
				}
			}
		}

		return $map;
	}

	/**
	 * @param Sale\Order $order
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	private static function isAutomaticEnabled(Sale\Order $order)
	{
		$shipmentCollection = $order->getShipmentCollection();
		if (
			(
				$shipmentCollection->count() > 2
				&& $shipmentCollection->isExistsSystemShipment()
			)
			||
			(
				$shipmentCollection->count() > 1
				&& !$shipmentCollection->isExistsSystemShipment()
			)
		)
		{
			return false;
		}

		$paymentCollection = $order->getPaymentCollection();
		if (
			(
				$paymentCollection->isExistsInnerPayment()
				&& $paymentCollection->count() > 2
			)
			||
			(
				!$paymentCollection->isExistsInnerPayment()
				&& $paymentCollection->count() > 1
			)
		)
		{
			return false;
		}

		return true;
	}

	/**
	 * @param $entity
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 */
	private static function collate($entity)
	{
		$map = array();

		if ($entity instanceof Sale\Payment)
		{
			$order = self::getOrder($entity);

			/** @var Sale\PaySystem\Service $service */
			$service = $entity->getPaySystem();
			if ($entity->isPaid() &&
				($service->getField("CAN_PRINT_CHECK") == "Y") &&
				($entity->getSum() == $order->getPrice())
			)
			{
				$checkEntities[] = $entity;

				$shipmentCollection = $order->getShipmentCollection();
				/** @var Sale\Shipment $shipment */
				foreach ($shipmentCollection as $shipment)
				{
					if (!$shipment->isSystem())
						$checkEntities[] = $shipment;
				}

				$map[] = array("TYPE" => SellCheck::getType(), "ENTITIES" => $checkEntities, "RELATED_ENTITIES" => array());
			}
		}

		return $map;
	}

	/**
	 * @param $entity
	 * @return array
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	private static function collateWithFFD105($entity)
	{
		$map = array();

		$order = self::getOrder($entity);
		if (!self::canPrintCheck($order))
		{
			return $map;
		}

		$entities = array();
		$relatedEntities = array();
		if ($entity instanceof Sale\Payment)
		{
			if ($entity->isInner() || !$entity->isPaid())
				return $map;

			$service = $entity->getPaySystem();
			$type = $service->getField('IS_CASH') === 'Y' ? Check::PAYMENT_TYPE_CASHLESS : Check::PAYMENT_TYPE_CASH;
			$entities[$type] = $entity;

			$fields = $order->getFields();
			$originalFields = $fields->getOriginalValues();
			if (!isset($originalFields['DEDUCTED']))
				$originalFields['DEDUCTED'] = $order->getField('DEDUCTED');

			$paymentCollection = $order->getPaymentCollection();
			if ($order->getField('DEDUCTED') === 'Y' && $originalFields['DEDUCTED'] === 'Y')
			{
				if ($paymentCollection->isExistsInnerPayment())
				{
					$relatedEntities[Check::PAYMENT_TYPE_ADVANCE][] = $paymentCollection->getInnerPayment();
				}

				$map[] = array("TYPE" => CreditPaymentCheck::getType(), "ENTITIES" => $entities, "RELATED_ENTITIES" => $relatedEntities);
			}
			else
			{
				$option = Main\Config\Option::get('sale', 'check_type_on_pay', 'sell');
				if ($option === 'prepayment')
				{
					$checkType = ($entity->getSum() == $entity->getOrder()->getPrice()) ? FullPrepaymentCheck::getType() : PrepaymentCheck::getType();

					$shipmentCollection = $order->getShipmentCollection()->getNotSystemItems();
					/** @var Sale\Shipment $shipment */
					foreach ($shipmentCollection as $shipment)
					{
						$relatedEntities[Check::SHIPMENT_TYPE_NONE][] = $shipment;
					}

					$map[] = array("TYPE" => $checkType, "ENTITIES" => $entities, "RELATED_ENTITIES" => $relatedEntities);
				}
				elseif ($option === 'advance')
				{
					$map[] = array("TYPE" => AdvancePaymentCheck::getType(), "ENTITIES" => $entities, "RELATED_ENTITIES" => $relatedEntities);
				}
				else
				{
					if ($paymentCollection->isExistsInnerPayment())
					{
						$map[] = array("TYPE" => AdvancePaymentCheck::getType(), "ENTITIES" => $entities, "RELATED_ENTITIES" => $relatedEntities);
					}
					else
					{
						$shipmentCollection = $order->getShipmentCollection()->getNotSystemItems();
						/** @var Sale\Shipment $shipment */
						foreach ($shipmentCollection as $shipment)
						{
							$relatedEntities[Check::SHIPMENT_TYPE_NONE][] = $shipment;
						}

						$map[] = array("TYPE" => SellCheck::getType(), "ENTITIES" => $entities, "RELATED_ENTITIES" => $relatedEntities);
					}
				}
			}
		}
		elseif ($entity instanceof Sale\Shipment)
		{
			if ($entity->getField('DEDUCTED') !== 'Y')
				return $map;

			$entities[] = $entity;
			if ($order->isPaid())
			{
				if (Main\Config\Option::get('sale', 'check_type_on_pay', 'sell') === 'sell')
				{
					return $map;
				}

				$paymentCollection = $order->getPaymentCollection();
				foreach ($paymentCollection as $payment)
				{
					$relatedEntities[Check::PAYMENT_TYPE_ADVANCE][] = $payment;
				}

				$map[] = array("TYPE" => SellCheck::getType(), "ENTITIES" => $entities, "RELATED_ENTITIES" => $relatedEntities);
			}
			else
			{
				$map[] = array("TYPE" => CreditCheck::getType(), "ENTITIES" => $entities, "RELATED_ENTITIES" => $relatedEntities);
			}
		}
		else
		{
			throw new Main\NotSupportedException();
		}

		return $map;
	}

	/**
	 * @param $entity
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private static function collatePaySystemWithFFD105($entity): array
	{
		$map = [];
		$entities = [];
		$relatedEntities = [];
		$type = SellCheck::getType();

		$option = Main\Config\Option::get('sale', 'check_type_on_pay', 'sell');

		if ($entity instanceof Sale\Payment)
		{
			$fields = $entity->getFields();
			$originalFields = $fields->getOriginalValues();
			if ($originalFields['PAID'] === 'Y' || $fields->get('IS_RETURN') === 'Y')
			{
				return $map;
			}

			$order = $entity->getOrder();

			$entities[] = $entity;

			$isShipped = false;
			/** @var Sale\Shipment $shipment */
			foreach ($order->getShipmentCollection()->getNotSystemItems() as $shipment)
			{
				$isShipped = $shipment->isShipped();
				if (!$isShipped)
				{
					$relatedEntities = [];
					break;
				}

				$relatedEntities[Check::SHIPMENT_TYPE_NONE][] = $shipment;
			}

			if (!$isShipped)
			{
				$order = $entity->getOrder();
				if ($option === 'prepayment')
				{
					$type = (Sale\PriceMaths::roundPrecision($entity->getSum()) === Sale\PriceMaths::roundPrecision($order->getPrice()))
						? FullPrepaymentCheck::getType()
						: PrepaymentCheck::getType();

					$shipmentCollection = $order->getShipmentCollection()->getNotSystemItems();
					/** @var Sale\Shipment $shipment */
					foreach ($shipmentCollection as $shipment)
					{
						$relatedEntities[Check::SHIPMENT_TYPE_NONE][] = $shipment;
					}
				}
				elseif ($option === 'advance')
				{
					$type = AdvancePaymentCheck::getType();
				}
				else
				{
					$shipmentCollection = $order->getShipmentCollection()->getNotSystemItems();
					/** @var Sale\Shipment $shipment */
					foreach ($shipmentCollection as $shipment)
					{
						$relatedEntities[Check::SHIPMENT_TYPE_NONE][] = $shipment;
					}
				}
			}
		}
		elseif ($entity instanceof Sale\Shipment)
		{
			$fields = $entity->getFields();
			$originalFields = $fields->getOriginalValues();
			if ($originalFields['DEDUCTED'] === 'Y' || $fields->get('DEDUCTED') !== 'Y')
			{
				return $map;
			}

			$order = $entity->getOrder();
			if ($order->isPaid() && $entity->isShipped())
			{
				if ($option === 'sell')
				{
					return $map;
				}

				$entities[] = $entity;

				/** @var Sale\Payment $payment */
				foreach ($order->getPaymentCollection() as $payment)
				{
					if ($payment->isInner())
					{
						continue;
					}

					$relatedEntities[Check::PAYMENT_TYPE_CASHLESS][] = $payment;
				}
			}
		}

		if ($entities)
		{
			$map[] = [
				'TYPE' => $type,
				'ENTITIES' => $entities,
				'RELATED_ENTITIES' => $relatedEntities,
			];
		}

		return $map;
	}

	/**
	 * @param Sale\Order $order
	 * @return bool
	 */
	private static function canPrintCheck(Sale\Order $order)
	{
		$paymentCollection = $order->getPaymentCollection();
		if ($paymentCollection->isEmpty())
		{
			return false;
		}

		/** @var Sale\Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			if ($payment->isInner())
				continue;

			$service = $payment->getPaySystem();
			if ($service === null
				|| $service->getField("CAN_PRINT_CHECK") !== 'Y'
			)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array $cashboxIds
	 * @param array $orderIds
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\ObjectException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	public static function getPrintableChecks(array $cashboxIds, array $orderIds = array())
	{
		$result = array();

		$con = Main\Application::getConnection();
		$dbLocRes = $con->query("SELECT GET_LOCK('get_check_list', 0) as L");
		$locResult = $dbLocRes->fetch();
		if ($locResult["L"] == "0")
		{
			return $result;
		}

		$filter = array(
			'LINK_PARAMS' => '',
			'CHECK2CASHBOX.CASHBOX_ID' => $cashboxIds,
			array(
				'LOGIC' => 'OR',
				array(
					'=STATUS' => 'N',
					'DATE_PRINT_START' => ''
				),
				array(
					'=STATUS' => 'P',
					'<MAX_DT_REPEAT_CHECK' => new Type\DateTime()
				)
			)
		);

		if ($orderIds)
		{
			$filter['ORDER_ID'] = $orderIds;
		}

		$limit = count($cashboxIds)*self::CHECK_LIMIT_RECORDS;
		$dbRes = self::getList(
			array(
				'select' => array('*', 'AVAILABLE_CASHBOX_ID' => 'CHECK2CASHBOX.CASHBOX_ID'),
				'filter' => $filter,
				'limit' => $limit,
				'runtime' => array(
					new Main\Entity\ExpressionField(
						'MAX_DT_REPEAT_CHECK',
						'DATE_ADD(DATE_PRINT_START, INTERVAL '.self::CHECK_RESENDING_TIME.' MINUTE)',
						null,
						array(
							'data_type' => 'datetime'
						)
					)
				)
			)
		);

		if ($data = $dbRes->fetch())
		{
			$i = 0;
			do
			{
				if (!isset($result[$data['ID']]))
				{
					$i++;
					if ($i > self::CHECK_LIMIT_RECORDS)
						break;

					$result[$data['ID']] = $data;
					$result[$data['ID']]['CASHBOX_LIST'] = array();
				}

				$result[$data['ID']]['CASHBOX_LIST'][] = $data['AVAILABLE_CASHBOX_ID'];
			}
			while ($data = $dbRes->fetch());

			foreach ($result as $checkId => $item)
			{
				if ($item['STATUS'] === 'P')
				{
					$now = new Type\DateTime();
					$nowTs = $now->getTimestamp();

					/** @var Type\DateTime $dateStartPrint */
					$dateStartPrint = $item['DATE_PRINT_START'];
					$dateStartPrintTs = $dateStartPrint->getTimestamp();

					if ($nowTs - $dateStartPrintTs > self::MIN_TIME_FOR_SWITCH_CASHBOX)
					{
						$availableCashboxIds = array_diff($item['CASHBOX_LIST'], array($item['CASHBOX_ID']));
						if ($availableCashboxIds)
						{
							$result[$checkId]['CASHBOX_ID'] = Manager::chooseCashbox($availableCashboxIds);
							CashboxCheckTable::update($checkId, array('CASHBOX_ID' => $result[$checkId]['CASHBOX_ID']));
						}
					}
					else
					{
						if ($item['CASHBOX_ID'] > 0 && !in_array($item['CASHBOX_ID'], $cashboxIds))
							unset($result[$checkId]);
					}

					continue;
				}

				$result[$checkId]['CASHBOX_ID'] = Manager::chooseCashbox($item['CASHBOX_LIST']);
				CashboxCheckTable::update($checkId, array('STATUS' => 'P', 'DATE_PRINT_START' => new Type\DateTime(), 'CASHBOX_ID' => $result[$checkId]['CASHBOX_ID']));
			}
		}

		$con->query("SELECT RELEASE_LOCK('get_check_list')");

		return $result;
	}

	/**
	 * @param array $settings
	 * @return Check|null
	 */
	public static function create(array $settings)
	{
		$check = CheckManager::createByType($settings['TYPE']);
		if ($check)
			$check->init($settings);

		return $check;
	}

	/**
	 * @param CollectableEntity $entity
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public static function getCheckInfo(Sale\Internals\CollectableEntity $entity)
	{
		$filter = array();
		if ($entity->getId() > 0)
		{
			if ($entity instanceof Sale\Payment)
			{
				$filter['ENTITY_REGISTRY_TYPE'] = $entity::getRegistryType();
				$filter['PAYMENT_ID'] = $entity->getId();
			}
			elseif ($entity instanceof Sale\Shipment)
			{
				$filter['ENTITY_REGISTRY_TYPE'] = $entity::getRegistryType();
				$filter['SHIPMENT_ID'] = $entity->getId();
			}

			return self::collectInfo($filter);
		}

		return array();
	}

	/**
	 * @param CollectableEntity $entity
	 * @return array|false
	 * @throws Main\ArgumentException
	 */
	public static function getLastPrintableCheckInfo(Sale\Internals\CollectableEntity $entity)
	{
		if (!($entity instanceof Sale\Payment)
			&& !($entity instanceof Sale\Shipment)
		)
		{
			return array();
		}

		$filter = array(
			'STATUS' => 'Y',
			'ENTITY_REGISTRY_TYPE' => $entity::getRegistryType()
		);
		if ($entity instanceof Sale\Payment)
		{
			$filter['PAYMENT_ID'] = $entity->getId();
		}
		elseif ($entity instanceof Sale\Shipment)
		{
			$filter['SHIPMENT_ID'] = $entity->getId();
		}

		$dbRes = self::getList(
			array(
				'select' => array('*'),
				'filter' => $filter,
				'order' => array('DATE_PRINT_END' => 'DESC'),
				'limit' => 1
			)
		);

		if ($data = $dbRes->fetch())
		{
			$data['LINK'] = '';
			if (!empty($data['LINK_PARAMS']))
			{
				$cashbox = Manager::getObjectById($data['CASHBOX_ID']);
				if ($cashbox)
					$data['LINK'] = $cashbox->getCheckLink($data['LINK_PARAMS']);
			}

			return $data;
		}

		return array();
	}

	/**
	 * @param array $filter
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public static function collectInfo(array $filter = array())
	{
		$result = array();

		$typeMap = CheckManager::getCheckTypeMap();

		$dbRes = self::getList(
			array(
				'select' => array('*'),
				'filter' => $filter
			)
		);

		while ($data = $dbRes->fetch())
		{
			$data['LINK'] = '';
			if (!empty($data['LINK_PARAMS']))
			{
				$cashbox = Manager::getObjectById($data['CASHBOX_ID']);
				if ($cashbox)
				{
					$data['LINK'] = $cashbox->getCheckLink($data['LINK_PARAMS']);
				}
			}

			/** @var Check $type */
			$type = $typeMap[$data['TYPE']];
			if (class_exists($type))
			{
				$data['TYPE_NAME'] = $type::getName();
			}

			$result[$data['ID']] = $data;
		}

		return $result;
	}

	/**
	 * @param $uuid
	 * @return array|false
	 * @throws Main\ArgumentException
	 */
	public static function getCheckInfoByExternalUuid($uuid)
	{
		$dbRes = self::getList(array('filter' => array('EXTERNAL_UUID' => $uuid)));
		return $dbRes->fetch();
	}

	/**
	 * @param $id
	 * @return Check|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getObjectById($id)
	{
		if ($id <= 0)
			return null;

		$dbRes = CashboxCheckTable::getById($id);
		if ($checkInfo = $dbRes->fetch())
		{
			$check = self::createByType($checkInfo['TYPE']);
			if ($check)
			{
				$check->init($checkInfo);
				return $check;
			}
		}

		return null;
	}

	/**
	 * @param array $parameters
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $parameters = array())
	{
		return CashboxCheckTable::getList($parameters);
	}

	/**
	 * @param $checkType
	 * @param $paymentId
	 * @param string $registryType
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 */
	public static function getRelatedEntitiesForPayment($checkType, $paymentId, $registryType = Sale\Registry::REGISTRY_TYPE_ORDER)
	{
		$result = array();

		$check = self::createByType($checkType);
		if ($check === null)
		{
			throw new Main\ArgumentTypeException($checkType);
		}

		$registry = Sale\Registry::getInstance($registryType);
		/** @var Sale\Payment $paymentClassName */
		$paymentClassName = $registry->getPaymentClassName();

		$dbRes = $paymentClassName::getList(array(
			'select' => array('ORDER_ID'),
			'filter' => array('=ID' => $paymentId)
		));

		$paymentData = $dbRes->fetch();
		if (!$paymentData)
		{
			return $result;
		}

		if ($check::getSupportedRelatedEntityType() === Check::SUPPORTED_ENTITY_TYPE_PAYMENT
			|| $check::getSupportedRelatedEntityType() === Check::SUPPORTED_ENTITY_TYPE_ALL
		)
		{
			if (Manager::isSupportedFFD105())
			{
				$dbRes = $paymentClassName::getList(array(
					'select' => array('ID', 'ACCOUNT_NUMBER', 'SUM', 'CURRENCY', 'NAME' => 'PAY_SYSTEM.NAME'),
					'filter' => array(
						'!ID' => $paymentId,
						'=ORDER_ID' => $paymentData['ORDER_ID']
					)
				));

				while ($data = $dbRes->fetch())
				{
					$data['PAYMENT_TYPES'] = array(
						array(
							'CODE' => Sale\Cashbox\Check::PAYMENT_TYPE_ADVANCE,
							'NAME' => Loc::getMessage('SALE_CASHBOX_CHECK_ADVANCE'),
						),
						array(
							'CODE' => Sale\Cashbox\Check::PAYMENT_TYPE_CREDIT,
							'NAME' => Loc::getMessage('SALE_CASHBOX_CHECK_CREDIT'),
						)
					);

					$result['PAYMENTS'][$data['ID']] = $data;
				}
			}
		}
		if ($check::getSupportedRelatedEntityType() === Check::SUPPORTED_ENTITY_TYPE_SHIPMENT
			|| $check::getSupportedRelatedEntityType() === Check::SUPPORTED_ENTITY_TYPE_ALL
		)
		{
			/** @var Sale\Shipment $shipmentClassName */
			$shipmentClassName = $registry->getShipmentClassName();
			$dbRes = $shipmentClassName::getList(array(
				'select' => array('ID', 'ACCOUNT_NUMBER', 'PRICE_DELIVERY', 'CURRENCY', 'NAME' => 'DELIVERY.NAME'),
				'filter' => array(
					'=ORDER_ID' => $paymentData['ORDER_ID'],
					'SYSTEM' => 'N'
				)
			));

			while ($data = $dbRes->fetch())
			{
				$result['SHIPMENTS'][$data['ID']] = $data;
			}
		}

		return $result;
	}

	/**
	 * @param $checkType
	 * @param $shipmentId
	 * @param string $registryType
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 */
	public static function getRelatedEntitiesForShipment($checkType, $shipmentId, $registryType = Sale\Registry::REGISTRY_TYPE_ORDER)
	{
		$result = array();

		if (!Manager::isSupportedFFD105())
		{
			return $result;
		}

		$check = self::createByType($checkType);
		if ($check === null)
		{
			throw new Main\ArgumentTypeException($checkType);
		}

		$registry = Sale\Registry::getInstance($registryType);
		/** @var Sale\Shipment $shipmentClassName */
		$shipmentClassName = $registry->getShipmentClassName();

		$dbRes = $shipmentClassName::getList(array(
			'select' => array('ORDER_ID'),
			'filter' => array('=ID' => $shipmentId)
		));

		$shipmentData = $dbRes->fetch();
		if (!$shipmentData)
		{
			return $result;
		}

		if ($check::getSupportedRelatedEntityType() === Check::SUPPORTED_ENTITY_TYPE_SHIPMENT
			|| $check::getSupportedRelatedEntityType() === Check::SUPPORTED_ENTITY_TYPE_ALL
		)
		{
			$dbRes = $shipmentClassName::getList(array(
				'select' => array('ID', 'ACCOUNT_NUMBER', 'PRICE_DELIVERY', 'CURRENCY', 'NAME' => 'DELIVERY.NAME'),
				'filter' => array(
					'!ID' => $shipmentId,
					'=ORDER_ID' => $shipmentData['ORDER_ID'],
					'SYSTEM' => 'N'
				)
			));

			while ($data = $dbRes->fetch())
			{
				$result['SHIPMENTS'][$data['ID']] = $data;
			}
		}

		if ($check::getSupportedRelatedEntityType() === Check::SUPPORTED_ENTITY_TYPE_PAYMENT
			|| $check::getSupportedRelatedEntityType() === Check::SUPPORTED_ENTITY_TYPE_ALL
		)
		{
			/** @var Sale\Payment $paymentClassName */
			$paymentClassName = $registry->getPaymentClassName();
			$dbRes = $paymentClassName::getList(array(
				'select' => array('ID', 'ACCOUNT_NUMBER', 'SUM', 'CURRENCY', 'NAME' => 'PAY_SYSTEM.NAME'),
				'filter' => array(
					'=ORDER_ID' => $shipmentData['ORDER_ID']
				)
			));

			while ($data = $dbRes->fetch())
			{
				$data['PAYMENT_TYPES'] = array(
					array(
						'CODE' => Sale\Cashbox\Check::PAYMENT_TYPE_ADVANCE,
						'NAME' => Loc::getMessage('SALE_CASHBOX_CHECK_ADVANCE'),
					),
					array(
						'CODE' => Sale\Cashbox\Check::PAYMENT_TYPE_CREDIT,
						'NAME' => Loc::getMessage('SALE_CASHBOX_CHECK_CREDIT'),
					)
				);

				$result['PAYMENTS'][$data['ID']] = $data;
			}
		}

		return $result;
	}

	/**
	 * Returns payment by check
	 *
	 * @param Check $check
	 * @return Sale\Payment
	 */
	public static function getPaymentByCheck(Check $check): ?Sale\Payment
	{
		$payment = null;

		$entities = $check->getEntities();
		foreach ($entities as $entity)
		{
			if ($entity instanceof Sale\Payment)
			{
				$payment = $entity;
			}
		}

		if (!$payment)
		{
			$relatedEntities = $check->getRelatedEntities();
			foreach ($relatedEntities as $relatedEntityCollection)
			{
				foreach ($relatedEntityCollection as $relatedEntity)
				{
					if ($relatedEntity instanceof Sale\Payment)
					{
						$payment = $relatedEntity;
					}
				}
			}
		}

		return $payment;
	}
}