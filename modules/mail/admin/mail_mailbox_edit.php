<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2005 Bitrix             #
# https://www.bitrixsoft.com                 #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mail/prolog.php");

ClearVars();

$message = null;
$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);

Bitrix\Main\Loader::includeModule('mail');

$err_mess = "File: ".__FILE__."<br>Line: ";

$strError = "";
$ID = intval($ID);

$bCanUseTLS = (defined('BX_MAIL_FORCE_USE_TLS') && BX_MAIL_FORCE_USE_TLS === true) || function_exists('openssl_open');

if($_SERVER['REQUEST_METHOD']=="POST" && ($save <> '' || $save_ext <> '' || $apply <> '') && $MOD_RIGHT=="W" && check_bitrix_sessid())
{
	$arFields = array(
		'ACTIVE'          => $ACTIVE,
		'LID'             => $LID,
		'SERVICE_ID'      => $SERVICE_ID,
		'NAME'            => $NAME,
		'SERVER'          => $SERVER,
		'PORT'            => $PORT,
		'RELAY'           => $RELAY,
		'AUTH_RELAY'      => $AUTH_RELAY,
		'DOMAINS'         => $DOMAINS,
		'SERVER_TYPE'     => $SERVER_TYPE,
		'LOGIN'           => $LOGIN,
		'PASSWORD'        => $PASSWORD,
		'CHARSET'         => $CHARSET,
		'USE_MD5'         => $USE_MD5,
		'DELETE_MESSAGES' => $DELETE_MESSAGES,
		'PERIOD_CHECK'    => $PERIOD_CHECK,
		'DESCRIPTION'     => $DESCRIPTION,
		'MAX_MSG_COUNT'   => $MAX_MSG_COUNT,
		'MAX_MSG_SIZE'    => ((int) $MAX_MSG_SIZE) * 1024,
		'MAX_KEEP_DAYS'   => (int) $MAX_KEEP_DAYS,
		'USE_TLS'         => $bCanUseTLS && $USE_TLS == 'Y' ? ($SKIP_CERT == 'Y' ? 'S' : 'Y') : 'N',
		'USER_ID'         => $USER_ID,
		'LINK'            => $LINK
	);

	if ($ID > 0)
	{
		if ($arFields['PASSWORD'] == '')
			unset($arFields['PASSWORD']);

		$res = CMailbox::Update($ID, $arFields);
	}
	else
	{
		$ID = CMailbox::Add($arFields);
		$res = $ID > 0;
	}

	if (!$res)
	{
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("MAIL_MBOX_EDT_ERROR"), $e);
	}
	else
	{
		if ($save_ext <> '' && $filter_type != '')
			LocalRedirect("mail_filter_edit.php?lang=".LANG."&filter_type=".$filter_type."&find_mailbox_id=".$ID);
		elseif ($save <> '')
			LocalRedirect("mail_mailbox_admin.php?lang=".LANG);
		else
			LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ID=".$ID);
	}
}
$mb = CMailbox::GetByID($ID);
if (!$mb->ExtractFields("str_"))
	$ID = 0;

if($ID===0){
	$str_SERVER_TYPE = $mailbox_type == 'user' ? 'imap' : 'pop3';
	$str_PORT        = $str_SERVER_TYPE == 'imap' ? ($bCanUseTLS ? '993' : '143') : '110';
	$str_USE_TLS     = $str_SERVER_TYPE == 'imap' && $bCanUseTLS ? 'Y' : 'N';
	$str_ACTIVE      = 'Y';
	$str_AUTH_RELAY  = 'Y';
	$str_RELAY       = 'Y';
}

if ($message)
	$DB->InitTableVarsForEdit("b_mail_mailbox", "", "str_");

$sDocTitle = ($ID > 0) ? preg_replace("'#ID#'i", $ID, GetMessage("MAIL_MBOX_EDT_TITLE_1")) : GetMessage("MAIL_MBOX_EDT_TITLE_2");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT" => GetMessage("MAIL_MBOX_EDT_BACK_LINK"),
		"LINK" => "mail_mailbox_admin.php?lang=".LANG
	)
);

if ($ID > 0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"ICON" => "btn_new",
		"TEXT" => GetMessage("MAIL_MBOX_EDT_NEW"),
		"LINK" => "mail_mailbox_edit.php?lang=".LANG
	);

	if ($MOD_RIGHT=="W")
	{
		$aMenu[] = array(
			"TEXT" => GetMessage("MAIL_MBOX_EDT_DEL"),
			"ICON" => "btn_delete",
			"LINK" => "javascript:if(confirm('".GetMessage("MAIL_MBOX_EDT_DEL_CONFIRM")."'))window.location='mail_mailbox_admin.php?action=delete&ID=".$ID."&lang=".LANG."&".bitrix_sessid_get()."';",
		);
	}
}
//echo ShowSubMenu($aMenu);

$context = new CAdminContextMenu($aMenu);
$context->Show();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIL_MBOX_EDT_TAB"), "ICON"=>"mail_mailbox_edit", "TITLE"=>$sDocTitle),
);
if (in_array($str_SERVER_TYPE, array('pop3', 'smtp')))
	$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("MAIL_MBOX_EDT_TAB2"), "ICON"=>"mail_mailbox_edit", "TITLE"=>$sDocTitle);
$tabControl = new CAdminTabControl("tabControl", $aTabs);


if (in_array($str_SERVER_TYPE, array('imap', 'domain', 'crdomain', 'controller')))
{
	$mailServices = array();
	$result = Bitrix\Mail\MailServicesTable::getList(array(
		'filter' => array('ACTIVE' => 'Y'),
		'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC')
	));
	while (($service = $result->fetch()) !== false)
	{
		if (!isset($mailServices[$service['SITE_ID']]))
			$mailServices[$service['SITE_ID']] = array();

		$mailServices[$service['SITE_ID']][$service['ID']] = $service;
	}
}

?>

<?
if ($message)
	echo $message->Show();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>&ID=<?=$ID?>" name="form1">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>

<?$tabControl->Begin();?>
<?$tabControl->BeginNextTab();?>
	<?if($ID>0):?>
	<tr>
		<td><?echo GetMessage("MAIL_MBOX_EDT_ID")?></td>
		<td><?echo $str_ID?></td>
	</tr>
	<?endif?>
	<?if($str_TIMESTAMP_X <> ''):?>
	<tr>
		<td><?echo GetMessage("MAIL_MBOX_EDT_DATECH")?></td>
		<td><?echo $str_TIMESTAMP_X?></td>
	</tr>
	<? endif; ?>
	<tr>
		<td width="40%"><?=GetMessage("MAIL_MBOX_EDT_LANG"); ?> </td>
		<td width="60%">
			<? $defSite = false; ?>
			<? if ($ID > 0) { ?>
			<? $defSite = $str_LID; ?>
			<? $result = Bitrix\Main\SiteTable::getList(array('filter' => array('LID' => $str_LID), 'order' => array('SORT' => 'ASC'))); ?>
			<? $site = $result->fetch(); ?>
			[<?=$str_LID; ?>] <?=htmlspecialcharsbx($site['NAME']); ?>
			<? } else { ?>
			<select id="mailbox_site_id" name="LID"<? if ($mailbox_type == 'user') { ?> onchange="changeServicesList();"<? } ?>>
			<? $result = Bitrix\Main\SiteTable::getList(array('order' => array('SORT' => 'ASC'))); ?>
			<? while (($site = $result->fetch()) !== false) { ?>
				<? $defSite = $defSite ?: ($mailbox_type != 'user' || !empty($mailServices[$site['LID']]) ? $site['LID'] : false); ?>
				<option value="<?=$site['LID']; ?>"<? if ($mailbox_type == 'user' && empty($mailServices[$site['LID']])) { ?> disabled="disabled"<? } ?>>
					<?=htmlspecialcharsbx($site['NAME']); ?><? if ($mailbox_type == 'user' && empty($mailServices[$site['LID']])) { ?> *<? } ?>
				</option>
			<? } ?>
			</select>
			<? } ?>
		</td>
	</tr>
	<? if (in_array($str_SERVER_TYPE, array('imap', 'domain', 'crdomain', 'controller'))) { ?>
	<tr>
		<td width="40%"><?=GetMessage("MAIL_MBOX_EDT_SERVICE"); ?> </td>
		<td width="60%">
			<? if ($str_SERVER_TYPE != 'imap' && $ID > 0) { ?>
			[<?=$str_SERVICE_ID; ?>] <?=htmlspecialcharsbx($mailServices[$str_LID][$str_SERVICE_ID]['NAME']) ?>
			<? } else { ?>
			<select id="mailbox_service_id" name="SERVICE_ID" onchange="changeFields();">
			<?
			if(is_array($mailServices[$defSite])):
				foreach ($mailServices[$defSite] as $service) { ?>
					<? if ($service['SERVICE_TYPE'] != 'imap') continue; ?>
					<option value="<?=$service['ID']; ?>"
						<? if ($str_SERVICE_ID == $service['ID']) { ?> selected="selected"<? } ?>>
						[<?=$service['ID']; ?>] <?=htmlspecialcharsbx($service['NAME']) ?>
					</option>
			<?
				}
			endif;?>
			</select>
			<? } ?>
		</td>
	</tr>
	<? } ?>
	<tr>
		<td><?echo GetMessage("MAIL_MBOX_EDT_ACT")?></td>
		<td><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("MAIL_MBOX_EDT_NAME")?></td>
		<td><input id="mailbox_name" type="text" name="NAME" size="53" maxlength="255" value="<?=$str_NAME?>"></td>
	</tr>
	<? if (in_array($str_SERVER_TYPE, array('pop3', 'smtp'))) { ?>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIL_MBOX_EDT_DESC")?></td>
		<td><textarea name="DESCRIPTION" cols="40" rows="5"><?echo $str_DESCRIPTION?></textarea>
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIL_MBOX_SERVER_TYPE")?></td>
		<td>
			<select onchange="change_type()" name="SERVER_TYPE" id="SERVER_TYPE">
				<option value="pop3"<? if ($str_SERVER_TYPE == 'pop3') { ?> selected="selected"<? } ?>><?=GetMessage('MAIL_MBOX_SERVER_TYPE_POP3'); ?></option>
				<option value="smtp"<? if ($str_SERVER_TYPE == 'smtp') { ?> selected="selected"<? } ?>><?=GetMessage('MAIL_MBOX_SERVER_TYPE_SMTP'); ?></option>
			</select><br>
			<div id="el0" class="pop3"><?=GetMessage('MAIL_MBOX_SERVER_TYPE_POP3_DESC'); ?></div>
			<div id="el1" class="smtp"><?=GetMessage('MAIL_MBOX_SERVER_TYPE_SMTP_DESC'); ?><br><?=GetMessage('MAIL_MBOX_SERVER_TYPE_SMTP_A'); ?></div>
		</td>
	</tr>
	<? } ?>
	<tr id="el2" class="pop3 imap smtp adm-detail-required-field">
		<td>
			<div id="el3" class="pop3"><?=GetMessage('MAIL_MBOX_EDT_SERVER'); ?></div>
			<div id="el4" class="imap"><?=GetMessage('MAIL_MBOX_EDT_SERVER_IMAP'); ?></div>
			<div id="el5" class="smtp"><?=GetMessage('MAIL_MBOX_SERVER_HOST'); ?><br><?=GetMessage('MAIL_MBOX_SERVER_HOST_AST'); ?></div>
		</td>
		<td><input id="mailbox_server" type="text" name="SERVER" size="42" maxlength="255" value="<?=$str_SERVER; ?>">:<input type="text" id="PORT_PORT" name="PORT" size="4" maxlength="5" value="<?=$str_PORT; ?>"></td>
	</tr>
	<tr id="el6" class="smtp">
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIL_MBOX_DOM")?><br> <?echo GetMessage("MAIL_MBOX_DOM_EMPTY")?></td>
		<td><textarea id="DOMAINS" name="DOMAINS" onkeyup="chdom()" cols="40" rows="4" onchange="chdom()"><?=$str_DOMAINS?></textarea></td>
	</tr>
	<tr id="el7" class="smtp">
		<td></td>
		<td>
			<input type="hidden" name="RELAY" value="N"><input type="hidden" name="AUTH_RELAY" value="N">
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="s2" name="RELAY" onclick="chrelay()" value="Y"<?if($str_RELAY=="Y")echo " checked"?>></div>
					<div class="adm-list-label"><label for="s2" id="s4"><?echo GetMessage("MAIL_MBOX_RELAY")?></label></div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="s3" name="AUTH_RELAY" value="Y"<?if($str_AUTH_RELAY=="Y")echo " checked"?>></div>
					<div class="adm-list-label"><label for="s3" id="s5"><?echo GetMessage("MAIL_MBOX_RELAY_AUTH")?></label></div>
				</div>
			</div>
		<td>
	</tr>
	<tr id="el8" class="pop3 imap">
		<td><?=GetMessage("MAIL_MBOX_EDT_USE_TLS"); ?><span class="required"><sup>1</sup></span></td>
		<td><input type="checkbox" name="USE_TLS" id="USE_TLS" value="Y"<? if ($str_USE_TLS == 'Y' || $str_USE_TLS == 'S') { ?> checked<? } ?> onclick="change_port();"<? if (!$bCanUseTLS) { ?> disabled<? } ?>></td>
	</tr>
	<tr id="el23" class="pop3 imap">
		<td><?=GetMessage('MAIL_MBOX_EDT_SKIP_CERT'); ?></td>
		<td><input type="checkbox" name="SKIP_CERT" id="SKIP_CERT" value="Y"<? if ($str_USE_TLS == 'S') { ?> checked<? } ?><? if ($str_USE_TLS != 'Y' && $str_USE_TLS != 'S') { ?> disabled<? } ?>></td>
	</tr>
	<tr id="el9" class="pop3 imap domain crdomain controller adm-detail-required-field">
		<td><?=GetMessage("MAIL_MBOX_EDT_LOGIN"); ?></td>
		<td><input type="text" name="LOGIN" size="53" maxlength="255" value="<?=$str_LOGIN?>"></td>
	</tr>
	<tr id="el10" class="pop3 imap adm-detail-required-field">
		<td><?=GetMessage("MAIL_MBOX_EDT_PASSWORD"); ?></td>
		<td><input type="password" name="PASSWORD" size="53" maxlength="255" value="<?=($MOD_RIGHT >= 'W' && $str_USER_ID == 0 ? $str_PASSWORD : ''); ?>"></td>
	</tr>
	<tr id="el11" class="imap adm-detail-required-field">
		<td><?=GetMessage("MAIL_MBOX_EDT_LINK"); ?></td>
		<td><input id="mailbox_link" type="text" name="LINK" size="53" maxlength="255" value="<?=$str_LINK; ?>"></td>
	</tr>
	<tr id="el12" class="imap domain crdomain controller adm-detail-required-field">
		<td><?=GetMessage("MAIL_MBOX_EDT_USER_ID"); ?></td>
		<td><?=FindUserID("USER_ID", $str_USER_ID); ?></td>
	</tr>
	<tr id="el13" class="pop3">
		<td><?=GetMessage("MAIL_MBOX_EDT_USE_APOP"); ?></td>
		<td><input type="checkbox" name="USE_MD5" value="Y"<?if($str_USE_MD5=="Y")echo " checked"?>></td>
	</tr>
	<tr id="el14" class="pop3">
		<td><?=GetMessage("MAIL_MBOX_EDT_DEL_AFTER_RETR"); ?></td>
		<td><input type="checkbox" name="DELETE_MESSAGES" value="Y"<?if($str_DELETE_MESSAGES=="Y")echo " checked"?>></td>
	</tr>
	<tr id="el15" class="pop3">
		<td><?=GetMessage("MAIL_MBOX_EDT_PERIOD"); ?></td>
		<td><input type="text" name="PERIOD_CHECK" size="5" maxlength="18" value="<?echo $str_PERIOD_CHECK?>"> <?echo GetMessage("MAIL_MBOX_EDT_PERIOD_MIN")?></td>
	</tr>

<? if ($ID <= 0) { ?>

	<tr id="el16" class="heading pop3 smtp">
		<td align="center" colspan="2"><?echo GetMessage("MAIL_MBOX_EDT_ADD_NEW_RULE")?></td>
	</tr>
	<tr id="el17" class="pop3 smtp">
		<td align="center" colspan="2" class="adm-detail-valign-top">
			<select name="filter_type">
				<option value="manual"<?if($filter_type==$a_ID)echo ' manual'?>><?echo GetMessage("MAIL_MBOX_EDT_ADD_NEW_RULE_MANUAL")?></option>
				<? $res = CMailFilter::GetFilterList();
				while($ar = $res->ExtractFields("a_")) { ?>
				<option value="<?=$a_ID?>"<?if($filter_type==$a_ID)echo ' selected'?>><?=$a_NAME?></option>
				<? } ?>
			</select>&nbsp;<input type="submit" <?if ($MOD_RIGHT<"W") echo "disabled" ?> name="save_ext" value="<?echo GetMessage("MAIL_MBOX_EDT_ADD")?>">
		</td>
	</tr>

<? } ?>

<? if (in_array($str_SERVER_TYPE, array('pop3', 'smtp'))) { ?>

<? $tabControl->BeginNextTab(); ?>

	<? $db_max_allowed = $DB->Query("SHOW VARIABLES LIKE 'MAX_ALLOWED_PACKET'", true);
	if ($db_max_allowed !== false && ($ar_max_allowed = $db_max_allowed->Fetch()) !== false && intval($ar_max_allowed["Value"]) > 0)
	{
		$B_MAIL_MAX_ALLOWED = intval($ar_max_allowed["Value"]);
		?><tr id="el18" class="pop3 smtp">
			<td><?echo GetMessage("MAIL_MBOX_EDT_MAX_ALLOWED")?></td>
			<td><?echo $B_MAIL_MAX_ALLOWED/1024?> <?echo GetMessage("MAIL_MBOX_EDT_MAX_ALLOWED_KB")?></td>
		</tr><?
	} ?>

	<tr id="el19" class="pop3 smtp">
		<td width="40%"><?echo GetMessage("MAIL_MBOX_EDT_MAX_MSGS")?></td>
		<td width="60%"><input type="text" name="MAX_MSG_COUNT" size="5" maxlength="18" value="<?echo $str_MAX_MSG_COUNT?>"> <?echo GetMessage("MAIL_MBOX_EDT_MAX_MSGS_CNT")?></td>
	</tr>
	<tr id="el20" class="pop3">
		<td><?echo GetMessage("MAIL_MBOX_EDT_MAX_SIZE")?></td>
		<td><input type="text" name="MAX_MSG_SIZE" size="5" maxlength="18" value="<?echo intval($str_MAX_MSG_SIZE/1024)?>"> <?echo GetMessage("MAIL_MBOX_EDT_MAX_SIZE_KB")?></td>
	</tr>
	<tr id="el21"  class="pop3 smtp">
		<td><?echo GetMessage("MAIL_MBOX_EDT_KEEP_DAYS")?></td>
		<td><input type="text" name="MAX_KEEP_DAYS" size="5" maxlength="18" value="<?echo intval($str_MAX_KEEP_DAYS)?>"> <?echo GetMessage("MAIL_MBOX_EDT_KEEP_DAYS_D")?></td>
	</tr>
	<tr id="el22"  class="pop3 smtp">
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIL_MBOX_EDT_CHARSET")?><br><?echo GetMessage("MAIL_MBOX_EDT_CHARSET_RECOMND")?></td>
		<td>
<? $chs = Array(
	"utf-8",
	"iso-8859-1",
	"iso-8859-2",
	"iso-8859-3",
	"iso-8859-4",
	"iso-8859-5",
	"iso-8859-6",
	"iso-8859-7",
	"iso-8859-8",
	"iso-8859-9",
	"iso-8859-10",
	"iso-8859-13",
	"iso-8859-14",
	"iso-8859-15",
	"windows-1251",
	"windows-1252",
	"cp866",
	"koi8-r"
); ?>
			<select onchange="BX('CHARSET').value = this.value">
				<option value=""></option>
				<option value=""<?if($str_CHARSET=="")echo ' selected'?>><?echo GetMessage("MAIL_MBOX_EDT_CHARSET_RECOMND_TEXT")?></option>
				<?foreach($chs as $ch):?>
					<option value="<?=$ch?>"<?if(mb_strtolower($ch) == mb_strtolower($str_CHARSET))echo ' selected'?>><?=$ch?></option>
				<?endforeach?>
			</select>
			<input type="text" name="CHARSET" id="CHARSET" size="12" maxlength="255" value="<?=$str_CHARSET?>">

		</td>
	</tr>

<? } ?>

	<? if (in_array($str_SERVER_TYPE, array('imap', 'domain', 'crdomain', 'controller'))) { ?>
	<input type="hidden" value="<?=$str_SERVER_TYPE; ?>" name="SERVER_TYPE" id="SERVER_TYPE">
	<? } ?>
	<input type="hidden" value="Y" name="apply">

<script>

	function change_type()
	{
		<? if (in_array($str_SERVER_TYPE, array('imap', 'domain', 'crdomain', 'controller'))) { ?>
		var serverType = document.getElementById('SERVER_TYPE').value;
		<? } else { ?>
		var serverTypeSelect = document.getElementById('SERVER_TYPE');
		var serverType = serverTypeSelect.options[serverTypeSelect.selectedIndex].value;
		<? } ?>

		for (var i = 0; i <= 23; i++)
		{
			var d = document.getElementById('el'+i);

			if (d)
				d.style.display = BX.hasClass(d, serverType) ? '' : 'none';
		}

		change_port();
	}
	setTimeout(change_type, 0);

	function chdom()
	{
		var domainsListEmpty = document.getElementById('DOMAINS').value.length <= 0;

		document.getElementById('s2').disabled = domainsListEmpty;
		document.getElementById('s3').disabled = domainsListEmpty;
		document.getElementById('s4').disabled = domainsListEmpty;
		document.getElementById('s5').disabled = domainsListEmpty;

		chrelay();
	}

	function chrelay()
	{
		var relay = document.getElementById('s2');

		document.getElementById('s3').disabled = (!relay.checked || relay.disabled);
		document.getElementById('s5').disabled = (!relay.checked || relay.disabled);
	}
	setTimeout(chdom, 0);

	function change_port()
	{
		var serverPort = document.getElementById('PORT_PORT');

		if (BX.util.in_array(serverPort.value, [25, 110, 143, 993, 995]))
		{
			<? if (in_array($str_SERVER_TYPE, array('imap', 'domain', 'crdomain', 'controller'))) { ?>
			var serverType = document.getElementById('SERVER_TYPE').value;
			<? } else { ?>
			var serverTypeSelect = document.getElementById('SERVER_TYPE');
			var serverType = serverTypeSelect.options[serverTypeSelect.selectedIndex].value;
			<? } ?>

			switch (serverType)
			{
				case 'pop3':
					serverPort.value = document.getElementById('USE_TLS').checked ? 995 : 110;
					if (document.getElementById('SKIP_CERT'))
						document.getElementById('SKIP_CERT').disabled = !document.getElementById('USE_TLS').checked;
					break;
				case 'imap':
					serverPort.value = document.getElementById('USE_TLS').checked ? 993 : 143;
					if (document.getElementById('SKIP_CERT'))
						document.getElementById('SKIP_CERT').disabled = !document.getElementById('USE_TLS').checked;
					break;
				case 'smtp':
					serverPort.value = 25;
					break;
			}
		}
	}

	<? if (in_array($str_SERVER_TYPE, array('imap', 'domain', 'crdomain', 'controller'))) { ?>
	var servicesMap = {
		<? foreach ($mailServices as $site => $services) { ?>
		'<?=$site; ?>': [
			<? foreach ($services as $service => $settings) { ?>
			'<?=$service; ?>',
			<? } ?>
		],
		<? } ?>
	};
	var services = {
		<? foreach ($mailServices as $site => $services) { ?>
		<? foreach ($services as $service => $settings) { ?>
		'<?=$service; ?>': {
			'name': '<?=CUtil::jsEscape($settings['NAME']) ?>',
			'link': '<?=CUtil::jsEscape($settings['LINK']) ?>',
			'server': '<?=CUtil::jsEscape($settings['SERVER']) ?>',
			'port': '<?=$settings['PORT']; ?>',
			'encryption': '<?=$settings['ENCRYPTION']; ?>',
			'type': '<?=$settings['SERVICE_TYPE']; ?>'
		},
		<? } ?>
		<? } ?>
	};
	function changeServicesList()
	{
		var siteIdSelect = document.getElementById('mailbox_site_id');
		var siteId = siteIdSelect.options[siteIdSelect.selectedIndex].value;

		var serviceSelect = document.getElementById('mailbox_service_id');

		while (serviceSelect.options.length > 0)
			serviceSelect.options.remove(0);

		if (typeof servicesMap[siteId] == 'undefined')
			return;

		for (var i in servicesMap[siteId])
		{
			var serviceId = servicesMap[siteId][i];
			var service = services[serviceId];
			var option = document.createElement('option');
				option.value = serviceId;
				option.text = '['+serviceId+'] '+service['name'];
			serviceSelect.options.add(option);
		}

		changeFields();
	}

	function changeFields()
	{
		var siteIdSelect = document.getElementById('mailbox_site_id');
		var siteId = siteIdSelect.options[siteIdSelect.selectedIndex].value;

		var serviceSelect = document.getElementById('mailbox_service_id');
		var serviceId = serviceSelect.options[serviceSelect.selectedIndex].value;

		if (typeof services[serviceId] == 'undefined')
			return;

		var service = services[serviceId];

		var serverType = document.getElementById('SERVER_TYPE');

		var mailboxName = document.getElementById('mailbox_name');
		var mailboxServer = document.getElementById('mailbox_server');
		var mailboxPort = document.getElementById('PORT_PORT');
		var mailboxEncryption = document.getElementById('USE_TLS');
		var mailboxAllowSkipCert = document.getElementById('SKIP_CERT');
		var mailboxLink = document.getElementById('mailbox_link');

		serverType.value = service['type'];

		mailboxName.value = service['name'];
		mailboxServer.value = service['server'];
		mailboxPort.value = service['port'] > 0 ? service['port'] : '';
		mailboxEncryption.checked = service['encryption'] == 'N' ? false : true;
		if (mailboxAllowSkipCert)
			mailboxAllowSkipCert.checked = service['encryption'] == 'S';
		mailboxLink.value = service['link'];

		change_type();
	}
	<? if ($ID <= 0) { ?>
	setTimeout(changeFields, 0);
	<? } ?>
	<? } ?>

</script>

<?$tabControl->EndTab();?>
<?$tabControl->Buttons(Array("disabled"=>$MOD_RIGHT<"W","back_url" =>"mail_mailbox_admin.php?lang=".LANG));?>
<?$tabControl->End();?>
</form>
<?$tabControl->ShowWarnings("form1", $message);?>

<?=BeginNote(); ?>
<span class="required"><sup>1</sup></span> <?=GetMessage('MAIL_MBOX_EDT_COMMENT1')?>
<? if (in_array($str_SERVER_TYPE, array('imap', 'domain', 'crdomain', 'controller'))) { ?><br>
<span class="required">*</span> <?=GetMessage('MAIL_MBOX_EDT_COMMENT_ASTERISK'); ?>
<? } ?>
<?=EndNote(); ?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
