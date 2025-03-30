<?php
/*
 Test tar no compression no encryption
 /var/www/html/bitrix/backup# cat `ls -1v *.tar*` | tar tvf -

 Test encrypted tar with no compression
 /var/www/html/bitrix/backup# cat `ls -1v *.enc*` | tail -c +513 | openssl aes-256-ecb -d -in - -out - -K `php -r 'echo bin2hex(md5("passwd"));'` -nosalt -nopad | tar tvf -

 Test compressed tar no encryption
 /var/www/html/bitrix/backup# cat `ls -1v *.tar.gz*` | tar tvzf -

 Test compressed and encrypted tar
 /var/www/html/bitrix/backup# cat `ls -1v *.enc.gz*` | gunzip | tail -c +513 | openssl aes-256-ecb -d -in - -out - -K `php -r 'echo bin2hex(md5("passwd"));'` -nosalt -nopad | tar tvf -
*/
class CBackup
{
	static $DOCUMENT_ROOT_SITE;
	static $REAL_DOCUMENT_ROOT_SITE;

	protected $strLastFile;
	protected $LastFileSize;

	public static function CheckDumpClouds()
	{
		$arRes = array();
		if (IntOption('dump_do_clouds') && $arAllBucket = CBackup::GetBucketList())
		{
			foreach($arAllBucket as $arBucket)
				if (IntOption('dump_cloud_'.$arBucket['ID']))
					$arRes[] = $arBucket['ID'];
			if (!empty($arRes))
				return $arRes;
		}
		return false;
	}

	public static function CheckDumpFiles()
	{
		return IntOption("dump_file_public") || IntOption("dump_file_kernel");
	}

	public static function GetBucketList($arFilter = array())
	{
		if (CModule::IncludeModule('clouds'))
		{
			$arBucket = array();
			$rsData = CCloudStorageBucket::GetList(
				array("SORT"=>"DESC", "ID"=>"ASC"),
				array_merge(array('ACTIVE'=>'Y','READ_ONLY'=>'N'), $arFilter)
			);
			while($f = $rsData->Fetch())
			{
				$arBucket[] = $f;
			}
			return count($arBucket) ? $arBucket : false;
		}
		return false;
	}

	public static function ignorePath($path)
	{
		if (!file_exists($path)) // in case of wrong symlinks
			return true;

		if (!self::$REAL_DOCUMENT_ROOT_SITE)
			self::$REAL_DOCUMENT_ROOT_SITE = realpath(self::$DOCUMENT_ROOT_SITE);

		## Ignore paths
		static $ignore_path;
		if (!$ignore_path)
			$ignore_path = array(
				BX_PERSONAL_ROOT."/cache",
				BX_PERSONAL_ROOT."/cache_image",
				BX_PERSONAL_ROOT."/managed_cache",
				BX_PERSONAL_ROOT."/managed_flags",
				BX_PERSONAL_ROOT."/stack_cache",
				BX_PERSONAL_ROOT."/html_pages",
				BX_PERSONAL_ROOT."/tmp",
				BX_ROOT."/tmp",
				BX_ROOT."/help",
				BX_ROOT."/updates",
				'/'.COption::GetOptionString("main", "upload_dir", "upload")."/tmp",
				'/'.COption::GetOptionString("main", "upload_dir", "upload")."/resize_cache",
			);

		foreach($ignore_path as $value)
			if(self::$DOCUMENT_ROOT_SITE.$value == $path)
				return true;

		## Clouds
		if (IntOption('dump_do_clouds'))
		{
			$clouds = self::$DOCUMENT_ROOT_SITE.BX_ROOT.'/backup/clouds/';
			if (mb_strpos($path, $clouds) === 0 || mb_strpos($clouds, $path) === 0)
				return false;
		}

		## Backups
		if (mb_strpos($path, self::$DOCUMENT_ROOT_SITE.BX_ROOT.'/backup/') === 0)
			return true;

		## Symlinks
		if (is_dir($path))
		{
			if (is_link($path))
			{
				if (mb_strpos(realpath($path), self::$REAL_DOCUMENT_ROOT_SITE) !== false) // если симлинк ведет на папку внутри структуры сайта
					return true;
			}
		} ## File size
		elseif (($max_file_size = IntOption("dump_max_file_size")) > 0 && filesize($path) > $max_file_size * 1024)
			return true;

		## Skip mask
		if (CBackup::skipMask($path))
			return true;

		## Kernel vs Public
		$dump_file_public = IntOption('dump_file_public');
		$dump_file_kernel = IntOption('dump_file_kernel');

		if ($dump_file_public == $dump_file_kernel) // если обе опции либо включены либо выключены
			return !$dump_file_public;

		if (mb_strpos(self::$DOCUMENT_ROOT_SITE.BX_ROOT, $path) !== false) // на пути к /bitrix
			return false;

		if (mb_strpos($path, self::$DOCUMENT_ROOT_SITE.BX_ROOT) === false) // за пределами /bitrix
			return !$dump_file_public;

		$path_root = mb_substr($path, mb_strlen(self::$DOCUMENT_ROOT_SITE));
		if (preg_match('#^/bitrix/(.settings.php|php_interface|templates)/([^/]*)#',$path_root.'/',$regs))
			return !$dump_file_public;

		if (preg_match('#^/bitrix/(activities|components|gadgets|wizards)/([^/]*)#',$path_root.'/',$regs))
		{
			if (!$regs[2])
				return false;
			if ($regs[2] == 'bitrix')
				return !$dump_file_kernel;
			return !$dump_file_public;
		}

		// всё остальное в папке bitrix - ядро
		return !$dump_file_kernel;
	}

	public static function GetBucketFileList($BUCKET_ID, $path)
	{
		static $CACHE;

		if (isset($CACHE[$BUCKET_ID]))
			$obBucket = $CACHE[$BUCKET_ID];
		else
			$CACHE[$BUCKET_ID] = $obBucket = new CCloudStorageBucket($BUCKET_ID);

		if ($obBucket->Init())
			return $obBucket->ListFiles($path);
		return false;
	}

	public static function _preg_escape($str)
	{
		$search = array('#','[',']','.','?','(',')','^','$','|','{','}');
		$replace = array('\#','\[','\]','\.','\?','\(','\)','\^','\$','\|','\{','\}');
		return str_replace($search, $replace, $str);
	}

	public static function skipMask($abs_path)
	{
		global $skip_mask_array;

		if (!IntOption('skip_mask'))
			return false;

		if (!is_array($skip_mask_array))
		{
			return false;
		}

		$path = mb_substr($abs_path, mb_strlen(self::$DOCUMENT_ROOT_SITE));
		$path = str_replace('\\','/',$path);

		static $preg_mask_array;
		if (!$preg_mask_array)
		{
			$preg_mask_array = array();
			foreach($skip_mask_array as $a)
				$preg_mask_array[] = CBackup::_preg_escape($a);
		}

		foreach($skip_mask_array as $k => $mask)
		{
			if (str_starts_with($mask, '/')) // absolute path
			{
				if (!str_contains($mask, '*')) // нет звездочки
				{
					if (mb_strpos($path.'/', $mask.'/') === 0)
						return true;
				}
				elseif (preg_match('#^'.str_replace('*','[^/]*?',$preg_mask_array[$k]).'$#i',$path))
					return true;
			}
			elseif (!str_contains($mask, '/'))
			{
				if (!str_contains($mask, '*'))
				{
					if (str_ends_with($path, $mask))
						return true;
				}
				elseif (preg_match('#/[^/]*'.str_replace('*','[^/]*?',$preg_mask_array[$k]).'$#i',$path))
					return true;
			}
		}
	}

	public static function GetArcName($prefix = '')
	{
		$arc_name = DOCUMENT_ROOT.BX_ROOT."/backup/".$prefix.date("Ymd_His");

		$k = IntOption('dump_file_kernel');
		$p = IntOption('dump_file_public');
		$b = IntOption('dump_base');

		if ($k && $p && $b)
			$arc_name .= '_full';
		elseif (!($p xor $b))
			$arc_name .= '_'.($k ? '' : 'no').'core';
		elseif (!($k xor $b))
			$arc_name .= '_'.($p ? '' : 'no').'pub';
		elseif (!($k xor $p))
			$arc_name .= '_'.($b ? '' : 'no').'sql';

		$arc_name .= '_' . \Bitrix\Main\Security\Random::getString(16);
		return $arc_name;
	}

	public static function MakeDump($strDumpFile, &$arState)
	{
		global $DB;

		$B = new CBackup;

		if (!$arState)
		{
			if(!$B->file_put_contents_ex($strDumpFile, "-- Started: ".date('Y-m-d H:i:s')."\n"))
				return false;

			$rs = $DB->Query('SHOW VARIABLES LIKE "character_set_results"');
			if (($f = $rs->Fetch()) && array_key_exists ('Value', $f))
				if (!$B->file_put_contents_ex($strDumpFile, "SET NAMES '".$f['Value']."';\n"))
					return false;

			$arState = array('TABLES' => array());
			$arTables = array();
			$rsTables = $DB->Query("SHOW FULL TABLES WHERE TABLE_TYPE NOT LIKE 'VIEW'", false, '', array("fixed_connection"=>true));
			while($arTable = $rsTables->Fetch())
			{
				$table = current($arTable);

				$rsIndexes = $DB->Query("SHOW INDEX FROM `".$DB->ForSql($table)."`", true, '', array("fixed_connection"=>true));
				if($rsIndexes)
				{
					$arIndexes = array();
					while($ar = $rsIndexes->Fetch())
						if($ar["Non_unique"] == "0")
							$arIndexes[$ar["Key_name"]][$ar["Seq_in_index"]-1] = $ar["Column_name"];

					foreach($arIndexes as $IndexName => $arIndexColumns)
						if(count($arIndexColumns) != 1)
							unset($arIndexes[$IndexName]);

					if(!empty($arIndexes))
					{
						foreach($arIndexes as $IndexName => $arIndexColumns)
						{
							foreach($arIndexColumns as $SeqInIndex => $ColumnName)
								$key_column = $ColumnName;
							break;
						}
					}
					else
					{
						$key_column = false;
					}
				}
				else
				{
					$key_column = false;
				}

				$arState['TABLES'][$table] = array(
					"TABLE_NAME" => $table,
					"KEY_COLUMN" => $key_column,
					"LAST_ID" => 0
				);
			}
			$rsTables = $DB->Query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW'", false, '', array("fixed_connection"=>true));
			while($arTable = $rsTables->Fetch())
			{
				$table = current($arTable);

				$arState['TABLES'][$table] = array(
					"TABLE_NAME" => $table,
					"KEY_COLUMN" => false,
					"LAST_ID" => 0
				);
			}
			$arState['TableCount'] = count($arState['TABLES']);
			if (!haveTime())
				return true;
		}

		foreach($arState['TABLES'] as $table => $arTable)
		{
			if(!$arTable["LAST_ID"])
			{
				$rs = $DB->Query("SHOW CREATE TABLE `".$DB->ForSQL($table)."`", true);
				if ($rs === false)
					RaiseErrorAndDie(GetMessage('DUMP_TABLE_BROKEN', array('#TABLE#' => $table)));

				$row = $rs->Fetch();
				$string = $row['Create Table'];
				if (!$string) // VIEW
				{
					$string = $row['Create View'];
					if (!$B->file_put_contents_ex($strDumpFile,
						"-- -----------------------------------\n".
						"-- Creating view ".$DB->ForSQL($table)."\n".
						"-- -----------------------------------\n".
						"DROP VIEW IF EXISTS `".$DB->ForSQL($table)."`;\n".
						$string.";\n\n"))
							return false;
					unset($arState['TABLES'][$table]);
					continue;
				}
				elseif (CBackup::SkipTableData($table))
				{
					$string = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $string);
					if (!$B->file_put_contents_ex($strDumpFile,
						"-- -----------------------------------\n".
						"-- Creating empty table ".$DB->ForSQL($table)."\n".
						"-- -----------------------------------\n".
						$string.";\n\n"))
							return false;
					unset($arState['TABLES'][$table]);
					continue;
				}


				if (!$B->file_put_contents_ex($strDumpFile,
					"-- -----------------------------------\n".
					"-- Dumping table ".$DB->ForSQL($table)."\n".
					"-- -----------------------------------\n".
					"DROP TABLE IF EXISTS `".$DB->ForSQL($table)."`;\n".
					$string.";\n\n"))
						return false;

				$arState['TABLES'][$table]['COLUMNS'] = $arTable["COLUMNS"] = CBackup::GetTableColumns($table);
				if (($k = $arTable['KEY_COLUMN']) && $arTable['COLUMNS'][$k] > 0) // check if promary key is not numeric
				{
					unset($arTable['KEY_COLUMN']);
					unset($arState['TABLES'][$table]['KEY_COLUMN']);
				}
			}

			$strInsert = "";
			$cnt = $LIMIT = 10000;
			while($cnt == $LIMIT)
			{
				$i = $arTable['LAST_ID'];
				if(!empty($arTable["KEY_COLUMN"]))
				{
					$strSelect = "
						SELECT *
						FROM `".$arTable["TABLE_NAME"]."`
						".($arTable["LAST_ID"] ? "WHERE `".$arTable["KEY_COLUMN"]."` > '".$arTable["LAST_ID"]."'": "")."
						ORDER BY `".$arTable["KEY_COLUMN"]."`
						LIMIT ".$LIMIT;
				}
				else
				{
					$strSelect = "
						SELECT *
						FROM `".$arTable["TABLE_NAME"]."`
						LIMIT ".($arTable["LAST_ID"] ? $arTable["LAST_ID"].", ": "").$LIMIT;
				}

				if (!$rsSource = self::QueryUnbuffered($strSelect))
					RaiseErrorAndDie('SQL Query Error');
				while($arSource = $rsSource->Fetch())
				{
					if(!$strInsert)
						$strInsert = "INSERT INTO `".$arTable["TABLE_NAME"]."` VALUES";
					else
						$strInsert .= ",";

					foreach($arSource as $key => $value)
					{
						if(!isset($value) || is_null($value))
							$arSource[$key] = 'NULL';
						elseif($arTable["COLUMNS"][$key] == 0)
							$arSource[$key] = $value;
						elseif($arTable["COLUMNS"][$key] == 1)
						{
							if(empty($value) && $value != '0')
								$arSource[$key] = '\'\'';
							else
								$arSource[$key] = '0x' . bin2hex($value);
						}
						elseif($arTable["COLUMNS"][$key] == 2)
						{
							$arSource[$key] = "'".$DB->ForSql($value)."'";
						}
					}

					$strInsert .= "\n(".implode(", ", $arSource).")";

					$arState['TABLES'][$table]['LAST_ID'] = $arTable['LAST_ID'] = !empty($arTable["KEY_COLUMN"]) ? $arSource[$arTable["KEY_COLUMN"]] : ++$i;

					if (strlen($strInsert) > 1000000)
					{
						if(!$B->file_put_contents_ex($strDumpFile, $strInsert.";\n"))
							return false;
						$strInsert = "";
					}

					if (!haveTime())
					{
						self::FreeResult();
						return $strInsert ? $B->file_put_contents_ex($strDumpFile, $strInsert.";\n") : true;
					}
				}
				$cnt = $rsSource->SelectedRowsCount();
				self::FreeResult();
			}

			if($strInsert && !$B->file_put_contents_ex($strDumpFile, $strInsert.";\n"))
				return false;

			if ($cnt < $LIMIT)
				unset($arState['TABLES'][$table]);
		}

		if(!$B->file_put_contents_ex($strDumpFile, "-- Finished: ".date('Y-m-d H:i:s')))
			return false;

		$arState['end'] = true;
		return true;
	}

	public static function QueryUnbuffered($q)
	{
		global $DB;

		$DB->result = mysqli_query($DB->db_Conn, $q, MYSQLI_USE_RESULT);

		$rsSource = new CDBResult($DB->result);
		$rsSource->DB = $DB;
		return $rsSource;
	}

	public static function FreeResult()
	{
		global $DB;

		mysqli_free_result($DB->result);
	}

	public function file_put_contents_ex($strDumpFile, $str)
	{
		$LIMIT = 2000000000;
		if (!$this->strLastFile)
		{
			$this->strLastFile = $strNextFile = $strDumpFile;
			$this->LastFileSize = 0;
			while(file_exists($strNextFile))
			{
				$this->LastFileSize = filesize($this->strLastFile = $strNextFile);
				$strNextFile = self::getNextName($strNextFile);
			}
		}

		$c = strlen($str);
		if ($this->LastFileSize + $c >= $LIMIT)
		{
			$this->strLastFile = self::getNextName($this->strLastFile);
			$this->LastFileSize = 0;
		}
		$this->LastFileSize += $c;
		return file_put_contents($this->strLastFile, $str, 8);
	}

	public static function GetTableColumns($TableName)
	{
		global $DB;
		$arResult = array();

		$sql = "SHOW COLUMNS FROM `".$TableName."`";
		$res = $DB->Query($sql, false, '', array("fixed_connection"=>true));
		while($row = $res->Fetch())
		{
			if(preg_match("/^(\w*int|year|float|double|decimal)/", $row["Type"]))
				$arResult[$row["Field"]] = 0;
			elseif(preg_match("/^(\w*(binary|blob))/", $row["Type"]))
				$arResult[$row["Field"]] = 1;
			else
				$arResult[$row["Field"]] = 2;
		}

		return $arResult;
	}

	public static function SkipTableData($table)
	{
		$table = mb_strtolower($table);
		if (preg_match("#^b_stat#", $table) && IntOption('dump_base_skip_stat'))
			return true;
		elseif (preg_match("#^b_search_#", $table) && !preg_match('#^(b_search_custom_rank|b_search_phrase)$#', $table) && IntOption('dump_base_skip_search'))
			return true;
		elseif($table == 'b_event_log' && IntOption('dump_base_skip_log'))
			return true;
		return false;
	}

	public static function getNextName($file)
	{
		static $CACHE;
		$c = &$CACHE[$file];

		if (!$c)
		{
			$l = strrpos($file, '.');
			$num = substr($file,$l+1);
			if (is_numeric($num))
				$file = substr($file,0,$l+1).++$num;
			else
				$file .= '.1';
			$c = $file;
		}
		return $c;
	}
}

class CDirScan
{
	var $DirCount = 0;
	var $FileCount = 0;
	var $err= array();

	var $bFound = false;
	var $nextPath = '';
	var $startPath = '';
	var $arIncludeDir = false;

	function __construct()
	{
	}

	function ProcessDirBefore($f)
	{
		return true;
	}

	function ProcessDirAfter($f)
	{
		return true;
	}

	function ProcessFile($f)
	{
		return true;
	}

	function Skip($f)
	{
		if ($this->startPath)
		{
			if (mb_strpos($this->startPath.'/', $f.'/') === 0)
			{
				if ($this->startPath == $f)
				{
					$this->startPath = '';
				}

				return false;
			}
			else
				return true;
		}
		return false;
	}

	function Scan($dir)
	{
		$dir = str_replace('\\','/',$dir);

		if ($this->Skip($dir))
		{
			// echo $dir."<br>\n";
			return;
		}

		$this->nextPath = $dir;

		if (is_dir($dir))
		{
		#############################
		# DIR
		#############################
			if (!$this->startPath) // если начальный путь найден или не задан
			{
				$r = $this->ProcessDirBefore($dir);
				if ($r === false)
					return false;
			}

			if (!($handle = opendir($dir)))
			{
				$this->err[] = 'Error opening dir: '.$dir;
				return false;
			}

			while (($item = readdir($handle)) !== false)
			{
				if ($item == '.' || $item == '..' || false !== mb_strpos($item, '\\'))
					continue;

				$f = $dir."/".$item;
				$r = $this->Scan($f);
				if ($r === false || $r === 'BREAK')
				{
					closedir($handle);
					return $r;
				}
			}
			closedir($handle);

			if (!$this->startPath) // если начальный путь найден или не задан
			{
				if ($this->ProcessDirAfter($dir) === false)
					return false;
				$this->DirCount++;
			}
		}
		else
		{
		#############################
		# FILE
		#############################
			$r = $this->ProcessFile($dir);
			if ($r === false)
				return false;
			elseif ($r === 'BREAK') // если файл обработан частично
				return $r;
			$this->FileCount++;
		}
		return true;
	}
}

class CDirRealScan extends CDirScan
{
	var $arSkip = array();
	function ProcessFile($f)
	{
		global $tar;
		while(haveTime())
		{
			$f = str_replace('\\', '/', $f);
			if (preg_match('#/bitrix/(php_interface/dbconn.php|.settings.php)$#', $f, $regs))
			{
				if (!$arInfo = $tar->getFileInfo($f))
					return false;

				if ($regs[1] == '.settings.php')
				{
					if (!is_array($ar = include($f)))
					{
						$this->err[] = 'Can\'t parse file: '.$f;
						return false;
					}

					if (is_array($ar['connections']['value']))
					{
						foreach($ar['connections']['value'] as $k => $arTmp)
						{
							$ar['connections']['value'][$k]['login'] = '******';
							$ar['connections']['value'][$k]['password'] = '******';
							$ar['connections']['value'][$k]['database'] = '******';
						}
					}

					$strFile = "<"."?php\nreturn ".var_export($ar, true).";\n";
				}
				else // dbconn.php
				{
					if (false === $arFile = file($f))
					{
						$this->err[] = 'Can\'t read file: '.$f;
						return false;
					}

					$strFile = '';
					foreach($arFile as $line)
					{
						if (preg_match("#^[ \t]*".'\$'."(DB(Login|Password|Name))#",$line,$regs))
							$strFile .= '$'.$regs[1].' = "******";'."\n";
						else
							$strFile .= str_replace("\r\n","\n",$line);
					}
				}

				$arInfo['size'] = strlen($strFile);
				if (!$tar->writeHeader($arInfo))
					return false;

				$i = 0;
				while($i < $arInfo['size'])
				{
					if (!$tar->writeBlock(pack("a512", substr($strFile, $i, 512))))
						return false;
					$i += 512;
				}

				return true;
			}

			if ($tar->addFile($f) === false)
				return false; // error
			if ($tar->ReadBlockCurrent == 0)
				return true; // finished
		}
		return 'BREAK';
	}

	function ProcessDirBefore($f)
	{
		global $tar;
		return $tar->addFile($f);
	}

	function Skip($f)
	{
		static $bFoundDocumentRoot;
		$res = false;
		if ($this->startPath)
		{
			if (mb_strpos($this->startPath.'/', $f.'/') === 0)
			{
				if ($this->startPath == $f)
				{
					$this->startPath = '';
				}

				return false;
			}
			else
				return true;
		}
		elseif (!empty($this->arSkip[$f]))
			return true;
		elseif ($bFoundDocumentRoot)
			$res = CBackup::ignorePath($f);

		$bFoundDocumentRoot = true;
		return $res;
	}
}

class CPasswordStorage
{
	const SIGN = 'CACHE_';

	public static function Init()
	{
		return function_exists('openssl_encrypt');
	}

	public static function getEncryptKey()
	{
		static $key;

		if ($key === null)
		{
			/** @var string $LICENSE_KEY defined in the license_key.php */
			if (file_exists($file = $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/license_key.php'))
				include($file);
			if ($LICENSE_KEY == '')
				$LICENSE_KEY = 'DEMO';

			$key = $LICENSE_KEY;

			$l = strlen($key);
			if ($l > 56)
				$key = substr($key, 0, 56);
			elseif ($l < 16)
				$key = str_repeat($key, ceil(16/$l));
		}

		return $key;
	}

	public static function Set($strName, $strVal)
	{
		if (!self::Init())
		{
			return false;
		}

		$encrypted = strlen($strVal) ? CTar::encrypt(self::SIGN . $strVal, self::getEncryptKey()) : '';
		$encoded = base64_encode($encrypted);

		return COption::SetOptionString('main', $strName, $encoded);
	}

	public static function Get($strName)
	{
		if (!self::Init())
		{
			return false;
		}

		$strVal = COption::GetOptionString('main', $strName, '');
		$decoded = base64_decode($strVal);
		$decrypted = '';
		foreach (CTar::getCryptoAlgorithmList() as $method)
		{
			$decrypted = CTar::decrypt($decoded, self::getEncryptKey(), $method);
			if (str_starts_with($decrypted, self::SIGN))
			{
				if ($method !== CTar::getDefaultCryptoAlgorithm())
				{
					// Update with default encryption method
					static::Set($strName, substr(rtrim($decrypted, "\x00"), strlen(self::SIGN)));
				}
				break;
			}
		}

		if (str_starts_with($decrypted, self::SIGN))
		{
			return substr(rtrim($decrypted, "\x00"), strlen(self::SIGN));
		}

		return false;
	}
}

class CTar
{
	var $gzip;
	var $file;
	var $err = array();
	var $LastErrCode;
	var $res;
	var $Block = 0;
	var $BlockHeader;
	var $path;
	var $FileCount = 0;
	var $DirCount = 0;
	var $ReadBlockMax = 2000;
	var $ReadBlockCurrent = 0;
	var $ReadFileSize = 0;
	var $header = null;
	var $ArchiveSizeLimit;
	const BX_EXTRA = 'BX0000';
	const BX_SIGNATURE = 'Bitrix Encrypted File';
	var $BufferSize;
	var $Buffer;
	var $dataSizeCache = array();
	var $EncryptKey;
	var $EncryptAlgorithm;
	var $prefix = '';

	##############
	# READ
	# {
	function openRead($file)
	{
		if (!isset($this->gzip) && (substr($file,-3)=='.gz' || substr($file,-4)=='.tgz'))
			$this->gzip = true;

		$this->BufferSize = 51200;

		if ($this->open($file, 'r'))
		{
			if ('' !== $str = $this->gzip ? gzread($this->res,512) : fread($this->res,512))
			{
				$data = unpack("a100empty/a90signature/a10version/a56tail/a256enc", $str);
				if (trim($data['signature']) != self::BX_SIGNATURE)
				{
					if (strlen($this->EncryptKey))
						$this->Error('Invalid encryption signature','ENC_SIGN');

					// Probably archive is not encrypted
					$this->gzip ? gzseek($this->res, 0) : fseek($this->res, 0);
					$this->EncryptKey = null;

					return $this->res;
				}

				$version = trim($data['version']);
				if (version_compare($version, '1.2', '>'))
					return $this->Error('Unsupported archive version: '.$version, 'ENC_VER');

				$key = $this->getEncryptKey();
				if (!$key)
				{
					return $this->Error('Invalid encryption key', 'ENC_KEY');
				}
				$this->BlockHeader = $this->Block = 1;

				$this->EncryptAlgorithm = null;
				foreach (static::getCryptoAlgorithmList() as $EncryptAlgorithm)
				{
					if (substr($str, 0, 256) === self::decrypt($data['enc'], $key, $EncryptAlgorithm))
					{
						$this->EncryptAlgorithm = $EncryptAlgorithm;
						break;
					}
				}

				if (!$this->EncryptAlgorithm)
				{
					return $this->Error('Invalid encryption key', 'ENC_KEY');
				}
			}
		}
		return $this->res;
	}

	function readBlock($bIgnoreOpenNextError = false)
	{
		if (!$this->Buffer)
		{
			$str = $this->gzip ? gzread($this->res, $this->BufferSize) : fread($this->res, $this->BufferSize);
			if ($str === '' && $this->openNext($bIgnoreOpenNextError))
				$str = $this->gzip ? gzread($this->res, $this->BufferSize) : fread($this->res, $this->BufferSize);
			if ($str !== '' && $key = $this->getEncryptKey())
				$str = self::decrypt($str, $key, $this->EncryptAlgorithm);
			$this->Buffer = $str;
		}

		$str = '';
		if ($this->Buffer)
		{
			$str = substr($this->Buffer, 0, 512);
			$this->Buffer = substr($this->Buffer, 512);
			$this->Block++;
		}

		return $str;
	}

	function SkipFile()
	{
		if ($this->Skip(ceil(intval($this->header['size'])/512)))
		{
			$this->header = null;
			return true;
		}
		return false;
	}

	function Skip($Block)
	{
		if ($Block == 0)
			return true;

		$this->Block += $Block;
		$toSkip = $Block * 512;

		if (strlen($this->Buffer) > $toSkip)
		{
			$this->Buffer = substr($this->Buffer, $toSkip);
			return true;
		}
		$this->Buffer = '';
		$NewPos = $this->Block * 512;

		if ($ArchiveSize = $this->getDataSize($file = self::getFirstName($this->file)))
		{
			while($NewPos > $ArchiveSize)
			{
				$file = $this->getNextName($file);
				$NewPos -= $ArchiveSize;
			}
		}

		if ($file != $this->file)
		{
			$this->close();
			if (!$this->open($file, $this->mode))
				return false;
		}

		if (0 === ($this->gzip ? gzseek($this->res, $NewPos) : fseek($this->res, $NewPos)))
			return true;
		return $this->Error('File seek error (file: '.$this->file.', position: '.$NewPos.')');
	}

	function SkipTo($Block)
	{
		return $this->Skip($Block - $this->Block);
	}

	function readHeader($Long = false)
	{
		$str = '';
		while(trim($str) == '')
		{
			if (!($l = strlen($str = $this->readBlock($bIgnoreOpenNextError = true))))
				return 0; // finish
		}

		if (!$Long)
			$this->BlockHeader = $this->Block - 1;

		if ($l != 512)
			return $this->Error('Wrong block size: '.strlen($str).' (block '.$this->Block.')');


		$data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix", $str);
		$chk = trim($data['devmajor'].$data['devminor']);

		if (!is_numeric(trim($data['checksum'])) || !empty($chk))
		{
			return $this->Error('Archive is corrupted, wrong block: '.($this->Block-1).', file: '.$this->file.', md5sum: '.md5_file($this->file));
		}

		$header['filename'] = trim(trim($data['prefix'], "\x00").'/'.trim($data['filename'], "\x00"),'/');
		$header['mode'] = OctDec($data['mode']);
		$header['uid'] = OctDec($data['uid']);
		$header['gid'] = OctDec($data['gid']);
		$header['size'] = OctDec($data['size']);
		$header['mtime'] = OctDec($data['mtime']);
		$header['type'] = trim($data['type'], "\x00");
//		$header['link'] = $data['link'];

		if (str_starts_with($header['filename'], './'))
			$header['filename'] = substr($header['filename'], 2);

		if ($header['type']=='L') // Long header
		{
			$filename = '';
			$n = ceil($header['size']/512);
			for ($i = 0; $i < $n; $i++)
				$filename .= $this->readBlock();

			if (!is_array($header = $this->readHeader($Long = true)))
				return $this->Error('Wrong long header, block: '.$this->Block);
			$header['filename'] = substr($filename, 0, strpos($filename, chr(0)));
		}

		if (str_starts_with($header['filename'], '/')) // trailing slash
			$header['type'] = 5; // Directory

		if ($header['type']=='5')
			$header['size'] = '';

		if ($header['filename']=='')
			return $this->Error('Filename is empty, wrong block: '.($this->Block-1));

		if (!$this->checkCRC($str, $data))
			return $this->Error('Checksum error on file: '.$header['filename']);

		$this->header = $header;

		return $header;
	}

	function checkCRC($str, $data)
	{
		$checksum = $this->checksum($str);
		$res = octdec($data['checksum']) == $checksum || $data['checksum']===0 && $checksum==256;
		return $res;
	}

	function extractFile()
	{
		if ($this->header === null)
		{
			if(($header = $this->readHeader()) === false || $header === 0 || $header === true)
			{
				if ($header === true && $this->SkipFile() === false)
					return false;
				return $header;
			}

			$this->lastPath = $f = $this->path.'/'.$header['filename'];

			if ($this->ReadBlockCurrent == 0)
			{
				if ($header['type']==5) // dir
				{
					if(!file_exists($f) && !self::xmkdir($f))
						return $this->ErrorAndSkip('Can\'t create folder: '.$f);
					//chmod($f, $header['mode']);
				}
				else // file
				{
					if (!self::xmkdir($dirname = dirname($f)))
						return $this->ErrorAndSkip('Can\'t create folder: '.$dirname);
					elseif (($rs = fopen($f, 'wb'))===false)
						return $this->ErrorAndSkip('Can\'t create file: '.$f);
				}
			}
			else
				return $this->Skip($this->ReadBlockCurrent);
		}
		else // файл уже частично распакован, продолжаем на том же хите
		{
			$header = $this->header;
			$this->lastPath = $f = $this->path.'/'.$header['filename'];
		}

		if ($header['type'] != 5) // пишем контент в файл
		{
			if (!$rs)
			{
				if (($rs = fopen($f, 'ab'))===false)
					return $this->ErrorAndSkip('Can\'t open file: '.$f);
			}

			$i = 0;
			$FileBlockCount = ceil($header['size'] / 512);
			while(++$this->ReadBlockCurrent <= $FileBlockCount && ($contents = $this->readBlock()))
			{
				if ($this->ReadBlockCurrent == $FileBlockCount && ($chunk = $header['size'] % 512))
					$contents = substr($contents, 0, $chunk);

				fwrite($rs,$contents);

				if ($this->ReadBlockMax && ++$i >= $this->ReadBlockMax)
				{
					fclose($rs);
					return true; // Break
				}
			}
			fclose($rs);

			if (($s = filesize($f)) != $header['size'])
				return $this->Error('File size is wrong: '.$header['filename'].' (real: '.$s.'  expected: '.$header['size'].')');

			//chmod($f, $header['mode']);
		}

		if ($this->header['type']==5)
			$this->DirCount++;
		else
			$this->FileCount++;

		$this->debug_header = $this->header;
		$this->BlockHeader = $this->Block;
		$this->ReadBlockCurrent = 0;
		$this->header = null;

		return true;
	}

	function openNext($bIgnoreOpenNextError)
	{
		if (file_exists($file = $this->getNextName()))
		{
			$this->close();
			return $this->open($file,$this->mode);
		}
		elseif (!$bIgnoreOpenNextError)
			return $this->Error("File doesn't exist: ".$file);
		return false;
	}

	public static function getLastNum($file)
	{
		$file = self::getFirstName($file);

		if (!file_exists($file))
			return false;
		$f = fopen($file, 'rb');
		fseek($f, 12);
		if (fread($f, 2) == 'LN')
			$res = end(unpack('va',fread($f, 2)));
		else
			$res = false;
		fclose($f);
		return $res;
	}

	# }
	##############

	##############
	# WRITE
	# {
	function openWrite($file)
	{
		if (!isset($this->gzip) && (substr($file,-3)=='.gz' || substr($file,-4)=='.tgz'))
			$this->gzip = true;

		$this->BufferSize = 51200;

		if (intval($this->ArchiveSizeLimit) <= 0)
			$this->ArchiveSizeLimit = 1024 * 1024 * 1024; // 1Gb


		$this->Block = 0;
		while(file_exists($file1 = $this->getNextName($file))) // находим последний архив
		{
			$this->Block += ceil($this->ArchiveSizeLimit / 512);
			$file = $file1;
		}

		$size = 0;
		if (file_exists($file) && !$size = $this->getDataSize($file))
		{
			return $this->Error('Can\'t get data size: '.$file);
		}

		$this->Block += $size / 512;
		if ($size >= $this->ArchiveSizeLimit) // если последний архив полон
		{
			$file = $file1;
			$size = 0;
		}
		$this->ArchiveSizeCurrent = $size;

		$res = $this->open($file, 'a');
		if ($res && $this->Block == 0 && ($key = $this->getEncryptKey())) // запишем служебный заголовок для зашифрованного архива
		{
			$ver = function_exists('openssl_encrypt') ? '1.2' : '1.1';
			$enc = pack("a100a90a10a56",md5(uniqid(rand(), true)), self::BX_SIGNATURE, $ver, "");
			$enc .= $this->encrypt($enc, $key);
			if (!($this->gzip ? gzwrite($this->res, $enc) : fwrite($this->res, $enc)))
			{
				return $this->Error('Error writing to file');
			}
			$this->Block = 1;
			$this->ArchiveSizeCurrent = 512;
		}
		return $res;
	}

	// создадим пустой gzip с экстра полем
	function createEmptyGzipExtra($file)
	{
		if (file_exists($file))
		{
			return $this->Error('File already exists: '.$file);
		}

		if (!($f = gzopen($file,'wb')))
		{
			return $this->Error('Can\'t open file: '.$file);
		}

		gzwrite($f,'');
		gzclose($f);

		$data = "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\x03\x00\x00\x00\xff\xff\x03\x00\x00\x00\x00\x00\x00\x00\x00\x00"; // buggy zlib 1.2.7

		if (!($f = fopen($file, 'w')))
		{
			return $this->Error('Can\'t open file for writing: '.$file);
		}

		$ar = unpack('A3bin0/c1FLG/A6bin1', substr($data,0,10));

		$EXTRA = "\x00\x00\x00\x00".self::BX_EXTRA; // 10 байт
		fwrite($f,$ar['bin0']."\x04".$ar['bin1'].chr(strlen($EXTRA))."\x00".$EXTRA.substr($data,10));
		fclose($f);
		return true;
	}

	function writeBlock($str)
	{
		$l = strlen($str);
		if ($l!=512)
			return $this->Error('Wrong block size: '.$l);

		if ($this->ArchiveSizeCurrent >= $this->ArchiveSizeLimit)
		{
			$file = $this->getNextName();
			$this->close();

			if (!$this->open($file,$this->mode))
				return false;

			$this->ArchiveSizeCurrent = 0;
		}

		$this->Buffer .= $str;

		$this->Block++;
		$this->ArchiveSizeCurrent += 512;

		if (strlen($this->Buffer) == $this->BufferSize)
			return $this->flushBuffer();

		return true;
	}

	function flushBuffer()
	{
		if (!$str = $this->Buffer)
			return true;
		$this->Buffer = '';

		if ($key = $this->getEncryptKey())
			$str = $this->encrypt($str, $key);

		return $this->gzip ? gzwrite($this->res, $str) : fwrite($this->res, $str);
	}

	function writeHeader($ar)
	{
		$header0 = pack("a100a8a8a8a12a12", $ar['filename'], decoct($ar['mode']), decoct($ar['uid']), decoct($ar['gid']), decoct($ar['size']), decoct($ar['mtime']));
		$header1 = pack("a1a100a6a2a32a32a8a8a155", $ar['type'],'','','','','','', '', $ar['prefix'] ?? null);

		$checksum = pack("a8",decoct($this->checksum($header0.'        '.$header1)));
		$header = pack("a512", $header0.$checksum.$header1);
		return $this->writeBlock($header) || $this->Error('Error writing header');
	}

	function addFile($f)
	{
		$f = str_replace('\\', '/', $f);
		$path = $this->prefix.substr($f, strlen($this->path) + 1);
		if ($path == '')
			return true;
		if (strlen($path) > 512)
			return $this->Error('Path is too long: '.$path);
		if (is_link($f) && !file_exists($f)) // broken link
			return true;

		if (!$ar = $this->getFileInfo($f))
			return false;

		if ($this->ReadBlockCurrent == 0) // read from start
		{
			$this->ReadFileSize = $ar['size'];
			if (strlen($path) > 100) // Long header
			{
				$ar0 = $ar;
				$ar0['type'] = 'L';
				$ar0['filename'] = '././@LongLink';
				$ar0['size'] = strlen($path);
				if (!$this->writeHeader($ar0))
					return $this->Error('Can\'t write header to file: '.$this->file);

				if (!$this->writeBlock(pack("a512",$path)))
					return $this->Error('Can\'t write to file: '.$this->file);

				$ar['filename'] = substr($path,0,100);
			}

			if (!$this->writeHeader($ar))
				return $this->Error('Can\'t write header to file: '.$this->file);
		}

		if ($ar['type'] == 0 && $ar['size'] > 0) // File
		{
			if (!($rs = fopen($f, 'rb')))
				return $this->Error('Error opening file: '.$f);

			if ($this->ReadBlockCurrent)
				fseek($rs, $this->ReadBlockCurrent * 512);

			$i = 0;
			while(!feof($rs) && ('' !== $str = fread($rs,512)))
			{
				if ($this->ReadFileSize && $this->ReadBlockCurrent * 512 > $this->ReadFileSize)
					return $this->Error('File has changed while reading: '.$f);
				$this->ReadBlockCurrent++;
				if (feof($rs))
				{
					$str = pack("a512", $str);
				}
				elseif (strlen($str) != 512)
				{
					return $this->Error('Error reading from file: '.$f);
				}

				if (!$this->writeBlock($str))
				{
					fclose($rs);
					return $this->Error('Error processing file: '.$f);
				}

				if ($this->ReadBlockMax && ++$i >= $this->ReadBlockMax)
				{
					fclose($rs);
					return true;
				}
			}
			fclose($rs);
			$this->ReadBlockCurrent = 0;
		}
		return true;
	}

	# }
	##############

	##############
	# BASE
	# {
	function open($file, $mode='r')
	{
		$this->file = $file;
		$this->mode = $mode;

		if (is_dir($file))
			return $this->Error('File is directory: '.$file);

		if ($this->EncryptKey && !function_exists('openssl_encrypt'))
			return $this->Error('Function openssl_encrypt is not available');

		if ($mode == 'r' && !file_exists($file))
			return $this->Error('File does not exist: '.$file);

		if ($this->gzip)
		{
			if(!function_exists('gzopen'))
			{
				return $this->Error('Function &quot;gzopen&quot; is not available');
			}
			else
			{
				if ($mode == 'a' && !file_exists($file) && !$this->createEmptyGzipExtra($file))
				{
					return false;
				}
				$this->res = gzopen($file,$mode."b");
			}
		}
		else
		{
			$this->res = fopen($file,$mode."b");
		}

		return $this->res;
	}

	function close()
	{
		if ($this->mode == 'a')
			$this->flushBuffer();

		if ($this->gzip)
		{
			gzclose($this->res);

			if ($this->mode == 'a')
			{
				// добавим фактический размер всех несжатых данных в extra поле
				$f = fopen($this->file, 'rb+');
				fseek($f, 18);
				fwrite($f, pack("V", $this->ArchiveSizeCurrent));
				fclose($f);

				$this->dataSizeCache[$this->file] = $this->ArchiveSizeCurrent;

				// сохраним номер последней части в первый архив для многотомных архивов
				if (preg_match('#^(.+)\.([0-9]+)$#', $this->file, $regs))
				{
					$f = fopen($regs[1], 'rb+');
					fseek($f, 12);
					fwrite($f, 'LN'.pack("v",$regs[2]));
					fclose($f);
				}
			}
		}
		else
			fclose($this->res);
		clearstatcache();
	}

	public function getNextName($file = '')
	{
		if (!$file)
			$file = $this->file;

		static $CACHE;
		$c = &$CACHE[$file];

		if (!$c)
		{
			$l = strrpos($file, '.');
			$num = substr($file,$l+1);
			if (is_numeric($num))
				$file = substr($file,0,$l+1).++$num;
			else
				$file .= '.1';
			$c = $file;
		}
		return $c;
	}

	function checksum($s)
	{
		$chars = count_chars(substr($s,0,148).'        '.substr($s,156,356));
		$sum = 0;
		foreach($chars as $ch => $cnt)
			$sum += $ch*$cnt;
		return $sum;
	}

	function getDataSize($file)
	{
		$size = &$this->dataSizeCache[$file];
		if (!$size)
		{
			if (!file_exists($file))
				$size = false;
			else
			{
				if (preg_match('#\.gz(\.[0-9]+)?$#',$file))
				{
					$f = fopen($file, "rb");
					fseek($f, 16);
					if (fread($f, 2) == 'BX')
						$size = end(unpack("V", fread($f, 4)));
					else
					{
//						$this->Error('Wrong GZIP Extra Field');
						$size = false;
					}
					fclose($f);
				}
				else
					$size = filesize($file);
			}
		}

		return $size;
	}

	function Error($str = '', $code = '')
	{
		if ($code)
			$this->LastErrCode = $code;
		$this->err[] = $str;
		return false;
	}

	function ErrorAndSkip($str = '', $code = '')
	{
		$this->Error($str, $code);
		$this->SkipFile();
		if ($this->readHeader() === 0)
			$this->BlockHeader = $this->Block;
		return false;
	}

	public static function xmkdir($dir)
	{
		if (!file_exists($dir))
		{
			$upper_dir = dirname($dir);
			if (!file_exists($upper_dir) && !self::xmkdir($upper_dir))
				return false;

			return mkdir($dir);
		}

		return is_dir($dir);
	}

	function getEncryptKey()
	{
		if (!$this->EncryptKey)
			return false;
		static $key;
		if (!$key)
			$key = md5($this->EncryptKey);
		return $key;
	}

	function getFileInfo($f)
	{
		$f = str_replace('\\', '/', $f);
		$path = substr($f, strlen($this->path) + 1);

		$ar = array();

		if (is_dir($f))
		{
			$ar['type'] = 5;
			$path .= '/';
		}
		else
			$ar['type'] = 0;

		if (!$info = stat($f))
			return $this->Error('Can\'t get file info: '.$f);

		if ($info['size'] < 0)
			return $this->Error('File is too large: '.$f);

		$ar['mode'] = 0777 & $info['mode'];
		$ar['uid'] = $info['uid'];
		$ar['gid'] = $info['gid'];
		$ar['size'] = $ar['type']==5 ? 0 : $info['size'];
		$ar['mtime'] = $info['mtime'];
		$ar['filename'] = $this->prefix.$path;

		return $ar;
	}

	public static function getCheckword($key)
	{
		return md5('BITRIXCLOUDSERVICE'.$key);
	}

	public static function getFirstName($file)
	{
		return preg_replace('#\.[0-9]+$#','',$file);
	}

	// List of all ever used cipher methods
	// It is critical to use ECB family because crypto stream might be interrupted.
	public static function getCryptoAlgorithmList()
	{
		return ['aes-256-ecb', 'bf-ecb'];
	}

	public static function getDefaultCryptoAlgorithm()
	{
		return static::getCryptoAlgorithmList()[0];
	}

	public static function encrypt($data, $md5_key)
	{
		$m = strlen($data) % 16;
		if ($m)
		{
			$data .= str_repeat("\x00", 16 - $m);
		}

		return openssl_encrypt($data, static::getDefaultCryptoAlgorithm(), $md5_key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
	}

	public static function decrypt($data, $md5_key, $method = null)
	{
		if ($method === null)
		{
			$method = static::getDefaultCryptoAlgorithm();
		}
		$result = openssl_decrypt($data, $method, $md5_key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
		if ($result === false && $method === 'bf-ecb')
		{
			$result = static::bf_ecb_decrypt($md5_key, $data);
		}

		return $result;
	}

	protected static function bf_ecb_decrypt($key, $ciphertext)
	{
		static $sbox0 = [
			0xd1310ba6, 0x98dfb5ac, 0x2ffd72db, 0xd01adfb7, 0xb8e1afed, 0x6a267e96, 0xba7c9045, 0xf12c7f99,
			0x24a19947, 0xb3916cf7, 0x0801f2e2, 0x858efc16, 0x636920d8, 0x71574e69, 0xa458fea3, 0xf4933d7e,
			0x0d95748f, 0x728eb658, 0x718bcd58, 0x82154aee, 0x7b54a41d, 0xc25a59b5, 0x9c30d539, 0x2af26013,
			0xc5d1b023, 0x286085f0, 0xca417918, 0xb8db38ef, 0x8e79dcb0, 0x603a180e, 0x6c9e0e8b, 0xb01e8a3e,
			0xd71577c1, 0xbd314b27, 0x78af2fda, 0x55605c60, 0xe65525f3, 0xaa55ab94, 0x57489862, 0x63e81440,
			0x55ca396a, 0x2aab10b6, 0xb4cc5c34, 0x1141e8ce, 0xa15486af, 0x7c72e993, 0xb3ee1411, 0x636fbc2a,
			0x2ba9c55d, 0x741831f6, 0xce5c3e16, 0x9b87931e, 0xafd6ba33, 0x6c24cf5c, 0x7a325381, 0x28958677,
			0x3b8f4898, 0x6b4bb9af, 0xc4bfe81b, 0x66282193, 0x61d809cc, 0xfb21a991, 0x487cac60, 0x5dec8032,
			0xef845d5d, 0xe98575b1, 0xdc262302, 0xeb651b88, 0x23893e81, 0xd396acc5, 0x0f6d6ff3, 0x83f44239,
			0x2e0b4482, 0xa4842004, 0x69c8f04a, 0x9e1f9b5e, 0x21c66842, 0xf6e96c9a, 0x670c9c61, 0xabd388f0,
			0x6a51a0d2, 0xd8542f68, 0x960fa728, 0xab5133a3, 0x6eef0b6c, 0x137a3be4, 0xba3bf050, 0x7efb2a98,
			0xa1f1651d, 0x39af0176, 0x66ca593e, 0x82430e88, 0x8cee8619, 0x456f9fb4, 0x7d84a5c3, 0x3b8b5ebe,
			0xe06f75d8, 0x85c12073, 0x401a449f, 0x56c16aa6, 0x4ed3aa62, 0x363f7706, 0x1bfedf72, 0x429b023d,
			0x37d0d724, 0xd00a1248, 0xdb0fead3, 0x49f1c09b, 0x075372c9, 0x80991b7b, 0x25d479d8, 0xf6e8def7,
			0xe3fe501a, 0xb6794c3b, 0x976ce0bd, 0x04c006ba, 0xc1a94fb6, 0x409f60c4, 0x5e5c9ec2, 0x196a2463,
			0x68fb6faf, 0x3e6c53b5, 0x1339b2eb, 0x3b52ec6f, 0x6dfc511f, 0x9b30952c, 0xcc814544, 0xaf5ebd09,
			0xbee3d004, 0xde334afd, 0x660f2807, 0x192e4bb3, 0xc0cba857, 0x45c8740f, 0xd20b5f39, 0xb9d3fbdb,
			0x5579c0bd, 0x1a60320a, 0xd6a100c6, 0x402c7279, 0x679f25fe, 0xfb1fa3cc, 0x8ea5e9f8, 0xdb3222f8,
			0x3c7516df, 0xfd616b15, 0x2f501ec8, 0xad0552ab, 0x323db5fa, 0xfd238760, 0x53317b48, 0x3e00df82,
			0x9e5c57bb, 0xca6f8ca0, 0x1a87562e, 0xdf1769db, 0xd542a8f6, 0x287effc3, 0xac6732c6, 0x8c4f5573,
			0x695b27b0, 0xbbca58c8, 0xe1ffa35d, 0xb8f011a0, 0x10fa3d98, 0xfd2183b8, 0x4afcb56c, 0x2dd1d35b,
			0x9a53e479, 0xb6f84565, 0xd28e49bc, 0x4bfb9790, 0xe1ddf2da, 0xa4cb7e33, 0x62fb1341, 0xcee4c6e8,
			0xef20cada, 0x36774c01, 0xd07e9efe, 0x2bf11fb4, 0x95dbda4d, 0xae909198, 0xeaad8e71, 0x6b93d5a0,
			0xd08ed1d0, 0xafc725e0, 0x8e3c5b2f, 0x8e7594b7, 0x8ff6e2fb, 0xf2122b64, 0x8888b812, 0x900df01c,
			0x4fad5ea0, 0x688fc31c, 0xd1cff191, 0xb3a8c1ad, 0x2f2f2218, 0xbe0e1777, 0xea752dfe, 0x8b021fa1,
			0xe5a0cc0f, 0xb56f74e8, 0x18acf3d6, 0xce89e299, 0xb4a84fe0, 0xfd13e0b7, 0x7cc43b81, 0xd2ada8d9,
			0x165fa266, 0x80957705, 0x93cc7314, 0x211a1477, 0xe6ad2065, 0x77b5fa86, 0xc75442f5, 0xfb9d35cf,
			0xebcdaf0c, 0x7b3e89a0, 0xd6411bd3, 0xae1e7e49, 0x00250e2d, 0x2071b35e, 0x226800bb, 0x57b8e0af,
			0x2464369b, 0xf009b91e, 0x5563911d, 0x59dfa6aa, 0x78c14389, 0xd95a537f, 0x207d5ba2, 0x02e5b9c5,
			0x83260376, 0x6295cfa9, 0x11c81968, 0x4e734a41, 0xb3472dca, 0x7b14a94a, 0x1b510052, 0x9a532915,
			0xd60f573f, 0xbc9bc6e4, 0x2b60a476, 0x81e67400, 0x08ba6fb5, 0x571be91f, 0xf296ec6b, 0x2a0dd915,
			0xb6636521, 0xe7b9f9b6, 0xff34052e, 0xc5855664, 0x53b02d5d, 0xa99f8fa1, 0x08ba4799, 0x6e85076a,
		];
		static $sbox1 = [
			0x4b7a70e9, 0xb5b32944, 0xdb75092e, 0xc4192623, 0xad6ea6b0, 0x49a7df7d, 0x9cee60b8, 0x8fedb266,
			0xecaa8c71, 0x699a17ff, 0x5664526c, 0xc2b19ee1, 0x193602a5, 0x75094c29, 0xa0591340, 0xe4183a3e,
			0x3f54989a, 0x5b429d65, 0x6b8fe4d6, 0x99f73fd6, 0xa1d29c07, 0xefe830f5, 0x4d2d38e6, 0xf0255dc1,
			0x4cdd2086, 0x8470eb26, 0x6382e9c6, 0x021ecc5e, 0x09686b3f, 0x3ebaefc9, 0x3c971814, 0x6b6a70a1,
			0x687f3584, 0x52a0e286, 0xb79c5305, 0xaa500737, 0x3e07841c, 0x7fdeae5c, 0x8e7d44ec, 0x5716f2b8,
			0xb03ada37, 0xf0500c0d, 0xf01c1f04, 0x0200b3ff, 0xae0cf51a, 0x3cb574b2, 0x25837a58, 0xdc0921bd,
			0xd19113f9, 0x7ca92ff6, 0x94324773, 0x22f54701, 0x3ae5e581, 0x37c2dadc, 0xc8b57634, 0x9af3dda7,
			0xa9446146, 0x0fd0030e, 0xecc8c73e, 0xa4751e41, 0xe238cd99, 0x3bea0e2f, 0x3280bba1, 0x183eb331,
			0x4e548b38, 0x4f6db908, 0x6f420d03, 0xf60a04bf, 0x2cb81290, 0x24977c79, 0x5679b072, 0xbcaf89af,
			0xde9a771f, 0xd9930810, 0xb38bae12, 0xdccf3f2e, 0x5512721f, 0x2e6b7124, 0x501adde6, 0x9f84cd87,
			0x7a584718, 0x7408da17, 0xbc9f9abc, 0xe94b7d8c, 0xec7aec3a, 0xdb851dfa, 0x63094366, 0xc464c3d2,
			0xef1c1847, 0x3215d908, 0xdd433b37, 0x24c2ba16, 0x12a14d43, 0x2a65c451, 0x50940002, 0x133ae4dd,
			0x71dff89e, 0x10314e55, 0x81ac77d6, 0x5f11199b, 0x043556f1, 0xd7a3c76b, 0x3c11183b, 0x5924a509,
			0xf28fe6ed, 0x97f1fbfa, 0x9ebabf2c, 0x1e153c6e, 0x86e34570, 0xeae96fb1, 0x860e5e0a, 0x5a3e2ab3,
			0x771fe71c, 0x4e3d06fa, 0x2965dcb9, 0x99e71d0f, 0x803e89d6, 0x5266c825, 0x2e4cc978, 0x9c10b36a,
			0xc6150eba, 0x94e2ea78, 0xa5fc3c53, 0x1e0a2df4, 0xf2f74ea7, 0x361d2b3d, 0x1939260f, 0x19c27960,
			0x5223a708, 0xf71312b6, 0xebadfe6e, 0xeac31f66, 0xe3bc4595, 0xa67bc883, 0xb17f37d1, 0x018cff28,
			0xc332ddef, 0xbe6c5aa5, 0x65582185, 0x68ab9802, 0xeecea50f, 0xdb2f953b, 0x2aef7dad, 0x5b6e2f84,
			0x1521b628, 0x29076170, 0xecdd4775, 0x619f1510, 0x13cca830, 0xeb61bd96, 0x0334fe1e, 0xaa0363cf,
			0xb5735c90, 0x4c70a239, 0xd59e9e0b, 0xcbaade14, 0xeecc86bc, 0x60622ca7, 0x9cab5cab, 0xb2f3846e,
			0x648b1eaf, 0x19bdf0ca, 0xa02369b9, 0x655abb50, 0x40685a32, 0x3c2ab4b3, 0x319ee9d5, 0xc021b8f7,
			0x9b540b19, 0x875fa099, 0x95f7997e, 0x623d7da8, 0xf837889a, 0x97e32d77, 0x11ed935f, 0x16681281,
			0x0e358829, 0xc7e61fd6, 0x96dedfa1, 0x7858ba99, 0x57f584a5, 0x1b227263, 0x9b83c3ff, 0x1ac24696,
			0xcdb30aeb, 0x532e3054, 0x8fd948e4, 0x6dbc3128, 0x58ebf2ef, 0x34c6ffea, 0xfe28ed61, 0xee7c3c73,
			0x5d4a14d9, 0xe864b7e3, 0x42105d14, 0x203e13e0, 0x45eee2b6, 0xa3aaabea, 0xdb6c4f15, 0xfacb4fd0,
			0xc742f442, 0xef6abbb5, 0x654f3b1d, 0x41cd2105, 0xd81e799e, 0x86854dc7, 0xe44b476a, 0x3d816250,
			0xcf62a1f2, 0x5b8d2646, 0xfc8883a0, 0xc1c7b6a3, 0x7f1524c3, 0x69cb7492, 0x47848a0b, 0x5692b285,
			0x095bbf00, 0xad19489d, 0x1462b174, 0x23820e00, 0x58428d2a, 0x0c55f5ea, 0x1dadf43e, 0x233f7061,
			0x3372f092, 0x8d937e41, 0xd65fecf1, 0x6c223bdb, 0x7cde3759, 0xcbee7460, 0x4085f2a7, 0xce77326e,
			0xa6078084, 0x19f8509e, 0xe8efd855, 0x61d99735, 0xa969a7aa, 0xc50c06c2, 0x5a04abfc, 0x800bcadc,
			0x9e447a2e, 0xc3453484, 0xfdd56705, 0x0e1e9ec9, 0xdb73dbd3, 0x105588cd, 0x675fda79, 0xe3674340,
			0xc5c43465, 0x713e38d8, 0x3d28f89e, 0xf16dff20, 0x153e21e7, 0x8fb03d4a, 0xe6e39f2b, 0xdb83adf7,
		];
		static $sbox2 = [
			0xe93d5a68, 0x948140f7, 0xf64c261c, 0x94692934, 0x411520f7, 0x7602d4f7, 0xbcf46b2e, 0xd4a20068,
			0xd4082471, 0x3320f46a, 0x43b7d4b7, 0x500061af, 0x1e39f62e, 0x97244546, 0x14214f74, 0xbf8b8840,
			0x4d95fc1d, 0x96b591af, 0x70f4ddd3, 0x66a02f45, 0xbfbc09ec, 0x03bd9785, 0x7fac6dd0, 0x31cb8504,
			0x96eb27b3, 0x55fd3941, 0xda2547e6, 0xabca0a9a, 0x28507825, 0x530429f4, 0x0a2c86da, 0xe9b66dfb,
			0x68dc1462, 0xd7486900, 0x680ec0a4, 0x27a18dee, 0x4f3ffea2, 0xe887ad8c, 0xb58ce006, 0x7af4d6b6,
			0xaace1e7c, 0xd3375fec, 0xce78a399, 0x406b2a42, 0x20fe9e35, 0xd9f385b9, 0xee39d7ab, 0x3b124e8b,
			0x1dc9faf7, 0x4b6d1856, 0x26a36631, 0xeae397b2, 0x3a6efa74, 0xdd5b4332, 0x6841e7f7, 0xca7820fb,
			0xfb0af54e, 0xd8feb397, 0x454056ac, 0xba489527, 0x55533a3a, 0x20838d87, 0xfe6ba9b7, 0xd096954b,
			0x55a867bc, 0xa1159a58, 0xcca92963, 0x99e1db33, 0xa62a4a56, 0x3f3125f9, 0x5ef47e1c, 0x9029317c,
			0xfdf8e802, 0x04272f70, 0x80bb155c, 0x05282ce3, 0x95c11548, 0xe4c66d22, 0x48c1133f, 0xc70f86dc,
			0x07f9c9ee, 0x41041f0f, 0x404779a4, 0x5d886e17, 0x325f51eb, 0xd59bc0d1, 0xf2bcc18f, 0x41113564,
			0x257b7834, 0x602a9c60, 0xdff8e8a3, 0x1f636c1b, 0x0e12b4c2, 0x02e1329e, 0xaf664fd1, 0xcad18115,
			0x6b2395e0, 0x333e92e1, 0x3b240b62, 0xeebeb922, 0x85b2a20e, 0xe6ba0d99, 0xde720c8c, 0x2da2f728,
			0xd0127845, 0x95b794fd, 0x647d0862, 0xe7ccf5f0, 0x5449a36f, 0x877d48fa, 0xc39dfd27, 0xf33e8d1e,
			0x0a476341, 0x992eff74, 0x3a6f6eab, 0xf4f8fd37, 0xa812dc60, 0xa1ebddf8, 0x991be14c, 0xdb6e6b0d,
			0xc67b5510, 0x6d672c37, 0x2765d43b, 0xdcd0e804, 0xf1290dc7, 0xcc00ffa3, 0xb5390f92, 0x690fed0b,
			0x667b9ffb, 0xcedb7d9c, 0xa091cf0b, 0xd9155ea3, 0xbb132f88, 0x515bad24, 0x7b9479bf, 0x763bd6eb,
			0x37392eb3, 0xcc115979, 0x8026e297, 0xf42e312d, 0x6842ada7, 0xc66a2b3b, 0x12754ccc, 0x782ef11c,
			0x6a124237, 0xb79251e7, 0x06a1bbe6, 0x4bfb6350, 0x1a6b1018, 0x11caedfa, 0x3d25bdd8, 0xe2e1c3c9,
			0x44421659, 0x0a121386, 0xd90cec6e, 0xd5abea2a, 0x64af674e, 0xda86a85f, 0xbebfe988, 0x64e4c3fe,
			0x9dbc8057, 0xf0f7c086, 0x60787bf8, 0x6003604d, 0xd1fd8346, 0xf6381fb0, 0x7745ae04, 0xd736fccc,
			0x83426b33, 0xf01eab71, 0xb0804187, 0x3c005e5f, 0x77a057be, 0xbde8ae24, 0x55464299, 0xbf582e61,
			0x4e58f48f, 0xf2ddfda2, 0xf474ef38, 0x8789bdc2, 0x5366f9c3, 0xc8b38e74, 0xb475f255, 0x46fcd9b9,
			0x7aeb2661, 0x8b1ddf84, 0x846a0e79, 0x915f95e2, 0x466e598e, 0x20b45770, 0x8cd55591, 0xc902de4c,
			0xb90bace1, 0xbb8205d0, 0x11a86248, 0x7574a99e, 0xb77f19b6, 0xe0a9dc09, 0x662d09a1, 0xc4324633,
			0xe85a1f02, 0x09f0be8c, 0x4a99a025, 0x1d6efe10, 0x1ab93d1d, 0x0ba5a4df, 0xa186f20f, 0x2868f169,
			0xdcb7da83, 0x573906fe, 0xa1e2ce9b, 0x4fcd7f52, 0x50115e01, 0xa70683fa, 0xa002b5c4, 0x0de6d027,
			0x9af88c27, 0x773f8641, 0xc3604c06, 0x61a806b5, 0xf0177a28, 0xc0f586e0, 0x006058aa, 0x30dc7d62,
			0x11e69ed7, 0x2338ea63, 0x53c2dd94, 0xc2c21634, 0xbbcbee56, 0x90bcb6de, 0xebfc7da1, 0xce591d76,
			0x6f05e409, 0x4b7c0188, 0x39720a3d, 0x7c927c24, 0x86e3725f, 0x724d9db9, 0x1ac15bb4, 0xd39eb8fc,
			0xed545578, 0x08fca5b5, 0xd83d7cd3, 0x4dad0fc4, 0x1e50ef5e, 0xb161e6f8, 0xa28514d9, 0x6c51133c,
			0x6fd5c7e7, 0x56e14ec4, 0x362abfce, 0xddc6c837, 0xd79a3234, 0x92638212, 0x670efa8e, 0x406000e0,
		];
		static $sbox3 = [
			0x3a39ce37, 0xd3faf5cf, 0xabc27737, 0x5ac52d1b, 0x5cb0679e, 0x4fa33742, 0xd3822740, 0x99bc9bbe,
			0xd5118e9d, 0xbf0f7315, 0xd62d1c7e, 0xc700c47b, 0xb78c1b6b, 0x21a19045, 0xb26eb1be, 0x6a366eb4,
			0x5748ab2f, 0xbc946e79, 0xc6a376d2, 0x6549c2c8, 0x530ff8ee, 0x468dde7d, 0xd5730a1d, 0x4cd04dc6,
			0x2939bbdb, 0xa9ba4650, 0xac9526e8, 0xbe5ee304, 0xa1fad5f0, 0x6a2d519a, 0x63ef8ce2, 0x9a86ee22,
			0xc089c2b8, 0x43242ef6, 0xa51e03aa, 0x9cf2d0a4, 0x83c061ba, 0x9be96a4d, 0x8fe51550, 0xba645bd6,
			0x2826a2f9, 0xa73a3ae1, 0x4ba99586, 0xef5562e9, 0xc72fefd3, 0xf752f7da, 0x3f046f69, 0x77fa0a59,
			0x80e4a915, 0x87b08601, 0x9b09e6ad, 0x3b3ee593, 0xe990fd5a, 0x9e34d797, 0x2cf0b7d9, 0x022b8b51,
			0x96d5ac3a, 0x017da67d, 0xd1cf3ed6, 0x7c7d2d28, 0x1f9f25cf, 0xadf2b89b, 0x5ad6b472, 0x5a88f54c,
			0xe029ac71, 0xe019a5e6, 0x47b0acfd, 0xed93fa9b, 0xe8d3c48d, 0x283b57cc, 0xf8d56629, 0x79132e28,
			0x785f0191, 0xed756055, 0xf7960e44, 0xe3d35e8c, 0x15056dd4, 0x88f46dba, 0x03a16125, 0x0564f0bd,
			0xc3eb9e15, 0x3c9057a2, 0x97271aec, 0xa93a072a, 0x1b3f6d9b, 0x1e6321f5, 0xf59c66fb, 0x26dcf319,
			0x7533d928, 0xb155fdf5, 0x03563482, 0x8aba3cbb, 0x28517711, 0xc20ad9f8, 0xabcc5167, 0xccad925f,
			0x4de81751, 0x3830dc8e, 0x379d5862, 0x9320f991, 0xea7a90c2, 0xfb3e7bce, 0x5121ce64, 0x774fbe32,
			0xa8b6e37e, 0xc3293d46, 0x48de5369, 0x6413e680, 0xa2ae0810, 0xdd6db224, 0x69852dfd, 0x09072166,
			0xb39a460a, 0x6445c0dd, 0x586cdecf, 0x1c20c8ae, 0x5bbef7dd, 0x1b588d40, 0xccd2017f, 0x6bb4e3bb,
			0xdda26a7e, 0x3a59ff45, 0x3e350a44, 0xbcb4cdd5, 0x72eacea8, 0xfa6484bb, 0x8d6612ae, 0xbf3c6f47,
			0xd29be463, 0x542f5d9e, 0xaec2771b, 0xf64e6370, 0x740e0d8d, 0xe75b1357, 0xf8721671, 0xaf537d5d,
			0x4040cb08, 0x4eb4e2cc, 0x34d2466a, 0x0115af84, 0xe1b00428, 0x95983a1d, 0x06b89fb4, 0xce6ea048,
			0x6f3f3b82, 0x3520ab82, 0x011a1d4b, 0x277227f8, 0x611560b1, 0xe7933fdc, 0xbb3a792b, 0x344525bd,
			0xa08839e1, 0x51ce794b, 0x2f32c9b7, 0xa01fbac9, 0xe01cc87e, 0xbcc7d1f6, 0xcf0111c3, 0xa1e8aac7,
			0x1a908749, 0xd44fbd9a, 0xd0dadecb, 0xd50ada38, 0x0339c32a, 0xc6913667, 0x8df9317c, 0xe0b12b4f,
			0xf79e59b7, 0x43f5bb3a, 0xf2d519ff, 0x27d9459c, 0xbf97222c, 0x15e6fc2a, 0x0f91fc71, 0x9b941525,
			0xfae59361, 0xceb69ceb, 0xc2a86459, 0x12baa8d1, 0xb6c1075e, 0xe3056a0c, 0x10d25065, 0xcb03a442,
			0xe0ec6e0e, 0x1698db3b, 0x4c98a0be, 0x3278e964, 0x9f1f9532, 0xe0d392df, 0xd3a0342b, 0x8971f21e,
			0x1b0a7441, 0x4ba3348c, 0xc5be7120, 0xc37632d8, 0xdf359f8d, 0x9b992f2e, 0xe60b6f47, 0x0fe3f11d,
			0xe54cda54, 0x1edad891, 0xce6279cf, 0xcd3e7e6f, 0x1618b166, 0xfd2c1d05, 0x848fd2c5, 0xf6fb2299,
			0xf523f357, 0xa6327623, 0x93a83531, 0x56cccd02, 0xacf08162, 0x5a75ebb5, 0x6e163697, 0x88d273cc,
			0xde966292, 0x81b949d0, 0x4c50901b, 0x71c65614, 0xe6c6c7bd, 0x327a140a, 0x45e1d006, 0xc3f27b9a,
			0xc9aa53fd, 0x62a80f00, 0xbb25bfe2, 0x35bdd2f6, 0x71126905, 0xb2040222, 0xb6cbcf7c, 0xcd769c2b,
			0x53113ec0, 0x1640e3d3, 0x38abbd60, 0x2547adf0, 0xba38209c, 0xf746ce76, 0x77afa1c5, 0x20756060,
			0x85cbfe4e, 0x8ae88dd8, 0x7aaaf9b0, 0x4cf9aa7e, 0x1948c25c, 0x02fb8a8c, 0x01c36ae4, 0xd6ebe1f9,
			0x90d4f869, 0xa65cdea0, 0x3f09252d, 0xc208e69f, 0xb74e6132, 0xce77e25b, 0x578fdfe3, 0x3ac372e6,
		];
		static $parray = [
			0x243f6a88, 0x85a308d3, 0x13198a2e, 0x03707344, 0xa4093822, 0x299f31d0,
			0x082efa98, 0xec4e6c89, 0x452821e6, 0x38d01377, 0xbe5466cf, 0x34e90c6c,
			0xc0ac29b7, 0xc97c50dd, 0x3f84d5b5, 0xb5470917, 0x9216d5d9, 0x8979fb1b,
		];

		$encryptFast = function (int $x0, int $x1, array $sbox0, array $sbox1, array $sbox2, array $sbox3, array $p)
		{
			$x0 ^= $p[0];
			$x1 ^= ((($sbox0[($x0 & 0xFF000000) >> 24] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[1];
			$x0 ^= ((($sbox0[($x1 & 0xFF000000) >> 24] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[2];
			$x1 ^= ((($sbox0[($x0 & 0xFF000000) >> 24] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[3];
			$x0 ^= ((($sbox0[($x1 & 0xFF000000) >> 24] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[4];
			$x1 ^= ((($sbox0[($x0 & 0xFF000000) >> 24] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[5];
			$x0 ^= ((($sbox0[($x1 & 0xFF000000) >> 24] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[6];
			$x1 ^= ((($sbox0[($x0 & 0xFF000000) >> 24] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[7];
			$x0 ^= ((($sbox0[($x1 & 0xFF000000) >> 24] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[8];
			$x1 ^= ((($sbox0[($x0 & 0xFF000000) >> 24] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[9];
			$x0 ^= ((($sbox0[($x1 & 0xFF000000) >> 24] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[10];
			$x1 ^= ((($sbox0[($x0 & 0xFF000000) >> 24] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[11];
			$x0 ^= ((($sbox0[($x1 & 0xFF000000) >> 24] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[12];
			$x1 ^= ((($sbox0[($x0 & 0xFF000000) >> 24] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[13];
			$x0 ^= ((($sbox0[($x1 & 0xFF000000) >> 24] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[14];
			$x1 ^= ((($sbox0[($x0 & 0xFF000000) >> 24] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[15];
			$x0 ^= ((($sbox0[($x1 & 0xFF000000) >> 24] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[16];

			return [$x1 & 0xFFFFFFFF ^ $p[17], $x0 & 0xFFFFFFFF];
		};
		$encryptSlow = function(int $x0, int $x1, array $sbox0, array $sbox1, array $sbox2, array $sbox3, array $p)
		{
			// -16777216 == intval(0xFF000000) on 32-bit PHP installs
			$x0 ^= $p[0];
			$x1 ^= intval((intval($sbox0[(($x0 & -16777216) >> 24) & 0xFF] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[1];
			$x0 ^= intval((intval($sbox0[(($x1 & -16777216) >> 24) & 0xFF] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[2];
			$x1 ^= intval((intval($sbox0[(($x0 & -16777216) >> 24) & 0xFF] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[3];
			$x0 ^= intval((intval($sbox0[(($x1 & -16777216) >> 24) & 0xFF] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[4];
			$x1 ^= intval((intval($sbox0[(($x0 & -16777216) >> 24) & 0xFF] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[5];
			$x0 ^= intval((intval($sbox0[(($x1 & -16777216) >> 24) & 0xFF] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[6];
			$x1 ^= intval((intval($sbox0[(($x0 & -16777216) >> 24) & 0xFF] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[7];
			$x0 ^= intval((intval($sbox0[(($x1 & -16777216) >> 24) & 0xFF] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[8];
			$x1 ^= intval((intval($sbox0[(($x0 & -16777216) >> 24) & 0xFF] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[9];
			$x0 ^= intval((intval($sbox0[(($x1 & -16777216) >> 24) & 0xFF] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[10];
			$x1 ^= intval((intval($sbox0[(($x0 & -16777216) >> 24) & 0xFF] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[11];
			$x0 ^= intval((intval($sbox0[(($x1 & -16777216) >> 24) & 0xFF] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[12];
			$x1 ^= intval((intval($sbox0[(($x0 & -16777216) >> 24) & 0xFF] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[13];
			$x0 ^= intval((intval($sbox0[(($x1 & -16777216) >> 24) & 0xFF] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[14];
			$x1 ^= intval((intval($sbox0[(($x0 & -16777216) >> 24) & 0xFF] + $sbox1[($x0 & 0xFF0000) >> 16]) ^ $sbox2[($x0 & 0xFF00) >> 8]) + $sbox3[$x0 & 0xFF]) ^ $p[15];
			$x0 ^= intval((intval($sbox0[(($x1 & -16777216) >> 24) & 0xFF] + $sbox1[($x1 & 0xFF0000) >> 16]) ^ $sbox2[($x1 & 0xFF00) >> 8]) + $sbox3[$x1 & 0xFF]) ^ $p[16];

			return [$x1 ^ $p[17], $x0];
		};

		$bctx = [
			'p'  => [],
			'sb' => [
				$sbox0,
				$sbox1,
				$sbox2,
				$sbox3,
			],
		];

		$key  = array_values(unpack('C*', $key));
		$keyl = count($key);
		for ($j = 0, $i = 0; $i < 18; ++$i)
		{
			for ($data = 0, $k = 0; $k < 4; ++$k)
			{
				$data = ($data << 8) | $key[$j];
				if (++$j >= $keyl)
				{
					$j = 0;
				}
			}
			$bctx['p'][] = $parray[$i] ^ intval($data);
		}

		$data = "\0\0\0\0\0\0\0\0";
		for ($i = 0; $i < 18; $i += 2)
		{
			$in = unpack('N*', $data);
			$l = $in[1];
			$r = $in[2];

			[$r, $l] = PHP_INT_SIZE == 4 ?
				$encryptSlow($l, $r, $bctx['sb'][0], $bctx['sb'][1], $bctx['sb'][2], $bctx['sb'][3], $bctx['p']) :
				$encryptFast($l, $r, $bctx['sb'][0], $bctx['sb'][1], $bctx['sb'][2], $bctx['sb'][3], $bctx['p']);

			$data = pack("N*", $r, $l);

			[$l, $r] = array_values(unpack('N*', $data));
			$bctx['p'][$i	] = $l;
			$bctx['p'][$i + 1] = $r;
		}
		for ($i = 0; $i < 4; ++$i)
		{
			for ($j = 0; $j < 256; $j += 2)
			{
				$in = unpack('N*', $data);
				$l = $in[1];
				$r = $in[2];

				[$r, $l] = PHP_INT_SIZE == 4 ?
					$encryptSlow($l, $r, $bctx['sb'][0], $bctx['sb'][1], $bctx['sb'][2], $bctx['sb'][3], $bctx['p']) :
					$encryptFast($l, $r, $bctx['sb'][0], $bctx['sb'][1], $bctx['sb'][2], $bctx['sb'][3], $bctx['p']);

				$data = pack("N*", $r, $l);

				[$l, $r] = array_values(unpack('N*', $data));
				$bctx['sb'][$i][$j	] = $l;
				$bctx['sb'][$i][$j + 1] = $r;
			}
		}

		$block_size = 8;
		$result = '';
		for ($i = 0; $i < strlen($ciphertext); $i += $block_size)
		{
			$p = $bctx['p'];
			$sb_0 = $bctx['sb'][0];
			$sb_1 = $bctx['sb'][1];
			$sb_2 = $bctx['sb'][2];
			$sb_3 = $bctx['sb'][3];

			$in = unpack('N*', substr($ciphertext, $i, $block_size));
			$l = $in[1];
			$r = $in[2];

			for ($j = 17; $j > 2; $j -= 2)
			{
				$l ^= $p[$j];
				$r ^= intval((intval($sb_0[$l >> 24 & 0xff] + $sb_1[$l >> 16 & 0xff])
					^ $sb_2[$l >> 8 & 0xff]) + $sb_3[$l & 0xff]
				);

				$r ^= $p[$j - 1];
				$l ^= intval((intval($sb_0[$r >> 24 & 0xff] + $sb_1[$r >> 16 & 0xff])
					^ $sb_2[$r >> 8 & 0xff]) + $sb_3[$r & 0xff]
				);
			}
			$result .= pack('N*', $r ^ $p[0], $l ^ $p[1]);
		}

		return $result;
	}

	# }
	##############
}

class CTarCheck extends CTar
{
	function extractFile()
	{
		$header = $this->readHeader();
		if($header === false || $header === 0)
			return $header;

		return $this->SkipFile();
	}
}

class CloudDownload
{
	function __construct($id)
	{
		$this->id = $id;
		$this->last_bucket_path = '';
		$this->arSkipped = array();
		$this->path = '';
		$this->download_cnt = 0;
		$this->download_size = 0;

		$this->obBucket = new CCloudStorageBucket($id);
		if (!$this->obBucket->Init())
			return;
	}

	function Scan($path)
	{
		$this->path = $path;

		if ($arCloudFiles = CBackup::GetBucketFileList($this->id, $path))
		{
			foreach($arCloudFiles['file'] as $k=>$file)
			{
				if ($this->last_bucket_path)
				{
					if ($path.'/'.$file == $this->last_bucket_path)
						$this->last_bucket_path = '';
					else
						continue;
				}

				$name = $this->path = $path.'/'.$file;
				if (!haveTime()) // Сохраняется путь файла, который еще предстоит сохранить, TODO: пошаговое скачивание больших файлов
					return false;

				$http = new \Bitrix\Main\Web\HttpClient();
				if ($http->download($this->obBucket->GetFileSRC(array("URN" => $name)), DOCUMENT_ROOT.BX_ROOT.'/backup/clouds/'.$this->id.$name))
				{
					$this->download_size += $arCloudFiles['file_size'][$k];
					$this->download_cnt++;
				}
				else
					$this->arSkipped[] = $name;
			}
		}

		foreach($arCloudFiles['dir'] as $dir)
		{
			if ($this->last_bucket_path)
			{
				if ($path.'/'.$dir == $this->last_bucket_path)
					$this->last_bucket_path = '';
				elseif (mb_strpos($this->last_bucket_path, $path.'/'.$dir) !== 0)
					continue;
			}

			if ($path.'/'.$dir == '/bitrix/backup')
				continue;

			if ($path.'/'.$dir == '/tmp')
				continue;

			if (!$this->Scan($path.'/'.$dir)) // partial
				return false;
		}

		return true;
	}
}

function HumanTime($t)
{
	$ar = array(GetMessage('TIME_S'),GetMessage('TIME_M'),GetMessage('TIME_H'));
	if ($t < 60)
		return sprintf('%d '.$ar[0], $t);
	if ($t < 3600)
		return sprintf('%d '.$ar[1], floor($t/60));
//		return sprintf('%d '.$ar[1].' %d '.$ar[0], floor($t/60), $t%60);
	return sprintf('%d '.$ar[2].' %d '.$ar[1], floor($t/3600), floor($t%3600/60));
//	return sprintf('%d '.$ar[2].' %d '.$ar[1].' %d '.$ar[0], floor($t/3600), floor($t%3600/60), $t%60);
}
