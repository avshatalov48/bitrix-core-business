<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if($arResult["FATAL_ERROR_MESSAGE"] <> '')
{
	?>
	<span class='errortext'><?=$arResult["FATAL_ERROR_MESSAGE"]?></span><br /><br />
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
	?>
	<script>
	function group_edit(id)
	{
		if (id == 0)
			document.getElementById("group_name").value = '';
		else
			document.getElementById("group_name").value = document.getElementById("name_" + id).value;
		document.getElementById("group_id").value = id;
		show_form(1);
	}

	function group_del(id)
	{
		if (document.getElementById("count_" + id).value == 0 || confirm("<?=GetMessage("BLOG_CONFIRM_DELETE")?>"))
		{
			document.getElementById("group_id").value = id;
			document.getElementById("group_del").value = "Y";
			document.REPLIER.submit();
		}
	}

	function show_form(flag)
	{
		if (flag==1)
		{
			document.getElementById("edit_form").style.display = 'block';
			document.getElementById("group_name").focus();
		}
		else
			document.getElementById("edit_form").style.display = 'none';
	}
	</script>
	<form action="<?=POST_FORM_ACTION_URI?>" name="REPLIER" method="post" enctype="multipart/form-data">
	<?=bitrix_sessid_post();?>
	<input type="hidden" name="ID" id="group_id">
	<input type="hidden" name="group_del" id="group_del">

	<table border=0 cellspacing=1 cellpadding=3 class="blog-group" width=300>
	<?
	if(!empty($arResult["USER_GROUP"]))
	{
	foreach($arResult["USER_GROUP"] as $v)
	{
		?>
		<input type="hidden" id="count_<?=$v["ID"]?>" value="<?=$v["CNT"]?>">
		<input type="hidden" id="name_<?=$v["ID"]?>" value="<?=$v["NAME"]?>">
		<tr>
			<td width="100%" nowrap><?=$v["NAME"]?> (<?=$v["CNT"]?>)</td>
			<td><a href="javascript:group_edit('<?=$v["ID"]?>')" title="<?=GetMessage("BLOG_NAME_CHANGE")?>" class="blog-group-edit"></a></td>
			<td><a href="javascript:group_del('<?=$v["ID"]?>')" title="<?=GetMessage("BLOG_GROUP_DELETE")?>" class="blog-group-delete"></a></td>
		</tr>
		<?
	}
	}
	?>
	<tr>
		<td colspan="3">
			<div>
			<a href="javascript:group_edit(0)" title="<?=GetMessage("BLOG_GROUP_ADD")?>" class="blog-group-add"></a>&nbsp;
			<a href="javascript:group_edit(0)" title="<?=GetMessage("BLOG_GROUP_ADD")?>"><?=GetMessage("BLOG_ADD")?></a><br clear="all" />
			</div>
			<div id="edit_form" style="display:none">
				<?=GetMessage("BLOG_GROUP_NAME")?><br />
				<input name="NAME" id="group_name" style="width:100%" maxlength="255">
				<input type="hidden" name="save" value="Y">
				<input type="submit" name="save" value=" OK ">
				<input type="button" onclick="show_form(0)" value="<?=GetMessage("BLOG_CANCEL")?>">
				<input type="hidden" name="BACK_URL" value="<?=$arResult["BACK_URL"]?>">
			</div>
		</td>
	</tr>
	</table>
	</form>
<?
}