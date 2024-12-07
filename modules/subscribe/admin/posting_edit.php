<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/prolog.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
define('HELP_FILE', 'add_issue.php');

IncludeModuleLangFile(__FILE__);

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
		'TAB' => GetMessage('post_posting_tab'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('post_posting_tab_title'),
	],
	[
		'DIV' => 'edit2',
		'TAB' => GetMessage('post_subscr_tab'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('post_subscr_tab_title'),
	],
	[
		'DIV' => 'edit3',
		'TAB' => GetMessage('post_attachments'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('post_attachments_title'),
	],
	[
		'DIV' => 'edit4',
		'TAB' => GetMessage('post_params_tab'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('post_params_tab_title'),
	],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs);

CModule::IncludeModule('fileman');
$ID = intval($request['ID']); // Id of the edited record
$STATUS = (string)$request['STATUS'];
$bCopy = (string)$request['action'] === 'copy';
$message = null;
$bVarsFromForm = false;
$posting = new CPosting();

if (
	$request->isPost()
	&& (
		(string)$request['save'] !== ''
		|| (string)$request['apply'] !== ''
		|| (string)$request['Send'] !== ''
		|| (string)$request['Resend'] !== ''
		|| (string)$request['Continue'] !== ''
	)
	&& $POST_RIGHT === 'W'
	&& check_bitrix_sessid()
)
{
	$arFields = [
		'FROM_FIELD' => $request['FROM_FIELD'],
		'TO_FIELD' => $request['TO_FIELD'],
		'BCC_FIELD' => $request['BCC_FIELD'],
		'EMAIL_FILTER' => $request['EMAIL_FILTER'],
		'SUBJECT' => $request['SUBJECT'],
		'BODY_TYPE' => ($request['BODY_TYPE'] !== 'html' ? 'text' : 'html'),
		'BODY' => $request['BODY'],
		'DIRECT_SEND' => ($request['DIRECT_SEND'] !== 'Y' ? 'N' : 'Y'),
		'CHARSET' => $request['CHARSET'],
		'SUBSCR_FORMAT' => ($request['SUBSCR_FORMAT'] !== 'html' && $request['SUBSCR_FORMAT'] !== 'text' ? false : $request['SUBSCR_FORMAT']),
		'RUB_ID' => $request['RUB_ID'],
		'GROUP_ID' => $request['GROUP_ID'],
		'AUTO_SEND_TIME' => ($request['AUTO_SEND_FLAG'] !== 'Y' ? false : $request['AUTO_SEND_TIME']),
	];

	if ($STATUS !== '')
	{
		if ($STATUS !== 'S' && $STATUS !== 'E' && $STATUS !== 'P' && $STATUS !== 'W')
		{
			$STATUS = 'D';
		}
	}

	if ($ID > 0)
	{
		$res = $posting->Update($ID, $arFields);
		if ((string)$request['Resend'] !== '')
		{
			$STATUS = 'W';
		}
		if ($res && $STATUS !== '')
		{
			$res = $posting->ChangeStatus($ID, $STATUS);
		}
	}
	else
	{
		$arFields['STATUS'] = 'D';
		$ID = $posting->Add($arFields);
		$res = ($ID > 0);
	}

	if ($res)
	{
		//Delete checked
		if (is_array($request->getPost('FILE_ID')))
		{
			foreach ($request->getPost('FILE_ID') as $file)
			{
				CPosting::DeleteFile($ID, $file);
			}
		}

		//New files
		$arFiles = [];

		//Brandnew
		if (is_array($_FILES['NEW_FILE']))
		{
			foreach ($_FILES['NEW_FILE'] as $attribute => $files)
			{
				if (is_array($files))
				{
					foreach ($files as $index => $value)
					{
						$arFiles[$index][$attribute] = $value;
					}
				}
			}

			foreach ($arFiles as $index => $file)
			{
				if (!is_uploaded_file($file['tmp_name']))
				{
					unset($arFiles[$index]);
				}
			}
		}

		//Copy
		if (is_array($request->getPost('FILES')))
		{
			if (intval($request['COPY_ID']) > 0)
			{
				//Files from posting_edit.php
				foreach (array_reverse($request->getPost('FILES'), true) as $key => $file_id)
				{
					//skip "deleted"
					if (is_array($request->getPost('FILE_ID')) && array_key_exists($key, $request->getPost('FILE_ID')))
					{
						continue;
					}
					//clone file
					if (intval($file_id) > 0)
					{
						$rsFile = CPosting::GetFileList($request['COPY_ID'], $file_id);
						if ($ar = $rsFile->Fetch())
						{
							array_unshift($arFiles, CFile::MakeFileArray($ar['ID']));
						}
					}
				}
			}
			else
			{
				//Files from template_test.php
				foreach (array_reverse($request->getPost('FILES'), true) as $file)
				{
					if (
						is_array($file)
						&& $file['tmp_name'] <> ''
						&& $APPLICATION->GetFileAccessPermission($file['tmp_name']) >= 'W'
					)
					{
						array_unshift($arFiles, $file);
					}
				}
			}
		}

		foreach ($arFiles as $file)
		{
			if ($file['name'] <> '' and intval($file['size']) > 0)
			{
				if (!$posting->SaveFile($ID, $file))
				{
					$_SESSION['SESS_ADMIN']['POSTING_EDIT_MESSAGE'] = [
						'MESSAGE' => $posting->LAST_ERROR,
						'TYPE' => 'ERROR',
					];
					LocalRedirect('posting_edit.php?ID=' . $ID . '&lang=' . LANGUAGE_ID . '&' . $tabControl->ActiveTabParam());
				}
			}
		}
	}

	if ($res)
	{
		if ((string)$request['Send'] !== '' || (string)$request['Resend'] || (string)$request['Continue'] !== '')
		{
			LocalRedirect('posting_admin.php?ID=' . $ID . '&action=send&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get());
		}

		if ((string)$request['apply'] !== '')
		{
			$_SESSION['SESS_ADMIN']['POSTING_EDIT_MESSAGE'] = [
				'MESSAGE' => GetMessage('post_save_ok'),
				'TYPE' => 'OK',
			];
			LocalRedirect('posting_edit.php?ID=' . $ID . '&lang=' . LANGUAGE_ID . '&' . $tabControl->ActiveTabParam());
		}
		else
		{
			LocalRedirect('posting_admin.php?lang=' . LANGUAGE_ID);
		}
	}
	else
	{
		if ($e = $APPLICATION->GetException())
		{
			$message = new CAdminMessage(GetMessage('post_save_error'), $e);
		}
		$bVarsFromForm = true;
	}
}

ClearVars();
$str_STATUS = 'D';
$str_DIRECT_SEND = 'Y';
$str_BODY = '';
$str_BODY_TYPE = 'text';
$str_FROM_FIELD = COption::GetOptionString('subscribe', 'default_from');
$str_TO_FIELD = COption::GetOptionString('subscribe', 'default_to');
$str_SUBJECT = '';
$str_AUTO_SEND_FLAG = 'N';
$str_AUTO_SEND_TIME = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL');
$str_SUBSCR_FORMAT = '';
$str_TIMESTAMP_X = '';
$str_DATE_SENT = '';
$str_EMAIL_FILTER = '';
$str_BCC_FIELD = '';
$str_CHARSET = '';

if ($ID > 0)
{
	$post = CPosting::GetByID($ID);
	if (!($post_arr = $post->ExtractFields('str_')))
	{
		$ID = 0;
	}
}

$aPostRub = [];
$aPostGrp = [];
if ($bVarsFromForm)
{
	if (!array_key_exists('DIRECT_SEND', $_REQUEST))
	{
		$DIRECT_SEND = 'N';
	}
	$DB->InitTableVarsForEdit('b_posting', '', 'str_');
	if (array_key_exists('AUTO_SEND_FLAG', $_REQUEST))
	{
		$str_AUTO_SEND_FLAG = 'Y';
	}
	else
	{
		$str_AUTO_SEND_FLAG = 'N';
	}

	if (is_array($request->getPost('RUB_ID')))
	{
		$aPostRub = $request->getPost('RUB_ID');
	}
	if (is_array($request->getPost('GROUP_ID')))
	{
		$aPostGrp = $request->getPost('GROUP_ID');
	}
}
elseif ($ID > 0)
{
	if ($str_AUTO_SEND_TIME <> '')
	{
		$str_AUTO_SEND_FLAG = 'Y';
	}
	else
	{
		$str_AUTO_SEND_FLAG = 'N';
	}

	$post_rub = CPosting::GetRubricList($ID);
	while ($ar = $post_rub->Fetch())
	{
		$aPostRub[] = $ar['ID'];
	}

	$post_grp = CPosting::GetGroupList($ID);
	while ($post_grp_arr = $post_grp->Fetch())
	{
		$aPostGrp[] = $post_grp_arr['ID'];
	}
}

$APPLICATION->SetTitle(($ID > 0 && !$bCopy ? GetMessage('post_title_edit') . $ID : GetMessage('post_title_add')));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$aMenu = [
	[
		'TEXT' => GetMessage('post_mnu_list'),
		'TITLE' => GetMessage('post_mnu_list_title'),
		'LINK' => 'posting_admin.php?lang=' . LANGUAGE_ID,
		'ICON' => 'btn_list',
	]
];
if ($ID > 0 && !$bCopy)
{
	$aMenu[] = ['SEPARATOR' => 'Y'];
	$aMenu[] = [
		'TEXT' => GetMessage('post_mnu_add'),
		'TITLE' => GetMessage('post_mnu_add_title'),
		'LINK' => 'posting_edit.php?lang=' . LANGUAGE_ID,
		'ICON' => 'btn_new',
	];
	$aMenu[] = [
		'TEXT' => GetMessage('post_mnu_copy'),
		'TITLE' => GetMessage('post_mnu_copy_title'),
		'LINK' => 'posting_edit.php?ID=' . $ID . '&amp;action=copy&amp;lang=' . LANGUAGE_ID,
		'ICON' => 'btn_copy',
	];
	$aMenu[] = [
		'TEXT' => GetMessage('post_mnu_del'),
		'TITLE' => GetMessage('post_mnu_del_title'),
		'LINK' => "javascript:if(confirm('" . GetMessage('post_mnu_confirm') . "'))window.location='posting_admin.php?ID=" . $ID . '&action=delete&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get() . "';",
		'ICON' => 'btn_delete',
	];
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if (
	isset($_SESSION['SESS_ADMIN']['POSTING_EDIT_MESSAGE'])
	&& is_array($_SESSION['SESS_ADMIN']['POSTING_EDIT_MESSAGE'])
)
{
	CAdminMessage::ShowMessage($_SESSION['SESS_ADMIN']['POSTING_EDIT_MESSAGE']);
	$_SESSION['SESS_ADMIN']['POSTING_EDIT_MESSAGE'] = false;
}

if ($message)
{
	echo $message->Show();
}
elseif ($posting->LAST_ERROR != '')
{
	CAdminMessage::ShowMessage($posting->LAST_ERROR);
}
?>

<form method="POST" Action="<?php echo $APPLICATION->GetCurPage()?>"  ENCTYPE="multipart/form-data" name="post_form">
<?php
$tabControl->Begin();
?>
<?php
//********************
//Posting issue
//********************
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('post_info')?></td>
	</tr>
<?php if ($ID > 0 && !$bCopy):?>
	<tr>
		<td><?php echo GetMessage('post_date_upd')?></td>
		<td><?php echo $str_TIMESTAMP_X;?></td>
	</tr>
	<?php if ($str_DATE_SENT !== ''):?>
	<tr>
		<td><?php echo GetMessage('post_date_sent')?></td>
		<td><?php echo $str_DATE_SENT;?></td>
	</tr>
	<?php endif;?>
	<?php
	$arEmailStatuses = CPosting::GetEmailStatuses($ID);
	if (array_key_exists('Y', $arEmailStatuses) || array_key_exists('E', $arEmailStatuses)):?>
	<tr>
		<td><?php echo GetMessage('POST_TO')?></td>
		<td>[&nbsp;<a class="tablebodylink" href="javascript:void(0)" OnClick="jsUtils.OpenWindow('posting_bcc.php?ID=<?php echo $ID?>&lang=<?php echo LANGUAGE_ID?>&find_status_id=E&set_filter=Y', 600, 500);"><?php echo GetMessage('POST_SHOW_LIST')?></a>&nbsp;]</td>
	</tr>
	<?php endif;?>
<?php endif; //ID?>
	<tr>
		<td width="40%"><?php echo GetMessage('post_stat')?></td>
		<td width="60%">
<?php
if ($ID > 0 && !$bCopy)
{
	if ($str_STATUS === 'D')
	{
		echo GetMessage('POST_STATUS_DRAFT');
	}
	elseif ($str_STATUS === 'S')
	{
		echo GetMessage('POST_STATUS_SENT');
	}
	elseif ($str_STATUS === 'P')
	{
		echo GetMessage('POST_STATUS_PART');
	}
	elseif ($str_STATUS === 'E')
	{
		echo GetMessage('POST_STATUS_ERROR');
	}
	elseif ($str_STATUS === 'W')
	{
		echo GetMessage('POST_STATUS_WAIT');
	}
}
else
{
	echo GetMessage('POST_STATUS_DRAFT');
}
?>
		</td>
	</tr>
<?php if ($ID > 0 && !$bCopy && $str_STATUS !== 'D'):?>
	<tr>
		<td><?php echo GetMessage('post_status_change')?></td>
		<td>
		<select class="typeselect" name="STATUS">
			<option value=""><?php echo GetMessage('post_status_not_change')?></option>
			<?php if ($str_STATUS !== 'D' && $str_STATUS !== 'P'):?>
			<option value="D"><?php echo GetMessage('POST_STATUS_DRAFT')?></option>
			<?php endif;?>
			<?php if ($str_STATUS === 'P'):?>
			<option value="W"><?php echo GetMessage('POST_STATUS_WAIT')?></option>
			<?php endif;?>
		</select>
		</td>
	</tr>
<?php endif;?>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('post_fields')?></td>
	</tr>
	<tr class="">
		<td><?php echo GetMessage('post_fields_from')?></td>
		<td><input type="text" name="FROM_FIELD" value="<?php echo $str_FROM_FIELD;?>" size="30" maxlength="255"></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('post_fields_to')?></td>
		<td><input type="text" name="TO_FIELD" value="<?php echo $str_TO_FIELD;?>" size="30" maxlength="255"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?php echo GetMessage('post_fields_subj')?></td>
		<td><input type="text" name="SUBJECT" value="<?php echo $str_SUBJECT;?>" size="30" maxlength="255"></td>
	</tr>
	<tr class="heading adm-detail-required-field">
		<td colspan="2"><?php echo GetMessage('post_fields_text')?><span class="required"><sup>1</sup></span></td>
	</tr>
	<tr>
		<td colspan="2">
		<?php
		CFileMan::AddHTMLEditorFrame('BODY', $str_BODY, 'BODY_TYPE', $str_BODY_TYPE, ['height' => '400', 'width' => '100%'], 'N', 0, '', '', SITE_ID);
		?>
		</td>
	</tr>
<?php
//********************
//Receipients
//********************
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('post_subscr')?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?php echo GetMessage('post_rub')?></td>
		<td>
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="RUB_ID_ALL" name="RUB_ID_ALL" value="Y" OnClick="CheckAll('RUB_ID', true)"></div>
					<div class="adm-list-label"><label for="RUB_ID_ALL"><?php echo GetMessage('MAIN_ALL')?></label></div>
				</div>
			<?php
			$rub = CRubric::GetList(['LID' => 'ASC', 'SORT' => 'ASC', 'NAME' => 'ASC'], ['ACTIVE' => 'Y']);
			while ($ar = $rub->GetNext()):
			?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="RUB_ID_<?php echo $ar['ID']?>" name="RUB_ID[]" value="<?php echo $ar['ID']?>" <?php echo (in_array($ar['ID'], $aPostRub)) ? 'checked' : '';?> OnClick="CheckAll('RUB_ID')"></div>
					<div class="adm-list-label"><label for="RUB_ID_<?php echo $ar['ID']?>"><?php echo '[' . $ar['LID'] . '] ' . $ar['NAME']?></label></div>
				</div>
			<?php endwhile;?>
			</div>
		</td>
	</tr>
	<tr>
		<td width="40%"><?php echo GetMessage('post_format')?></td>
		<td width="60%">
		<select class="typeselect" name="SUBSCR_FORMAT" id="SUBSCR_FORMAT">
			<option value="" <?php echo ($str_SUBSCR_FORMAT === '') ? 'selected' : '';?>><?php echo GetMessage('post_format_any')?></option>
			<option value="text" <?php echo ($str_SUBSCR_FORMAT === 'text') ? 'selected' : '';?>><?php echo GetMessage('post_format_text')?></option>
			<option value="html" <?php echo ($str_SUBSCR_FORMAT === 'html') ? 'selected' : '';?>>HTML</option>
		</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('post_users')?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?php echo GetMessage('post_groups')?></td>
		<td>
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="GROUP_ID_ALL" name="GROUP_ID_ALL" value="Y" OnClick="CheckAll('GROUP_ID', true)"></div>
					<div class="adm-list-label"><label for="GROUP_ID_ALL"><?php echo GetMessage('MAIN_ALL')?></label></div>
				</div>

		<?php
			$group = CGroup::GetList();
			while ($ar = $group->GetNext())
			{
			?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="GROUP_ID_<?php echo $ar['ID']?>" name="GROUP_ID[]" value="<?php echo $ar['ID']?>" <?php echo (in_array($ar['ID'], $aPostGrp)) ? 'checked' : '';?> OnClick="CheckAll('GROUP_ID')"></div>
					<div class="adm-list-label"><label for="GROUP_ID_<?php echo $ar['ID']?>"><?php echo $ar['NAME']?>&nbsp;[<a href="/bitrix/admin/group_edit.php?ID=<?php echo $ar['ID']?>&amp;lang=<?php echo LANGUAGE_ID?>"><?php echo $ar['ID']?></a>]</label></div>
				</div>
			<?php
			}
		?>
			</div>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('post_filter_title')?></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('post_filter')?></td>
		<td><input type="text" name="EMAIL_FILTER" id="EMAIL_FILTER" value="<?php echo $str_EMAIL_FILTER?>" size="30" maxlength="255"></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		<script>
		<!--
		function ShowEMails()
		{
			var strParam = 'EMAIL_FILTER='+escape(document.post_form.EMAIL_FILTER.value);
			var aCheckBox;
			try
			{
				if('['+document.post_form.elements['RUB_ID[]'].type+']'=='[undefined]')
					aCheckBox = document.post_form.elements['RUB_ID[]'];
				else
					aCheckBox = new Array(document.post_form.elements['RUB_ID[]']);

				for(i=0; i<aCheckBox.length; i++)
					if(aCheckBox[i].checked)
						strParam += ('&RUB_ID[]='+aCheckBox[i].value);
			}
			catch (e)
			{
				//there is no rubrics so we can safely ignore
			}
			if('['+document.post_form.elements['GROUP_ID[]'].type+']'=='[undefined]')
				aCheckBox = document.post_form.elements['GROUP_ID[]'];
			else
				aCheckBox = new Array(document.post_form.elements['GROUP_ID[]']);

			for(i=0; i<aCheckBox.length; i++)
				if(aCheckBox[i].checked)
					strParam += ('&GROUP_ID[]='+aCheckBox[i].value);

			strParam += ('&SUBSCR_FORMAT='+document.post_form.SUBSCR_FORMAT[document.post_form.SUBSCR_FORMAT.selectedIndex].value);

			jsUtils.OpenWindow('posting_search.php?'+strParam+'&lang=<?php echo LANGUAGE_ID?>', 600, 500);
		}
		function CheckAll(prefix, act)
		{
			var bCheck = document.getElementById(prefix+'_ALL').checked;
			var bAll = true;
			var aCheckBox;
			try
			{
				if('['+document.post_form.elements[prefix+'[]'].type+']'=='[undefined]')
					aCheckBox = document.post_form.elements[prefix+'[]'];
				else
					aCheckBox = new Array(document.post_form.elements[prefix+'[]']);

				for(i=0; i<aCheckBox.length; i++)
				{
					if(act)
					{
						if(bCheck)
							aCheckBox[i].checked = true;
						else
							aCheckBox[i].checked = false;
					}
					else
						bAll = bAll && aCheckBox[i].checked;
				}
			}
			catch (e)
			{
				//there is no rubrics so we can safely ignore
			}
			if(!act)
				document.getElementById(prefix+'_ALL').checked = bAll;
		}
		CheckAll('RUB_ID');
		CheckAll('GROUP_ID');
		//-->
		</script>[ <a class="tablebodylink" title="<?php echo GetMessage('post_list_title')?>" href="javascript:ShowEMails()"><?php echo GetMessage('post_filter_list')?></a> ]</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('post_additional')?></td>
	</tr>
	<tr>
		<td align="center" colspan="2"><textarea name="BCC_FIELD" cols="50" rows="7" style="width:100%"><?php echo $str_BCC_FIELD?></textarea></td>
	</tr>
<?php
//********************
//Attachments
//********************
$tabControl->BeginNextTab();
?>
<?php
if (COption::GetOptionString('subscribe', 'attach_images') === 'Y' && $str_BODY !== '' && $str_BODY_TYPE === 'html'):
	$tools = new CMailTools;
	$tools->ReplaceImages($str_BODY);
	if (count($tools->aMatches) > 0):
?>
	<tr>
		<td width="40%" class="adm-detail-valign-top"><?=GetMessage('post_images_list')?>:</td>
		<td width="60%">
			<table border="0" cellspacing="0" cellpadding="0" class="internal">
			<tr class="heading">
				<td align="center"><?php echo GetMessage('post_file')?></td>
				<td align="center"><?php echo GetMessage('post_size')?></td>
			</tr>
			<?php
			foreach ($tools->aMatches as $attachment):
				$image = new \Bitrix\Main\File\Image($attachment['PATH']);
				if ($image->getInfo() === null)
				{
					continue;
				}
			?>
			<tr>
				<td><a href="<?php echo $attachment['SRC']?>" target=_blank><?php echo $attachment['DEST']?></a></td>
				<td align="right"><?php echo filesize($attachment['PATH'])?></td>
			</tr>
			<?php endforeach;?>
			</table>
		</td>
	</tr>
<?php
	endif;
endif;
?>
	<?php if ($ID > 0 && ($rsFiles = CPosting::GetFileList($ID)) && ($arFile = $rsFiles->GetNext())):?>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage('post_attachments_list')?>:</td>
		<td>
		<table border="0" cellpadding="0" cellspacing="0" class="internal">
		<tr class="heading">
			<td align="center"><?php echo GetMessage('post_att_file')?></td>
			<td align="center"><?php echo GetMessage('post_size')?></td>
			<td align="center"><?php echo GetMessage('post_att_delete')?></td>
		</tr>
<?php
		do
		{
?>
			<tr>
				<td><a href="posting_attachment.php?POSTING_ID=<?php echo $ID?>&amp;FILE_ID=<?php echo $arFile['ID']?>"><?php echo $arFile['ORIGINAL_NAME']?><a></td>
				<td align="right"><?php echo $arFile['FILE_SIZE']?></td>
				<td align="center">
					<input type="checkbox" name="FILE_ID[<?php echo $arFile['ID']?>]" value="<?php echo $arFile['ID']?>">
					<?php if ($bCopy):?>
					<input type="hidden" name="FILES[<?php echo $arFile['ID']?>]" value="<?php echo $arFile['ID']?>">
					<?php endif?>
				</td>
			</tr>
<?php
		} while ($arFile = $rsFiles->GetNext());
?>
		</table></td>
	</tr>
	<?php endif;?>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage('post_attachments_load')?>:</td>
		<td>
			<table border="0" cellpadding="0" cellspacing="0">
			<tr><td><?php echo CFile::InputFile('NEW_FILE[n0]', 40, 0)?><br><br></td></tr>
			<tr><td><?php echo CFile::InputFile('NEW_FILE[n1]', 40, 0)?><br><br></td></tr>
			<tr><td><?php echo CFile::InputFile('NEW_FILE[n2]', 40, 0)?><br><br></td></tr>
			</table>
		</td>
	</tr>
<?php
//********************
//Parameters
//********************
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('post_params')?></td>
	</tr>
	<tr>
		<td width="40%"><?php echo GetMessage('post_enc')?></td>
		<td width="60%">
		<select class="typeselect" name="CHARSET">
		<?php
		$aCharset = explode(',', COption::GetOptionString('subscribe', 'posting_charset'));
		foreach ($aCharset as $strCharset)
		{
			?><option value="<?php echo htmlspecialcharsbx($strCharset)?>" <?php echo ($ID > 0 && mb_strtolower($str_CHARSET) === mb_strtolower($strCharset)) ? 'selected' : '';?>><?php echo htmlspecialcharsEx($strCharset)?></option><?php
		}
		?>
		</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?php echo GetMessage('post_send_params')?></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('post_direct')?></td>
		<td>
			<input type="checkbox" name="DIRECT_SEND" value="Y" <?php echo ($str_DIRECT_SEND !== 'N') ? 'checked' : '';?>>
		</td>
	</tr>
	<?php if ($str_STATUS === 'D' || $str_STATUS === 'W'):?>
	<tr>
		<td><?php echo GetMessage('post_send_flag')?></td>
		<td>
			<input type="checkbox" name="AUTO_SEND_FLAG" value="Y" <?php echo ($str_AUTO_SEND_FLAG === 'Y') ? 'checked' : '';?> OnClick="EnableAutoSend()">
		</td>
	</tr>
	<tr>
		<td><?php echo GetMessage('post_send_time') . ':'?><span class="required"><sup>2</sup></span></td>
		<td><?php echo CalendarDate('AUTO_SEND_TIME', $str_AUTO_SEND_TIME, 'post_form', '20')?></td>
	</tr>
<script>
<!--
function EnableAutoSend()
{
	document.post_form.AUTO_SEND_TIME.disabled = !document.post_form.AUTO_SEND_FLAG.checked;
}
EnableAutoSend();
//-->
</script>
	<?php else:
	$str_AUTO_SEND_FLAG = $str_AUTO_SEND_TIME <> '' ? 'Y' : 'N';
	?>
	<tr>
		<td><?php echo GetMessage('post_send_flag')?></td>
		<td><?php echo ($str_AUTO_SEND_FLAG === 'Y' ? GetMessage('post_yes') : GetMessage('post_no'))?></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('post_send_time') . ':'?><span class="required"><sup>2</sup></span>
		<input type="hidden" name="AUTO_SEND_FLAG" value="<?php echo $str_AUTO_SEND_FLAG?>">
		<input type="hidden" name="AUTO_SEND_TIME" value="<?php echo $str_AUTO_SEND_TIME?>">
		</td>
		<td><?php echo $str_AUTO_SEND_TIME?></td>
	</tr>
	<?php endif;?>
<?php
$tabControl->Buttons(
	[
		'disabled' => ($POST_RIGHT < 'W'),
		'back_url' => 'posting_admin.php?lang=' . LANGUAGE_ID,

	]
);
?>
<?php echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?php if ($str_STATUS === 'D'):?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input <?php echo ($POST_RIGHT < 'W') ? 'disabled' : '';?> type="submit" value="<?php echo GetMessage('post_butt_send')?>" name="Send" title="<?php echo GetMessage('post_hint_send')?>">
<?php elseif ($str_STATUS === 'W'):?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input <?php echo ($POST_RIGHT < 'W') ? 'disabled' : '';?> type="submit" value="<?php echo GetMessage('post_continue')?>" name="Continue" title="<?php echo GetMessage('post_continue_conf')?>">
<?php elseif ($str_STATUS === 'E'):?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input <?php echo ($POST_RIGHT < 'W') ? 'disabled' : '';?> type="submit" value="<?php echo GetMessage('post_resend')?>" name="Resend" title="<?php echo GetMessage('post_resend_conf')?>">
<?php endif?>
<?php if ($ID > 0):?>
	<?php if ($bCopy):?>
		<input type="hidden" name="COPY_ID" value="<?=$ID?>">
	<?php else:?>
		<input type="hidden" name="ID" value="<?=$ID?>">
	<?php endif?>
<?php endif;?>
<?php
$tabControl->End();
?>
</form>

<?php
$tabControl->ShowWarnings('post_form', $message);
?>

<?php echo BeginNote();?>
<span class="required"><sup>1</sup></span><?php echo GetMessage('post_note')?><br>
<br>
<span class="required"><sup>2</sup></span><?php echo GetMessage('post_send_msg')?><br>
<?php echo EndNote();?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
