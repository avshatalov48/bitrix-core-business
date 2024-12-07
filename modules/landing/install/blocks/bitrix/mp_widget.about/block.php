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
	style="--bg: ;--bg-url: url('https://cdn.bitrix24.site/bitrix/images/landing/widget/about/bg.jpg');--bg-url-2x: url('https://cdn.bitrix24.site/bitrix/images/landing/widget/about/bg.jpg');--bg-overlay: hsla(212, 18%, 18%, 0.75);--bg-size: cover;--bg-attachment: scroll;"
>
	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:landing.blocks.mp_widget.about',
		'',
		[],
	);
	?>
</section>
