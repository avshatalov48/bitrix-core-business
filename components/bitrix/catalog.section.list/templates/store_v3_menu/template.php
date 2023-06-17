<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);
$emptyImagePath = $this->getFolder().'/images/tile-empty.png';

if ($arResult['SECTIONS_COUNT'] > 0)
{
	?>
	<div class="catalog-sections-list-menu">
		<ul class="catalog-sections-list-menu-items">
			<?php
			$sectionEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'SECTION_EDIT');
			$sectionDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'SECTION_DELETE');
			$sectionDeleteParams = [
				'CONFIRM' => Loc::getMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM'),
			];

			foreach ($arResult['SECTIONS'] as &$section)
			{
				$this->addEditAction($section['ID'], $section['EDIT_LINK'], $sectionEdit);
				$this->addDeleteAction($section['ID'], $section['DELETE_LINK'], $sectionDelete, $sectionDeleteParams);
				?>
				<li class="catalog-sections-list-menu-item nav-item" id="<?=$this->getEditAreaId($section['ID'])?>">
					<a href="<?=$section['SECTION_PAGE_URL']?>" class="catalog-sections-list-menu-item-link">
						<span class="catalog-sections-list-menu-item-text"><?=$section['NAME'] ." <i>".$section['ELEMENT_CNT']. "</i>";?></span>
						<?php if($arParams['SHOW_ANGLE'] === 'Y'): ?>
							<span class="catalog-sections-list-menu-item-angle"></span>
						<?php endif; ?>
					</a>
				</li>
				<?php
			}
			unset($section);
			?>
		</ul>
	</div>
	<?php
}
