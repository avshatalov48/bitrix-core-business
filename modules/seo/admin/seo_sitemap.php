<?
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

define('ADMIN_MODULE_NAME', 'seo');

use Bitrix\Main;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\SitemapTable;
use Bitrix\Seo\SitemapRuntime;
use Bitrix\Seo\SitemapRuntimeTable;

Loc::loadMessages(dirname(__FILE__).'/seo_sitemap.php');

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

$tableID = "tbl_sitemap";

$oSort = new CAdminSorting($tableID, "ID", "desc");
$adminList = new CAdminList($tableID, $oSort);

if(($arID = $adminList->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$rsData = SitemapTable::getList(array(
			"select" => array("ID"),
		));

		while($arRes = $rsData->fetch())
		{
			$arID[] = $arRes['ID'];
		}
	}

	foreach($arID as $ID)
	{
		$ID = intval($ID);
		if($ID <= 0)
			continue;

		switch($_REQUEST['action'])
		{
			case "delete":
				SitemapRuntimeTable::clearByPid($ID);
				SitemapTable::delete($ID);
			break;
		}
	}
}

$dbSites = Bitrix\Main\SiteTable::getList(
	array(
		'order' => array('DEF' => 'DESC', 'NAME' => 'ASC'),
		'select' => array('NAME', 'LID')
	)
);

$arSites = array();
while($arRes = $dbSites->fetch(Converter::getHtmlConverter()))
{
	$arSites[$arRes['LID']] = $arRes;
}

$map = SitemapTable::getMap();
unset($map['SETTINGS']);

$sitemapList = SitemapTable::getList(array(
	'order' => array($by => $order),
	"select" => array_keys($map),
));
$data = new CAdminResult($sitemapList, $tableID);
$data->NavStart();

$arHeaders = array(
	array("id"=>"ID", "content"=>Loc::getMessage("SITEMAP_ID"), "sort"=>"ID", "default"=>true),
	array("id"=>"TIMESTAMP_X", "content"=>Loc::getMessage('SITEMAP_TIMESTAMP_X'), "sort"=>"TIMESTAMP_X", "default"=>true),
	array("id"=>"NAME", "content"=>Loc::getMessage('SITEMAP_NAME'), "sort"=>"NAME", "default"=>true),
//	array("id"=>"ACTIVE", "content"=>Loc::getMessage('SITEMAP_ACTIVE'), "sort"=>"ACTIVE", "default"=>true, "align" => "center"),
	array("id"=>"SITE_ID", "content"=>Loc::getMessage('SITEMAP_SITE_ID'), "sort"=>"SITE_ID", "default"=>true),
	array("id"=>"DATE_RUN", "content"=>Loc::getMessage('SITEMAP_DATE_RUN'), "sort"=>"DATE_RUN", "default"=>true),
	array("id"=>"RUN", "content"=>"", "default"=>true),
);

$adminList->AddHeaders($arHeaders);
$adminList->NavText($data->GetNavPrint(Loc::getMessage("PAGES")));
while($sitemap = $data->NavNext())
{
	$id = intval($sitemap['ID']);

	$row = &$adminList->AddRow($sitemap["ID"], $sitemap, "seo_sitemap_edit.php?ID=".$sitemap["ID"]."&lang=".LANGUAGE_ID, Loc::getMessage("SITEMAP_EDIT_TITLE"));

	$row->AddViewField("ID", $sitemap['ID']);
	$row->AddViewField('TIMESTAMP_X', $sitemap['TIMESTAMP_X']);
	$row->AddViewField('DATE_RUN', $sitemap['DATE_RUN'] ? $sitemap['DATE_RUN'] : Loc::getMessage('SITEMAP_DATE_RUN_NEVER'));
	$row->AddViewField('SITE_ID', '<a href="site_edit.php?lang='.LANGUAGE_ID.'&amp;LID='.$sitemap['SITE_ID'].'">['.$sitemap['SITE_ID'].'] '.$arSites[$sitemap['SITE_ID']]['NAME'].'</a>');

	$row->AddField("NAME", '<a href="seo_sitemap_edit.php?ID='.$sitemap["ID"].'&amp;lang='.LANGUAGE_ID.'" title="'.Loc::getMessage("SITEMAP_EDIT_TITLE").'">'.Converter::getHtmlConverter()->encode($sitemap['NAME']).'</a>');
	$row->AddField("RUN", '<input type="button" class="adm-btn-save" value="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SITEMAP_RUN')).'" onclick="generateSitemap('.$sitemap['ID'].')" name="save" id="sitemap_run_button_'.$sitemap['ID'].'" />');

	//$row->AddInputField("NAME");
	//$row->AddCheckField("ACTIVE");

	$row->AddActions(array(
		array(
			"ICON" => "edit",
			"TEXT" => Loc::getMessage("SITEMAP_EDIT"),
			"ACTION" => $adminList->ActionRedirect("seo_sitemap_edit.php?ID=".$sitemap["ID"]."&lang=".LANGUAGE_ID),
			"DEFAULT" => true,
		),
		array(
			"ICON" => "move",
			"TEXT" => Loc::getMessage("SITEMAP_RUN"),
			"ACTION" => 'generateSitemap('.$sitemap['ID'].');',
		),
		array(
			"ICON"=>"delete",
			"TEXT" => Loc::getMessage("SITEMAP_DELETE"),
			"ACTION" => "if(confirm('".\CUtil::JSEscape(Loc::getMessage('SITEMAP_DELETE_CONFIRM'))."')) ".$adminList->ActionDoGroup($id, "delete")
		),
	));
}

$arDDMenu = array();

$arDDMenu[] = array(
	"HTML" => "<b>".Loc::getMessage("SEO_ADD_SITEMAP_CHOOSE_SITE")."</b>",
	"ACTION" => false
);

foreach($arSites as $arRes)
{
	$arDDMenu[] = array(
		"HTML" => "[".$arRes["LID"]."] ".$arRes["NAME"],
		"LINK" => "seo_sitemap_edit.php?lang=".LANGUAGE_ID."&site_id=".$arRes['LID']
	);
}

$aContext = array();
$aContext[] = array(
	"TEXT"	=> Loc::getMessage("SEO_ADD_SITEMAP"),
	"TITLE"	=> Loc::getMessage("SEO_ADD_SITEMAP_TITLE"),
	"ICON"	=> "btn_new",
	"MENU" => $arDDMenu
);

$adminList->AddAdminContextMenu($aContext);
$adminList->AddGroupActionTable(array("delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE")));

$adminList->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("SEO_SITEMAP_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminList->DisplayList();

?>

<script>
function generateSitemap(ID)
{
	var node = BX('sitemap_run');

	node.style.display = 'block';

	var windowPos = BX.GetWindowSize();
	var pos = BX.pos(node);

	if(pos.top > windowPos.scrollTop + windowPos.innerHeight)
	{
		window.scrollTo(windowPos.scrollLeft, pos.top + 150 - windowPos.innerHeight);
	}

	BX.runSitemap(ID, 0, '', '');
}

BX.runSitemap = function(ID, value, pid, NS)
{
	BX.adminPanel.showWait(BX('sitemap_run_button_' + ID));
	BX.ajax.post('/bitrix/admin/seo_sitemap_run.php', {
		lang:'<?=LANGUAGE_ID?>',
		action: 'sitemap_run',
		ID: ID,
		value: value,
		pid: pid,
		NS: NS,
		sessid: BX.bitrix_sessid()
	}, function(data)
	{
		BX.adminPanel.closeWait(BX('sitemap_run_button_' + ID));
		BX('sitemap_progress').innerHTML = data;
	});
};

BX.finishSitemap = function()
{
	window.tbl_sitemap.GetAdminList('/bitrix/admin/seo_sitemap.php?lang=<?=LANGUAGE_ID?>');
};
</script>

<div id="sitemap_run" style="display: none;">
	<div id="sitemap_progress"><?=SitemapRuntime::showProgress(Loc::getMessage('SEO_SITEMAP_RUN_INIT'), Loc::getMessage('SEO_SITEMAP_RUN_TITLE'), 0)?></div>
</div>
<?
if(isset($_REQUEST['run']) && check_bitrix_sessid())
{
	$ID = intval($_REQUEST['run']);
	if($ID > 0)
	{
?>
<script>BX.ready(BX.defer(function(){
	generateSitemap(<?=$ID?>);
}));
</script>
<?
	}
}
?>
<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>