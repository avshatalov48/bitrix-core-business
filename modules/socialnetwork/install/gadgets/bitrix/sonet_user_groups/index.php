<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("socialnetwork"))
	return false;
	
$arGadgetParams["GROUPS_COUNT"] = ($arGadgetParams["GROUPS_COUNT"] ? $arGadgetParams["GROUPS_COUNT"] : 10);

if (is_array($arGadgetParams["GROUPS_LIST"]))
	$arGadgetParams["GROUPS_LIST"] = array_slice($arGadgetParams["GROUPS_LIST"], 0, $arGadgetParams["GROUPS_COUNT"]);
else
	$arGadgetParams["GROUPS_LIST"] = array();

?>
<table width="100%">
<tr>
	<td><?
	if ($arGadgetParams["CAN_VIEW_GROUPS"]):
		if ($arGadgetParams["GROUPS_LIST"]):
			?><ul><?
			foreach ($arGadgetParams["GROUPS_LIST"] as $group)
			{
				echo "<li><a href=\"".$group["GROUP_URL"]."\">";
				echo $group["GROUP_NAME"];
				echo "</a></li>";
			}
			?></ul>
			<a href="<?= $arGadgetParams["URL_GROUPS"] ?>"><?= GetMessage("GD_SONET_USER_GROUPS_ALL_GROUPS") ?></a>
			<br /><?
		else:
			?><?= GetMessage("GD_SONET_USER_GROUPS_NO_GROUPS") ?>
			<br><br><?
		endif;
	else:
		?><?= GetMessage("GD_SONET_USER_GROUPS_GR_UNAVAIL") ?>
		<br><br><?
	endif;
	
	if ($arGadgetParams["IS_CURRENT_USER"]):
		if ($arGadgetParams["CAN_CREATE_GROUP"]):
			?><a href="<?= $arGadgetParams["URL_GROUPS_ADD"] ?>"><?= GetMessage("GD_SONET_USER_GROUPS_CREATE_GROUP") ?></a><br /><?
		endif;
		?><a href="<?= $arGadgetParams["URL_GROUPS_SEARCH"] ?>"><?= GetMessage("GD_SONET_USER_GROUPS_SEARCH_GROUP") ?></a><br />
		<a href="<?= $arGadgetParams["URL_LOG_GROUPS"] ?>"><?= GetMessage("GD_SONET_USER_GROUPS_LOG") ?></a><?
	endif;
	?></td>
</tr>
</table>