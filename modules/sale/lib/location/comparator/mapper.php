<?
namespace Bitrix\Sale\Location\Comparator;

use Bitrix\Sale\Delivery\ExternalLocationMap;

abstract class Mapper extends ExternalLocationMap
{
	protected $collectDuplicated = false;
	protected $collectNotFound = false;
	protected $collectMapped = false;

	/**
	 * @param string $stage Identities current stage.
	 * @param string $step Idenifies current step of the stage.
	 * @param int $progress Operation progress (%) 0 - 100.
	 * @param int $timeout Seconds for stepping.
	 * @return array
	 */
	abstract public function map($stage, $step = '', $progress = 0, $timeout = 0);

	public function setCollectDuplicated($collect)	{ $this->collectDuplicated = (bool)$collect; }
	public function setCollectNotFound($collect)	{ $this->collectNotFound = (bool)$collect; }
	public function setCollectMapped($collect)		{ $this->collectMapped = (bool)$collect; }
}
