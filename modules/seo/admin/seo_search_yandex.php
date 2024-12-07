<?
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

define('ADMIN_MODULE_NAME', 'seo');

use Bitrix\Main;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Engine;

Loc::loadMessages(__DIR__.'/../../main/tools.php');
Loc::loadMessages(__DIR__.'/seo_search.php');

if (!$USER->CanDoOperation('seo_tools'))
{
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

if(!Main\Loader::includeModule('seo'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_NO_MODULE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

if(!Main\Loader::includeModule('socialservices'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_NO_MODULE_SOCSERV"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}


$strError = "";
$engine = new Engine\Yandex();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST['CODE']) && check_bitrix_sessid())
{
	try
	{
		$engine->getAuth($_REQUEST['CODE']);
		LocalRedirect($APPLICATION->GetCurPageParam('oauth=yes', array('CODE', 'oauth')));
	}
	catch (Exception $e)
	{
		$strError = Loc::getMessage('SEO_ERROR_GET_ACCESS', array("#ERROR_TEXT#" => $e->getMessage()));
	}
}

$sTableID = "tbl_seo_domains";
$oSort = new CAdminSorting($sTableID, "SORT", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$lAdmin->AddHeaders(array(
	array("id"=>"DOMAIN", "content"=>Loc::getMessage('SEO_DOMAIN'), "sort"=>"DOMAIN", "default"=>true),
	array("id"=>"SITE", "content"=>Loc::getMessage("SEO_SITE"), "default"=>true),
	array("id"=>"SITE_ACTIVE","content"=>Loc::getMessage('SEO_SITE_ACTIVE'), "sort"=>"active", "default"=>true),
	array("id"=>"BINDED", "content"=>Loc::getMessage("SEO_BINDED"), "default"=>true),
	array("id"=>"VERIFIED", "content"=>Loc::getMessage("SEO_VERIFIED"), "default"=>true),
));

$bNeedAuth = !$engine->getAuthSettings();

try
{
	$currentUser = $engine->getCurrentUser();
}
catch(Exception $e)
{
	$currentUser = null;
	$bNeedAuth = true;
}

$dbSites = new Bitrix\Main\DB\ArrayResult(\CSeoUtils::getDomainsList());
$rsData = new CAdminResult($dbSites, $sTableID);

while($arSite = $dbSites->fetch(Converter::getHtmlConverter()))
{
	$arSite['DOMAIN'] = mb_strtolower($arSite['DOMAIN']);

	$row =& $lAdmin->AddRow($arSite['DOMAIN'], $arSite);

	$siteDomainEnc = Converter::getHtmlConverter()->encode($arSite['DOMAIN']);
	$e = [];
	$siteDomainEncView = Converter::getHtmlConverter()->encode(\CBXPunycode::ToUnicode($arSite['DOMAIN'], $e));
	$siteDirEnc = Converter::getHtmlConverter()->encode($arSite['SITE_DIR']);

	$row->AddViewField("DOMAIN", '<a href="http://'.Converter::getHtmlConverter()->encode($arSite['DOMAIN'].CHTTP::urnEncode($arSite['SITE_DIR'])).'">'.$siteDomainEncView.$siteDirEnc.'</a>');
	$row->AddViewField("SITE", '[<a href="site_edit.php?lang='.LANGUAGE_ID.'&amp;LID='.urlencode($arSite['LID']).'">'.$arSite['LID'].'</a>] '.$arSite['SITE_NAME']);
	$row->AddCheckField("SITE_ACTIVE", false);

	$row->AddViewField("BINDED", '<span data-role="site-binded" data-domain="'.$siteDomainEnc.'" data-dir="'.$siteDirEnc.'">'.($bNeedAuth ? Loc::getMessage('SEO_NEED_AUTH') : Loc::getMessage('SEO_LOADING')).'</span>');
	$row->AddViewField("VERIFIED", '<span data-role="site-verified" data-domain="'.$siteDomainEnc.'" data-dir="'.$siteDirEnc.'">'.($bNeedAuth ? Loc::getMessage('SEO_NEED_AUTH') : Loc::getMessage('SEO_LOADING')).'</span>');

	if(!$bNeedAuth)
	{
		$arActions = Array();

		$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage('SEO_DETAIL'), "ACTION" => "getSiteInfo('".urlencode($arSite['DOMAIN'])."')", "DEFAULT"=>true);

		$row->AddActions($arActions);
	}
}

$lAdmin->CheckListMode();
$APPLICATION->SetTitle(Loc::getMessage("SEO_YANDEX_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($strError != '')
{
	CAdminMessage::ShowMessage($strError);
}

?>
<div id="ajax_status"></div>
<script>
	BX.message({'SEO_VERIFY_STATUS_NONE':'<?=Loc::getMessage('SEO_VERIFY_STATUS_NONE')?>'});
	BX.message({'SEO_VERIFY_STATUS_VERIFIED':'<?=Loc::getMessage('SEO_VERIFY_STATUS_VERIFIED')?>'});
	BX.message({'SEO_VERIFY_STATUS_IN_PROGRESS':'<?=Loc::getMessage('SEO_VERIFY_STATUS_IN_PROGRESS')?>'});
	BX.message({'SEO_VERIFY_STATUS_VERIFICATION_FAILED':'<?=Loc::getMessage('SEO_VERIFY_STATUS_VERIFICATION_FAILED')?>'});
	BX.message({'SEO_VERIFY_STATUS_INTERNAL_ERROR':'<?=Loc::getMessage('SEO_VERIFY_STATUS_INTERNAL_ERROR')?>'});
	
window.lastSeoResult = null;
function getSiteInfo(domain)
{
	if(window.lastSeoResult)
	{
		if(!window.lastSeoResult[domain])
		{
			alert('<?=CUtil::JSEscape(Loc::getMessage('SEO_ERROR_NO_BIND'))?>')
		}
		else
		{
			var wnd = new BX.CAdminDialog({
				content_url: "/bitrix/admin/seo_search_yandex_detail.php?lang="+BX.message('LANGUAGE_ID')+"&domain="+domain+"&bxpublic=Y"
			});
			wnd.Show();
		}
	}
}

function updateCallback(res)
{
	if(!!res && typeof res.error != 'undefined')
	{
		if(res.error.code == 'EXPIRED_TOKEN')
		{
			BX('auth_result').innerHTML = '<?=\CUtil::JSEscape(Loc::getMessage('SEO_ERROR_ACCESS_EXPIRED'))?> <a href="javascript:void(0)" onclick="makeNewAuth();"><?=\CUtil::JSEscape(Loc::getMessage('SEO_ERROR_ACCESS_EXPIRED_LINK'))?></a>';
		}
		else
		{
			alert(res.error.message);
		}
		return;
	}

	if(!window.lastSeoResult)
		window.lastSeoResult = {};

	for(var i in res)
	{
		lastSeoResult[i]=res[i];
	}

	BX.ready(function(){
		var nodes = BX.findChildren(
			BX('<?=CUtil::JSEscape($sTableID)?>'),
			{tag: 'span', attr: 'data-domain'},
			true
		);

		for(var i = 0; i < nodes.length; i++)
		{
			var role = nodes[i].getAttribute('data-role'),
				domain = nodes[i].getAttribute('data-domain');

			if(typeof res._domain != 'undefined' && res._domain != domain)
				continue;

			if(typeof res[domain] != 'undefined')
			{
				switch(role)
				{
					case 'site-binded':
						nodes[i].innerHTML = '<?=CUtil::JSEscape(Loc::getMessage('MAIN_YES'))?>';
					break;
					case 'site-verified':
						if(typeof res[domain].verification != 'undefined')
						{
							nodes[i].innerHTML = BX.message('SEO_VERIFY_STATUS_' + res[domain].verification);
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
	BX.ajax.loadJSON('/bitrix/tools/seo_yandex.php?action=site_add&domain='+BX.util.urlencode(domain)+'&dir='+BX.util.urlencode(dir)+'&sessid=' + BX.bitrix_sessid(), updateCallback);
}

function verifyDomain(domain, dir, node)
{
	node.innerHTML = '<?=CUtil::JSEscape(Loc::getMessage('SEO_LOADING'))?>';
	BX.ajax.loadJSON('/bitrix/tools/seo_yandex.php?action=site_verify&domain='+BX.util.urlencode(domain)+'&dir='+BX.util.urlencode(dir)+'&sessid=' + BX.bitrix_sessid(), updateCallback);
}


function updateInfo()
{
	BX.ajax.loadJSON('/bitrix/tools/seo_yandex.php?action=sites_feed&sessid=' + BX.bitrix_sessid(), updateCallback);
}

function setAjaxStatus(text)
{
	BX('ajax_status').innerHTML = text;
}
</script>
<?

// if(strlen($engine->getInterface()->getError()) > 0)
// {
// 	ShowError(Loc::getMessage('SEO_ERROR_GET_ACCESS', array("#ERROR_TEXT#" => $ob->getError())));
// }

$arYandexSites = array();
?>
<script>
function makeNewAuth()
{
	BX.showWait(BX('auth_result'));
	BX.ajax.loadJSON('/bitrix/tools/seo_yandex.php?action=nullify_auth&sessid=' + BX.bitrix_sessid(), function(){
		window.lastSeoResult = null;
		BX.closeWait(BX('auth_result'));
		BX('auth_result').style.display = 'none';
		BX('auth_button').style.display = 'block';
	});
}

function makeAuth()
{
	BX('auth_button').style.display = 'none';
	BX('auth_code').style.display = 'block';
	var wnd = BX.util.popup('<?=CUtil::JSEscape($engine->getAuthUrl())?>', 700, 500);
}
</script>
<?=BeginNote();?>
<div id="auth_button" style="display: <?=$bNeedAuth ? 'block' : 'none'?>">
	<p><?=Loc::getMessage('SEO_AUTH_HINT')?></p>
	<input type=button onclick="makeAuth()" value="<?=Loc::getMessage('SEO_AUTH_YANDEX')?>" />
</div>
<div id="auth_code" style="display: none;">
	<form name="auth_code_form" action="<?=Converter::getHtmlConverter()->encode($APPLICATION->getCurPageParam("", array("CODE", "oauth")))?>" method="POST"><?=bitrix_sessid_post();?><?=Loc::getMessage('SEO_AUTH_CODE')?>: <input type="text" name="CODE" style="width: 200px" /> <input type="submit" name="send_code" value="<?=Loc::getMessage('SEO_AUTH_CODE_SUBMIT')?>"></form></div>
<?
if(!$bNeedAuth)
{
	if(is_array($currentUser))
	{
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
?>
<script>updateInfo();</script>
<?
}
?>
<?=EndNote();?>
<?
$lAdmin->DisplayList();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>