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
?>

<div class="landing-agreement-shadow">
	<div id="<?= $id;?>" style="display: none;">
		<div class="landing-agreement-popup-content">
			<form method="POST" action="<?= POST_FORM_ACTION_URI;?>" id="<?= $id;?>-form">
				<input type="hidden" name="action" value="accept_agreement" />
				<?= bitrix_sessid_post();?>
				<?= $arResult['AGREEMENT']['TEXT'];?>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	BX.ready(function()
	{
		var accept = false;
		var oPopup = BX.PopupWindowManager.create('<?= $id;?>', null, {
			content: BX('<?= $id;?>'),
			titleBar: {content: BX.create('span', {html: ''})},
			closeIcon : true,
			closeByEsc : true,
			draggable: true,
			lightShadow: true,
			overlay: true,
			className: 'landing-agreement-popup-wrapper',
			buttons: [
				new BX.PopupWindowButton({
					text: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ACCEPT'));?>',
					className: 'popup-window-button-accept',
					events: {
						click: function()
						{
							accept = true;
							BX.PopupWindowManager.getCurrentPopup().close();
							BX('<?= $id;?>-form').submit();
						}
					}
				})
			],
			events: {
				onPopupClose: function()
				{
					if (!accept)
					{
						top.window.location.href = '<?= \CUtil::jsEscape(SITE_DIR);?>';
					}
				}
			}
		});
		oPopup.setTitleBar('<?= \CUtil::jsEscape($arResult['AGREEMENT']['NAME']);?>');
		oPopup.show();
	});
</script>