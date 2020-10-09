<?
/**********************************************************************/
/**    DO NOT MODIFY THIS FILE                                       **/
/**    MODIFICATION OF THIS FILE WILL ENTAIL SITE FAILURE            **/
/**********************************************************************/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$FM_RIGHT = $APPLICATION->GetGroupRight("main");
if ($FM_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<style>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/admin_styles.css");

if ($APPLICATION->GetAdditionalCSS() <> '')
{
	require($_SERVER["DOCUMENT_ROOT"].$APPLICATION->GetAdditionalCSS());
}
?>
</style>

<script language="javascript">
function DoEvent(str)
{
	try
	{
		eval("parent."+this.name+"_"+str);
	}
	catch(e){}
}
</script>

</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="white">

<table width="100%" border="0" id="updates_items">
</table>

</body>
</html>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php")
?>