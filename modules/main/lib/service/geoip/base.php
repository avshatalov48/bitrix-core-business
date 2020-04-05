<?
namespace Bitrix\Main\Service\GeoIp;

/**
 * Class Base
 * @package Bitrix\Main\Service\GeoIp
 *
 * Base class for geolocation handlers
 */
abstract class Base
{
	protected $id;
	protected $sort;
	protected $active;
	protected $config;

	/**
	 * Base constructor.
	 * @param array $fields DB fields of handlers settings.
	 */
	public function __construct(array $fields = array())
	{
		$this->id = isset($fields['ID']) ? intval($fields['ID']) : 0;
		$this->sort = isset($fields['SORT']) ? intval($fields['SORT']) : 100;
		$this->active = isset($fields['ACTIVE']) && $fields['ACTIVE'] == 'Y' ? 'Y' : 'N';
		$this->config = isset($fields['CONFIG']) && is_array($fields['CONFIG']) ? $fields['CONFIG'] : array();
	}

	/**
	 * @return int DB record identifier.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return int DB field sorting.
	 */
	public function getSort()
	{
		return $this->sort;
	}

	/**
	 * @return bool Is handler active, or not.
	 */
	public function isActive()
	{
		return $this->active == 'Y';
	}

	/**
	 * @return string Title of handler.
	 */
	abstract public function getTitle();

	/**
	 * @return string Handler description.
	 */
	abstract public function getDescription();

	/**
	 * @param string $ip Ip address
	 * @param string $lang Language identifier
	 * @return Result | null
	 */
	abstract public function getDataResult($ip, $lang = '');

	/**
	 * Languages supported by handler ISO 639-1
	 * @return array
	 */
	public function getSupportedLanguages()
	{
		return array();
	}

	/**
	 * Is this handler installed and ready for using.
	 * @return bool
	 */
	public function isInstalled()
	{
		return true;
	}

	/**
	 * @return array Set of fields description for administration purposes.
	 */
	public function getConfigForAdmin()
	{
		return array();
	}

	/**
	 * @param array $postFields  Admin form posted fields during saving process.
	 * @return array Field CONFIG for saving to DB in admin edit form.
	 */
	public function createConfigField(array $postFields)
	{
		return array();
	}

	/**
	 * @return ProvidingData Geolocation information witch handler can return.
	 */
	public function getProvidingData()
	{
		return new ProvidingData();
	}
}