<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(strlen($arResult["FatalError"])>0)
{
	?>
	<span class='errortext'><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?>
		<span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}
	?>
	<div style="margin-bottom: 1em;">	
	<table width="100%" cellspacing="0" cellpadding="8" border="0" class="sonet-group-short">
	<tr>
		<td valign="top" width="65%">
			<h4 style="margin-top:0;"><?=$arResult["Group"]["NAME"]?></h4>
			<table width="100%" cellspacing="2" cellpadding="2">
				<?if($arResult["Group"]["CLOSED"] == "Y"):?>
				<tr>
					<td colspan="2"><b><?= GetMessage("SONET_C39_ARCHIVE_GROUP") ?></b></td>
				</tr>
				<?endif;?>
				<?if(strlen($arResult["Group"]["SUBJECT_NAME"])>0):?>
				<tr>
					<td width="25%"><?= GetMessage("SONET_C6_TOPIC") ?>:</td>
					<td width="75%"><?=$arResult["Group"]["SUBJECT_NAME"]?></td>
				</tr>
				<?endif;?>
				<?if(strlen($arResult["Group"]["DESCRIPTION"])>0):?>
				<tr>
					<td width="25%" valign="top"><?= GetMessage("SONET_C6_DESCR") ?>:</td>
					<td valign="top" width="75%"><?=nl2br($arResult["Group"]["DESCRIPTION"])?></td>
				</tr>
				<?endif;?>
				<?if ($arResult["GroupProperties"]["SHOW"] == "Y"):?>
					<?foreach ($arResult["GroupProperties"]["DATA"] as $fieldName => $arUserField):?>
						<?if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):?>
							<tr>
								<td width="25%"><?=$arUserField["EDIT_FORM_LABEL"]?>:</td>
								<td width="75%">
									<?
									$APPLICATION->IncludeComponent(
										"bitrix:system.field.view", 
										$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
										array("arUserField" => $arUserField),
										null,
										array("HIDE_ICONS"=>"Y")
									);
									?></td>
							</tr>
						<?endif;?>
					<?endforeach;?>
				<?endif;?>
			</table>
			<?if (false && $GLOBALS["USER"]->IsAuthorized()):?>
				<div class="bx-group-control">
					<ul>
						<li class="bx-icon-message"><a href="<?= $arResult["Urls"]["MessageToGroup"] ?>" onclick="window.open('<?= $arResult["Urls"]["MessageToGroup"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=750,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 750)/2-5)); return false;" title="<?= GetMessage("SONET_C39_SEND_MESSAGE_GROUP_TITLE") ?>"><?= GetMessage("SONET_C39_SEND_MESSAGE_GROUP") ?></a></li>
					</ul>
				</div>
				<?if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"]):?>
				<div class="bx-group-control">
					<ul>
						<li class="bx-icon-edit"><a href="<?= $arResult["Urls"]["Edit"] ?>"><?= GetMessage("SONET_C6_ACT_EDIT") ?></a></li>
						<li class="bx-icon-settings"><a href="<?= $arResult["Urls"]["Features"] ?>"><?= GetMessage("SONET_C6_ACT_FEAT") ?></a></li>
					</ul>
				</div>
				<?endif;?>
				<?if (($arResult["CurrentUserPerms"]["UserCanInitiate"] || !$arResult["CurrentUserPerms"]["UserRole"] || $arResult["CurrentUserPerms"]["UserIsMember"] && !$arResult["CurrentUserPerms"]["UserIsOwner"])):?>
				<div class="bx-group-control">
					<ul>
					<?if ($arResult["CurrentUserPerms"]["UserCanInitiate"] && $arResult["Group"]["OPENED"] != "Y"):?>
						<li class="bx-icon-requests"><a href="<?= $arResult["Urls"]["GroupRequests"] ?>"><?= GetMessage("SONET_C6_ACT_VREQU") ?></a></li>
					<?endif;?>
					<?if (!$arResult["CurrentUserPerms"]["UserRole"]):?>
						<li class="bx-icon-join"><a href="<?= $arResult["Urls"]["UserRequestGroup"] ?>"><?= GetMessage("SONET_C6_ACT_JOIN") ?></a></li>
					<?endif;?>
					<?
					if (
						$arResult["CurrentUserPerms"]["UserIsMember"]
						&& (!isset($arResult["CurrentUserPerms"]["UserIsAutoMember"]) || !$arResult["CurrentUserPerms"]["UserIsAutoMember"])
						&& !$arResult["CurrentUserPerms"]["UserIsOwner"]
					):?>
						<li class="bx-icon-leave"><a href="<?= $arResult["Urls"]["UserLeaveGroup"] ?>"><?= GetMessage("SONET_C6_ACT_EXIT") ?></a></li>
					<?endif;?>
					</ul>
				</div>
				<?endif;?>
			<?endif;?>

		</td>
		<td valign="top" width="35%" class="sonet-group-avatar">
			<?=$arResult["Group"]["IMAGE_ID_IMG"]?>
		</td>
	</tr>
	</table>
	</div>
	<?
}
?>