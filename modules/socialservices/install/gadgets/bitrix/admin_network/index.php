<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 */

global $APPLICATION;
global $USER;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Converter;
use Bitrix\Socialservices\Network;

Loc::loadMessages(__FILE__);

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/admin_network/styles.css');

$url = "";
$onclick = "";
$messageCode = "GD_NETWORK_CONNECT";

if(Loader::includeModule('socialservices'))
{
	if(Option::get("socialservices", "bitrix24net_id", "") == "")
	{
		$url = "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=socialservices&register_network=yes";
	}
	else
	{
		$dbRes = \Bitrix\Socialservices\UserTable::getList(array(
			'filter' => array(
				'=USER_ID' => $USER->GetID(),
				'=EXTERNAL_AUTH_ID' => CSocServBitrix24Net::ID
			),
		));

		$profileInfo = $dbRes->fetch();
		if(!$profileInfo)
		{
			$url = "javascript:void(0)";
			$onclick = "BX.util.popup('".\CUtil::JSEscape(Network::getAuthUrl("popup", array("admin")))."', 700, 500);";
		}
		else
		{
			$url = "/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID."&ID=".$USER->GetID()."&user_edit_active_tab=edit_socialservices";
			$messageCode = "GD_NETWORK_CONNECTED";
		}
	}
}
else
{
	$url = "/bitrix/admin/module_admin.php?&action=&lang=".LANGUAGE_ID."&id=socialservices&install=1&".bitrix_sessid_get();
}
?>

<div class="bx-gadgets-content-layout-network">
	<div class="bx-gadgets-title"><?= Loc::getMessage('GD_NETWORK_TITLE')?></div>
	<div class="bx-gadget-bottom-cont bx-gadget-bottom-button-cont bx-gadget-mark-cont">
		<a href="<?= Converter::getHtmlConverter()->encode($url)?>" onclick="<?= Converter::getHtmlConverter()->encode($onclick)?>" class="bx-gadget-button">
			<div class="bx-gadget-button-lamp"></div>
			<div class="bx-gadget-button-text"><?=Loc::getMessage($messageCode)?></div>
		</a>
		<div class="bx-gadget-mark">
			<?=Loc::getMessage('GD_NETWORK_ADDITIONAL')?>
			<div class="bx-gadget-mark-desc"><?=Loc::getMessage('GD_NETWORK_ADDITIONAL2')?></div>
		</div>
	</div>
</div>
<div class="bx-gadget-shield"></div>
<div style="cursor:move;" class="bx-gadgets-side"></div>
