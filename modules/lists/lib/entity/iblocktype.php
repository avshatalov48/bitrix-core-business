<?
namespace Bitrix\Lists\Entity;

use Bitrix\Lists\Service\Param;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;

class IblockType implements Errorable
{
	use ErrorableImplementation;

	private $param;
	private $params = [];

	public function __construct(Param $param)
	{
		$this->param = $param;
		$this->params = $param->getParams();

		$this->errorCollection = new ErrorCollection;
	}

	/**
	 * Returns the iblock type id by the iblock.
	 *
	 * @return string|null
	 */
	public function getIblockTypeId()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_CODE", "IBLOCK_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return null;
		}

		$filter = ["CHECK_PERMISSIONS" => "Y"];

		if (empty($this->params["IBLOCK_ID"]))
		{
			$filter["=CODE"] = $this->params["IBLOCK_CODE"];
		}
		if (empty($this->params["IBLOCK_CODE"]))
		{
			$filter["=ID"] = $this->params["IBLOCK_ID"];
		}

		$queryObject = \CIBlock::getList([], $filter);
		if ($iblock = $queryObject->fetch())
		{
			return ($iblock["IBLOCK_TYPE_ID"] ? $iblock["IBLOCK_TYPE_ID"] : null);
		}
		else
		{
			return null;
		}
	}
}