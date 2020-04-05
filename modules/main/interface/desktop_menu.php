<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

if ($USER->IsAuthorized())
{
	$DESKTOP_CURRENT = $APPLICATION->GetCurPage(true) == "/bitrix/admin/index.php" ? intval($_REQUEST['dt_page']) : -1;
	$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_admin_index", array(), false);
	if(!is_array($arUserOptions))
		$arUserOptions = Array();

	if (count($arUserOptions) > 0):
		?><div id="adm-submenu-desktop" class="adm-submenu-items-wrap adm-submenu-desktop" style="">
			<div class="adm-submenu-items-block"><?
				foreach ($arUserOptions as $DESKTOP_ID => $arUserOption):
					$desktop_className = 'adm-submenu-main-desktop'.($DESKTOP_ID == $DESKTOP_CURRENT ? ' adm-submenu-item-desktop-active' : '');
					?><a href="/bitrix/admin/?dt_page=<?=$DESKTOP_ID?>" class="adm-submenu-item<?=$desktop_className ? ' '.$desktop_className : ''?>">
						<div class="adm-submenu-item-icon"></div>
						<div class="adm-submenu-item-text"><?=strlen($arUserOption["NAME"]) > 0 ? htmlspecialcharsbx($arUserOption["NAME"]) : str_replace('#NUM#', $DESKTOP_ID + 1, GetMessage('DESKTOP_DEFAULT_NAME'));?></div>
					</a><?
				endforeach;
				?><div class="adm-submenu-add-desktop" onclick="BX.adminPanel.addDesktop();">
					<span class="adm-submenu-add-desktop-icon"></span><span class="adm-submenu-add-desktop-text"><?=GetMessage('DESKTOP_ADD')?></span>
				</div>
			</div>
			<div class="adm-submenu-separator"></div>
		</div><?
	endif;
}
?>