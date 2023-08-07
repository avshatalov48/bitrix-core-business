<?if (CModule::IncludeModule("iblock")):
	IncludeModuleLangFile(__FILE__);
?>
<form action="<?=$APPLICATION->GetCurPage()?>" name="photo_form" id="photo_form" class="form-photo" method="POST">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="id" value="photogallery">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">
<table class="list-table">
	<?if ($GLOBALS["APPLICATION"]->GetGroupRight("iblock") >= "W"):?>
	<tr class="head"><td colspan="2"><input type="checkbox" name="iblock" id="iblock" value="Y" onclick="CheckCreate(this);" <?=(($_REQUEST["iblock"] ?? null) == "Y" ? " checked='checked'" : "")?>/> <label for="iblock"><?=GetMessage("P_CREATE_NEW_IBLOCK")?></label></td></tr>
	<tbody id="iblock_create" <?=(($_REQUEST["iblock"] ?? null) == "Y" ? "" : "style=\"display:none;\"")?>>
	<tr class="adm-detail-required-field"><td><?=GetMessage("P_CREATE_NEW_IBLOCK_NAME")?>: </td><td><input type="text" name="iblock_name" value="<?=htmlspecialcharsbx($_REQUEST["iblock_name"] ?? null)?>" /></td></tr>
	<tr class="adm-detail-required-field"><td><?=GetMessage("P_CREATE_NEW_IBLOCK_TYPE")?>: </td><td>
		<input onclick="ChangeStatus(this)" type="radio" name="create_iblock_type" id="create_iblock_type_n" value="N" <?=(($_REQUEST["create_iblock_type"] ?? null) != "Y" ? " checked=\"checked\"" : "")?> />
		<label for="create_iblock_type_n"><?=GetMessage("P_SELECT")?>: </label>
		<select name="iblock_type_id" <?=(($_REQUEST["create_iblock_type"] ?? null) == "Y" ? "disabled='disabled'" : "")?>><?
			$arIBlockType = array();
			$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
			while ($arr=$rsIBlockType->Fetch())
			{
				if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
				{
					?><option value="<?=$arr["ID"]?>" <?=(($_REQUEST["iblock_type_id"] ?? null) == $arr["ID"] ? " selected='selected'" : "")?>><?="[".$arr["ID"]."] ".$ar["NAME"]?></option><?
				}
			}
			?></select><br />
		<input onclick="ChangeStatus(this)" type="radio" name="create_iblock_type" id="create_iblock_type_y" value="Y" <?=(($_REQUEST["create_iblock_type"] ?? null) == "Y" ? " checked=\"checked\"" : "")?> />
		<label for="create_iblock_type_y"><?=GetMessage("P_CREATE")?>: </label>
		<strong><?=GetMessage("P_ID")?> (ID):</strong>
			<input type="text" name="iblock_type_name" value="<?=htmlspecialcharsbx($_REQUEST["iblock_type_name"] ?? null)?>" <?=(($_REQUEST["create_iblock_type"] ?? null) != "Y" ? "disabled='disabled'" : "")?>/><br />
		</td></tr>
	</tbody><?
	endif;
	if (IsModuleInstalled("blog") && CModule::IncludeModule("blog") && $GLOBALS["APPLICATION"]->GetGroupRight("blog") >= "W"):
	?><tr class="head"><td colspan="2"><input type="checkbox" name="blog" id="blog" value="Y" onclick="CheckCreate(this);" <?=(($_REQUEST["blog"] ?? null) == "Y" ? " checked='checked'" : "")?>/> <label for="blog"><?=GetMessage("P_CREATE_NEW_BLOG")?></label></td></tr>
	<tbody id="blog_create" <?=(($_REQUEST["blog"] ?? null) == "Y" ? "" : "style=\"display:none;\"")?>>
	<tr class="adm-detail-required-field"><td><?=GetMessage("P_NAME")?>:</td><td><input type="text" name="blog_name" value="<?=htmlspecialcharsbx($_REQUEST["blog_name"] ?? null)?>" /></td></tr>
	<tr><td><?=GetMessage("P_DESCRIPTION")?>:</td><td><input type="text" name="blog_description" value="<?=htmlspecialcharsbx($_REQUEST["blog_description"] ?? null)?>" /></td></tr>
	<tr class="adm-detail-required-field"><td><?=GetMessage("P_NAME_LATIN")?>:</td><td><input type="text" name="blog_url" value="<?=htmlspecialcharsbx($_REQUEST["blog_url"] ?? null)?>" /></td></tr>
	<tr class="adm-detail-required-field"><td><?=GetMessage("P_GROUP_BLOG")?>:</td><td>
		<input onclick="ChangeStatus(this)" type="radio" name="create_blog_group" id="create_blog_group_n" value="N" <?=(($_REQUEST["create_blog_group"] ?? null) != "Y" ? " checked=\"checked\"" : "")?> />
		<label for="create_blog_group_n"><?=GetMessage("P_SELECT")?>: </label>
		<select name="blog_group_id" <?=(($_REQUEST["create_blog_group"] ?? null) == "Y" ? "disabled=\"disabled\"" : "")?>>
				<?
				$dbBlogGroup = CBlogGroup::GetList(
					array("NAME" => "ASC"),
					array()
				);
				while ($arBlogGroup = $dbBlogGroup->Fetch())
				{
					?><option value="<?=$arBlogGroup["ID"]?>" <?=(($_REQUEST["blog_group_id"] ?? null) == $arBlogGroup["ID"] ? " selected" : "")?>>[<?= htmlspecialcharsbx($arBlogGroup["SITE_ID"]) ?>] <?= htmlspecialcharsbx($arBlogGroup["NAME"]) ?></option><?
				}
				?>
			</select><br />
		<input  onclick="ChangeStatus(this)" type="radio" name="create_blog_group" id="create_blog_group_y" value="Y" <?=(($_REQUEST["create_blog_group"] ?? null) == "Y" ? " checked=\"checked\"" : "")?> />
		<label for="create_blog_group_y"><?=GetMessage("P_CREATE")?>: </label>
		<input type="text" name="blog_group_name" value="<?=htmlspecialcharsbx($_REQUEST["blog_group_name"] ?? null)?>" <?=(($_REQUEST["create_blog_group"] ?? null) != "Y" ? "disabled=\"disabled\"" : "")?>/>

		</td></tr>
	</tbody>


<?
	endif;

?>
<tr><td colspan="2"><input type="submit" value="<?=GetMessage("P_INSTALL")?>" /></td></tr>
</table>
</form>
<script>
function ChangeStatus(pointer)
{
	if (typeof pointer != "object" || (document.forms['photo_form'] == null))
		return false;
	if (pointer.name == 'create_iblock_type')
	{
		document.forms['photo_form'].elements['iblock_type_id'].disabled = (pointer.id == 'create_iblock_type_y');
		document.forms['photo_form'].elements['iblock_type_name'].disabled = !(pointer.id == 'create_iblock_type_y');
	}
	else if (pointer.name == 'create_blog_group')
	{
		document.forms['photo_form'].elements['blog_group_id'].disabled = (pointer.id == 'create_blog_group_y');
		document.forms['photo_form'].elements['blog_group_name'].disabled = !(pointer.id == 'create_blog_group_y');
	}
}

function CheckCreate(pointer)
{
	if (!pointer || typeof pointer != "object" || !document.getElementById(pointer.id + '_create'))
		return false;
	document.getElementById(pointer.id + '_create').style.display = (pointer.checked ? "" : "none");
}
CheckCreate(document.getElementById('iblock'));
CheckCreate(document.getElementById('blog'));
</script>
<?endif;?>