<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seoproxy
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Seo\WebHook\Payload;

/**
 * Class Payload
 *
 * @package Bitrix\Seo\WebHook\Payload
 */
class Batch
{
	/** @var Item[] $items Items.  */
	protected $items = [];

	/** @var  string $requestId Request ID. */
	protected $requestId;

	/** @var  string $source Source. */
	protected $source;

	/** @var  string $code Code. */
	protected $code;

	/** @var  string $externalId External ID. */
	protected $externalId;

	/**
	 * Set lead items array.
	 *
	 * @param array $items Items.
	 * @return $this
	 */
	public function setLeadItemsArray(array $items)
	{
		foreach ($items as $item)
		{
			$this->addItem(new LeadItem($item));
		}

		return $this;
	}

	/**
	 * Set array.
	 *
	 * @param array $data
	 * @return $this
	 */
	public function setArray(array $data)
	{
		$this->setRequestId($data['requestId'])
			->setLeadItemsArray($data['items'])
			->setCode($data['code'])
			->setExternalId($data['externalId'])
			->setSource($data['source']);

		return $this;
	}

	public function getArray()
	{
		return [
			'code' => $this->getCode(),
			'externalId' => $this->getExternalId(),
			'requestId' => $this->getRequestId(),
			'items' => array_map(
				function ($item)
				{
					/** @var Item $item */
					return $item->getData();
				},
				$this->getItems()
			),
			'source' => $this->getSource(),
		];
	}

	/**
	 * Get item instances.
	 *
	 * @return Item[]|LeadItem[]
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Add item.
	 *
	 * @param Item $item Item instance.
	 * @return $this
	 */
	public function addItem(Item $item)
	{
		$this->items[] = $item;
		return $this;
	}

	/**
	 * Get request ID.
	 *
	 * @return string
	 */
	public function getRequestId()
	{
		return $this->requestId;
	}

	/**
	 * Set request ID.
	 *
	 * @param string $requestId Request ID.
	 * @return $this
	 */
	public function setRequestId($requestId)
	{
		$this->requestId = $requestId;
		return $this;
	}

	/**
	 * Get source.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * Set request ID.
	 *
	 * @param string $source Source.
	 * @return $this
	 */
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Set code.
	 *
	 * @param string $code Code.
	 * @return $this
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}

	/**
	 * Get external ID.
	 *
	 * @return string
	 */
	public function getExternalId()
	{
		return $this->externalId;
	}

	/**
	 * Set external ID.
	 *
	 * @param string $externalId External ID.
	 * @return $this
	 */
	public function setExternalId($externalId)
	{
		$this->externalId = $externalId;
		return $this;
	}
}

