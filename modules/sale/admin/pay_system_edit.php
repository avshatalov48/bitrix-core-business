<?php
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Main\Page\Asset;
use Bitrix\Sale\Helpers\Admin\BusinessValueControl;
use Bitrix\Sale\Services\PaySystem\Restrictions;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\BusinessValue;
use Bitrix\Main\SystemException;
use Bitrix\Main\IO;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/helpers/admin/businessvalue.php');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/cashbox/inputs/file.php");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."sale_pay_system.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loc::loadMessages(__FILE__);

$lheStyle = '
<style type="text/css">
	.bxlhe_frame_hndl_dscr {
		-moz-border-bottom-colors: none;
		-moz-border-left-colors: none;
		-moz-border-right-colors: none;
		-moz-border-top-colors: none;
		background: none repeat scroll 0 0 #FFFFFF;
		border-color: #87919C #959EA9 #9EA7B1;
		border-image: none;
		border-radius: 4px 4px 4px 4px;
		border-style: solid;
		border-width: 1px;
		box-shadow: 0 1px 0 0 rgba(255, 255, 255, 0.3), 0 2px 2px -1px rgba(180, 188, 191, 0.7) inset;
		color: #000000;
		display: inline-block;
		outline: medium none;
		vertical-align: middle;
		!important;
	}
	.bx-button-add-template {
		border-bottom: 1px dashed #2067B0;
	    text-decoration: none;
		color: #2067B0;
		cursor: pointer;
	}
</style>';

Asset::getInstance()->addString($lheStyle);
Asset::getInstance()->addJs("/bitrix/js/sale/pay_system.js");

\Bitrix\Sale\Delivery\Restrictions\Manager::getClassesList();

$instance = Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();
$server = $context->getServer();
$documentRoot = Application::getDocumentRoot();
$paySystem = array();

$psDescription = '';
$description = '';

$id = (int)$request->get('ID');

if (CModule::IncludeModule("fileman"))
	$bFilemanModuleInst = true;

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("SPSN_TAB_PAYSYS"),
		"ICON" => "sale",
		"TITLE" => GetMessage("SPSN_TAB_PAYSYS_DESCR"),
	)
);

if ($id > 0 && $request->getRequestMethod() !== 'POST')
{
	$aTabs[] = array(
		"DIV" => "edit2",
		"TAB" => GetMessage("SPS_PAY_SYSTEM_RESTRICTION"),
		"ICON" => "sale",
		"TITLE" => GetMessage("SPS_PAY_SYSTEM_RESTRICTION_DESC"),
	);
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);


$errorMessage = '';
$businessValueControl = new BusinessValueControl('PAYSYSTEM');

if ($server->getRequestMethod() == "POST"
	&& ($request->get('save') !== null || $request->get('apply') !== null)
	&& $saleModulePermissions == "W"
	&& check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent($request);

	$isNewSystem = ($id <= 0);

	$name = trim($request->get('NAME'));
	if ($name == '')
		$errorMessage .= Loc::getMessage("ERROR_NO_NAME")."<br>";

	if ($request->get('ACTION_FILE') == '')
		$errorMessage .= Loc::getMessage("ERROR_NO_ACTION_FILE")."<br>";

	$sort = (int)$request->get('SORT');

	if ($sort <= 0)
		$sort = 100;

	$actionFile = $request->get('ACTION_FILE');
	if (!$actionFile)
	{
		$errorMessage = Loc::getMessage('SALE_PSE_ERROR_ACTION_SAVE');
	}

	// temp crutch because of CSalePdf does not support all images
	if (mb_strpos($actionFile, 'bill') === 0)
	{
		$consumer = $isNewSystem ? 'PAYSYSTEM_NEW' : 'PAYSYSTEM_'.$id;

		$fileNameList = [];
		if (isset($_FILES['PAYSYSTEMBizVal']['name']['MAP'][$consumer])
			&& is_array($_FILES['PAYSYSTEMBizVal']['name']['MAP'][$consumer])
		)
		{
			$fileNameList = array_keys($_FILES['PAYSYSTEMBizVal']['name']['MAP'][$consumer]);
		}

		$fileFields = [];
		if (isset($_FILES['PAYSYSTEMBizVal'])
			&& is_array($_FILES['PAYSYSTEMBizVal']))
		{
			$fileFields = array_keys($_FILES['PAYSYSTEMBizVal']);
		}

		foreach ($fileNameList as $fileName)
		{
			$file = array();
			foreach ($fileFields as $key)
			{
				$file[$key] = $_FILES['PAYSYSTEMBizVal'][$key]['MAP'][$consumer][$fileName][0]['PROVIDER_VALUE'];
			}

			if (!CFile::IsImage($file['name']))
			{
				continue;
			}

			$description = PaySystem\Manager::getHandlerDescription($actionFile);

			$result = CSalePdf::CheckImage($file);
			if ($result)
			{
				$errorMessage .= Loc::getMessage(
						'SALE_PSE_ERROR_IMAGE_ERROR',
						array('#SETTING_NAME#' => $description['CODES'][$fileName]['NAME'])
				);
			}
		}
	}

	if ($actionFile === 'orderdocument'
		&& !$request->get('PS_MODE')
	)
	{
		$errorMessage .= Loc::getMessage('SALE_PSE_ERROR_DOCUMENT_TEMPLATE_EMPTY');
	}

	if ($errorMessage === '')
	{
		$fields = array(
			"NAME" => $name,
			"PSA_NAME" => $request->get('PSA_NAME'),
			"ACTIVE" => ($request->get('ACTIVE') != 'Y') ? 'N' : $request->get('ACTIVE'),
			"CAN_PRINT_CHECK" => ($request->get('CAN_PRINT_CHECK') != 'Y') ? 'N' : $request->get('CAN_PRINT_CHECK'),
			"CODE" => $request->get('CODE'),
			"NEW_WINDOW" => ($request->get('NEW_WINDOW') != 'Y') ? 'N' : $request->get('NEW_WINDOW'),
			"ALLOW_EDIT_PAYMENT" => ($request->get('ALLOW_EDIT_PAYMENT') != 'Y') ? 'N' : $request->get('ALLOW_EDIT_PAYMENT'),
			"IS_CASH" => (!in_array($request->get('IS_CASH'), array('Y', 'A'))) ? 'N' : $request->get('IS_CASH'),
			"ENTITY_REGISTRY_TYPE" => \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER,
			"SORT" => $sort,
			"ENCODING" => $request->get('ENCODING'),
			"DESCRIPTION" => $request->get('DESCRIPTION'),
			"ACTION_FILE" => $actionFile,
			'PS_MODE' => ($request->get('PS_MODE')) ? $request->get('PS_MODE') : '',
			'XML_ID' => ($request->get('XML_ID')) ?: PaySystem\Manager::generateXmlId()
		);

		if ($request->get('AUTO_CHANGE_1C') == 'Y')
			$fields['AUTO_CHANGE_1C'] = 'Y';
		else
			$fields['AUTO_CHANGE_1C'] = 'N';

		$path = PaySystem\Manager::getPathToHandlerFolder($actionFile);
		if (\Bitrix\Main\IO\File::isFileExists($documentRoot.$path.'/handler.php'))
		{
			list($className) = PaySystem\Manager::includeHandler($actionFile);

			$fields['HAVE_PAYMENT'] = 'Y';

			if (is_subclass_of($className, '\Bitrix\Sale\PaySystem\IPrePayable'))
				$fields['HAVE_PREPAY'] = 'Y';

			if (is_subclass_of($className, '\Bitrix\Sale\PaySystem\ServiceHandler'))
				$fields['HAVE_RESULT_RECEIVE'] = 'Y';

			if (is_subclass_of($className, '\Bitrix\Sale\PaySystem\IPayable'))
				$fields['HAVE_PRICE'] = 'Y';

			if (is_subclass_of($className, '\Bitrix\Sale\PaySystem\ICheckable'))
				$fields['HAVE_RESULT'] = 'Y';
		}
		else
		{
			if (\Bitrix\Main\IO\File::isFileExists($documentRoot.$actionFile."/pre_payment.php"))
				$fields["HAVE_PREPAY"] = "Y";
			if (\Bitrix\Main\IO\File::isFileExists($documentRoot.$actionFile."/result.php"))
				$fields["HAVE_RESULT"] = "Y";
			if (\Bitrix\Main\IO\File::isFileExists($documentRoot.$actionFile."/action.php"))
				$fields["HAVE_ACTION"] = "Y";
			if (\Bitrix\Main\IO\File::isFileExists($documentRoot.$actionFile."/payment.php"))
				$fields["HAVE_PAYMENT"] = "Y";
			if (\Bitrix\Main\IO\File::isFileExists($documentRoot.$actionFile."/result_rec.php"))
				$fields["HAVE_RESULT_RECEIVE"] = "Y";
		}

		if($request->get('TARIF') !== null)
		{
			if ($path !== null)
			{
				if (\Bitrix\Main\IO\File::isFileExists($documentRoot.$path.'/handler.php'))
				{
					list($className) = PaySystem\Manager::includeHandler($actionFile);
					$fields["TARIF"] = $className::prepareToField($request->get('TARIF'));
				}
			}
			else
			{
				$fields["TARIF"] = CSalePaySystemsHelper::prepareTarifForSaving($actionFile, $request->get('TARIF'));
			}
		}

		$isConsumerChange = $request->get('ACTION_FILE') != $request->get('PRIOR_ACTION_FILE');
		if (!$isConsumerChange)
		{
			$isConsumerChange = $request->get('PS_MODE') != $request->get('PRIOR_PS_MODE');
		}

		$file = $request->getFile('LOGOTIP');

		if ($file !== null && $file["error"] == 0)
		{
			$imageFileError = CFile::CheckImageFile($file);

			if ($imageFileError === null)
			{
				$fields['LOGOTIP'] = $file;
				$fields['LOGOTIP']['del'] = trim($request->get("LOGOTIP_del"));
				$fields['LOGOTIP']['MODULE_ID'] = "sale";
				CFile::SaveForDB($fields, 'LOGOTIP', 'sale/paysystem/logotip');
			}
			else
			{
				$errorMessage .= $imageFileError.'.<br>';
			}
		}
		elseif ($request->get("LOGOTIP_del") !== null && $request->get("LOGOTIP_del") == 'Y')
		{
			$fields['LOGOTIP'] = 0;
		}
		elseif ($id <= 0)
		{
			$psMode = $request->get('PS_MODE');
			$handler = $request->get('ACTION_FILE');

			if ($psMode)
			{
				$image = '/bitrix/images/sale/sale_payments/'.$handler.'/'.$psMode.'.png';
				if (IO\File::isFileExists($documentRoot.$image))
				{
					$fields['LOGOTIP'] = CFile::MakeFileArray($image);
					$fields['LOGOTIP']['MODULE_ID'] = "sale";
					CFile::SaveForDB($fields, 'LOGOTIP', 'sale/paysystem/logotip');
				}
			}

			if (!isset($fields['LOGOTIP']))
			{
				$image = '/bitrix/images/sale/sale_payments/'.$handler.'.png';
				if (IO\File::isFileExists($documentRoot.$image))
				{
					$fields['LOGOTIP'] = CFile::MakeFileArray($image);
					$fields['LOGOTIP']['MODULE_ID'] = "sale";
					CFile::SaveForDB($fields, 'LOGOTIP', 'sale/paysystem/logotip');
				}
			}
		}

		$data = PaySystem\Manager::getHandlerDescription($request->get('ACTION_FILE'), $request->get('PS_MODE'));

		if ($id > 0)
		{
			$result = PaySystemActionTable::update($id, $fields);

			if (!$result->isSuccess())
				$errorMessage .= join(',', $result->getErrorMessages()).".<br>";
		}
		else
		{
			$result = PaySystemActionTable::add($fields);
			if (!$result->isSuccess())
			{
				$errorMessage .= join(',', $result->getErrorMessages());
			}
			else
			{
				$id = $result->getId();
				if ($id > 0)
				{
					$fields = array(
						'PARAMS' => serialize(array('BX_PAY_SYSTEM_ID' => $id)),
						'PAY_SYSTEM_ID' => $id
					);

					$result = PaySystemActionTable::update($id, $fields);
					if (!$result->isSuccess())
						$errorMessage .= join(',', $result->getErrorMessages());

					$service = PaySystem\Manager::getObjectById($id);
					$currency = $service->getCurrency();
					if ($currency)
					{
						$params = array(
							'SERVICE_ID' => $id,
							'SERVICE_TYPE' => Restrictions\Manager::SERVICE_TYPE_PAYMENT,
							'PARAMS' => array('CURRENCY' => $currency)
						);
						Restrictions\Manager::getClassesList();
						$saveResult = \Bitrix\Sale\Services\PaySystem\Restrictions\Currency::save($params);
						if (!$saveResult->isSuccess())
							$errorMessage .= Loc::getMessage('SALE_PSE_ERROR_RSRT_CURRENCY_SAVE');
					}
				}
			}
		}

		if ($errorMessage === '')
		{
			if ($isConsumerChange)
			{
				$priorActionFile = $request->get('PRIOR_ACTION_FILE');
				if ($isNewSystem)
					BusinessValue::addConsumer('PAYSYSTEM_NEW', $data);
				else
					BusinessValue::changeConsumer('PAYSYSTEM_'.$id, $data);
			}

			if ($businessValueControl->setMapFromPost())
			{
				if ($isConsumerChange && $isNewSystem)
					$businessValueControl->changeConsumerKey('PAYSYSTEM_NEW', 'PAYSYSTEM_'.$id);

				if (!$businessValueControl->saveMap())
					$errorMessage .= Loc::getMessage('SALE_PSE_ERROR_SET_BIZVAL_MAP');
			}
			else
			{
				$errorMessage .= Loc::getMessage('SALE_PSE_ERROR_SET_BIZVAL_MAP');
			}
		}
	}

	if ($errorMessage === '')
	{
		if ($adminSidePanelHelper->isAjaxRequest())
		{
			if ($request->get('apply') <> '')
			{
				$adminSidePanelHelper->sendSuccessResponse("apply", array("ID" => $id, "reloadUrl" =>
					$selfFolderUrl."sale_pay_system_edit.php?ID=".$id."&lang=".$context->getLanguage().
					"&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER&".$tabControl->ActiveTabParam()));
			}
			else
			{
				$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $id));
			}
		}
		else
		{
			if ($request->get('apply') <> '')
			{
				$applyUrl = $selfFolderUrl."sale_pay_system_edit.php?lang=".$context->getLanguage()."&ID=".$id."&".$tabControl->ActiveTabParam();
				$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
				LocalRedirect($applyUrl);
			}
			else
			{
				$adminSidePanelHelper->localRedirect($listUrl);
				LocalRedirect($listUrl);
			}
		}
	}
	else
	{
		$adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
	}
}

$paySystem = array();
if ($id > 0)
{
	$dbRes = \Bitrix\Sale\PaySystem\Manager::getList(
		array(
			'filter' => array('ID' => $id),
			'order' => array("SORT" => "ASC")
		)
	);

	$paySystem = $dbRes->fetch();
}

require_once($documentRoot."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(($id > 0) ? Loc::getMessage("SALE_EDIT_RECORD", array("#ID#" => $id)) : Loc::getMessage("SALE_NEW_RECORD"));

$restrictionsHtml = '';

if ($id > 0 && $request->getRequestMethod() !== 'POST')
{
	ob_start();
	require_once($documentRoot."/bitrix/modules/sale/admin/pay_system_restrictions_list.php");
	$restrictionsHtml = ob_get_contents();
	ob_end_clean();
}

require($documentRoot."/bitrix/modules/main/include/prolog_admin_after.php");

?>

<?
$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("SPSN_2FLIST"),
		"LINK" => $listUrl,
		"ICON" => "btn_list"
	)
);

if ($id > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$addUrl = $selfFolderUrl."sale_pay_system_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SPSN_NEW_PAYSYS"),
		"LINK" => $addUrl,
		"ICON" => "btn_new"
	);
	$deleteUrl = "".$selfFolderUrl."sale_pay_system.php?action=delete&ID[]=".$id."&lang=".$context->getLanguage()."&".bitrix_sessid_get()."#tb";
	$buttonAction = "LINK";
	if ($adminSidePanelHelper->isPublicFrame())
	{
		$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
		$buttonAction = "ONCLICK";
	}
	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SPSN_DELETE_PAYSYS"),
		$buttonAction => "javascript:if(confirm('".Loc::getMessage("SPSN_DELETE_PAYSYS_CONFIRM")."')) top.window.location.href='".$deleteUrl."';",
		"WARNING" => "Y",
		"ICON" => "btn_delete"
	);
}
$contextMenu = new CAdminContextMenu($aMenu);
$contextMenu->Show();
?>

<?if ($errorMessage !== '')
	CAdminMessage::ShowMessage(array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>Loc::getMessage("SPSN_ERROR"), "HTML"=>true));?>

<script language="JavaScript">
function setLHEClass(lheDivId)
{
	BX.ready(
		function(){
			var lheDivObj = BX(lheDivId);

			if(lheDivObj)
				BX.addClass(lheDivObj, 'bxlhe_frame_hndl_dscr');
	});
}
</script>
<?
$actionUrl = $APPLICATION->GetCurPage();
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
?>
<form method="POST" action="<?=$actionUrl?>" name="pay_sys_form" enctype="multipart/form-data">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?=$context->getLanguage();?>">
<input type="hidden" name="ID" value="<?=$id;?>" id="ID">
<?=bitrix_sessid_post();?>

<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<?if ($id>0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$id;?></td>
		</tr>
	<?endif;?>
	<tr class="adm-detail-required-field">
		<td width="40%" valign="top"><?=Loc::getMessage("SPS_ACT_FILE");?>:</td>
		<td width="60%" valign="top">
			<select name="ACTION_FILE" id="ACTION_FILE" onchange='BX.Sale.PaySystem.getHandlerOptions(this)'>
				<?
					$handlerList = Bitrix\Sale\PaySystem\Manager::getHandlerList();
					if (isset($handlerList['SYSTEM']['invoicedocument']))
					{
						unset($handlerList['SYSTEM']['invoicedocument']);
					}

					$mainHandlers = [
						'yandexcheckout' => 'yandexcheckout',
						'orderdocument' => 'orderdocument',
						'roboxchange' => 'roboxchange',
						'paypal' => 'paypal',
						'cash' => 'cash',
					];
					natsort($handlerList['SYSTEM']);
					natsort($handlerList['USER']);

					if ($request->get('ACTION_FILE') !== null)
						$handlerName = $request->get('ACTION_FILE');
					else
						$handlerName = $paySystem['ACTION_FILE'];

					$pathToDesc = '';
					$pathToHandler = PaySystem\Manager::getPathToHandlerFolder($handlerName);
					if (\Bitrix\Main\IO\File::isFileExists($documentRoot.$pathToHandler.'/.description.php'))
						$pathToDesc = $documentRoot.$pathToHandler.'/.description.php';

					if ($pathToDesc !== '')
					{
						include $pathToDesc;
					}

					$selected = false;
				?>
				<option value=""><?=Loc::getMessage("SPS_NO_ACT_FILE") ?></option>
				<?
				foreach($handlerList['USER'] as $handler => $title)
				{
					// for B24
					if (mb_strpos($handler, 'quote_') !== false)
					{
						unset($handlerList['USER'][$handler]);
					}
				}
				?>
				<?if ($handlerList['USER']):?>
					<optgroup label="<?=Loc::getMessage("SPS_ACT_USER");?>">
						<?foreach($handlerList['USER'] as $handler => $title): ?>
							<?
								if (ToLower($handlerName) == ToLower($handler))
									$selected = true;
							?>
							<option value="<?=htmlspecialcharsbx($handler) ?>"<?=(ToLower($handlerName) == ToLower($handler)) ? " selected" : '';?>>
								<?=htmlspecialcharsbx($title);?>
							</option>
						<?endforeach;?>
					</optgroup>
				<?endif;?>
				<optgroup label="<?=Loc::getMessage("SPS_ACT_SYSTEM");?>">
					<?
					foreach($mainHandlers as $handler):?>
						<?if (isset($handlerList['SYSTEM'][$handler])):?>
							<option value="<?=htmlspecialcharsbx($handler) ?>"<?=((!$selected && ToLower($handlerName) == ToLower($handler)) ? " selected" : '');?>>
								<?=htmlspecialcharsEx($handlerList['SYSTEM'][$handler]) ?>
							</option>
							<?unset($handlerList['SYSTEM'][$handler])?>
						<?endif;?>
					<?endforeach;?>

					<?$innerId = PaySystem\Manager::getInnerPaySystemId();
					foreach($handlerList['SYSTEM'] as $handler => $title):?>
						<?
							if ((
									$innerId > 0
									&& $handler == 'inner'
									&& $handlerName != 'inner'
								)
								|| (
									IsModuleInstalled('documentgenerator')
									&& mb_strpos($handler, 'bill') === 0
									&& ToLower($handlerName) !== ToLower($handler)
								)
							)
							{
								continue;
							}
						?>
						<option value="<?=htmlspecialcharsbx($handler) ?>"<?=((!$selected && ToLower($handlerName) == ToLower($handler)) ? " selected" : '');?>>
							<?=htmlspecialcharsEx($title) ?>
						</option>
					<?endforeach;?>
				</optgroup>
			</select>
			<input type="hidden" value="<?=htmlspecialcharsbx($paySystem['ACTION_FILE'])?>" name="PRIOR_ACTION_FILE">
			<input type="hidden" value="<?=htmlspecialcharsbx($paySystem['PS_MODE'])?>" name="PRIOR_PS_MODE">
		</td>
	</tr>
	<tbody id="pay_system_ps_mode">
	<?
		$psMode = ($request->get('PS_MODE') !== null) ? $request->get('PS_MODE') : $paySystem['PS_MODE'];

		/** @var PaySystem\BaseServiceHandler $className */
		list($className) = PaySystem\Manager::includeHandler($handlerName);

		$handlerModeList = array();
		if (class_exists($className))
			$handlerModeList = $className::getHandlerModeList();

		$isOrderDocument = mb_strpos($handlerName, 'orderdocument') === 0;
		if ($handlerModeList || $isOrderDocument):?>
			<tr>
				<?
					$postfix = $isOrderDocument ? '_DOCUMENT' : '';
				?>
				<td width="40%" valign="top"><?=Loc::getMessage("F_PS_MODE".$postfix);?>:</td>
				<td width="60%" valign="top">
				<?
					if ($handlerModeList)
					{
						if (!class_exists('\Bitrix\Sale\Internals\Input\Enum'))
							require $documentRoot.'/bitrix/modules/sale/lib/internals/input.php';

						if (class_exists($className))
						{
							echo Bitrix\Sale\Internals\Input\Enum::getEditHtml(
								'PS_MODE',
								[
									'OPTIONS' => $handlerModeList,
									'ID' => 'PS_MODE',
									'ONCHANGE' => "BX.Sale.PaySystem.getHandlerOptions(BX('ACTION_FILE'))",
								],
								$psMode
							);
						}
					}

					if ($isOrderDocument):
						$componentPath = \CComponentEngine::makeComponentPath('bitrix:documentgenerator.templates');
						$componentPath = getLocalPath('components'.$componentPath.'/slider.php');
						$uri = new \Bitrix\Main\Web\Uri($componentPath);
						$params = [
							'PROVIDER' => \Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Invoice::class,
							'MODULE' => 'crm'
						];
						$href = $uri->addParams($params)->getLocator();
					?>
						<span>
							<span class="bx-button-add-template" onclick='BX.SidePanel.Instance.open("<?=$href;?>", {width: 930, events: {onCloseComplete: function() {BX.Sale.PaySystem.getHandlerOptions(BX("ACTION_FILE"));}}});'>
								<?=Loc::getMessage('F_PS_MODE_DOCUMENT_ADD');?>
							</span>
						</span>
					<?
					endif;
					?>
				</td>
			</tr>
		<?endif;?>
	</tbody>
	<?
		$handlerDescription = '';
		if ($psDescription)
		{
			$handlerDescription = $psDescription;
		}
		elseif ($description)
		{
			if (is_array($description))
			{
				if (array_key_exists('MAIN', $description))
					$handlerDescription = $description['MAIN'];
			}
			else
			{
				$handlerDescription = $description;
			}
		}
	?>
	<tbody id="pay_system_ps_description">
		<?if ($handlerDescription !== ''):?>
			<tr>
				<td width="40%"></td>
				<td width="60%"><?=$handlerDescription;?></td>
			</tr>
		<?endif;?>
	<tbody>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=Loc::getMessage("F_PSA_NAME");?>:</td>
		<td width="60%">
			<?
			$psaName = $request->get('PSA_NAME') ? $request->get('PSA_NAME') : $paySystem['PSA_NAME'];
			?>
			<input type="text" name="PSA_NAME" id="PSA_NAME" value="<?=htmlspecialcharsbx($psaName);?>" size="40">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=Loc::getMessage("F_NAME");?>:</td>
		<td width="60%">
			<?
				$name = $request->get('NAME') ? $request->get('NAME') : $paySystem['NAME'];
			?>
			<input type="text" name="NAME" id="NAME" value="<?=htmlspecialcharsbx($name);?>" size="40">
		</td>
	</tr>
	<tr>
		<td width="40%"><label for="ACTIVE"><?=Loc::getMessage("F_ACTIVE");?>:</label></td>
		<td width="60%">
			<?
				if ($request->isPost())
					$active = $request->get('ACTIVE') ? $request->get('ACTIVE') : '';
				else
					$active = isset($paySystem['ACTIVE']) ? $paySystem['ACTIVE'] : 'Y';
			?>
			<input type="checkbox" name="ACTIVE" id="ACTIVE" value="Y" <?=($active == 'Y' ? 'checked' : '')?>>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage("F_SORT");?>:</td>
		<td width="60%">
			<?
				$sort = $request->get('SORT') ? $request->get('SORT') : $paySystem['SORT'];
			?>
			<input type="text" name="SORT" id="SORT" value="<?=htmlspecialcharsbx($sort)?>" size="5">
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top"><?=Loc::getMessage("F_DESCRIPTION");?>:</td>
		<td width="60%" valign="top">
			<?
				$description = $request->get('DESCRIPTION') ? $request->get('DESCRIPTION') : $paySystem['DESCRIPTION'];
			?>
			<?=wrapDescrLHE("DESCRIPTION", $description, "hndl_dscr_".$id);?>
			<script language="JavaScript">setLHEClass('bxlhe_frame_hndl_dscr_<?=$id;?>'); </script>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top"><?=Loc::getMessage('SPS_LOGOTIP')?>:</td>
		<td width="60%" valign="top">
			<div><input type="file" name="LOGOTIP" id="LOGOTIP"></div>
			<?if ($paySystem["LOGOTIP"] > 0):?>
				<br>
				<?
				$logoFileArray = CFile::GetFileArray($paySystem["LOGOTIP"]);
				echo CFile::ShowImage($logoFileArray, 150, 150, "border=0", "", false);
				?>
				<?if (!empty($paySystem["LOGOTIP"])) :?>
					<div style="margin-top:10px;">
						<input type="checkbox" name="LOGOTIP_del" value="Y" id="LOGOTIP_del" >
						<label for="LOGOTIP_del"><?=Loc::getMessage("SPS_LOGOTIP_DEL");?></label>
					</div>
				<?endif;?>
			<?endif;?>
		</td>
	</tr>
	<tr>
		<td width="40%" align="right"><label for="NEW_WINDOW"><?=Loc::getMessage("SPS_NEW_WINDOW");?>:</label></td>
		<td width="60%">
			<?
				if ($request->isPost())
					$active = $request->get('NEW_WINDOW') ? $request->get('NEW_WINDOW') : '';
				else
					$active = isset($paySystem['NEW_WINDOW']) ? $paySystem['NEW_WINDOW'] : 'N';
			?>

			<input type="checkbox" name="NEW_WINDOW" id="NEW_WINDOW" value="Y"<?=($active == 'Y') ? ' checked' : '';?>>
		</td>
	</tr>
	<tr>
		<td width="40%" align="right"><label for="IS_CASH"><?=Loc::getMessage("SPS_IS_CASH");?>:</label></td>
		<?
			$isCash = ($request->isPost()) ? $request->get('IS_CASH') : $paySystem['IS_CASH'];
		?>
		<td width="60%">
			<select name="IS_CASH">
				<option value="N" <? if ($isCash == "N") echo "selected"?>><?=Loc::getMessage('SPS_IS_CASH_TYPE_NO_CASH');?></option>
				<option value="Y" <? if ($isCash == "Y") echo "selected"?>><?=Loc::getMessage('SPS_IS_CASH_TYPE_CASH');?></option>
				<option value="A" <? if ($isCash == "A") echo "selected"?>><?=Loc::getMessage('SPS_IS_CASH_TYPE_ACQUIRING');?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%" align="right"><label for="ALLOW_EDIT_PAYMENT"><?=Loc::getMessage("SPS_ALLOW_EDIT_PAYMENT");?>:</label></td>
		<td width="60%">
			<?
				if ($request->isPost())
					$allowEditPayment = $request->get('ALLOW_EDIT_PAYMENT') ? $request->get('ALLOW_EDIT_PAYMENT') : '';
				else
					$allowEditPayment = isset($paySystem['ALLOW_EDIT_PAYMENT']) ? $paySystem['ALLOW_EDIT_PAYMENT'] : 'Y';
			?>

			<input type="checkbox" name="ALLOW_EDIT_PAYMENT" id="ALLOW_EDIT_PAYMENT" value="Y"<?=($allowEditPayment == 'Y') ? ' checked' : '';?>>
		</td>
	</tr>
	<?
	$licensePrefix = CModule::IncludeModule("bitrix24") ? \CBitrix24::getLicensePrefix() : "";
	if (!IsModuleInstalled("bitrix24") || in_array($licensePrefix, array("ru"))):
	?>
	<tr>
		<td width="40%" align="right"><label for="CAN_PRINT_CHECK"><?=Loc::getMessage("SPS_CAN_PRINT_CHECK");?>:</label></td>
		<td width="60%">
			<?
				if ($request->isPost())
					$printable = $request->get('CAN_PRINT_CHECK') ? $request->get('CAN_PRINT_CHECK') : '';
				else
					$printable = isset($paySystem['CAN_PRINT_CHECK']) ? $paySystem['CAN_PRINT_CHECK'] : 'N';
			?>

			<input type="checkbox" name="CAN_PRINT_CHECK" id="CAN_PRINT_CHECK" value="Y"<?=($printable == 'Y') ? ' checked' : '';?>>
		</td>
	</tr>
	<?endif;?>
	<tr>
		<td width="40%" align="right"><?=Loc::getMessage("SPS_ENCODING");?>:</td>
		<td width="60%">
			<select name="ENCODING">
				<option value="" <? if ($paySystem['ENCODING'] == "") echo "selected"?>></option>
				<option value="windows-1251" <? if ($paySystem['ENCODING'] == "windows-1251") echo "selected"?>>windows-1251</option>
				<option value="utf-8" <? if ($paySystem['ENCODING'] == "utf-8") echo "selected"?>>utf-8</option>
				<option value="iso-8859-1" <? if ($paySystem['ENCODING'] == "iso-8859-1") echo "selected"?>>iso-8859-1</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage("F_CODE");?>:</td>
		<td width="60%">
			<?
				$code = $request->get('CODE') ? $request->get('CODE') : $paySystem['CODE'];
			?>
			<input type="text" name="CODE" value="<?=htmlspecialcharsbx($code);?>" size="40">
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SPS_XML_ID')?>:</td>
		<td width="60%">
			<?
				$xmlId = $request->get('XML_ID') ? $request->get('XML_ID') : $paySystem['XML_ID'];
				if (!$xmlId)
				{
					$xmlId = PaySystem\Manager::generateXmlId();
				}
			?>
			<input type="text" name="XML_ID" value="<?=htmlspecialcharsbx($xmlId);?>" size="40">
		</td>
	</tr>

	<?php
	$entityName = $handlerName;
	if ($psMode)
	{
		$entityName .= $psMode;
	}
	$needVerification = PaySystem\Domain\Verification\Manager::needVerification($entityName);
	?>
	<tbody id="pay_system_validation_domain" <?=($needVerification ? "" : "style='display:none;'")?>>
		<tr>
			<td colspan="2" align="center" class="heading"><?=Loc::getMessage("SPS_VALIDATION_DOMAIN_HEAD")?></td>
		</tr>
		<tr>
			<?php
			$domainVerificationFormUrl = \CComponentEngine::makeComponentPath('bitrix:sale.domain.verification.form');
			$domainVerificationFormUrl = getLocalPath('components'.$domainVerificationFormUrl.'/slider.php');
			$domainVerificationFormUrl = new \Bitrix\Main\Web\Uri($domainVerificationFormUrl);
			$domainVerificationFormUrl->addParams([
				'analyticsLabel' => 'paySystemDomainVerification',
				'entity' => $entityName,
				'manager' => PaySystem\Domain\Verification\Manager::class,
			]);
			?>
			<td><?=Loc::getMessage("SPS_VALIDATION_DOMAIN_VALIDATION")?></td>
			<td><a href="javascript:void(0);" id="domain-verification-link" onclick="BX.Sale.PaySystem.openVerificationForm('<?=$domainVerificationFormUrl?>')"><?=Loc::getMessage("SPS_VALIDATION_DOMAIN_FORM")?></a></td>
		</tr>
	</tbody>
	<tr>
		<td colspan="2" align="center" class="heading">
			<?=Loc::getMessage('SALE_PSE_BIS_VAL_SETTINGS')?>
		</td>
	</tr>
	<tr>
		<td colspan="2" id="paysystem-business-value-settings" style="padding: 10px 0">
			<?
				if ($request->get('ACTION_FILE') !== null)
				{
					$data = PaySystem\Manager::getHandlerDescription($request->get('ACTION_FILE'));

					if ($id > 0)
					{
						$consumer = 'PAYSYSTEM_'.$id;
						try
						{
							BusinessValue::changeConsumer($consumer, $data);
						}
						catch (SystemException $e)
						{
							BusinessValue::addConsumer('PAYSYSTEM_'.$id, $data);
						}
					}
					else
					{
						$consumer = 'PAYSYSTEM_NEW';
						if ($data)
							BusinessValue::addConsumer($consumer, $data);
					}

					$businessValueControl->renderMap(array('CONSUMER_KEY' => $consumer));
				}
				elseif ($id > 0)
				{
					$businessValueControl->renderMap(array('CONSUMER_KEY' => 'PAYSYSTEM_'.$id));
				}
			?>
		</td>
	</tr>
	<tbody id="pay_system_tariff">
		<?
			$actionFile = ($request->get('ACTION_FILE')) ? $request->get('ACTION_FILE') : $paySystem['ACTION_FILE'];
			$tariffBlock = '';

			$tariff = PaySystem\Manager::getTariff($actionFile, $paySystem['ID']);
			if($tariff)
			{
				$tariffBlock = '<tr class="heading"><td align="center" colspan="2">'.Loc::getMessage('SALE_PS_TARIFF').'</td></tr>';

				$arMultiControlQuery = array();
				foreach ($tariff as $fieldId => $arField)
				{
					if(!empty($arMultiControlQuery)
						&&
						(!isset($arField['MCS_ID'])|| !array_key_exists($arField['MCS_ID'], $arMultiControlQuery))
					)
					{
						$tariffBlock .= CSaleHelper::getAdminMultilineControl($arMultiControlQuery);
						$arMultiControlQuery = array();
					}

					$controlHtml = CSaleHelper::getAdminHtml($fieldId, $arField, 'TARIF', 'pay_sys_form');

					if($arField["TYPE"] == 'MULTI_CONTROL_STRING')
					{
						$arMultiControlQuery[$arField['MCS_ID']]['CONFIG'] = $arField;
						continue;
					}
					elseif(isset($arField['MCS_ID']))
					{
						$arMultiControlQuery[$arField['MCS_ID']]['ITEMS'][] = $controlHtml;
						continue;
					}

					$tariffBlock .= CSaleHelper::wrapAdminHtml($controlHtml, $arField);
				}

				if(!empty($arMultiControlQuery))
					$tariffBlock .= CSaleHelper::getAdminMultilineControl($arMultiControlQuery);

				echo $tariffBlock;
				echo "<script type=\"text/javascript\">BX.Sale.PaySystem.initTariffLoad();</script>";
			}
		?>
	</tbody>
	<tbody id="pay_system_yandex_settings">
	<?
		if ($paySystem && array_key_exists('ACTION_FILE', $paySystem))
		{
			if ($paySystem['ACTION_FILE'] == 'yandex' && !$adminSidePanelHelper->isPublicSidePanel())
			{
				$service = new PaySystem\Service($paySystem);
				if ($service->isRefundable())
				{
					$pathToReturnPage = $documentRoot.'/bitrix/modules/sale/handlers/paysystem/'.$handlerName.'/settings/refund.php';
					if (IO\File::isFileExists($pathToReturnPage)):?>
						<tr>
							<td colspan="2" align="center" class="heading" style="padding-top: 10px">
								<?=Loc::getMessage('SALE_PSE_RETURN')?>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="padding-top: 10px" align="center">
								<?
								$message = Loc::getMessage('SALE_PS_RETURN_SETTINGS_YANDEX');
								if(mb_strpos($message, "/bitrix/admin/"))
								{
									$message = str_replace("/bitrix/admin/", $selfFolderUrl, $message);
								}
								echo $message;
								$message = $adminSidePanelHelper->editUrlToPublicPage($message);
								?>
							</td>
						</tr>
					<?endif;
				}
			}
			elseif ($paySystem['ACTION_FILE'] == 'yandexinvoice')
			{
				$pathToReturnPage = $documentRoot.'/bitrix/modules/sale/admin/yandexinvoice_settings.php';
				if (IO\File::isFileExists($pathToReturnPage)):?>
					<tr>
						<td colspan="2" align="center" class="heading" style="padding-top: 10px">
							<?=Loc::getMessage('SALE_PSE_YANDEX_INVOICE_SETTINGS_TITLE')?>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="padding-top: 10px" align="center">
							<?
								$yandexInvoiceSettings = array();
								$shopId = BusinessValue::get('YANDEX_INVOICE_SHOP_ID', 'PAYSYSTEM_'.$id, null);
								if ($shopId)
								{
									$dbRes = \Bitrix\Sale\Internals\YandexSettingsTable::getById($shopId);
									$yandexInvoiceSettings = $dbRes->fetch();
								}
								if ($yandexInvoiceSettings && $yandexInvoiceSettings['PKEY'] && $yandexInvoiceSettings['PUB_KEY'])
								{
									$message = Loc::getMessage('SALE_PSE_YANDEX_INVOICE_SETTINGS_OK', array('#ID#' => $id));
									if(mb_strpos($message, "/bitrix/admin/"))
									{
										$message = str_replace("/bitrix/admin/", $selfFolderUrl, $message);
									}
									$message = $adminSidePanelHelper->editUrlToPublicPage($message);
									echo $message;
								}
								else
								{
									$message = Loc::getMessage('SALE_PSE_YANDEX_INVOICE_SETTINGS', array('#ID#' => $id));
									if(mb_strpos($message, "/bitrix/admin/"))
									{
										$message = str_replace("/bitrix/admin/", $selfFolderUrl, $message);
									}
									$message = $adminSidePanelHelper->editUrlToPublicPage($message);
									echo $message;
								}

							?>
						</td>
					</tr>
				<?endif;
			}
		}
	?>
	</tbody>
<?

$tabControl->EndTab();

if ($restrictionsHtml !== ''):?>
	<?$tabControl->BeginNextTab();?>
		<tr><td id="sale-paysystem-restriction-container"><?=$restrictionsHtml?></td></tr>
	<?$tabControl->EndTab();
endif;

$tabControl->Buttons(array("disabled" => ($saleModulePermissions < "W"), "back_url" => $listUrl));
$tabControl->End();
?>
</form>
<script language="JavaScript">
	BX.message({
		SALE_RDL_RESTRICTION: '<?=Loc::getMessage("SALE_RDL_RESTRICTION")?>',
		SALE_RDL_SAVE: '<?=Loc::getMessage("SALE_RDL_SAVE")?>',
		SALE_PS_MODE: '<?=Loc::getMessage("F_PS_MODE")?>',
		SALE_BT_DEL: '<?=Loc::getMessage("SPS_LOGOTIP_DEL")?>',
		SALE_TEMPLATE_DOCUMENT_ADD: '<?=Loc::getMessage("F_PS_MODE_DOCUMENT_ADD")?>'
	});
</script>
<?
require($documentRoot."/bitrix/modules/main/include/epilog_admin.php");

function wrapDescrLHE($inputName, $content = '', $divId = false)
{
	ob_start();
	$ar = array(
		'inputName' => $inputName,
		'height' => '160',
		'width' => '100%',
		'content' => $content,
		'bResizable' => true,
		'bManualResize' => true,
		'bUseFileDialogs' => false,
		'bFloatingToolbar' => false,
		'bArisingToolbar' => false,
		'bAutoResize' => true,
		'bSaveOnBlur' => true,
		'toolbarConfig' => array(
			'Bold', 'Italic', 'Underline', 'Strike',
			'CreateLink', 'DeleteLink',
			'Source', 'BackColor', 'ForeColor', 'Image'
		)
	);

	if($divId)
		$ar['id'] = $divId;

	$LHE = new CLightHTMLEditor;
	$LHE->Show($ar);
	$sVal = ob_get_contents();
	ob_end_clean();

	return $sVal;
}

?>
