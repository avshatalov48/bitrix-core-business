<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
global $APPLICATION;
?>

<section class="landing-block g-pt-30 g-pb-30 js-animation slideInLeft u-block-border-none">
	<div class="container">
		<?php $APPLICATION->IncludeComponent(
			'bitrix:landing.blocks.crm_contacts',
			'',
			[
				'BUTTON_POSITION' => 'right',
				'TEMPLATE_MODE' => 'lightmode',
			]
		); ?>
	</div>
</section>