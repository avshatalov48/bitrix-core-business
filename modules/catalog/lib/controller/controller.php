<?php


namespace Bitrix\Catalog\Controller;

use \Bitrix\Main\Engine;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\ClosureWrapper;
use Bitrix\Main\Error;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Rest\Integration\ViewManager;
use Bitrix\Rest\Integration\Externalizer;

class Controller extends Engine\Controller
{
	const IBLOCK_READ = 'iblock_admin_display';
	const IBLOCK_ELEMENT_READ = 'element_read';
	const IBLOCK_ELEMENT_EDIT = 'element_edit';
	const IBLOCK_ELEMENT_DELETE = 'element_delete';
	const IBLOCK_SECTION_READ = 'section_read';
	const IBLOCK_SECTION_EDIT = 'section_edit';
	const IBLOCK_SECTION_DELETE = 'section_delete';
	const IBLOCK_ELEMENT_EDIT_PRICE = 'element_edit_price';
	const IBLOCK_SECTION_SECTION_BIND = 'section_section_bind';
	const IBLOCK_ELEMENT_SECTION_BIND = 'section_element_bind';
	const IBLOCK_EDIT = 'iblock_edit';

	protected $viewManager;

	protected function create($actionName)
	{
		$action = parent::create($actionName);

		if($this->getScope() == Engine\Controller::SCOPE_REST)
		{
			$this->viewManager = new \Bitrix\Rest\Integration\CatalogViewManager($action);
		}

		return $action;
	}

	public function configureActions()
	{
		return [
			'getFields' => [
				'+prefilters' => [
					function() 
					{
						/** @var ClosureWrapper $this */
						/** @var Action $action */
						$action = $this->getAction();
						if($action->getController()->getScope() !== \Bitrix\Main\Engine\Controller::SCOPE_REST)
						{
							throw new SystemException('the method is only available in the rest service');
						}
					}
				],
			],
		];
	}

	/**
	 * @return ViewManager
	 */
	public function getViewManager()
	{
		return $this->viewManager;
	}

	protected function processBeforeAction(Engine\Action $action)
	{
		$r = $this->checkPermission($action->getName(), $action->getArguments());
		if($r->isSuccess())
		{
			if($this->getScope() == Engine\Controller::SCOPE_REST)
			{
				$internalizer = new \Bitrix\Rest\Integration\Internalizer($this->getViewManager());

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
				return parent::processBeforeAction($action);
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
		if($this->getScope() == Engine\Controller::SCOPE_REST)
		{
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

					return $result instanceof Engine\Response\DataType\Page ?
						$externalizer->getPage($result):$externalizer;
				}
			}
		}

		return parent::processAfterAction($action, $result);
	}

	protected static function getApplication()
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		return $APPLICATION;
	}

	protected static function getGlobalUser()
	{
		/** @global \CUser $USER */
		global $USER;

		return $USER;
	}

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

	protected function checkGetFieldsPermissionEntity()
	{
		return $this->checkReadPermissionEntity();
	}

	protected function checkReadPermissionEntity()
	{
		throw new NotImplementedException('Check read permission. The method checkReadPermissionEntity is not implemented.');
	}

	protected function checkModifyPermissionEntity()
	{
		throw new NotImplementedException('Check modify permission. The method checkModifyPermissionEntity is not implemented.');
	}

	protected function checkCreatePermissionEntity()
	{
		return $this->checkModifyPermissionEntity();
	}

	protected function checkUpdatePermissionEntity()
	{
		return $this->checkModifyPermissionEntity();
	}

	protected function checkDeletePermissionEntity()
	{
		return $this->checkModifyPermissionEntity();
	}

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

	protected function getList($select, $filter, $order, PageNavigation $pageNavigation)
	{
		$entityTable = $this->getEntityTable();

		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$items = $entityTable::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return $items;
	}

	protected function count($filter)
	{
		$entityTable = $this->getEntityTable();

		return $entityTable::getCount([$filter]);
	}

	protected function get($id)
	{
		$entityTable = $this->getEntityTable();

		return $entityTable::getById($id)->fetch();
	}

	protected static function getNavData($start, $orm = false)
	{
		if($start >= 0)
		{
			return ($orm ?
				['limit' => \IRestService::LIST_LIMIT, 'offset' => intval($start)]
				:['nPageSize' => \IRestService::LIST_LIMIT, 'iNumPage' => intval($start / \IRestService::LIST_LIMIT) + 1]
			);
		}
		else
		{
			return ($orm ?
				['limit' => \IRestService::LIST_LIMIT]
				:['nTopCount' => \IRestService::LIST_LIMIT]
			);
		}
	}
}