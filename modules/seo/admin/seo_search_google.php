<?
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

define('ADMIN_MODULE_NAME', 'seo');

use Bitrix\Main;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Webmaster;

Loc::loadMessages(__DIR__.'/../../main/tools.php');
Loc::loadMessages(__DIR__.'/seo_search.php');

if (!$USER->CanDoOperation('seo_tools'))
{
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

if (!Main\Loader::includeModule('seo'))
{
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_NO_MODULE"));
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
}

if (!Main\Loader::includeModule('socialservices'))
{
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_NO_MODULE_SOCSERV"));
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
}

$strError = "";

$sTableID = "tbl_seo_domains";
$oSort = new CAdminSorting($sTableID, "SORT", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$lAdmin->AddHeaders([
	["id"=>"DOMAIN", "content"=>Loc::getMessage('SEO_DOMAIN'), "sort"=>"DOMAIN", "default"=>true],
	["id"=>"SITE", "content"=>Loc::getMessage("SEO_SITE"), "default"=>true],
	["id"=>"SITE_ACTIVE","content"=>Loc::getMessage('SEO_SITE_ACTIVE'), "sort"=>"active", "default"=>true],
	["id"=>"BINDED", "content"=>Loc::getMessage("SEO_BINDED"), "default"=>true],
	["id"=>"VERIFIED", "content"=>Loc::getMessage("SEO_VERIFIED"), "default"=>true],
]);

$hasAuth = false;
try
{
	$authAdapter =
		Webmaster\Service::getAuthAdapter(Webmaster\Service::TYPE_GOOGLE)
			->setService(Webmaster\Service::getInstance())
	;
	$hasAuth = $authAdapter->hasAuth();
}
catch(SystemException $e)
{
	$strError .= $e->getMessage();
}

$dbSites = new Bitrix\Main\DB\ArrayResult(\CSeoUtils::getDomainsList());
$rsData = new CAdminResult($dbSites, $sTableID);

while($arSite = $dbSites->fetch(Converter::getHtmlConverter()))
{
	$row =& $lAdmin->AddRow($arSite['DOMAIN'], $arSite);

	$siteDomainEnc = Converter::getHtmlConverter()->encode($arSite['DOMAIN']);
	$e = [];
	$siteDomainEncView = Converter::getHtmlConverter()->encode(\CBXPunycode::ToUnicode($arSite['DOMAIN'], $e));

	$siteDirEnc = Converter::getHtmlConverter()->encode($arSite['SITE_DIR']);

	$row->AddViewField("DOMAIN", '<a href="http://'.Converter::getHtmlConverter()->encode($arSite['DOMAIN'].CHTTP::urnEncode($arSite['SITE_DIR'])).'">'.$siteDomainEncView.$siteDirEnc.'</a>');
	$row->AddViewField("SITE", '[<a href="site_edit.php?lang='.LANGUAGE_ID.'&amp;LID='.urlencode($arSite['LID']).'">'.$arSite['LID'].'</a>] '.$arSite['SITE_NAME']);
	$row->AddCheckField("SITE_ACTIVE", false);

	$row->AddViewField("BINDED", '<span data-role="site-binded" data-domain="'.$siteDomainEnc.'" data-dir="'.$siteDirEnc.'">'.(!$hasAuth ? Loc::getMessage('SEO_NEED_AUTH') : Loc::getMessage('SEO_LOADING')).'</span>');
	$row->AddViewField("VERIFIED", '<span data-role="site-verified" data-domain="'.$siteDomainEnc.'" data-dir="'.$siteDirEnc.'">'.(!$hasAuth ? Loc::getMessage('SEO_NEED_AUTH') : Loc::getMessage('SEO_LOADING')).'</span>');
}

$lAdmin->CheckListMode();
$APPLICATION->SetTitle(Loc::getMessage("SEO_GOOGLE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($strError != '')
{
	CAdminMessage::ShowMessage($strError);
}
?>
<div id="ajax_status"></div>


<?=BeginNote();?>

<?php if (!$hasAuth): ?>
	<p><?=Loc::getMessage('SEO_AUTH_HINT_SHORT')?></p>
	<input type=button
		onclick="BX.util.popup('<?= $authAdapter->getAuthUrl() ?>',800,600)"
		value="<?= Loc::getMessage('SEO_AUTH_GOOGLE') ?>"
		id="seo_authorize_btn"
	/>

<?php else: ?>
	<div id="auth_result" class="seo-auth-result">
		<b><?=Loc::getMessage('SEO_AUTH_OK')?></b><br>
		<a href="javascript:void(0)" onclick="makeNewAuth()"><?=Loc::getMessage('SEO_AUTH_CANCEL')?></a>
	</div>

	<script>
		window.lastSeoResult = null;

		function updateCallback(res)
		{
			if(!!res && typeof res.error != 'undefined')
			{
				BX.Runtime.loadExtension('ui.dialogs.messagebox')
					.then(() => {
						BX.UI.Dialogs.MessageBox.alert(res.error);
					})
				;

				return;
			}

			window.lastSeoResult = {};
			for (let i in res)
			{
				lastSeoResult[i]=res[i];
			}

			BX.ready(function(){
				const nodes = BX.findChildren(
					BX('<?=CUtil::JSEscape($sTableID)?>'),
					{tag: 'span', attr: 'data-domain'},
					true
				);

				for (let i = 0; i < nodes.length; i++)
				{
					const role = nodes[i].getAttribute('data-role');

					const domain = nodes[i].getAttribute('data-domain');

					if (typeof res._domain !== 'undefined' && res._domain !== domain)
					{
						continue;
					}

					if (typeof res[domain] !== 'undefined')
					{
						switch (role)
						{
							case 'site-binded':
								nodes[i].innerHTML =
									res[domain]['binded'] == true
										? '<?=CUtil::JSEscape(Loc::getMessage('MAIN_YES'))?>'
										: '<?=CUtil::JSEscape(Loc::getMessage('MAIN_NO'))?>'
								;
								break;
							case 'site-verified':
								if (res[domain].verified == true)
								{
									nodes[i].innerHTML = '<?=CUtil::JSEscape(Loc::getMessage('MAIN_YES'))?>';
								}
								else
								{
									BX.cleanNode(nodes[i]);
									BX.adjust(nodes[i], {children:[
											'<?=CUtil::JSEscape(Loc::getMessage('MAIN_NO'))?> ',
											BX.create('A', {
												attrs: {'href':'javascript:void(0)'},
												events: {
													click: _clickVerify
												},
												text: '<?=CUtil::JSEscape(Loc::getMessage('SEO_VERIFY'))?>'
											})
										]});
								}
								break;
						}
					}
					else
					{
						BX.cleanNode(nodes[i]);
						BX.adjust(nodes[i], {children:[
								'<?=CUtil::JSEscape(Loc::getMessage('MAIN_NO'))?> ',
								BX.create('A', {
									attrs: {'href':'javascript:void(0)'},
									events: {
										click: _clickBind
									},
									text: '<?=CUtil::JSEscape(Loc::getMessage('SEO_BIND'))?>'
								})
							]});
					}
				}
			});
		}

		function _clickBind()
		{
			bindDomain(this.parentNode.getAttribute('data-domain'), this.parentNode.getAttribute('data-dir'), this.parentNode);
		}

		function _clickVerify()
		{
			verifyDomain(this.parentNode.getAttribute('data-domain'), this.parentNode.getAttribute('data-dir'), this.parentNode);
		}

		function bindDomain(domain, dir, node)
		{
			node.innerHTML = '<?=CUtil::JSEscape(Loc::getMessage('SEO_LOADING'))?>';
			BX.ajax.loadJSON('/bitrix/tools/seo_google.php?action=site_add&domain='+BX.util.urlencode(domain)+'&dir='+BX.util.urlencode(dir)+'&sessid=' + BX.bitrix_sessid(), updateCallback);
		}

		function verifyDomain(domain, dir, node)
		{
			node.innerHTML = '<?=CUtil::JSEscape(Loc::getMessage('SEO_LOADING'))?>';
			BX.ajax.loadJSON('/bitrix/tools/seo_google.php?action=site_verify&domain='+BX.util.urlencode(domain)+'&dir='+BX.util.urlencode(dir)+'&sessid=' + BX.bitrix_sessid(), updateCallback);
		}

		function updateInfo()
		{
			BX.ajax.loadJSON('/bitrix/tools/seo_google.php?action=sites_feed&sessid=' + BX.bitrix_sessid(), updateCallback);
		}

		function setAjaxStatus(text)
		{
			BX('ajax_status').innerHTML = text;
		}
	</script>

	<script>
		function makeNewAuth()
		{
			BX.showWait(BX('auth_result'));
			BX.ajax.loadJSON('/bitrix/tools/seo_google.php?action=nullify_auth&sessid=' + BX.bitrix_sessid(), function(){
				window.lastSeoResult = null;
				BX.closeWait(BX('auth_result'));
				BX('auth_result').style.display = 'none';
				BX('auth_button').style.display = 'block';
			});
		}
	</script>

	<script>
		updateInfo();
	</script>
<?php endif; ?>
<?=EndNote();?>

<?php
$lAdmin->DisplayList();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
