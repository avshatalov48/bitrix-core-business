<?php

use Bitrix\Main\Diag;

class CSecurityEvent
{
	private static $instance = null;

	private $isDBEngineActive = false;
	/** @var \Psr\Log\LoggerInterface */
	private $fileLogger;
	/** @var \Psr\Log\LoggerInterface */
	private $sysLogger;
	private $syslogPriority = LOG_WARNING;

	/** @var CSecurityEventMessageFormatter $messageFormatter */
	private $messageFormatter = null;

	private static $syslogFacilities = array(
		LOG_SYSLOG   => "LOG_SYSLOG",
		LOG_AUTH     => "LOG_AUTH",
		LOG_AUTHPRIV => "LOG_AUTHPRIV",
		LOG_DAEMON   => "LOG_DAEMON",
		LOG_USER     => "LOG_USER"
	);

	private static $syslogPriorities = array(
		LOG_EMERG   => "LOG_EMERG",
		LOG_ALERT   => "LOG_ALERT",
		LOG_CRIT    => "LOG_CRIT",
		LOG_ERR     => "LOG_ERR",
		LOG_WARNING => "LOG_WARNING",
		LOG_NOTICE  => "LOG_NOTICE",
		LOG_INFO    => "LOG_INFO",
		LOG_DEBUG   => "LOG_DEBUG"
	);

	/**
	 * @return CSecurityEvent
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * @param string $severity
	 * @param string $auditType
	 * @param string $itemName
	 * @param string $itemDescription
	 * @return bool
	 */
	public function doLog($severity, $auditType, $itemName, $itemDescription)
	{
		$result = false;

		if ($this->isDBEngineActive)
		{
			$result = CEventLog::log($severity, $auditType, "security", $itemName, $itemDescription);
		}

		if ($this->sysLogger || $this->fileLogger)
		{
			$message = $this->messageFormatter->format($auditType, $itemName, $itemDescription);

			if ($this->sysLogger)
			{
				$level = Diag\SysLogger::priorityToLevel($this->syslogPriority);
				$this->sysLogger->log($level, $message);
			}

			if ($this->fileLogger)
			{
				$message = static::sanitizeMessage($message);
				$message .= "\n";

				$this->fileLogger->warning($message);
			}
			$result = true;
		}
		return $result;
	}

	/**
	 * @return array
	 */
	public static function getSyslogPriorities()
	{
		return static::$syslogPriorities;
	}

	/**
	 * @return array
	 */
	public static function getSyslogFacilities()
	{
		if (static::isRunOnWin())
			return array(LOG_USER => "LOG_USER");
		else
			return static::$syslogFacilities;
	}

	/**
	 * Return WAF events count for Admin's informer popup and Admin's gadget
	 * @param string $timestamp  - from date
	 * @return integer
	 */
	public function getEventsCount($timestamp = '')
	{
		if (!$this->isDBEngineActive)
			return 0;

		/**
		 * @global CCacheManager $CACHE_MANAGER
		 * @global CDataBase $DB
		 */
		global $DB, $CACHE_MANAGER;
		$ttl = 3600;
		$cacheId = 'sec_events_count';
		$cacheDir = '/security/events';
		
		if ($CACHE_MANAGER->read($ttl, $cacheId, $cacheDir))
		{
			$result = $CACHE_MANAGER->get($cacheId);
		}
		else
		{
			if ($timestamp == '')
			{
				$days = COption::getOptionInt("main", "event_log_cleanup_days", 7);
				if ($days > 7)
					$days = 7;
				$timestamp = convertTimeStamp(time()-$days*24*3600+CTimeZone::getOffset());
			}

			$arAudits = array(
				"SECURITY_FILTER_SQL",
				"SECURITY_FILTER_XSS",
				"SECURITY_FILTER_XSS2",
				"SECURITY_FILTER_PHP"
			);

			$strAuditsSql = implode("', '",$arAudits);

			$strSql = "
				SELECT COUNT(ID) AS COUNT
				FROM
					b_event_log
				WHERE
					AUDIT_TYPE_ID in ('".$strAuditsSql."')
				AND
					(MODULE_ID = 'security' and MODULE_ID is not null)
				AND
					TIMESTAMP_X >= ".$DB->charToDateFunction($DB->forSQL($timestamp))."
			";

			$res = $DB->query($strSql);

			if ($arRes = $res->fetch())
				$result = $arRes["COUNT"];
			else
				$result = 0;

			$CACHE_MANAGER->set($cacheId, $result);
		}

		return $result;
	}

	public function getMessageFormatter()
	{
		return $this->messageFormatter;
	}

	private function __construct()
	{
		if (COption::getOptionString("security", "security_event_db_active") === "Y")
			$this->initializeDBEngine();

		if (COption::getOptionString("security", "security_event_syslog_active") == "Y")
			$this->initializeSyslogEngine();

		if (COption::getOptionString("security", "security_event_file_active") == "Y")
			$this->initializeFileEngine();

		$this->messageFormatter = new CSecurityEventMessageFormatter(
			COption::getOptionString("security", "security_event_format"),
			COption::getOptionString("security", "security_event_userinfo_format")
		);
	}

	private function initializeFileEngine()
	{
		$filePath = COption::getOptionString("security", "security_event_file_path");
		if ($filePath && checkDirPath($filePath))
		{
			$this->fileLogger = new Diag\FileLogger($filePath, 0);
		}
	}

	private function initializeDBEngine()
	{
		$this->isDBEngineActive = true;
	}

	private function initializeSyslogEngine()
	{
		if (self::isRunOnWin())
		{
			$facility = LOG_USER;
		}
		else
		{
			$facility = (int) COption::getOptionString("security", "security_event_syslog_facility");
		}

		$this->syslogPriority = COption::getOptionString("security", "security_event_syslog_priority");

		$this->sysLogger = new Diag\SysLogger('Bitrix WAF', LOG_ODELAY, $facility);
	}

	/**
	 * @return bool
	 */
	private static function isRunOnWin()
	{
		return (strtoupper(substr(PHP_OS, 0, 3)) === "WIN");
	}

	/**
	 * @param string $message
	 * @return string mixed
	 */
	private static function sanitizeMessage($message)
	{
		return str_replace(array("\r", "\n"), array("\\r", "\\n"), $message);
	}
}
