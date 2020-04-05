<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script>
	var smartFilter = new JCSmartFilter('<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>');
</script>

<div  id="modef" <?if(!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"';?>>
	<?echo GetMessage("CT_BCSF_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num">'.intval($arResult["ELEMENT_COUNT"]).'</span>'));?>
	<a  href="<?echo $arResult["FILTER_URL"]?>" ><?echo GetMessage("CT_BCSF_FILTER_SHOW")?></a>
</div>
<div class="filter_wrap">
<div class="filter_component">
	<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get" class="smartfilter" id="smartFilterForm">

		<?foreach($arResult["HIDDEN"] as $arItem):?>
			<input
				type="hidden"
				name="<?echo $arItem["CONTROL_NAME"]?>"
				id="<?echo $arItem["CONTROL_ID"]?>"
				value="<?echo $arItem["HTML_VALUE"]?>"
			/>
		<?endforeach;?>

		<?foreach($arResult["ITEMS"] as $key=>$arItem):?>
			<?if(isset($arItem["PRICE"])):?>
				<?unset($arResult["ITEMS"][$key]);?>
			<?
			if (empty($arItem["VALUES"]["MIN"]["VALUE"])) $arItem["VALUES"]["MIN"]["VALUE"] = 0;
			if (empty($arItem["VALUES"]["MAX"]["VALUE"])) $arItem["VALUES"]["MAX"]["VALUE"] = 100000;
			?>
			<div class="filter_container ">
				<h3><?=$arItem["NAME"]?></h3>
				<div class="filter_param_container">
					<div class="filter_param_drag_container">
						<div class="filter_param_drag_min_price" id="curMinPrice_<?=$key?>"><?=($arItem["VALUES"]["MIN"]["HTML_VALUE"]) ? $arItem["VALUES"]["MIN"]["HTML_VALUE"] : $arItem["VALUES"]["MIN"]["VALUE"]?></div>
						<div class="filter_param_drag_max_price" id="curMaxPrice_<?=$key?>"><?=($arItem["VALUES"]["MAX"]["HTML_VALUE"]) ? $arItem["VALUES"]["MAX"]["HTML_VALUE"] : $arItem["VALUES"]["MAX"]["VALUE"]?></div>
						<div class="filter_param_visible_drag_track"></div>
						<div class="filter_param_drag_track" id="drag_track_<?=$key?>">
							<div class="filter_param_drag_dragger" 	style="left:0;" id="left_slider_<?=$key?>"></div>
							<div class="filter_param_drag_bar"		style="left:0;width:100%" id="drag_tracker_<?=$key?>"></div>
							<div class="filter_param_drag_dragger" 	style="left:100%;" id="right_slider_<?=$key?>"></div>
						</div>
					</div>

				<input
					class="min-price"
					type="hidden"
					name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
					id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
					value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
					size="5"
					onkeyup="smartFilter.keyup(this)"
					/>
				<input
					class="max-price"
					type="hidden"
					name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
					id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
					value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
					size="5"
					onkeyup="smartFilter.keyup(this)"
					/>
				</div>
			</div>
			<script type="text/javascript" defer="defer">
				var DoubleTrackBar_<?=$key?> = new touchTrackBar('drag_track_<?=$key?>', 'drag_tracker_<?=$key?>', 'left_slider_<?=$key?>', 'right_slider_<?=$key?>',
				{
					MinPrice: parseFloat(<?=$arItem["VALUES"]["MIN"]["VALUE"]?>),
					MaxPrice: parseFloat(<?=$arItem["VALUES"]["MAX"]["VALUE"]?>),
					CurMinPrice: curMinPrice_<?=$key?>,
					CurMaxPrice: curMaxPrice_<?=$key?>,
					MinInputId : <?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>,
					MaxInputId : <?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>,
				});
				BX('left_slider_<?=$key?>').addEventListener('touchmove', function(event) {
					DoubleTrackBar_<?=$key?>.touchmoveleft(event);
				});
				BX('right_slider_<?=$key?>').addEventListener('touchmove', function(event) {
					DoubleTrackBar_<?=$key?>.touchmoveright(event);
				});
				DoubleTrackBar_<?=$key?>.startPosition();
			</script>
			<?endif?>
		<?endforeach?>

		<?foreach($arResult["ITEMS"] as $arItem):?>
			<?/*if($arItem["PROPERTY_TYPE"] == "N"):?>
			<div class="filter_container">
				<h3 onclick="BX.toggle(BX('ul_<?echo $arItem["ID"]?>')); return false;"><?=$arItem["NAME"]?><span class="filter_arrow"></span></h3>
				<ul id="ul_<?echo $arItem["ID"]?>">
					<?
						//$arItem["VALUES"]["MIN"]["VALUE"];
						//$arItem["VALUES"]["MAX"]["VALUE"];
					?>
					<li class="lvl2">
						<table border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td>
									<span class="max-price"><?echo GetMessage("CT_BCSF_FILTER_TO")?></span>
								</td>
								<td>
									<span class="min-price"><?echo GetMessage("CT_BCSF_FILTER_FROM")?></span>
								</td>
							</tr>
							<tr>
								<td><input
									class="max-price"
									type="text"
									name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
									id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
									value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
									size="5"
									onkeyup="smartFilter.keyup(this)"
								/></td>
								<td><input
									class="min-price"
									type="text"
									name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
									id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
									value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
									size="5"
									onkeyup="smartFilter.keyup(this)"
								/></td>
							</tr>
						</table>
					</li>
				</ul>
			</div>
			<?else*/if(!empty($arItem["VALUES"])):;?>
			<div class="filter_container close" onclick="OpenClose(this)">
				<h3 onclick="BX.toggle(BX('ul_<?echo $arItem["ID"]?>')); return false;"><?=$arItem["NAME"]?><span class="filter_arrow"></span></h3>
				<div class="filter_param_container p0">
					<ul class="col-2 <?if (count($arItem["VALUES"])==1) echo "one-item"; elseif(count($arItem["VALUES"])==2) echo "two-item";elseif(count($arItem["VALUES"])==3) echo "three-item";?>" id="ul_<?echo $arItem["ID"]?>" >
						<?foreach($arItem["VALUES"] as $val => $ar):?>
						<li <?echo $ar["CHECKED"]? 'class="checked"': ''?>> <!--class="lvl2<?echo $ar["DISABLED"]? ' disable': ''?>"-->
							<input
								type="checkbox"
								value="<?echo $ar["HTML_VALUE"]?>"
								name="<?echo $ar["CONTROL_NAME"]?>"
								id="<?echo $ar["CONTROL_ID"]?>"
								<?echo $ar["CHECKED"]? 'checked="checked"': ''?>
								onclick="
									if (BX.hasClass(BX(this).parentNode, 'checked'))
										BX.removeClass(BX(this).parentNode, 'checked')
									else
									{
										BX.addClass(BX(this).parentNode, 'checked');
										BX.removeClass(BX(this).parentNode, 'disable');
									}
									smartFilter.click(this)"
								style="display:none;"
							/>
							<label for="<?echo $ar["CONTROL_ID"]?>" ><?echo $ar["VALUE"];?></label>
						</li>
						<?endforeach;?>
					</ul>
				</div>
			</div>
			<?endif;?>
		<?endforeach;?>
		<div class="filter_bottom">
		<input type="hidden" id="set_filter" name="set_filter" value="<?=GetMessage("CT_BCSF_SET_FILTER")?>" class="filter_view_button"/>
		<a href="javascript:void(0)" class="filter_view_button button_red_medium" ontouchstart="BX.toggleClass(this, 'active');" ontouchend="BX.toggleClass(this, 'active');" onclick="BX.remove(BX('del_filter')); BX('smartFilterForm').submit();"><?=GetMessage("CT_BCSF_SET_FILTER")?></a>
		<input type="hidden" id="del_filter" name="del_filter" value="<?=GetMessage("CT_BCSF_DEL_FILTER")?>" class="filter_refresh_button"/>
		<a href="javascript:void(0)" class="filter_refresh_button button_gray_medium" ontouchstart="BX.toggleClass(this, 'active');" ontouchend="BX.toggleClass(this, 'active');" onclick="BX.remove(BX('set_filter')); BX('smartFilterForm').submit();"><?=GetMessage("CT_BCSF_DEL_FILTER")?></a>
		</div>
	</form>
</div>
</div>