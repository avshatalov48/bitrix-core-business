<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\GoogleApiSync;
use Bitrix\Calendar\Sync\Managers\PushManagerInterface;
use Bitrix\Calendar\Sync\Push\Push;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web;

class PushManager extends Manager implements PushManagerInterface
{
	/**
	 * @param SectionConnection $link
	 * @return Result
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addSectionPush(SectionConnection $link): Result
    {
		$result = new Result();
		if ($this->isVirtualCalendar($link))
		{
			return $result->addError(new Error('This type of calendar doesnt support push.', 415));
		}

		$calendarId = $link->getVendorSectionId();
		$params = $this->makeChannelParams($link->getId(), Dictionary::PUSH_CHANNEL_TYPES['sectionConnection']);

		// TODO: Remake it: move this logic to parent::request().
		// Or, better, in separate class.
		$this->httpClient->query(
			Web\HttpClient::HTTP_POST,
			$this->connection->getVendor()->getServer()->getFullPath()
			. '/calendars/' . urlencode($calendarId) . '/events/watch',
			Web\Json::encode($params, JSON_UNESCAPED_SLASHES)
		);

		if ($this->httpClient->getStatus() === 200)
		{
			$data = Web\Json::decode($this->httpClient->getResult());
			$result->setData([
				'CHANNEL_ID' => $data['id'],
				'RESOURCE_ID' => $data['resourceId'],
				'EXPIRES' => new DateTime($data['expiration'] / 1000, 'U'),
			]);
		}
		else if ($this->httpClient->getStatus() === 401)
		{
			$this->handleUnauthorize($this->connection);

			$result->addError(new Error('Unauthorized', $this->httpClient->getStatus()));
		}
		else
		{
			$result->addError(new Error('Error of create channel', $this->httpClient->getStatus()));
		}

		return $result;
    }

    public function renewPush(Push $pushChannel): Result
    {
		return (new Result())->addError(new Error('Service doesnt support this method', 405));
    }

	/**
	 * @param Push $pushChannel
	 * @return Result
	 * @throws ArgumentException
	 * @throws LoaderException
	 */
    public function deletePush(Push $pushChannel): Result
    {
		$result = new Result();
		// TODO: Remake it: move this logic to parent::request().
		// Or, better, in separate class.
		$this->httpClient->query(
			Web\HttpClient::HTTP_POST,
			$this->connection->getVendor()->getServer()->getFullPath() . '/channels/stop',
			Web\Json::encode([
				'id' => $pushChannel->getChannelId(), // TODO: need to understand - what id is waiting
				'resourceId' => $pushChannel->getResourceId()
			], JSON_UNESCAPED_SLASHES)
		);

		if ($this->httpClient->getStatus() !== 200)
		{
			$result->addError(new Error('Error of stopping push channel.', $this->httpClient->getStatus()));
		}

		return $result;
    }

	/**
	 * @param Connection $connection
	 * @return Result
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addConnectionPush(Connection $connection): Result
	{
		$result = new Result();
		$channelInfo = $this->makeChannelParams($connection->getName(), GoogleApiSync::CONNECTION_CHANNEL_TYPE);
		// TODO: Remake it: move this logic to parent::request().
		// Or, better, in separate class.
		$this->httpClient->query(
			Web\HttpClient::HTTP_POST,
			$this->connection->getVendor()->getServer()->getFullPath() . '/users/me/calendarList/watch',
			Web\Json::encode($channelInfo, JSON_UNESCAPED_SLASHES)
		);

		if ($this->isRequestSuccess())
		{
			$data = Web\Json::decode($this->httpClient->getResult());
			$result->setData([
				'CHANNEL_ID' => $data['id'],
				'RESOURCE_ID' => $data['resourceId'],
				'EXPIRES' => new DateTime($data['expiration'] / 1000, 'U'),
			]);
		}
		else if ($this->httpClient->getStatus() === 401)
		{
			$this->handleUnauthorize($this->connection);

			$result->addError(new Error('Unauthorized', $this->httpClient->getStatus()));
		}
		else
		{
			$result->addError(new Error('Error of create channel', $this->httpClient->getStatus()));
		}

		return $result;
	}

	/**
	 * @param $uniqId
	 * @param string $type
	 *
	 * @return array
	 */
	private function makeChannelParams($uniqId, string $type): array
	{
		if (defined('BX24_HOST_NAME') && BX24_HOST_NAME)
		{
			$externalUrl = GoogleApiSync::EXTERNAL_LINK . BX24_HOST_NAME;
		}
		else
		{
			$request = Context::getCurrent()->getRequest();
			if (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME)
			{
				$host = SITE_SERVER_NAME;
			}
			else
			{
				$host = Option::get('main', 'server_name', $request->getHttpHost());
			}

			$externalUrl = 'https://' . $host . '/bitrix/tools/calendar/push.php';
		}

		return [
			'id' => $type . '_' . $this->userId.'_'.md5($uniqId. time()),
			'type' => 'web_hook',
			'address' => $externalUrl,
			'expiration' => (time() + GoogleApiSync::CHANNEL_EXPIRATION) * 1000,
		];
	}

	/**
	 * @param SectionConnection $link
	 *
	 * @return bool
	 */
	private function isVirtualCalendar(SectionConnection $link): bool
	{
		return (strpos($link->getVendorSectionId(), 'holiday.calendar.google.com'))
			|| (strpos($link->getVendorSectionId(), 'group.v.calendar.google.com'))
			|| (strpos($link->getVendorSectionId(), '@virtual'))
			|| (strpos($link->getSection()->getExternalType(), '_readonly'))
			|| (strpos($link->getSection()->getExternalType(), '_freebusy'))
			;
	}
}