<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SocialnetworkEntityError extends \CBitrixComponent
{
	private const ENTITY_USER = 'USER';
	private const ENTITY_SONET_GROUP = 'SONET_GROUP';
	private const ENTITY_SUPPORT_BOT = 'SUPPORT_BOT';

	public function executeComponent()
	{
		$this->fillResult();

		$this->includeComponentTemplate();
	}

	private function isValidEntitiesList()
	{
		return [
			self::ENTITY_USER,
			self::ENTITY_SONET_GROUP,
			self::ENTITY_SUPPORT_BOT,
		];
	}

	private function fillResult()
	{
		$this->arResult = [
			'TITLE' => '',
			'DESCRIPTION' => '',
			'HELP_LINK' => false,
		];

		if (
			!isset($this->arParams['ENTITY'])
			|| !in_array($this->arParams['ENTITY'], $this->isValidEntitiesList())
		)
		{
			return;
		}

		if ($this->arParams['ENTITY'] === self::ENTITY_SUPPORT_BOT)
		{
			global $APPLICATION;
			$APPLICATION->SetTitle(Loc::getMessage('SOCIALNETWORK_ENTITY_TITLE_' . self::ENTITY_SUPPORT_BOT));
			$this->arResult['HELP_LINK'] = true;
		}

		$this->arResult['TITLE'] = Loc::getMessage('SOCIALNETWORK_ENTITY_ERROR_COMPONENT_TITLE_' . $this->arParams['ENTITY']);
		$this->arResult['DESCRIPTION'] = Loc::getMessage('SOCIALNETWORK_ENTITY_ERROR_COMPONENT_DESCRIPTION_' . $this->arParams['ENTITY']);

		$this->arResult = array_merge($this->arResult, $this->arParams);
	}
}
