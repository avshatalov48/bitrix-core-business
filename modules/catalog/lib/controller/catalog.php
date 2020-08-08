<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\Base;

final class Catalog extends Controller
{
	//region Actions
	public function getFieldsAction()
	{
		/** @var Base $view */
		$view = $this->getViewManager()
			->getView($this);

		return ['CATALOG'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
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
			$catalog = new \CCatalog();

			$list = [];
			$r = $catalog::GetList([], $filter);
			while ($l = $r->fetch())
				$list[] = $l;

			return count($list);
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
			if (!static::getGlobalUser()->CanDoOperation('catalog_settings'))
			{
				$r->addError(new Error('Access Denied', 200040300020));
			}
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if(!static::getGlobalUser()->CanDoOperation('view_other_settings') && !static::getGlobalUser()->CanDoOperation('edit_other_settings'))
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}

		if (!static::getGlobalUser()->CanDoOperation('catalog_read') && !static::getGlobalUser()->CanDoOperation('catalog_settings'))
		{
			$r->addError(new Error('Access Denied', 200040300030));
		}

		return $r;
	}
}