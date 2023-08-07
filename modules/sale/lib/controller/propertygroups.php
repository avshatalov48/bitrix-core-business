<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Result;

class PropertyGroups extends Controller
{
	//region Actions
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\PropertyGroups();
		return ['PROPERTY_GROUP'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function addAction(array $fields)
	{
		global $APPLICATION;

		$r = new Result();

		$propertyGroupId = 0;
		$orderPropsGroup = new \CSaleOrderPropsGroup();

		if((int)$fields['PERSON_TYPE_ID']<=0)
			$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_PERSON_TYPE_ID_FIELD_EMPTY'), 'PERSON_TYPE_ID_FIELD_EMPTY'));
		if(trim($fields['NAME'])=='')
			$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_PERSON_TYPE_ID_FIELD_EMPTY'), 'NAME_FIELD_EMPTY'));

		if($r->isSuccess())
		{
			$propertyGroupId = $orderPropsGroup->Add($fields);
			if ((int)$propertyGroupId <= 0)
			{
				if ($ex = $APPLICATION->GetException())
					$r->addError(new Error($ex->GetString(), $ex->GetID()));
				else
					$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_ADD_PROPS_GROUP'), 'ERROR_ADD_PROPS_GROUP'));
			}
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
			return ['PROPERTY_GROUP'=>$this->get($propertyGroupId)];


	}

	public function updateAction($id, array $fields)
	{
		global $APPLICATION;

		$orderPropsGroup = new \CSaleOrderPropsGroup();

		$r = $this->exists($id);
		if($r->isSuccess())
		{
			if(isset($fields['PERSON_TYPE_ID']))
				unset($fields['PERSON_TYPE_ID']);

			if(!$orderPropsGroup->Update($id, $fields))
			{
				if ($ex = $APPLICATION->GetException())
					$r->addError(new Error($ex->GetString(), $ex->GetId()));
				else
					$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_UPDATE_PROPS_GROUP', ['#ID#'=>$id]), 'ERROR_UPDATE_PROPS_GROUP'));
			}
		}

		if($r->isSuccess())
		{
			return ['PROPERTY_GROUP'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function deleteAction($id)
	{
		global $APPLICATION;

		$orderPropsGroup = new \CSaleOrderPropsGroup();

		$r = $this->exists($id);
		if($r->isSuccess())
		{
			if (!$orderPropsGroup->Delete($id))
			{
				if ($ex = $APPLICATION->GetException())
					$r->addError(new Error($ex->GetString(), $ex->GetId()));
				else
					$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_DELETE_PROPS_GROUP', ['#ID#'=>$id]),'ERROR_DELETE_PROPS_GROUP'));
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

	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['PROPERTY_GROUP'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function listAction($select=[], $filter, $order=[], $start=0)
	{
		$result = [];

		$orderPropsGroup = new \CSaleOrderPropsGroup();

		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$r = $orderPropsGroup->GetList($order, $filter, false, self::getNavData($start), $select);
		while ($l = $r->fetch())
			$result[] = $l;

		return new Page('PROPERTY_GROUPS', $result, function() use ($filter)
		{
			$orderPropsGroup = new \CSaleOrderPropsGroup();

			$list = [];
			$r = $orderPropsGroup->GetList([], $filter);
			while ($l = $r->fetch())
				$list[] = $l;

			return count($list);
		});
	}
	//end region

	protected function get($propertyGroupId)
	{
		$orderPropsGroup = new \CSaleOrderPropsGroup();

		return $orderPropsGroup->GetById($propertyGroupId);
	}

	protected function exists($id)
	{
		$r = new Result();
		if($this->get($id)['ID']<=0)
			$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_PROPS_GROUP_NOT_EXISTS', ['#ID#'=>$id]), 'PROPS_GROUP_NOT_EXISTS'));

		return $r;
	}
}
