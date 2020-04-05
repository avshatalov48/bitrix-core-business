<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("SUBSCRIBE_INSTALL_PUBLIC_TITLE"));
?><?$APPLICATION->IncludeComponent("bitrix:subscribe.index", ".default", Array(
	"SHOW_COUNT"	=>	"N",
	"SHOW_HIDDEN"	=>	"N",
	"PAGE"	=>	"subscr_edit.php",
	"CACHE_TIME"	=>	"3600",
	"SET_TITLE"	=>	"Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>