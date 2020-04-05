<?php
namespace Bitrix\Lists\Entity;

use Bitrix\Lists\Service\Param;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\ErrorableImplementation;

class Iblock implements Controllable, Errorable
{
	use ErrorableImplementation;

	const ERROR_ADD_IBLOCK = "ERROR_ADD_IBLOCK";
	const ERROR_UPDATE_IBLOCK = "ERROR_UPDATE_IBLOCK";
	const ERROR_IBLOCK_NOT_FOUND = "ERROR_IBLOCK_NOT_FOUND";
	const ERROR_IBLOCK_ALREADY_EXISTS = "ERROR_IBLOCK_ALREADY_EXISTS";

	private $param;
	private $params = [];
	private $fieldList = [];
	private $messageList = [];

	private $iblockId;

	public function __construct(Param $param)
	{
		$this->param = $param;
		$this->params = $param->getParams();

		$this->fieldList = ["NAME", "ACTIVE", "DESCRIPTION", "SORT", "BIZPROC", "PICTURE"];
		$this->messageList = ["ELEMENTS_NAME", "ELEMENT_NAME", "ELEMENT_ADD", "ELEMENT_EDIT", "ELEMENT_DELETE",
			"SECTIONS_NAME", "SECTION_ADD", "SECTION_EDIT", "SECTION_DELETE"];

		$this->iblockId = Utils::getIblockId($this->params);

		$this->errorCollection = new ErrorCollection;
	}

	/**
	 * Checks whether an iblock exists.
	 *
	 * @return bool
	 */
	public function isExist()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_TYPE_ID", "IBLOCK_CODE"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return false;
		}

		$filter = [
			"ID" => $this->params["IBLOCK_ID"] ? $this->params["IBLOCK_ID"] : "",
			"CODE" => $this->params["IBLOCK_CODE"] ? $this->params["IBLOCK_CODE"] : "",
			"CHECK_PERMISSIONS" => "N",
		];
		$queryObject = \CIBlock::getList([], $filter);

		return (bool) $queryObject->fetch();
	}

	/**
	 * Adds an iblock.
	 *
	 * @return int|bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function add()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_TYPE_ID", "IBLOCK_CODE", ["FIELDS" => ["NAME"]]]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return false;
		}

		$iblockObject = new \CIBlock();
		$result = $iblockObject->add($this->getFields());

		if ($result)
		{
			if (!empty($this->params["SOCNET_GROUP_ID"]) && Loader::includeModule("socialnetwork"))
			{
				\CSocNetGroup::setLastActivity($this->params["SOCNET_GROUP_ID"]);
			}
			return (int)$result;
		}
		else
		{
			if ($iblockObject->LAST_ERROR)
			{
				$this->errorCollection->setError(new Error($iblockObject->LAST_ERROR, self::ERROR_ADD_IBLOCK));
			}
			else
			{
				$this->errorCollection->setError(new Error("Unknown error", self::ERROR_ADD_IBLOCK));
			}

			return false;
		}
	}

	/**
	 * Returns a list of iblock data.
	 *
	 * @param array $navData Navigation data.
	 *
	 * @return array
	 */
	public function get(array $navData = [])
	{
		$this->param->checkRequiredInputParams(["IBLOCK_TYPE_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return [];
		}

		$iblocks = [];

		$filter = $this->getFilter();

		$order = $this->getOrder();

		$queryObject = \CIBlock::getList($order, $filter);
		$queryObject->NavStart($navData);
		while ($result = $queryObject->fetch())
		{
			$iblocks[] = $result;
		}

		return [$iblocks, $queryObject];
	}

	/**
	 * Updates an iblock.
	 *
	 * @return bool
	 */
	public function update()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_TYPE_ID", "IBLOCK_CODE", "IBLOCK_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return false;
		}

		$iblockObject = new \CIBlock;
		if ($iblockObject->update($this->iblockId, $this->getFields()))
		{
			return true;
		}
		else
		{
			if ($iblockObject->LAST_ERROR)
			{
				$this->errorCollection->setError(new Error($iblockObject->LAST_ERROR, self::ERROR_UPDATE_IBLOCK));
			}
			else
			{
				$this->errorCollection->setError(new Error("Unknown error", self::ERROR_UPDATE_IBLOCK));
			}

			return false;
		}
	}

	/**
	 * Deletes an iblock.
	 *
	 * @return bool
	 */
	public function delete()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_TYPE_ID", "IBLOCK_CODE", "IBLOCK_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return false;
		}

		return \CIBlock::delete($this->iblockId);
	}

	private function getFilter()
	{
		$filter = [
			"TYPE" => $this->params["IBLOCK_TYPE_ID"],
			"ID" => ($this->params["IBLOCK_ID"] ? $this->params["IBLOCK_ID"] : ""),
			"CODE" => ($this->params["IBLOCK_CODE"] ? $this->params["IBLOCK_CODE"] : ""),
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => ($this->params["SOCNET_GROUP_ID"]) ? "N" : "Y",
		];
		if ($this->params["SOCNET_GROUP_ID"])
		{
			$filter["=SOCNET_GROUP_ID"] = $this->params["SOCNET_GROUP_ID"];
		}
		else
		{
			$filter["SITE_ID"] = SITE_ID;
		}

		return $filter;
	}

	private function getOrder()
	{
		$order = ["ID" => "ASC"];
		if (is_array($this->params["IBLOCK_ORDER"]))
		{
			$fieldList = ["ID", "IBLOCK_TYPE", "NAME", "ACTIVE", "CODE", "SORT", "ELEMENT_CNT", "TIMESTAMP_X"];
			$orderList = ["asc", "desc"];
			foreach ($this->params["IBLOCK_ORDER"] as $fieldId => $orderParam)
			{
				if (!in_array($orderParam, $orderList) || !in_array($fieldId, $fieldList))
				{
					continue;
				}
				$order[$fieldId] = $orderParam;
			}
		}

		return $order;
	}

	private function getFields()
	{
		$fields = [
			"IBLOCK_TYPE_ID" => $this->params["IBLOCK_TYPE_ID"],
			"WORKFLOW" => "N",
			"RIGHTS_MODE" => "E",
			"SITE_ID" => \CSite::getDefSite(),
		];
		if ($this->params["IBLOCK_CODE"])
		{
			$fields["CODE"] = $this->params["IBLOCK_CODE"];
		}
		if ($this->params["SOCNET_GROUP_ID"])
		{
			$fields["SOCNET_GROUP_ID"] = $this->params["SOCNET_GROUP_ID"];
		}

		foreach ($this->params["FIELDS"] as $fieldId => $fieldValue)
		{
			if (!in_array($fieldId, $this->fieldList))
			{
				continue;
			}

			if ($fieldId == "PICTURE")
			{
				$fieldValue = \CRestUtil::saveFile($fieldValue);
			}

			$fields[$fieldId] = $fieldValue;
		}

		foreach ($this->params["MESSAGES"] as $messageId => $messageValue)
		{
			if (!in_array($messageId, $this->messageList))
			{
				continue;
			}

			$fields[$messageId] = $messageValue;
		}

		$fields["RIGHTS"] = $this->getCurrentRights();
		foreach ($this->getInputRight() as $rightId => $right)
		{
			$fields["RIGHTS"][$rightId] = $right;
		}

		return $fields;
	}

	private function getCurrentRights()
	{
		$result = [];

		$iblockRights = new \CIBlockRights($this->iblockId);
		$rights = $iblockRights->getRights();
		foreach ($rights as $rightId => $right)
		{
			$result[$rightId] = $right;
		}

		return $result;
	}

	private function getInputRight()
	{
		$result = [];

		if (empty($this->params["RIGHTS"]) || !is_array($this->params["RIGHTS"]))
		{
			global $USER;
			$this->params["RIGHTS"] = [];
			$this->params["RIGHTS"]["U".$USER->getID()] = "X";
		}

		$count = 0;
		foreach ($this->params["RIGHTS"] as $rightCode => $access)
		{
			$result["n".($count++)] = [
				"GROUP_CODE" => $rightCode,
				"TASK_ID" => \CIBlockRights::letterToTask($access),
				"DO_CLEAN" => "N",
			];
		}

		return $result;
	}
}