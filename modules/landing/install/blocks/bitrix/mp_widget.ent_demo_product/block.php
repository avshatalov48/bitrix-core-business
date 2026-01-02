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
	class="landing-block g-bg g-pl-25 g-pr-25 g-pt-10 g-pb-15"
	style="--bg: #ffffff;"
>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:landing.blocks.mp_widget.enterprise_west',
	'schedule',
	[],
);
?>
</section>
