<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include_once(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));
$arAVSErr = array(
	"A" => GetMessage("AN_AVS_A"),
	"E" => GetMessage("AN_AVS_E"),
	"N" => GetMessage("AN_AVS_N"),
	"P" => GetMessage("AN_AVS_P"),
	"R" => GetMessage("AN_AVS_R"),
	"S" => GetMessage("AN_AVS_S"),
	"U" => GetMessage("AN_AVS_U"),
	"W" => GetMessage("AN_AVS_W"),
	"X" => GetMessage("AN_AVS_X"),
	"Y" => GetMessage("AN_AVS_Y"),
	"Z" => GetMessage("AN_AVS_Z"),
);

$arCVVErr = array(
	"M" => GetMessage("AN_CVV_M"),
	"N" => GetMessage("AN_CVV_N"),
	"P" => GetMessage("AN_CVV_P"),
	"S" => GetMessage("AN_CVV_S"),
	"U" => GetMessage("AN_CVV_U"),
);

$arCAVVErr = array(
	"0" => GetMessage("AN_CAVV_0"),
	"1" => GetMessage("AN_CAVV_1"),
	"2" => GetMessage("AN_CAVV_2"),
	"3" => GetMessage("AN_CAVV_3"),
	"4" => GetMessage("AN_CAVV_4"),
	"7" => GetMessage("AN_CAVV_7"),
	"8" => GetMessage("AN_CAVV_8"),
	"9" => GetMessage("AN_CAVV_9"),
	"A" => GetMessage("AN_CAVV_A"),
	"B" => GetMessage("AN_CAVV_B"),
);
?>