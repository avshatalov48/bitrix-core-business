<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/prolog.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
IncludeModuleLangFile(__FILE__);
define('HELP_FILE', 'add_newsletter.php');

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
		'TAB' => GetMessage('rub_tab_rubric'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('rub_tab_rubric_title'),
	],
	[
		'DIV' => 'edit2',
		'TAB' => GetMessage('rub_tab_generation'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('rub_tab_generation_title'),
	],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs);

$ID = intval($request['ID']); // Id of the edited record
$message = null;
$bVarsFromForm = false;
$rubric = new CRubric;

if (
	$request->isPost()
	&& (
		(string)$request['save'] !== ''
		|| (string)$request['apply'] !== ''
	)
	&& $POST_RIGHT === 'W'
	&& check_bitrix_sessid()
)
{
	$arFields = [
		'ACTIVE' => ($request['ACTIVE'] !== 'Y' ? 'N' : 'Y'),
		'NAME' => $request['NAME'],
		'CODE' => $request['CODE'],
		'SORT' => $request['SORT'],
		'DESCRIPTION' => $request['DESCRIPTION'],
		'LID' => $request['LID'],
		'AUTO' => ($request['AUTO'] !== 'Y' ? 'N' : 'Y'),
		'DAYS_OF_MONTH' => $request['DAYS_OF_MONTH'],
		'DAYS_OF_WEEK' => (is_array($request['DAYS_OF_WEEK']) ? implode(',', $request['DAYS_OF_WEEK']) : ''),
		'TIMES_OF_DAY' => $request['TIMES_OF_DAY'],
		'TEMPLATE' => $request['TEMPLATE'],
		'VISIBLE' => ($request['VISIBLE'] !== 'Y' ? 'N' : 'Y'),
		'FROM_FIELD' => $request['FROM_FIELD'],
		'LAST_EXECUTED' => $request['LAST_EXECUTED']
	];

	if ($ID > 0)
	{
		$res = $rubric->Update($ID, $arFields);
	}
	else
	{
		$ID = $rubric->Add($arFields);
		$res = ($ID > 0);
	}

	if ($res)
	{
		if ((string)$request['apply'] !== '')
		{
			LocalRedirect('/bitrix/admin/rubric_edit.php?ID=' . $ID . '&mess=ok&lang=' . LANGUAGE_ID . '&' . $tabControl->ActiveTabParam());
		}
		else
		{
			LocalRedirect('/bitrix/admin/rubric_admin.php?lang=' . LANGUAGE_ID);
		}
	}
	else
	{
		if ($e = $APPLICATION->GetException())
		{
			$message = new CAdminMessage(GetMessage('rub_save_error'), $e);
		}
		$bVarsFromForm = true;
	}
}

//Edit/Add part
ClearVars();
$str_SORT = 100;
$str_ACTIVE = 'Y';
$str_AUTO = 'N';
$str_DAYS_OF_MONTH = '';
$str_DAYS_OF_WEEK = '';
$str_TIMES_OF_DAY = '';
$str_VISIBLE = 'Y';
$str_LAST_EXECUTED = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL');
$str_FROM_FIELD = COption::GetOptionString('subscribe', 'default_from');
$str_LID = '';
$str_NAME = '';
$str_CODE = '';
$str_DESCRIPTION = '';
$str_TEMPLATE = '';

if ($ID > 0)
{
	if (!CRubric::GetByID($ID)->ExtractFields('str_'))
	{
		$ID = 0;
	}
}

$DAYS_OF_WEEK = [];
if ($ID > 0)
{
	$DAYS_OF_WEEK = explode(',', $str_DAYS_OF_WEEK);
}

if ($bVarsFromForm)
{
	$DB->InitTableVarsForEdit('b_list_rubric', '', 'str_');
	if (is_array($request->getPost('DAYS_OF_WEEK')))
	{
		$DAYS_OF_WEEK = $request->getPost('DAYS_OF_WEEK');
	}
}

$APPLICATION->SetTitle(($ID > 0 ? GetMessage('rub_title_edit') . $ID : GetMessage('rub_title_add')));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$aMenu = [
	[
		'TEXT' => GetMessage('rub_list'),
		'TITLE' => GetMessage('rub_list_title'),
		'LINK' => 'rubric_admin.php?lang=' . LANGUAGE_ID,
		'ICON' => 'btn_list',
	]
];
if ($ID > 0)
{
	$aMenu[] = ['SEPARATOR' => 'Y'];
	$aMenu[] = [
		'TEXT' => GetMessage('rub_add'),
		'TITLE' => GetMessage('rubric_mnu_add'),
		'LINK' => 'rubric_edit.php?lang=' . LANGUAGE_ID,
		'ICON' => 'btn_new',
	];
	$aMenu[] = [
		'TEXT' => GetMessage('rub_delete'),
		'TITLE' => GetMessage('rubric_mnu_del'),
		'LINK' => "javascript:if(confirm('" . GetMessage('rubric_mnu_del_conf') . "'))window.location='rubric_admin.php?ID=" . $ID . '&action=delete&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get() . "';",
		'ICON' => 'btn_delete',
	];
	$aMenu[] = ['SEPARATOR' => 'Y'];
	$aMenu[] = [
		'TEXT' => GetMessage('rub_check'),
		'TITLE' => GetMessage('rubric_mnu_check'),
		'LINK' => 'template_test.php?lang=' . LANGUAGE_ID . '&ID=' . $ID
	];
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?php
if ($request['mess'] === 'ok' && $ID > 0)
{
	CAdminMessage::ShowMessage(['MESSAGE' => GetMessage('rub_saved'), 'TYPE' => 'OK']);
}

if ($message)
{
	echo $message->Show();
}
elseif ($rubric->LAST_ERROR !== '')
{
	CAdminMessage::ShowMessage($rubric->LAST_ERROR);
}
?>

<form method="POST" Action="<?php echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
<?php
$tabControl->Begin();
?>
<?php
//********************
//Rubric
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?php echo GetMessage('rub_act')?></td>
		<td width="60%"><input type="checkbox" name="ACTIVE" value="Y" <?php echo ($str_ACTIVE === 'Y') ? 'checked' : '';?>></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('rub_visible')?></td>
		<td><input type="checkbox" name="VISIBLE" value="Y" <?php echo ($str_VISIBLE === 'Y') ? 'checked' : '';?>></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('rub_site')?></td>
		<td><?php echo CLang::SelectBox('LID', $str_LID);?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?php echo GetMessage('rub_name')?></td>
		<td><input type="text" name="NAME" value="<?php echo $str_NAME;?>" size="45" maxlength="100"></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('rub_sort')?></td>
		<td><input type="text" name="SORT" value="<?php echo $str_SORT;?>" size="6"></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('rub_code')?></td>
		<td><input type="text" name="CODE" value="<?php echo $str_CODE;?>" size="45"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?php echo GetMessage('rub_desc')?></td>
		<td><textarea class="typearea" name="DESCRIPTION" cols="45" rows="5" wrap="VIRTUAL" style="width:100%"><?php echo $str_DESCRIPTION; ?></textarea></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('rub_auto')?></td>
		<td><input type="checkbox" name="AUTO" value="Y" <?php echo ($str_AUTO === 'Y') ? 'checked' : '';?> OnClick="if(this.checked) tabControl.EnableTab('edit2'); else tabControl.DisableTab('edit2');"></td>
	</tr>
<?php
//********************
//Auto params
//********************
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('rub_schedule')?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?php echo GetMessage('rub_last_executed') . ':'?></td>
		<td width="60%"><?php echo CalendarDate('LAST_EXECUTED', $str_LAST_EXECUTED, 'post_form', '20')?></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('rub_dom')?></td>
		<td><input class="typeinput" type="text" name="DAYS_OF_MONTH" value="<?php echo $str_DAYS_OF_MONTH;?>" size="30" maxlength="100"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?php echo GetMessage('rub_dow')?></td>
		<td>
		<table cellspacing=1 cellpadding=0 border=0 class="internal">
		<?php	$arDoW = [
			'1' => GetMessage('rubric_mon'),
			'2' => GetMessage('rubric_tue'),
			'3' => GetMessage('rubric_wed'),
			'4' => GetMessage('rubric_thu'),
			'5' => GetMessage('rubric_fri'),
			'6' => GetMessage('rubric_sat'),
			'7' => GetMessage('rubric_sun')
		];
		?>
			<tr class="heading"><?php foreach ($arDoW as $strDoW):?>
				<td><?=$strDoW?></td>
				<?php endforeach;?>
			</tr>
			<tr>
			<?php foreach ($arDoW as $strVal => $strDoW):?>
				<td style="text-align:center"><input type="checkbox" name="DAYS_OF_WEEK[]" value="<?=$strVal?>" <?php echo (array_search($strVal, $DAYS_OF_WEEK) !== false) ? 'checked' : '';?>></td>
			<?php endforeach;?>
			</tr>
		</table>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?php echo GetMessage('rub_tod')?></td>
		<td><input type="text" name="TIMES_OF_DAY" value="<?php echo $str_TIMES_OF_DAY;?>" size="30" maxlength="255"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('rub_template')?></td>
	</tr>
<?php
$arTemplates = CPostingTemplate::GetList();
if (count($arTemplates) > 0):
?>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?php echo GetMessage('rub_templates')?></td>
		<td><table>
<?php
	$i = 0;
	foreach ($arTemplates as $strTemplate):
		$arTemplate = CPostingTemplate::GetByID($strTemplate);
?>
		<tr>
			<td class="adm-detail-valign-top"><input type="radio" id="TEMPLATE<?=$i?>" name="TEMPLATE" value="<?=$arTemplate['PATH']?>" <?php echo ($str_TEMPLATE === $arTemplate['PATH']) ? 'checked' : '';?>></td>
			<td>
				<label for="TEMPLATE<?=$i?>" title="<?=$arTemplate['DESCRIPTION'] ?? ''?>"><?=($arTemplate['NAME'] ?? GetMessage('rub_no_name'))?></label><br>
				<?php if (IsModuleInstalled('fileman')):?>
					<a title="<?=GetMessage('rub_manage')?>" href="/bitrix/admin/fileman_admin.php?path=<?=urlencode('/' . $arTemplate['PATH'])?>"><?=$arTemplate['PATH']?></a>
				<?php else:?>
					<?=$arTemplate['PATH']?>
				<?php endif?>
			</td>
		<?php $i++?>
		</tr>
	<?php endforeach;?>
		</table></td>
	</tr>
<?php else:?>
	<tr>
		<td colspan="2"><?=GetMessage('rub_no_templates')?></td>
	</tr>
<?php endif?>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('rub_post_fields')?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?php echo GetMessage('rub_post_fields_from')?></td>
		<td><input type="text" name="FROM_FIELD" value="<?php echo $str_FROM_FIELD;?>" size="30" maxlength="255"></td>
	</tr>
<?php
$tabControl->Buttons(
	[
		'disabled' => ($POST_RIGHT < 'W'),
		'back_url' => 'rubric_admin.php?lang=' . LANGUAGE_ID,

	]
);
?>
<?php echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?php if ($ID > 0):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?php endif;?>
<?php
$tabControl->End();
?>

<?php
$tabControl->ShowWarnings('post_form', $message);
?>

<script language="JavaScript">
<!--
	if(document.post_form.AUTO.checked)
		tabControl.EnableTab('edit2');
	else
		tabControl.DisableTab('edit2');
//-->
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
