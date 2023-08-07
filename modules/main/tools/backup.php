<?php
if (ini_get('short_open_tag') == 0 && mb_strtoupper(ini_get('short_open_tag')) != 'ON')
	die("Error: short_open_tag parameter must be turned on in php.ini\n");

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
define('START_TIME', microtime(1));
define('BX_FORCE_DISABLE_SEPARATED_SESSION_MODE', true);
define('CLI', defined('BX_CRONTAB') && BX_CRONTAB === true || !$_SERVER['DOCUMENT_ROOT']);

if (!defined('NOT_CHECK_PERMISSIONS'))
{
	define('NOT_CHECK_PERMISSIONS', true);
}

$NS = array(); // NewState

if (CLI && defined('BX_CRONTAB')) // start from cron_events.php
{
	IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/dump.php');

	if (IntOption('dump_auto_enable') != 1)
		return;

	$l = COption::GetOptionInt('main', 'last_backup_start_time', 0);
	if (time() - $l < IntOption('dump_auto_interval') * 86400)
		return;

	$min_left = IntOption('dump_auto_time') - date('H')*60 - date("i");
	if ($min_left > 0 || $min_left < -60)
		return;

	define('LOCK_FILE', $_SERVER['DOCUMENT_ROOT'].'/bitrix/backup/auto_lock');

	if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/backup'))
		mkdir($_SERVER['DOCUMENT_ROOT'].'/bitrix/backup');

	if (file_exists(LOCK_FILE))
	{
		if (!($time = file_get_contents(LOCK_FILE)))
			RaiseErrorAndDie('Can\'t read file: '.LOCK_FILE, 1);

		if ($time + 86400 > time())
		{
			return;
		}
		else
		{
			ShowBackupStatus('Warning! Last backup has failed');
			CEventLog::Add(array(
				"SEVERITY" => "WARNING",
				"AUDIT_TYPE_ID" => "BACKUP_ERROR",
				"MODULE_ID" => "main",
				"ITEM_ID" => LOCK_FILE,
				"DESCRIPTION" => GetMessage('AUTO_LOCK_EXISTS_ERR', array('#DATETIME#' => ConvertTimeStamp($time))),
			));

			foreach(GetModuleEvents("main", "OnAutoBackupUnknownError", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array(array('TIME' => $time)));

			unlink(LOCK_FILE) || RaiseErrorAndDie('Can\'t delete file: '.LOCK_FILE, 2);
		}
	}

	if (!file_put_contents(LOCK_FILE, time()))
		RaiseErrorAndDie('Can\'t create file: '.LOCK_FILE, 3);

	COption::SetOptionInt('main', 'last_backup_start_time', time());
}
else
{
	define('NO_AGENT_CHECK', true);
	define("STATISTIC_SKIP_ACTIVITY_CHECK", true);
	if (!$_SERVER['DOCUMENT_ROOT'])
		$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__.'/../../../../');
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
	IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/dump.php');
}
if (!defined('DOCUMENT_ROOT'))
	define('DOCUMENT_ROOT', rtrim(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),'/'));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/backup.php");

$public = $USER->IsAdmin();
if ($public) // backup from public
{
	$NS =& $_SESSION['BX_DUMP_STATE'];
	if (isset($_REQUEST['start']) && $_REQUEST['start'] == 'Y')
		$NS = array();
}
elseif (!CLI) // hit from bitrixcloud service
{
	if ((!$backup_secret_key =  CPasswordStorage::Get('backup_secret_key')) || $backup_secret_key != $_REQUEST['secret_key'])
	{
#		echo $backup_secret_key."\n"; COption::SetOptionInt('main', 'dump_auto_enable'.'_auto', 2); # debug
		RaiseErrorAndDie('Secret key is incorrect', 10);
	}
	elseif (isset($_REQUEST['check_auth']) && $_REQUEST['check_auth'])
	{
		echo 'SUCCESS';
		exit(0);
	}
	if (IntOption('dump_auto_enable') != 2)
		RaiseErrorAndDie('Backup is disabled', 4);

	session_write_close();
	ini_set("session.use_strict_mode", "0");
	session_id(md5($backup_secret_key));
	session_start();
	$NS =& $_SESSION['BX_DUMP_STATE'];

	if ($NS['TIMESTAMP'] && ($i = IntOption('dump_max_exec_time_sleep')) > 0)
	{
		if (time() - $NS['TIMESTAMP'] < $i)
		{
			sleep(3);
			echo "NEXT\n".
			GetProgressPercent($NS);
			exit(0);
		}
	}
}

if (!file_exists(DOCUMENT_ROOT.'/bitrix/backup'))
	mkdir(DOCUMENT_ROOT.'/bitrix/backup');

if (!file_exists(DOCUMENT_ROOT."/bitrix/backup/index.php"))
{
	$f = fopen(DOCUMENT_ROOT."/bitrix/backup/index.php","w");
	fwrite($f,"<head><meta http-equiv=\"REFRESH\" content=\"0;URL=/bitrix/admin/index.php\"></head>");
	fclose($f);
}

while(ob_end_flush());

@set_time_limit(0);

$bGzip = function_exists('gzcompress');
$bBitrixCloud = function_exists('openssl_encrypt') && CModule::IncludeModule('bitrixcloud') && CModule::IncludeModule('clouds');

if ($public)
{
	$arParams = array(
		'dump_archive_size_limit' => 100 * 1024 * 1024,
		'dump_use_compression' => $bGzip,
		'dump_integrity_check' => 1,
		'dump_delete_old' => 0,
		'dump_site_id' => array(),

		'dump_base' => 1,
		'dump_base_skip_stat' => 0,
		'dump_base_skip_search' => 0,
		'dump_base_skip_log' => 0,

		'dump_file_public' => 1,
		'dump_file_kernel' => 1,
		'dump_do_clouds' => 0,
		'skip_mask' => 0,
		'skip_mask_array' => array(),
		'dump_max_file_size' => 0,
	);
}
else
{
	$arParams = array(
		'dump_archive_size_limit' => IntOption('dump_archive_size_limit'),
		'dump_use_compression' => $bGzip && IntOption('dump_use_compression'),
		'dump_integrity_check' => IntOption('dump_integrity_check'),

		'dump_delete_old' => IntOption('dump_delete_old'),
		'dump_old_time' => IntOption('dump_old_time'),
		'dump_old_cnt' => IntOption('dump_old_cnt'),
		'dump_old_size' => IntOption('dump_old_size'),

		'dump_site_id' => is_array($ar = unserialize(COption::GetOptionString("main","dump_site_id"."_auto"), ['allowed_classes' => false])) ? $ar : array(),
	);

	$arExpertBackupDefaultParams = array(
		'dump_base' => IntOption('dump_base', 1),
		'dump_base_skip_stat' => IntOption('dump_base_skip_stat', 0),
		'dump_base_skip_search' => IntOption('dump_base_skip_search', 0),
		'dump_base_skip_log' => IntOption('dump_base_skip_log', 0),

		'dump_file_public' => IntOption('dump_file_public', 1),
		'dump_file_kernel' => IntOption('dump_file_kernel', 1),
		'dump_do_clouds' => IntOption('dump_do_clouds', 1),
		'skip_mask' => IntOption('skip_mask', 0),
		'skip_mask_array' => is_array($ar = unserialize(COption::GetOptionString("main","skip_mask_array_auto"), ['allowed_classes' => false])) ? $ar : array(),
		'dump_max_file_size' => IntOption('dump_max_file_size', 0),
	);

	if (!is_array($arExpertBackupParams))
		$arExpertBackupParams = array();

	$arParams = array_merge($arExpertBackupDefaultParams, $arExpertBackupParams, $arParams);
}

$skip_mask_array = $arParams['skip_mask_array'];

if ($DB->type!= 'MYSQL')
	$arParams['dump_base'] = 0;

if (!$NS['step'])
{
	$NS = array('step' => 1, 'step_cnt' => 0);
	$NS['START_TIME'] = START_TIME;
	if ($public)
	{
		$dump_bucket_id = 0;
	}
	else
	{
		$NS['dump_encrypt_key'] = CPasswordStorage::Get('dump_temporary_cache');
		$dump_bucket_id = IntOption('dump_bucket_id');
	}

	if ($dump_bucket_id == -1)
	{
		if (!$bBitrixCloud || !$NS['dump_encrypt_key'])
		{
			$dump_bucket_id = 0;
			ShowBackupStatus('BitrixCloud is not available');
		}
	}
	$NS['BUCKET_ID'] = $dump_bucket_id;

	if ($dump_bucket_id == -1)
		$arc_name = DOCUMENT_ROOT.BX_ROOT."/backup/".date('Ymd_His_').rand(11111111,99999999);
	elseif(($arc_name = $argv[1]) && !is_dir($arc_name))
		$arc_name =  str_replace(array('.tar','.gz','.enc'),'',$arc_name);
	else
	{
		$prefix = str_replace('/', '', COption::GetOptionString("main", "server_name", ""));
		$arc_name = CBackup::GetArcName(preg_match('#^[a-z0-9\.\-]+$#i', $prefix) ? substr($prefix, 0, 20).'_' : '');
	}

	$NS['arc_name'] = $arc_name.($NS['dump_encrypt_key'] ? ".enc" : ".tar").($arParams['dump_use_compression'] ? ".gz" : '');
	$NS['dump_name'] = $arc_name.'.sql';

	if (!empty($arParams['dump_site_id']))
	{
		$NS['site_path_list'] = array();
		$res = CSite::GetList('sort', 'asc', array('ACTIVE'=>'Y'));
		while($f = $res->Fetch())
		{
			$root = rtrim(str_replace('\\','/',$f['ABS_DOC_ROOT']),'/');
			if (is_dir($root) && in_array($f['ID'], $arParams['dump_site_id']))
				$NS['site_path_list'][$f['ID']] = $root;
		}
	}
	else
		$NS['site_path_list'] = array('s1' => DOCUMENT_ROOT);

	if (!$public)
	{
		foreach(GetModuleEvents("main", "OnAutoBackupStart", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($NS));
	}

	ShowBackupStatus('Backup started to file: '.$NS['arc_name']);
	if ($arParams['dump_base'])
		$NS['step_cnt'] = 2;
	if ($arParams['dump_do_clouds'] && ($arDumpClouds = CBackup::GetBucketList()))
		$NS['step_cnt']++;
	if (($arParams['dump_file_public'] || $arParams['dump_file_kernel']))
		$NS['step_cnt']++;
	if ($arParams['dump_integrity_check'])
		$NS['step_cnt']++;
	if ($NS['BUCKET_ID'])
		$NS['step_cnt']++;
	if ($arParams['dump_delete_old'] > 1)
		$NS['step_cnt']++;
	$NS['step_finished'] = 0;
}

$NS['step_done'] = 0;
$after_file = str_replace('.sql','_after_connect.sql',preg_replace('#\.[0-9]+$#', '', $NS['dump_name']));

if ($NS['step'] <= 2)
{
	// dump database
	if ($arParams['dump_base'])
	{
		if ($NS['step'] == 1)
		{
			ShowBackupStatus('Dumping database');
			if (!CBackup::MakeDump($NS['dump_name'], $NS['dump_state']))
				RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS'), 100, $NS['dump_name']);

			$TotalTables = $NS['dump_state']['TableCount'];
			$FinishedTables = $TotalTables - count($NS['dump_state']['TABLES']);
			$NS['step_done'] = $FinishedTables / $TotalTables;

			if (!$NS['dump_state']['end'])
				CheckPoint();

			$rs = $DB->Query('SHOW VARIABLES LIKE "character_set_results"');
			if (($f = $rs->Fetch()) && array_key_exists ('Value', $f))
				file_put_contents($after_file, "SET NAMES '".$f['Value']."';\n");

			$rs = $DB->Query('SHOW VARIABLES LIKE "collation_database"');
			if (($f = $rs->Fetch()) && array_key_exists ('Value', $f))
				file_put_contents($after_file, "ALTER DATABASE `<DATABASE>` COLLATE ".$f['Value'].";\n",8);

			$NS['step'] = 2;
			$NS['step_finished']++;
			clearstatcache();

			$next_part = $NS['dump_name'];
			$NS['dump_size'] = filesize($next_part);
			while(file_exists($next_part = CBackup::getNextName($next_part)))
				$NS['dump_size'] += filesize($next_part);
		}

		ShowBackupStatus('Archiving database dump');
		$tar = new CTar;
		$tar->EncryptKey = $NS['dump_encrypt_key'];
		$tar->ArchiveSizeLimit = $arParams['dump_archive_size_limit'];
		$tar->gzip = $arParams['dump_use_compression'];
		$tar->path = DOCUMENT_ROOT;
		$tar->ReadBlockCurrent = intval($NS['ReadBlockCurrent']);
		$tar->ReadFileSize = intval($NS['ReadFileSize']);

		if (!$tar->openWrite($NS["arc_name"]))
			RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS'), 200, $NS['arc_name']);

		if (!$tar->ReadBlockCurrent)
		{
			if (file_exists($f = DOCUMENT_ROOT.BX_ROOT.'/.config.php')) // legacy SaaS support
				$tar->addFile($f);

			if (file_exists($after_file))
			{
				$tar->addFile($after_file);
				unlink($after_file);
			}
		}

		$Block = $tar->Block;
		while(haveTime())
		{
			$r = $tar->addFile($NS['dump_name']);
			if ($r === false)
				RaiseErrorAndDie(implode('<br>',$tar->err), 210, $NS['arc_name'], true);
			if ($tar->ReadBlockCurrent == 0)
			{
				unlink($NS["dump_name"]);
				if (file_exists($next_part = CBackup::getNextName($NS['dump_name'])))
				{
					$NS['dump_name'] = $next_part;
				}
				else // finish
				{
					$NS['arc_size'] = 0;
					$name = $NS["arc_name"];
					while(file_exists($name))
					{
						$size = filesize($name);
						$NS['arc_size'] += $size;
						$name = $tar->getNextName($name);
					}
					$NS['step_finished']++;

					break;
				}
			}
		}
		$tar->close();

		$NS["data_size"] += 512 * ($tar->Block - $Block);
		$NS["ReadBlockCurrent"] = $tar->ReadBlockCurrent;
		$NS["ReadFileSize"] = $tar->ReadFileSize;
		$NS['step_done'] = $NS['data_size'] / $NS['dump_size'];

		CheckPoint();
	}
	$NS['step'] = 3;
}

if ($NS['step'] == 3)
{
	$NS['step_done'] = 0;
	// Download cloud files
	if ($arParams['dump_do_clouds'] && ($arDumpClouds = CBackup::GetBucketList()))
	{
		ShowBackupStatus('Downloading cloud files');
		foreach($arDumpClouds as $arBucket)
		{
			$id = $arBucket['ID'];
			if ($NS['bucket_finished_'.$id])
				continue;

			$obCloud = new CloudDownload($arBucket['ID']);
			$obCloud->last_bucket_path = $NS['last_bucket_path'];
			if ($res = $obCloud->Scan(''))
			{
				$NS['bucket_finished_'.$id] = true;
			}
			else
			{
				$NS['last_bucket_path'] = $obCloud->path;
				break;
			}
		}

		CheckPoint();
		$NS['step_finished']++;
	}
	$NS['step'] = 4;
}

$DB->Disconnect();

if ($NS['step'] == 4)
{
	// Tar files
	$NS['step_done'] = 0;
	if ($arParams['dump_file_public'] || $arParams['dump_file_kernel'])
	{
		ShowBackupStatus('Archiving files');

		$DirScan = new CDirRealScan;
		$DirScan->startPath = $NS['startPath'];

		$tar = new CTar;
		$tar->EncryptKey = $NS['dump_encrypt_key'];
		$tar->ArchiveSizeLimit = $arParams['dump_archive_size_limit'];
		$tar->gzip = $arParams['dump_use_compression'];
		$tar->ReadBlockCurrent = intval($NS['ReadBlockCurrent']);
		$tar->ReadFileSize = intval($NS['ReadFileSize']);

		foreach($NS['site_path_list'] as $SITE_ID => $DOCUMENT_ROOT_SITE)
		{
			$tar->path = $DOCUMENT_ROOT_SITE;

			if (!$tar->openWrite($NS["arc_name"]))
				RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS'), 400, $NS['arc_name'], true);

			CBackup::$DOCUMENT_ROOT_SITE = $DOCUMENT_ROOT_SITE;
			CBackup::$REAL_DOCUMENT_ROOT_SITE = realpath($DOCUMENT_ROOT_SITE);

			if ($NS['multisite'])
			{
				$tar->prefix = 'bitrix/backup/sites/'.$SITE_ID.'/';
				$DirScan->arSkip[rtrim($DOCUMENT_ROOT_SITE, '/').'/bitrix'] = true;
				$DirScan->arSkip[rtrim($DOCUMENT_ROOT_SITE, '/').'/upload'] = true;
			}


			$Block = $tar->Block;

			$r = $DirScan->Scan($DOCUMENT_ROOT_SITE);
			$tar->close();

			if (!isset($NS["data_size"]))
			{
				$NS["data_size"] = 0;
			}
			$NS["data_size"] += 512 * ($tar->Block - $Block);

			if ($r === false)
				RaiseErrorAndDie(implode('<br>',array_merge($tar->err,$DirScan->err)), 410, $tar->file, true);

			$NS["ReadBlockCurrent"] = $tar->ReadBlockCurrent;
			$NS["ReadFileSize"] = $tar->ReadFileSize;
			$NS["startPath"] = $DirScan->nextPath;

			if (!isset($NS["cnt"]))
			{
				$NS["cnt"] = 0;
			}
			$NS["cnt"] += $DirScan->FileCount;

			$last_files_count = IntOption('last_files_count');
			if (!$last_files_count)
				$last_files_count = 200000;
			$NS['step_done'] = $NS['cnt'] / $last_files_count;
			if ($NS['step_done'] > 1)
				$NS['step_done'] = 1;

			if ($r !== 'BREAK') // finish scan
			{
				array_shift($NS['site_path_list']);
				$NS['multisite'] = true;
				unset($NS['startPath']);
			}

			CheckPoint();
		}

		$NS['arc_size'] = 0;
		$name = $NS["arc_name"];
		$tar = new CTar();
		while(file_exists($name))
		{
			$size = filesize($name);
			$NS['arc_size'] += $size;
			$name = $tar->getNextName($name);
		}
		DeleteDirFilesEx(BX_ROOT.'/backup/clouds');
		if ($arParams['dump_file_public'] && $arParams['dump_file_kernel'])
			COption::SetOptionInt("main", "last_files_count", $NS['cnt']);
		$NS['step_finished']++;
	}
	$NS['step'] = 5;
}

if ($NS['step'] == 5)
{
	// Integrity check
	$NS['step_done'] = 0;
	if ($arParams['dump_integrity_check'])
	{
		ShowBackupStatus('Checking archive integrity');
		$tar = new CTarCheck;
		$tar->EncryptKey = $NS['dump_encrypt_key'];

		if (!$tar->openRead($NS["arc_name"]))
			RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS_READ').'<br>'.implode('<br>',$tar->err), 510, $NS['arc_name']);
		else
		{
			if(($Block = intval($NS['Block'] ?? 0)) && !$tar->SkipTo($Block))
				RaiseErrorAndDie(implode('<br>',$tar->err), 520, $tar->file, true);
			while(($r = $tar->extractFile()) && haveTime());
			$NS["Block"] = $tar->Block;
			$NS['step_done'] = $NS['Block'] * 512 / $NS['data_size'];
			if ($r === false)
				RaiseErrorAndDie(implode('<br>',$tar->err), 530, $tar->file, true);
		}
		$tar->close();

		CheckPoint();
		$NS['step_finished']++;
	}
	$NS['step'] = 6;
}

$DB->DoConnect();

if ($NS['step'] == 6)
{
	// Send to the cloud
	$NS['step_done'] = 0;
	if ($NS['BUCKET_ID'])
	{
		ShowBackupStatus('Sending backup to the cloud');
		if (!CModule::IncludeModule('clouds'))
			RaiseErrorAndDie(GetMessage("MAIN_DUMP_NO_CLOUDS_MODULE"), 600, $NS['arc_name']);

		$tar = new CTar();
		while(CheckPoint())
		{
			$file_size = filesize($NS["arc_name"]);
			$file_name = $NS['BUCKET_ID'] == -1? basename($NS['arc_name']) : substr($NS['arc_name'], strlen(DOCUMENT_ROOT));
			$obUpload = new CCloudStorageUpload($file_name);

			if ($NS['BUCKET_ID'] == -1)
			{
				if (!$bBitrixCloud)
					RaiseErrorAndDie(getMessage('DUMP_BXCLOUD_NA'), 610);

				$obBucket = null;
				if (!$NS['obBucket'])
				{
					try
					{
						$backup = CBitrixCloudBackup::getInstance();
						$q = $backup->getQuota();
						if ($q && $NS['arc_size'] > $q)
							RaiseErrorAndDie(GetMessage('DUMP_ERR_BIG_BACKUP', array('#ARC_SIZE#' => $NS['arc_size'], '#QUOTA#' => $q)), 620);

						$obBucket = $backup->getBucketToWriteFile(CTar::getCheckword($NS['dump_encrypt_key']), basename($NS['arc_name']));
						$NS['obBucket'] = serialize($obBucket);
					}
					catch (Exception $e)
					{
						unset($NS['obBucket']);
						RaiseErrorAndDie($e->getMessage(), 630);
					}
				}
				else
				{
					$obBucket = unserialize(
						$NS['obBucket'],
						['allowed_classes' => ['CBitrixCloudBackupBucket']]
					);
				}

				$obBucket->Init();
				$obBucket->GetService()->setPublic(false);

				$bucket_id = $obBucket;
			}
			else
			{
				$obBucket = null;
				$bucket_id = $NS['BUCKET_ID'];
			}

			if (!$obUpload->isStarted())
			{
				if (is_object($obBucket))
					$obBucket->setCheckWordHeader();

				if (!$obUpload->Start($bucket_id, $file_size))
				{
					if ($e = $APPLICATION->GetException())
						$strError = $e->GetString();
					else
						$strError = GetMessage('MAIN_DUMP_INT_CLOUD_ERR');
					RaiseErrorAndDie($strError, 640, $NS['arc_name']);
				}

				if (is_object($obBucket))
					$obBucket->unsetCheckWordHeader();
			}

			if (!$fp = fopen($NS['arc_name'],'rb'))
				RaiseErrorAndDie(GetMessage("MAIN_DUMP_ERR_OPEN_FILE").' '.$NS['arc_name'], 650, $NS['arc_name']);

			fseek($fp, $obUpload->getPos());
			while($obUpload->getPos() < $file_size && haveTime())
			{
				$part = fread($fp, $obUpload->getPartSize());
				$fails = 0;
				$res = false;
				while($obUpload->hasRetries())
				{
					if($res = $obUpload->Next($part, $obBucket))
						break;
					elseif (++$fails >= 10)
					{
						$e = $APPLICATION->GetException();
						$strError = $e ? '. ' . $e->GetString() : '';
						RaiseErrorAndDie('Internal Error: could not init upload for ' . $fails . ' times' . $strError, 660, $NS['arc_name']);
					}
				}
				$NS['step_done'] = $obUpload->getPos() / $NS['arc_size'];

				if (!$res)
				{
					$obUpload->Delete();
					$e = $APPLICATION->GetException();
					$strError = $e ? '. ' . $e->GetString() : '';
					RaiseErrorAndDie(GetMessage('MAIN_DUMP_ERR_FILE_SEND') . ' ' . basename($NS['arc_name']) . $strError, 670, $NS['arc_name']);
				}
			}
			fclose($fp);

			CheckPoint();

			if($obUpload->Finish($obBucket))
			{
				if ($NS['BUCKET_ID'] != -1)
				{
					$oBucket = new CCloudStorageBucket($NS['BUCKET_ID']);
					$oBucket->IncFileCounter($file_size);
				}

				if (file_exists($arc_name = $tar->getNextName($NS['arc_name'])))
				{
					unset($NS['obBucket']);
					$NS['arc_name'] = $arc_name;
				}
				else
				{
					if ($bBitrixCloud)
					{
						$ob = new CBitrixCloudBackup;
						$ob->clearOptions();
					}

					if ($arParams['dump_delete_old'] == 1)
					{
						$name = CTar::getFirstName($NS['arc_name']);
						while(file_exists($name))
						{
							$size = filesize($name);
							if (unlink($name) && COption::GetOptionInt('main', 'disk_space', 0) > 0)
								CDiskQuota::updateDiskQuota("file", $size, "del");
							$name = $tar->getNextName($name);
						}
					}
					break;
				}
			}
			else
			{
				$obUpload->Delete();
				$e = $APPLICATION->GetException();
				$strError = $e ? '. ' . $e->GetString() : '';
				RaiseErrorAndDie(GetMessage('MAIN_DUMP_ERR_FILE_SEND') . basename($NS['arc_name']) . $strError, 680, $NS['arc_name']);
			}
		}
		$NS['step_finished']++;
	}
	$NS['step'] = 7;
}

if ($NS['step'] == 7)
{
	// Delete old backups
	$NS['step_done'] = 0;
	if ($arParams['dump_delete_old'] > 1)
	{
		ShowBackupStatus('Deleting old backups');
		$arFiles = array();
		$arParts = array();

		$TotalSize = $NS['arc_size'];

		if (is_dir($p = DOCUMENT_ROOT.BX_ROOT.'/backup'))
		{
			if ($dir = opendir($p))
			{
				$arc_name = CTar::getFirstName(basename($NS['arc_name']));
				while(($item = readdir($dir)) !== false)
				{
					$f = $p.'/'.$item;
					if (!is_file($f))
						continue;

					if (!preg_match('#\.(sql|tar|gz|enc|[0-9]+)$#', $item))
						continue;

					$name = CTar::getFirstName($item);
					if ($name == $arc_name)
						continue;

					$s = filesize($f);
					$m = filemtime($f);

					$arFiles[$name] = $m;
					$arParts[$name][] = $item;
					$TotalSize += $s;
				}
				closedir($dir);
			}
		}
		asort($arFiles);
		$cnt = count($arFiles) + 1;

		foreach($arFiles as $name => $m)
		{
			switch ($arParams['dump_delete_old'])
			{
				case 2: // time
					if ($m >= time() - 86400 * $arParams['dump_old_time'])
						break 2;
				break;
				case 4: // cnt
					if ($cnt <= $arParams['dump_old_cnt'])
						break 2;
				break;
				case 8: // size
					if ($TotalSize / 1024 / 1024 / 1024 <= $arParams['dump_old_size'])
						break 2;
				break;
				default:
				break;
			}

			$cnt--;
			foreach($arParts[$name] as $item)
			{
				$f = $p.'/'.$item;
				$size = filesize($f);
				$TotalSize -= $size;
				if (!unlink($f))
					RaiseErrorAndDie('Could not delete file: '.$f, 700, $NS['arc_name']);
				if (COption::GetOptionInt('main', 'disk_space', 0) > 0)
					CDiskQuota::updateDiskQuota("file", $size, "del");
			}
		}
		$NS['step_finished']++;
	}
	$NS['step'] = 8;
}

if (COption::GetOptionInt('main', 'disk_space', 0) > 0)
{
	$name = $NS["arc_name"];
	$tar = new CTar();
	while(file_exists($name))
	{
		$size = filesize($name);
		CDiskQuota::updateDiskQuota("file", $size, "add");
		$name = $tar->getNextName($name);
	}
}

$info = "Finished.\n\nData size: ".round($NS['data_size']/1024/1024, 2)." M\nArchive size: ".round($NS['arc_size']/1024/1024, 2)." M\nTime: ".round(time() - $NS['START_TIME'], 2)." sec\n";
ShowBackupStatus($info);
CEventLog::Add(array(
	"SEVERITY" => "WARNING",
	"AUDIT_TYPE_ID" => "BACKUP_SUCCESS",
	"MODULE_ID" => "main",
	"ITEM_ID" => $NS['arc_name'],
	"DESCRIPTION" => $info,
));

foreach(GetModuleEvents("main", "OnAutoBackupSuccess", true) as $arEvent)
	ExecuteModuleEventEx($arEvent, array($NS));

$NS = array();
if (defined('LOCK_FILE'))
	unlink(LOCK_FILE) || RaiseErrorAndDie('Can\'t delete file: '.LOCK_FILE, 1000);
if (!CLI)
	echo 'FINISH';
COption::SetOptionInt('main', 'last_backup_end_time', time());
##########################################
########################### Functions ####
function IntOption($name, $def = 0)
{
	global $arParams;
	if (isset($arParams[$name]))
		return $arParams[$name];

	static $CACHE;
	$name .= '_auto';

	if (!isset($CACHE[$name]))
		$CACHE[$name] = COption::GetOptionInt("main", $name, $def);
	return $CACHE[$name];
}

function ShowBackupStatus($str)
{
	if (!CLI && !$_REQUEST['show_status'])
		return;
	global $NS;
	echo round(microtime(1)-$NS['START_TIME'], 2).' sec	'.$str."\n";
}

function haveTime()
{
	static $timeout;
	if (!$timeout)
	{
		$timeout = IntOption('dump_max_exec_time', 30);
		if ($timeout < 5)
			$timeout = 5;
	}
	if (!CLI && time() - START_TIME > $timeout)
		return false;
	return true;
}

function RaiseErrorAndDie($strError, $errCode = 0, $ITEM_ID = '', $delete = false)
{
	global $DB, $NS;

	if ($delete)
	{
		$arc_name = CTar::getFirstName(basename($NS['arc_name']));

		if ($dir = opendir($path = DOCUMENT_ROOT.'/bitrix/backup'))
		{
			while($item = readdir($dir))
			{
				if (is_dir($path.'/'.$item))
					continue;
				if (CTar::getFirstName($item) == $arc_name)
					$delete = unlink($path.'/'.$item) && $delete;
			}
			closedir($dir);
		}
		else
			$delete = false;

		$strError .= "\n".($delete ? 'The backup was incorrect and it was deleted' : 'The backup was incorrect but there was an error deleting it');
	}

	$NS0 = $NS;
	$NS = array();
	session_write_close();

	if (CLI)
		echo 'Error ['.$errCode.']: '.str_replace('<br>',"\n",$strError)."\n";
	else
	{
		echo "ERROR_".$errCode."\n".htmlspecialcharsbx($strError)."\n";
	}

	if (is_object($DB))
	{
		$DB->DoConnect();

		CEventLog::Add(array(
			"SEVERITY" => "WARNING",
			"AUDIT_TYPE_ID" => "BACKUP_ERROR",
			"MODULE_ID" => "main",
			"ITEM_ID" => $ITEM_ID,
			"DESCRIPTION" => "[".$errCode."] ".$strError,
		));

		foreach(GetModuleEvents("main", "OnAutoBackupError", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(array_merge($NS0, array('ERROR' => $strError, 'ERROR_CODE' => $errCode, 'ITEM_ID' => $ITEM_ID))));

		$link = '/bitrix/admin/event_log.php?set_filter=Y&find_type=audit_type_id&find_audit_type[]=BACKUP_ERROR';
		$ar = Array(
			"MESSAGE" => 'The last automatic backup has failed. Please check your <a href="'.$link.'">system log<a>',
			"TAG" => "BACKUP",
			"MODULE_ID" => "MAIN",
			'NOTIFY_TYPE' => CAdminNotify::TYPE_ERROR,
			'ENABLE_CLOSE' => 'Y'
		);
		foreach(array('ru', 'ua', 'en', 'de') as $lang)
		{
			\Bitrix\Main\Localization\Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/dump.php', $lang);
			$ar["LANG"][$lang] = \Bitrix\Main\Localization\Loc::getMessage('DUMP_ERR_AUTO', array('#MESSAGE#' => $strError, '#LINK#' => $link), $lang);
		}
		CAdminNotify::Add($ar);
	}
	die();
}

function CheckPoint()
{
	if (haveTime())
		return true;

	global $NS;
	$NS['WORK_TIME'] = microtime(1) - START_TIME;
	$NS['TIMESTAMP'] = time();

	session_write_close();
	echo "NEXT\n".
	GetProgressPercent($NS);
	exit(0);
}

function GetProgressPercent($NS)
{
	if ($NS['step_done'] > 1)
		$NS['step_done'] = 1;
	$res = round(100*($NS['step_finished']+$NS['step_done'])/$NS['step_cnt']);
	if ($res > 99)
		$res = 99;
	return $res;
}
