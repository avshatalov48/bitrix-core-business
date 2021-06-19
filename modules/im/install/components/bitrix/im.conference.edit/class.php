<?php

use Bitrix\Im\Call\Conference;
use Bitrix\Im\Chat;
use Bitrix\Im\User;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

class ImComponentConferenceEdit extends CBitrixComponent
{
	protected const VIEW_MODE = 'view';
	protected const EDIT_MODE = 'edit';
	protected const CREATE_MODE = 'create';


	protected $conference;
	protected $errorCollection;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
	}

	protected function initParams(): bool
	{
		$this->arParams['ID'] = isset($this->arParams['ID']) ? (int) $this->arParams['ID'] : 0;

		return true;
	}

	protected function prepareResult(): bool
	{
		global $APPLICATION;

		$editMode = $this->arParams['ID'] > 0;
		if ($editMode)
		{
			$APPLICATION->SetTitle(Loc::getMessage('IM_CONFERENCE_EDIT_TITLE'));
			$this->conference = Conference::getById($this->arParams['ID']);
			if (!$this->conference)
			{
				$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('IM_CONFERENCE_EDIT_CLASS_ERROR_CONFERENCE_NOT_FOUND'));

				return false;
			}

			if (!$this->conference->canUserEdit(CurrentUser::get()->getId()))
			{
				$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('IM_CONFERENCE_EDIT_CLASS_ERROR_CONFERENCE_CANT_EDIT'));

				return false;
			}

			if (!Chat::isUserInChat($this->conference->getChatId()))
			{
				$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('IM_CONFERENCE_EDIT_CLASS_ERROR_CONFERENCE_ACCESS_DENIED'));

				return false;
			}

			$chatHost = User::getInstance($this->conference->getHostId())->getArray();
			$this->arResult['CHAT_HOST'] = [
				'ID' => $chatHost['ID'],
				'FIRST_NAME' => $chatHost['FIRST_NAME'],
				'FULL_NAME' => $chatHost['NAME'],
				'AVATAR' => $chatHost['AVATAR']
			];
			$this->arResult['CHAT_USERS'] = $this->conference->getUsers();
			$this->arResult['PUBLIC_LINK'] = $this->conference->getPublicLink();
			$this->arResult['CHAT_ID'] = $this->conference->getChatId();
			$this->arResult['FIELDS_DATA'] = [
				'TITLE' => $this->conference->getChatName(),
				'PASSWORD' => $this->conference->getPassword(),
				'BROADCAST' => $this->conference->isBroadcast()
			];
			$this->arResult['INVITATION'] = $this->conference->getInvitation();
			$this->arResult['MODE'] = self::VIEW_MODE;

			if ($this->conference->isBroadcast())
			{
				$presenters = $this->conference->getPresentersInfo();
				$this->arResult['PRESENTERS'] = array_map(function($user){
					return [
						'id' => $user['id'],
						'title' => $user['name'],
						'avatar' => $user['avatar']
					];
				}, $presenters);
			}
		}
		else
		{
			$APPLICATION->SetTitle(Loc::getMessage('IM_CONFERENCE_CREATE_TITLE'));

			$chatHost = User::getInstance()->getArray();
			$this->arResult['CHAT_HOST'] = [
				'ID' => $chatHost['ID'],
				'FIRST_NAME' => $chatHost['FIRST_NAME'],
				'FULL_NAME' => $chatHost['NAME'],
				'AVATAR' => $chatHost['AVATAR']
			];
			$this->arResult['MODE'] = self::CREATE_MODE;
		}

		return true;
	}

	public function executeComponent()
	{
		if (!Loader::includeModule('im'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error("IM module is not installed");
		}

		$this->initParams();
		$this->prepareResult();

		if(!$this->errorCollection->isEmpty())
		{
			$this->arResult = [
				"COMPONENT_ERRORS" => $this->errorCollection->toArray()
			];
		}

		$this->includeComponentTemplate();

		return $this->arResult;
	}
}