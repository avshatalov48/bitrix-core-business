<?
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

require_once(__DIR__."/../include/prolog_admin_before.php");
define("HELP_FILE", "utilities/sql.php");

if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_sql";
$message = null;

CPageOption::SetOptionString("main", "nav_page_in_session", "N");
$lAdmin = new CAdminList($sTableID);
if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($query) && $isAdmin && check_bitrix_sessid())
{
	$first = microtime(true);
	$arErrors = array();
	$arQuery = $DB->ParseSQLBatch(str_replace("\r", "", $query));
	foreach($arQuery as $i => $sql)
	{
		$dbr = $DB->Query($sql, true);
		if(!$dbr)
			$arErrors[$i] = $DB->GetErrorMessage();
	}
	if(empty($arErrors))
	{
		$exec_time = round(microtime(true)-$first, 5);
		$rsData = new CAdminResult($dbr, $sTableID);

		$message = new CAdminMessage(array(
			"MESSAGE" => GetMessage("SQL_SUCCESS_EXECUTE"),
			"DETAILS" => GetMessage("SQL_EXEC_TIME")."<b>".$exec_time."</b> ".GetMessage("SQL_SEC"),
			"TYPE" => "OK",
			"HTML" => true,
		));

		$rsData = new CAdminResult($rsData, $sTableID);
		$rsData->bPostNavigation = true;
		$rsData->NavStart();
		$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SQL_PAGES")));

		$intNumFields = $rsData->FieldsCount();
		$i = 0;
		$header = Array();
		$arFieldName = Array();
		while ($i<$intNumFields)
		{
			$fieldName = htmlspecialcharsbx($rsData->FieldName($i));
			$header[] =
				array("id"=>$fieldName, "content"=>$fieldName,	"sort"=>$fieldName, "default"=>true, "align"=>"left", "valign" => "top");
			$arFieldName[] = $fieldName;
			$i++;
		}

		$lAdmin->AddHeaders($header);

		$j = 0;
		while($db_res=$rsData->Fetch()):
			$row =& $lAdmin->AddRow("ID", $db_res);
			foreach ($arFieldName as $field_name) :
				$row->AddViewField($field_name, TxtToHtml($db_res[$field_name]));
			endforeach;
		endwhile;
	}
	else
	{
		foreach($arErrors as $i => $strError)
		{
			$lAdmin->AddFilterError(GetMessage("SQL_QUERY_ERROR_1")."<br>".$strError);
		}
	}
}

if($message != null)
{
	$lAdmin->BeginPrologContent();
	echo $message->Show();
	$lAdmin->EndPrologContent();
}

$lAdmin->BeginEpilogContent();
?>
	<input type="hidden" name="query" id="query" value="<?=htmlspecialcharsbx($query ?? '')?>">
<?
$lAdmin->EndEpilogContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SQL_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<script>
function __FSQLSubmit()
{
	if(confirm('<?echo GetMessage("SQL_CONFIRM_EXECUTE")?>'))
	{
		document.getElementById('query').value = document.getElementById('sql').value;
		window.scrollTo(0, 500);
		<?=$lAdmin->ActionPost(CUtil::JSEscape($APPLICATION->GetCurPageParam("mode=frame", Array("mode", "PAGEN_1"))))?>
	}
}
</script>
<?
$aTabs = array(
	array("DIV"=>"tab1", "TAB"=>GetMessage("SQL_TAB"), "TITLE"=>GetMessage("SQL_TAB_TITLE")),
);
$editTab = new CAdminTabControl("editTab", $aTabs);

?>
<form name="form1" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>" method="POST">
<?=bitrix_sessid_post()?>
<?
$editTab->Begin();
$editTab->BeginNextTab();
?>
<tr valign="top">
	<td width="100%" colspan="2">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<textarea cols="60" name="sql" id="sql" rows="15" wrap="OFF" style="width:100%;"><? echo htmlspecialcharsbx($query ?? ''); ?></textarea><br />	</td>
</tr>
<?$editTab->Buttons();?>
<input <?if (!$isAdmin) echo "disabled"?> type="button" accesskey="x" name="execute" value="<?echo GetMessage("SQL_EXECUTE")?>" onclick="return __FSQLSubmit();" class="adm-btn-save">
<input type="reset" value="<?echo GetMessage("SQL_RESET")?>">
<?
$editTab->End();
?>
</form>

<?
if(COption::GetOptionString('fileman', "use_code_editor", "Y") == "Y" && CModule::IncludeModule('fileman'))
	CCodeEditor::Show(array('textareaId' => 'sql', 'height' => 350, 'forceSyntax' => 'sql'));

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
