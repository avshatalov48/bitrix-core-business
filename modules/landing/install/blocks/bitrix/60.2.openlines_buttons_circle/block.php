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
	<div class="landing-block-node-container g-flex-centered container">
		<?$APPLICATION->IncludeComponent(
			'bitrix:landing.blocks.openlines',
			'circle_buttons',
			[
				'BUTTON_ID' => $classBlock->get('BUTTON_ID')
			]
		);?>
	</div>
</section>
