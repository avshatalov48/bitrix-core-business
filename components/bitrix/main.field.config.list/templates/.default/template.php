<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load("ui.alerts");

$hasErrors = (!empty($arResult['errors']) && is_array($arResult['errors']));

?>
<div class="main-user-field-list-container">
	<div class="user-field-list-errors-container ui-alert ui-alert-danger"<?= (!$hasErrors ? ' style="display: none;"' : '') ;?>>
		<?php if($hasErrors): ?>
			<?php foreach($arResult['errors'] as $error): ?>
				<div class="main-user-field-error ui-alert-message"><?= htmlspecialcharsbx($error); ?></div>
			<?php endforeach;?>
		<?php return;
		endif;?>
		<span class="ui-alert-close-btn" onclick="this.parentNode.style.display = 'none';"></span>
	</div>
	<div class="main-user-field-list-grid">
		<?php
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			"bitrix:main.ui.grid",
			"",
			$arResult['grid']
		);
		?>
	</div>
<script>
	BX.ready(function()
	{
		var gridId = '<?= CUtil::JSEscape($arResult['grid']['GRID_ID']); ?>';
		if(gridId.length > 0 && BX.Main.gridManager)
		{
			BX.addCustomEvent('SidePanel.Slider:onMessage', function(message)
			{
				if(message.getEventId() === 'userfield-list-update')
				{
					BX.Main.gridManager.reload(gridId);
				}
			});
		}
	});
</script>
</div>