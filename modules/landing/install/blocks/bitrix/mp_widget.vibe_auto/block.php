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
	class="landing-block g-bg g-pl-20 g-pr-20 g-pt-10 g-pb-10"
	style="--bg: #ffffff;"
>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:landing.blocks.mp_widget.vibe_auto',
	'',
	[],
);
?>
</section>
