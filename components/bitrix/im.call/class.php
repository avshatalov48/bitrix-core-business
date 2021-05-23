<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Im\Call\Call;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class CImCallComponent extends \CBitrixComponent
{
	const MODULE='im';

	protected $call;
	/** @var \Bitrix\Main\Error[] */
	protected $errors = [];
	protected $userId;

	protected $action;

	public function init()
	{
		$this->action = $_REQUEST['action'] === 'startCall' ? 'startCall' : 'answer';

		$this->userId = \Bitrix\Im\User::getInstance()->getId();;

		if ($this->action === 'startCall')
		{
			$this->call = \Bitrix\Im\Call\Call::createWithEntity(
				Call::TYPE_INSTANT,
				Call::PROVIDER_PLAIN,
				\Bitrix\Im\Call\Integration\EntityType::CHAT,
				$_REQUEST['dialogId'],
				$this->userId
			);
		}
		else
		{
			$this->call = Call::loadWithId($_REQUEST['callId']);
		}

		if(!$this->call)
		{
			$this->errors[] = new \Bitrix\Main\Error('Conference is not found');
			return false;
		}

		if(!$this->call->checkAccess($this->userId))
		{
			$this->errors[] = new \Bitrix\Main\Error('You have no access to the conference');
			return false;
		}
		return true;
	}

	protected function getPublicIds(array $userIds)
	{
		if(!Loader::includeModule('pull'))
		{
			return [];
		}

		return \Bitrix\Pull\Channel::getPublicIds([
			'USERS' => $userIds,
			'JSON' => true
		]);
	}

	public function prepareData()
	{
		$this->arResult['CALL'] = $this->call->toArray();
		$this->arResult['ACTION'] = $this->action;
		$this->arResult['CALL_USERS'] = $this->call->getUsers();
		$this->arResult['PUBLIC_IDS'] = $this->getPublicIds($this->arResult['CALL_USERS']);
	}

	public function executeComponent()
	{
		if (!Loader::includeModule(self::MODULE))
		{
			return false;
		}

		if(!$this->init())
		{
			foreach ($this->errors as $error)
			{
				ShowError($error->getMessage());
			}
			return false;
		}

		$this->prepareData();
		$this->includeComponentTemplate();
		return $this->arResult;
	}
}