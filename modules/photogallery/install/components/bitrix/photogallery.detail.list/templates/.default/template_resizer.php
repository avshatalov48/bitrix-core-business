<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
if (empty($arParams["PICTURES"]))
	return true;

if (!function_exists("__photo_cmp"))
{
	function __photo_cmp($a, $b) {
	    if ($a["size"] == $b["size"])
	        return 0;
	    return ($a["size"] < $b["size"] ? -1 : 1);
	}
}
$arResTmp = array_merge(
	array(
		"standart" => array(
			"title" => GetMessage("P_STANDARD"),
			"size" => $arParams["THUMBNAIL_SIZE"])),
	$arParams["PICTURES"]);
$arRes = array();
foreach ($arResTmp as $key => $val)
	$arRes[] = $val + array("id" => $key);
usort($arRes, __photo_cmp);
$number = 0;
foreach ($arRes as $val):
	$number++;
	if ($val["id"] == $arParams["PICTURES_SIGHT"])
	{
		break;
	}
endforeach;
?>
		<li class="photo-control photo-control-first photo-control-photo-sights" style="padding-top: 2px;">
			<div class="bx-mixer" id="bx_mixer_size">
				<div class="bx-mixer-top">
					<div class="bx-mixer-top-inner">
						<div class="bx-mixer-scales"><?
						$count = count($arRes) - 1;
						$width = round(100/$count, 2);
						for ($ii = 1; $ii <= $count; $ii++)
						{
							?><div class="bx-mixer-scale <?=($ii == 1 ? "bx-mixer-scale-first" : ($ii == $count ? "bx-mixer-scale-last" : ""))?>" <?
								?> style="width:<?=$width?>%;"></div><?
						}
						?>
						</div>
					</div>
				</div>
				<div class="bx-mixer-bottom">
					<div class="bx-mixer-minus"><div id="bx_speed_mixers_minus"><span></span></div></div>
					<div class="bx-mixer-ruler"><div id="bx_speed_mixers_ruler"><?
							?><a id="bx_speed_mixers_cursor" href="#"><span></span></a></div></div>
					<div class="bx-mixer-plus"><div id="bx_speed_mixers_plus"><span></span></div></div>
				</div>
			</div>
<script>
var oPhotoObjects;
if (!oPhotoObjects || null == oPhotoObjects) { oPhotoObjects = {}; }
oPhotoObjects["sights"] = <?=CUtil::PhpToJSObject($arRes)?>;
oPhotoObjects["resizer"] = false;
var __photo_init_mixer = setInterval(function() {
	try {
		if (bPhotoCursorLoad === true && jsUtils)
		{
			oPhotoResizer = new BPCMixer(
				document.getElementById('bx_speed_mixers_ruler'),
				document.getElementById('bx_speed_mixers_cursor'),
				<?=($count + 1)?>,
				{
					'minus' : document.getElementById('bx_speed_mixers_minus'),
					'plus' : document.getElementById('bx_speed_mixers_plus')
				}
			);
			oPhotoResizer.SetCursor(<?=$number?>);
			oPhotoResizer.events['AfterSetCursor'] = function() {
					arguments = arguments[0];
					var number_template = arguments[1] - 1;
					if (!oPhotoObjects["sights"][number_template])
						return false;

					setTimeout(new Function("if (" + number_template + " != oPhotoResizer.current) {" +
							"__photo_change_template_data(" +
								"'sight', " +
								"oPhotoObjects['sights'][" + number_template + "]['id'], " +
								"'<?=$arParams["ID"]?>', " +
								"{'PICTURES_SIGHT' : oPhotoObjects['sights'][" + number_template + "]['id']}); }"), 1000);
				}
			clearInterval(__photo_init_mixer);
		}
	} catch (e) { }
}, 500);
</script>
		</li>