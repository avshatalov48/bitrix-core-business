<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Rest\Entity\BusinessValuePersonDomainType;
use Bitrix\Sale\Result;

Loc::loadMessages(__FILE__);

class PersonType extends Controller
{
	//region Actions
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\PersonType();
		return ['PERSON_TYPE'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}
/*
	public function modifyAction(array $fields)
	{
		$fields = self::prepareFields($fields);

		$personTypeId = 0;
		if(isset($fields['ID']))
			$personTypeId = $fields['ID'];

		$r = (int)$personTypeId>0 ? $this->updateAction($personTypeId, $fields):$this->addAction($fields);

		if(is_array($r))
			$this->modifyBusinessValuePersonDomain(array_merge($r, ['BUSVAL_DOMAIN'=>$fields['BUSVAL_DOMAIN']['DOMAIN_TYPE']]));

		return ['PERSON_TYPE'=>$r];
	}*/

	public function addAction(array $fields)
	{
		$r = new Result();

		$personTypeId = 0;
		$salePersonType = new \CSalePersonType();

		if(isset($fields['ID']))
			unset($fields['ID']);

		if(isset($fields['CODE']))
			$r = $this->isCodeUniq($fields['CODE']);

		if($r->isSuccess())
		{
			$personTypeId = $salePersonType->Add($fields);
			if ((int)$personTypeId<=0)
			{
				if ($ex = self::getApplication()->GetException())
				{
					self::getApplication()->ResetException();
					self::getApplication()->ThrowException($ex->GetString(), 200750000006);

					$r->addError(new Error($ex->GetString(), $ex->GetID()));
				}
				else
					$r->addError(new Error('add person type error', 200750000001));
			}
		}

		if($r->isSuccess())
		{
			return ['PERSON_TYPE'=>$this->get($personTypeId)];
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
		if($r->isSuccess())
		{
			if(isset($fields['CODE']))
				$r = $this->isCodeUniq($fields['CODE'], $id);

			if($r->isSuccess())
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
						$r->addError(new Error('update person type error', 200750000002));
				}
			}
		}

		if($r->isSuccess())
		{
			return ['PERSON_TYPE'=>$this->get($id)];
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
			return ['PERSON_TYPE'=>$this->get($id)];
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
		if($r->isSuccess())
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
						$r->addError(new Error( 'delete person type error', 200750000004));
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

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$items = \Bitrix\Sale\PersonType::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		);

		return new Page('PERSON_TYPES', $items, function() use ($filter)
		{
			return count(
				\Bitrix\Sale\PersonType::getList(['filter'=>$filter])->fetchAll()
			);
		});
	}
	//end region

	protected function get($id)
	{
		$r = \Bitrix\Sale\PersonType::getList(['filter'=>['ID'=>$id]])->fetchAll();
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

	protected function modifyBusinessValuePersonDomain(array $fields)
	{
		\Bitrix\Sale\Internals\BusinessValuePersonDomainTable::delete([
			'PERSON_TYPE_ID' => $fields['ID']
		]);

		if ($fields['BUSVAL_DOMAIN'] !== '' && in_array($fields['BUSVAL_DOMAIN'],
				array_keys(BusinessValuePersonDomainType::getAllDescriptions())
			))
		{
			\Bitrix\Sale\Internals\BusinessValuePersonDomainTable::add([
				'PERSON_TYPE_ID' => $fields['ID'],
				'DOMAIN' => $fields['BUSVAL_DOMAIN'],
			]);
		}
	}

	static public function prepareFields(array $fields)
	{
		$personType = isset($fields['PERSON_TYPE'])? $fields['PERSON_TYPE']:[];
		$domain = isset($fields['BUSVAL_DOMAIN'])? $fields['BUSVAL_DOMAIN']:[];
		return array_merge($personType, ['BUSVAL_DOMAIN'=>$domain]);
	}
}