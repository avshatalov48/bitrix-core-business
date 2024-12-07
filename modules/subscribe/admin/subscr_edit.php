<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/prolog.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
IncludeModuleLangFile(__FILE__);
define('HELP_FILE', 'add_subscriber.php');

$POST_RIGHT = CMain::GetUserRight('subscribe');
if ($POST_RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

/* @var $request \Bitrix\Main\HttpRequest */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => GetMessage('subscr_tab_subscriber'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('subscr_tab_subscriber_title'),
	],
	[
		'DIV' => 'edit2',
		'TAB' => GetMessage('subscr_tab_subscription'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('subscr_tab_subscription_title'),
	],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs);

$ID = intval($request['ID']); // Id of the edited record
$message = null;
$strError = '';
$bVarsFromForm = false;

if (
	$request->isPost()
	&& (
		(string)$request['save'] !== ''
		|| (string)$request['apply'] !== ''
	)
	&& $POST_RIGHT >= 'W'
	&& check_bitrix_sessid()
)
{
	$subscr = new CSubscription;
	$arFields = [
		'USER_ID' => ($request['ANONYMOUS'] === 'Y' ? false : $request['USER_ID']),
		'ACTIVE' => ($request['ACTIVE'] !== 'Y' ? 'N' : 'Y'),
		'FORMAT' => ($request['FORMAT'] !== 'html' ? 'text' : 'html'),
		'EMAIL' => $request['EMAIL'],
		'CONFIRMED' => ($request['CONFIRMED'] !== 'Y' ? 'N' : 'Y'),
		'SEND_CONFIRM' => ($request['SEND_CONFIRM'] !== 'Y' ? 'N' : 'Y'),
		'RUB_ID' => $request['RUB_ID'],
		'ALL_SITES' => 'Y',
	];
	if ($ID > 0)
	{
		$res = $subscr->Update($ID, $arFields, $request['SITE_ID']);
	}
	else
	{
		$ID = $subscr->Add($arFields, $request['SITE_ID']);
		$res = ($ID > 0);
	}

	if ($res)
	{
		if ((string)$request['apply'] !== '')
		{
			LocalRedirect('/bitrix/admin/subscr_edit.php?ID=' . $ID . '&mess=ok&lang=' . LANGUAGE_ID . '&' . $tabControl->ActiveTabParam());
		}
		else
		{
			LocalRedirect('/bitrix/admin/subscr_admin.php?lang=' . LANGUAGE_ID);
		}
	}
	else
	{
		if ($e = $APPLICATION->GetException())
		{
			$message = new CAdminMessage(GetMessage('subs_save_error'), $e);
		}
		$bVarsFromForm = true;
	}
}

ClearVars();
$str_FORMAT = 'text';
$str_ACTIVE = 'Y';
$str_USER_ID = 0;
$str_DATE_INSERT = '';
$str_DATE_UPDATE = '';
$str_CONFIRMED = '';
$str_CONFIRM_CODE = '';
$str_DATE_CONFIRM = '';
$str_EMAIL = '';
$str_DATE_UPDATE = '';

if ($ID > 0)
{
	$subscr = CSubscription::GetByID($ID);
	if (!$subscr->ExtractFields('str_'))
	{
		$ID = 0;
	}
}

if ($bVarsFromForm)
{
	$DB->InitTableVarsForEdit('b_subscription', '', 'str_');
}

$APPLICATION->SetTitle(($ID > 0 ? GetMessage('subscr_title_edit') . $ID : GetMessage('subscr_title_add')));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$aMenu = [
	[
		'TEXT' => GetMessage('subscr_list_text'),
		'TITLE' => GetMessage('subscr_list'),
		'LINK' => 'subscr_admin.php?lang=' . LANGUAGE_ID,
		'ICON' => 'btn_list',
	]
];
if ($ID > 0)
{
	$aMenu[] = ['SEPARATOR' => 'Y'];
	$aMenu[] = [
		'TEXT' => GetMessage('subscr_add_text'),
		'TITLE' => GetMessage('subscr_mnu_add'),
		'LINK' => 'subscr_edit.php?lang=' . LANGUAGE_ID,
		'ICON' => 'btn_new',
	];
	$aMenu[] = [
		'TEXT' => GetMessage('subscr_del_text'),
		'TITLE' => GetMessage('subscr_mnu_del'),
		'LINK' => "javascript:if(confirm('" . GetMessage('subscr_mnu_del_conf') . "'))window.location='subscr_admin.php?ID=" . $ID . '&action=delete&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get() . "';",
		'ICON' => 'btn_delete',
	];
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?php
if ($request['mess'] == 'ok' && $ID > 0)
{
	CAdminMessage::ShowMessage(['MESSAGE' => GetMessage('subs_saved'), 'TYPE' => 'OK']);
}
if ($message)
{
	echo $message->Show();
}
?>

<form method="POST" action="<?php echo $APPLICATION->GetCurPage()?>"  enctype="multipart/form-data" name="subscrform">
<?php
$tabControl->Begin();
?>
<?php
//********************
//Subscriber tab
//********************
$tabControl->BeginNextTab();
?>
	<?php if ($ID > 0):?>
		<tr>
			<td><?php echo GetMessage('subscr_date_add')?></td>
			<td><?php echo $str_DATE_INSERT;?></td>
		</tr>
		<?php if ($str_DATE_UPDATE !== ''):?>
			<tr>
				<td><?php echo GetMessage('subscr_date_upd')?></td>
				<td><?php echo $str_DATE_UPDATE;?></td>
			</tr>
		<?php endif?>
	<?php endif?>
	<tr>
		<td width="40%"><?php echo GetMessage('subscr_conf')?></td>
		<td width="60%"><input type="checkbox" name="CONFIRMED" value="Y" <?php echo ($str_CONFIRMED === 'Y') ? 'checked' : '';?>></td>
	</tr>
	<?php if ($ID > 0):?>
		<tr>
			<td><?php echo GetMessage('subscr_conf_code')?></td>
			<td><?php echo $str_CONFIRM_CODE?></td>
		</tr>
		<tr>
			<td><?php echo GetMessage('subscr_date_conf')?></td>
			<td><?php echo $str_DATE_CONFIRM;?></td>
		</tr>
	<?php endif;?>
	<tr>
		<td><?php echo GetMessage('subscr_anonym')?></td>
		<td><input type="checkbox" name="ANONYMOUS" value="Y" <?php echo ((integer)$str_USER_ID == 0) ? 'checked' : '';?> onClick="document.subscrform.USER_ID.disabled=document.subscrform.FindUser.disabled=this.checked;"></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('subscr_user')?></td>
		<td>
		<?php
		$sUser = '';
		if ($ID > 0 && $str_USER_ID > 0)
		{
			$rsUser = CUser::GetByID($str_USER_ID);
			$arUser = $rsUser->GetNext();
			if ($arUser)
			{
				$sUser = '[<a href="user_edit.php?ID=' . $arUser['ID'] . '&amp;lang=' . LANGUAGE_ID . '">' . $arUser['ID'] . '</a>] (' . $arUser['LOGIN'] . ') ' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
			}
		}
		echo FindUserID('USER_ID', ($str_USER_ID > 0 ? $str_USER_ID : ''), $sUser, 'subscrform', '10', '', ' ... ', '', '');

		if ((integer)$str_USER_ID == 0):
		?><script>document.subscrform.USER_ID.disabled=document.subscrform.FindUser.disabled=true;</script><?php
		endif;
		?></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('subscr_active')?></td>
		<td><input type="checkbox" name="ACTIVE" value="Y" <?php echo ($str_ACTIVE === 'Y') ? 'checked' : '';?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td>E-Mail:</td>
		<td><input type="text" name="EMAIL" value="<?php echo $str_EMAIL;?>" size="30" maxlength="255"></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('subscr_send_conf')?></td>
		<td><input type="checkbox" name="SEND_CONFIRM" value="Y" <?php echo ($request['SEND_CONFIRM'] == 'Y') ? 'checked' : '';?> onClick="document.subscrform.SITE_ID.disabled=!this.checked;"></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('subscr_templ')?></td>
		<td><?php echo CSite::SelectBox('SITE_ID', $request['SITE_ID']);?></td>
	</tr>
<?php if ($request['SEND_CONFIRM'] !== 'Y'):?>
	<script>document.subscrform.SITE_ID.disabled=true;</script>
<?php endif;?>
<?php
//********************
//Subscribtions tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td><?php echo GetMessage('subscr_fmt')?></td>
		<td><input type="radio" id="FORMAT_1" name="FORMAT" value="text" <?php echo ($str_FORMAT === 'text') ? 'checked' : '';?>><label for="FORMAT_1"><?php echo GetMessage('subscr_fmt_text')?></label>&nbsp;/<input type="radio" id="FORMAT_2" name="FORMAT" value="html" <?php echo ($str_FORMAT === 'html') ? 'checked' : '';?>><label for="FORMAT_2">HTML</label></td>
	</tr>
	<tr>
		<td width="40%" class="adm-detail-valign-top"><?php echo GetMessage('subscr_rub')?></td>
		<td width="60%">
			<div class="adm-list">
			<?php
			if ($bVarsFromForm)
			{
				$aSubscrRub = is_array($request['RUB_ID']) ? $request['RUB_ID'] : [];
			}
			else
			{
				$aSubscrRub = CSubscription::GetRubricArray($ID);
			}

			$rsRubrics = CRubric::GetList(['LID' => 'ASC', 'SORT' => 'ASC', 'NAME' => 'ASC'], ['ACTIVE' => 'Y']);
			while ($arRubric = $rsRubrics->GetNext()):?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="RUB_ID_<?php echo $arRubric['ID']?>" name="RUB_ID[]" value="<?php echo $arRubric['ID']?>" <?php echo (in_array($arRubric['ID'], $aSubscrRub)) ? 'checked' : '';?>></div>
					<div class="adm-list-label"><label for="RUB_ID_<?php echo $arRubric['ID']?>"><?php echo '[' . $arRubric['LID'] . '] ' . $arRubric['NAME']?></label></div>
				</div>
			<?php endwhile;?>
			</div>
		</td>
	</tr>
<?php
$tabControl->Buttons([
	'disabled' => ($POST_RIGHT < 'W'),
	'back_url' => 'subscr_admin.php?lang=' . LANGUAGE_ID,
]);
?>
<?php echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID?>">
<?php if ($ID > 0):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?php endif;?>
<?php
$tabControl->End();
?>
</form>

<?php
$tabControl->ShowWarnings('subscrform', $message);
?>

<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
