<?php
define('ADMIN_MODULE_NAME', 'bitrixcloud');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
IncludeModuleLangFile(__FILE__);
/* @var CMain $APPLICATION */
/* @var CUser $USER */
if (!$USER->CanDoOperation('bitrixcloud_backup') || !CModule::IncludeModule('bitrixcloud'))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}
$strError = '';
$APPLICATION->SetTitle(GetMessage('BCL_BACKUP_JOB_TITLE'));

try
{
	$sTableID = 't_bitrixcloud_backup_job';
	$lAdmin = new CAdminList($sTableID);

	if ($arID = $lAdmin->GroupAction())
	{
		foreach ($arID as $ID)
		{
			if ($ID == '')
			{
				continue;
			}
			$ID = intval($ID);
			if ($_REQUEST['action'] === 'delete')
			{
				$strError = CBitrixCloudBackup::getInstance()->deleteBackupJob();
			}
		}
	}

	if (
		$_SERVER['REQUEST_METHOD'] === 'POST'
		&& check_bitrix_sessid()
	)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/backup.php';
		$backup_secret_key = CPasswordStorage::Get('backup_secret_key');
		if ($backup_secret_key == '')
		{
			$backup_secret_key = \Bitrix\Main\Security\Random::getString(10);
			CPasswordStorage::Set('backup_secret_key', $backup_secret_key);
		}
		$time = 0;
		if (preg_match('/^(\\d{1,2}):(\\d{1,2})$/', $_POST['TIME'], $match))
		{
			$time = $match[1] * 3600 + $match[2] * 60;
		}
		$strError = CBitrixCloudBackup::getInstance()->addBackupJob($backup_secret_key, $_POST['URL'], $time, $_POST['WEEK_DAYS']);
		if ($strError === '')
		{
			LocalRedirect('/bitrix/admin/bitrixcloud_backup_job.php?lang=' . LANGUAGE_ID);
		}
	}

	$arHeaders = [
		[
			'id' => 'URL',
			'content' => GetMessage('BCL_BACKUP_JOB_URL'),
			'default' => true,
		],
		[
			'id' => 'TIME',
			'content' => GetMessage('BCL_BACKUP_JOB_TIME'),
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'WEEK_DAYS',
			'content' => GetMessage('BCL_BACKUP_JOB_WEEK_DAYS'),
			'default' => true,
		],
		[
			'id' => 'STATUS',
			'content' => GetMessage('BCL_BACKUP_JOB_STATUS'),
			'default' => true,
		],
		[
			'id' => 'FINISH_TIME',
			'content' => GetMessage('BCL_BACKUP_JOB_FINISH_TIME'),
			'align' => 'right',
			'default' => true,
		],
	];

	$arJobs = CBitrixCloudBackup::getInstance()->getBackupJob();
	if (is_string($arJobs))
	{
		throw new CBitrixCloudException($arJobs);
	}

	$lAdmin->AddHeaders($arHeaders);
	$rsData = new CDBResult;
	$rsData->InitFromArray($arJobs);
	$rsData = new CAdminResult($rsData, $sTableID);

	while ($arRes = $rsData->GetNext())
	{
		$row = $lAdmin->AddRow($arRes['URL'], $arRes);
		if ($arRes['STATUS'] == '')
		{
			$status = GetMessage('BCL_BACKUP_JOB_NEVER');
		}
		else
		{
			$status = $arRes['STATUS'];
		}
		$row->AddViewField('STATUS', $status);

		$week_days = [];
		foreach ($arRes['WEEK_DAYS'] as $dow)
		{
			if (HasMessage('DOW_' . $dow))
			{
				$week_days[] = GetMessage('DOW_' . $dow);
			}
		}
		$row->AddViewField('WEEK_DAYS', implode(', ', $week_days));

		if ($_GET['allow_manage'] === 'y')
		{
			$arActions = [
				[
					'ICON' => 'delete',
					'TEXT' => GetMessage('BCL_BACKUP_JOB_DELETE'),
					'ACTION' => "if(confirm('" . GetMessage('BCL_BACKUP_JOB_DELETE_CONF') . "')) " . $lAdmin->ActionDoGroup($arRes['URL'], 'delete'),
				],
			];
			$row->AddActions($arActions);
		}
	}

	if (empty($arJobs) && $_GET['allow_manage'] === 'y')
	{
		$aContext = [
			[
				'TEXT' => GetMessage('BCL_BACKUP_JOB_ADD'),
				'LINK' => 'javascript:show_upload_form()',
				'TITLE' => '',
				'ICON' => 'btn_new',
			],
		];
		$lAdmin->AddAdminContextMenu($aContext, /*$bShowExcel=*/false);

		$lAdmin->BeginPrologContent();

		if ($strError)
		{
			CAdminMessage::ShowMessage($strError);
		}

		$aTabs = [
			[
				'DIV' => 'edit1',
				'TAB' => GetMessage('BCL_BACKUP_JOB_ADD_TAB'),
				'ICON' => 'main_user_edit',
				'TITLE' => GetMessage('BCL_BACKUP_JOB_ADD_TAB_TITLE'),
			],
		];
		$tabControl = new CAdminTabControl('tabControl', $aTabs, true, true);
		?>
		<script>

			function show_upload_form()
			{
				(new BX.fx({
					start: 0,
					finish: 200,
					time: 0.5,
					type: 'accelerated',
					callback: function(res){
						BX('upload_form', true).style.height = res+'px';
					},
					callback_start: function(){
						BX('upload_form', true).style.height = '0px';
						BX('upload_form', true).style.overflow = 'hidden';
						BX('upload_form', true).style.display = 'block';
					},
					callback_complete: function(){
						BX('upload_form', true).style.height = 'auto';
						BX('upload_form', true).style.overflow = 'auto';
					}
				})).start();
			}
			function hide_upload_form()
			{
				BX('upload_form').style.display='none';
				return;
			}
		</script>
		<div id="upload_form" <?php echo $strError === '' ? 'style="display:none;height:200px;"' : '';?>>
			<form method="POST" action="<?php echo htmlspecialcharsbx($APPLICATION->GetCurPageParam())?>"  enctype="multipart/form-data" name="editform" id="editform">
				<?php
				$tabControl->Begin();
				$tabControl->BeginNextTab();
				?>
				<tr>
					<td width="40%"><?php echo GetMessage('BCL_BACKUP_JOB_URL')?>:</td>
					<?php
					if ($strError)
					{
						$URL = $_POST['URL'];
					}
					else
					{
						/* @var \Bitrix\Main\HttpRequest $request */
						$request = \Bitrix\Main\Context::getCurrent()->getRequest();
						$URL = $request->isHttps() ? 'https://' : 'http://';
						$URL .= COption::GetOptionString('main', 'server_name');
					}
					?>
					<td width="60%"><input type="text" name="URL" size="45" value="<?php echo htmlspecialcharsbx($URL)?>"></td>
				</tr>
				<tr>
					<td><?php echo GetMessage('BCL_BACKUP_JOB_TIME')?>:</td>
					<?php
					if ($strError)
					{
						$TIME = $_POST['TIME'];
					}
					else
					{
						$TIME = sprintf('%02d:%d0', mt_rand(1,5), mt_rand(0, 5));
					}
					?>
					<td><input type="text" name="TIME" size="6" value="<?php echo htmlspecialcharsbx($TIME)?>"></td>
				</tr>
				<tr>
					<td class="adm-detail-valign-top"><?php echo GetMessage('BCL_BACKUP_JOB_WEEK_DAYS')?>:</td>
					<td>
						<?php
						$rand = mt_rand(0, 7);
						for ($i = 0; $i < 7; $i++):
						?>
							<input type="checkbox" name="WEEK_DAYS[]" value="<?php echo $i?>" id="dow_<?php echo $i?>" <?php
							if (
								(
									$strError === ''
									&& $i == $rand
								) || (
									$strError !== ''
									&& is_array($_POST['WEEK_DAYS'])
									&& in_array($i, $_POST['WEEK_DAYS'])
								)
							)
							{
								echo 'checked="checked"';
							}
							?>>
							<label for="dow_<?php echo $i?>"><?php echo GetMessage('DOW_' . $i)?></label>
							<br>
						<?php endfor;?>
					</td>
				</tr>
				<?php $tabControl->Buttons();?>
				<input type="hidden" name="action" value="add_new">
				<?php echo bitrix_sessid_post();?>
				<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID?>">
				<input type="submit" value="<?php echo GetMessage('BCL_BACKUP_JOB_SAVE_BTN')?>" class="adm-btn-save">
				<input type="button" value="<?php echo GetMessage('BCL_BACKUP_JOB_CANCEL_BTN')?>" onclick="hide_upload_form()">
				<?php
				$tabControl->End();
				?>
			</form>
		</div>
	<?php
		$lAdmin->EndPrologContent();
	}

	CJSCore::Init(['fx']);
	$lAdmin->CheckListMode();

	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

	$lAdmin->DisplayList();
}
catch (Exception $e)
{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
	CAdminMessage::ShowMessage($e->getMessage());
	$arFiles = false;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
