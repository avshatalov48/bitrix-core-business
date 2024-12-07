<?php

/*.require_module 'standard';.*/
/*.require_module 'bitrix_main';.*/
if (!defined('CACHED_b_clouds_file_bucket'))
{
	define('CACHED_b_clouds_file_bucket', 360000);
}
if (!defined('CACHED_clouds_file_resize'))
{
	define('CACHED_clouds_file_resize', 360000);
}
if (!defined('BX_S3_MIN_UPLOAD_PART_SIZE'))
{
	define('BX_S3_MIN_UPLOAD_PART_SIZE', 5242880); //5MB
}
//if (defined("BX24_IS_STAGE") && BX24_IS_STAGE === true) define("BX_CLOUDS_COUNTERS_DEBUG", "#^/([^/]+/)?(tmp/BXTEMP-|export/|BXTEMP-[0-9-]+/)#");

CModule::AddAutoloadClasses(
	'clouds',
	[
		'clouds' => 'install/index.php',
		'CCloudUtil' => 'classes/general/util.php',
		'CCloudStorage' => 'classes/general/storage.php',
		'CAllCloudStorageBucket' => 'classes/general/storage_bucket.php',
		'CCloudStorageBucket' => 'classes/general/storage_bucket.php',
		'CCloudStorageUpload' => 'classes/general/storage_upload.php',
		'CCloudTempFile' => 'classes/general/temp_file.php',
		'CCloudFailover' => 'classes/general/failover.php',
		'CCloudFileHash' => 'classes/general/filehash.php',
		'CCloudStorageService' => 'classes/general/storage_service.php',
		'CCloudStorageService_S3' => 'classes/general/storage_service_s3.php',
		'CCloudStorageService_AmazonS3' => 'classes/general/storage_service_amazon.php',
		'CCloudStorageService_Yandex' => 'classes/general/storage_service_yandex.php',
		'CCloudStorageService_HotBox' => 'classes/general/storage_service_hotbox.php',
		'CCloudStorageService_OpenStackStorage' => 'classes/general/storage_service_openstack.php',
		'CCloudStorageService_RackSpaceCloudFiles' => 'classes/general/storage_service_rackspace.php',
		'CCloudStorageService_ClodoRU' => 'classes/general/storage_service_clodo.php',
		'CCloudStorageService_Selectel' => 'classes/general/storage_service_selectel.php',
		'CCloudStorageService_Selectel_S3' => 'classes/general/storage_service_selectel_s3.php',
		'CCloudStorageService_GoogleStorage' => 'classes/general/storage_service_google.php',
		'CCloudSecurityService_AmazonS3' => 'classes/general/security_service_s3.php',
		'CCloudSecurityService_HotBox' => 'classes/general/security_service_hotbox.php',
		'CCloudSecurityService_STS' => 'classes/general/security_service_sts.php',
		'CCloudSecurityService_Amazon' => 'classes/general/security_service_amazon.php',
		'CCloudSecurityService_Yandex' => 'classes/general/security_service_yandex.php',
	]
);

class CCloudsDebug
{
	protected static $instances = [];

	public static function getInstance($action = 'counters')
	{
		if (!isset(static::$instances[$action]))
		{
			static::$instances[$action] = new static($action);
		}
		return static::$instances[$action];
	}

	protected $head = '';
	protected $id = '';

	public function __construct($action)
	{
		$this->head = BX24_HOST_NAME . '|' . $action;
		$this->id = 0;
		$now = time();
		$expired = $now - 600;
		while (!apcu_add($this->head . '|' . $this->id, $now))
		{
			$prev = apcu_fetch($this->head . '|' . $this->id);
			if ($prev > 0 && $prev < $expired)
			{
				$cloudsKey = $this->head . '|' . $this->id . '|mess';
				$prevTrace = apcu_fetch($cloudsKey);
				if ($prevTrace)
				{
					AddMessage2Log($prevTrace, 'clouds', 0);
				}
				apcu_delete($cloudsKey);
				apcu_delete($this->head . '|' . $this->id);
			}
			$this->id++;
		}
	}

	public function __destruct()
	{
		$cloudsKey = $this->head . '|' . $this->id . '|mess';
		$prevTrace = apcu_fetch($cloudsKey);
		if ($prevTrace)
		{
			AddMessage2Log($prevTrace, 'clouds', 0);
		}
		apcu_delete($cloudsKey);
		apcu_delete($this->head . '|' . $this->id);
	}

	public static function getBackTrace($skip = 0)
	{
		$functionStack = '';
		$fileStack = '';
		foreach (Bitrix\Main\Diag\Helper::getBackTrace(0, DEBUG_BACKTRACE_IGNORE_ARGS, $skip) as $backTraceFrame)
		{
			if ($functionStack)
			{
				$functionStack .= ' < ';
			}

			if (isset($backTraceFrame['class']))
			{
				$functionStack .= $backTraceFrame['class'] . '::';
			}

			$functionStack .= $backTraceFrame['function'];

			if (isset($backTraceFrame['file']))
			{
				$fileStack .= "\t" . $backTraceFrame['file'] . ':' . $backTraceFrame['line'] . "\n";
			}
		}
		return '    ' . $functionStack . "\n" . $fileStack;
	}

	public function startAction($filePath = '')
	{
		$newTrace = $this->head . ':v2:' . $filePath . "\n" . $_SERVER['REQUEST_URI'] . "\n" . static::getBackTrace(2);
		$cloudsKey = $this->head . '|' . $this->id . '|mess';
		if (!apcu_add($cloudsKey, $newTrace))
		{
			$prevTrace = apcu_fetch($cloudsKey);
			if ($prevTrace)
			{
				AddMessage2Log($prevTrace, 'clouds', 0);
			}
		}
		apcu_store($cloudsKey, $newTrace);
	}

	public function endAction()
	{
		$cloudsKey = $this->head . '|' . $this->id . '|mess';
		apcu_delete($cloudsKey);
	}
}
