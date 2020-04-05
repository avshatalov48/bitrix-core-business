<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<title>Flash Demo</title>
<meta name="robots" content="all">
<script language="JavaScript">
<!--
function KeyPress()
{
	if(window.event.keyCode == 27)
		window.close();
}
//-->
</script>	
</head>	
<BODY topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" onKeyPress="KeyPress()"><center><OBJECT CLASSID="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" WIDTH="<?echo htmlspecialcharsbx($_GET["width"])?>" HEIGHT="<?echo htmlspecialcharsbx($_GET["height"])?>" CODEBASE="http://active.macromedia.com/flash5/cabs/swflash.cab#version=5,0,0,0">
<PARAM NAME=movie VALUE="<?echo htmlspecialcharsbx($_GET["img"])?>">
<PARAM NAME=play VALUE=true>
<PARAM NAME=loop VALUE=0>
<PARAM NAME=quality VALUE=high>
<EMBED NAME=robodemo SRC="<?echo htmlspecialcharsbx($_GET["img"])?>" WIDTH="<?echo htmlspecialcharsbx($_GET["width"])?>" HEIGHT="<?echo htmlspecialcharsbx($_GET["height"])?>" loop=0 quality=high TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" swLiveConnect=true>
</EMBED>
</OBJECT>
</center>
</body></html>
