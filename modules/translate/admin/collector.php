<?php
//region head
define('ADMIN_MODULE_NAME', 'translate');
require_once($_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_before.php');

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 */

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Translate,
	Bitrix\Main\Web\Json;


if(!Main\Loader::includeModule('translate'))
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

	\CAdminMessage::showMessage('Translate module not found');

	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
}

if (!Translate\Permission::canEdit($USER))
{
	$APPLICATION->AuthForm(Main\Localization\Loc::getMessage('ACCESS_DENIED'));
}
if (!Translate\Permission::canEditSource($USER))
{
	$APPLICATION->AuthForm(\Bitrix\Main\Localization\Loc::getMessage('ACCESS_DENIED'));
}

define('HELP_FILE', 'translate_list.php');

$APPLICATION->SetTitle(Loc::getMessage('TRANS_TITLE'));

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

Main\UI\Extension::load([
	'main.loader',
	'ui.buttons',
	'ui.alerts',
	'ui.notification',
	'ui.stepprocessing',
]);

//endregion

$isUtfMode = Translate\Config::isUtfMode();
$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();

$enabledLanguages = Translate\Config::getEnabledLanguages();
$availableLanguages = Translate\Config::getAvailableLanguages();
$allLanguages = Translate\Config::getLanguages();
$allowedEncodings = Translate\Config::getAllowedEncodings();


$assemblyDate = date('Ymd');
$languageId = Loc::getCurrentLang();
$encoding = Main\Localization\Translation::getCurrentEncoding();
$convertEncoding = $useTranslationRepository;
$packFile = Translate\IO\Archiver::libAvailable();
$updatePublic = false;

const ACTION_EXTRACT = 'extract';
const ACTION_COLLECT = 'collect';

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$currentAction = $request->get('tabControl_active_tab') === ACTION_EXTRACT ? ACTION_EXTRACT : ACTION_COLLECT;

$aTabs = array(
	array(
		'DIV' => ACTION_COLLECT,
		'TAB' => Loc::getMessage("TRANS_UPLOAD"),
		'TITLE' => Loc::getMessage("TRANS_UPLOAD"),
		'ONSELECT' => "tabSelect('".ACTION_COLLECT."')"
	),
	array(
		'DIV' => ACTION_EXTRACT,
		'TAB' => Loc::getMessage("TRANS_DOWNLOAD"),
		'TITLE' => Loc::getMessage("TRANS_DOWNLOAD"),
		'ONSELECT' => "tabSelect('".ACTION_EXTRACT."')"
	),
);

$tabControl = new \CAdminTabControl('tabControl', $aTabs, false, true);
$tabControl->selectedTab = $currentAction;

$tabControl->Begin();

//region Form COLLECT LANGUAGE
?>
	<form method="post" action="" name="form<?= ACTION_COLLECT ?>">
		 <?/*
	<input type="hidden" name="tabControl_active_tab" value="<?= TAB_COLLECT ?>">
	<?=bitrix_sessid_post()?>
	<?
	*/

		 $tabControl->BeginNextTab();

		 ?>
		<tr class="adm-required-field">
			<td style="width:40%">
					<?= Loc::getMessage("TR_SELECT_LANGUAGE")?>:
			</td>
			<td>
				<select name="languageId">
						 <?
						 $iterator = Main\Localization\LanguageTable::getList([
							 'select' => ['ID', 'NAME'],
							 'filter' => [
								 'ID' => array_intersect($availableLanguages, $enabledLanguages),
								 '=ACTIVE' => 'Y'
							 ],
							 'order' => ['SORT' => 'ASC']
						 ]);
						 while ($row = $iterator->fetch())
						 {
							 ?><option value="<?= $row['ID'] ?>"<?=($row['ID'] == $languageId ? ' selected' : ''); ?>><?= $row['NAME'] ?> (<?= $row['ID'] ?>)</option><?
						 }
						 ?>
				</select>
			</td>
		</tr>

		<tr class="adm-required-field">
			<td>
					<?= Loc::getMessage("TR_COLLECT_DATE")?>:
			</td>
			<td>
				<input type="text" name="assemblyDate" size="10" maxlength="8" value="<?= htmlspecialcharsbx($assemblyDate) ?>">
			</td>
		</tr>
		 <?

		 if (!$isUtfMode && !$useTranslationRepository)
		 {
			 ?>
		   <tr>
			   <td>
						<?= Loc::getMessage("TR_CONVERT_UTF8")?>:
			   </td>
			   <td>
				   <input type="hidden" name="encoding" value="utf-8" <?= ($convertEncoding ? '' : 'disabled="disabled"') ?>>
				   <input type="checkbox" name="convertEncoding" value="Y" <?= ($convertEncoding ? 'checked="checked"' : '') ?> onClick="encodeClicked()">
			   </td>
		   </tr>
			 <?
		 }
		 else
		 {
			 ?>
		   <tr>
			   <td>
						<?= Loc::getMessage("TR_CONVERT_NATIONAL")?>:
			   </td>
			   <td>
				   <input type="checkbox" name="convertEncoding" value="Y" <?= ($convertEncoding ? 'checked="checked"' : '') ?> onClick="encodeClicked()">
			   </td>
		   </tr>
		   <tr>
			   <td>
						<?= Loc::getMessage("TR_CONVERT_ENCODING") ?>:
			   </td>
			   <td>
				   <select name="encoding">
							 <?
							 foreach ($allowedEncodings as $enc)
							 {
								 $encTitle = Translate\Config::getEncodingName($enc);
								 ?><option value="<?= htmlspecialcharsbx($enc) ?>"<?if ($enc == $encoding) echo " selected";?>><?= $encTitle ?></option><?
							 }
							 ?>
				   </select>
			   </td>
		   </tr>
			 <?
		 }

		 ?>
		<tr>
			<td>
					<?= Loc::getMessage("TR_PACK_FILES")?>:
			</td>
			<td>
				<input type="checkbox" name="packFile" value="Y" <?= ($packFile ?  'checked="checked"' : '') ?>>
			</td>
		</tr>
		 <?

		 $tabControl->EndTab();

		 ?>
	</form>
<?

//endregion


//region Form UPLOAD FILE

?>
	<form method="post" action="" name="form<?= ACTION_EXTRACT ?>" enctype="multipart/form-data">
		 <?/*
	<input type="hidden" name="tabControl_active_tab" value="<?= TAB_EXTRACT ?>">
	<?=bitrix_sessid_post()?>
	<?
	*/

		 $tabControl->BeginNextTab();

		 ?>
		<tr class="adm-required-field">
			<td style="width:40%" nowrap>
					<?= Loc::getMessage('TR_UPLOAD_FILE')?>
					<?= Loc::getMessage('TR_UPLOAD_FILE_MAX_SIZE', ['#SIZE#' => \CFile::FormatSize(Translate\Controller\Asset\Grabber::getMaxUploadSize())]) ?>:
			</td>
			<td>
				<input type="file" name="tarFile">
			</td>
		</tr>
		<tr class="adm-required-field">
			<td>
					<?= Loc::getMessage("TR_SELECT_LANGUAGE")?> <?=Loc::getMessage("TR_SELECT_LANGUAGE_DESCRIPTION")?>:
			</td>
			<td>
				<select name="languageId">
						 <?
						 $iterator = Main\Localization\LanguageTable::getList([
							 'select' => ['ID', 'NAME'],
							 'filter' => [
								 'ID' => $enabledLanguages,
								 '=ACTIVE' => 'Y'
							 ],
							 'order' => ['SORT' => 'ASC']
						 ]);
						 while ($row = $iterator->fetch())
						 {
							 ?><option value="<?= $row['ID'] ?>"<?=($row['ID'] == $languageId ? ' selected' : ''); ?>><?= $row['NAME'] ?> (<?= $row['ID'] ?>)</option><?
						 }
						 ?>
				</select>
			</td>
		</tr>
		 <?

		 if (!$isUtfMode && !$useTranslationRepository)
		 {
			 ?>
		   <tr>
			   <td>
						<?= Loc::getMessage("TR_CONVERT_FROM_UTF8")?>:
			   </td>
			   <td>
				   <input type="hidden" name="encoding" value="utf-8" <?= ($convertEncoding ? '' : 'disabled="disabled"') ?>>
				   <input type="checkbox" name="localizeEncoding" value="Y" <?=($convertEncoding ? 'checked="checked"' : ''); ?> onClick="localizeClicked()">
			   </td>
		   </tr>
			 <?
		 }
		 else
		 {
			 ?>
		   <tr>
			   <td>
						<?= Loc::getMessage("TR_CONVERT_FROM_NATIONAL")?>:
			   </td>
			   <td>
				   <input type="checkbox" id="localizeEncoding" name="localizeEncoding" value="Y" <?=($convertEncoding ? 'checked="checked"' : ''); ?> onClick="localizeClicked()">
			   </td>
		   </tr>
		   <tr id="tr-encoding" >
			   <td>
						<?= Loc::getMessage("TR_CONVERT_ENCODING")?>:
			   </td>
			   <td>
				   <select name="encoding" <?= ($convertEncoding ? '' : 'disabled="disabled"') ?>><?
							 foreach ($allowedEncodings as $enc)
							 {
								 $encTitle = Translate\Config::getEncodingName($enc);

								 ?><option value="<?=htmlspecialcharsbx($enc); ?>"<?if ($enc == $encoding) echo " selected";?>><?= $encTitle ?></option><?
							 }
							 ?></select>
			   </td>
		   </tr>
			 <?
		 }

		 ?>
		<tr>
			<td>
					<?= Loc::getMessage("TR_IMPORT_UPDATE_PUBLIC")?>:
			</td>
			<td>
				<input type="checkbox" name="updatePublic" value="Y" <?=($updatePublic ? 'checked="checked"' : ''); ?>>
			</td>
		</tr>
		 <?


		 $tabControl->EndTab();

		 ?>
	</form>
<?

//endregion


$tabControl->Buttons();
?>
	<input type="submit" id="tr_submit" class="adm-btn-green" data-action="<?= $currentAction ?>" value="<?
	 echo $currentAction === ACTION_EXTRACT ? Loc::getMessage("TR_DOWNLOAD_LANGUAGE") : Loc::getMessage("TR_COLLECT_LANGUAGE"); ?>">
<?

$tabControl->End();


?>
	<script>

		function tabSelect(action)
		{
			var pageTitle = BX("adm-title"), button = BX('tr_submit');

			if(action == '<?=ACTION_COLLECT?>')
			{
				document.title = '<?= Loc::getMessage("TRANS_UPLOAD") ?>';
				if(pageTitle) pageTitle.innerHTML = '<?= Loc::getMessage("TRANS_UPLOAD") ?>';
				if(button){
					button.value = '<?= Loc::getMessage("TR_COLLECT_LANGUAGE") ?>';
					BX.data(button, 'action', action);
				}
				encodeClicked();
			}
			else
			{
				document.title = '<?= Loc::getMessage("TRANS_DOWNLOAD") ?>';
				if(pageTitle) pageTitle.innerHTML = '<?= Loc::getMessage("TRANS_DOWNLOAD") ?>';
				if(button){
					button.value = '<?= Loc::getMessage("TR_DOWNLOAD_LANGUAGE") ?>';
					BX.data(button, 'action', action);
				}
				localizeClicked();
			}
		}

		function encodeClicked()
		{
			document.forms['form<?=ACTION_COLLECT?>'].elements['encoding'].disabled =
				!document.forms['form<?=ACTION_COLLECT?>'].elements['convertEncoding'].checked;
		}
		function localizeClicked()
		{
			document.forms['form<?=ACTION_EXTRACT?>'].elements['encoding'].disabled =
				!document.forms['form<?=ACTION_EXTRACT?>'].elements['localizeEncoding'].checked;
		}


		BX.ready(function(){

			BX.bind(BX('tr_submit'), 'click', function ()
			{
				var process, form, button, action;
				button = BX('tr_submit');
				action = BX.data(button, 'action');
				if (action == '<?=ACTION_COLLECT?>')
				{
					form = document.forms['form<?=ACTION_COLLECT?>'];
					process = BX.UI.StepProcessing.ProcessManager.get('<?=ACTION_COLLECT?>');
				}
				else
				{
					form = document.forms['form<?=ACTION_EXTRACT?>'];
					process = BX.UI.StepProcessing.ProcessManager.get('<?=ACTION_EXTRACT?>');
				}
				process
					.setParams(BX.ajax.prepareForm(form).data)
					.showDialog()
					.start();
			});


			BX.UI.StepProcessing.ProcessManager.create(<?=Json::encode([
				'id' => ACTION_COLLECT,
				'controller' => 'bitrix:translate.controller.asset.grabber',
				'messages' => [
					'DialogTitle' => Loc::getMessage("TRANS_UPLOAD"),
					'DialogStopButton' => Loc::getMessage('TR_DLG_BTN_STOP'),
					'DialogCloseButton' => Loc::getMessage('TR_DLG_BTN_CLOSE'),
					'RequestCanceling' => Loc::getMessage('TR_DLG_REQUEST_CANCEL'),
					'RequestCanceled' => Loc::getMessage('TR_COLLECT_CANCELED'),
					'RequestCompleted' => Loc::getMessage('TR_COLLECT_COMPLETED'),
					'DialogExportDownloadButton' => Loc::getMessage('TR_COLLECT_DOWNLOAD'),
					'DialogExportClearButton' => Loc::getMessage('TR_COLLECT_CLEAR'),
				],
				'showButtons' => ['stop' => true, 'close' => true],
				'queue' => [
					[
						'action' => \Bitrix\Translate\Controller\Index\Collector::ACTION_COLLECT_LANG_PATH,
						'controller' => 'bitrix:translate.controller.index.collector',
						'title' => Loc::getMessage('TR_COLLECT_LANG_PATH', ['#NUM#' => 1, '#LEN#' => 3]),
						'progressBarTitle' => Loc::getMessage('TR_COLLECT_LANG_PATH_PROGRESS'),
						'params' => [
							'path' => \Bitrix\Translate\Controller\Asset\Grabber::START_PATH
						]
					],
					[
						'action' => \Bitrix\Translate\Controller\Asset\Grabber::ACTION_COLLECT,
						'controller' => 'bitrix:translate.controller.asset.grabber',
						'title' => Loc::getMessage('TR_COLLECT_COLLECT', ['#NUM#' => 2, '#LEN#' => 3]),
						'progressBarTitle' => Loc::getMessage('TR_COLLECT_COLLECT_PROGRESS'),
						'params' => [
							'path' => \Bitrix\Translate\Controller\Asset\Grabber::START_PATH
						]
					],
					[
						'action' => \Bitrix\Translate\Controller\Asset\Grabber::ACTION_PACK,
						'controller' => 'bitrix:translate.controller.asset.grabber',
						'title' => Loc::getMessage('TR_COLLECT_PACK', ['#NUM#' => 3, '#LEN#' => 3]),
						'progressBarTitle' => Loc::getMessage('TR_COLLECT_PACK_PROGRESS'),
						'params' => [
							'path' => \Bitrix\Translate\Controller\Asset\Grabber::START_PATH
						]
					],
					[
						'action' => \Bitrix\Translate\Controller\Asset\Grabber::ACTION_FINALIZE,
						'finalize' => true,
					],
				],
			])?>);

			BX.UI.StepProcessing.ProcessManager.create(<?=Json::encode([
					'id' => ACTION_EXTRACT,
					'controller' => 'bitrix:translate.controller.asset.grabber',
					'messages' => [
						'DialogTitle' => Loc::getMessage("TRANS_DOWNLOAD"),
						'DialogStopButton' => Loc::getMessage('TR_DLG_BTN_STOP'),
						'DialogCloseButton' => Loc::getMessage('TR_DLG_BTN_CLOSE'),
						'RequestCanceling' => Loc::getMessage('TR_DLG_REQUEST_CANCEL'),
						'RequestCanceled' => Loc::getMessage('TR_IMPORT_CANCELED'),
						'RequestCompleted' => Loc::getMessage('TR_IMPORT_COMPLETED'),
					],
					'showButtons' => ['stop' => true, 'close' => true],
					'queue' => [
						[
							'action' => \Bitrix\Translate\Controller\Asset\Grabber::ACTION_UPLOAD,
							'title' => Loc::getMessage('TR_EXTRACT_ACTION_UPLOAD', ['#NUM#' => 1, '#LEN#' => 4]),
							'progressBarTitle' => Loc::getMessage('TR_EXTRACT_ACTION_UPLOAD_PROGRESS'),
						],
						[
							'action' => \Bitrix\Translate\Controller\Asset\Grabber::ACTION_EXTRACT,
							'title' => Loc::getMessage('TR_EXTRACT_ACTION_EXTRACTING', ['#NUM#' => 2, '#LEN#' => 4]),
							'progressBarTitle' => Loc::getMessage('TR_EXTRACT_ACTION_EXTRACTING_PROGRESS'),
						],
						[
							'action' => \Bitrix\Translate\Controller\Asset\Grabber::ACTION_APPLY,
							'title' => Loc::getMessage('TR_EXTRACT_ACTION_APPLYING', ['#NUM#' => 3, '#LEN#' => 4]),
							'progressBarTitle' => Loc::getMessage('TR_EXTRACT_ACTION_APPLYING_PROGRESS'),
						],
						[
							'action' => \Bitrix\Translate\Controller\Asset\Grabber::ACTION_APPLY_PUBLIC,
							'title' => Loc::getMessage('TR_EXTRACT_ACTION_APPLYING_PUBLIC', ['#NUM#' => 4, '#LEN#' => 4]),
							'progressBarTitle' => Loc::getMessage('TR_EXTRACT_ACTION_APPLYING_PROGRESS'),
						],
						[
							'action' => \Bitrix\Translate\Controller\Asset\Grabber::ACTION_FINALIZE,
							'finalize' => true,
						],
					],
				])?>);

			localizeClicked();
			encodeClicked();
		});
	</script>
<?



require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");