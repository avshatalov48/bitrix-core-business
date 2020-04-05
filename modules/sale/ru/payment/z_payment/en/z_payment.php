<?
////////////////////////////////////////////////////////////////
//              Модуль Z-PAYMENT для 1C-Bitrix                //
////////////////////////////////////////////////////////////////
//      Z-PAYMENT, система приема и обработки платежей        //
//      All rights reserved © 2002-2007, TRANSACTOR LLC       //
////////////////////////////////////////////////////////////////

global $MESS;

$MESS["SWMWP_DTITLE"] = "Payment via Z-PAYMENT (Russian Payment System)";
$MESS["SWMWP_DDESCR"] = "Payment via Z-PAYMENT using <b>Web Merchant Interface</b> <a href=\"https://z-payment.ru\" target=\"_blank\">https://z-payment.ru</a>.<br> For automatically receiving payments on this shop it should be add into list of shops.  Authorise and on the site <a href=\"https://z-payment.ru/addshop.php\" target=\"_blank\">https://z-payment.ru/addshop.php</a> you will get page with sets of shop.<br>\r\nset parameters:<UL><LI><b>Result URL</b> - to track payments automatically, specify its address <nobr>(http://site/bitrix/php_interface/include/sale_payment/z_payment_result.php)</nobr>; for notifications via e-mail - <nobr>mailto:name@address.ru</nobr></LI><LI><b>Success URL</b> - is the page to which the customer will be redirected  on success <nobr>(http://site/success.html)</nobr>. Specify appropriate page here.</LI><LI><b>Fail URL</b> - is the page to which the customer will be redirected  if the Web Merchant Interface failed to process payment. <nobr>(http://site/fail.html)</nobr>.</LI><LI>Signature algorithm - <b>MD5</b></LI><LI><b>Merchant Key</b> - any set of symbols. specify if you want to track payments automatically.</LI></UL>";
$MESS["SWMWP_ZPIDSD"] = '<b><font color="red">*</font>Shop identifier</b>';
$MESS["SWMWP_ZPIDS"]  = "Whole number - shop identifier in Z-PAYMENT Merchant system. It appropriate automatically by system during create new shop.";
$MESS["SWMWP_ZPSCD"]   = '<b><font color="red">*</font>Shop Key - Merchant Key</b>';
$MESS["SWMWP_ZPSC"]   = "";
$MESS["SWMWP_ZPRURD"]  = '<b><font color="red">*</font>"Russian ruble" currency code in system.</b>';
$MESS["SWMWP_ZPRUR"]  = 'For example, RUR or RUB!';

?>