<?if(!check_bitrix_sessid()) return;
global $errors;
$install_public = (($install_public == "Y") ? "Y" : "N");

if ($install_public == "Y")
{
	$public_dir = Trim($public_dir);
	$public_rewrite = (($public_rewrite == "Y") ? "Y" : "N");
	$bReWritePublicFiles = (($public_rewrite == "Y") ? true : false);
}
else
{
	$public_dir = "";
	$public_rewrite = "N";
	$bReWritePublicFiles = false;
}

if ($install_public == "Y" && !empty($public_dir))
{
	function DEMO_Sale_AddMenuItem($menuFile, $menuItem)
	{
		if(CModule::IncludeModule('fileman'))
		{
			$arResult = CFileMan::GetMenuArray($_SERVER["DOCUMENT_ROOT"].$menuFile);
			$arMenuItems = $arResult["aMenuLinks"];
			$menuTemplate = $arResult["sMenuTemplate"];

			$bFound = false;
			foreach($arMenuItems as $item)
				if($item[1] == $menuItem[1])
					$bFound = true;

			if(!$bFound)
			{
				$arMenuItems[] = $menuItem;
				CFileMan::SaveMenu(array($arParams["site_id"], $menuFile), $arMenuItems, $menuTemplate);
			}
		}
	}

	$b = "sort";
	$o = "asc";
	$dbSites = CSite::GetList($b, $o, array("ACTIVE" => "Y"));
	while ($site = $dbSites->Fetch())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/public/".$site['LANGUAGE_ID'], $site['ABS_DOC_ROOT'].$site["DIR"].$public_dir, $bReWritePublicFiles, true);

		if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/personal/.left.menu.php"))
		{
			IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/public_menu.php", $site['LANGUAGE_ID']);

			DEMO_Sale_AddMenuItem($site["DIR"].$public_dir."/.left.menu.php", Array(
				GetMessage("SALE_INSTALL_MENU_ORDER"),
				$site["DIR"].$public_dir."/order/",
				Array(),
				Array(),
				""
			));

			DEMO_Sale_AddMenuItem($site["DIR"].$public_dir."/.left.menu.php", Array(
				GetMessage("SALE_INSTALL_MENU_BASKET"),
				$site["DIR"].$public_dir."/cart/",
				Array(),
				Array(),
				""
			));

			DEMO_Sale_AddMenuItem($site["DIR"].$public_dir."/.left.menu.php", Array(
				GetMessage("SALE_INSTALL_MENU_REGULAR_PAYMENT"),
				$site["DIR"].$public_dir."/regular-payment/",
				Array(),
				Array(),
				""
			));

			DEMO_Sale_AddMenuItem($site["DIR"].$public_dir."/.left.menu.php", Array(
				GetMessage("SALE_INSTALL_MENU_SALE_PROFILES"),
				$site["DIR"].$public_dir."/customer-profiles/",
				Array(),
				Array(),
				""
			));

			DEMO_Sale_AddMenuItem($site["DIR"].$public_dir."/.left.menu.php", Array(
				GetMessage("SALE_INSTALL_MENU_SALE_ACCOUNT"),
				$site["DIR"].$public_dir."/account/",
				Array(),
				Array(),
				""
			));
		}
	}
}

if(empty($errors)):
	echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
else:

	$alErrors .= implode('<br>', $errors);
	echo CAdminMessage::ShowMessage(array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_INST_ERR"), "DETAILS"=>$alErrors, "HTML"=>true));
endif;
if ($ex = $APPLICATION->GetException())
{
	echo CAdminMessage::ShowMessage(array("TYPE" => "ERROR", "MESSAGE" => GetMessage("MOD_INST_ERR"), "HTML" => true, "DETAILS" => $ex->GetString()));
}

if ($public_dir <> '') :
?>
<p><?=GetMessage("MOD_DEMO_DIR")?></p>
<table border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td align="center"><p><b><?=GetMessage("MOD_DEMO_SITE")?></b></p></td>
		<td align="center"><p><b><?=GetMessage("MOD_DEMO_LINK")?></b></p></td>
	</tr>
	<?
	$sites = CSite::GetList($by, $order, Array("ACTIVE"=>"Y"));
	while($site = $sites->Fetch())
	{
		?>
		<tr>
			<td width="0%"><p>[<?=htmlspecialcharsbx($site["ID"])?>] <?=htmlspecialcharsbx($site["NAME"])?></p></td>
			<td width="0%"><p><a href="<?if($site["SERVER_NAME"] <> '') echo "http://".htmlspecialcharsbx($site["SERVER_NAME"]);?><?=htmlspecialcharsbx($site["DIR"]).$public_dir?>/"><?=htmlspecialcharsbx($site["DIR"]).$public_dir?>/</a></p></td>
		</tr>
		<?
	}
	?>
</table>
<?
endif;
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
<form>