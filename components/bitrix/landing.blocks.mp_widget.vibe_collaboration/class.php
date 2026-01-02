<?php

use Bitrix\Im\V2\Chat\GeneralChat;
use Bitrix\Landing\Manager;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageVibeCollaboration extends LandingBlocksMainpageWidgetBase
{
	private const WIDGET_CSS_VAR_PROPERTIES = [];

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->initializeParams();
		$this->getData();
		parent::executeComponent();
	}

	protected function initializeParams(): void
	{
		foreach (self::WIDGET_CSS_VAR_PROPERTIES as $property => $cssVar)
		{
			$this->addCssVarProperty($property, $cssVar);
		}
	}

	protected function getData(): void
	{
		$this->arResult['ZONE']  = Manager::getZone();

		if (Loader::includeModule('intranet'))
		{
			$this->arResult['INVITE_DIALOG_LINK'] = \CIntranetInviteDialog::GetInviteDialogLink()->getUri();
		}

		if (Loader::includeModule('im'))
		{
			$this->arResult['GENERAL_CHAT_ID'] = GeneralChat::getGeneralChatId();
		}
	}
}
