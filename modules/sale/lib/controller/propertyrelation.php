<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Internals\OrderPropsRelationTable;
use Bitrix\Sale\Result;

class PropertyRelation extends Controller
{
	//region Actions
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\PropertyRelation();
		return ['PROPERTY_VARIANT'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function addAction(array $fields)
	{
		$r = new Result();
		$relationTable = new OrderPropsRelationTable();

		$res = $this->existsByFilter([
			'PROPERTY_ID'=>$fields['PROPERTY_ID'],
			'ENTITY_ID'=>$fields['ENTITY_ID'],
			'ENTITY_TYPE'=>$fields['ENTITY_TYPE']
		]);

		if($res->isSuccess() == false)
		{
			$r = $this->existsProperty($fields['PROPERTY_ID']);
			if($r->isSuccess())
			{
				$r = $relationTable->add($fields);
			}
		}
		else
		{
			$r->addError(new Error('Duplicate entry for key [propertyId, entityId, entityType]', 201650000001));
		}



		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			return [
				'PROPERTY_RELATION'=>
					OrderPropsRelationTable::getList([
						'filter'=>[
							'PROPERTY_ID'=>$fields['PROPERTY_ID'],
							'ENTITY_ID'=>$fields['ENTITY_ID'],
							'ENTITY_TYPE'=>$fields['ENTITY_TYPE']
						]
					])->fetchAll()[0]
			];
		}
	}

	public function deleteByFilterAction($fields)
	{
		$r = $this->checkFields($fields);

		if($r->isSuccess())
		{
			$r = $this->existsByFilter($fields);
			if($r->isSuccess())
			{
				$relationTable = new OrderPropsRelationTable();
				$r = $relationTable->delete($fields);
			}
		}


		if($r->isSuccess())
		{
			return true;
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['PROPERTY_ID'=>'ASC']:$order;

		$items = OrderPropsRelationTable::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return new Page('PROPERTY_RELATIONS', $items, function() use ($filter)
		{
			return count(
				OrderPropsRelationTable::getList(['filter'=>$filter])->fetchAll()
			);
		});
	}
	//endregion

	protected function existsByFilter($filter)
	{
		$r = new Result();

		$row = OrderPropsRelationTable::getList(['filter'=>['PROPERTY_ID'=>$filter['PROPERTY_ID'], 'ENTITY_ID'=>$filter['ENTITY_ID'], 'ENTITY_TYPE'=>$filter['ENTITY_TYPE']]])->fetchAll();
		if(isset($row[0]['PROPERTY_ID']) == false)
			$r->addError(new Error('property relation is not exists', 201640400004));

		return $r;
	}

	protected function checkFields($fields)
	{
		$r = new Result();

		if(isset($fields['PROPERTY_ID']) == false && $fields['PROPERTY_ID'] <> '')
			$r->addError(new Error('propertyId - parametrs is empty', 201640400001));

		if(isset($fields['ENTITY_ID'])  == false && $fields['ENTITY_ID'] <> '')
			$r->addError(new Error('propertyId - parametrs is empty', 201640400002));

		if(isset($fields['ENTITY_TYPE'])  == false && $fields['ENTITY_TYPE'] <> '')
			$r->addError(new Error('propertyId - parametrs is empty', 201640400003));

		return $r;
	}

	protected function checkPermissionEntity($name)
	{
		if($name == 'deletebyfilter')
		{
			$r = $this->checkReadPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}
		return $r;
	}

	protected function existsProperty($id)
	{
		$r = new Result();

		$property = \Bitrix\Sale\Internals\OrderPropsTable::getRow([
			'filter' => [
				'=ID' => $id
			]
		]);

		if(is_null($property))
			$r->addError(new Error('property id is not exists', 201650000002));

		return $r;
	}
}