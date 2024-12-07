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
	class="landing-block g-bg-image"
	style="--bg: ;--bg-url: url('https://cdn.bitrix24.site/bitrix/images/landing/widget/new_employees_v2/bg.jpg');--bg-url-2x: url('https://cdn.bitrix24.site/bitrix/images/landing/widget/new_employees_v2/bg.jpg');--bg-overlay: hsla(0, 0%, 100%, 0.9);--bg-size: cover;--bg-attachment: fixed;"
>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:landing.blocks.mp_widget.new_employees',
	'v2',
	[

	],
);
?>
</section>
