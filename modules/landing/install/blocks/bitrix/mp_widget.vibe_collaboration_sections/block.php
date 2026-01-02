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
	class="landing-block g-pl-30 g-pr-30 g-pt-30 g-pb-25 g-cursor-default g-bg g-pos-rel"
	style="z-index: 3; --bg: #ffffff; --bg-size: cover; --bg-attachment: scroll;"
>
	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:landing.blocks.mp_widget.vibe_collaboration',
		'',
		[],
	);
	?>
</section>
