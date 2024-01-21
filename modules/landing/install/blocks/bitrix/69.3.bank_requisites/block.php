<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var \CMain $APPLICATION
 */
?>

<section class="landing-block g-bg g-pt-25 g-pb-25" style="--bg: #eef2f4;">
	<div class="landing-block-container g-max-width-container g-color" style="--color: #333333;">
		<?php
		$APPLICATION->IncludeComponent(
			'bitrix:landing.blocks.crm_requisites',
			'',
			[
				'TYPE' => 'BANK',
				'BANK_REQUISITE' => '',
			]
		);
		?>
	</div>
</section>
