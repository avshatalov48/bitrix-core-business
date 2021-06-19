<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals;

use Bitrix\Main\Access\Event\EventDictionary;
use Bitrix\Main\Access\Exception\AccessException;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\AccessController;
use Bitrix\Sender\Access\Exception\UnknownActionException;
use Bitrix\Sender\Access\Exception\WrongPermissionException;
use Bitrix\Sender\Security;
use CBitrixComponent;

Loc::loadMessages(__FILE__);

/**
 * Common component for sender component classes
 * @package Bitrix\Sender\Internals
 */
abstract class CommonSenderComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;
	/**
	 * @var int $userId
	 */
	protected $userId;

	/**
	 * @var AccessController $accessController
	 */
	protected $accessController;

	protected function checkRequiredParams()
	{
		try
		{
			if (!Loader::includeModule('sender'))
			{
				$this->errors->setError(new Error(Loc::getMessage('SENDER_MODULE_NOT_INSTALLED')));

				return false;
			}
		}
		catch (LoaderException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Initialization of component parameters.
	 * Validating access to the modify action by Permission Entity Code parameter
	 *
	 */
	protected function initParams()
	{
		$this->arParams['PATH_TO_LIST'] = isset($this->arParams['PATH_TO_LIST']) ?
			$this->arParams['PATH_TO_LIST'] : '';
		$this->arParams['PATH_TO_USER_PROFILE'] = isset($this->arParams['PATH_TO_USER_PROFILE']) ?
			$this->arParams['PATH_TO_USER_PROFILE'] : '';
		$this->arParams['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ?
			\CAllSite::GetNameFormat(false) :
			str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);

		$this->arParams['RENDER_FILTER_INTO_VIEW'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW']) ? $this->arParams['RENDER_FILTER_INTO_VIEW'] : '';
		$this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] = isset($this->arParams['RENDER_FILTER_INTO_VIEW_SORT']) ? $this->arParams['RENDER_FILTER_INTO_VIEW_SORT'] : 10;

		if(isset($this->arParams['GRID_ID']))
		{
			$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ?
				$this->arParams['FILTER_ID'] : $this->arParams['GRID_ID'] . '_FILTER';
		}

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ?
			$this->arParams['SET_TITLE'] == 'Y' : true;

		$this->canEdit();
	}

	protected function canEdit()
	{
		if(is_null(static::getEditAction()))
		{
			return;
		}

		try
		{
			$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT']) ? $this->arParams['CAN_EDIT']
				: $this->getAccessController()->check(static::getEditAction());
		}
		catch (UnknownActionException $e)
		{
			$this->errors->setError(new Error(Loc::getMessage('SENDER_WRONG_PERMISSION')));
			exit;
		}
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error->getMessage());
		}
	}

	protected function checkComponentExecution()
	{
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return false;
		}

		if (!static::prepareResult())
		{
			$this->printErrors();
			return false;
		}

		return true;
	}

	protected function getAccessController(): AccessController
	{
		if (!$this->accessController)
		{
			$this->accessController = new AccessController($this->userId);
		}
		return $this->accessController;
	}

	public function executeComponent()
	{
		$this->errors = new ErrorCollection();
		$this->userId = Security\User::current()->getId();
		Security\Access::registerEvent(EventDictionary::EVENT_ON_AFTER_CHECK);

		try
		{
			$canAccess = $this->getAccessController()->check(static::getViewAction());

			if(!$this->arParams['CAN_VIEW'] && !$canAccess)
			{
				throw new WrongPermissionException();
			}
		}
		catch (AccessException $e)
		{
			$this->errors->setError(new Error(Loc::getMessage('SENDER_WRONG_PERMISSION')));
			$this->printErrors();
			exit;
		}
		static::initParams();

		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			exit;
		}
	}

	/**
	 * @param string $template
	 *
	 * @return array|void
	 */
	protected function prepareResultAndTemplate($template = "")
	{
		if (!static::prepareResult())
		{
			$this->printErrors();
			exit();
		}

		$this->printErrors();

		if(!is_null($template))
		{
			$this->includeComponentTemplate($template);
			return;
		}
	}

	abstract protected function prepareResult();
	abstract public function getEditAction();
	abstract public function getViewAction();
}