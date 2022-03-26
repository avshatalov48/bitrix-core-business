<?php

use Bitrix\Main\Web\Json;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var Tariff24Block $classBlock
 */
?>

<section class="landing-block g-pt-0 g-p-20 g-pl-0 g-pr-0">
	<a hidden class="landing-block-link-1"><p></p></a>
	<a hidden class="landing-block-link-2"><p></p></a>
	<a hidden class="landing-block-link-3"><p></p></a>

	<?php $APPLICATION->IncludeComponent(
		'bitrix:landing.blocks.tariffs',
		'',
	); ?>
</section>