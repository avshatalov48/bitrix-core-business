<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
CModule::IncludeModule('support');
IncludeModuleLangFile(__FILE__);

$FMUTagName = 'USER_IDS';
$FMUFormID = 'form1';

$bDemo = CTicket::IsDemo();
$bAdmin = CTicket::IsAdmin();

if(!$bAdmin && !$bDemo)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$LIST_URL = '/bitrix/admin/ticket_group_list.php';
	
$ID = intval($ID);

$message = false;

if (($save <> '' || $apply <> '') && $REQUEST_METHOD=='POST' && $bAdmin && check_bitrix_sessid())
{
	$obSUG = new CSupportUserGroup();
	$bOK = false;
	$new = false;
	
	$arParams = array(
		'NAME' => $_POST['NAME'],
		'SORT' => intval($_POST['SORT']),
		'XML_ID' => $_POST['XML_ID'],
		'IS_TEAM_GROUP' => $_POST['IS_TEAM_GROUP'],
	);
	
	if ($ID > 0)
	{
		$bOK = $obSUG->Update($ID, $arParams);
	}
	else 
	{
		if ($ID = $obSUG->Add($arParams))
		{
			$bOK = true;
			$new = true;
		}
	}
	
	if ($bOK && isset($_POST[$FMUTagName]['VALS']) && is_array($_POST[$FMUTagName]['VALS']))
	{
		$UIDS = array_map('intval', $_POST[$FMUTagName]['VALS']);
		$UIDS = array_unique($UIDS);
		$USERS = array();
		foreach ($UIDS as $k => $v)
		{
			$USERS[] = array(
					'USER_ID' => $v,
					'CAN_VIEW_GROUP_MESSAGES' => $_POST[$FMUTagName]['CHECKS'][$k],
					'CAN_MAIL_GROUP_MESSAGES' => $_POST[$FMUTagName]['MAIL'][$k],
					'CAN_MAIL_UPDATE_GROUP_MESSAGES' => $_POST[$FMUTagName]['MAIL_UPDATE'][$k]
			);
		}
		
		$errors = CSupportUser2UserGroup::SetGroupUsers($ID, $USERS);
		$bOK = count($errors) <= 0;
		if (!$bOK)
		{
			$APPLICATION->ThrowException(implode('<br>', $errors));
		}
	}
	
	if ($bOK)
	{
		if ($save <> '') LocalRedirect($LIST_URL . '?lang=' . LANG);
		elseif ($new) LocalRedirect($APPLICATION->GetCurPage() . '?ID='.$ID. '&lang='.LANG.'&tabControl_active_tab='.urlencode($tabControl_active_tab));
	}
	else 
	{
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage('SUP_GE_ERROR'), $e);
	}	
}

$rsGroups = CSupportUserGroup::GetList(false, array('ID' => $ID));
$arGroup = $rsGroups->GetNext();
if (!$arGroup)
{
	$ID = 0;
}

$arGroupUsers = array();

if ($arGroup)
{
	$rs_ug = CSupportUser2UserGroup::GetList(false, array('GROUP_ID' => $ID));
	while ($ar_ug = $rs_ug->GetNext())
	{
		$arGroupUsers[] = array(
			'USER_ID' => $ar_ug['USER_ID'],
			'CAN_VIEW_GROUP_MESSAGES' => $ar_ug['CAN_VIEW_GROUP_MESSAGES'],
			'CAN_MAIL_GROUP_MESSAGES' => $ar_ug['CAN_MAIL_GROUP_MESSAGES'],
			'CAN_MAIL_UPDATE_GROUP_MESSAGES' => $ar_ug['CAN_MAIL_UPDATE_GROUP_MESSAGES'],
			'USER_NAME' => '[<a title="'.GetMessage("MAIN_USER_PROFILE").'" href="user_edit.php?ID='.$ar_ug["USER_ID"].'&amp;lang='.LANG.'">'.$ar_ug["USER_ID"].'</a>] ('.$ar_ug["LOGIN"].') '.$ar_ug["FIRST_NAME"].' '.$ar_ug["LAST_NAME"],
		);
	}
}

$arGroupUsers[] = array('USER_ID' => '');
$arGroupUsers[] = array('USER_ID' => '');
$arGroupUsers[] = array('USER_ID' => '');
	

if ($ID > 0)
{
	$APPLICATION->SetTitle(GetMessage('SUP_GE_TITLE_EDIT', array('%GROUP_NAME%' => $arGroup['~NAME'])));
}
else 
{
	$APPLICATION->SetTitle(GetMessage('SUP_GE_TITLE_NEW'));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		'ICON'	=> 'btn_list',
		'TEXT'	=> GetMessage('SUP_GE_GROUPS_LIST'), 
		'LINK'	=> $LIST_URL . '?lang=' . LANG
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
	echo $message->Show();

$aTabs = array();
$aTabs[] = array(
	'DIV' => 'edit1',
	'TAB' => GetMessage('SUP_GE_GROUP'),
	'TITLE'=>GetMessage('SUP_GE_GROUP_TITLE')
);
$aTabs[] = array(
	'DIV' => 'edit2',
	'TAB' => GetMessage('SUP_GE_GROUP_USERS'),
	'TITLE'=>GetMessage('SUP_GE_GROUP_USERS_TITLE')
);
$tabControl = new CAdminTabControl('tabControl', $aTabs, true, true);

?>
<form name="<?=$FMUFormID?>" method="POST" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>&ID=<?=$ID?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value="<?=$ID?>">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?$tabControl->Begin();?>

<?$tabControl->BeginNextTab();?>
<tr class="adm-detail-required-field"> 
	<td align="right" width="40%"><?=GetMessage('SUP_GE_NAME')?>:</td>
	<td width="60%"><input type="text" name="NAME" size="40" maxlength="255" value="<?=$arGroup['NAME']?>"></td>
</tr>
<tr> 
	<td align="right"><?=GetMessage('SUP_GE_SORT')?>:</td>
	<td><input type="text" name="SORT" size="5" maxlength="255" value="<?=$arGroup['SORT']?>"></td>
</tr>
<tr> 
	<td align="right"><?=GetMessage('SUP_GE_XML_ID')?>:</td>
	<td><input type="text" name="XML_ID" size="40" maxlength="255" value="<?=$arGroup['XML_ID']?>"></td>
</tr>
<tr> 
	<td align="right"><?=GetMessage('SUP_GE_IS_TEAM_GROUP')?>:</td>
	<td><input type="checkbox" name="IS_TEAM_GROUP" value="Y"<?if ($arGroup['IS_TEAM_GROUP'] == 'Y'){?> checked<?}?>></td>
</tr>

<?$tabControl->BeginNextTab();?>

<tr valign="top"> 
	<td align="right"><?=GetMessage('SUP_GE_GROUP_USERS')?>:</td>
	<td>

		<table id="FMUtab">
		<tr>
			<td><?=GetMessage('SUP_GE_USER')?></td>
			<td style="padding-right: 10px"><?=GetMessage('SUP_GE_CAN_VIEW')?></td>
			<td><?=GetMessage('SUP_GE_CAN_MAIL')?></td>
			<td><?=GetMessage('SUP_GE_CAN_MAIL_UPDATE')?></td>
		</tr>
		<?
		$i = 0;
		$UIDS = array();
		foreach ($arGroupUsers as $val)
		{
			$UIDS[$i] = '';
			$UserPr = ( (string) $val['USER_ID'] <> '' );
			if( $UserPr ) $UIDS[$i] = intval($val['USER_ID']);
			$cVgm = ( $val['CAN_VIEW_GROUP_MESSAGES'] == "Y" || !$UserPr ) ? " checked" : "";
			$cMgm = ( $val['CAN_MAIL_GROUP_MESSAGES'] == "Y" || !$UserPr ) ? " checked" : "";
			$cMUgm = ( $val['CAN_MAIL_UPDATE_GROUP_MESSAGES'] == "Y" || !$UserPr ) ? " checked" : "";
						
		?>
		<tr>
		<td>
			<input type="text" id="<?=$FMUTagName?>[VALS][<?=$i?>]" name="<?=$FMUTagName?>[VALS][<?=$i?>]" value="<?=$UIDS[$i]?>" size="5">
			<iframe style="width:0px; height:0px; border:0px" src="javascript:''" name="FMUhiddenframe<?=$i?>" id="FMUhiddenframe<?=$i?>"></iframe>
			<input class="" type="button" name="FMUButton<?=$i?>" id="FMUButton<?=$i?>" OnClick="window.open('/bitrix/admin/user_search.php?lang=<?=LANGUAGE_ID?>&FN=<?=$FMUFormID?>&FC=<?=urlencode($FMUTagName.'[VALS]['.$i.']')?>', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));" value="...">
			<span id="div_FMUdivUN<?=$i?>"><?=$val['USER_NAME']?></span>
		</td>
		<td><input type="checkbox" name="<?=$FMUTagName?>[CHECKS][<?=$i?>]"<?=$cVgm?> value="Y"></td>
		<td><input type="checkbox" name="<?=$FMUTagName?>[MAIL][<?=$i?>]"<?=$cMgm?> value="Y"></td>
		<td><input type="checkbox" name="<?=$FMUTagName?>[MAIL_UPDATE][<?=$i?>]"<?=$cMUgm?> value="Y"></td>
		</tr>
		<?
			$i++;
		}
		?>
		
		<tr>
		<td colspan="2"><input type="button" value="<?=GetMessage('SUP_GE_ADD_MORE_USERS')?>" onclick="window.open('/bitrix/admin/user_search.php?lang=<?=LANGUAGE_ID?>&JSFUNC=usergroups', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));"></td>
		</tr>
		</table>
		
		<script type="text/javascript">
		
		var rowCounter = <?=intval($i)?>;
		var UIDS = new Array();
		<?foreach ($UIDS as $k => $v){?>
		UIDS[<?=$k?>] = '<?=$v?>';
		<?}?>
		
		function SUVUpdateUserNames()
		{
			var str;
			var div;
			for(i in UIDS)
			{
				//alert(document.<?echo $FMUFormID;?>["<?=$FMUTagName?>[VALS]["+String(i)+"]"].value);
				str = document.<?echo $FMUFormID;?>["<?=$FMUTagName?>[VALS]["+String(i)+"]"].value;
				if(str.length > 0)
				{
					if(String(UIDS[i]) != str)
					{
						div = document.getElementById('div_FMUdivUN'+String(i));
						div.innerHTML = '<i><?=GetMessage('MAIN_WAIT')?></i>';
						document.getElementById("FMUhiddenframe"+String(i)).src='/bitrix/admin/get_user.php?ID=' + str + '&strName=FMUdivUN'+String(i)+'&lang=<?=LANG?><?=(defined("ADMIN_SECTION") && ADMIN_SECTION===true?"&admin_section=Y":"")?>';
						UIDS[i] = str;
					}
				}
			}
			
			setTimeout(function(){SUVUpdateUserNames()},1000);
		}
		
		SUVUpdateUserNames();
		
		function SUVusergroups(USER_ID)
		{
			var oTbl=document.getElementById('FMUtab');
			
			var sRowCounter = String(rowCounter);
			
			var newRow = oTbl.insertRow(oTbl.rows.length - 1);
			var newCell1 = newRow.insertCell(-1);
			newCell1.innerHTML = '<input type="text" id="<?=$FMUTagName?>[VALS]['+sRowCounter+']" name="<?=$FMUTagName?>[VALS]['+sRowCounter+']" value="'+String(USER_ID)+'" size="5"> ' +
				'<iframe style="width:0px; height:0px; border:0px" src="javascript:\'\'" name="FMUhiddenframe'+sRowCounter+'" id="FMUhiddenframe'+sRowCounter+'"></iframe> ' +
				'<input class="" type="button" name="FMUButton'+sRowCounter+'" id="FMUButton'+sRowCounter+'" OnClick="window.open(\'/bitrix/admin/user_search.php?lang=<?=LANGUAGE_ID?>&FN=<?=$FMUFormID?>&FC=<?=urlencode($FMUTagName)?>%5BVALS%5D%5B'+sRowCounter+'%5D\', \'\', \'scrollbars=yes,resizable=yes,width=760,height=500,top=\'+Math.floor((screen.height - 560)/2-14)+\',left=\'+Math.floor((screen.width - 760)/2-5));" value="..."> ' + 
				'<span id="div_FMUdivUN'+sRowCounter+'"></span>';
			
			var newCell2 = newRow.insertCell(-1);
			newCell2.innerHTML = '<input type="checkbox" name="<?=$FMUTagName?>[CHECKS]['+sRowCounter+']" value="Y" checked>';
			var newCell3 = newRow.insertCell(-1);
			newCell3.innerHTML = '<input type="checkbox" name="<?=$FMUTagName?>[MAIL]['+sRowCounter+']" value="Y" checked>';
			var newCell4 = newRow.insertCell(-1);
			newCell4.innerHTML = '<input type="checkbox" name="<?=$FMUTagName?>[MAIL_UPDATE]['+sRowCounter+']" value="Y" checked>';

			BX.adminPanel.modifyFormElements(newCell2);
			BX.adminPanel.modifyFormElements(newCell3);
			BX.adminPanel.modifyFormElements(newCell4);
			
			UIDS[rowCounter] = '';
			rowCounter++;
		}
		
		</script>	
	
	</td>
</tr>

<?
$tabControl->Buttons(Array("disabled"=>!$bAdmin, 'back_url' => $LIST_URL . '?lang=' . LANGUAGE_ID));
$tabControl->End();
?>
</form>

<?echo BeginNote();?>
<span style="font-weight: bold;"><?echo GetMessage("REQUIRED_FIELDS")?></span>
<?echo EndNote();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>