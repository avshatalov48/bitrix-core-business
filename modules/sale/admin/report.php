<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

global $DB;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions <= "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
if(!CBXFeatures::IsFeatureEnabled('SaleReports'))
{
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

// <editor-fold defaultstate="collapsed" desc="--------- Server processing ---------">
ClearVars();
$errorMessage = '';
$errAdmMessage = null;

// Using report module
if (!CModule::IncludeModule('report'))
{
	$errorMessage .= GetMessage("REPORT_MODULE_NOT_INSTALLED").'<br>';
}

// Using catalog module
if (!CModule::IncludeModule('catalog'))
{
	$errorMessage .= GetMessage("CATALOG_MODULE_NOT_INSTALLED").'<br>';
}

// Using iblock module
if (!CModule::IncludeModule('iblock'))
{
	$errorMessage .= GetMessage("IBLOCK_MODULE_NOT_INSTALLED").'<br>';
}

if (!$errorMessage)
{

	CBaseSaleReportHelper::init();

	$arParams = array(
		'PATH_TO_REPORT_LIST' => '/bitrix/admin/sale_report.php?lang='.LANG
	);

	// <editor-fold defaultstate="collapsed" desc="Creating or updating base reports">
	//$ownerId = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getOwnerId'));

	// auto create fresh default reports only if some reports alredy exist
	$optionSaleReportsVersion = '~U_'.SALE_REPORT_OWNER_ID.'_REPORTS';
	$userReportVersion = CUserOptions::GetOption('sale', $optionSaleReportsVersion, CBaseSaleReportHelper::getFirstVersion());

	$saleReportVersion = CBaseSaleReportHelper::getCurrentVersion();

	if ($saleReportVersion !== $userReportVersion  && CheckVersion($saleReportVersion, $userReportVersion))
	{
		$nReps = 0;
		foreach (CBaseSaleReportHelper::getOwners() as $ownerId) $nReps += CReport::GetCountInt($ownerId);
		if ($nReps > 0)
		{
			$dReports = CBaseSaleReportHelper::getDefaultReports();

			foreach ($dReports as  $moduleVer => $vReports)
			{
				if ($moduleVer !== $userReportVersion && CheckVersion($moduleVer, $userReportVersion))
				{
					// add fresh vReports
					//CReport::addFreshDefaultReports($vReports, $ownerId);
					foreach ($vReports as &$dReport)
					{
						$dReport['settings']['mark_default'] = $dReport['mark_default'];
						$dReport['settings']['title'] = $dReport['title'];
						$dReport['settings']['description'] = $dReport['description'];
						$dReport['settings']['owner'] = $dReport['owner'];

						CReport::Add($dReport['settings']);
					}
					unset($dReport);
				}
			}
		}
		unset($nReps);

		CUserOptions::SetOption('sale', $optionSaleReportsVersion, $saleReportVersion);
	}


	// create default reports by user request
	if (!empty($_POST['CREATE_DEFAULT']))
	{
		$dReports = CBaseSaleReportHelper::getDefaultReports();

		foreach ($dReports as $moduleVer => $vReports)
		{
			//CReport::addFreshDefaultReports($vReports, $ownerId);
			foreach ($vReports as &$dReport)
			{
				$dReport['settings']['mark_default'] = $dReport['mark_default'];
				$dReport['settings']['title'] = $dReport['title'];
				$dReport['settings']['description'] = $dReport['description'];
				$dReport['settings']['owner'] = $dReport['owner'];

				CReport::Add($dReport['settings']);
			}
			unset($dReport);
		}

		LocalRedirect($arParams['PATH_TO_REPORT_LIST']);
	}
	// </editor-fold>

	$needDisplayUpdate14_5_2message = false;
	if(CUserOptions::GetOption('report', 'NEED_DISPLAY_UPDATE_14_5_2_MESSAGE', 'Y') === 'Y')
	{
		$needDisplayUpdate14_5_2message = true;
		CUserOptions::SetOption('report', 'NEED_DISPLAY_UPDATE_14_5_2_MESSAGE', 'N');
	}


	// Preparing reports list.
	$sTableID = 'tbl_sale_report';

	$lReports = new CAdminList($sTableID);

	if (($arID = $lReports->GroupAction()) && $saleModulePermissions >= 'W')
	{
		if (
			isset($_REQUEST['action_target'])
			&& (
				$_REQUEST['action_target'] === 'on'
				|| $_REQUEST['action_target'] === 'selected'
			)
		)
		{
			$arID = array();
			// Getting reports list.
			$res = Bitrix\Report\ReportTable::getList(array(
				'select' => array('ID'),
				'filter' => array('=CREATED_BY' => $USER->GetID(), '=OWNER_ID' => CBaseSaleReportHelper::getOwners())
			));
			while ($row = $res->fetch())
				$arID[] = $row['ID'];
			unset($res);
			unset($row);
		}

		foreach ($arID as $ID)
		{
			if ($ID == '') continue;
			switch ($_REQUEST['action'])
			{
				case "delete":
					@set_time_limit(0);
					$DB->StartTransaction();
					if (!CReport::Delete($ID))
					{
						$DB->Rollback();
						if ($ex = $APPLICATION->GetException())
							$lReports->AddGroupError($ex->GetString(), $ID);
						else
							$lReports->AddGroupError(GetMessage("SALE_REPORT_ERROR_DELREPFROMLIST"), $ID);
					}
					else
					{
						$DB->Commit();
					}
					break;
			}
		}
	}

	// Getting reports list.
	$dbRepList = Bitrix\Report\ReportTable::getList(array(
		'select' => array('ID', 'TITLE', 'DESCRIPTION', 'CREATED_DATE', 'MARK_DEFAULT'),
		'filter' => array('=CREATED_BY' => $USER->GetID(), '=OWNER_ID' => CBaseSaleReportHelper::getOwners())
	));

	$dbRepList = new CAdminResult($dbRepList, $sTableID);
	$dbRepList->NavStart();
	$lReports->NavText($dbRepList->GetNavPrint(GetMessage("SALE_REPORT_TITLE")));

	$lReports->AddHeaders(
		array(
			array(
				'id'  =>'TITLE',
				'content'  =>'<b>'.GetMessage('SALE_REPORT_LIST_TITLE').'</b>',
				'sort'     =>'TITLE',
				'default'  =>true
			),
			array(
				'id'    =>'CREATED_DATE',
				'content'  =>'<b>'.GetMessage('SALE_REPORT_LIST_CREATED_DATE').'</b>',
				'sort'     =>'CREATED_DATE',
				'default'  =>true
			)
		)
	);

	// $arRes containing array as: array( 'ID' =>..., 'TITLE'=>..., 'DESCRIPTION'=>..., 'CREATED_DATE'=>... ).
	$nReports = 0;
	while ($arRes = $dbRepList->NavNext(false))
	{
		$lRow = $lReports->AddRow($arRes['ID'], $arRes);
		$lRow->AddViewField('TITLE', "<a href='" . "sale_report_view.php?lang=" . LANG . '&ID=' . $arRes['ID'] . "' title='" . CUtil::addslashes(htmlspecialcharsEx($arRes['DESCRIPTION'])) . "'>" . CUtil::addslashes(htmlspecialcharsEx($arRes['TITLE'])) . "</a>");
		$createdDateStr = ($arRes['CREATED_DATE'] instanceof \Bitrix\Main\Type\DateTime || $arRes['CREATED_DATE'] instanceof \Bitrix\Main\Type\Date) ? ConvertTimeStamp($arRes['CREATED_DATE']->getTimestamp(), 'SHORT') : '';
		$lRow->AddViewField('CREATED_DATE', $createdDateStr);

		$markNum = 0;
		if (isset($arRes['MARK_DEFAULT']))
			$markNum = intval($arRes['MARK_DEFAULT']);

		// <editor-fold defaultstate="collapsed" desc="Context menu of rows of AdminList.">
		$arRowActions = array();
		if ($saleModulePermissions >= 'D')
		{
			$arRowActions[] = array(
				"ICON"=>"view",
				"TEXT"=>GetMessage('SALE_REPORT_LIST_ROW_ACTIONS_VIEW_TEXT'),
				"ACTION"=>$lReports->ActionRedirect("sale_report_view.php?lang=" . LANGUAGE_ID . "&ID=".$arRes['ID']),
				"DEFAULT"=>true
			);
		}
		if ($saleModulePermissions >= 'W')
		{
			$arRowActions[] = array(
				"ICON"=>"copy",
				"TEXT"=>GetMessage('SALE_REPORT_LIST_ROW_ACTIONS_COPY_TEXT'),
				"ACTION"=>$lReports->ActionRedirect("sale_report_construct.php?copyID=".$arRes['ID']."&lang=".LANG),
				//"DEFAULT"=>true
			);
			if ($markNum === 0)
			{
				$arRowActions[] = array(
					"ICON"=>"edit",
					"TEXT"=>GetMessage('SALE_REPORT_LIST_ROW_ACTIONS_EDIT_TEXT'),
					"ACTION"=>$lReports->ActionRedirect("sale_report_construct.php?ID=".$arRes['ID']."&lang=".LANG),
					//"DEFAULT"=>true
				);
			}
			$arRowActions[] = array(
				"ICON"=>"delete",
				"TEXT"=>GetMessage('SALE_REPORT_LIST_ROW_ACTIONS_DELETE_TEXT'),
				"ACTION"=>"if(confirm('".GetMessage("REPORT_DELETE_CONFIRM")."')) ".$lReports->ActionDoGroup($arRes['ID'], "delete")
			);
		}
		// </editor-fold>
		$lRow->AddActions($arRowActions);
		$nReports++;
	}

	// Group actions
	$lReports->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")/*,
			"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
			"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE")*/
		)
	);

	// Menu of actions.
	$arContextMenu = array(
		array(
			"TEXT" => GetMessage("SALE_REPORT_LIST_ADD_REPORT"),
			"LINK" => "sale_report_construct.php?lang=" . LANG,
			"TITLE" => GetMessage("SALE_REPORT_LIST_ADD_REPORT_TITLE"),
			"ICON" => "btn_new"
		)
	);
	// Attach "Menu of actions".
	$lReports->AddAdminContextMenu($arContextMenu);

	// Adding summary row.
	$lReports->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$dbRepList->SelectedRowsCount()), // quatity of elements
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // counter of selected elements
		)
	);

	// Processing options or export to Excel.
	$lReports->CheckListMode();
}
	// </editor-fold>



// Page header
$APPLICATION->SetTitle(GetMessage("SALE_REPORT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");



	// <editor-fold defaultstate="collapsed" desc="--------- Page output ---------">
if( $errorMessage )
{
	$errAdmMessage = new CAdminMessage(
		array(
			"DETAILS"=>$errorMessage,
			"TYPE"=>"ERROR",
			"MESSAGE"=>GetMessage('SALE_REPORT_ERROR_GETREPORTLIST'),
			"HTML"=>true
		)
	);
	echo $errAdmMessage->Show();
}
else
{
	?>
	<?php if ($nReports == 0): ?>

		<?
		$arMessageNoReps = new CAdminMessage(array('MESSAGE' => GetMessage('SALE_REPORT_EMPTY_LIST'), 'TYPE' => 'OK'));
		echo $arMessageNoReps->Show();
		?>

		<form action="" method="POST">
			<input type="hidden" name="CREATE_DEFAULT" value="1" />
			<input class="adm-btn-save" type="submit" value="<?=GetMessage('SALE_REPORT_CREATE_DEFAULT')?>" />
		</form>

	<?php else: ?>
	<?php
		if ($needDisplayUpdate14_5_2message)
		{
			$admMessage = new CAdminMessage(
				array(
					"TYPE"=>"OK",
					"MESSAGE"=>GetMessage('REPORT_UPDATE_14_5_2_MESSAGE')
				)
			);
			echo $admMessage->Show();
			unset($admMessage);
		}
	?>
	<?php $lReports->DisplayList(); ?>
	<?php endif; ?>
<?php
}
	// </editor-fold>



require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>