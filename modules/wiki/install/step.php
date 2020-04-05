<?if (CModule::IncludeModule("iblock")):
	IncludeModuleLangFile(__FILE__);
?>
<script>
function ChangeStatus(pointer)
{
	if (typeof pointer != "object" || (document.forms['wiki_form'] == null))
		return false;
	if (pointer.name == 'create_iblock_type')
	{
		document.forms['wiki_form'].elements['iblock_type_id'].disabled = (pointer.id == 'create_iblock_type_y');
		document.forms['wiki_form'].elements['iblock_type_name'].disabled = !(pointer.id == 'create_iblock_type_y');
	}
	else if (pointer.name == 'create_forum_group')
	{
		document.forms['wiki_form'].elements['forum_group_id'].disabled = (pointer.id == 'create_forum_group_y');
		document.forms['wiki_form'].elements['forum_group_name'].disabled = !(pointer.id == 'create_forum_group_y');
	}
	else if (pointer.name == 'create_socnet_iblock_type')
	{
		document.forms['wiki_form'].elements['socnet_iblock_type_id'].disabled = (pointer.id == 'create_socnet_iblock_type_y');
		document.forms['wiki_form'].elements['socnet_iblock_type_name'].disabled = !(pointer.id == 'create_socnet_iblock_type_y');
	}
	else if (pointer.name == 'create_socnet_forum_group')
	{
		document.forms['wiki_form'].elements['socnet_forum_group_id'].disabled = (pointer.id == 'create_socnet_forum_group_y');
		document.forms['wiki_form'].elements['socnet_forum_group_name'].disabled = !(pointer.id == 'create_socnet_forum_group_y');
	}
}

function CheckCreate(pointer)
{
	if (!pointer || typeof pointer != "object" || !document.getElementById(pointer.id + '_create'))
		return false;
	document.getElementById(pointer.id + '_create').style.display = (pointer.checked ? "" : "none");
}
CheckCreate(document.getElementById('iblock'));
CheckCreate(document.getElementById('socnet_iblock'));
CheckCreate(document.getElementById('forum'));
CheckCreate(document.getElementById('sconet_forum'));
</script>
<form action="<?=$APPLICATION->GetCurPage()?>" name="wiki_form" id="wiki_form" class="form-photo" method="POST">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<input type="hidden" name="id" value="wiki">
<input type="hidden" name="install" value="Y">
<input type="hidden" name="step" value="2">
<table class="list-table">
	<?if ($GLOBALS["APPLICATION"]->GetGroupRight("iblock") >= "W"):?>
	<tr class="head"><td colspan="2"><input type="checkbox" name="iblock" id="iblock" value="Y" onclick="CheckCreate(this);" <?=($_REQUEST["iblock"] == "Y" ? " checked='checked'" : "")?>/> <label for="iblock"><?=GetMessage("WIKI_CREATE_NEW_IBLOCK")?></label></td></tr>
	<tbody id="iblock_create" <?=($_REQUEST["iblock"] == "Y" ? "" : "style=\"display:none;\"")?>>
	<tr><td><span class="required">*</span><?=GetMessage("WIKI_CREATE_NEW_IBLOCK_NAME")?>: </td><td><input type="text" name="iblock_name" value="<?=htmlspecialcharsbx($_REQUEST["iblock_name"])?>" /></td></tr>
	<tr><td><span class="required">*</span><?=GetMessage("WIKI_CREATE_NEW_IBLOCK_TYPE")?>: </td><td>
		<input onclick="ChangeStatus(this)" type="radio" name="create_iblock_type" id="create_iblock_type_n" value="N" <?=($_REQUEST["create_iblock_type"] != "Y" ? " checked=\"checked\"" : "")?> />
		<label for="create_iblock_type_n"><?=GetMessage("WIKI_SELECT")?>: </label>
		<select name="iblock_type_id" <?=($_REQUEST["create_iblock_type"] == "Y" ? "disabled='disabled'" : "")?>><?
			$arIBlockType = array();
			$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
			while ($arr=$rsIBlockType->Fetch())
			{
				if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
				{
					?><option value="<?=$ar["ID"]?>" <?=($_REQUEST["iblock_type_id"] == $ar["ID"] ? " selected='selected'" : "")?>><?="[".$ar["ID"]."] ".$ar["NAME"]?></option><?
				}
			}
			?></select><br />
		<input onclick="ChangeStatus(this)" type="radio" name="create_iblock_type" id="create_iblock_type_y" value="Y" <?=($_REQUEST["create_iblock_type"] == "Y" ? " checked=\"checked\"" : "")?> />
		<label for="create_iblock_type_y"><?=GetMessage("WIKI_CREATE")?>: </label>
		<span class="required">*</span><?=GetMessage("WIKI_ID")?> (ID):
		<input type="text" name="iblock_type_name" value="<?=htmlspecialcharsbx($_REQUEST["iblock_type_name"])?>" <?=($_REQUEST["create_iblock_type"] != "Y" ? "disabled='disabled'" : "")?>/><br />
		</td></tr>
	</tbody><?
	endif;
	if (IsModuleInstalled("forum") && CModule::IncludeModule("forum") && $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W"):
	?><tr class="head"><td colspan="2"><input type="checkbox" name="forum" id="forum" value="Y" onclick="CheckCreate(this);" <?=($_REQUEST["forum"] == "Y" ? " checked='checked'" : "")?>/> <label for="forum"><?=GetMessage("WIKI_CREATE_NEW_FORUM")?></label></td></tr>
	<tbody id="forum_create" <?=($_REQUEST["forum"] == "Y" ? "" : "style=\"display:none;\"")?>>
	<tr><td><span class="required">*</span><?=GetMessage("WIKI_CREATE_NEW_FORUM_NAME")?>: </td><td><input type="text" name="forum_name" value="<?=htmlspecialcharsbx($_REQUEST["forum_name"])?>" /></td>
	</tr>
	</tbody>
	<?
	endif;
	if (IsModuleInstalled("socialnetwork")):
		if ($GLOBALS["APPLICATION"]->GetGroupRight("iblock") >= "W"):?>
		<tr class="head"><td colspan="2"><input type="checkbox" name="socnet_iblock" id="socnet_iblock" value="Y" onclick="CheckCreate(this);" <?=($_REQUEST["socnet_iblock"] == "Y" ? " checked='checked'" : "")?>/> <label for="socnet_iblock"><?=GetMessage("WIKI_CREATE_NEW_SOCNET_IBLOCK")?></label></td></tr>
		<tbody id="socnet_iblock_create" <?=($_REQUEST["socnet_iblock"] == "Y" ? "" : "style=\"display:none;\"")?>>
		<tr><td><span class="required">*</span><?=GetMessage("WIKI_CREATE_NEW_SOCNET_IBLOCK_NAME")?>: </td><td><input type="text" name="socnet_iblock_name" value="<?=htmlspecialcharsbx($_REQUEST["socnet_iblock_name"])?>" /></td></tr>
		<tr><td><span class="required">*</span><?=GetMessage("WIKI_CREATE_NEW_SOCNET_IBLOCK_TYPE")?>: </td><td>
			<input onclick="ChangeStatus(this)" type="radio" name="create_socnet_iblock_type" id="create_socnet_iblock_type_n" value="N" <?=($_REQUEST["create_socnet_iblock_type"] != "Y" ? " checked=\"checked\"" : "")?> />
			<label for="create_iblock_type_n"><?=GetMessage("WIKI_SELECT")?>: </label>
			<select name="socnet_iblock_type_id" <?=($_REQUEST["create_socnet_iblock_type"] == "Y" ? "disabled='disabled'" : "")?>><?
				$arIBlockType = array();
				$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
				while ($arr=$rsIBlockType->Fetch())
				{
					if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
					{
						?><option value="<?=$arr["ID"]?>" <?=($_REQUEST["socnet_iblock_type_id"] == $arr["ID"] ? " selected='selected'" : "")?>><?="[".$arr["ID"]."] ".$ar["NAME"]?></option><?
					}
				}
				?></select><br />
			<input onclick="ChangeStatus(this)" type="radio" name="create_socnet_iblock_type" id="create_socnet_iblock_type_y" value="Y" <?=($_REQUEST["create_socnet_iblock_type"] == "Y" ? " checked=\"checked\"" : "")?> />
			<label for="create_iblock_type_y"><?=GetMessage("WIKI_CREATE")?>: </label>
			<span class="required">*</span><?=GetMessage("WIKI_ID")?> (ID):
				<input type="text" name="socnet_iblock_type_name" value="<?=htmlspecialcharsbx($_REQUEST["socnet_iblock_type_name"])?>" <?=($_REQUEST["create_socnet_iblock_type"] != "Y" ? "disabled='disabled'" : "")?>/><br />
			</td></tr>
		</tbody><?
		endif;
		if (IsModuleInstalled("forum") && CModule::IncludeModule("forum") && $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W"):
		?><tr class="head"><td colspan="2"><input type="checkbox" name="socnet_forum" id="socnet_forum" value="Y" onclick="CheckCreate(this);" <?=($_REQUEST["socnet_forum"] == "Y" ? " checked='checked'" : "")?>/> <label for="socnet_forum"><?=GetMessage("WIKI_CREATE_NEW_SOCNET_FORUM")?></label></td></tr>
		<tbody id="socnet_forum_create" <?=($_REQUEST["socnet_forum"] == "Y" ? "" : "style=\"display:none;\"")?>>
		<tr><td><span class="required">*</span><?=GetMessage("WIKI_CREATE_NEW_SOCNET_FORUM_NAME")?>: </td><td><input type="text" name="socnet_forum_name" value="<?=htmlspecialcharsbx($_REQUEST["socnet_forum_name"])?>" /></td>
		</tr>
		</tbody>
		<?
		endif;
	endif;
	?>
	<tr>
		<td colspan="2"><input type="submit" value="<?=GetMessage("MOD_INSTALL")?>" /></td>
	</tr>
</table>
</form>
<?endif;?>