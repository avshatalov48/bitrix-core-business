<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

Loc::loadMessages(dirname(__FILE__).'/template.php');
$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('REPORT_LIST'));

\Bitrix\Main\Loader::includeModule('ui');

$APPLICATION->IncludeComponent("bitrix:ui.info.helper", "", []);

?>

<div class="reports-list-wrap-restricted --lock">
&nbsp;
</div>

<script type="text/javascript">
	BX.ready(
		function ()
		{
			var codes = {
				crm: 'limit_crm_tasks_constructor_reports',
				tasks: 'limit_tasks_constructor_reports'
			};
			var sliderCode = (<?=($arResult['HELPER_CLASS'] === 'CTasksReportHelper')?> ? codes.tasks : codes.crm);

			BX.UI.InfoHelper.show(sliderCode);
		}
	);
</script>
