<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
?>

<section class="landing-block g-pt-15 g-pb-15 g-pl-15 g-pr-15 g-theme-bitrix-bg-dark-v2">
	<div class="container">
		<form class="landing-block-node-form input-group">
			<div class="landing-block-node-input-container form-control g-brd-gray-light-v3 g-brd-primary--focus g-color-white g-px-20 g-height-45 g-theme-bitrix-bg-dark-v2">
				<input class="g-brd-none g-bg-transparent form-control g-px-0 g-py-0 g-font-size-16 g-height-100x " type="text" name="q" placeholder=" MESS[LANDING_BLOCK_PLACEHOLDER] ">
			</div>
			<div class="landing-block-node-button-container input-group-append g-z-index-4 g-bg-gray-light-v4 g-bg-gray-light-v5--hover g-color-black g-color-black--hover g-font-size-15">
				<button class="g-brd-1 g-brd-style-solid g-brd-transparent g-bg-transparent text-uppercase g-pl-20 g-pr-20 g-letter-spacing-2"
						type="submit">
					<div class="d-none d-md-block"> MESS[LANDING_BLOCK_SEARCH] </div>
					<i class="d-block d-md-none fa fa-search"></i>
				</button>
			</div>
		</form>
	</div>
</section>