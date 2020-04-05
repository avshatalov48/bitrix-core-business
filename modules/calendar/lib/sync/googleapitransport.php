<?
namespace Bitrix\Calendar\Sync;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\Web;

/**
 * Class GoogleApiTransport
 *
 * @package Bitrix\Calendar\Sync
 */
final class GoogleApiTransport //implements IErrorable
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
		#return $this->client->getResult();
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
		$this->errors = $response = array();
		if (!in_array($type, array(Web\HttpClient::HTTP_PATCH, Web\HttpClient::HTTP_PUT, Web\HttpClient::HTTP_DELETE, Web\HttpClient::HTTP_GET, Web\HttpClient::HTTP_POST)))
		{
			throw new ArgumentException('Bad request type');
		}
		$this->client->query($type, $url, ($requestParams ? $requestParams : null));
		//Only "OK" response is acceptable.
		if ($this->client->getStatus() == 200)
		{
			$response = Web\Json::decode($this->client->getResult());
		}
		else
		{
			try {
				$error = Web\Json::decode($this->client->getResult());
				$this->errors[] = array("code" => "CONNECTION", "message" => "[" . $error['error']['code'] . "] " . $error['error']['message']);
			}
			catch (\Bitrix\Main\ArgumentException $exception)
			{
				foreach($this->client->getError() as $code => $error)
				{
					$this->errors[] = array("code" => $code, "message" => $error);
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
		return $this->doRequest(Web\HttpClient::HTTP_PATCH, self::API_BASE_URL . 'calendars/' . urlencode($calendarId) . '/events/' . str_replace('@google.com', '', $eventId), $requestBody);
	}
	/**
	 * Updates event data at google calendar
	 *
	 * @param $eventData
	 * @param $calendarId
	 * @param $eventId
	 * @return array|mixed
	 */
	public function updateEvent($eventData, $calendarId, $eventId)
	{
		$this->currentMethod = __METHOD__;
		$requestBody = Web\Json::encode($eventData, JSON_UNESCAPED_SLASHES);
		return $this->doRequest(Web\HttpClient::HTTP_PATCH, self::API_BASE_URL . 'calendars/' . $calendarId . '/events/' . str_replace('@google.com', '', $eventId), $requestBody);
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
}
