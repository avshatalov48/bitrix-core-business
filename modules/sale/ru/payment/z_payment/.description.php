<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
////////////////////////////////////////////////////////////////
//              Модуль Z-PAYMENT для 1C-Bitrix                //
////////////////////////////////////////////////////////////////
//      Z-PAYMENT, система приема и обработки платежей        //
//      All rights reserved © 2002-2007, TRANSACTOR LLC       //
////////////////////////////////////////////////////////////////

include(GetLangFileName(dirname(__FILE__)."/", "/z_payment.php"));

$psTitle = GetMessage("SWMWP_DTITLE");
$psDescription  = GetMessage("SWMWP_DDESCR");
$ps_zpidsd =  GetMessage("SWMWP_ZPIDSD");
$ps_zpids =  GetMessage("SWMWP_ZPIDS");
$ps_zpscd =  GetMessage("SWMWP_ZPSCD");
$ps_zpsc =  GetMessage("SWMWP_ZPSC");
$ps_zprurd =  GetMessage("SWMWP_ZPRURD");
$ps_zprur =  GetMessage("SWMWP_ZPRUR");

$arPSCorrespondence = array(
		"ZP_SHOP_ID" => array(
				"NAME" => $ps_zpidsd,
				"DESCR" => $ps_zpids,
				"VALUE" => "",
				"TYPE" => ""
			),
		"ZP_MERCHANT_KEY" => array(
				"NAME" => $ps_zpscd,
				"DESCR" => $ps_zpsc,
				"VALUE" => "",
				"TYPE" => ""
			),
				"ZP_CODE_RUR" => array(
				"NAME" => $ps_zprurd,
				"DESCR" => $ps_zprur,
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>