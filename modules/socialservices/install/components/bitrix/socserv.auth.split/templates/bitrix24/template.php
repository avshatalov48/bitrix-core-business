<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load(['ui.common', 'ui.buttons']);

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'socserv-auth-split-body-modifier');

if($arResult['ERROR_MESSAGE'])
{
	ShowMessage($arResult['ERROR_MESSAGE']);
}

$arServices = $arResult['AUTH_SERVICES_ICONS'];
$zoomConnected = false;

if(isset($arResult['DB_SOCSERV_USER']) && $arParams['SHOW_PROFILES'] !== 'N')
{
	foreach($arResult['DB_SOCSERV_USER'] as $key => $arUser)
	{
		if (($arUser['EXTERNAL_AUTH_ID'] === 'zoom') && in_array($arUser['ID'], $arResult['ALLOW_DELETE_ID'], true))
		{
			$zoomConnected = true;
			$deleteUrl = htmlspecialcharsbx($arUser['DELETE_LINK']);
		}
	}
}

if (!$zoomConnected && $arServices['zoom'])
{
	?>
	<div class="socserv-auth-split-box socserv-auth-split-color-connect">
		<div class="socserv-auth-split-left">
			<div class="socserv-auth-split-icon"></div>
			<div class="socserv-auth-split-title">Zoom</div>
		</div>
		<div class="socserv-auth-split-content"><?= Loc::getMessage('SS_PROFILE_ZOOM_CONNECT_TITLE') ?></div>
		<button onclick="<?=$arServices['zoom']['ONCLICK']?>" class="socserv-auth-split-btn ui-btn ui-btn-sm ui-btn-primary">
			<?=Loc::getMessage('SS_PROFILE_ZOOM_CONNECT')?>
		</button>
	</div>
	<?php
}
elseif ($zoomConnected && $arServices['zoom'])
{?>
	<div class="socserv-auth-split-box socserv-auth-split-color-disconnect">
		<div class="socserv-auth-split-left">
			<div class="socserv-auth-split-icon"></div>
			<div class="socserv-auth-split-title">Zoom</div>
			<div class="socserv-auth-split-status"><?= Loc::getMessage('SS_PROFILE_ZOOM_CONNECTED') ?></div>
		</div>
		<div class="socserv-auth-split-content"><?= Loc::getMessage('SS_PROFILE_ZOOM_CONFERENCE_TITLE') ?></div>
		<button
				onclick="if (confirm('<?=Loc::getMessage('SS_PROFILE_DELETE_CONFIRM')?>'))location.href='<?=$deleteUrl?>';"
				class="socserv-auth-split-btn ui-btn ui-btn-sm ui-btn-light-border">
			<?=Loc::getMessage('SS_PROFILE_ZOOM_DISCONNECT')?>
		</button>
	</div>
	<?php
}
?>
