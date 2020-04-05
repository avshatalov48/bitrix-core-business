<?

namespace Bitrix\Seo\LeadAds;

use Bitrix\Seo\Retargeting\BaseApiObject;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\WebHook\Service as WebHookService;

abstract class Form extends BaseApiObject
{
	const URL_FORM_LIST = '';

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
	 * Get result by id.
	 *
	 * @param string $id ID.
	 * @return Result
	 */
	abstract public function getResult($id);

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

	protected function registerFormWebHook($adsFormId)
	{
		$type = Service::getEngineCode(static::TYPE_CODE);
		return WebHookService::create($type, $adsFormId)->register();
	}

	protected function removeFormWebHook($adsFormId)
	{
		$type = Service::getEngineCode(static::TYPE_CODE);
		return WebHookService::create($type, $adsFormId)->remove();
	}
}