<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

if ($USER->IsAuthorized())
{
	$DESKTOP_CURRENT = $APPLICATION->GetCurPage(true) == "/bitrix/admin/index.php" ? (int)($_REQUEST['dt_page'] ?? 0) : -1;
	$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_admin_index", array(), false);
	if (!is_array($arUserOptions))
	{
		$arUserOptions = [];
	}

	if (!empty($arUserOptions)):
		?><div id="adm-submenu-desktop" class="adm-submenu-items-wrap adm-submenu-desktop" style="">
			<div class="adm-submenu-items-block"><?php
				foreach ($arUserOptions as $DESKTOP_ID => $arUserOption):
					$desktop_className = 'adm-submenu-main-desktop'.($DESKTOP_ID == $DESKTOP_CURRENT ? ' adm-submenu-item-desktop-active' : '');
					?><a href="/bitrix/admin/?dt_page=<?=$DESKTOP_ID?>" class="adm-submenu-item<?=$desktop_className ? ' '.$desktop_className : ''?>">
						<div class="adm-submenu-item-icon"></div>
						<div class="adm-submenu-item-text"><?php
							$userOptionName = (string)($arUserOption['NAME'] ?? '');
							echo
								$userOptionName !== ''
									? htmlspecialcharsbx($userOptionName)
									: Loc::getMessage(
										'DESKTOP_DEFAULT_NAME',
										[
											'#NUM#' => $DESKTOP_ID + 1,
										]
									)
							;?></div>
					</a><?php
				endforeach;
				?><div class="adm-submenu-add-desktop" onclick="BX.adminPanel.addDesktop();">
					<span class="adm-submenu-add-desktop-icon"></span><span class="adm-submenu-add-desktop-text"><?=Loc::getMessage('DESKTOP_ADD')?></span>
				</div>
			</div>
			<div class="adm-submenu-separator"></div>
		</div><?php
	endif;
}
