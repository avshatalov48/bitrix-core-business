<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/prolog.php");
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
global $APPLICATION;
/** @var CAdminMessage $message */
$searchDB = CDatabase::GetModuleConnection('search');

$SEARCH_RIGHT = $APPLICATION->GetGroupRight("search");
if($SEARCH_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_search_phrase_list";
$oSort = new CAdminSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

$ref = $ref_id = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
{
	$ref[] = $ar["ID"];
	$ref_id[] = $ar["ID"];
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

$arFilterFields = Array(
	"find_id",
	"find_date1",
	"find_date2",
	"find_site_id",
	"find_phrase",
	"find_tags",
	"find_stat_sess_id",
	"find_url_to",
	"find_url_to_404",
);

$lAdmin->InitFilter($arFilterFields);
if($lAdmin->IsDefaultFilter())
{
	$sdate = time();
	$sdate = mktime(0, 0, 0, date("m", $sdate), date("d", $sdate)-1, date("Y", $sdate));
	$find_date1 = ConvertTimeStamp($sdate);
}

$arFilter = array();

if($_REQUEST["find_id_exact_match"] == "Y")
	$arFilter["=ID"] = $find_id;
else
	$arFilter["ID"] = $find_id;

$arFilter[">=TIMESTAMP_X"] = $find_date1;
$arFilter["<=TIMESTAMP_X"] = $find_date2;
$arFilter["=SITE_ID"] = $find_site_id;

if($_REQUEST["find_phrase_exact_match"] == "Y")
	$arFilter["=PHRASE"] = $find_phrase;
else
	$arFilter["PHRASE"] = $find_phrase;

if($_REQUEST["find_tags_exact_match"] == "Y")
	$arFilter["=TAGS"] = $find_tags;
else
	$arFilter["TAGS"] = $find_tags;

if($_REQUEST["find_stat_sess_id_exact_match"] == "Y")
	$arFilter["=STAT_SESS_ID"] = $find_stat_sess_id;
else
	$arFilter["STAT_SESS_ID"] = $find_stat_sess_id;

if($_REQUEST["find_url_to_exact_match"] == "Y")
	$arFilter["=URL_TO"] = $find_url_to;
else
	$arFilter["URL_TO"] = $find_url_to;

$arFilter["=URL_TO_404"] = $find_url_to_404;

foreach($arFilter as $key => $value)
	if($value == '')
		unset($arFilter[$key]);

$rsData = CSearchStatistic::GetList(array($by => $order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SEARCH_PHL_PHRASES")));

$aContext=array();

$lAdmin->AddAdminContextMenu($aContext);
$arHeaders=array(
	array("id"=>"ID", "content"=>GetMessage("SEARCH_PHL_ID"), "sort"=>"ID", "default"=>true, "align"=>"right"),
	array("id"=>"SITE_ID", "content"=>GetMessage("SEARCH_PHL_SITE_ID"), "default"=>true),
	array("id"=>"PHRASE", "content"=>GetMessage("SEARCH_PHL_PHRASE"), "sort"=>"PHRASE", "default"=>true),
	array("id"=>"TAGS", "content"=>GetMessage("SEARCH_PHL_TAGS"), "sort"=>"TAGS", "default"=>true),
	array("id"=>"TIMESTAMP_X", "content"=>GetMessage("SEARCH_PHL_TIMESTAMP_X"), "sort"=>"TIMESTAMP_X", "default"=>true),
	array("id"=>"URL_TO", "content"=>GetMessage("SEARCH_PHL_URL_TO"), "sort"=>"URL_TO", "default"=>true),
	array("id"=>"RESULT_COUNT", "content"=>GetMessage("SEARCH_PHL_RESULT_COUNT"), "sort"=>"RESULT_COUNT", "default"=>true, "align"=>"right"),
	array("id"=>"PAGES", "content"=>GetMessage("SEARCH_PHL_PAGES"), "title" => GetMessage("SEARCH_PHL_PAGES_ALT"), "sort"=>"PAGES", "default"=>true, "align"=>"right"),
);

if(IsModuleInstalled('statistic'))
	$arHeaders[] = array("id"=>"STAT_SESS_ID", "content"=>GetMessage("SEARCH_PHL_STAT_SESS_ID"), "sort"=>"STAT_SESS_ID", "default"=>true, "align"=>"right");

$lAdmin->AddHeaders($arHeaders);

$i=0;
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);
	if($_REQUEST["mode"] != "excel")
		$row->AddViewField("TIMESTAMP_X", str_replace(" ", "&nbsp;", $f_TIMESTAMP_X));
	$row->AddViewField("URL_TO", ($f_URL_TO_SITE_ID? "[".$f_URL_TO_SITE_ID."]&nbsp;": "")."<a ".($f_URL_TO_404=="Y"? 'style="color:red"': '')." title=\"".GetMessage("SEARCH_PHL_LINK_OPEN")."\" href=\"$f_URL_TO\">".TruncateText(InsertSpaces($f_URL_TO,50,"<wbr>"),100)."</a>&nbsp;");
	$row->AddViewField("STAT_SESS_ID", "<a title=\"".GetMessage("SEARCH_PHL_SESSION")."\" href=\"session_list.php?lang=".LANGUAGE_ID."&find_id=$f_STAT_SESS_ID&find_id_exact_match=Y&set_filter=Y\">$f_STAT_SESS_ID</a>&nbsp;");
}

$lAdmin->AddFooter(array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	)
);

$lAdmin->CheckListMode();
/***************************************************************************
			HTML form
****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SEARCH_PHL_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(is_object($message))
	echo $message->Show();
?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"find_tags" => GetMessage("SEARCH_PHL_TAGS"),
		"find_id" => GetMessage("SEARCH_PHL_ID"),
		"find_dates" => GetMessage("SEARCH_PHL_DATE"),
		"find_site_id" => GetMessage("SEARCH_PHL_SITE_ID"),
		"find_url_to" => GetMessage("SEARCH_PHL_URL_TO"),
		"find_stat_sess_id" => GetMessage("SEARCH_PHL_STAT_SESS_ID"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td nowrap><b><?echo GetMessage("SEARCH_PHL_PHRASE")?>:</b></td>
	<td><input type="text" name="find_phrase" size="47" value="<?echo htmlspecialcharsbx($find_phrase)?>"></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("SEARCH_PHL_TAGS")?>:</td>
	<td><input type="text" name="find_tags" size="47" value="<?echo htmlspecialcharsbx($find_tags)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("SEARCH_PHL_ID")?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("SEARCH_PHL_DATE")?>:</td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("SEARCH_PHL_SITE_ID")?>:</td>
	<td><?echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("SEARCH_PHL_SITE"));?></td>
</tr>

<tr>
	<td nowrap><?echo GetMessage("SEARCH_PHL_URL_TO")?></td>
	<td><?
		echo SelectBoxFromArray("find_url_to_404", array("reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_url_to_404), GetMessage("SEARCH_PHL_404"));
	?>&nbsp;<input type="text" name="find_url_to" size="33" value="<?echo htmlspecialcharsbx($find_url_to)?>"></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("SEARCH_PHL_STAT_SESS_ID")?></td>
	<td><input type="text" name="find_stat_sess_id" size="47" value="<?echo htmlspecialcharsbx($find_stat_sess_id)?>"></td>
</tr>

<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()));
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
