<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\TaskTable;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Internals\StatusGroupTaskTable;
use Bitrix\Sale\Internals\StatusLangTable;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale\Result;

class Status extends Controller
{
	//region Actions
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\Status();
		return ['STATUS'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function addAction($fields)
	{
		$r = new Result();

		$res = $this->exists($fields['ID']);
		if($res->isSuccess() == false)
		{
			$r = $this->validate($fields);
			if($r->isSuccess())
			{
				$fields = $this->prepareFields($fields);
				$r  = StatusTable::add($fields);
			}
		}
		else
		{
			$r->addError(new Error('Duplicate entry for key [id]',201350000001));
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			return ['STATUS'=>$this->get($fields['ID'])];
		}
	}

	public function updateAction($id, array $fields)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			$fields['ID'] = $id;

			$r = $this->validate($fields);
			if($r->isSuccess())
			{
				$r  = StatusTable::update($id, $fields);
			}
		}

		if($r->isSuccess())
		{
			return ['STATUS'=>$this->get($id)];
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
			if (in_array($id, [
				\Bitrix\Sale\OrderStatus::getInitialStatus(),
				\Bitrix\Sale\OrderStatus::getFinalStatus(),
				\Bitrix\Sale\DeliveryStatus::getInitialStatus(),
				\Bitrix\Sale\DeliveryStatus::getFinalStatus()]))
			{
				$r->addError(new Error('delete status type loced',201350000002));
			}

			if($r->isSuccess())
			{
				$r  = StatusTable::delete($id);
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
			return ['STATUS'=>$this->get($id)];
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

		$items = \Bitrix\Sale\Internals\StatusTable::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return new Page('STATUSES', $items, function() use ($filter)
		{
			return count(
				\Bitrix\Sale\Internals\StatusTable::getList(['filter'=>$filter])->fetchAll()
			);
		});
	}
	//endregion

	protected function prepareFields($fields)
	{
		if(!isset($fields['XML_ID']) && $fields['XML_ID'] == '')
		{
			$fields['XML_ID'] = \Bitrix\Sale\Internals\StatusTable::generateXmlId();
		}

		$fields['COLOR'] = isset($fields['COLOR']) ? $fields['COLOR']:'';
		$fields['NOTIFY'] = isset($fields['NOTIFY']) && $fields['NOTIFY']=='Y' ? 'Y':'N';
		$fields['SORT'] = isset($fields['SORT']) ? $fields['SORT']:0;

		return $fields;
	}

	protected function validate(array $fields)
	{
		$r = new Result();

		if(!in_array($fields['TYPE'], [
			\Bitrix\Sale\OrderStatus::TYPE,
			\Bitrix\Sale\DeliveryStatus::TYPE
		]))
		{
			$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_STATUS_TYPE_OUT_OF_RANGE'), 201350000003));
		}

		if(trim($fields['ID'])=='')
		{
			$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_STATUS_TYPE_ID_EMPTY'), 201350000004));
		}
		elseif(strlen($fields['ID'])>2)
		{
			$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_STATUS_TYPE_STRLEN'), 201350000005));
		}

		/* TODO: check is_latin()
		 * if(!is_latin($fields['TYPE']))
		{
			$r->addError(new Error('', 'ERROR_STATUS_TYPE_LATIN_ONLY'));
		}*/

		if($r->isSuccess())
		{
			if($status = $this->get($fields['ID']))
			{
				$lockedType = $this->getLockedStatusType($fields['ID']);
				if($lockedType<>'' && $lockedType!=$fields['TYPE'])
				{
					$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_STATUS_WRONG_TYPE'),201350000006));
				}

				if ($status['TYPE'] != $fields['TYPE'])
				{
					if ($status['TYPE'] == \Bitrix\Sale\OrderStatus::TYPE)
					{
						if(\Bitrix\Sale\Internals\OrderTable::getList([
							'select'=>['ID'],
							'filter'=>['STATUS_ID'=>$status['ID']],
							'limit'=>1
						])->fetch())
						{
							$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_STATUS_TYPE_ORDER_EXISTS'),201350000007));
						}
					}
					else
					{
						if(\Bitrix\Sale\Internals\ShipmentTable::getList([
							'select'=>['ID'],
							'filter'=>['STATUS_ID'=>$status['ID']],
							'limit'=>1
						])->fetch())
						{
							$r->addError(new Error(Loc::getMessage('CONTROLLER_ERROR_STATUS_TYPE_SHIPMENT_EXISTS'),201350000008));
						}
					}
				}
			}
		}

		return $r;
	}

	protected function getLockedStatusType($statusId)
	{
		$lockedStatusList = [
			\Bitrix\Sale\OrderStatus::TYPE=>[
				\Bitrix\Sale\OrderStatus::getInitialStatus(),
				\Bitrix\Sale\OrderStatus::getFinalStatus()
			],
			\Bitrix\Sale\DeliveryStatus::TYPE=>[
				\Bitrix\Sale\DeliveryStatus::getInitialStatus(),
				\Bitrix\Sale\DeliveryStatus::getFinalStatus()
			]
		];

		foreach ($lockedStatusList as $lockStatusType=>$lockStatusIdList)
		{
			foreach ($lockStatusIdList as $lockStatusId)
			{
				if ($lockStatusId == $statusId)
				{
					return $lockStatusType;
				}
			}
		}
		return '';
	}

	protected function get($id)
	{
		return StatusTable::getById($id)->fetch();
	}

	protected function exists($id)
	{
		$r = new Result();
		if(isset($this->get($id)['ID']) == false)
			$r->addError(new Error('status is not exists', 201340400001));

		return $r;
	}
}