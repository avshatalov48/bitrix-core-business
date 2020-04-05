<?

namespace Bitrix\Seo\LeadAds;

use Bitrix\Main\Context;
use Bitrix\Seo\Retargeting\BaseApiObject;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\WebHook;
use Bitrix\Seo\Retargeting;

abstract class Form extends BaseApiObject
{
	const URL_FORM_LIST = '';
	const USE_GROUP_AUTH = false;

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
	public function __construct($accountId = null)
	{
		$this->accountId = $accountId;
		parent::__construct();
	}

	/**
	 * Set account id.
	 *
	 * @param string $accountId Account ID.
	 * @return mixed
	 */
	public function setAccountId($accountId)
	{
		return $this->accountId = $accountId;
	}

	/**
	 * Get list.
	 *
	 * @return Response
	 */
	abstract public function getList();

	/**
	 * Get result.
	 *
	 * @param WebHook\Payload\LeadItem $item Payload item instance.
	 * @return Result
	 */
	abstract public function getResult(WebHook\Payload\LeadItem $item);

	/**
	 * Add.
	 *
	 * @param array $data Data.
	 * @return Response
	 */
	abstract public function add(array $data);

	/**
	 * Unlink.
	 *
	 * @param string $id.
	 * @return bool
	 */
	abstract public function unlink($id);

	/**
	 * Get registered groups.
	 *
	 * @return string[]
	 */
	public function getRegisteredGroups()
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
	 * @return bool
	 */
	public function unRegisterGroup($groupId)
	{
		$row = Internals\CallbackSubscriptionTable::getRow([
			'filter' => [
				'=TYPE' => static::TYPE_CODE,
				'=GROUP_ID' => $groupId
			]
		]);
		if (!$row)
		{
			return true;
		}

		return Internals\CallbackSubscriptionTable::delete($row['ID'])->isSuccess();
	}

	/**
	 * Register group.
	 *
	 * @param string $groupId Group ID.
	 * @return bool
	 */
	public function registerGroup($groupId)
	{
		$hasGroup = false;
		$list = Internals\CallbackSubscriptionTable::getList([
			'filter' => [
				'=TYPE' => static::TYPE_CODE
			]
		]);
		foreach ($list as $row)
		{
			if ($row['GROUP_ID'] == $groupId)
			{
				$hasGroup = true;
			}
			else
			{
				Internals\CallbackSubscriptionTable::delete($row['ID']);
			}
		}

		if ($hasGroup)
		{
			return true;
		}

		return Internals\CallbackSubscriptionTable::add([
			'TYPE' => static::TYPE_CODE,
			'GROUP_ID' => $groupId
		])->isSuccess();
	}

	/**
	 * Convert field.
	 *
	 * @param Field $field Field.
	 * @return array
	 */
	public static function convertField(Field $field)
	{
		$item = $field->toArray();
		foreach ($item as $key => $value)
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
	public static function convertFields(array $fields)
	{
		$list = [];
		foreach ($fields as $field)
		{
			$list[] = static::convertField($field);
		}

		return $list;
	}

	/**
	 * Get form list url.
	 *
	 * @return string
	 */
	public static function getUrlFormList()
	{
		return static::URL_FORM_LIST;
	}

	/**
	 * Is account supported.
	 *
	 * @return bool
	 */
	public static function isSupportAccount()
	{
		return true;
	}

	/**
	 * Get privacy policy url.
	 *
	 * @return string
	 */
	public static function getPrivacyPolicyUrl()
	{
		$langId = Context::getCurrent()->getLanguage();
		switch ($langId)
		{
			case 'ru':
			case 'ua':
			case 'de':
				return "https://www.bitrix24.{$langId}/privacy/";

			default:
				return 'https://www.bitrix24.com/privacy/';
		}
	}

	protected function registerFormWebHook($adsFormId, array $parameters = [])
	{
		$type = Service::getEngineCode(static::TYPE_CODE);
		return WebHook\Service::create($type, $adsFormId)->register($parameters);
	}

	protected function removeFormWebHook($adsFormId)
	{
		$type = Service::getEngineCode(static::TYPE_CODE);
		return WebHook\Service::create($type, $adsFormId)->remove();
	}

	/**
	 * Return true if group auth used.
	 *
	 * @return bool
	 */
	public static function isGroupAuthUsed()
	{
		return static::USE_GROUP_AUTH;
	}

	protected function getAuthParameters()
	{
		return [];
	}

	/**
	 * Get group auth adapter.
	 *
	 * @return Retargeting\AuthAdapter|null
	 */
	public function getGroupAuthAdapter()
	{
		if (!$this->isGroupAuthUsed())
		{
			return null;
		}

		$type = static::TYPE_CODE;
		$adapter = Retargeting\AuthAdapter::create(static::TYPE_CODE . '.groups')
			->setService(Service::getInstance())
			->setParameters($this->getAuthParameters());


		$row = Internals\CallbackSubscriptionTable::getRow([
			'filter' => [
				'=TYPE' => $type,
			]
		]);
		if ($row && $row['HAS_AUTH'] !== 'Y' && $adapter->hasAuth())
		{
			Internals\CallbackSubscriptionTable::update($row['ID'], ['HAS_AUTH' => 'Y']);
		}

		return $adapter;
	}
}