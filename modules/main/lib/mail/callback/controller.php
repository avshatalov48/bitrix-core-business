<?php

namespace Bitrix\Main\Mail\Callback;

use Bitrix\Mail\Helper\OAuth;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Mail\Address;
use Bitrix\Main\Mail\Smtp\CloudOAuthRefreshData;
use Bitrix\Main\Mail\Smtp\OAuthConfigPreparer;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Mail\Internal;
use Bitrix\Main\Mail\SenderSendCounter;
use Bitrix\Main\Mail\Sender;

/**
 * Class Controller
 *
 * @package Bitrix\Main\Mail\Callback
 */
class Controller
{
	public const STATUS_DEFERED = 'defered';
	public const STATUS_BOUNCED = 'bounced';
	public const STATUS_DELIVERED = 'delivered';

	public const DESC_AUTH = 'AUTH_ERROR';
	public const DESC_UNKNOWN_USER = 'UNKNOWN_USER';
	public const DESC_UNROUTEABLE = 'UNROUTEABLE';
	private const DESC_SMTP_LIMITED = 'SMTP_LIMITED';

	/** @var  string $id ID of mail. */
	protected $id;

	/** @var  Result $result Result instance. */
	protected $result;

	/** @var  Config $config Config instance. */
	protected $config;

	/** @var  Address $address Address instance. */
	protected $address;

	/** @var string[] $blacklist Black list of emails. */
	protected $blacklist = [];

	/** @var  string[] $smtpLimited List of emails which out of limit. */
	protected $smtpLimited = [];

	/** @var bool $answerExceptions Flush exceptions in answer. */
	protected static $answerExceptions = true;

	/** @var bool $enableItemErrors Ignore item errors. */
	protected $enableItemErrors = false;

	/** @var int $countItems. */
	protected $countItems = 0;
	/** @var int $countItems. */
	protected $countItemsProcessed = 0;
	/** @var int $countItems. */
	protected $countItemsError = 0;

	protected array $refreshedTokens = [];

	/**
	 * Run controller.
	 *
	 * @param string $data Data.
	 * @param array $parameters Parameters.
	 * @return void
	 */
	public static function run($data = null, array $parameters = [])
	{
		$request = Context::getCurrent()->getRequest();
		if ($data === null)
		{
			$data = $request->getPostList()->getRaw('data');
		}
		if (!isset($parameters['IGNORE_ITEM_ERRORS']))
		{
			$parameters['ENABLE_ITEM_ERRORS'] = mb_strtoupper($request->get('enableItemErrors')) === 'Y';
		}

		$instance = new self();
		if ($parameters['ENABLE_ITEM_ERRORS'])
		{
			$instance->enableItemErrors();
		}

		try
		{
			if (empty($data))
			{
				self::giveAnswer(true, 'No input data.');
			}

			try
			{
				$data = Json::decode($data);
			}
			catch (\Exception $exception)
			{
			}

			if (!is_array($data))
			{
				self::giveAnswer(true, 'Wrong data.');
			}

			if (!isset($data['list']) || !is_array($data['list']))
			{
				self::giveAnswer(true, 'Parameter `list` required.');
			}

			$instance->processList($data['list']);

			self::giveAnswer(false, ['list' => $instance->getCounters()]);
		}
		catch (SystemException $exception)
		{
			self::giveAnswer(
				true,
				[
					'text' => self::$answerExceptions ? $exception->getMessage() : null,
					'list' => $instance->getCounters()
				]
			);
		}
	}

	/**
	 * Give answer.
	 *
	 * @param bool $isError Data.
	 * @param string|array|null $answer Answer.
	 * @return void
	 */
	public static function giveAnswer($isError = false, $answer = null)
	{
		$response = Context::getCurrent()->getResponse();
		$response->addHeader('Status', $isError ? '422' : '200');
		$response->addHeader('Content-Type', 'application/json');

		if (!is_array($answer))
		{
			$answer = [
				'text' => $answer ?: null
			];
		}
		$answer['error'] = $isError;
		if (empty($answer['text']))
		{
			$answer['text'] = $isError ? 'Unknown error' : 'Success';
		}
		$answer = Json::encode($answer);

		\CMain::FinalActions($answer);
		exit;
	}

	/**
	 * Controller constructor.
	 */
	public function __construct()
	{
		$this->config = new Config();
		$this->result = new Result();
		$this->address = new Address();
	}

	/**
	 * Enable item errors.
	 *
	 * @return $this
	 */
	public function enableItemErrors()
	{
		$this->enableItemErrors = true;
		return $this;
	}

	protected function validateItem($item)
	{
		if (empty($item['id']))
		{
			throw new ArgumentException('Field `id` is required for item.');
		}
		if(!preg_match("/[a-zA-Z0-1=]{3,}/", $item['id']))
		{
			throw new ArgumentException('Field `id` has disallowed chars.');
		}

		if (empty($item['sign']))
		{
			throw new ArgumentException("Field `sign` is required for item with id `{$item['id']}`.");
		}

		if (empty($item['status']))
		{
			throw new ArgumentException("Field `status` is required for item with id `{$item['id']}`.");
		}

		if (empty($item['email']))
		{
			throw new ArgumentException("Field `email` is required for item with id `{$item['id']}`.");
		}
	}

	/**
	 * Process list.
	 *
	 * @param array $list List of items.
	 * @return void
	 * @throws SystemException
	 */
	public function processList($list)
	{
		$this->countItems = count($list);

		$this->blacklist = [];
		$this->smtpLimited = [];
		foreach ($list as $index => $item)
		{
			$this->countItemsProcessed++;
			try
			{
				$result = $this->processItem($item);
				if (!$result)
				{
					$this->countItemsError++;
				}
			}
			catch (SystemException $exception)
			{
				$this->countItemsError++;

				if ($this->enableItemErrors)
				{
					throw $exception;
				}
			}
		}

		Internal\BlacklistTable::insertBatch($this->blacklist);
		$this->decreaseLimit();
	}

	/**
	 * Decrease limits.
	 *
	 * @return void
	 */
	public function decreaseLimit()
	{
		if (!$this->smtpLimited)
		{
			return;
		}

		foreach ($this->smtpLimited as $email)
		{
			Sender::setEmailLimit(
				$email,
				SenderSendCounter::DEFAULT_LIMIT,
				false,
			);
		}
	}

	/**
	 * Process item.
	 *
	 * @param array $item Item data.
	 * @return bool
	 * @throws SystemException
	 */
	public function processItem($item)
	{
		$this->validateItem($item);

		$this->config->unpackId($item['id']);
		if (!$this->config->verifySignature($item['sign']))
		{
			throw new SystemException('Item parameter `sign` is invalid.');
		}

		if (!$this->config->getEntityId())
		{
			return false;
		}

		$email = $this->address->set($item['email'])->getEmail();
		if (!$email)
		{
			return false;
		}

		$this->processAsRefreshRequest($item);

		if (!empty($item['sender']) && self::isSmtpLimited($item['statusDescription']) )
		{
			$this->smtpLimited[] = $this->address->set($item['sender'])->getEmail();
		}

		$this->result
			->setModuleId($this->config->getModuleId())
			->setEntityType($this->config->getEntityType())
			->setEntityId($this->config->getEntityId())
			->setEmail($email)
			->setDateSent((int) $item['completedAt'])
			->setError(self::isStatusError($item['status']))
			->setPermanentError(self::isStatusPermanentError($item['status']))
			->setBlacklistable(self::isBlacklistable($item['statusDescription']))
			->setDescription($item['statusDescription'])
			->setMessage($item['message']);

		if ($this->result->isPermanentError() && $this->result->isBlacklistable())
		{
			$this->blacklist[] = $this->result->getEmail();
		}

		$this->result->sendEvent();

		return true;
	}

	/**
	 * Get counters
	 *
	 * @return array
	 */
	public function getCounters(): array
	{
		$result = [
			'all' => $this->countItems,
			'processed' => $this->countItemsProcessed,
			'errors' => $this->countItemsError,
		];

		if ($this->refreshedTokens)
		{
			$result['refreshedTokens'] = $this->refreshedTokens;
		}

		return $result;
	}

	/**
	 * Return true if status is error.
	 *
	 * @param string $status Status.
	 * @return bool
	 */
	public static function isStatusError($status)
	{
		return in_array($status, [self::STATUS_DEFERED, self::STATUS_BOUNCED]);
	}

	/**
	 * Return true if status is permanent error.
	 *
	 * @param string $status Status.
	 * @return bool
	 */
	public static function isStatusPermanentError($status)
	{
		return $status === self::STATUS_BOUNCED;
	}

	/**
	 * Return true if status descriptions is blacklistable.
	 *
	 * @param string $description Description.
	 * @return bool
	 */
	public static function isBlacklistable($description)
	{
		return $description && in_array($description, [self::DESC_UNKNOWN_USER, self::DESC_UNROUTEABLE]);
	}

	/**
	 * Return true if status descriptions is available for smtp.
	 *
	 * @param string $description Description.
	 * @return bool
	 */
	private static function isSmtpLimited(string $description)
	{
		return $description && in_array($description, [self::DESC_SMTP_LIMITED], true);
	}

	/**
	 * @throws LoaderException
	 * @throws SystemException
	 */
	private function processAsRefreshRequest(array $item): void
	{
		if (empty($item['refreshUid']) || !isset($item['refreshExpires']) || empty($item['refreshSign']))
		{
			return;
		}

		$uid = (string)base64_decode($item['refreshUid']);
		$data = new CloudOAuthRefreshData($uid, (int)$item['refreshExpires']);
		if (!$data->isSignValid((string)$item['refreshSign']))
		{
			throw new SystemException('Invalid refresh oauth signature');
		}

		if (!Loader::includeModule('mail'))
		{
			throw new SystemException('Module mail not installed');
		}

		$mailOAuth = OAuth::getInstanceByMeta($uid);
		if (!$mailOAuth || !$mailOAuth->getStoredUid())
		{
			throw new SystemException('Incorrect refresh meta');
		}

		$expireGapSeconds = (new OAuthConfigPreparer())->getOAuthTokenExpireGapSeconds();
		$token = $mailOAuth->getStoredToken(null, $expireGapSeconds);
		if (empty($token))
		{
			throw new SystemException('Cannot refresh token');
		}

		$expires = $defaultExpires = time() + $expireGapSeconds;
		$oauthEntity = $mailOAuth->getOAuthEntity();
		if (is_object($oauthEntity) && method_exists($oauthEntity, 'getTokenData'))
		{
			$expires = $oauthEntity->getTokenData()['expires_in'] ?? $defaultExpires;
		}

		$this->refreshedTokens[] = [
			'uid' => $item['refreshUid'],
			'accessToken' => $token,
			'expires' => (int)$expires,
		];
	}

}
