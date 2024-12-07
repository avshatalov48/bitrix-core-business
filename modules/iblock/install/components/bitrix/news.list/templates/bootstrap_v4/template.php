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

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-'.$arParams['TEMPLATE_THEME'] : '';
?>
<div class="row news-list<?=$themeClass?>">
	<div class="col">
		<?if($arParams["DISPLAY_TOP_PAGER"]):?>
			<?=$arResult["NAV_STRING"]?><br />
		<?endif;?>

		<div class="row">
			<?foreach($arResult["ITEMS"] as $arItem):?>
				<?
					$this->AddEditAction(
						$arItem['ID'],
						$arItem['EDIT_LINK'],
						CIBlock::GetArrayByID(
							$arItem["IBLOCK_ID"],
							"ELEMENT_EDIT"
						)
					);
					$this->AddDeleteAction(
						$arItem['ID'],
						$arItem['DELETE_LINK'],
						CIBlock::GetArrayByID(
							$arItem["IBLOCK_ID"],
							"ELEMENT_DELETE"),
						array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM'))
					);
				?>
				<div class="news-list-item mb-2 col-sm" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
					<div class="card">
						<?if($arParams["DISPLAY_PICTURE"]!="N"):
							if ($arItem["VIDEO"] ?? null)
							{
								?>
								<div class="news-list-item-embed-video embed-responsive embed-responsive-16by9">
									<iframe
										class="embed-responsive-item"
										src="<?echo $arItem["VIDEO"]?>"
										frameborder="0"
										allowfullscreen=""
										></iframe>
								</div>
								<?
							}
							elseif ($arItem["SOUND_CLOUD"] ?? null)
							{
								?>
								<div class="news-list-item-embed-audio embed-responsive embed-responsive-16by9">
									<iframe
										class="embed-responsive-item"
										width="100%"
										scrolling="no"
										frameborder="no"
										src="https://w.soundcloud.com/player/?url=<?echo urlencode($arItem["SOUND_CLOUD"])?>&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false"
										></iframe>
								</div>
								<?
							}
							elseif (isset($arItem["SLIDER"]) && is_array($arItem["SLIDER"]) && count($arItem["SLIDER"]) > 1)
							{
								?>
								<div class="news-list-item-embed-slider">
									<div class="news-list-slider-container" style="width: <?
									echo count($arItem["SLIDER"]) * 100 ?>%;left: 0;">
										<?
										foreach ($arItem["SLIDER"] as $file):?>
											<div class="news-list-slider-slide">
												<img src="<?= $file["SRC"] ?>" alt="<?= $file["DESCRIPTION"] ?>">
											</div>
										<?endforeach ?>
									</div>
									<div class="news-list-slider-arrow-container-left">
										<div class="news-list-slider-arrow"><i class="fa fa-angle-left"></i></div>
									</div>
									<div class="news-list-slider-arrow-container-right">
										<div class="news-list-slider-arrow"><i class="fa fa-angle-right"></i></div>
									</div>
									<ul class="news-list-slider-control">
										<?
										foreach ($arItem["SLIDER"] as $i => $file):?>
											<li rel="<?= ($i + 1) ?>" <?
											if (!$i)
												echo 'class="current"' ?>><span></span></li>
										<?endforeach ?>
									</ul>
								</div>
								<script>
								BX.ready(function () {
									new JCNewsSlider('<?=CUtil::JSEscape($this->GetEditAreaId($arItem['ID']));?>', {
										imagesContainerClassName: 'news-list-slider-container',
										leftArrowClassName: 'news-list-slider-arrow-container-left',
										rightArrowClassName: 'news-list-slider-arrow-container-right',
										controlContainerClassName: 'news-list-slider-control'
									});
								});
							</script>
								<?
							}
							else if ($arItem["SLIDER"] ?? null)
							{
								?>
								<div class="news-list-item-embed-img">
									<? if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"]))
									{
										?>
										<a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
											<img
												class="card-img-top"
												src="<?= $arItem["SLIDER"][0]["SRC"] ?>"
												width="<?= $arItem["SLIDER"][0]["WIDTH"] ?>"
												height="<?= $arItem["SLIDER"][0]["HEIGHT"] ?>"
												alt="<?= $arItem["SLIDER"][0]["ALT"] ?>"
												title="<?= $arItem["SLIDER"][0]["TITLE"] ?>"
											/>
										</a>
										<?
									}
									else
									{
										?>
										<img
											class="card-img-top"
											src="<?= $arItem["SLIDER"][0]["SRC"] ?>"
											width="<?= $arItem["SLIDER"][0]["WIDTH"] ?>"
											height="<?= $arItem["SLIDER"][0]["HEIGHT"] ?>"
											alt="<?= $arItem["SLIDER"][0]["ALT"] ?>"
											title="<?= $arItem["SLIDER"][0]["TITLE"] ?>"
										/>
										<?
									}
									?>
								</div>
								<?
							}
							elseif (isset($arItem["PREVIEW_PICTURE"]) && is_array($arItem["PREVIEW_PICTURE"]))
							{
								if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"]))
								{
									?>
									<a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
										<img
											class="card-img-top"
											src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
											alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"
											title="<?= $arItem["PREVIEW_PICTURE"]["TITLE"] ?>"
										/>
									</a>
									<?
								}
								else
								{
									?>
									<img
										src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
										class="card-img-top"
										alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"
										title="<?= $arItem["PREVIEW_PICTURE"]["TITLE"] ?>"
									/>
								<?
								}
							}
							?>

						<?endif;?>

						<div class="card-body">

							<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
								<h4 class="card-title">
									<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
										<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><?echo $arItem["NAME"]?></a>
									<?else:?>
										<?echo $arItem["NAME"]?>
									<?endif;?>
								</h4>
							<?endif;?>

							<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
								<p class="card-text"><?echo $arItem["PREVIEW_TEXT"];?></p>
							<?endif;?>

							<?foreach($arItem["FIELDS"] as $code=>$value):?>
								<?if($code == "SHOW_COUNTER"):?>
									<div class="news-list-view news-list-post-params">
										<span class="news-list-icon news-list-icon-eye"></span>
										<span class="news-list-param"><?=GetMessage("IBLOCK_FIELD_".$code)?>: </span>
										<span class="news-list-value"><?=intval($value);?></span>
									</div>
								<?elseif(
									$value
									&& (
										$code == "SHOW_COUNTER_START"
										|| $code == "DATE_ACTIVE_FROM"
										|| $code == "ACTIVE_FROM"
										|| $code == "DATE_ACTIVE_TO"
										|| $code == "ACTIVE_TO"
										|| $code == "DATE_CREATE"
										|| $code == "TIMESTAMP_X"
									)
								):?>
									<?
									$value = CIBlockFormatProperties::DateFormat($arParams["ACTIVE_DATE_FORMAT"], MakeTimeStamp($value, CSite::GetDateFormat()));
									?>
									<div class="news-list-view news-list-post-params">
										<span class="news-list-icon news-list-icon-calendar"></span>
										<span class="news-list-param"><?=GetMessage("IBLOCK_FIELD_".$code)?>: </span>
										<span class="news-list-value"><?=$value;?></span>
									</div>
								<?elseif($code == "TAGS" && $value):?>
									<div class="news-list-view news-list-post-params">
										<span class="news-list-icon news-list-icon-tag"></span>
										<span class="news-list-param"><?=GetMessage("IBLOCK_FIELD_".$code)?>:</span>
										<span class="news-list-value"><?=$value;?></span>
									</div>
								<?elseif(
									$value
									&& (
										$code == "CREATED_USER_NAME"
										|| $code == "USER_NAME"
									)
								):?>
									<div class="news-list-view news-list-post-params">
										<span class="news-list-icon news-list-icon-user"></span>
										<span class="news-list-param"><?=GetMessage("IBLOCK_FIELD_".$code)?>:</span>
										<span class="news-list-value"><?=$value;?></span>
									</div>
								<?elseif ($value != ""):?>
									<div class="news-list-view news-list-post-params">
										<span class="news-list-icon"></span>
										<span class="news-list-param"><?=GetMessage("IBLOCK_FIELD_".$code)?>:</span>
										<span class="news-list-value"><?=$value;?></span>
									</div>
								<?endif;?>
							<?endforeach;?>

							<?foreach($arItem["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
								<?
								if(is_array($arProperty["DISPLAY_VALUE"]))
									$value = implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
								else
									$value = $arProperty["DISPLAY_VALUE"];
								?>
								<?if($arProperty["CODE"] == "FORUM_MESSAGE_CNT"):?>
									<div class="news-list-view news-list-post-params">
										<span class="news-list-icon news-list-icon-comments"></span>
										<span class="news-list-param"><?=$arProperty["NAME"]?>:<?=$value;?></span>
										<span class="news-list-value"><?=$value;?></span>
									</div>
								<?elseif ($value != ""):?>
									<div class="news-list-view news-list-post-params">
										<span class="news-list-icon"></span>
										<span class="news-list-param"><?=$arProperty["NAME"]?>:</span>
										<span class="news-list-value"><?=$value;?></span>
									</div>
								<?endif;?>
							<?endforeach;?>
							<div class="d-flex justify-content-between">

								<?if($arParams["DISPLAY_DATE"]!="N" && $arItem["DISPLAY_ACTIVE_FROM"]):?>
									<div class="news-list-view news-list-post-params">
										<span class="news-list-icon news-list-icon-calendar"></span>
										<span class="news-list-param"><?echo $arItem["DISPLAY_ACTIVE_FROM"]?></span>
									</div>
								<?endif?>

								<?if($arParams["USE_RATING"]=="Y"):?>
									<div>
										<?$APPLICATION->IncludeComponent(
											"bitrix:iblock.vote",
											"bootstrap_v4",
											Array(
												"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
												"IBLOCK_ID" => $arParams["IBLOCK_ID"],
												"ELEMENT_ID" => $arItem["ID"],
												"MAX_VOTE" => $arParams["MAX_VOTE"],
												"VOTE_NAMES" => $arParams["VOTE_NAMES"],
												"CACHE_TYPE" => $arParams["CACHE_TYPE"],
												"CACHE_TIME" => $arParams["CACHE_TIME"],
												"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
												"SHOW_RATING" => "N",
											),
											$component
										);?>
									</div>
								<?endif?>
							</div>
							<div class="d-flex justify-content-between align-items-center">
								<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
									<div class="news-list-more">
										<a class="btn btn-primary btn-sm" href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><?echo GetMessage("CT_BNL_GOTO_DETAIL")?></a>
									</div>
								<?endif;?>
								<? if ($arParams["USE_SHARE"] == "Y")
								{
									?>
									<div class="text-right">
										<?
										$APPLICATION->IncludeComponent(
											"bitrix:main.share",
											$arParams["SHARE_TEMPLATE"],
											[
												"HANDLERS" => $arParams["SHARE_HANDLERS"],
												"PAGE_URL" => $arResult["~DETAIL_PAGE_URL"],
												"PAGE_TITLE" => $arResult["~NAME"],
												"SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
												"SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
												"HIDE" => $arParams["SHARE_HIDE"],
											],
											$component,
											["HIDE_ICONS" => "Y"]
										);
										?>
									</div>
									<?
								}
								?>
							</div>
						</div>
					</div>
				</div>
			<?endforeach;?>
		</div>

		<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
			<?=$arResult["NAV_STRING"]?>
		<?endif;?>
	</div>
</div>
