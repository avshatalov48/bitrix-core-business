<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\ImConnector\Library;
use Bitrix\Main\Loader;

class Informant extends \Bitrix\MessageService\Providers\Base\Informant
{
	public function isConfigurable(): bool
	{
		return true;
	}

	public function getId(): string
	{
		return Constants::ID;
	}

	public function getName(): string
	{
		if (RegionHelper::isInternational())
		{
			return 'Edna.io WhatsApp';
		}

		return 'Edna.ru WhatsApp';
	}

	public function getShortName(): string
	{
		if (RegionHelper::isInternational())
		{
			return 'Edna.io WhatsApp';
		}

		return 'Edna.ru WhatsApp';
	}

	public function getManageUrl(): string
	{
		if (defined('ADMIN_SECTION') && ADMIN_SECTION === true)
		{
			return parent::getManageUrl();
		}

		if (!Loader::includeModule('imopenlines') || !Loader::includeModule('imconnector'))
		{
			return '';
		}

		$contactCenterUrl = \Bitrix\ImOpenLines\Common::getContactCenterPublicFolder();

		return $contactCenterUrl . 'connector/?ID=' . Library::ID_EDNA_WHATSAPP_CONNECTOR;
	}

}