<?
if (count($arResult["SEARCH_RESULT"]) > 0):
	if ($arResult['CURRENT_VIEW'] == "list"):
		foreach ($arResult["SEARCH_RESULT"] as $i => $v):
			if (array_key_exists("IMAGE_FILE", $v) && is_array($v["IMAGE_FILE"]) && $v["IMAGE_FILE"]["SRC"] <> ''):

				$arFileTmp = CFile::ResizeImageGet(
					$v["IMAGE_FILE"],
					array("width" => 75, "height" => 75),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					false
				);
				$arResult["SEARCH_RESULT"][$i]["IMAGE_FILE"]["SRC"] = $arFileTmp["src"];
			endif;
		endforeach;
	endif;
endif;	

