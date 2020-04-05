<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
?>
<?
if($arResult['ERROR_MESSAGE'])
	ShowMessage($arResult['ERROR_MESSAGE']);
?>
<?
$arServices = $arResult["AUTH_SERVICES_ICONS"];
if(!empty($arResult["AUTH_SERVICES"]))
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
			"AUTH_SERVICES"=>$arResult["AUTH_SERVICES"],
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

if(isset($arResult["DB_SOCSERV_USER"]) && $arParams["SHOW_PROFILES"] != 'N')
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
			foreach($arResult["DB_SOCSERV_USER"] as $key => $arUser)
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
						<a class="split-delete-item" href="<?=htmlspecialcharsbx($arUser["DELETE_LINK"])?>" onclick="return confirm('<?=GetMessage("SS_PROFILE_DELETE_CONFIRM")?>')" title=<?=GetMessage("SS_DELETE")?>></a>
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