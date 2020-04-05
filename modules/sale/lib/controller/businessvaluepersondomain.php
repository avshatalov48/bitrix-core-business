<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Internals\BusinessValuePersonDomainTable;
use Bitrix\Sale\Result;

class BusinessValuePersonDomain extends Controller
{
	//region Actions
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\BusinessValuePersonDomain();
		return ['BUSINESS_VALUE_PERSON_DOMAIN'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['PERSON_TYPE_ID'=>'ASC']:$order;

		$items = BusinessValuePersonDomainTable::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return new Page('BUSINESS_VALUE_PERSON_DOMAINS', $items, function() use ($filter)
		{
			return count(
				BusinessValuePersonDomainTable::getList(['filter'=>$filter])->fetchAll()
			);
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
			return ['BUSINESS_VALUE_PERSON_DOMAIN'=>$this->get($personTypeId)];
		}
		else
		{
			$this->addErrors($result->getErrors());
			return null;
		}
	}

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

	public function deleteAction($personTypeId)
	{
		$r = $this->exists($personTypeId);
		if($r->isSuccess())
		{
			\Bitrix\Sale\Internals\BusinessValuePersonDomainTable::delete([
				'PERSON_TYPE_ID' => $personTypeId
			]);
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
}