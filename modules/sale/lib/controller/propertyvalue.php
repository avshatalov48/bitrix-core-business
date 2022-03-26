<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Sale;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Result;

class PropertyValue extends ControllerBase
{

	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			Sale\PropertyValue::class,
			'propertyValue',
			function($className, $id) {
				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var Sale\Property $propertyValueClass */
				$propertyValueClass = $registry->getPropertyValueClassName();

				$r = $propertyValueClass::getList([
					'select'=>['ORDER_ID'],
					'filter'=>['ID'=>$id]
				]);

				if($row = $r->fetch())
				{
					/** @var Sale\Order $orderClass */
					$orderClass = $registry->getOrderClassName();

					$propertyValue = $orderClass::load($row['ORDER_ID'])
						->getPropertyCollection()
						->getItemById($id);
					if ($propertyValue)
					{
						return $propertyValue;
					}
				}
				else
				{
					$this->addError(new Error('property value is not exists', 201040400001));
				}
				return null;
			}
		);
	}

	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['PROPERTY_VALUE'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	public function modifyAction(array $fields)
	{
		$builder = $this->getBuilder();
		$builder->buildEntityProperties($fields);

		if($builder->getErrorsContainer()->getErrorCollection()->count()>0)
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());
			return null;
		}

		$order = $builder->getOrder();

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		return ['PROPERTY_VALUES'=>$this->toArray($order)['ORDER']['PROPERTY_VALUES']];
	}

	public function deleteAction(Sale\PropertyValue $propertyValue)
	{
		$r = $propertyValue->delete();
		if($r->isSuccess())
		{
			$r = $propertyValue
				->getCollection()
				->getOrder()
				->save();
		}

		if(!$r->isSuccess())
			$this->addErrors($r->getErrors());

		return $r->isSuccess();
	}

	public function getAction(Sale\PropertyValue $propertyValue)
	{
		return ['PROPERTY_VALUE'=>$this->getItem($propertyValue)];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;
		$runtime = [
			new \Bitrix\Main\Entity\ReferenceField(
				'ORDER_PROPS',
				'\Bitrix\Sale\Internals\OrderPropsTable',
				array('=this.ORDER_PROPS_ID' => 'ref.ID')
			)
		];

		$payments = \Bitrix\Sale\PropertyValue::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit(),
				'runtime' => $runtime
			]
		)->fetchAll();

		return new Page('PROPERTY_VALUES', $payments, function() use ($select, $filter, $runtime)
		{
			return (int) \Bitrix\Sale\PropertyValue::getList([
				'select' => ['CNT'],
				'filter'=>$filter,
				'runtime' => [new ExpressionField('CNT', 'COUNT(ID)')]
			])->fetch()['CNT'];
		});
	}
	//end region

	protected function getItem(Sale\PropertyValue $propertyValue, array $fields=[])
	{
		return current(
			array_filter(
				$propertyValue
					->getCollection()
					->toArray(),
				function ($item) use ($propertyValue){
					if($propertyValue->getPropertyId() == $item['ORDER_PROPS_ID'])
					{
						return $item;
					}
				}
			)
		);
	}

	static public function prepareFields($fields)
	{
		$data = null;

		if(isset($fields['PROPERTY_VALUES']))
		{
			foreach($fields['PROPERTY_VALUES'] as $field)
			{
				$data[$field['ORDER_PROPS_ID']]=$field['VALUE'];
			}
		}
		return is_array($data)?['PROPERTIES'=>$data]:[];
	}

	protected function checkModifyPermissionEntity()
	{
		$r = new Result();

		$saleModulePermissions = self::getApplication()->GetGroupRight("sale");
		if ($saleModulePermissions  < "W")
		{
			$r->addError(new Error('Access Denied', 200040300020));
		}
		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		$saleModulePermissions = self::getApplication()->GetGroupRight("sale");
		if ($saleModulePermissions  == "D")
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}

	protected function checkPermissionEntity($name, $arguments=[])
	{
		$name = mb_strtolower($name);

		if($name == 'modify')
		{
			$r = $this->checkModifyPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}
		return $r;
	}
}