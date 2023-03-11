<?php
namespace Bitrix\Lists\Rest;

use Bitrix\Lists\Entity\Element;
use Bitrix\Lists\Entity\Field;
use Bitrix\Lists\Entity\Iblock;
use Bitrix\Lists\Entity\IblockType;
use Bitrix\Lists\Entity\Section;
use Bitrix\Lists\Entity\Utils;
use Bitrix\Lists\Service\Param;
use Bitrix\Lists\Security\ElementRight;
use Bitrix\Lists\Security\IblockRight;
use Bitrix\Lists\Security\Right;
use Bitrix\Lists\Security\RightParam;
use Bitrix\Lists\Security\SectionRight;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\RestException;

Loader::includeModule("rest");

class RestService extends \IRestService
{
	const SCOPE = "lists";

	public static function onRestServiceBuildDescription()
	{
		return array(
			static::SCOPE => array(
				"lists.get.iblock.type.id" => array(__CLASS__, "getIblockTypeId"),

				"lists.add" => array(__CLASS__, "addLists"),
				"lists.get" => array(__CLASS__, "getLists"),
				"lists.update" => array(__CLASS__, "updateLists"),
				"lists.delete" => array(__CLASS__, "deleteLists"),

				"lists.section.add" => array(__CLASS__, "addSection"),
				"lists.section.get" => array(__CLASS__, "getSection"),
				"lists.section.update" => array(__CLASS__, "updateSection"),
				"lists.section.delete" => array(__CLASS__, "deleteSection"),

				"lists.field.add" => array(__CLASS__, "addField"),
				"lists.field.get" => array(__CLASS__, "getFields"),
				"lists.field.update" => array(__CLASS__, "updateField"),
				"lists.field.delete" => array(__CLASS__, "deleteField"),
				"lists.field.type.get" => array(__CLASS__, "getFieldTypes"),

				"lists.element.add" => array(__CLASS__, "addElement"),
				"lists.element.get" => array(__CLASS__, "getElement"),
				"lists.element.update" => array(__CLASS__, "updateElement"),
				"lists.element.delete" => array(__CLASS__, "deleteElement"),
				"lists.element.get.file.url" => array(__CLASS__, "getFileUrl"),
			)
		);
	}

	public static function getIblockTypeId(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblockType = new IblockType($param);

		$iblockTypeId = $iblockType->getIblockTypeId();
		if ($iblockType->hasErrors())
		{
			self::throwError($iblockType->getErrors());
		}

		return $iblockTypeId;
	}

	public static function addLists(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if ($iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock already exists", Iblock::ERROR_IBLOCK_ALREADY_EXISTS);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new IblockRight($rightParam));
		$right->checkPermission(IblockRight::EDIT);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$iblockId = $iblock->add();
		if ($iblock->hasErrors())
		{
			self::throwError($iblock->getErrors());
		}

		return $iblockId;
	}

	public static function getLists(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		if (!\CLists::isListFeatureEnabled((string)$rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new IblockRight($rightParam));
		$right->checkPermission(IblockRight::READ);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$iblock = new Iblock($param);
		list ($iblocks, $queryObject) = $iblock->get(self::getNavData($n));
		if (empty($iblocks) || $iblock->hasErrors())
		{
			return [];
		}
		else
		{
			return self::setNavData(array_values($iblocks), $queryObject);
		}
	}

	public static function updateLists(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new IblockRight($rightParam));
		$right->checkPermission(IblockRight::EDIT);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		if ($iblock->update())
		{
			return true;
		}
		else
		{
			self::throwError($iblock->getErrors());
		}
	}

	public static function deleteLists(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new IblockRight($rightParam));
		$right->checkPermission(IblockRight::EDIT);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		if ($iblock->delete())
		{
			return true;
		}
		else
		{
			self::throwError($iblock->getErrors());
		}
	}

	public static function addSection(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);
		$params = $param->getParams();

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId($params["IBLOCK_SECTION_ID"]);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new SectionRight($rightParam));
		$right->checkPermission(SectionRight::ADD);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$section = new Section($param);
		$sectionId = $section->add();
		if ($section->hasErrors())
		{
			self::throwError($section->getErrors());
		}

		return $sectionId;
	}

	public static function getSection(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);
		$params = $param->getParams();

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId($params["IBLOCK_SECTION_ID"]);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new SectionRight($rightParam));
		$right->checkPermission(SectionRight::READ);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$section = new Section($param);
		list ($sections, $queryObject) = $section->get(self::getNavData($n));
		if (empty($sections) || $section->hasErrors())
		{
			return [];
		}
		else
		{
			return self::setNavData(array_values($sections), $queryObject);
		}
	}

	public static function updateSection(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);
		$params = $param->getParams();

		$section = new Section($param);
		if (!$section->isExist())
		{
			self::throwError($section->getErrors(), "Section not found", Section::ERROR_SECTION_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId($params["IBLOCK_SECTION_ID"]);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new SectionRight($rightParam));
		$right->checkPermission(SectionRight::EDIT);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		if ($section->update())
		{
			return true;
		}
		else
		{
			self::throwError($section->getErrors());
		}
	}

	public static function deleteSection(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);
		$params = $param->getParams();

		$section = new Section($param);
		if (!$section->isExist())
		{
			self::throwError($section->getErrors(), "Section not found", Section::ERROR_SECTION_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId($params["IBLOCK_SECTION_ID"]);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new SectionRight($rightParam));
		$right->checkPermission(SectionRight::DELETE);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		if ($section->delete())
		{
			return true;
		}
		else
		{
			self::throwError($section->getErrors());
		}
	}

	public static function addField(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new IblockRight($rightParam));
		$right->checkPermission(IblockRight::EDIT);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$field = new Field($param);
		$fieldId = $field->add();
		if ($field->hasErrors())
		{
			self::throwError($field->getErrors());
		}
		else
		{
			return $fieldId;
		}
	}

	public static function getFields(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new IblockRight($rightParam));
		$right->checkPermission();
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$field = new Field($param);
		return $field->get();
	}

	public static function updateField(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new IblockRight($rightParam));
		$right->checkPermission(IblockRight::EDIT);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$field = new Field($param);
		if ($field->update())
		{
			return true;
		}
		else
		{
			self::throwError($field->getErrors());
		}
	}

	public static function deleteField(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new IblockRight($rightParam));
		$right->checkPermission(IblockRight::EDIT);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$field = new Field($param);
		$field->delete();

		return true;
	}

	public static function getFieldTypes(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$right = new Right($rightParam, new IblockRight($rightParam));
		$right->checkPermission(IblockRight::EDIT);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$field = new Field($param);
		return $field->getAvailableTypes();
	}

	public static function addElement(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);
		$params = $param->getParams();

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId($params["IBLOCK_SECTION_ID"]);

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$elementRight = new ElementRight($rightParam);
		$right = new Right($rightParam, $elementRight);
		$right->checkPermission(ElementRight::ADD);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$element = new Element($param);
		if ($element->isExist())
		{
			self::throwError($element->getErrors(), "Element already exists", Element::ERROR_ELEMENT_ALREADY_EXISTS);
		}

		$elementId = $element->add();
		if ($element->hasErrors())
		{
			self::throwError($element->getErrors());
		}

		return $elementId;
	}

	public static function getElement(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);
		$params = $param->getParams();

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId(Utils::getElementId($param->getParams()));

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$elementRight = new ElementRight($rightParam);
		$param->setParam(["CAN_FULL_EDIT" => ($elementRight->canFullEdit() ? "Y" : "N")]);

		$right = new Right($rightParam, $elementRight);
		$right->checkPermission(ElementRight::READ);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$element = new Element($param);
		if (is_array($params["FILTER"]))
		{
			list($availableFields, $listCustomFields) = $element->getAvailableFields();
			$element->resultSanitizeFilter = self::getSanitizeFilter(
				$params["FILTER"], $availableFields, $listCustomFields);
		}
		list ($elements, $queryObject) = $element->get(self::getNavData($n));
		if ($elements)
		{
			return self::setNavData(array_values($elements), $queryObject);
		}
		else
		{
			return [];
		}
	}

	public static function updateElement(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId(Utils::getElementId($param->getParams()));

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$elementRight = new ElementRight($rightParam);
		$right = new Right($rightParam, $elementRight);
		$right->checkPermission(ElementRight::EDIT);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$element = new Element($param);
		if (!$element->isExist())
		{
			self::throwError($element->getErrors(), "Element not found", Element::ERROR_ELEMENT_NOT_FOUND);
		}

		if ($element->update())
		{
			return true;
		}
		else
		{
			self::throwError($element->getErrors());
		}
	}

	public static function deleteElement(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId(Utils::getElementId($param->getParams()));

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$elementRight = new ElementRight($rightParam);
		$right = new Right($rightParam, $elementRight);
		$right->checkPermission(ElementRight::EDIT);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$element = new Element($param);
		if (!$element->isExist())
		{
			self::throwError($element->getErrors(), "Element not found", Element::ERROR_ELEMENT_NOT_FOUND);
		}

		$elementRight->canDelete();
		if ($elementRight->hasErrors())
		{
			self::throwError($elementRight->getErrors());
		}

		if ($element->delete())
		{
			return true;
		}
		else
		{
			self::throwError($element->getErrors());
		}
	}

	public static function getFileUrl(array $params, $n, \CRestServer $server)
	{
		$param = new Param($params);

		$iblock = new Iblock($param);
		if (!$iblock->isExist())
		{
			self::throwError($iblock->getErrors(), "Iblock not found", Iblock::ERROR_IBLOCK_NOT_FOUND);
		}

		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId(Utils::getElementId($param->getParams()));

		if (!\CLists::isListFeatureEnabled($rightParam->getIblockTypeId()))
		{
			throw new AccessException('Available only on extended plans');
		}

		$elementRight = new ElementRight($rightParam);
		$right = new Right($rightParam, $elementRight);
		$right->checkPermission(ElementRight::READ);
		if ($right->hasErrors())
		{
			self::throwError($right->getErrors());
		}

		$element = new Element($param);
		if (!$element->isExist())
		{
			self::throwError($element->getErrors(), "Element not found", Element::ERROR_ELEMENT_NOT_FOUND);
		}

		return $element->getFileUrl();
	}

	private static function throwError(array $errors, $message = "", $code = "")
	{
		$error = end($errors);

		if ($error instanceof Error)
		{
			$message = $error->getMessage();
			if (is_array($message) && array_key_exists("message", $message))
			{
				$message = $message["message"];
			}
			else
			{
				$message = $message ?: "Unknown error";
			}
			throw new RestException($message, $error->getCode());
		}
		elseif ($error)
		{
			throw new RestException($error, RestException::ERROR_CORE);
		}
		elseif ($message && $code)
		{
			throw new RestException($message, $code);
		}

		throw new RestException("Unknown error", RestException::ERROR_NOT_FOUND);
	}

	private static function getSanitizeFilter($filter, $availableFields, $listCustomFields)
	{
		return parent::sanitizeFilter(
			$filter,
			$availableFields,
			function($field, $value) use ($listCustomFields)
			{
				if (array_key_exists($field, $listCustomFields))
				{
					$callback = $listCustomFields[$field];
					if ($callback instanceof \Closure)
					{
						return $callback($value);
					}
					else
					{
						return call_user_func_array($listCustomFields[$field], [[], ["VALUE" => $value]]);
					}
				}
				return $value;
			},
			["", "!%", ">=", "><", "!><", ">", "<=", "<", "%", "=", "*", "!"]
		);
	}

	/**
	 * @deprecated Constants are no longer used.
	 */
	const ENTITY_LISTS_CODE_PREFIX = "REST";
	const ERROR_REQUIRED_PARAMETERS_MISSING = "ERROR_REQUIRED_PARAMETERS_MISSING";
	const ERROR_IBLOCK_ALREADY_EXISTS = "ERROR_IBLOCK_ALREADY_EXISTS";
	const ERROR_SAVE_IBLOCK = "ERROR_SAVE_IBLOCK";
	const ERROR_IBLOCK_NOT_FOUND = "ERROR_IBLOCK_NOT_FOUND";
	const ERROR_SAVE_FIELD = "ERROR_SAVE_FIELD";
	const ERROR_PROPERTY_ALREADY_EXISTS = "ERROR_PROPERTY_ALREADY_EXISTS";
	const ERROR_SAVE_ELEMENT = "ERROR_SAVE_ELEMENT";
	const ERROR_DELETE_ELEMENT = "ERROR_DELETE_ELEMENT";
	const ERROR_BIZPROC = "ERROR_BIZPROC";
}