<?php

namespace Bitrix\Seo\LeadAds;

use Bitrix\Main\Context;
use Bitrix\Seo\LeadAds;
use Bitrix\Seo\Retargeting\BaseApiObject;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\WebHook;
use Bitrix\Seo\Retargeting;

abstract class Form extends BaseApiObject
{
	public const URL_FORM_LIST = '';
	public const USE_GROUP_AUTH = false;
	public const FIELD_MAP = [];

	/** @var  array $listRowMap Map. */
	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
	);

	/** @var  string|null $accountId Account ID. */
	protected $accountId;

	/** @var  string|null $formId Form ID. */
	protected $formId;

	/**
	 * Form constructor.
	 *
	 * @param string|null $accountId Account ID.
	 */
	public function __construct(string $accountId = null)
	{
		$this->accountId = $accountId;
		parent::__construct();
	}

	/**
	 * Set account id.
	 *
	 * @param string $accountId Account ID.
	 *
	 * @return self
	 */
	public function setAccountId(string $accountId): Form
	{
		$this->accountId = $accountId;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getAccountId(): ?string
	{
		return $this->accountId;
	}

	/**
	 * @return Mapper
	 */
	protected static function getFieldMapper(): Mapper
	{
		static $mapper;
		return $mapper = $mapper ?? new LeadAds\Mapper(static::FIELD_MAP);
	}


	/**
	 * @param $formId
	 *
	 * @return LeadAds\Response\FormResponse
	 */
	abstract public function getForm($formId):  LeadAds\Response\FormResponse;

	/**
	 * Get list.
	 *
	 * @return LeadAds\Response\FormResponse
	 */
	abstract public function getList(): LeadAds\Response\FormResponse;

	/**
	 * Get result.
	 *
	 * @param WebHook\Payload\LeadItem $item Payload item instance.
	 * @return Result
	 */
	abstract public function getResult(WebHook\Payload\LeadItem $item): Result;

	/**
	 * Add.
	 *
	 * @param array $data Data.
	 * @return Response
	 */
	abstract public function add(array $data): Response;

	/**
	 * Register WebHook
	 * @param $formId
	 * @return bool
	 */
	abstract public function register($formId): bool;

	/**
	 * Unlink.
	 *
	 * @param string $id.
	 *
	 * @return bool
	 */
	abstract public function unlink(string $id): bool;


	/**
	 * Get registered groups.
	 *
	 * @return string[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getRegisteredGroups(): array
	{
		$rows = Internals\CallbackSubscriptionTable::getList([
			'select' => ['GROUP_ID'],
			'filter' => [
				'=TYPE' => static::TYPE_CODE,
			]
		])->fetchAll();

		return array_column($rows, 'GROUP_ID');
	}

	/**
	 * Unregister group.
	 *
	 * @param string $groupId Group ID.
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function unRegisterGroup(string $groupId) : bool
	{
		$row = Internals\CallbackSubscriptionTable::getRow([
			'filter' => [
				'=TYPE' => static::TYPE_CODE,
				'=GROUP_ID' => $groupId
			]
		]);

		return $row && Internals\CallbackSubscriptionTable::delete($row['ID'])->isSuccess();
	}

	/**
	 * Register group.
	 *
	 * @param string $groupId Group ID.
	 *
	 * @return bool
	 *
	 */
	public function registerGroup(string $groupId): bool
	{
		$hasGroup = false;

		$list = Internals\CallbackSubscriptionTable::getList([
			'filter' => [
				'=TYPE' => static::TYPE_CODE
			]
		]);
		foreach ($list as $row)
		{
			if ($row['GROUP_ID'] === $groupId)
			{
				$hasGroup = true;
				continue;
			}

			Internals\CallbackSubscriptionTable::delete($row['ID']);
		}
		if ($hasGroup)
		{
			return true;
		}

		$callbackSubscriptionResult = Internals\CallbackSubscriptionTable::add([
			'TYPE' => static::TYPE_CODE,
			'GROUP_ID' => $groupId
		]);

		return $callbackSubscriptionResult->isSuccess();
	}

	/**
	 * Convert field.
	 *
	 * @param Field $field Field.
	 * @return array
	 */
	public static function convertField(Field $field): array
	{
		foreach ($item = $field->toArray() as $key => $value)
		{
			if (empty($value))
			{
				unset($item[$key]);
			}
		}

		return $item;
	}

	/**
	 * Convert fields.
	 *
	 * @param Field[] $fields Fields.
	 * @return array
	 */
	public static function convertFields(array $fields): array
	{
		return array_map(
			static function($field)
			{
				return static::convertField($field);
			},
			$fields
		);
	}

	/**
	 * Get form list url.
	 *
	 * @return string
	 */
	public static function getUrlFormList(): string
	{
		return static::URL_FORM_LIST;
	}

	/**
	 * Is account supported.
	 *
	 * @return bool
	 */
	public static function isSupportAccount(): bool
	{
		return true;
	}

	/**
	 * Get privacy policy url.
	 *
	 * @return string
	 */
	public static function getPrivacyPolicyUrl(): string
	{
		switch ($langId = Context::getCurrent()->getLanguage())
		{
			case 'ua':
			case 'ru':
			case 'kz':
			case 'by':
				return "https://www.bitrix24.{$langId}/about/privacy.php";
			case 'de':
				return "https://www.bitrix24.{$langId}/privacy/";

			default:
				return 'https://www.bitrix24.com/privacy/';
		}
	}

	protected function registerFormWebHook($adsFormId, array $parameters = []): bool
	{
		return WebHook\Service::create(
			Service::getEngineCode(static::TYPE_CODE),
			$adsFormId
		)->register($parameters);
	}

	protected function removeFormWebHook($adsFormId): bool
	{
		return WebHook\Service::create(
			Service::getEngineCode(static::TYPE_CODE),
			$adsFormId
		)->remove();
	}

	/**
	 * Return true if group auth used.
	 *
	 * @return bool
	 */
	public static function isGroupAuthUsed(): bool
	{
		return static::USE_GROUP_AUTH;
	}

	protected function getAuthParameters(): array
	{
		return [];
	}

	/**
	 * Get group auth adapter.
	 *
	 * @return Retargeting\AuthAdapter|null
	 */
	public function getGroupAuthAdapter(): ?Retargeting\AuthAdapter
	{
		if (!self::isGroupAuthUsed())
		{
			return null;
		}

		$adapter = Retargeting\AuthAdapter::create(static::TYPE_CODE . '.groups', $this->service);
		$adapter = $adapter->setParameters($this->getAuthParameters());
		$row = Internals\CallbackSubscriptionTable::getRow([
			'filter' => [
				'=TYPE' => static::TYPE_CODE,
			]
		]);

		if ($row && $row['HAS_AUTH'] !== 'Y' && $adapter->hasAuth())
		{
			Internals\CallbackSubscriptionTable::update($row['ID'], ['HAS_AUTH' => 'Y']);
		}

		return $adapter;
	}
}
