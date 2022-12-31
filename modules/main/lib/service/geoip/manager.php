<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Service\GeoIp;

use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\EventResult;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web;
use Bitrix\Main\Diag;
use Psr\Log;

/**
 * Class for working with geolocation information.
 * @package Bitrix\Main\Service\GeoIp
 */
class Manager
{
	/** @var Base[] | null  */
	protected static $handlers = null;

	/** @var Data[][] */
	protected static $data = [];

	/** @var bool */
	protected static $logErrors = false;

    /** @var Log\LoggerInterface|null  */
    protected static $logger;

	/** @deprecated */
	const INFO_NOT_AVAILABLE = null;

	protected const CACHE_DIR = 'geoip_manager';

	/**
	 * Get the two letters country code.
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string
	 */
	public static function getCountryCode($ip = '', $lang = '')
	{
		$resultData = self::getDataResult($ip, $lang, array('countryCode'));
		return $resultData !== null ? $resultData->getGeoData()->countryCode : '';
	}

	/**
	 * Get the full country name.
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string
	 */
	public static function getCountryName($ip = '', $lang = '')
	{
		$resultData = self::getDataResult($ip, $lang, array('countryName'));
		return $resultData !== null ? $resultData->getGeoData()->countryName : '';
	}

	/**
	 * Get the full city name.
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string|null
	 */
	public static function getCityName($ip = '', $lang = '')
	{
		$resultData = self::getDataResult($ip, $lang, array('cityName'));
		return $resultData !== null ? $resultData->getGeoData()->cityName : '';
	}

	/**
	 * Get the Postal Code, FSA or Zip Code.
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string|null
	 */
	public static function getCityPostCode($ip = '', $lang = '')
	{
		$resultData = self::getDataResult($ip, $lang, array('zipCode'));
		return $resultData !== null ? $resultData->getGeoData()->zipCode : '';
	}

	/**
	 * Get geo-position attribute.
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return array|null
	 */
	public static function getGeoPosition($ip = '', $lang = '')
	{
		$data = self::getDataResult($ip, $lang, array('latitude', 'longitude'));

		if (
			$data !== null
			&& $data->getGeoData()->latitude != null
			&&  $data->getGeoData()->longitude != null
		)
		{
			$result = Array(
				'latitude' => $data->getGeoData()->latitude,
				'longitude' => $data->getGeoData()->longitude,
			);
		}
		else
		{
			$result = null;
		}
		
		return $result;
	}

	/**
	 * Get the Latitude as signed double.
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string
	 */
	public static function getGeoPositionLatitude($ip = '', $lang = '')
	{
		$resultData = self::getDataResult($ip, $lang, array('latitude'));
		return $resultData !== null ? $resultData->getGeoData()->latitude : '';
	}
	
	/**
	 * Get the Longitude as signed double.
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string
	 */
	public static function getGeoPositionLongitude($ip = '', $lang = '')
	{
		$resultData = self::getDataResult($ip, $lang, array('longitude'));
		return $resultData !== null ? $resultData->getGeoData()->longitude : '';
	}
	
	/**
	 * Get the organization name.
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string
	 */
	public static function getOrganizationName($ip = '', $lang = '')
	{
		$resultData = self::getDataResult($ip, $lang, array('organizationName'));
		return $resultData !== null ? $resultData->getGeoData()->organizationName : '';
	}
	
	/**
	 * Get the Internet Service Provider (ISP) name.
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string
	 */
	public static function getIspName($ip = '', $lang = '')
	{
		$resultData = self::getDataResult($ip, $lang, array('ispName'));
		return $resultData !== null ? $resultData->getGeoData()->ispName : '';
	}

	/**
	 * Get the time zone for country and region code combo.
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string|null
	 */
	public static function getTimezoneName($ip = '', $lang = '')
	{
		$resultData = self::getDataResult($ip, $lang, array('timezone'));
		return $resultData !== null ? $resultData->getGeoData()->timezone : '';
	}
	
	/**
	 * Get the all available information about geolocation.
	 *
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @param array $required Required fields for result data.
	 * @return Result | null
	 */
	public static function getDataResult($ip = '', $lang = '', array $required = [])
	{
		$result = null;

		if ($ip == '')
		{
			$ip = self::getRealIp();
		}

		// cache on the hit
		if (isset(self::$data[$ip][$lang]))
		{
			$data = self::$data[$ip][$lang];
			if (empty($required) || self::hasDataAllRequiredFields($required, $data))
			{
				$result = ((new Result())->setGeoData($data));
			}
		}

		if (!$result)
		{
			if(self::$handlers === null)
			{
				self::initHandlers();
			}

			foreach (self::$handlers as $class => $handler)
			{
				if (!$handler->isInstalled() || !$handler->isActive())
				{
					continue;
				}

				if ($lang != '' && !in_array($lang, $handler->getSupportedLanguages()))
				{
					continue;
				}

				if (!empty($required) && !self::hasDataAllRequiredFields($required, $handler->getProvidingData()))
				{
					continue;
				}

				$ipAddress = new Web\IpAddress($ip);

				// get from cache
				$records = self::getFromStore($ipAddress, $class);
				$data = static::findForIp($ipAddress, $records, $lang);

				if ($data)
				{
					if (empty($required) || self::hasDataAllRequiredFields($required, $data))
					{
						$data->ip = $ip;
						self::$data[$ip][$lang] = $data;

						$result = ((new Result())->setGeoData($data));
						break;
					}
				}

				$dataResult = $handler->getDataResult($ip, $lang);

				if (!$dataResult)
				{
					continue;
				}

				if (!$dataResult->isSuccess())
				{
					if (self::$logErrors && ($logger = static::getLogger()))
					{
						$logger->error(
							"{date} - {host}\nIP: {ip}, handler: {handler}, lang: {lang}\n{errors}\n{trace}{delimiter}\n",
							[
								'ip' => $ip,
								'lang' => $lang,
								'handler' => $handler->getId(),
								'errors' => $dataResult->getErrorMessages(),
								'trace' => Diag\Helper::getBackTrace(6, DEBUG_BACKTRACE_IGNORE_ARGS, 3),
							]
						);
					}

					continue;
				}

				$data = $dataResult->getGeoData();
				$data->handlerClass = $class;
				$data->ip = $ip;

				// write to cache
				self::$data[$ip][$lang] = $data;
				self::saveToStore($ipAddress, $records, $data, $lang);

				// save geonames
				if (Option::get('main', 'collect_geonames', 'N') == 'Y')
				{
					if (!empty($data->geonames))
					{
						Internal\GeonameTable::save($data->geonames);
					}
				}

				$result = $dataResult;
				break;
			}
		}

		if ($result)
		{
			$event = new Event('main', 'onGeoIpGetResult', [
				'originalData' => clone $result->getGeoData(),
				'data' => $result->getGeoData(),
			]);
			$event->send();
		}

		return $result;
	}

	protected static function getCacheId(Web\IpAddress $ipAddress, string $handler): string
	{
		return $ipAddress->toRange(24) . ':v1:' . $handler;
	}

	protected static function getFromStore(Web\IpAddress $ipAddress, string $handler): array
	{
		$cacheTtl = static::getCacheTtl();

		if ($cacheTtl > 0)
		{
			$cache = Application::getInstance()->getManagedCache();
			$cacheId = static::getCacheId($ipAddress, $handler);

			if ($cache->read($cacheTtl, $cacheId, self::CACHE_DIR))
			{
				$records = $cache->get($cacheId);

				if (is_array($records))
				{
					return $records;
				}
			}
		}

		return [];
	}

	protected static function findForIp(Web\IpAddress $ipAddress, array $records, string $lang): ?Data
	{
		foreach ($records as $range => $data)
		{
			if (isset($data[$lang]))
			{
				// sorted by the most specific first
				if ($ipAddress->matchRange($range))
				{
					$result = new Data();

					foreach ($data[$lang] as $attr => $value)
					{
						if (property_exists($result, $attr))
						{
							$result->$attr = $value;
						}
					}
					return $result;
				}
			}
		}
		return null;
	}

	protected static function saveToStore(Web\IpAddress $ipAddress, array $records, Data $geoData, string $lang): void
	{
		$cacheTtl = static::getCacheTtl();

		if ($cacheTtl > 0)
		{
			$storedData = [];
			foreach (get_object_vars($geoData) as $attr => $value)
			{
				if ($value !== null)
				{
					$storedData[$attr] = $value;
				}
			}

			$network = $geoData->ipNetwork ?? $ipAddress->toRange(32);
			$records[$network][$lang] = $storedData;

			// the most specific first
			krsort($records);

			$cache = Application::getInstance()->getManagedCache();
			$cacheId = static::getCacheId($ipAddress, $geoData->handlerClass);

			$cache->clean($cacheId, self::CACHE_DIR);
			$cache->read($cacheTtl, $cacheId, self::CACHE_DIR);
			$cache->set($cacheId, $records);
		}
	}

	/**
	 * @param array $required
	 * @param Data $geoData
	 * @return bool
	 */
	private static function hasDataAllRequiredFields(array $required, $geoData)
	{
		if(empty($required))
		{
			return true;
		}

		$vars = get_object_vars($geoData);

		foreach($required as $field)
		{
			if($vars[$field] === null)
			{
				return false;
			}
		}

		return true;
	}

	private static function initHandlers()
	{
		if(self::$handlers !== null)
			return;

		self::$handlers = array();
		$handlersList = array();
		$buildInHandlers = array(
			'\Bitrix\Main\Service\GeoIp\GeoIP2' => 'lib/service/geoip/geoip2.php',
			'\Bitrix\Main\Service\GeoIp\MaxMind' => 'lib/service/geoip/maxmind.php',
			'\Bitrix\Main\Service\GeoIp\Extension' => 'lib/service/geoip/extension.php',
			'\Bitrix\Main\Service\GeoIp\SypexGeo' => 'lib/service/geoip/sypexgeo.php'
		);

		Loader::registerAutoLoadClasses('main', $buildInHandlers);

		$handlersFields = array();
		$res = HandlerTable::getList(['cache' => ['ttl' => static::getCacheTtl()]]);

		while($row = $res->fetch())
			$handlersFields[$row['CLASS_NAME']] = $row;

		foreach($buildInHandlers as $class => $file)
		{
			if(self::isHandlerClassValid($class))
			{
				$fields = $handlersFields[$class] ?? [];
				$handlersList[$class] = new $class($fields);
				$handlersSort[$class] = $handlersList[$class]->getSort();
			}
		}

		$event = new Event('main', 'onMainGeoIpHandlersBuildList');
		$event->send();
		$resultList = $event->getResults();

		if (is_array($resultList) && !empty($resultList))
		{
			$customClasses = array();

			foreach ($resultList as $eventResult)
			{
				if ($eventResult->getType() != EventResult::SUCCESS)
					continue;

				$params = $eventResult->getParameters();

				if(!empty($params) && is_array($params))
					$customClasses = array_merge($customClasses, $params);
			}

			if(!empty($customClasses))
			{
				Loader::registerAutoLoadClasses(null, $customClasses);

				foreach($customClasses as $class => $file)
				{
					if(!File::isFileExists(Application::getDocumentRoot().'/'.$file))
					{
						continue;
					}

					if(self::isHandlerClassValid($class))
					{
						$fields = $handlersFields[$class] ?? [];
						$handlersList[$class] = new $class($fields);
						$handlersSort[$class] = $handlersList[$class]->getSort();
					}
				}
			}
		}

		asort($handlersSort, SORT_NUMERIC);

		foreach($handlersSort as $class => $sort)
			self::$handlers[$class] = $handlersList[$class];
	}

	/**
	 * @param string $className
	 * @return bool
	 */
	private static function isHandlerClassValid($className)
	{
		if(!class_exists($className))
			return false;

		if(!is_subclass_of($className, '\Bitrix\Main\Service\GeoIp\Base'))
			return false;

		return true;
	}

	/**
	 * @return string | false Ip address.
	 */
	public static function getRealIp()
	{
		$ip = false;
		$xForwarded = Application::getInstance()->getContext()->getServer()->get('HTTP_X_FORWARDED_FOR');

		if (!empty($xForwarded))
		{
			$ips = explode (", ", $xForwarded);
			$fCount = count($ips);

			for ($i = 0; $i < $fCount; $i++)
			{
				if (!preg_match("/^(10|172\\.16|192\\.168)\\./", $ips[$i]))
				{
					$ip = $ips[$i];
					break;
				}
			}
		}

		if(!$ip)
		{
			$ip = trim(Application::getInstance()->getContext()->getRequest()->getRemoteAddress());
		}

		return $ip;
	}

	/**
	 * @return Base[] Handlers list.
	 */

	public static function getHandlers()
	{
		if(self::$handlers === null)
			self::initHandlers();

		return self::$handlers;
	}

	/**
	 * @param string $className. Class name of handler.
	 * @return Base | null Handler.
	 */
	public static function getHandlerByClassName($className)
	{
		if(self::$handlers === null)
			self::initHandlers();

		return self::$handlers[$className] ?? null;
	}

	/**
	 * Turn on / off error logging for debugging purposes.
	 * @param bool $isLog
	 */
	public static function setLogErrors($isLog)
	{
		self::$logErrors = $isLog;
	}

	/**
	 * @param Base $handler
	 * @return string Config HTML for admin interface form.
	 */
	public static function getHandlerAdminConfigHtml(Base $handler)
	{
		$result = '';
		$adminFields = $handler->getConfigForAdmin();

		foreach ($adminFields as $field)
		{
			if ($field['TYPE'] == 'COLSPAN2')
			{
				$heading = isset($field['HEADING']) && $field['HEADING'] ? ' class="heading"' : '';
				$result .= '<tr'.$heading.'><td colspan="2">'.$field['TITLE'];
			}
			elseif ($field['TYPE'] == 'TEXT' || $field['TYPE'] == 'CHECKBOX' || $field['TYPE'] == 'LIST')
			{
				$required = isset($field['REQUIRED']) && $field['REQUIRED'] ? ' class="adm-detail-required-field"' : '';
				$disabled = isset($field['DISABLED']) && $field['DISABLED'] ? ' disabled' : '';
				$value = isset($field['VALUE']) ? ' value="'.$field['VALUE'].'"' : '';
				$name = isset($field['NAME']) ? ' name="'.$field['NAME'].'"' : '';
				$title = isset($field['TITLE']) ? ' title="'.$field['TITLE'].'"' : '';

				$result .= '<tr'.$required.'><td width="40%">'.$field['TITLE'].':</td><td width="60%">';

				if ($field['TYPE'] == 'TEXT')
				{
					$result .= '<input type="text" size="45" maxlength="255"'.$name.$value.$disabled.$title.'>';
				}
				elseif ($field['TYPE'] == 'CHECKBOX')
				{
					$checked = isset($field['CHECKED']) && $field['CHECKED'] ? ' checked' : '';
					$result .= '<input type="checkbox"'.$name.$value.$checked.$disabled.$title.'>';
				}
				else
				{
					$result .= '<select' . $name . $disabled . $title . '>';
					if (is_array($field['OPTIONS']))
					{
						foreach ($field['OPTIONS'] as $key => $val)
						{
							$result .= '<option value="' . $key . '"' . ($key == $field['VALUE'] ? ' selected' : '') . '>' . $val . '</option>';
						}
					}
					$result .= '</select>';
				}
			}

			$result .= '</td></tr>';
		}

		return $result;
	}

	protected static function getCacheTtl(): int
	{
		static $cacheTtl = null;

		if($cacheTtl === null)
		{
			$cacheFlags = Configuration::getValue('cache_flags');
			$cacheTtl = $cacheFlags['geoip_manager'] ?? 604800; // a week
		}
		return $cacheTtl;
	}

	public static function cleanCache(): void
	{
		$cache = Application::getInstance()->getManagedCache();
		$cache->cleanDir(static::CACHE_DIR);
	}

	protected static function getLogger()
	{
		if (static::$logger === null)
		{
			$logger = Diag\Logger::create('main.GeoIpManager');

			if ($logger !== null)
			{
				static::$logger = $logger;
			}
		}

		return static::$logger;
	}
}
