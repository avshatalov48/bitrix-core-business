<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

Loc::loadMessages(__FILE__);

class Profile extends ControllerBase
{
	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['PROFILE'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = [])
	{
		$result = [];

		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$r = \CSaleOrderUserProps::GetList($order, $filter, false, self::getNavData($pageNavigation->getOffset()), $select);
		while ($l = $r->fetch())
			$result[] = $l;

		return new Page('PROFILES', $result, function() use ($filter)
		{
			return (int) \CSaleOrderUserProps::GetList([], $filter, []);
		});
	}

	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['PROFILE'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}
	//endregion

	protected function exists($id)
	{
		$r = new Result();
		if(isset($this->get($id)['ID']) == false)
			$r->addError(new Error('Profile is not exists'));

		return $r;
	}

	protected function get($id)
	{
		return \CSaleOrderUserProps::GetByID($id);
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (self::getApplication()->GetGroupRight("sale") == "D")
		{
			$r->addError(new Error('Buyer access denied'));
		}

		return $r;
	}
}