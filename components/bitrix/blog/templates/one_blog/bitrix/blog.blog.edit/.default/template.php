<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div class="blog-edit">
<?
if($arResult["NEED_AUTH"] == "Y")
{
	?>
	<div class="blog-errors">
		<div class="blog-error-text">
			<ul><?=GetMessage("BLOG_NEED_AUTH")?></ul>
		</div>
	</div>
	<?
}
elseif(!empty($arResult["FATAL_ERROR"])>0)
{
	?>
	<div class="blog-errors">
		<div class="blog-error-text">
			<ul>
			<?
			foreach($arResult["FATAL_ERROR"] as $v)
			{
				?>
				<li><?=$v?></li>
				<?
			}
			?>
			</ul>
		</div>
	</div>
	<?
}
else
{
	if(!empty($arResult["ERROR_MESSAGE"])>0)
	{
		?>
		<div class="blog-errors">
			<div class="blog-error-text">
				<ul>
				<?
				foreach($arResult["ERROR_MESSAGE"] as $v)
				{
					?>
					
					<li><?=$v?></li>
					<?
				}
				?>
				</ul>
			</div>
		</div>
		<?
	}
	?>
	

	<form method="post" action="<?=POST_FORM_ACTION_URI?>" ENCTYPE="multipart/form-data">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="BLOG_URL" value="<?=$arResult["BLOG"]["URL"]?>">
	<table class="blog-table-header-left">
		<tr>
			<th><span class="blog-required-field">*</span> <?=GetMessage('BLOG_TITLE')?></th>
			<td><div class="blog-edit-input"><input type="text" name="NAME" maxlength="100" size="40" value="<?= $arResult["BLOG"]["NAME"]?>"></div></td>
		</tr>
		<tr>
			<th><?=GetMessage('BLOG_DESCR')?></th>
			<td>
				<textarea name="DESCRIPTION" rows="5" cols="40"><?=$arResult["BLOG"]["DESCRIPTION"]?></textarea>
			</td>
		</tr>
		<?
		if($arResult["useCaptcha"] == "U")
		{
			?>
			<tr>
				<th><?=GetMessage('BLOG_AUTO_MSG')?></th>
				<td>
					<input id="IMG_VERIF" type="checkbox" name="ENABLE_IMG_VERIF" value="Y"<?if ($arResult["BLOG"]["ENABLE_IMG_VERIF"] != "N") echo " checked";?>>
					<label for="IMG_VERIF"><?=GetMessage('BLOG_AUTO_MSG_TITLE')?></label><br />
					<small><?=GetMessage('BLOG_CAPTHA')?></small>
				</td>
			</tr>
			<?
		}
		?>
		<tr>
			<th><?=GetMessage('BLOG_EMAIL_NOTIFY')?></th>
			<td>
				<input id="EMAIL_NOTIFY" type="checkbox" name="EMAIL_NOTIFY" value="Y"<?if ($arResult["BLOG"]["EMAIL_NOTIFY"] != "N") echo " checked";?>>
				<label for="EMAIL_NOTIFY"><?=GetMessage('BLOG_EMAIL_NOTIFY_TITLE')?></label><br />
				<small><?=GetMessage('BLOG_EMAIL_NOTIFY_HELP')?></small>
			</td>
		</tr>
		<tr>
			<th><?=GetMessage('BLOG_OPENED_GRPS')?></th>
			<td>
				<script>
				function group_edit(id)
				{
					if (id == 0)
						document.getElementById("group_name_").value = '';
					else
						document.getElementById("group_name_"+id).value = document.getElementById("grp_name_" + id).value;
					
					showForm(1, id);
				}

				function group_del(id)
				{
					if (document.getElementById("grp_count_" + id).value == 0 || confirm("<?=GetMessage("BLOG_CONFIRM_DELETE")?>"))
					{
						document.getElementById("grp_delete_"+id).value = 'Y';
						document.getElementById("group-line-"+id).style.display = "none";
					}
				}

				function showForm(flag, id)
				{
					if (flag==1)
					{
						document.getElementById("group-form-"+id).style.display = 'inline';
						document.getElementById("group-name-"+id).style.display = 'none';
						document.getElementById("group_name_"+id).focus();
					}
					else
					{
						document.getElementById("group-form-"+id).style.display = 'none';
						document.getElementById("group-name-"+id).style.display = 'inline';
					}
				}
				
				function saveGroup(id)
				{
					if(document.getElementById('group_name_'+id).value.length > 0)
					{
						document.getElementById('group-label-'+id).innerHTML = document.getElementById('group_name_'+id).value;
						document.getElementById('grp_name_'+id).value = document.getElementById('group_name_'+id).value;
						showForm(0, id);
					}
				}
				
				function newGroup()
				{
					id = 'n' + document.getElementById('newGroupCount').value;
					newHtml = '<div id="group-line-'+id+'">' +
						'<input id="open_group_'+id+'" type="checkbox" name="group['+id+']" value="Y">' +
						'<span id="group-form-'+id+'" style="display:inline;"><input name="grp_name['+id+']" id="group_name_'+id+'" size="20" maxlength="255" value=""> <input type="button" name="blog_save" value="Ok" onclick="javascript:saveGroup(\''+id+'\');"></span>'+
						'<span id="group-name-'+id+'" class="blog-group" style="display:none;">' +
							'<label for="open_group_'+id+'">&nbsp;<span class="blog-group-label" id="group-label-'+id+'"></span> (0)</label>' +
							'<input type="hidden" name="grp_count['+id+']" id="grp_count_'+id+'" value="0">' +
							'<input type="hidden" name="grp_name['+id+']" id="grp_name_'+id+'" value="">' +
							'<input type="hidden" name="grp_delete['+id+']" id="grp_delete_'+id+'" value="">' +
							'&nbsp;<a href="javascript:group_edit(\''+id+'\')" title="<?=GetMessage("BLOG_NAME_CHANGE")?>" class="blog-group-edit"></a>' +
							'<a href="javascript:group_del(\''+id+'\')" title="<?=GetMessage("BLOG_GROUP_DELETE")?>" class="blog-group-delete"></a>' +
						'</span>' +
					'</div>';
					//alert(document.getElementById('forNewGroup').innerHTML);
					//document.getElementById('forNewGroup').innerHTML += newHtml;
					document.getElementById('forNewGroup1').innerHTML = newHtml;
					document.getElementById('forNewGroup').appendChild(document.getElementById('group-line-'+id));
					document.getElementById('newGroupCount').value += 1;
					
				}
				</script>

				<?
				foreach($arResult["USER_GROUP"] as $v)
				{
					?>
					<div id="group-line-<?=$v["ID"]?>">
						<input id="open_group_<?=$v["ID"]?>" type="checkbox" name="group[<?=$v['ID']?>]"<?if($v["CHECKED"] == "Y") echo " checked";?> value="Y">
						<span id="group-form-<?=$v["ID"]?>" style="display:none;"><input name="GROUP_NAME" id="group_name_<?=$v["ID"]?>" size="20" maxlength="255" value="<?=$v["NAME"]?>"> <input type="button" name="blog_save" value="Ok" onclick="javascript:saveGroup('<?=$v["ID"]?>');"></span>
						
						<span id="group-name-<?=$v["ID"]?>" class="blog-group">
							<label for="open_group_<?=$v["ID"]?>"><span class="blog-group-label" id="group-label-<?=$v["ID"]?>"><?=$v["NAME"]?></span> (<?=intval($v["CNT"])?>)</label>
							<input type="hidden" name="grp_count[<?=$v["ID"]?>]" id="grp_count_<?=$v["ID"]?>" value="<?=intval($v["CNT"])?>">
							<input type="hidden" name="grp_name[<?=$v["ID"]?>]" id="grp_name_<?=$v["ID"]?>" value="<?=$v["NAME"]?>">
							<input type="hidden" name="grp_delete[<?=$v["ID"]?>]" id="grp_delete_<?=$v["ID"]?>" value="">
							&nbsp;<a href="javascript:group_edit('<?=$v["ID"]?>')" title="<?=GetMessage("BLOG_NAME_CHANGE")?>"><span class="blog-group-edit"></span></a>
							<a href="javascript:group_del('<?=$v["ID"]?>')" title="<?=GetMessage("BLOG_GROUP_DELETE")?>"><span class="blog-group-delete"></span></a>
						</span>
					</div>
					
					<?
				}
				?>
				<div id="forNewGroup"></div>
				<div id="forNewGroup1" style="display:none;"></div>
				<div><a href="javascript:newGroup()"><?=GetMessage("BLOG_GROUP_ADD")?></a></div>
				<input type="hidden" id="newGroupCount" value="0">
				<small><?=GetMessage('BLOG_OPENED_TITLE')?></small>
			</td>
		</tr>
		<?	

		function ShowSelectPerms($type, $id, $def, $arr)
		{
			if(empty($def))
			{
				if($type == "p")
					$def = BLOG_PERMS_READ;
				elseif($type == "c")
					$def = BLOG_PERMS_WRITE;
			}
			
			$res = "<select name='perms_".$type."[".$id."]'>";
			foreach($arr as $key)
				if ($id > 1 || ($type=='p' && $key <= BLOG_PERMS_READ) || ($type=='c' && $key <= BLOG_PERMS_WRITE))
					$res.= "<option value='".$key."'".(($key == $def)?' selected':'').">".$GLOBALS["AR_BLOG_PERMS"][$key]."</option>";
			$res.= "</select>";
			return $res;
		}
		?>
		<tr>
			<th><?=GetMessage('BLOG_DEF_PERMS')?></th>
			<td>
				<table class="blog-edit-perms-table">
					<tr>
						<th><?=GetMessage('BLOG_GROUPS')?></th>
						<th><?=GetMessage('BLOG_MESSAGES')?></th>
						<th><?=GetMessage('BLOG_COMMENTS')?></th>
					</tr>
					<tr>
						<td nowrap><?=GetMessage('BLOG_ALL_USERS')?></td>
						<td><?
							if(!empty($arResult["ar_post_everyone_rights"]))
								echo ShowSelectPerms('p', 1, $arResult["BLOG"]["perms_p"][1], $arResult["ar_post_everyone_rights"]);
							else
								echo ShowSelectPerms('p', 1, $arResult["BLOG"]["perms_p"][1], $arResult["BLOG_POST_PERMS"]);
						?></td>
						<td><?
							if(!empty($arResult["ar_comment_everyone_rights"]))
								echo ShowSelectPerms('c', 1, $arResult["BLOG"]["perms_c"][1], $arResult["ar_comment_everyone_rights"]);
							else
								echo ShowSelectPerms('c', 1, $arResult["BLOG"]["perms_c"][1], $arResult["BLOG_COMMENT_PERMS"]);
						?></td>
					</tr>
					<tr>
						<td nowrap><?=GetMessage('BLOG_REGISTERED')?></td>
						<td><?
							if(!empty($arResult["ar_post_auth_user_rights"]))
								echo ShowSelectPerms('p', 2, $arResult["BLOG"]["perms_p"][2], $arResult["ar_post_auth_user_rights"]);
							else
								echo ShowSelectPerms('p', 2, $arResult["BLOG"]["perms_p"][2], $arResult["BLOG_POST_PERMS"]);
						?></td>
						<td><?
							if(!empty($arResult["ar_comment_auth_user_rights"]))
								echo ShowSelectPerms('c', 2, $arResult["BLOG"]["perms_c"][2], $arResult["ar_comment_auth_user_rights"]);
							else
								echo ShowSelectPerms('c', 2, $arResult["BLOG"]["perms_c"][2], $arResult["BLOG_COMMENT_PERMS"]);
						?></td>
					</tr>
					
					<?
					if(!empty($arResult["USER_GROUP"]))
					{
						foreach($arResult["USER_GROUP"] as $v)
						{
							?>
							<tr>
								<td nowrap><?=$v['NAME']?></td>
								<td><?
									if(!empty($arResult["ar_post_group_user_rights"]))
										echo ShowSelectPerms('p', $v['ID'], $arResult["BLOG"]["perms_p"][$v['ID']], $arResult["ar_post_group_user_rights"]);
									else
										echo ShowSelectPerms('p', $v['ID'], $arResult["BLOG"]["perms_p"][$v['ID']], $arResult["BLOG_POST_PERMS"]);
								?></td>
								<td><?
									if(!empty($arResult["ar_comment_group_user_rights"]))
										echo ShowSelectPerms('c', $v['ID'], $arResult["BLOG"]["perms_c"][$v['ID']], $arResult["ar_comment_group_user_rights"]);
									else
										echo ShowSelectPerms('c', $v['ID'], $arResult["BLOG"]["perms_c"][$v['ID']], $arResult["BLOG_COMMENT_PERMS"]);
								?></td>
							</tr>
							<?
						}
					}
					?>
				</table>
			</td>
		</tr>
		<?if($arResult["BLOG_PROPERTIES"]["SHOW"] == "Y"):?>
			<?foreach ($arResult["BLOG_PROPERTIES"]["DATA"] as $FIELD_NAME => $arBlogField):?>
			<tr>
				<th><?=$arBlogField["EDIT_FORM_LABEL"]?>:</th>
				<td>
						<?$APPLICATION->IncludeComponent(
							"bitrix:system.field.edit", 
							$arBlogField["USER_TYPE"]["USER_TYPE_ID"], 
							array("arUserField" => $arBlogField), null, array("HIDE_ICONS"=>"Y"));?>
				</td>
			</tr>			
			<?endforeach;?>
		<?endif;?>
		</table>
		<div class="blog-buttons">
			<input type="submit" name="save" value="<?= (intval($arResult["BLOG"]["ID"])>0 ? GetMessage('BLOG_SAVE') : GetMessage('BLOG_CREATE')) ?>">
			<?
			if ($arResult["CAN_UPDATE"]=="Y")
			{
				?>
				<input type="submit" name="apply" value="<?=GetMessage('BLOG_APPLY')?>">
				<input type="submit" name="reset" value="<?=GetMessage('BLOG_CANCEL')?>">
				<?
			}
			?>
			<input type="hidden" name="do_blog" value="Y">
		</div>
	</form>
	
	<p>
		<?echo GetMessage("STOF_REQUIED_FIELDS_NOTE")?>
	</p>
	<?
}
?>
</div>