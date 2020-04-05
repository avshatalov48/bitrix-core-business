<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");


use \Bitrix\Sender\MailingChainTable;
use \Bitrix\Sender\PostingTable;
use \Bitrix\Sender\PostingRecipientTable;

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_sender_mailing_chain";
$MAILING_ID = intval($_REQUEST["MAILING_ID"]);
$ID = intval($_REQUEST["ID"]);

if($_REQUEST["action"]=="copy" && check_bitrix_sessid() && $POST_RIGHT>="W")
{
	$copiedId = \Bitrix\Sender\MailingChainTable::copy($ID);
	if ($copiedId)
	{
		\Bitrix\Sender\MailingChainTable::update(array('ID' => $copiedId), array('CREATED_BY' => $USER->GetID()));
		LocalRedirect("sender_mailing_chain_edit.php?MAILING_ID=" . $MAILING_ID . "&ID=" . $copiedId . "&mess=copied");
	}
}

if($_REQUEST["action"]=="send_to_me" && check_bitrix_sessid() && $POST_RIGHT>="W")
{
	$arResult = array();
	$isChainNotFound = true;
	$sendException = null;
	$filter = array();

	if(isset($_REQUEST["IS_TRIGGER"]) && $_REQUEST["IS_TRIGGER"] == 'Y')
	{
		$filter['=MAILING_ID'] = $MAILING_ID;
		$filter['=IS_TRIGGER'] = 'Y';
	}
	else
	{
		$filter['=ID'] = $ID;
	}

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	$adminMessage = new CAdminMessage('');

	$sendToMeAddr = $_POST["send_to_me_addr"];
	$sendToMeAddr = explode(",", $sendToMeAddr);

	try
	{
		$mailingChainDb = MailingChainTable::getList(array(
			'select' => array('ID'),
			'filter' => $filter,
			'order' => array('TIME_SHIFT' => 'ASC', 'ID' => 'ASC')
		));

		while($mailingChain = $mailingChainDb->fetch())
		{
			$isChainNotFound = false;
			foreach($sendToMeAddr as $address)
			{
				$address = trim($address);
				if(!empty($address))
				{
					$sendResult = \Bitrix\Sender\PostingManager::sendToAddress($mailingChain['ID'], $address);
					if ($sendResult == \Bitrix\Sender\PostingManager::SEND_RESULT_SENT)
					{
						$arResult[] = $address;
						MailingChainTable::setEmailToMeList($address);
					}
				}
			}
		}

	}
	catch(Exception $e)
	{
		$sendException = $e;
	}

	if($isChainNotFound)
	{
		$adminMessage->ShowMessage(GetMessage("MAILING_ADM_POST_NOT_FOUND"));
	}
	elseif($sendException)
	{
		$adminMessage->ShowMessage($sendException->getMessage());
	}
	else
	{
		$arResult = array_unique($arResult);
		if(!empty($arResult))
			$adminMessage->ShowNote(GetMessage("sender_mailing_chain_adm_test_send_success").implode(', ', $arResult));
		else
			$adminMessage->ShowMessage(GetMessage("sender_mailing_chain_adm_test_send_empty"));
	}

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}

if(check_bitrix_sessid() && $POST_RIGHT>="W" && isset($_REQUEST["action"]) && in_array($_REQUEST["action"], array('js_pause', 'js_stop', 'js_send_error')))
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	$message = null;
	$mailingChain = MailingChainTable::getRowById(array('ID' => $ID));
	if($_REQUEST["action"] == "js_pause" && in_array($mailingChain['STATUS'], array(MailingChainTable::STATUS_SEND, MailingChainTable::STATUS_WAIT)))
	{
		MailingChainTable::update(array('ID' => $ID), array('STATUS' => MailingChainTable::STATUS_PAUSE));
		$message = GetMessage("MAILING_ADM_SENDING_PAUSE");
	}
	elseif($_REQUEST["action"] == "js_stop" && $mailingChain['STATUS'] == MailingChainTable::STATUS_PAUSE)
	{
		MailingChainTable::update(array('ID' => $ID), array('STATUS' => MailingChainTable::STATUS_END));
		$message = GetMessage("MAILING_ADM_SENDING_STOP");
	}
	elseif($_REQUEST["action"] == "js_send_error" && MailingChainTable::canReSendErrorRecipients($ID))
	{
		MailingChainTable::prepareReSendErrorRecipients($ID);
		$message = GetMessage("MAILING_ADM_SENDING_PLANING");
	}

	if($message)
	{
		$adminMessage = new CAdminMessage(array("MESSAGE"=>$message, "TYPE"=>"OK"));
		echo $adminMessage->show();
	}
	?>
	<script><?=$sTableID?>.GetAdminList('<?echo $APPLICATION->GetCurPage();?>?MAILING_ID=<?=$MAILING_ID?>&ID=<?=$ID?>&lang=<?=LANGUAGE_ID?>');</script>
	<?

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}

if($_REQUEST["action"]=="js_send" && check_bitrix_sessid() && $POST_RIGHT>="W")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	$message = null;

	$mailingChainId = $ID;

	$mailingChainPrimary = array('ID' => $mailingChainId);
	$mailingChainDb = MailingChainTable::getList(array(
		'select' => array(
			'ID',
			'STATUS',
			'POSTING_ID',
			'REITERATE',
			'AUTO_SEND_TIME',
			'SUBJECT',
		),
		'filter' => $mailingChainPrimary
	));
	$arMailingChain = $mailingChainDb->fetch();

	if($arMailingChain)
	{
		$actionJsList = false;
		$actionJsMoveProgress = false;

		if($arMailingChain["REITERATE"] == 'Y' || !empty($arMailingChain["AUTO_SEND_TIME"]))
		{
			if(in_array($arMailingChain["STATUS"], array(MailingChainTable::STATUS_NEW, MailingChainTable::STATUS_PAUSE)))
			{
				if($arMailingChain["REITERATE"] == 'Y')
					$status = MailingChainTable::STATUS_WAIT;
				else
					$status = MailingChainTable::STATUS_SEND;

				MailingChainTable::update($mailingChainPrimary, array('STATUS' => $status));

				$message = array(
					"MESSAGE"=>GetMessage("MAILING_ADM_SENDING_PLANING"),
					"DETAILS" => $arMailingChain['SUBJECT']
						.'#PROGRESS_BAR#'
					,
					"HTML"=>true,
					"TYPE"=>"PROGRESS",
					"PROGRESS_TOTAL" => 1,
					"PROGRESS_VALUE" => 1,
				);

				if(\Bitrix\Sender\MailingChainTable::isReadyToSend($ID))
				{
					$message["BUTTONS"] = array(
						array(
							"ID" => "btn_cont",
							"VALUE" => GetMessage("MAILING_ADM_BTN_NEXT"),
							"ONCLICK" => "window.location='sender_mailing_chain_admin.php?MAILNG_ID=".$MAILING_ID."&ID=".$ID."&action=send&lang=".LANGUAGE_ID."'",
						)
					);
				}

				$actionJsList = true;
			}
		}
		else
		{
			switch($arMailingChain["STATUS"])
			{
				case MailingChainTable::STATUS_NEW:
					$messageDetails = '<br><p>'.GetMessage("MAILING_ADM_SENDING_NOTE_LINE1").'<br>'.GetMessage("MAILING_ADM_SENDING_NOTE_LINE2").'</p>';
					$message = array(
						"MESSAGE"=> GetMessage("MAILING_ADM_SENDING_SEND"),
						"BUTTONS" => array(
							array(
								"ID" => "btn_stop",
								"VALUE" => GetMessage("MAILING_ADM_BTN_STOP"),
								"ONCLICK" => "Stop()",
							),
							array(
								"ID" => "btn_cont",
								"VALUE" => GetMessage("MAILING_ADM_BTN_CONT"),
								"ONCLICK" => "Cont()",
							),
						),
					);

					MailingChainTable::initPosting($mailingChainId);

					MailingChainTable::update($mailingChainPrimary, array('STATUS' => MailingChainTable::STATUS_SEND));
					$actionJsList = true;
					$actionJsMoveProgress = true;
					break;
				case MailingChainTable::STATUS_SEND:

					\Bitrix\Sender\MailingManager::chainSend($mailingChainId);
					$sendErrors = \Bitrix\Sender\MailingManager::getErrors();
					if(empty($sendErrors))
					{
						$messageDetails = '<br><p>' . GetMessage("MAILING_ADM_SENDING_NOTE_LINE1") . '<br>' . GetMessage("MAILING_ADM_SENDING_NOTE_LINE2") . '</p>';
						$message = array(
							"MESSAGE" => GetMessage("MAILING_ADM_SENDING_SEND"),
							"BUTTONS" => array(
								array(
									"ID" => "btn_stop",
									"VALUE" => GetMessage("MAILING_ADM_BTN_STOP"),
									"ONCLICK" => "Stop()",
								),
								array(
									"ID" => "btn_cont",
									"VALUE" => GetMessage("MAILING_ADM_BTN_CONT"),
									"ONCLICK" => "Cont()",
								),
							),
						);

						$actionJsMoveProgress = true;
					}
					else
					{
						$message = $sendErrors->getMessage();
					}

					break;


				case MailingChainTable::STATUS_PAUSE:
					$message = array(
						"MESSAGE"=> GetMessage("MAILING_ADM_SENDING_PAUSE"),
						"BUTTONS" => array(
							array(
								"ID" => "btn_stop",
								"VALUE" => GetMessage("MAILING_ADM_BTN_STOP"),
								"ONCLICK" => "Stop()",
							),
							array(
								"ID" => "btn_cont",
								"VALUE" => GetMessage("MAILING_ADM_BTN_CONT"),
								"ONCLICK" => "Cont()",
							),
						),
					);

					MailingChainTable::update($mailingChainPrimary, array('STATUS' => MailingChainTable::STATUS_SEND));
					$actionJsList = true;
					$actionJsMoveProgress = true;

					break;

				case MailingChainTable::STATUS_END:
					$message = array(
						"MESSAGE"=>GetMessage("MAILING_ADM_SENDING_RESULT_OK"),
					);

					$actionJsList = true;
					break;

				default:
					$message =  GetMessage("MAILING_ADM_POST_NOT_FOUND");
			}

			if(is_array($message))
			{
				$arEmailStatuses = PostingTable::getRecipientCountByStatus($arMailingChain['POSTING_ID']);
				$nEmailsSent = intval($arEmailStatuses[PostingRecipientTable::SEND_RESULT_SUCCESS]);
				$nEmailsError = intval($arEmailStatuses[PostingRecipientTable::SEND_RESULT_ERROR]);
				$nEmailsNone = intval($arEmailStatuses[PostingRecipientTable::SEND_RESULT_NONE]);
				$nEmailsTotal = $nEmailsNone + $nEmailsSent + $nEmailsError;

				$message = array_merge($message, array(
					"DETAILS" => htmlspecialcharsbx($arMailingChain['SUBJECT']) . $messageDetails
						.'#PROGRESS_BAR#'
						.'<p>'.GetMessage("MAILING_ADM_SENDING_PROCESSED").' <b>'.($nEmailsSent + $nEmailsError).'</b> '
						.GetMessage("MAILING_ADM_SENDING_PROCESSED_OF").' <b>'.$nEmailsTotal.'</b></p>'
						.'<p>'.GetMessage("MAILING_ADM_WITH_ERRORS").': <b>'.$nEmailsError.'</b>.</p>'
					,
					"HTML"=>true,
					"TYPE"=>"PROGRESS",
					"PROGRESS_TOTAL" => $nEmailsTotal,
					"PROGRESS_VALUE" => $nEmailsSent + $nEmailsError,
				));
			}
		}
	}
	else
	{
		$message = GetMessage("MAILING_ADM_POST_NOT_FOUND");
	}

	$adminMessage = new CAdminMessage($message);
	echo $adminMessage->show();
	if($actionJsList && $actionJsMoveProgress)
	{
		?>
		<script><?=$sTableID?>.GetAdminList('<?echo $APPLICATION->GetCurPage();?>?MAILING_ID=<?=$MAILING_ID?>&ID=<?=$ID?>&lang=<?=LANGUAGE_ID?>', MoveProgress());</script>
		<?
	}
	elseif($actionJsList)
	{
		?>
		<script><?=$sTableID?>.GetAdminList('<?echo $APPLICATION->GetCurPage();?>?MAILING_ID=<?=$MAILING_ID?>&ID=<?=$ID?>&lang=<?=LANGUAGE_ID?>');</script>
		<?
	}
	elseif($actionJsMoveProgress)
	{
		?>
		<script>MoveProgress();</script>
		<?
	}

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}

CJSCore::Init(array('window'));

$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;

	return count($lAdmin->arFilterErrors)==0;
}

$FilterArr = Array(
	"find_id",
	"find_name",
	"find_status",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array();
if (CheckFilter())
{
	$arFilter = Array(
		"ID" => ($find!="" && $find_type == "id"? $find:$find_id),
		"%NAME" => ($find!="" && $find_type == "name"? $find:$find_name),
		"STATUS" => $find_status,
	);

	foreach($arFilter as $k => $v) if(empty($v)) unset($arFilter[$k]);
}
$arFilter['=MAILING_ID'] = $MAILING_ID;

if(isset($order)) $order = ($order=='asc'?'ASC': 'DESC');


if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
	if($_REQUEST['action_target']=='selected')
	{
		$dataListDb = \Bitrix\Sender\MailingChainTable::getList(array(
			'select' => array('ID'),
			'filter' => $arFilter,
			'order' => array($by=>$order)
		));
		while($arRes = $dataListDb->fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
		$ID = IntVal($ID);
		$dataPrimary = array('ID' => $ID);
		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			$dataDeleteDb = \Bitrix\Sender\MailingChainTable::delete($dataPrimary);
			if(!$dataDeleteDb->isSuccess())
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("sender_mailing_chain_adm_del_error"), $ID);
			}
			$DB->Commit();
			break;
		}

	}
}

$groupListDb = \Bitrix\Sender\MailingChainTable::getList(array(
	'select' => array(
		'ID', 'MAILING_ID',  'POSTING_ID',  'CREATED_BY',  'STATUS',
		'REITERATE', 'LAST_EXECUTED',  'EMAIL_FROM',  'AUTO_SEND_TIME',
		'DAYS_OF_MONTH', 'DAYS_OF_WEEK', 'TIMES_OF_DAY',
		'NAME' => 'SUBJECT', 'TITLE',
		'MAILING_ACTIVE' => 'MAILING.ACTIVE'
	),
	'filter' => $arFilter,
	'order' => array($by=>$order)
));

$rsData = new CAdminResult($groupListDb, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("sender_mailing_chain_adm_nav")));

$lAdmin->AddHeaders(array(
	array(	"id"		=>"ID",
		"content"	=>"ID",
		"sort"		=>"ID",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"NAME",
		"content"	=>GetMessage("sender_mailing_chain_adm_field_name"),
		"sort"		=>"NAME",
		"default"	=>true,
	),
	array(	"id"		=>"TITLE",
		"content"	=>GetMessage("sender_mailing_chain_adm_field_title"),
		"sort"		=>"TITLE",
		"default"	=>false,
	),
	array(	"id"		=>"CREATED_BY",
		"content"	=>GetMessage("sender_mailing_chain_adm_field_created_by"),
		"sort"		=>"CREATED_BY",
		"default"	=>true,
	),
	array(	"id"		=>"STATUS",
		"content"	=>GetMessage("sender_mailing_chain_adm_field_status"),
		"sort"		=>"STATUS",
		"default"	=>true,
	),
	array(	"id"		=>"EMAIL_FROM",
		"content"	=>GetMessage("sender_mailing_chain_adm_field_email_from"),
		"sort"		=>"EMAIL_FROM",
		"default"	=>false,
	),
	array(	"id"		=>"REITERATE",
		"content"	=>GetMessage("sender_mailing_chain_adm_field_reiterate"),
		"sort"		=>"REITERATE",
		"default"	=>false,
	),
	array(	"id"		=>"AUTO_SEND_TIME",
		"content"	=>GetMessage("sender_mailing_chain_adm_field_auto_send_time"),
		"sort"		=>"AUTO_SEND_TIME",
		"default"	=>false,
	),
));

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("NAME", '<a href="/bitrix/admin/sender_mailing_chain_edit.php?MAILING_ID='.$MAILING_ID.'&ID='.$f_ID.'&amp;lang='.LANG.'">'.$f_NAME.'</a>');
	$arUser = \Bitrix\Main\UserTable::getRowById(intval($f_CREATED_BY));
	$row->AddViewField("CREATED_BY", '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$f_CREATED_BY.'">'.htmlspecialcharsbx($arUser['NAME']." ".$arUser['LAST_NAME'])."</a>");

	$arStatus = MailingChainTable::getStatusList();
	$statusPercent = '';
	if(in_array($f_STATUS, array(MailingChainTable::STATUS_SEND, MailingChainTable::STATUS_PAUSE)))
	{
		$statusPercent = '<br>' . '<span style="font-size: 12px; color: #C2C2C2;">' .
			GetMessage("sender_mailing_chain_adm_action_stat") . ': ' . PostingTable::getSendPercent($f_POSTING_ID) . '%' .
			'</span>';
	}
	$row->AddViewField("STATUS", $arStatus[$f_STATUS] . ' ' . $statusPercent);



	$row->AddViewField("REITERATE", $f_REITERATE == 'Y' ? GetMessage("MAIN_YES") : GetMessage("MAIN_NO"));

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("sender_mailing_chain_adm_action_edit"),
		"ACTION"=>$lAdmin->ActionRedirect("sender_mailing_chain_edit.php?MAILING_ID=".$MAILING_ID."&ID=".$f_ID)
	);

	if ($POST_RIGHT>="W")
	{
		$arActions[] = array(
			"ICON"=>"copy",
			"TEXT"=>GetMessage("sender_mailing_chain_adm_action_copy"),
			"ACTION"=>$lAdmin->ActionRedirect("sender_mailing_chain_admin.php?action=copy&MAILING_ID=".$MAILING_ID."&ID=".$f_ID."&".bitrix_sessid_get())
		);

		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("sender_mailing_chain_adm_action_delete"),
			"ACTION"=>"if(confirm('".GetMessage('sender_mailing_chain_adm_action_delete_confirm')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "MAILING_ID=".$MAILING_ID)
		);
	}

	$arActions[] = array("SEPARATOR"=>true);

	if (in_array($f_STATUS, array(MailingChainTable::STATUS_END, MailingChainTable::STATUS_PAUSE)))
	{
		$arActions[] = array(
			"ICON"=>"",
			"DEFAULT" => false,
			"TEXT"=>GetMessage("sender_mailing_chain_adm_action_stats"),
			"ACTION"=>$lAdmin->ActionRedirect("sender_mailing_stat.php?MAILING_ID=".$MAILING_ID."&ID=".$f_ID)
		);
	}

	if ($f_MAILING_ACTIVE == 'Y')
	{
		switch($f_STATUS)
		{
			case MailingChainTable::STATUS_NEW:
				if ($POST_RIGHT>="W")
				{
					$arActions[] = array(
						"ICON" => "",
						"DEFAULT" => false,
						"TEXT" => GetMessage("sender_mailing_chain_adm_action_send"),
						"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/sender_mailing_chain_admin.php?MAILING_ID=".$MAILING_ID."&ID=".$f_ID."&action=send&lang=" . LANGUAGE_ID)
					);
				}
				break;
			case MailingChainTable::STATUS_WAIT:
			case MailingChainTable::STATUS_SEND:
				if ($POST_RIGHT>="W")
				{
					$arActions[] = array(
						"ICON" => "",
						"DEFAULT" => false,
						"TEXT" => GetMessage("sender_mailing_chain_adm_action_pause"),
						"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/sender_mailing_chain_admin.php?MAILING_ID=".$MAILING_ID."&ID=".$f_ID."&action=pause&lang=" . LANGUAGE_ID)
					);
				}
				break;
			case MailingChainTable::STATUS_PAUSE:
				if ($POST_RIGHT>="W")
				{
					$arActions[] = array(
						"ICON" => "",
						"DEFAULT" => false,
						"TEXT" => GetMessage("sender_mailing_chain_adm_action_send"),
						"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/sender_mailing_chain_admin.php?MAILING_ID=".$MAILING_ID."&ID=".$f_ID."&action=send&lang=" . LANGUAGE_ID)
					);

					$arActions[] = array(
						"ICON" => "",
						"DEFAULT" => false,
						"TEXT" => GetMessage("sender_mailing_chain_adm_action_stop"),
						"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/sender_mailing_chain_admin.php?MAILING_ID=".$MAILING_ID."&ID=".$f_ID."&action=stop&lang=" . LANGUAGE_ID)
					);
				}
				break;
		}
	}


	if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
		unset($arActions[count($arActions)-1]);
	$row->AddActions($arActions);

endwhile;

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);
$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
));

$aContext = array(
	array(
		"TEXT"=>GetMessage("MAIN_ADD"),
		"LINK"=>"/bitrix/admin/sender_mailing_chain_edit.php?MAILING_ID=".$MAILING_ID."&lang=".LANGUAGE_ID,
		"TITLE"=>GetMessage("POST_ADD_TITLE"),
		"ICON"=>"btn_new",
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("sender_mailing_chain_adm_title"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		GetMessage("sender_mailing_chain_adm_field_name"),
		GetMessage("sender_mailing_chain_adm_field_status"),
	)
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><?="ID"?>:</td>
	<td>
		<input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("sender_mailing_chain_adm_field_name")?>:</td>
	<td>
		<input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("sender_mailing_chain_adm_field_status")?>:</td>
	<td>
		<?
		$arStatus = \Bitrix\Sender\MailingChainTable::getStatusList();
		$arr = array(
			"reference" => array_values($arStatus),
			"reference_id" => array_keys($arStatus)
		);
		echo SelectBoxFromArray("find_status", $arr, $find_status, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage()."?MAILING_ID=".$MAILING_ID,"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?
//******************************
// Send mailing and show progress
//******************************
$jsAction = $_REQUEST['action'];
if(in_array($jsAction, array('send', 'pause', 'stop', 'send_error'))):

	$canSend = true;
	if($jsAction == 'send')
	{
		$canSend = \Bitrix\Sender\MailingChainTable::isReadyToSend($ID);
		if(!$canSend)
			$canSend = \Bitrix\Sender\MailingChainTable::isManualSentPartly($ID);
	}


	if($canSend):
		?>
		<div id="progress_message"></div>
		<script>
			var stop = false;
			function Stop()
			{
				stop=true;
				document.getElementById('btn_stop').disabled = true;
				document.getElementById('btn_cont').disabled = false;
			}
			function Cont()
			{
				stop=false;
				document.getElementById('btn_stop').disabled = false;
				document.getElementById('btn_cont').disabled = true;
				MoveProgress();
			}
			function MoveProgress()
			{
				if(stop)
					return;

				var url = '/bitrix/admin/sender_mailing_chain_admin.php?lang=<?echo LANGUAGE_ID?>&MAILING_ID=<?echo $MAILING_ID?>&ID=<?echo $ID?>&<?echo bitrix_sessid_get()?>&action=js_<?=htmlspecialcharsbx($jsAction)?>';
				ShowWaitWindow();
				BX.ajax.post(
					url,
					null,
					function(result){
						CloseWaitWindow();
						document.getElementById('progress_message').innerHTML = result;
					}
				);
			}

			setTimeout('MoveProgress()', 100);
		</script>
	<?
	endif;
endif;?>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>