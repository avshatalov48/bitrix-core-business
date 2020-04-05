<?
namespace Bitrix\Sale\Delivery\Requests;

/**
 * Class RequestResult
 * @package Bitrix\Sale\Delivery\Requests
 */
class RequestResult extends Result
{
	protected $externalId = '';
	protected $internalId = 0;

	const ERROR_NOT_FOUND = 1;

	/**
	 * @return string
	 */
	public function getExternalId()
	{
		return $this->externalId;
	}

	/**
	 * @param string $externalId
	 */
	public function setExternalId($externalId)
	{
		$this->externalId = $externalId;
	}

	/**
	 * @return string
	 */
	public function getInternalId()
	{
		return $this->internalId;
	}

	/**
	 * @param string $internalId
	 */
	public function setInternalId($internalId)
	{
		$this->internalId = $internalId;
	}

}