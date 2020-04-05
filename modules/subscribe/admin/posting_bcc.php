<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/include.php");

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
if($POST_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$ID = intval($_GET["ID"]);

$APPLICATION->SetTitle(GetMessage("post_title"));

$sTableID = "tbl_posting_bcc";
$oSort = new CAdminSorting($sTableID, "EMAIL", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find_status_id",
);

$lAdmin->InitFilter($FilterArr);

if($find_status_id != "N")
	$find_status_id = "E";

if(($arEMAIL = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
	$arSubscr = array();
	$rsData = CPosting::GetEmailsByStatus($ID, $find_status_id);
	while($arRes = $rsData->Fetch())
		$arSubscr[$arRes['EMAIL']] = $arRes["SUBSCRIPTION_ID"];

	if($_REQUEST['action_target']=='selected')
	{
		$arEMAIL = array_keys($arSubscr);
	}

	foreach($arEMAIL as $EMAIL)
	{
		$SUBSCR_ID = IntVal($arSubscr[$EMAIL]);
		if($SUBSCR_ID <= 0)
			continue;

		switch($_REQUEST['action'])
		{
		case "sudelete":
			CSubscription::Delete($SUBSCR_ID);
			break;
		case "inactive":
			$oSubscription = new CSubscription;
			$oSubscription->Update($SUBSCR_ID, array("ACTIVE"=>"N"));
			break;
		}
	}
}

$lAdmin->AddHeaders(array(
	array(
		"id" => "EMAIL",
		"content" => GetMessage("POST_EMAIL"),
		"default" => true,
	),
	array(
		"id" => "SUBSCRIPTION_ID",
		"content" => GetMessage("POST_SUBSCRIPTION_ID"),
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "USER_ID",
		"content" => GetMessage("POST_USER_ID"),
		"default" => true,
		"align" => "right",
	),
));

$cData = new CPosting;
$rsData = $cData->GetEmailsByStatus($ID, $find_status_id);
$rsData = new CAdminResult($rsData, $sTableID);

$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(""));
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_EMAIL, $arRes);
	if($f_SUBSCRIPTION_ID > 0)
	{
		$rs = CSubscription::GetByID($f_SUBSCRIPTION_ID);
		$ar = $rs->Fetch();
		if(!$ar)
			$row->AddViewField("SUBSCRIPTION_ID", $f_SUBSCRIPTION_ID.' ('.GetMessage("POST_SUBSCR_DELETED").')');
		elseif($ar["ACTIVE"]=="N")
			$row->AddViewField("SUBSCRIPTION_ID", '<a target="_blank" href="subscr_edit.php?lang='.LANGUAGE_ID.'&amp;ID='.$f_SUBSCRIPTION_ID.'">'.$f_SUBSCRIPTION_ID.'</a> ('.GetMessage("POST_SUBSCR_INACTIVE").')');
		else
			$row->AddViewField("SUBSCRIPTION_ID", '<a target="_blank" href="subscr_edit.php?lang='.LANGUAGE_ID.'&amp;ID='.$f_SUBSCRIPTION_ID.'">'.$f_SUBSCRIPTION_ID.'</a>');
	}
	if($f_USER_ID > 0)
		$row->AddViewField("USER_ID", '<a target="_blank" href="user_edit.php?lang='.LANGUAGE_ID.'&amp;ID='.$f_USER_ID.'">'.$f_USER_ID.'</a>');
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("post_total"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);
$lAdmin->AddGroupActionTable(Array(
	"inactive"=>GetMessage("POST_GROUP_ACTION_INACTIVE"),
	"sudelete"=>GetMessage("POST_GROUP_ACTION_DELETE"),
));

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array()
);

$oFilter->Begin();
?>
<tr>
	<td><?=GetMessage("POST_STATUS_ID")?>:</td>
	<td>
		<?
		$arr = array(
			"reference" => array(
				GetMessage("POST_STATUS_ID_ERROR"),
				GetMessage("POST_STATUS_ID_SUCCESS"),
			),
			"reference_id" => array(
				"E",
				"N",
			)
		);
		echo SelectBoxFromArray("find_status_id", $arr, $find_status_id);
		?>
	</td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form" => "find_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
?>