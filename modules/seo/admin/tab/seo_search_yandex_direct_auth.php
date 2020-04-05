<?php
/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global bool $bNeedAuth
 * @global array $currentUser
 * @global Bitrix\Seo\Engine\YandexDirect $engine
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Converter;
use Bitrix\Seo\Service;

$authAction = "";
$request = Context::getCurrent()->getRequest();

echo BeginNote();
if(!Service::isRegistered())
{
	$authAction = "registerClient();";
?>
	<input type=button onclick="<?=$authAction?>" value="<?=Loc::getMessage('SEO_YANDEX_REGISTER')?>"
 id="seo_authorize_btn" />
<?
}
else
{
	$authInfo = Service::getAuth($engine->getCode());
	if(!$authInfo)
	{
		$authorizeUrl = Service::getAuthorizeLink();
		$authorizeData = Service::getAuthorizeData($engine->getCode());
?>
		<input type=button onclick="authorizeUser('<?= $authorizeUrl ?>', <?=CUtil::PhpToJSObject($authorizeData)?>)" value="<?= Loc::getMessage('SEO_AUTH_YANDEX') ?>" id="seo_authorize_btn"/>
<?
	}
	else
	{
		$currentUser = $authInfo['user'];
?>
<div id="auth_result" class="seo-auth-result">
	<b><?=Loc::getMessage('SEO_AUTH_CURRENT')?>:</b><div style="width: 300px; padding: 10px 0 0 0;">
		<?=Converter::getHtmlConverter()->encode($currentUser['real_name'].' ('.$currentUser['display_name'].')')?><br />
		<a href="javascript:void(0)" onclick="makeNewAuth()"><?=Loc::getMessage('SEO_AUTH_CANCEL')?></a>
		<div style="clear: both;"></div>
	</div>
</div>
<?
	}
}
echo EndNote();
?>

<script type="text/javascript">
	function makeNewAuth()
	{
		BX.showWait(BX('auth_result'));
		BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php?action=nullify_auth&sessid=' + BX.bitrix_sessid(), function(){
			window.location.reload();
		});
	}

	function registerClient()
	{
		BX('seo_authorize_btn').disabled = true;

		BX('seo_authorize_btn').value = '<?=CUtil::JSEscape(Loc::getMessage("SEO_YANDEX_REGISTER_RPOGRESS"))?>';

		BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php?action=register&sessid=' + BX.bitrix_sessid(), function(result)
		{
			if(result['result'])
			{
				BX.reload();
			}
			else if(result["error"])
			{
				alert('<?=CUtil::JSEscape(Loc::getMessage("SEO_ERROR"))?> : ' + result['error']['message']);
				BX('seo_authorize_btn').value = '<?=CUtil::JSEscape(Loc::getMessage('SEO_YANDEX_REGISTER'))?>';
			}
		});
	}


	function authorizeUser(url, data)
	{
		var s = '<form action="'+BX.util.htmlspecialchars(url)+'">';

		for(var i in data)
		{
			if(data.hasOwnProperty(i))
			{
				s += '<input type="hidden" name="'+BX.util.htmlspecialchars(i)+'" value="'+BX.util.htmlspecialchars(data[i])+'" />';
			}
		}

		s += '</form>';

		var popup = BX.util.popup('', 680, 600);
		popup.document.write(s);
		popup.document.forms[0].submit();
	}

<?
if($request["auth"] && $authAction != "")
{
	echo $authAction;
}
?>
</script>