<?
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
			if (count($arRes))
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

		if ($CACHE[$BUCKET_ID])
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
		if (!IntOption('skip_mask'))
			return false;

		global $skip_mask_array;

		$path = mb_substr($abs_path, mb_strlen(self::$DOCUMENT_ROOT_SITE));
		$path = str_replace('\\','/',$path);
		
		static $preg_mask_array;
		if (!$preg_mask_array)
		{
			$preg_mask_array = array();
			foreach($skip_mask_array as $a)
				$preg_mask_array[] = CBackup::_preg_escape($a);
		}

		reset($skip_mask_array);
		foreach($skip_mask_array as $k => $mask)
		{
			if (mb_strpos($mask, '/') === 0) // absolute path
			{
				if (mb_strpos($mask, '*') === false) // нет звездочки
				{
					if (mb_strpos($path.'/', $mask.'/') === 0)
						return true;
				}
				elseif (preg_match('#^'.str_replace('*','[^/]*?',$preg_mask_array[$k]).'$#i',$path))
					return true;
			}
			elseif (mb_strpos($mask, '/') === false)
			{
				if (mb_strpos($mask, '*') === false)
				{
					if (mb_substr($path, -mb_strlen($mask)) == $mask)
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

		$arc_name .= '_'.mb_substr(md5(uniqid(rand(), true)), 0, 8);
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

					if(count($arIndexes) > 0)
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
				if($arTable["KEY_COLUMN"])
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

					$arState['TABLES'][$table]['LAST_ID'] = $arTable['LAST_ID'] = $arTable["KEY_COLUMN"] ? $arSource[$arTable["KEY_COLUMN"]] : ++$i;

					if (CTar::strlen($strInsert) > 1000000)
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

	public function QueryUnbuffered($q)
	{
		global $DB;
		if (defined('BX_USE_MYSQLI') && BX_USE_MYSQLI === true)
			$DB->result = mysqli_query($DB->db_Conn, $q, MYSQLI_USE_RESULT);
		else
			$DB->result = mysql_unbuffered_query($q, $DB->db_Conn);
		$rsSource = new CDBResult($DB->result);
		$rsSource->DB = $DB;
		return $rsSource;
	}

	public function FreeResult()
	{
		global $DB;
		if (defined('BX_USE_MYSQLI') && BX_USE_MYSQLI === true)
			mysqli_free_result($DB->result);
		else
			mysql_free_result($DB->result);
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

		$c = CTar::strlen($str);
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
			$l = mb_strrpos($file, '.');
			$num = CTar::substr($file,$l+1);
			if (is_numeric($num))
				$file = CTar::substr($file,0,$l+1).++$num;
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
					unset($this->startPath);
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

				$arInfo['size'] = CTar::strlen($strFile);
				if (!$tar->writeHeader($arInfo))
					return false;

				$i = 0;
				while($i < $arInfo['size'])
				{
					if (!$tar->writeBlock(pack("a512",CTar::substr($strFile,$i,512))))
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
					unset($this->startPath);
				return false;
			}
			else
				return true;
		}
		elseif ($this->arSkip[$f])
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
		if (!function_exists('mcrypt_encrypt') && !function_exists('openssl_encrypt'))
			return false;
		return true;
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

			$l = CTar::strlen($key);
			if ($l > 56)
				$key = CTar::substr($key, 0, 56);
			elseif ($l < 16)
				$key = str_repeat($key, ceil(16/$l));
		}

		return $key;
	}

	public static function Set($strName, $strVal)
	{
		if (!self::Init())
			return false;

		$temporary_cache = '';
		if ($strVal)
			$temporary_cache = CTar::encrypt(self::SIGN.$strVal, self::getEncryptKey());
		return COption::SetOptionString('main', $strName, base64_encode($temporary_cache));
	}

	public static function Get($strName)
	{
		if (!self::Init())
			return false;

		$temporary_cache = base64_decode(COption::GetOptionString('main', $strName, ''));
		$pass = CTar::decrypt($temporary_cache, self::getEncryptKey());
		if (CTar::substr($pass, 0, 6) == self::SIGN)
			return CTar::substr(preg_replace('#\x00+$#', '', $pass), 6);
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
	var $prefix = '';

	##############
	# READ
	# {
	function openRead($file)
	{
		if (!isset($this->gzip) && (self::substr($file,-3)=='.gz' || self::substr($file,-4)=='.tgz'))
			$this->gzip = true;

		$this->BufferSize = 51200;

		if ($this->open($file, 'r'))
		{
			if ('' !== $str = $this->gzip ? gzread($this->res,512) : fread($this->res,512))
			{
				$data = unpack("a100empty/a90signature/a10version/a56tail/a256enc", $str);
				if (trim($data['signature']) != self::BX_SIGNATURE)
				{
					if (self::strlen($this->EncryptKey))
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
				$this->BlockHeader = $this->Block = 1;

				if (!$key || self::substr($str, 0, 256) != self::decrypt($data['enc'], $key))
					return $this->Error('Invalid encryption key', 'ENC_KEY');
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
				$str = self::decrypt($str, $key);
			$this->Buffer = $str;
		}

		$str = '';
		if ($this->Buffer)
		{
			$str = self::substr($this->Buffer, 0, 512);
			$this->Buffer = self::substr($this->Buffer, 512);
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

		if (self::strlen($this->Buffer) > $toSkip)
		{
			$this->Buffer = self::substr($this->Buffer, $toSkip);
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
			if (!($l = self::strlen($str = $this->readBlock($bIgnoreOpenNextError = true))))
				return 0; // finish
		}

		if (!$Long)
			$this->BlockHeader = $this->Block - 1;

		if ($l != 512)
			return $this->Error('Wrong block size: '.self::strlen($str).' (block '.$this->Block.')');


		$data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix", $str);
		$chk = $data['devmajor'].$data['devminor'];

		if (!is_numeric(trim($data['checksum'])) || $chk!='' && $chk!=0)
			return $this->Error('Archive is corrupted, wrong block: '.($this->Block-1).', file: '.$this->file.', md5sum: '.md5_file($this->file));

		$header['filename'] = trim(trim($data['prefix'], "\x00").'/'.trim($data['filename'], "\x00"),'/');
		$header['mode'] = OctDec($data['mode']);
		$header['uid'] = OctDec($data['uid']);
		$header['gid'] = OctDec($data['gid']);
		$header['size'] = OctDec($data['size']);
		$header['mtime'] = OctDec($data['mtime']);
		$header['type'] = trim($data['type'], "\x00");
//		$header['link'] = $data['link'];

		if (self::strpos($header['filename'],'./') === 0)
			$header['filename'] = self::substr($header['filename'], 2);

		if ($header['type']=='L') // Long header
		{
			$filename = '';
			$n = ceil($header['size']/512);
			for ($i = 0; $i < $n; $i++)
				$filename .= $this->readBlock();

			if (!is_array($header = $this->readHeader($Long = true)))
				return $this->Error('Wrong long header, block: '.$this->Block);
			$header['filename'] = self::substr($filename,0,self::strpos($filename,chr(0)));
		}
		
		if (self::strpos($header['filename'],'/') === 0) // trailing slash
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
					$contents = self::substr($contents, 0, $chunk);

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
		if (!isset($this->gzip) && (self::substr($file,-3)=='.gz' || self::substr($file,-4)=='.tgz'))
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
			return $this->Error('Can\'t get data size: '.$file);

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
				return $this->Error('Error writing to file');
			$this->Block = 1;
			$this->ArchiveSizeCurrent = 512;
		}
		return $res;
	}

	// создадим пустой gzip с экстра полем
	function createEmptyGzipExtra($file)
	{
		if (file_exists($file))
			return $this->Error('File already exists: '.$file);

		if (!($f = gzopen($file,'wb')))
			return $this->Error('Can\'t open file: '.$file);
		gzwrite($f,'');
		gzclose($f);

		// $data = file_get_contents($file);
		$data = "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\x03\x00\x00\x00\xff\xff\x03\x00\x00\x00\x00\x00\x00\x00\x00\x00"; // buggy zlib 1.2.7


		if (!($f = fopen($file, 'w')))
			return $this->Error('Can\'t open file for writing: '.$file);

		$ar = unpack('A3bin0/A1FLG/A6bin1',self::substr($data,0,10));
		if ($ar['FLG'] != 0)
			return $this->Error('Error writing extra field: already exists');

		$EXTRA = "\x00\x00\x00\x00".self::BX_EXTRA; // 10 байт
		fwrite($f,$ar['bin0']."\x04".$ar['bin1'].chr(self::strlen($EXTRA))."\x00".$EXTRA.self::substr($data,10));
		fclose($f);
		return true;
	}

	function writeBlock($str)
	{
		$l = self::strlen($str);
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

		if (self::strlen($this->Buffer) == $this->BufferSize)
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
		$header1 = pack("a1a100a6a2a32a32a8a8a155", $ar['type'],'','','','','','', '', $ar['prefix']);

		$checksum = pack("a8",decoct($this->checksum($header0.'        '.$header1)));
		$header = pack("a512", $header0.$checksum.$header1);
		return $this->writeBlock($header) || $this->Error('Error writing header');
	}

	function addFile($f)
	{
		$f = str_replace('\\', '/', $f);
		$path = $this->prefix.self::substr($f,self::strlen($this->path) + 1);
		if ($path == '')
			return true;
		if (self::strlen($path)>512)
			return $this->Error('Path is too long: '.$path);
		if (is_link($f) && !file_exists($f)) // broken link
			return true;

		if (!$ar = $this->getFileInfo($f))
			return false;

		if ($this->ReadBlockCurrent == 0) // read from start
		{
			$this->ReadFileSize = $ar['size'];
			if (self::strlen($path) > 100) // Long header
			{
				$ar0 = $ar;
				$ar0['type'] = 'L';
				$ar0['filename'] = '././@LongLink';
				$ar0['size'] = self::strlen($path);
				if (!$this->writeHeader($ar0))
					return $this->Error('Can\'t write header to file: '.$this->file);

				if (!$this->writeBlock(pack("a512",$path)))
					return $this->Error('Can\'t write to file: '.$this->file);

				$ar['filename'] = self::substr($path,0,100);
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
					$str = pack("a512", $str);
				elseif (self::strlen($str) != 512)
					return $this->Error('Error reading from file: '.$f);

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

		if ($this->EncryptKey && !function_exists('mcrypt_encrypt') && !function_exists('openssl_encrypt'))
			return $this->Error('Function mcrypt_encrypt/openssl_encrypt is not available');
		
		if ($mode == 'r' && !file_exists($file))
			return $this->Error('File does not exist: '.$file);

		if ($this->gzip)
		{
			if(!function_exists('gzopen'))
				return $this->Error('Function &quot;gzopen&quot; is not available');
			else
			{
				if ($mode == 'a' && !file_exists($file) && !$this->createEmptyGzipExtra($file))
					return false;
				$this->res = gzopen($file,$mode."b");
			}
		}
		else
			$this->res = fopen($file,$mode."b");

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
			$l = mb_strrpos($file, '.');
			$num = self::substr($file,$l+1);
			if (is_numeric($num))
				$file = self::substr($file,0,$l+1).++$num;
			else
				$file .= '.1';
			$c = $file;
		}
		return $c;
	}

	function checksum($s)
	{
		$chars = count_chars(self::substr($s,0,148).'        '.self::substr($s,156,356));
		$sum = 0;
		foreach($chars as $ch => $cnt)
			$sum += $ch*$cnt;
		return $sum;
	}

	public static function substr($s, $a, $b = null)
	{
		if (function_exists('mb_orig_substr'))
			return $b === null ? mb_orig_substr($s, $a) : mb_orig_substr($s, $a, $b);
		return $b === null? mb_substr($s, $a) : mb_substr($s, $a, $b);
	}

	public static function strlen($s)
	{
		if (function_exists('mb_orig_strlen'))
			return mb_orig_strlen($s);
		return mb_strlen($s);
	}

	public static function strpos($s, $a)
	{
		if (function_exists('mb_orig_strpos'))
			return mb_orig_strpos($s, $a);
		return mb_strpos($s, $a);
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
		$path = self::substr($f,self::strlen($this->path) + 1);

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

	public static function encrypt($data, $md5_key)
	{
		if ($m = self::strlen($data)%8)
			$data .= str_repeat("\x00",  8 - $m);
		if (function_exists('openssl_encrypt'))
			return openssl_encrypt($data, 'BF-ECB', $md5_key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
		else
			return mcrypt_encrypt(MCRYPT_BLOWFISH, $md5_key, $data, MCRYPT_MODE_ECB);
	}

	public static function decrypt($data, $md5_key)
	{
		if (function_exists('openssl_decrypt'))
			$val = openssl_decrypt($data, 'BF-ECB', $md5_key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
		else
			$val = mcrypt_decrypt(MCRYPT_BLOWFISH, $md5_key, $data, MCRYPT_MODE_ECB);
		return $val;
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

				$HTTP = new CHTTP;
				if ($HTTP->Download($this->obBucket->GetFileSRC(array("URN" => $name)), DOCUMENT_ROOT.BX_ROOT.'/backup/clouds/'.$this->id.$name))
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
