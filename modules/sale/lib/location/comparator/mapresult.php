<?
namespace Bitrix\Sale\Location\Comparator;

use Bitrix\Sale\Location\LocationTable;

/**
 * Class MapResult
 * @package Bitrix\Sale\Location\Comparator
 */

class MapResult
{
	protected $lastProcessedId = 0;
	protected $supportedCount = 0;

	protected $mapped = array();
	protected $notFound = array();
	protected $duplicated = array();

	public function setSupportedCount($count)	{ $this->supportedCount = intval($count); }
	public function setLastProcessedId($id)		{ $this->lastProcessedId = intval($id); }
	public function getLastProcessedId()		{ return $this->lastProcessedId; }
	public function getSupportedCount()			{ return $this->supportedCount; }
	public function getDuplicated() 			{ return $this->duplicated; }
	public function getNotFound() 				{ return $this->notFound; }
	public function getMapped() 				{ return $this->mapped; }

	public function addNotFound($eLocId, $eLocName)
	{
		$this->notFound[] = array($eLocId, $eLocName);
	}

	public function addDuplicated($eLocId, $eLocName, $bLocId)
	{
		$this->duplicated[] = array($eLocId, $eLocName, $bLocId);
	}

	public function addMapped($eLocId, $eLocName, $bLocId)
	{
		$this->mapped[] = array($eLocId, $eLocName, $bLocId, $this->getLocationChain($bLocId));
	}

	protected function getLocationChain($locationId)
	{
		$res = LocationTable::getList(array(
			'filter' => array(
				array(
					'LOGIC' => 'OR',
					'=CODE' => $locationId,
					'=ID' => $locationId
				),
			),
			'select' => array(
				'ID', 'CODE', 'LEFT_MARGIN', 'RIGHT_MARGIN'
			)
		));

		if(!$loc = $res->fetch())
			return '';
	
		$result = '';
		$res = LocationTable::getList(array(
			'filter' => array(
				'<=LEFT_MARGIN' => $loc['LEFT_MARGIN'],
				'>=RIGHT_MARGIN' => $loc['RIGHT_MARGIN'],
				'NAME.LANGUAGE_ID' => 'ru'
			),
			'select' => array(
				'ID', 'CODE',
				'LOC_NAME' => 'NAME.NAME'
			),
			'order' => array('LEFT_MARGIN' => 'ASC')
		));

		while($loc = $res->fetch())
			$result .= $loc['LOC_NAME'].', ';

		return $result;
	}
}