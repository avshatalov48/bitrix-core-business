<?
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

define('ADMIN_MODULE_NAME', 'seo');

use Bitrix\Main;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\RobotsFile;

Loc::loadMessages(__FILE__);

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

$errors = '';

$siteId = isset($_REQUEST['site_id']) ? $_REQUEST['site_id'] : '';

$arCurrentSite = array();
$arDefaultSite = array();
$arSites = array();

$dbSites = Bitrix\Main\SiteTable::getList(
	array(
		'order' => array('DEF' => 'DESC', 'NAME' => 'ASC'),
		'select' => array('LID', 'NAME', 'DEF', 'DIR', 'DOC_ROOT', 'SERVER_NAME')
	)
);


while($arRes = $dbSites->fetch(Converter::getHtmlConverter()))
{
	if($arRes['DOC_ROOT'] == '')
	{
		$arRes['DOC_ROOT'] = Converter::getHtmlConverter()->encode(
			Main\SiteTable::getDocumentRoot($arRes['LID'])
		);
	}

	if($arRes['DEF'] == 'Y')
	{
		$arDefaultSite = $arRes;
	}

	$arSites[$arRes['LID']] = $arRes;
}

$arCurrentSite = isset($arSites[$siteId]) ? $arSites[$siteId] : $arDefaultSite;
$siteId = $arCurrentSite['LID'];

$arRobotsConfig = array(
	'common' => array(
		array('*', Loc::getMessage('SEO_ROBOTS_COMMON')),
	),

	'yandex' => array(
		array('Yandex', Loc::getMessage('SEO_ROBOTS_YANDEX')),
		array('YandexBot', Loc::getMessage('SEO_ROBOTS_YANDEXBOT')),
		array('YandexMedia', Loc::getMessage('SEO_ROBOTS_YANDEXMEDIA')),
		array('YandexImages', Loc::getMessage('SEO_ROBOTS_YANDEXIMAGES')),
		array('YandexBlogs', Loc::getMessage('SEO_ROBOTS_YANDEXBLOGS')),
		array('YandexNews', Loc::getMessage('SEO_ROBOTS_YANDEXNEWS')),
		array('YandexMetrika', Loc::getMessage('SEO_ROBOTS_YANDEXMETRIKA')),
		array('YandexMarket', Loc::getMessage('SEO_ROBOTS_YANDEXMARKET')),
	),

	'google' => array(
		array('Googlebot', Loc::getMessage('SEO_ROBOTS_GOOGLEBOT')),
		array('Googlebot-News', Loc::getMessage('SEO_ROBOTS_GOOGLEBOT_NEWS')),
		array('Googlebot-Image', Loc::getMessage('SEO_ROBOTS_GOOGLEBOT_IMAGE')),
		array('Googlebot-Video', Loc::getMessage('SEO_ROBOTS_GOOGLEBOT_VIDEO')),
		array('Googlebot-Mobile', Loc::getMessage('SEO_ROBOTS_GOOGLEBOT_MOBILE')),
	),
);

$bVendor = COption::GetOptionString('main', 'vendor', '') == '1c_bitrix';

if (!$bVendor)
{
	unset($arRobotsConfig['yandex']);
}

$aTabs = array();

foreach ($arRobotsConfig as $key => $arConfig)
{
	$aTabs[] = array("DIV" => "seo_robots_".$key, "TAB" => Loc::getMessage('SEO_ROBOTS_'.$key), "TITLE" => Loc::getMessage('SEO_ROBOTS_TITLE_'.$key));
}

$aTabs[] = array("DIV" => "seo_robots_edit", "TAB" => Loc::getMessage('SEO_ROBOTS_EDIT'), "TITLE" => Loc::getMessage('SEO_ROBOTS_TITLE_EDIT'), 'ONSELECT' => 'seoParser.compile();');

$tabControl = new \CAdminTabControl("seoRobotsTabControl", $aTabs, true, true);

$robotsFile = new RobotsFile($siteId);

if($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid() && strlen($_POST["save"]) > 0)
{
	$robotsFile->putContents($_REQUEST['ROBOTS']);
	LocalRedirect(BX_ROOT."/admin/seo_robots.php?lang=".LANGUAGE_ID.'&site_id='.$siteId."&".$tabControl->ActiveTabParam());
}

$hostName = $arCurrentSite['SERVER_NAME'];
if(strlen($hostName) <= 0)
{
	$hostName = COption::GetOptionString('main', 'server_name', '');
}

CJSCore::RegisterExt('seo_robots', array(
	'js' => '/bitrix/js/seo/robots.js',
	'css' => '/bitrix/panel/seo/robots.css',
	'lang' =>  BX_ROOT.'/modules/seo/lang/'.LANGUAGE_ID.'/js_robots.php',
	'lang_additional' => array('SEO_HOST' => $hostName, 'SEO_SITE_ID' => $siteId),
));

$APPLICATION->addHeadScript('/bitrix/js/main/utils.js');
$APPLICATION->addHeadScript('/bitrix/js/main/file_dialog.js');

CJSCore::Init('seo_robots');

$APPLICATION->SetTitle(Loc::getMessage("SEO_ROBOTS_EDIT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array();

$arDDMenu = array();

$arDDMenu[] = array(
	"HTML" => "<b>".Loc::getMessage("SEO_ROBOTS_CHOOSE_SITE")."</b>",
	"ACTION" => false
);

foreach($arSites as $arRes)
{
	$arDDMenu[] = array(
		"HTML" => "[".$arRes["LID"]."] ".$arRes["NAME"],
		"LINK" => "seo_robots.php?lang=".LANGUAGE_ID."&site_id=".$arRes['LID']
	);
}

$aContext = array();
$aContext[] = array(
	"TEXT"	=> $arCurrentSite['NAME'],
	"MENU" => $arDDMenu
);

$context = new CAdminContextMenu($aContext);
$context->Show();

if(!empty($errors))
{
	CAdminMessage::ShowMessage(join("\n", $errors));
}
?>
<form method="POST" action="<?=POST_FORM_ACTION_URI?>" name="robots_form" onsubmit="window.seoParser.compile();">
	<input type="hidden" name="site_id" value="<?=$siteId?>">
<?
$fileContent = '';
if($robotsFile->isExists())
{
	$fileContent = $robotsFile->getContents();
}
else
{
	$msg = new CAdminMessage(array(
		'TYPE' => 'ERROR',
		'MESSAGE' => Loc::getMessage('SEO_ROBOTS_ERROR_NO_ROBOTS', array('#PATH#' => $robotsFile->getPath())),
		'DETAILS' => Loc::getMessage('SEO_ROBOTS_ERROR_NO_ROBOTS_MESSAGE'),
	));
	echo $msg->Show();
}
?>
<script>
window.seoParser = new BX.seoParser('<?=CUtil::JSEscape($fileContent)?>', 'robots_text');
</script>
<?
$tabControl->Begin();

foreach($arRobotsConfig as $key => $arConfig)
{
	$tabControl->BeginNextTab();
?>
<tr>
	<td colspan="2" align="center">
<?
	$arSubTabs = array();
	foreach($arConfig as $i => $arAgent)
	{
		$arSubTabs[] = array('DIV' => 'subtab_'.$key.'_'.$i, 'TAB' => $arAgent[0] == '*' ? $arAgent[1] : $arAgent[0], 'TITLE' => $arAgent[0] == '*' ? $arAgent[1] : '');
	}

	$childTabControl = new CAdminViewTabControl("childTabControl_".$key, $arSubTabs);

	$childTabControl->Begin();
	foreach($arSubTabs as $i => $tab)
	{
		$userAgent = $arConfig[$i][0];

		if($childTabControl->tabs[$i]['TITLE'] == '')
		{
			$childTabControl->tabs[$i]['TITLE'] = $arConfig[$i][1];
		}

		$childTabControl->BeginNextTab();
?>
<table width="70%">
	<tr>
		<td width="70%" valign="top"><pre id="rules_<?=$key?>_<?=$i?>" class="seo-robots-content" style="text-align: left;"></pre></td>
		<td width="30%" valign="top"><div id="rules_buttons_<?=$key?>_<?=$i?>"></div></td>
	</tr>
</table>
<script>
window.seoParser.registerEditor(new BX.seoEditor({
	service: '<?=$key?>',
	userAgent: '<?=CUtil::JSEscape($userAgent)?>',
	cont: {
		rules: 'rules_<?=$key?>_<?=$i?>',
		buttons: 'rules_buttons_<?=$key?>_<?=$i?>'
	}
}));
</script>
<?
	}
	$childTabControl->End();
?>
	</td>
</tr>
<tr>
	<td colspan="2"><?echo BeginNote().Loc::getMessage('SEO_ROBOTS_HINT'.(!$bVendor ? '' : '_1C')).EndNote();
?></td>
</tr>

<?
}

$tabControl->BeginNextTab();
?>
<tr>
	<td colspan="2">
		<b><?=$robotsFile->getPath();?></b><br /><br />
		<textarea style="width: 100%" rows="30" id="robots_text" name="ROBOTS[TEXT]"><?=Converter::getHtmlConverter()->encode($fileContent)?></textarea>
	</td>
</tr>
<tr>
	<td colspan="2"><?echo BeginNote().Loc::getMessage('SEO_ROBOTS_HINT'.(!$bVendor ? '' : '_1C')).EndNote();
?></td>
</tr>

<?
$tabControl->Buttons(array('btnApply' => false));
?>
<?=bitrix_sessid_post();?>
<?
$tabControl->End();
?>
</form>
<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>