<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
?>

<section class="landing-block g-pt-15 g-pb-15 g-pl-15 g-pr-15">
	<div class="container">
		<form class="landing-block-node-form input-group">
			<div class="landing-block-node-input-container form-control g-brd-gray-light-v3 g-brd-primary--focus g-color-gray-dark-v4 g-bg-white g-px-20 g-height-45">
				<input class="g-brd-none g-bg-transparent form-control g-px-0 g-font-size-12" type="text" name="q">
			</div>
			<div class="landing-block-node-button-container input-group-append g-z-index-4 g-bg-gray-light-v4 g-bg-gray-light-v5--hover g-color-gray-dark-v4 g-color-primary--hover">
				<button class="btn g-font-weight-700 g-font-size-13 text-uppercase g-pl-20 g-pr-20"
						type="submit">
					<div class="d-none d-md-block"> MESS[LANDING_BLOCK_SEARCH] </div>
					<i class="d-block d-md-none fa fa-search"></i>
				</button>
			</div>
		</form>
	</div>
</section>