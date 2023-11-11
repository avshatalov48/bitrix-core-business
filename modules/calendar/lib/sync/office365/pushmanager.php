<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Exceptions\ApiException;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Exceptions\NotFoundException;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Internals\HasContextTrait;
use Bitrix\Calendar\Sync\Managers\PushManagerInterface;
use Bitrix\Calendar\Sync\Push\Push;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Error;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;
use Exception;

class PushManager extends AbstractManager implements PushManagerInterface
{
	use HasContextTrait;

	public function __construct(Office365Context $context)
	{
		$this->context = $context;
		parent::__construct($context->getConnection());
	}

	/**
	 * @param Push $pushChannel
	 *
	 * @return Result
	 *
	 * @throws ObjectException
	 * @throws Exception
	 */
	public function renewPush(Push $pushChannel): Result
	{
		$result = new Result();
		try
		{
			if ($data = $this->context->getVendorSyncService()->resubscribe($pushChannel->getResourceId()))
			{
				if (empty($data['expirationDateTime']))
				{
					$time = time() + 70 * 60 * 60;
					$data['expirationDateTime'] = date('c', $time);
				}

				$pushChannel->setExpireDate(new Date($this->convertToDateTime($data['expirationDateTime'])));
				$result->setData([
					'CHANNEL_ID'  => $pushChannel->getChannelId(),
					'RESOURCE_ID' => $data['id'],
					'EXPIRES'     => $this->convertToDateTime($data['expirationDateTime']),
				]);
			}
			else
			{
				$result->addError(new Error('Error of renew push channel'));
			}
		}
		catch (ApiException $e)
		{
			$result->addError(new Error('Error of MS Graph API', $e->getCode(), $e->getMessage()));
		}

		return $result;
	}

	/**
	 * @param string $time
	 *
	 * @return DateTime
	 *
	 * @throws Exception
	 */
	private function convertToDateTime(string $time): DateTime
	{
		$phpDateTime = new \DateTime($time);

		return DateTime::createFromPhp($phpDateTime);
	}

	/**
	 * @param SectionConnection $link
	 *
	 * @return Result
	 *
	 * @throws Exception
	 */
	public function addSectionPush(SectionConnection $link): Result
	{
		$result = new Result();
		try
		{
			$data = $this->context->getVendorSyncService()->subscribeSection($link);

			if ($data && !empty($data['channelId']))
			{
				$result->setData([
					'CHANNEL_ID' => $data['channelId'],
					'RESOURCE_ID' => $data['id'],
					'EXPIRES' => $this->convertToDateTime($data['expirationDateTime'] ?? date('c')),
				]);
			}
			else
			{
				$result->addError(new Error('Error of create subscription.'));
			}
		}
		catch (ApiException $e)
		{
			$result->addError(new Error('Error of Push subscribing. Vendor returned error.', $e->getCode()));
		}
		catch (AuthException $e)
		{
			$result->addError(new Error('No authentication data', $e->getCode()));
		}

		return $result;
	}

	/**
	 * @param Push $pushChannel
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws AuthException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws LoaderException
	 * @throws NotFoundException
	 * @throws RemoteAccountException
	 */
	public function deletePush(Push $pushChannel): Result
	{
		$result = new Result();

		try
		{
			if ($data = $this->context->getVendorSyncService()->unsubscribe($pushChannel->getResourceId()))
			{
				$result->setData($data);
			}

			return $result;
		}
		catch (RemoteAccountException|AuthException $exception)
		{
			return $result;
		}
	}

	/**
	 * method for supporting
	 *
	 * @return void
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws LoaderException
	 * @throws NotFoundException
	 * @throws AuthException
	 * @throws RemoteAccountException
	 */
	public function clearAllSubscriptions(): void
	{
		$apiResponse = $this->context->getApiClient()->get('subscriptions');
		if (!empty($apiResponse['value']))
		{
			foreach ($apiResponse['value'] as $subscription)
			{
				$this->context->getApiClient()->delete('subscriptions/' . $subscription['id']);
			}
		}
	}

	/**
	 * @param Connection $connection
	 *
	 * @return Result
	 */
	public function addConnectionPush(Connection $connection): Result
	{
		return (new Result())->addError(new Error('Connection push unavailable for this service', 503));
	}
}
