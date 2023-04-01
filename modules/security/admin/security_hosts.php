<?
define('ADMIN_MODULE_NAME', 'security');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');



use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Security;

\Bitrix\Main\Loader::includeModule('security');
Loc::loadMessages(__FILE__);

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 **/

if(!$USER->isAdmin())
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));

$tabs = array(
	array(
		'DIV' => 'main',
		'TAB' => Loc::getMessage('SECURITY_HOSTS_MAIN_TAB'),
		'ICON' => 'main_user_edit',
		'TITLE' => Loc::getMessage('SECURITY_HOSTS_MAIN_TAB_TITLE'),
	),
);

$tabControl = new \CAdminTabControl('tabControl', $tabs, true, true);

$bVarsFromForm = false;
$hosts = new Security\HostRestriction();
/** @var $request Main\HttpRequest */
$request = Main\Context::getCurrent()->getRequest();
$returnUrlQuery = $request['return_url']? '&return_url='.urlencode($request['return_url']): '';

$errorMessage = null;
$properties = $hosts->getProperties();

if($request->isPost() && $request['save'].$request['apply'] && check_bitrix_sessid())
{
	try
	{
		$properties = $request->getPost('properties');

		if (isset($properties['active']) && $properties['active'] === 'Y')
			$properties['active'] = true;
		else
			$properties['active'] = false;

		if (isset($properties['logging']) && $properties['logging'] === 'Y')
			$properties['logging'] = true;
		else
			$properties['logging'] = false;

		if (!isset($properties['action']) || !$properties['action'])
			throw new Security\LogicException('Action not presents', 'SECURITY_HOST_EMPTY_ACTION');

		if (!isset($properties['hosts']) || !trim($properties['hosts']))
			throw new Security\LogicException('Hosts not presents', 'SECURITY_HOST_EMPTY_HOSTS');

		$hosts
			->setHosts($properties['hosts'])
			->setAction($properties['action'], $properties['action_options']?:array())
			->setLogging($properties['logging'])
			->setActive($properties['active'])
			->save();

		if($request['save'] && $request['return_url'] != '')
			LocalRedirect($request['return_url']);

		LocalRedirect('/bitrix/admin/security_hosts.php?lang='.LANGUAGE_ID.$returnUrlQuery.'&'.$tabControl->ActiveTabParam());
	}
	catch (Security\LogicException $e)
	{
		$errorMessage = $e->getLocMessage();

	}
	catch (Main\ArgumentException $e)
	{
		$errorMessage = Loc::getMessage('SECURITY_HOSTS_SAVE_UNKNOWN_ERROR', array(
			'#CODE#' => $e->getMessage()
		));
	}
}

if ($hosts->getActive())
{
	$messageType = 'OK';
	$messageText = Loc::getMessage('SECURITY_HOSTS_ON');
} else
{
	$messageType = 'ERROR';
	$messageText = Loc::getMessage('SECURITY_HOSTS_OFF');
}

$APPLICATION->SetTitle(Loc::getMessage('SECURITY_HOSTS_TITLE'));

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

\CAdminMessage::ShowMessage(array(
			'MESSAGE' => $messageText,
			'TYPE' => $messageType
		)
);

if (!is_null($errorMessage))
	\CAdminMessage::ShowMessage(array(
			'MESSAGE' => Loc::getMessage('SECURITY_HOSTS_SAVE_ERROR'),
			'DETAILS' => $errorMessage,
			'TYPE' => 'ERROR'
		)
	);
?>

<form method="POST" action="security_hosts.php?lang=<?=LANGUAGE_ID?><?=$returnUrlQuery?>" enctype="multipart/form-data" name="editform">
<?echo bitrix_sessid_post();?>
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
<tr>
	<td width="40%"><?=Loc::getMessage('SECURITY_HOSTS_ACTIVE')?>:</td>
	<td width="60%"><input type="checkbox" name="properties[active]" value="Y" <?=$properties['active']? 'checked':''?> ></td>
</tr>
<tr>
	<td><?=Loc::getMessage('SECURITY_HOSTS_LOGGING')?>:</td>
	<td><input type="checkbox" name="properties[logging]" value="Y" <?=$properties['logging']? 'checked':''?> ></td>
</tr>
<tr>
	<td><?=Loc::getMessage('SECURITY_HOSTS_REACTION')?>:</td>
	<td>
		<label>
			<input type="radio" name="properties[action]" value="<?=$hosts::ACTION_STOP?>" <?=isset($properties['action']) && $properties['action'] === $hosts::ACTION_STOP? 'checked': '';?>>
			<?=Loc::getMessage('SECURITY_HOSTS_REACTION_STOP')?>
		</label><br>
		<label>
			<input type="radio" name="properties[action]" value="<?=$hosts::ACTION_REDIRECT?>" <?=isset($properties['action']) && $properties['action'] === $hosts::ACTION_REDIRECT? 'checked': '';?>>
			<?=Loc::getMessage('SECURITY_HOSTS_REACTION_REDIRECT')?>
		</label>
	</td>
</tr>
<tr class="adm-detail-required-field">
	<td><?=Loc::getMessage('SECURITY_HOSTS_REACTION_REDIRECT_HOST')?>:</td>
	<td>
		<input type="text" name="properties[action_options][host]" placeholder="http://example.com" value="<?=isset($properties['action_options']['host'])? htmlspecialcharsbx($properties['action_options']['host']): ''?>">
	</td>
</tr>
<tr class="adm-detail-required-field">
	<td><?=Loc::getMessage('SECURITY_HOSTS_HOSTS_LIST')?>:<br><?=Loc::getMessage('SECURITY_HOSTS_HOSTS_LIST_EXAMPLE')?></td>
	<td>
		<textarea name="properties[hosts]" cols="40" rows="5"><?=htmlspecialcharsbx($properties['hosts']?:"{$properties['current_host']} # current")?></textarea>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?=Loc::getMessage('SECURITY_HOSTS_NOTE')?>
		<?echo EndNote();?>
	</td>
</tr>
<?
$tabControl->Buttons(
	array(
		'back_url' => $request['return_url']? $request['return_url']: 'security_hosts.php?lang='.LANG,
	)
);
?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?
$tabControl->End();
?>
</form>
<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>