<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

final class Catalog extends Controller
{
	//region Actions
	public function getFieldsAction(): array
	{
		return ['CATALOG' => $this->getViewFields()];
	}

	public function isOffersAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return $this->isOffers($id);
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

		$catalog = new \CCatalog();

		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$r = $catalog::GetList($order, $filter, false, self::getNavData($start), $select);
		while ($l = $r->fetch())
		{
			$result[] = $l;
		}

		return new Page('CATALOGS', $result, function() use ($filter)
		{
			return (int)\CCatalog::GetList([], $filter, []);
		});
	}

	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['CATALOG'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function addAction($fields)
	{
		$r = new Result();

		$res = $this->exists($fields['IBLOCK_ID']);
		if($res->isSuccess() == false)
		{
			$r = $this->addValidate($fields);
			if($r->isSuccess())
			{
				\CCatalog::add($fields);
			}
		}
		else
		{
			$r->addError(new Error('Duplicate entry for key [iblockId]'));
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			return ['CATALOG'=>$this->get($fields['IBLOCK_ID'])];
		}
	}

	public function updateAction($id, array $fields)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			$r = $this->updateValidate($fields+['ID'=>$id]);
			if($r->isSuccess())
			{
				\CCatalog::update($id, $fields);
			}
		}

		if($r->isSuccess())
		{
			return ['CATALOG'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function deleteAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			$r = $this->deleteValidate($id);
			if($r->isSuccess())
			{
				if (!\CCatalog::Delete($id))
				{
					if ($ex = self::getApplication()->GetException())
						$r->addError(new Error($ex->GetString(), $ex->GetId()));
					else
						$r->addError(new Error('delete catalog error'));
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
	//endregion

	protected function isOffers($id)
	{
		return $this->get($id)["PRODUCT_IBLOCK_ID"] ? true:false;
	}

	protected function getEntityTable()
	{
		return new CatalogIblockTable();
	}

	protected function exists($id)
	{
		$r = new Result();

		if(isset($this->get($id)['ID']) == false)
			$r->addError(new Error('Catalog is not exists'));

		return $r;
	}

	protected function get($id)
	{
		return \CCatalog::GetByID($id);
	}

	protected function addValidate(array $fields)
	{
		$r = new Result();

		if(!\CCatalog::CheckFields("ADD", $fields, $fields['ID']))
		{
			if ($ex = self::getApplication()->GetException())
				$r->addError(new Error($ex->GetString(), $ex->GetId()));
			else
				$r->addError(new Error('Validate catalog error'));
		}

		return $r;
	}

	protected function updateValidate(array $fields)
	{
		$r = new Result();

		if(!\CCatalog::CheckFields("UPDATE", $fields, $fields['ID']))
		{
			if ($ex = self::getApplication()->GetException())
				$r->addError(new Error($ex->GetString(), $ex->GetId()));
			else
				$r->addError(new Error('Validate catalog error'));
		}

		return $r;
	}

	protected function deleteValidate($id)
	{
		$r = new Result();

		if($this->isOffers($id))
			$r->addError(new Error('Catalog is offers'));

		return $r;
	}

	protected function checkPermissionEntity($name, $arguments=[])
	{
		$name = mb_strtolower($name); //for ajax mode

		if($name == 'isoffers')
		{
			$r = $this->checkReadPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}

		return $r;
	}

	protected function checkModifyPermissionEntity()
	{
		$r = $this->checkReadPermissionEntity();
		if($r->isSuccess())
		{
			if (!$this->accessController->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS))
			{
				$r->addError(new Error('Access Denied', 200040300020));
			}
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		$user = CurrentUser::get();
		if(!$user->canDoOperation('view_other_settings') && !$user->canDoOperation('edit_other_settings'))
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}

		if (
			!$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			&& !$this->accessController->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS)
		)
		{
			$r->addError(new Error('Access Denied', 200040300030));
		}

		return $r;
	}
}
