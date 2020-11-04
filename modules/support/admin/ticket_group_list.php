<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
CModule::IncludeModule('support');

IncludeModuleLangFile(__FILE__);

$bDemo = CTicket::IsDemo();
$bAdmin = CTicket::IsAdmin();

if(!$bAdmin && !$bDemo)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
$EDIT_URL = "/bitrix/admin/ticket_group_edit.php";
$LIST_URL = $APPLICATION->GetCurPage();


$sTableID = 't_ugroups_list';
$oSort = new CAdminSorting($sTableID, 'SORT', 'asc');
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID.'_filter_id', 
	array(
		'ID',
		GetMessage('SUP_GL_FLT_IS_TEAM_GROUP'),
	)
);

$arFilterFields = Array(
	'FIND_NAME',
	'FIND_NAME_EXACT_MATCH',
	'FIND_ID',
	'FIND_IS_TEAM_GROUP',
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
foreach($arFilterFields as $key)
{
	if (mb_strpos($key, '_EXACT_MATCH') !== false) continue;
	
	if (array_key_exists($key . '_EXACT_MATCH', $_REQUEST) && $_REQUEST[$key . '_EXACT_MATCH'] == 'Y')
	{
		$op = '=';
	}
	else 
	{
		$op = '%';
	}
	
	if (array_key_exists($key, $_REQUEST) && (string) $_REQUEST[$key] <> '')
	{
		if (in_array($key . '_EXACT_MATCH', $arFilterFields))
		{
			$arFilter[$op.mb_substr($key, 5)] = $_REQUEST[$key];
		}
		else 
		{
			$arFilter[mb_substr($key, 5)] = $_REQUEST[$key];
		}
	}
}

if ($bAdmin && $lAdmin->EditAction()) //если идет сохранение со списка
{
	$obSUG = new CSupportUserGroup();
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

			
		$arUpdate = array( "SORT" => intval($arFields["SORT"]), );
		
		if (!$obSUG->Update($ID, $arUpdate))
		{
			$ex = $APPLICATION->GetException();
			$lAdmin->AddUpdateError($ex->GetString(), $ID);
		}

		/*
		if (strlen(trim($arFields["NAME"]))>0)
		{
			CTicketSLA::Set(array("NAME" => $arFields["NAME"], "PRIORITY" => $arFields["PRIORITY"]), $ID);
		}
		else
		{
			$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("SUP_FORGOT_NAME")), $ID);
		}
		*/
	}
}


if($bAdmin && ($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CSupportUserGroup::GetList(array($by => $order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = intval($ID);

		switch($_REQUEST['action'])
		{
			case 'delete':
				@set_time_limit(0);
				CSupportUserGroup::Delete($ID);
			break;
		}
	}
}

$rsData = CSupportUserGroup::GetList(array($by => $order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart(50);

$lAdmin->NavText($rsData->GetNavPrint(GetMessage('SUP_GL_PAGES')));

$arHeaders = Array();
$arHeaders[] = Array('id'=>'ID', 'content'=>'ID', 'default'=>true, 'sort' => 'ID');
$arHeaders[] = Array('id'=>'NAME', 'content'=>GetMessage('SUP_GL_NAME'), 'default'=>true, 'sort' => 'NAME');
$arHeaders[] = Array('id'=>'SORT', 'content'=>GetMessage('SUP_GL_SORT'), 'default'=>true, 'sort' => 'SORT');
$arHeaders[] = Array('id'=>'XML_ID', 'content'=>GetMessage('SUP_GL_XML_ID'), 'default'=>false, 'sort' => 'XML_ID');
$arHeaders[] = Array('id'=>'IS_TEAM_GROUP', 'content'=>GetMessage('SUP_GL_IS_TEAM_GROUP'), 'default'=>true, 'sort' => 'IS_TEAM_GROUP');

$lAdmin->AddHeaders($arHeaders);

while ($arGroup = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow($arGroup['ID'], $arGroup);
	$row->AddViewField('NAME', '<a href="'.$EDIT_URL.'?lang='.LANGUAGE_ID.'&amp;ID='.$arGroup['ID'].'">'.$arGroup['NAME'].'</a>');
	$row->AddInputField('XML_ID');
	$row->AddInputField('SORT', Array('size'=>'5'));
	
	$row->AddCheckField('IS_TEAM_GROUP');
	
	$arActions = Array();
	
	$arActions[] = array(
		'ICON'=>'edit',
		'DEFAULT' => 'Y',
		'TEXT'=>GetMessage('SUP_GL_EDIT'),
		'ACTION'=>$lAdmin->ActionRedirect($EDIT_URL.'?lang='.LANGUAGE_ID.'&ID='.$arGroup['ID'])
	);
	
	$arActions[] = array("SEPARATOR" => true);
	$arActions[] = array(
		'ICON' => 'delete',
		'TEXT'	=> GetMessage('SUP_GL_DELETE'),
		'ACTION'=>'if(confirm(\''.GetMessage('SUP_GL_DELETE_CONFIRMATION').'\')) '.$lAdmin->ActionDoGroup($arGroup['ID'], 'delete'),
	);
	
	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array('title'=>GetMessage('MAIN_ADMIN_LIST_SELECTED'), 'value'=>$rsData->SelectedRowsCount()),
		array('counter'=>true, 'title'=>GetMessage('MAIN_ADMIN_LIST_CHECKED'), 'value'=>'0'),
	)
);

$lAdmin->AddGroupActionTable(Array(
	'delete'=>GetMessage('MAIN_ADMIN_LIST_DELETE'),
	)
);

$aContext = array(
	array(
		'ICON'=> 'btn_new',
		'TEXT'=> GetMessage('SUP_GL_ADD'),
		'LINK'=>$EDIT_URL.'?lang='.LANG,
		'TITLE'=>GetMessage('SUP_GL_ADD')
	),
);


$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('SUP_GL_TITLE'));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?><form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?"><?
$filter->Begin();
?>
<tr> 
	<td><?=GetMessage("SUP_GL_NAME")?>:</td>
	<td><input type="text" name="FIND_NAME" size="47" value="<?=htmlspecialcharsbx($FIND_NAME)?>"><?=InputType("checkbox", "FIND_NAME_EXACT_MATCH", "Y", $FIND_NAME_EXACT_MATCH, false, "", "title='".GetMessage('SUP_GL_EXACT_MATCH')."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr> 
	<td>ID:</td>
	<td><input type="text" name="FIND_ID" size="47" value="<?=htmlspecialcharsbx($FIND_ID)?>"></td>
</tr>
<tr> 
	<td><?=GetMessage('SUP_GL_FLT_IS_TEAM_GROUP_CN')?></td>
	<td><?
		$arr = array("reference"=>array(GetMessage("SUP_GL_FLT_SUPPORT"), GetMessage("SUP_GL_FLT_CLIENT")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("FIND_IS_TEAM_GROUP", $arr, $FIND_IS_TEAM_GROUP, GetMessage("MAIN_ALL"));
		?></td>
</tr>
<?

$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$filter->End();
?></form><?
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>
