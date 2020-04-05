<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Converter;

/**
 * @global int $ID - Edited user id
 * @global string $strError - Save error
 * @global \CUser $USER
 * @global CMain $APPLICATION
 */

Loc::loadMessages(__FILE__);

$ID = intval($ID);

if(
	$ID > 0
	&& \Bitrix\Main\Loader::includeModule('socialservices')
	&& \Bitrix\Main\Config\Option::get("socialservices", "bitrix24net_id", "") != ""
):
	$dbRes = \Bitrix\Socialservices\UserTable::getList(array(
		'filter' => array(
			'=USER_ID' => $ID,
			'=EXTERNAL_AUTH_ID' => CSocServBitrix24Net::ID
		),
	));

	$profileInfo = $dbRes->fetch();
?>
	<input type="hidden" name="profile_module_id[]" value="socialservices">
<?
	if(!$profileInfo)
	{
?>
		<tr>
			<td>
				<?=BeginNote()?>
<?
		if($ID == $USER->GetID()):
			$url = \Bitrix\Socialservices\Network::getAuthUrl("popup", array("admin"));
?>
				<?=Loc::getMessage("SS_USERTAB_NOT_CONNECTED_SELF")?> <input type="button" onclick="BX.util.popup('<?=CUtil::JSEscape($url)?>', 700, 500);" class="adm-btn-green" value="<?=Loc::getMessage("SS_USERTAB_CREATE_LINK")?>">
<?
		else:
?>
				<?=Loc::getMessage("SS_USERTAB_NOT_CONNECTED_OTHER")?>
<?
		endif;
?>
				<?=EndNote();?>
			</td>
		</tr>
<?
	}
	else
	{
?>
		<tr>
			<td>
				<?=BeginNote()?>
				<span id="ss_network_profile"><?=Loc::getMessage("SS_USERTAB_LINKED_PROFILE")?>: <a href="<?= Converter::getHtmlConverter()->encode($profileInfo["PERSONAL_WWW"])?>" target="_blank"><?=Converter::getHtmlConverter()->encode($profileInfo["NAME"]." ".$profileInfo["LAST_NAME"]." (".$profileInfo["EMAIL"].")")?></a></span><br /><br /><input type="checkbox" name="SS_REMOVE_NETWORK" id="ss_remove_network" value="Y" onclick="BX('ss_network_profile').style.textDecoration = this.checked ? 'line-through' : 'none'"> <label for="ss_remove_network"><?=Loc::getMessage("SS_USERTAB_DELETE_LINK")?></label>

				<?=EndNote();?>
			</td>
		</tr>
<?
	}
?>
<?
endif;