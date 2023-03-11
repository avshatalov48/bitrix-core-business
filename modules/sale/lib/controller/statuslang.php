<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Internals\StatusLangTable;
use Bitrix\Sale\Result;

class StatusLang extends ControllerBase
{
	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['STATUS_LANG'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	protected function getEntityTable(): StatusLangTable
	{
		return new StatusLangTable();
	}

	public function addAction(array $fields)
	{
		$r = new Result();

		$res = $this->existsByFilter([
			'STATUS_ID'=>$fields['STATUS_ID'],
			'LID'=>$fields['LID']
		]);

		if($res->isSuccess() == false)
		{
			$r = $this->validate($fields);
			if($r->isSuccess())
			{
				$r = $this->getEntityTable()::add($fields);
			}
		}
		else
		{
			$r->addError(new Error('Duplicate entry for key [statusId, lid]', 201750000003));
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			return [
				'STATUS_LANG'=>
					$this->getEntityTable()::getList([
						'filter'=>[
							'STATUS_ID'=>$fields['STATUS_ID'],
							'LID'=>$fields['LID']
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
			$r = $this->validate($fields);
			if($r->isSuccess())
			{
				$r = $this->existsByFilter([
					'STATUS_ID'=>$fields['STATUS_ID'],
					'LID'=>$fields['LID']
				]);
				if($r->isSuccess())
				{
					$r = $this->getEntityTable()::delete(['STATUS_ID'=>$fields['STATUS_ID'], 'LID'=>$fields['LID']]);
				}
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

	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		$select = empty($select) ? ['*'] : $select;
		$order = empty($order) ? ['STATUS_ID' => 'ASC'] : $order;

		$items = StatusLangTable::getList(
			[
				'select' => $select,
				'filter' => $filter,
				'order' => $order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit(),
			]
		)->fetchAll();

		return new Page('STATUS_LANGS', $items, function() use ($filter)
		{
			return $this->getEntityTable()::getCount([$filter]);
		});
	}

	public function getListLangsAction()
	{
		$items = LanguageTable::getList(
			[
				'select'=>['ACTIVE','NAME','LID'],
				'filter'=>[],
				'order'=>['LID'=>'ASC']
			]
		)->fetchAll();

		return new Page('LANGS', $items, function()
		{
			return count(
				LanguageTable::getList()->fetchAll()
			);
		});
	}
	//endregion

	protected function validate(array $fields)
	{
		$r = new Result();
		if(is_set($this->getListLangs(), $fields['LID']) == false)
		{
			$r->addError(new Error('lid out of range',201750000004));
		}

		return $r;
	}

	protected function getListLangs()
	{
		$r=[];
		$result = LanguageTable::getList([
			'select' => ['LID', 'NAME'],
			'filter' => ['=ACTIVE'=>'Y']
		]);
		while ($row = $result->fetch())
			$r[$row['LID']] = $row['NAME'];
		return $r;
	}

	protected function existsByFilter($filter)
	{
		$r = new Result();

		$row = $this->getEntityTable()::getList(['filter'=>['STATUS_ID'=>$filter['STATUS_ID'], 'LID'=>$filter['LID']]])->fetchAll();
		if(isset($row[0]['STATUS_ID']) == false)
			$r->addError(new Error('status lang is not exists', 201740400001));

		return $r;
	}

	protected function checkFields($fields)
	{
		$r = new Result();

		if(isset($fields['STATUS_ID']) == false && $fields['STATUS_ID'] <> '')
			$r->addError(new Error('statusId - parametrs is empty', 201750000001));

		if(isset($fields['LID'])  == false && $fields['LID'] <> '')
			$r->addError(new Error('lid - parametrs is empty', 201750000002));

		return $r;
	}

	protected function checkPermissionEntity($name, $arguments = [])
	{
		if($name == 'deletebyfilter' ||
			$name == 'getlistlangs')
		{
			$r = $this->checkReadPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}
		return $r;
	}

	protected function checkModifyPermissionEntity(): Result
	{
		$r = new Result();

		$saleModulePermissions = self::getApplication()->GetGroupRight("sale");
		if ($saleModulePermissions  < "W")
		{
			$r->addError(new Error('Access Denied', 200040300020));
		}
		return $r;
	}

	protected function checkReadPermissionEntity(): Result
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