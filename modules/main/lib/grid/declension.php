<?

namespace Bitrix\Main\Grid;


/**
 * Class Declension
 * @package Bitrix\Main\Grid
 */
class Declension
{
	/**
	 * @var string
	 */
	public $oneItem;

	/**
	 * @var string
	 */
	public $fourItem;

	/**
	 * @var string
	 */
	public $fiveItem;


	/**
	 * Declension constructor.
	 *
	 * @param string $one
	 * @param string $four
	 * @param string $five
	 */
	public function __construct($one = "", $four = "", $five = "")
	{
		$this->oneItem = $one;
		$this->fourItem = $four;
		$this->fiveItem = $five;
	}


	/**
	 * Gets declension
	 * @param number|string $number
	 * @return string
	 */
	public function get($number)
	{
		$result = $this->fiveItem;
		$number = $number % 100;

		if ($number >= 11 && $number <= 19)
		{
			$result = $this->fiveItem;
		}
		else
		{
			$number = $number % 10;

			if ($number === 1)
			{
				$result = $this->oneItem;
			}

			if ($number >= 2 && $number <= 4)
			{
				$result = $this->fourItem;
			}
		}

		return $result;
	}
}