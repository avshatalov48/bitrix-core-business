<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Result;

class PropertyGroup extends ControllerBase
{
	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['PROPERTY_GROUP'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	public function addAction(array $fields)
	{
		$r = new Result();

		$propertyGroupId = 0;
		$orderPropsGroup = new \CSaleOrderPropsGroup();

		if((int)$fields['PERSON_TYPE_ID']<=0)
			$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_PERSON_TYPE_ID_FIELD_EMPTY'), 200950000001));
		if(trim($fields['NAME'])=='')
			$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_PERSON_TYPE_ID_FIELD_EMPTY'), 200950000002));

		if($r->isSuccess())
		{
			$propertyGroupId = $orderPropsGroup->Add($fields);
			if ((int)$propertyGroupId <= 0)
			{
				if ($ex = self::getApplication()->GetException())
				{
					$r->addError(new Error($ex->GetString(), $ex->GetID()));
				}
				else
				{
					$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_ADD_PROPS_GROUP'), 200950000003));
				}
			}
		}

		if(!$r->isSuccess())
		{
			foreach ($r->getErrors() as $error)
			{
				$this->addError(new Error($error->getMessage(), 200950000006));
			}
			return null;
		}
		else
			return ['PROPERTY_GROUP'=>$this->get($propertyGroupId)];
	}

	public function updateAction($id, array $fields)
	{
		$orderPropsGroup = new \CSaleOrderPropsGroup();

		$r = $this->exists($id);
		if($r->isSuccess())
		{
			if(isset($fields['PERSON_TYPE_ID']))
				unset($fields['PERSON_TYPE_ID']);

			if(!$orderPropsGroup->Update($id, $fields))
			{
				if ($ex = self::getApplication()->GetException())
				{
					self::getApplication()->ResetException();
					self::getApplication()->ThrowException($ex->GetString(), 200950000008);

					$r->addError(new Error($ex->GetString(), $ex->GetID()));
				}
				else
					$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_UPDATE_PROPS_GROUP', ['#ID#'=>$id]), 200950000004));
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
		$orderPropsGroup = new \CSaleOrderPropsGroup();

		$r = $this->exists($id);
		if($r->isSuccess())
		{
			if (!$orderPropsGroup->Delete($id))
			{
				if ($ex = self::getApplication()->GetException())
				{
					$r->addError(new Error($ex->GetString(), $ex->GetID()));
				}
				else
					$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_DELETE_PROPS_GROUP', ['#ID#'=>$id]),200950000005));
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

	public function listAction($select=[], $filter=[], $order=[], $start=0)
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
			return (int) \CSaleOrderPropsGroup::GetList([], $filter, []);
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
			$r->addError(new Error('property group is not exists', 200940400001));

		return $r;
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
}
