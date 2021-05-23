<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<div class="landing-item landing-item-add-developer-site">
	<span class="landing-item-inner" data-href="<?=$arResult["MASTER_PATH"]?>">
		<span class="landing-item-add-new-inner">
			<span class="landing-item-add-icon"></span>
			<span class="landing-item-text">
				<?=Loc::getMessage('SALE_BSMB_DEVELOPER_SITE_BUTTON_TITLE');?>
			</span>
		</span>
	</span>
</div>

<script type="text/javascript">
	BX.bind(document.querySelector('.landing-item-add-developer-site span.landing-item-inner'), 'click', function(event) {
		BX.SidePanel.Instance.open(event.currentTarget.dataset.href, {
			allowChangeHistory: false
		});
	});
</script>