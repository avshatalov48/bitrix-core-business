<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Main\Service\GeoIp;

use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\EventResult;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Json;

/**
 * Class for working with geolocation information.
 * @package Bitrix\Main\Service\GeoIp
 */
final class Manager
{
	/** @var array | null  */
	private static $handlers = null;

	/** @var Result */
	private static $data = array();

	/** @var bool */
	private static $logErrors = false;

	/** @var bool */
	private static $useCookie = false;

	/**
	 * Constant for parameters who information not available.
	 */
	const INFO_NOT_AVAILABLE = null;

	const COOKIE_NAME = 'BX_GEO_IP';
	const COOKIE_EXPIRED = 86400; //day

	/**
	 * Get the two letter country code.
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
			&& $data->getGeoData()->latitude != self::INFO_NOT_AVAILABLE
			&&  $data->getGeoData()->longitude != self::INFO_NOT_AVAILABLE
		)
		{
			$result = Array(
				'latitude' => $data->getGeoData()->latitude,
				'longitude' => $data->getGeoData()->longitude,
			);
		}
		else
		{
			$result = self::INFO_NOT_AVAILABLE;
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
	 * @param string  $ip Ip address.
	 * @param string  $lang Language identifier.
	 * @param array $required Required fields for result data.
	 * @return Result | null
	 */
	public static function getDataResult($ip = '', $lang = '', array $required = array())
	{
		$result = null;

		if(strlen($ip) <= 0)
			$ip = self::getRealIp();

		if(self::$useCookie && !isset(self::$data[$ip]))
			self::$data[$ip] = self::getCookie($ip);

		if(isset(self::$data[$ip]) && is_array(self::$data[$ip]))
		{
			/** @var Result $dataResult */
			foreach(self::$data[$ip] as $data)
			{
				if(is_object($data) && ($data instanceof Data))
				{
					if(empty($required) || self::hasDataAllRequiredFields($required, $data))
					{
						if(strlen($lang) <= 0 || $data->lang == $lang)
						{
							$result = new Result();
							$result->setGeoData($data);
							break;
						}
					}
				}
			}
		}
		else
		{
			self::$data[$ip] = array();
		}

		if(!$result)
		{
			if(self::$handlers === null)
				self::initHandlers();

			/** @var Base $handler */
			foreach(self::$handlers as $handler)
			{
				if(!$handler->isInstalled())
					continue;

				if(!$handler->isActive())
					continue;

				if(strlen($lang) > 0)
					if(!in_array($lang, $handler->getSupportedLanguages()))
						continue;

				if(!empty($required) && !self::hasDataAllRequiredFields($required, $handler->getProvidingData()))
					continue;

				$dataResult = $handler->getDataResult($ip, $lang);

				if(!$dataResult)
					continue;

				if(!$dataResult->isSuccess())
				{
					if(self::$logErrors)
					{
						$eventLog = new \CEventLog;

						$eventLog->Add(array(
							"SEVERITY" => \CEventLog::SEVERITY_ERROR,
							"AUDIT_TYPE_ID" => 'MAIN_SERVICES_GEOIP_GETDATA_ERROR',
							"MODULE_ID" => "main",
							"ITEM_ID" => $ip.'('.$lang.')',
							"DESCRIPTION" => 'Handler id: '.$handler->getId()."\n<br>".implode("\n<br>",$dataResult->getErrorMessages()),
						));
					}

					continue;
				}

				$geoData = $dataResult->getGeoData();
				$geoData->handlerClass = get_class($handler);
				$result = $dataResult;
				self::$data[$ip][$geoData->handlerClass] = $result->getGeoData();

				if(self::$useCookie)
					self::setCookie($ip, self::$data[$ip]);

				break;
			}
		}

		return $result;
	}

	/**
	 * @param $ip
	 * @return bool| Result[]
	 */
	private static function getCookie($ip)
	{
		$name = self::getCookieName($ip);
		$cookieData = Application::getInstance()->getContext()->getRequest()->getCookieRaw($name);

		if(!$cookieData)
			return false;

		if(function_exists('gzuncompress'))
			$cookieData = @\gzuncompress($cookieData);

		if(!$cookieData)
			return false;

		try
		{
			$cookieData = Json::decode($cookieData);
		}
		catch(\Exception $e)
		{
			$cookieData = false;
		}

		if(!is_array($cookieData))
			return false;

		$result = array();

		foreach($cookieData as $class => $data)
		{
			$tmpData = new Data();

			foreach($data as $attr => $value)
				if(property_exists($tmpData, $attr))
					$tmpData->$attr = $value;

			$result[$class] = $tmpData;
		}

		return !empty($result) ? $result : false;
	}

	/**
	 * @param string $ip
	 * @param Data[] $geoData
	 * @return bool
	 */
	private static function setCookie($ip, $geoData)
	{
		$cookieData = array();

		foreach($geoData as $class => $data)
		{
			$cookieData[$class] = array();
			$values = get_object_vars($data);

			foreach($values as $attr => $value)
				if($value !== self::INFO_NOT_AVAILABLE)
					$cookieData[$class][$attr] = $value;
		}

		$cookieData = Json::encode($cookieData);

		if(function_exists('gzcompress'))
			$cookieData = \gzcompress($cookieData, 9);

		return setcookie(
			self::getCookieName($ip),
			$cookieData,
			time()+self::COOKIE_EXPIRED,
			'/'
		);
	}

	/**
	 * @param string $ip
	 * @return string
	 */
	private static function getCookieName($ip)
	{
		return self::COOKIE_NAME.'_'.str_replace('.', '_',$ip);
	}

	/**
	 * @param array $required
	 * @param Data|ProvidingData $geoData
	 * @return bool
	 */
	private static function hasDataAllRequiredFields(array $required, $geoData)
	{
		if(empty($required))
			return true;

		$vars = get_object_vars($geoData);

		foreach($required as $field)
			if($vars[$field] === self::INFO_NOT_AVAILABLE)
				return false;

		return true;
	}

	private static function initHandlers()
	{
		if(self::$handlers !== null)
			return;

		self::$handlers = array();
		$handlersList = array();
		$buildInHandlers = array(
			'\Bitrix\Main\Service\GeoIp\MaxMind' => 'lib/service/geoip/maxmind.php',
			'\Bitrix\Main\Service\GeoIp\Extension' => 'lib/service/geoip/extension.php',
			'\Bitrix\Main\Service\GeoIp\SypexGeo' => 'lib/service/geoip/sypexgeo.php'
		);

		Loader::registerAutoLoadClasses('main', $buildInHandlers);

		$handlersFields = array();
		$res = HandlerTable::getList();

		while($row = $res->fetch())
			$handlersFields[$row['CLASS_NAME']] = $row;

		foreach($buildInHandlers as $class => $file)
		{
			if(self::isHandlerClassValid($class))
			{
				$fields = isset($handlersFields[$class]) ? $handlersFields[$class] : array();
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
				/** @var  EventResult $eventResult*/
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
					if(self::isHandlerClassValid($class))
					{
						$fields = isset($handlersFields[$class]) ? $handlersFields[$class] : array();
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

		return isset(self::$handlers[$className]) ? self::$handlers[$className] : null;
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
	 * Turn on / off storing geolocation info in cookie for performance purposes.
	 * @param bool $isUse
	 */
	public static function useCookieToStoreInfo($isUse)
	{
		self::$useCookie = $isUse;
	}

	/**
	 * @param Base $handler
	 * @return string Config HTML for admin interface form.
	 */
	public static function getHandlerAdminConfigHtml(Base $handler)
	{
		$result = '';
		$adminFields = $handler->getConfigForAdmin();

		foreach($adminFields as $field)
		{
			if($field['TYPE'] == 'COLSPAN2')
			{
				$heading = isset($field['HEADING']) && $field['HEADING'] == true ? ' class="heading"' : '';
				$result .= '<tr'.$heading.'><td colspan="2">'.$field['TITLE'];
			}
			elseif($field['TYPE'] == 'TEXT' || $field['TYPE'] == 'CHECKBOX')
			{
				$required = isset($field['REQUIRED']) && $field['REQUIRED'] == true ? ' class="adm-detail-required-field"' : '';
				$disabled = isset($field['DISABLED']) && $field['DISABLED'] == true ? ' disabled' : '';
				$value = isset($field['VALUE']) ? ' value="'.$field['VALUE'].'"' : '';
				$name = isset($field['NAME']) ? ' name="'.$field['NAME'].'"' : '';
				$title = isset($field['TITLE']) ? ' title="'.$field['TITLE'].'"' : '';

				$result .= '<tr'.$required.'><td width="40%">'.$field['TITLE'].':</td><td width="60%">';

				if($field['TYPE'] == 'TEXT')
				{
					$result .= '<input type="text" size="45" maxlength="255"'.$name.$value.$disabled.$title.'>';
				}
				elseif($field['TYPE'] == 'CHECKBOX')
				{
					$checked = isset($field['CHECKED']) && $field['CHECKED'] == true ? ' checked' : '';
					$result .= '<input type="checkbox"'.$name.$value.$checked.$disabled.$title.'>';
				}
			}

			$result .= '</td></tr>';
		}

		return $result;
	}

	/**
	 * @param string $ip
	 * @param string $lang
	 * @param array $required
	 * @return DataResult|null
	 * @deprecated
	 */
	public static function getData($ip = '', $lang = '', array $required = array())
	{
		$dataResult = self::getDataResult($ip, $lang, $required);

		if(!$dataResult)
			return null;

		$result = new DataResult();

		foreach($dataResult as $attr => $value)
			if(property_exists($result, $attr))
				$result->$attr = $value;

		return $result;
	}
}