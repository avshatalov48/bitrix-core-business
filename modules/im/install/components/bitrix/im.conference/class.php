<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class CImConferenceComponent extends \CBitrixComponent
{
	const MODULE='im';
	protected $callPublicId;
	/** @var \Bitrix\Im\Call\Call */
	protected $call;
	/** @var \Bitrix\Main\Error[] */
	protected $errors = [];
	protected $userId;


	public function init()
	{
		$this->callPublicId = $this->arParams['PUBLIC_ID'];
		$this->call = \Bitrix\Im\Call\Call::createWithPublicId($this->callPublicId);
		$this->userId = \Bitrix\Im\User::getInstance()->getId();;

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