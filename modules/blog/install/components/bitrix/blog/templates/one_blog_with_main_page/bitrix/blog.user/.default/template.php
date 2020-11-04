<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
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
		<table class="blog-user-table" style="width:100%;">
		<colgroup span="3">
			<col class="head" />
			<col class="value" />
			<col class="descr" />
		</colgroup>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_ALIAS")?></b></td>
			<td><input type=text size="47" name="ALIAS" value="<?=$arResult["User"]["ALIAS"]?>"></td>
			<td><?=GetMessage("B_B_USER_ALIAS_COM")?></td>
		</tr>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_ABOUT")?></b></td>
			<td><textarea name="DESCRIPTION" style="width:98%" rows="5"><?=$arResult["User"]["DESCRIPTION"]?></textarea></td>
			<td><?=GetMessage("B_B_USER_ABOUT_COM")?></td>
		</tr>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_SITE")?></b></td>
			<td><input type=text size="47" name="PERSONAL_WWW" value="<?=$arResult["User"]["PERSONAL_WWW"]?>"></td>
			<td><?=GetMessage("B_B_USER_SITE_COM")?></td>
		</tr>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_SEX")?></b></td>
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
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_BIRTHDAY")?></b></td>
			<td><?
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
			<td><?=GetMessage("B_B_USER_BIRTHDAY_COM")?> (<?=FORMAT_DATE?>).</td>
		</tr>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_AVATAR")?></b></td>
			<td>
				<input name="AVATAR" size="30" type="file"><br />
				<label><input name="AVATAR_del" value="Y" type="checkbox"><?=GetMessage("BU_DELETE_FILE");?></label>
				<?if ($arResult["User"]["AVATAR_ARRAY"]!==false):?>
					<br /><?=$arResult["User"]["AVATAR_IMG"]?>
				<?endif?>
			</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_INTERESTS")?></b></td>
			<td><textarea name="INTERESTS" style="width:98%" rows="5"><?=$arResult["User"]["INTERESTS"]?></textarea></td>
			<td><?=GetMessage("B_B_USER_INTERESTS_COM")?></td>
		</tr>
		<?// ********************* User properties ***************************************************?>
		<?if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):?>
			<?foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):?>
			<tr><td class="head">
				<?if ($arUserField["MANDATORY"]=="Y"):?>
					<span class="required">*</span>
				<?endif;?>
				<b><?=$arUserField["EDIT_FORM_LABEL"]?>:</b></td><td>
					<?$APPLICATION->IncludeComponent(
						"bitrix:system.field.edit", 
						$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
						array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField), null, array("HIDE_ICONS"=>"Y"));?></td><td></td></tr>
			<?endforeach;?>
		<?endif;?>
		
		<?// ******************** /User properties ***************************************************?>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_LAST_AUTH")?></b></td>
			<td><?=$arResult["User"]["LAST_VISIT_FORMATED"]?>&nbsp;</td>
			<td nowrap>&nbsp;</td>
		</tr>
		</table>
		<input type="hidden" name="BLOG_USER_ID" value="<?=$arResult["BlogUser"]["ID"]?>">
		<input type="hidden" name="ID" value="<?=$arParams["ID"]?>">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="mode" value="edit">
		<br />
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
		<br />
		<input type="submit" name="save" value="<?=GetMessage("B_B_USER_SAVE")?>">
		<input type="reset" name="cancel" value="<?=GetMessage("B_B_USER_CANCEL")?>" OnClick="window.location='<?=$arResult["urlToCancel"]?>'">
		</form>
		<?
	}
	else
	{
		if($arResult["urlToEdit"] <> '')
		{
			?>
			<span class="blogtext">
			<?=GetMessage("B_B_USER_TEXT2")?> <a href="<?=$arResult["urlToEdit"]?>"><?=GetMessage("B_B_USER_TEXT3")?></a>.<br /><br />
			</span>
			<?
		}
		?>
		<table class="blog-user-table">
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_USER")?></b></td>
			<td><?=$arResult["userName"]?><br />
			<small><?=$arResult["User"]["DESCRIPTION"]?></small>
			</td>
		</tr>
		<?if($arResult["User"]["PERSONAL_WWW"] <> ''):?>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_SITE")?></b></td>
			<td><a target="blank" href="<?=$arResult["User"]["PERSONAL_WWW"]?>"><?=$arResult["User"]["PERSONAL_WWW"]?></a></td>
		</tr>
		<?endif;?>
		<?if($arResult["User"]["PERSONAL_GENDER"] <> ''):?>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_SEX")?></b></td>
			<td><?=$arResult["arSex"][$arResult["User"]["PERSONAL_GENDER"]]?>&nbsp;</td>
		</tr>
		<?endif;?>
		<?if($arResult["User"]["PERSONAL_BIRTHDAY"] <> ''):?>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_BIRTHDAY")?></b></td>
			<td><?=$arResult["User"]["PERSONAL_BIRTHDAY"]?>&nbsp;</td>
		</tr>
		<?endif;?>
		<?if(intval($arResult["User"]["AVATAR"])>0):?>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_AVATAR")?></b></td>
			<td><?=$arResult["User"]["AVATAR_IMG"]?>&nbsp;</td>
		</tr>
		<?endif;?>
		<?if(count($arResult["User"]["Hobby"])>0):?>
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_INTERESTS")?></b></td>
			<td nowrap><?
				foreach($arResult["User"]["Hobby"] as $k => $v)
				{
					if($k!=0)
						echo ", ";
					echo $v["name"];
				}
				?>
			</td>
		</tr>
		<?endif;?>
		<?// ********************* User properties ***************************************************?>
		<?if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):?>
			<?foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):?>
			<td class="head" nowrap><b><?=$arUserField["EDIT_FORM_LABEL"]?>:</td><td>
					<?$APPLICATION->IncludeComponent(
						"bitrix:system.field.view", 
						$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
						array("arUserField" => $arUserField), null, array("HIDE_ICONS"=>"Y"));?></td></tr>			
			<?endforeach;?>
		<?endif;?>
		<?// ******************** /User properties ***************************************************?>		
		<tr>
			<td class="head" nowrap><b><?=GetMessage("B_B_USER_LAST_AUTH")?></b></td>
			<td nowrap><?=$arResult["BlogUser"]["LAST_VISIT_FORMATED"]?>&nbsp;</td>
		</tr>
		</table>
		<?
	}
}
?>