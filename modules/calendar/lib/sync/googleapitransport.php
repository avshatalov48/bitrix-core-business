<?
namespace Bitrix\Calendar\Sync;

use Bitrix\Calendar\Sync\Util\RequestLogger;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\Web;
use CCalendar;
use CSocServGoogleOAuth;
use CSocServGoogleProxyOAuth;
use Exception;

/**
 * Class GoogleApiTransport
 *
 * @package Bitrix\Calendar\Sync
 */
final class GoogleApiTransport
{
	private const API_BASE_URL = Google\Helper::GOOGLE_SERVER_PATH_V3;
	protected const SERVICE_NAME = 'google';
	private $client;
	private $errors;
	private $currentMethod = '';
	/**
	 * @var CSocServGoogleOAuth
	 */
	private $oAuth;
	/**
	 * @var RequestLogger
	 */
	protected $requestLogger;

	/**
	 * GoogleApiTransport constructor.
	 *
	 * @param int $userId
	 *
	 * @throws SystemException
	 * @throws ArgumentNullException
	 * @throws LoaderException
	 */
	public function __construct($userId)
	{
		if (!Loader::includeModule('socialservices'))
		{
			throw new SystemException("Can not include module \"SocialServices\"! " . __METHOD__);
		}

		$this->client = new Web\HttpClient();
		if (RequestLogger::isEnabled())
		{
			$this->requestLogger = new RequestLogger((int)$userId, self::SERVICE_NAME);
		}

		if (CSocServGoogleProxyOAuth::isProxyAuth())
		{
			$oAuth = new CSocServGoogleProxyOAuth($userId);
		}
		else
		{
			$oAuth = new CSocServGoogleOAuth($userId);
		}

		$oAuth->getEntityOAuth()->addScope(
			[
				'https://www.googleapis.com/auth/calendar',
				'https://www.googleapis.com/auth/calendar.readonly',
			]
		);
		$oAuth->getEntityOAuth()->setUser($userId);
		if ($oAuth->getEntityOAuth()->GetAccessToken())
		{
			$this->client->setHeader('Authorization', 'Bearer ' . $oAuth->getEntityOAuth()->getToken());
			$this->client->setHeader('Content-Type', 'application/json');
			$this->client->setHeader('Referer', $this->getDomain());
			unset($oAuth);
		}
		else
		{
			$this->errors[] = array("code" => "NO_ACCESS_TOKEN", "message" => "No access token found");
		}
	}

	/**
	 * @param $channelInfo
	 * @return array
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws LoaderException
	 */
	public function openCalendarListChannel($channelInfo): array
	{
		$this->currentMethod = __METHOD__;

		return $this->doRequest(
			Web\HttpClient::HTTP_POST,
			self::API_BASE_URL. '/users/me/calendarList/watch',
			Web\Json::encode($channelInfo, JSON_UNESCAPED_SLASHES)
		);
	}

	/**
	 * @param $calendarId
	 * @param $channelInfo
	 * @return array
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws LoaderException
	 */
	public function openEventsWatchChannel($calendarId, $channelInfo): array
	{
		$this->currentMethod = __METHOD__;

		return $this->doRequest(
			Web\HttpClient::HTTP_POST,
			self::API_BASE_URL . '/calendars/' . urlencode($calendarId) . '/events/watch',
			Web\Json::encode($channelInfo, JSON_UNESCAPED_SLASHES)
		);
	}

	/**
	 * @param $channelId
	 * @param $resourceId
	 * @return array
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws LoaderException
	 */
	public function stopChannel($channelId, $resourceId): array
	{
		$this->currentMethod = __METHOD__;

		return $this->doRequest(
			Web\HttpClient::HTTP_POST,
			self::API_BASE_URL . '/channels/stop',
			Web\Json::encode(['id' => $channelId, 'resourceId' => $resourceId], JSON_UNESCAPED_SLASHES)
		);
	}

	/**
	 * @param $type
	 * @param $url
	 * @param $requestParams
	 * @return array|mixed
	 * @throws ArgumentException
	 * @throws LoaderException
	 */
	private function doRequest($type, $url, $requestParams = '')
	{
		$this->errors = $response = [];

		if (!in_array($type, [Web\HttpClient::HTTP_PATCH, Web\HttpClient::HTTP_PUT, Web\HttpClient::HTTP_DELETE, Web\HttpClient::HTTP_GET, Web\HttpClient::HTTP_POST], true))
		{
			throw new ArgumentException('Bad request type');
		}

		$this->client->query($type, $url, ($requestParams ?: null));

		//Only "OK" response is acceptable.
		if ($this->client->getStatus() === 200)
		{
			$contentType = $this->client->getHeaders()->getContentType();

			if ($contentType === 'multipart/mixed')
			{
				$response = $this->multipartDecode($this->client->getResult());
			}
			else
			{
				try
				{
					$response = Web\Json::decode($this->client->getResult());
				}
				catch (ArgumentException $exception)
				{
					$response = null;
				}
			}
		}
		else
		{
			try
			{
				$error = Web\Json::decode($this->client->getResult());
				$this->errors[] = ["code" => "CONNECTION", "message" => "[" . $error['error']['code'] . "] " . $error['error']['message']];
			}
			catch (ArgumentException $exception)
			{
				foreach($this->client->getError() as $code => $error)
				{
					$this->errors[] = ["code" => $code, "message" => $error];
				}
			}
		}

		if ($this->requestLogger)
		{
			$this->requestLogger->write([
				'requestParams' => $requestParams,
				'url' => $url,
				'method' => $type,
				'statusCode' => $this->client->getStatus(),
				'response' => $this->prepareResponseForDebug($response),
				'error' => $this->prepareErrorForDebug(),
			]);
		}

		return $response;
	}

	/**
	 * Deletes event from google calendar
	 *
	 * @param $eventId
	 * @param $calendarId
	 * @return array|mixed
	 */
	public function deleteEvent($eventId, $calendarId)
	{
		$this->currentMethod = __METHOD__;
		return $this->doRequest(Web\HttpClient::HTTP_DELETE, self::API_BASE_URL . '/calendars/' . $calendarId . '/events/' . $eventId);
	}

	/**
	 * Updates only specified event fields
	 *
	 * @param $patchData
	 * @param $calendarId
	 * @param $eventId
	 * @return array|mixed
	 */
	public function patchEvent($patchData, $calendarId, $eventId)
	{
		$this->currentMethod = __METHOD__;
		$requestBody = Web\Json::encode($patchData, JSON_UNESCAPED_SLASHES);
		return $this->doRequest(Web\HttpClient::HTTP_PUT, self::API_BASE_URL . '/calendars/' . $calendarId . '/events/' . $eventId, $requestBody);
	}

	/**
	 * Updates instance for recurring event
	 *
	 * @param $eventData
	 * @param $calendarId
	 * @param $instanceId
	 * @return array|mixed
	 */
	public function updateEvent($eventData, $calendarId, $eventId)
	{
		$this->currentMethod = __METHOD__;
		$requestBody = Web\Json::encode($eventData, JSON_UNESCAPED_SLASHES);
		return $this->doRequest(Web\HttpClient::HTTP_PUT, self::API_BASE_URL . '/calendars/' . $calendarId . '/events/' . $eventId, $requestBody);
	}

	/**
	 * Creates event at google calendar
	 *
	 * @param $eventData
	 * @param $calendarId
	 * @return array|mixed
	 */
	public function insertEvent($eventData, $calendarId)
	{
		$this->currentMethod = __METHOD__;
		$requestBody = Web\Json::encode($eventData, JSON_UNESCAPED_SLASHES);
		return $this->doRequest(Web\HttpClient::HTTP_POST, self::API_BASE_URL . '/calendars/' . $calendarId . '/events/', $requestBody);
	}

	/**
	 * Creates event at google calendar
	 *
	 * @param $eventData
	 * @param $calendarId
	 * @return array|mixed
	 */
	public function importEvent($eventData, $calendarId)
	{
		$this->currentMethod = __METHOD__;
		$requestBody = Web\Json::encode($eventData, JSON_UNESCAPED_SLASHES);
		return $this->doRequest(Web\HttpClient::HTTP_POST, self::API_BASE_URL . '/calendars/' . $calendarId . '/events/import', $requestBody);
	}

	/**
	 * @param $params
	 * @return array
	 * @throws ArgumentException
	 */
	public function getCalendarList(array $requestParameters = null): ?array
	{
		$this->currentMethod = __METHOD__;

		$url = self::API_BASE_URL . '/users/me/calendarList';
		$url .= empty($requestParameters) ? '' : '?' . preg_replace('/(%3D)/', '=', http_build_query($requestParameters));

		return $this->doRequest(Web\HttpClient::HTTP_GET, $url);
	}

	/**
	 * get google calendar color codes
	 *
	 * @return array|mixed
	 */
	public function getColors()
	{
		$this->currentMethod = __METHOD__;
		return $this->doRequest(Web\HttpClient::HTTP_GET, self::API_BASE_URL . '/colors');
	}

	/**
	 * Get Event List from calendar
	 *
	 * @param $calendarId
	 * @param string $syncToken
	 * @return array|mixed
	 */
	public function getEvents($calendarId, $requestParams = array())
	{
		$this->currentMethod = __METHOD__;
		$requestParams = array_filter($requestParams);
		$url = self::API_BASE_URL . '/calendars/' . urlencode($calendarId) . '/events';
		$url .= empty($requestParams) ? '' : '?' . preg_replace('/(%3D)/', '=', http_build_query($requestParams));
		return $this->doRequest(Web\HttpClient::HTTP_GET, $url);
	}

	/**
	 * Getting array of errors.
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Getting array of errors with the specified code.
	 *
	 * @param string $code Code of error.
	 * @return array
	 */
	public function getErrorsByCode($code)
	{
		return array_filter($this->errors, function($error) use ($code)
		{
			return $error['code'] == $code;
		});
	}

	/**
	 * Getting once error with the specified code.
	 *
	 * @param string $code Code of error.
	 * @return array
	 */
	public function getErrorByCode($code)
	{
		if (!is_array($this->errors))
		{
			return [];
		}

		$errorsByCode = array_filter($this->errors, function($error) use ($code)
		{
			return $error['code'] == $code;
		});

		if (!empty($errorsByCode))
		{
			return end($errorsByCode);
		}

		return [];
	}

	/**
	 * getting id of the recurrent event instance
	 *
	 * @param $calendarId
	 * @param $eventId
	 * @param $originalStart
	 * @return array|mixed
	 */
	public function getInstanceRecurringEvent($calendarId, $eventId, $originalStart)
	{
		$this->currentMethod = __METHOD__;

		$requestParameters = ['originalStart' => $originalStart];
		$requestParameters = array_filter($requestParameters);
		$url = self::API_BASE_URL . '/calendars/' . urlencode($calendarId) . '/events/' . urlencode($eventId) . '/instances/';
		$url .= empty($requestParameters) ? '' : '?' . preg_replace('/(%3D)/', '=', http_build_query($requestParameters));

		return $this->doRequest(Web\HttpClient::HTTP_GET, $url);
	}

	/**
	 * Creates local calendar at google calendar
	 *
	 * @param $eventData
	 * @return array|mixed
	 */
	public function insertCalendar($calendarData)
	{
		$this->currentMethod = __METHOD__;
		$requestBody = Web\Json::encode($calendarData, JSON_UNESCAPED_SLASHES);
		return $this->doRequest(Web\HttpClient::HTTP_POST, self::API_BASE_URL . '/calendars/', $requestBody);
	}

	public function sendBatchEvents($body, $calendarId, $params)
	{
		$url = "https://www.googleapis.com/batch/calendar/v3/";
		$requestBody = $this->prepareMultipartMixed($body, $calendarId, $params);
		return $this->doRequest(Web\HttpClient::HTTP_POST, $url, $requestBody);
	}

	/**
	 * Performs multipart/mixed encoding.
	 *
	 * @param array $postData Entity of POST request
	 * @return string
	 */
	protected function prepareMultipartMixed($postData, $calendarId, $params)
	{
		if (is_array($postData))
		{
			$boundary = 'BXC'.md5(rand().time());
			$this->client->setHeader('Content-type', 'multipart/mixed; boundary='.$boundary);

			$data = '';

			foreach ($postData as $key => $value)
			{
				$data .= '--'.$boundary."\r\n";

				if (is_array($value))
				{
					$contentId = '<item'.$key.':'.$key.'>';

					if (is_array($value))
					{
						$data .= 'Content-Type: application/http'."\r\n";
						$data .= 'Content-ID: '.$contentId."\r\n\r\n";

						if (!empty($value['gEventId']))
						{
							$data .= $params['method'].' /calendar/v3/calendars/'.$calendarId.'/events/'.$value['gEventId']."\r\n";
						}
						else
						{
							$data .= 'POST /calendar/v3/calendars/'.$calendarId.'/events'."\r\n";
						}

						$data .= 'Content-type: application/json'."\r\n";

						$data .= 'Content-Length: '.mb_strlen($value['partBody'])."\r\n\r\n";
						$data .= $value['partBody'];
						$data .= "\r\n\r\n";
					}
				}
			}

			$data .= '--'.$boundary."--\r\n";
			$postData = $data;
		}

		return $postData;
	}

	/**
	 * @param $response
	 * @return array
	 * @throws ArgumentException
	 */
	public function multipartDecode($response): array
	{
		$events = [];

		$boundary = $this->client->getHeaders()->getBoundary();

		$response = str_replace("--$boundary--", "--$boundary", $response);
		$parts = explode("--$boundary\r\n", $response);

		foreach ($parts as $key => $part)
		{
			$part = trim($part);
			if (!empty($part))
			{
				$partEvent = explode("\r\n\r\n", $part);
				$data = $this->getMetaInfo($partEvent[1]);

				if ($data['status'] === 200)
				{
					$id = $this->getId($partEvent[0]);
					if ($id === null)
					{
						continue;
					}

					try
					{
						$event = Web\Json::decode($partEvent[2]);
					}
					catch(Exception $exception)
					{
						continue;
					}

					$event['etag'] = $data['etag'];
					$events[$id] = $event;
				}
				else
				{
					AddMessage2Log('Event sync error. ID: ' . $this->getId($partEvent[0]));
				}
			}
		}

		return $events;
	}

	private function getMetaInfo($headers): array
	{
		$data = [];
		foreach (explode("\n", $headers) as $k => $header)
		{
			if($k === 0)
			{
				if(preg_match('#HTTP\S+ (\d+)#', $header, $find))
				{
					$data['status'] = (int)$find[1];
				}
			}
			elseif(mb_strpos($header, ':') !== false)
			{
				[$headerName, $headerValue] = explode(':', $header, 2);
				if(mb_strtolower($headerName) === 'etag')
				{
					$data['etag'] = trim($headerValue);
				}
			}
		}

		return $data;
	}

	/**
	 * @param $headers
	 * @return int|null
	 */
	private function getId ($headers): ?int
	{
		$id = null;
		foreach (explode("\n", $headers) as $k => $header)
		{
			if(mb_strpos($header, ':') !== false)
			{
				[$headerName, $headerValue] = explode(':', $header, 2);
				if(mb_strtolower($headerName) === 'content-id')
				{
					$part = explode(':', $headerValue);
					$id = rtrim($part[1], ">");
				}
			}
		}

		return (int)$id;
	}

	/**
	 * @param string $calendarId
	 * @return array
	 * @throws ArgumentException
	 */
	public function deleteCalendar(string $calendarId): array
	{
		$this->currentMethod = __METHOD__;
		return $this->doRequest(Web\HttpClient::HTTP_DELETE, self::API_BASE_URL . '/calendars/' . $calendarId . '');
	}

	/**
	 * @return string
	 */
	private function getDomain(): string
	{
		if (CCalendar::isBitrix24())
		{
			return 'https://bitrix24.com';
		}

		if (defined('BX24_HOST_NAME') && BX24_HOST_NAME)
		{
			return "https://" . (string)BX24_HOST_NAME;
		}

		$server = Application::getInstance()->getContext()->getServer();

		return "https://" . (string)$server['HTTP_HOST'];
	}

	/**
	 * @param string $calendarId
	 * @param $calendarData
	 * @return array
	 * @throws ArgumentException
	 */
	public function updateCalendar(string $calendarId, $calendarData): array
	{
		$this->currentMethod = __METHOD__;
		$requestBody = Web\Json::encode($calendarData, JSON_UNESCAPED_SLASHES);

		return $this->doRequest(Web\HttpClient::HTTP_PUT, self::API_BASE_URL . '/calendars/' . $calendarId, $requestBody);
	}

	/**
	 * @param string $calendarId
	 * @param $calendarData
	 * @return array
	 * @throws ArgumentException
	 */
	public function updateCalendarList(string $calendarId, $calendarData): array
	{
		$this->currentMethod = __METHOD__;

		$url = self::API_BASE_URL . '/users/me/calendarList/' . $calendarId;
		$url .= '?' . preg_replace('/(%3D)/', '=', http_build_query(['colorRgbFormat' => "True"]));

		$requestBody = Web\Json::encode($calendarData, JSON_UNESCAPED_SLASHES);

		return $this->doRequest(Web\HttpClient::HTTP_PUT, $url, $requestBody);
	}

	/**
	 * @param $response
	 * @return string
	 */
	private function prepareResponseForDebug($response): string
	{
		if (!$response || !is_array($response))
		{
			return '';
		}

		$result = '';

		foreach ($response as $key => $value)
		{
			if (is_string($value))
			{
				$result .= "{$key}:{$value}; ";
			}
			elseif (is_array($value))
			{
				$result .= "{$key}:";
				foreach ($value as $valueKey => $valueValue)
				{
					$result .= "{$valueKey}:{$valueValue}, ";
				}
				$result .= "; ";
			}
		}

		return $result;
	}

	/**
	 * @return string
	 */
	private function prepareErrorForDebug(): string
	{
		if (!$this->errors || !is_array($this->errors))
		{
			return '';
		}

		$result = '';
		foreach ($this->errors as $error)
		{
			$result .= $error['code'] . " " . $error['message'] . "; ";
		}

		return $result;
	}
}
