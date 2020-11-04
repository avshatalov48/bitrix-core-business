<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<noindex>
<?
if($arResult["FATAL_ERROR"] <> '')
{
	?>
	<span class='errortext'><?=$arResult["FATAL_ERROR"]?></span><br /><br />
	<?
}
else
{
	if($arResult["ERROR_MESSAGE"] <> '')
	{
		?>
		<span class='errortext'><?=$arResult["ERROR_MESSAGE"]?></span><br /><br />
		<?
	}
	
	if($arResult["bEdit"]=="Y")
	{
		?>
		<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
		<table class="blog-table-header-left">
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_ALIAS")?></th>
			<td><input type=text size="47" name="ALIAS" value="<?=$arResult["User"]["ALIAS"]?>"></td>
		</tr>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_ABOUT")?></th>
			<td><textarea name="DESCRIPTION" style="width:98%" rows="5"><?=$arResult["User"]["DESCRIPTION"]?></textarea></td>
		</tr>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_SITE")?></th>
			<td><input type=text size="47" name="PERSONAL_WWW" value="<?=$arResult["User"]["PERSONAL_WWW"]?>"></td>
		</tr>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_SEX")?></th>
			<td>
				<select name="PERSONAL_GENDER">
					<?
					foreach($arResult["arSex"] as $k => $v)
					{
					?>
						<option value="<?=$k?>"<?if($k==$arResult["User"]["PERSONAL_GENDER"]) echo " selected";?>><?=$v?></option>
					<?
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_BIRTHDAY")?></th>
			<td>
			<?
			$APPLICATION->IncludeComponent(
				'bitrix:main.calendar',
				'',
				array(
					'SHOW_INPUT' => 'Y',
					'FORM_NAME' => 'form1',
					'INPUT_NAME' => 'PERSONAL_BIRTHDAY',
					'INPUT_VALUE' => $arResult["User"]["PERSONAL_BIRTHDAY"],
					'SHOW_TIME' => 'N'
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);?></td>
		</tr>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_PHOTO")?></th>
			<td>
				<input name="PERSONAL_PHOTO" size="30" type="file"><br />
				<label><input name="PERSONAL_PHOTO_del" value="Y" type="checkbox"><?=GetMessage("BU_DELETE_FILE");?></label>
				<?if ($arResult["User"]["PERSONAL_PHOTO_ARRAY"]!==false):?>
					<br /><?=$arResult["User"]["PERSONAL_PHOTO_IMG"]?>
				<?endif?>
			</td>
		</tr>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_AVATAR")?></th>
			<td>
				<input name="AVATAR" size="30" type="file"><br />
				<label><input name="AVATAR_del" value="Y" type="checkbox"><?=GetMessage("BU_DELETE_FILE");?></label>
				<?if ($arResult["User"]["AVATAR_ARRAY"]!==false):?>
					<br /><?=$arResult["User"]["AVATAR_IMG"]?>
				<?endif?>
			</td>
		</tr>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_INTERESTS")?></th>
			<td><textarea name="INTERESTS" style="width:98%" rows="5"><?=$arResult["User"]["INTERESTS"]?></textarea></td>
		</tr>
		<?// ********************* User properties ***************************************************?>
		<?if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):?>
			<?foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):?>
			<tr><th>
				<?if ($arUserField["MANDATORY"]=="Y"):?>
					<span class="required">*</span>
				<?endif;?>
				<?=$arUserField["EDIT_FORM_LABEL"]?>:</th><td>
					<?$APPLICATION->IncludeComponent(
						"bitrix:system.field.edit", 
						$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
						array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField), null, array("HIDE_ICONS"=>"Y"));?></td></tr>
			<?endforeach;?>
		<?endif;?>
		
		<?// ******************** /User properties ***************************************************?>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_LAST_AUTH")?></th>
			<td><?=$arResult["User"]["LAST_VISIT_FORMATED"]?>&nbsp;</td>
		</tr>
		</table>
		<?
		if ($arParams['USER_CONSENT'] == 'Y')
			$APPLICATION->IncludeComponent(
				"bitrix:main.userconsent.request",
				"",
				array(
					"ID" => $arParams["USER_CONSENT_ID"],
					"IS_CHECKED" => $arParams["USER_CONSENT_IS_CHECKED"],
					"AUTO_SAVE" => "Y",
					"IS_LOADED" => $arParams["USER_CONSENT_IS_LOADED"],
					"ORIGIN_ID" => "sender/sub",
					"ORIGINATOR_ID" => "",
					"REPLACE" => array(
						'button_caption' => GetMessage("B_B_USER_SAVE"),
						'fields' => array(GetMessage("B_B_USER_ALIAS"), GetMessage("B_B_USER_SITE"), GetMessage("B_B_USER_BIRTHDAY"), GetMessage("B_B_USER_PHOTO"))
					),
				)
			);
		?>
		<div class="blog-buttons">
			<input type="hidden" name="BLOG_USER_ID" value="<?=$arResult["BlogUser"]["ID"]?>">
			<input type="hidden" name="ID" value="<?=$arParams["ID"]?>">
			<?=bitrix_sessid_post()?>
			<input type="hidden" name="mode" value="edit">
			<input type="submit" name="save" value="<?=GetMessage("B_B_USER_SAVE")?>">
			<input type="reset" name="cancel" value="<?=GetMessage("B_B_USER_CANCEL")?>" OnClick="window.location='<?=$arResult["urlToCancel"]?>'">
		</div>
		</form>
		<?
	}
	else
	{
		if($arResult["urlToEdit"] <> '')
		{
			?>
			<?=GetMessage("B_B_USER_TEXT2")?> <a href="<?=$arResult["urlToEdit"]?>"><?=GetMessage("B_B_USER_TEXT3")?></a>.<br /><br />
			<?
		}
		?>
		<table class="blog-table-header-left">
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_USER")?></th>
			<td><?=$arResult["userName"]?><br />
			<small><?=$arResult["User"]["DESCRIPTION"]?></small>
			</td>
		</tr>
		<?if($arResult["Blog"]["urlToBlog"] <> ''):?>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_BLOG")?></th>
			<td><a href="<?=$arResult["Blog"]["urlToBlog"]?>"><?=$arResult["Blog"]["NAME"]?></a><br />
				<small><?=$arResult["Blog"]["DESCRIPTION"]?></small>
			</td>
		</tr>
		<?endif;?>
		<?if($arResult["User"]["PERSONAL_WWW"] <> ''):?>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_SITE")?></th>
			<td><a target="blank" href="<?=$arResult["User"]["PERSONAL_WWW"]?>" rel="nofollow"><?=$arResult["User"]["PERSONAL_WWW"]?></a></td>
		</tr>
		<?endif;?>
		<?if($arResult["User"]["PERSONAL_GENDER"] <> ''):?>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_SEX")?></th>
			<td><?=$arResult["arSex"][$arResult["User"]["PERSONAL_GENDER"]]?>&nbsp;</td>
		</tr>
		<?endif;?>
		<?if($arResult["User"]["PERSONAL_BIRTHDAY"] <> ''):?>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_BIRTHDAY")?></th>
			<td><?=$arResult["User"]["PERSONAL_BIRTHDAY"]?>&nbsp;</td>
		</tr>
		<?endif;?>
		<?if(intval($arResult["User"]["PERSONAL_PHOTO"])>0):?>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_PHOTO")?></th>
			<td><?=$arResult["User"]["PERSONAL_PHOTO_IMG"]?>&nbsp;</td>
		</tr>
		<?endif;?>
		<?if(intval($arResult["User"]["AVATAR"])>0):?>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_AVATAR")?></th>
			<td><?=$arResult["User"]["AVATAR_IMG"]?>&nbsp;</td>
		</tr>
		<?endif;?>
		<?if(count($arResult["User"]["Hobby"])>0):?>
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_INTERESTS")?></th>
			<td ><?
				foreach($arResult["User"]["Hobby"] as $k => $v)
				{
					if($k!=0)
						echo ", ";
					?><a href="<?=$v["link"]?>" rel="nofollow"><?=$v["name"]?></a><?
				}
				?>
			</td>
		</tr>
		<?endif;?>
		<?// ********************* User properties ***************************************************?>
		<?if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):?>
			<?foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):?>
			<th nowrap><?=$arUserField["EDIT_FORM_LABEL"]?>:</th><td>
					<?$APPLICATION->IncludeComponent(
						"bitrix:system.field.view", 
						$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
						array("arUserField" => $arUserField), null, array("HIDE_ICONS"=>"Y"));?></td></tr>			
			<?endforeach;?>
		<?endif;?>
		<?// ******************** /User properties ***************************************************?>		
		<tr>
			<th nowrap><?=GetMessage("B_B_USER_LAST_AUTH")?></th>
			<td nowrap><?=$arResult["BlogUser"]["LAST_VISIT_FORMATED"]?>&nbsp;</td>
		</tr>
		<tr>
			<th nowrap><?=GetMessage("B_B_FR_FR_OF")?></th>
			<td >
			<?
			if(count($arResult["User"]["friendsOf"])>0)
			{
				foreach($arResult["User"]["friendsOf"] as $k => $v)
				{
					if($k!=0)
						echo ", ";
					?><a href="<?=$v["link"]?>"><?=$v["name"]?></a><?
				}
			}
			else
			{	
				?>
				<i><?=GetMessage("B_B_FR_NO")?></i>
				<?
			}
			?>
		</td>
		</tr>
		<tr>
			<th nowrap><?=GetMessage("B_B_FR_FR")?></th>
			<td >
			<?
			if(count($arResult["User"]["friends"])>0)
			{
				foreach($arResult["User"]["friends"] as $k => $v)
				{
					if($k!=0)
						echo ", ";
					?><a href="<?=$v["link"]?>"><?=$v["name"]?></a><?
				}
			}
			else
			{	
				?>
				<i><?=GetMessage("B_B_FR_NO")?></i>
				<?
			}
			?>
		</td>
		</tr>
		</table>
		<?
	}
}
?>
</noindex>