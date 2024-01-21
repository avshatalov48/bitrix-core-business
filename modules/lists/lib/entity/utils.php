<?
namespace Bitrix\Lists\Entity;

class Utils
{
	/**
	 * Returns an iblock id by code or id.
	 *
	 * @param array $params Incoming parameters.
	 *
	 * @return int
	 */
	public static function getIblockId(array $params)
	{
		if ($params["IBLOCK_ID"])
		{
			return (int) $params["IBLOCK_ID"];
		}
		elseif ($params["IBLOCK_CODE"] ?? null)
		{
			$queryObject = \CIBlock::getList([], [
				"CHECK_PERMISSIONS" => "N",
				"=CODE" => $params["IBLOCK_CODE"]
			]);
			if ($iblock = $queryObject->fetch())
			{
				return (int) $iblock["ID"];
			}
		}

		return 0;
	}

	/**
	 * Returns an element id by code or id.
	 *
	 * @param array $params Incoming parameters.
	 *
	 * @return int
	 */
	public static function getElementId(array $params)
	{
		if ($params["ELEMENT_ID"])
		{
			return (int) $params["ELEMENT_ID"];
		}
		elseif ($params["ELEMENT_CODE"])
		{
			$queryObject = \CIBlockElement::getList([], [
				"IBLOCK_ID" => Utils::getIblockId($params),
				"CHECK_PERMISSIONS" => "N",
				"=CODE" => $params["ELEMENT_CODE"],
			], false, false, ["ID"]);
			if ($element = $queryObject->fetch())
			{
				return (int) $element["ID"];
			}
		}

		return 0;
	}

	/**
	 * Returns an section id by code or id.
	 *
	 * @param array $params Incoming parameters.
	 *
	 * @return int
	 */
	public static function getSectionId(array $params)
	{
		if ($params["SECTION_ID"])
		{
			return (int)$params["SECTION_ID"];
		}
		elseif ($params["SECTION_CODE"])
		{
			$queryObject = \CIBlockSection::getList([], [
				"CHECK_PERMISSIONS" => "N",
				"CODE" => $params["SECTION_CODE"]
			], false, false, ["ID"]);
			if ($section = $queryObject->fetch())
			{
				return (int) $section["ID"];
			}
		}

		return 0;
	}
}
