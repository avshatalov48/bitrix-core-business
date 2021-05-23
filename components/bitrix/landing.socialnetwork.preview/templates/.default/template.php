<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$preview = $arResult['PREVIEW'];
$this->addExternalCss('/bitrix/css/main/font-awesome.css');
?>

<div class="urlpreview_landing">
	<div class="urlpreview_landing__frame">
		<div class="urlpreview_landing__container">
			<?if ($preview['PICTURE']):?>
				<div class="urlpreview_landing__image">
					<a href="<?= \htmlspecialcharsbx($preview['URL']);?>" target="_blank">
						<img src="<?= $preview['PICTURE'];?>" alt="<?= \htmlspecialcharsbx($preview['TITLE']);?>" >
					</a>
				</div>
			<?endif?>
		</div>
		<div class="urlpreview_landing__title">
			<?= \htmlspecialcharsbx($preview['TITLE']);?>
		</div>
		<?if ($preview['DESCRIPTION']):?>
			<div class="urlpreview_landing__description">
				<?= \htmlspecialcharsbx($preview['DESCRIPTION']);?>
			</div>
		<?endif;?>
		<div class="urlpreview_landing__clearfix"></div>
		<div class="urlpreview_landing__bottom">
			<a href="<?= \htmlspecialcharsbx($preview['URL']);?>">
				<?= Loc::getMessage('LANDING_TPL_MORE');?>
			</a>
		</div>
	</div>
</div>