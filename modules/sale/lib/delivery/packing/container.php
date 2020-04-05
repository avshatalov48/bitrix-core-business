<?
namespace Bitrix\Sale\Delivery\Packing;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\SystemException;

/**
 * Class Container
 * Contains Boxes
 * @package Bitrix\Sale\Delivery\Packing
 */
class Container
{
	/** @var Box[] Boxes list.*/
	protected $boxes = array();
	protected $availableVertexes = array(
		array (0,0,0)
	);

	/**
	 * @param int[] $boxDims Point move box to
	 * @return bool
	 */
	public function addBox(array $boxDims)
	{
		$box = new Box($boxDims);

		foreach($this->availableVertexes as $vId => $v)
		{
			$box->move($v);

			if(!$this->isVertexSuitable($box))
				continue;

			$this->boxes[] = $box;
			unset($this->availableVertexes[$vId]);
			$this->refreshVertexesAfterBoxAdd($box);
			return true;
		}

		return false;
	}

	public function addBoxToVertex(array $boxDims, $vertexIdx)
	{
		if(!isset($this->availableVertexes[$vertexIdx]))
			throw new SystemException('No such vertex');

		$box = new Box($boxDims);
		$box->move($this->availableVertexes[$vertexIdx]);

		if(!$this->isVertexSuitable($box))
			return false;

		$this->boxes[] = $box;
		unset($this->availableVertexes[$vertexIdx]);
		$this->refreshVertexesAfterBoxAdd($box);
		return true;
	}

	public function extractLastBox()
	{
		/** @var Box $box */
		$box = array_pop($this->boxes);
		$this->availableVertexes[] = $box->getVMin();
		usort($this->availableVertexes, __CLASS__.'::distanceCompare');
		$box->move(array(0,0,0));
		return $box;
	}

	public function insertBox(Box $box, $vertexId = 0)
	{
		$box->move($this->availableVertexes[$vertexId]);

		if($this->isVertexSuitable($box))
		{
			$this->boxes[] = $box;
			unset($this->availableVertexes[$vertexId]);
			$this->refreshVertexesAfterBoxAdd($box);
			return true;
		}

		return false;
	}

	protected function refreshVertexesAfterBoxAdd(Box $box)
	{
		$bVert = $box->getVertexes();
		$this->availableVertexes[] = array($bVert[1][0], $bVert[0][1], $bVert[0][2]);
		$this->availableVertexes[] = array($bVert[0][0], $bVert[1][1], $bVert[0][2]);
		$this->availableVertexes[] = array($bVert[0][0], $bVert[0][1], $bVert[1][2]);
		$this->availableVertexes[] = array($bVert[1][0], $bVert[1][1], $bVert[0][2]);
		usort($this->availableVertexes, __CLASS__.'::distanceCompare');
	}

	/**
	 * @return Box[]
	 */
	public function getBoxes()
	{
		$result = array();

		foreach($this->boxes as $box)
			$result[] = clone $box;

		return $result;
	}

	public function getAvailableVertexes()
	{
		return $this->availableVertexes;
	}

	/**
	 * Check if box can be added
	 * @param Box $newBox
	 * @return bool
	 */
	protected function isVertexSuitable(Box $newBox)
	{
		$result = true;

		foreach($this->boxes as $existBox)
		{
			if($this->isBoxesIntersects($existBox, $newBox))
			{
				return false;
				break;
			}
		}

		return $result;
	}

	/**
	 * Checks if boxes intersect
	 * @param Box $box1
	 * @param Box $box2
	 * @return bool
	 */
	protected static function isBoxesIntersects(Box $box1, Box $box2)
	{
		$result = true;
		$v1 = $box1->getVertexes();
		$v2 = $box2->getVertexes();

		for($i = 0; $i < 3; $i++)
			$result = $result && self::isEdgesIntersects($v1[0][$i], $v1[1][$i], $v2[0][$i], $v2[1][$i]);

		return $result;
	}

	/**
	 * Are edges intersect
	 * @param int $min1
	 * @param int $max1
	 * @param int $min2
	 * @param int $max2
	 * @return bool
	 */
	protected static function isEdgesIntersects($min1, $max1, $min2, $max2)
	{
		return !($min1 >= $max2 || $max1 <= $min2);
	}

	/**
	 * @return array Dimensions of space filled by boxes
	 */
	public function getFilledDimensions()
	{
		if(empty($this->boxes))
			return(array(0,0,0));

		$maxX = $maxY = $maxZ = 0;

		foreach($this->boxes as $box)
		{
			$v = $box->getVertexes();

			if($maxX < $v[1][0])
				$maxX = $v[1][0];

			if($maxY < $v[1][1])
				$maxY = $v[1][1];

			if($maxZ < $v[1][2])
				$maxZ = $v[1][2];
		}

		return array($maxX, $maxY, $maxZ);
	}

	/**
	 * @return int Volume of space filled by boxes
	 */
	public function getFilledVolume()
	{
		$dims = $this->getFilledDimensions();
		return $dims[0]*$dims[1]*$dims[2];
	}

	/**
	 * @param int[] $p1
	 * @param int[] $p2
	 * @return int
	 * @internal
	 */
	public static function distanceCompare(array $p1, array $p2)
	{
		$zero = array(0,0,0);
		$d1 = self::calculateDistance($p1, $zero);
		$d2 = self::calculateDistance($p2, $zero);

		if($d1 == $d2)
			return 0;

		return ($d1 < $d2) ? -1 : 1;
	}

	/**
	 * Calculate distance between two points
	 * @param array $p1
	 * @param array $p2
	 * @return float
	 */
	public static function calculateDistance(array $p1, array $p2)
	{
		return sqrt(pow($p1[0]-$p2[0], 2) + pow($p1[1]-$p2[1], 2) + pow($p1[2]-$p2[2], 2));
	}
}