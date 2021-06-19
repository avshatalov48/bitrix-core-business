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
		];
	}

	private function fillResult()
	{
		$this->arResult = [
			'TITLE' => '',
			'DESCRIPTION' => '',
		];

		if (
			!isset($this->arParams['ENTITY'])
			|| !in_array($this->arParams['ENTITY'], $this->isValidEntitiesList())
		)
		{
			return;
		}

		$this->arResult['TITLE'] = Loc::getMessage('SOCIALNETWORK_ENTITY_ERROR_COMPONENT_TITLE_' . $this->arParams['ENTITY']);
		$this->arResult['DESCRIPTION'] = Loc::getMessage('SOCIALNETWORK_ENTITY_ERROR_COMPONENT_DESCRIPTION_' . $this->arParams['ENTITY']);

		$this->arResult = array_merge($this->arResult, $this->arParams);
	}
}
