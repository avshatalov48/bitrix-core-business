<?

namespace Bitrix\Seo\LeadAds;

use Bitrix\Main\Result as BaseResult;
use Bitrix\Main\Type\DateTime;

class Result extends BaseResult
{
	/** @var  string|null $id ID. */
	protected $id;

	/** @var  DateTime $dateCreate Create date. */
	protected $dateCreate;

	/** @var int $currentIterationNumber Current iteration number. */
	protected $currentIterationNumber = 0;

	/**
	 * Sets data of the result.
	 *
	 * @param array $data Data.
	 */
	public function setData(array $data)
	{
		$this->data = array();
		$this->currentIterationNumber = 0;

		foreach ($data as $item)
		{
			if (!isset($item['NAME']) || !$item['NAME'])
			{
				continue;
			}

			if (!isset($item['VALUES']) || !is_array($item['VALUES']) || !$item['VALUES'])
			{
				continue;
			}

			$this->addFieldValues($item['NAME'], $item['VALUES']);
		}
	}

	/**
	 * Add field values.
	 *
	 * @param string $name Name.
	 * @param array $values Values.
	 */
	public function addFieldValues($name, array $values)
	{
		$this->data[] = array(
			'NAME' => $name,
			'VALUES' => $values
		);
	}

	/**
	 * Get id.
	 *
	 * @return string|null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set id.
	 *
	 * @param string $id ID.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Get create date.
	 *
	 * @return DateTime|null
	 */
	public function getDateCreate()
	{
		return $this->dateCreate;
	}

	/**
	 * Set create date.
	 *
	 * @param DateTime $dateCreate Create date.
	 */
	public function setDateCreate(DateTime $dateCreate)
	{
		$this->dateCreate = $dateCreate;
	}

	/**
	 * Fetch.
	 *
	 * @return array|null
	 */
	public function fetch()
	{
		if (!isset($this->data[$this->currentIterationNumber]))
		{
			return null;
		}

		$row = $this->data[$this->currentIterationNumber];
		$this->currentIterationNumber++;

		return $row;
	}
}