<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var OlWidgetBlock $classBlock
 */
?>
<section class="landing-block g-pt-30 g-pb-30">
	<div class="landing-block-node-container container">
		<h3 class="landing-block-node-title g-mb-10">Contact us</h3>
		<?$APPLICATION->IncludeComponent(
			'bitrix:landing.blocks.openlines',
			'',
			[
				'BUTTON_ID' => $classBlock->get('BUTTON_ID')
			]
		);?>
	</div>
</section>
