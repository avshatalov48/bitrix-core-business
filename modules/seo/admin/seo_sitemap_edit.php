<?
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

define('ADMIN_MODULE_NAME', 'seo');

use Bitrix\Main;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\RobotsFile;
use Bitrix\Seo\SitemapTable;
use Bitrix\Seo\SitemapEntityTable;
use Bitrix\Seo\SitemapIblockTable;
use Bitrix\Seo\SitemapForumTable;
use Bitrix\Seo\SitemapRuntimeTable;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(dirname(__FILE__).'/../../main/tools.php');
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

$bIBlock = Main\Loader::includeModule('iblock');
$bForum = Main\Loader::includeModule('forum');

$mapId = intval($_REQUEST['ID']);
$siteId = htmlspecialcharsbx(trim($_REQUEST['site_id']));

$bDefaultHttps = false;

if($mapId > 0)
{
	$dbSitemap = SitemapTable::getById($mapId);
	$arSitemap = $dbSitemap->fetch();

	if(!is_array($arSitemap))
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		ShowError(Loc::getMessage("SEO_ERROR_SITEMAP_NOT_FOUND"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	}
	else
	{
		if($_REQUEST['action'] == 'delete' && check_bitrix_sessid())
		{
			SitemapRuntimeTable::clearByPid($mapId);
			SitemapTable::delete($mapId);
			LocalRedirect(BX_ROOT."/admin/seo_sitemap.php?lang=".LANGUAGE_ID);
		}

		$arSitemap['SETTINGS'] = unserialize($arSitemap['SETTINGS']);

		$arSitemap['SETTINGS']['IBLOCK_AUTO'] = array();
		$dbRes = SitemapIblockTable::getList(array(
			"filter" => array("SITEMAP_ID" => $mapId),
			"select" => array("IBLOCK_ID"),
		));

		while($arRes = $dbRes->fetch())
		{
			$arSitemap['SETTINGS']['IBLOCK_AUTO'][$arRes['IBLOCK_ID']] = 'Y';
		}

		$dbRes = SitemapEntityTable::getList(array(
			"filter" => array("SITEMAP_ID" => $mapId),
		));
		while($arRes = $dbRes->fetch())
		{
			if (!is_array($arSitemap['SETTINGS'][$arRes["ENTITY_TYPE"].'_AUTO']))
				$arSitemap['SETTINGS'][$arRes["ENTITY_TYPE"].'_AUTO'] = array();
			$arSitemap['SETTINGS'][$arRes["ENTITY_TYPE"].'_AUTO'][$arRes['ENTITY_ID']] = 'Y';
		}
		if (empty($arSitemap['SETTINGS']['FILENAME_FORUM']))
			$arSitemap['SETTINGS']['FILENAME_FORUM'] = "sitemap_forum_#FORUM_ID#.xml";

		$siteId = $arSitemap['SITE_ID'];
	}
}

if($siteId <> '')
{
	$dbSite = Main\SiteTable::getByPrimary($siteId);
	$arSite = $dbSite->fetch();
	if(!is_array($arSite))
	{
		$siteId = '';
	}
	else
	{
		$siteId = $arSite['LID'];
		$arSite['DOMAINS'] = array();

		$robotsFile = new RobotsFile($siteId);
		if($robotsFile->isExists())
		{
			$arHostsList = $robotsFile->getRules('Host');
			foreach ($arHostsList as $rule)
			{
				$host = $rule[1];
				if(strncmp($host, 'https://', 8) === 0)
				{
					$host = mb_substr($host, 8);
					$bDefaultHttps = true;
				}
				$arSite['DOMAINS'][] = $host;
			}
		}

		if($arSite['SERVER_NAME'] != '')
			$arSite['DOMAINS'][] = $arSite['SERVER_NAME'];

		$dbDomains = Bitrix\Main\SiteDomainTable::getList(
			array(
				'filter' => array('LID' => $siteId),
				'select'=>array('DOMAIN')
			)
		);
		while($arDomain = $dbDomains->fetch())
		{
			$arSite['DOMAINS'][] = $arDomain['DOMAIN'];
		}
		$arSite['DOMAINS'][] = \Bitrix\Main\Config\Option::get('main', 'server_name', '');
		$arSite['DOMAINS'] = array_unique($arSite['DOMAINS']);
	}
}

if($siteId == '')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_SITEMAP_NO_SITE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

$aTabs = array(
	array("DIV" => "seo_sitemap_common", "TAB" => Loc::getMessage('SEO_SITEMAP_COMMON'), "ICON" => "main_settings", "TITLE" => Loc::getMessage('SEO_SITEMAP_COMMON_TITLE')),
	array("DIV" => "seo_sitemap_files", "TAB" => Loc::getMessage('SEO_SITEMAP_FILES'), "ICON" => "main_settings", "TITLE" => Loc::getMessage('SEO_SITEMAP_FILES_TITLE')),
);
if($bIBlock)
{
	$aTabs[] = array("DIV" => "seo_sitemap_iblock", "TAB" => Loc::getMessage('SEO_SITEMAP_IBLOCK'), "ICON" => "main_settings", "TITLE" => Loc::getMessage('SEO_SITEMAP_IBLOCK_TITLE'));
}
if($bForum)
{
	$aTabs[] = array("DIV" => "seo_sitemap_forum", "TAB" => Loc::getMessage('SEO_SITEMAP_FORUM'), "ICON" => "main_settings", "TITLE" => Loc::getMessage('SEO_SITEMAP_FORUM_TITLE'));
}

$tabControl = new \CAdminTabControl("seoSitemapTabControl", $aTabs, true, true);

$errors = array();

if($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid() && ($_POST["save"] <> '' || $_POST['apply'] <> '' || $_POST['save_and_add'] <> ''))
{
	$fileNameIndex = trim($_REQUEST['FILENAME_INDEX']);
	$fileNameFiles = trim($_REQUEST['FILENAME_FILES']);
	$fileNameForum = trim($_REQUEST['FILENAME_FORUM']);
	$fileNameIblock = trim($_REQUEST['FILENAME_IBLOCK']);

	if($fileNameIndex == '')
	{
		$errors[] = Loc::getMessage('SEO_ERROR_SITEMAP_NO_VALUE', array('#FIELD#' => Loc::getMessage('SITEMAP_FILENAME_ADDRESS')));
	}
	if($fileNameFiles == '')
	{
		$errors[] = Loc::getMessage('SEO_ERROR_SITEMAP_NO_VALUE', array('#FIELD#' => Loc::getMessage('SITEMAP_FILENAME_FILE')));
	}
	if($bIBlock && $fileNameIblock == '')
	{
		$errors[] = Loc::getMessage('SEO_ERROR_SITEMAP_NO_VALUE', array('#FIELD#' => Loc::getMessage('SITEMAP_FILENAME_IBLOCK')));
	}
	if($bForum && $fileNameForum == '')
	{
		$errors[] = Loc::getMessage('SEO_ERROR_SITEMAP_NO_VALUE', array('#FIELD#' => Loc::getMessage('SITEMAP_FILENAME_FORUM')));
	}

	if(empty($errors))
	{
		$arSitemapSettings = SitemapTable::prepareSettings(array(
			'FILE_MASK' => trim($_REQUEST['FILE_MASK']),
			'ROBOTS' => $_REQUEST['ROBOTS'] == 'N' ? 'N' : 'Y',
			'logical' => $_REQUEST['log'] == 'N' ? 'N' : 'Y',
			'DIR' => $_REQUEST['DIR'],
			'FILE' => $_REQUEST['FILE'],
			'PROTO' => $_REQUEST['PROTO'],
			'DOMAIN' => $_REQUEST['DOMAIN'],
			'FILENAME_INDEX' => $fileNameIndex,
			'FILENAME_FILES' => $fileNameFiles,
			'FILENAME_IBLOCK' => $fileNameIblock,
			'FILENAME_FORUM' => $fileNameForum,
			'IBLOCK_ACTIVE' => $_REQUEST['IBLOCK_ACTIVE'],
			'IBLOCK_LIST' => $_REQUEST['IBLOCK_LIST'],
			'IBLOCK_SECTION' => $_REQUEST['IBLOCK_SECTION'],
			'IBLOCK_ELEMENT' => $_REQUEST['IBLOCK_ELEMENT'],
			'IBLOCK_SECTION_SECTION' => $_REQUEST['IBLOCK_SECTION_SECTION'],
			'IBLOCK_SECTION_ELEMENT' => $_REQUEST['IBLOCK_SECTION_ELEMENT'],
			'FORUM_ACTIVE' => $_REQUEST['FORUM_ACTIVE'],
			'FORUM_TOPIC' => $_REQUEST['FORUM_TOPIC'],
		));

		$arSiteMapFields = array(
			'NAME' => trim($_REQUEST['NAME']),
			'ACTIVE' => $_REQUEST['ACTIVE'] == 'N' ? 'N' : 'Y',
			'SITE_ID' => $siteId,
			'SETTINGS' => serialize($arSitemapSettings),
		);

		if($mapId > 0)
		{
			$result = SitemapTable::update($mapId, $arSiteMapFields);
		}
		else
		{
			$result = SitemapTable::add($arSiteMapFields);
			$mapId = $result->getId();
		}

		if($result->isSuccess())
		{
			$arSitemapIblock = array();

			SitemapIblockTable::clearBySitemap($mapId);

			if(is_array($_REQUEST['IBLOCK_AUTO']))
			{
				foreach($_REQUEST['IBLOCK_AUTO'] as $iblockId => $auto)
				{
					if($auto === 'Y')
					{
						$result = SitemapIblockTable::add(array(
							'SITEMAP_ID' => $mapId,
							'IBLOCK_ID' => intval($iblockId),
						));
					}
				}
			}

			SitemapForumTable::clearBySitemap($mapId);

			if(is_array($_REQUEST['FORUM_AUTO']))
			{
				foreach($_REQUEST['FORUM_AUTO'] as $forumId => $auto)
				{
					if($auto === 'Y')
					{
						$result = SitemapForumTable::add(array('SITEMAP_ID' => $mapId, 'ENTITY_ID' => $forumId));
					}
				}
			}

			if($_REQUEST["save"] <> '')
			{
				LocalRedirect(BX_ROOT."/admin/seo_sitemap.php?lang=".LANGUAGE_ID);
			}
			elseif($_REQUEST["save_and_add"] <> '')
			{
				LocalRedirect(BX_ROOT."/admin/seo_sitemap.php?lang=".LANGUAGE_ID."&run=".$mapId."&".bitrix_sessid_get());
			}
			else
			{
				LocalRedirect(BX_ROOT."/admin/seo_sitemap_edit.php?lang=".LANGUAGE_ID."&ID=".$mapId."&".$tabControl->ActiveTabParam());
			}
		}
		else
		{
			$errors = $result->getErrorMessages();
		}
	}
}

function seo_getDir($bLogical, $site_id, $dir, $depth, $checked, $arChecked = array())
{
	if(!is_array($arChecked))
		$arChecked = array();

	$arDirs = \CSeoUtils::getDirStructure($bLogical, $site_id, $dir);
	if(count($arDirs) > 0)
	{
		foreach ($arDirs as $arDir)
		{
			$d = Main\IO\Path::combine($dir,$arDir['FILE']);

			$bChecked = $arChecked[$d] === 'Y' || $checked && $arChecked[$d] !== 'N';

			$d = Converter::getHtmlConverter()->encode($d);
			$r = RandString(8);

			$varName = $arDir['TYPE'] == 'D' ? 'DIR' : 'FILE';
?>
<div class="sitemap-dir-item">
<?
			if($arDir['TYPE']=='D'):
?>
	<span onclick="loadDir(<?=$bLogical?'true':'false'?>, this, '<?=CUtil::JSEscape($d)?>', '<?=$r?>', '<?=$depth+1?>', BX('DIR_<?=$d?>').checked)" class="sitemap-tree-icon"></span><?
			endif;
?><span class="sitemap-dir-item-text">
		<input type="hidden" name="<?=$varName?>[<?=$d?>]" value="N" />
		<input type="checkbox" name="<?=$varName?>[<?=$d?>]" id="DIR_<?=$d?>"<?=$bChecked ? ' checked="checked"' : ''?> value="Y" onclick="checkAll('<?=$r?>', this.checked);" />
		<label for="DIR_<?=$d?>"><?=Converter::getHtmlConverter()->encode($arDir['NAME'].($bLogical ? (' ('.$arDir['FILE'].')') : ''))?></label>
	</span>
	<div id="subdirs_<?=$r?>" class="sitemap-dir-item-children"></div>
</div>
<?
		}
	}
	else
	{
		echo $space.Loc::getMessage('SEO_SITEMAP_NO_DIRS_FOUND');
	}
}

function seo_getIblock($iblockId, $sectionId, $sectionChecked, $elementChecked, $arSectionChecked = array(), $arElementChecked = array())
{
	$dbIblock = \CIBlock::GetByID($iblockId);
	$arIBlock = $dbIblock->Fetch();
	if(is_array($arIBlock))
	{
		$bSection = $arIBlock['SECTION_PAGE_URL'] <> '';
		$bElement = $arIBlock['DETAIL_PAGE_URL'] <> '';

		$dbRes = \CIBlockSection::GetList(
			array('SORT' => 'ASC', 'NAME' => 'ASC'),
			array(
				'IBLOCK_ID' => $iblockId,
				'SECTION_ID' => $sectionId,
				'ACTIVE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y'
			)
		);
		$bFound = false;
		while ($arRes = $dbRes->Fetch())
		{
			$r = RandString(8);
			$d = $arRes['ID'];

			$bSectionChecked = $bSection && ($arSectionChecked[$d] === 'Y' || $sectionChecked && $arSectionChecked[$d] !== 'N');
			$bElementChecked = $bElement && ($arElementChecked[$d] === 'Y' || $elementChecked && $arElementChecked[$d] !== 'N');


			if(!$bFound)
			{
				$bFound = true;
?>
<table class="internal" style="width: 100%;">
	<tr class="heading">
		<td colspan="2"><?=Loc::getMessage('SEO_SITEMAP_IBLOCK_SECTION_NAME')?></td>
		<td width="100"><?=Loc::getMessage('SEO_SITEMAP_IBLOCK_SECTION_SECTION')?></td>
		<td width="100"><?=Loc::getMessage('SEO_SITEMAP_IBLOCK_SECTION_ELEMENTS')?></td>
	</tr>
<?
			}
?>
	<tr>
		<td width="20"><span onclick="loadIblock(this, '<?=$arRes['IBLOCK_ID']?>', '<?=$d?>', '<?=$r?>', BX('IBLOCK_SECTION_SECTION_<?=$d?>').checked, BX('IBLOCK_SECTION_ELEMENT_<?=$d?>').checked);" class="sitemap-tree-icon-iblock"></span></td>
		<td><a href="iblock_list_admin.php?lang=<?=LANGUAGE_ID?>&amp;IBLOCK_ID=<?=$arRes['IBLOCK_ID']?>&amp;find_section_section=<?=$d?>"><?=Converter::getHtmlConverter()->encode($arRes['NAME'])?></a></td>
		<td align="center"><input type="hidden" name="IBLOCK_SECTION_SECTION[<?=$iblockId?>][<?=$d?>]" value="N" /><input type="checkbox" name="IBLOCK_SECTION_SECTION[<?=$iblockId?>][<?=$d?>]" id="IBLOCK_SECTION_SECTION_<?=$d?>" value="Y"<?=$bSection?'':' disabled="disabled"'?><?=$bSectionChecked?' checked="checked"':''?> data-type="section" onclick="checkAllSection('<?=$r?>', this.checked);" />&nbsp;<label for="IBLOCK_SECTION_SECTION_<?=$d?>"><?=Loc::getMessage('MAIN_YES')?></label></td>
		<td align="center"><input type="hidden" name="IBLOCK_SECTION_ELEMENT[<?=$iblockId?>][<?=$d?>]" value="N" /><input type="checkbox" name="IBLOCK_SECTION_ELEMENT[<?=$iblockId?>][<?=$d?>]" id="IBLOCK_SECTION_ELEMENT_<?=$d?>" value="Y"<?=$bElement?'':' disabled="disabled"'?><?=$bElementChecked?' checked="checked"':''?> data-type="element" onclick="checkAllElement('<?=$r?>', this.checked);" />&nbsp;<label for="IBLOCK_SECTION_ELEMENT_<?=$d?>"><?=Loc::getMessage('MAIN_YES')?></label></td>
	</tr>
	<tr style="display: none" id="subdirs_row_<?=$r?>">
		<td colspan="4" id="subdirs_<?=$r?>" align="center"></td>
	</tr>
<?
		}

		if(!$bFound)
		{
			echo Loc::getMessage('SEO_SITEMAP_NO_DIRS_FOUND');
		}
	}
}

// load directory structure
if(isset($_REQUEST['dir']) && check_bitrix_sessid())
{
	$bLogical = $_REQUEST['log'] == 'Y';
	$dir = $_REQUEST['dir'];
	$depth = intval($_REQUEST['depth']);
	$checked = $_REQUEST['checked'] == 'Y';

	$APPLICATION->RestartBuffer();

	if(!is_array($arSitemap['SETTINGS']['DIR']))
		$arSitemap['SETTINGS']['DIR'] = array();
	if(!is_array($arSitemap['SETTINGS']['FILE']))
		$arSitemap['SETTINGS']['FILE'] = array();

	$arChecked = array_merge($arSitemap['SETTINGS']['DIR'], $arSitemap['SETTINGS']['FILE']);

	echo seo_getDir($bLogical, $siteId, $dir, $depth, $checked, $arChecked);
	die();
}

// load iblock structure
if($bIBlock && isset($_REQUEST['iblock']) && check_bitrix_sessid())
{
	$iblock = intval($_REQUEST['iblock']);
	$section = intval($_REQUEST['section']);
	$sectionChecked = $_REQUEST['section_checked'] == 'Y';
	$elementChecked = $_REQUEST['element_checked'] == 'Y';

	$APPLICATION->RestartBuffer();

	if(is_array($arSitemap['SETTINGS']['IBLOCK_SECTION_SECTION'][$iblock]) || is_array($arSitemap['SETTINGS']['IBLOCK_SECTION_ELEMENT'][$iblock]))
	{
		echo seo_getIblock($iblock, $section, $sectionChecked, $elementChecked, $arSitemap['SETTINGS']['IBLOCK_SECTION_SECTION'][$iblock], $arSitemap['SETTINGS']['IBLOCK_SECTION_ELEMENT'][$iblock]);
	}
	else
	{
		echo seo_getIblock($iblock, $section, $sectionChecked, $elementChecked);
	}
	die();
}

if($mapId <= 0)
{
	$arSitemap = array(
		"NAME" => Loc::getMessage('SITEMAP_NAME_DEFAULT', array("#DATE#" => ConvertTimeStamp())),
		"ACTIVE" => "Y",
		"DATE_RUN" => "",
		"SETTINGS" => array(
			"ROBOTS" => "Y",
			"PROTO" => $bDefaultHttps ? 1 : 0,
			"FILE_MASK" => SitemapTable::SETTINGS_DEFAULT_FILE_MASK,
			"logical" => 'Y',
			"FILENAME_INDEX" => "sitemap.xml",
			"FILENAME_FILES" => "sitemap_files.xml",
			"FILENAME_IBLOCK" => "sitemap_iblock_#IBLOCK_ID#.xml",
			"FILENAME_FORUM" => "sitemap_forum_#FORUM_ID#.xml"
		)
	);
}

if(!empty($errors))
{
	$arSitemap["NAME"] = $_REQUEST['NAME'];
	$arSitemap["SETTINGS"]["ROBOTS"] = $_REQUEST['ROBOTS'] == 'N' ? 'N' : 'Y';
	$arSitemap["SETTINGS"]["PROTO"] = $_REQUEST['PROTO'];
	$arSitemap["SETTINGS"]["DOMAIN"] = $_REQUEST['DOMAIN'];
	$arSitemap["SETTINGS"]["FILE_MASK"] = trim($_REQUEST['FILE_MASK']);
	$arSitemap["SETTINGS"]["logical"] = $_REQUEST['log'] == 'N' ? 'N' : 'Y';
	$arSitemap["SETTINGS"]["FILENAME_INDEX"] = trim($_REQUEST['FILENAME_INDEX']);
	$arSitemap["SETTINGS"]["FILENAME_FILES"] = trim($_REQUEST['FILENAME_FILES']);
	$arSitemap["SETTINGS"]["FILENAME_IBLOCK"] = trim($_REQUEST['FILENAME_IBLOCK']);
	$arSitemap["SETTINGS"]["FILENAME_FORUM"] = trim($_REQUEST['FILENAME_FORUM']);
	$arSitemap["SETTINGS"]["DIR"] = $_REQUEST['DIR'];
	$arSitemap["SETTINGS"]["FILE"] = $_REQUEST['FILE'];
	$arSitemap["SETTINGS"]["IBLOCK_ACTIVE"] = $_REQUEST['IBLOCK_ACTIVE'];
	$arSitemap["SETTINGS"]["IBLOCK_LIST"] = $_REQUEST['IBLOCK_LIST'];
	$arSitemap["SETTINGS"]["IBLOCK_SECTION"] = $_REQUEST['IBLOCK_SECTION'];
	$arSitemap["SETTINGS"]["IBLOCK_ELEMENT"] = $_REQUEST['IBLOCK_ELEMENT'];
	$arSitemap["SETTINGS"]["IBLOCK_SECTION_SECTION"] = $_REQUEST['IBLOCK_SECTION_SECTION'];
	$arSitemap["SETTINGS"]["IBLOCK_SECTION_ELEMENT"] = $_REQUEST['IBLOCK_SECTION_ELEMENT'];
	$arSitemap["SETTINGS"]["FORUM_ACTIVE"] = $_REQUEST['FORUM_ACTIVE'];
	$arSitemap["SETTINGS"]["FORUM_TOPIC"] = $_REQUEST['FORUM_TOPIC'];
}

$bLogical = $arSitemap['SETTINGS']['logical'] != 'N';

$APPLICATION->SetAdditionalCSS("/bitrix/panel/seo/sitemap.css");

$APPLICATION->SetTitle($mapId > 0 ? Loc::getMessage("SEO_SITEMAP_EDIT_TITLE") : Loc::getMessage("SEO_SITEMAP_ADD_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array();

$aMenu[] = array(
	"TEXT"	=> Loc::getMessage("SITEMAP_LIST"),
	"LINK"	=> "/bitrix/admin/seo_sitemap.php?lang=".LANGUAGE_ID,
	"ICON"	=> "btn_list",
	"TITLE"	=> Loc::getMessage("SITEMAP_LIST_TITLE"),
);
if ($mapId > 0)
{
	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("SITEMAP_DELETE"),
		"LINK"	=> "javascript:if(confirm('".Loc::getMessage("SITEMAP_DELETE_CONFIRM")."')) window.location='/bitrix/admin/seo_sitemap_edit.php?action=delete&ID=".$mapId."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		"ICON"	=> "btn_delete",
		"TITLE"	=> Loc::getMessage("SITEMAP_DELETE_TITLE"),
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if(!empty($errors))
{
	CAdminMessage::ShowMessage(join("\n", $errors));
}

?>
<form method="POST" action="<?=POST_FORM_ACTION_URI?>" name="sitemap_form">
	<input type="hidden" name="ID" value="<?=$mapId?>">
	<input type="hidden" name="site_id" value="<?=$siteId?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<tr class="adm-detail-required-field">
	<td width="40%"><?=Loc::getMessage("SITEMAP_NAME")?>:</td>
	<td width="60%"><input type="text" name="NAME" value="<?=Converter::getHtmlConverter()->encode($arSitemap["NAME"])?>" style="width:70%"></td>
</tr>
<tr class="adm-detail-required-field">
	<td width="40%"><?=Loc::getMessage("SITEMAP_FILENAME_ADDRESS")?>:</td>
	<td width="60%"><select name="PROTO">
		<option value="0"<?=$arSitemap['SETTINGS']['PROTO'] == 0 ? ' selected="selected"' : ''?>>http</option>
		<option value="1"<?=$arSitemap['SETTINGS']['PROTO'] == 1 ? ' selected="selected"' : ''?>>https</option>
	</select> <b>://</b> <select name="DOMAIN">
<?
foreach($arSite['DOMAINS'] as $domain):
	$hd = Converter::getHtmlConverter()->encode($domain);
	$hdc = Converter::getHtmlConverter()->encode(CBXPunycode::ToUnicode($domain, $e = null));
?>
	<option value="<?=$hd?>"<?=$domain == $arSitemap['SETTINGS']['DOMAIN'] ? ' selected="selected"' : ''?>><?=$hdc?></option>
<?
endforeach;
?>
</select> <b><?=Converter::getHtmlConverter()->encode($arSite['DIR']);?></b> <input type="text" name="FILENAME_INDEX" value="<?=Converter::getHtmlConverter()->encode($arSitemap['SETTINGS']["FILENAME_INDEX"])?>" /></td>
</tr>
<tr>
	<td></td>
	<style>
		.adm-info-message{margin-top:0 !important;}
	</style>
	<td><?echo BeginNote(),Loc::getMessage("SITEMAP_FILENAME_ADDRESS_ATTENTION"),EndNote();?></td>
</tr>
<tr>
	<td width="40%"><label for="SITEMAP_ROBOTS_Y"><?echo Loc::getMessage("SITEMAP_ROBOTS")?>:</label></td>
	<td width="60%"><input type="hidden"  name="ROBOTS" value="N"><input type="checkbox" id="SITEMAP_ROBOTS_Y" name="ROBOTS" value="Y"<?=$arSitemap['SETTINGS']['ROBOTS'] == 'Y' ? ' checked="checked"' : ''?>> <label for="SITEMAP_ROBOTS_Y"><?=Loc::getMessage('MAIN_YES')?></label></td>
</tr>
<tr>
	<td width="40%"><?=Loc::getMessage('SITEMAP_DATE_RUN')?>:</td>
	<td width="60%"><?=$arSitemap['DATE_RUN'] ? $arSitemap['DATE_RUN'] : Loc::getMessage('SITEMAP_DATE_RUN_NEVER')?></td>
</tr>
<?
$tabControl->BeginNextTab();

$startDir = HtmlFilter::encode($arSite['DIR']);
$bChecked = isset($arSitemap['SETTINGS']['DIR'])
	? $arSitemap['SETTINGS']['DIR'][$startDir] == 'Y'
	: true;
?>
<script>
var loadedDirs = {};
function loadDir(bLogical, sw, dir, div, depth, checked)
{
	div = 'subdirs_' + div;
	if(!!sw && BX.hasClass(sw, 'sitemap-opened'))
	{
		BX(div).style.display = 'none';
		BX.removeClass(sw, 'sitemap-opened')
	}
	else if (div != 'subdirs_<?=$startDir?>' && !!loadedDirs[div])
	{
		if(sw)
		{
			BX.addClass(sw, 'sitemap-opened');
		}
		BX(div).style.display = 'block';
	}
	else
	{
		BX.ajax.get('<?=$APPLICATION->GetCurPageParam('', array('dir', 'depth'))?>', {dir:dir,depth:depth,checked:checked?'Y':'N',log:bLogical?'Y':'N',sessid:BX.bitrix_sessid()}, function(res)
		{
			BX(div).innerHTML = res;
			BX(div).style.display = 'block';
			if(sw)
			{
				BX.addClass(sw, 'sitemap-opened');
			}
			loadedDirs[div] = true;
			BX.adminFormTools.modifyFormElements(BX(div));
		});
	}

	BX.onCustomEvent('onAdminTabsChange');
}

var bChanged = false;
function switchLogic(l)
{
	if(!bChanged || confirm('<?=CUtil::JSEscape(Loc::getMessage('SEO_SITEMAP_LOGIC_WARNING'))?>'))
	{
		loadDir(l, null, '<?=$startDir?>', '<?=$startDir?>', 0, BX('DIR_<?=$startDir?>').checked);
		bChanged = false;
	}
	else
	{
		BX('log_' +(l ? 'N' : 'Y')).checked = true;
	}
}

function checkAll(div, v)
{
	bChanged = true;
	_check_all(div, {tagName:'INPUT',property:{type:'checkbox'}}, v);
}

function _check_all(div, isElement, v)
{
	var c = BX.findChildren(BX('subdirs_' + div), isElement, true);
	for(var i = 0; i < c.length; i++)
	{
		c[i].checked = v;
	}
}
</script>
<tr class="adm-detail-required-field">
	<td width="40%"><?=Loc::getMessage("SITEMAP_FILENAME_FILE")?>:</td>
	<td width="60%"><input type="text" name="FILENAME_FILES" value="<?=Converter::getHtmlConverter()->encode($arSitemap['SETTINGS']["FILENAME_FILES"])?>" style="width:70%"></td>
</tr>
<tr>
	<td width="40%" valign="top"><?=Loc::getMessage('SEO_SITEMAP_STRUCTURE_TYPE')?>:</td>
	<td width="60%">
		<input type="radio" name="log" id="log_Y" value="Y"<?=$bLogical ? ' checked="checked"' : ''?> onclick="switchLogic(true)" /><label for="log_Y"><?=Loc::getMessage('SEO_SITEMAP_STRUCTURE_TYPE_Y')?></label><br />
		<input type="radio" name="log" id="log_N" value="N"<?=$bLogical ? '' : ' checked="checked"'?> onclick="switchLogic(false)" /><label for="log_N"><?=Loc::getMessage('SEO_SITEMAP_STRUCTURE_TYPE_N')?></label>
	</td>
</tr>
<tr>
	<td width="40%" valign="top"><?=Loc::getMessage('SEO_SITEMAP_STRUCTURE')?>: </td>
	<td width="60%">
<input type="hidden" name="DIR[<?=$startDir?>]" value="N" /><input type="checkbox" name="DIR[<?=$startDir?>]" id="DIR_<?=$startDir?>"<?=$bChecked ? ' checked="checked"' : ''?> value="Y" onclick="checkAll('<?=$startDir?>', this.checked);" />&nbsp;<label for="DIR_<?=$startDir?>"><?=$startDir?></label></div>
<div id="subdirs_<?=$startDir?>">
<?
if(is_array($arSitemap['SETTINGS']['FILE']))
{
	foreach($arSitemap['SETTINGS']['FILE'] as $dir => $value)
	{
?>
	<input type="hidden" name="FILE[<?=Converter::getHtmlConverter()->encode($dir);?>]" value="<?=$value=='N'?'N':'Y'?>" />
<?
	}
}
else
{
	$arSitemap['SETTINGS']['FILE'] = array();
}

if(is_array($arSitemap['SETTINGS']['DIR']))
{
	foreach($arSitemap['SETTINGS']['DIR'] as $dir => $value)
	{
		if($dir != $startDir)
		{
?>
	<input type="hidden" name="DIR[<?=Converter::getHtmlConverter()->encode($dir);?>]" value="<?=$value=='N'?'N':'Y'?>" />
<?
		}
	}

}
else
{
	$arSitemap['SETTINGS']['DIR'] = array();
}

$arChecked = array_merge($arSitemap['SETTINGS']['DIR'], $arSitemap['SETTINGS']['FILE']);

echo seo_getDir($bLogical, $siteId, $startDir, 1, $bChecked, $arChecked);
?>
	</td>
</tr>
<tr>
	<td width="40%" valign="top"><?=Loc::getMessage('SEO_SITEMAP_STRUCTURE_FILE_MASK')?>: </td>
	<td width="60%"><input type="text" name="FILE_MASK" value="<?=Converter::getHtmlConverter()->encode($arSitemap['SETTINGS']['FILE_MASK'])?>" />
<?
echo BeginNote();
echo Loc::getMessage('SEO_FILE_MASK_HELP');
echo EndNote();
?>
	</td>
</tr>
<?
if($bIBlock)
{
	$tabControl->BeginNextTab();
?>
<tr class="adm-detail-required-field">
	<td width="40%"><?=Loc::getMessage("SITEMAP_FILENAME_IBLOCK")?>:</td>
	<td width="60%"><input type="text" name="FILENAME_IBLOCK" value="<?=Converter::getHtmlConverter()->encode($arSitemap['SETTINGS']["FILENAME_IBLOCK"])?>" style="width:70%"></td>
</tr>

<tr>
	<td colspan="2" align="center">
<?
	$dbRes = CIBlock::GetList(array("ID" => "ASC"), array(
		'SITE_ID' => $siteId
	));
	$bFound = false;
	while ($arRes = $dbRes->Fetch())
	{
		if(!$bFound)
		{
?>
<script>
var loadedIblocks = {};
function loadIblock(sw, iblock, section, div, section_checked, element_checked)
{
	if(!!BX('IBLOCK_ACTIVE_' + div) && !BX('IBLOCK_ACTIVE_' + div).checked)
		return;

	var row = 'subdirs_row_' + div,
		div = 'subdirs_' + div;

	if(!!sw && BX.hasClass(sw, 'sitemap-opened'))
	{
		BX(row).style.display = 'none';
		BX.removeClass(sw, 'sitemap-opened');
	}
	else if (!!loadedIblocks[div])
	{
		if(sw)
		{
			BX.addClass(sw, 'sitemap-opened');
		}

		BX(row).style.display = '';
	}
	else
	{
		BX(div).innerHTML = BX.message('JS_CORE_LOADING');
		BX.ajax.get('<?=$APPLICATION->GetCurPageParam('', array('dir', 'iblock', 'section', 'depth'))?>', {iblock:iblock,section:section,section_checked:section_checked?'Y':'N',element_checked:element_checked?'Y':'N',sessid:BX.bitrix_sessid()}, function(res)
		{
			BX(div).innerHTML = res;
			BX(row).style.display = '';
			if(sw)
			{
				BX.addClass(sw, 'sitemap-opened');
			}
			loadedIblocks[div] = true;
			BX.adminFormTools.modifyFormElements(BX(div));
		});
	}

	BX.onCustomEvent('onAdminTabsChange');
}

function checkAllSection(div, v)
{
	_check_all(div, {tagName:'INPUT',property:{type:'checkbox'}, attribute:{'data-type':'section'}}, v);
}

function checkAllElement(div, v)
{
	_check_all(div, {tagName:'INPUT',property:{type:'checkbox'}, attribute:{'data-type':'element'}}, v);
}

function setIblockActive(check, cont)
{
	var row = check.parentNode.parentNode;

	if(!check.checked)
	{
		row.cells[1].style.textDecoration = 'line-through';
		BX('subdirs_row_' + cont).style.display = 'none';
	}
	else
	{
		row.cells[1].style.textDecoration = 'none';
	}

}
</script>
		<table class="internal" style="width: 80%;">
			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage('SEO_SITEMAP_IBLOCK_NAME')?></td>
				<td width="100"><?=Loc::getMessage('SEO_SITEMAP_IBLOCK_AUTO')?></td>
				<td width="100"><?=Loc::getMessage('SEO_SITEMAP_IBLOCK_LIST')?></td>
				<td width="100"><?=Loc::getMessage('SEO_SITEMAP_IBLOCK_SECTIONS')?></td>
				<td width="120"><?=Loc::getMessage('SEO_SITEMAP_IBLOCK_ELEMENTS')?></td>
			</tr>
<?
			$bFound = true;
		}

		$r = RandString(8);
		$d = $arRes['ID'];

		$bList = $arRes['LIST_PAGE_URL'] <> '';
		$bListChecked = $bList && (!is_array($arSitemap['SETTINGS']['IBLOCK_LIST']) || $arSitemap['SETTINGS']['IBLOCK_LIST'][$d] == 'Y');
		$bSection = $arRes['SECTION_PAGE_URL'] <> '';
		$bSectionChecked = $bSection && (!is_array($arSitemap['SETTINGS']['IBLOCK_SECTION']) || $arSitemap['SETTINGS']['IBLOCK_SECTION'][$d] == 'Y');
		$bElement = $arRes['DETAIL_PAGE_URL'] <> '';
		$bElementChecked = $bElement && (!is_array($arSitemap['SETTINGS']['IBLOCK_ELEMENT']) || $arSitemap['SETTINGS']['IBLOCK_ELEMENT'][$d] == 'Y');

		$bAuto = ($bElementChecked || $bSectionChecked) && $arSitemap['SETTINGS']['IBLOCK_AUTO'][$d] == 'Y';

		$bActive = !isset($arSitemap['SETTINGS']['IBLOCK_ACTIVE']) || $arSitemap['SETTINGS']['IBLOCK_ACTIVE'][$d] == 'Y';
?>
			<tr>
				<td width="20"><span onclick="loadIblock(this, '<?=$d?>', '0', '<?=$r?>', BX('IBLOCK_SECTION_<?=$d?>').checked, BX('IBLOCK_ELEMENT_<?=$d?>').checked);" class="sitemap-tree-icon-iblock"></span></td>
				<td<?=$bActive ? '' : ' style="text-decoration:line-through"'?>>
					<input type="hidden" name="IBLOCK_ACTIVE[<?=$d?>]" value="N" />
					<input type="checkbox" name="IBLOCK_ACTIVE[<?=$d?>]" id="IBLOCK_ACTIVE_<?=$r?>" onclick="setIblockActive(this, '<?=$r?>')"<?=$bActive ? ' checked="checked"' : ''?> value="Y" />
					<a href="iblock_edit.php?lang=<?=LANGUAGE_ID?>&amp;ID=<?=$d?>&amp;type=<?=$arRes['IBLOCK_TYPE_ID']?>&amp;admin=Y">[<?=$arRes['ID']?>] <?=Converter::getHtmlConverter()->encode($arRes['NAME'].' ('.$arRes['CODE'].')')?></a>
				</td>
				<td align="center"><input type="hidden" name="IBLOCK_AUTO[<?=$d?>]" value="N" /><input type="checkbox" name="IBLOCK_AUTO[<?=$d?>]" id="IBLOCK_AUTO_<?=$d?>" value="Y"<?=$bAuto?' checked="checked"':''?> />&nbsp;<label for="IBLOCK_AUTO_<?=$d?>"><?=Loc::getMessage('MAIN_YES')?></label></td>
				<td align="center"><input type="hidden" name="IBLOCK_LIST[<?=$d?>]" value="N" /><input type="checkbox" name="IBLOCK_LIST[<?=$d?>]" id="IBLOCK_LIST_<?=$d?>" value="Y"<?=$bList?'':' disabled="disabled"'?><?=$bListChecked?' checked="checked"':''?> />&nbsp;<label for="IBLOCK_LIST_<?=$d?>"><?=Loc::getMessage('MAIN_YES')?></label></td>
				<td align="center"><input type="hidden" name="IBLOCK_SECTION[<?=$d?>]" value="N" /><input type="checkbox" name="IBLOCK_SECTION[<?=$d?>]" id="IBLOCK_SECTION_<?=$d?>" value="Y"<?=$bSection?'':' disabled="disabled"'?><?=$bSectionChecked?' checked="checked"':''?> onclick="checkAllSection('<?=$r?>', this.checked);" />&nbsp;<label for="IBLOCK_SECTION_<?=$d?>"><?=Loc::getMessage('MAIN_YES')?></label></td>
				<td align="center"><input type="hidden" name="IBLOCK_ELEMENT[<?=$d?>]" value="N" /><input type="checkbox" name="IBLOCK_ELEMENT[<?=$d?>]" id="IBLOCK_ELEMENT_<?=$d?>" value="Y"<?=$bElement?'':' disabled="disabled"'?><?=$bElementChecked?' checked="checked"':''?> onclick="checkAllElement('<?=$r?>', this.checked);" />&nbsp;<label for="IBLOCK_ELEMENT_<?=$d?>"><?=Loc::getMessage('MAIN_YES')?></label></td>
			</tr>
			<tr style="display: none" id="subdirs_row_<?=$r?>">
<?

		if(is_array($arSitemap['SETTINGS']['IBLOCK_SECTION_SECTION'][$arRes['ID']]))
		{
			foreach($arSitemap['SETTINGS']['IBLOCK_SECTION_SECTION'][$arRes['ID']] as $dir => $value)
			{
?>
				<input type="hidden" name="IBLOCK_SECTION_SECTION[<?=$arRes['ID']?>][<?=Converter::getHtmlConverter()->encode($dir);?>]" value="<?=$value=='N'?'N':'Y'?>" />
<?
			}
		}

		if(is_array($arSitemap['SETTINGS']['IBLOCK_SECTION_ELEMENT'][$arRes['ID']]))
		{
			foreach($arSitemap['SETTINGS']['IBLOCK_SECTION_ELEMENT'][$arRes['ID']] as $dir => $value)
			{
?>
				<input type="hidden" name="IBLOCK_SECTION_ELEMENT[<?=$arRes['ID']?>][<?=Converter::getHtmlConverter()->encode($dir);?>]" value="<?=$value=='N'?'N':'Y'?>" />
<?
			}
		}
?>
				<td colspan="6" align="center" id="subdirs_<?=$r?>"></td>
			</tr>
<?
	}

	if($bFound)
	{
?>
		</table>
<?
	}
	else
	{
		echo BeginNote(),Loc::getMessage('SEO_SITEMAP_NO_IBLOCK_FOUND'),EndNote();
	}
?>
	</td>
</tr>
<?
}
if($bForum)
{
	$tabControl->BeginNextTab();
?>
<tr class="adm-detail-required-field">
	<td width="40%"><?=Loc::getMessage("SITEMAP_FILENAME_FORUM")?>:</td>
	<td width="60%"><input type="text" name="FILENAME_FORUM" value="<?=Converter::getHtmlConverter()->encode($arSitemap['SETTINGS']["FILENAME_FORUM"])?>" style="width:70%"></td>
</tr>

<tr>
	<td colspan="2" align="center">
<?
	$dbRes = CForumNew::GetListEx(array("ID" => "ASC"), array('PERMS' => array(2, 'A'), 'ACTIVE' => 'Y', 'SITE_ID' => $siteId));
	$bFound = false;
	while (!!$dbRes && ($arRes = $dbRes->Fetch()))
	{

		if(!$bFound)
		{
?>
<script type="text/javascript">
function setForumActive(check, cont)
{
	var row = check.parentNode.parentNode;

	if(!check.checked)
	{
		row.cells[0].style.textDecoration = 'line-through';
	}
	else
	{
		row.cells[0].style.textDecoration = 'none';
	}
}
</script>
		<table class="internal" style="width: 80%;">
			<tr class="heading">
				<td><?=GetMessage("SEO_SITEMAP_FORUM")?></td>
				<td width="100"><?=GetMessage("SEO_SITEMAP_IBLOCK_AUTO")?></td>
				<td width="100"><?=GetMessage("SEO_SITEMAP_FORUM_TOPIC")?></td>
			</tr>
<?
			$bFound = true;
		}

		$r = RandString(8);
		$d = $arRes['ID'];
		$bTopic = $arRes['PATH2FORUM_MESSAGE'] <> '';
		$bTopicChecked = $bTopic && (!is_array($arSitemap['SETTINGS']['FORUM_TOPIC']) || $arSitemap['SETTINGS']['FORUM_TOPIC'][$d] == 'Y');

		$bAuto = $bTopicChecked && $arSitemap['SETTINGS']['FORUM_AUTO'][$d] == 'Y';
		$bActive = !isset($arSitemap['SETTINGS']['FORUM_ACTIVE']) || $arSitemap['SETTINGS']['FORUM_ACTIVE'][$d] == 'Y';
?>
			<tr>
				<td<?=$bActive ? '' : ' style="text-decoration:line-through"'?>>
					<input type="hidden" name="FORUM_ACTIVE[<?=$d?>]" value="N" />
					<input type="checkbox" name="FORUM_ACTIVE[<?=$d?>]" id="FORUM_ACTIVE_<?=$r?>" onclick="setForumActive(this, '<?=$r?>')"<?=$bActive ? ' checked="checked"' : ''?> value="Y" />
					<a href="forum_edit.php?lang=<?=LANGUAGE_ID?>&amp;ID=<?=$d?>">[<?=$arRes['ID']?>] <?=Converter::getHtmlConverter()->encode($arRes['NAME'])?></a>
				</td>
				<td align="center"><input type="hidden" name="FORUM_AUTO[<?=$d?>]" value="N" /><input type="checkbox" name="FORUM_AUTO[<?=$d?>]" id="FORUM_AUTO_<?=$d?>" value="Y"<?=$bAuto?' checked="checked"':''?> />&nbsp;<label for="FORUM_AUTO_<?=$d?>"><?=Loc::getMessage('MAIN_YES')?></label></td>
				<td align="center"><input type="hidden" name="FORUM_TOPIC[<?=$d?>]" value="N" /><input type="checkbox" name="FORUM_TOPIC[<?=$d?>]" id="FORUM_TOPIC_<?=$d?>" value="Y"<?=$bTopic?'':' disabled="disabled"'?><?=$bTopicChecked?' checked="checked"':''?> />&nbsp;<label for="FORUM_ELEMENT_<?=$d?>"><?=Loc::getMessage('MAIN_YES')?></label></td>
			</tr>
<?
	}

	if($bFound)
	{
?>
		</table>
<?
	}
	else
	{
		echo BeginNote(),Loc::getMessage('SEO_SITEMAP_NO_FORUM_FOUND'),EndNote();
	}
?>
	</td>
</tr>
<?
}
$tabControl->Buttons(array());
?>
<input type="submit" name="save_and_add" value="<?=Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_SITEMAP_SAVEANDRUN'))?>" />
<?=bitrix_sessid_post();?>
<?
$tabControl->End();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>