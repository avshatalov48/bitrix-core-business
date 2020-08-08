<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/include.php");

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
if($POST_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(GetMessage("post_title"));

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("post_subscribers"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("post_tab_title"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

?>
<form method="GET" action="posting_search.php" name="post_form">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("post_search_rub")?></td>
	</tr>
	<tr>
		<td width="40%" class="adm-detail-valign-top"><?=GetMessage("post_rub")?>:</td>
		<td width="60%">
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="RUB_ID_ALL" name="RUB_ID_ALL" value="Y" OnClick="CheckAll('RUB_ID', true)"></div>
					<div class="adm-list-label"><label for="RUB_ID_ALL"><?echo GetMessage("MAIN_ALL")?></label></div>
				</div>
			<?
			if(!is_array($RUB_ID))
				$RUB_ID = array();
			$aRub = array();
			$rub = CRubric::GetList(array("LID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), array("ACTIVE"=>"Y"));
			while($ar = $rub->GetNext()):
				$aRub[] = $ar["ID"];
			?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="RUB_ID_<?echo $ar["ID"]?>" name="RUB_ID[]" value="<?echo $ar["ID"]?>"<?if(in_array($ar["ID"], $RUB_ID)) echo " checked"?> OnClick="CheckAll('RUB_ID')"></div>
					<div class="adm-list-label"><label for="RUB_ID_<?echo $ar["ID"]?>"><?echo "[".$ar["LID"]."] ".$ar["NAME"]?></label></div>
				</div>
			<?endwhile;?>
			</div>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("post_format")?></td>
		<td>
			<select name="SUBSCR_FORMAT">
				<option value=""<?if($SUBSCR_FORMAT=="") echo" selected"?>><?echo GetMessage("post_format_any")?></option>
				<option value="text"<?if($SUBSCR_FORMAT=="text") echo" selected"?>><?echo GetMessage("post_format_text")?></option>
				<option value="html"<?if($SUBSCR_FORMAT=="html") echo" selected"?>>HTML</option>
			</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("post_search_users")?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("post_group")?></td>
		<td>
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="GROUP_ID_ALL" name="GROUP_ID_ALL" value="Y" OnClick="CheckAll('GROUP_ID', true)"></div>
					<div class="adm-list-label"><label for="GROUP_ID_ALL"><?echo GetMessage("MAIN_ALL")?></label></div>
				</div>
			<?
			if(!is_array($GROUP_ID))
				$GROUP_ID = array();
			$aGroup = array();
			$group = CGroup::GetList(($by="sort"), ($order="asc"));
			while($ar = $group->GetNext()):
				$aGroup[] = $ar["ID"];
			?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="GROUP_ID_<?echo $ar["ID"]?>" name="GROUP_ID[]" value="<?echo $ar["ID"]?>"<?if(in_array($ar["ID"], $GROUP_ID)) echo " checked"?> OnClick="CheckAll('GROUP_ID')"></div>
					<div class="adm-list-label"><label for="GROUP_ID_<?echo $ar["ID"]?>"><?echo $ar["NAME"]?>&nbsp;[<a target="_blank" href="/bitrix/admin/group_edit.php?ID=<?echo $ar["ID"]?>&amp;lang=<?echo LANGUAGE_ID?>"><?echo $ar["ID"]?></a>]</label></div>
				</div>
			<?endwhile;?>
			</div>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("post_search_filter")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("post_filter")?></td>
		<td><input type="text" name="EMAIL_FILTER" value="<?echo htmlspecialcharsbx($EMAIL_FILTER)?>" size="30" maxlength="255"></td>
	</tr>
<?
$tabControl->Buttons();
?>
<input type="submit" name="search" value="<?echo GetMessage("post_search")?>">
<input type="reset" name="Reset" value="<?echo GetMessage("post_reset")?>">
<input type="hidden" name="search" value="search">
<input type="hidden" name="lang" value="<?echo LANG?>">
<?
$tabControl->End();
?>
<?
$aEmail = array();

/*subscribers*/
$subscr = CSubscription::GetList(
	array("ID"=>"ASC"),
	array("RUBRIC_MULTI"=>$RUB_ID, "CONFIRMED"=>"Y", "ACTIVE"=>"Y", "FORMAT"=>$SUBSCR_FORMAT, "EMAIL"=>$EMAIL_FILTER)
);
while($subscr_arr = $subscr->Fetch())
	$aEmail[$subscr_arr["EMAIL"]] = 1;

/*users by groups*/
if(is_array($GROUP_ID) && count($GROUP_ID)>0)
{
	$arFilter = array("ACTIVE"=>"Y", "EMAIL"=>$EMAIL_FILTER);
	if(!in_array(2, $GROUP_ID))
		$arFilter["GROUP_MULTI"] = $GROUP_ID;

	$user = CUser::GetList(($b="id"), ($o="asc"), $arFilter);
	while($user_arr = $user->Fetch())
		if($user_arr["EMAIL"] <> '')
			$aEmail[$user_arr["EMAIL"]] = 1;
}

$aEmail = array_keys($aEmail);

if(count($aEmail)>0):?>
	<h2><?echo GetMessage("post_result")?></h2>
	<p class="subscribe_border">
		<?echo implode(", ",$aEmail)?>
	</p>
	<p><?echo GetMessage("post_total")?> <b><?echo count($aEmail);?></b></p><?
else:
	CAdminMessage::ShowMessage(GetMessage("post_notfound"));
endif;?>
<script>
<!--
function SetValues()
{
	var d = window.opener.document;
	d.getElementById('EMAIL_FILTER').value="<?echo CUtil::JSEscape($EMAIL_FILTER)?>";
	d.getElementById('SUBSCR_FORMAT').value="<?echo CUtil::JSEscape($SUBSCR_FORMAT)?>";
	<?foreach($aRub as $id):?>
	d.getElementById('RUB_ID_<?echo $id?>').checked = <?echo (in_array($id, $RUB_ID)? "true":"false")?>;
	<?endforeach?>
	<?foreach($aGroup as $id):?>
	d.getElementById('GROUP_ID_<?echo $id?>').checked = <?echo (in_array($id, $GROUP_ID)? "true":"false")?>;
	<?endforeach?>
	window.opener.CheckAll('RUB_ID');
	window.opener.CheckAll('GROUP_ID');
	window.close();
}
function CheckAll(prefix, act)
{
	var bAll = true;
	var aCheckBox;
	try
	{
		if('['+document.post_form.elements[prefix+'[]'].type+']'=='[undefined]')
			var aCheckBox = document.post_form.elements[prefix+'[]'];
		else
			var aCheckBox = new Array(document.post_form.elements[prefix+'[]']);

		for(i=0; i<aCheckBox.length; i++)
			if(!aCheckBox[i].checked)
			{
				if(act)
					aCheckBox[i].checked = true;
				else
					bAll = false;
			}
	}
	catch (e)
	{
		//there is no rubrics so we can safely ignore
	}
	document.getElementById(prefix+'_ALL').checked = bAll;
}
CheckAll('RUB_ID');
CheckAll('GROUP_ID');
//-->
</script>
<input title="<?echo GetMessage("post_search_set_title")?> (<?echo count($aEmail);?>)"  type="button" name="Set" value="<?echo GetMessage("post_set")?>" OnClick="SetValues()" class="adm-btn-save">
<input type="button" name="Close" value="<?echo GetMessage("post_cancel")?>" OnClick="window.close()">
</form>
<?echo BeginNote();?>
<?echo GetMessage("post_search_note")?>
<?echo EndNote();?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php")?>