<?php

use PhpParser\Node;
use PhpParser\NodeFinder;
use Bitrix\Security\XScanResultTable;
use Bitrix\Security\XScanResult;

IncludeModuleLangFile(__FILE__);

class CBitrixXscan
{
	static $var = '\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
	static $spaces = "[ \r\t\n]*";
	static $request = '(?:_REQUEST|_GET|_POST|_COOKIE|_SERVER(?!\[[\'"]DOCUMENT_ROOT[\'"]\])|_FILES)';
	static $functions = '(?:parse_str|hex2bin|str_rot13|base64_decode|url_decode|str_replace|str_ireplace|preg_replace|move_uploaded_file)';
	static $evals = ['eval', 'assert', 'create_function', 'exec', 'passthru', 'pcntl_exec', 'popen', 'proc_open', 'set_include_path', 'shell_exec', 'system'];
	static $evals_reg = '(?:assert|call_user_func|call_user_func_array|create_function|eval|exec|ob_start|passthru|pcntl_exec|popen|proc_open|set_include_path|shell_exec|system)';
	static $black_reg = '(https?://[0-9a-z\-]+\.pw/|wp-config|wp-admin|wp-login|deprecated-media-js|customize-menus-rtl|adminer_errors|/etc/passwd|/etc/hosts|mysql_pdo|__halt_compiler|/bin/sh|registerPHPFunctions|[e3]xp[l1][o0][i1][7td])';
	static $mehtods = [
		'Bitrix\Im\Call\Auth::authorizeById',
		'Bitrix\ImOpenLines\Controller\Widget\Filter\Authorization::authorizeById',
		'Bitrix\Imopenlines\Widget\Auth::authorizeById',
		'Bitrix\Sale\Delivery\Services\Automatic::createConfig',
		'Bitrix\Sender\Internals\DataExport::toCsv',
		'Bitrix\Sender\Internals\QueryController\Base::call',
		'CAllSaleBasket::ExecuteCallbackFunction',
		'CAllSaleOrder::PrepareSql',
		'CBPHelper::UsersStringToArray',
		'CControllerClient::RunCommand',
		'CMailFilter::CheckPHP',
		'CMailFilter::DoPHPAction',
		'CRestUtil::makeAuth',
		'CSaleHelper::getOptionOrImportValues',
		'CWebDavTools::sendJsonResponse',
	];
    public $false_positives = ['9223e925409363b7db262cfea1b6a7e2', '4d2cb64743ff3647bad4dea540d5b08e', 'd40c4da27ce1860c111fc0e68a4b39b5',
        'ef9287187dc22a6ce47476fd80720878', '13484affcdf9f45d29b61d732f8a5855', '4a171d5dc7381cce26227c5d83b5ba0c', 'b41d3b390f0f5ac060f9819e40bda7eb',
        '40142320d26a29586dc8528cfb183aac', 'f454f39a15ec9240d93df67536372c1b', '29bba835e33ab80598f88e438857f342', '77cdd8164d4940cb6bfaac906383a766',
        '5b3425a6ff518fa2337b373e1c799959', '7c60ccaee2b919c9e6b16b307eb80dab', 'bde611db5c3545005a7270edcffd8dc2', '4d6b616171dbf06ff57d1dab8ea6bbce',
        'a85abce54b4deb8cb157438dddca5a7c', 'de4f7ee97d421cf14d3951c0b4e5c2dd', '379918e8f6486ce9a7bb2ed5a69dbee6', '7ac4a2afcee04e683b092eb9402ee7ed',
        '1d5eb769111fc9c7be2021300ee5740e', 'f2357a1fe8e984052b6ee69933d467dc', 'a9158139e1a619ca8cc320cf4469c250'];


	static $default_config = ['request' => true, 'from_request' => true, 'crypted' => true, 'files' => true,
		'assigned' => false, 'params' => false, 'concat' => true, 'hardcoded' => false, 'value' => true, 'recursive' => false];
	public static $database = false;
	public $db_log = null;
	public $db_file = null;
	public $doc_root = null;
	public $start_time = null;
	public $time_limit = null;
	public $base_dir = null;
	public $break_point = null;
	public $skip_path = null;
	public $found = false;
	public $mem_enought = false;
	public $progress = 0;
	public $total = 0;
	public $collect_exceptions = true;
	private $errors = [];

	static $cryptors = ['rot13', 'str_rot13', 'base32_decode', 'base64_decode', 'gzinflate', 'unserialize',
		'url_decode', 'pack', 'unpack', 'hex2bin', 'bzdecompress', 'gzuncompress', 'lzf_decompress', 'strrev'];
	static $string_change = ['preg_replace', 'str_ireplace', 'str_replace', 'substr', 'strrev'];
	static $scoring = [
		'[337] strings from black list' => [0.9],
		'[630] long line' => [0.4],
		'[321] base64_encoded code' => [0.8],
		'[610] strange vars' => [0.5],
		'[302] preg_replace_eval' => [0.9],
		'[663] binary data' => [0.75],
		'[640] strange exif' => [0.6],
		'[500] php wrapper' => [0.7],
		'[665] chars by code' => [0.8],
		'[665] encoded code' => [0.8],
		'[303] create_function' => [0.8, 0.8, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
		'[300] eval' => [1, 0.4, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.9],
		'[302] unsafe callable argument' => [0.8, 0.8, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
		'[307] danger method' => [1, 0.4, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.9],
		'[662] function return as a function' => [0.9, 0.8, 0.1, 1, 1, 0.3, 0.7, 0.7, 0.8],
		'[663] strange function' => [1, 1, 0.1, 1, 1, 0.8, 0.7, 0.8, 0.9],
		'[302] eregi' => [0.8, 0.8, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
		'[887] backticks' => [1, 0.8, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
		'[600] strange include' => [0.8, 0.8, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
		'[660] array member as a function' => [0.9, 0.8, 0.1, 1, 0.8, 0.3, 0.5, 0.8, 0.8],
		'[298] mysql function' => [0.6, 0.8, 0.1, 1, 0.8, 0.9, 0.7, 0.8, 0.8],
		'[300] command injection' => [1, 0.7, 0.1, 1, 0.8, 0.6, 0.7, 0.8, 0.9],
		'[299] mail function' => [0.6, 0.8, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
		'[650] variable as a function' => [0.9, 0.8, 0.1, 1, 0.8, 0.3, 0.5, 0.8, 0.8],
		'[304] filter_callback' => [0.6, 0.8, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
		'[305] strange function and eval' => [0.8, 0.8, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
		'[301] file operations' => [0.5, 0.4, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
		'[302] file operations' => [0.8, 0.4, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
		'[400] bitrix auth' => [0.9, 0.8, 1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
        '[308] no prolog file operations' => [0.5, 0.4, 0.1, 1, 0.8, 0.3, 0.7, 0.8, 0.8],
	];
	private $results = [];
	private $tags = [];
	private $result_collection = null;
	private $score = 1;

	function __construct($progress = 0, $total = 0)
	{
		$this->doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

		$this->result_collection = new \Bitrix\Security\XScanResults();
		$this->db_file = $this->doc_root . '/bitrix/modules/security/data/database.json';
		$this->start_time = time();

		$mem = (int)ini_get('memory_limit');
		$this->time_limit = ini_get('max_execution_time') ?: 30;
		$this->time_limit = min($this->time_limit, 30);
		$this->time_limit = $this->time_limit * 0.7;

		$this->mem_enought = $mem == -1 || $mem >= 128;

		$this->progress = $progress;
		$this->total = $total;

		$this->parser = (new PhpParser\ParserFactory)->create(PhpParser\ParserFactory::PREFER_PHP7);
		$this->nodeFinder = new NodeFinder;
		$this->errorHandler = new PhpParser\ErrorHandler\Collecting;
		$this->pprinter = new PhpParser\PrettyPrinter\Standard;

		$errs = XScanResultTable::getList(['select' => ['SRC'], 'filter' => ['TYPE' => 'file', 'MESSAGE' => 'error']]);

		while ($row = $errs->fetch())
		{
			$this->errors[] = $row['SRC'];
		}
	}

	function clean()
	{
		global $DB;
		$DB->Query("TRUNCATE TABLE b_sec_xscan_results", true);
		$this->errors = [];
	}

	function CheckEvents()
	{
		global $DB;

		$r = $DB->Query('SELECT * from b_module_to_module');

		while ($row = $r->Fetch())
		{
			if ($row['TO_CLASS'] && $row['TO_METHOD'])
			{
				$class_method = trim($row['TO_CLASS'] . '::' . $row['TO_METHOD'], '\\');
				$found = false;
				foreach (self::$mehtods as $mtd)
				{
					if (stripos($class_method, $mtd) !== false)
					{
						$found = true;
						break;
					}
				}

				if ($found)
				{
					$result = (new XScanResult)->setType('event')->setSrc($row['ID'])->setScore(1)->setMessage('[050] dangerous method at event, check arguments');
					$this->result_collection[] = $result;
				}
			}
		}
	}

	function CheckAgents()
	{
		global $DB;

		$r = $DB->Query('SELECT * from b_agent');

		while ($row = $r->Fetch())
		{
			if (!$row['NAME'])
			{
				continue;
			}

			$src = "<?php\n" . $row['NAME'] . "\n?>";
			$this->CheckCode($src);

			if ($this->results)
			{
				$message = [];
				foreach ($this->results as $res)
				{
					$message[] = $res['subj'];
				}

				if (is_array($message))
				{
					$message = implode(' <br> ', array_unique($message));
				}

				$result = (new XScanResult)->setType('agent')->setSrc($row['ID'])->setScore(1)->setMessage($message);
				$this->result_collection[] = $result;
			}
		}
	}

	static function crc($a)
	{
		return crc32(implode('|', $a));
	}

	static function CountBlocks($src, &$result)
	{
		$code = strtolower($src);

		$code = preg_replace('~<\?(php|=)?~', '', $code);
		$code = preg_replace('~<[^>$()]*?>~', '', $code);
		$code = str_replace('?>', '', $code);
		$code = preg_split('~[\n;{}(),\s]+~', $code);

		$arr = [];

		foreach ($code as $chunk)
		{
			$chunk = trim($chunk);

			if ($chunk !== '')
			{
				$arr[] = $chunk;
			}
		}

		$crcs = [];

		if (!empty($arr))
		{
			while (count($arr) < 3)
			{
				$arr[] = $arr[0];
			}

			$block = [$arr[0], $arr[1], $arr[2]];
			$crcs[] = self::crc($block);

			$end = count($arr) - 1;
			for ($i = 3; $i <= $end; $i++)
			{
				$block = [$block[1], $block[2], $arr[$i]];
				$crcs[] = self::crc($block);
			}
		}

		$result = array_unique($crcs);

		unset($code);
		unset($arr);
		unset($crcs);
	}

	function SearchInDataBase($src)
	{
		$result = [];
		$found = [];
		self::CountBlocks($src, $result);

		foreach ($result as $token)
		{
			if (isset(self::$database['tokens'][$token]))
			{
				foreach (self::$database['tokens'][$token] as $shell)
				{
					if (!isset($found[$shell]))
					{
						$found[$shell] = 0;
					}
					$found[$shell] += 1;
				}
			}
		}

		$bFound = false;

		foreach ($found as $key => $value)
		{
			if ($value / self::$database['shells'][$key] > 0.8)
			{
				$bFound = true;
				break;
			}
		}

		unset($result);
		unset($found);

		return $bFound;
	}

	function addResult($subj, $code, $score, $checksum = '')
	{
		$this->results[] = ['subj' => $subj, 'code' => $code, 'score' => $score, 'checksum' => $checksum];
	}


    static function detectDocRoot($file_path)
    {
        static $doc_root;

        if (!$doc_root || strpos($file_path, $doc_root . '/') !== 0)
		{
            $path = explode('/', ltrim($file_path, '/'));
            $doc_root = '';
            $found = false;
            foreach ($path as $comp)
			{
                if (is_file($doc_root . '/bitrix/.settings.php'))
				{
                    $found = true;
                    break;
                }
                $doc_root .= '/' . $comp;
            }

            if (!$found)
			{
                $doc_root = '';
            }
        }
        return $doc_root;
    }


    static function getVersion($moduleName, $doc_root)
    {
        $moduleName = preg_replace("/[^a-zA-Z0-9_.]+/i", "", trim($moduleName));
        if ($moduleName == '')
            return false;


        if ($moduleName == 'main')
        {
            $content = file_get_contents("$doc_root/bitrix/modules/main/classes/general/version.php");

        }
        else
        {
            $content = file_get_contents("$doc_root/bitrix/modules/$moduleName/install/version.php");
        }

        preg_match('/\d+\.\d+\.\d+/', $content, $m);
        $version = count($m)? $m[0]: false;

        return $version;
    }

    static function getHashes($module, $version)
    {
        global $DB;

        static $static_cache;
        static $map = [
            'socserv' => 'socialservices',
            'system' => 'main',
            'rating' => 'main',
            'spotlight' => 'main',
            'desktop' => 'main',
            'menu' => 'main',
            'pdf' => 'fileman',
            'player' => 'fileman',
            'map' => 'fileman',
            'news' => 'iblock',
            'photo' => 'iblock',
            'support' => 'iblock',
            'rss' => 'iblock',
            'voting' => 'vote',
            'payroll' => 'intranet',
            'planner' => 'intranet',
            'eshop' => 'bitrix.eshop',
            'furniture' => 'bitrix.sitecorporate',
            'app' => 'rest'
        ];

        $module = isset($map[$module])? $map[$module]: $module;

        if (!is_array($static_cache)){
            $static_cache = [];
        }

        $key = $module . '_' . $version;

        if (isset($static_cache[$key]))
        {
            return $static_cache[$key];
        }

        $cache = \Bitrix\Main\Data\Cache::createInstance();

        if ($cache && $cache->initCache(12 * 3600, 'xscan_' . $key, 'xscan'))
        {
            $result = $cache->getVars();
            $static_cache[$key] = $result;
            return $static_cache[$key];
        }
        else
        {
            $sHost = COption::GetOptionString("main", "update_site", "www.bitrixsoft.com");
            $proxyAddr = COption::GetOptionString("main", "update_site_proxy_addr", "");
            $proxyPort = COption::GetOptionString("main", "update_site_proxy_port", "");
            $proxyUserName = COption::GetOptionString("main", "update_site_proxy_user", "");
            $proxyPassword = COption::GetOptionString("main", "update_site_proxy_pass", "");
            $dbtype = mb_strtolower($DB->type);
            $http = new \Bitrix\Main\Web\HttpClient();
            $http->setProxy($proxyAddr, $proxyPort, $proxyUserName, $proxyPassword);

            $data = $http->get("https://{$sHost}/bitrix/updates/checksum.php?check_sum=Y&module_id={$module}&ver={$version}&dbtype={$dbtype}&mode=2");
            $result = @unserialize(gzinflate($data), ['allowed_classes' => false]);

            $static_cache[$key] = [];

            if (is_array($result)) {
                $result = array_filter($result, function ($value) {
                    return substr($value, -4) === '.php';
                }, ARRAY_FILTER_USE_KEY);

                $cache->startDataCache();
                $cache->endDataCache($result);

                $static_cache[$key] = $result;
            }
            return $static_cache[$key];
        }

        return false;
    }

    static function checkByHash($file_path)
    {
        $module = '';
        $file = '';

        if (preg_match("~bitrix/modules/([^.]+?)/(.+)~", $file_path, $matches))
        {
            $file = $matches[2];
            $module = $matches[1];
        }
        elseif(preg_match("~/bitrix(/components/bitrix/socialnetwork_(?:group|user)/.+)~", $file_path, $matches))
        {
            $file = 'install' . $matches[1];
            $module = 'socialnetwork';
        }
        elseif(preg_match("~/bitrix(/components/bitrix/photogallery_user/.+)~", $file_path, $matches))
        {
            $file = 'install' . $matches[1];
            $module = 'photogallery';
        }
        elseif(preg_match("~/bitrix(/(?:components|wizards)/bitrix/([a-z24]+)[./].+)~", $file_path, $matches))
        {
            $file = 'install' . $matches[1];
            $module = $matches[2];
        }
        elseif(preg_match("~/bitrix(/templates/bitrix24/.+)~", $file_path, $matches))
        {
            $file = 'install' . $matches[1];
            $module = 'intranet';
        }
        elseif(preg_match("~/bitrix(/blocks/bitrix/.+)~", $file_path, $matches))
        {
            $file = 'install' . $matches[1];
            $module = 'landing';
        }

        if($file && $module)
        {
            $doc_root = static::detectDocRoot($file_path);

            if(!$doc_root)
            {
                return false;
            }

            $version = static::getVersion($module, $doc_root);
            if (!$version)
            {
                return;
            }

            $hashes = static::getHashes($module, $version);

            if ($hashes && isset($hashes[$file]) && $hashes[$file] === md5_file($file_path))
            {
                return true;
            }
        }

        return false;
    }

	function CheckFile($file_path)
	{
		$this->results = [];
		$this->tags = [];

		static $me;
		if (!$me)
		{
			$me = realpath(__FILE__);
		}
		if (realpath($file_path) == $me)
		{
			return false;
		}

		if (in_array($file_path, $this->errors))
		{
			return false;
		}

		if ($this->SystemFile($file_path))
		{
			return false;
		}

		# CODE 100
		if (basename($file_path) == '.htaccess')
		{
			$src = file_get_contents($file_path);
			$res = preg_match('#<(\?|script)#i', $src, $regs);
			if ($res)
			{
				$this->addResult('[100] htaccess', $regs[0], 1);
				return true;
			}

			$res = preg_match('#\bwp-[a-z]+\.php#i', $src, $regs);
			if ($res)
			{
				$this->addResult('[100] htaccess', $regs[0], 1);
				return true;
			}

			if (preg_match_all('#x-httpd-php[578]?\s+(.+)#i', $src, $regs))
			{
				foreach ($regs[1] as $i => $val)
				{
					$val = preg_split('/\s+/', $val);
					foreach ($val as $ext)
					{
						$ext = trim(strtolower($ext), '"\'');
						if (!in_array($ext, ['.php', '.php5', '.php7', '.html', '']))
						{
							$this->addResult('[100] htaccess', $regs[0][$i], 1);
							return true;
						}
					}
				}
			}

			return false;
		}

		# CODE 110
		if (preg_match('#^/upload/.*\.php$#i', str_replace($this->doc_root, '', $file_path)))
		{
			$this->addResult('[110] php file in upload dir', '', 1);
			return true;
		}

		if (!preg_match('#\.php[578]?$#i', $file_path, $regs))
		{
			return false;
		}

		if (static::checkByHash($file_path))
		{
			return false;
		}

		# CODE 200
		if (($src = @file_get_contents($file_path)) === false)
		{
			$this->addResult('[200] read error', '', 1);
			return true;
		}

		$this->CheckCodeInternal($src, $file_path);
		$tot = 1;

		foreach ($this->results as $value)
		{
			$tot = $tot * (1 - $value['score']);
		}

		$tot = round(1 - $tot, 2);

		$this->score = $tot;

		return !empty($this->results);
	}

	function CalcChecksum($file_path, $code, $subj)
	{

        $doc_root = static::detectDocRoot($file_path);

		if ($doc_root)
		{
			$file_path = substr($file_path, strlen($doc_root));
		}

		if (strpos($file_path, '/') !== 0)
		{
			$file_path = '/' . $file_path;
		}

		$file_path = preg_replace('#^/bitrix/modules/[a-z0-9._]+/install/components/bitrix#', '/bitrix/components/bitrix', $file_path);
		$checksum = md5($file_path . '|' . trim($code) . '|' . $subj);

		return $checksum;
	}

	function IsFalsePositive($checksum)
	{
		return in_array($checksum, $this->false_positives, true);
	}

	function getResult()
	{
		return $this->results;
	}

	function getErrors()
	{
		return $this->errors;
	}

    function setErrors($val)
    {
        $this->errors = $val;
    }

	function getTags()
	{
		return $this->tags;
	}

	function getScore()
	{
		return $this->score;
	}

	function CheckCode(&$src, $file_path = false)
	{
		$this->results = [];
		$this->tags = [];
		return $this->CheckCodeInternal($src, $file_path);
	}

	private function CheckCodeInternal(&$src, $file_path = false)
	{
		$file_path = $file_path ? $file_path : '';

		if (!self::$database && is_file($this->db_file) && $this->mem_enought)
		{
			$tmp = file_get_contents($this->db_file);
			self::$database = json_decode($tmp, true);
			unset($tmp);
		}

		$code = preg_replace("/<\?=/", "<?php echo ", $src);
		$code = preg_replace("/<\?(?!php)/", "<?php ", $code);
		$code = preg_replace("/else if\s*\(/", "elseif (", $code); // crutch

		# OBFUSCATORS

		$cmt = '';
		if (
			($cmt = '$$') && substr_count($code, '${${') > 0 ||
			($cmt = 'vars') && preg_match_all('/(?:\$|function\s+)(?:[o0]{4,}|[il]{4,})/i', $code) > 3 ||
			($cmt = 'goto') && preg_match_all('/goto\s+[0-9A-Z]+\s*;/i', $code) > 2 ||
			($cmt = 'globals') && preg_match_all('/\$GLOBALS\s*\[["\'][0-9_]+["\']\]/', $code) > 3 ||
            ($cmt = 'base64_short') && preg_match_all("/base64_decode\s*\(\s*[^$].{3,14}\)/i", $code) > 3 ||
            ($cmt = 'functions') && preg_match_all("/function\s+_\w{1,3}\b/i", $code) > 3 ||
            ($cmt = 'concat') && preg_match_all("~(?:(['\"])[0-9a-z=+\/_]{1,20}\\1(?:\s*\.\s*)?){2,}~i", $code) > 20 ||
			// ($cmt = 'len') && strlen($code) / max(substr_count($code, "\n"), 1) > 500 ||
			// ($cmt = 'base_strings') && preg_match_all('~[0-9A-Z+/]{80,100}~i', $code) > 5 ||
			($cmt = 'urlenc') && preg_match_all('/(%[0-9A-Z]{2}){80,100}/i', $code) > 2 ||
			($cmt = 'base64_keys') && substr_count($code, "[base64") > 1 /* ||
			($cmt = 'long_space') && preg_match('/\t{30,}+(?:[()$]|\S.*?[()$])/', $code) ||
			($cmt = 'long_space') && preg_match('/[\t ]{80,}+(?:[()$]|\S.*?[()$])/', $code) */
		)
		{
			$this->tags[] = defined('XSCAN_DEBUG') ? "obfuscator [$cmt]" : "obfuscator";
            if (in_array($cmt, ['$$', 'vars', 'goto', 'globals', 'base64_short', 'functions', 'concat']))
			{
				$this->addResult('[001] obfuscator', '', 0.6);
			}
		}
		else
		{
			$comments = [];
			preg_match_all('~/\*(.+?)\*/~', $code, $comments);
			$cnt = 0;
			$comments = $comments ? $comments[1] : [];

			$comments = array_unique($comments);

			foreach ($comments as $comment)
			{
				$comment = trim($comment);
				if (strlen($comment) <= 15 && preg_match('~([A-Za-z\s_]++|[0-9]++|[!@#$%^&():;`<>?,.{}|\~[\]+-=?]){4,}~', $comment))
				{
					$cnt += 1;
					if ($cnt > 20)
					{
						$this->tags[] = defined('XSCAN_DEBUG') ? "obfuscator [comments]" : "obfuscator";
						$this->addResult('[001] obfuscator', '', 0.6);
						break;
					}
				}
			}

			unset($comments);

			if ($cnt < 20)
			{
				$funcsVars = [];
				preg_match_all('/(?:\$|function\s+)([0-9a-z_]++)/i', $code, $funcsVars);
				$cnt = 0;
				$funcsVars = $funcsVars ? $funcsVars[1] : [];
				$funcsVars = array_unique($funcsVars);

				foreach ($funcsVars as $value)
				{
					$value = str_replace('24', '', $value); // crutch
					if (preg_match('/\d/', $value) && preg_match('/_\d|(?:[a-z_]++|[A-Z_]++|[0-9]++){4,}/', $value))
					{
						$cnt++;
					}
				}

				if (count($funcsVars) && $cnt / count($funcsVars) > 0.5)
				{
					$this->tags[] = defined('XSCAN_DEBUG') ? "obfuscator [rand_names]" : "obfuscator";
				}
				unset($funcsVars);
			}
		}

		if (strpos($file_path, '/bitrix/modules/main/') !== false)
		{
			$this->tags[] = 'core';
		}

		if (preg_match('~/bitrix/(?:modules|components)/[0-9a-z_]+\.[0-9a-z_]+/~i', $file_path) ||
			preg_match('~/bitrix/components/(?!bitrix/)~i', $file_path))
		{
			$this->tags[] = 'marketplace';
		}

		if (preg_match('/(?:[a-z_]++|[0-9]++){4,}/i', $file_path))
		{
			$this->tags[] = 'random_name';
		}

		if (preg_match('~/lang/~i', $file_path))
		{
			$this->tags[] = 'lang';
		}

		if (preg_match('~/\.~i', $file_path))
		{
			$this->tags[] = 'hidden';
		}

		if (strpos($file_path, '/bitrix/modules/') === false &&
			strpos($file_path, '/upload/') === false &&
			strpos($file_path, '/bitrix/php_interface/') === false &&
			strpos($src, 'B_PROLOG_INCLUDED') === false &&
			strpos($src, '/bitrix/header.php') === false &&
			strpos($src, '/bitrix/modules/main/start.php') === false &&
			strpos($src, '/bitrix/modules/main/include/prolog') === false &&
			strpos($src, '/bitrix/modules/main/include/mainpage.php') === false &&
			strpos($src, '/bitrix/main/include/routing_index.php') === false
		)
		{
			$this->tags[] = 'no_prolog';

			if (preg_match('/copy\s*\(|file_put_contents|move_uploaded_file|fwrite|fputs/i', $src, $m))
			{
                $subj = '[308] no prolog file operations';
				$checksum = $this->CalcChecksum($file_path, $m[0], $subj);
				if (!$this->IsFalsePositive($checksum))
				{
					$this->addResult($subj, $m[0], self::CalcCrit($subj), $checksum);
				}
			}
		}

		$parser = $this->parser;
		$errorHandler = $this->errorHandler;
		$pprinter = $this->pprinter;

		$errorHandler->clearErrors();

		try
		{
			$stmts = $parser->parse($code, $errorHandler);
			$params = [];

			if (!$stmts && $errorHandler->getErrors())
			{
				throw new Exception('syntax error in file');
			}

			$this->CheckStmts($stmts, $params, $file_path);
		}
		catch (Exception $e)
		{
			//  echo 'Parse Error: ' . $file_path . " " . $e->getMessage() . "\n";
			if ($this->collect_exceptions)
			{
				$this->addResult('[000] syntax error in file', '', 1);
			}
		}

		# REGEXP BASED CODES

		$src = preg_replace('#/\*.*?\*/#s', '', $src);
		$src = preg_replace('#[\r\n][ \t]*//.*#m', '', $src);
		$src = preg_replace('/[\r\n][ \t]*#.*/m', '', $src);

		# CODE 007
		if (self::$database && $this->SearchInDataBase($src))
		{
			$this->addResult('[007] looks like a well-known shell', '', 1);
			return true; // is not false-positive
		}

		# CODE 302
		if (preg_match_all('#preg_replace' . self::$spaces . '(\(((?>[^()]+)|(?-2))*\))#i', $src, $regs))
		{
			foreach ($regs[1] as $i => $val)
			{
				$code = $regs[0][$i];
				$spiltter = $val[2];
				$spl = $spiltter === '#' ? '~' : '#';
				if (preg_match($spl . preg_quote($spiltter) . '[imsxADSUXju]*e[imsxADSUXju]*[\'"]' . $spl, $val))
				{
					$subj = '[302] preg_replace_eval';
					$checksum = $this->CalcChecksum($file_path, $code, $subj);
					if (!$this->IsFalsePositive($checksum))
					{
						$this->addResult($subj, $code, self::CalcCrit($subj), $checksum);
					}
				}
			}
		}

		$content = preg_replace('/[\'"]\s*?\.\s*?[\'"]/smi', '', $src);

		# CODE 321
		if (preg_match_all('#[A-Za-z0-9+/]{20,}=*#i', $content, $regs))
		{
			foreach ($regs[0] as $val)
			{
				$code = $val;
				$val = base64_decode($val);
				if (preg_match('#(' . self::$request . '|' . self::$functions . '|' . self::$evals_reg . '|' . self::$black_reg . ')#i', $val))
				{
					$subj = '[321] base64_encoded code';
					$checksum = $this->CalcChecksum($file_path, $code, $subj);
					if (!$this->IsFalsePositive($checksum))
					{
						$this->addResult($subj, $code, self::CalcCrit($subj), $checksum);
					}
				}
			}
		}
//        unset($content);

		# CODE 337
		if (preg_match_all('#' . self::$black_reg . '#i', $content, $regs))
		{
			$code = implode(' | ', $regs[0]);

			$subj = '[337] strings from black list';
			$checksum = $this->CalcChecksum($file_path, $code, $subj);
			if (!$this->IsFalsePositive($checksum))
			{
				$this->addResult($subj, $code, self::CalcCrit($subj), $checksum);
			}
		}

		# CODE 400
		/*        if (preg_match_all('#\$(USER|GLOBALS..USER..)->Authorize' . self::$spaces . '(\(((?>[^()]+)|(?-2))*\))#i', $src, $regs)) {*/
//
//            foreach ($regs[3] as $i => $val) {
//                $code = $regs[0][$i];
//
//                $val = explode(',', $val)[0];
//
//                if (preg_match('#' . self::$request . '|([\'"]?0?[xbe]?[0-9]+[\'"]?)#', $val)) {
//                    $subj = '[400] bitrix auth';
//                    if ($checksum = $this->CalcChecksum($file_path, $code, $subj)  && $this->IsFalsePositive($checksum)) {
//                        $this->results[] = [$subj, $code];
//                    }
//
//                }
//            }
//        }

		# CODE 500
		if (preg_match_all('#[\'"](php://filter|phar://)#i', $content, $regs))
		{
			foreach ($regs[0] as $i => $value)
			{
				$code = $value;
				$subj = '[500] php wrapper';
				$checksum = $this->CalcChecksum($file_path, $code, $subj);
				if (!$this->IsFalsePositive($checksum))
				{
					$this->addResult($subj, $code, self::CalcCrit($subj), $checksum);
				}
			}
		}

		# CODE 630
		if (preg_match('#[a-z0-9+=/\n\r]{255,}#im', $src, $regs))
		{
			$code = $regs[0];
			if (!preg_match('#data:image/[^;]+;base64,[a-z0-9+=/]{255,}#i', $src, $regs))
			{
				$subj = '[630] long line';
				$checksum = $this->CalcChecksum($file_path, $code, $subj);
				if (!$this->IsFalsePositive($checksum))
				{
					$this->addResult($subj, $code, self::CalcCrit($subj), $checksum);
				}
			}
		}

		# CODE 640
		if (preg_match_all('#exif_read_data\(#i', $src, $regs))
		{
			foreach ($regs[0] as $i => $value)
			{
				$code = $value;
				$subj = '[640] strange exif';
				$checksum = $this->CalcChecksum($file_path, $code, $subj);
				if (!$this->IsFalsePositive($checksum))
				{
					$this->addResult($subj, $code, self::CalcCrit($subj), $checksum);
				}
			}
		}

		# CODE 663
		if (preg_match("#^.*([\x01-\x08\x0b\x0c\x0f-\x1f])#m", $src, $regs))
		{
			$code = $regs[1];
			if (!preg_match('#^\$ser_content = #', $regs[0]))
			{
				$subj = '[663] binary data';
				$checksum = $this->CalcChecksum($file_path, $code, $subj);
				if (!$this->IsFalsePositive($checksum))
				{
					$this->addResult($subj, $code, self::CalcCrit($subj), $checksum);
				}
			}
		}

		# CODE 665
		if ($file_path && preg_match_all('#(?:\\\\x[a-f0-9]{2}|\\\\[0-9]{2,3})+#i', $content, $regs))
		{
			$regs = $regs[0];
			$all = implode("", $regs);
			if (count($regs) > 1)
			{
				$regs[] = $all;
			}
			$found = false;

			foreach ($regs as $code)
			{
				$val = stripcslashes($code);
				if (preg_match('#(' . self::$request . '|' . self::$functions . '|' . self::$evals_reg . '|' . self::$black_reg . ')#i', $val))
				{
					$subj = '[665] encoded code';
					$checksum = $this->CalcChecksum($file_path, $code, $subj);
					if (!$this->IsFalsePositive($checksum))
					{
						$this->addResult($subj, $code, self::CalcCrit($subj), $checksum);
						$found = true;
					}
				}
				elseif (preg_match_all('#[A-Za-z0-9+/]{20,}=*#i', $val, $regs))
				{
					foreach ($regs[0] as $val)
					{
						$val = base64_decode($val);
						if (preg_match('#(' . self::$request . '|' . self::$functions . '|' . self::$evals_reg . '|' . self::$black_reg . ')#i', $val))
						{
							$subj = '[665] encoded code';
							$checksum = $this->CalcChecksum($file_path, $code, $subj);
							if (!$this->IsFalsePositive($checksum))
							{
								$this->addResult($subj, $code, self::CalcCrit($subj), $checksum);
								$found = true;
							}
						}
					}
				}
			}

			if (!$found && strlen($all) / filesize($file_path) > 0.1)
			{
				$subj = '[665] chars by code';
				$checksum = $this->CalcChecksum($file_path, $code, $subj);
				if (!$this->IsFalsePositive($checksum))
				{
					$this->addResult($subj, $code, self::CalcCrit($subj), $checksum);
				}
			}
		}

		unset($src);
		unset($content);

		return !empty($this->results);
	}

	function CheckStmts($stmts, &$params, $file_path, $in_closure = false)
	{
		$nodeFinder = $this->nodeFinder;
		$pprinter = $this->pprinter;

		$nodeFinder->find($stmts, function (Node $node) use (&$file_path) {
			if ($node instanceof Node\Stmt\Function_ || $node instanceof Node\Stmt\ClassMethod)
			{
				$this->CheckStmts($node->stmts, $node->params, $file_path);
				$node->stmts = [];
			}
			elseif ($node instanceof Node\Expr\Closure)
			{
				$this->CheckStmts($node->stmts, $node->params, $file_path, true);
				$node->stmts = [];
			}
			elseif ($node instanceof Node\Expr\ArrowFunction)
			{
				$this->CheckStmts($node->expr, $node->params, $file_path, true);
				$node->stmts = [];
			}
		});

		$nodes = ['assigns' => [], 'variables' => [], 'params' => [], 'foreaches' => [], 'calls' => [], 'evals' => [],
			'backticks' => [], 'includes' => [], 'auth' => [], 'mtds' => [], 'strings' => []];
		$extract = false;

		$nodeFinder->find($stmts, function (Node $node) use (&$nodes, &$pprinter, &$extract)
		{
			if ($node->getComments())
			{
				$node->setAttribute('comments', []);
			}

//            if ($node instanceof Node\Stmt\Function_) {
//                $name = $node->name instanceof Node\Identifier ? $node->name->toString() : false
//                if (is_string($name) && self::isVarStrange('$' . trim($name, '_'))) {
//                    $this->addResult('[110] strange function name', $name, 0.3);
//                }
//            }
			if ($node instanceof Node\Expr\Assign || $node instanceof Node\Expr\AssignOp)
			{
				$nodes['assigns'][] = $node;
			}
			if ($node instanceof Node\Expr\Variable)
			{
				$nodes['variables'][] = $node;
			}
			if ($node instanceof Node\Stmt\Foreach_)
			{
				$nodes['foreaches'][] = $node;
			}
			if ($node instanceof Node\Expr\FuncCall)
			{
				$nodes['calls'][] = $node;
				if ($node->name instanceof Node\Name && $node->name->toLowerString() === 'extract')
				{
					if (count($node->args) < 2 || $pprinter->prettyPrintExpr($node->args[1]->value) !== 'EXTR_SKIP')
					{
						$extract = true;
					}
				}
			}
			if ($node instanceof Node\Expr\Eval_)
			{
				$nodes['evals'][] = $node;
			}
			if ($node instanceof Node\Expr\ShellExec)
			{
				$nodes['backticks'][] = $node;
			}
			if ($node instanceof Node\Expr\Include_)
			{
				$nodes['includes'][] = $node;
			}
			if ($node instanceof Node\Expr\MethodCall && $node->name instanceof Node\Identifier && $node->name->toLowerString() == 'authorize'
                && preg_match('/user|globals/i', $pprinter->prettyPrintExpr($node->var))) 
            {
                $nodes['auth'][] = $node;
            }

            if ($node instanceof Node\Expr\MethodCall && $node->name instanceof Node\Expr\Variable &&
                $node->var instanceof Node\Expr\ArrayDimFetch &&
                $node->var->var instanceof Node\Expr\Variable && $node->var->var->name == 'GLOBALS' &&
                (!($node->var->dim instanceof Node\Scalar\String_) || $node->var->dim->value == 'USER')
            )
			{
				$nodes['auth'][] = $node;
			}

			if ($node instanceof Node\Expr\StaticCall)
			{
				$nodes['mtds'][] = $node;
			}

			if ($node instanceof Node\Scalar\String_ && $node->value)
			{
				$nodes['strings'][] = $node;
			}

			# this is dirty hack
			if ($node instanceof Node\Expr\ArrayDimFetch && $node->var instanceof Node\Expr\Variable && $node->var->name == '_SERVER')
			{
				$dim = $node->dim instanceof Node\Scalar\String_ ? $node->dim->value : "qwerty";
				if (!preg_match('/^(?:DOCUMENT_ROOT|SERVER_ADDR|REMOTE_ADDR|SERVER_NAME|HTTPS|SERVER_PORT|REMOTE_PORT)$/', $dim))
				{
					$node->var->name = '_REQUEST';
				}
			}

//            if ($node instanceof Node\Expr\ArrayDimFetch && $node->var instanceof Node\Expr\Variable && $node->var->name == 'GLOBALS') {
//                $dim = $node->dim instanceof Node\Scalar\String_ ? $node->dim->value : "qwerty";
//                if (preg_match('/^(?:_GET|_POST|_REQUEST|_COOKIE|_FILES|_SERVER)$/', $dim)) {
//                    $node->var->name = '_REQUEST';
//                }
//            }
		});

		$vars_names = [];

		$vars = [
			'request' => ['_GET' => true, '_POST' => true, '_REQUEST' => true, '_COOKIE' => true, '_FILES' => true],
			'params' => [],
			'from_request' => [],
			'crypted' => [],
			'assigned' => ['_GET' => true, '_POST' => true, '_REQUEST' => true, '_COOKIE' => true, '_SESSION' => true,
				'_SERVER' => true, '_FILES' => true, 'this' => true, 'USER' => true, 'DB' => true, 'APPLICATION' => true],
			'values' => [],
			'closures' => [],
		];

		foreach ($nodes['variables'] as $var)
		{
			if (is_string($var->name))
			{
				$var = '$' . $var->name;
			}
			else
			{
				$var = $this->pprinter->prettyPrintExpr($var->name);
			}

			$vars_names[] = $var;
		}
		$vars_names = array_unique($vars_names);

		foreach ($params as $param)
		{
			$n = substr($this->pprinter->prettyPrintExpr($param->var), 1);
			$vars['params'][$n] = true;
			$vars['assigned'][$n] = true;

			if ($param->type instanceof Node\Name\FullyQualified && implode('', $param->type->parts) == 'Closure')
			{
				$vars['closures'][] = $n;
			}
		}

		foreach ($nodes['assigns'] as $fnd)
		{
			$n = substr($this->pprinter->prettyPrintExpr($fnd->var), 1);
			$vars['assigned'][$n] = true;
			if ($fnd->expr instanceof Node\Expr\Closure)
			{
				$vars['closures'][] = $n;
			}
		}

		foreach ($nodes['foreaches'] as $fnd)
		{
			if ($fnd->keyVar)
			{
				$n = substr($this->pprinter->prettyPrintExpr($fnd->keyVar), 1);
				$vars['assigned'][$n] = true;
			}
			if ($fnd->valueVar)
			{
				$n = substr($this->pprinter->prettyPrintExpr($fnd->valueVar), 1);
				$vars['assigned'][$n] = true;
			}
		}

		for ($_ = 0; $_ < 2; $_++)
		{
			$res = [];
			foreach ($nodes['assigns'] as $node)
			{
				$flag = $nodeFinder->findFirst($node->expr,
					function (Node $node) use (&$vars) {
						return $node instanceof Node\Expr\Variable && is_string($node->name) && $node->name && (isset($vars['request'][$node->name]) || isset($vars['from_request'][$node->name]));
					}
				);

				if ($flag)
				{
					$res[] = $node;
				}
			}

			foreach ($res as $fnd)
			{
				$n = substr($this->pprinter->prettyPrintExpr($fnd->var), 1);
				$vars['from_request'][$n] = true;
			}
		}

		for ($_ = 0; $_ < 1; $_++)
		{
			$res = [];
			foreach ($nodes['assigns'] as $node)
			{
				$flag = $nodeFinder->findFirst($node->expr,
					function (Node $node) {
						return $node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name &&
							in_array($node->name->toLowerString(), self::$cryptors, true);
					}
				);
				if ($flag)
				{
					$res[] = $node;
				}
			}

			foreach ($res as $fnd)
			{
				$n = substr($this->pprinter->prettyPrintExpr($fnd->var), 1);
				$vars['crypted'][$n] = true;
			}
		}

		foreach ($nodes['assigns'] as $fnd)
		{
			$n = substr($this->pprinter->prettyPrintExpr($fnd->var), 1);
			$tmp = $this->parseValue($fnd->expr, $vars);
			if (!isset($vars['values'][$n]))
			{
				$vars['values'][$n] = $tmp;
			}
			else
			{
				$vars['values'][$n] .= '|' . $tmp;
			}
		}

//        print_r($vars['values']);

		$crypto_vars = array_keys($vars['crypted']);

		# CODE 300

		$res = [];
		$config = self::genConfig(['params' => true, 'assigned' => $extract]);
		foreach ($nodes['evals'] as $node)
		{
			[$flag, $comment] = $this->CheckArg($node->expr, $vars, $config);
			if ($flag)
			{
				$node->setAttribute('comment', $comment);
				$res[] = $node;
			}
		}

		$this->CheckResults($res, '[300] eval', $file_path);

		# CODE 298

		$sql_map = [
			'mysqli_connect' => [0, 1, 2, 3],
			'mysqli_query' => [1],
			'mysqli_real_query' => [1],
			# i know its removed at php7
			'mysql_connect' => [0, 1, 2],
			'mysql_query' => [1],
			'mysql_db_query' => [0, 1],
		];

		$config = self::genConfig(['params' => true]);
		$res = $this->checkFuncCalls($nodes['calls'], $sql_map, $nodeFinder, $vars, $config);

		$this->CheckResults($res, '[298] mysql function', $file_path);

		# CODE 299

		$mail_map = [
			'mail' => [0],
			'bxmail' => [0],
		];

		$config = self::genConfig();
		$res = $this->checkFuncCalls($nodes['calls'], $mail_map, $nodeFinder, $vars, $config);

		$this->CheckResults($res, '[299] mail function', $file_path);

		# CODE 300

		$evals_map = [
			'assert' => [0],
			'create_function' => [0],
			'exec' => [0],
			'passthru' => [0],
			'pcntl_exec' => [0],
			'popen' => [0],
			'proc_open' => [0],
			'set_include_path' => [0],
			'shell_exec' => [0],
            'system' => [0]
		];

		$config = self::genConfig(['params' => true, 'assigned' => $extract]);
		$res = $this->checkFuncCalls($nodes['calls'], $evals_map, $nodeFinder, $vars, $config);

		$this->CheckResults($res, '[300] command injection', $file_path);

		# CODE 301

		$files_map = [
			'copy' => [1], // 0,1
			'file_get_contents' => [0],
			'file_put_contents' => [0],
			'move_uploaded_file' => [1], // 0,1
			'opendir' => [0],
            'fopen' => [0]
		];

		$config = self::genConfig(['concat' => false, 'files' => false, 'value' => false]);
		$res = $this->checkFuncCalls($nodes['calls'], $files_map, $nodeFinder, $vars, $config);

		$this->CheckResults($res, '[301] file operations', $file_path);

		/*
		$files_map = [
			'file_put_contents' => [1],
			'fwrite' => [1],
			'fputs' => [1],

		];

		$config = self::genConfig(['value'=> true, 'recursive' => true, 'concat'=> false]);
		$res = $this->checkFuncCalls($nodes['calls'], $files_map, $nodeFinder, $vars, $config);

		$this->CheckResults($res, '[302] file operations', $file_path);
		*/

		# CODE 302

		$f_w_clb_map = ['call_user_func' => [0],
			'call_user_func_array' => [0],
			'forward_static_call' => [0],
			'forward_static_call_array' => [0],
			'register_shutdown_function' => [0],
			'register_tick_function' => [0],
			'ob_start' => [0],
			'usort' => [1],
			'uasort' => [1],
			'uksort' => [1],
			'array_walk' => [1],
			'array_walk_recursive' => [1],
			'array_reduce' => [1],
			'array_intersect_ukey' => [2],
			'array_uintersect' => [2],
			'array_uintersect_assoc' => [2],
			'array_intersect_uassoc' => [2],
			'array_uintersect_uassoc' => [2, 3],
			'array_diff_ukey' => [2],
			'array_udiff' => [2],
			'array_udiff_assoc' => [2],
			'array_diff_uassoc' => [2],
			'array_udiff_uassoc' => [2, 3],
			'array_filter' => [1],
			'array_map' => [0],
            'mb_ereg_replace_callback' => [1]
		];

		$config = self::genConfig(['assigned' => $extract]);
		$res = $this->checkFuncCalls($nodes['calls'], $f_w_clb_map, $nodeFinder, $vars, $config);

		$this->CheckResults($res, '[302] unsafe callable argument', $file_path);

		# CODE 303

		$some_calls = array_filter($nodes['calls'], function (Node $node) use (&$danger) {
			return $node->name instanceof Node\Name && $node->name->toLowerString() == 'create_function';
		}
		);

		$res = [];
		foreach ($some_calls as $node)
		{
			$flag = $nodeFinder->findFirst($node->args,
				function (Node $node) {
					return ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name && (!function_exists($node->name->toLowerString())
								|| in_array($node->name->toLowerString(), self::$cryptors, true)
								|| in_array($node->name->toLowerString(), self::$string_change, true))
						) ||
						($node instanceof Node\Scalar\String_ && preg_match('/(?:assert|' . implode('|', self::$cryptors) . ')/i', $node->value));
				}
			);

			if ($flag)
			{
				$res[] = $node;
			}
		}

		$this->CheckResults($res, '[303] create_function', $file_path);

		# CODE 304

		$clb = ['filter_input', 'filter_input_array', 'filter_var', 'filter_var_array'];

		$some_calls = array_filter($nodes['calls'], function (Node $node) use (&$clb) {
			return $node->name instanceof Node\Name && in_array($node->name->toLowerString(), $clb, true);
		}
		);

		$res = [];
		foreach ($some_calls as $node)
		{
			if (preg_match_all('#(?:_POST|_GET|_COOKIE|_REQUEST|FILTER_CALLBACK|1024|filter_input|filter_var)|' . self::$evals_reg . '|' . self::$functions . '#i', $pprinter->prettyPrint($node->args)) > 1)
			{
				$res[] = $node;
			}
		}

		$this->CheckResults($res, '[304] filter_callback', $file_path);

		# CODE 305

		$res = [];
		foreach ($nodes['evals'] as $node)
		{
			$flag = $nodeFinder->findFirst($node->expr,
				function (Node $node) {
					return ($node instanceof Node\Expr\FuncCall && (
							($node->name instanceof Node\Name && !function_exists($node->name->toLowerString())) ||
							($node->name instanceof Node\Expr\Variable) ||
							($node->name instanceof Node\Expr\ArrayDimFetch)
						)
					);
				}
			);

			if ($flag)
			{
				$node->setAttribute('comment', 'strange code');
				$res[] = $node;
			}
		}

		$this->CheckResults($res, '[305] strange function and eval', $file_path);

		# CODE 306

		$eregi_map = [
			'mb_eregi_replace' => [1],
			'mb_ereg_replace' => [1],
		];

		$config = self::genConfig();
		$res = $this->checkFuncCalls($nodes['calls'], $eregi_map, $nodeFinder, $vars, $config);

		$this->CheckResults($res, '[302] eregi', $file_path);

		# CODE 307

		$res = [];
		foreach ($nodes['mtds'] as $node)
		{
			$class = $node->class instanceof Node\Name ? $node->class->toString() : '';
			$mtd = $node->name instanceof Node\Identifier ? $node->name->toString() : '';

			if (!$class || !$mtd)
			{
				continue;
			}

			$class_method = "$class::$mtd";
			foreach (self::$mehtods as $mtd)
			{
				if (stripos($class_method, $mtd) !== false)
				{
					$arg = isset($node->args[0]) ? $node->args[0] : false;

					[$flag, $comment] = $this->CheckArg($arg->value, $vars, $config);
					if ($flag)
					{
						$res[] = $node;
						$node->setAttribute('comment', $comment);
					}
					break;
				}
			}
		}

		$this->CheckResults($res, '[307] danger method', $file_path);

		# CODE 400

		$config = self::genConfig(['hardcoded' => true]);

		$res = [];
		foreach ($nodes['auth'] as $node)
		{
			$arg = isset($node->args[0]) ? $node->args[0] : false;
			$flag = false;
			$comment = '';

			[$flag, $comment] = $this->CheckArg($arg->value, $vars, $config);
			if ($flag)
			{
				$node->setAttribute('comment', $comment);

				$res[] = $node;
			}
		}

		$this->CheckResults($res, '[400] bitrix auth', $file_path);

		# CODE 600

		$res = [];
		$config = self::genConfig(['concat' => false, 'value' => false]);

		foreach ($nodes['includes'] as $node)
		{
			$flag = false;
			$comment = '';

			$inc = $pprinter->prettyPrintExpr($node->expr);

			if (preg_match('/\.(gif|png|jpg|jpeg|var|pdf|exe)/i', $inc))
			{
				$flag = true;
				$comment = 'gif|png|jpg|jpeg|var|pdf|exe';
			}
			elseif (preg_match('#(https?|ftps?|compress\.zlib|php|glob|data|phar)://#i', $inc))
			{
				$flag = true;
				$comment = 'wrapper';
			}
			else
			{
				[$flag, $comment] = $this->CheckArg($node->expr, $vars, $config);
//                    $flag = $flag || $nodeFinder->findFirst($node->expr,
//                            function (Node $node) use (&$vars) {
//                                return $node instanceof Node\Expr\Variable && is_string($node->name) && $node->name && (isset($vars['request'][$node->name]) || isset($vars['crypted'][$node->name]));
//                            }
//                        );
			}

			if ($flag)
			{
				$node->setAttribute('comment', $comment);
				$res[] = $node;
			}
		}

		$this->CheckResults($res, '[600] strange include', $file_path);

		# CODE 610 615 620

		$checked = [];
		foreach ($nodes['variables'] as $var)
		{
			$v = $this->pprinter->prettyPrintExpr($var);
			if (in_array($v, $checked, true))
			{
				continue;
			}
			$checked[] = $v;
            if (preg_match('#\$_{3,}#i', $v) || preg_match('#\$\{.*?(?:->|::|\()#i', $v))
			{
				$subj = '[610] strange vars';
				$checksum = $this->CalcChecksum($file_path, $v, $subj);
				if (!$this->IsFalsePositive($checksum))
				{
					$this->addResult($subj, $v, self::CalcCrit($subj), $checksum);
				}
			}

			if (preg_match('#\${["\']\\\\x[0-9]{2}[a-z0-9\\\\]+["\']}#i', $v))
			{
				$subj = '[615] hidden vars';
				$checksum = $this->CalcChecksum($file_path, $v, $subj);
				if (!$this->IsFalsePositive($checksum))
				{
					$this->addResult($subj, $v, self::CalcCrit($subj), $checksum);
				}
			}

			if (preg_match("#\$(?:[\x80-\xff][_\x80-\xff]*|_(?:[\x80-\xff][_\x80-\xff]*|_[_\x80-\xff]+))" . self::$spaces . '=#i', $v))
			{
				$subj = '[620] binary vars';
				$checksum = $this->CalcChecksum($file_path, $v, $subj);
				if (!$this->IsFalsePositive($checksum))
				{
					$this->addResult($subj, $v, self::CalcCrit($subj), $checksum);
				}
			}
		}

		# CODE 650

		$res = [];
		$config = self::genConfig();

		foreach ($nodes['calls'] as $node)
		{
			$flag = false;
			$comment = '';
			if ($node->name instanceof Node\Expr\Variable)
			{
				$var = is_string($node->name) ? '$' . $node->name : $this->pprinter->prettyPrintExpr($node->name);
				$name = substr($var, 1);

				[$flag, $comment] = $this->CheckArg($node->name, $vars, $config);
				if (!$flag)
				{
					$flag = $nodeFinder->findFirst($node->args,
						function (Node $node) {
							return $node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Expr\Variable;
						});

					if ($flag)
					{
						$comment = 'functions inside';
					}
				}

				if (!$flag)
				{
					foreach ($node->args as $arg)
					{
                        if ($arg instanceof Node\Arg)
                        {
							[$flag, $comment] = $this->CheckArg($arg->value, $vars, $config);
							if ($flag)
							{
								$comment = $comment;
								break;
							}
						}
					}
                }
				if (!$flag && !in_array($name, $vars['closures'], true) && !$in_closure)
				{
					$comment = 'other';
					if (self::isVarStrange($var))
					{
						$comment = 'strange var';
					}
					$flag = true;
				}

				if ($flag)
				{
					$node->setAttribute('comment', $comment);
					$res[] = $node;
				}
			}
		}

		$this->CheckResults($res, '[650] variable as a function', $file_path);

		# CODE 660

		$config = self::genConfig();

		$res = [];
		foreach ($nodes['calls'] as $node)
		{
			if ($node->name instanceof Node\Expr\ArrayDimFetch)
			{
				$res[] = $node;

				[$flag, $comment] = $this->CheckArg($node->name, $vars, $config);

				if (!$flag)
				{
					foreach ($node->args as $arg)
					{
						[$flag, $comment] = $this->CheckArg($arg->value, $vars, $config);
						if ($flag)
						{
							break;
						}
					}
				}

				if ($flag)
				{
					$node->setAttribute('comment', $comment);
				}
			}
		}

		$this->CheckResults($res, '[660] array member as a function', $file_path);

		# CODE 662

		$config = self::genConfig();

		$res = [];
		foreach ($nodes['calls'] as $node)
		{
			if ($node->name instanceof Node\Expr\FuncCall)
			{
				$res[] = $node;

				[$flag, $comment] = $this->CheckArg($node->name, $vars, $config);
				if ($flag)
				{
					$node->setAttribute('comment', $comment);
				}
			}
		}

		$this->CheckResults($res, '[662] function return as a function', $file_path);

		$res = [];
		foreach ($nodes['calls'] as $node)
		{
			if ($node->name instanceof Node\Scalar\String_ || $node->name instanceof Node\Expr\BinaryOp)
			{
				$res[] = $node;

				[$flag, $comment] = $this->CheckArg($node->name, $vars, $config);
				if ($flag)
				{
					$node->setAttribute('comment', $comment);
				}
			}
		}

		$this->CheckResults($res, '[663] strange function', $file_path);

		# CODE 665
		$res = [];
		foreach ($nodes['strings'] as $node)
		{
			$str = $node->value;

			$str2 = base64_decode($str);
			if ($str2 && preg_match('#(' . self::$request . '|' . self::$functions . '|' . self::$evals_reg . '|' . self::$black_reg . ')#i', $str2))
			{
				$res[] = $node;
				continue;
			}

			if (preg_match('/^[a-z\s0-9:._-]+$/i', $str))
			{
				continue;
			}

			if (strpos($str, '<?') !== false && preg_match('#(' . self::$request . '|' . self::$functions . '|' . self::$evals_reg . '|' . self::$black_reg . ')#i', $str))
			{
				$subscan = new CBitrixXscan();
				$subscan->collect_exceptions = false;
				// $str2 = "<?php\n" . $str;
				if ($subscan->CheckCode($str))
				{
					$res[] = $node;
				}
				unset($str, $str2, $subscan);
			}
		}

		$this->CheckResults($res, '[665] encoded code', $file_path);

		# CODE 887

		$this->CheckResults($nodes['backticks'], '[887] backticks', $file_path);

		unset($stmts, $nodes, $some_calls, $res, $req, $code);
	}

	public static function genConfig($options = false)
	{
		$config = self::$default_config;
		if (is_array($options))
		{
			foreach ($options as $key => $val)
			{
				$config[$key] = $val;
			}
		}

		return $config;
	}

	public function checkFuncCalls(&$all_calls, &$funcs_map, &$nodeFinder, &$vars, &$config)
	{
		$funcs = array_keys($funcs_map);

		$some_calls = array_filter($all_calls, function (Node $node) use (&$funcs) {
			return $node->name instanceof Node\Name && in_array($node->name->toLowerString(), $funcs, true);
        });

		$result = [];
		foreach ($some_calls as $node)
		{
			$ret = false;
			$func = $node->name->toLowerString();

			$comment = '';

			foreach ($funcs_map[$func] as $i)
			{
				if (!isset($node->args[$i]) || $ret)
				{
					continue;
				}

				if ($node->args[$i] instanceof Node\Arg && $node->args[$i]->value instanceof Node\Expr\Closure)
				{
					continue;
				}

				$arg = $node->args[$i]->value;

				[$ret, $comment] = $this->CheckArg($arg, $vars, $config);
			}

			if ($ret && $comment)
			{
				$node->setAttribute('comment', $comment);
				$result[] = $node;
			}
		}
		return $result;
	}

	public function parseValue($node, &$vars)
	{
		$ret = '';
		$temp_name = false;

		while ($node instanceof Node\Expr\ArrayDimFetch || $node instanceof Node\Expr\PropertyFetch)
		{
			if ($node instanceof Node\Expr\ArrayDimFetch && $node->dim instanceof Node\Scalar\String_ and $node->dim->value == 'tmp_name')
			{
				$temp_name = true;
			}
			if ($node instanceof Node\Expr\ArrayDimFetch && $node->var instanceof Node\Expr\Variable && $node->var->name == 'GLOBALS'
				&& $node->dim instanceof Node\Scalar\String_)
			{
				$node = new Node\Expr\Variable($node->dim->value);
			}
			else
			{
				$node = $node->var;
			}
		}

		if ($node instanceof Node\Expr\Variable)
		{
			$name = $node->name;
			if (is_string($name) && $name)
			{
				if (isset($vars['values'][$name]))
				{
					$ret = $vars['values'][$name];
				}
				elseif (isset($vars['request'][$name]) && !$temp_name)
				{
					$ret = '$_REQUEST';
				}
				elseif (isset($vars['from_request'][$name]))
				{
					$ret = '$_FROM_REQUEST';
				}
				elseif (isset($vars['crypted'][$name]))
				{
					$ret = 'CRYPTED';
				}
				elseif (isset($vars['params'][$name]))
				{
					$ret = 'PARAMS';
				}
			}
			elseif (!is_string($name))
			{
				$ret = $name = $this->parseValue($name, $vars);
			}
		}
		elseif ($node instanceof Node\Expr\BinaryOp)
		{
			$left = $this->parseValue($node->left, $vars);
			$right = $this->parseValue($node->right, $vars);

			if ($node instanceof Node\Expr\BinaryOp\Div && (int)$right != 0)
			{
				$ret = (string)((int)$left / (int)$right);
			}
			elseif ($node instanceof Node\Expr\BinaryOp\Mul)
			{
				$ret = (string)((int)$left * (int)$right);
			}
			elseif ($node instanceof Node\Expr\BinaryOp\Minus)
			{
				$ret = (string)((int)$left - (int)$right);
			}
			elseif ($node instanceof Node\Expr\BinaryOp\Plus)
			{
				$ret = (string)((int)$left + (int)$right);
			}
			elseif ($node instanceof Node\Expr\BinaryOp\BitwiseXor)
			{
				$ret = (string)($left ^ $right);
			}
			else
			{
				$ret = $left . $right;
			}
		}
		elseif ($node instanceof Node\Scalar\Encapsed)
		{
			foreach ($node->parts as $part)
			{
				$part = $this->parseValue($part, $vars);
				$ret .= $part;
			}
		}
		elseif ($node instanceof Node\Scalar\LNumber ||
			$node instanceof Node\Scalar\DNumber ||
			$node instanceof Node\Scalar\String_ ||
			$node instanceof Node\Scalar\EncapsedStringPart
		)
		{
			$ret = (string)$node->value;
		}
		elseif ($node instanceof Node\Expr\FuncCall)
		{
			$name = $node->name instanceof Node\Name ? $node->name->toLowerString() : "\$v";

			if ($name === 'chr')
			{
				$v = $this->parseValue($node->args[0]->value, $vars);
				$ret = chr((int)$v);
			}
			else
			{
				$args = [];
				foreach ($node->args as $arg)
				{
                    if ($arg instanceof Node\Arg)
                    {
						$args[] = $this->parseValue($arg->value, $vars);
					}
                }

				$ret = "$name(" . implode(",", $args) . ")";

				[$a, $b] = self::checkString($ret);
				$ret = $a ? $b : '';
				unset($args);
			}
		}
		elseif ($node instanceof Node\Expr\Ternary)
		{
			$if = $this->parseValue($node->if, $vars);
			$ret = $if ?: $this->parseValue($node->else, $vars);
		}
		elseif ($node && property_exists($node, 'expr'))
		{
			return $this->parseValue($node->expr, $vars);
		}

		return $ret;
	}

	public function CheckArg($arg, &$vars, &$config)
	{
		$ret = false;
		$comment = '';
		$temp_name = false;

		while ($arg instanceof Node\Expr\ArrayDimFetch || $arg instanceof Node\Expr\PropertyFetch)
		{
			if ($arg instanceof Node\Expr\ArrayDimFetch && $arg->dim instanceof Node\Scalar\String_ and $arg->dim->value == 'tmp_name')
			{
				$temp_name = true;
			}

			if ($arg instanceof Node\Expr\ArrayDimFetch && $arg->var instanceof Node\Expr\Variable && $arg->var->name == 'GLOBALS'
				&& $arg->dim instanceof Node\Scalar\String_)
			{
				$arg = new Node\Expr\Variable($arg->dim->value);
			}
			else
			{
				$arg = $arg->var;
			}
		}

		$temp_name = $temp_name && ($arg instanceof Node\Expr\Variable and is_string($arg->name) && $arg->name == '_FILES');

		while ($arg instanceof Node\Expr\ConstFetch)
		{
			$arg = $arg->name;
		}

		if ($config['hardcoded'] && (
				$arg instanceof Node\Scalar\LNumber ||
				$arg instanceof Node\Scalar\DNumber ||
				$arg instanceof Node\Scalar\String_ ||
				$arg instanceof Node\Scalar\Encapsed)
		)
		{
			$comment = 'hardcoded value';
			$ret = true;
		}
		elseif ($arg instanceof Node\Expr\Variable)
		{
			$name = $arg->name;
			if (!is_string($name))
			{
				$ret = true;
				$comment = 'crypted var';
			}
			else
			{
				$ret = is_string($name) && $name && ($config['files'] || (!$config['files'] && !$temp_name)) && (
						($config['request'] && isset($vars['request'][$name]) && $comment = 'request') ||
						($config['from_request'] && isset($vars['from_request'][$name]) && $comment = 'var from request') ||
						($config['crypted'] && isset($vars['crypted'][$name]) && $comment = 'crypted var') ||
						($config['assigned'] && !isset($vars['assigned'][$name]) && $comment = 'var was not assigned') ||
						($config['params'] && isset($vars['params'][$name]) && $comment = 'var from params') ||
						($name == 'GLOBALS' && $comment = 'strange globals')
					);
			}
		}
		elseif ($arg instanceof Node\Expr\FuncCall && $arg->name instanceof Node\Name)
		{
			$name = $arg->name->toLowerString();
			$ret = in_array($name, self::$evals, true) || in_array($name, ['getenv', 'debug_backtrace'], true) || ($config['crypted'] && in_array($name, self::$cryptors, true));
			if (!$ret)
			{
				foreach ($arg->args as $argv)
				{
					[$ret, $comment] = $this->CheckArg($argv->value, $vars, $config);
					if ($ret)
					{
						break;
					}
				}
			}
			else
			{
				$comment = 'danger function';
			}
		}
		elseif ($arg instanceof Node\Scalar\String_)
		{
			$comment = 'danger function';
			$ret = preg_match('/^(' . implode('|', self::$evals) . '|call_user_func|getenv)$/i', $arg->value);
		}
		elseif ($arg instanceof Node\Scalar\EncapsedStringPart)
		{
			$comment = 'danger function';
			$ret = preg_match('/(' . implode('|', self::$evals) . '|call_user_func|getenv)/i', $arg->value);
		}
		elseif ($arg instanceof Node\Name)
		{
			$comment = 'danger function';
			$func = $arg->toLowerString();
			$ret = preg_match('/(' . implode('|', self::$evals) . '|call_user_func|getenv)/i', $func);
		}
		elseif ($arg instanceof Node\Expr\BinaryOp\Concat || $arg instanceof Node\Expr\BinaryOp\Coalesce)
		{
			[$a, $b] = $this->CheckArg($arg->left, $vars, $config);
			if ($a)
			{
				[$ret, $comment] = [$a, $b];
			}
			else
			{
				[$a, $b] = $this->CheckArg($arg->right, $vars, $config);
				if ($a)
				{
					[$ret, $comment] = [$a, $b];
				}
				elseif ($config['concat'])
				{
					$comment = 'strange concatination';
					$ret = true;
				}
			}
		}
		elseif ($arg instanceof Node\Scalar\Encapsed)
		{
			foreach ($arg->parts as $part)
			{
				[$a, $b] = $this->CheckArg($part, $vars, $config);
				if ($a)
				{
					[$ret, $comment] = [$a, $b];
				}
			}
		}
		elseif ($arg instanceof Node\Expr\Ternary)
		{
			[$a, $b] = $this->CheckArg($arg->if, $vars, $config);
			if ($a)
			{
				[$ret, $comment] = [$a, $b];
			}
			else
			{
				[$a, $b] = $this->CheckArg($arg->else, $vars, $config);
				if ($a)
				{
					[$ret, $comment] = [$a, $b];
				}
			}
		}

//        print_r($arg);

		if (!$ret && $config['value'])
		{
			$val = $this->parseValue($arg, $vars);

			if ($config['recursive'])
			{
				$subscan = new CBitrixXscan();
				$subscan->collect_exceptions = false;
				$res = $subscan->CheckCode($val);
				if ($res)
				{
					[$ret, $comment] = [true, 'recursive'];
				}
				unset($subscan);
			}
			else
			{
				[$ret, $comment] = self::checkString($val);
			}
		}

		return [$ret, $comment];
	}

	public static function checkString($val)
	{
		$ret = '';
		$comment = '';

		if (preg_match('/BXS_(?:EVAL|CRYPTED|BLACKLIST|REQUEST)/', $val, $m))
		{
			$ret = true;
			$comment = $m[0];
		}
		elseif (preg_match('/\b(' . implode('|', self::$evals) . '|getenv)\b/i', $val))
		{
			$ret = true;
			$comment = 'BXS_EVAL';
		}
		elseif (preg_match('/\b(' . implode('|', self::$cryptors) . '|CRYPTED)\b/i', $val))
		{
			$ret = true;
			$comment = 'BXS_CRYPTED';
		}
		elseif (preg_match('#\b' . self::$black_reg . '\b#i', $val))
		{
			$ret = true;
			$comment = 'BXS_BLACKLIST';
		}
		elseif (preg_match('/(\$_REQUEST|\$_FROM_REQUEST)/i', $val))
		{
			$ret = true;
			$comment = 'BXS_REQUEST';
		}
		return [$ret, $comment];
	}

	public static function isVarStrange($var)
	{
		$ret = 0;
		$ret = preg_match('/^\$_?([0o]+|[1li]+)$/i', $var); // obfusacator
		$ret = $ret || preg_match('/^\$__/i', $var) || $var == '$_';
		$ret = $ret || preg_match('/__/', $var);
		$ret = $ret || preg_match('/^\$_*[a-z0-9]{1,2}$/i', $var);  // very short

		$ret = $ret || preg_match('/\d{2,}$/i', $var); // 2+ digits in the end
		$ret = $ret || preg_match_all('/[A-Z][a-z][A-Z]/', $var) > 1;  // CaSe dAnCe

		$ret = $ret || preg_match('/[^$a-z0-9_]/i', $var);
		$ret = $ret || preg_match('/[a-z]+[0-9]+[a-z]+/i', $var); // digits in centre

		$ret = $ret || (preg_match_all('#[qwrtpsdfghjklzxcvbnm]{4,}#i', $var, $regs)
				&& (strlen(implode('', $regs[0])) / strlen($var) > 0.4));

		return $ret > 0;
	}

	public static function CalcCrit($subj, $com = '')
	{
		if (!isset(self::$scoring[$subj]))
		{
			die("error: " . $subj);
		}

		static $nums = [
			'self' => 0,
			'strange concatination' => 1,
			'hardcoded value' => 2,
			'request' => 3,
			'danger function' => 4,
			'var from params' => 5,
			'var was not assigned' => 6,
			'crypted var' => 7,
			'var from request' => 8,
		];

		$self = self::$scoring[$subj][0];

		if ($com == 'other')
		{
			$arg = 0.3;
		}
		else
		{
			$num = isset($nums[$com]) ? $nums[$com] : 0;
			$arg = isset(self::$scoring[$subj][$num]) ? self::$scoring[$subj][$num] : 1;
		}

		return round($self * $arg, 2);
	}

	public function CheckResults(&$res, $subj, $file_path)
	{
		foreach ($res as $r)
		{
			$code = $this->pprinter->prettyPrintExpr($r);

			$com = $r->getAttribute('comment', '');
			$crit = self::CalcCrit($subj, $com);
			$checksum = $this->CalcChecksum($file_path, $code, $subj);

			$str = defined('XSCAN_DEBUG') ? "$subj [$com] | $crit | $checksum" : $subj;

			if (!$this->IsFalsePositive($checksum))
			{
				$this->addResult($str, $code, $crit, $checksum);
			}
		}
	}

	public static function ParseNode(&$node)
	{
		if (isset($arr[0]))
		{
			foreach ($node as $v)
			{
				self::ParseNode($v);
			}
			return;
		}
	}

	static function CountVars($str)
	{
		$regular = '#' . self::$var . '#';
		if (!preg_match_all($regular, $str, $regs))
		{
			return 0;
		}
		$ar0 = $regs[0];
		$ar0 = array_unique($ar0);
		$ar0 = array_filter($ar0, function ($v) {
			return !in_array($v, ['$_GET', '$_POST', '$_REQUEST', '$_GET', '$_SERVER', '$_FILES', '$APPLICATION', '$DB', '$USER']);
		});

		return count($ar0);
	}

	static function StatVulnCheck($str, $bAll = false)
	{
		$regular = $bAll ? '#\$?[a-z_]+#i' : '#' . self::$var . '#';
		if (!preg_match_all($regular, $str, $regs))
		{
			return false;
		}
		$ar0 = $regs[0];
		$ar1 = array_unique($ar0);
		$uniq = count($ar1) / count($ar0);

		$ar2 = [];
		foreach ($ar1 as $var)
		{
			if ($bAll && function_exists($var))
			{
				$p = 0;
			}
			elseif ($bAll && preg_match('#^[a-z]{1,2}$#i', $var))
			{
				$p = 1;
			}
			elseif (preg_match('#^\$?(function|php|csv|sql|__DIR__|__FILE__|__LINE__|DBDebug|DBType|DBName|DBPassword|DBHost|APPLICATION)$#i', $var))
			{
				$p = 0;
			}
			elseif (preg_match('#__#', $var))
			{
				$p = 1;
			}
			elseif (preg_match('#^\$(ar|str)[A-Z]#', $var, $regs))
			{
				$p = 0;
			}
			elseif (preg_match_all('#([qwrtpsdfghjklzxcvbnm]{3,}|[a-z]+[0-9]+[a-z]+)#i', $var, $regs))
			{
				$p = strlen(implode('', $regs[0])) / strlen($var) > 0.3;
			}
			else
			{
				$p = 0;
			}

			$ar2[] = $p;
		}
		$prob = array_sum($ar2) / count($ar2);
		if ($prob < 0.3)
		{
			return false;
		}

		if (!$bAll)
		{
			return self::StatVulnCheck($str, true);
		}

		return true;
	}

	function Search($path, $mode = 'search')
	{
		$path = str_replace('\\', '/', $path);
		do
		{
			$path = str_replace('//', '/', $path, $flag);
		}
		while ($flag);

		if (php_sapi_name() != "cli")
		{
			header('xscan-bp: ' . $path, true);
		}

		if ($this->start_time && time() - $this->start_time > $this->time_limit)
		{
			if ($mode == 'search' && !$this->break_point)
			{
				$this->break_point = $path;
			}
			if ($mode == 'count')
			{
				$this->total = 0;
			}
			return;
		}

		if ($mode == 'search' && $this->skip_path && !$this->found)
		{
			if (strpos($this->skip_path, dirname($path)) !== 0)
			{
				return;
			}

			if ($this->skip_path == $path)
			{
				$this->found = true;
			}
		}

		if (is_dir($path)) // dir
		{
			$p = realpath($path);

			if (is_link($path))
			{
				$d = dirname($path);
				if (strpos($p, $d) !== false || strpos($d, $p) !== false)
				{
					return true;
				}
			}

			$dir = opendir($path);
			$isbitrix = basename($path) == 'bitrix' && is_file($path . '/.settings.php');

			while ($item = readdir($dir))
			{
				if ($item == '.' || $item == '..')
				{
					continue;
				}

				if ($isbitrix && in_array($item, ['cache', 'managed_cache', 'stack_cache', 'updates']))
				{
					continue;
				}

				$this->Search($path . '/' . $item, $mode);
			}
			closedir($dir);
		}
		elseif (preg_match('/(?:\.htaccess|\.php[578]?)$/i', $path)) // file
		{
			if ($mode == 'count')
			{
				$this->total += 1;
				return;
			}

			if (!$this->skip_path || $this->found)
			{
				$this->progress += 1;
				$res = $this->CheckFile($path);
				if ($res)
				{
					$this->pushResult($path);
				}
			}
		}
	}

	function SystemFile($f)
	{
		static $system = [
			'/bitrix/modules/controller/install/activities/bitrix/controllerremoteiblockactivity/controllerremoteiblockactivity.php',
			'/bitrix/activities/bitrix/controllerremoteiblockactivity/controllerremoteiblockactivity.php',
			'/bitrix/modules/main/classes/general/update_class.php',
			'/bitrix/modules/main/classes/general/file.php',
			'/bitrix/modules/imconnectorserver/lib/connectors/telegrambot/emojiruleset.php',
			'/bitrix/modules/imconnectorserver/lib/connectors/facebook/emojiruleset.php',
			'/bitrix/modules/main/include.php',
			'/bitrix/modules/main/classes/general/update_client.php',
			'/bitrix/modules/main/install/wizard/wizard.php',
			'/bitrix/modules/main/start.php',
			'/bitrix/modules/landing/lib/mutator.php',
			'/bitrix/modules/main/tools.php',
			'/bitrix/modules/main/lib/engine/response/redirect.php',
			'/bitrix/modules/main/lib/config/option.php',
			'/bitrix/modules/main/classes/general/main.php',
			'/bitrix/modules/main/lib/UpdateSystem/PortalInfo.php',
			'/bitrix/modules/main/lib/UpdateSystem/HashCodeParser.php',
			'/bitrix/modules/main/lib/UpdateSystem/ActivationSystem.php',
			'/bitrix/modules/main/lib/license.php',
            '/bitrix/modules/crm/classes/general/sql_helper.php'

		];
		foreach ($system as $path)
		{
			if (preg_match('#' . $path . '$#', $f))
			{
				return true;
			}
		}
		return false;
	}

	function pushResult($f)
	{
		$message = [];
		foreach ($this->results as $res)
		{
			$message[] = $res['subj'];
		}

		if (is_array($message))
		{
			$message = implode(' <br> ', array_unique($message));
		}

		$stat = @stat($f);

		$result = (new XScanResult)->setType('file')->setSrc($f)->setScore($this->score)->setMessage($message);
		if (is_array($stat))
		{
			$result->setCtime(ConvertTimeStamp($stat['ctime'], "FULL"));
			$result->setMtime(ConvertTimeStamp($stat['mtime'], "FULL"));
		}
		$result->setTags(implode(' ', $this->tags));

		$this->result_collection[] = $result;
	}

	function SavetoDB()
	{
		if (isset($this->result_collection) && $this->result_collection)
		{
			$this->result_collection->save(true);
		}
		unset($this->result_collection);
	}

	static function ShowMsg($str, $color = 'green')
	{
		$class = $color == 'green' ? 'ui-alert-primary ui-alert-icon-info' : 'ui-alert-danger ui-alert-icon-danger';
		return '<br><div class="ui-alert ' . $class . '"><span class="ui-alert-message">' . $str . '</span></div><br>';
	}

	static function HumanSize($s)
	{
		$i = 0;
		$ar = ['b', 'kb', 'M', 'G'];
		while ($s > 1024)
		{
			$s /= 1024;
			$i++;
		}
		return round($s, 1) . ' ' . $ar[$i];
	}

	static function getIsolateButton($file_path)
	{
		$file_path = htmlspecialcharsbx(CUtil::JSEscape($file_path));
		return '<a class="ui-btn ui-btn-danger ui-btn-sm" style="text-decoration: none; color: #ffffff;" onclick="xscan_prison(\'' . $file_path . '\')">' . GetMessage("BITRIX_XSCAN_ISOLATE") . '</a>';
	}

	static function getUnIsolateButton($file_path)
	{
		$file_path = htmlspecialcharsbx(CUtil::JSEscape($file_path));
		return '<a class="ui-btn ui-btn-primary ui-btn-sm" style="text-decoration: none; color: #ffffff;" onclick="xscan_release(\'' . $file_path . '\')">' . GetMessage("BITRIX_XSCAN_UNISOLATE") . '</a>';
	}

	static function getHideButton($file_path)
	{
		$file_path = htmlspecialcharsbx(CUtil::JSEscape($file_path));
		return '<a class="ui-btn ui-btn-success ui-btn-sm" style="text-decoration: none; color: #ffffff;" onclick="xscan_hide(\'' . $file_path . '\')">' . GetMessage("BITRIX_XSCAN_HIDE_BTN") . '</a>';
	}

	static function getFileWatchLink($file_path)
	{
		return sprintf(
			'<a href="?action=showfile&file=%s">%s</a>',
			urlencode($file_path),
			htmlspecialcharsbx($file_path)
		);
	}

	static function getFileWatchButton($file_path)
	{
		return sprintf(
			'<a class="ui-btn ui-btn-sm" style="text-decoration: none; color: #ffffff;" target="_blank" href="?action=showfile&file=%s">' . GetMessage("BITRIX_XSCAN_WATCH_EVENT") . '</a>',
			urlencode($file_path)
		);
	}

	static function getEventWatchLink($event, $table, $id)
	{
		return sprintf(
			'<a target="_blank" href="/bitrix/admin/perfmon_row_edit.php?table_name=%s&pk[ID]=%d">%s</a>',
			$table,
			$id,
			htmlspecialcharsbx($event)
		);
	}

	static function getEventWatchButton($table, $id)
	{
		return sprintf(
			'<a class="ui-btn ui-btn-sm" target="_blank" style="text-decoration: none; color: #ffffff;" href="/bitrix/admin/perfmon_row_edit.php?table_name=%s&pk[ID]=%d">' . GetMessage("BITRIX_XSCAN_WATCH_EVENT") . '</a>',
			$table,
			$id
		);
	}

	static function getTotal($filter)
	{
		return XScanResultTable::getCount($filter);
	}

	static function getList($filter, $nav, $sort)
	{
		$output = [];

		$results = XScanResultTable::getList([
			'filter' => $filter,
			'offset' => $nav->getOffset(),
			'limit' => $nav->getlimit(),
            'order' => $sort['sort']
		]);

		foreach ($results as $result)
		{
			if ($result['TYPE'] === 'file')
			{
				$type = $result['MESSAGE'];
				$f = $result['SRC'];

				$code = preg_match('#\[([0-9]+)\]#', $type, $regs) ? $regs[1] : 0;
				$fu = urlencode(trim($f));
				$bInPrison = strpos('[100]', $type) === false;

				if (!file_exists($f) && file_exists($new_f = preg_replace('#\.php[578]?$#i', '.ph_', $f)))
				{
					$bInPrison = false;
					$f = $new_f;
					$fu = urlencode(trim($new_f));
				}

				$action = '';

				if (preg_match('/\.ph[_p][578]?$/i', $f))
				{
					$action = strtolower(substr($f, -4)) !== '.ph_' ? self::getIsolateButton($f) : self::getUnIsolateButton($f);
				}

				$output[] = [
					'data' => [
						'ID' => $result['ID'],
						'FILE_NAME' => self::getFileWatchLink($f),
						'FILE_TYPE' => $type,
						'FILE_SCORE' => $result['SCORE'],
						'FILE_SIZE' => self::HumanSize(@filesize($f)),
						'FILE_MODIFY' => $result['MTIME'],
						'FILE_CREATE' => $result['CTIME'],
						'TAGS' => $result['TAGS'],
						'ACTIONS' => $action,
						'HIDE' => self::getHideButton($f),
					]
				];
			}
			else
			{
				$table = $result['TYPE'] === 'agent' ? 'b_agent' : 'b_module_to_module';
				$output[] = [
					'data' => [
						'ID' => $result['ID'],
						'FILE_NAME' => self::getEventWatchLink($result['TYPE'] . " " . $result['SRC'], $table, $result['SRC']),
						'FILE_TYPE' => $result['MESSAGE'],
						'FILE_SCORE' => $result['SCORE'],
						'ACTIONS' => self::getEventWatchButton($table, $result['SRC'])
					]
				];
			}
		}

		return $output;
	}
}
