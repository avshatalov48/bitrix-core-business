<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>

<section class="landing-block g-pt-0 g-pb-0 g-pl-0 g-pr-0">
	<? $APPLICATION->IncludeComponent(
		'bitrix:landing.blocks.html',
		'',
		array(
			'ONLY_PAYED' => 'Y'
		)
	); ?>
</section>
