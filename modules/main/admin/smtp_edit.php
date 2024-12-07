<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
require_once(__DIR__ . "/../include/prolog_admin_before.php");
define("HELP_FILE", "settings/smtp_edit.php");

if (!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
{
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$isAdmin = $USER->CanDoOperation('edit_other_settings');

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

function checkSmtp(array &$fields, Main\ErrorCollection $errors)
{
	$smtpConfig = $fields['OPTIONS']['smtp'] ?? [];
	if (!$smtpConfig)
	{
		return;
	}
	$smtpConfig = new Bitrix\Main\Mail\Smtp\Config([
		'from' => $fields['EMAIL'] ?? '',
		'host' => $smtpConfig['server'] ?? '',
		'port' => $smtpConfig['port'] ?? '',
		'protocol' => $smtpConfig['protocol'] ?? '',
		'login' => $smtpConfig['login'] ?? '',
		'password' => $smtpConfig['password'] ?? '',
	]);
	$context = new Main\Mail\Context();
	$context->setSmtp($smtpConfig);

	if (Main\Mail\Smtp\Mailer::checkConnect($context, $errors))
	{
		\Bitrix\Main\Mail\Sender::clearCustomSmtpCache($smtpConfig->getLogin());
		$fields['IS_CONFIRMED'] = true;
	}
}

function fillSmtpConfigurationFromPost(Main\Mail\Internal\Sender $configuration, Main\ErrorCollection $errors)
{
	static $formFields = [
		'EMAIL',
		'NAME',
		'IS_PUBLIC',
	];

	static $smtpOptionFields = [
		'login',
		'server',
		'port',
		'password',
	];
	$request = Main\Context::getCurrent()->getRequest();

	$fields = $configuration->entity->getFields();

	//set values from the form
	$preparedFields = [];
	foreach ($formFields as $fieldName)
	{
		$value = trim($request->getPost($fieldName));
		if ($fields[$fieldName] instanceof Main\ORM\Fields\BooleanField)
		{
			$value = ($value == 1);
		}

		if ($fieldName === 'EMAIL' && !Bitrix\Main\Mail\Address::isValid($value))
		{
			$errors->add([new \Bitrix\Main\Error(Loc::getMessage("smtp_configuration_wrong_field_value", [
				'%FIELD_NAME%' => $fields[$fieldName]->getTitle(),
			]))]);
		}

		$preparedFields[$fieldName] = $value;
	}

	//set values from the form
	$options = $configuration->getOptions();
	foreach ($smtpOptionFields as $optionField)
	{
		$value = trim($request->getPost($optionField));

		if ($optionField === 'protocol')
		{
			$value = $value == 1 ? 'smtps' : 'smtp';
		}

		if ($optionField === 'port' && !empty($value) && ($value < 0 || $value > 65535))
		{
			$errors->add([new \Bitrix\Main\Error(Loc::getMessage("smtp_configuration_wrong_field_value", [
				'%FIELD_NAME%' => Loc::getMessage('smtp_configuration_edit_' . $optionField),
			]))]);
		}

		if ($optionField === 'password')
		{
			if ($request->getPost($optionField . '_delete') == 'Y')
			{
				$value = '';
			}
			elseif ($value == '')
			{
				continue;
			}
		}

		$options['smtp'][$optionField] = $value;
	}

	$preparedFields['OPTIONS'] = $options;

	// shouldn't be set from the request
	unset($preparedFields['IS_CONFIRMED']);

	if (!empty(trim($request->getPost('password'))) || empty($options['smtp']['password']))
	{
		// check connection only if the password is from the request OR is stored empty
		$checkFields = $preparedFields;
		$checkFields['OPTIONS']['smtp']['password'] = trim($request->getPost('password'));
		checkSmtp($checkFields, $errors);
		$preparedFields['IS_CONFIRMED'] = $checkFields['IS_CONFIRMED'] ?? false;
	}

	foreach ($preparedFields as $field => $value)
	{
		$configuration->set($field, $value);
	}

	$configuration->setUserId(Main\Engine\CurrentUser::get()->getId() ?? 0);
}

$aTabs = [
	[
		"DIV" => "edit1",
		"TAB" => Loc::getMessage("smtp_configuration_edit_tab"),
		"ICON" => "message_edit",
		"TITLE" => Loc::getMessage("smtp_configuration_edit_tab_title"),
	],
];
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$request = Main\Context::getCurrent()->getRequest();

$errors = new Main\ErrorCollection();
$ID = intval($request["ID"]);
$COPY_ID = intval($request["COPY_ID"]);

$entity = Main\Mail\Internal\SenderTable::getEntity();
$fields = $entity->getFields();
$configuration = null;

if ($request->isPost() && ($request["save"] <> '' || $request["apply"] <> '') && $isAdmin && check_bitrix_sessid())
{
	if ($ID > 0)
	{
		$configuration = Main\Mail\Internal\SenderTable::getById($ID)->fetchObject();
	}
	else
	{
		$configuration = $entity->createObject();
	}

	//set values from the form
	fillSmtpConfigurationFromPost($configuration, $errors);

	if (empty($errors->getValues()))
	{
		$result = $configuration->save();

		if ($result instanceof Main\ORM\Data\AddResult)
		{
			$ID = $result->getId();
		}

		if ($result->isSuccess())
		{
			if ($request["save"] <> '')
			{
				LocalRedirect(BX_ROOT . "/admin/smtp_admin.php?lang=" . LANGUAGE_ID);
			}
			else
			{
				LocalRedirect(BX_ROOT . "/admin/smtp_edit.php?lang=" . LANGUAGE_ID . "&ID=" . $ID . "&" . $tabControl->ActiveTabParam());
			}
		}
		else
		{
			$errors = $result->getErrorCollection();
		}
	}

}

if ($ID > 0 || $COPY_ID > 0)
{
	//existing
	$conigurationId = ($COPY_ID > 0 ? $COPY_ID : $ID);
	$configuration = Main\Mail\Internal\SenderTable::getById($conigurationId)->fetchObject();
}
elseif (!$configuration)
{
	//new
	$configuration = $entity->createObject();
}

$APPLICATION->SetTitle(($ID > 0 ? Loc::getMessage("smtp_configuration_edit_title") : Loc::getMessage("smtp_configuration_edit_add")));

require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/prolog_admin_after.php");

$aMenu = [
	[
		"TEXT" => Loc::getMessage("smtp_configuration_edit_list"),
		"LINK" => "smtp_admin.php?lang=" . LANGUAGE_ID,
		"TITLE" => Loc::getMessage("smtp_configuration_edit_list_title"),
		"ICON" => "btn_list",
	]
];

if ($ID > 0 && $isAdmin)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = [
		"TEXT" => Loc::getMessage("smtp_configuration_edit_add_btn"),
		"LINK" => "smtp_edit.php?lang=" . LANGUAGE_ID,
		"TITLE" => Loc::getMessage("smtp_configuration_edit_add_btn_title"),
		"ICON" => "btn_new",
	];
	$aMenu[] = [
		"TEXT" => Loc::getMessage("smtp_configuration_edit_copy"),
		"LINK" => "smtp_edit.php?lang=" . LANGUAGE_ID . "&amp;COPY_ID=" . $ID,
		"TITLE" => Loc::getMessage("smtp_configuration_edit_copy_title"),
		"ICON" => "btn_copy",
	];
	$aMenu[] = [
		"TEXT" => Loc::getMessage("smtp_configuration_edit_del"),
		"LINK" => "javascript:if(confirm('" . CUtil::JSEscape(Loc::getMessage("smtp_configuration_edit_del_conf")) . "')) window.location='smtp_admin.php?ID=" . $ID . "&lang=" . LANGUAGE_ID . "&" . bitrix_sessid_get() . "&action_button=delete';",
		"TITLE" => Loc::getMessage("smtp_configuration_edit_del_title"),
		"ICON" => "btn_delete",
	];
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if (!empty($errors))
{
	CAdminMessage::ShowMessage(join("\n", $errors->toArray()));
}
?>
	<script>
		window.bxCurrentControl = null;

		function PutString(str) {
			if (window.bxCurrentControl) {
				window.bxCurrentControl.value += str;
			}
		}
	</script>

	<form method="POST" action="<?= HtmlFilter::encode($request->getRequestedPage()) ?>" name="form1">
		<?= bitrix_sessid_post() ?>
		<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
		<input type="hidden" name="ID" value="<?= $ID ?>">
		<?php if ($COPY_ID > 0): ?><input type="hidden" name="COPY_ID" value="<?= $COPY_ID ?>"><?php endif ?>
		<?php
		$tabControl->Begin();

		$tabControl->BeginNextTab();
		$options = $configuration->getOptions();
		$options = $options['smtp'] ?? [];
		?>
		<?php if ($ID > 0): ?>
			<tr>
				<td><?= $fields["ID"]->getTitle() ?>:</td>
				<td><?= $ID ?></td>
			</tr>
		<?php endif ?>
		<tr class="adm-detail-required-field">
			<td><?= $fields["EMAIL"]->getTitle() ?>:</td>
			<td><input type="text" name="EMAIL" size="30" maxlength="511"
					value="<?= HtmlFilter::encode($configuration->getEmail()) ?>"
					onfocus="window.bxCurrentControl=this"/></td>
		</tr>
		<tr class="adm-detail-field">
			<td><?= $fields["NAME"]->getTitle() ?>:</td>
			<td><input type="text" name="NAME" size="30" maxlength="511"
					value="<?= HtmlFilter::encode($configuration->getName()) ?>"
					onfocus="window.bxCurrentControl=this" autocomplete="off"/></td>
		</tr>
		<tr class="heading">
			<td colspan="2"><?php echo Loc::getMessage('smtp_configuration_edit_params') ?></td>
		</tr>
		<tr class="adm-detail-field">
			<td><?= $fields["IS_PUBLIC"]->getTitle() ?>:</td>
			<td><input type="checkbox" name="IS_PUBLIC" id="active"
					value="1"<?php if ($configuration->getIsPublic()) echo " checked" ?>></td>
		</tr>
		<tr class="adm-detail-field">
			<td><?= Loc::getMessage('smtp_configuration_edit_host') ?>:</td>
			<td><input type="text" name="server" size="30" maxlength="511"
					value="<?= HtmlFilter::encode($options['server'] ?? "") ?>"
					onfocus="window.bxCurrentControl=this"/></td>
		</tr>
		<tr class="adm-detail-field">
			<td><?= Loc::getMessage('smtp_configuration_edit_port') ?>:</td>
			<td><input type="number" name="port" size="30" maxlength="10"
					value="<?= HtmlFilter::encode($options['port'] ?? "") ?>"
					onfocus="window.bxCurrentControl=this"/></td>
		</tr>
		<tr class="adm-detail-field">
			<td><?= Loc::getMessage('smtp_configuration_edit_login') ?>:</td>
			<td><input type="text" name="login" size="30" maxlength="511"
					value="<?= HtmlFilter::encode($options['login'] ?? "") ?>"
					onfocus="window.bxCurrentControl=this"/></td>
		</tr>
		<tr class="adm-detail-field">
			<td><?= Loc::getMessage('smtp_configuration_edit_password') ?>:</td>
			<td><input type="password" name="password" size="30" maxlength="511" autocomplete="new-password" value=""
					<?php if (!empty($options['password'])):?>placeholder="<?= Loc::getMessage('smtp_configuration_edit_pass_set') ?>"<?php endif ?>
					onfocus="window.bxCurrentControl=this"/><?php
				if (!empty($options['password'])): ?> <label><input type="checkbox" name="password_delete" value="Y" title="<?= Loc::getMessage('smtp_configuration_edit_pass_title') ?>">
					<?= Loc::getMessage('smtp_configuration_edit_pass_delete') ?></label><?php endif?></td>
		</tr>
		<tr class="adm-detail-field">
			<td></td>
			<td><?= BeginNote('', 'style="margin: 0;"') . Loc::getMessage('smtp_configuration_edit_note') . EndNote() ?></td>
		</tr>
		<?php
		$tabControl->Buttons(array("disabled" => !$isAdmin, "back_url" => "smtp_admin.php?lang=" . LANGUAGE_ID));
		$tabControl->End();
		?>
	</form>

<?php
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
