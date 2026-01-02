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
	class="landing-block g-bg g-pt-65 g-pr-40 g-pb-65 g-pl-40"
	style="--bg: #060138;"
>
	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:landing.blocks.mp_widget.vibe_collaboration',
		'v2',
		[],
	);
	?>
</section>
