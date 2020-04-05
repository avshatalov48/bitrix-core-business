<?
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('iblock');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$IBLOCK_ID = (isset($_GET['IBLOCK_ID']) ? (int)$_GET['IBLOCK_ID'] : 0);
if (!CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$max_execution_time = (isset($_REQUEST['max_execution_time']) ? (int)$_REQUEST['max_execution_time'] : 0);
if ($max_execution_time <= 0)
	$max_execution_time = 20;

$res = false;
$iblockDropDown = array();
$iblockFilter = array('=PROPERTY_INDEX' => 'I');
if (Loader::includeModule('catalog'))
{
	$OfferIblocks = array();
	$offersIterator = \Bitrix\Catalog\CatalogIblockTable::getList(array(
		'select' => array('IBLOCK_ID'),
		'filter' => array('!PRODUCT_IBLOCK_ID' => 0)
	));
	while ($offer = $offersIterator->fetch())
	{
		$OfferIblocks[] = (int)$offer['IBLOCK_ID'];
	}
	if (!empty($OfferIblocks))
	{
		unset($offer);
		$iblockFilter['!ID'] = $OfferIblocks;
	}
	unset($offersIterator, $OfferIblocks);
}
$iblockList = \Bitrix\Iblock\IblockTable::getList(array(
	'select' => array('ID', 'NAME', 'ACTIVE'),
	'filter' => $iblockFilter,
	'order'  => array('ID' => 'asc', 'NAME' => 'asc'),
));
while ($iblockInfo = $iblockList->fetch())
{
	$iblockDropDown[$iblockInfo['ID']] = '['.$iblockInfo['ID'].'] '.$iblockInfo['NAME'].($iblockInfo['ACTIVE'] == 'N' ? ' ('.GetMessage('IBLOCK_REINDEX_DEACTIVE').')' : '');
}
unset($iblockInfo, $iblockList);

if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["Reindex"]=="Y")
{
	CUtil::JSPostUnescape();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	if (empty($iblockDropDown))
	{
		$message = new CAdminMessage(array(
			"MESSAGE" => GetMessage("IBLOCK_REINDEX_COMPLETE"),
			"DETAILS" => GetMessage("IBLOCK_REINDEX_TOTAL_COMPLETE"),
			"HTML" => true,
			"TYPE" => "OK",
		));
		echo $message->Show();
	}
	else
	{
		if (!isset($iblockDropDown[$IBLOCK_ID]))
			$IBLOCK_ID = key($iblockDropDown);

		$index = \Bitrix\Iblock\PropertyIndex\Manager::createIndexer($IBLOCK_ID);

		if (!empty($_POST["NS"]) && is_array($_POST["NS"]))
		{
			$NS = $_POST["NS"];
		}
		else
		{
			$NS = array();
		}

		if (!isset($NS[$IBLOCK_ID]))
		{
			$NS[$IBLOCK_ID] = array(
				"CNT" => 0,
				"LAST_ID" => 0,
			);
			$index->startIndex();
			$NS[$IBLOCK_ID]["TOTAL"] = $index->estimateElementCount();
		}

		$index->setLastElementId($NS[$IBLOCK_ID]["LAST_ID"]);
		$res = $index->continueIndex($max_execution_time);
		if ($res > 0)
		{
			$NS[$IBLOCK_ID]["CNT"] += $res;
			$NS[$IBLOCK_ID]["LAST_ID"] = $index->getLastElementId();

			$message = new CAdminMessage(array(
				"MESSAGE" => GetMessage("IBLOCK_REINDEX_IN_PROGRESS"),
				"DETAILS" => GetMessage("IBLOCK_REINDEX_TOTAL") . " <span id=\"some_left\"><b>" . $NS[$IBLOCK_ID]["CNT"] . "</b></span><br>#PROGRESS_BAR#",
				"HTML" => true,
				"TYPE" => "PROGRESS",
				"PROGRESS_TOTAL" => $NS[$IBLOCK_ID]["TOTAL"],
				"PROGRESS_VALUE" => $NS[$IBLOCK_ID]["CNT"],
			));
			echo $message->Show();
			?>
			<script type="text/javascript">
				jsSelectUtils.selectOption(BX('iblock'), <?echo $IBLOCK_ID?>);
				DoNext(<?echo CUtil::PhpToJSObject($NS)?>);
			</script>
			<?
		}
		else
		{
			$index->endIndex();
			\Bitrix\Iblock\PropertyIndex\Manager::checkAdminNotification();
			CBitrixComponent::clearComponentCache("bitrix:catalog.smart.filter");
			CIBlock::clearIblockTagCache($IBLOCK_ID);
			unset($iblockDropDown[$IBLOCK_ID]);

			if (empty($iblockDropDown) || $NS['iblock'] > 0)
				$mess = GetMessage("IBLOCK_REINDEX_TOTAL") . " <b>" . $NS[$IBLOCK_ID]["CNT"] . "</b>";
			else
				$mess = GetMessage("IBLOCK_REINDEX_TOTAL") . " <span id=\"some_left\"><b>" . $NS[$IBLOCK_ID]["CNT"] . "</b></span>";

			$message = new CAdminMessage(array(
				"MESSAGE" => GetMessage("IBLOCK_REINDEX_COMPLETE"),
				"DETAILS" => $mess,
				"HTML" => true,
				"TYPE" => "OK",
			));
			echo $message->Show();
			?>
			<script type="text/javascript">
				jsSelectUtils.deleteOption(BX('iblock'), <?echo $IBLOCK_ID?>);
				<?
				if (!empty($iblockDropDown) && $NS['iblock'] <= 0)
				{
					$IBLOCK_ID = key($iblockDropDown);
					?>
					jsSelectUtils.selectOption(BX('iblock'), <?echo $IBLOCK_ID?>);
					DoNext(<?echo CUtil::PhpToJSObject($NS)?>);
					<?
				}
				?>
			</script>
			<?
		}
	}
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}
elseif (empty($iblockDropDown))
{
	$APPLICATION->SetTitle(GetMessage("IBLOCK_REINDEX_TITLE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$message = new CAdminMessage(array(
		"DETAILS" => GetMessage("IBLOCK_REINDEX_TOTAL_COMPLETE"),
		"HTML" => true,
		"TYPE" => "OK",
	));
	echo $message->Show();
	$aMenu = array(
		array(
			"TEXT" => GetMessage("IBLOCK_BACK_TO_ADMIN"),
			"LINK" => '/bitrix/admin/iblock_reindex_admin.php?lang='.LANGUAGE_ID,
			"ICON" => "btn_list",
		)
	);
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
else
{
	$APPLICATION->SetTitle(GetMessage("IBLOCK_REINDEX_TITLE"));
	$aTabs = array(
		array(
			"DIV" => "edit1",
			"TAB" => GetMessage("IBLOCK_REINDEX_TAB"),
			"ICON" => "main_user_edit",
			"TITLE" => GetMessage("IBLOCK_REINDEX_TAB_TITLE"),
		),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?>
	<script type="text/javascript">
	var savedNS,
		stop,
		interval = 0;
	function StartReindex()
	{
		stop = false;
		BX('reindex_result_div').innerHTML = '';
		BX('stop_button').disabled = false;
		BX('start_button').disabled = true;
		BX('continue_button').disabled = true;
		DoNext({iblock: BX('iblock').value});
	}
	function StopReindex()
	{
		stop = true;
		BX('stop_button').disabled = true;
		BX('start_button').disabled = false;
		BX('continue_button').disabled = false;
	}
	function ContinueReindex()
	{
		stop = false;
		BX('stop_button').disabled = false;
		BX('start_button').disabled = true;
		BX('continue_button').disabled = true;
		DoNext(savedNS);
	}
	function EndReindex()
	{
		stop = true;
		BX('stop_button').disabled = true;
		BX('start_button').disabled = false;
		BX('continue_button').disabled = true;
	}
	function DoNext(NS)
	{
		var queryString = 'Reindex=Y'
			+ '&lang=<?echo LANGUAGE_ID?>'
			+ '&IBLOCK_ID=' + BX('iblock').value
			;

		savedNS = NS;

		if(!stop)
		{
			BX.showWait();
			BX.ajax.post(
				'iblock_reindex.php?' + queryString,
				{
					'NS': NS,
					'max_execution_time': BX('max_execution_time').value,
					'sessid': BX.bitrix_sessid()
				},
				function(result)
				{
					BX('reindex_result_div').innerHTML = result;
					BX.closeWait();
					if(!BX('some_left'))
					{
						EndReindex();
					}
				}
			);
		}
	}
	</script>
	<div id="reindex_result_div"></div>
	<?
	$aMenu = array(
		array(
			"TEXT" => GetMessage("IBLOCK_BACK_TO_ADMIN"),
			"LINK" => '/bitrix/admin/iblock_reindex_admin.php?lang='.LANGUAGE_ID,
			"ICON" => "btn_list",
		)
	);
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
	?>
	<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo htmlspecialcharsbx(LANG)?>" name="fs1">
	<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
		<tr>
			<td><label for="iblock"><?echo GetMessage("IBLOCK_REINDEX_IBLOCK")?></label></td>
			<td><select name="iblock" id="iblock"> <?echo GetMessage("IBLOCK_REINDEX_STEP_SEC")?>
					<option value=""><?echo GetMessage('MAIN_ALL')?></option>
					<?foreach ($iblockDropDown as $key => $value)
					{
						?><option value="<?echo htmlspecialcharsbx($key)?>" <?if ($_GET['IBLOCK_ID'] == $key) echo 'selected="selected"';?>><?echo htmlspecialcharsEx($value)?></option><?
					}?>
				</select></td>
		</tr>
		<tr>
			<td><label for="max_execution_time"><?echo GetMessage("IBLOCK_REINDEX_STEP")?></label></td>
			<td><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?echo $max_execution_time;?>"> <?echo GetMessage("IBLOCK_REINDEX_STEP_SEC")?></td>
		</tr>
	<?
	$tabControl->Buttons();
	?>
		<input type="button" id="start_button" value="<?echo GetMessage("IBLOCK_REINDEX_START_BUTTON")?>" OnClick="StartReindex();" class="adm-btn-save">
		<input type="button" id="stop_button" value="<?=GetMessage("IBLOCK_REINDEX_STOP_BUTTON")?>" OnClick="StopReindex();" disabled>
		<input type="button" id="continue_button" value="<?=GetMessage("IBLOCK_REINDEX_CONTINUE_BUTTON")?>" OnClick="ContinueReindex();" disabled>
	<?
	$tabControl->End();
	?>
	</form>
	<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}