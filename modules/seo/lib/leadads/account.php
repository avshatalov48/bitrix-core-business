<?

namespace Bitrix\Seo\LeadAds;

use Bitrix\Seo\Retargeting;
use Bitrix\Seo\Retargeting\IRequestDirectly;
/**
 * Class Account
 *
 * @package Bitrix\Seo\LeadAds
 */
abstract class Account extends Retargeting\Account
{
	public const URL_ACCOUNT_LIST = '';
	public const URL_INFO = '';

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
	 * @return Retargeting\Response|array
	 */
	public function getProfileCached()
	{
		return $this->getProfile();
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

}