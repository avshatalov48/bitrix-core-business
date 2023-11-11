<?php


namespace Bitrix\Rest\Integration\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\ClosureWrapper;
use Bitrix\Main\Error;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Rest\Integration\Externalizer;
use Bitrix\Rest\Integration\Internalizer;
use Bitrix\Rest\Integration\ModificationFieldsBase;
use Bitrix\Rest\Integration\ViewManager;

class Base extends Controller
{
	protected $viewManager;

	/**
	 * @param $actionName
	 * @return Engine\ClosureAction|Engine\InlineAction|null
	 * @throws SystemException
	 */
	protected function create($actionName)
	{
		$action = parent::create($actionName);

		$this->viewManager = $this->createViewManager($action);

		return $action;
	}

	/**
	 * @param Action $action
	 * @throws NotImplementedException
	 */
	protected function createViewManager(Action $action)
	{
		throw new NotImplementedException('The method createViewManager is not implemented.');
	}


	/**
	 * @return ViewManager
	 */
	public function getViewManager()
	{
		return $this->viewManager;
	}

	/**
	 * @param Action $action
	 * @return bool|null
	 * @throws NotImplementedException
	 * @throws SystemException
	 */
	protected function processBeforeAction(Engine\Action $action)
	{
		$r = $this->checkPermission($action->getName(), $action->getArguments());

		if($r->isSuccess())
		{
			$internalizer = new Internalizer(
				$this->getViewManager()
			);

			$r = $internalizer->process();

			if($r->isSuccess())
			{
				$action->setArguments($r->getData()['data']);
				return parent::processBeforeAction($action);
			}
			else
			{
				$this->addErrors($r->getErrors());
				return null;
			}
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	protected function processAfterAction(Engine\Action $action, $result)
	{
		$externalizer = null;
		if($this->errorCollection->count()==0)
		{
			if($result instanceof Engine\Response\DataType\Page || is_array($result))
			{
				$data = $result instanceof Engine\Response\DataType\Page ?
					$result->toArray():$result;

				$externalizer = new Externalizer(
					$this->getViewManager(),
					$data
				);
			}

			if($externalizer instanceof ModificationFieldsBase)
			{
				if($this->getScope() == Engine\Controller::SCOPE_REST)
				{
					return $result instanceof Engine\Response\DataType\Page ?
						$externalizer->getPage($result):$externalizer;
				}
				else if($this->getScope() == Engine\Controller::SCOPE_AJAX)
				{
					return $externalizer;
				}
			}
		}

		return parent::processAfterAction($action, $result);
	}

	/**
	 * @param $name
	 * @param array $arguments
	 * @return Result
	 * @throws NotImplementedException
	 */
	private function checkPermission($name, $arguments=[])
	{
		if($name == 'add')
		{
			$r = $this->checkCreatePermissionEntity();
		}
		elseif ($name == 'update')
		{
			$r = $this->checkUpdatePermissionEntity();
		}
		elseif ($name == 'list')
		{
			$r = $this->checkReadPermissionEntity();
		}
		elseif ($name == 'getfields')
		{
			$r = $this->checkGetFieldsPermissionEntity();
		}
		elseif ($name == 'get')
		{
			$r = $this->checkReadPermissionEntity();
		}
		elseif ($name == 'delete')
		{
			$r = $this->checkDeletePermissionEntity();
		}
		else
		{
			$r = $this->checkPermissionEntity($name, $arguments);
		}

		return $r;
	}

	/**
	 * @throws NotImplementedException
	 */
	protected function checkGetFieldsPermissionEntity()
	{
		return $this->checkReadPermissionEntity();
	}

	/**
	 * @throws NotImplementedException
	 */
	protected function checkReadPermissionEntity()
	{
		throw new NotImplementedException('Check read permission. The method checkReadPermissionEntity is not implemented.');
	}

	/**
	 * @return Result
	 * @throws NotImplementedException
	 */
	protected function checkModifyPermissionEntity()
	{
		throw new NotImplementedException('Check modify permission. The method checkModifyPermissionEntity is not implemented.');
	}

	/**
	 * @return Result
	 * @throws NotImplementedException
	 */
	protected function checkCreatePermissionEntity()
	{
		return $this->checkModifyPermissionEntity();
	}

	/**
	 * @return Result
	 * @throws NotImplementedException
	 */
	protected function checkUpdatePermissionEntity()
	{
		return $this->checkModifyPermissionEntity();
	}

	/**
	 * @return Result
	 * @throws NotImplementedException
	 */
	protected function checkDeletePermissionEntity()
	{
		return $this->checkModifyPermissionEntity();
	}

	/**
	 * @param $name
	 * @param array $arguments
	 * @return Result
	 * @throws NotImplementedException
	 */
	protected function checkPermissionEntity($name, $arguments=[])
	{
		throw new NotImplementedException('Check permission entity. The method '.$name.' is not implemented.');
	}

	/**
	 * @return \Bitrix\Main\Entity\DataManager
	 * @throws NotImplementedException
	 */
	protected function getEntityTable()
	{
		throw new NotImplementedException('The method getEntityTable is not implemented.');
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation|null $pageNavigation
	 * @return array
	 * @throws NotImplementedException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	protected function getList(array $select, array $filter, array $order, PageNavigation $pageNavigation = null): array
	{
		$entityTable = $this->getEntityTable();

		$select = empty($select) ? ['*'] : $select;
		$order = empty($order) ? ['ID' => 'ASC'] : $order;
		$params = [
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
		];
		if ($pageNavigation)
		{
			$params['offset'] = $pageNavigation->getOffset();
			$params['limit'] = $pageNavigation->getLimit();
		}

		return $entityTable::getList($params)->fetchAll();
	}

	/**
	 * @param $filter
	 * @return int
	 * @throws NotImplementedException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	protected function count($filter)
	{
		return function() use ($filter)
		{
			$entityTable = $this->getEntityTable();
			return $entityTable::getCount([$filter]);
		};
	}

	/**
	 * @param $id
	 * @return array|false
	 * @throws NotImplementedException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	protected function get($id)
	{
		$entityTable = $this->getEntityTable();

		return $entityTable::getById($id)->fetch();
	}

	/**
	 * @param array $fields
	 * @return \Bitrix\Main\ORM\Data\AddResult
	 * @throws NotImplementedException
	 */
	public function add(array $fields)
	{
		$entityTable = $this->getEntityTable();
		return $entityTable::add($fields);
	}

	/**
	 * @param $id
	 * @param array $fields
	 * @return \Bitrix\Main\ORM\Data\UpdateResult|null
	 * @throws NotImplementedException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	protected function update($id, array $fields)
	{
		$entityTable = $this->getEntityTable();

		/** @var \Bitrix\Main\Result $r */
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return $entityTable::update($id, $fields);
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	/**
	 * @param $id
	 * @return \Bitrix\Main\ORM\Data\DeleteResult|null
	 * @throws NotImplementedException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	protected function delete($id)
	{
		$entityTable = $this->getEntityTable();

		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return $entityTable::delete($id);
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	/**
	 * @param $id
	 * @return Result
	 * @throws NotImplementedException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	protected function exists($id)
	{
		$r = new \Bitrix\Main\Result();
		if(isset($this->get($id)['ID']) == false)
			$r->addError(new Error('Entity is not exists'));

		return $r;
	}

	/**
	 * @param $filter
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function existsByFilter($filter)
	{
		$result = new Result();
		$entityData = $this->getEntityTable()::getList(['filter' => $filter, 'limit' => 1])->fetch();

		if (!$entityData)
		{
			$result->addError(new Error('Entity is not exists'));
		}

		return $result;
	}
}