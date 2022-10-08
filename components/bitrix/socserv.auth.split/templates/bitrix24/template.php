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

if ($arServices['zoom'])
{
	if (!$zoomConnected)
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
	elseif ($zoomConnected)
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
}

if(!empty($arResult["AUTH_SERVICES_DISK"]))
{
?>
<div class="soc-serv-main">
	<div class="soc-serv-title-grey">
		<?=GetMessage("SS_GET_COMPONENT_INFO")?>
		<br><br>
	</div>
	<?
	$APPLICATION->IncludeComponent("bitrix:socserv.auth.form", "",
		array(
			"AUTH_SERVICES"=>$arResult["AUTH_SERVICES_DISK"],
			"CURRENT_SERVICE"=>$arResult["CURRENT_SERVICE"],
			"AUTH_URL"=>$arResult['CURRENTURL'],
			"POST"=>$arResult["POST"],
			"SHOW_TITLES"=>'N',
			"FOR_SPLIT"=>'Y',
			"AUTH_LINE"=>'N',
		),
		$component,
		array("HIDE_ICONS"=>"Y")
	);
	?>
<?
}

if(
	isset($arResult["DB_SOCSERV_USER_DISK"])
	&& !empty($arResult["DB_SOCSERV_USER_DISK"])
	&& $arParams["SHOW_PROFILES"] != 'N'
)
{
?>
	<div class="soc-serv-title">
		<?=GetMessage("SS_YOUR_ACCOUNTS");?>
	</div>
	<div class="soc-serv-accounts">
		<table cellspacing="0" cellpadding="8">
			<tr class="soc-serv-header">
				<td><?=GetMessage("SS_SOCNET");?></td>
				<td><?=GetMessage("SS_NAME");?></td>
			</tr>
			<?
			foreach($arResult["DB_SOCSERV_USER_DISK"] as $key => $arUser)
			{
				if(!$icon = htmlspecialcharsbx($arResult["AUTH_SERVICES_ICONS"][$arUser["EXTERNAL_AUTH_ID"]]["ICON"]))
					$icon = 'openid';
				$authID = ($arServices[$arUser["EXTERNAL_AUTH_ID"]]["NAME"]) ? $arServices[$arUser["EXTERNAL_AUTH_ID"]]["NAME"] : $arUser["EXTERNAL_AUTH_ID"];
				?>
				<tr class="soc-serv-personal">
					<td class="bx-ss-icons">
						<i class="bx-ss-icon <?=$icon?>">&nbsp;</i>
						<?if ($arUser["PERSONAL_LINK"] != ''):?>
						<a class="soc-serv-link" target="_blank" href="<?=$arUser["PERSONAL_LINK"]?>">
							<?endif;?>
							<?=$authID?>
							<?if ($arUser["PERSONAL_LINK"] != ''):?>
						</a>
					<?endif;?>
					</td>
					<td class="soc-serv-name">
						<?=$arUser["VIEW_NAME"]?>
					</td>
					<td class="split-item-actions">
						<?if (in_array($arUser["ID"], $arResult["ALLOW_DELETE_ID"])):?>
							<a class="split-delete-item" href="<?=htmlspecialcharsbx($arUser["DELETE_LINK"])?>" onclick="return confirm('<?=GetMessage("SS_PROFILE_DELETE_CONFIRM_OTHER")?>')" title=<?=GetMessage("SS_DELETE")?>></a>
						<?endif;?>
					</td>
				</tr>
				<?
			}
			?>
		</table>
	</div>
	<?
}
?>
<?
if(!empty($arResult["AUTH_SERVICES"]))
{
?>
	</div>
<?
}
?>
