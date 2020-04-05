<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/lang_files.php");
?>
<?

$isSidePanel = (isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y");
//End of Content

if(COption::GetOptionString("main", "update_devsrv", "") == "Y")
{
	?><br><br><?
	echo BeginNote('style="position: relative; top: -15px;"');
	?><span class="required"><?echo GetMessage("DEVSERVER_ADMIN_MESSAGE");?></span><?
	echo EndNote();
}
?>
				</div><?//adm-workarea?>
			</td><?//adm-workarea-wrap?>
		</tr>
		<?if (!$isSidePanel):?>
		<tr class="adm-footer-wrap">
			<td class="adm-left-side-wrap"></td>
			<td class="adm-workarea-wrap">
<?
//Footer
$vendor = COption::GetOptionString("main", "vendor", "1c_bitrix");

//wizard customization file
if(isset($bxProductConfig["admin"]["copyright"]))
	$sCopyright = $bxProductConfig["admin"]["copyright"];
else
	$sCopyright = GetMessage("EPILOG_ADMIN_POWER").' <a href="'.GetMessage("EPILOG_ADMIN_URL_PRODUCT_".$vendor).'">'.GetMessage("EPILOG_ADMIN_SM_".$vendor).'#VERSION#</a>. '.GetMessage("EPILOG_ADMIN_COPY_".$vendor);
$sVer = ($GLOBALS['USER']->CanDoOperation('view_other_settings')? " ".SM_VERSION : "");
$sCopyright = str_replace("#VERSION#", $sVer, $sCopyright);

if(isset($bxProductConfig["admin"]["links"]))
	$sLinks = $bxProductConfig["admin"]["links"];
else
	$sLinks = '<a href="'.GetMessage("EPILOG_ADMIN_URL_MAIN_".$vendor).'">'.GetMessage("EPILOG_ADMIN_URL_MAIN_TEXT_".$vendor).'</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.GetMessage("EPILOG_ADMIN_URL_SUPPORT_".$vendor).'" class="adm-main-support-link">'.GetMessage("epilog_support_link").'</a>';
?>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td><?echo $sCopyright?></td>
					<td align="right"><?if(($siteSupport = getLocalPath("php_interface/this_site_support.php", BX_PERSONAL_ROOT)) !== false):?><?include($_SERVER["DOCUMENT_ROOT"].$siteSupport);?><?else:?><?echo $sLinks?><?endif;?></td>
				</tr>
			</table>
<?
//End of Footer
?>
			</td>
		</tr>
		<?endif;?>
	</table>
	<div id="fav_cont_item" class="adm-favorites-main" style="display:none;">
		<div class="adm-favorites-alignment">
			<div class="adm-favorites-center" id="fav_dest_item">
				<div id="fav_text_item" style="display: inline-block;">
					<div class="adm-favorites-text"><?=GetMessage('ADMIN_FAV_ADD')?></div>
					<div class="adm-favorites-description"><?=GetMessage('ADMIN_FAV_HINT')?></div>
				</div>
				<div id="fav_text_finish_item" style="display: none;">
					<div class="adm-favorites-text"><?=GetMessage('ADMIN_FAV_ADD_SUCCESS')?></div>
					<div class="adm-favorites-description"><a class="adm-favorites-description_link" href="javascript:void(0);" onclick="BX.adminMenu.showFavorites(this);"><?=GetMessage('ADMIN_FAV_GOTO')?></a></div>
				</div>
				<div id="fav_text_error_item" style="display: none;">
					<div class="adm-favorites-text"><?=GetMessage('ADMIN_FAV_ADD_ERROR')?></div>
				</div>
				<div class="adm-favorites-center-border-2"></div>
				<div class="adm-favorites-center-border-1"></div>
				<div class="adm-favorites-check-icon" id="fav_icon_finish_item"></div>
			</div>
		</div>
	</div>
	<div id="fav_cont_fav" class="adm-favorites-main remove-favorites-main" style="display:none;">
		<div class="adm-favorites-alignment">
			<div class="adm-favorites-center" id="fav_dest_fav">
				<div id="fav_text_fav" style="display: inline-block;">
					<div class="adm-favorites-text"><?=GetMessage('ADMIN_FAV_DEL')?></div>
					<div class="adm-favorites-description"><?=GetMessage('ADMIN_FAV_HINT')?></div>
				</div>
				<div id="fav_text_finish_fav" style="display: none;">
					<div class="adm-favorites-text"><?=GetMessage('ADMIN_FAV_DEL_SUCCESS')?></div>
				</div>
				<div id="fav_text_error_fav" style="display: none;">
					<div class="adm-favorites-text"><?=GetMessage('ADMIN_FAV_DEL_ERROR')?></div>
				</div>
				<div class="adm-favorites-center-border-2"></div>
				<div class="adm-favorites-center-border-1"></div>
				<div class="adm-favorites-check-icon" id="fav_icon_finish_fav"></div>
			</div>
		</div>
	</div>
<?
if (!defined('ADMIN_SECTION_LOAD_AUTH') || !ADMIN_SECTION_LOAD_AUTH):
?>
</body>
</html>
<?
endif;
?>
