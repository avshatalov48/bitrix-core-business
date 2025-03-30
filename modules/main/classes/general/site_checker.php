<?php

use Bitrix\Main\Application;
use Bitrix\Main\ModuleTable;

class CSiteCheckerTest
{
	const MIN_PHP_VER = '8.1.0';

	public $arTestVars;
	public $percent;
	public $last_function;
	public $strCurrentTestName;
	public $strNextTestName;
	public $result;
	public $LogResourse;
	public $LogResult;
	public $group_name;
	public $group_desc;
	public $test_percent = 0;
	public $strError = '';
	public $timeout = 10; // sec for one step
	public $strResult = '';
	public $step;
	public $fix_mode;
	public $cafile;
	public $force_repair;
	public $host;
	public $ssl;
	public $port;
	public $arTest = [];
	public $function;
	public $LogFile;

	public function __construct($step = 0, $fast = 0, $fix_mode = 0)
	{
		$this->step = intval($step);
		if (!$this->step)
		{
			$this->arTestVars['site_checker_success'] = 'Y';
		}
		$this->fix_mode = intval($fix_mode);
		$this->cafile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/cacert.pem';
		$this->force_repair = defined('SITE_CHECKER_FORCE_REPAIR') && SITE_CHECKER_FORCE_REPAIR === true;

		$this->host = isset($_REQUEST['HTTP_HOST']) && $_REQUEST['HTTP_HOST'] ? $_REQUEST['HTTP_HOST'] : 'localhost';
		if (!$fix_mode) // no need to know the host in fix mode
		{
			if (!preg_match('/^[a-z0-9.\-]+$/i', $this->host)) // cyrillic domain hack
			{
				$converter = new CBXPunycode();
				$this->host = $converter->Encode($this->host);
			}
		}
		$this->ssl = isset($_REQUEST['HTTPS']) && $_REQUEST['HTTPS'] == 'on';
		$this->port = isset($_REQUEST['SERVER_PORT']) && $_REQUEST['SERVER_PORT'] ? $_REQUEST['SERVER_PORT'] : ($this->ssl ? 443 : 80);

		$arTestGroup = [];
		$arGroupName = [];

		$arGroupName[1] = IsModuleInstalled('intranet') ? GetMessage("MAIN_SC_GENERAL") : GetMessage("MAIN_SC_GENERAL_SITE");
		$arGroupDesc[1] = GetMessage("MAIN_SC_REQUIRED_MODS_DESC");
		$arTestGroup[1] = [
			['check_php_modules' => GetMessage('SC_T_MODULES')],
			['check_php_settings' => GetMessage('SC_T_PHP')],
			['check_security' => GetMessage('SC_T_APACHE')],
			['check_server_vars' => GetMessage('SC_T_SERVER')],
			['check_session' => GetMessage('SC_T_SESS')],
			['check_mbstring' => GetMessage('SC_T_MBSTRING')],
			['check_install_scripts' => GetMessage('SC_T_INSTALL_SCRIPTS')],
			['check_socket' => GetMessage('SC_T_SOCK')],
			['check_bx_crontab' => GetMessage("MAIN_SC_AGENTS_CRON")],
		];

		$arGroupName[2] = GetMessage("MAIN_SC_BUSINESS");
		$arGroupDesc[2] = GetMessage("MAIN_SC_CORRECT_DESC");
		$arTestGroup[2] = [
			['check_pull_stream' => GetMessage("MAIN_SC_TEST_PUSH_SERVER")],
			['check_pull_comments' => GetMessage("MAIN_SC_TEST_COMMENTS")],
			['check_turn' => GetMessage("MAIN_SC_TEST_VIDEO")],
			['check_access_mobile' => GetMessage("MAIN_SC_TEST_MOBILE")],
			['check_push_bitrix' => GetMessage("MAIN_SC_TEST_PUSH")],
			['check_access_docs' => GetMessage("MAIN_SC_TEST_DOCS")],
			['check_fast_download' => GetMessage("MAIN_SC_TEST_FAST_FILES_MSGVER_1")],
			['check_search' => GetMessage("MAIN_SC_TEST_SEARCH_CONTENTS")],
			['check_mail' => GetMessage("MAIN_SC_MAIL_TEST")],
			['check_ca_file' => GetMessage("MAIN_SC_CLOUD_TEST")],
			['check_connect_mail' => GetMessage("MAIN_SC_TEST_MAIL_INTEGRATION")],
			['check_socnet' => GetMessage("MAIN_SC_TEST_SOCNET_INTEGRATION")],
			['check_rest' => GetMessage("MAIN_SC_TEST_REST")],
			['check_mail_push' => GetMessage("MAIN_SC_TEST_MAIL_PUSH")],
		];
		if (IsModuleInstalled('extranet'))
		{
			$arTestGroup[2][] = ['check_extranet' => GetMessage("MAIN_SC_EXTRANET_ACCESS")];
		}

		$arGroupName[4] = GetMessage("MAIN_SC_WINDOWS_ENV");
		$arGroupDesc[4] = '';
		$arTestGroup[4] = [
			['check_webdav' => GetMessage("MAIN_SC_DOCS_EDIT_MS_OFFICE")],
			['check_socket_ssl' => GetMessage("MAIN_SC_EXTERNAL_APPS_TEST")],
			['check_ad' => GetMessage("MAIN_SC_TEST_LDAP")],
			['check_ntlm' => GetMessage("MAIN_SC_TEST_NTLM")],
		];

		$arGroupName[8] = GetMessage("MAIN_SC_PERFORM");
		$arGroupDesc[8] = '';
		$arTestGroup[8] = [
			['check_perf' => GetMessage("MAIN_SC_PERF_TEST")],
			['check_compression' => GetMessage("MAIN_SC_COMPRESSION_TEST")],
		];

		$arGroupName[16] = GetMessage('SC_GR_EXTENDED');
		$arTestGroup[16] = [
			['check_dbconn' => GetMessage('SC_T_DBCONN')],
			['check_session_ua' => GetMessage('SC_T_SESS_UA')],
			['check_sites' => GetMessage('SC_T_SITES')],

			['check_pcre_recursion' => GetMessage('SC_T_RECURSION')],

			['check_upload' => GetMessage('SC_T_UPLOAD')],
			['check_upload_big' => GetMessage('SC_T_UPLOAD_BIG')],
			['check_upload_raw' => GetMessage('SC_T_UPLOAD_RAW')],
			['check_post' => GetMessage('SC_T_POST')],

			['check_mail' => GetMessage('SC_T_MAIL')],
			['check_mail_big' => GetMessage('SC_T_MAIL_BIG')],
			['check_mail_b_event' => GetMessage('SC_T_MAIL_B_EVENT')],

			['check_localredirect' => GetMessage('SC_T_REDIRECT')],
			['check_memory_limit' => GetMessage('SC_T_MEMORY')],
			['check_cache' => GetMessage('SC_T_CACHE')],

			['check_update' => GetMessage('SC_UPDATE_ACCESS')],
			['check_http_auth' => GetMessage('SC_T_AUTH')],
			['check_exec' => GetMessage('SC_T_EXEC')],
			['check_getimagesize' => GetMessage('SC_T_GETIMAGESIZE')],
		];

		$arGroupName[32] = GetMessage('SC_GR_MYSQL');
		$arTestGroup[32] = [
			['check_mysql_bug_version' => GetMessage('SC_T_MYSQL_VER')],
			['check_mysql_time' => GetMessage('SC_T_TIME')],
			['check_mysql_mode' => GetMessage('SC_T_SQL_MODE')],
			['check_mysql_connection_charset' => GetMessage('SC_CONNECTION_CHARSET')],
			['check_mysql_db_charset' => GetMessage('SC_DB_CHARSET')],
			['check_mysql_table_format' => GetMessage('SC_T_FORMAT')],
			['check_mysql_table_charset' => GetMessage('SC_T_CHARSET')],
			['check_mysql_table_structure' => GetMessage('SC_T_STRUCTURE')],
		];

		$arGroupName[64] = GetMessage('SC_GR_MYSQL');
		$arTestGroup[64] = [
			['check_pgsql_version' => GetMessage('SC_T_PGSQL_VER')],
			['check_mysql_time' => GetMessage('SC_T_TIME')],
			['check_pgsql_db_charset' => GetMessage('SC_DB_CHARSET')],
			['check_pgsql_connection_charset' => GetMessage('SC_CONNECTION_CHARSET')],
		];

		if ($this->fix_mode)
		{
			switch ($this->fix_mode)
			{
				case 1:
					$this->arTest = [
						['check_mysql_table_status' => GetMessage('SC_T_CHECK')],
					];
					break;
				case 2:
					$this->arTest = [
						['check_mysql_connection_charset' => GetMessage('SC_CONNECTION_CHARSET')],
						['check_mysql_db_charset' => GetMessage('SC_DB_CHARSET')],
						['check_mysql_table_format' => GetMessage('SC_T_FORMAT')],
						['check_mysql_table_charset' => GetMessage('SC_T_CHARSET')],
						['check_mysql_table_structure' => GetMessage('SC_T_STRUCTURE')],
					];
					break;
				case 3:
					$this->arTest = [
						['check_mysql_table_structure' => GetMessage('SC_T_STRUCTURE')],
					];
					break;
				case 4:
					$this->arTest = [
						['check_mbstring' => GetMessage('SC_T_MBSTRING')],
					];
					break;
				default:
					$this->arTest = [
						['check_server_vars' => GetMessage('SC_T_SERVER')],
					];
					break;
			}
		}
		else
		{
			$profile = 1;
			if ($fast)
			{
				if (IsModuleInstalled('intranet'))
				{
					$profile |= 2;
					$profile |= 4;
					$profile |= 8;
				}
			}
			else
			{
				$profile |= 16;
				switch (Application::getConnection()->getType())
				{
					case 'mysql':
						$profile |= 32;
						break;
					case 'pgsql':
						$profile |= 64;
						break;
				}
			}
			$this->arTest = [];
			$step0 = $step;
			foreach ($arTestGroup as $i => $ar)
			{
				if ($i & $profile)
				{
					if (!$this->group_name)
					{
						$c = count($ar);
						if ($step0 >= $c)
						{
							$step0 -= $c;
						}
						else
						{
							$this->group_name = $arGroupName[$i] ?? '';
							$this->group_desc = $arGroupDesc[$i] ?? '';
						}
					}
					$this->arTest = array_merge($this->arTest, $ar);
				}
			}
		}

		$this->function = key($this->arTest[$this->step]);
		$this->strCurrentTestName = current($this->arTest[$this->step]);
		$this->strNextTestName = $this->strCurrentTestName;

		$LICENSE_KEY = '';
		if (file_exists($file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix' . '/license_key.php'))
		{
			include($file);
		}
		if ($LICENSE_KEY == '')
		{
			$LICENSE_KEY = 'DEMO';
		}
		if (!defined('LICENSE_HASH'))
		{
			define('LICENSE_HASH', md5('CONNECTION_TEST'));
		}
		$this->LogFile = '/bitrix' . '/site_checker_' . md5('SITE_CHECKER' . $LICENSE_KEY) . '.log';
	}

	public static function GetTestList()
	{
		$ar = [];
		foreach (get_class_methods('CSiteCheckerTest') as $method)
		{
			if (str_starts_with($method, 'check_'))
			{
				$ar[] = $method;
			}
		}
		return $ar;
	}

	function Start()
	{
		$this->test_percent = 100; // by default

		ob_start();
		try
		{
			if ($this->fix_mode && (!isset($this->arTestVars['start_function']) || $this->arTestVars['start_function'] != $this->function))
			{
				// dummy hit to display zero progress
				$this->arTestVars['start_function'] = $this->function;
				$this->test_percent = 0;
				$this->result = true;
			}
			else
			{
				$this->result = call_user_func([$this, $this->function]);
			}
		}
		catch (Exception $e)
		{
			$this->Result(null, GetMessage("MAIN_SC_TEST_IS_INCORRECT"));
			echo $e->getMessage();
		}
		$this->strError = ob_get_clean();

		if (!$this->strResult)
		{
			$this->Result($this->result);
		}

		if (!$this->fix_mode)
		{
			// write to log
			if (@$this->OpenLog())
			{
				$text = date('Y-M-d H:i:s') . ' ' . $this->strCurrentTestName . ' (' . $this->function . "): " . $this->LogResult . "\n";
				if ($this->test_percent < 100)
				{
					$text .= $this->test_percent . '% done' . "\n";
				}

				if ($this->strError)
				{
					$text .= str_replace('<br>', "\n", $this->strError) . "\n";
				}

				if ($this->test_percent >= 100) // test finished
				{
					$text .= preg_replace('#<[^<>]+>#', '', $this->strResult) . "\n";
				}

				$text = htmlspecialchars_decode($text);

				fwrite($this->LogResourse, $text);
			}
		}

		$this->last_function = $this->function;
		$this->percent = floor(($this->step + $this->test_percent / 100) / count($this->arTest) * 100);

		if ($this->test_percent >= 100) // test finished
		{
			if ($this->step + 1 < count($this->arTest))
			{
				$this->step++;
				$this->test_percent = 0;
				$this->arTestVars['last_value'] = '';
				$this->function = key($this->arTest[$this->step]);
				$this->strNextTestName = current($this->arTest[$this->step]);
			}
			else // finish
			{
				if (!$this->fix_mode) // if we have a kernel
				{
					COption::SetOptionString('main', 'site_checker_success', $this->arTestVars['site_checker_success'] ?? '');
					if (isset($this->arTestVars['site_checker_success']) && $this->arTestVars['site_checker_success'] == 'Y')
					{
						CAdminNotify::DeleteByTag('SITE_CHECKER');
					}
				}
			}
		}
		elseif ($this->result === true)
		{
			$this->strResult = '';
		} // in case of temporary result on this step

		if ($this->result === false)
		{
			$this->arTestVars['site_checker_success'] = 'N';
		}
	}

	function Result($result, $text = '')
	{
		if ($result === true)
		{
			$this->LogResult = 'Ok';
		}
		elseif ($result === null)
		{
			$this->LogResult = 'Warning';
		}
		else
		{
			$this->LogResult = 'Fail';
		}

		if ($result === false)
		{
			$text = GetMessage('SC_ERROR0') . ' ' . ($text ?: GetMessage('SC_ERROR1'));
		}
		elseif ($result === null)
		{
			$text = GetMessage("MAIN_SC_SOME_WARNING") . '. ' . ($text ?: GetMessage('SC_WARN'));
		}
		else
		{
			$text = $text ?: GetMessage('SC_TEST_SUCCESS');
		}

		$this->strResult = $text;
		return $result;
	}

	function OpenLog()
	{
		$continue = $this->step > 0;
		if (!$this->LogResourse = fopen($_SERVER['DOCUMENT_ROOT'] . $this->LogFile, $continue ? 'ab' : 'wb'))
		{
			$this->arTestVars['site_checker_success'] = 'N';
		}
		return $this->LogResourse;
	}

	function ConnectToHost($host = false, $port = false, $ssl = false)
	{
		if (!$host)
		{
			if (!empty($this->arTestVars['check_socket_fail']))
			{
				return $this->Result(null, GetMessage('SC_SOCK_NA'));
			}

			$host = $this->host;
			$port = $this->port;
			$ssl = $this->ssl ? 'ssl://' : '';
		}

		echo "Connection to $ssl$host:$port	";
		$res = false;
		try
		{
			$res = fsockopen($ssl . $host, $port, $errno, $errstr, 5);
		}
		catch (Exception)
		{
		}

		if (!$res)
		{
			echo "Fail\n";
			echo "Socket error [$errno]: $errstr" . "\n";
			return $this->Result(false);
		}
		echo "Success\n";

		return $res;
	}

	function Unformat($str)
	{
		$str = strtolower($str);
		$res = intval($str);
		$suffix = substr($str, -1);
		if ($suffix == "k")
		{
			$res *= 1024;
		}
		elseif ($suffix == "m")
		{
			$res *= 1048576;
		}
		elseif ($suffix == "g")
		{
			$res *= 1048576 * 1024;
		}
		elseif ($suffix == "b")
		{
			$res = self::Unformat(substr($str, 0, -1));
		}
		return $res;
	}

	function TableFieldCanBeAltered($f, $f_tmp)
	{
		if ($f['Type'] == str_replace(['long', 'medium'], '', $f_tmp['Type']) || $this->force_repair)
		{
			return true;
		}
		if (
			preg_match('#^([a-z]+)\(([0-9]+)\)(.*)$#i', $f['Type'], $regs)
			&&
			preg_match('#^([a-z]+)\(([0-9]+)\)(.*)$#i', $f_tmp['Type'], $regs_tmp)
			&&
			str_replace('varchar', 'char', strtolower($regs[1])) == str_replace('varchar', 'char', strtolower($regs_tmp[1]))
			&&
			$regs[2] <= $regs_tmp[2]
			&&
			$regs[3] == $regs_tmp[3] // signed || unsigned
		)
		{
			return true;
		}
		return false;
	}

	###### TESTS #######
	# {
	#

	function check_php_modules()
	{
		$arMods = [
			'fsockopen' => GetMessage("SC_SOCKET_F"),
			'xml_parser_create' => GetMessage("SC_MOD_XML"),
			'imagettftext' => "Free Type Text",
			'gzcompress' => "Zlib",
			'imagecreatetruecolor' => GetMessage("SC_MOD_GD"),
			'imagecreatefromjpeg' => GetMessage("SC_MOD_GD_JPEG"),
			'hash' => 'Hash',
			'highlight_file' => 'PHP Syntax Highlight',
		];

		$strError = '';
		foreach ($arMods as $func => $desc)
		{
			if (!function_exists($func))
			{
				$strError .= $desc . "<br>";
			}
		}

		if (!function_exists('openssl_encrypt'))
		{
			$strError .= GetMessage("MAIN_SC_MCRYPT") . ' OpenSSL';
		}

		if (!function_exists('mb_substr'))
		{
			$strError .= GetMessage("SC_MOD_MBSTRING") . "<br>";
		}

		if (!in_array('ssl', stream_get_transports()))
		{
			$strError .= GetMessage('ERR_NO_SSL') . '<br>';
		}

		if ($strError)
		{
			return $this->Result(false, GetMessage('ERR_NO_MODS') . "<br>" . $strError);
		}

		if (IsModuleInstalled('intranet'))
		{
			if (!class_exists('DOMDocument') || !class_exists('ZipArchive'))
			{
				return $this->Result(null, GetMessage('ERR_NO_MODS_DOC_GENERATOR'));
			}
		}
		return $this->Result(true, GetMessage("MAIN_SC_ALL_MODULES"));
	}

	function check_php_settings()
	{
		$strError = '';
		if (version_compare($v = phpversion(), self::MIN_PHP_VER, '<'))
		{
			$strError = GetMessage('SC_VER_ERR', ['#CUR#' => $v, '#REQ#' => self::MIN_PHP_VER]) . "<br>";
		}

		$arRequiredParams = [
			'safe_mode' => 0,
			'file_uploads' => 1,
			'session.auto_start' => 0,
			'magic_quotes_runtime' => 0,
			'magic_quotes_sybase' => 0,
			'magic_quotes_gpc' => 0,
			'arg_separator.output' => '&',
			'register_globals' => 0,
			'zend.multibyte' => 0,
		];

		if (extension_loaded('xcache'))
		{
			$arRequiredParams['xcache.cacher'] = 0;
			$arRequiredParams['xcache.stat'] = 1;
		}

		foreach ($arRequiredParams as $param => $val)
		{
			$cur = ini_get($param);
			if (strtolower($cur) == 'on')
			{
				$cur = 1;
			}
			elseif (strtolower($cur) == 'off')
			{
				$cur = 0;
			}

			if ($cur != $val)
			{
				$strError .= GetMessage('SC_ERR_PHP_PARAM', ['#PARAM#' => $param, '#CUR#' => $cur ? htmlspecialcharsbx($cur) : 'off', '#REQ#' => $val ? 'on' : 'off']) . "<br>";
			}
		}

		$param = 'opcache.revalidate_freq';
		if (($cur = ini_get($param)) <> 0)
		{
			$strError .= GetMessage('SC_ERR_PHP_PARAM', ['#PARAM#' => $param, '#CUR#' => htmlspecialcharsbx($cur), '#REQ#' => '0']) . "<br>";
		}

		$param = 'default_socket_timeout';
		if (($cur = ini_get($param)) < 60)
		{
			$strError .= GetMessage('SC_ERR_PHP_PARAM', ['#PARAM#' => $param, '#CUR#' => htmlspecialcharsbx($cur), '#REQ#' => '60']) . "<br>";
		}

		if (($m = ini_get('max_input_vars')) && $m < 10000)
		{
			$strError .= GetMessage('ERR_MAX_INPUT_VARS', ['#MIN#' => 10000, '#CURRENT#' => $m]) . "<br>";
		}

		if (IsModuleInstalled('intranet'))
		{
			$vm = getenv('BITRIX_VA_VER');
			if (!$vm)
			{
				$strError .= GetMessage('ERR_NO_VM') . "<br>";
			}
			else
			{
				$last_version = '7.3.0';
				$tmp = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/bitrix-env.version';
				if (!file_exists($tmp) || time() - filemtime($tmp) > 86400)
				{
					$http = new \Bitrix\Main\Web\HttpClient([
						"socketTimeout" => 5,
						"streamTimeout" => 5,
					]);
					$http->download('https://repos.1c-bitrix.ru/yum/bitrix-env.version', $tmp);
				}

				if (file_exists($tmp))
				{
					$last_version_remote = str_replace('-', '.', file_get_contents($tmp));
					if (version_compare($last_version_remote, $last_version, '>'))
					{
						$last_version = $last_version_remote;
					}
				}
				if (version_compare($vm, $last_version, '<'))
				{
					$strError .= GetMessage('ERR_OLD_VM', ['#CURRENT#' => $vm, '#LAST_VERSION#' => $last_version]) . "<br>";
				}
			}
		}

		// check_divider
		$locale_info = localeconv();
		$delimiter = $locale_info['decimal_point'];
		if ($delimiter != '.')
		{
			$strError .= GetMessage('SC_DELIMITER_ERR', ['#VAL#' => $delimiter]) . '<br>';
		}

		// check_precision
		if (1234567891 != (string)doubleval(1234567891))
		{
			$strError .= GetMessage("MAIN_SC_ERROR_PRECISION") . '<br>';
		}

		// check_suhosin
		if (in_array('suhosin', get_loaded_extensions()) && !ini_get('suhosin.simulation'))
		{
			$strError .= GetMessage('SC_WARN_SUHOSIN', ['#VAL#' => ini_get('suhosin.simulation') ? 1 : 0]) . '<br>';
		}

		// check_backtrack_limit
		$param = 'pcre.backtrack_limit';
		$cur = self::Unformat(ini_get($param));
		ini_set($param, $cur + 1);
		$new = ini_get($param);
		if ($new != $cur + 1)
		{
			$strError .= GetMessage("MAIN_SC_CANT_CHANGE") . '<br>';
		}

		if ($strError)
		{
			return $this->Result(false, $strError);
		}
		return $this->Result(true, GetMessage("MAIN_SC_CORRECT_SETTINGS"));
	}

	function check_server_vars()
	{
		$strError = '';
		[$host,] = explode(':', $_SERVER['HTTP_HOST'] ?? '');
		if ($host != 'localhost' && !preg_match('#^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$#', $host))
		{
			if (!preg_match('#^[a-z0-9\-.]{1,192}\.(xn--)?[a-z0-9]{2,63}$#i', $host))
			{
				$strError .= GetMessage("SC_TEST_DOMAIN_VALID", ['#VAL#' => htmlspecialcharsbx($_SERVER['HTTP_HOST'] ?? '')]) . "<br>";
			}
		}
		if ($strError)
		{
			return $this->Result(false, $strError);
		}
		return $this->Result(true, GetMessage("MAIN_IS_CORRECT"));
	}

	function check_mail($big = false)
	{
		$eol = \Bitrix\Main\Mail\Mail::getMailEol();
		$body = "Test message. Delete it.{$eol}";
		if ($big)
		{
			$body = str_repeat($body, 8000);
		}

		$startTime = microtime(true);
		if ($big)
		{
			$val = mail("hosting_test@bitrixsoft.com", "Bitrix site checker" . $eol . "\tmultiline subject", $body, 'BCC: noreply@bitrixsoft.com');
		}
		else
		{
			$val = mail("hosting_test@bitrixsoft.com", "Bitrix site checker", $body);
		}
		$endTime = microtime(true);
		$time = round($endTime - $startTime, 2);
		if ($val)
		{
			if ($time > 1)
			{
				return $this->Result($this->arTestVars['check_bx_crontab'] ? null : false, GetMessage('SC_SENT') . ' ' . $time . ' ' . GetMessage('SC_SEC'));
			}
		}
		else
		{
			return false;
		}

		return true;
	}

	function check_mail_big()
	{
		return $this->check_mail(true);
	}

	function check_mail_b_event()
	{
		global $DB, $CACHE_MANAGER;

		$res = $DB->Query("SELECT COUNT(1) AS A FROM b_event WHERE SUCCESS_EXEC = 'N'");
		$f = $res->Fetch();
		if ($f['A'] > 0)
		{
			$info = defined('BX_CRONTAB_SUPPORT') && BX_CRONTAB_SUPPORT ? '<br> ' . GetMessage('SC_CRON_WARN') : '';
			if (CACHED_b_event !== false && $CACHE_MANAGER->Read(CACHED_b_event, "events"))
			{
				$info .= "<br> " . GetMessage('SC_CACHED_EVENT_WARN');
			}
			return $this->Result(false, GetMessage('SC_T_MAIL_B_EVENT_ERR') . ' ' . $f['A'] . $info);
		}
		return true;
	}

	function check_connect_mail()
	{
		if (!CModule::IncludeModule('mail'))
		{
			return $this->Result(null, GetMessage("MAIN_SC_MAIL_IS_NOT_INSTALLED"));
		}
		$rs = CMailBox::GetList([], ['SERVER_TYPE' => 'imap']);
		if ($rs->Fetch())
		{
			return true;
		}
		return $this->Result(null, GetMessage("MAIN_SC_MAIL_INTEGRATION"));
	}

	function check_socnet()
	{
		if (!CModule::IncludeModule('socialservices'))
		{
			return $this->Result(null, GetMessage("MAIN_SC_NO_SOCIAL_MODULE"));
		}
		$oAuthManager = new CSocServAuthManager();
		$arActiveSocServ = $oAuthManager->GetActiveAuthServices([]);
		if (!empty($arActiveSocServ))
		{
			if (is_array($arActiveSocServ['Bitrix24Net']))
			{
				return true;
			}
			return $this->Result(null, GetMessage("MAIN_SC_NO_SOCIAL_SERVICES_24NET"));
		}
		return $this->Result(null, GetMessage("MAIN_SC_NO_SOCIAL_SERVICES"));
	}

	function check_rest()
	{
		if (!CModule::IncludeModule('rest'))
		{
			return $this->Result(null, GetMessage("MAIN_SC_NO_REST_MODULE"));
		}
		return true;
	}

	function check_mail_push()
	{
		$site = \Bitrix\Main\SiteTable::getList([
			'select' => ['LID', 'NAME', 'SERVER_NAME'],
			'order' => ['DEF' => 'DESC', 'SORT' => 'ASC'],
		])->fetch();

		$domain = $site['SERVER_NAME'] ?: COption::getOptionString('main', 'server_name', '');

		if (preg_match('/^(?<domain>.+):(?<port>\d+)$/', $domain, $matches))
		{
			$domain = $matches['domain'];
		}
		if (!getmxrr($domain, $mxhosts) || !count($mxhosts))
		{
			return $this->Result(null, GetMessage('SC_ERR_DNS', ['#DOMAIN#' => $domain]));
		}

		foreach ($mxhosts as $mx)
		{
			if ($mx != 'mail-001.bitrix24.com')
			{
				print_r($mxhosts);
				return $this->Result(null, GetMessage('SC_ERR_DNS_WRONG', ['#DOMAIN#' => $mx]));
			}
		}

		if (!$res = $this->ConnectToHost('mail-001.bitrix24.com', 25))
		{
			return $this->Result(null, GetMessage('SC_ERR_CONNECT_MAIL001'));
		}

		echo fgets($res);
		fwrite($res, "HELO " . $domain . "\r\n");
		echo fgets($res);
		fwrite($res, "MAIL FROM: sitecheckerfrom@" . $domain . "\r\n");
		echo fgets($res);
		fwrite($res, "RCPT TO: rplsitecheckerto@" . $domain . "\r\n");
		echo fgets($res);
		fwrite($res, "DATA\r\n");
		echo fgets($res);
		fwrite($res,
			"From: sitecheckerfrom@" . $domain . "\r\n" .
			"To: rplsitecheckerto@" . $domain . "\r\n" .
			"Subject: Site checker mail test\r\n" .
			"Content-type: text/plain\r\n" .
			"MIME-Version: 1.0\r\n" .
			"\r\n" .
			".\r\n");
		echo($str = fgets($res));
		fclose($res);
		if (preg_match('#\(Bad Request 400\)#i', $str))
		{
			echo "Success\n";
			return true;
		}
		return $this->Result(null, GetMessage('SC_ERR_TEST_MAIL_PUSH', ['#DOMAIN#' => $domain]));
	}

	function check_socket()
	{
		$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=socket_test&unique_id=" . checker_get_unique_id() . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		$strRequest .= "\r\n";

		$retVal = false;

		if ($res = $this->ConnectToHost())
		{
			$retVal = IsHttpResponseSuccess($res, $strRequest);
		}

		if (!$retVal)
		{
			$this->arTestVars['check_socket_fail'] = 1;
		}
		return $retVal;
	}

	function check_compression()
	{
		$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=compression&unique_id=" . checker_get_unique_id() . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		$strRequest .= "Accept-Encoding: gzip, deflate\r\n";
		$strRequest .= "\r\n";

		if (!$res = $this->ConnectToHost())
		{
			return false;
		}

		$strRes = GetHttpResponse($res, $strRequest, $strHeaders);

		if (preg_match('#gzip|deflate#mi', $strHeaders) && strlen($strRes) < 64 * 1024) // comression not supported by server
		{
			return $this->Result(true, GetMessage("MAIN_SC_ENABLED_MOD"));
		}
		else
		{
			return $this->Result(false, GetMessage("MAIN_SC_COMP_DISABLED_MOD"));
		}
	}

	function check_socket_ssl()
	{
		if (!file_exists($this->cafile) || filesize($this->cafile) == 0)
		{
			return $this->Result(null, GetMessage("MAIN_SC_TEST_SSL1"));
		}

		if (!$context = stream_context_create(
			[
				'ssl' => [
					'verify_peer' => true,
					'allow_self_signed' => false,
					'cafile' => $this->cafile,
				],
			]
		))
		{
			return false;
		}

		echo "Connection to ssl://{$this->host}:443 (certificate check enabled)	";
		if ($res = stream_socket_client('ssl://' . $this->host . ':443', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context))
		{
			echo "Success\n";
			fclose($res);
			return true;
		}
		echo "Fail\n";

		if (!$context = stream_context_create(
			[
				'ssl' => [
					'verify_peer' => false,
					'allow_self_signed' => true,
					'cafile' => $this->cafile,
				],
			]
		))
		{
			return false;
		}

		echo "Connection to ssl://{$this->host}:443	";
		if ($res = stream_socket_client('ssl://' . $this->host . ':443', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context))
		{
			echo "Success\n";
			fclose($res);
			return $this->Result(null, GetMessage("MAIN_SC_SSL_NOT_VALID"));
		}
		echo "Fail\n";
		return $this->Result(null, GetMessage("MAIN_SC_NO_CONNECTTO", ['#HOST#' => 'https://' . $this->host]));
	}

	function check_ad()
	{
		if (!CModule::IncludeModule('ldap'))
		{
			return $this->Result(null, GetMessage("MAIN_SC_NO_LDAP_MODULE"));
		}
		$rs = CLdapServer::GetList();
		if (!$rs->Fetch())
		{
			return $this->Result(null, GetMessage("MAIN_SC_NO_LDAP_INTEGRATION"));
		}
		return true;
	}

	function check_ntlm()
	{
		if (!CModule::IncludeModule('ldap'))
		{
			return $this->Result(null, GetMessage("MAIN_SC_NO_LDAP_MODULE"));
		}
		if (COption::GetOptionString('ldap', 'use_ntlm', 'N') != 'Y')
		{
			return $this->Result(null, GetMessage("MAIN_SC_OPTION_SWITCHED_OFF"));
		}
		if (COption::GetOptionString('ldap', 'bitrixvm_auth_support', 'N') == 'Y')
		{
			return true;
		}
		if (($ntlm_varname = COption::GetOptionString('ldap', 'ntlm_varname', 'REMOTE_USER')) && ($user = trim($_SERVER[$ntlm_varname])))
		{
			return $this->Result(true, GetMessage("MAIN_SC_NTLM_SUCCESS") . $user);
		}
		return $this->Result(null, GetMessage("MAIN_SC_NO_NTLM"));
	}

	function check_ca_file()
	{
		if (file_exists($this->cafile))
		{
			unlink($this->cafile);
		}
		CheckDirPath($this->cafile);

		$region = Application::getInstance()->getLicense()->getRegion();

		if (in_array($region, ['ru', 'by', 'kz']))
		{
			$url = 'https://www.1c-bitrix.ru/upload/lib/cafile.pem';
		}
		else
		{
			$url = 'https://www.bitrixsoft.com/upload/lib/cafile.pem';
		}

		$http = new \Bitrix\Main\Web\HttpClient([
			"socketTimeout" => 5,
			"streamTimeout" => 5,
		]);
		if ($http->download($url, $this->cafile) && is_file($this->cafile) && filesize($this->cafile) > 0)
		{
			return true;
		}

		return $this->Result(null, GetMessage("MAIN_SC_NO_ACCESS") . '&quot;');
	}

	function check_dbconn()
	{
		$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=dbconn_test&unique_id=" . checker_get_unique_id() . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		$strRequest .= "\r\n";

		$retVal = false;
		$file = '';
		if (IsModuleInstalled('security'))
		{
			$file = COption::GetOptionString("security", "ipcheck_disable_file", "");
			COption::SetOptionString("security", "ipcheck_disable_file", $this->LogFile);
		}
		if ($res = $this->ConnectToHost())
		{
			$retVal = IsHttpResponseSuccess($res, $strRequest);
		}
		if (IsModuleInstalled('security'))
		{
			COption::SetOptionString("security", "ipcheck_disable_file", $file);
		}
		return $retVal;
	}

	function check_upload($big = false, $raw = false)
	{
		if (($sp = ini_get("upload_tmp_dir")))
		{
			if (!file_exists($sp))
			{
				return $this->Result(false, GetMessage('SC_NO_TMP_FOLDER') . ' <i>(' . htmlspecialcharsbx($sp) . ')</i>');
			}
			elseif (!is_writable($sp))
			{
				return $this->Result(false, GetMessage('SC_TMP_FOLDER_PERMS') . ' <i>(' . htmlspecialcharsbx($sp) . ')</i>');
			}
		}

		if (defined('BX_TEMPORARY_FILES_DIRECTORY'))
		{
			$sp = BX_TEMPORARY_FILES_DIRECTORY;
			if (!file_exists($sp))
			{
				return $this->Result(false, GetMessage('SC_NO_TMP_FOLDER') . ' <i>(BX_TEMPORARY_FILES_DIRECTORY: ' . htmlspecialcharsbx($sp) . ')</i>');
			}
			elseif (!is_writable($sp))
			{
				return $this->Result(false, GetMessage('SC_TMP_FOLDER_PERMS') . ' <i>(BX_TEMPORARY_FILES_DIRECTORY: ' . htmlspecialcharsbx($sp) . ')</i>');
			}
		}

		$binaryData = '';
		for ($i = 40; $i < 240; $i++)
		{
			$binaryData .= chr($i);
		}
		if ($big)
		{
			$binaryData = str_repeat($binaryData, 21000);
		}

		if ($raw)
		{
			$POST = $binaryData;
		}
		else
		{
			$boundary = '--------' . md5(checker_get_unique_id());

			$POST = "--$boundary\r\n";
			$POST .= 'Content-Disposition: form-data; name="test_file"; filename="site_checker.bin"' . "\r\n";
			$POST .= 'Content-Type: image/gif' . "\r\n";
			$POST .= "\r\n";
			$POST .= $binaryData . "\r\n";
			$POST .= "--$boundary\r\n";
		}

		$strRequest = "POST " . "/bitrix/admin/site_checker.php?test_type=upload_test&unique_id=" . checker_get_unique_id() . "&big=" . ($big ? 1 : 0) . "&raw=" . ($raw ? 1 : 0) . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		if (!$raw)
		{
			$strRequest .= "Content-Type: multipart/form-data; boundary=$boundary\r\n";
		}
		$strRequest .= "Content-Length: " . strlen($POST) . "\r\n";
		$strRequest .= "\r\n";
		$strRequest .= $POST;

		if ($res = $this->ConnectToHost())
		{
			return IsHttpResponseSuccess($res, $strRequest);
		}
		return false;
	}

	function check_upload_big()
	{
		return $this->check_upload(true);
	}

	function check_upload_raw()
	{
		return $this->check_upload(false, true);
	}

	function check_post()
	{
		$POST = '';
		for ($i = 0; $i < 201; $i++)
		{
			$POST .= 'i' . $i . '=' . md5($i) . '&';
		}

		$strRequest = "POST " . "/bitrix/admin/site_checker.php?test_type=post_test&unique_id=" . checker_get_unique_id() . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		$strRequest .= "Content-Length: " . strlen($POST) . "\r\n";
		$strRequest .= "Content-Type: application/x-www-form-urlencoded\r\n";

		$strRequest .= "\r\n";
		$strRequest .= $POST;

		if ($res = $this->ConnectToHost())
		{
			return IsHttpResponseSuccess($res, $strRequest);
		}
		return false;
	}

	function check_memory_limit()
	{
		$total_steps = 5;

		if (!$this->arTestVars['last_value'])
		{
			$last_success = 0;
			$max = 16;
			$step = 1;
		}
		else
		{
			if (!CheckSerializedData($this->arTestVars['last_value']))
			{
				return false;
			}
			[$last_success, $max, $step] = unserialize($this->arTestVars['last_value'], ['allowed_classes' => false]);
		}

		$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=memory_test&unique_id=" . checker_get_unique_id() . "&max=" . ($max - 1) . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		$strRequest .= "\r\n";

		if (!$res = $this->ConnectToHost())
		{
			return false;
		}

		if (IsHttpResponseSuccess($res, $strRequest))
		{
			$last_success = $max;
			$max *= 2;
		}
		else
		{
			$max = floor(($last_success + $max) / 2);
		}

		if ($max < 16)
		{
			return false;
		}

		if ($step < $total_steps)
		{
			$this->test_percent = floor(100 / $total_steps * $step);
			$step++;
			$this->arTestVars['last_value'] = serialize([$last_success, $max, $step]);
			return true;
		}

		$ok = false;
		$res = GetMessage('SC_NOT_LESS', ['#VAL#' => $last_success]);
		$last_success = (int)$last_success;
		if ($last_success > 32)
		{
			$ok = true;
			$cur = \Bitrix\Main\Config\Ini::getInt('memory_limit');
			if ($cur > 0 && $cur < $last_success * 1024 * 1024)
			{
				$res .= '<br> ' . GetMessage('SC_MEMORY_CHANGED', ['#VAL0#' => ini_get('memory_limit'), '#VAL1#' => '512M']);
				$ok = null;
			}
		}
		return $this->Result($ok, $res);
	}

	function check_session()
	{
		if (!$this->arTestVars['last_value'])
		{
			$_SESSION['CHECKER_CHECK_SESSION'] = 'SUCCESS';
			$this->test_percent = 50;
			$this->arTestVars['last_value'] = 'Y';
		}
		else
		{
			if ($_SESSION['CHECKER_CHECK_SESSION'] != 'SUCCESS')
			{
				return false;
			}
			unset($_SESSION['CHECKER_CHECK_SESSION']);
		}
		return true;
	}

	function check_session_ua()
	{
		$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=session_test&unique_id=" . checker_get_unique_id() . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";

		if ($this->arTestVars['last_value']) // second step: put session id
		{
			$strRequest .= "Cookie: " . $this->arTestVars['last_value'] . "\r\n";
		}

		$strRequest .= "\r\n";

		if (!$res = $this->ConnectToHost())
		{
			return false;
		}

		if (!$this->arTestVars['last_value']) // first step: read session id
		{
			$strRes = GetHttpResponse($res, $strRequest, $strHeaders);
			if (!preg_match('#Set-Cookie: (' . session_name() . '=[a-z0-9\-_]+?);#i', $strHeaders, $regs))
			{
				PrintHTTP($strRequest, $strHeaders, $strRes);
				return false;
			}

			$this->arTestVars['last_value'] = $regs[1];
			$this->test_percent = 50;
			return true;
		}
		else
		{
			return IsHttpResponseSuccess($res, $strRequest);
		}
	}

	function check_mbstring()
	{
		$file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/.settings.php';

		if ($this->fix_mode)
		{
			if (!file_exists($file))
			{
				\Bitrix\Main\Config\Configuration::wnc();
				Application::resetAccelerator();
			}
			return $this->Result(file_exists($file));
		}

		if (!file_exists($file))
		{
			$this->arTestVars['check_mbstring_fail'] = true;
			$mode = 4;
			$link = ' <a href="javascript:show_popup(\'' . GetMessageJS('SC_FIX_MBSTRING') . '\', \'?fix_mode=' . $mode . '\', \'' . GetMessageJS('SC_FIX_MBSTRING_CONFIRM') . '\')">' . GetMessage('SC_FIX') . '</a>';
			return $this->Result(false, GetMessage('SC_ERR_NO_SETTINGS') . $link);
		}

		$encoding = strtolower(ini_get('mbstring.internal_encoding'));
		$default = strtolower(ini_get('default_charset'));

		if ($default == "")
		{
			return $this->Result(false, GetMessage("MAIN_SC_DEFAULT_CHARSET"));
		}

		if ($encoding <> '' && $encoding <> $default)
		{
			return $this->Result(false, GetMessage("MAIN_SC_ENC_EQUAL"));
		}

		if (ini_get('mbstring.func_overload') > 0)
		{
			//should be non-existent
			return $this->Result(false, GetMessage("MAIN_SC_FUNC_OVERLOAD"));
		}

		$retVal = true;
		$bUtf = false;

		$rs = CSite::GetList('', '', ['ACTIVE' => 'Y']);
		while ($f = $rs->Fetch())
		{
			if (str_contains(strtolower($f['CHARSET']), 'utf'))
			{
				$bUtf = true;
				break;
			}
		}

		if ($bUtf)
		{
			$text = GetMessage('SC_MB_UTF');

			if ($default <> "utf-8")
			{
				$retVal = false;
				$text .= '<br>' . GetMessage("MAIN_SC_ENC_UTF");
				$this->arTestVars['check_mbstring_fail'] = true;
			}
		}
		else
		{
			$text = GetMessage('SC_MB_NOT_UTF');
		}

		if ($retVal)
		{
			$retVal = (strlen("\xd0\xa2") == 2);
			if (!$retVal)
			{
				$text = GetMessage('SC_STRLEN_FAIL_PHP56');
			}
		}

		return $this->Result($retVal, ($retVal ? GetMessage("MAIN_SC_CORRECT") . '. ' : '') . $text);
	}

	function check_http_auth()
	{
		$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=auth_test&unique_id=" . checker_get_unique_id() . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		$strRequest .= "Authorization: Basic dGVzdF91c2VyOnRlc3RfcGFzc3dvcmQ=\r\n";
		$strRequest .= "\r\n";

		if ($res = $this->ConnectToHost())
		{
			return IsHttpResponseSuccess($res, $strRequest);
		}
		return false;
	}

	function check_update()
	{
		$ServerIP = COption::GetOptionString("main", "update_site", "www.bitrixsoft.com");
		$ServerPort = 80;

		$proxyAddr = COption::GetOptionString("main", "update_site_proxy_addr", "");
		$proxyPort = COption::GetOptionString("main", "update_site_proxy_port", "");
		$proxyUserName = COption::GetOptionString("main", "update_site_proxy_user", "");
		$proxyPassword = COption::GetOptionString("main", "update_site_proxy_pass", "");

		$bUseProxy = !$this->arTestVars['last_value'] && $proxyAddr <> '' && $proxyPort <> '';

		if ($bUseProxy)
		{
			$proxyPort = intval($proxyPort);
			if ($proxyPort <= 0)
			{
				$proxyPort = 80;
			}

			$requestIP = $proxyAddr;
			$requestPort = $proxyPort;
		}
		else
		{
			$requestIP = $ServerIP;
			$requestPort = $ServerPort;
		}

		$strRequest = "";
		$page = "us_updater_list.php";
		if ($bUseProxy)
		{
			$strRequest .= "POST http://" . $ServerIP . "/bitrix/updates/" . $page . " HTTP/1.0\r\n";
			if ($proxyUserName <> '')
			{
				$strRequest .= "Proxy-Authorization: Basic " . base64_encode($proxyUserName . ":" . $proxyPassword) . "\r\n";
			}
		}
		else
		{
			$strRequest .= "POST /bitrix/updates/" . $page . " HTTP/1.0\r\n";
		}

		$strRequest .= "User-Agent: BitrixSMUpdater\r\n";
		$strRequest .= "Accept: */*\r\n";
		$strRequest .= "Host: " . $ServerIP . "\r\n";
		$strRequest .= "Accept-Language: en\r\n";
		$strRequest .= "Content-type: application/x-www-form-urlencoded\r\n";
		$strRequest .= "Content-length: 7\r\n\r\n";
		$strRequest .= "lang=en";
		$strRequest .= "\r\n";

		$res = false;
		try
		{
			$res = fsockopen($requestIP, $requestPort, $errno, $errstr, 5);
		}
		catch (Exception $e)
		{
			echo $e->getMessage() . "\n";
		}

		if (!$res)
		{
			if ($bUseProxy)
			{
				return $this->Result(false, GetMessage('SC_NO_PROXY') . ' (' . $errstr . ')');
			}
			else
			{
				return $this->Result(false, GetMessage('SC_UPDATE_ERROR') . ' (' . $errstr . ')');
			}
		}
		else
		{
			if (\Bitrix\Main\Config\Option::get("updateserverlight", "is_turned_on", "N") === "Y")
			{
				return true;
			}

			$strRes = GetHttpResponse($res, $strRequest, $strHeaders);

			if ((str_contains($strRes, "EMPTY_LICENSE_KEY"))
				|| (str_contains($strRes, "LICENSE_KEY_REQUIRED")))
			{
				return true;
			}
			else
			{
				$strRes = mb_strtolower(strip_tags($strRes));
				PrintHTTP($strRequest, $strHeaders, $strRes);
				if ($bUseProxy)
				{
					return $this->Result(false, GetMessage('SC_PROXY_ERR_RESP'));
				}
				else
				{
					return $this->Result(false, GetMessage('SC_UPDATE_ERR_RESP'));
				}
			}
		}
	}

	function check_pull_stream()
	{
		if (CModule::IncludeModule('pull'))
		{
			$text = md5(mt_rand(100000, 999999));
			$channelId = md5($text);
			$id = CPullChannel::SignChannel($channelId);
			if (CPullOptions::GetQueueServerStatus())
			{
				$isServerShared = CPullOptions::IsServerShared();
				if ($isServerShared && !\Bitrix\Pull\SharedServer\Config::isRegistered())
				{
					$this->arTestVars['push_stream_fail'] = true;
					return $this->Result(false, GetMessage("MAIN_SC_PULL_NOT_REGISTERED"));
				}
				else
				{
					if (CPullOptions::GetQueueServerVersion() < 4)
					{
						$this->arTestVars['push_stream_warn'] = true;
						return $this->Result(null, GetMessage("MAIN_SC_PULL_UNSUPPORTED_VERSION"));
					}
					else
					{
						if (!$ar = parse_url(str_replace('#DOMAIN#', $this->host, \Bitrix\Pull\Config::getPublishUrl($id))))
						{
							return $this->Result(false, GetMessage("MAIN_SC_PATH_PUB"));
						}

						$pub_domain = $ar['host'];
						$pub_host = ($ar['scheme'] == 'https' ? 'ssl://' : '') . $pub_domain;
						$pub = $ar['path'] . '?' . $ar['query'];
						$pub_port = $ar['port'];
						if (!$pub_port)
						{
							$pub_port = $ar['scheme'] == 'https' ? 443 : 80;
						}

						if ($isServerShared)
						{
							$listenUrl = \Bitrix\Pull\SharedServer\Config::getLongPollingUrl();
							$listenUrl .= "?CHANNEL_ID=" . $id . "&clientId=" . \Bitrix\Pull\SharedServer\Client::getPublicLicenseCode();
						}
						else
						{
							$listenUrl = $this->ssl ? CPullOptions::GetListenSecureUrl($id) : CPullOptions::GetListenUrl($id);
						}

						if (!$ar = parse_url(str_replace('#DOMAIN#', $this->host, $listenUrl)))
						{
							return $this->Result(false, GetMessage("MAIN_SC_PATH_SUB"));
						}

						$sub_domain = $ar['host'];
						$sub_host = ($ar['scheme'] == 'https' ? 'ssl://' : '') . $sub_domain;
						$sub = $ar['path'] . '?' . $ar['query'];
						$sub_port = $ar['port'] ?? null;
						if (!$sub_port)
						{
							$sub_port = $ar['scheme'] == 'https' ? 443 : 80;
						}
					}
				}
			}
			else
			{
				$this->arTestVars['push_stream_fail'] = true;
				return $this->Result(false, GetMessage("MAIN_SC_STREAM_DISABLED_2"));
			}
		}
		else
		{
			$this->arTestVars['push_stream_fail'] = true;
			return $this->Result(false, GetMessage("MAIN_NO_PULL"));
		}

		$ver = CPullOptions::GetQueueServerVersion();
		$bNodeJS = $ver > 2;
		echo 'Server version: ' . $ver . ($bNodeJS ? ' (Bitrix Push server)' : ' (nginx-push-stream-module)') . "\n";

		$strRequest0 = 'POST ' . $pub . ' HTTP/1.0' . "\r\n" .
			'Host: ' . $pub_domain . "\r\n" .
			'Content-Length: ' . strlen($text) . "\r\n" .
			"\r\n" .
			$text . "\r\n";
		$strRequest1 = 'GET ' . $sub . ' HTTP/1.0' . "\r\n" .
			'If-Modified-Since: ' . date('r', time() - 86400) . "\r\n" .
			'Host: ' . $sub_domain . "\r\n\r\n";
		$strRequest2 = 'DELETE ' . $pub . ' HTTP/1.0' . "\r\n" .
			'Host: ' . $sub_domain . "\r\n\r\n";

		if (!$bNodeJS)
		{
			// POST - to create a channel
			if (!$res0 = $this->ConnectToHost($pub_host, $pub_port))
			{
				$this->arTestVars['push_stream_fail'] = true;
				return $this->Result(false, GetMessage("MAIN_SC_NO_PUSH_STREAM_CONNECTION"));
			}
			fwrite($res0, $strRequest0);
			fclose($res0);
		}

		// GET - connection
		if (!$res1 = $this->ConnectToHost($sub_host, $sub_port))
		{
			$this->arTestVars['push_stream_fail'] = true;
			return $this->Result(false, GetMessage("MAIN_SC_NO_SUB_CONNECTION_2"));
		}
		fwrite($res1, $strRequest1);
		sleep(1); // we need some time to create channel

		// POST - message
		$postResult = CPullChannel::Send($channelId, $text, ['dont_wait_answer' => false]);
		if (!$postResult)
		{
			$this->arTestVars['push_stream_fail'] = true;
			return $this->Result(false, GetMessage("MAIN_SC_NO_PUSH_STREAM_CONNECTION"));
		}

		// GET - message
		$strRes1 = fread($res1, 4096);

		$retVal = true;
		if (!str_contains($strRes1, $text))
		{
			PrintHTTP($strRequest1, '', $strRes1);
			$this->arTestVars['push_stream_fail'] = true;
			$retVal = $this->Result(false, GetMessage("MAIN_SC_PUSH_INCORRECT", ['#MODULE#' => $bNodeJS ? 'Bitrix Push server' : 'nginx-push-stream-module']));
		}

		// DELETE
		if (!$res2 = $this->ConnectToHost($pub_host, $pub_port))
		{
			$this->arTestVars['push_stream_fail'] = true;
			return $this->Result(false, GetMessage("MAIN_SC_NO_PUSH_STREAM_CONNECTION_2"));
		}
		fwrite($res2, $strRequest2);
		fclose($res2);

		if ($retVal && COption::GetOptionString('main', 'session_expand', 'Y') <> 'N' && (!defined("BX_SKIP_SESSION_EXPAND") || BX_SKIP_SESSION_EXPAND === false))
		{
			return $this->Result(null, GetMessage('MAIN_SC_WARN_EXPAND_SESSION'));
		}

		return $retVal;
	}

	function check_pull_comments()
	{
		if (!empty($this->arTestVars['push_stream_warn']))
		{
			return $this->Result(null, GetMessage("MAIN_SC_NO_PUSH_STREAM_2"));
		}
		else
		{
			if (!empty($this->arTestVars['push_stream_fail']))
			{
				return $this->Result(false, GetMessage("MAIN_SC_NO_PUSH_STREAM_2"));
			}
		}
		return true;
	}

	function check_turn()
	{
		if (!IsModuleInstalled('im'))
		{
			return $this->Result(null, GetMessage("MAIN_SC_NO_IM"));
		}

		if (!empty($this->arTestVars['push_stream_warn']))
		{
			return $this->Result(null, GetMessage("MAIN_SC_NO_PUSH_STREAM_VIDEO_2"));
		}
		else
		{
			if (!empty($this->arTestVars['push_stream_fail']))
			{
				return $this->Result(false, GetMessage("MAIN_SC_NO_PUSH_STREAM_VIDEO_2"));
			}
		}

		if (COption::GetOptionString("im", "turn_server_self") == 'Y')
		{
			$host = COption::GetOptionString("im", "turn_server");
		}
		else
		{
			$host = 'turn.calls.bitrix24.com';
		}
		$port = 3478;

		if (!($res = $this->ConnectToHost($host, $port)))
		{
			$res = $this->ConnectToHost('udp://' . $host, $port);
		}

		if ($res)
		{
			fclose($res);
			return $this->Result(true, GetMessage("MAIN_SC_AVAIL"));
		}
		return $this->Result(null, GetMessage("MAIN_SC_NOT_AVAIL"));
	}

	function check_push_bitrix()
	{
		if (!CModule::IncludeModule('pull'))
		{
			return $this->Result(null, GetMessage("MAIN_NO_PULL_MODULE"));
		}
		if (!CPullOptions::GetPushStatus())
		{
			return $this->Result(null, GetMessage("MAIN_NO_OPTION_PULL"));
		}

		if ($this->arTestVars['check_access_fail'])
		{
			return $this->Result(false, GetMessage("MAIN_SC_NO_EXTERNAL_ACCESS_MOB"));
		}

		$host = 'cloud-messaging.bitrix24.com';
		$POST = 'Action=SendMessage&MessageBody=batch';

		$strRequest = "POST /send/?key=" . md5('key') . " HTTP/1.1\r\n";
		$strRequest .= "User-Agent: BitrixCloud SiteChecker\r\n";
		$strRequest .= "Host: " . $host . "\r\n";
		$strRequest .= "Content-type: application/x-www-form-urlencoded\r\n";
		$strRequest .= "Content-length: " . strlen($POST) . "\r\n";
		$strRequest .= "\r\n" . $POST . "\r\n";

		if (!$res = $this->ConnectToHost('ssl://' . $host, 443))
		{
			return false;
		}

		$strRes = mb_strtolower(GetHttpResponse($res, $strRequest, $strHeaders));
		if (str_contains($strRes, 'xml version='))
		{
			return true;
		}

		PrintHTTP($strRequest, $strHeaders, $strRes);
		return $this->Result(false, GetMessage("MAIN_WRONG_ANSWER_PULL"));
	}

	function check_access_docs()
	{
		if ($this->arTestVars['check_access_fail'])
		{
			return $this->Result(null, GetMessage("MAIN_SC_NO_EXTERNAL_ACCESS_"));
		}
		return true;
	}

	function check_extranet()
	{
		if ($this->arTestVars['check_access_fail'])
		{
			return $this->Result(false, GetMessage("MAIN_SC_NO_EXTRANET_CONNECT"));
		}
		return true;
	}

	function check_webdav()
	{
		if (!CModule::IncludeModule('webdav') && !CModule::IncludeModule('disk'))
		{
			return $this->Result(false, GetMessage("MAIN_SC_NO_WEBDAV_MODULE"));
		}

		if ($this->arTestVars['check_socket_fail'])
		{
			return $this->Result(null, GetMessage('SC_SOCK_NA'));
		}

		$allow = [
			"PUT" => ["rights" => "U", "min_rights" => "U"],
			"LOCK" => ["rights" => "U", "min_rights" => "U"],
			"MOVE" => ["rights" => "W", "min_rights" => "U"],
			"MKCOL" => ["rights" => "W", "min_rights" => "W"],
			"PROPFIND" => ["rights" => "R", "min_rights" => "R"],
		];

		foreach ($allow as $method => $ar)
		{
			$strRequest = $method . " /bitrix/admin/site_checker.php?test_type=webdav_test&method=$method&unique_id=" . checker_get_unique_id() . " HTTP/1.1\r\n";
			$strRequest .= "Host: " . $this->host . "\r\n";
			$strRequest .= "\r\n";

			if (!$res = $this->ConnectToHost())
			{
				return null;
			}
			$strRes = GetHttpResponse($res, $strRequest, $strHeaders);
			if (trim($strRes) != 'SUCCESS')
			{
				PrintHTTP($strRequest, $strHeaders, $strRes);
				return $this->Result(null, str_replace('#METHOD#', $method, GetMessage("MAIN_SC_METHOD_NOT_SUP")));
			}
		}
		return true;
	}

	function check_search()
	{
		if (!CModule::IncludeModule('intranet'))
		{
			return null;
		}

		$tmp = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/success.doc';
		if (!CheckDirPath($tmp) || !file_put_contents($tmp, 'SUCCESS'))
		{
			return $this->Result(false, GetMessage("MAIN_TMP_FILE_ERROR"));
		}

		$res = CIntranetSearchConverters::OnSearchGetFileContent($tmp);
		unlink($tmp);

		if (is_array($res) && str_contains($res['CONTENT'], 'SUCCESS'))
		{
			return true;
		}

		$strError = GetMessage("MAIN_SC_SEARCH_INCORRECT") . "<br>\n";
		if ($res === false && function_exists('exec'))
		{
			exec('catdoc -V', $output, $return_var);
			if ($return_var === 0)
			{
				$version = $output[0];
				if (str_contains($version, '0.94.4') || str_contains($version, '0.94.3'))
				{
					$strError .= GetMessage('MAIN_CATDOC_WARN', ['#VERSION#' => $version]);
				}
			}
		}

		return $this->Result(false, $strError);
	}

	function check_fast_download()
	{
		$tmp = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/success.txt';
		if (!CheckDirPath($tmp) || !file_put_contents($tmp, 'SUCCESS'))
		{
			return $this->Result(false, GetMessage("MAIN_TMP_FILE_ERROR"));
		}

		$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=fast_download&unique_id=" . checker_get_unique_id() . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		$strRequest .= "\r\n";

		if (!$res = $this->ConnectToHost())
		{
			return false;
		}
		if (IsHttpResponseSuccess($res, $strRequest))
		{
			$retVal = COption::GetOptionString('main', 'bx_fast_download', 'N') == 'Y' ? true : $this->Result(false, GetMessage("MAIN_FAST_DOWNLOAD_SUPPORT"));
		}
		else
		{
			$retVal = COption::GetOptionString('main', 'bx_fast_download', 'N') == 'N' ? $this->Result(false, GetMessage("MAIN_SC_NOT_SUPPORTED")) : $this->Result(false, GetMessage("MAIN_FAST_DOWNLOAD_ERROR"));
		}
		unlink($tmp);
		return $retVal;
	}

	function check_access_mobile()
	{
		$checker = 'checker.internal.bitrix24.com';
		$retVal = null;
		$strRes = '';

		if (preg_match('#^(127|10|172\.16|192\.168)\.#', $this->host))
		{
			$status = 0;
		}
		else
		{
			$strRequest =
				'GET /check/?license_hash=' . LICENSE_HASH . '&host=' . urlencode($this->host) . '&port=' . urlencode($this->port) . '&https=' . ($this->ssl ? 'Y' : 'N') . ' HTTP/1.1' . "\r\n" .
				'host: ' . $checker . "\r\n" .
				"\r\n";

			if (!$res = $this->ConnectToHost($checker, 80))
			{
				$this->arTestVars['check_access_fail'] = true;
				return $this->Result($retVal, GetMessage("MAIN_SC_NO_CONNECTTO", ['#HOST#' => $checker]));
			}

			$strRes = GetHttpResponse($res, $strRequest, $strHeaders);
			PrintHTTP($strRequest, $strHeaders, $strRes);
			if (!preg_match('#^Status: (.+)$#m', $strRes, $regs))
			{
				$this->arTestVars['check_access_fail'] = true;
				return $this->Result($retVal, GetMessage("MAIN_SC_UNKNOWN_ANSWER", ['#HOST#' => $checker]));
			}
			$status = intval($regs[1]);
		}

		if (!$status)
		{
			$this->arTestVars['check_access_fail'] = true;
			return $this->Result($retVal, GetMessage("MAIN_SC_NO_EXTERNAL_CONNECT_WARN"));
		}

		if ($status != 200 && $status != 401)
		{
			$this->arTestVars['check_access_fail'] = true;
			echo $strRes;
			return $this->Result($retVal, GetMessage("MAIN_SC_EXTERNAL_ANSWER_INCORRECT"));
		}

		if (!CModule::IncludeModule('pull'))
		{
			return $this->Result(null, GetMessage("MAIN_NO_PULL_MODULE"));
		}
		if (!CPullOptions::GetPushStatus())
		{
			return $this->Result(null, GetMessage("MAIN_NO_OPTION_PULL"));
		}
		if (!$ar = parse_url(str_replace('#DOMAIN#', $this->host, COption::GetOptionString('pull', 'path_to_mobile_listener' . ($this->ssl ? '_secure' : '')))))
		{
			return $this->Result(false, GetMessage("MAIN_SC_PATH_SUB"));
		}
		$sub_port = $ar['port'];
		if (!$sub_port)
		{
			$sub_port = 80;
		}
		if ($sub_port != $this->port)
		{
			$strRequest =
				'GET /check/?license_hash=' . LICENSE_HASH . '&host=' . urlencode($this->host) . '&port=' . urlencode($sub_port) . '&https=' . ($ar['scheme'] == 'https' ? 'Y' : 'N') . ' HTTP/1.1' . "\r\n" .
				'host: ' . $checker . "\r\n" .
				"\r\n";

			if (!$res = $this->ConnectToHost($checker, 80))
			{
				return $this->Result($retVal, GetMessage("MAIN_SC_NO_CONNECTTO", ['#HOST#' => $checker]));
			}
			$strRes = GetHttpResponse($res, $strRequest, $strHeaders);
			PrintHTTP($strRequest, $strHeaders, $strRes);
			if (!preg_match('#^Status: (.+)$#m', $strRes, $regs))
			{
				return $this->Result($retVal, GetMessage("MAIN_SC_UNKNOWN_ANSWER", ['#HOST#' => $checker]));
			}
			$status = intval($regs[1]);
			if (!$status)
			{
				return $this->Result($retVal, GetMessage("MAIN_SC_NO_PULL_EXTERNAL_2"));
			}
		}

		return true;
	}

	function check_perf()
	{
		$arTime = [];
		$count = 3;
		for ($i = 0; $i < $count; $i++)
		{
			if (!$res = $this->ConnectToHost())
			{
				return false;
			}

			$file = '';
			if (IsModuleInstalled('security'))
			{
				$file = COption::GetOptionString("security", "ipcheck_disable_file", "");
				COption::SetOptionString("security", "ipcheck_disable_file", $this->LogFile);
			}
			$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=perf&unique_id=" . checker_get_unique_id() . "&i=" . $i . " HTTP/1.1\r\n";
			$strRequest .= "Host: " . $this->host . "\r\n";
			$strRequest .= "\r\n";

			$strRes = GetHttpResponse($res, $strRequest, $strHeaders);

			if (IsModuleInstalled('security'))
			{
				COption::SetOptionString("security", "ipcheck_disable_file", $file);
			}

			if (!is_numeric($strRes))
			{
				PrintHTTP($strRequest, $strHeaders, $strRes);
				return $this->Result(false, GetMessage('SC_TEST_FAIL'));
			}

			$arTime[] = doubleval($strRes);
		}

		$r = doubleval($count) / array_sum($arTime);
		if ($r < 10)
		{
			$strResult = GetMessage("MAIN_PERF_VERY_LOW");
		}
		elseif ($r < 15)
		{
			$strResult = GetMessage("MAIN_PERF_LOW");
		}
		elseif ($r < 30)
		{
			$strResult = GetMessage("MAIN_PERF_MID");
		}
		else
		{
			$strResult = GetMessage("MAIN_PERF_HIGH");
		}
		return $this->Result($r >= 10, $strResult . ' (' . number_format($r, 2, ".", " ") . ' ' . GetMessage("MAIN_PAGES_PER_SECOND") . ')');
	}

	function check_cache()
	{
		$dir = $_SERVER["DOCUMENT_ROOT"] . BX_PERSONAL_ROOT . "/cache";
		$file0 = $dir . "/" . md5(mt_rand());
		$file1 = $file0 . ".tmp";
		$file2 = $file0 . ".php";
		if (!file_exists($dir))
		{
			mkdir($dir, BX_DIR_PERMISSIONS);
		}

		return ($f = fopen($file1, 'wb')) && (fclose($f)) && (rename($file1, $file2)) && (unlink($file2));
	}

	function check_exec()
	{
		$path = '/bitrix' . '/site_check_exec.php';
		if (!($f = fopen($_SERVER['DOCUMENT_ROOT'] . $path, 'wb')))
		{
			return $this->Result(false, GetMessage('SC_CHECK_FILES'));
		}

		chmod($_SERVER['DOCUMENT_ROOT'] . $path, BX_FILE_PERMISSIONS);

		fwrite($f, '<' . '? echo "SUCCESS"; ?' . '>');
		fclose($f);

		$strRequest = "GET " . $path . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		$strRequest .= "\r\n";

		if ($res = $this->ConnectToHost())
		{
			$retVal = IsHttpResponseSuccess($res, $strRequest);
		}
		else
		{
			$retVal = false;
		}

		unlink($_SERVER['DOCUMENT_ROOT'] . $path);

		return $retVal;
	}

	function check_security()
	{
		if (function_exists('apache_get_modules'))
		{
			$arLoaded = apache_get_modules();

			if (!in_array('mod_rewrite', $arLoaded))
			{
				return $this->Result(false, GetMessage('SC_WARN_MOD_REWRITE') . "<br>");
			}

			$strError = '';
			if (in_array('mod_security', $arLoaded))
			{
				$strError .= GetMessage('SC_WARN_SECURITY') . "<br>";
			}
			if (in_array('mod_dav', $arLoaded) || in_array('mod_dav_fs', $arLoaded))
			{
				$strError .= GetMessage('SC_WARN_DAV') . "<br>";
			}
			if ($strError)
			{
				return $this->Result(null, $strError);
			}
		}

		return $this->Result(true, GetMessage("MAIN_SC_NO_CONFLICT"));
	}

	function check_install_scripts()
	{
		$strError = '';
		foreach ([
			'restore.php',
			'bitrix_server_test.php',
			'bitrixsetup.php',
			'bitrix_install.php',
			'bitrix_setup.php',
			'bitrix6setup.php',
			'bitrix7setup.php',
			'bitrix8setup.php',
			'export_file.csv',
			'1c_bx_import.php',
		] as $file)
		{
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $file))
			{
				$strError .= GetMessage('SC_FILE_EXISTS') . ' ' . $file . "\n<br>";
			}
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/' . $file))
			{
				$strError .= GetMessage('SC_FILE_EXISTS') . ' /bitrix/' . $file . "\n<br>";
			}
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/' . $file))
			{
				$strError .= GetMessage('SC_FILE_EXISTS') . ' /upload/' . $file . "\n<br>";
			}
		}
		if ($strError)
		{
			return $this->Result(false, $strError);
		}
		return $this->Result(true, GetMessage("MAIN_SC_ABSENT_ALL"));
	}

	function check_getimagesize()
	{
		$file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/fileman/install/components/bitrix/player/mediaplayer/player';
		if (!file_exists($file))
		{
			return $this->Result(null, "File not found: " . $file);
		}

		if (false === getimagesize($file))
		{
			return $this->Result(null, GetMessage('SC_SWF_WARN'));
		}
		return true;
	}

	function check_localredirect()
	{
		$strSERVER = '';
		foreach (['SERVER_PORT', 'HTTPS', 'FCGI_ROLE', 'HTTP_HOST', 'SERVER_PROTOCOL'] as $var)
		{
			$strSERVER .= '&' . $var . '=' . urlencode($_SERVER[$var] ?? '');
		}

		if (!$this->arTestVars['last_value'])
		{
			$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=redirect_test&unique_id=" . checker_get_unique_id() . $strSERVER . " HTTP/1.1\r\n";
			$strRequest .= "Host: " . $this->host . "\r\n";
			$strRequest .= "\r\n";

			if (!$res = $this->ConnectToHost())
			{
				return false;
			}

			$strRes = GetHttpResponse($res, $strRequest, $strHeaders);

			if (preg_match('#Location: (.+)#i', $strHeaders, $regs))
			{
				$url = trim($regs[1]);
				if (!$url)
				{
					PrintHTTP($strRequest, $strHeaders, $strRes);
					return false;
				}

				$this->arTestVars['last_value'] = $url;
				$this->test_percent = 50;

				return true;
			}

			PrintHTTP($strRequest, $strHeaders, $strRes);
			return false;
		}
		else
		{
			$url = $this->arTestVars['last_value'];
			if (!$url)
			{
				return false;
			}

			$ar = parse_url($url);

			$host = $ar['host'];
			$ssl = $ar['scheme'] == 'https' ? 'ssl://' : '';
			$port = intval($ar['port']) ?: ($ssl ? 443 : 80);

			$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=redirect_test&unique_id=" . checker_get_unique_id() . $strSERVER . "&done=Y HTTP/1.1\r\n";
			$strRequest .= "Host: " . $host . "\r\n";
			$strRequest .= "\r\n";

			if ($res = $this->ConnectToHost($host, $port, $ssl))
			{
				return IsHttpResponseSuccess($res, $strRequest);
			}
			return false;
		}
	}

	function check_sites()
	{
		$strError = '';
		$bUtf = false;
		$bChar = false;
		$arDocRoot = [];

		$rs = CSite::GetList('', '', ['ACTIVE' => 'Y']);
		while ($f = $rs->Fetch())
		{
			$arDocRoot[] = trim($f['DOC_ROOT']);
			$bFound = str_contains(strtolower($f['CHARSET']), 'utf');

			$bUtf = $bUtf || $bFound;
			$bChar = $bChar || !$bFound;
		}

		if (count($arDocRoot) == 1)
		{
			if ($root = $arDocRoot[0])
			{
				$strError = GetMessage('SC_PATH_FAIL_SET') . ' <i>' . htmlspecialcharsbx($root) . '</i><br>';
			}
		}
		else
		{
			foreach ($arDocRoot as $root)
			{
				if ($root)
				{
					if (!is_readable($root . '/bitrix'))
					{
						$strError .= GetMessage('SC_NO_ROOT_ACCESS') . ' <i>' . htmlspecialcharsbx($root) . '/bitrix</i><br>';
					}
				}
			}
		}

		if ($bUtf && $bChar)
		{
			$strError .= GetMessage("SC_SITE_CHARSET_FAIL");
		}

		if ($strError)
		{
			return $this->Result(false, $strError);
		}

		return $this->Result(true, GetMessage("MAIN_SC_CORRECT"));
	}

	function check_pcre_recursion()
	{
		$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=pcre_recursion_test&unique_id=" . checker_get_unique_id() . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		$strRequest .= "\r\n";

		if ($res = $this->ConnectToHost())
		{
			if ('SUCCESS' == $strRes = GetHttpResponse($res, $strRequest, $strHeaders))
			{
				return true;
			}
			if ($strRes == 'CLEAN')
			{
				return $this->Result(null, GetMessage('SC_PCRE_CLEAN'));
			}
		}
		return false;
	}

	function check_method_exists()
	{
		$strRequest = "GET " . "/bitrix/admin/site_checker.php?test_type=method_exists&unique_id=" . checker_get_unique_id() . " HTTP/1.1\r\n";
		$strRequest .= "Host: " . $this->host . "\r\n";
		$strRequest .= "\r\n";

		if ($res = $this->ConnectToHost())
		{
			return IsHttpResponseSuccess($res, $strRequest);
		}
		return false;
	}

	function check_bx_crontab()
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$this->arTestVars['check_bx_crontab'] = false;
		if (defined('BX_CRONTAB'))
		{
			return $this->Result(false, GetMessage("MAIN_BX_CRONTAB_DEFINED"));
		}

		$bCron = COption::GetOptionString("main", "agents_use_crontab", "N") == 'Y' || defined('BX_CRONTAB_SUPPORT') && BX_CRONTAB_SUPPORT === true || COption::GetOptionString("main", "check_agents", "Y") != 'Y';
		if ($bCron)
		{
			if (!$connection->query('SELECT LAST_EXEC FROM b_agent WHERE LAST_EXEC > ' . $helper->addDaysToDateTime(-1) . ' AND IS_PERIOD = \'N\' LIMIT 1')->fetch())
			{
				return $this->Result(false, GetMessage("MAIN_CRON_NO_START"));
			}
			$this->arTestVars['check_bx_crontab'] = true;
			return true;
		}
		return $this->Result(null, GetMessage("MAIN_AGENTS_HITS"));
	}

	function check_pgsql_version()
	{
		$connection = Application::getConnection();

		$PgSql_vercheck_min = '11.0.0';

		$ver = $connection->getVersion()[0];
		if (version_compare($ver, $PgSql_vercheck_min, '<'))
		{
			return $this->Result(false, GetMessage('SC_PGSQL_ERR_VER', [
				'#CUR#' => $ver,
				'#REQ#' => $PgSql_vercheck_min,
			]));
		}

		return true;
	}

	function check_pgsql_db_charset()
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$strError = '';

		$f = $connection->query('select datctype as LC_CTYPE from pg_database where datname = \'' . $helper->forSql($connection->getDatabase()) . '\'')->fetch();
		$collation_database = $f['LC_CTYPE'];

		if (!preg_match('/\.(UTF-8|UTF8)$/i', $collation_database))
		{
			$strError = GetMessage('SC_DATABASE_LC_CTYPE', ['#VAL0#' => $collation_database]);
		}

		if (!$strError)
		{
			return true;
		}

		$this->arTestVars['db_charset_fail'] = true;
		return $this->Result(false, $strError);
	}

	function check_pgsql_connection_charset()
	{
		$connection = Application::getConnection();
		$strError = '';

		if ($this->arTestVars['check_mbstring_fail'])
		{
			return $this->Result(null, GetMessage('SC_MBSTRING_NA'));
		}

		$f = $connection->query('SHOW client_encoding')->fetch();
		$character_set_connection = $f['CLIENT_ENCODING'];

		if ($character_set_connection != 'UTF8')
		{
			$strError = GetMessage('SC_CONNECTION_CHARSET_WRONG', ['#VAL#' => 'utf8', '#VAL1#' => $character_set_connection]);
		}

		echo 'character_set_connection=' . $character_set_connection;

		if (!$strError)
		{
			return true;
		}

		$this->arTestVars['check_connection_charset_fail'] = true;

		return $this->Result(false, $strError);
	}

	##############################
	# MYSQL Tests follow
	##############################
	function check_mysql_bug_version()
	{
		global $DB;

		$MySql_vercheck_min = "5.0.0";

		$ver = $DB->GetVersion();
		if (version_compare($ver, $MySql_vercheck_min, '<'))
		{
			return $this->Result(false, GetMessage('SC_MYSQL_ERR_VER', ['#CUR#' => $ver, '#REQ#' => $MySql_vercheck_min]));
		}

		if ($ver == '4.1.21' // sorting
			|| $ver == '5.1.34' // auto_increment
			|| $ver == '5.0.41' // search
//			|| $ver == '5.1.66' // forum page navigation
		)
		{
			return $this->Result(false, GetMessage('SC_DB_ERR') . ' ' . $ver);
		}

		return true;
	}

	function check_mysql_mode()
	{
		global $DB;

		$errors = [];

		$f = $DB->Query('SHOW VARIABLES LIKE \'innodb_strict_mode\'')->Fetch();
		if (strtoupper($f['Value']) != 'OFF')
		{
			$errors[] = GetMessage('SC_DB_ERR_INNODB_STRICT', ['#VALUE#' => $f['Value']]);
		}

		$f = $DB->Query('SHOW VARIABLES LIKE \'sql_mode\'')->Fetch();
		if ($f['Value'] <> '')
		{
			$errors[] = GetMessage('SC_DB_ERR_MODE') . ' ' . $f['Value'];
		}

		$f = $DB->Query('SHOW VARIABLES LIKE \'innodb_large_prefix\'')->Fetch();
		if ($f)
		{
			if ($f['Value'] === '0' || $f['Value'] === 'OFF')
			{
				$errors[] = GetMessage('SC_DB_ERR_INNODB_LARGE_PREFIX', ['#VALUE#' => $f['Value']]);
			}
		}

		$f = $DB->Query('SHOW VARIABLES LIKE \'innodb_default_row_format\'')->Fetch();
		if ($f)
		{
			if (strtolower($f['Value']) !== 'dynamic')
			{
				$errors[] = GetMessage('SC_DB_ERR_INNODB_DEFAULT_ROW_FORMAT', ['#VALUE#' => $f['Value']]);
			}
		}

		$f = $DB->Query('SHOW VARIABLES LIKE \'default_storage_engine\'')->Fetch();
		if ($f)
		{
			if (strtolower($f['Value']) !== 'innodb')
			{
				$errors[] = GetMessage('SC_DB_ERR_DEFAULT_STORAGE_ENGINE', ['#VALUE#' => $f['Value']]);
			}
		}

		return $errors ? $this->Result(false, implode('<br>', $errors)) : true;
	}

	function check_mysql_time()
	{
		global $DB;

		$s = time();
		while ($s == time())
		{
			;
		}
		$s++;
		$res = $DB->Query('SELECT NOW() AS A');
		$f = $res->Fetch();
		if (($diff = abs($s - strtotime($f['A']))) <= 1)
		{
			return true;
		}
		return $this->Result(false, GetMessage('SC_TIME_DIFF', ['#VAL#' => $diff]));
	}

	function check_mysql_table_status()
	{
		global $DB;
		$time = time();

		$strError = '';
		$i = 0;
		$res = $DB->Query('SHOW TABLES');
		$cnt = $res->SelectedRowsCount();
		while ($f = $res->Fetch())
		{
			$i++;
			$table = current($f);

			if ($this->arTestVars['last_value'])
			{
				if ($this->arTestVars['last_value'] == $table)
				{
					unset($this->arTestVars['last_value']);
				}
				continue;
			}

			if (!$this->fix_mode)
			{
				$res0 = $DB->Query('CHECK TABLE ' . $DB->quote($table));
			}
			else
			{
				$res0 = $DB->Query('REPAIR TABLE ' . $DB->quote($table));
			}

			$f0 = $res0->Fetch();
			if ($f0['Msg_type'] == 'error' || $f0['Msg_type'] == 'warning')
			{
				$strError .= GetMessage('SC_TABLE_ERR', ['#VAL#' => $table]) . ' ' . $f0['Msg_text'] . "\n<br>";
			}

			if (time() - $time >= $this->timeout)
			{
				$this->arTestVars['last_value'] = $table;
				$this->test_percent = floor($i / $cnt * 100);
				return true;
			}
		}

		if (!$strError)
		{
			return true;
		}

		if (!$this->fix_mode)
		{
			$this->arTestVars['check_table_status_fail'] = true;
			echo $strError; // to log
			return $this->Result(false, GetMessage('SC_TABLES_NEED_REPAIR') . fix_link(1));
		}

		return $this->Result(false, $strError);
	}

	function check_mysql_connection_charset()
	{
		global $DB;
		$strError = '';

		if (!empty($this->arTestVars['check_mbstring_fail']))
		{
			return $this->Result(null, GetMessage('SC_MBSTRING_NA'));
		}

		$res = $DB->Query('SHOW VARIABLES LIKE "character_set_connection"');
		$f = $res->Fetch();
		$character_set_connection = $f['Value'];

		$res = $DB->Query('SHOW VARIABLES LIKE "character_set_results"');
		$f = $res->Fetch();
		$character_set_results = $f['Value'];

		$res = $DB->Query('SHOW VARIABLES LIKE "collation_connection"');
		$f = $res->Fetch();
		$collation_connection = $f['Value'];

		if (!in_array($character_set_connection, ['utf8', 'utf8mb3', 'utf8mb4']))
		{
			$strError = GetMessage("SC_CONNECTION_CHARSET_WRONG", ['#VAL#' => 'utf8', '#VAL1#' => $character_set_connection]);
		}
		elseif (!preg_match('/^(utf8|utf8mb3|utf8mb4)_/', $collation_connection))
		{
			$strError = GetMessage("SC_CONNECTION_COLLATION_WRONG_UTF", ['#VAL#' => $collation_connection]);
		}

		if (!$strError && $character_set_connection != $character_set_results)
		{
			$strError = GetMessage('SC_CHARSET_CONN_VS_RES', ['#CONN#' => $character_set_connection, '#RES#' => $character_set_results]);
		}

		echo 'character_set_connection=' . $character_set_connection . ', collation_connection=' . $collation_connection . ', character_set_results=' . $character_set_results;

		if (!$strError)
		{
			return true;
		}

		$this->arTestVars['check_connection_charset_fail'] = true;

		return $this->Result(false, $strError);
	}

	function check_mysql_db_charset()
	{
		global $DB;
		if (!empty($this->arTestVars['check_mbstring_fail']))
		{
			return $this->Result(null, GetMessage('SC_MBSTRING_NA'));
		}
		elseif (!empty($this->arTestVars['check_table_status_fail']))
		{
			return $this->Result(null, GetMessage('SC_TABLES_NEED_REPAIR'));
		}
		elseif (!empty($this->arTestVars['check_connection_charset_fail']))
		{
			return $this->Result(null, GetMessage('SC_CONNECTION_CHARSET_NA'));
		}

		$strError = '';

		$res = $DB->Query('SHOW VARIABLES LIKE \'character_set_connection\'');
		$f = $res->Fetch();
		$character_set_connection = $f['Value'];

		$res = $DB->Query('SHOW VARIABLES LIKE \'collation_connection\'');
		$f = $res->Fetch();
		$collation_connection = $f['Value'];

		$res = $DB->Query('SHOW VARIABLES LIKE \'character_set_database\'');
		$f = $res->Fetch();
		$character_set_database = $f['Value'];

		$res = $DB->Query('SHOW VARIABLES LIKE \'collation_database\'');
		$f = $res->Fetch();
		$collation_database = $f['Value'];

		if ($this->fix_mode)
		{
			if (!$DB->Query($sql = 'ALTER DATABASE ' . $DB->quote($DB->DBName) . ' DEFAULT CHARACTER SET ' . $character_set_connection . ' COLLATE ' . $collation_connection, true))
			{
				$strError = $sql . ' [' . $DB->db_Error . ']';
			}
		}
		else
		{
			if ($character_set_connection != $character_set_database)
			{
				$strError = GetMessage('SC_DATABASE_CHARSET_DIFF', ['#VAL0#' => $character_set_connection, '#VAL1#' => $character_set_database]) . fix_link();
			}
			elseif ($collation_database != $collation_connection)
			{
				$strError = GetMessage('SC_DATABASE_COLLATION_DIFF', ['#VAL0#' => $collation_connection, '#VAL1#' => $collation_database]) . fix_link();
			}
		}

		echo 'CHARSET=' . $character_set_database . ', COLLATION=' . $collation_database;

		if (!$strError)
		{
			return true;
		}

		$this->arTestVars['db_charset_fail'] = true;
		return $this->Result(false, $strError);
	}

	function check_mysql_table_format()
	{
		global $DB;
		$strError = '';

		$res = $DB->Query("
			select
				TABLE_NAME
				,ENGINE
			from
				information_schema.TABLES
			where
				TABLE_SCHEMA = '" . $DB->ForSql($DB->DBName) . "'
				and TABLE_TYPE = 'BASE TABLE'
				and TABLE_NAME like 'b\_%'
				and CREATE_OPTIONS <> _ascii'row_format=DYNAMIC'
				and (
					UPPER(ROW_FORMAT) in ('REDUNDANT', 'COMPACT')
					or ENGINE <> 'InnoDB'
				)
		");
		while ($f = $res->Fetch())
		{
			if ($this->fix_mode)
			{
				$alter = 'ALTER TABLE ' . $DB->quote($f['TABLE_NAME']) . ' ROW_FORMAT=Dynamic ENGINE=InnoDB';
				$res0 = $DB->Query($alter, true);
				if (!$res0)
				{
					$strError .= $alter . ' [' . $DB->db_Error . ']';
					break;
				}
			}
			else
			{
				$strError .= GetMessage('SC_TABLE_ROW_FORMAT', ['#TABLE#' => $f['TABLE_NAME']]) . '<br>';
				$this->arTestVars['iError']++;
				$this->arTestVars['iErrorAutoFix']++;
			}
		}

		if (!$strError)
		{
			return true;
		}

		$this->arTestVars['table_format_fail'] = true;

		if ($this->fix_mode)
		{
			return $this->Result(false, $strError);
		}
		else
		{
			echo $strError; // to log
			return $this->Result(false, GetMessage('SC_TABLE_ROW_FORMAT_ERRORS', [
					'#VAL#' => intval($this->arTestVars['iError']),
					'#VAL1#' => intval($this->arTestVars['iErrorAutoFix']),
				]) . ($this->arTestVars['iErrorAutoFix'] > 0 ? fix_link() : ''));
		}
	}

	function check_mysql_table_charset()
	{
		global $DB;
		$strError = '';

		if (!empty($this->arTestVars['check_mbstring_fail']))
		{
			return $this->Result(null, GetMessage('SC_MBSTRING_NA'));
		}
		elseif (!empty($this->arTestVars['check_table_status_fail']))
		{
			return $this->Result(null, GetMessage('SC_TABLES_NEED_REPAIR'));
		}
		elseif (!empty($this->arTestVars['check_connection_charset_fail']))
		{
			return $this->Result(null, GetMessage('SC_CONNECTION_CHARSET_NA'));
		}
		elseif (!empty($this->arTestVars['db_charset_fail']))
		{
			return $this->Result(null, GetMessage('SC_TABLE_CHECK_NA'));
		}
		elseif (!empty($this->arTestVars['table_format_fail']))
		{
			return $this->Result(null, GetMessage('SC_TABLE_ROW_FORMAT_NA'));
		}

		$res = $DB->Query('SHOW VARIABLES LIKE "character_set_database"');
		$f = $res->Fetch();
		$charset = trim($f['Value']);

		$res = $DB->Query('SHOW VARIABLES LIKE "collation_database"');
		$f = $res->Fetch();
		$collation = trim($f['Value']);

		$time = time();
		$i = 0;
		$res = $DB->Query('SHOW TABLES LIKE \'b\_%\'');
		$cnt = $res->SelectedRowsCount();

		$arExclusion = [
			'b_sale_loc_search_word' => 'WORD',
			'b_search_content_stem' => 'STEM',
			'b_search_content_freq' => 'STEM',
			'b_search_stem' => 'STEM',
			'b_search_tags' => 'NAME',
			'b_translate_path' => 'NAME',
			'b_translate_phrase' => 'CODE',
		];
		while ($f = $res->Fetch())
		{
			$i++;
			$table = current($f);

			if (!empty($this->arTestVars['last_value']))
			{
				if ($this->arTestVars['last_value'] == $table)
				{
					unset($this->arTestVars['last_value']);
				}
				continue;
			}

			$res0 = $DB->Query('SHOW CREATE TABLE ' . $DB->quote($table), true);
			if ($res0 === false)
			{
				if ($this->fix_mode)
				{
					$DB->Query('DROP TABLE ' . $DB->quote($table), true);
				}
				else
				{
					$strError .= GetMessage('SC_TABLE_BROKEN', ['#TABLE#' => $table]) . "<br>";
					$this->arTestVars['iError']++;
					$this->arTestVars['iErrorAutoFix']++;
				}
				continue;
			}
			$f0 = $res0->Fetch();

			if (preg_match('/DEFAULT CHARSET=([a-z0-9\-_]+)/i', $f0['Create Table'], $regs))
			{
				$t_charset = $regs[1];
				if (preg_match('/COLLATE=([a-z0-9\-_]+)/i', $f0['Create Table'], $regs))
				{
					$t_collation = $regs[1];
				}
				else
				{
					$res0 = $DB->Query('SHOW CHARSET LIKE \'' . $DB->ForSql(str_replace('_', '\_', $t_charset)) . '\'');
					$f0 = $res0->Fetch();
					$t_collation = $f0['Default collation'];
				}
			}
			else
			{
				$res0 = $DB->Query('SHOW TABLE STATUS LIKE \'' . $DB->ForSql(str_replace('_', '\_', $table)) . '\'');
				$f0 = $res0->Fetch();
				if (!$t_collation = $f0['Collation'])
				{
					continue;
				}
				$t_charset = getCharsetByCollation($t_collation);
			}

			if ($charset != $t_charset)
			{
				// table charset differs
				if (!$this->fix_mode)
				{
					$strError .= GetMessage('SC_DB_MISC_CHARSET', ['#TABLE#' => $table, '#VAL1#' => $t_charset, '#VAL0#' => $charset]) . "<br>";
					$this->arTestVars['iError']++;
					if ($this->force_repair)
					{
						$this->arTestVars['iErrorAutoFix']++;
					}
				}
				elseif ($this->force_repair && !$DB->Query($sql = 'ALTER TABLE ' . $DB->quote($table) . ' CHARACTER SET ' . $charset, true))
				{
					$strError .= $sql . ' [' . $DB->db_Error . ']';
					break;
				}
			}
			elseif ($t_collation != $collation)
			{    // table collation differs
				if (!$this->fix_mode)
				{
					$strError .= GetMessage('SC_COLLATE_WARN', ['#TABLE#' => $table, '#VAL1#' => $t_collation, '#VAL0#' => $collation]) . "<br>";
					$this->arTestVars['iError']++;
					$this->arTestVars['iErrorAutoFix']++;
				}
				elseif (!$DB->Query($sql = 'ALTER TABLE ' . $DB->quote($table) . ' COLLATE ' . $collation, true))
				{
					$strError .= $sql . ' [' . $DB->db_Error . ']';
					break;
				}
			}

			// fields check
			$arFix = [];
			$res0 = $DB->Query("SHOW FULL COLUMNS FROM " . $DB->quote($table));
			while ($f0 = $res0->Fetch())
			{
				$f_collation = $f0['Collation'];
				if ($f_collation === null || $f_collation === "NULL")
				{
					continue;
				}

				$f_charset = getCharsetByCollation($f_collation);

				if ($charset != $f_charset)
				{
					// field charset differs
					if (!$this->fix_mode)
					{
						$strError .= GetMessage('SC_TABLE_CHARSET_WARN', ['#TABLE#' => $table, '#VAL0#' => $charset, '#VAL1#' => $f_charset, '#FIELD#' => $f0['Field']]) . "<br>";
						$this->arTestVars['iError']++;
						if ($this->force_repair)
						{
							$this->arTestVars['iErrorAutoFix']++;
						}
					}
					elseif ($this->force_repair)
					{
						$arFix[] = ' MODIFY ' . $DB->quote($f0['Field'])
							. ' ' . $f0['Type']
							. ' CHARACTER SET ' . $charset
							. ($f0['Null'] == 'YES' ? ' NULL' : ' NOT NULL')
							. ($f0['Default'] === null ? ($f0['Null'] == 'YES' ? ' DEFAULT NULL ' : '') : ' DEFAULT ' . ($f0['Type'] == 'timestamp' && $f0['Default'] ? $f0['Default'] : '"' . $DB->ForSQL($f0['Default']) . '"'))
							. ' ' . str_ireplace('DEFAULT_GENERATED', '', $f0['Extra'])
							. ($f0['Comment'] ? ' COMMENT \'' . $DB->ForSql($f0['Comment']) . '\'' : '');
					}
				}
				elseif ($collation != $f_collation)
				{
					if (!empty($arExclusion[$table]) && strtoupper($f0['Field']) == $arExclusion[$table])
					{
						continue;
					}

					// field collation differs
					if (!$this->fix_mode)
					{
						$strError .= GetMessage('SC_FIELDS_COLLATE_WARN', ['#TABLE#' => $table, '#VAL0#' => $collation, '#VAL1#' => $f_collation, '#FIELD#' => $f0['Field']]) . "<br>";

						if (!isset($this->arTestVars['iError']))
						{
							$this->arTestVars['iError'] = 0;
						}
						$this->arTestVars['iError']++;

						if (!isset($this->arTestVars['iErrorAutoFix']))
						{
							$this->arTestVars['iErrorAutoFix'] = 0;
						}
						$this->arTestVars['iErrorAutoFix']++;
					}
					else
					{
						$arFix[] = ' MODIFY ' . $DB->quote($f0['Field'])
							. ' ' . $f0['Type']
							. ' COLLATE ' . $collation . ($f0['Null'] == 'YES' ? ' NULL' : ' NOT NULL')
							. ($f0['Default'] === null ? ($f0['Null'] == 'YES' ? ' DEFAULT NULL ' : '') : ' DEFAULT ' . ($f0['Type'] == 'timestamp' && $f0['Default'] ? $f0['Default'] : '"' . $DB->ForSQL($f0['Default']) . '"'))
							. ' ' . str_ireplace('DEFAULT_GENERATED', '', $f0['Extra'])
							. ($f0['Comment'] ? ' COMMENT \'' . $DB->ForSql($f0['Comment']) . '\'' : '');
					}
				}
			}

			if ($this->fix_mode && count($arFix))
			{
				if (!$DB->Query($sql = 'ALTER TABLE ' . $DB->quote($table) . ' ' . implode(",\n", $arFix), true))
				{
					$strError .= $sql . ' [' . $DB->db_Error . ']';
					break;
				}
			}

			if (time() - $time >= $this->timeout)
			{
				$this->arTestVars['last_value'] = $table;
				$this->test_percent = floor($i / $cnt * 100);
				return true;
			}
		}

		if (!$strError)
		{
			return true;
		}

		$this->arTestVars['table_charset_fail'] = true;

		if ($this->fix_mode)
		{
			return $this->Result(false, $strError);
		}
		else
		{
			echo $strError; // to log
			return $this->Result(false, GetMessage('SC_CHECK_TABLES_ERRORS', ['#VAL#' => intval($this->arTestVars['iError']), '#VAL1#' => intval($this->arTestVars['iErrorAutoFix'])]) . ($this->arTestVars['iErrorAutoFix'] > 0 ? fix_link() : ''));
		}
	}

	function check_mysql_table_structure()
	{
		global $DB;
		$strError = '';

		$arInsertExclude = [
			'b_seo_search_engine' => 1,
			'b_hot_keys_code' => 1,
		];

		if (!empty($this->arTestVars['table_charset_fail']))
		{
			return $this->Result(null, GetMessage('SC_TABLE_COLLATION_NA'));
		}
		elseif (!empty($this->arTestVars['table_format_fail']))
		{
			return $this->Result(null, GetMessage('SC_TABLE_ROW_FORMAT_NA'));
		}

		$DB->Query("SET SQL_MODE=''");

		$module = '';
		$cnt = $iCurrent = 0;
		if ($dir = opendir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules'))
		{
			while (false !== ($item = readdir($dir)))
			{
				if (str_contains($item, '.')) // skipping all external modules
				{
					continue;
				}

				$cnt++;

				if (!empty($this->arTestVars['last_value']))
				{
					$iCurrent++;
					if ($this->arTestVars['last_value'] == $item)
					{
						unset($this->arTestVars['last_value']);
					}
				}
				elseif (!$module)
				{
					$module = $item;
				}
			}
			closedir($dir);
		}
		else
		{
			return false;
		}

		$file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module . '/install/db/mysql/install.sql';
		if (!file_exists($file))
		{
			$file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module . '/install/mysql/install.sql';
		}
		if (file_exists($file)) // uses database...
		{
			$arTableColumns = [];
			$bModuleInstalled = ModuleTable::getById($module)->fetch();

			if (false === ($query = file_get_contents($file)))
			{
				return false;
			}

			$arTables = [];
			$arQuery = $DB->ParseSQLBatch(str_replace("\r", "", $query));
			foreach ($arQuery as $sql)
			{
				if (preg_match('#^(CREATE TABLE )(IF NOT EXISTS)? *`?([a-z0-9_]+)`?(.*);?$#mis', $sql, $regs))
				{
					$table = $regs[3];
					if (str_starts_with($table, 'site_checker_'))
					{
						continue;
					}

					$bTableExists = $DB->Query('SHOW TABLES LIKE \'' . $DB->ForSql(str_replace('_', '\_', $table)) . '\'')->Fetch();
					if (!$bTableExists && $bModuleInstalled)
					{
						if ($this->fix_mode)
						{
							if (!$DB->Query($sql, true))
							{
								return $this->Result(false, 'Mysql Query Error: ' . $sql . ' [' . $DB->db_Error . ']');
							}
						}
						else
						{
							$strError .= GetMessage('SC_ERR_NO_TABLE', ['#TABLE#' => $table]) . "<br>";
							Application::getInstance()->getSession()['FixQueryList'][] = $sql;
							$this->arTestVars['iError'] = ($this->arTestVars['iError'] ?? 0) + 1;
							$this->arTestVars['iErrorAutoFix'] = ($this->arTestVars['iErrorAutoFix'] ?? 0) + 1;
							$this->arTestVars['cntNoTables'] = ($this->arTestVars['cntNoTables'] ?? 0) + 1;
							continue;
						}
					}

					if ($bTableExists || $bModuleInstalled)
					{
						$arTables[$table] = $sql;
						$tmp_table = 'site_checker_' . $table;
						$DB->Query('DROP TABLE IF EXISTS ' . $DB->quote($tmp_table));
						$DB->Query($regs[1] . ' ' . $DB->quote($tmp_table) . $regs[4]);
					}
				}
				elseif (preg_match('#^(ALTER TABLE)( )?`?([a-z0-9_]+)`?(.*);?$#mis', $sql, $regs))
				{
					$table = $regs[3];
					if (!$arTables[$table])
					{
						continue;
					}
					$tmp_table = 'site_checker_' . $table;
					$DB->Query($regs[1] . ' ' . $DB->quote($tmp_table) . $regs[4]);
				}
				elseif (preg_match('#^INSERT INTO *`?([a-z0-9_]+)`?[^(]*\(?([^)]*)\)?[^V]*VALUES[^(]*\((.+)\);?$#mis', $sql, $regs))
				{
					$table = $regs[1];
					if (!$arTables[$table] || !empty($arInsertExclude[$table]))
					{
						continue;
					}
					$tmp_table = 'site_checker_' . $table;

					if ($regs[2])
					{
						$arColumns = explode(',', $regs[2]);
					}
					else
					{
						if (!$arTableColumns[$tmp_table])
						{
							$rs = $DB->Query('SHOW COLUMNS FROM ' . $DB->quote($tmp_table));
							while ($f = $rs->Fetch())
							{
								$arTableColumns[$tmp_table][] = $f['Field'];
							}
						}
						$arColumns = $arTableColumns[$tmp_table];
					}

					$strValues = $regs[3];
					$ar = explode(",", $strValues);
					$arValues = [];
					$i = 0;
					$str = '';
					foreach ($ar as $v)
					{
						$str .= ($str ? ',' : '') . $v;
						if (preg_match('#^ *(-?[0-9]+|\'.*\'|".*"|null|now\(\)) *$#i', $str))
						{
							$arValues[$i] = $str;
							$str = '';
							$i++;
						}
					}

					if (!$str)
					{
						$sqlSelect = 'SELECT * FROM ' . $DB->quote($table) . ' WHERE 1=1 ';
						foreach ($arColumns as $k => $c)
						{
							$v = $arValues[$k];
							if (!preg_match('#null|now\(\)#i', $v))
							{
								$sqlSelect .= ' AND ' . $c . '=' . $v;
							}
						}
						$rs = $DB->Query($sqlSelect);
						if (!$rs->Fetch())
						{
							if ($this->fix_mode)
							{
								if (!$DB->Query($sql, true))
								{
									return $this->Result(false, 'Mysql Query Error: ' . $sql . ' [' . $DB->db_Error . ']');
								}
							}
							else
							{
								$strError .= GetMessage('SC_ERR_NO_VALUE', ['#TABLE#' => $table, '#SQL#' => $sql]) . "<br>";
								Application::getInstance()->getSession()['FixQueryList'][] = $sql;
								$this->arTestVars['iError'] = ($this->arTestVars['iError'] ?? 0) + 1;
								$this->arTestVars['iErrorAutoFix'] = ($this->arTestVars['iErrorAutoFix'] ?? 0) + 1;
								$this->arTestVars['cntNoValues'] = ($this->arTestVars['cntNoValues'] ?? 0) + 1;
							}
						}
					}
					else
					{
						echo "Error parsing SQL:\n" . $sql . "\n";
					}
				}
			}

			if (file_exists($file = str_replace('/install.sql', '/install_ft.sql', $file)))
			{
				if (false === ($query = file_get_contents($file)))
				{
					return false;
				}
				$query = preg_replace('# on +([a-z_0-9]+) \(#i', ' on site_checker_\\1 (', $query);
				$arQuery = $DB->ParseSQLBatch(str_replace("\r", "", $query));
				foreach ($arQuery as $sql)
				{
					if (!$DB->Query($sql, true))
					{
						break;
					}
				}
			}

			foreach ($arTables as $table => $sql)
			{
				$arIndexes = [];
				$rs = $DB->Query('SHOW INDEXES FROM ' . $DB->quote($table));
				while ($f = $rs->Fetch())
				{
					$column = strtolower($f['Column_name'] . ($f['Sub_part'] ? '(' . $f['Sub_part'] . ')' : ''));
					if (!empty($arIndexes[$f['Key_name']]))
					{
						$arIndexes[$f['Key_name']] .= ',' . $column;
					}
					else
					{
						$arIndexes[$f['Key_name']] = $column;
					}
				}

				$tmp_table = 'site_checker_' . $table;
				$arIndexes_tmp = [];
				$arFT = [];
				$arUnique = [];
				$rs = $DB->Query('SHOW INDEXES FROM ' . $DB->quote($tmp_table));
				while ($f = $rs->Fetch())
				{
					$column = strtolower($f['Column_name'] . ($f['Sub_part'] ? '(' . $f['Sub_part'] . ')' : ''));
					if (!empty($arIndexes_tmp[$f['Key_name']]))
					{
						$arIndexes_tmp[$f['Key_name']] .= ',' . $column;
					}
					else
					{
						$arIndexes_tmp[$f['Key_name']] = $column;
					}
					if ($f['Index_type'] == 'FULLTEXT')
					{
						$arFT[$f['Key_name']] = true;
					}
					if ($f['Non_unique'] == 0)
					{
						$arUnique[$f['Key_name']] = true;
					}
				}

				foreach ($arIndexes_tmp as $name => $ix)
				{
					if (!in_array($ix, $arIndexes))
					{
						if (!empty($arIndexes[$name]))
						{
							if ($name == 'PRIMARY') // dropping primary is not supported
							{
								continue;
							}

							$sql = 'ALTER TABLE ' . $DB->quote($table) . ' DROP INDEX ' . $DB->quote($name);
							if ($this->fix_mode)
							{
								if (!$DB->Query($sql, true))
								{
									return $this->Result(false, 'Mysql Query Error: ' . $sql . ' [' . $DB->db_Error . ']');
								}
							}
							else
							{
								Application::getInstance()->getSession()['FixQueryList'][] = $sql;
								$this->arTestVars['iError'] = ($this->arTestVars['iError'] ?? 0) + 1;
								$this->arTestVars['iErrorAutoFix'] = ($this->arTestVars['iErrorAutoFix'] ?? 0) + 1;
							}
						}
					}
				}

				$arColumns = [];
				$rs = $DB->Query('SHOW COLUMNS FROM ' . $DB->quote($table));
				while ($f = $rs->Fetch())
				{
					$arColumns[strtolower($f['Field'])] = $f;
				}

				$rs = $DB->Query('SHOW COLUMNS FROM ' . $DB->quote($tmp_table));
				while ($f_tmp = $rs->Fetch())
				{
					$tmp = TableFieldConstruct($f_tmp);
					if (isset($arColumns[strtolower($f_tmp['Field'])]))
					{
						$f = $arColumns[strtolower($f_tmp['Field'])];
						if (($cur = TableFieldConstruct($f)) != $tmp)
						{
							$sql = 'ALTER TABLE ' . $DB->quote($table) . ' CHANGE ' . $DB->quote($f['Field']) . ' ' . $tmp;
							if ($this->fix_mode)
							{
								if ($this->TableFieldCanBeAltered($f, $f_tmp))
								{
									if (!$DB->Query($sql, true))
									{
										return $this->Result(false, 'Mysql Query Error: ' . $sql . ' [' . $DB->db_Error . ']');
									}
								}
								else
								{
									$this->arTestVars['iErrorFix'] = ($this->arTestVars['iErrorFix'] ?? 0) + 1;
								}
							}
							else
							{
								Application::getInstance()->getSession()['FixQueryList'][] = $sql;
								$strError .= GetMessage('SC_ERR_FIELD_DIFFERS', ['#TABLE#' => $table, '#FIELD#' => $f['Field'], '#CUR#' => $cur, '#NEW#' => $tmp]) . "<br>";
								$this->arTestVars['iError'] = ($this->arTestVars['iError'] ?? 0) + 1;
								if ($this->TableFieldCanBeAltered($f, $f_tmp))
								{
									$this->arTestVars['iErrorAutoFix'] = ($this->arTestVars['iErrorAutoFix'] ?? 0) + 1;
								}
								$this->arTestVars['cntDiffFields'] = ($this->arTestVars['cntDiffFields'] ?? 0) + 1;
							}
						}
					}
					else
					{
						$sql = 'ALTER TABLE ' . $DB->quote($table) . ' ADD ' . preg_replace('#auto_increment#i', '', $tmp); // if only Primary Key is missing we will have to pass the test twice
						if ($this->fix_mode)
						{
							if (!$DB->Query($sql, true))
							{
								return $this->Result(false, 'Mysql Query Error: ' . $sql . ' [' . $DB->db_Error . ']');
							}
						}
						else
						{
							Application::getInstance()->getSession()['FixQueryList'][] = $sql;
							$strError .= GetMessage('SC_ERR_NO_FIELD', ['#TABLE#' => $table, '#FIELD#' => $f_tmp['Field']]) . "<br>";
							$this->arTestVars['iError'] = ($this->arTestVars['iError'] ?? 0) + 1;
							$this->arTestVars['iErrorAutoFix'] = ($this->arTestVars['iErrorAutoFix'] ?? 0) + 1;
							$this->arTestVars['cntNoFields'] = ($this->arTestVars['cntNoFields'] ?? 0) + 1;
						}
					}
				}

				foreach ($arIndexes_tmp as $name => $ix)
				{
					if (!in_array($ix, $arIndexes))
					{
						if ($name == 'PRIMARY' && $arIndexes['PRIMARY']) // Primary key exists
						{
							$this->arTestVars['iError'] = ($this->arTestVars['iError'] ?? 0) + 1;
							$strError .= GetMessage('SC_ERR_NO_INDEX', ['#TABLE#' => $table, '#INDEX#' => $name . ' (' . $ix . ')']) . "<br>";
							continue;
						}

						if ($name == 'PRIMARY')
						{
							$sql = 'ALTER TABLE ' . $DB->quote($table) . ' ADD PRIMARY KEY (' . $ix . ')';
						}
						else
						{
							$indexType = '';
							if (!empty($arFT[$name]))
							{
								$indexType = 'FULLTEXT ';
							}
							elseif (!empty($arUnique[$name]))
							{
								$indexType = 'UNIQUE ';
							}
							$sql = 'CREATE ' . $indexType . 'INDEX ' . $DB->quote($name) . ' ON ' . $DB->quote($table) . ' (' . $ix . ')';
						}
						if ($this->fix_mode)
						{
							if (!$DB->Query($sql, true))
							{
								return $this->Result(false, 'Mysql Query Error: ' . $sql . ' [' . $DB->db_Error . ']');
							}
						}
						else
						{
							Application::getInstance()->getSession()['FixQueryList'][] = $sql;
							$strError .= GetMessage('SC_ERR_NO_INDEX', ['#TABLE#' => $table, '#INDEX#' => $name . ' (' . $ix . ')']) . "<br>";
							$this->arTestVars['iError'] = ($this->arTestVars['iError'] ?? 0) + 1;
							$this->arTestVars['iErrorAutoFix'] = ($this->arTestVars['iErrorAutoFix'] ?? 0) + 1;
							$this->arTestVars['cntNoIndexes'] = ($this->arTestVars['cntNoIndexes'] ?? 0) + 1;
						}
					}
				}

				$DB->Query('DROP TABLE ' . $DB->quote($tmp_table));
			}

			echo $strError; // to log
		}

		if ($iCurrent < $cnt) // partial
		{
			$this->arTestVars['last_value'] = $module;
			$this->test_percent = floor($iCurrent / $cnt * 100);
			return true;
		}

		if ($this->fix_mode)
		{
			if (!empty($this->arTestVars['iErrorFix']))
			{
				return $this->Result(null, GetMessage('SC_CHECK_TABLES_STRUCT_ERRORS_FIX',
					[
						'#VAL#' => intval($this->arTestVars['iErrorFix']),
					]));
			}
		}
		else
		{
			if (isset($this->arTestVars['iError']) && $this->arTestVars['iError'] > 0)
			{
				if (is_array(Application::getInstance()->getSession()['FixQueryList']) && count(Application::getInstance()->getSession()['FixQueryList']))
				{
					echo implode(";\n", Application::getInstance()->getSession()['FixQueryList']) . ';';
				}
				Application::getInstance()->getSession()['FixQueryList'] = [];
				return $this->Result(
					false,
					GetMessage('SC_CHECK_TABLES_STRUCT_ERRORS', [
						'#VAL#' => intval($this->arTestVars['iError'] ?? 0),
						'#VAL1#' => intval($this->arTestVars['iErrorAutoFix'] ?? 0),
						'#NO_TABLES#' => intval($this->arTestVars['cntNoTables'] ?? 0),
						'#NO_FIELDS#' => intval($this->arTestVars['cntNoFields'] ?? 0),
						'#DIFF_FIELDS#' => intval($this->arTestVars['cntDiffFields'] ?? 0),
						'#NO_INDEXES#' => intval($this->arTestVars['cntNoIndexes'] ?? 0),
						'#NO_VALUES#' => intval($this->arTestVars['cntNoValues'] ?? 0),
					]) . (!empty($this->arTestVars['iErrorAutoFix']) ? fix_link(3) : '')
				);
			}
		}
		return true;
	}
	###############
	# }
	#

	public static function CommonTest()
	{
		if (
			defined('BX_CRONTAB')
			|| (defined('CHK_EVENT') && CHK_EVENT === true)
			|| (!isset($_SERVER['HTTP_HOST']) || !$_SERVER['HTTP_HOST'])
		)
		{
			// can't get real HTTP server vars from cron
			return "CSiteCheckerTest::CommonTest();";
		}

		if (
			($ntlm_varname = COption::GetOptionString('ldap', 'ntlm_varname', 'REMOTE_USER'))
			&& (trim($_SERVER[$ntlm_varname] ?? ''))
		)
		{
			// Server NTLM is enabled, no way to connect through a socket
			return "CSiteCheckerTest::CommonTest();";
		}

		IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/site_checker.php');

		$step = 0;
		$ar = null;
		$success = true;
		while (true)
		{
			$oTest = new CSiteCheckerTest($step, 1);
			if ($ar !== null)
			{
				$oTest->arTestVars = $ar;
			}
			$oTest->ssl =
				(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
				|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
				|| (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443);

			if (preg_match('#^(.+):([0-9]+)$#', $_SERVER['HTTP_HOST'], $regs))
			{
				$oTest->host = $regs[1];
				$oTest->port = $regs[2];
				if ($oTest->port == 443)
				{
					$oTest->ssl = true;
				}
			}
			else
			{
				$oTest->host = $_SERVER['HTTP_HOST'];
				$oTest->port = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : ($oTest->ssl ? 443 : 80);
			}
			$oTest->Start();
			if ($oTest->result === false)
			{
				$error = [
					"MESSAGE" =>
						(
						IsModuleInstalled('intranet') ?
							GetMessage("MAIN_SC_GOT_ERRORS", ['#LINK#' => "/bitrix/admin/site_checker.php?lang=" . LANGUAGE_ID . "&express_test=Y"]) :
							GetMessage("MAIN_SC_SITE_GOT_ERRORS", ['#LINK#' => "/bitrix/admin/site_checker.php?lang=" . LANGUAGE_ID . "&start_test=Y"])
						),
					"TAG" => "SITE_CHECKER",
					"MODULE_ID" => "MAIN",
					'NOTIFY_TYPE' => CAdminNotify::TYPE_NORMAL,
				];
				CAdminNotify::Add($error);

				$success = false;
				break;
			}

			if ($oTest->percent >= 100)
			{
				break;
			}

			$step++;

			$ar = $oTest->arTestVars;
		}

		$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
		$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
		$_SERVER['REMOTE_ADDR'] = '-';
		$_SERVER['HTTP_USER_AGENT'] = '-';
		CEventLog::Add([
			"SEVERITY" => "WARNING",
			"AUDIT_TYPE_ID" => $success ? 'SITE_CHECKER_SUCCESS' : 'SITE_CHECKER_ERROR',
			"MODULE_ID" => "main",
			"ITEM_ID" => 'CSiteCheckerTest::CommonTest();',
			"URL" => '-',
			"DESCRIPTION" => '',
		]);
		$_SERVER['REMOTE_ADDR'] = $REMOTE_ADDR;
		$_SERVER['HTTP_USER_AGENT'] = $HTTP_USER_AGENT;

		return "CSiteCheckerTest::CommonTest();";
	}

	public static function PhpTestAgent()
	{
		IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/site_checker.php');

		if (version_compare($v = phpversion(), self::MIN_PHP_VER, '<'))
		{
			CAdminNotify::Add([
				'MESSAGE' => GetMessage('PHP_VER_NOTIFY', ['#CUR#' => $v, '#REQ#' => self::MIN_PHP_VER]),
				'TAG' => 'PHP_VERSION',
				'MODULE_ID' => 'MAIN',
				'NOTIFY_TYPE' => CAdminNotify::TYPE_ERROR,
			]);

			return "CSiteCheckerTest::PhpTestAgent();";
		}

		CAdminNotify::DeleteByTag('PHP_VERSION');

		return '';
	}
}

class CSearchFiles
{
	public $StartTime;
	public $arFail = [];
	public $FilesCount = 0;
	public $MaxFail = 9;
	public $TimeLimit = 0;
	public $SkipPath = '';
	public $BreakPoint = '';

	public function __construct()
	{
		$this->StartTime = time();
	}

	function Search($path)
	{
		if (time() - $this->StartTime > $this->TimeLimit)
		{
			$this->BreakPoint = $path;
			return empty($this->arFail);
		}

		if (count($this->arFail) > $this->MaxFail)
		{
			return false;
		}

		if ($this->SkipPath)
		{
			if (!str_starts_with($this->SkipPath, dirname($path)))
			{
				return null;
			}

			if ($this->SkipPath == $path)
			{
				$this->SkipPath = null;
			}
		}

		if (is_dir($path))
		{
			if (is_readable($path))
			{
				if (!is_writable($path))
				{
					$this->arFail[] = $path;
				}

				if ($dir = opendir($path))
				{
					while (false !== $item = readdir($dir))
					{
						if ($item == '.' || $item == '..')
						{
							continue;
						}

						$this->Search($path . '/' . $item);
						if ($this->BreakPoint)
						{
							break;
						}
					}
					closedir($dir);
				}
			}
			else
			{
				$this->arFail[] = $path;
			}
		}
		elseif (!$this->SkipPath)
		{
			$this->FilesCount++;
			if (!is_readable($path) || !is_writable($path))
			{
				$this->arFail[] = $path;
			}
		}
		return empty($this->arFail);
	}
}

////////////////////////////////////////////////////////////////////////
//////////   FUNCTIONS   ///////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
function CheckGetModuleInfo($path)
{
	include_once($path);

	$arr = explode("/", $path);
	$i = array_search("modules", $arr);
	$class_name = $arr[$i + 1];

	return CModule::CreateModuleObject($class_name);
}

function IsHttpResponseSuccess($res, $strRequest)
{
	$strRes = GetHttpResponse($res, $strRequest, $strHeaders);
	if (trim($strRes) == 'SUCCESS')
	{
		return true;
	}
	else
	{
		PrintHTTP($strRequest, $strHeaders, $strRes);
		return false;
	}
}

function GetHttpResponse($res, $strRequest, &$strHeaders)
{
	fputs($res, $strRequest);

	$strHeaders = "";
	$bChunked = false;
	$Content_Length = false;
	while (!feof($res) && ($line = fgets($res, 4096)) && $line != "\r\n")
	{
		$strHeaders .= $line;
		if (preg_match("/Transfer-Encoding: +chunked/i", $line))
		{
			$bChunked = true;
		}

		if (preg_match("/Content-Length: +([0-9]+)/i", $line, $regs))
		{
			$Content_Length = $regs[1];
		}
	}

	$strRes = "";
	if ($bChunked)
	{
		$maxReadSize = 4096;

		$line = fgets($res, $maxReadSize);
		$line = strtolower($line);

		$strChunkSize = "";
		$i = 0;
		while ($i < strlen($line) && in_array($line[$i], ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"]))
		{
			$strChunkSize .= $line[$i];
			$i++;
		}

		$chunkSize = hexdec($strChunkSize);

		while ($chunkSize > 0)
		{
			$processedSize = 0;
			$readSize = (($chunkSize > $maxReadSize) ? $maxReadSize : $chunkSize);

			while ($readSize > 0 && $line = fread($res, $readSize))
			{
				$strRes .= $line;
				$processedSize += strlen($line);
				$newSize = $chunkSize - $processedSize;
				$readSize = (($newSize > $maxReadSize) ? $maxReadSize : $newSize);
			}

			$line = fgets($res, $maxReadSize);
			$line = strtolower($line);

			$strChunkSize = "";
			$i = 0;
			while ($i < strlen($line) && in_array($line[$i], ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"]))
			{
				$strChunkSize .= $line[$i];
				$i++;
			}

			$chunkSize = hexdec($strChunkSize);
		}
	}
	elseif ($Content_Length !== false)
	{
		if ($Content_Length > 0)
		{
			$strRes = fread($res, $Content_Length);
		}
	}
	else
	{
		while ($line = fread($res, 4096))
		{
			$strRes .= $line;
		}
	}

	fclose($res);
	return $strRes;
}

function checker_get_unique_id()
{
	$LICENSE_KEY = '';
	@include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/license_key.php');
	if ($LICENSE_KEY == '')
	{
		$LICENSE_KEY = 'DEMO';
	}
	return md5($_SERVER['DOCUMENT_ROOT'] . filemtime($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/site_checker.php') . $LICENSE_KEY);
}

function getCharsetByCollation($collation)
{
	global $DB;
	static $CACHE;
	if (!$c = &$CACHE[$collation])
	{
		$res0 = $DB->Query('SHOW COLLATION LIKE "' . $collation . '"');
		$f0 = $res0->Fetch();
		$c = $f0['Charset'];
	}
	return $c;
}

function InitPureDB()
{
	require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/lib/loader.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/autoload.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/tools.php");

	global $DB, $DBDebug, $DBDebugToFile;

	/**
	 * Defined in dbconn.php
	 * @var $DBDebug
	 * @var $DBDebugToFile
	 */
	require_once $_SERVER["DOCUMENT_ROOT"] . getLocalPath('php_interface/dbconn.php', BX_PERSONAL_ROOT);

	require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/constants.php");

	// Database-dependent classes
	CAllDatabase::registerAutoload();

	$DB = new CDatabase;
	$DB->debug = $DBDebug;
	$DB->DebugToFile = $DBDebugToFile;

	if (!$DB->DoConnect())
	{
		CDatabase::showConnectionError();
		die();
	}
}

function TableFieldConstruct($field)
{
	global $DB;

	$tmp = $DB->quote($field['Field']) . ' ';

	if (preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)\\(\d+\\)(.*)/i", $field['Type'], $matches))
	{
		// As of MySQL 8.0.17, the ZEROFILL attribute is deprecated for numeric data types, as is the display width attribute for integer data types
		$tmp .= $matches[1] . $matches[2];
	}
	else
	{
		$tmp .= $field['Type'];
	}

	if ($field['Null'] == 'YES')
	{
		$tmp .= ' NULL';
	}
	else
	{
		$tmp .= ' NOT NULL';
	}

	if ($field['Default'] === null)
	{
		if ($field['Null'] == 'YES')
		{
			$tmp .= ' DEFAULT NULL ';
		}
	}
	else
	{
		$tmp .= ' DEFAULT ';
		if (($field['Type'] == 'timestamp' || $field['Type'] == 'datetime') && !preg_match('#^\d{4}#', $field['Default']))
		{
			$tmp .= $field['Default'];
		}
		elseif ($field['Type'] == 'text' && preg_match("/^'.*'$/", $field['Default']))
		{
			// MariaDB's bug with text fields default values in single quotes
			$tmp .= $field['Default'];
		}
		else
		{
			$tmp .= "'" . $DB->ForSQL($field['Default']) . "'";
		}
	}

	$tmp .= ' ' . str_ireplace('DEFAULT_GENERATED', '', $field['Extra']);

	return trim($tmp);
}

function fix_link($mode = 2)
{
	return ' <a href="javascript:show_popup(\'' . GetMessageJS('SC_FIX_DATABASE') . '\', \'?fix_mode=' . $mode . '\', \'' . GetMessageJS('SC_FIX_DATABASE_CONFIRM') . '\')">' . GetMessage('SC_FIX') . '</a>';
}

function PrintHTTP($strRequest, $strHeaders, $strRes)
{
	echo
		"== Request ==\n" .
		(($l = strlen($strRequest)) > 1000 ? substr($strRequest, 0, 1000) . ' ... (' . $l . ' bytes)' : $strRequest) . "\n" .
		"== Response ==\n" .
		$strHeaders . "\n" .
		"== Body ==\n" .
		(($l = strlen($strRes)) > 1000 ? substr($strRes, 0, 1000) . ' ... (' . $l . ' bytes)' : $strRes) . "\n" .
		"==========\n";
}
