<?php

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class CPerfomanceMeasure
{
	protected static function noOp(&$k)
	{
	}

	protected static function oneOp(&$k)
	{
		$k++;
		$k--;
		$k++;
		$k--;
	}

	public static function GetPHPCPUMark()
	{
		$k = 0;
		$res = [];
		for ($j = 0; $j < 4; $j++)
		{
			$m1 = microtime(true);
			for ($i = 0; $i < 100000; $i++)
			{
				static::noOp($k);
			}

			$m2 = microtime(true);
			for ($i = 0; $i < 100000; $i++)
			{
				static::oneOp($k);
			}

			$m3 = microtime(true);
			if ($m1 <= $m2 && $m2 <= $m3)
			{
				$N1 = $m2 - $m1;
				$N2 = $m3 - $m2;

				if ($N2 > $N1)
				{
					$res[] = 1 / ($N2 - $N1);
				}
			}
		}

		if (count($res))
		{
			return array_sum($res) / doubleval(count($res));
		}

		return 0;
	}

	public static function GetPHPFilesMark()
	{
		$res = [];
		$file_name = $_SERVER['DOCUMENT_ROOT'] . '/' . \Bitrix\Main\Config\Option::get('main', 'upload_dir', '/upload/') . '/perfmon#i#.php';
		$content = "<?\$s='" . str_repeat('x', 1024) . "';?><?/*" . str_repeat('y', 1024) . "*/?><?\$r='" . str_repeat('z', 1024) . "';?>";

		for ($j = 0; $j < 4; $j++)
		{
			$s1 = microtime(true);
			for ($i = 0; $i < 100; $i++)
			{
				$fn = str_replace('#i#', $i, $file_name);
			}
			$e1 = microtime(true);
			$N1 = $e1 - $s1;

			$s2 = microtime(true);
			for ($i = 0; $i < 100; $i++)
			{
				//This is one op
				$fn = str_replace('#i#', $i, $file_name);
				$fh = fopen($fn, 'wb');
				fwrite($fh, $content);
				fclose($fh);
				include $fn;
				unlink($fn);
			}
			$e2 = microtime(true);
			$N2 = $e2 - $s2;

			if ($N2 > $N1)
			{
				$res[] = 100 / ($N2 - $N1);
			}
		}

		if (count($res))
		{
			return array_sum($res) / doubleval(count($res));
		}

		return 0;
	}

	public static function GetPHPMailMark()
	{
		$addr = 'hosting_test@bitrix.ru';
		$subj = 'Bitrix server test';
		$body = 'This is test message. Delete it.';

		$s1 = microtime(true);
		bxmail($addr, $subj, $body);
		$e1 = microtime(true);

		return $e1 - $s1;
	}

	public static function GetDBMark($type)
	{
		global $DB;

		$res = [];
		switch ($type)
		{
			case 'read':
				$strSql = 'select * from b_perf_test WHERE ID = #i#';
				$bFetch = true;
				break;
			case 'update':
				$strSql = "update b_perf_test set REFERENCE_ID = ID+1, NAME = '" . str_repeat('y', 200) . "' WHERE ID = #i#";
				$bFetch = false;
				break;
			default:
				$DB->Query('truncate table b_perf_test');
				$strSql = "insert into b_perf_test (REFERENCE_ID, NAME) values (#i#-1, '" . str_repeat('x', 200) . "')";
				$bFetch = false;
		}

		for ($j = 0; $j < 4; $j++)
		{
			$s1 = microtime(true);
			for ($i = 0; $i < 100; $i++)
			{
				$sql = str_replace('#i#', $i, $strSql);
			}
			$e1 = microtime(true);
			$N1 = $e1 - $s1;

			$s2 = microtime(true);
			for ($i = 0; $i < 100; $i++)
			{
				//This is one op
				$sql = str_replace('#i#', $i, $strSql);
				$rs = $DB->Query($sql);
				if ($bFetch)
				{
					$rs->Fetch();
				}
			}
			$e2 = microtime(true);
			$N2 = $e2 - $s2;

			if ($N2 > $N1)
			{
				$res[] = 100 / ($N2 - $N1);
			}
		}

		if (count($res))
		{
			return array_sum($res) / doubleval(count($res));
		}
	}

	public static function GetAccelerator()
	{
		$accelerators = self::GetAllAccelerators();
		if ($accelerators)
		{
			return $accelerators[0];
		}

		return false;
	}

	public static function GetAllAccelerators()
	{
		$result = [];
		if (extension_loaded('Zend OPcache'))
		{
			$result[] = new CPerfAccelZendOpCache;
		}

		return $result;
	}
}

class CPerfAccel
{
	public $enabled;
	public $cache_ttl;
	public $max_file_size;
	public $check_mtime;
	public $memory_total;
	public $memory_used;
	public $cache_limit;

	public function __construct($enabled, $cache_ttl, $max_file_size, $check_mtime, $memory_total, $memory_used, $cache_limit = -1)
	{
		$this->enabled = $enabled;
		$this->cache_ttl = $cache_ttl;
		$this->max_file_size = $max_file_size;
		$this->check_mtime = $check_mtime;
		$this->memory_total = $memory_total;
		$this->memory_used = $memory_used;
		$this->cache_limit = $cache_limit;
	}

	public function GetParams()
	{
		return [];
	}

	public function IsWorking()
	{
		if (!$this->enabled)
		{
			return false;
		}

		if ($this->cache_ttl == 0)
		{
			return false;
		}

		if ($this->max_file_size >= 0)
		{
			if ($this->max_file_size < 4 * 1024 * 1024)
			{
				return false;
			}
		}

		if (!$this->check_mtime)
		{
			return false;
		}

		if ($this->memory_used >= 0)
		{
			//Check for 10% free
			if (($this->memory_used / $this->memory_total) > 0.9)
			{
				return false;
			}
		}
		else
		{
			//Or at least 40M total when no used memory stat available
			if ($this->memory_total < 40 * 1024 * 1024)
			{
				return false;
			}
		}

		if ($this->cache_limit == 0)
		{
			return false;
		}

		return true;
	}

	public function GetRecommendations()
	{
		$arResult = [];
		$arParams = $this->GetParams();

		if (array_key_exists('enabled', $arParams))
		{
			$is_ok = $this->enabled;
			foreach ($arParams['enabled'] as $ar)
			{
				if (!isset($ar['IS_OK']))
				{
					$ar['IS_OK'] = $is_ok;
				}
				$arResult[] = $ar;
			}
		}

		if (array_key_exists('cache_ttl', $arParams))
		{
			$is_ok = $this->cache_ttl != 0;
			foreach ($arParams['cache_ttl'] as $ar)
			{
				if (!isset($ar['IS_OK']))
				{
					$ar['IS_OK'] = $is_ok;
				}
				$arResult[] = $ar;
			}
		}

		if (array_key_exists('max_file_size', $arParams) && $this->max_file_size >= 0)
		{
			$is_ok = $this->max_file_size >= 4 * 1024 * 1024;
			foreach ($arParams['max_file_size'] as $ar)
			{
				if (!isset($ar['IS_OK']))
				{
					$ar['IS_OK'] = $is_ok;
				}
				$arResult[] = $ar;
			}
		}

		if (array_key_exists('check_mtime', $arParams))
		{
			$is_ok = $this->check_mtime;
			foreach ($arParams['check_mtime'] as $ar)
			{
				if (!isset($ar['IS_OK']))
				{
					$ar['IS_OK'] = $is_ok;
				}
				$arResult[] = $ar;
			}
		}

		if (array_key_exists('memory_pct', $arParams) && $this->memory_used >= 0)
		{
			if ($this->memory_total > 0)
			{
				//Check for 10% free
				$is_ok = ($this->memory_used / $this->memory_total) <= 0.9;
				foreach ($arParams['memory_pct'] as $ar)
				{
					$arResult[] = [
						'PARAMETER' => $ar['PARAMETER'],
						'VALUE' => Loc::getMessage(
							'PERFMON_MEASURE_MEMORY_USAGE',
							['#percent#' => number_format(($this->memory_used / $this->memory_total) * 100, 2)]
						),
						'RECOMMENDATION' => Loc::getMessage('PERFMON_MEASURE_CACHE_REC'),
						'IS_OK' => $is_ok,
					];
				}
			}
			else
			{
				foreach ($arParams['memory_pct'] as $ar)
				{
					$arResult[] = [
						'PARAMETER' => $ar['PARAMETER'],
						'VALUE' => '',
						'RECOMMENDATION' => Loc::getMessage('PERFMON_MEASURE_GREATER_THAN_ZERO_REC'),
						'IS_OK' => false,
					];
				}
			}
		}
		elseif (array_key_exists('memory_abs', $arParams))
		{
			//Or at least 40M total when no used memory stat available
			$is_ok = $this->memory_total >= 40 * 1024 * 1024;
			foreach ($arParams['memory_abs'] as $ar)
			{
				if (!isset($ar['IS_OK']))
				{
					$ar['IS_OK'] = $is_ok;
				}
				$arResult[] = $ar;
			}
		}

		if (array_key_exists('cache_limit', $arParams))
		{
			$is_ok = $this->cache_limit != 0;
			foreach ($arParams['cache_limit'] as $ar)
			{
				if (!isset($ar['IS_OK']))
				{
					$ar['IS_OK'] = $is_ok;
				}
				$arResult[] = $ar;
			}
		}

		return $arResult;
	}

	public static function unformat($str)
	{
		$str = mb_strtolower($str);
		$res = intval($str);
		$suffix = mb_substr($str, -1);
		if ($suffix === 'k')
		{
			$res *= 1024;
		}
		elseif ($suffix === 'm')
		{
			$res *= 1048576;
		}
		elseif ($suffix === 'g')
		{
			$res *= 1048576 * 1024;
		}

		return $res;
	}
}

class CPerfAccelZendOpCache extends CPerfAccel
{
	public function __construct()
	{
		$memory = [
			'memorySize' => intval(ini_get('opcache.memory_consumption')) * 1024 * 1024,
			'memoryAllocated' => -1,
		];

		parent::__construct(
			ini_get('opcache.enable') != '0',
			-1,
			-1,
			ini_get('opcache.validate_timestamps') != '0',
			$memory['memorySize'],
			$memory['memoryAllocated'],
			-1
		);
	}

	public function GetRecommendations()
	{
		$arResult = parent::GetRecommendations();

		if (extension_loaded('Zend OPcache'))
		{
			$max_accelerated_files = intval(ini_get('opcache.max_accelerated_files'));
			$rec_accelerated_files = 100000;
			$is_ok = ($max_accelerated_files >= $rec_accelerated_files);

			array_unshift($arResult, [
				'PARAMETER' => 'opcache.max_accelerated_files',
				'IS_OK' => $is_ok,
				'VALUE' => $max_accelerated_files,
				'RECOMMENDATION' => Loc::getMessage('PERFMON_MEASURE_EQUAL_OR_GREATER_THAN_REC', ['#value#' => $rec_accelerated_files]),
			]);

			if (function_exists('opcache_get_status'))
			{
				$cacheStatus = opcache_get_status(false);
				$cachedKeys = intval($cacheStatus['opcache_statistics']['num_cached_keys']);
				$maxKeys = intval($cacheStatus['opcache_statistics']['max_cached_keys']);
				$is_ok = ($cachedKeys <= 0) || ($maxKeys <= 0) || ($cachedKeys < $maxKeys);

				if (!$is_ok)
				{
					array_unshift($arResult, [
						'PARAMETER' => 'opcache.max_accelerated_files',
						'IS_OK' => $is_ok,
						'VALUE' => $maxKeys,
						'RECOMMENDATION' => Loc::getMessage('PERFMON_MEASURE_EQUAL_OR_GREATER_THAN_REC', ['#value#' => $cachedKeys]),
					]);
				}
			}
		}

		return $arResult;
	}

	public function GetParams()
	{
		$res = [
			'enabled' => [
				[
					'PARAMETER' => 'opcache.enable',
					'VALUE' => ini_get('opcache.enable'),
					'RECOMMENDATION' => Loc::getMessage('PERFMON_MEASURE_SET_REC', ['#value#' => '1']),
				]
			],
			'check_mtime' => [
				[
					'PARAMETER' => 'opcache.validate_timestamps',
					'VALUE' => ini_get('opcache.validate_timestamps'),
					'RECOMMENDATION' => Loc::getMessage('PERFMON_MEASURE_SET_REC', ['#value#' => '1']),
				]
			],
			'memory_abs' => [
				[
					'PARAMETER' => 'opcache.memory_consumption',
					'VALUE' => ini_get('opcache.memory_consumption'),
					'RECOMMENDATION' => Loc::getMessage('PERFMON_MEASURE_EQUAL_OR_GREATER_THAN_REC', ['#value#' => '40']),
				]
			],
			'max_file_size' => [
				[
					'PARAMETER' => 'opcache.max_file_size',
					'VALUE' => ini_get('opcache.max_file_size'),
					'RECOMMENDATION' => Loc::getMessage('PERFMON_MEASURE_SET_REC', ['#value#' => '0']),
				]
			],
		];

		if (function_exists('opcache_get_status'))
		{
			$conf = opcache_get_status(false);
			$res['memory_abs'][] = [
				'PARAMETER' => 'opcache.memory_usage.used_memory',
				'VALUE' => CFile::FormatSize($conf['memory_usage']['used_memory']),
				'RECOMMENDATION' => '',
			];

			$res['memory_abs'][] = [
				'PARAMETER' => 'opcache.memory_usage.free_memory',
				'VALUE' => CFile::FormatSize($conf['memory_usage']['free_memory']),
				'RECOMMENDATION' => '',
			];
		}

		return $res;
	}
}
