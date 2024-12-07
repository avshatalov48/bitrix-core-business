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
	style="--bg: #ffffff;"
>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:landing.blocks.mp_widget.active_employees',
	'',
	[],
);
?>
</section>
