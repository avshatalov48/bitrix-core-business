<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$dialog = $arResult['dialog'];

$data = $dialog->getRuntimeData();
$map = $dialog->getMap();
$activityData = $data['ACTIVITY_DATA'];

$properties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : array();
$currentValues = $dialog->getCurrentValues();

foreach ($properties as $id => $property):
	$name = $map[$id]['FieldName'];
	$title = \Bitrix\Bizproc\RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID);
	?>
	<div class="bizproc-automation-popup-settings">
		<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete"><?=htmlspecialcharsbx($title)?>: </span>
		<?
		echo $dialog->renderFieldControl($map[$id], $currentValues[$name]);
		?>
	</div>
	<?
endforeach;

if ($activityData['USE_PLACEMENT'] === 'Y' && !empty($activityData['APP_ID_INT'])):

$appJs = (int) $activityData['APP_ID_INT'];
$codeJs = htmlspecialcharsbx(CUtil::JSEscape($activityData['CODE']));
$actNameJs = htmlspecialcharsbx(CUtil::JSEscape($dialog->getActivityName()));
?>
	<div class="bizproc-automation-popup-settings">
		<span class="ui-btn ui-btn-primary" onclick="if (!BX.rest) return false; BX.rest.AppLayout.openApplication(<?=$appJs?>, {
			action: 'robot_settings',
			code: '<?=$codeJs?>',
			activity_name: '<?=$actNameJs?>'
		});">
			<?=GetMessage('BIZPROC_AUTOMATION_CONFIGURE')?>
		</span>
	</div>
<?
endif;