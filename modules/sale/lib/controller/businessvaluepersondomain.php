<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Internals\BusinessValuePersonDomainTable;
use Bitrix\Sale\Result;

class BusinessValuePersonDomain extends ControllerBase
{
	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['BUSINESS_VALUE_PERSON_DOMAIN'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		$select = empty($select) ? ['*'] : $select;
		$order = empty($order) ? ['PERSON_TYPE_ID'=>'ASC'] : $order;

		$items = BusinessValuePersonDomainTable::getList(
			[
				'select' => $select,
				'filter' => $filter,
				'order' => $order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit(),
			]
		)->fetchAll();

		return new Page('BUSINESS_VALUE_PERSON_DOMAINS', $items, function() use ($filter)
		{
			return BusinessValuePersonDomainTable::getCount([$filter]);
		});
	}

	public function addAction(array $fields)
	{
		$result = new Result();

		$personTypeId = $fields['PERSON_TYPE_ID'];
		$domain = $fields['DOMAIN'];

		$r = $this->personTypeExists($personTypeId);

		if($r->isSuccess())
		{
			$r = $this->exists($personTypeId);
			if($r->isSuccess())
			{
				$result->addError(new Error('Duplicate entry for key [personTypeId]', 201450000001));
			}
			else
			{
				BusinessValuePersonDomainTable::add(array(
					'PERSON_TYPE_ID' => $personTypeId,
					'DOMAIN' => $domain,
				));
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		if($result->isSuccess())
		{
			return [
				'BUSINESS_VALUE_PERSON_DOMAIN'=>BusinessValuePersonDomainTable::getList(['filter'=>[
					'PERSON_TYPE_ID'=>$personTypeId,
					'DOMAIN'=>$domain
				]])->fetchAll()[0]
			];
		}
		else
		{
			$this->addErrors($result->getErrors());
			return null;
		}
	}

	/** @deprecated  */
	public function getAction($personTypeId)
	{
		$r = $this->exists($personTypeId);
		if($r->isSuccess())
		{
			return ['BUSINESS_VALUE_PERSON_DOMAIN'=>$this->get($personTypeId)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	/** @deprecated  */
	public function deleteAction($personTypeId)
	{
		$r = $this->exists($personTypeId);
		if($r->isSuccess())
		{
			\Bitrix\Sale\Internals\BusinessValuePersonDomainTable::deleteByPersonTypeId((int)$personTypeId);
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

	public function deleteByFilterAction($fields)
	{
		$r = $this->checkFields($fields);

		if($r->isSuccess())
		{
			$r = $this->existsByFilter([
				'PERSON_TYPE_ID'=>$fields['PERSON_TYPE_ID'],
				'DOMAIN'=>$fields['DOMAIN']
			]);
			if($r->isSuccess())
			{
				$r = BusinessValuePersonDomainTable::delete(['PERSON_TYPE_ID'=>$fields['PERSON_TYPE_ID'], 'DOMAIN'=>$fields['DOMAIN']]);
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

	protected function getPersonType($id)
	{
		$r = \Bitrix\Sale\PersonType::getList(['filter'=>['ID'=>$id]])->fetchAll();
		return $r? $r[0]:[];
	}

	protected function personTypeExists($id)
	{
		$r = new Result();
		if($this->getPersonType($id)['ID']<=0)
			$r->addError(new Error('person type is not exists', 201440400002));

		return $r;
	}

	protected function get($personTypeId)
	{
		$r = BusinessValuePersonDomainTable::getList(['filter'=>['PERSON_TYPE_ID'=>$personTypeId]])->fetchAll();
		return $r? $r[0]:[];
	}

	protected function exists($personTypeId)
	{
		$r = new Result();
		if($this->get($personTypeId)['PERSON_TYPE_ID']<=0)
			$r->addError(new Error('business value person domain is not exists', 201440400001));

		return $r;
	}

	protected function existsByFilter($filter)
	{
		$r = new Result();

		$row = BusinessValuePersonDomainTable::getList(['filter'=>['PERSON_TYPE_ID'=>$filter['PERSON_TYPE_ID'], 'DOMAIN'=>$filter['DOMAIN']]])->fetchAll();
		if(isset($row[0]['PERSON_TYPE_ID']) == false)
			$r->addError(new Error('business value person domain is not exists', 201440400003));

		return $r;
	}

	protected function checkFields($fields)
	{
		$r = new Result();

		if(isset($fields['PERSON_TYPE_ID']) == false && $fields['PERSON_TYPE_ID'] <> '')
			$r->addError(new Error('personTypeId - parametrs is empty', 201450000002));

		if(isset($fields['DOMAIN'])  == false && $fields['DOMAIN'] <> '')
			$r->addError(new Error('domian - parametrs is empty', 201450000003));

		return $r;
	}

	protected function checkPermissionEntity($name, $arguments = [])
	{
		if($name == 'deletebyfilter')
		{
			$r = $this->checkModifyPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}
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