<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!CModule::IncludeModule('learning'))
{
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php'); // second system's prolog

	if (IsModuleInstalled('learning') && defined('LEARNING_FAILED_TO_LOAD_REASON'))
		echo LEARNING_FAILED_TO_LOAD_REASON;
	else
		CAdminMessage::ShowMessage(GetMessage('LEARNING_MODULE_NOT_FOUND'));

	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');	// system's epilog
	exit();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/prolog.php");
IncludeModuleLangFile(__FILE__);

$sTableID = "t_learning_group_admin";
$oSort = new CAdminSorting($sTableID, "ID", "desc");// sort initializing
$lAdmin = new CAdminList($sTableID, $oSort);// list initializing

$filter = new CAdminFilter(
	$sTableID . "_filter",
	array(
		GetMessage('LEARNING_ADMIN_GROUPS_TITLE'),
		'ID',
		GetMessage('LEARNING_ADMIN_GROUPS_ACTIVE'),
		GetMessage('LEARNING_ADMIN_GROUPS_CODE'),
		GetMessage('LEARNING_ADMIN_GROUPS_COURSE_TITLE'),
		GetMessage('LEARNING_ADMIN_GROUPS_COURSE_LESSON_ID'),
		GetMessage('LEARNING_ADMIN_GROUPS_SORT'),
		GetMessage('LEARNING_ADMIN_GROUPS_ACTIVE_FROM'),
		GetMessage('LEARNING_ADMIN_GROUPS_ACTIVE_TO')
	)
);

$arFilterFields = array(
	"filter_title",
	"filter_id",
	"filter_active",
	"filter_code",
	"filter_course_title",
	"filter_course_lesson_id",
	"filter_sort",
	"filter_active_from_from", "filter_active_from_to",
	"filter_active_to_from", "filter_active_to_to"
);

$lAdmin->InitFilter($arFilterFields);// filter initializing

$arFilter = array(
	'ID'               => $filter_id,
	'ACTIVE'           => $filter_active,
	'TITLE'            => $filter_title,
	'CODE'             => $filter_code,
	'SORT'             => $filter_sort,
	'>=ACTIVE_FROM'    => $filter_active_from_from,
	'<=ACTIVE_FROM'    => $filter_active_from_to,
	'>=ACTIVE_TO'      => $filter_active_to_from,
	'<=ACTIVE_TO'      => $filter_active_to_to,
	'COURSE_TITLE'     => $filter_course_title,
	'COURSE_LESSON_ID' => $filter_course_lesson_id
);

if($lAdmin->EditAction()) // save from the list
{
	foreach ($FIELDS as $ID => $arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;

		if ( ! CLearningGroup::update((int) $ID, $arFields) )
		{
			if ($e = $APPLICATION->GetException())
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR") . $ID . ": " . $e->GetString(), $ID);
		}
	}
}

// group and single actions processing
if ($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CLearningGroup::GetList(array($by => $order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;

		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				if(!CLearningGroup::delete($ID))
					$lAdmin->AddGroupError(GetMessage("LEARNING_DELETE_ERROR"), $ID);
			break;
		}
	}
}

// fetch data
$rsData = CLearningGroup::GetList(array($by => $order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_ADMIN_RESULTS")));

// list header
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage('LEARNING_ADMIN_GROUPS_ACTIVE'), "sort" =>"active", "default"=>true),
	array("id"=>"TITLE", "content"=>GetMessage('LEARNING_ADMIN_GROUPS_TITLE'), "sort" =>"title", "default"=>true),
	array("id"=>"CODE", "content"=>GetMessage('LEARNING_ADMIN_GROUPS_CODE'), "sort" =>"code", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('LEARNING_ADMIN_GROUPS_SORT'), "sort" =>"sort", "default"=>true),
	array("id"=>"ACTIVE_FROM", "content"=>GetMessage('LEARNING_ADMIN_GROUPS_ACTIVE_FROM'), "sort" =>"active_from", "default"=>true),
	array("id"=>"ACTIVE_TO", "content"=>GetMessage('LEARNING_ADMIN_GROUPS_ACTIVE_TO'), "sort" =>"active_to", "default"=>true),
	array("id"=>"COURSE_LESSON_ID", "content"=>GetMessage('LEARNING_ADMIN_GROUPS_COURSE_LESSON_ID'), "sort" =>"course_lesson_id", "default"=>true),
	array("id"=>"COURSE_TITLE", "content"=>GetMessage('LEARNING_ADMIN_GROUPS_COURSE_TITLE'), "sort" =>"course_title", "default"=>true)
));

// building list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField(
		"COURSE_TITLE",
		'<a href="learn_unilesson_admin.php?lang=' . LANG . '&PARENT_LESSON_ID=' . (int) $arRes['COURSE_LESSON_ID'] . '&LESSON_PATH=' . (int) $arRes['COURSE_LESSON_ID'] . '">'
		. htmlspecialcharsbx($arRes['COURSE_TITLE']) . ' [' . $arRes['COURSE_LESSON_ID'] . ']'
		. '</a>'
	);

	$row->AddField("ID", '<a href="/bitrix/admin/learn_group_edit.php?ID='.$f_ID.'&lang='.LANGUAGE_ID.'" title="'.GetMessage("MAIN_ADMIN_MENU_EDIT").'">'.$f_ID.'</a>');
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("TITLE", Array("size"=>"20"));
	$row->AddInputField("CODE", Array("size"=>"10"));
	$row->AddInputField("SORT", Array("size"=>"3"));

	$arActions = array();

	$arActions[] = array(
		"ICON"    => "edit",
		"DEFAULT" => "Y",
		"TEXT"    => GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"ACTION"  => $lAdmin->ActionRedirect("learn_group_edit.php?lang=" . LANG . "&ID=" . $f_ID . GetFilterParams("filter_"))
	);

	$arActions[] = array("SEPARATOR"=>true);

	$arActions[] = array(
		"ICON"   => "delete",
		"TEXT"   => GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION" => "if(confirm('".GetMessageJS('LEARNING_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete",""));

	$row->AddActions($arActions);
}

// list footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
));

$adminChain->AddItem(array("TEXT"=>GetMessage("LEARNING_ADMIN_RESULTS"), "LINK"=>""));

$lAdmin->AddAdminContextMenu(array(
	array(
		'ICON'  => 'btn_new',
		'TEXT'  =>  GetMessage('LEARNING_ADD'),
		'LINK'  => 'learn_group_edit.php?lang=' . LANG . GetFilterParams('filter_'),
		'TITLE' =>  GetMessage('LEARNING_ADD_ALT')
	)
));

$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("LEARNING_ADMIN_TITLE").($arGroup ? ": ".$arGroup["~TEST_NAME"].": ".$arGroup["~USER_NAME"] : ""));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (defined("LEARNING_ADMIN_ACCESS_DENIED"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"), false);
?>

<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>" onsubmit="return this.set_filter.onclick();">
<?$filter->Begin();?>

	<tr>
		<td><b><?echo GetMessage("LEARNING_ADMIN_GROUPS_TITLE")?>:</b></td>
		<td align="left">
			<input type="text" name="filter_title" value="<?echo htmlspecialcharsex($filter_title)?>" size="30">
		</td>
	</tr>

	<tr>
		<td>ID:</b></td>
		<td><input type="text" name="filter_id" value="<?echo htmlspecialcharsbx($filter_id)?>" size="47"></td>
	</tr>

	<tr>
		<td><?=GetMessage("LEARNING_ADMIN_GROUPS_ACTIVE")?>:</td>
		<td>
			<?
			$arr = array("reference"=>array(GetMessage("LEARNING_YES"), GetMessage("LEARNING_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("filter_active", $arr, htmlspecialcharsex($filter_active), GetMessage('LEARNING_ALL'));
			?>
		</td>
	</tr>

	<tr>
		<td><b><?echo GetMessage("LEARNING_ADMIN_GROUPS_CODE")?>:</b></td>
		<td align="left">
			<input type="text" name="filter_code" value="<?echo htmlspecialcharsex($filter_code)?>" size="30">
		</td>
	</tr>

	<tr>
		<td><b><?echo GetMessage("LEARNING_ADMIN_GROUPS_COURSE_TITLE")?>:</b></td>
		<td align="left">
			<input type="text" name="filter_course_title" value="<?echo htmlspecialcharsex($filter_course_title)?>" size="30">
		</td>
	</tr>

	<tr>
		<td><b><?echo GetMessage("LEARNING_ADMIN_GROUPS_COURSE_LESSON_ID")?>:</b></td>
		<td align="left">
			<input type="text" name="filter_course_lesson_id" value="<?echo htmlspecialcharsex($filter_course_lesson_id); ?>" size="30">
		</td>
	</tr>

	<tr>
		<td><b><?echo GetMessage("LEARNING_ADMIN_GROUPS_SORT")?>:</b></td>
		<td align="left">
			<input type="text" name="filter_sort" value="<?echo htmlspecialcharsex($filter_sort)?>" size="30">
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_ADMIN_GROUPS_ACTIVE_FROM")?>:</td>
		<td><?echo CalendarPeriod("filter_active_from_from", htmlspecialcharsex($filter_active_from_from), "filter_active_from_to", htmlspecialcharsex($filter_active_from_to), "filter_active_from")?></td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_ADMIN_GROUPS_ACTIVE_TO")?>:</td>
		<td><?echo CalendarPeriod("filter_active_to_from", htmlspecialcharsex($filter_active_to_from), "filter_active_to_to", htmlspecialcharsex($filter_active_to_to), "filter_active_to")?></td>
	</tr>

<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()."?".GetFilterParams("filter_"), "form"=>"form1"));$filter->End();?>
</form>


<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>