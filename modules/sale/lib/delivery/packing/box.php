<?
namespace Bitrix\Sale\Delivery\Packing;

/**
 * Class Box
 * @package Bitrix\Sale\Delivery\Packing
 */
class Box
{
	/** @var int[] Vertex closest to origin */
	protected $vMin = array(0, 0, 0);
	/** @var int[] Vertex most far from origin */
	protected $vMax = array(0, 0, 0);

	/**
	 * Box constructor.
	 * @param int[] $sizes Box sizes array($sizeX, $sizeY, $sizeZ).
	 */
	public function __construct(array $sizes)
	{
		$this->vMax = $sizes;
	}

	/**
	 * @return int[]
	 */
	public function getVMin()
	{
		return $this->vMin;
	}

	public function getSizes()
	{
		return array(
			$this->vMax[0] - $this->vMin[0],
			$this->vMax[1] - $this->vMin[1],
			$this->vMax[2] - $this->vMin[2]
		);
	}

	/**
	 * @return int[]
	 */
	public function getVMax()
	{
		return $this->vMax;
	}

	/**
	 * @return array[]
	 */
	public function getVertexes()
	{
		return array($this->vMin, $this->vMax);
	}

	/**
	 * @param int[] $vertex
	 */
	public function setVMin(array $vertex)
	{
		$this->vMin = $vertex;
	}

	/**
	 * @param int[] $vertex
	 */
	public function setVMax(array $vertex)
	{
		$this->vMax = $vertex;
	}

	/**
	 * Set nearest to origin vertex to given point.
	 * @param int[] $point Point coordinates.
	 */
	public function move(array $point)
	{
		for($i = 0; $i < 3; $i++)
		{
			$this->vMax[$i] = $this->vMax[$i] - $this->vMin[$i] + $point[$i];
			$this->vMin[$i] = $point[$i];
		}
	}

	/**
	 * Rotates box around given axe pi/2 degree
	 * @param bool[] $axes Axes for rotation
	 */
	public function rotate(array $axes) // array (0,1,0)
	{
		if($axes[0])
		{
			$this->vMin = array($this->vMin[0], $this->vMin[2], $this->vMin[1]);
			$this->vMax = array($this->vMax[0], $this->vMax[2], $this->vMax[1]);
		}

		if($axes[1])
		{
			$this->vMin = array($this->vMin[2], $this->vMin[1], $this->vMin[0]);
			$this->vMax = array($this->vMax[2], $this->vMax[1], $this->vMax[0]);
		}

		if($axes[2])
		{
			$this->vMin = array($this->vMin[2], $this->vMin[0], $this->vMin[1]);
			$this->vMax = array($this->vMax[2], $this->vMax[0], $this->vMax[1]);
		}
	}
}