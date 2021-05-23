<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Bitrix\Sale,
	Bitrix\Main\Type,
	Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
global $APPLICATION, $USER;
/** @var CAdminMessage $message */
Loader::includeModule('sale');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if($saleModulePermissions == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$statusesList = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('delete'));
	
if($saleModulePermissions < "W" && empty($statusesList))
{
	LocalRedirect("sale_order_archive.php?lang=".LANGUAGE_ID);
}

$res = false;

if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["archive"]=="Y" && check_bitrix_sessid())
{
	CUtil::JSPostUnescape();
	@set_time_limit(0);

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	$timeLimit = null;
	$filter = array();

	if(isset($_REQUEST["site_id"]) && $_REQUEST["site_id"] != "")
		$nextStep["SITE_ID"] = $_REQUEST["site_id"];

	if (isset($_POST['archive_status_id']))
	{
		$filter["STATUS_ID"] = array();
		foreach ($_POST['archive_status_id'] as $key=>$status)
		{
			if ($saleModulePermissions == "W" || in_array($status[$key], $statusesList))
			{
				$filter["STATUS_ID"][] = $status[$key];
			}
		}
	}

	if($_POST['archive_payed'] <> '')
	{
		$filter["=PAYED"] = $_POST['archive_payed'];
	}

	if($_POST['archive_canceled'] <> '')
	{
		$filter["=CANCELED"] = $_POST['archive_canceled'];
	}

	if($_POST['archive_deducted'] <> '')
	{
		$filter["=DEDUCTED"] = $_POST['archive_deducted'];
	}

	if (isset($_POST['archive_site']))
	{
		foreach ($_POST['archive_site'] as $key=>$site)
		{
			$filter["LID"][] = $site[$key];
		}
	}

	if ((int)($_POST['archive_period']) > 0)
	{
		$date = new Type\DateTime();
		$latestDate = $date->add('-'.(int)$_POST['archive_period'].' day');
		$filter['<=DATE_INSERT'] = $latestDate;
	}

	if (isset($_POST['archive_count_execution']))
	{
		$timeLimit = (int)$_POST['archive_count_execution'];
	}
	unset($filter['PERIOD']);

	if ($saleModulePermissions == 'P')
	{
		$userCompanyList = Sale\Services\Company\Manager::getUserCompanyList($USER->GetID());

		$filter[] = array(
			"LOGIC" => "OR",
			'=RESPONSIBLE_ID' => $USER->GetID(),
			'=COMPANY_ID' => $userCompanyList,
		);
	}

	if (
		($_POST['archive_blocked_order_accept'] !== 'Y' && $saleModulePermissions == "W")
		|| $saleModulePermissions < "W"
	)
	{
		$filter[] = array(
			"LOGIC" => "OR",
			'=LOCKED_BY' => $USER->GetID(),
			array(
				"=DATE_LOCK" => null,
				'=LOCKED_BY' => null,
			)
		);
	}

	$resultArchiving = Sale\Archive\Manager::archiveOrders($filter, 200, $timeLimit);

	$dataResult = $resultArchiving->getData();

	$count = (int)$dataResult['count'];

	if($_POST['countArchived'] <> '')
	{
		$count += (int)$_POST['countArchived'];
	}

	if($resultArchiving->isSuccess() && $dataResult['count'] > 0)
	{
		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>Loc::getMessage("ARCHIVE_IN_PROGRESS"),
			"DETAILS"=>Loc::getMessage("ARCHIVE_TOTAL")." <b>".$count."</b><br>
			<a id=\"continue_href\" onclick=\"ContinueArchive(".$count."); return false;\" href=\"".htmlspecialcharsbx("sale_archive.php?continue=Y&lang=".urlencode(LANGUAGE_ID))."\">".Loc::getMessage("SEARCH_REINDEX_NEXT_STEP")."</a>",
			"HTML"=>true,
			"TYPE"=>"PROGRESS",
		));
		?>
		<script>
			CloseWaitWindow();
			DoNext(<?= $count?>);
			count = <?= $count?>;
		</script>
		<?
	}
	else
	{
		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>Loc::getMessage("ARCHIVE_COMPLETE"),
			"DETAILS"=>Loc::getMessage("ARCHIVE_TOTAL")." <b>".$count."</b>",
			"HTML"=>true,
			"TYPE"=>"OK",
		));
		if (!$resultArchiving->isSuccess())
		{
			$errorList = $resultArchiving->getErrorMessages();
			foreach ($errorList as $error)
			{
				CAdminMessage::ShowMessage(array(
					"MESSAGE"=>$error,
					"TYPE"=>"ERROR",
				));
			}
		}
	}
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}
else
{
	$APPLICATION->SetTitle(Loc::getMessage("ARCHIVE_TITLE"));

	$aTabs = array(
		array("DIV" => "edit1", "TAB" => Loc::getMessage("ARCHIVE_TAB"), "ICON"=>"main_user_edit"),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?>

	<script language="JavaScript">
		var savedNextStep;
		var stop;
		var interval = 0;

		function StartReindex()
		{
			stop = false;
			document.getElementById('archive_result_div').innerHTML = '';
			document.getElementById('stop_button').disabled = false;
			document.getElementById('start_button').disabled = true;
			document.getElementById('continue_button').disabled = true;
			DoNext();
		}

		function DoNext(countArchived)
		{
			var queryString = 'archive=Y'
				+ '&lang=<?echo htmlspecialcharsbx(LANG)?>'
				+ '&<?echo bitrix_sessid_get()?>';

			if(!stop)
			{
				var params = {};
				var inputElements = document.getElementById("archiveForm").elements;
				Array.prototype.forEach.call(inputElements, function(element)
				{
					if (
						element.id == "archive_status_id"
						|| element.id == "archive_site"
					)
					{
						var options = element.getElementsByTagName('option');
						var statusValue = [];
						Array.prototype.forEach.call(options, function(option)
						{
							if (option.selected)
							{
								statusValue.push(option.value);
							}
						});

						if (statusValue.length)
							params[element.name] = statusValue;
					}
					else if (element.id == "archive_blocked_order_accept")
					{
						if (element.checked)
							params[element.name] = element.value;
					}
					else if (element.value)
					{
						params[element.name] = element.value;
					}
				});
				if (countArchived)
				{
					params['countArchived'] = countArchived;
				}
				ShowWaitWindow();
				BX.ajax.post(
					'sale_archive.php?'+queryString,
					params,
					function(result)
					{
						document.getElementById('archive_result_div').innerHTML = result;
						var href = document.getElementById('continue_href');
						if(!href)
						{
							CloseWaitWindow();
							StopArchive();
						}
					}
				);
			}
		}
		function StopArchive()
		{
			stop=true;
			document.getElementById('stop_button').disabled=true;
			document.getElementById('start_button').disabled=false;
			document.getElementById('continue_button').disabled=false;
		}
		function ContinueArchive()
		{
			stop=false;
			document.getElementById('stop_button').disabled=false;
			document.getElementById('start_button').disabled=true;
			document.getElementById('continue_button').disabled=true;
			DoNext(count);
		}
		function EndArchive()
		{
			stop=true;
			document.getElementById('stop_button').disabled=true;
			document.getElementById('start_button').disabled=false;
			document.getElementById('continue_button').disabled=true;
		}
	</script>

	<div id="archive_result_div" style="margin:0">
	</div>
	<form method="GET" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo htmlspecialcharsbx(LANG)?>" id="archiveForm">
		<?
			$tabControl->Begin();
			$tabControl->BeginNextTab();
			$filterValues = Option::get('sale', 'archive_params');

			$filterValues = unserialize($filterValues, ['allowed_classes' => false]);

			$countExecutionOrders = Option::get('sale', 'archive_time_limit', false);
			if(!$countExecutionOrders)
			{
				$countExecutionOrders = 10;
			}
		?>
		<tr>
			<td><label for="archive_count_execution"><?echo Loc::getMessage("ARCHIVE_STEP")?>:</label></td>
			<td><input type="text" name="archive_count_execution" id="archive_count_execution" size="3" value="<?echo $countExecutionOrders;?>"> <?echo Loc::getMessage("ARCHIVE_STEP_ORDER")?></td>
		</tr>
		<tr>
			<td><label for="archive_period"><?=Loc::getMessage("ARCHIVE_PERIOD")?>:</label></td>
			<td><input type="text" name="archive_period" value="<?=(int)$filterValues['PERIOD'] ? (int)$filterValues['PERIOD'] : 365?>" size="5" id="archive_period"></td>
		</tr>
		<?
		if($saleModulePermissions >= "W")
		{
			?>
			<tr>
				<td valign="top"><label for="archive_blocked_order_accept"><?echo Loc::getMessage("ARCHIVE_BLOCKED_ORDER_ACCEPT")?>:</label></td>
				<td>
					<input type="checkbox" name="archive_blocked_order_accept" id="archive_blocked_order_accept" value="Y" <?if(Option::get("sale", "archive_blocked_order") === "Y") echo "checked"?>>
				</td>
			</tr>
			<?
			$shopList = array();
			$siteList = \Bitrix\Main\SiteTable::getList();
			while ($site = $siteList->fetch())
			{
				$shop = Option::get("sale", "SHOP_SITE_".$site["LID"], "");
				if ($shop == $site['LID'])
				{
					$shopList[$site['LID']] = $site['NAME']."[".$site['LID']."]";
				}
			}
			if (count($shopList) > 1)
			{
				?>
				<tr valign="top">
					<td><label for="archive_site"><?=Loc::getMessage("ARCHIVE_SITE")?>:</label></td>
					<td>
						<select name="archive_site[]" id="archive_site" multiple size="<?=(count($shopList) < 5) ? count($shopList) : 5?>">
							<?
							foreach($shopList as $id => $site)
							{
								?>
								<option
									value="<?= htmlspecialcharsbx($id) ?>"
									<?
										if (
											(is_array($filterValues['LID'])	&& in_array($id, $filterValues['LID']))
											|| empty($filterValues['LID'])
										)
											echo " selected"
									?>
								>
									<?=htmlspecialcharsbx($site)?>
								</option>
								<?
							}
							?>
						</select>
					</td>
				</tr>
				<?
			}
		}		
		?>
		<tr>
			<td valign="top"><label for="archive_status_id"><?echo Loc::getMessage("ARCHIVE_STATUS")?>:</label></td>
			<td>
				<select name="archive_status_id[]" id="archive_status_id" multiple size="3">
					<?
						$allStatusNames = \Bitrix\Sale\OrderStatus::getAllStatusesNames();

						foreach($statusesList as  $statusCode)
						{
							if (!$statusName = $allStatusNames[$statusCode])
								continue;
							?>
							<option
								value="<?= htmlspecialcharsbx($statusCode) ?>"
								<?
									if (
										(is_array($filterValues['STATUS_ID']) && in_array($statusCode, $filterValues['STATUS_ID']))
										|| empty($filterValues['STATUS_ID'])
									)
										echo " selected"
								?>
							>
								[<?= htmlspecialcharsbx($statusCode) ?>] <?= htmlspecialcharsbx($statusName) ?>
							</option>
							<?
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="ORDER_ARCHIVE_PAYED"><?echo Loc::getMessage("ARCHIVE_PAYED")?>:</label>
			</td>
			<td>
				<select name="archive_payed" id="ORDER_ARCHIVE_PAYED">
					<option value="" selected><?echo Loc::getMessage("ARCHIVE_ALL")?></option>
					<option value="Y"<?if($filterValues['=PAYED'] == "Y") echo " selected"?>><?echo Loc::getMessage("ARCHIVE_YES")?></option>
					<option value="N"<?if($filterValues['=PAYED'] == 'N') echo " selected"?>><?echo Loc::getMessage("ARCHIVE_NO")?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="ORDER_ARCHIVE_CANCELED"><?echo Loc::getMessage("ARCHIVE_CANCELED")?>:</label>
			</td>
			<td>
				<select name="archive_canceled" id="ORDER_ARCHIVE_CANCELED">
					<option value="" selected><?echo Loc::getMessage("ARCHIVE_ALL")?></option>
					<option value="Y"<?if($filterValues['=CANCELED'] == "Y") echo " selected"?>><?echo Loc::getMessage("ARCHIVE_YES")?></option>
					<option value="N"<?if($filterValues['=CANCELED'] == 'N') echo " selected"?>><?echo Loc::getMessage("ARCHIVE_NO")?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="ORDER_ARCHIVE_DEDUCTED"><?echo Loc::getMessage("ARCHIVE_DEDUCTED")?>:</label>
			</td>
			<td>
				<select name="archive_deducted" id="ORDER_ARCHIVE_DEDUCTED">
					<option value="" selected><?echo Loc::getMessage("ARCHIVE_ALL")?></option>
					<option value="Y"<?if($filterValues['=DEDUCTED'] == "Y") echo " selected"?>><?echo Loc::getMessage("ARCHIVE_YES")?></option>
					<option value="N"<?if($filterValues['=DEDUCTED'] == 'N') echo " selected"?>><?echo Loc::getMessage("ARCHIVE_NO")?></option>
				</select>
			</td>
		</tr>
		<?
			$tabControl->Buttons();
		?>
		<input type="button" id="start_button" value="<?echo Loc::getMessage("ARCHIVE_ARCHIVE_BUTTON")?>" OnClick="StartReindex();" class="adm-btn-save">
		<input type="button" id="stop_button" value="<?=Loc::getMessage("ARCHIVE_STOP")?>" OnClick="StopArchive();" disabled>
		<input type="button" id="continue_button" value="<?=Loc::getMessage("ARCHIVE_CONTINUE")?>" OnClick="ContinueArchive();" disabled>
		<?
			$tabControl->End();
		?>
	</form>
<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}