<?php
namespace Bitrix\Main\Text;

/**
 * Class DateConverterResult
 * @package Bitrix\Main\Text
 */
class DateConverterResult
{
	/**
	 * @var \Bitrix\Main\Type\DateTime|null
	 */
	private $date = null;
	
	/**
	 * @var string
	 */
	private $text = '';
	
	/**
	 * @var integer
	 */
	private $textPosition = 0;
	
	/**
	 * @var integer
	 */
	private $textLength = 0;
	
	/**
	 * @var string
	 */
	private $type = '';
	
	/**
	 * @var array
	 */
	private $metrics = Array();
	
	/**
	 * @var array
	 */
	private $metricModifier = Array();
	
	const TYPE_UNKNOWN = 'UNKNOWN';
	const TYPE_RELATIVE = 'RELATIVE';
	const TYPE_DAYOFWEEK = 'DAYOFWEEK';
	const TYPE_MODIFIER = 'MODIFIER';
	const TYPE_CALENDAR = 'CALENDAR';
	const TYPE_PARTOFDAY = 'PARTOFDAY';

	/**
	 * DateConverterResult constructor.
	 * @param \Bitrix\Main\Type\DateTime $date
	 * @param string $text
	 * @param $type
	 * @param array $metrics
	 * @param array $metricModifier
	 */
	function __construct(\Bitrix\Main\Type\DateTime $date, $matchParams, $type = self::TYPE_UNKNOWN, $metrics = Array(), $metricModifier = Array())
	{
		$this->date = $date;
		$this->type = $type;
		if (is_array($matchParams))
		{
			$this->text = $matchParams['TEXT'];
			$this->textPosition = $matchParams['POSITION'];
			$this->textLength = $matchParams['LENGTH'];
		}
		$this->metrics = $metrics;
		$this->metricModifier = $metricModifier;
	}

	/**
	 * Date from the text
	 * 
	 * @return \Bitrix\Main\Type\DateTime|null
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * The string used to build the date
	 * 
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}
	
	/**
	 * Position of string used to build the date
	 * 
	 * @return integer
	 */
	public function getTextPosition()
	{
		return $this->textPosition;
	}
	
	/**
	 * Length of string used to build the date
	 * 
	 * @return integer
	 */
	public function getTextLength()
	{
		return $this->textLength;
	}

	/**
	 * Get primary recognition type
	 * 
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get recognition metrics
	 * 
	 * @return array
	 */
	public function getMetrics()
	{
		return $this->metrics;
	}

	/**
	 * Get recognition metric modifier
	 * 
	 * @return array
	 */
	public function getMetricModifier()
	{
		return $this->metricModifier;
	}
}