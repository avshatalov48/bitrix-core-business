<?php
define('ADMIN_MODULE_NAME', 'clouds');

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @global CUser $USER */
global $USER;
/** @global CMain $APPLICATION */
global $APPLICATION;

if (!$USER->CanDoOperation('clouds_config'))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

if (!CModule::IncludeModule('clouds'))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

IncludeModuleLangFile(__FILE__);
$sTableID = 'tbl_clouds_duplicates_list';

$obBucket = new CCloudStorageBucket(intval($_GET['bucket']), false);
$APPLICATION->SetTitle($obBucket->BUCKET);
if (!$obBucket->Init())
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
	$message = new CAdminMessage([
		'MESSAGE' => GetMessage('CLO_STORAGE_DUP_ERROR'),
		'DETAILS' => GetMessage('CLO_STORAGE_DUP_UNKNOWN_ERROR', ['#CODE#' => 'L00']),
	]);
	echo $message->Show();
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
	die();
}

if ($obBucket->READ_ONLY === 'Y')
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
	$message = new CAdminMessage([
		'MESSAGE' => GetMessage('CLO_STORAGE_DUP_ERROR'),
		'DETAILS' => GetMessage('CLO_STORAGE_DUP_RO_ERROR'),
	]);
	echo $message->Show();
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
	die();
}

if (
	isset($_REQUEST['act'])
	&& $_REQUEST['act'] == 'process'
	&& check_bitrix_sessid()
)
{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_js.php';

	$etime = time() + 20;
	$batchSize = 100;
	$lastKey = (int)$_REQUEST['lastKey'];
	$total = (int)$_REQUEST['total'];
	$progress = (int)$_REQUEST['progress'];
	$deletedSize = (int)$_REQUEST['deletedSize'];


	AddEventHandler('clouds', 'OnAfterDeleteFile', function ($bucket, $eventData, $filePath)
	{
		global $deletedSize;
		$deletedSize += $eventData['size'];
	});

	$c = 0;
	$rsData = \Bitrix\Clouds\FileHashTable::duplicateList($obBucket->ID, ['>FILE_ID_MIN' => $lastKey], ['FILE_ID_MIN' => 'ASC'], $batchSize);
	while ($data = $rsData->fetch())
	{
		$fileIds = \Bitrix\Clouds\FileHashTable::getFileDuplicates($obBucket->ID, $data['FILE_HASH'], $data['FILE_SIZE']);
		$originalId = \Bitrix\Clouds\FileHashTable::prepareDuplicates($obBucket->ID, $fileIds);
		if ($originalId && $fileIds)
		{
			while ($fileIds)
			{
				CFile::DeleteDuplicates($originalId, array_splice($fileIds, 0, 10));
				if (time() > $etime)
				{
					break;
				}
			}
			if (!$fileIds)
			{
				$c++;
				$progress++;
				$lastKey = $originalId;
			}
		}

		if (time() > $etime)
		{
			break;
		}
	}

	if ($c < $batchSize && !$data)
	{
		$message = new CAdminMessage([
			'TYPE' => 'OK',
			'MESSAGE' => GetMessage('CLO_STORAGE_DUP_DONE_PROCESS'),
			'DETAILS' => GetMessage('CLO_STORAGE_DUP_PROCESS_PROGRESS', [
				'#free_size#' => CFile::FormatSize($deletedSize),
			]),
			'HTML' => true,
		]);
		echo $message->Show();
		?>
		<script>
			CloseWaitWindow();
		</script>
		<?php
	}
	else
	{
		$message = new CAdminMessage([
			'TYPE' => 'PROGRESS',
			'MESSAGE' => GetMessage('CLO_STORAGE_DUP_IN_PROCESS'),
			'DETAILS' => GetMessage('CLO_STORAGE_DUP_PROCESS_PROGRESS', [
				'#free_size#' => CFile::FormatSize($deletedSize),
			]) . '#PROGRESS_BAR#',
			'HTML' => true,
			'PROGRESS_TOTAL' => $total,
			'PROGRESS_VALUE' => $progress,
			'BUTTONS' => [
				[
					'VALUE' => GetMessage('CLO_STORAGE_DUP_PROCESS_STOP'),
					'ONCLICK' => 'window.location = \'' . CUtil::AddSlashes('/bitrix/admin/clouds_duplicates_list.php?lang=' . urlencode(LANGUAGE_ID) . '&bucket=' . urlencode($obBucket->ID)) . '\'',
				],
			],
		]);
		echo $message->Show();
		?>
		<script>
			Start(<?=$lastKey?>, {'total' : <?=$total?>, 'progress' : <?=$progress?>, 'deletedSize' : <?=$deletedSize?>});
		</script>
		<?php
	}

	require $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_admin_js.php';
}

$oSort = new CAdminSorting($sTableID, 'FILE_HASH', 'asc');
/** @var string $by */
/** @var string $order */
$lAdmin = new CAdminList($sTableID, $oSort);

$arID = $lAdmin->GroupAction();
$action = isset($_REQUEST['action']) && is_string($_REQUEST['action']) ? $_REQUEST['action'] : '';
if ($action && is_array($arID))
{
	foreach ($arID as $ID)
	{
		if ($ID == '' || intval($ID) <= 0)
		{
			continue;
		}

		if ($action === 'process')
		{
			$fileSizeAndHash = explode(',', $ID, 2);
			$fileIds = \Bitrix\Clouds\FileHashTable::getFileDuplicates($obBucket->ID, $fileSizeAndHash[1], $fileSizeAndHash[0]);
			$originalId = \Bitrix\Clouds\FileHashTable::prepareDuplicates($obBucket->ID, $fileIds);
			if ($originalId && $fileIds)
			{
				CFile::DeleteDuplicates($originalId, $fileIds);
			}
		}
	}
}

$arHeaders = [
	[
		'id' => 'FILE_HASH',
		'content' => GetMessage('CLO_STORAGE_DUP_FILE_HASH'),
		'default' => true,
		'sort' => 'FILE_HASH',
	],
	[
		'id' => 'FILE_SIZE',
		'content' => GetMessage('CLO_STORAGE_DUP_FILE_SIZE'),
		'align' => 'right',
		'default' => true,
		'sort' => 'FILE_SIZE',
	],
	[
		'id' => 'FILE_COUNT',
		'content' => GetMessage('CLO_STORAGE_DUP_FILE_COUNT'),
		'align' => 'right',
		'default' => true,
		'sort' => 'FILE_COUNT',
	],
	[
		'id' => 'FILE_LIST',
		'content' => GetMessage('CLO_STORAGE_DUP_FILE_LIST'),
		'default' => true,
	],
];
$lAdmin->AddHeaders($arHeaders);

if ($order && $by && in_array($by, ['FILE_HASH', 'FILE_SIZE', 'FILE_COUNT'], true))
{
	$sort = [$by => ($order === 'desc' ? 'desc' : 'asc')];
}
else
{
	$sort = ['FILE_HASH' => 'asc'];
}


$rsData = \Bitrix\Clouds\FileHashTable::duplicateList($obBucket->ID, [], $sort);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(''));
while (is_array($arRes = $rsData->Fetch()))
{
	$fileList = [];
	$idList = \Bitrix\Clouds\FileHashTable::getFileDuplicates($obBucket->ID, $arRes['FILE_HASH'], $arRes['FILE_SIZE']);
	foreach ($idList as $i => $fileId)
	{
		$file = CFile::GetFileArray($fileId);
		if ($file)
		{
			$fileList[$file['ID']] = '<a href="' . $file['SRC'] . '">' . htmlspecialcharsEx($file['SUBDIR'] . '/' . $file['FILE_NAME']) . '</a>';
		}
	}

	$row =& $lAdmin->AddRow($arRes['FILE_SIZE'] . ',' . $arRes['FILE_HASH'], $arRes);

	$row->AddViewField('FILE_SIZE', CFile::FormatSize($arRes['FILE_SIZE']));
	$row->AddViewField('FILE_LIST', implode('<br>', $fileList));
	//$row->AddViewField('FILE_HASH', $arRes['FILE_HASH'].'@'.implode(',', array_keys($fileList)));

	if ($fileList)
	{
		$arActions = [
			[
				'ICON' => 'move',
				'TEXT' => GetMessage('CLO_STORAGE_DUP_PROCESS'),
				'ACTION' => $lAdmin->ActionDoGroup($arRes['FILE_SIZE'] . ',' . $arRes['FILE_HASH'], 'process', 'bucket=' . $obBucket->ID)
			]
		];
		$row->AddActions($arActions);
	}
}

$aContext = [
	[
		'TEXT' => GetMessage('CLO_STORAGE_DUP_PROCESS_ALL'),
		'LINK' => "javascript:Start('', {'total' : " . (int)$rsData->NavRecordCount . '})',
	],
];

$lAdmin->AddAdminContextMenu($aContext, /*$bShowExcel=*/false);

$lAdmin->BeginPrologContent();
?>
<script>
	function Start(lastKey, data)
	{
		ShowWaitWindow();
		BX.ajax.post(
			'clouds_duplicates_list.php'
				+ '?lang=<?php echo LANGUAGE_ID?>'
				+ '&<?php echo bitrix_sessid_get()?>'
				+ '&act=process'
				+ '&bucket=<?=$obBucket->ID?>'
				+ '&lastKey=' + lastKey
				,
			data,
			function (result)
			{
				BX('progress').innerHTML = result;
			}
		);
	}
</script>
<div id="progress">
</div>
<?php

$lAdmin->EndPrologContent();

$lAdmin->CheckListMode();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$lAdmin->DisplayList();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
