<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SiteTable;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Result;

Loc::loadMessages(__FILE__);

class PersonType extends ControllerBase
{
	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this)
		;

		return [
			'PERSON_TYPE' => $view->prepareFieldInfos($view->getFields()),
		];
	}

	public function addAction(array $fields)
	{
		$r = new Result();

		$personTypeId = 0;
		$salePersonType = new \CSalePersonType();

		if (isset($fields['ID']))
		{
			unset($fields['ID']);
		}

		if (isset($fields['CODE']))
		{
			$r = $this->isCodeUniq($fields['CODE']);
		}

		if (Loader::includeModule('bitrix24'))
		{
			$selectedSite = SiteTable::getRow(['select' => ['LID'], 'filter' => ['DEF' => 'Y']]);

			if (isset($selectedSite))
			{
				$fields['LID'] = $selectedSite['LID'];
			}
		}

		if ($r->isSuccess())
		{
			$personTypeId = (int)$salePersonType->Add($fields);
			if ($personTypeId <= 0)
			{
				if ($ex = self::getApplication()->GetException())
				{
					self::getApplication()->ResetException();
					self::getApplication()->ThrowException($ex->GetString(), 200750000006);

					$r->addError(new Error($ex->GetString(), $ex->GetID()));
				}
				else
				{
					$r->addError(new Error('add person type error', 200750000001));
				}
			}
		}

		if ($r->isSuccess())
		{
			return ['PERSON_TYPE' => $this->get($personTypeId)];
		}
		else
		{
			$this->addErrors($r->getErrors());

			return null;
		}
	}

	public function updateAction($id, array $fields)
	{
		$salePersonType = new \CSalePersonType();

		$r = $this->exists($id);
		if ($r->isSuccess())
		{
			if (isset($fields['CODE']))
			{
				$r = $this->isCodeUniq($fields['CODE'], $id);
			}

			if ($r->isSuccess())
			{
				if (!$salePersonType->Update($id, $fields))
				{
					if ($ex = self::getApplication()->GetException())
					{
						self::getApplication()->ResetException();
						self::getApplication()->ThrowException($ex->GetString(), 200750000007);

						$r->addError(new Error($ex->GetString(), $ex->GetID()));
					}
					else
					{
						$r->addError(new Error('update person type error', 200750000002));
					}
				}
			}
		}

		if ($r->isSuccess())
		{
			return ['PERSON_TYPE' => $this->get($id)];
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

		if ($r->isSuccess())
		{
			return ['PERSON_TYPE' => $this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());

			return null;
		}
	}

	public function deleteAction($id)
	{
		$salePersonType = new \CSalePersonType();

		$r = $this->exists($id);
		if ($r->isSuccess())
		{
			$fields = $this->get($id);
			if ($fields['CODE'] === 'CRM_COMPANY' || $fields['CODE'] === 'CRM_CONTACT')
			{
				$r->addError(new Error('person type code is protected', 200750000003));
			}
			else
			{
				if (!$salePersonType->Delete($id))
				{
					if ($ex = self::getApplication()->GetException())
					{
						self::getApplication()->ResetException();
						self::getApplication()->ThrowException($ex->GetString(), 200750000008);

						$r->addError(new Error($ex->GetString(), $ex->GetID()));
					}
					else
					{
						$r->addError(new Error( 'delete person type error', 200750000004));
					}
				}
			}
		}

		if ($r->isSuccess())
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
		$order = empty($order) ? ['ID'=>'ASC'] : $order;

		$items = \Bitrix\Sale\PersonType::getList(
			[
				'select' => $select,
				'filter' => $filter,
				'order' => $order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit(),
			]
		);

		return new Page(
			'PERSON_TYPES',
			$items,
			function () use ($filter)
			{
				return (int)\Bitrix\Sale\PersonType::getList([
					'select' => ['CNT'],
					'filter' => $filter,
					'runtime' => [
						new ExpressionField('CNT', 'COUNT(ID)')
					]
				])->fetch()['CNT'];
			},
		);
	}
	//end region

	protected function get($id)
	{
		$r = \Bitrix\Sale\PersonType::getList(['filter'=>['ID'=>$id]])
			->fetchAll()
		;

		return $r? $r[0]:[];
	}

	protected function isCodeUniq($code, $id=null)
	{
		$r = new Result();

		if (\Bitrix\Sale\PersonType::getList(['filter'=>['CODE'=>$code, '!ID'=>$id]])->fetchAll())
			$r->addError(new Error('person type code exists', 200750000005));

		return $r;
	}

	protected function exists($id)
	{
		$r = new Result();
		if($this->get($id)['ID']<=0)
			$r->addError(new Error('person type is not exists', 200740400001));

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