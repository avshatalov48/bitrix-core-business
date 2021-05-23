<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$this->setFrameMode(true);

$items = $arResult['ITEMS'];
?>

<div class="landing-block-menu-container collapse navbar-collapse align-items-center flex-sm-row" id="navBar">
	<ul class="landing-block-node-menu-list text-uppercase g-font-weight-700 g-font-size-15 navbar-nav navbar-nav-store w-100 flex-wrap">
		<?foreach ($items as $item):?>
			<li class="landing-block-node-menu-list-item landing-block-node-menu-list-item-card nav-item">
				<a class="landing-block-node-menu-list-item-link nav-link nav-link-store g-bg-white-opacity-0_2--hover g-color-white" href="<?= $item['SECTION_PAGE_URL'];?>">
					<?= $item['NAME'];?>
				</a>
			</li>
		<?endforeach;?>
	</ul>
</div>