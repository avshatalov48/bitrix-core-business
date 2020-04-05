<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_sender_posting_recipient";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$MAILING_ID = intval($_REQUEST['MAILING_ID']);
$ID = intval($_REQUEST['ID']);
if($find_mailing_chain_id>0)
	$ID = $find_mailing_chain_id;

if($ID <= 0)
{
	$postingDb = \Bitrix\Sender\PostingTable::getList(array(
		'select' => array('MAILING_CHAIN_ID'),
		'filter' => array('MAILING_ID' => $MAILING_ID),
		'order' => array('DATE_SENT' => 'DESC', 'DATE_CREATE' => 'DESC'),
	));
	$arPosting = $postingDb->fetch();
	if($arPosting)
	{
		$ID = intval($arPosting['MAILING_CHAIN_ID']);
	}
}


function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;

	return count($lAdmin->arFilterErrors)==0;
}

$FilterArr = Array(
	"find_email",
	"find_name",
	"find_mailing",
	"find_mailing_chain_id",
	"find_sent",
	"find_read",
	"find_click",
	"find_unsub",
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter() || $ID>0)
{
	$arFilter = Array(
		"%NAME" => $find_name,
		"%EMAIL" => $find_email,
		"=POSTING.MAILING_ID" => $MAILING_ID,
		"=POSTING.MAILING_CHAIN_ID" => $ID,
	);

	foreach($arFilter as $k => $v) if(empty($v)) unset($arFilter[$k]);


	if($find_sent && in_array($find_sent, array_keys(\Bitrix\Sender\PostingRecipientTable::getStatusList())))
	{
		$arFilter["=STATUS"] = $find_sent;
	}
	if(in_array($find_read, array('Y', 'N')))
	{
		$arFilter['=IS_READ'] = $find_read;
	}
	if(in_array($find_click, array('Y', 'N')))
	{
		$arFilter['=IS_CLICK'] = $find_click;
	}
	if(in_array($find_unsub, array('Y', 'N')))
	{
		$arFilter['=IS_UNSUB'] = $find_unsub;
	}

}

if(isset($order)) $order = ($order=='asc'?'ASC': 'DESC');

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-sender-recipient");
$recipientListDb = \Bitrix\Sender\PostingRecipientTable::getList(array(
	'select' => array('NAME', 'EMAIL', 'CALC_IS_READ', 'CALC_IS_CLICK', 'CALC_IS_UNSUB'),
	'filter' => $arFilter,
	'runtime' => array(
		new \Bitrix\Main\Entity\ExpressionField('CALC_IS_READ', 'MAX(%s)', 'IS_READ'),
		new \Bitrix\Main\Entity\ExpressionField('CALC_IS_CLICK', 'MAX(%s)', 'IS_CLICK'),
		new \Bitrix\Main\Entity\ExpressionField('CALC_IS_UNSUB', 'MAX(%s)', 'IS_UNSUB'),
	),
	'group' => array('NAME', 'EMAIL'),
	'order' => array($by=>$order),
	'count_total' => true,
	'offset' => $nav->getOffset(),
	'limit' => $nav->getLimit(),
));

$aContext = array();
$nav->setRecordCount($recipientListDb->getCount());
$lAdmin->setNavigation($nav, \Bitrix\Main\Localization\Loc::getMessage("rub_nav"));

$lAdmin->AddHeaders(array(
	array(	"id"		=>"EMAIL",
		"content"	=>GetMessage("rub_email"),
		"sort"		=>"EMAIL",
		"default"	=>true,
	),
	array(	"id"		=>"NAME",
		"content"	=>GetMessage("rub_name"),
		"sort"		=>"NAME",
		"default"	=>true,
	),
	array(	"id"		=>"IS_READ",
		"content"	=>GetMessage("rub_f_read"),
		"sort"		=>"IS_READ",
		"default"	=>true,
	),
	array(	"id"		=>"IS_CLICK",
		"content"	=>GetMessage("rub_f_click"),
		"sort"		=>"IS_CLICK",
		"default"	=>true,
	),
	array(	"id"		=>"IS_UNSUB",
		"content"	=>GetMessage("rub_f_unsub"),
		"sort"		=>"IS_UNSUB",
		"default"	=>true,
	),
));

while($resultRow = $recipientListDb->fetch()):
	$row =& $lAdmin->AddRow(false, $resultRow);
	$row->AddViewField("NAME", htmlspecialcharsbx($resultRow['NAME']));
	$row->AddViewField("EMAIL", htmlspecialcharsbx($resultRow['EMAIL']));
	$row->AddViewField("IS_READ", $resultRow['CALC_IS_READ'] == 'Y' ? GetMessage("POST_U_YES") : GetMessage("POST_U_NO"));
	$row->AddViewField("IS_CLICK", $resultRow['CALC_IS_CLICK'] == 'Y' ? GetMessage("POST_U_YES") : GetMessage("POST_U_NO"));
	$row->AddViewField("IS_UNSUB", $resultRow['CALC_IS_UNSUB'] == 'Y' ? GetMessage("POST_U_YES") : GetMessage("POST_U_NO"));
endwhile;

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$recipientListDb->getCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("rub_title"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		($MAILING_ID > 0 ? GetMessage("rub_f_mailing_chain") : null),
		GetMessage("rub_f_email"),
		GetMessage("rub_f_name"),
		GetMessage("rub_f_sent"),
		GetMessage("rub_f_read"),
		GetMessage("rub_f_click"),
		GetMessage("rub_f_unsub"),
	)
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
	<?if($MAILING_ID > 0):?>
		<tr>
			<td><?=GetMessage("rub_f_mailing_chain")?>:</td>
			<td valign="middle">
				<?
				$arr = array();
				$mailingChainDb = \Bitrix\Sender\MailingChainTable::getList(array(
					'select' => array('SUBJECT', 'TITLE', 'ID'),
					'filter' => array('MAILING_ID' => $MAILING_ID)
				));
				while($arMailingChain = $mailingChainDb->fetch())
				{
					$arr['reference'][] = $arMailingChain['TITLE'] ? $arMailingChain['TITLE'] : $arMailingChain['SUBJECT'];
					$arr['reference_id'][] = $arMailingChain['ID'];
				}
				echo SelectBoxFromArray("find_mailing_chain_id", $arr, $ID, false, "");
				?>
			</td>
		</tr>
	<?endif;?>
	<tr>
		<td><?=GetMessage("rub_f_email")?>:</td>
		<td>
			<input type="text" name="find_email" size="47" value="<?echo htmlspecialcharsbx($find_email)?>">
		</td>
	</tr>
<tr>
	<td><?=GetMessage("rub_f_name")?>:</td>
	<td>
		<input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>">
</td>
</tr>

<tr>
	<td><?=GetMessage("rub_f_sent")?>:</td>
	<td>
		<?
		$arRecipientStatus = \Bitrix\Sender\PostingRecipientTable::getStatusList();
		$arr = array(
			"reference" => array_values($arRecipientStatus),
			"reference_id" => array_keys($arRecipientStatus)
		);
		echo SelectBoxFromArray("find_sent", $arr, $find_sent, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_read")?>:</td>
	<td>
		<?
		$arr = array(
			"reference" => array(
				GetMessage("MAIN_YES"),
				GetMessage("MAIN_NO"),
			),
			"reference_id" => array(
				"Y",
				"N",
			)
		);
		echo SelectBoxFromArray("find_read", $arr, $find_read, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_click")?>:</td>
	<td>
		<?
		$arr = array(
			"reference" => array(
				GetMessage("MAIN_YES"),
				GetMessage("MAIN_NO"),
			),
			"reference_id" => array(
				"Y",
				"N",
			)
		);
		echo SelectBoxFromArray("find_click", $arr, $find_click, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_unsub")?>:</td>
	<td>
		<?
		$arr = array(
			"reference" => array(
				GetMessage("MAIN_YES"),
				GetMessage("MAIN_NO"),
			),
			"reference_id" => array(
				"Y",
				"N",
			)
		);
		echo SelectBoxFromArray("find_unsub", $arr, $find_unsub, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>

<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage()."?MAILING_ID=".$MAILING_ID,"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>