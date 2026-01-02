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
	class="landing-block g-pt-90 g-pr-40 g-pb-100 g-pl-40 g-bg"
	style="--bg: #ffffff;"
>
	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:landing.blocks.mp_widget.apps',
		'collaboration',
		[],
	);
	?>
</section>
