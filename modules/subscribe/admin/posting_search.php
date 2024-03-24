<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/include.php';
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = CMain::GetUserRight('subscribe');
if ($POST_RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$SUBSCR_FORMAT = (string)$request['SUBSCR_FORMAT'];
$EMAIL_FILTER = (string)$request['EMAIL_FILTER'];
$RUB_ID = is_array($request['RUB_ID']) ? $request['RUB_ID'] : [];
$GROUP_ID = is_array($request['GROUP_ID']) ? $request['GROUP_ID'] : [];

$APPLICATION->SetTitle(GetMessage('post_title'));

$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => GetMessage('post_subscribers'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('post_tab_title'),
	],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs, true, true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_popup_admin.php';

?>
<form method="GET" action="posting_search.php" name="post_form">
<?php
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('post_search_rub')?></td>
	</tr>
	<tr>
		<td width="40%" class="adm-detail-valign-top"><?=GetMessage('post_rub')?>:</td>
		<td width="60%">
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="RUB_ID_ALL" name="RUB_ID_ALL" value="Y" OnClick="CheckAll('RUB_ID', true)"></div>
					<div class="adm-list-label"><label for="RUB_ID_ALL"><?php echo GetMessage('MAIN_ALL')?></label></div>
				</div>
			<?php
			$aRub = [];
			$rub = CRubric::GetList(['LID' => 'ASC', 'SORT' => 'ASC', 'NAME' => 'ASC'], ['ACTIVE' => 'Y']);
			while ($ar = $rub->GetNext()):
				$aRub[] = $ar['ID'];
			?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="RUB_ID_<?php echo $ar['ID']?>" name="RUB_ID[]" value="<?php echo $ar['ID']?>" <?php echo (in_array($ar['ID'], $RUB_ID)) ? 'checked' : '';?> OnClick="CheckAll('RUB_ID')"></div>
					<div class="adm-list-label"><label for="RUB_ID_<?php echo $ar['ID']?>"><?php echo '[' . $ar['LID'] . '] ' . $ar['NAME']?></label></div>
				</div>
			<?php endwhile;?>
			</div>
		</td>
	</tr>
	<tr>
		<td><?php echo GetMessage('post_format')?></td>
		<td>
			<select name="SUBSCR_FORMAT">
				<option value="" <?php echo ($SUBSCR_FORMAT === '') ? 'selected' : '';?>><?php echo GetMessage('post_format_any')?></option>
				<option value="text" <?php echo ($SUBSCR_FORMAT === 'text') ? 'selected' : '';?>><?php echo GetMessage('post_format_text')?></option>
				<option value="html" <?php echo ($SUBSCR_FORMAT === 'html')? 'selected' : '';?>>HTML</option>
			</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('post_search_users')?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?php echo GetMessage('post_group')?></td>
		<td>
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="GROUP_ID_ALL" name="GROUP_ID_ALL" value="Y" OnClick="CheckAll('GROUP_ID', true)"></div>
					<div class="adm-list-label"><label for="GROUP_ID_ALL"><?php echo GetMessage('MAIN_ALL')?></label></div>
				</div>
			<?php
			$aGroup = [];
			$group = CGroup::GetList();
			while ($ar = $group->GetNext()):
				$aGroup[] = $ar['ID'];
			?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="GROUP_ID_<?php echo $ar['ID']?>" name="GROUP_ID[]" value="<?php echo $ar['ID']?>"<?php echo (in_array($ar['ID'], $GROUP_ID)) ? 'checked' : '';?> OnClick="CheckAll('GROUP_ID')"></div>
					<div class="adm-list-label"><label for="GROUP_ID_<?php echo $ar['ID']?>"><?php echo $ar['NAME']?>&nbsp;[<a target="_blank" href="/bitrix/admin/group_edit.php?ID=<?php echo $ar['ID']?>&amp;lang=<?php echo LANGUAGE_ID?>"><?php echo $ar['ID']?></a>]</label></div>
				</div>
			<?php endwhile;?>
			</div>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('post_search_filter')?></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('post_filter')?></td>
		<td><input type="text" name="EMAIL_FILTER" value="<?php echo htmlspecialcharsbx($EMAIL_FILTER)?>" size="30" maxlength="255"></td>
	</tr>
<?php
$tabControl->Buttons();
?>
<input type="submit" name="search" value="<?php echo GetMessage('post_search')?>">
<input type="reset" name="Reset" value="<?php echo GetMessage('post_reset')?>">
<input type="hidden" name="search" value="search">
<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID?>">
<?php
$tabControl->End();
?>
<?php
$aEmail = [];

/*subscribers*/
$subscr = CSubscription::GetList(
	['ID' => 'ASC'],
	['RUBRIC_MULTI' => $RUB_ID, 'CONFIRMED' => 'Y', 'ACTIVE' => 'Y', 'FORMAT' => $SUBSCR_FORMAT, 'EMAIL' => $EMAIL_FILTER]
);
while ($subscr_arr = $subscr->Fetch())
{
	$aEmail[$subscr_arr['EMAIL']] = 1;
}

/*users by groups*/
if (is_array($GROUP_ID) && count($GROUP_ID) > 0)
{
	$arFilter = ['ACTIVE' => 'Y', 'EMAIL' => $EMAIL_FILTER];
	if (!in_array(2, $GROUP_ID))
	{
		$arFilter['GROUP_MULTI'] = $GROUP_ID;
	}

	$user = CUser::GetList('id', 'asc', $arFilter);
	while ($user_arr = $user->Fetch())
	{
		if ($user_arr['EMAIL'] <> '')
		{
			$aEmail[$user_arr['EMAIL']] = 1;
		}
	}
}

$aEmail = array_keys($aEmail);

if (count($aEmail) > 0):?>
	<h2><?php echo GetMessage('post_result')?></h2>
	<p class="subscribe_border">
		<?php echo implode(', ',$aEmail)?>
	</p>
	<p><?php echo GetMessage('post_total')?> <b><?php echo count($aEmail);?></b></p><?php
else:
	CAdminMessage::ShowMessage(GetMessage('post_notfound'));
endif;?>
<script>
<!--
function SetValues()
{
	var d = window.opener.document;
	d.getElementById('EMAIL_FILTER').value="<?php echo CUtil::JSEscape($EMAIL_FILTER)?>";
	d.getElementById('SUBSCR_FORMAT').value="<?php echo CUtil::JSEscape($SUBSCR_FORMAT)?>";
	<?php foreach ($aRub as $id):?>
	d.getElementById('RUB_ID_<?php echo $id?>').checked = <?php echo (in_array($id, $RUB_ID) ? 'true' : 'false')?>;
	<?php endforeach?>
	<?php foreach ($aGroup as $id):?>
	d.getElementById('GROUP_ID_<?php echo $id?>').checked = <?php echo (in_array($id, $GROUP_ID) ? 'true' : 'false')?>;
	<?php endforeach?>
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
<input title="<?php echo GetMessage('post_search_set_title')?> (<?php echo count($aEmail);?>)"  type="button" name="Set" value="<?php echo GetMessage('post_set')?>" OnClick="SetValues()" class="adm-btn-save">
<input type="button" name="Close" value="<?php echo GetMessage('post_cancel')?>" OnClick="window.close()">
</form>
<?php echo BeginNote();?>
<?php echo GetMessage('post_search_note')?>
<?php echo EndNote();?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_popup_admin.php';
