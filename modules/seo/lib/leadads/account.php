<?

namespace Bitrix\Seo\LeadAds;

use Bitrix\Seo\Retargeting;
use Bitrix\Seo\Retargeting\IRequestDirectly;
/**
 * Class Account
 *
 * @package Bitrix\Seo\LeadAds
 */
abstract class Account extends Retargeting\Account implements IRequestDirectly
{
	const URL_ACCOUNT_LIST = '';
	const URL_INFO = '';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
	);

	protected $accountId;
	protected $pageId;

	/**
	 * Account constructor.
	 *
	 * @param null|string $accountId Account ID.
	 */
	public function __construct($accountId = null)
	{
		$this->accountId = $accountId;
		parent::__construct();
	}

	/**
	 * Get profile cached.
	 *
	 * @return Retargeting\Response
	 */
	public function getProfileCached()
	{
		$profile = $this->getProfile();

		return $profile;
	}

	/**
	 * Get url account list.
	 *
	 * @return string
	 */
	public static function getUrlAccountList()
	{
		return static::URL_ACCOUNT_LIST;
	}

	/**
	 * Get url info.
	 *
	 * @return string
	 */
	public static function getUrlInfo()
	{
		return static::URL_INFO;
	}

	/**
	 * Get group auth adapter.
	 *
	 * @param string $type Type.
	 * @return Retargeting\AuthAdapter
	 */
	public static function getGroupAuthAdapter($type)
	{
		$adapter = Retargeting\AuthAdapter::create($type . '.groups');

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