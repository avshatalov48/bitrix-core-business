<?php
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;

if (!check_bitrix_sessid())
{
	return;
}

global $obModule;
if (!isset($obModule) || !is_object($obModule))
{
	return;
}

IncludeModuleLangFile(__FILE__);

if (!Loader::includeModule('iblock'))
{
	return;
}

global $MESS;

$bReWriteAdditionalFiles_n = ($_REQUEST['public_rewrite_n'] ?? '') === 'Y';
$bReWriteAdditionalFiles_c = ($_REQUEST['public_rewrite_c'] ?? '') === 'Y';

function CheckIBlockType($ID, $SECTIONS = "Y")
{
	$obType = new CIBlockType;
	$rsType = $obType->GetByID($ID);
	if($arType = $rsType->Fetch())
	{
		return $arType["ID"];
	}
	else
	{
		$arFields = array(
			"ID" => $ID,
			"SECTIONS" => $SECTIONS,
			"LANG" => array(),
		);
		$rsLanguages = CLanguage::GetList();
		while($arLanguage = $rsLanguages->Fetch())
		{
			$MY_MESS = IncludeModuleLangFile(__FILE__, $arLanguage["LID"], true);
			$arFields["LANG"][$arLanguage["LID"]] =  array(
				"NAME" => $MY_MESS["IBLOCK_INSTALL_".mb_strtoupper($ID)."_NAME"],
				"SECTION_NAME" => $MY_MESS["IBLOCK_INSTALL_".mb_strtoupper($ID)."_SECTIONS_NAME"],
				"ELEMENT_NAME" => $MY_MESS["IBLOCK_INSTALL_".mb_strtoupper($ID)."_ELEMENTS_NAME"],
			);
		}
		$result = $obType->Add($arFields);
		if($result)
			return $result;
		else
			return false;
	}
}

$createNews = ($_REQUEST['news'] ?? '') === 'Y';
$news_dir = trim((string)$_REQUEST['news_dir'] ?? 'news');
$createCatalog = ($_REQUEST['catalog'] ?? '') === 'Y';
$catalog_dir = trim((string)($_REQUEST['catalog_dir'] ?? 'catalog'));

if($obModule->errors===false)
{
	if ($createNews && CheckIBlockType("news", "N"))
	{
		//This makes translation checker happy
		//$MY_MESS['IBLOCK_INSTALL_NEWS_NAME']
		//$MY_MESS['IBLOCK_INSTALL_NEWS_SECTIONS_NAME']
		//$MY_MESS['IBLOCK_INSTALL_NEWS_ELEMENTS_NAME']
		//$MY_MESS['IBLOCK_INSTALL_NEWS_SECTION_NAME']
		//$MY_MESS['IBLOCK_INSTALL_NEWS_ELEMENT_NAME']
		$sites = CSite::GetList('', '', Array("ACTIVE"=>"Y"));
		while($site = $sites->Fetch())
		{
			$MY_MESS = IncludeModuleLangFile(__FILE__, $site["LANGUAGE_ID"], true);

			$obBlock = new CIBlock;
			$arFields = array(
				"LID" => $site["LID"],
				"NAME" => GetMessage("IBLOCK_INSTALL_COMPANY_NEWS"),
				"IBLOCK_TYPE_ID" => "news",
				"CODE" => "comp_news",
				"LIST_PAGE_URL" => "#SITE_DIR#/".$news_dir."/index.php",
				"DETAIL_PAGE_URL" => "#SITE_DIR#/".$news_dir."/index.php?news=#ID#",
				"ELEMENTS_NAME" => $MY_MESS["IBLOCK_INSTALL_NEWS_ELEMENTS_NAME"],
				"ELEMENT_NAME" => $MY_MESS["IBLOCK_INSTALL_NEWS_ELEMENT_NAME"],
				"GROUP_ID" => CIBlock::getDefaultRights(),
			);
			if($id = $obBlock->Add($arFields))
			{
				$obBlockProperty = new CIBlockProperty;
				$arFields = array(
					"IBLOCK_ID" => $id,
					"NAME" => GetMessage("IBLOCK_INSTALL_SOURCE"),
					"CODE" => "SOURCE",
					"COL_COUNT" => "30",
				);
				$obBlockProperty->Add($arFields);
			}

			if($news_dir !== '')
			{
				$source = $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/iblock/install/public/news/";
				$target = $site['ABS_DOC_ROOT'].$site["DIR"].$news_dir."/";
				if(file_exists($source))
				{
					CheckDirPath($target);
					$dh = opendir($source);
					while($file = readdir($dh))
					{
						if($file == "." || $file == "..")
							continue;
						if($bReWriteAdditionalFiles_n || !file_exists($target.$file))
						{
							$fh = fopen($source.$file, "rb");
							$php_source = fread($fh, filesize($source.$file));
							fclose($fh);
							if(preg_match_all('/GetMessage\("(.*?)"\)/', $php_source, $matches))
							{
								IncludeModuleLangFile($source.$file, $site["LANGUAGE_ID"]);
								$MESS["IBLOCK_INSTALL_PUBLIC_IBLOCK_ID"] = $id;
								foreach($matches[0] as $i => $text)
								{
									$php_source = str_replace(
										$text,
										'"'.GetMessage($matches[1][$i]).'"',
										$php_source
									);
								}
							}
							$fh = fopen($target.$file, "wb");
							fwrite($fh, $php_source);
							fclose($fh);
							@chmod($target.$file, BX_FILE_PERMISSIONS);
						}
					}
				}
			}
		}
	}

	if ($createCatalog && CheckIBlockType("catalog", "Y"))
	{
		//This makes translation checker happy
		//$MY_MESS['IBLOCK_INSTALL_CATALOG_NAME']
		//$MY_MESS['IBLOCK_INSTALL_CATALOG_SECTIONS_NAME']
		//$MY_MESS['IBLOCK_INSTALL_CATALOG_ELEMENTS_NAME']
		//$MY_MESS['IBLOCK_INSTALL_CATALOG_SECTION_NAME']
		//$MY_MESS['IBLOCK_INSTALL_CATALOG_ELEMENT_NAME']
		$sites = CSite::GetList('', '', Array("ACTIVE"=>"Y"));
		while($site = $sites->Fetch())
		{
			$MY_MESS = IncludeModuleLangFile(__FILE__, $site["LANGUAGE_ID"], true);

			$obBlock = new CIBlock;
			$arFields = array(
				"LID" => $site["LID"],
				"NAME" => GetMessage("IBLOCK_INSTALL_PRODUCTS"),
				"IBLOCK_TYPE_ID" => "catalog",
				"CODE" => "comp_catalog",
				"LIST_PAGE_URL" => "#SITE_DIR#/".$catalog_dir."/index.php",
				"DETAIL_PAGE_URL" => "#SITE_DIR#/".$catalog_dir."/index.php?ID=#ID#",
				"ELEMENTS_NAME" => $MY_MESS["IBLOCK_INSTALL_CATALOG_ELEMENTS_NAME"],
				"ELEMENT_NAME" => $MY_MESS["IBLOCK_INSTALL_CATALOG_ELEMENT_NAME"],
				"SECTIONS_NAME" => $MY_MESS["IBLOCK_INSTALL_CATALOG_SECTIONS_NAME"],
				"SECTION_NAME" => $MY_MESS["IBLOCK_INSTALL_CATALOG_SECTION_NAME"],
				"GROUP_ID" => CIBlock::getDefaultRights(),
			);
			if($id = $obBlock->Add($arFields))
			{
				$obBlockProperty = new CIBlockProperty;
				$arFields = array(
					"IBLOCK_ID" => $id,
					"NAME" => GetMessage("IBLOCK_INSTALL_SIMILAR_PRODUCTS"),
					"CODE" => "ANALOG",
					"PROPERTY_TYPE" => "E",
					"MULTIPLE" => "Y",
					"LINK_IBLOCK_ID" => $id,
				);
				$obBlockProperty->Add($arFields);
			}

			if ($catalog_dir !== '')
			{
				$source = $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/iblock/install/public/catalog/";
				$target = $site['ABS_DOC_ROOT'].$site["DIR"].$catalog_dir."/";
				if(file_exists($source))
				{
					CheckDirPath($target);
					$dh = opendir($source);
					while($file = readdir($dh))
					{
						if($file == "." || $file == "..")
							continue;
						if($bReWriteAdditionalFiles_c || !file_exists($target.$file))
						{
							$fh = fopen($source.$file, "rb");
							$php_source = fread($fh, filesize($source.$file));
							fclose($fh);
							if(preg_match_all('/GetMessage\("(.*?)"\)/', $php_source, $matches))
							{
								IncludeModuleLangFile($source.$file, $site["LANGUAGE_ID"]);
								$MESS["IBLOCK_INSTALL_PUBLIC_IBLOCK_ID"] = $id;
								foreach($matches[0] as $i => $text)
								{
									$php_source = str_replace(
										$text,
										'"'.GetMessage($matches[1][$i]).'"',
										$php_source
									);
								}
							}
							$fh = fopen($target.$file, "wb");
							fwrite($fh, $php_source);
							fclose($fh);
							@chmod($target.$file, BX_FILE_PERMISSIONS);
						}
					}
				}
			}
		}
	}

}

if(!empty($obModule->errors) && is_array($obModule->errors)):
	CAdminMessage::ShowMessage(array(
		"TYPE"=>"ERROR",
		"MESSAGE" =>GetMessage("MOD_INST_ERR"),
		"DETAILS"=>implode("<br>", $obModule->errors),
		"HTML"=>true
	));
else:
	CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
endif;

if($obModule->errors===false && $createNews && $news_dir !== ''):
?>
<p><?=GetMessage("IBLOCK_DEMO_DIR")?></p>
<table border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td align="center"><p><b><?=GetMessage("IBLOCK_SITE")?></b></p></td>
		<td align="center"><p><b><?=GetMessage("IBLOCK_LINK")?></b></p></td>
	</tr>
	<?php
	$sites = CSite::GetList('', '', Array("ACTIVE"=>"Y"));
	while($site = $sites->Fetch())
	{
		$server = '';
		if ($site["SERVER_NAME"] <> '')
			$server .= "http://".$site["SERVER_NAME"];
		$url = $site["DIR"].$news_dir.'/';
		?>
		<tr>
			<td width="0%"><p>[<?=$site["ID"]?>] <?= htmlspecialcharsbx($site["NAME"]);?></p></td>
			<td width="0%"><p><a href="<?= htmlspecialcharsbx($server.$url);?>"><?= htmlspecialcharsEx($url);?></a></p></td>
		</tr>
		<?php
	}
	?>
</table>
<?php
endif;

if($obModule->errors===false && $createCatalog && $catalog_dir !== ''):
?>
<p><?=GetMessage("IBLOCK_DEMO_DIR")?></p>
<table border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td align="center"><p><b><?=GetMessage("IBLOCK_SITE")?></b></p></td>
		<td align="center"><p><b><?=GetMessage("IBLOCK_LINK")?></b></p></td>
	</tr>
	<?php
	$sites = CSite::GetList('', '', Array("ACTIVE"=>"Y"));
	while($site = $sites->Fetch())
	{
		?>
		<tr>
			<td width="0%"><p>[<?=$site["ID"]?>] <?=htmlspecialcharsbx($site["NAME"])?></p></td>
			<td width="0%"><p><a href="<?= ($site["SERVER_NAME"] !== '' ? "http://" . $site["SERVER_NAME"] : '');?><?=$site["DIR"].$catalog_dir?>/"><?=$site["DIR"].$catalog_dir?>/</a></p></td>
		</tr>
		<?php
	}
	?>
</table>
<?php
endif;
?>
<form action="<?= $APPLICATION->GetCurPage()?>">
<p>
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID; ?>">
	<input type="submit" name="" value="<?= htmlspecialcharsbx(GetMessage("MOD_BACK"))?>">
</p>
<form>