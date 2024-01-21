<?php

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(['ui.design-tokens', 'ui.fonts.opensans']);
?>
<div class="sonet-entity-error">
	<div class="sonet-entity-error-inner">
		<div class="sonet-entity-error-title"><?= $arResult['TITLE'] ?></div>
		<div class="sonet-entity-error-subtitle"><?= $arResult['DESCRIPTION'] ?></div>
		<div class="sonet-entity-error-img">
			<div class="sonet-entity-error-img-inner"></div>
		</div>
	</div>
</div>

<?php if ($arResult['HELP_LINK']): ?>
	<script>
		BX.ready(() => {
			const link = document.querySelector('#sonet-helper-link-error');

			if (link)
			{
				link.addEventListener('click', () => {
					top.BX.Helper.show();
				});
			}
		})
	</script>
<?php endif; ?>
