<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;

class PropertyValue extends Controller
{

	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			\Bitrix\Sale\PropertyValue::class,
			'propertyValue',
			function($className, $id) {

				$r = \Bitrix\Sale\PropertyValue::getList([
					'select'=>['ORDER_ID'],
					'filter'=>['ID'=>$id]
				]);

				if($row = $r->fetch())
				{
					$propertyValue = \Bitrix\Sale\Order::load($row['ORDER_ID'])
						->getPropertyCollection()
						->getItemById($id);
					if($propertyValue instanceof \Bitrix\Sale\PropertyValue)
						return $propertyValue;
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
		$entity = new \Bitrix\Sale\Rest\Entity\PropertyValue();
		return ['PROPERTY_VALUE'=>$entity->prepareFieldInfos(
			$entity->getFields()
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

	public function deleteAction(\Bitrix\Sale\PropertyValue $propertyValue)
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

	public function getAction(\Bitrix\Sale\PropertyValue $propertyValue)
	{
		return ['PROPERTY_VALUE'=>$this->get($propertyValue)];
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
			return count(
				\Bitrix\Sale\PropertyValue::getList(['select'=>$select, 'filter'=>$filter, 'runtime'=>$runtime])->fetchAll()
			);
		});
	}
	//end region

	protected function get(\Bitrix\Sale\PropertyValue $propertyValue, array $fields=[])
	{
		$properties = $this->toArray($propertyValue
			->getCollection()
			->getOrder(),
			$fields)['ORDER']['PROPERTY_VALUES'];
		foreach ($properties as $property)
		{
			if($property['ID']==$propertyValue->getId())
			{
				return $property;
			}
		}
		return [];
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
}