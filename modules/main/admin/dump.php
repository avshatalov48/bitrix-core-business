<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

use Bitrix\Main\Security\Random;

define('NO_AGENT_CHECK', true);
define("STATISTIC_SKIP_ACTIVITY_CHECK", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("HELP_FILE", "utilities/dump.php");

if(!$USER->CanDoOperation('edit_php'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!defined("START_EXEC_TIME"))
	define("START_EXEC_TIME", microtime(true));

IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/backup.php");
$strBXError = '';
$bGzip = function_exists('gzcompress');
$encrypt = function_exists('openssl_encrypt');
$bHash = function_exists('hash');
$bBitrixCloud = $encrypt && $bHash;
if (!CModule::IncludeModule('bitrixcloud'))
{
	$bBitrixCloud = false;
	$strBXError = GetMessage('ERR_NO_BX_CLOUD');
}
elseif (!CModule::IncludeModule('clouds'))
{
	$bBitrixCloud = false;
	$strBXError = GetMessage('ERR_NO_CLOUDS');
}


if($bBitrixCloud)
{
	$backup = CBitrixCloudBackup::getInstance();
	$arFiles = $backup->listFiles();
	$backup->saveToOptions();
}

define('DOCUMENT_ROOT', rtrim(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),'/'));

// define('DUMP_DEBUG_MODE', true);
// xdebug_start_trace();

$arAllBucket = CBackup::GetBucketList();
$status_title = "";

if (isset($_REQUEST['ajax_mode']) && $_REQUEST['ajax_mode'] == 'Y')
{
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_table_size')
	{
		?>
		<script>
			BX('db_size').innerHTML = "(<?=CFile::FormatSize(getTableSize(""))?>)";
			BX('db_stat_size').innerHTML = "(<?=CFile::FormatSize(getTableSize("^b_stat"))?>)";
			BX('db_search_size').innerHTML = "(<?=CFile::FormatSize(getTableSize("^b_search"))?>)";
			BX('db_event_size').innerHTML = "(<?=CFile::FormatSize(getTableSize("^b_event_log$"))?>)";
			EndDump();
		</script>
		<?
		die();
	}
}
elseif(isset($_REQUEST['process']) && $_REQUEST['process'] == "Y")
{
	if (!check_bitrix_sessid())
		RaiseErrorAndDie(GetMessage("DUMP_MAIN_SESISON_ERROR"));

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	$NS =& \Bitrix\Main\Application::getInstance()->getSession()['BX_DUMP_STATE'];
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'start')
	{
		define('NO_TIME', true);

		$bFull = isset($_REQUEST['dump_all']) && $_REQUEST['dump_all'] == 'Y';

		if(!file_exists(DOCUMENT_ROOT.BX_ROOT."/backup"))
			mkdir(DOCUMENT_ROOT.BX_ROOT."/backup", BX_DIR_PERMISSIONS);

		if(!file_exists(DOCUMENT_ROOT.BX_ROOT."/backup/index.php"))
		{
			$f = fopen(DOCUMENT_ROOT.BX_ROOT."/backup/index.php","w");
			fwrite($f,"<head><meta http-equiv=\"REFRESH\" content=\"0;URL=/bitrix/admin/index.php\"></head>");
			fclose($f);
		}

		if(!is_dir(DOCUMENT_ROOT.BX_ROOT."/backup") || !is_writable(DOCUMENT_ROOT.BX_ROOT."/backup"))
			RaiseErrorAndDie(GetMessage("MAIN_DUMP_FOLDER_ERR",array('#FOLDER#' => DOCUMENT_ROOT.BX_ROOT.'/backup')));

		DeleteDirFilesEx(BX_ROOT.'/backup/clouds');

		$NS = Array();
		$NS['finished_steps'] = 0;
		$NS['dump_state'] = '';
		$NS['BUCKET_ID'] = intval($_REQUEST['dump_bucket_id'] ?? 0);
		COption::SetOptionInt("main", "dump_bucket_id", $NS['BUCKET_ID']);

		if ($encrypt && !empty($_REQUEST['dump_encrypt_key']))
		{
			$NS['dump_encrypt_key'] =  $_REQUEST['dump_encrypt_key'];
			COption::SetOptionInt("main", "dump_encrypt", 1);
		}
		else
			COption::SetOptionInt("main", "dump_encrypt", 0);

		if ($NS['BUCKET_ID'] == -1 && !$NS['dump_encrypt_key'])
			RaiseErrorAndDie(GetMessage('MAIN_DUMP_BXCLOUD_ENC'));


		$bUseCompression = $bGzip && (!isset($_REQUEST['dump_disable_gzip']) || $_REQUEST['dump_disable_gzip'] != 'Y' || $bFull);
		COption::SetOptionInt("main", "dump_use_compression", $bUseCompression);

		if ($bFull)
		{
			$NS['total_steps'] = 4; // dump, tar dump, tar files, integrity

			COption::SetOptionInt("main", "dump_max_exec_time", 20);
			COption::SetOptionInt("main", "dump_max_exec_time_sleep", 1);
			COption::SetOptionInt("main", "dump_archive_size_limit", 100 * 1024 * 1024);
			COption::SetOptionInt("main", "dump_integrity_check", 1);
			COption::SetOptionInt("main", "dump_max_file_size", 0);

			COption::SetOptionInt("main", "dump_file_public", 1);
			COption::SetOptionInt("main", "dump_file_kernel", 1);
			COption::SetOptionInt("main", "dump_base", $DB->type == 'MYSQL' ? 1 : 0);
			COption::SetOptionInt("main", "dump_base_skip_stat", 0);
			COption::SetOptionInt("main", "dump_base_skip_search", 0);
			COption::SetOptionInt("main", "dump_base_skip_log", 0);

			if ($arAllBucket)
			{
				$bDumpCloud = 1;
				$NS['total_steps']++;
				COption::SetOptionInt("main", "dump_do_clouds", 1);
				foreach($arAllBucket as $arBucket)
					COption::SetOptionInt('main', 'dump_cloud_'.$arBucket['ID'], 1);
			}

			COption::SetOptionInt("main", "skip_mask", 0);
		}
		else
		{
			COption::SetOptionInt("main", "dump_max_exec_time", max(intval($_REQUEST['dump_max_exec_time'] ?? 0), 5));
			COption::SetOptionInt("main", "dump_max_exec_time_sleep", $_REQUEST['dump_max_exec_time_sleep'] ?? 0);
			$dump_archive_size_limit = intval($_REQUEST['dump_archive_size_limit'] ?? 0);
			if ($dump_archive_size_limit > 2047 || $dump_archive_size_limit <= 10)
				$dump_archive_size_limit = 100;
			COption::SetOptionInt("main", "dump_archive_size_limit", $dump_archive_size_limit * 1024 * 1024);
			COption::SetOptionInt("main", "dump_max_file_size", $_REQUEST['max_file_size'] ?? 0);

			$NS['total_steps'] = 0;
			if ($r = (isset($_REQUEST['dump_file_public']) && $_REQUEST['dump_file_public'] == 'Y'))
				$NS['total_steps'] = 1;
			COption::SetOptionInt("main", "dump_file_public", $r);

			if ($r = (isset($_REQUEST['dump_file_kernel']) && $_REQUEST['dump_file_kernel'] == 'Y'))
				$NS['total_steps'] = 1;
			COption::SetOptionInt("main", "dump_file_kernel", $r);

			if ($r = $DB->type == 'MYSQL' ? (isset($_REQUEST['dump_base']) && $_REQUEST['dump_base'] == 'Y') : 0)
				$NS['total_steps'] += 2;
			COption::SetOptionInt("main", "dump_base", $r);
			COption::SetOptionInt("main", "dump_base_skip_stat", isset($_REQUEST['dump_base_skip_stat']) && $_REQUEST['dump_base_skip_stat'] == 'Y');
			COption::SetOptionInt("main", "dump_base_skip_search", isset($_REQUEST['dump_base_skip_search']) && $_REQUEST['dump_base_skip_search'] == 'Y');
			COption::SetOptionInt("main", "dump_base_skip_log", isset($_REQUEST['dump_base_skip_log']) && $_REQUEST['dump_base_skip_log'] == 'Y');

			if ($r = (isset($_REQUEST['dump_integrity_check']) && $_REQUEST['dump_integrity_check'] == 'Y'))
				$NS['total_steps']++;
			COption::SetOptionInt("main", "dump_integrity_check", $r);

			$bDumpCloud = false;
			if ($arAllBucket)
			{
				foreach($arAllBucket as $arBucket)
				{
					if ($res = (isset($_REQUEST['dump_cloud'][$arBucket['ID']]) && $_REQUEST['dump_cloud'][$arBucket['ID']] == 'Y'))
						$bDumpCloud = true;
					COption::SetOptionInt('main', 'dump_cloud_'.$arBucket['ID'], $res);
				}
				if ($bDumpCloud)
					$NS['total_steps']++;
			}
			COption::SetOptionInt("main", "dump_do_clouds", $bDumpCloud);

			$skip_mask = isset($_REQUEST['skip_mask']) && $_REQUEST['skip_mask'] == 'Y';
			COption::SetOptionInt("main", "skip_mask", $skip_mask);

			$skip_mask_array = array();
			if ($skip_mask && isset($_REQUEST['arMask']) && is_array($_REQUEST['arMask']))
			{
				$arMask = array_unique($_REQUEST['arMask']);
				foreach($arMask as $mask)
					if (trim($mask))
					{
						$mask = rtrim(str_replace('\\','/',trim($mask)),'/');
						$skip_mask_array[] = $mask;
					}
				COption::SetOptionString("main", "skip_mask_array", serialize($skip_mask_array));
			}
		}

		$NS["step"] = 1;

		if ($NS['BUCKET_ID']) // send to the [bitrix]cloud
			$NS['total_steps']++;

		$NS['dump_site_id'] = $_REQUEST['dump_site_id'] ?? '';
		if (!is_array($NS['dump_site_id']))
			$NS['dump_site_id'] = array();
		COption::SetOptionString("main", "dump_site_id", serialize($NS['dump_site_id']));

		if ($NS['BUCKET_ID'] == -1) // Bitrixcloud
		{
			$name = DOCUMENT_ROOT . BX_ROOT . "/backup/" . date('Ymd_His_') . Random::getStringByAlphabet(16, Random::ALPHABET_NUM);
			$NS['arc_name'] = $name.'.enc'.($bUseCompression ? ".gz" : '');
			$NS['dump_name'] = $name.'.sql';
		}
		else
		{
			$prefix = '';
			if (count($NS['dump_site_id']) == 1)
			{
				$rs = CSite::GetList('sort', 'asc', array('ID' => $NS['dump_site_id'][0], 'ACTIVE' => 'Y'));
				if ($f = $rs->Fetch())
					$prefix = str_replace('/', '', $f['SERVER_NAME']);
			}
			else
				$prefix = str_replace('/', '', COption::GetOptionString("main", "server_name", ""));

			$arc_name = CBackup::GetArcName(preg_match('#^[a-z0-9.\-]+$#i', $prefix) ? substr($prefix, 0, 20).'_' : '');
			$NS['dump_name'] = $arc_name.".sql";
			$NS['arc_name'] = $arc_name.(!empty($NS['dump_encrypt_key']) ? ".enc" : ".tar").($bUseCompression ? ".gz" : '');
		}
	}
	elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'cloud_send')
	{
		define('NO_TIME', true);
		$NS = Array();
		$NS['finished_steps'] = 0;
		$NS['total_steps'] = 1;
		$NS['cloud_send'] = 1;
		$NS['dump_encrypt_key'] = $_REQUEST['dump_encrypt_key'] ?? '';
		$NS['arc_name'] = $name = DOCUMENT_ROOT.BX_ROOT.'/backup/'.str_replace(array('..','/','\\'),'',$_REQUEST['f_id'] ?? '');
		$NS['arc_size'] = filesize($NS['arc_name']);
		$NS['BUCKET_ID'] = intval($_REQUEST['dump_bucket_id'] ?? 0);
		$tar = new CTar;
		while(file_exists($name = $tar->getNextName($name)))
			$NS['arc_size'] += filesize($name);
		$NS['step'] = 6;
	}
	elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'check_archive')
	{
		define('NO_TIME', true);
		$NS = Array();
		$NS['finished_steps'] = 0;
		$NS['total_steps'] = 1;
		$NS['arc_name'] = $name = DOCUMENT_ROOT.BX_ROOT.'/backup/'.str_replace(array('..','/','\\'),'',$_REQUEST['f_id'] ?? '');
		$NS['step'] = 5;
		$NS['dump_encrypt_key'] = $_REQUEST['dump_encrypt_key'] ?? '';
		$NS['check_archive'] = true;
		$tar = new CTar;
		$NS['data_size'] = $tar->getDataSize($name);
	}
	else
	{
		$ar = unserialize(COption::GetOptionString("main","skip_mask_array"), ['allowed_classes' => false]);
		$skip_mask_array = is_array($ar) ? $ar : array();
	}

	$after_file = str_replace('.sql','_after_connect.sql',preg_replace('#\.[0-9]+$#', '', $NS['dump_name']));

	$FinishedTables = 0;
	// Step 1: Dump
	if($NS["step"] == 1)
	{
		$step_done = 0;
		if (IntOption('dump_base'))
		{
			if (!CBackup::MakeDump($NS['dump_name'], $NS['dump_state']))
			{
				RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS'));
			}

			$TotalTables = $NS['dump_state']['TableCount'];
			$FinishedTables = $TotalTables - count($NS['dump_state']['TABLES']);

			$status_title = GetMessage('DUMP_DB_CREATE');
			$status_details = GetMessage("MAIN_DUMP_TABLE_FINISH")." <b>".(intval($FinishedTables))."</b> ".GetMessage('MAIN_DUMP_FROM').' <b>'.$TotalTables.'</b>';
			$step_done = $FinishedTables / $TotalTables;

			if (!empty($NS['dump_state']['end']))
			{
				$rs = $DB->Query('SHOW VARIABLES LIKE "character_set_results"');
				if (($f = $rs->Fetch()) && array_key_exists ('Value', $f))
					file_put_contents($after_file, "SET NAMES '".$f['Value']."';\n");

				$rs = $DB->Query('SHOW VARIABLES LIKE "collation_database"');
				if (($f = $rs->Fetch()) && array_key_exists ('Value', $f))
					file_put_contents($after_file, "ALTER DATABASE `<DATABASE>` COLLATE ".$f['Value'].";\n",8);

				clearstatcache();
				$NS["step"]++;
				$NS['finished_steps']++;
			}
		}
		else
			$NS["step"]++;
	}

	// Step 2: pack dump
	if($NS["step"] == 2)
	{
		$step_done = 0;
		if (IntOption('dump_base'))
		{
			if (haveTime())
			{
				$tar = new CTar;
				$tar->EncryptKey = $NS['dump_encrypt_key'] ?? '';
				$tar->ArchiveSizeLimit = IntOption('dump_archive_size_limit');
				$tar->gzip = IntOption('dump_use_compression');
				$tar->path = DOCUMENT_ROOT;
				$tar->ReadBlockCurrent = intval($NS['ReadBlockCurrent'] ?? 0);

				if (!$tar->openWrite($NS["arc_name"]))
				{
					RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS'));
				}

				if (!$tar->ReadBlockCurrent && file_exists($f = DOCUMENT_ROOT.BX_ROOT.'/.config.php'))
					$tar->addFile($f);

				$Block = $tar->Block;
				$r = null;
				while(haveTime() && ($r = $tar->addFile($NS['dump_name'])) && $tar->ReadBlockCurrent > 0);

				if (!isset($NS["data_size"]))
				{
					$NS["data_size"] = 0;
				}

				$NS["data_size"] += 512 * ($tar->Block - $Block);

				if ($r === false)
					RaiseErrorAndDie(implode('<br>',$tar->err));

				$NS["ReadBlockCurrent"] = $tar->ReadBlockCurrent;

				if (empty($NS['dump_size']))
				{
					$next_part = $NS['dump_name'];
					$NS['dump_size'] = filesize($next_part);
					while(file_exists($next_part = CBackup::getNextName($next_part)))
						$NS['dump_size'] += filesize($next_part);
				}

				$status_title = GetMessage("MAIN_DUMP_DB_PROC");
				$status_details = GetMessage('CURRENT_POS').' <b>'.round(100 * $NS['data_size'] / $NS['dump_size']).'%</b>';
				$step_done = $NS['data_size'] / $NS['dump_size'];

				if($tar->ReadBlockCurrent == 0)
				{
					unlink($NS["dump_name"]);

					if (file_exists($next_part = CBackup::getNextName($NS['dump_name'])))
					{
						$NS['dump_name'] = $next_part;
					}
					else
					{
						if (file_exists($after_file))
						{
							$tar->addFile($after_file);
							unlink($after_file);
						}

						$NS['arc_size'] = 0;
						$name = $NS["arc_name"];
						while(file_exists($name))
						{
							$size = filesize($name);
							$NS['arc_size'] += $size;
							if (IntOption("disk_space") > 0)
								CDiskQuota::updateDiskQuota("file", $size, "add");
							$name = $tar->getNextName($name);
						}

						$NS["step"]++;
						$NS['finished_steps']++;
					}
				}
				$tar->close();
			}
		}
		else
			$NS["step"]++;
	}

	// Step 3: Download Cloud Files
	$arDumpClouds = false;
	if($NS["step"] == 3)
	{
		$step_done = 0;
		if ($arDumpClouds = CBackup::CheckDumpClouds())
		{
			if (haveTime())
			{
				$res = null;
				foreach($arDumpClouds as $id)
				{
					if (!empty($NS['bucket_finished_'.$id]))
						continue;

					$obCloud = new CloudDownload($id);
					$obCloud->last_bucket_path = $NS['last_bucket_path'] ?? '';
					if ($res = $obCloud->Scan(''))
					{
						$NS['bucket_finished_'.$id] = true;
					}
					else // partial
					{
						$NS['last_bucket_path'] = $obCloud->path;
						$NS['download_cnt'] += $obCloud->download_cnt;
						$NS['download_size'] += $obCloud->download_size;
						if ($c = count($obCloud->arSkipped))
							$NS['download_skipped'] += $c;
						break;
					}
				}

				$status_title = GetMessage("MAIN_DUMP_CLOUDS_DOWNLOAD");
				$status_details = GetMessage("MAIN_DUMP_FILES_DOWNLOADED").': <b>'.intval($NS["download_cnt"] ?? 0).'</b>';
//				if ($NS['download_skipped'])
//					$status_title .= GetMessage("MAIN_DUMP_DOWN_ERR_CNT").': <b>'.$NS['download_skipped'].'</b><br>';

				if ($res) // finish
				{
					$NS['step']++;
					$NS['finished_steps']++;
				}
			}
		}
		else
			$NS["step"]++;
	}

	// Step 4: Tar Files
	if($NS["step"] == 4)
	{
		$step_done = 0;
		if (CBackup::CheckDumpFiles() || CBackup::CheckDumpClouds())
		{
			if (haveTime())
			{
				$tar = new CTar;
				$DirScan = new CDirRealScan;

				$DOCUMENT_ROOT_SITE = DOCUMENT_ROOT;
				if (is_array($NS['dump_site_id']))
				{
					$SITE_ID = reset($NS['dump_site_id']);
					$rs = CSite::GetList('sort', 'asc', array('ID' => $SITE_ID, 'ACTIVE' => 'Y'));
					if ($f = $rs->Fetch())
					{
						$DOCUMENT_ROOT_SITE = rtrim(str_replace('\\','/',$f['ABS_DOC_ROOT']),'/');
						if (!empty($NS['multisite']))
						{
							$tar->prefix = 'bitrix/backup/sites/'.$f['LID'].'/';
							$DirScan->arSkip[$DOCUMENT_ROOT_SITE.'/bitrix'] = true;
							$DirScan->arSkip[$DOCUMENT_ROOT_SITE.'/upload'] = true;
							if (is_link($DOCUMENT_ROOT_SITE.'/local'))
							{
								// if it's a link, we need it only the first time
								$DirScan->arSkip[$DOCUMENT_ROOT_SITE.'/local'] = true;
							}
						}
					}
				}

				CBackup::$DOCUMENT_ROOT_SITE = $DOCUMENT_ROOT_SITE;
				CBackup::$REAL_DOCUMENT_ROOT_SITE = realpath($DOCUMENT_ROOT_SITE);

				$tar->EncryptKey = $NS['dump_encrypt_key'] ?? '';
				$tar->ArchiveSizeLimit = IntOption('dump_archive_size_limit');
				$tar->gzip = IntOption('dump_use_compression');
				$tar->path = $DOCUMENT_ROOT_SITE;
				$tar->ReadBlockCurrent = intval($NS['ReadBlockCurrent'] ?? 0);
				$tar->ReadFileSize = intval($NS['ReadFileSize'] ?? 0);

				if (!$tar->openWrite($NS["arc_name"] ?? ''))
				{
					RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS'));
				}

				$Block = $tar->Block;

				if (empty($NS['startPath']))
				{
					if (!IntOption('dump_base') && file_exists($f = DOCUMENT_ROOT.BX_ROOT.'/.config.php'))
						$tar->addFile($f);
				}
				else
					$DirScan->startPath = $NS['startPath'];

				$r = $DirScan->Scan($DOCUMENT_ROOT_SITE);

				if (!isset($NS["data_size"]))
				{
					$NS["data_size"] = 0;
				}
				$NS["data_size"] += 512 * ($tar->Block - $Block);

				$tar->close();

				if ($r === false)
					RaiseErrorAndDie(implode('<br>',array_merge($tar->err,$DirScan->err)));

				$NS["ReadBlockCurrent"] = $tar->ReadBlockCurrent;
				$NS["ReadFileSize"] = $tar->ReadFileSize;
				$NS["startPath"] = $DirScan->nextPath;

				if (!isset($NS["cnt"]))
				{
					$NS["cnt"] = 0;
				}
				$NS["cnt"] += $DirScan->FileCount;

				$status_title = GetMessage("MAIN_DUMP_SITE_PROC");
				$status_details = GetMessage("MAIN_DUMP_FILE_CNT")." <b>".intval($NS["cnt"])."</b>";
				$last_files_count = IntOption('last_files_count');
				if (!$last_files_count)
					$last_files_count = 200000;
				$step_done = $NS['cnt'] / $last_files_count;
				if ($step_done > 1)
					$step_done = 1;

				if ($r !== 'BREAK')
				{
					if (count($NS['dump_site_id']) > 1)
					{
						array_shift($NS['dump_site_id']);
						$NS['multisite'] = true;
						unset($NS['startPath']);
					}
					else // finish
					{
						$NS['arc_size'] = 0;
						$name = $NS["arc_name"];
						while(file_exists($name))
						{
							$size = filesize($name);
							$NS['arc_size'] += $size;
							if (IntOption("disk_space") > 0)
								CDiskQuota::updateDiskQuota("file", $size, "add");
							$name = $tar->getNextName($name);
						}
						DeleteDirFilesEx(BX_ROOT.'/backup/clouds');
						$NS["step"]++;
						$NS['finished_steps']++;
					}
				}
			}
		}
		else
			$NS["step"]++;
	}

	// Step 5: Integrity check
	if($NS["step"] == 5)
	{
		$step_done = 0;
		if (IntOption('dump_integrity_check') || $NS['check_archive'])
		{
			if (haveTime())
			{
				$tar = new CTarCheck;
				$tar->EncryptKey = $NS['dump_encrypt_key'] ?? '';

				if (!$tar->openRead($NS["arc_name"] ?? ''))
					RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS_READ').'<br>'.implode('<br>',$tar->err));
				else
				{
					if(($Block = intval($NS['Block'] ?? 0)) && !$tar->SkipTo($Block))
						RaiseErrorAndDie(implode('<br>',$tar->err));
					while(($r = $tar->extractFile()) && haveTime());

					$NS["Block"] = $tar->Block;
					$status_title = GetMessage('INTEGRITY_CHECK');
					$status_details = GetMessage('CURRENT_POS').' <b>'.CFile::FormatSize($NS['Block'] * 512).'</b> '.GetMessage('MAIN_DUMP_FROM').' <b>'.CFile::FormatSize($NS['data_size']).'</b>';
					$step_done = $NS['Block'] * 512 / $NS['data_size'];

					if ($r === false)
						RaiseErrorAndDie(implode('<br>',$tar->err));
					if ($r === 0)
					{
						$NS["step"]++;
						$NS['finished_steps']++;
					}
				}
				$tar->close();
			}
		}
		else
			$NS["step"]++;
	}

	// Step 6: Send to the cloud
	if($NS["step"] == 6)
	{
		$step_done = 0;
		if ($NS['BUCKET_ID'])
		{
			if (haveTime())
			{
				if (!CModule::IncludeModule('clouds'))
					RaiseErrorAndDie(GetMessage("MAIN_DUMP_NO_CLOUDS_MODULE"));

				$file_size = filesize($NS["arc_name"]);
				$file_name = $NS['BUCKET_ID'] == -1? basename($NS['arc_name']) : substr($NS['arc_name'], strlen(DOCUMENT_ROOT));
				$obUpload = new CCloudStorageUpload($file_name);

				if (!$NS['upload_start_time'])
					$NS['upload_start_time'] = START_EXEC_TIME;

				if ($NS['BUCKET_ID'] == -1)
				{
					if (!$bBitrixCloud)
						RaiseErrorAndDie(getMessage('DUMP_BXCLOUD_NA'));

					$obBucket = null;
					if (!$NS['obBucket'])
					{
						$backup = CBitrixCloudBackup::getInstance();
						$q = $backup->getQuota();
						if ($e = $APPLICATION->GetException())
						{
							unset($NS['obBucket']);
							RaiseErrorAndDie($e->GetString(),true);
						}
						else
						{
							if ($NS['arc_size'] > $q)
								RaiseErrorAndDie(GetMessage('DUMP_ERR_BIG_BACKUP', array('#ARC_SIZE#' => $NS['arc_size'], '#QUOTA#' => $q)), true);

							try
							{
								$obBucket = $backup->getBucketToWriteFile(CTar::getCheckword($NS['dump_encrypt_key']), basename($NS['arc_name']));
								$NS['obBucket'] = serialize($obBucket);
							}
							catch (CBitrixCloudException $e)
							{
								RaiseErrorAndDie($e->GetMessage(),true);
							}
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
						unset($NS['obBucket']);
						RaiseErrorAndDie($strError,true);
					}

					if (is_object($obBucket))
						$obBucket->unsetCheckWordHeader();
				}

				if ($fp = fopen($NS['arc_name'],'rb'))
				{
					fseek($fp, $obUpload->getPos());
					$part = fread($fp, $obUpload->getPartSize());
					fclose($fp);
					$fails = 0;
					$res = null;
					while($obUpload->hasRetries())
					{
						if($res = $obUpload->Next($part, $obBucket))
							break;
						elseif (++$fails >= 10)
						{
							$e = $APPLICATION->GetException();
							$strError = $e ? '. ' . $e->GetString() : '';
							RaiseErrorAndDie('Internal Error: could not init upload for ' . $fails . ' times' . $strError);
						}
					}

					if ($res)
					{
						$pos = $obUpload->getPos();
						if ($pos >= $file_size) // file ended
						{
							if($obUpload->Finish($obBucket))
							{
								$NS['pos'] += $file_size;
								$pos = 0;

								if ($NS['BUCKET_ID'] != -1)
								{
									$oBucket = new CCloudStorageBucket($NS['BUCKET_ID']);
									$oBucket->IncFileCounter($file_size);
								}

								$tar = new CTar;

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
									$name = CTar::getFirstName($NS['arc_name']);
									while(file_exists($name))
									{
										$size = filesize($name);
										if (unlink($name) && IntOption("disk_space") > 0)
											CDiskQuota::updateDiskQuota("file",$size , "del");
										$name = $tar->getNextName($name);
									}

									$NS["step"]++;
									$NS['finished_steps']++;
								}
							}
							else
							{
								$obUpload->Delete();
								unset($NS['obBucket']);
								$e = $APPLICATION->GetException();
								$strError = $e ? '. ' . $e->GetString() : '';
								RaiseErrorAndDie(GetMessage('MAIN_DUMP_ERR_FILE_SEND') . basename($NS['arc_name']) . $strError,true);
							}
						}

						$pos += $NS['pos'];

						$status_title = GetMessage("MAIN_DUMP_FILE_SENDING");
						$status_details = GetMessage('CURRENT_POS').' <b>'.CFile::FormatSize($pos).'</b>  '.GetMessage('MAIN_DUMP_FROM').' <b>'.CFile::FormatSize($NS["arc_size"])."</b>".
							GetMessage('TIME_LEFT', array('#TIME#' => HumanTime(($NS['arc_size'] - $pos) / $pos * (microtime(true) - $NS['upload_start_time']))));
						$step_done = $pos / $NS['arc_size'];
					}
					else
					{
						$obUpload->Delete();
						unset($NS['obBucket']);
						$e = $APPLICATION->GetException();
						$strError = $e ? '. ' . $e->GetString() : '';
						RaiseErrorAndDie(GetMessage('MAIN_DUMP_ERR_FILE_SEND') . basename($NS['arc_name']) . $strError, true);
					}
				}
				else
				{
					unset($NS['obBucket']);
					RaiseErrorAndDie(GetMessage("MAIN_DUMP_ERR_OPEN_FILE").$NS['arc_name'],true);
				}
			}
		}
		else
			$NS["step"]++;
	}

	if (!isset($NS["time"]))
	{
		$NS["time"] = 0;
	}

	$NS["time"] += workTime();

	if ($NS["step"] <= 6) // partial
	{
		CAdminMessage::ShowMessage(array(
			"TYPE" => "PROGRESS",
			"MESSAGE" => $status_title,
			"DETAILS" => $status_details.'#PROGRESS_BAR#'.
//				GetMessage('TIME_SPENT').' '.HumanTime($NS["time"]),
				GetMessage('TIME_SPENT').' <span id="counter_field">'.sprintf('%02d',floor($NS["time"]/60)).':'.sprintf('%02d', $NS['time']%60).'</span><!--'.intval($NS['time']).'-->',
			"HTML" => true,
			"PROGRESS_TOTAL" => 100,
			"PROGRESS_VALUE" => ($NS['finished_steps'] + $step_done) * 100 / $NS['total_steps'],
		));
		?>
		<script>
			window.setTimeout("if(!stop)AjaxSend('?process=Y&<?=bitrix_sessid_get()?>')",<?=1000 * IntOption("dump_max_exec_time_sleep")?>);
		</script>
		<?
	}
	else // Finish
	{
		$title = (!empty($NS['cloud_send']) ? GetMessage("MAIN_DUMP_SUCCESS_SENT") : GetMessage("MAIN_DUMP_FILE_FINISH")).'<br><br>';
		$status_msg = '';

		if (!empty($NS["arc_size"]))
		{
			$status_msg .= GetMessage("MAIN_DUMP_ARC_NAME").": <b>".basename(CTar::getFirstName($NS["arc_name"]))."</b><br>";
			$status_msg .= GetMessage("MAIN_DUMP_ARC_SIZE")." <b>".CFile::FormatSize($NS["arc_size"])."</b><br>";
			if ($NS['BUCKET_ID'] > 0)
				$l = ''; //htmlspecialcharsbx($arBucket['BUCKET'].' ('.$arBucket['SERVICE_ID'].')');
			elseif ($NS['BUCKET_ID'] == -1)
				$l = GetMessage('DUMP_MAIN_BITRIX_CLOUD');
			else
				$l = GetMessage("MAIN_DUMP_LOCAL");

			if ($l)
				$status_msg .= GetMessage("MAIN_DUMP_LOCATION").": <b>".$l."</b><br>";
		}

		if ($FinishedTables)
			$status_msg .= GetMessage("MAIN_DUMP_TABLE_FINISH")." <b>".$FinishedTables."</b><br>";
		if (!empty($NS["cnt"]))
		{
			$status_msg .= GetMessage("MAIN_DUMP_FILE_CNT")." <b>".$NS["cnt"]."</b><br>";
			if (IntOption("dump_file_public") && IntOption("dump_file_kernel"))
				COption::SetOptionInt("main", "last_files_count", $NS['cnt']);
		}

		if (!empty($NS["data_size"]))
			$status_msg .= GetMessage("MAIN_DUMP_FILE_SIZE")." <b>".CFile::FormatSize($NS["data_size"])."</b><br>";

		$status_msg .= GetMessage('TIME_SPENT').' <b>'.HumanTime($NS["time"]).'</b>';

		CAdminMessage::ShowMessage(array(
			"MESSAGE" => $title,
			"DETAILS" => $status_msg,
			"TYPE" => "OK",
			"HTML" => true));

?>
		<?echo bitrix_sessid_post()?>
		<script>
			EndDump();
		</script>
<?
	}
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
	die();
}

// in case of error
$DB->Query("UNLOCK TABLES",true);

$APPLICATION->SetTitle(GetMessage("MAIN_DUMP_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aTabs = array();
$aTabs[] = array("DIV"=>"std", "TAB"=>GetMessage("DUMP_MAIN_MAKE_ARC"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("MAKE_DUMP_FULL"));
$aTabs[] = array("DIV"=>"expert", "TAB"=>GetMessage("DUMP_MAIN_PARAMETERS"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("DUMP_MAIN_EXPERT_SETTINGS"));

$editTab = new CAdminTabControl("editTab", $aTabs, true, true);

if ($DB->type != 'MYSQL')
	echo BeginNote().GetMessage('MAIN_DUMP_MYSQL_ONLY').EndNote();
if (!$encrypt || !$bHash)
{
	CAdminMessage::ShowMessage(array(
		"MESSAGE" => ($encrypt ? '' : GetMessage("MAIN_DUMP_NOT_INSTALLED1")).($bHash ? '' : ' '.GetMessage('MAIN_DUMP_NOT_INSTALLED_HASH')),
		"DETAILS" => GetMessage("MAIN_DUMP_NO_ENC_FUNCTIONS"),
		"TYPE" => "ERROR",
		"HTML" => true));
}

if (defined('DUMP_DEBUG_MODE'))
	echo '<div style="color:red">DEBUG MODE</div><input type=button value=Next onclick="AjaxSend(\'?process=Y&'.bitrix_sessid_get().'\')">';

?><div id="dump_result_div"></div><?
$aMenu = array(
	array(
		"TEXT"	=> GetMessage("MAIN_DUMP_LIST_PAGE_TITLE"),
		"LINK"	=> "/bitrix/admin/dump_list.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("MAIN_DUMP_LIST_PAGE_TITLE"),
		"ICON"	=> "btn_list"
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();

CAdminFileDialog::ShowScript(
	Array
	(
		"event" => "__bx_select_dir",
		"arResultDest" => Array("FUNCTION_NAME" => "mnu_SelectValue"),
		"arPath" => Array('PATH'=>"/"),
		"select" => 'D',
		"operation" => 'O',
		"showUploadTab" => false,
		"showAddToMenuTab" => false,
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
?>
<script>
var counter_started = false;
var counter_sec = 0;

function StartCounter()
{
	counter_started = true;
}

function StopCounter(result)
{
	counter_started = false;
	if (result)
	{
		var regs;
		if (regs = /<!--([0-9]+)-->/.exec(result))
			counter_sec = regs[1];
	}
}

function IncCounter()
{
	window.setTimeout(IncCounter, 1000);
	if (!counter_started)
		return;

	counter_sec ++;
	var ob;
	if (ob = BX('counter_field'))
	{
		var min = Math.floor(counter_sec / 60);
		var sec = counter_sec % 60;
		if (min < 10)
			min = '0' + min;
		if (sec < 10)
			sec = '0' + sec;
		ob.innerHTML = min + ':' + sec;
	}
}
window.setTimeout(IncCounter, 1000);

function GetLicenseInfo()
{
	CHttpRequest.Action = function(result)
	{
		BX('license_info').innerHTML = result;
	};
	CHttpRequest.Send('?action=key_info&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>');
}

var numRows=0;
function AddTableRow()
{
	var oTable = BX('skip_mask_table');
	numRows = oTable.rows.length;
	var oRow = oTable.insertRow(-1);
	var oCell = oRow.insertCell(0);
	oCell.innerHTML = '<input type="text" name="arMask[]" id="mnu_FILES_' + numRows  +'" size=30><input type="button" id="mnu_FILES_btn_' + numRows  + '" value="..." onclick="showMenu(this, '+ numRows  +')">';
}

var currentID;

function showMenu(div, id)
{
	currentID = id;
	__bx_select_dir();
}

function mnu_SelectValue(filename, path, site, title, menu)
{
	BX('mnu_FILES_' + currentID).value = path + (path == '/' ? '' : '/') + filename;
}

function CheckExpert()
{
	var ob = document.fd1.dump_expert;

	var table = BX('tr_dump_expert').parentNode.parentNode;
	var found = false;
	for(var i=0;i<table.rows.length;i++)
	{
		if (found)
			table.rows[i].style.display = ob.checked ? '' : 'none';
		if (table.rows[i].id == 'tr_dump_expert')
			found = true;
	}
	CheckActiveStart();
}

function CheckActiveStart()
{
	if (counter_started)
		return;

	var start = true;
	if (document.fd1.dump_expert.checked)
	{
		start = document.fd1.dump_file_public.checked || document.fd1.dump_file_kernel.checked;

		document.fd1.max_file_size.disabled = !start;
		document.fd1.skip_mask.disabled = !start;

		var mask = start && document.fd1.skip_mask.checked;
		BX('more_button').disabled = !mask;

		var oTable = BX('skip_mask_table');
		numRows = oTable.rows.length;
		for(var i=0;i<numRows;i++)
		{
			BX('mnu_FILES_'+i).disabled = !mask;
			BX('mnu_FILES_btn_'+i).disabled = !mask;
		}

		<?
		if ($arAllBucket)
		{
			foreach($arAllBucket as $arBucket)
				echo 'start = start || BX("dump_cloud_'.$arBucket['ID'].'").checked;'."\n";
		}
		?>

		var ob;
		if (ob = document.fd1.dump_base)
		{
			document.fd1.dump_base_skip_stat.disabled = !ob.checked;
			document.fd1.dump_base_skip_search.disabled = !ob.checked;
			document.fd1.dump_base_skip_log.disabled = !ob.checked;

			start = start || ob.checked;
		}
	}

	BX('start_button').disabled = !start;
}

function CheckEncrypt(ob)
{
	var enc;
	if(enc = document.fd1.dump_encrypt)
	{
		enc.disabled = (ob.value == -1);
	}
}

BX.ready(
	function()
	{
		CheckExpert();
		<?
			if (isset($_REQUEST['from']) && $_REQUEST['from'] == 'bitrixcloud')
				echo 'StartDump();';
			elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'cloud_send' && check_bitrix_sessid())
				echo "AjaxSend('?process=Y&action=cloud_send&f_id=".CUtil::JSEscape($_REQUEST['f_id'] ?? '')."&dump_encrypt_key=".CUtil::JSEscape($_REQUEST['dump_encrypt_key'] ?? '')."&dump_bucket_id=".CUtil::JSEscape($_REQUEST['dump_bucket_id'] ?? '')."&".bitrix_sessid_get()."');";
			elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'check_archive' && check_bitrix_sessid())
				echo "AjaxSend('?process=Y&action=check_archive&f_id=".CUtil::JSEscape($_REQUEST['f_id'] ?? '')."&dump_encrypt_key=".CUtil::JSEscape($_REQUEST['dump_encrypt_key'] ?? '')."&".bitrix_sessid_get()."');";
		?>
	}
);

var stop;
var dump_encrypt_key;
var PasswordDialog;

function SavePassword()
{
	var key = BX('dump_encrypt_key').value;
	var l = key.length;

	var strError = '';
	if (!l)
		strError = '<?=GetMessageJS("MAIN_DUMP_EMPTY_PASS")?>';
	else if (!/^[\040-\176]*$/.test(key))
		strError = '<?=GetMessageJS('DUMP_ERR_NON_ASCII')?>';
	else if (l < 6)
		strError = '<?=GetMessageJS("MAIN_DUMP_ENC_PASS_DESC")?>';
	else if (key != BX('dump_encrypt_key_confirm').value)
		strError = '<?=GetMessageJS("DUMP_MAIN_ERR_PASS_CONFIRM")?>';

	if (strError)
	{
		BX('password_error').innerHTML = strError;
		BX('dump_encrypt_key').focus();
	}
	else
	{
		BX('password_error').innerHTML = '';
		dump_encrypt_key = key;
		BX.WindowManager.Get().Close();
		DoDump();
	}
}

function StartDump()
{
	var ob;
	if (BX('bitrixcloud').checked || (document.fd1.dump_expert.checked && (ob = document.fd1.dump_encrypt) && ob.checked))
	{
		if (!PasswordDialog)
		{
			PasswordDialog = new BX.CDialog({
				title: '<?=GetMessage("DUMP_MAIN_ENC_ARC")?>',
				content: '<?
					echo '<div style="color:red" id=password_error></div>';
					echo CUtil::JSEscape(BeginNote().GetMessage('MAIN_DUMP_SAVE_PASS').EndNote());
					echo '<table>';
					echo '<tr><td>'.GetMessage('MAIN_DUMP_ENC_PASS').'</td><td><input type="password" value="" id="dump_encrypt_key" onkeyup="if(event.keyCode==13) {BX(&quot;dump_encrypt_key_confirm&quot;).focus()}"/></td></tr>';
					echo '<tr><td>'.GetMessage('DUMP_MAIN_PASSWORD_CONFIRM').'</td><td><input type="password" value="" id="dump_encrypt_key_confirm"  onkeyup="if(event.keyCode==13) {SavePassword()}"/></td></tr>';
					echo '</table>';
				?>',
				height: 300,
				width: 600,
				resizable: false,
				buttons: [ {
					title: '<?=GetMessage("MAIN_DUMP_FILE_DUMP_BUTTON")?>',
	//				id: 'my_save',
	//				name: 'my_save',
					className: 'adm-btn-save',
					action: SavePassword

				}, BX.CAdminDialog.btnCancel ]
			})
		}
		PasswordDialog.Show();
		BX('dump_encrypt_key').focus();
	}
	else
	{
		dump_encrypt_key = '';
		DoDump();
	}
}

function DoDump()
{
	counter_sec = 0;
	var queryString = 'lang=<?echo htmlspecialcharsbx(LANGUAGE_ID)?>&process=Y&action=start';

	var ob = document.fd1.dump_bucket_id;
	for (var i = 0; i<ob.length; i++)
		if (ob[i].checked)
			queryString += '&dump_bucket_id=' + ob[i].value;

	i = 0;
	while (ob = document.fd1['dump_site_id' + i])
	{
		if (ob.checked)
			queryString += '&dump_site_id[]=' + ob.value;
		i++;
	}

	if (dump_encrypt_key)
		queryString += '&dump_encrypt_key=' + encodeURIComponent(dump_encrypt_key);

	if (document.fd1.dump_expert.checked)
	{
		queryString+='&dump_max_exec_time=' + encodeURIComponent(document.fd1.dump_max_exec_time.value);
		queryString+='&dump_max_exec_time_sleep=' + encodeURIComponent(document.fd1.dump_max_exec_time_sleep.value);
		queryString+='&dump_archive_size_limit=' + encodeURIComponent(document.fd1.dump_archive_size_limit.value);

		if (document.fd1.dump_disable_gzip.checked)
			queryString += '&dump_disable_gzip=Y';

		if (document.fd1.dump_integrity_check.checked)
			queryString += '&dump_integrity_check=Y';

		if(document.fd1.dump_file_public.checked)
			queryString +='&dump_file_public=Y';

		if(document.fd1.dump_file_kernel.checked)
			queryString+='&dump_file_kernel=Y';

		if(document.fd1.skip_mask.checked)
		{
			queryString+='&skip_mask=Y';

			var oTable = BX('skip_mask_table');
			numRows = oTable.rows.length;

			for(i=0;i<numRows;i++)
				queryString+='&arMask[]=' + encodeURIComponent(BX('mnu_FILES_'+i).value);
		}

		if(document.fd1.dump_file_public.checked || document.fd1.dump_file_kernel.checked)
			queryString+='&max_file_size=' + document.fd1.max_file_size.value;

		if((ob = document.fd1.dump_base) && ob.checked)
		{
			queryString +='&dump_base=Y';

			if(document.fd1.dump_base_skip_stat.checked)
				queryString +='&dump_base_skip_stat=Y';
			if(document.fd1.dump_base_skip_search.checked)
				queryString +='&dump_base_skip_search=Y';
			if(document.fd1.dump_base_skip_log.checked)
				queryString +='&dump_base_skip_log=Y';
		}

		<?
		if ($arAllBucket)
		{
			foreach($arAllBucket as $arBucket)
			{
				?>
					if (BX('dump_cloud_<?=$arBucket['ID']?>').checked)
						queryString += '&dump_cloud[<?=$arBucket['ID']?>]=Y';
				<?
			}
		}
		?>
	}
	else
		queryString += '&dump_all=Y';

	queryString += '&<?=bitrix_sessid_get()?>';

	BX('dump_result_div').innerHTML='';
	AjaxSend('dump.php', queryString);
	window.scrollTo(0, 0);
}

function EndDump(conditional)
{
	BX('stop_button').disabled = stop = true;

	if (!conditional || !counter_started)
		BX('start_button').disabled = false;
}

function AjaxSend(url, data)
{
	stop = false;
	BX('stop_button').disabled=false;
	BX('start_button').disabled=true;

	StartCounter();
	CHttpRequest.Action = function(result)
	{
		StopCounter(result);
		if (stop)
		{
			EndDump();
			BX('dump_result_div').innerHTML = '';
		}
		else
			BX('dump_result_div').innerHTML = result;
	};
	if (data)
		CHttpRequest.Post(url, data);
	else
		CHttpRequest.Send(url);
}

function RetryRequest()
{
	if (ob = BX('retry_button'))
		ob.disabled=true;
	AjaxSend('?process=Y&<?=bitrix_sessid_get()?>');
}

function getTableSize()
{
	AjaxSend('?ajax_mode=Y&action=get_table_size');
}
</script>


	<form name="fd1" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>" method="GET">
	<?
	$editTab->Begin();
	$editTab->BeginNextTab();

	if (!COption::GetOptionInt("main", 'dump_auto_enable_auto', 0))
	{
		?>
		<tr>
			<td colspan=2><?
			echo BeginNote();
			echo GetMessage('MAIN_DUMP_AUTO_WARN', array('#LINK#' => '/bitrix/admin/dump_auto.php?lang='.LANGUAGE_ID));
			echo EndNote();
			?></td>
		</tr>
		<?
	}

	if ($bBitrixCloud)
	{
	?>
	<tr>
		<td class="adm-detail-valign-top" width="40%"><?=GetMessage('DUMP_MAIN_BITRIX_CLOUD_DESC')?><span class="required"><sup>1</sup></span>:</td>
		<td width="60%">
		<?
		if(is_object($backup))
		{
			CAdminMessage::ShowMessage(array(
				"TYPE" => "PROGRESS",
				"DETAILS" => GetMessage("BCL_BACKUP_USAGE", array(
					"#QUOTA#" => CFile::FormatSize($quota = $backup->getQuota()),
					"#USAGE#" => CFile::FormatSize($usage = $backup->getUsage()),
				)).'#PROGRESS_BAR#',
				"HTML" => false,
				"PROGRESS_TOTAL" => $quota,
				"PROGRESS_VALUE" => $usage,
			));
		}
		?>
		</td>
	</tr>
	<?
	}
	?>
	<tr>
		<td class="adm-detail-valign-top" width=40%><?=GetMessage('MAIN_DUMP_ARC_LOCATION')?></td>
		<td>
			<div><input type=radio name=dump_bucket_id value="-1" <?=$bBitrixCloud ? "checked" : ""?> id="bitrixcloud" <?=$bBitrixCloud ? '' : 'disabled'?> onclick="CheckEncrypt(this)"> <label for="bitrixcloud"><?=GetMessage('DUMP_MAIN_IN_THE_BXCLOUD')?></label><?=$strBXError ? ' <span style="color:red">('.$strBXError.')</span>' : ''?></div>
			<div><input type=radio name=dump_bucket_id value="0"  <?=!$bBitrixCloud ? "checked" : ""?> id="dump_bucket_id_0" onclick="CheckEncrypt(this)"> <label for="dump_bucket_id_0"><?=GetMessage('MAIN_DUMP_LOCAL_DISK')?></label></div>
			<?
			$arWriteBucket = CBackup::GetBucketList($arFilter = array('READ_ONLY' => 'N'));
			if ($arWriteBucket)
			{
				foreach($arWriteBucket as $f)
					echo '<div><input type=radio name=dump_bucket_id value="'.$f['ID'].'" id="dump_bucket_id_'.$f['ID'].'" onclick="CheckEncrypt(this)"> <label for="dump_bucket_id_'.$f['ID'].'">'.GetMessage('DUMP_MAIN_IN_THE_CLOUD').' '.htmlspecialcharsbx($f['BUCKET'].' ('.$f['SERVICE_ID'].')').'</label></div>';
			}
			?>
		</td>
	</tr>
<?
	$arSitePath = array();
	$res = CSite::GetList('sort', 'asc', array('ACTIVE'=>'Y'));
	while($f = $res->Fetch())
	{
		$root = rtrim($f['ABS_DOC_ROOT'],'/');
		if (is_dir($root))
			$arSitePath[$root] = array($f['ID'] => '['.$f['ID'].'] '.$f['NAME']);
	}

	if (count($arSitePath) > 1)
	{
	?>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("DUMP_MAIN_SITE")?><span class="required"><sup>2</sup></span></td>
		<td>
			<?
				if ($s = COption::GetOptionString("main", "dump_site_id", $NS['dump_site_id']))
					$dump_site_id = unserialize($s, ['allowed_classes' => false]);
				else
					$dump_site_id = array();
				$i = 0;
				foreach($arSitePath as $path => $val)
				{
					$path = rtrim(str_replace('\\','/',$path),'/');
					$k = key($val);
					$v = current($val);
					echo '<div><input type=checkbox id="dump_site_id'.$i.'" value="'.htmlspecialcharsbx($k).'" '.(in_array($k, $dump_site_id) ? ' checked' : '').'> <label for="dump_site_id'.$i.'">'.htmlspecialcharsbx($v).'</label></div>';
					$i++;
				}
			?>
		</td>
	</tr>
	<?
	}
	?>
	<?
	$editTab->BeginNextTab();
	?>
	<tr>
		<td colspan=2 align=center><input type="checkbox" name="dump_expert" onclick="CheckExpert()" id="dump_expert"> <label for="dump_expert"><?=GetMessage("DUMP_MAIN_ENABLE_EXPERT")?></label></td>
	</tr>
	<tr id="tr_dump_expert">
		<td colspan=2><?
		echo BeginNote();
		echo GetMessage("DUMP_MAIN_CHANGE_SETTINGS");
		echo EndNote();
		?></td>
	</tr>



	<tr class="heading">
		<td colspan="2"><?=GetMessage("DUMP_MAIN_ARC_CONTENTS")?></td>
	</tr>
<?
if ($arAllBucket)
{
?>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("DUMP_MAIN_DOWNLOAD_CLOUDS")?></td>
		<td>
			<?
			foreach($arAllBucket as $arBucket)
				echo '<div><input type="checkbox" id="dump_cloud_'.$arBucket['ID'].'" OnClick="CheckActiveStart()" '.(IntOption("dump_cloud_".$arBucket['ID'], 1) ? "checked" : "").'> <label for="dump_cloud_'.$arBucket['ID'].'">'.htmlspecialcharsbx($arBucket['BUCKET'].' ('.$arBucket['SERVICE_ID'].')').'</label></div>';
			?>
		</td>
	</tr>
<?
}
?>
	<?
	if ($DB->type == 'MYSQL')
	{
		?>
		<tr>
			<td><?=GetMessage("DUMP_MAIN_ARC_DATABASE")?> <span id="db_size">(<a href="javascript:getTableSize()">?</a> <?=GetMessage("MAIN_DUMP_BASE_SIZE")?>)</span>:</td>
			<td><input type="checkbox" name="dump_base" OnClick="CheckActiveStart()" <?=IntOption("dump_base", 1) ? "checked" : "" ?>></td>
		</tr>
		<tr>
			<td class="adm-detail-valign-top"><?=GetMessage("DUMP_MAIN_DB_EXCLUDE")?></td>
			<td>
				<div><input type="checkbox" name="dump_base_skip_stat" <?=IntOption("dump_base_skip_stat", 0) ? "checked" : "" ?> id="dump_base_skip_stat"> <label for="dump_base_skip_stat"><?=GetMessage("MAIN_DUMP_BASE_STAT")?></label> <span id=db_stat_size></span></div>
				<div><input type="checkbox" name="dump_base_skip_search" value="Y" <?=IntOption("dump_base_skip_search", 0) ? "checked" : "" ?> id="dump_base_skip_search"> <label for="dump_base_skip_search"><?=GetMessage("MAIN_DUMP_BASE_SINDEX")?></label> <span id=db_search_size></span></div>
				<div><input type="checkbox" name="dump_base_skip_log" value="Y"<?=IntOption("dump_base_skip_log", 0) ? "checked" : "" ?> id="dump_base_skip_log"> <label for="dump_base_skip_log"><?=GetMessage("MAIN_DUMP_EVENT_LOG")?></label> <span id=db_event_size></span></div>
			</td>
		</tr>
		<?
	}
	?>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_FILE_KERNEL")?></td>
		<td><input type="checkbox" name="dump_file_kernel" value="Y" OnClick="CheckActiveStart()" <?=IntOption("dump_file_kernel", 1) ? "checked" : ''?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_FILE_PUBLIC")?></td>
		<td><input type="checkbox" name="dump_file_public" value="Y" OnClick="CheckActiveStart()" <?=IntOption("dump_file_public", 1) ? "checked" : ''?>></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIN_DUMP_MASK")?><span class="required"><sup>3</sup></span></td>
		<td>
			<input type="checkbox" name="skip_mask" value="Y" <?=IntOption('skip_mask', 0)?" checked":'';?> onclick="CheckActiveStart()">
			<table id="skip_mask_table" cellspacing=0 cellpadding=0>
			<?
			$i=-1;

			$res = unserialize(COption::GetOptionString("main","skip_mask_array"), ['allowed_classes' => false]);
			$skip_mask_array = is_array($res)?$res:array();

			foreach($skip_mask_array as $mask)
			{
				$i++;
				echo
				'<tr><td>
					<input type="text" name="arMask[]" id="mnu_FILES_'.$i.'" value="'.htmlspecialcharsbx($mask).'" size=30>'.
					'<input type="button" id="mnu_FILES_btn_'.$i.'" value="..." onclick="showMenu(this, \''.$i.'\')">'.
				'</tr>';
			}
			$i++;
			?>
				<tr><td><input type="text" name="arMask[]" id="mnu_FILES_<?=$i?>" size=30><input type="button" id="mnu_FILES_btn_<?=$i?>" value="..." onclick="showMenu(this, '<?=$i?>')"></tr>
			</table>
			<input type=button id="more_button" value="<?=GetMessage('MAIN_DUMP_MORE')?>" onclick="AddTableRow()">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_FILE_MAX_SIZE")?></td>
		<td><input type="text" name="max_file_size" size="10" value="<?=IntOption("dump_max_file_size", 0)?>" <?=CBackup::CheckDumpFiles() ? '' : "disabled"?>>
		<?echo GetMessage("MAIN_DUMP_FILE_MAX_SIZE_kb")?></td>
	</tr>




	<tr class="heading">
		<td colspan="2"><?=GetMessage("DUMP_MAIN_ARC_MODE")?></td>
	</tr>
	<tr>
		<td><?=GetMessage("MAIN_DUMP_ENABLE_ENC")?><span class="required"><sup>4</sup></td>
		<td><input type="checkbox" name="dump_encrypt" value="Y" <?=(IntOption("dump_encrypt", 0) ? "checked" : "")?> <?=$encrypt && !$bBitrixCloud  ? '' : 'disabled'?>></td>
	</tr>
	<tr>
		<td width=40%><?=GetMessage('INTEGRITY_CHECK_OPTION')?></td>
		<td><input type="checkbox" name="dump_integrity_check" <?=IntOption('dump_integrity_check', 1) ? 'checked' : '' ?>>
	</tr>
	<tr>
		<td><?=GetMessage('DISABLE_GZIP')?></td>
		<td><input type="checkbox" name="dump_disable_gzip" <?=IntOption('dump_use_compression', 1) && $bGzip ? '' : 'checked' ?> <?=$bGzip ? '' : 'disabled'?>>
	</tr>
	<tr>
		<td width=40%><?=GetMessage('STEP_LIMIT')?></td>
		<td>
			<input type="text" name="dump_max_exec_time" value="<?=IntOption("dump_max_exec_time", 20)?>" size=2>
			<?echo GetMessage("MAIN_DUMP_FILE_STEP_sec");?>,
			<?echo GetMessage("MAIN_DUMP_FILE_STEP_SLEEP")?>
			<input type="text" name="dump_max_exec_time_sleep" value="<?=IntOption("dump_max_exec_time_sleep", 3)?>" size=2>
			<?echo GetMessage("MAIN_DUMP_FILE_STEP_sec");?>
		</td>
	</tr>

	<tr>
		<td><?=GetMessage("MAIN_DUMP_MAX_ARCHIVE_SIZE")?></td>
		<td><input type="text" name="dump_archive_size_limit" value="<?=intval(COption::GetOptionString('main', 'dump_archive_size_limit', 100 * 1024 * 1024)) / 1024 / 1024?>" size=4> <?=GetMessage("MAIN_DUMP_MAX_ARCHIVE_SIZE_VALUES")?><span class="required"><sup>5</sup></span></td>
	</tr>
	<?
	$editTab->Buttons();
	?>
	<input type="button" id="start_button" class="adm-btn-save" value="<?=GetMessage("MAIN_DUMP_FILE_DUMP_BUTTON")?>" OnClick="StartDump();">
	<input type="button" id="stop_button" value="<?=GetMessage("MAIN_DUMP_FILE_STOP_BUTTON")?>" OnClick="EndDump(true)" disabled>
	<?
	$editTab->End();
	?>
	</form>
	<br>

<?
echo BeginNote();
echo '<div><span class=required><sup>1</sup></span> '.GetMessage("DUMP_MAIN_BXCLOUD_INFO").'</div>';
echo '<div><span class=required><sup>2</sup></span> '.GetMessage("DUMP_MAIN_MULTISITE_INFO").'</div>';
echo '<div><span class=required><sup>3</sup></span> '.GetMessage("MAIN_DUMP_FOOTER_MASK").'</div>';
echo '<div><span class=required><sup>4</sup></span> '.GetMessage("MAIN_DUMP_BXCLOUD_ENC").'</div>';
echo '<div><span class=required><sup>5</sup></span> '.GetMessage("MAIN_DUMP_MAX_ARCHIVE_SIZE_INFO").'</div>';
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

function IntOption($name, $def = 0)
{
	static $CACHE;
	if (!isset($CACHE[$name]))
		$CACHE[$name] = COption::GetOptionInt("main", $name, $def);
	return $CACHE[$name];
}

function getTableSize($reg)
{
	global $DB;
	if ($DB->type != 'MYSQL')
		return 0;

	static $CACHE;

	if (!$CACHE)
	{
		$CACHE = array();
		$sql = "SHOW TABLE STATUS";
		$res = $DB->Query($sql);

		while($row = $res->Fetch())
			$CACHE[$row['Name']] = $row['Data_length'];
	}

	$size = 0;
	foreach($CACHE as $table => $s)
		if (!$reg || preg_match('#'.$reg.'#i', $table))
			$size += $s;
	return $size;
}

function haveTime()
{
	if(defined('NO_TIME'))
		return microtime(true) - START_EXEC_TIME < 1;
	return microtime(true) - START_EXEC_TIME < IntOption("dump_max_exec_time");
}

function workTime()
{
	return microtime(true) - START_EXEC_TIME;
}


function RaiseErrorAndDie($strError, $bRepeat = false)
{
	if ($bRepeat)
	{
		$strError .= '<br><input type=button value="'.GetMessage('DUMP_RETRY').'" onclick="RetryRequest()" id="retry_button">
		<script>window.setTimeout(RetryRequest, 60000);</script>';
	}

	CAdminMessage::ShowMessage(array(
		"MESSAGE" => GetMessage("MAIN_DUMP_ERROR"),
		"DETAILS" =>  $strError,
		"TYPE" => "ERROR",
		"HTML" => true));
	echo '<script>EndDump();</script>';
	die();
}
