<?php


namespace Bitrix\Sale;


use Bitrix\Main;
use Bitrix\Sale\Internals;
use Bitrix\Sale;

Main\Localization\Loc::loadMessages(__FILE__);

class EntityMarker
{
	const ENTITY_MARKED_TYPE_AUTO = 'AUTO';
	const ENTITY_MARKED_TYPE_MANUAL = 'MANUAL';

	const ENTITY_TYPE_ORDER = 'ORDER';
	const ENTITY_TYPE_BASKET_ITEM = 'BASKET_ITEM';
	const ENTITY_TYPE_SHIPMENT = 'SHIPMENT';
	const ENTITY_TYPE_PAYMENT = 'PAYMENT';
	const ENTITY_TYPE_PROPERTY_VALUE = 'PROPERTY_VALUE';

	const ENTITY_SUCCESS_CODE_FAIL = 'N';
	const ENTITY_SUCCESS_CODE_DONE = 'Y';

	/** @var array $pool */
	protected static $pool = array();

	/**
	 * @param OrderBase $order
	 * @param Internals\Entity $entity
	 * @param Result $result
	 */
	public static function addMarker(OrderBase $order, Internals\Entity $entity, Result $result)
	{
		if (!$result->hasWarnings())
		{
			return;
		}

		$entityType = static::getEntityType($entity);
		if ($entityType === null)
		{
			return;
		}

		$fields = array(
			'ENTITY' => $entity,
			'ORDER' => $order,
		);

		if ($order->getId() > 0)
		{
			$fields['ORDER_ID'] = $order->getId();
		}

		if ($entity->getId() > 0)
		{
			$fields['ENTITY_ID'] = $entity->getId();
		}

		$fields['ENTITY_TYPE'] = $entityType;
		/** @var ResultError $resultError */
		foreach ($result->getWarnings() as $resultWarning)
		{
			$code = $resultWarning->getCode();
			$message = $resultWarning->getMessage();
			$isAutoFix = false;

			if ($entity instanceof \IEntityMarker)
			{
				$isAutoFix = $entity->canAutoFixError($code);
			}

			$fields['CODE'] = $code;
			$fields['MESSAGE'] = $message;
			$fields['TYPE'] = $isAutoFix ? static::ENTITY_MARKED_TYPE_AUTO : static::ENTITY_MARKED_TYPE_MANUAL;
			$fields['SUCCESS'] = static::ENTITY_SUCCESS_CODE_FAIL;
			static::addItem($order, $entityType, $fields);
		}
		$lastWarning = end($result->getWarnings());
		$order->setField('REASON_MARKED', $lastWarning->getMessage());

	}

	/**
	 * @param $id
	 * @param array $values
	 * @param Order $order
	 * @param Internals\Entity $entity
	 *
	 * @return Result
	 */
	public static function updateMarker($id, array $values, Order $order, Internals\Entity $entity)
	{
		$result = new Result();
		$entityType = static::getEntityType($entity);
		if ($entityType !== null)
		{
			$r = static::updateItem($id, $values, $order, $entityType);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param OrderBase $order
	 * @param $entityType
	 * @param array $values
	 *
	 * @return bool
	 */
	protected static function addItem(OrderBase $order, $entityType, array $values)
	{
		$orderCode = $order->getInternalId();

		if (!empty(static::$pool[$orderCode]) && !empty(static::$pool[$orderCode][$entityType]) && is_array(static::$pool[$orderCode][$entityType]))
		{
			foreach (static::$pool[$orderCode][$entityType] as $index => $fields)
			{
				$foundItem = false;

				foreach (static::getFieldsDuplicateCheck() as $checkField)
				{
					if (!empty($fields[$checkField]) && !empty($values[$checkField]) && $fields[$checkField] == $values[$checkField])
					{
						$foundItem = true;
						continue;
					}

					$foundItem = false;
					break;
				}

				if ($foundItem)
				{
					if (!empty($values['SUCCESS']))
					{
						static::$pool[$orderCode][$entityType][$index]['SUCCESS'] = $values['SUCCESS'];
						return true;
					}
				}
			}
		}

		static::$pool[$orderCode][$entityType][] = $values;
		return true;
	}

	/**
	 * @param $id
	 * @param $values
	 * @param Order $order
	 * @param $entityType
	 *
	 * @return Result
	 */
	protected static function updateItem($id, $values, Order $order, $entityType)
	{
		$orderCode = $order->getInternalId();
		$result = new Result();

		if (!empty(static::$pool[$orderCode]) && !empty(static::$pool[$orderCode][$entityType]) && is_array(static::$pool[$orderCode][$entityType]))
		{
			foreach (static::$pool[$orderCode][$entityType] as $index => $fields)
			{
				$foundItem = false;
				if ((isset($fields['ID']) && $id > 0 && intval($fields['ID']) == $id))
				{
					$foundItem = true;
				}

				if (!$foundItem)
				{
					foreach (static::getFieldsDuplicateCheck() as $checkField)
					{
						if (!empty($fields[$checkField]) && !empty($values[$checkField]) && $fields[$checkField] == $values[$checkField])
						{
							$foundItem = true;
							continue;
						}

						$foundItem = false;
						break;
					}
				}

				if ($foundItem)
				{
					static::$pool[$orderCode][$entityType][$index] = array_merge($fields, $values);
					return $result;
				}
			}
		}

		$values['ID'] = $id;

		if (empty($values['ORDER']))
		{
			$values['ORDER'] = $order;
		}

		if ($order->getId() > 0)
		{
			$values['ORDER_ID'] = $order->getId();
		}

		static::$pool[$orderCode][$entityType][] = $values;

		return $result;
	}

	/**
	 * @param int $orderCode
	 * @param Internals\Entity|null $entity
	 *
	 * @return array|null
	 */
	public static function getMarker($orderCode, Internals\Entity $entity = null)
	{
		if (empty(static::$pool[$orderCode]))
		{
			return null;
		}

		if ($entity !== null)
		{
			$entityType = static::getEntityType($entity);
			if ($entityType !== null && array_key_exists($entityType, static::$pool[$orderCode]))
			{
				return static::$pool[$orderCode][$entityType];
			}
		}
		else
		{
			return static::$pool[$orderCode];
		}

		return null;
	}

	/**
	 * @return array
	 */
	protected static function getEntityTypeList()
	{
		return array(
			static::ENTITY_TYPE_ORDER => '\Bitrix\Sale\OrderBase',
			static::ENTITY_TYPE_BASKET_ITEM => '\Bitrix\Sale\BasketItemBase',
			static::ENTITY_TYPE_SHIPMENT => '\Bitrix\Sale\Shipment',
			static::ENTITY_TYPE_PAYMENT => '\Bitrix\Sale\Payment',
			static::ENTITY_TYPE_PROPERTY_VALUE => '\Bitrix\Sale\PropertyValue',
		);
	}

	/**
	 * @param Internals\Entity $entity
	 *
	 * @return null|string
	 */
	protected static function getEntityType(Internals\Entity $entity)
	{
		$typeList = static::getEntityTypeList();

		foreach ($typeList as $type => $entityClass)
		{
			if ($entity instanceof $entityClass)
			{
				return $type;
			}
		}

		return null;
	}

	/**
	 * @param null|Order $order
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws \Exception
	 */
	public static function saveMarkers(Order $order = null)
	{
		global $USER;
		$result = new Result();

		$saveList = array();

		$oldMarkerDataList = array();

		$orderCode = null;

		$newOrderList = array();

		if ($order instanceof Order && $order->getId() > 0)
		{
			$orderCode = $order->getInternalId();
		}

		foreach (static::$pool as $orderIndex => $entityList)
		{
			foreach ($entityList as $entityType => $fieldsList)
			{
				foreach ($fieldsList as $fieldIndex => $values)
				{
					if ($values['ORDER'] instanceof Order)
					{
						if (empty($values['ORDER_ID']) && $values['ORDER']->getId() > 0)
						{
							$values['ORDER_ID'] = $values['ORDER']->getId();
							$newOrderList[] = $values['ORDER_ID'];
						}

						if ($order instanceof Order && $values['ORDER']->getInternalId() != $order->getInternalId())
						{
							continue 3;
						}
					}

					if (!empty($values['ENTITY']) && $values['ENTITY'] instanceof Internals\Entity)
					{
						if (empty($values['ENTITY_TYPE']))
						{
							$entityType = static::getEntityType($values['ENTITY']);

							if (strval($entityType) != '')
							{
								$values['ENTITY_TYPE'] = $entityType;
							}
						}

						if (intval($values['ENTITY_ID']) <= 0)
						{
							$values['ENTITY_ID'] = $values['ENTITY']->getId();
						}
					}

					$fields = array();

					if (empty($values['ID']))
					{
						if (intval($values['ENTITY_ID']) <= 0)
						{
							continue;
						}

						if (empty($values['ENTITY_TYPE']))
						{
							throw new Main\ArgumentNullException('ENTITY_TYPE');
						}

						$fields = array(
							'ENTITY_TYPE' => $values['ENTITY_TYPE'],
							'ENTITY_ID' => intval($values['ENTITY_ID']),
							'TYPE' => $values['TYPE'],
							'CODE' => $values['CODE'],
							'MESSAGE' => $values['MESSAGE'],
							'COMMENT' => $values['COMMENT'],
						);

						if (is_object($USER) && $USER->IsAuthorized())
						{
							$fields['USER_ID'] = $USER->GetID();
						}
					}

					if (intval($values['ORDER_ID']) >= 0)
					{
						$fields['ORDER_ID'] = intval($values['ORDER_ID']);
					}

					if (empty($fields['ENTITY_ID']) && intval($values['ENTITY_ID']) >= 0)
					{
						$fields['ENTITY_ID'] = intval($values['ENTITY_ID']);
					}

					if (empty($fields['ENTITY_TYPE']) && !empty($values['ENTITY_TYPE']))
					{
						$fields['ENTITY_TYPE'] = $values['ENTITY_TYPE'];
					}

					if (!empty($values['ID']))
					{
						$fields['ID'] = $values['ID'];
					}

					if (!empty($values['SUCCESS']))
					{
						$fields['SUCCESS'] = $values['SUCCESS'];
					}

					if (!empty($values['DATE_CREATE']) && $values['DATE_CREATE'] instanceof Main\Type\Date)
					{
						$fields['DATE_CREATE'] = $values['DATE_CREATE'];
					}

					if (!empty($values['DATE_UPDATE']) && $values['DATE_UPDATE'] instanceof Main\Type\Date)
					{
						$fields['DATE_UPDATE'] = $values['DATE_UPDATE'];
					}

					if ($values['ORDER'] instanceof Order)
					{
						unset(static::$pool[$values['ORDER']->getInternalId()][$entityType][$fieldIndex]);
					}

					if (empty($fields))
						continue;

					$markerOrderId = null;

					if (!empty($values['ORDER_ID']))
					{
						$markerOrderId = $values['ORDER_ID'];
					}

					$saveList[$markerOrderId][] = $fields;
				}
			}
		}

		if (!empty($saveList) && is_array($saveList))
		{
			$filter = array(
				'select' => array(
					'ID', 'ORDER_ID', 'ENTITY_TYPE', 'ENTITY_ID', 'CODE', 'SUCCESS', 'MESSAGE'
				),
				'filter' => array(
					'!=SUCCESS' => static::ENTITY_SUCCESS_CODE_DONE
				),
				'order' => array('ID' => 'ASC')
			);

			foreach ($saveList as $fieldsList)
			{

				foreach ($fieldsList as $fields)
				{
					if (!empty($fields['ORDER_ID']) && in_array($fields['ORDER_ID'], $newOrderList))
					{
						continue;
					}

					if (!empty($fields['ORDER_ID']) && (empty($filter['filter']['=ORDER_ID']) || !in_array($fields['ORDER_ID'], $filter['filter']['=ORDER_ID'])))
					{
						$filter['filter']['=ORDER_ID'][] = $fields['ORDER_ID'];
					}

					if (!empty($fields['ENTITY_TYPE'])
						&& (empty($filter['filter']['=ENTITY_TYPE'])
							|| (is_array($filter['filter']['=ENTITY_TYPE']) && !in_array($fields['ENTITY_TYPE'], $filter['filter']['=ENTITY_TYPE']))))
					{
						$filter['filter']['=ENTITY_TYPE'][] = $fields['ENTITY_TYPE'];
					}
				}
			}
			
			
			if (!empty($filter['filter']['=ENTITY_TYPE']))
			{
				$res = static::getList($filter);
				while($data = $res->fetch())
				{
					if (isset($saveList[$data['ORDER_ID']]) && is_array($saveList[$data['ORDER_ID']]))
					{
						foreach($saveList[$data['ORDER_ID']] as $key => $values)
						{
							if (!empty($values['ID']) && $data['ID'] == $values['ID'])
							{
								$oldMarkerDataList[$data['ID']] = $data;

								$values = array_merge($data, $values);
								$saveList[$data['ORDER_ID']][$key] = $values;
								continue;
							}
							$foundItem = false;

							if (!$foundItem)
							{
								foreach (static::getFieldsDuplicateCheck() as $checkField)
								{
									if (!empty($data[$checkField]) && !empty($values[$checkField]) && $data[$checkField] == $values[$checkField])
									{
										$foundItem = true;
										continue;
									}

									$foundItem = false;
									break;
								}
							}

							if ($foundItem)
							{
								foreach($saveList[$data['ORDER_ID']] as $doubleKey => $doubleValues)
								{
									if ($doubleKey == $key)
										continue;

									if (!empty($doubleValues['ID']) && $data['ID'] == $doubleValues['ID'])
									{
										if (empty($values['SUCCESS']))
										{
											unset($doubleValues['SUCCESS']);
										}

										$values = array_merge($doubleValues, $values);
										unset($saveList[$data['ORDER_ID']][$doubleKey]);
									}
								}

								$fields = array(
									'ID' => $data['ID'],
								);

								if (!empty($values['SUCCESS']) && $data['SUCCESS'] != $values['SUCCESS'])
								{
									$fields['SUCCESS'] = $values['SUCCESS'];
								}

								$saveList[$data['ORDER_ID']][$key] = $fields;
							}
						}
					}
				}
			}

			foreach ($saveList as $orderId => $fieldsList)
			{
				foreach ($fieldsList as $fields)
				{
					if (!empty($fields['ID']))
					{
						$elementId = intval($fields['ID']);
						unset($fields['ID']);

						if (empty($fields))
							continue;

						if (!empty($oldMarkerDataList) && !empty($oldMarkerDataList[$elementId]))
						{
							foreach($fields as $fieldName => $fieldValue)
							{
								if (array_key_exists($fieldName, $oldMarkerDataList[$elementId])
									&& $oldMarkerDataList[$elementId][$fieldName] == $fieldValue)
								{
									unset($fields[$fieldName]);
								}
							}
						}

						if (empty($fields))
							continue;

						if (empty($fields['DATE_UPDATE']))
						{
							$fields['DATE_UPDATE'] = new Main\Type\DateTime();
						}

						if (!empty($fields['SUCCESS']) && $fields['SUCCESS'] == static::ENTITY_SUCCESS_CODE_DONE
							&& !empty($oldMarkerDataList) && !empty($oldMarkerDataList[$elementId]))
						{
							OrderHistory::addAction(
								$oldMarkerDataList[$elementId]['ENTITY_TYPE'],
								$oldMarkerDataList[$elementId]['ORDER_ID'],
								'MARKER_SUCCESS',
								$oldMarkerDataList[$elementId]['ENTITY_ID'],
								null,
								array(
									"ENTITY_ID" => $oldMarkerDataList[$elementId]['ENTITY_ID'],
									"MESSAGE" => $oldMarkerDataList[$elementId]['MESSAGE'],
									"ENTITY_TYPE" => $oldMarkerDataList[$elementId]['ENTITY_TYPE'],
								),
								OrderHistory::SALE_ORDER_HISTORY_ACTION_LOG_LEVEL_1
							);

							$r = static::delete($elementId);
							if (!$r->isSuccess())
							{
								$result->addErrors($r->getErrors());
							}

							continue;
						}

						$r = static::updateInternal($elementId, $fields);
						if (!$r->isSuccess())
						{
							$result->addErrors($r->getErrors());
						}
					}
					else
					{
						if (empty($fields['DATE_CREATE']))
						{
							$fields['DATE_CREATE'] = new Main\Type\DateTime();
						}

						$r = static::addInternal($fields);
						if (!$r->isSuccess())
						{
							$result->addErrors($r->getErrors());
						}
					}
				}
			}
		}

		static::resetMarkers($orderCode);

		return $result;
	}


	protected static function resetMarkers($orderCode = null)
	{
		if (intval($orderCode) > 0)
		{
			unset(static::$pool[$orderCode]);
		}
		else
		{
			static::$pool = array();
		}
	}
	/**
	 * @param Order $order
	 * @param $markerId
	 *
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	public static function tryFixErrorsByOrder(Order $order, $markerId = null)
	{
		$result = new Result();
		if ($order->getId() <=0)
		{
			return $result;
		}

		$resultList = array(
			'LIST' => array(),
			'ERRORS' => array(),
		);

		$filter = array(
			'filter' => array(
				'=ORDER_ID' => $order->getId(),
				'=TYPE' => static::ENTITY_MARKED_TYPE_AUTO,
			),
			'select' => array('ID', 'ENTITY_TYPE', 'ENTITY_ID', 'CODE', 'SUCCESS'),
			'order' => array('ID' => 'DESC')
		);

		if (intval($markerId) > 0)
		{
			$filter['filter']['=ID'] = intval($markerId);
		}
		else
		{
			$filter['filter']['!=SUCCESS'] = static::ENTITY_SUCCESS_CODE_DONE;
		}
		
		$res = static::getList($filter);
		while($markerData = $res->fetch())
		{
			if ($markerData['SUCCESS'] == static::ENTITY_SUCCESS_CODE_DONE)
			{
				$resultList['LIST'][$markerData['ID']] = static::ENTITY_SUCCESS_CODE_DONE;
			}
			else
			{
				if (!$entity = static::getEntity($order, $markerData['ENTITY_TYPE'], $markerData['ENTITY_ID']))
				{
					$result->addError(new ResultError(Main\Localization\Loc::getMessage('SALE_ENTITY_MARKER_ENTITY_NOT_FOUND'), 'SALE_ENTITY_MARKER_ENTITY_NOT_FOUND'));
					return $result;
				}

				if (!($entity instanceof \IEntityMarker))
				{
					return $result;
				}

				$r = $entity->tryFixError($markerData['CODE']);
				if ($r->isSuccess() && !$r->hasWarnings())
				{
					$markerData['SUCCESS'] = static::ENTITY_SUCCESS_CODE_DONE;
				}
				else
				{
					$markerData['SUCCESS'] = static::ENTITY_SUCCESS_CODE_FAIL;
					if (!isset($resultList['ERRORS'][$markerData['ID']]))
					{
						$resultList['ERRORS'][$markerData['ID']] = array();
					}
					$resultList['ERRORS'][$markerData['ID']] = array_merge($resultList['ERRORS'][$markerData['ID']], $r->getWarningMessages());
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}

				static::updateMarker($markerData['ID'], $markerData, $order, $entity);
				$resultList['LIST'][$markerData['ID']] = ($markerData['SUCCESS'] == static::ENTITY_SUCCESS_CODE_DONE);
			}
		}

		if (!empty($resultList) && is_array($resultList))
		{
			$result->setData($resultList);
		}

		return $result;
	}


	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	public static function tryFixErrors()
	{
		static $orderList = array();
		$orderSaveList = array();
		$lastOrderId = null;

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$result = new Result();
		$res = static::getList(array(
			'filter' => array(
				'=TYPE' => static::ENTITY_MARKED_TYPE_AUTO,
				'!=SUCCESS' => static::ENTITY_SUCCESS_CODE_DONE
			),
			'select' => array('ID', 'ENTITY_TYPE', 'ENTITY_ID', 'CODE', 'ORDER_ID'),
			'order' => array('ORDER_ID' => 'ASC', 'ID' => 'DESC')
		));
		while($data = $res->fetch())
		{
			if (array_key_exists($data['ORDER_ID'], $orderList))
			{
				$order = $orderList[$data['ORDER_ID']];
			}
			else
			{
				$order = $orderClass::load($data['ORDER_ID']);
				$orderList[$data['ORDER_ID']] = $order;
			}

			if (!$entity = static::getEntity($order, $data['ENTITY_TYPE'], $data['ENTITY_ID']))
			{
				continue;
			}

			if ($lastOrderId !== null && $lastOrderId !== $order->getId())
			{
				if (isset($orderSaveList[$lastOrderId]))
				{
					$r = $orderSaveList[$lastOrderId]->save();
					unset($orderSaveList[$lastOrderId]);
				}
			}

			if (!($entity instanceof \IEntityMarker))
			{
				continue;
			}

			$r = $entity->tryFixError($data['CODE']);
			if ($r->isSuccess())
			{
				$data['SUCCESS'] = static::ENTITY_SUCCESS_CODE_DONE;

				if (!array_key_exists($data['ORDER_ID'], $orderSaveList))
				{
					$orderSaveList[$order->getId()] = $order;
				}
			}
			else
			{
				$data['SUCCESS'] = static::ENTITY_SUCCESS_CODE_FAIL;
			}

			static::updateMarker($data['ID'], $data, $order, $entity);

			$lastOrderId = $order->getId();
		}

		if (!empty($orderSaveList))
		{
			foreach ($orderSaveList as $order)
			{
				$order->save();
			}
		}

		foreach ($orderList as $order)
		{
			static::saveMarkers($order);
		}

		return $result;
	}

	public static function loadFromDb(array $filter)
	{
		$entityDat = static::getList($filter)->fetch();
		if ($entityDat)
		{
			return $entityDat;
		}

		return false;
	}

	/**
	 * @param Order $order
	 * @param string $entityType
	 * @param int $entityId
	 *
	 * @return Internals\Entity
	 * @throws Main\ArgumentNullException
	 */
	public static function getEntity(Order $order, $entityType, $entityId)
	{
		static $entityList = array();

		$hash = md5($order->getId(). '|'. $entityType . '|' . $entityId);

		if (!empty($entityList[$hash]))
		{
			return $entityList[$hash];
		}

		$entity = null;
		$entityCollection = null;

		if ($entityType == static::ENTITY_TYPE_ORDER)
		{
			if ($order->getId() == $entityId)
			{
				return $order;
			}
			return null;
		}
		elseif($entityType == static::ENTITY_TYPE_SHIPMENT)
		{
			/** @var Internals\EntityCollection $entityCollection */
			$entityCollection = $order->getShipmentCollection();
		}
		elseif($entityType == static::ENTITY_TYPE_PAYMENT)
		{
			/** @var Internals\EntityCollection $entityCollection */
			$entityCollection = $order->getPaymentCollection();
		}
		elseif($entityType == static::ENTITY_TYPE_BASKET_ITEM)
		{
			/** @var Internals\EntityCollection $entityCollection */
			$entityCollection = $order->getBasket();
		}

		if ($entity === null && !$entityCollection)
			return null;

		if ($entity === null)
		{
			/** @var Internals\Entity $entity */
			if (!$entity = $entityCollection->getItemById($entityId))
			{
				return null;
			}
		}

		if ($entity !== null)
		{
			$entityList[$hash] = $entity;
		}

		return $entity;
	}

	/**
	 * @param array $parameters
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\EntityMarkerTable::getList($parameters);
	}

	/**
	 * @param $id
	 *
	 * @return Main\Entity\DeleteResult
	 * @throws Main\ArgumentNullException
	 * @throws \Exception
	 */
	public static function delete($id)
	{
		if (intval($id) <= 0)
		{
			throw new Main\ArgumentNullException('ID');
		}

		return Internals\EntityMarkerTable::delete($id);
	}

	protected static function addInternal(array $data)
	{
		return Internals\EntityMarkerTable::add($data);
	}

	protected static function updateInternal($primary, array $data)
	{
		return Internals\EntityMarkerTable::update($primary, $data);
	}

	/**
	 * @param Order $order
	 * @param int $id
	 * @param string $entityType
	 * @param int $entityId
	 * @param string $code
	 *
	 * @return string|null
	 */
	public static function getPoolItemSuccess(Order $order, $id, $entityType, $entityId, $code)
	{
		$orderCode = $order->getInternalId();

		if (!empty(static::$pool[$orderCode]))
		{
			foreach (static::$pool[$orderCode] as $poolEntityType => $fieldsList)
			{
				foreach ($fieldsList as $fieldIndex => $values)
				{
					if ($values['ORDER'] instanceof Order)
					{
						if ($order instanceof Order && $values['ORDER']->getInternalId() != $order->getInternalId())
						{
							continue 2;
						}
					}

					if (!empty($values['SUCCESS'])
						&& (isset($values['ENTITY_ID']) && intval($values['ENTITY_ID']) == intval($entityId))
						&& (isset($values['ENTITY_TYPE']) && $values['ENTITY_TYPE'] == $entityType)
						&& (isset($values['CODE']) && $values['CODE'] == $code)
					)
					{
						if ((!empty($values['ID']) && $values['ID'] == $id) || !isset($values['ID']))
						{
							return $values['SUCCESS'];
						}
					}
				}
			}
		}

		return null;
	}

	public static function hasErrors(Order $order)
	{
		$orderCode = $order->getInternalId();
		if (!empty(static::$pool[$orderCode]))
		{
			foreach (static::$pool[$orderCode] as $poolEntityType => $fieldsList)
			{
				foreach ($fieldsList as $fieldIndex => $values)
				{
					if ($values['ORDER'] instanceof Order)
					{
						if ($order instanceof Order && $values['ORDER']->getInternalId() != $order->getInternalId())
						{
							continue 2;
						}
					}

					if(empty($values['SUCCESS']) || ($values['SUCCESS'] != static::ENTITY_SUCCESS_CODE_DONE))
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * @param null|OrderBase $order
	 *
	 * @return Result
	 */
	public static function getPoolAsResult(OrderBase $order = null)
	{
		$result = new Result();


		foreach (static::$pool as $entityList)
		{
			foreach ($entityList as $entityType => $fieldsList)
			{
				foreach ($fieldsList as $fieldIndex => $values)
				{
					if ($values['ORDER'] instanceof Order)
					{
						if ($order instanceof Order && $values['ORDER']->getInternalId() != $order->getInternalId())
						{
							continue 2;
						}
					}

					$result->addError(new ResultError($values['MESSAGE'], $values['CODE']));
				}
			}
		}

		return $result;
	}


	private static function getFieldsDuplicateCheck()
	{
		return array(
			'ENTITY_ID',
			'ENTITY_TYPE',
			'CODE',
		);
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public static function deleteByOrderId($id)
	{
		if(intval($id) <= 0)
			return false;

		$res = static::getList(array(
			'filter' => array(
				'=ORDER_ID' => $id
			),
			'select' => array('ID')
		));
		while($data = $res->fetch())
		{
			static::delete($data['ID']);
		}
	}

	/**
	 * @param Internals\Entity $entity
	 *
	 * @return bool
	 */
	public static function deleteByEntity(Internals\Entity $entity)
	{
		if($entity->getId() <= 0)
			return false;

		if ($entityType = static::getEntityType($entity))
		{
			$res = static::getList(array(
				'filter' => array(
					'=ENTITY_ID' => $entity->getId(),
					'=ENTITY_TYPE' => $entityType
				),
				'select' => array('ID')
			));
			while($data = $res->fetch())
			{
				static::delete($data['ID']);
			}
		}
	}

	public static function deleteByFilter(array $values)
	{
		$res = static::getList(array(
			'filter' => $values,
			'select' => array('ID')
		));
		while($data = $res->fetch())
		{
			static::delete($data['ID']);
		}
	}

	/**
	 * @param Order $order
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public static function refreshMarkers(Order $order)
	{
		if ($order->getId() == 0)
		{
			return;
		}

		$shipmentCollection = $order->getShipmentCollection();
		if (!$shipmentCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		$paymentCollection = $order->getPaymentCollection();
		if (!$paymentCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
		}

		$basket = $order->getBasket();
		if (!$basket)
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		$markList = [];

		$filter = [
			'filter' => [
				'=ORDER_ID' => $order->getId(),
				'!=SUCCESS' => static::ENTITY_SUCCESS_CODE_DONE
			],
			'select' => ['ID', 'ENTITY_TYPE', 'ENTITY_ID', 'CODE', 'SUCCESS'],
			'order' => ['ID' => 'DESC']
		];

		$res = static::getList($filter);
		while($markerData = $res->fetch())
		{
			if (!empty($markList[$markerData['ENTITY_TYPE']])
				&& !empty($markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']])
				&& $markerData['CODE'] == $markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']]
			)
			{
				continue;
			}

			if ($markerData['SUCCESS'] != static::ENTITY_SUCCESS_CODE_DONE)
			{
				$markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']][] = $markerData['CODE'];
			}

			$poolItemSuccess = static::getPoolItemSuccess(
				$order,
				$markerData['ID'],
				$markerData['ENTITY_TYPE'],
				$markerData['ENTITY_ID'],
				$markerData['CODE']
			);

			if ($poolItemSuccess && $poolItemSuccess == static::ENTITY_SUCCESS_CODE_DONE)
			{
				foreach ($markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']] as $markerIndex => $markerCode)
				{
					if ($markerData['CODE'] == $markerCode)
					{
						unset($markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']][$markerIndex]);
					}
				}

				if (empty($markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']]))
				{
					unset($markList[$markerData['ENTITY_TYPE']][$markerData['ENTITY_ID']]);
				}
			}

			if (empty($markList[$markerData['ENTITY_TYPE']]))
			{
				unset($markList[$markerData['ENTITY_TYPE']]);
			}
		}

		if (!empty($markList))
		{
			foreach ($markList as $markEntityType => $markEntityList)
			{
				foreach ($markEntityList as $markEntityId => $markEntityCodeList)
				{
					if (empty($markEntityCodeList))
					{
						if (($entity = static::getEntity($order, $markEntityType, $markEntityId)) && ($entity instanceof \IEntityMarker))
						{
							if ($entity->canMarked())
							{
								$markedField = $entity->getMarkField();
								$entity->setField($markedField, 'N');
							}
						}
					}
				}
			}
		}

		if (empty($markList) && !static::hasErrors($order))
		{
			if ($shipmentCollection->isMarked())
			{
				/** @var Shipment $shipment */
				foreach ($shipmentCollection as $shipment)
				{
					if ($shipment->isMarked())
					{
						$shipment->setField('MARKED', 'N');
					}
				}
			}
			if ($paymentCollection->isMarked())
			{
				/** @var Payment $payment */
				foreach ($paymentCollection as $payment)
				{
					if ($payment->isMarked())
					{
						$payment->setField('MARKED', 'N');
					}
				}
			}

			$order->setField('MARKED', 'N');
		}
	}
}