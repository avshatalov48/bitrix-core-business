<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if (!empty($arResult['ERRORS']))
{
	if ($arParams['AUTH_FORM_IN_TEMPLATE'] && isset($arResult['ERRORS'][$component::E_NOT_AUTHORIZED]))
	{
		?>
		<div class="row mb-3">
			<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
				<div class="alert alert-danger"><?=$arResult['ERRORS'][$component::E_NOT_AUTHORIZED]?></div>
			</div>
			<? $authListGetParams = array(); ?>
			<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3" id="catalog-subscriber-auth-form" style="<?=$authStyle?>">
				<?$APPLICATION->AuthForm('', false, false, 'N', false);?>
			</div>
		</div>
		<?
	}

	return;
}

?>
<div class="row mb-3">
	<div class="col">
		<a href="<?=$arParams["PATH_TO_LIST"]?>"><?=GetMessage("SPPD_RECORDS_LIST")?></a>
	</div>
</div>

<?
if(strlen($arResult["ID"])>0)
{
	ShowError($arResult["ERROR_MESSAGE"]);
	?>
	<div class="row mb-3">
		<form method="post"  class="col sale-profile-detail-form" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">

			<div class="row mb-2">
				<?=bitrix_sessid_post()?>
				<input type="hidden" name="ID" value="<?=$arResult["ID"]?>">
				<div class="col">
					<h4><?= Loc::getMessage('SPPD_PROFILE_NO', array("#ID#" => $arResult["ID"]))?></h4>
				</div>
			</div>

			<div class="form-group row mb-2">
				<label class="col-sm-3 text-right col-form-label"><?=Loc::getMessage('SALE_PERS_TYPE')?>:</label>
				<div class="col-sm-9 col-form-label"><?=$arResult["PERSON_TYPE"]["NAME"]?></div>
			</div>

			<div class="form-group row mb-2">
				<label class="col-sm-3 text-right col-form-label" for="sale-personal-profile-detail-name"><?=Loc::getMessage('SALE_PNAME')?>:<span class="req">*</span></label>
				<div class="col-sm-9">
					<input class="form-control mb-1" type="text" name="NAME" maxlength="50" id="sale-personal-profile-detail-name" value="<?=$arResult["NAME"]?>" />
				</div>
			</div>

			<?
			foreach($arResult["ORDER_PROPS"] as $block)
			{
				if (!empty($block["PROPS"]))
				{
					?>
					<div class="row mb-2 mt-4">
						<div class="col">
							<h4><?= $block["NAME"]?></h4>
						</div>
					</div>
					<?
					foreach($block["PROPS"] as $property)
					{
						$key = (int)$property["ID"];
						$name = "ORDER_PROP_".$key;
						$currentValue = $arResult["ORDER_PROPS_VALUES"][$name];
						$alignTop = ($property["TYPE"] === "LOCATION" && $arParams['USE_AJAX_LOCATIONS'] === 'Y') ? "vertical-align-top" : "";
						?>
							<?
							if ($property["TYPE"] == "CHECKBOX")
							{
								?>
								<div class="form-check row mb-2">
									<label class="col-sm-3 text-right col-form-label <?=$alignTop?>" for="sppd-property-<?=$key?>">
										<?= $property["NAME"]?>:
										<? if ($property["REQUIED"] == "Y")
										{
											?><span class="req">*</span><?
										}
										?>
									</label>
									<div class="col-sm-9">
										<input class="form-check-input" id="sppd-property-<?=$key?>" type="checkbox" name="<?=$name?>" value="Y"
										<?if ($currentValue == "Y" || !isset($currentValue) && $property["DEFAULT_VALUE"] == "Y") echo " checked";?>/>
									</div>
								</div>
								<?
							}
							elseif ($property["TYPE"] == "TEXT")
							{
								?>
								<div class="form-group row mb-2">
									<label class="col-sm-3 text-right col-form-label <?=$alignTop?>" for="sppd-property-<?=$key?>">
										<?= $property["NAME"]?>:
										<? if ($property["REQUIED"] == "Y")
										{
											?><span class="req">*</span><?
										}
										?>
									</label>
									<div class="col-sm-9">
										<?
										if ($property["MULTIPLE"] === 'Y')
										{
											if (empty($currentValue) || !is_array($currentValue))
												$currentValue = array('');
											foreach ($currentValue as $elementValue)
											{
												?>
												<input class="form-control mb-1" type="text" name="<?=$name?>[]" maxlength="50" id="sppd-property-<?=$key?>" value="<?=$elementValue?>"/>
												<?
											}
											?>
											<span class="btn btn-primary" data-add-type=<?=$property["TYPE"]?> data-add-name="<?=$name?>[]"><?=Loc::getMessage('SPPD_ADD')?></span>
											<?
										}
										else
										{
											?>
											<input class="form-control mb-1" type="text" name="<?=$name?>" maxlength="50" id="sppd-property-<?=$key?>" value="<?=$currentValue?>"/>
											<?
										}
										?>
									</div>
								</div>
								<?
							}
							elseif ($property["TYPE"] == "SELECT")
							{
								?>
								<div class="form-group row mb-2">
									<label class="col-sm-3 text-right col-form-label <?=$alignTop?>" for="sppd-property-<?=$key?>">
										<?= $property["NAME"]?>:
										<? if ($property["REQUIED"] == "Y")
										{
											?><span class="req">*</span><?
										}
										?>
									</label>
									<div class="col-sm-9">
										<select class="form-control mb-1" name="<?=$name?>" id="sppd-property-<?=$key?>" size="<?echo (intval($property["SIZE1"])>0)?$property["SIZE1"]:1; ?>">
											<?
											foreach ($property["VALUES"] as $value)
											{
												?>
												<option value="<?= $value["VALUE"]?>" <?if ($value["VALUE"] == $currentValue || !isset($currentValue) && $value["VALUE"]==$property["DEFAULT_VALUE"]) echo " selected"?>>
													<?= $value["NAME"]?>
												</option>
												<?
											}
											?>
										</select>
									</div>
								</div>
								<?
							}
							elseif ($property["TYPE"] == "MULTISELECT")
							{
								?>
								<div class="form-group row mb-2">
									<label class="col-sm-3 text-right col-form-label <?=$alignTop?>" for="sppd-property-<?=$key?>">
										<?= $property["NAME"]?>:
										<? if ($property["REQUIED"] == "Y")
										{
											?><span class="req">*</span><?
										}
										?>
									</label>
									<div class="col-sm-9">
										<select class="form-control mb-1" id="sppd-property-<?=$key?>" multiple name="<?=$name?>[]" size="<?echo (intval($property["SIZE1"])>0)?$property["SIZE1"]:5; ?>">
											<?
											$arCurVal = array();
											$arCurVal = explode(",", $currentValue);
											for ($i = 0, $cnt = count($arCurVal); $i < $cnt; $i++)
												$arCurVal[$i] = trim($arCurVal[$i]);
											$arDefVal = explode(",", $property["DEFAULT_VALUE"]);
											for ($i = 0, $cnt = count($arDefVal); $i < $cnt; $i++)
												$arDefVal[$i] = trim($arDefVal[$i]);
											foreach($property["VALUES"] as $value)
											{
												?>
												<option value="<?= $value["VALUE"]?>"<?if (in_array($value["VALUE"], $arCurVal) || !isset($currentValue) && in_array($value["VALUE"], $arDefVal)) echo" selected"?>>
													<?= $value["NAME"]?>
												</option>
												<?
											}
											?>
										</select>
									</div>
								</div>
								<?
							}
							elseif ($property["TYPE"] == "TEXTAREA")
							{
								?>
								<div class="form-group row mb-2">
									<label class="col-sm-3 text-right col-form-label <?=$alignTop?>" for="sppd-property-<?=$key?>">
										<?= $property["NAME"]?>:
										<? if ($property["REQUIED"] == "Y")
										{
											?><span class="req">*</span><?
										}
										?>
									</label>
									<div class="col-sm-9">
										<textarea
											class="form-control mb-1"
											id="sppd-property-<?=$key?>"
											rows="<?echo ((int)($property["SIZE2"])>0)?$property["SIZE2"]:4; ?>"
											cols="<?echo ((int)($property["SIZE1"])>0)?$property["SIZE1"]:40; ?>"
											name="<?=$name?>"><?= (isset($currentValue)) ? $currentValue : $property["DEFAULT_VALUE"];?>
										</textarea>
									</div>
								</div>
								<?
							}
							elseif ($property["TYPE"] == "LOCATION")
							{
								?>
								<div class="form-group row mb-2">
									<label class="col-sm-3 text-right col-form-label <?=$alignTop?>" for="sppd-property-<?=$key?>">
										<?= $property["NAME"]?>:
										<? if ($property["REQUIED"] == "Y")
										{
											?><span class="req">*</span><?
										}
										?>
									</label>
									<div class="col-sm-9">
										<?
											$locationTemplate = ($arParams['USE_AJAX_LOCATIONS'] !== 'Y') ? "popup" : "";
											$locationClassName = 'location-block-wrapper';
											if ($arParams['USE_AJAX_LOCATIONS'] === 'Y')
											{
												$locationClassName .= ' location-block-wrapper-delimeter';
											}
											if ($property["MULTIPLE"] === 'Y')
											{
												if (empty($currentValue) || !is_array($currentValue))
													$currentValue = array($property["DEFAULT_VALUE"]);

												foreach ($currentValue as $code => $elementValue)
												{
													$locationValue = intval($elementValue) ? $elementValue : $property["DEFAULT_VALUE"];
													CSaleLocation::proxySaleAjaxLocationsComponent(
														array(
															"ID" => "propertyLocation".$name."[$code]",
															"AJAX_CALL" => "N",
															'CITY_OUT_LOCATION' => 'Y',
															'COUNTRY_INPUT_NAME' => $name.'_COUNTRY',
															'CITY_INPUT_NAME' => $name."[$code]",
															'LOCATION_VALUE' => $locationValue,
														),
														array(
														),
														$locationTemplate,
														true,
														$locationClassName
													);
												}
												?><span class="btn btn-primary btn-md input-add-multiple"
													  data-add-type="<?=$property["TYPE"]?>"
													  data-add-name="<?=$name?>"
													  data-add-last-key="<?=$code?>"
													  data-add-template="<?=$locationTemplate?>"><?=Loc::getMessage('SPPD_ADD')?></span><?
										}
										else
										{
											$locationValue = (int)($currentValue) ? (int)$currentValue : $property["DEFAULT_VALUE"];

											CSaleLocation::proxySaleAjaxLocationsComponent(
												array(
													"AJAX_CALL" => "N",
													'CITY_OUT_LOCATION' => 'Y',
													'COUNTRY_INPUT_NAME' => $name.'_COUNTRY',
													'CITY_INPUT_NAME' => $name,
													'LOCATION_VALUE' => $locationValue,
												),
												array(
												),
												$locationTemplate,
												true,
												'location-block-wrapper'
											);
										}
										?>
									</div>
								</div>
								<?
							}
							elseif ($property["TYPE"] == "RADIO")
							{
								foreach($property["VALUES"] as $value)
								{
									?>
									<div class="form-check row mb-2">
										<label class="col-sm-3 text-right col-form-label <?=$alignTop?>" for="sppd-property-<?=$key?>">
											<?= $property["NAME"]?>:
											<? if ($property["REQUIED"] == "Y")
											{
												?><span class="req">*</span><?
											}
											?>
										</label>
										<div class="col-sm-9">
											<input type="radio" id="sppd-property-<?=$key?>" name="<?=$name?>" value="<?= $value["VALUE"]?>"
												<?if ($value["VALUE"] == $currentValue || !isset($currentValue) && $value["VALUE"] == $property["DEFAULT_VALUE"]) echo " checked"?>>
											<?= $value["NAME"]?>
										</div>
									</div>
									<?
								}
							}
							elseif ($property["TYPE"] == "FILE")
							{
								$multiple = ($property["MULTIPLE"] === "Y") ? "multiple" : '';
								$profileFiles = is_array($currentValue) ? $currentValue : array($currentValue);
								if (count($currentValue) > 0)
								{
									?>
									<input type="hidden" name="<?=$name?>_del" class="profile-property-input-delete-file">
									<?
									foreach ($profileFiles as $file)
									{
										?>
										<div class="sale-personal-profile-detail-form-file">
											<?
											$fileId = $file['ID'];
											if (CFile::IsImage($file['FILE_NAME']))
											{
												?>
												<div class="sale-personal-profile-detail-prop-img">
													<?=CFile::ShowImage($fileId, 150, 150, "border=0", "", true)?>
												</div>
												<?
											}
											else
											{
												?>
												<a download="<?=$file["ORIGINAL_NAME"]?>" href="<?=CFile::GetFileSRC($file)?>">
													<?=Loc::getMessage('SPPD_DOWNLOAD_FILE', array("#FILE_NAME#" => $file["ORIGINAL_NAME"]))?>
												</a>
												<?
											}
											?>
											<input type="checkbox" value="<?=$fileId?>" class="profile-property-check-file" id="profile-property-check-file-<?=$fileId?>">
											<label for="profile-property-check-file-<?=$fileId?>"><?=Loc::getMessage('SPPD_DELETE_FILE')?></label>
										</div>
										<?
									}
								}
								?>
								<div class="form-group row mb-2">
									<label class="col-sm-3 text-right col-form-label <?=$alignTop?>" for="sppd-property-<?=$key?>">
										<?= $property["NAME"]?>:
										<? if ($property["REQUIED"] == "Y")
										{
											?><span class="req">*</span><?
										}
										?>
									</label>
									<div class="col-sm-9">
										<label>
											<span class="btn btn-primary btn-md"><?=Loc::getMessage('SPPD_SELECT')?></span>
											<span class="sale-personal-profile-detail-load-file-info">
												<?=Loc::getMessage('SPPD_FILE_NOT_SELECTED')?>
											</span>
											<?=CFile::InputFile($name."[]", 20, null, false, 0, "IMAGE", "class='btn sale-personal-profile-detail-input-file' ".$multiple)?>
										</label>
										<span class="sale-personal-profile-detail-load-file-cancel sale-personal-profile-hide"></span>
									</div>
								</div>
								<?
							}
							if (strlen($property["DESCRIPTION"]) > 0)
							{
								?>
								<br /><small><?= $property["DESCRIPTION"] ?></small>
								<?
							}
							?>
						<?
					}
				}
			}
			?>
			<div class="row mb-3 mt-5">
				<div class="col">
					<input type="submit" class="btn btn-primary btn-md" name="save" value="<?echo GetMessage("SALE_SAVE") ?>">
					<input type="submit" class="btn btn-primary btn-md"  name="apply" value="<?=GetMessage("SALE_APPLY")?>">
					<input type="submit" class="btn btn-link btn-md"  name="reset" value="<?echo GetMessage("SALE_RESET")?>">
				</div>
			</div>
		</form>
	</div>
	<?
	$javascriptParams = array(
		"ajaxUrl" => CUtil::JSEscape($this->__component->GetPath().'/ajax.php'),
	);
	$javascriptParams = CUtil::PhpToJSObject($javascriptParams);
	?>
	<script>
		BX.message({
			SPPD_FILE_COUNT: '<?=Loc::getMessage('SPPD_FILE_COUNT')?>',
			SPPD_FILE_NOT_SELECTED: '<?=Loc::getMessage('SPPD_FILE_NOT_SELECTED')?>'
		});
		BX.Sale.PersonalProfileComponent.PersonalProfileDetail.init(<?=$javascriptParams?>);
	</script>
	<?
}
else
{
	ShowError($arResult["ERROR_MESSAGE"]);
}
?>