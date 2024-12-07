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
	style="--bg: hsla(0, 0%, 100%, 0.9);--bg-url: ;--bg-url-2x: ;--bg-overlay: ;--bg-size: ;--bg-attachment: ;background-image: ;"
>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:landing.blocks.mp_widget.bp',
	'v2',
	[

	],
);
?>
</section>
