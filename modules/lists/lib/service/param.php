<?
namespace Bitrix\Lists\Service;

use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorableImplementation;

class Param implements Errorable
{
	use ErrorableImplementation;

	const ERROR_REQUIRED_PARAMETERS_MISSING = "ERROR_REQUIRED_PARAMETERS_MISSING";

	private $params = [];
	private $interchangeableParams = [];

	public function __construct(array $params)
	{
		$this->params = $this->changeKeyCaseRecursive($params);

		$this->interchangeableParams = [
			"IBLOCK_ID" => "IBLOCK_CODE",
			"IBLOCK_CODE" => "IBLOCK_ID",
			"ELEMENT_ID" => "ELEMENT_CODE",
			"ELEMENT_CODE" => "ELEMENT_ID",
			"SECTION_ID" => "SECTION_CODE",
			"SECTION_CODE" => "SECTION_ID"
		];

		$this->errorCollection = new ErrorCollection;
	}

	/**
	 * Checks required input parameters.
	 *
	 * @param array $requiredInputParams List of keys that are required in the input parameters.
	 */
	public function checkRequiredInputParams(array $requiredInputParams)
	{
		foreach ($requiredInputParams as $param)
		{
			if (is_array($param))
			{
				$this->checkArrayParam($param);
			}
			else
			{
				$this->checkParam($param);
			}
		}
	}

	/**
	 * Adds a parameter to an array of parameters.
	 *
	 * @param array $params
	 */
	public function setParam(array $params)
	{
		$params = $this->changeKeyCaseRecursive($params);
		$this->params = array_merge($this->params, $params);
	}

	/**
	 * Returns parameters after necessary processing.
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	private function changeKeyCaseRecursive(array $params)
	{
		return array_map(function($item) {
			if (is_array($item))
			{
				$item = $this->changeKeyCaseRecursive($item);
			}
			return $item;
		}, array_change_key_case($params, CASE_UPPER));
	}

	private function checkArrayParam(array $param)
	{
		foreach ($param as $innerParam => $listParam)
		{
			if (is_array($listParam))
			{
				foreach ($listParam as $listInnerParam)
				{
					if (!isset($this->params[$innerParam][$listInnerParam]))
					{
						if (
							!isset($this->interchangeableParams[$listInnerParam]) ||
							!isset($this->params[$this->interchangeableParams[$listInnerParam]])
						)
						{
							$this->setParamError($listInnerParam);
						}
					}
				}
			}
		}
	}

	private function checkParam($param)
	{
		if (!isset($this->params[$param]))
		{
			if (
				!isset($this->interchangeableParams[$param]) ||
				!isset($this->params[$this->interchangeableParams[$param]])
			)
			{
				$this->setParamError($param);
			}
		}
	}

	private function setParamError($param)
	{
		$this->errorCollection->setError(
			new Error(
				"Required parameter \"".$param."\" is missing", self::ERROR_REQUIRED_PARAMETERS_MISSING
			)
		);
	}
}