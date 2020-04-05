<?

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$map = array(
	"android"=>array(
		"launch"=>array(
			array(
				"width" => 1536,
				"height" => 2048,
				"name" => GetMessage("RES_LAUNCH_SCREEN_PORT")
			),
			array(
				"width" => 2048,
				"height" => 1536,
				"name" => GetMessage("RES_LAUNCH_SCREEN_LAND")
			)
		),
		"icon"=>array(
			array("width" => 1024, "height" => 1024, "name" => GetMessage("RES_ICON"))
		),
		"additional"=>array(
			array("width" => 1024, "height" => 500, "name" => GetMessage("RES_GP_PROMO_IMAGE"))
		),
	),
	"ios"=>array
	(
		"launch"=> array
		(
			//iPhone 4/4s
			array("width" => 640, "height" => 960, "name" => GetMessage("RES_LSCREEN_IPHONE_INCH_3_5")),
			//iPhone 5/5s/5c
			array("width" => 640, "height" => 1136, "name" => GetMessage("RES_LSCREEN_IPHONE_INCH_4")),
			//iPad
			array("width" => 1024, "height" => 748, "name" => GetMessage("RES_LSCREEN_IPAD_NON_RETINA_LAND")),
			array("width" => 768, "height" => 1024, "name" => GetMessage("RES_LSCREEN_IPAD_NON_RETINA_PORT")),
			array("width" => 2048, "height" => 1536, "name" => GetMessage("RES_LSCREEN_IPAD_RETINA_LAND")),
			array("width" => 1536, "height" => 2048, "name" => GetMessage("RES_LSCREEN_IPAD_RETINA_PORT")),
			//iPhone 6 Plus
			array("width" => 1242, "height" => 2208, "name" => GetMessage("RES_LSCREEN_IPHONE_INCH_5_5_PORT")),
			array("width" => 2208, "height" => 1242, "name" => GetMessage("RES_LSCREEN_IPHONE_INCH_5_5_LAND")),
			//iPhone 6
			array("width" => 750, "height" => 1334, "name" => GetMessage("RES_LSCREEN_IPHONE_INCH_4_7_PORT")),
		),
		"icon"=>array(
			array("width" => 1024, "height" => 1024, "name" => GetMessage("RES_ICON"))
		),
	)
);

return $map;