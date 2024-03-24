<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/prolog.php';
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = CMain::GetUserRight('subscribe');
if ($POST_RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$MAIN_RIGHT = CMain::GetUserRight('main');

/* @var $request \Bitrix\Main\HttpRequest */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => GetMessage('imp_import_tab'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('imp_import_tab_title'),
	],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs, true, true);

$message = null;
$arError = [];
$bShowRes = false;
$aEmail = [];
$nError = 0;
$nSuccess = 0;

//default values
$CONFIRMED = 'Y';
$USER_TYPE = 'A';
$SEND_REG_INFO = 'Y';
$FORMAT = 'text';
$SEND_CONFIRM = 'Y';
$USER_GROUP_ID = [];
$RUB_ID = [];
$LID = [];

if (
	$request->isPost()
	&& (
		(string)$request['Import'] !== ''
	)
	&& $POST_RIGHT >= 'W'
	&& check_bitrix_sessid()
)
{
	//*************************************
	// Prepare emails
	//*************************************
	//This is from the form
	$sAddr = $request['ADDR_LIST'] . ',';
	//And this is from the file
	if (!empty($_FILES['ADDR_FILE']['tmp_name']))
	{
		if ((integer)$_FILES['ADDR_FILE']['error'] <> 0)
		{
			$arError[] = ['id' => 'ADDR_FILE', 'text' => GetMessage('subscr_imp_err1') . ' (' . GetMessage('subscr_imp_err2') . ' ' . $_FILES['ADDR_FILE']['error'] . ')'];
		}
		else
		{
			$sAddr .= file_get_contents($_FILES['ADDR_FILE']['tmp_name']);
		}
	}

	//explode to emails array
	$addr = strtok($sAddr, ", \r\n\t");
	while ($addr !== false)
	{
		if ($addr <> '')
		{
			$aEmail[$addr] = true;
		}
		$addr = strtok(", \r\n\t");
	}

	//check for duplicate emails
	$addr = CSubscription::GetList();
	while ($addr_arr = $addr->Fetch())
	{
		if (isset($aEmail[$addr_arr['EMAIL']]))
		{
			unset($aEmail[$addr_arr['EMAIL']]);
		}
	}

	//*************************************
	//add users and subscribers
	//*************************************

	//constant part of the subscriber
	$CONFIRMED = ($request['CONFIRMED'] !== 'Y' ? 'N' : 'Y');
	$USER_TYPE = (string)$request['USER_TYPE'];
	$SEND_REG_INFO = ($request['SEND_REG_INFO'] !== 'Y' ? 'N' : 'Y');
	$FORMAT = ($request['FORMAT'] !== 'html' ? 'text' : 'html');
	$SEND_CONFIRM = ($request['SEND_CONFIRM'] !== 'Y' ? 'N' : 'Y');
	$USER_GROUP_ID = is_array($request['USER_GROUP_ID']) ? $request['USER_GROUP_ID'] : [];
	$RUB_ID = is_array($request['RUB_ID']) ? $request['RUB_ID'] : [];
	$LID = is_array($request['LID']) ? $request['LID'] : [];

	$subscr = new CSubscription;
	$arFields = [
		'ACTIVE' => 'Y',
		'FORMAT' => $FORMAT,
		'CONFIRMED' => $CONFIRMED,
		'SEND_CONFIRM' => $SEND_CONFIRM,
		'ALL_SITES' => 'Y',
		'RUB_ID' => $RUB_ID,
	];

	foreach ($aEmail as $email => $temp)
	{
		$USER_ID = false;
		if ($request['USER_TYPE'] == 'U')
		{
			$user = new CUser;
			//add user
			$sPassw = \Bitrix\Main\Security\Random::getString(6, true);
			$arUserFields = [
				'LOGIN' => \Bitrix\Main\Security\Random::getString(50, true),
				'CHECKWORD' => \Bitrix\Main\Security\Random::getString(8, true),
				'PASSWORD' => $sPassw,
				'CONFIRM_PASSWORD' => $sPassw,
				'EMAIL' => $email,
				'ACTIVE' => 'Y',
				'GROUP_ID' => ($MAIN_RIGHT >= 'W' ? $USER_GROUP_ID : explode(',', COption::GetOptionString('main', 'new_user_registration_def_group'))),
			];
			if ($USER_ID = $user->Add($arUserFields))
			{
				$user->Update($USER_ID, ['LOGIN' => 'user' . $USER_ID]);

				//send registration message
				if ($SEND_REG_INFO === 'Y')
				{
					CUser::SendUserInfo($USER_ID, $request['LID'], GetMessage('subscr_send_info'));
				}
			}
			else
			{
				$arError[] = ['id' => '', 'text' => $email . ': ' . $user->LAST_ERROR];
				$nError++;
				continue;
			}
		}//$USER_TYPE == "U"

		//add subscription
		$arFields['USER_ID'] = $USER_ID;
		$arFields['EMAIL'] = $email;
		if (!$subscr->Add($arFields, $request['LID']))
		{
			$arError[] = ['id' => '', 'text' => $email . ': ' . $subscr->LAST_ERROR];
			$nError++;
		}
		else
		{
			$nSuccess++;
		}
	}//foreach
	$bShowRes = true;
}

$APPLICATION->SetTitle(GetMessage('imp_title'));

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

if (count($arError) > 0)
{
	$e = new CAdminException($arError);
	$message = new CAdminMessage(GetMessage('imp_error'), $e);
	echo $message->Show();
}
?>
<?php
if ($bShowRes)
{
	CAdminMessage::ShowMessage([
		'MESSAGE' => GetMessage('imp_results'),
		'DETAILS' => GetMessage('imp_results_total') . ' <b>' . count($aEmail) . '</b><br>'
			. GetMessage('imp_results_added') . ' <b>' . $nSuccess . '</b><br>'
			. GetMessage('imp_results_err') . ' <b>' . $nError . '</b>',
		'HTML' => true,
		'TYPE' => 'PROGRESS',
	]);
}
?>
<form ENCTYPE="multipart/form-data" action="<?php echo $APPLICATION->GetCurPage();?>" method="POST" name="impform">
<?php
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('imp_delim')?></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('imp_file')?></td>
		<td><input type=file name="ADDR_FILE" size=30></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?php echo GetMessage('imp_list')?></td>
		<td><textarea name="ADDR_LIST" rows=10 cols=45></textarea></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('imp_send_code')?></td>
		<td><input type="checkbox" name="SEND_CONFIRM" value="Y" <?php echo ($SEND_CONFIRM === 'Y') ? 'checked' : '';?>></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('imp_conf')?></td>
		<td><input type="checkbox" name="CONFIRMED" value="Y" <?php echo ($CONFIRMED === 'Y') ? 'checked' : '';?>></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('imp_user')?><br><?php echo GetMessage('imp_user_anonym')?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?php echo GetMessage('imp_add')?></td>
		<td>
		<input id="USER_TYPE_1" name="USER_TYPE" type="radio" value="A" <?php echo ($USER_TYPE === 'A') ? 'checked' : '';?> onClick="DisableControls(true);"><label for="USER_TYPE_1"><?php echo GetMessage('imp_add_anonym')?></label><br>
		<input id="USER_TYPE_2" name="USER_TYPE" type="radio" value="U" <?php echo ($USER_TYPE === 'U') ? 'checked' : '';?> onClick="DisableControls(false);"><label for="USER_TYPE_2"><?php echo GetMessage('imp_add_users')?></label></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('imp_send_reg')?></td>
		<td><input type="checkbox" name="SEND_REG_INFO" value="Y" <?php echo ($SEND_REG_INFO === 'Y') ? 'checked' : '';?>>
<?php if ($MAIN_RIGHT < 'W'):?>
		<script language="JavaScript">
		function DisableControls(bDisable)
		{
			document.impform.SEND_REG_INFO.disabled=bDisable;
		}
		<?php
		if ($USER_TYPE === 'A'):
		?>DisableControls(true);<?php
		endif;
		?></script>
<?php endif;?>
		</td>
	</tr>
<?php if ($MAIN_RIGHT >= 'W'):?>
	<tr>
		<td class="adm-detail-valign-top"><?php echo GetMessage('imp_add_gr')?></td>
		<td><select name="USER_GROUP_ID[]" multiple size=10><?php
		$groups = CGroup::GetList('sort', 'asc', ['ACTIVE' => 'Y']);
		while (($gr = $groups->Fetch())):
		?><OPTION VALUE="<?php echo $gr['ID']?>" <?php echo (in_array($gr['ID'], $USER_GROUP_ID)) ? 'SELECTED' : '';?>><?php echo htmlspecialcharsbx($gr['NAME']) . ' [' . $gr['ID'] . ']'?></OPTION><?php
		endwhile;
		?></SELECT>
		<script language="JavaScript">
		function DisableControls(bDisable)
		{
		document.impform.SEND_REG_INFO.disabled=bDisable;
		document.impform.elements['USER_GROUP_ID[]'].disabled=bDisable;
		}
		<?php
		if ($USER_TYPE === 'A'):
		?>DisableControls(true);<?php
		endif;
		?></script>
		</td>
	</tr>
<?php endif;?>
	<tr>
		<td class="adm-detail-valign-top"><?php echo GetMessage('imp_subscr')?></td>
		<td>
			<div class="adm-list">
			<?php
		$rubrics = CRubric::GetList(['LID' => 'ASC', 'SORT' => 'ASC', 'NAME' => 'ASC'], ['ACTIVE' => 'Y']);
		$n = 1;
		while (($rub = $rubrics->Fetch())):
			?>
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="checkbox" id="RUB_ID_<?php echo $n?>" name="RUB_ID[]" value="<?php echo $rub['ID']?>" <?php echo (!$bShowRes || in_array($rub['ID'], $RUB_ID)) ? 'checked' : '';?>></div>
				<div class="adm-list-label"><label for="RUB_ID_<?php echo $n?>"><?php echo '[' . $rub['LID'] . ']&nbsp;' . htmlspecialcharsbx($rub['NAME'])?></label></div>
			</div>
			<?php
			$n++;
		endwhile;
		?></div></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('imp_fmt')?></td>
		<td><input id="FORMAT_1" name="FORMAT" type="radio" value="text" <?php echo ($FORMAT === 'text') ? 'checked' : '';?>><label for="FORMAT_1"><?php echo GetMessage('imp_fmt_text')?></label>&nbsp;/<input id="FORMAT_2" name="FORMAT" type="radio" value="html" <?php echo ($FORMAT === 'html') ? 'checked' : '';?>><label for="FORMAT_2">HTML</label></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('imp_site')?></td>
		<td><?php echo CLang::SelectBox('LID', $LID);?></td>
	</tr>
<?php
$tabControl->Buttons();
?>
<input <?php echo ($POST_RIGHT < 'W') ? 'disabled' : '';?> type="submit" name="Import" value="<?php echo GetMessage('imp_butt')?>" class="adm-btn-save">
<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID?>">
<?php echo bitrix_sessid_post();?>
<?php
$tabControl->End();
?>
</form>

<?php
$tabControl->ShowWarnings('impform', $message);
?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
