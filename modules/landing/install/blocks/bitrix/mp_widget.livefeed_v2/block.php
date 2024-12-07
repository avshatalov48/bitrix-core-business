<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var \CMain $APPLICATION
 */
?>

<section
	class="landing-block g-bg"
	style="--bg: var(--primary-opacity-0_9);"
>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:landing.blocks.mp_widget.livefeed',
	'v2',
	[],
);
?>
</section>
