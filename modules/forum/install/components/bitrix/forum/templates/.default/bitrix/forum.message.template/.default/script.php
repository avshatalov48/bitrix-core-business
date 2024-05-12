<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script>
	window.fAddSId = function(n)
	{
		if (typeof n == "string") n += (n.indexOf("?") < 0 ? "?" : "&") + "sessid=" + BX.bitrix_sessid();
		else if (BX.type.isDomNode(n)) n.href += (n.href.indexOf("?") < 0 ? "?" : "&") + "sessid=" + BX.bitrix_sessid();
		return n;
	}
</script>
