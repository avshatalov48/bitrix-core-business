<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = array();
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch())
{
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
	{
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["~NAME"];
	}
}
$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
while($arUGroups = $dbUGroups -> Fetch())
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}

$res = unserialize(COption::GetOptionString("photogallery", "pictures"));
$arSights = array();
if (is_array($res))
{
	foreach ($res as $key => $val)
	{
		$arSights[str_pad($key, 5, "_").$val["code"]] = $val["title"];
	}
}

if (empty($arCurrentValues["INDEX_URL"]) && !empty($arCurrentValues["SECTIONS_TOP_URL"]))
	$arCurrentValues["INDEX_URL"] = $arCurrentValues["SECTIONS_TOP_URL"];
$arComponentParameters = array(
	"GROUPS" => array(
		"PHOTO_SETTINGS" => array(
			"NAME" => GetMessage("P_PHOTO_SETTINGS")
		),
		"WATERMARK" => array(
			"NAME" => GetMessage("P_WATERMARK")
		),
		"THUMBS_SETTINGS" => array(
			"NAME" => GetMessage("P_PREVIEW"),
			"PARENT" => "PHOTO_SETTINGS"
		),
		// "DETAIL_SETTINGS" => array(
			// "NAME" => GetMessage("P_DETAIL"),
			// "PARENT" => "PHOTO_SETTINGS"
		// ),
		"ORIGINAL_SETTINGS" => array(
			"NAME" => GetMessage("P_ORIGINAL"),
			"PARENT" => "PHOTO_SETTINGS"
		)
	),

	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
		),
		"SECTION_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SECTION_ID"]}',
		),
		"INDEX_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_INDEX_URL"),
			"DEFAULT" => "index.php"
		),
		"SECTION_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SECTION_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "section.php?SECTION_ID=#SECTION_ID#"
		)
	)
);

if ($arCurrentValues["UPLOADER_TYPE"])
{
	$arComponentParameters["PARAMETERS"]["UPLOADER_TYPE"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_UPLOADER_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"form" => GetMessage("P_UPLOADER_TYPE_FORM_SIMPLE"),
			"applet" => GetMessage("P_UPLOADER_TYPE_APPLET"),
			"flash" => GetMessage("P_UPLOADER_TYPE_FLASH")
		),
		"DEFAULT" => "form",
		"HIDDEN" => $arCurrentValues["UPLOADER_TYPE"] == "form" ? "Y" : "N",
		"REFRESH" => "Y"
	);
}

if ($arCurrentValues["UPLOADER_TYPE"] == "applet")
{
	$arComponentParameters["PARAMETERS"]["APPLET_LAYOUT"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_APPLET_LAYOUT"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"extended" => GetMessage("P_APPLET_LAYOUT_EXTENDED"),
				"simple" => GetMessage("P_APPLET_LAYOUT_SIMPLE"),
			),
			"DEFAULT" => "extended"
	);
}

$arComponentParameters["PARAMETERS"]["UPLOAD_MAX_FILE_SIZE"] = array(
	"PARENT" => "PHOTO_SETTINGS",
	"NAME" => GetMessage("P_UPLOAD_MAX_FILE_SIZE"),
	"TYPE" => "STRING",
	"DEFAULT" => "7"
);

if (count($arSights) > 0)
{
	$arComponentParameters["PARAMETERS"]["ADDITIONAL_SIGHTS"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_ADDITIONAL_SIGHTS"),
		"TYPE" => "LIST",
		"VALUES" => $arSights,
		"DEFAULT" => array(),
		"MULTIPLE" => "Y"
	);
}

$arComponentParameters["PARAMETERS"]["MODERATION"] = array(
	"PARENT" => "PHOTO_SETTINGS",
	"NAME" => GetMessage("P_MODERATION"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N"
);

$arComponentParameters["PARAMETERS"]["USE_WATERMARK"] = array(
	"PARENT" => "WATERMARK",
	"NAME" => GetMessage("P_USE_WATERMARK"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
	"REFRESH" => "Y"
);

if ($arCurrentValues["USE_WATERMARK"] != "N")
{
	$arComponentParameters["PARAMETERS"]["WATERMARK_RULES"] = array(
		"PARENT" => "WATERMARK",
		"NAME" => GetMessage("P_WATERMARK_RULES"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"USER" => GetMessage("P_WATERMARK_RULES_USER"),
			"ALL" => GetMessage("P_WATERMARK_RULES_ALL")),
		"DEFAULT" => "USER",
		"REFRESH" => "Y"
	);

	// Applly  watermark to all photos on server
	if ($arCurrentValues["WATERMARK_RULES"] == "ALL")
	{
		$arComponentParameters["PARAMETERS"]["WATERMARK_TYPE"] = array(
			"PARENT" => "WATERMARK",
			"NAME" => GetMessage("P_WATERMARK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"TEXT" => GetMessage("P_WATERMARK_TYPE_TEXT"),
				"PICTURE" => GetMessage("P_WATERMARK_TYPE_PICTURE")),
			"DEFAULT" => "PICTURE",
			"REFRESH" => "Y"
		);

		if ($arCurrentValues["WATERMARK_TYPE"] == "TEXT")
		{
			$arComponentParameters["PARAMETERS"]["WATERMARK_TEXT"] = array(
				"PARENT" => "WATERMARK",
				"NAME" => GetMessage("P_WATERMARK_TEXT"),
				"TYPE" => "STRING"
			);
			$arComponentParameters["PARAMETERS"]["WATERMARK_COLOR"] = array(
				"PARENT" => "WATERMARK",
				"NAME" => GetMessage("P_WATERMARK_COLOR"),
				"TYPE" => "STRING",
				"VALUES" => "FF00EE"
			);
			$arComponentParameters["PARAMETERS"]["WATERMARK_SIZE"] = array(
				"PARENT" => "WATERMARK",
				"NAME" => GetMessage("P_WATERMARK_SIZE"),
				"TYPE" => "STRING",
				"VALUES" => "10"
			);
		}
		else
		{
			$arComponentParameters["PARAMETERS"]["WATERMARK_FILE"] = array(
				"PARENT" => "WATERMARK",
				"NAME" => GetMessage("P_WATERMARK_FILE"),
				"TYPE" => "STRING",
				"VALUES" => ""
			);
			$arComponentParameters["PARAMETERS"]["WATERMARK_FILE_ORDER"] = array(
				"PARENT" => "WATERMARK",
				"NAME" => GetMessage("P_WATERMARK_FILE_ORDER"),
				"VALUES" => array(
					"usual" => GetMessage("P_WATERMARK_FILE_ORDER_USUAL"),
					"resize" => GetMessage("P_WATERMARK_FILE_ORDER_RESIZE"),
					"repeat" => GetMessage("P_WATERMARK_FILE_ORDER_REPEAT")
				),
				"DEFAULT" => "usual"
			);
		}

		$arComponentParameters["PARAMETERS"]["WATERMARK_POSITION"] = array(
			"PARENT" => "WATERMARK",
			"NAME" => GetMessage("P_WATERMARK_POSITION"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"tl" => GetMessage("P_WATERMARK_POSITION_TL"),
				"tc" => GetMessage("P_WATERMARK_POSITION_TC"),
				"tr" => GetMessage("P_WATERMARK_POSITION_TR"),
				"ml" => GetMessage("P_WATERMARK_POSITION_ML"),
				"mc" => GetMessage("P_WATERMARK_POSITION_MC"),
				"mr" => GetMessage("P_WATERMARK_POSITION_MR"),
				"bl" => GetMessage("P_WATERMARK_POSITION_BL"),
				"bc" => GetMessage("P_WATERMARK_POSITION_BC"),
				"br" => GetMessage("P_WATERMARK_POSITION_BR")),
			"DEFAULT" => "mc"
		);

		if ($arCurrentValues["WATERMARK_TYPE"] != "TEXT")
			$arComponentParameters["PARAMETERS"]["WATERMARK_TRANSPARENCY"] = array(
				"PARENT" => "WATERMARK",
				"NAME" => GetMessage("P_WATERMARK_TRANSPARENCY"),
				"TYPE" => "STRING",
				"DEFAULT" => "50"
			);
	}
	else
	{
		$arComponentParameters["PARAMETERS"]["WATERMARK_TYPE"] = array(
			"PARENT" => "WATERMARK",
			"NAME" => GetMessage("P_WATERMARK_TYPE1"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"BOTH" => GetMessage("P_WATERMARK_TYPE_BOTH"),
				"TEXT" => GetMessage("P_WATERMARK_TYPE_TEXT"),
				"PICTURE" => GetMessage("P_WATERMARK_TYPE_PICTURE")),
			"DEFAULT" => "BOTH",
			"REFRESH" => "Y"
		);
	}

	if ($arCurrentValues["UPLOADER_TYPE"] != "applet")
	{
		$arComponentParameters["PARAMETERS"]["PATH_TO_FONT"] = array(
			"PARENT" => "WATERMARK",
			"NAME" => GetMessage("P_PATH_TO_FONT"),
			"TYPE" => "STRING",
			"DEFAULT" => "default.ttf"
		);
	}

	$arComponentParameters["PARAMETERS"]["WATERMARK_MIN_PICTURE_SIZE"] = array(
		"PARENT" => "WATERMARK",
		"NAME" => GetMessage("P_WATERMARK_MIN_PICTURE_SIZE"),
		"TYPE" => "STRING",
		"DEFAULT" => "800"
	);
}

// $arComponentParameters["PARAMETERS"]["ALBUM_PHOTO_WIDTH"] = array(
	// "PARENT" => "PHOTO_SETTINGS",
	// "NAME" => GetMessage("P_ALBUM_PHOTO_WIDTH"),
	// "TYPE" => "STRING",
	// "DEFAULT" => "200"
// );

$arComponentParameters["PARAMETERS"]["ALBUM_PHOTO_THUMBS_WIDTH"] = array(
	"PARENT" => "PHOTO_SETTINGS",
	"NAME" => GetMessage("P_ALBUM_PHOTO_THUMBS_WIDTH"),
	"DEFAULT" => "120"
);

$arComponentParameters["PARAMETERS"]["THUMBNAIL_SIZE"] = array(
	"PARENT" => "THUMBS_SETTINGS",
	"NAME" => GetMessage("P_SIZE"),
	"DEFAULT" => "90"
);
$arComponentParameters["PARAMETERS"]["JPEG_QUALITY1"] = array(
	"PARENT" => "THUMBS_SETTINGS",
	"NAME" => GetMessage("P_JPEG_QUALITY"),
	"DEFAULT" => "100"
);

$arComponentParameters["PARAMETERS"]["ORIGINAL_SIZE"] = array(
	"PARENT" => "ORIGINAL_SETTINGS",
	"NAME" => GetMessage("P_ORIGINAL_SIZE"),
	"DEFAULT" => "1280"
);

$arComponentParameters["PARAMETERS"]["JPEG_QUALITY"] = array(
	"PARENT" => "ORIGINAL_SETTINGS",
	"NAME" => GetMessage("P_JPEG_QUALITY"),
	"DEFAULT" => "100"
);

// $arComponentParameters["PARAMETERS"]["DISPLAY_PANEL"] = array(
	// "PARENT" => "ADDITIONAL_SETTINGS",
	// "NAME" => GetMessage("T_IBLOCK_DESC_NEWS_PANEL"),
	// "TYPE" => "CHECKBOX",
	// "DEFAULT" => "N");
$arComponentParameters["PARAMETERS"]["SET_TITLE"] = array();
?>