<?php


namespace Bitrix\Sale\Controller;

use \Bitrix\Main\Engine;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\ClosureWrapper;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Rest\Integration\ViewManager;
use Bitrix\Rest\Integration\Externalizer;

class ControllerBase extends Engine\Controller
{
	protected $viewManager;

	protected function create($actionName)
	{
		$action = parent::create($actionName);

		if($this->getScope() == Engine\Controller::SCOPE_REST)
		{
			$this->viewManager = new \Bitrix\Rest\Integration\SaleViewManager($action);
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

	/**
	 * @param $name
	 * @param array $arguments
	 * @throws NotImplementedException
	 * @return Result
	 */
	private function checkPermission($name, $arguments=[])
	{
		if ($name == 'list')
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
		else
		{
			$r = $this->checkPermissionEntity($name, $arguments);
		}

		return $r;
	}

	/**
	 * @throws NotImplementedException
	 * @return Result
	 */
	protected function checkGetFieldsPermissionEntity()
	{
		return $this->checkReadPermissionEntity();
	}

	/**
	 * @throws NotImplementedException
	 * @return Result
	 */
	protected function checkReadPermissionEntity()
	{
		throw new NotImplementedException('Check read permission. The method checkReadPermissionEntity is not implemented.');
	}

	/**
	 * @param $name
	 * @param array $arguments
	 * @throws NotImplementedException
	 * @return Result
	 */
	protected function checkPermissionEntity($name, $arguments=[])
	{
		throw new NotImplementedException('Check permission entity. The method '.$name.' is not implemented.');
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

	protected static function getApplication()
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		return $APPLICATION;
	}
}