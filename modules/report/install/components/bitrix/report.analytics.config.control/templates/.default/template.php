<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
CJSCore::Init(array("popup"));
\Bitrix\Main\UI\Extension::load("ui.buttons.icons");
?>

<div class="analytic-board-config-control">
	<a id="analytic_board_configuration_button" class="ui-btn ui-btn-light-border ui-btn-icon-setting"></a>
</div>


<script type="text/javascript">
	BX.message({
		'VISUALCONSTRUCTOR_DASHBOARD_GO_TO_DEFAULT': "<?=\Bitrix\Main\Localization\Loc::getMessage('VISUALCONSTRUCTOR_DASHBOARD_GO_TO_DEFAULT')?>"
	});
	new BX.Report.Analytics.Config.Controls({
		boardId: <?=CUtil::PhpToJSObject($arResult['BOARD_ID'])?>,
		configurationButton: BX('analytic_board_configuration_button')
	});
</script>