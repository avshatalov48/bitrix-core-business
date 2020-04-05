<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_sender_contact";

if($_REQUEST["action"]=="js_pull" && check_bitrix_sessid() && $POST_RIGHT>="W")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	$connectorCountInfo = '('.htmlspecialcharsbx($_REQUEST['CONNECTOR_COUNT_INFO_CURR']).' '.GetMessage('CONTACT_ADM_PULL_FROM').' '.htmlspecialcharsbx($_REQUEST['CONNECTOR_COUNT_INFO_ALL']).')';
	$connectorDataPage = intval($_REQUEST['CONNECTOR_PAGE']);
	$isShowConnectorInfo = ($connectorDataPage <= 0);
	$isShowNextButton = (bool) intval($_REQUEST['CONNECTOR_NEXT_EXIST']);
	$isPulling = false;
	$isUpdateList = false;

	$connector = null;
	$endpoint = $_REQUEST['CONNECTOR_ENDPOINT'];
	if(is_array($endpoint) && isset($endpoint['MODULE_ID']) && !empty($endpoint['CODE']))
		$connector = \Bitrix\Sender\ConnectorManager::getConnector($endpoint);

	if($connector)
	{
		if($isShowConnectorInfo)
		{
			$countAll = $connector->getDataCount();
			$message = array(
				"MESSAGE" => $connector->getName().($connectorCountInfo),
				"DETAILS" => GetMessage("CONTACT_ADM_PULL")
					.'#PROGRESS_BAR#'
					.'<p>'.GetMessage("CONTACT_ADM_PULL_ALL").' <b>'.($countAll).'</b> '
				,
				"HTML"=>true,
				"TYPE"=>"PROGRESS",
				"PROGRESS_TOTAL" => $countAll,
				"PROGRESS_VALUE" => 0,
				"BUTTONS" => array(
					array(
						"ID" => "btn_start",
						"VALUE" => GetMessage("CONTACT_ADM_BTN_START"),
						"ONCLICK" => "Start()",
					),
				)
			);

			if($isShowNextButton)
			{
				$message["BUTTONS"][] = array(
					"ID" => "btn_next",
					"VALUE" => GetMessage("CONTACT_ADM_BTN_SKIP"),
					"ONCLICK" => "Next()",
				);
			}
		}
		else
		{
			$timeout = \COption::GetOptionInt("sender", "interval");
			$arPullResult = \Bitrix\Sender\ContactTable::addFromConnector($connector, $connectorDataPage, $timeout);

			$arPullResult['COUNT_NEW'] += intval($_REQUEST['COUNTER_ADDRESS_NEW']);
			$arPullResult['COUNT_ERROR'] += intval($_REQUEST['COUNTER_ADDRESS_ERROR']);
			$message = array(
				"MESSAGE" => $connector->getName().($connectorCountInfo),
				"DETAILS" => ($arPullResult['STATUS'] ? GetMessage("CONTACT_ADM_PULLING") : GetMessage("CONTACT_ADM_PULLED"))
					.'#PROGRESS_BAR#'
					.'<p>'.GetMessage("CONTACT_ADM_PULL_ALL").' <b>'.($arPullResult['COUNT_ALL']).'</b> </p>'
					.'<p>'.GetMessage("CONTACT_ADM_PULL_NEW").' <b>'.($arPullResult['COUNT_NEW']).'</b> </p>'
					.'<p>'.GetMessage("CONTACT_ADM_PULL_ERROR").' <b>'.($arPullResult['COUNT_ERROR']).'</b> </p>'
				,
				"HTML"=>true,
				"TYPE"=>"PROGRESS",
				"PROGRESS_TOTAL" => $arPullResult['COUNT_ALL'],
				"PROGRESS_VALUE" => $arPullResult['COUNT_PROGRESS'],
				"BUTTONS" => array()
			);


			if($arPullResult['STATUS'])
			{
				$message["BUTTONS"][] = array(
					"ID" => "btn_stop",
					"VALUE" => GetMessage("CONTACT_ADM_BTN_STOP"),
					"ONCLICK" => "Stop()",
				);
				$message["BUTTONS"][] = array(
					"ID" => "btn_cont",
					"VALUE" => GetMessage("CONTACT_ADM_BTN_CONT"),
					"ONCLICK" => "Cont()",
				);

				$isPulling = true;
			}
			else
			{
				$isUpdateList = true;
			}


			if($isShowNextButton)
			{
				$message["BUTTONS"][] = array(
					"ID" => "btn_next",
					"VALUE" => GetMessage("CONTACT_ADM_BTN_NEXT"),
					"ONCLICK" => "Next()",
				);
			}

			?>
			<script>
				currentConnectorPage = <?=intval($arPullResult['STATUS'])?>;
				counterAddressNew = <?=$arPullResult['COUNT_NEW']?>;
				counterAddressError = <?= $arPullResult['COUNT_ERROR']?>;
			</script>
			<?
		}
	}
	else
	{
		$message = GetMessage("CONTACT_CONNECTOR_NOT_FOUND");
	}

	$adminMessage = new CAdminMessage($message);
	echo $adminMessage->show();

	if($isPulling)
	{
		?>
		<script>MoveProgress();</script>
		<?
	}
	if($isUpdateList)
	{
		?>
		<script><?=$sTableID?>.GetAdminList('<?echo $APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID?>');</script>
		<?
	}

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}

$needGroup = false;
$arFilter = array();
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;

	return count($lAdmin->arFilterErrors)==0;
}

$FilterArr = Array(
	"find_name",
	"find_email",
	"find_list",
	"find_subscribed",
	"find_unsubscribed",
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter())
{
	$arFilter = Array(
		"%NAME" => $find_name,
		"%EMAIL" => $find_email,
	);
	if($find_list>0)
		$arFilter["=CONTACT_LIST.LIST_ID"] = $find_list;
	elseif(!empty($find_list))
		$arFilter["=CONTACT_LIST.LIST_ID"] = $find_list;
	if(!empty($find_subscribed))
	{
		if($find_subscribed == 'ALL')
		{
			$arFilter[">MAILING_SUBSCRIPTION.MAILING_ID"] = 0;
			$needGroup = true;
		}
		else
		{
			$arFilter["=MAILING_SUBSCRIPTION.MAILING_ID"] = $find_subscribed;
		}
	}
	if(!empty($find_unsubscribed))
	{
		if($find_unsubscribed == 'ALL')
		{
			$arFilter[">MAILING_UNSUBSCRIPTION.MAILING_ID"] = 0;
			$needGroup = true;
		}
		else
		{
			$arFilter["=MAILING_UNSUBSCRIPTION.MAILING_ID"] = $find_unsubscribed;
		}
	}

	foreach($arFilter as $k => $v)
	{
		if(!in_array($k, array('=CONTACT_LIST.LIST_ID', '>MAILING_UNSUBSCRIPTION.MAILING_ID', '>MAILING_SUBSCRIPTION.MAILING_ID')) && empty($v))
		{
			unset($arFilter[$k]);
		}
	}
}

if(isset($order)) $order = ($order=='asc'?'ASC': 'DESC');

if($lAdmin->EditAction() && $POST_RIGHT=="W")
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$connection = \Bitrix\Main\Application::getInstance()->getConnection();
		$connection->startTransaction();
		$ID = IntVal($ID);
		$dataPrimary = array('ID' => $ID);
		$arData = \Bitrix\Sender\ContactTable::getRowById($dataPrimary);
		if($arData)
		{
			foreach($arFields as $key=>$value)
				$arData[$key]=$value;

			unset($arData['ID']);
			$dataUpdateDb = \Bitrix\Sender\ContactTable::update($dataPrimary, $arData);
			if(!$dataUpdateDb->isSuccess())
			{
				$LAST_ERROR = $dataUpdateDb->getErrorMessages();
				$LAST_ERROR = $LAST_ERROR[0];
				$lAdmin->AddGroupError(GetMessage("rub_save_error")." ".$LAST_ERROR, $ID);
				$connection->rollbackTransaction();
			}
		}
		else
		{
			$lAdmin->AddGroupError(GetMessage("rub_save_error")." ".GetMessage("rub_no_rubric"), $ID);
			$connection->rollbackTransaction();
		}
		$connection->commitTransaction();
	}
}

if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
	if($_REQUEST['action_target']=='selected')
	{
		$dataDb = \Bitrix\Sender\ContactTable::getList(array(
			'select' => array('ID'),
			'filter' => $arFilter,
			'order' => array($by=>$order)
		));
		while($arRes = $dataDb->fetch())
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
				$connection = \Bitrix\Main\Application::getInstance()->getConnection();
				$connection->startTransaction();
				$dataDeleteDb = \Bitrix\Sender\ContactTable::delete($dataPrimary);
				if (!$dataDeleteDb->isSuccess())
				{
					$connection->rollbackTransaction();
					$lAdmin->AddGroupError(GetMessage("rub_del_err"), $ID);
				}
				$connection->commitTransaction();
				break;
		}
	}
}

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-sender-contact");
$selectParams = array(
	'select' => array('ID', 'DATE_INSERT', 'NAME', 'EMAIL'),
	'filter' => $arFilter,
	'order' => array($by=>$order),
	'count_total' => true,
	'offset' => $nav->getOffset(),
	'limit' => $nav->getLimit(),
);
if($needGroup)
{
	$selectParams['group'] = array('ID', 'DATE_INSERT', 'NAME', 'EMAIL');
}
$contactListDb = \Bitrix\Sender\ContactTable::getList($selectParams);
$nav->setRecordCount($contactListDb->getCount());
$lAdmin->setNavigation($nav, \Bitrix\Main\Localization\Loc::getMessage("contact_nav"));

$lAdmin->AddHeaders(array(
	array(	"id"		=>"DATE_INSERT",
		"content"	=>GetMessage("rub_date_insert"),
		"sort"		=>"DATE_INSERT",
		"align"		=>"left",
		"default"	=>true,
	),
	array(	"id"		=>"NAME",
		"content"	=>GetMessage("rub_name"),
		"sort"		=>"NAME",
		"default"	=>true,
	),
	array(	"id"		=>"EMAIL",
		"content"	=>GetMessage("rub_email"),
		"sort"		=>"EMAIL",
		"default"	=>true,
	),
	array(	"id"		=>"LIST",
		"content"	=>GetMessage("rub_list"),
		"default"	=>true,
	),
));

while($contact = $contactListDb->fetch())
{
	$contactId = htmlspecialcharsbx($contact["ID"]);
	$row =& $lAdmin->AddRow($contactId, $contact);

	$row->AddViewField("DATE_INSERT", htmlspecialcharsbx($contact["DATE_INSERT"]));
	$row->AddInputField("NAME", array("size" => 20));
	$row->AddViewField("NAME", htmlspecialcharsbx($contact["NAME"]));
	$row->AddInputField("EMAIL", array("size" => 20));
	$row->AddViewField("EMAIL", htmlspecialcharsbx($contact["EMAIL"]));

	$list = array();
	$listDb = \Bitrix\Sender\ListTable::getList(array(
		'select' => array('NAME', 'ID'),
		'filter' => array('CONTACT_LIST.CONTACT_ID' => $contactId),
	));
	while ($item = $listDb->fetch())
	{
		$list[] = htmlspecialcharsbx($item['NAME']);
	}
	$list = implode(', ', $list);
	$row->AddViewField("LIST", $list);

	$actions = Array();

	$actions[] = array(
		"ICON" => "edit",
		"DEFAULT" => true,
		"TEXT" => GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"ACTION" => $lAdmin->ActionRedirect("sender_contact_edit.php?ID=" . $contactId)
	);

	if ($POST_RIGHT >= "W")
	{
		$actions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"ACTION" => "if(confirm('" . GetMessage('CONTACT_DELETE_CONFIRM') . "')) " . $lAdmin->ActionDoGroup($contactId, "delete")
		);
	}

	$actions[] = array("SEPARATOR" => true);


	if (is_set($actions[count($actions) - 1], "SEPARATOR"))
	{
		unset($actions[count($actions) - 1]);
	}
	$row->AddActions($actions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=> $nav->getRecordCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);
$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
));

$aContext = array(
	array(
		"TEXT"=> GetMessage("SENDER_CONTACT_LIST_BUTTON_ADD"),
		"TITLE"=>GetMessage("POST_ADD_TITLE"),
		"ICON"=>"btn_new",
		"MENU" => array(
			array(
				"TEXT" => GetMessage("SENDER_CONTACT_LIST_BUTTON_ADD_FORM"),
				"ACTION" => $lAdmin->ActionRedirect("sender_contact_edit.php?lang=".LANGUAGE_ID),
			),
			array(
				"TEXT" => GetMessage("SENDER_CONTACT_LIST_BUTTON_ADD_LIST"),
				"ACTION" => $lAdmin->ActionRedirect("sender_contact_import.php?lang=".LANGUAGE_ID),
			),
			array(
				"TEXT" => GetMessage("SENDER_CONTACT_LIST_BUTTON_ADD_FILE"),
				"ACTION" => $lAdmin->ActionRedirect("sender_contact_import.php?lang=".LANGUAGE_ID),
			),
			(
				$POST_RIGHT>="W"
				?
				array(
					"TEXT" => GetMessage("SENDER_CONTACT_LIST_BUTTON_ADD_PULL1"),
					"ACTION" => $lAdmin->ActionRedirect("sender_contact_admin.php?action=pull&lang=".LANGUAGE_ID),
				)
				: null
			)
		)
	),
);

$aContext[] = array(
	"TEXT"=>GetMessage("SENDER_CONTACT_LIST_EDIT"),
	"LINK"=>"/bitrix/admin/sender_list_admin.php?lang=".LANG,
	"TITLE"=>GetMessage("SENDER_CONTACT_LIST_EDIT_TITLE"),
	"ICON"=>"",
);

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("rub_title"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("rub_f_name"),
		GetMessage("rub_f_email"),
		GetMessage("rub_f_subscribed"),
		GetMessage("rub_f_unsubscribed"),
		GetMessage("rub_f_list"),
	)
);

$oFilter->SetDefaultRows(array("find_email", "find_list"));

$oFilter->AddPreset(array(
	"ID" => "subscribed_list",
	"NAME" => GetMessage('SENDER_CONTACT_LIST_PRESET_SUB'),
	"FIELDS" => array(
		"find_email" => "",
		"find_subscribed" => "ALL",
	),
));

$oFilter->AddPreset(array(
	"ID" => "unsubscribed_list",
	"NAME" => GetMessage('SENDER_CONTACT_LIST_PRESET_UN_SUB'),
	"FIELDS" => array(
		"find_email" => "",
		"find_unsubscribed" => "ALL",
	),
));


$filterMailingSubList = array('reference' => array(GetMessage('rub_f_yes')), 'reference_id' => array('ALL'));
$filterMailingUnSubList = array('reference' => array(GetMessage('rub_f_yes')), 'reference_id' => array('ALL'));
$mailingDb = \Bitrix\Sender\MailingTable::getList(array('select'=>array('IS_TRIGGER', 'REFERENCE'=>'NAME','REFERENCE_ID'=>'ID')));
while($arMailing = $mailingDb->fetch())
{
	if($arMailing['IS_TRIGGER'] != 'Y')
	{
		$filterMailingSubList['reference'][] = $arMailing['REFERENCE'];
		$filterMailingSubList['reference_id'][] = $arMailing['REFERENCE_ID'];
	}

	$filterMailingUnSubList['reference'][] = $arMailing['REFERENCE'];
	$filterMailingUnSubList['reference_id'][] = $arMailing['REFERENCE_ID'];
}
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><?=GetMessage("rub_f_name")?>:</td>
	<td>
		<input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_email")?>:</td>
	<td>
		<input type="text" name="find_email" size="47" value="<?echo htmlspecialcharsbx($find_email)?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_subscribed")?>:</td>
	<td>
		<?echo SelectBoxFromArray("find_subscribed", $filterMailingSubList, $rub_f_subscribed, GetMessage("MAIN_ALL"), "");?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_unsubscribed")?>:</td>
	<td>
		<?echo SelectBoxFromArray("find_unsubscribed", $filterMailingUnSubList, $rub_f_unsubscribed, GetMessage("MAIN_ALL"), "");?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_list")?>:</td>
	<td>
		<?
		$arr = array();
		$arr['reference'][] = GetMessage("SENDER_CONTACT_ADM_FILTER_WITHOUT");
		$arr['reference_id'][] = 'NONE';
		$listDb = \Bitrix\Sender\ListTable::getList(array('select'=>array('REFERENCE'=>'NAME','REFERENCE_ID'=>'ID')));
		while($arList = $listDb->fetch())
		{
			$arr['reference'][] = $arList['REFERENCE'];
			$arr['reference_id'][] = $arList['REFERENCE_ID'];
		}
		echo SelectBoxFromArray("find_list", $arr, $find_list, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>


<?
//******************************
// Import addresses from connectors
//******************************
if($_REQUEST['action']=="pull"):

	$connectorClassList = \Bitrix\Sender\ConnectorManager::getConnectorClassList();
	if(!empty($connectorClassList)):
		$arConnectors = array();
		/** @var \Bitrix\Sender\Connector $connector */
		foreach($connectorClassList as $connectorClass)
		{
			if($connectorClass['MODULE_ID'] != 'sender' && !$connectorClass['REQUIRE_CONFIGURE'])
			{
				$arConnectors[] = array(
					'CODE' => $connectorClass['CODE'],
					'MODULE_ID' => $connectorClass['MODULE_ID']
				);
			}
		}

		$arMessages = array(
			'CONTACT_ADM_PULL_FROM' => GetMessage('CONTACT_ADM_PULL_FROM')
		);
		?>
		<div id="progress_message"></div>
		<script>
			BX.message(<?=CUtil::PhpToJSObject($arMessages);?>);
			var connectors = <?=CUtil::PhpToJSObject($arConnectors);?>;

			var stop = false;
			var currentConnector = -1;
			var currentConnectorPage = 0;

			var counterAddressNew = 0;
			var counterAddressError = 0;
			var counterAddressAll = 0;

			function disableButton(id, cond)
			{
				if(document.getElementById(id))
					document.getElementById(id).disabled = cond;
			}
			function Stop()
			{
				stop=true;
				disableButton('btn_stop', true);
				disableButton('btn_cont', false);
				disableButton('btn_next', false);
				disableButton('btn_start', false);
			}
			function Cont()
			{
				stop=false;
				disableButton('btn_stop', false);
				disableButton('btn_cont', true);
				disableButton('btn_next', false);
				disableButton('btn_start', false);
				MoveProgress();
			}
			function Next()
			{
				stop=false;
				disableButton('btn_stop', false);
				disableButton('btn_cont', false);
				disableButton('btn_next', true);
				disableButton('btn_start', true);
				NextConnector();
			}
			function Start()
			{
				stop=false;
				disableButton('btn_stop', false);
				disableButton('btn_cont', false);
				disableButton('btn_next', false);
				disableButton('btn_start', true);
				StartConnector();
			}
			function NextConnector()
			{
				currentConnector++;
				currentConnectorPage = 0;
				MoveProgress();
			}
			function StartConnector()
			{
				counterAddressError = 0;
				counterAddressNew = 0;
				currentConnectorPage = 1;
				MoveProgress();
			}
			function MoveProgress()
			{
				if(stop)
					return;

				var hasCurrConnector = (BX.util.in_array(currentConnector, BX.util.array_keys(connectors))?1:0);
				var hasNextConnector = (BX.util.in_array(currentConnector+1, BX.util.array_keys(connectors))?1:0);

				var data = {};
				data['CONNECTOR_PAGE'] = currentConnectorPage;
				data['CONNECTOR_NEXT_EXIST'] = hasNextConnector;
				data['CONNECTOR_ENDPOINT'] = (hasCurrConnector ? connectors[currentConnector] : '');
				data['CONNECTOR_COUNT_INFO_CURR'] = currentConnector+1;
				data['CONNECTOR_COUNT_INFO_ALL'] = connectors.length;
				data['COUNTER_ADDRESS_ALL'] = counterAddressAll;
				data['COUNTER_ADDRESS_NEW'] = counterAddressNew;
				data['COUNTER_ADDRESS_ERROR'] = counterAddressError;

				var url = '/bitrix/admin/sender_contact_admin.php?lang=<?echo LANGUAGE_ID?>&<?echo bitrix_sessid_get()?>&action=js_pull';
				ShowWaitWindow();
				BX.ajax.post(
					url,
					data,
					function(result){
						CloseWaitWindow();
						document.getElementById('progress_message').innerHTML = result;

						if(!stop)
							disableButton('btn_cont', true);
						else
							disableButton('btn_stop', true);
					}
				);
			}

			setTimeout('NextConnector()', 100);
		</script>
	<?
	endif;
endif;?>


<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>