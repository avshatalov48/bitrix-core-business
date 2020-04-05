<?

namespace Bitrix\Seo\Retargeting;

use \Bitrix\Main\Error;
use Bitrix\Main\SystemException;
use \Bitrix\Seo\Retargeting\Internals\ServiceLogTable;

/**
 * Class Request
 * @package Bitrix\Seo\Retargeting
 */
abstract class Request
{
	const TYPE_CODE = '';

	/** @var AuthAdapter */
	protected $adapter;

	/** @var AdsHttpClient */
	protected $client;

	/** @var Response $response Response. */
	protected $response;

	/** @var string $type Type. */
	protected $type;

	/** @var  mixed $endpoint Endpoint. */
	protected $endpoint;

	/**
	 * Request constructor.
	 */
	public function __construct()
	{
		$this->type = static::TYPE_CODE;

		$options = array(
			'socketTimeout' => 5
		);
		$this->client = new AdsHttpClient($options);
	}

	/**
	 * Get auth adapter.
	 *
	 * @return AuthAdapter
	 */
	public function getAuthAdapter()
	{
		return $this->adapter;
	}

	/**
	 * Set auth adapter.
	 *
	 * @param AuthAdapter $adapter Auth adapter.
	 * @return $this
	 */
	public function setAuthAdapter(AuthAdapter $adapter)
	{
		$this->adapter = $adapter;
		return $this;
	}

	/**
	 * Get response.
	 *
	 * @return mixed
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Get client.
	 *
	 * @return AdsHttpClient
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Set client.
	 *
	 * @param AdsHttpClient $client Http client.
	 * @return $this
	 */
	public function setClient(AdsHttpClient $client)
	{
		$this->client = $client;
		return $this;
	}

	/**
	 * Get endpoint.
	 *
	 * @return mixed
	 */
	public function getEndpoint()
	{
		return $this->endpoint;
	}

	/**
	 * Create instance.
	 *
	 * @param string $type Type.
	 * @return static
	 */
	public static function create($type)
	{
		return Factory::create(get_called_class(), $type);
	}

	/**
	 * Send request.
	 *
	 * @param array $params Parameters.
	 * @return Response
	 * @throws SystemException
	 */
	public function send(array $params = array())
	{
		if (!$this->adapter)
		{
			throw new SystemException('AuthAdapter not applied.');
		}

		//if (!$this->client)
		{
			$options = array(
				'socketTimeout' => 5
			);
			$this->client = new AdsHttpClient($options);
		}

		$data = $this->query($params);
		$response = Response::create($this->type);
		$response->setRequest($this);
		$response->setResponseText($data);
		try
		{
			$response->parse($data);
		}
		catch (\Exception $exception)
		{
			$response->addError(new Error($exception->getMessage(), $exception->getCode()));
		}

		if ($response->getErrorCollection()->count() > 0)
		{
			$errors = $response->getErrors();
			foreach ($errors as $error)
			{
				if (!$error->getMessage())
				{
					continue;
				}

				ServiceLogTable::add(array(
					'GROUP_ID' => 'retargeting',
					'TYPE' => static::TYPE_CODE,
					'CODE' => $error->getCode(),
					'MESSAGE' => $error->getMessage()
				));
			}
		}


		return $response;
	}

	/**
	 * Query.
	 *
	 * @param array $params Parameters.
	 * @return mixed
	 */
	abstract public function query(array $params = array());
}