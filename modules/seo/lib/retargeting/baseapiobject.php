<?

namespace Bitrix\Seo\Retargeting;

class BaseApiObject
{
	const TYPE_CODE = '';

	/** @var Request $request */
	protected $request;

	/** @var Service $service */
	protected $service;

	protected static $listRowMap = array();

	public static function normalizeListRow(array $row)
	{
		$return = array();
		foreach(static::$listRowMap as $key => $value)
		{
			if (is_array($value))
			{
				$return[$key] = $value;
			}
			else
			{
				$return[$key] = $row[$value];
			}
		}

		return $return;
	}

	public function __construct()
	{
		$this->request = Request::create(static::TYPE_CODE);
		$this->request->setUseDirectQuery($this instanceof IRequestDirectly);
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	public function setRequest(Request $request)
	{
		$this->request = $request;
		return $this;
	}

	/**
	 * @param $type
	 * @param null $parameters
	 * @param IService|null $service
	 * @return static
	 */
	public static function create($type, $parameters = null, IService $service = null)
	{
		$instance = Factory::create(get_called_class(), $type, $parameters);
		if ($service)
		{
			$instance->setService($service);
		}

		return $instance;
	}

	public function setService(IService $service)
	{
		$this->service = $service;
		$this->request->setAuthAdapter($this->service->getAuthAdapter(static::TYPE_CODE));

		return $this;
	}
}