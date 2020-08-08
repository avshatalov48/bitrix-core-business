<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\ImBot\Bot\Partner24;

Loc::loadMessages(__FILE__);

class CUiInfoHelperComponent extends \CBitrixComponent
{
	private function getHostName()
	{
		if (ModuleManager::isModuleInstalled("bitrix24"))
		{
			return BX24_HOST_NAME;
		}

		$host = "";
		$site = Bitrix\Main\SiteTable::getList(array(
			'filter' => defined('SITE_ID') ? array('LID' => SITE_ID) : array(),
			'order'  => array('ACTIVE' => 'DESC', 'DEF' => 'DESC', 'SORT' => 'ASC'),
			'select' => array('SERVER_NAME')
		))->fetch();

		$host = $site['SERVER_NAME'] ?: COption::getOptionString('main', 'server_name', '');

		return $host;
	}

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		$isBitrix24Cloud = Loader::includeModule("bitrix24");

		$notifyUrl = "";
		if (Loader::includeModule("ui"))
		{
			$notifyUrl = \Bitrix\UI\Util::getHelpdeskUrl()."/widget2/show/code/";
		}

		$host = $this->getHostName();

		$parameters = [
			"is_admin" => Loader::includeModule("bitrix24") && \CBitrix24::IsPortalAdmin($USER->GetID()) || !$isBitrix24Cloud && $USER->IsAdmin() ? 1 : 0,
			"tariff" => COption::GetOptionString("main", "~controller_group_name", ""),
			"is_cloud" => $isBitrix24Cloud ? "1" : "0",
			"host"  => $host,
			"languageId" => LANGUAGE_ID,
			"user_name" => $APPLICATION->ConvertCharsetArray($USER->GetFirstName(), SITE_CHARSET, 'utf-8'),
			"user_last_name" => $APPLICATION->ConvertCharsetArray($USER->GetLastName(), SITE_CHARSET, 'utf-8'),
		];
		if(Loader::includeModule('imbot'))
		{
			$parameters['support_partner_code'] = Partner24::getBotCode();
			$partnerName = $APPLICATION->ConvertCharsetArray(Partner24::getPartnerName(), SITE_CHARSET, 'utf-8');
			$parameters['support_partner_name'] = $partnerName;
		}

		if (!$isBitrix24Cloud)
		{
			$parameters["head"] = md5("BITRIX".LICENSE_KEY."LICENCE");
			$parameters["key"] = md5($host.$USER->GetID().$parameters["head"]);
		}
		else
		{
			$parameters["key"] = CBitrix24::RequestSign($host.$USER->GetID());
		}

		$this->arResult["NOTIFY_URL"] = CHTTP::urlAddParams($notifyUrl, $parameters, array("encode" => true));

		$this->includeComponentTemplate();
	}
}
?>