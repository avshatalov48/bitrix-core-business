<?

namespace Bitrix\Seo\LeadAds;

use Bitrix\Seo\Retargeting\BaseApiObject;
use Bitrix\Seo\Retargeting\Response;

abstract class Account extends BaseApiObject
{
	const URL_ACCOUNT_LIST = '';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
	);

	protected $accountId;
	protected $pageId;

	public function __construct($accountId = null)
	{
		$this->accountId = $accountId;
		parent::__construct();
	}

	public function getProfileCached()
	{
		$profile = $this->getProfile();
		if($profile)
		{

		}

		return $profile;
	}

	public static function getUrlAccountList()
	{
		return static::URL_ACCOUNT_LIST;
	}

	/**
	 * @return Response
	 */
	abstract public function getList();

	/**
	 * @return Response
	 */
	abstract public function getProfile();
}