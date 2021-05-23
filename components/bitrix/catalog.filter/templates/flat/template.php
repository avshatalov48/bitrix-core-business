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
$this->addExternalCss("/bitrix/css/main/bootstrap.css");
?>
<div class="bx-flat-filter">
<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get">
	<div class="bx-filter-section container-fluid">
		<div class="row"><div class="col-lg-12 bx-filter-title"><?=GetMessage("CT_BCF_FILTER_TITLE")?></div></div>
		<div class="row">
		<?foreach($arResult["ITEMS"] as $arItem):?>
			<?if(array_key_exists("HIDDEN", $arItem)):?>
				<?=$arItem["INPUT"]?>
			<?elseif ($arItem["TYPE"] == "RANGE"):?>
				<div class="col-sm-6 col-md-4 bx-filter-parameters-box active">
					<div class="bx-filter-parameters-box-title"><span><?=$arItem["NAME"]?></span></div>
					<div class="bx-filter-block">
						<div class="row bx-filter-parameters-box-container">
							<div class="col-xs-6 bx-filter-parameters-box-container-block  bx-left">
								<div class="bx-filter-input-container">
									<input
										type="text"
										value="<?=$arItem["INPUT_VALUES"][0]?>"
										name="<?=$arItem["INPUT_NAMES"][0]?>"
										placeholder="<?=GetMessage("CT_BCF_FROM")?>"
									/>
								</div>
							</div>
							<div class="col-xs-6 bx-filter-parameters-box-container-block  bx-right">
								<div class="bx-filter-input-container">
									<input
										type="text"
										value="<?=$arItem["INPUT_VALUES"][1]?>"
										name="<?=$arItem["INPUT_NAMES"][1]?>"
										placeholder="<?=GetMessage("CT_BCF_TO")?>"
									/>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?elseif ($arItem["TYPE"] == "DATE_RANGE"):?>
				<div class="col-sm-6 col-md-4 bx-filter-parameters-box active">
					<div class="bx-filter-parameters-box-title"><span><?=$arItem["NAME"]?></span></div>
					<div class="bx-filter-block">
						<div class="row bx-filter-parameters-box-container">
							<div class="col-xs-6 bx-filter-parameters-box-container-block  bx-left"><div class="bx-filter-input-container bx-filter-calendar-container">
									<?$APPLICATION->IncludeComponent(
										'bitrix:main.calendar',
										'',
										array(
											'FORM_NAME' => $arResult["FILTER_NAME"]."_form",
											'SHOW_INPUT' => 'Y',
											'INPUT_ADDITIONAL_ATTR' => 'class="calendar" placeholder="'.FormatDate("SHORT", $arItem["VALUES"]["MIN"]["VALUE"]).'"',
											'INPUT_NAME' => $arItem["INPUT_NAMES"][0],
											'INPUT_VALUE' => $arItem["INPUT_VALUES"][0],
											'SHOW_TIME' => 'N',
											'HIDE_TIMEBAR' => 'Y',
										),
										null,
										array('HIDE_ICONS' => 'Y')
									);?>
							</div></div>
							<div class="col-xs-6 bx-filter-parameters-box-container-block  bx-right"><div class="bx-filter-input-container bx-filter-calendar-container">
									<?$APPLICATION->IncludeComponent(
										'bitrix:main.calendar',
										'',
										array(
											'FORM_NAME' => $arResult["FILTER_NAME"]."_form",
											'SHOW_INPUT' => 'Y',
											'INPUT_ADDITIONAL_ATTR' => 'class="calendar" placeholder="'.FormatDate("SHORT", $arItem["VALUES"]["MAX"]["VALUE"]).'"',
											'INPUT_NAME' => $arItem["INPUT_NAMES"][1],
											'INPUT_VALUE' => $arItem["INPUT_VALUES"][1],
											'SHOW_TIME' => 'N',
											'HIDE_TIMEBAR' => 'Y',
										),
										null,
										array('HIDE_ICONS' => 'Y')
									);?>
							</div></div>
						</div>
					</div>
				</div>
			<?elseif ($arItem["TYPE"] == "SELECT"):
				?>
				<div class="col-sm-6 col-md-4 bx-filter-parameters-box active">
					<div class="bx-filter-parameters-box-title"><span><?=$arItem["NAME"]?></span></div>
					<div class="bx-filter-block">
						<div class="row bx-filter-parameters-box-container">
							<div class="col-xs-12 bx-filter-parameters-box-container-block">
								<div class="bx-filter-input-container">
									<select name="<?=$arItem["INPUT_NAME"].($arItem["MULTIPLE"] == "Y" ? "[]" : "")?>">
										<?foreach ($arItem["LIST"] as $key => $value):?>
											<option
												value="<?=htmlspecialcharsBx($key)?>"
												<?if ($key == $arItem["INPUT_VALUE"]) echo 'selected="selected"'?>
											><?=htmlspecialcharsEx($value)?></option>
										<?endforeach?>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?elseif ($arItem["TYPE"] == "CHECKBOX"):
				?>
				<div class="col-sm-6 col-md-4 bx-filter-parameters-box active">
					<div class="bx-filter-parameters-box-title"><span><?=$arItem["NAME"]?></span></div>
					<div class="bx-filter-block">
						<div class="row bx-filter-parameters-box-container">
							<div class="col-xs-12 bx-filter-parameters-box-container-block">
							<?
							$arListValue = (is_array($arItem["~INPUT_VALUE"]) ? $arItem["~INPUT_VALUE"] : array($arItem["~INPUT_VALUE"]));
							foreach ($arItem["LIST"] as $key => $value):?>
							<div class="checkbox">
								<label class="bx-filter-param-label">
									<input
										type="checkbox"
										value="<?=htmlspecialcharsBx($key)?>"
										name="<?echo $arItem["INPUT_NAME"]?>[]"
										<?if (in_array($key, $arListValue)) echo 'checked="checked"'?>
									>
									<span class="bx-filter-param-text"><?=htmlspecialcharsEx($value)?></span>
								</label>
							</div>
							<?endforeach?>
							</div>
						</div>
					</div>
				</div>
			<?elseif ($arItem["TYPE"] == "RADIO"):
				?>
				<div class="col-sm-6 col-md-4 bx-filter-parameters-box active">
					<div class="bx-filter-parameters-box-title"><span><?=$arItem["NAME"]?></span></div>
					<div class="bx-filter-block">
						<div class="row bx-filter-parameters-box-container">
							<div class="col-xs-12 bx-filter-parameters-box-container-block">
								<?
								$arListValue = (is_array($arItem["~INPUT_VALUE"]) ? $arItem["~INPUT_VALUE"] : array($arItem["~INPUT_VALUE"]));
								foreach ($arItem["LIST"] as $key => $value):?>
								<div class="radio">
									<label class="bx-filter-param-label">
										<input
											type="radio"
											value="<?=htmlspecialcharsBx($key)?>"
											name="<?echo $arItem["INPUT_NAME"]?>"
											<?if (in_array($key, $arListValue)) echo 'checked="checked"'?>
										>
										<span class="bx-filter-param-text"><?=htmlspecialcharsEx($value)?></span>
									</label>
								</div>
								<?endforeach?>
							</div>
						</div>
					</div>
				</div>
			<?else:?>
				<div class="col-sm-6 col-md-4 bx-filter-parameters-box active">
					<div class="bx-filter-parameters-box-title"><span><?=$arItem["NAME"]?></span></div>
					<div class="bx-filter-block">
						<div class="row bx-filter-parameters-box-container">
							<div class="col-xs-12 bx-filter-parameters-box-container-block">
								<div class="bx-filter-input-container">
									<?=$arItem["INPUT"]?>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?endif?>
		<?endforeach;?>
		</div>
		<div class="row">
			<div class="col-xs-12 bx-filter-button-box">
				<div class="bx-filter-block">
					<div class="bx-filter-parameters-box-container">
						<input type="submit" name="set_filter" value="<?=GetMessage("CT_BCF_SET_FILTER")?>" class="btn btn-primary" />
						<input type="hidden" name="set_filter" value="Y" />
						<input type="submit" name="del_filter" value="<?=GetMessage("CT_BCF_DEL_FILTER")?>" class="btn btn-link" />
					</div>
				</div>
			</div>
		</div>
		<!--  -->
		<div class="clb"></div>
	</div>
</form>
</div>