<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

if (empty($arResult['AGREEMENT']))
{
	return;
}

use \Bitrix\Main\Localization\Loc;

$id = 'landing-agreement-popup';
$pageName = $this->getPageName();
?>

<div id="<?= $id;?>" style="display: none;">
	<div class="<?= $id;?>-content">
		<form method="POST" action="<?= str_replace('IS_AJAX=Y', '', POST_FORM_ACTION_URI);?>" id="<?= $id;?>-form">
			<input type="hidden" name="action" value="accept_agreement" />
			<?= bitrix_sessid_post();?>
			<?= $arResult['AGREEMENT']['TEXT'];?>
		</form>
	</div>
</div>

<script>
	var landingAgreementPopup = function(params)
	{
		params = params || {};

		var oPopup = BX.PopupWindowManager.create('<?= $id;?>', null, {
			content: BX('<?= $id;?>'),
			titleBar: {content: BX.create('span', {html: ''})},
			closeIcon : <?= $pageName === 'landing_view' ? 'false' : 'true'?>,
			closeByEsc : <?= $pageName === 'landing_view' ? 'false' : 'true'?>,
			draggable: true,
			lightShadow: true,
			overlay: true,
			className: '<?= $id;?>-wrapper',
			buttons: [
				new BX.PopupWindowButton({
					text: '<?= \CUtil::jsEscape(Loc::getMessage($arResult['AGREEMENT_ACCEPTED'] ? 'LANDING_TPL_ACCEPTED' : 'LANDING_TPL_ACCEPT'));?>',
					className: '<?= $arResult['AGREEMENT_ACCEPTED'] ? 'popup-window-button-cancel' : 'popup-window-button-accept';?>',
					events: {
						click: function()
						{
							BX.PopupWindowManager.getCurrentPopup().close();
							<?if (!$arResult['AGREEMENT_ACCEPTED']):?>
							if (typeof params.success !== 'undefined')
							{
								params.success();
							}
							BX('<?= $id;?>-form').submit();
							<?endif;?>
						}
					}
				})
			]
		});
		oPopup.setTitleBar('<?= \CUtil::jsEscape($arResult['AGREEMENT']['NAME']);?>');
		oPopup.show();
	};
	<?if ($pageName === 'landing_view' && !$arResult['AGREEMENT_ACCEPTED'] && \Bitrix\Landing\Site\Type::isPublicScope()):?>
	landingAgreementPopup();
	<?endif;?>
</script>