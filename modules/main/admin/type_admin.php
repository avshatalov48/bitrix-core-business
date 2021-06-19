<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/mail_events/messagetype_admin.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Internal\EventTypeTable;

$err_mess = "File: ".__FILE__."<br>Line: ";
$arFilter = array();
$error = false;

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

$sTableID = "tbl_event_type";
$oSort = new CAdminSorting($sTableID, "event_name", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

/**
 * 	@global $find
 * 	@global $find_type
 * 	@global $find_type_id
 * 	@global $find_tmpl_id
 * 	@global $find_event_type
 */
$arFilterFields = Array(
	"find",
	"find_type",
	"find_type_id",
	"find_tmpl_id",
	"find_event_type",
);
$lAdmin->InitFilter($arFilterFields);
if ($find <> '' && in_array(mb_strtoupper($find_type), array('EVENT_NAME', 'NAME', 'DESCRIPTION')))
{
	$arFilter["=%".mb_strtoupper($find_type)] = '%' . $find . '%';
}
if ($find_type_id <> '')
{
	$arFilter["ID"] = $find_type_id;
}
if ($find_tmpl_id <> '')
{
	$arFilter["MESSAGE_ID"] = $find_tmpl_id;
}
if ($find_event_type <> '')
{
	$arFilter["=EVENT_TYPE"] = $find_event_type;
}

if(($arID = $lAdmin->GroupAction()) && $isAdmin && check_bitrix_sessid())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CEventType::GetListEx(array($by => $order), $arFilter, array("type" => "none"));
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['EVENT_NAME'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		switch($_REQUEST['action'])
		{
			case "delete":
			case "clean":
				$DB->StartTransaction();
				$ID = array("EVENT_NAME" => $ID);
				$db_res = CEventMessage::GetList('', '', $ID);
				if ($db_res && ($res = $db_res->Fetch()))
				{
					do 
					{
						if (!CEventMessage::Delete($res["ID"]))
						{
							$error = true;
							break;
						}
					} while ($res = $db_res-> Fetch());
				}
				
				if ($error || !CEventType::Delete($ID))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(Loc::getMessage("DELETE_ERROR"), $ID);
				}
				else
					$DB->Commit();
			break;
		}
	}
}
$arLID = array();
$db_res = CLanguage::GetList();
if ($db_res && $res = $db_res->GetNext())
{
	do 
	{
		$arLID[$res["LID"]] = $res["LID"];
	} while ($res = $db_res->GetNext());
}


$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "default"=>true),
	array("id"=>"LID", "content"=>Loc::getMessage("LANG"), "default"=>true),
	array("id"=>"EVENT_NAME", "content"=>Loc::getMessage("EVENT_TYPE"), "sort"=>"event_name", "default"=>true),
	array("id"=>"NAME", "content"=>Loc::getMessage("EVENT_NAME"), "default"=>true),
	array("id"=>"EVENT_TYPE", "content"=>Loc::getMessage("event_type_admin_type"), "default"=>false),
	array("id"=>"DESCRIPTION", "content"=>Loc::getMessage("EVENT_DESCRIPTION"), "default"=>false),
	array("id"=>"TEMPLATES", "content"=>Loc::getMessage("EVENT_TEMPLATES"), "default"=>false))
);

$resultMessageByTypeList = array();
$resultMessageByTypeDb = \Bitrix\Main\Mail\Internal\EventMessageTable::getList(array('select' => array('ID', 'EVENT_NAME')));
while($messageByType = $resultMessageByTypeDb->fetch())
{
	$resultMessageByTypeList[$messageByType['EVENT_NAME']][] = $messageByType['ID'];
}

$resultList = array();
$runtimeList = array();
$arFilter['!EVENT_NAME'] = null;
if(isset($arFilter['MESSAGE_ID']))
{
	$runtimeList[] = new \Bitrix\Main\Entity\ReferenceField(
		'MESSAGE',
		'Bitrix\Main\Mail\Internal\EventMessageTable',
		array('=this.EVENT_NAME' => 'ref.EVENT_NAME')
	);
	$arFilter['MESSAGE.ID'] = $arFilter['MESSAGE_ID'];
	unset($arFilter['MESSAGE_ID']);
}
$resultDb = \Bitrix\Main\Mail\Internal\EventTypeTable::getList(array(
	'filter' => $arFilter,
	'runtime' => $runtimeList,
	'order' => array('EVENT_NAME' => (mb_strtoupper($order) == 'DESC' ? 'DESC' : 'ASC'))
));
$resultTypeList = $resultDb->fetchAll();
foreach($resultTypeList as $type)
{
	$key = $type['EVENT_NAME'];
	if(!isset($resultList[$key]))
	{
		$typeTmp = $type;
		$typeTmp['BY_LANGUAGE'] = array();
		$typeTmp['ID'] = array($type['ID']);
		$typeTmp['LID'] = array($type['LID']);
		$resultList[$key] = $typeTmp;
	}
	else
	{
		$resultList[$key]['ID'][] = $type['ID'];
		$resultList[$key]['LID'][] = $type['LID'];
	}

	$resultList[$key]['BY_LANGUAGE'][$type['LID']] = $type;
}

foreach($resultList as $key => $type)
{
	if(empty($type['BY_LANGUAGE'][LANGUAGE_ID]["NAME"]))
	{
		$type["NAME"] = $type['BY_LANGUAGE']["en"]["NAME"];
	}
	else
	{
		$type["NAME"] = $type['BY_LANGUAGE'][LANGUAGE_ID]["NAME"];
	}

	if(empty($type['BY_LANGUAGE'][LANGUAGE_ID]["SORT"]))
	{
		$type["SORT"] = $type['BY_LANGUAGE']["en"]["SORT"];
	}
	else
	{
		$type["SORT"] = $type['BY_LANGUAGE'][LANGUAGE_ID]["SORT"];
	}

	if(empty($type['BY_LANGUAGE'][LANGUAGE_ID]["DESCRIPTION"]))
	{
		$type["DESCRIPTION"] = $type['BY_LANGUAGE']["en"]["DESCRIPTION"];
	}
	else
	{
		$type["DESCRIPTION"] = $type['BY_LANGUAGE'][LANGUAGE_ID]["DESCRIPTION"];
	}

	$type["TEMPLATES"] = $resultMessageByTypeList[$type["EVENT_NAME"]];

	unset($type['BY_LANGUAGE']);

	$resultList[$key] = $type;
}

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-event-type");
$nav->setRecordCount(count($resultList));
$lAdmin->setNavigation($nav, Loc::getMessage("PAGES"));
$iterateNumber = 0;
foreach($resultList as $resultItem)
{
	if($iterateNumber++ >= $nav->getOffset())
	{
		if($iterateNumber - $nav->getOffset() > $nav->getLimit())
		{
			break;
		}
	}
	else
	{
		continue;
	}

	$arr = $resultItem['ID'];
	$f_ID = htmlspecialcharsEx($resultItem['EVENT_NAME']);
	$row =& $lAdmin->AddRow($f_ID, $resultItem, "type_edit.php?EVENT_NAME=".$f_ID, Loc::getMessage("type_admin_edit_title1"));
	$row->AddViewField("ID", implode("<br />", $arr));
	$row->AddViewField("LID", implode("<br />", array_intersect($arLID, $resultItem['LID'])));
	$row->AddViewField("EVENT_NAME", "<a href=\"type_edit.php?EVENT_NAME=".$f_ID."\">".$f_ID."</a>");
	$row->AddViewField("NAME", htmlspecialcharsEx($resultItem['NAME']));
	$row->AddViewField("EVENT_TYPE", ($resultItem['EVENT_TYPE'] == EventTypeTable::TYPE_SMS? "SMS" : "Email"));
	$row->AddViewField("DESCRIPTION", htmlspecialcharsEx($resultItem['DESCRIPTION']));
	$templates = array();
	if (is_array($resultItem['TEMPLATES']) && !empty($resultItem['TEMPLATES']))
	{
		$templates = array();
		foreach ($resultItem['TEMPLATES'] as $k)
		{
			$templates[$k] = "<a href=\"".BX_ROOT."/admin/message_edit.php?ID=".intval($k)."&lang=".LANGUAGE_ID."\">".intval($k)."</a>";
		}
	}
	$row->AddViewField("TEMPLATES", implode("<br />", $templates));

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("MAIN_ADMIN_MENU_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("type_edit.php?EVENT_NAME=".$f_ID));
	if($isAdmin)
	{
		$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("MAIN_ADMIN_MENU_DELETE"), "ACTION"=>"if(confirm('".Loc::getMessage('CONFIRM_DEL_ALL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}
	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(Array(
	"delete"=>true,
));

$aContext = array(
	array(
		"TEXT" => Loc::getMessage("ADD_TYPE"),
		"LINK" => "type_edit.php?lang=".LANGUAGE_ID,
		"TITLE" => Loc::getMessage("ADD_TYPE_TITLE1"),
		"ICON" => "btn_new"
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(Loc::getMessage("TITLE1"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?><form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?"><?
$oFilter = new CAdminFilter($sTableID."_filter", array(
	Loc::getMessage('F_ID')." ".Loc::getMessage('F_TYPE1'),
	Loc::getMessage('F_ID')." ".Loc::getMessage('F_TMPL'),
	Loc::getMessage("event_type_admin_type_flt"),
));
$oFilter->Begin();
?><tr>
	<td><b><?=Loc::getMessage("F_SEARCH")?>:</b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?=htmlspecialcharsbx($find)?>" title="<?=Loc::getMessage("F_SEARCH_TITLE")?>">
		<select name="find_type">
			<option value="event_name"<?if($find_type=="event_name") echo " selected"?>><?=Loc::getMessage('F_EVENT_NAME1')?></option>
			<option value="name"<?if($find_type=="subject") echo " selected"?>><?=Loc::getMessage('F_NAME')?></option>
			<option value="description"<?if($find_type=="from") echo " selected"?>><?=Loc::getMessage('F_DESCRIPTION')?></option>
		</select>
	</td>
</tr>
<tr>
	<td>ID <?=Loc::getMessage('F_TYPE1')?>:</td>
	<td><input type="text" name="find_type_id" size="47" value="<?=htmlspecialcharsbx($find_type_id)?>"></td>
</tr>
<tr>
	<td>ID <?=Loc::getMessage('F_TMPL')?>:</td>
	<td><input type="text" name="find_tmpl_id" size="47" value="<?=htmlspecialcharsbx($find_tmpl_id)?>"></td>
</tr>
<tr>
	<td><?=Loc::getMessage("event_type_admin_type_flt")?>:</td>
	<td><select name="find_event_type">
			<option value=""><?echo Loc::getMessage("event_type_admin_type_flt_all")?></option>
			<option value="<?=EventTypeTable::TYPE_EMAIL?>"<?if($find_event_type == EventTypeTable::TYPE_EMAIL) echo " selected"?>><?echo Loc::getMessage("event_type_admin_type_flt_email")?></option>
			<option value="<?=EventTypeTable::TYPE_SMS?>"<?if($find_event_type == EventTypeTable::TYPE_SMS) echo " selected"?>><?echo Loc::getMessage("event_type_admin_type_flt_sms")?></option>
		</select></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>