<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mail/prolog.php");

ClearVars();

$message = null;
$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if ($MOD_RIGHT < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
Bitrix\Main\Loader::includeModule('mail');

$err_mess = "File: ".__FILE__."<br>Line: ";

$strError = "";
$ID = intval($ID);

$bCanUseTLS = (defined('BX_MAIL_FORCE_USE_TLS') && BX_MAIL_FORCE_USE_TLS === true) || function_exists('openssl_open');

$str_ACTIVE = 'Y';
$str_SORT   = 100;

$ms = Bitrix\Mail\MailServicesTable::getById($ID)->fetch();
if ($ms)
{
	$str_ID         = $ms['ID'];
	$str_SITE_ID    = $ms['SITE_ID'];
	$str_ACTIVE     = $ms['ACTIVE'];
	$str_TYPE       = $ms['SERVICE_TYPE'];
	$str_NAME       = htmlspecialcharsbx($ms['NAME']);
	$str_SERVER     = htmlspecialcharsbx($ms['SERVER']);
	$str_PORT       = $ms['PORT'];
	$str_ENCRYPTION = $ms['ENCRYPTION'];
	$str_LINK       = htmlspecialcharsbx($ms['LINK']);
	$str_ICON       = $ms['ICON'];
	$str_TOKEN      = htmlspecialcharsbx($ms['TOKEN']);
	$str_SORT       = $ms['SORT'];
}
else
{
	$ID = 0;
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && ($save <> '' || $apply <> '') && $MOD_RIGHT == "W" && check_bitrix_sessid())
{
	$ICON = $_FILES['ICON'];
	$ICON['old_file'] = $str_ICON;
	$ICON['del'] = $remove_icon;

	$arFields = array(
		'SITE_ID'    => $SITE_ID,
		'ACTIVE'     => $ACTIVE ?: 'N',
		'NAME'       => $NAME,
		'SERVER'     => $SERVER,
		'PORT'       => $PORT ?: null,
		'ENCRYPTION' => $TYPE == 'imap' ? ($bCanUseTLS ? $ENCRYPTION : 'N') : ($ENCRYPTION == 'N' ? 'N' : 'Y'),
		'LINK'       => $LINK,
		'ICON'       => $ICON,
		'TOKEN'      => $TOKEN,
		'SORT'       => $SORT
	);

	if ($ID > 0)
	{
		$result = Bitrix\Mail\MailServicesTable::update($ID, $arFields);
	}
	else
	{
		$arFields['SERVICE_TYPE'] = $TYPE;

		$result = Bitrix\Mail\MailServicesTable::add($arFields);
		$ID = $result->isSuccess() ? $result->getId() : 0;
	}

	if (!$result->isSuccess())
	{
		$message = new CAdminMessage(array(
			'MESSAGE' => GetMessage("MAIL_MSERVICE_EDT_ERROR"),
			'DETAILS' => join('<br>', $result->getErrorMessages())
		));
	}
	else
	{
		if ($save <> '')
			LocalRedirect("mail_mailservice_admin.php?lang=".LANG);
		else
			LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ID=".$ID);
	}
}

if ($message)
{
	$DB->InitTableVarsForEdit("b_mail_mailservices", "", "str_");
	$str_ICON = null;
}

$sDocTitle = ($ID > 0) ? preg_replace("'#ID#'i", $ID, GetMessage("MAIL_MSERVICE_EDT_TITLE_1")) : GetMessage("MAIL_MSERVICE_EDT_TITLE_2");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT" => GetMessage("MAIL_MSERVICE_EDT_BACK_LINK"),
		"LINK" => "mail_mailservice_admin.php?lang=".LANG
	)
);

if ($ID > 0)
{
	$aMenu[] = array("SEPARATOR" => "Y");
	$aMenu[] = array(
		"ICON" => "btn_new",
		"TEXT" => GetMessage("MAIL_MSERVICE_EDT_NEW"),
		"LINK" => "mail_mailservice_edit.php?lang=".LANG
	);

	if ($MOD_RIGHT == "W")
	{
		$aMenu[] = array(
			"TEXT" => GetMessage("MAIL_MSERVICE_EDT_DELETE"),
			"ICON" => "btn_delete",
			"LINK" => "javascript:if(confirm('".GetMessage("MAIL_MSERVICE_EDT_DELETE_CONFIRM")."'))window.location='mail_mailservice_admin.php?action=delete&ID=".$ID."&lang=".LANG."&".bitrix_sessid_get()."';",
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIL_MSERVICE_EDT_TAB"), "ICON" => "mail_mailbox_edit", "TITLE" => $sDocTitle),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);


?>

<? if ($message) echo $message->Show(); ?>
<form method="POST" action="<?=$APPLICATION->GetCurPage(); ?>?lang=<?=LANG; ?>&ID=<?=$ID; ?>" name="form1" enctype="multipart/form-data">
<?=bitrix_sessid_post(); ?>
<?=GetFilterHiddens("find_"); ?>

<? $tabControl->Begin(); ?>
<? $tabControl->BeginNextTab(); ?>
	<? if ($ID > 0) { ?>
	<tr>
		<td><?=GetMessage("MAIL_MSERVICE_EDT_ID"); ?></td>
		<td><?=$str_ID; ?></td>
	</tr>
	<? } ?>
	<tr>
		<td width="40%"><?=GetMessage("MAIL_MSERVICE_EDT_SITE_ID"); ?></td>
		<td width="60%">
			<select name="SITE_ID">
				<? $result = Bitrix\Main\SiteTable::getList(array('filter' => array('ACTIVE' => 'Y'), 'order' => array('SORT' => 'ASC'))); ?>
				<? while (($site = $result->fetch()) !== false): ?>
					<option value="<?=$site['LID'] ?>" <? if ($str_SITE_ID == $site['LID']) echo 'selected'; ?>><?=htmlspecialcharsbx($site['NAME']) ?></option>
				<? endwhile ?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("MAIL_MSERVICE_EDT_ACT"); ?></td>
		<td><input type="checkbox" name="ACTIVE" value="Y"<? if ($str_ACTIVE == "Y") { ?> checked="checked"<? } ?>></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage('MAIL_MSERVICE_EDT_ICON'); ?></td>
		<td>
			<input type="file" name="ICON">
			<? if ($icon = Bitrix\Mail\MailServicesTable::getIconSrc($str_NAME, $str_ICON)) { ?>
			<br><br><img src="<?=$icon; ?>" alt="<?=$str_NAME; ?>"><br>
			<? if ($str_ICON) { ?>
			<input type="checkbox" name="remove_icon" value="Y" id="remove_icon" >
			<label for="remove_icon"><?=GetMessage("MAIL_MSERVICE_EDT_ICON_REMOVE"); ?></label>
			<? } ?>
			</div>
			<? } ?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("MAIL_MSERVICE_EDT_NAME"); ?></td>
		<td><input type="text" name="NAME" size="53" maxlength="255" value="<?=$str_NAME; ?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("MAIL_MSERVICE_EDT_TYPE"); ?></td>
		<td>
			<? if ($ID > 0) { ?>
			<?=$str_TYPE; ?>
			<? } else { ?>
			<select onchange="change_type()" name="TYPE" id="TYPE">
				<option value="imap">imap</option>
				<option value="domain">domain</option>
			</select>
			<? } ?>
		</td>
	</tr>
	<tr id="el0" class="imap">
		<td><?=GetMessage('MAIL_MSERVICE_EDT_SERVER'); ?></td>
		<td><input type="text" name="SERVER" size="42" maxlength="255" value="<?=$str_SERVER; ?>">:<input type="text" id="PORT_PORT" name="PORT" size="4" maxlength="5" value="<?=$str_PORT; ?>"></td>
	</tr>
	<tr id="el1" class="adm-detail-required-field domain crdomain">
		<td><?=GetMessage('MAIL_MSERVICE_EDT_DOMAIN'); ?></td>
		<td><input type="text" name="SERVER" size="53" maxlength="255" value="<?=$str_SERVER; ?>"></td>
	</tr>
	<tr id="el2" class="adm-detail-required-field domain">
		<td><?=GetMessage('MAIL_MSERVICE_EDT_TOKEN'); ?></td>
		<td><input type="text" name="TOKEN" size="53" maxlength="255" value="<?=$str_TOKEN; ?>"></td>
	</tr>
	<tr id="el3" class="imap">
		<td><?=GetMessage("MAIL_MSERVICE_EDT_ENCRYPTION"); ?><span class="required"><sup>1</sup></span></td>
		<td>
			<select name="ENCRYPTION"<? if (!$bCanUseTLS) { ?> disabled<? } ?>>
				<option value=""></option>
				<option value="Y"<? if ($str_ENCRYPTION == "Y") { ?> selected="selected"<? } ?>><?=GetMessage('MAIN_YES'); ?></option>
				<option value="N"<? if ($str_ENCRYPTION == "N") { ?> selected="selected"<? } ?>><?=GetMessage('MAIN_NO'); ?></option>
			</select>
		</td>
	</tr>
	<tr id="el4" class="domain crdomain">
		<td><?=GetMessage("MAIL_MSERVICE_EDT_PUBLIC"); ?></td>
		<td><input type="checkbox" name="ENCRYPTION" value="N"<? if (!$ID || $str_ENCRYPTION == 'N') { ?> checked<? } ?>></td>
	</tr>
	<tr id="el5" class="imap">
		<td><?=GetMessage("MAIL_MSERVICE_EDT_LINK"); ?></td>
		<td><input type="text" name="LINK" size="53" maxlength="255" value="<?=$str_LINK; ?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage('MAIL_MSERVICE_EDT_SORT'); ?></td>
		<td><input type="text" name="SORT" value="<?=$str_SORT; ?>" size="20"></td>
	</tr>

	<input type="hidden" value="Y" name="apply">


<script>

	function change_type()
	{
		<? if ($ID > 0) { ?>
		var type = '<?=$str_TYPE; ?>';
		<? } else { ?>
		var typeSelect = document.getElementById('TYPE');
		var type = typeSelect.options[typeSelect.selectedIndex].value;
		<? } ?>

		for (var i = 0; i <= 5; i++)
		{
			var d = document.getElementById('el'+i);

			if (d)
			{
				var inps = BX.findChildren(d, {tag: 'input'}, true).concat(BX.findChildren(d, {tag: 'select'}, true));

				if (BX.hasClass(d, type))
				{
					for (var j in inps)
						inps[j].disabled = false;
					d.style.display = '';
				}
				else
				{
					for (var j in inps)
						inps[j].disabled = true;
					d.style.display = 'none';
				}
			}
		}
	}
	setTimeout(change_type, 0);

</script>

<? $tabControl->EndTab(); ?>
<? $tabControl->Buttons(array("disabled" => $MOD_RIGHT < "W", "back_url" => "mail_mailservice_admin.php?lang=".LANG)); ?>
<? $tabControl->End(); ?>
</form>
<? $tabControl->ShowWarnings("form1", $message); ?>

<?=BeginNote(); ?>
<span class="required"><sup>1</sup></span> <?=GetMessage('MAIL_MSERVICE_EDT_COMMENT1'); ?>
<?=EndNote(); ?>

<? require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php"); ?>
