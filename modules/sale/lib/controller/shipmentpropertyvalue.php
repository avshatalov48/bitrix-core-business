<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Sale;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Result;
use Bitrix\Main\Entity\ReferenceField;

class ShipmentPropertyValue extends ControllerBase
{
	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()->getView($this);

		return [
			'PROPERTY_VALUE' => $view->prepareFieldInfos($view->getFields()),
		];
	}

	public function modifyAction(array $fields)
	{
		$shipmentId = isset($fields['SHIPMENT']['ID']) ? (int)$fields['SHIPMENT']['ID'] : 0;
		if (!$shipmentId)
		{
			$this->addError(new Error('Shipment identifier is not specified', 201040400006));

			return null;
		}

		$shipment = Sale\Repository\ShipmentRepository::getInstance()->getById($shipmentId);
		if (!$shipment)
		{
			$this->addError(new Error('Shipment has not been found', 201040400007));

			return null;
		}

		$propertyValuesList =
			(
				isset($fields['SHIPMENT']['PROPERTY_VALUES'])
				&& is_array($fields['SHIPMENT']['PROPERTY_VALUES'])
			)
				? $fields['SHIPMENT']['PROPERTY_VALUES']
				: []
		;
		$propertyCollection = $shipment->getPropertyCollection();

		foreach ($propertyValuesList as $propertyValueItem)
		{
			$propertyValue = $propertyCollection->getItemByOrderPropertyId($propertyValueItem['SHIPMENT_PROPS_ID']);
			if (!$propertyValue)
			{
				continue;
			}

			$propertyValue->setValue($propertyValueItem['VALUE']);
		}

		$orderSaveResult = $shipment->getOrder()->save();
		if (!$orderSaveResult->isSuccess())
		{
			$this->addError(new Error('Order save error', 201040400008));

			return null;
		}

		return [
			'PROPERTY_VALUES' => $shipment->toArray()['PROPERTIES'],
		];
	}

	public function deleteAction(int $id): ?bool
	{
		$propertyValue = $this->getShipmentPropertyValueById($id);
		if (!$propertyValue)
		{
			return null;
		}

		$propertyValueDeleteResult = $propertyValue->delete();
		if (!$propertyValueDeleteResult->isSuccess())
		{
			$this->addError(new Error('Property value delete error', 201040400004));

			return null;
		}

		$orderSaveResult = $propertyValue->getCollection()->getOrder()->save();
		if (!$orderSaveResult)
		{
			$this->addError(new Error('Order save error', 201040400005));

			return null;
		}

		return true;
	}

	public function getAction(int $id): ?array
	{
		$propertyValue = $this->getShipmentPropertyValueById($id);
		if (!$propertyValue)
		{
			return null;
		}

		return [
			'PROPERTY_VALUE' => current(
				array_filter(
					$propertyValue->getCollection()->toArray(),
					static function ($item) use ($propertyValue)
					{
						return (int)$propertyValue->getPropertyId() === (int)$item['ORDER_PROPS_ID'];
					}
				)
			),
		];
	}

	public function listAction(
		PageNavigation $pageNavigation,
		array $select = [],
		array $filter = [],
		array $order = []
	): Page
	{
		$select = empty($select) ? ['*'] : $select;
		$order = empty($order) ? ['ID'=>'ASC'] : $order;
		$runtime = [
			new ReferenceField(
				'ORDER_PROPS',
				'\Bitrix\Sale\Internals\OrderPropsTable',
				[
					'=this.ORDER_PROPS_ID' => 'ref.ID',
					'=ref.ENTITY_TYPE' => new SqlExpression('?s', Sale\Registry::ENTITY_SHIPMENT),
				],
				[
					'join_type' => 'INNER',
				]
			)
		];

		$propertyValues = Sale\ShipmentPropertyValue::getList(
			[
				'select' => $select,
				'filter' => $filter,
				'order' => $order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit(),
				'runtime' => $runtime,
			]
		)->fetchAll();

		return new Page('PROPERTY_VALUES', $propertyValues, static function() use ($select, $filter, $runtime)
		{
			return (int) Sale\ShipmentPropertyValue::getList([
				'select' => ['CNT'],
				'filter' => $filter,
				'runtime' => [new ExpressionField('CNT', 'COUNT(ID)')]
			])->fetch()['CNT'];
		});
	}
	//end region

	protected function checkModifyPermissionEntity()
	{
		$r = new Result();

		$saleModulePermissions = self::getApplication()->GetGroupRight('sale');
		if ($saleModulePermissions  < 'W')
		{
			$r->addError(new Error('Access Denied', 200040300020));
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		$saleModulePermissions = self::getApplication()->GetGroupRight('sale');
		if ($saleModulePermissions  === 'D')
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}

		return $r;
	}

	protected function checkPermissionEntity($name, $arguments = [])
	{
		$name = mb_strtolower($name);

		if ($name === 'modify')
		{
			return $this->checkModifyPermissionEntity();
		}

		return parent::checkPermissionEntity($name);
	}

	private function getShipmentPropertyValueById(int $id): ?Sale\ShipmentPropertyValue
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\ShipmentProperty $propertyValueClass */
		$propertyValueClass = $registry->getShipmentPropertyValueClassName();

		$shipmentPropertiesList = $propertyValueClass::getList([
			'select' => [
				'ORDER_ID',
				'ENTITY_ID',
			],
			'filter' => [
				'ID' => $id,
				'ENTITY_TYPE' => Sale\Registry::ENTITY_SHIPMENT,
			],
		]);

		if (!$shipmentPropertyRow = $shipmentPropertiesList->fetch())
		{
			$this->addError(new Error('Property value has not been found', 201040400001));

			return null;
		}

		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		/** @var Sale\Shipment $shipment */
		$shipment = $orderClass::load($shipmentPropertyRow['ORDER_ID'])
			->getShipmentCollection()
			->getItemById($shipmentPropertyRow['ENTITY_ID'])
		;

		if (!$shipment)
		{
			$this->addError(new Error('Shipment has not been found', 20104040002));

			return null;
		}

		/** @var Sale\ShipmentPropertyValue|null $propertyValue */
		$propertyValue = $shipment->getPropertyCollection()->getItemById($id);
		if (!$propertyValue)
		{
			$this->addError(new Error('Property value has not been found', 201040400003));

			return null;
		}

		return $propertyValue;
	}
}
