<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 * @global array $arResult
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Converter;

if(count($arResult["CONTACTS"]) > 0)
{
?>
	<script>
		function networkConnect(node, id, connections)
		{
			if(!!connections)
			{
				if(connections.length > 1)
				{
					var connMenu = [];
					for(var i = 0; i < connections.length; i++)
					{
						connMenu.push({
							text: connections[i].portal,
							onclick: 'BXIM.openMessenger(\''+connections[i].id+'\')'
						});
					}

					BX.PopupMenu.show('connection_menu_' + id, node, connMenu);
				}
				else
				{
					BXIM.openMessenger(connections[0].id);
				}
			}
		}
	</script>
	<div>
<?
	foreach($arResult["CONTACTS"] as $contact):
?>
		<div class="mycontants_card b_4">
			<div onclick="networkConnect(this, <?=$contact["ID"]?>, <?=Converter::getHtmlConverter()->encode(\CUtil::PhpToJSObject($contact["CONNECT"]))?>)" class="mycontants_card_container">
				<div class="mycontants_card_avatar" <?if($contact["CONTACT_PHOTO"] != ''):?>style="background-image: url('<?= Converter::getHtmlConverter()->encode($contact["CONTACT_PHOTO"])?>');"<?endif;?>></div>
				<div class="mycontants_card_name">
					<a href="javascript:void(0)"><?=Converter::getHtmlConverter()->encode($contact["NAME_FORMATTED"])?></a>
				</div>
				<a class="mycontants_card_action" href="javascript:void(0)"><?=Loc::getMessage('SC_T_SEND_MESSAGE')?></a>
				<div style="clear: both;"></div>
			</div>
		</div>
<?
	endforeach;
?>
	</div>
	<div style="clear: both"></div>
<?
	$APPLICATION->IncludeComponent(
		"bitrix:main.pagenavigation",
		"",
		array(
			"NAV_OBJECT" => $arResult["NAV"],
			"SEF_MODE" => "N",
		),
		false
	);
}
else
{
	ShowNote(Loc::getMessage("SC_T_NO_CONTACTS"));
}
