<?

namespace Bitrix\Seo\Retargeting;

use Bitrix\Main\Result;

abstract class Response extends Result
{
	const TYPE_CODE = '';

	protected $id;
	protected $type;
	protected $adapter;
	protected $responseText;

	/* @var Request|null */
	protected $request;

	protected $result;
	protected $fetchIterator = 0;

	public function __construct()
	{
		parent::__construct();
		$this->type = static::TYPE_CODE;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setData(array $data)
	{
		parent::setData($data);
		$this->fetchIterator = 0;
	}

	public function setResponseText($responseText)
	{
		$this->responseText = $responseText;
	}

	public function getResponseText()
	{
		return $this->responseText;
	}

	public function fetch()
	{
		if(is_array($this->data) && !isset($this->data[0]))
		{
			if ($this->fetchIterator == 0)
			{
				$row = $this->data;
				$this->fetchIterator++;
			}
			else
			{
				return null;
			}
		}
		else if(is_array($this->data) && isset($this->data[$this->fetchIterator]))
		{
			$row = $this->data[$this->fetchIterator];
			$this->fetchIterator++;
		}
		else
		{
			return null;
		}

		if (is_array($row))
		{
			$result = array();
			foreach ($row as $k => $v)
			{
				$result[mb_strtoupper($k)] = $v;
			}

			return $result;
		}
		else
		{
			return $row;
		}
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function setRequest(Request $request)
	{
		return $this->request = $request;
	}

	/**
	 * @param $type
	 * @return static
	 */
	public static function create($type)
	{
		return Factory::create(get_called_class(), $type);
	}

	abstract public function parse($data);
}