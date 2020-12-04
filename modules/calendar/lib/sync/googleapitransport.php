<?
namespace Bitrix\Calendar\Sync;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\BinaryString;
use Bitrix\Main\Web;

/**
 * Class GoogleApiTransport
 *
 * @package Bitrix\Calendar\Sync
 */
final class GoogleApiTransport
{
	const API_BASE_URL = "https://www.googleapis.com/calendar/v3/";
	private $client;
	private $errors;
	private $currentMethod = '';


	/**
	 * BEGIN PUSH SECTION
	 *
	 *
	 */

	/**
	 * @param $channelInfo
	 * @return array
	 * @throws ArgumentException
	 */
	public function openCalendarListChannel($channelInfo)
	{
		$requestBody = Web\Json::encode($channelInfo, JSON_UNESCAPED_SLASHES);
		return $this->doRequest(Web\HttpClient::HTTP_POST, self::API_BASE_URL . 'users/me/calendarList/watch', $requestBody);
	}

	/**
	 * @param $calendarId
	 * @param $channelInfo
	 * @return array
	 * @throws ArgumentException
	 */
	public function openEventsWatchChannel($calendarId, $channelInfo)
	{
		$this->currentMethod = __METHOD__;
		$requestBody = Web\Json::encode($channelInfo, JSON_UNESCAPED_SLASHES);
		return $this->doRequest(Web\HttpClient::HTTP_POST, self::API_BASE_URL . 'calendars/' . urlencode($calendarId) . '/events/watch', $requestBody);
	}

	/**
	 * @param $channelId
	 * @param $resourceId
	 * @return array
	 * @throws ArgumentException
	 */
	public function stopChannel($channelId, $resourceId)
	{
		$this->currentMethod = __METHOD__;
		$requestBody = Web\Json::encode(array('id' => $channelId, 'resourceId' => $resourceId), JSON_UNESCAPED_SLASHES);
		return $this->doRequest(Web\HttpClient::HTTP_POST, self::API_BASE_URL . 'channels/stop', $requestBody);
	}


	/**
	 * END PUSH SECTION
	 */

	/**
	 * GoogleApiTransport constructor.
	 * @throws SystemException
	 * @param int $userId
	 */
	public function __construct($userId)
	{
		if (!Loader::includeModule('socialservices'))
		{
			throw new SystemException("Can't include module \"SocialServices\"! " . __METHOD__);
		}

		$this->client = new Web\HttpClient();

		$oAuth = new \CSocServGoogleOAuth($userId);
		$oAuth->getEntityOAuth()->addScope(array('https://www.googleapis.com/auth/calendar', 'https://www.googleapis.com/auth/calendar.readonly'));
		$oAuth->getEntityOAuth()->setUser($userId);
		if ($oAuth->getEntityOAuth()->GetAccessToken())
		{
			$this->client->setHeader('Authorization', 'Bearer ' . $oAuth->getEntityOAuth()->getToken());
			$this->client->setHeader('Content-Type', 'application/json');
			unset($oAuth);
		}
		else
		{
			$this->errors[] = array("code" => "NO_ACCESS_TOKEN", "message" => "No access token found");
		}
	}

	/**
	 * Doing request to API server
	 *
	 * @param $type
	 * @param $url
	 * @param array $requestParams
	 * @throws ArgumentException
	 * @return array
	 */
	private function doRequest($type, $url, $requestParams = '')
	{
		$this->errors = $response = [];

		if (!in_array($type, [Web\HttpClient::HTTP_PATCH, Web\HttpClient::HTTP_PUT, Web\HttpClient::HTTP_DELETE, Web\HttpClient::HTTP_GET, Web\HttpClient::HTTP_POST]))
		{
			throw new ArgumentException('Bad request type');
		}

		$this->client->query($type, $url, ($requestParams ? $requestParams : null));

		//Only "OK" response is acceptable.
		if ($this->client->getStatus() == 200)
		{
			$contentType = $this->client->getHeaders()->getContentType();

			if ($contentType === 'multipart/mixed')
			{
				$response = $this->multipartDecode($this->client->getResult());
			}
			else
			{
				$response = Web\Json::decode($this->client->getResult());
			}
		}
		else
		{
			try
			{
				$error = Web\Json::decode($this->client->getResult());
				$this->errors[] = ["code" => "CONNECTION", "message" => "[" . $error['error']['code'] . "] " . $error['error']['message']];
			}
			catch (\Bitrix\Main\ArgumentException $exception)
			{
				foreach($this->client->getError() as $code => $error)
				{
					$this->errors[] = ["code" => $code, "message" => $error];
				}
			}
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
		return $this->doRequest(Web\HttpClient::HTTP_DELETE, self::API_BASE_URL . 'calendars/' . $calendarId . '/events/' . str_replace('@google.com', '', $eventId));
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
		return $this->doRequest(Web\HttpClient::HTTP_PATCH, self::API_BASE_URL . 'calendars/' . $calendarId . '/events/' . $eventId, $requestBody);
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
		return $this->doRequest(Web\HttpClient::HTTP_PUT, self::API_BASE_URL . 'calendars/' . $calendarId . '/events/' . $eventId, $requestBody);
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
		return $this->doRequest(Web\HttpClient::HTTP_POST, self::API_BASE_URL . 'calendars/' . $calendarId . '/events/', $requestBody);
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
		return $this->doRequest(Web\HttpClient::HTTP_POST, self::API_BASE_URL . 'calendars/' . $calendarId . '/events/import', $requestBody);
	}

	/**
	 * @param string $syncToken
	 * @return array|mixed
	 */
	public function getCalendarList($syncToken = '')
	{
		$this->currentMethod = __METHOD__;
		$params = $syncToken ? '?' . http_build_query(array('syncToken' => $syncToken)) : '';
		return $this->doRequest(Web\HttpClient::HTTP_GET, self::API_BASE_URL . 'users/me/calendarList' . $params);
	}

	/**
	 * get google calendar color codes
	 *
	 * @return array|mixed
	 */
	public function getColors()
	{
		$this->currentMethod = __METHOD__;
		return $this->doRequest(Web\HttpClient::HTTP_GET, self::API_BASE_URL . 'colors');
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
		$url = self::API_BASE_URL . 'calendars/' . urlencode($calendarId) . '/events';
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
			return array();
		}

		$errorsByCode = array_filter($this->errors, function($error) use ($code)
		{
			return $error['code'] == $code;
		});

		if (!empty($errorsByCode))
		{
			return end($errorsByCode);
		}
		else
		{
			return array();
		}
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
		$url = self::API_BASE_URL . 'calendars/' . urlencode($calendarId) . '/events/'.str_replace('@google.com', '', $eventId).'/instances/';
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
		return $this->doRequest(Web\HttpClient::HTTP_POST, self::API_BASE_URL . 'calendars/', $requestBody);
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

						$data .= 'Content-Length: '.BinaryString::getLength($value['partBody'])."\r\n\r\n";
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

	public function multipartDecode($response)
	{
		$events = [];
		$boundaryParts = explode('=', $this->client->getHeaders()->getBoundary());
		$boundary = $boundaryParts[1];

		$response = str_replace("--$boundary--", "--$boundary", $response);
		$parts = explode("--$boundary\r\n", $response);

		foreach ($parts as $key => $part)
		{
			$part = trim($part);
			if (!empty($part))
			{
				$partEvent = explode("\r\n\r\n", $part);
				$data = static::getMetaInfo($partEvent[1]);

				if ($data['status'] === 200)
				{
					$id = static::getId($partEvent[0]);
					$event = Web\Json::decode($partEvent[2]);
					$event['etag'] = $data['etag'];
					$events[$id] = $event;
				}
				else
				{
					AddMessage2Log('Event sync error. ID: '.static::getId($partEvent[0]));
				}
			}
		}

		return $events;
	}

	private function getMetaInfo($headers)
	{
		foreach (explode("\n", $headers) as $k => $header)
		{
			if($k == 0)
			{
				if(preg_match('#HTTP\S+ (\d+)#', $header, $find))
				{
					$data['status'] = intval($find[1]);
				}
			}
			elseif(mb_strpos($header, ':') !== false)
			{
				list($headerName, $headerValue) = explode(':', $header, 2);
				if(mb_strtolower($headerName) == 'etag')
				{
					$data['etag'] = trim($headerValue);
				}
			}
		}

		return $data;
	}

	private function getId ($headers)
	{
		foreach (explode("\n", $headers) as $k => $header)
		{
			if(mb_strpos($header, ':') !== false)
			{
				list($headerName, $headerValue) = explode(':', $header, 2);
				if(mb_strtolower($headerName) == 'content-id')
				{
					$part = explode(':', $headerValue);
					$id = rtrim($part[1], ">");
				}
			}
		}

		return $id;
	}
}
