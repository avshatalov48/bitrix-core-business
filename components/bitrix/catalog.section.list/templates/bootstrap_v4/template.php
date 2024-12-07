<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$arViewModeList = $arResult['VIEW_MODE_LIST'];

$arViewStyles = array(
	'LIST' => array(
		'CONT' => 'bx_sitemap',
		'TITLE' => 'bx_sitemap_title',
		'LIST' => 'catalog-section-list-list',
	),
	'LINE' => array(
		'TITLE' => 'catalog-section-list-item-title',
		'LIST' =>  'catalog-section-list-line-list mb-4',
		'EMPTY_IMG' => $this->GetFolder().'/images/line-empty.png'
	),
	'TEXT' => array(
		'TITLE' => 'catalog-section-list-item-title',
		'LIST' =>  'catalog-section-list-text-list row mb-4'
	),
	'TILE' => array(
		'TITLE' => 'catalog-section-list-item-title',
		'LIST' =>  'catalog-section-list-tile-list row mb-4',
		'EMPTY_IMG' => $this->GetFolder().'/images/tile-empty.png'
	)
);
$arCurView = $arViewStyles[$arParams['VIEW_MODE']];

switch ($arParams['LIST_COLUMNS_COUNT'])
{
	case "1":
		$listColumsClass = "col-12";
		break;
	case "2":
		$listColumsClass = "col-6";
		break;
	case "3":
		$listColumsClass = "col-sm-4 col-6";
		break;
	case "4":
		$listColumsClass = "col-md-3 col-sm-4 col-6";
		break;
	case "6":
		$listColumsClass = "col-lg-2 col-md-3 col-sm-4 col-6";
		break;
	case "12":
		$listColumsClass = "col-lg-1 col-md-3 col-sm-4 col-6";
		break;
}

$strSectionEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_EDIT");
$strSectionDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_DELETE");
$arSectionDeleteParams = array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM'));

?>
<div class="row mb-4">
	<div class="col">
		<? if ('Y' == $arParams['SHOW_PARENT_NAME'] && 0 < $arResult['SECTION']['ID'])
		{
			$this->AddEditAction($arResult['SECTION']['ID'], $arResult['SECTION']['EDIT_LINK'], $strSectionEdit);
			$this->AddDeleteAction($arResult['SECTION']['ID'], $arResult['SECTION']['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);

			?><h2 class="mb-3" id="<? echo $this->GetEditAreaId($arResult['SECTION']['ID']); ?>" ><?
			echo (
			isset($arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"]) && $arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] != ""
				? $arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"]
				: $arResult['SECTION']['NAME']
			);
			?>
			</h2><?
		}

		if (0 < $arResult["SECTIONS_COUNT"])
		{
		?><ul class="<? echo $arCurView['LIST']; ?>"><?

			switch ($arParams['VIEW_MODE'])
			{
				case 'LINE':
					foreach ($arResult['SECTIONS'] as &$arSection)
					{
						$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
						$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);

						if (false === $arSection['PICTURE'])
						{
							$altValue = (string)($arSection['IPROPERTY_VALUES']['SECTION_PICTURE_FILE_ALT'] ?? '');
							if ($altValue === '')
							{
								$altValue = $arSection['NAME'];
							}
							$titleValue = (string)($arSection['IPROPERTY_VALUES']['SECTION_PICTURE_FILE_TITLE'] ?? '');
							if ($titleValue === '')
							{
								$titleValue = $arSection['NAME'];
							}
							$arSection['PICTURE'] = array(
								'SRC' => $arCurView['EMPTY_IMG'],
								'ALT' => $altValue,
								'TITLE' => $titleValue,
							);
							unset($titleValue, $altValue);
						}
							?>
							<li id="<? echo $this->GetEditAreaId($arSection['ID']); ?>" class="catalog-section-list-item">
								<div class="catalog-section-list-line-img-container">
									<a
										href="<? echo $arSection['SECTION_PAGE_URL']; ?>"
										class="catalog-section-list-item-img"
										style="background-image:url('<? echo $arSection['PICTURE']['SRC']; ?>');"
										title="<? echo $arSection['PICTURE']['TITLE']; ?>"
									></a>
								</div>
								<div class="catalog-section-list-item-inner">
									<h3 class="catalog-section-list-item-title">
										<a class="catalog-section-list-item-link" href="<? echo $arSection['SECTION_PAGE_URL']; ?>">
											<? echo $arSection['NAME']; ?>
										</a>
										<? if ($arParams["COUNT_ELEMENTS"] && $arSection['ELEMENT_CNT'] !== null)
										{
											?>
											<span class="catalog-section-list-item-counter">(<? echo $arSection['ELEMENT_CNT']; ?>)</span>
											<?
										}
										?>
									</h3>
									<? if ('' != $arSection['DESCRIPTION'])
									{
										?>
										<p class="catalog-section-list-item-description"><? echo $arSection['DESCRIPTION']; ?></p>
										<?
									}
									?>
								</div>

						</li><?
					}
					unset($arSection);
					break;

				case 'TEXT':
					foreach ($arResult['SECTIONS'] as &$arSection)
					{
						$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
						$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);

						?>
						<li id="<? echo $this->GetEditAreaId($arSection['ID']); ?>" class="<?=$listColumsClass?> catalog-section-list-item">
							<div class="catalog-section-list-item-inner">
								<h3 class="catalog-section-list-item-title">
									<a class="catalog-section-list-item-link" href="<? echo $arSection['SECTION_PAGE_URL']; ?>">
										<? echo $arSection['NAME']; ?>
									</a>
									<? if ($arParams["COUNT_ELEMENTS"] && $arSection['ELEMENT_CNT'] !== null)
									{
										?>
										<span class="catalog-section-list-item-counter">(<? echo $arSection['ELEMENT_CNT']; ?>)</span>
										<?
									}
									?>
								</h3>
							</div>
						</li><?
					}
					unset($arSection);
					break;

				case 'TILE':
					foreach ($arResult['SECTIONS'] as &$arSection)
					{
						$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
						$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);

						if (false === $arSection['PICTURE'])
						{
							$altValue = (string)($arSection['IPROPERTY_VALUES']['SECTION_PICTURE_FILE_ALT'] ?? '');
							if ($altValue === '')
							{
								$altValue = $arSection['NAME'];
							}
							$titleValue = (string)($arSection['IPROPERTY_VALUES']['SECTION_PICTURE_FILE_TITLE'] ?? '');
							if ($titleValue === '')
							{
								$titleValue = $arSection['NAME'];
							}
							$arSection['PICTURE'] = array(
								'SRC' => $arCurView['EMPTY_IMG'],
								'ALT' => $altValue,
								'TITLE' => $titleValue,
							);
							unset($titleValue, $altValue);
						}
							?>
							<li id="<? echo $this->GetEditAreaId($arSection['ID']); ?>"  class="<?=$listColumsClass?> catalog-section-list-item">
								<div class="catalog-section-list-tile-img-container">
									<a
										href="<? echo $arSection['SECTION_PAGE_URL']; ?>"
										class="catalog-section-list-item-img"
										style="background-image:url('<? echo $arSection['PICTURE']['SRC']; ?>');"
										title="<? echo $arSection['PICTURE']['TITLE']; ?>"
										></a>
								</div>
								<? if ('Y' != $arParams['HIDE_SECTION_NAME'])
								{
									?>
										<div class="catalog-section-list-item-inner">
											<h3 class="catalog-section-list-item-title">
												<a class="catalog-section-list-item-link" href="<? echo $arSection['SECTION_PAGE_URL']; ?>">
													<? echo $arSection['NAME']; ?>
												</a>
												<? if ($arParams["COUNT_ELEMENTS"] && $arSection['ELEMENT_CNT'] !== null)
												{
													?>
													<span class="catalog-section-list-item-counter">(<? echo $arSection['ELEMENT_CNT']; ?>)</span>
													<?
												}
												?>
											</h3>
										</div>
									<?
								}
								?>
							</li>
						<?
					}
					unset($arSection);
					break;

				case 'LIST':
					$intCurrentDepth = 1;
					$boolFirst = true;
					foreach ($arResult['SECTIONS'] as &$arSection)
					{
						$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
						$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);

						if ($intCurrentDepth < $arSection['RELATIVE_DEPTH_LEVEL'])
						{
							if (0 < $intCurrentDepth)
								echo "\n",str_repeat("\t", $arSection['RELATIVE_DEPTH_LEVEL']),'<ul>';
						}
						elseif ($intCurrentDepth == $arSection['RELATIVE_DEPTH_LEVEL'])
						{
							if (!$boolFirst)
								echo '</li>';
						}
						else
						{
							while ($intCurrentDepth > $arSection['RELATIVE_DEPTH_LEVEL'])
							{
								echo '</li>',"\n",str_repeat("\t", $intCurrentDepth),'</ul>',"\n",str_repeat("\t", $intCurrentDepth-1);
								$intCurrentDepth--;
							}
							echo str_repeat("\t", $intCurrentDepth-1),'</li>';
						}

						echo (!$boolFirst ? "\n" : ''),str_repeat("\t", $arSection['RELATIVE_DEPTH_LEVEL']);
						?>
						<li class="catalog-section-list-item" id="<?=$this->GetEditAreaId($arSection['ID']);?>">
							<h3 class="catalog-section-list-list-title">
								<a class="catalog-section-list-list-link" href="<? echo $arSection["SECTION_PAGE_URL"]; ?>"><? echo $arSection["NAME"];?><?
									if ($arParams["COUNT_ELEMENTS"] && $arSection['ELEMENT_CNT'] !== null)
									{
										?> <span>(<? echo $arSection["ELEMENT_CNT"]; ?>)</span><?
									}
									?>
								</a>
							</h3>
						<?

						$intCurrentDepth = $arSection['RELATIVE_DEPTH_LEVEL'];
						$boolFirst = false;
					}
					unset($arSection);
					while ($intCurrentDepth > 1)
					{
						echo '</li>',"\n",str_repeat("\t", $intCurrentDepth),'</ul>',"\n",str_repeat("\t", $intCurrentDepth-1);
						$intCurrentDepth--;
					}
					if ($intCurrentDepth > 0)
					{
						echo '</li>',"\n";
					}
					break;
			}
			?></ul><?
		}
		?>
	</div>
</div>