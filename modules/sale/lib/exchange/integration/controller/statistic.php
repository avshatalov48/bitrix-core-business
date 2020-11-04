<?php


namespace Bitrix\Sale\Exchange\Integration\Controller;


use Bitrix\Main\Engine\Action;
use Bitrix\Main\Error;
use Bitrix\Rest\Integration\Controller\Base;
use Bitrix\Rest\Integration\SaleViewManager;
use Bitrix\Sale\Exchange\Integration\Entity\B24IntegrationStatProviderTable;
use Bitrix\Sale\Exchange\Integration\Entity\B24integrationStatTable;
use Bitrix\Sale\Result;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\UI\PageNavigation;

class Statistic extends Base
{
	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['STATISTIC'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation $pageNavigation
	 * @return Page
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		return new Page('STATISTIC',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	/**
	 * @param $id
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['STATISTIC'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	/**
	 * @param array $fields
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function upsertAction(array $fields)
	{
		$r = $this->checkProviderById($fields['PROVIDER_ID']);
		if($r->isSuccess())
		{
			$entityTable = $this->getEntityTable();
			$r = $entityTable::upsert($fields);
			if($r->isSuccess())
			{
				return ['STATISTIC'=>$this->get($r->getPrimary())];
			}
		}

		$this->addErrors($r->getErrors());
		return null;
	}

	/**
	 * @param array $batch
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function modifyAction(array $fields)
	{
		$statistics = $fields['STATISTICS'];
		$providerId = $fields['PROVIDER']['ID'];

		$r = $this->checkPackageLimit($statistics);
		if($r->isSuccess())
		{
			$r = $this->checkProviderById($providerId);
			if($r->isSuccess() === false)
			{
				$this->addErrors($r->getErrors());
				return null;
			}

			$statistics = $this->onBeforeModify($providerId, $statistics);

			$entityTable = $this->getEntityTable();
			$r = $entityTable::modify($statistics);
			if($r->isSuccess())
			{
				$this->onAfterModify($providerId);

				$provider = new B24IntegrationStatProviderTable();

				return [
					'ITEMS' => [
						'PROVIDER' => $provider::getRow(['filter'=>['ID'=>$providerId]]),
					]
				];
			}
		}

		$this->addErrors($r->getErrors());
		return null;
	}

	/**
	 * @param array $fields
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addAction(array $fields)
	{
		$r = $this->checkProviderById($fields['PROVIDER_ID']);
		if($r->isSuccess())
		{
			$r = parent::add($fields);
			if($r->isSuccess())
			{
				return ['STATISTIC'=>$this->get($r->getPrimary())];
			}
		}

		$this->addErrors($r->getErrors());
		return null;
	}

	/**
	 * @param $id
	 * @param array $fields
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function updateAction($id, array $fields)
	{
		$r = parent::update($id, $fields);
		if($r->isSuccess())
		{
			return ['STATISTIC'=>$this->get($id)];
		}

		$this->addErrors($r->getErrors());
		return null;
	}

	/**
	 * @param $id
	 * @return bool|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function deleteAction($id)
	{
		$r = parent::delete($id);
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

	/**
	 * @param $providerId
	 * @param $statistics
	 * @return mixed
	 */
	protected function onBeforeModify($providerId, $statistics)
	{
		foreach ($statistics as $k=>$statistic)
		{
			$statistics[$k]['PROVIDER_ID'] = $providerId;
		}
		return $statistics;
	}

	/**
	 * @param $providerId
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function onAfterModify($providerId)
	{
		$provider = new B24IntegrationStatProviderTable();

		$fields = $this->getEntityTable()::getRow([
				'filter'=>['PROVIDER_ID'=>$providerId],
				'order'=>['DATE_UPDATE'=>'DESC']]
		);

		if(is_array($fields))
		{
			$provider::update($providerId, ['SETTINGS'=>['LAST_DATE_UPDATE'=>$fields['DATE_UPDATE']->format('c')]]);
		}
	}

	protected function createViewManager(Action $action)
	{
		return new SaleViewManager($action);
	}

	protected function getEntityTable()
	{
		return new B24integrationStatTable();
	}

	protected function checkReadPermissionEntity()
	{
		return new Result();
	}

	protected function checkCreatePermissionEntity()
	{
		return new Result();
	}

	protected function checkUpdatePermissionEntity()
	{
		return new Result();
	}

	protected function checkDeletePermissionEntity()
	{
		return new Result();
	}

	protected function checkPermissionEntity($name, $arguments=[])
	{
		if($name == 'upsert' || $name == 'modify')
		{
			$r = $this->checkCreatePermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}

		return $r;
	}

	/**
	 * @param $id
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function checkProviderById($id)
	{
		$r = new Result();

		if(is_null($this->getProviderById($id)))
		{
			$r->addError(new Error('Provider is not exists'));
		}
		return $r;
	}

	/**
	 * @param array $data
	 * @return Result
	 */
	protected function checkPackageLimit(array $data)
	{
		$r = new Result();

		if(count($data) > \Bitrix\Sale\Exchange\Integration\Manager\Statistic::STATISTIC_IMPORT_PACKAGE_LIMIT)
		{
			$r->addError(new Error('Batch exceeded the limit - '.\Bitrix\Sale\Exchange\Integration\Manager\Statistic::STATISTIC_IMPORT_PACKAGE_LIMIT));
		}
		return $r;
	}

	/**
	 * @param $id
	 * @return array|false|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getProviderById($id)
	{
		$result = B24IntegrationStatProviderTable::getByPrimary($id);
		$row = $result->fetch();

		return (is_array($row)? $row : null);
	}
}