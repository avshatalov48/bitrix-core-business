<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="marketplace-banner">
	<a href="http://marketplace.1c-bitrix.ru/?utm_source=demoshop&utm_medium=referral&utm_campaign=marketplace" target="_blank" class="marketplace-banner-link">
		<?=GetMessage("ESHOP_BANNER_CONTENT")?>
	</a>
	<?if ($USER->IsAdmin()):?>
	<a href="javascript:void(0)"
	   class="marketplace-banner-close"
	   onclick="eshopBannerClose(this.parentNode);">
			<?=GetMessage("ESHOP_BANNER_CLOSE")?>
	</a>

	<script>
		function eshopBannerClose(banner)
		{
			BX.ajax.post(
				'<?=POST_FORM_ACTION_URI?>',
				{
					sessid: BX.bitrix_sessid(),
					action: 'eshopBannerClose'
				},
				function(result)
				{
					banner.style.display = "none";
				}
			);
		}
	</script>
	<?endif?>
</div>