<?php

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Rest\Exceptions\ArgumentException;
use Bitrix\Rest\RestException;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\Exceptions\ArgumentNullException;

class CBitrixRestEntity extends IRestService
{
	const ENTITY_IBLOCK_CODE_PREFIX = 'APP';

	const ERROR_ENTITY_ALREADY_EXISTS = 'ERROR_ENTITY_ALREADY_EXISTS';
	const ERROR_ENTITY_NOT_FOUND = 'ERROR_ENTITY_NOT_FOUND';
	const ERROR_SECTION_NOT_FOUND = 'ERROR_SECTION_NOT_FOUND';
	const ERROR_ITEM_NOT_FOUND = 'ERROR_ITEM_NOT_FOUND';
	const ERROR_PROPERTY_NOT_FOUND = 'ERROR_PROPERTY_NOT_FOUND';
	const ERROR_PROPERTY_ALREADY_EXISTS = 'ERROR_PROPERTY_ALREADY_EXISTS';
	const ERROR_UNSUPPORTED_PROPERTY_TYPE = 'ERROR_UNSUPPORTED_PROPERTY_TYPE';
	const ERROR_UNSUPPORTED_PROPERTY_TYPE_CHANGE = 'ERROR_UNSUPPORTED_PROPERTY_TYPE_CHANGE';

	private static $arAllowedOperations = array('', '!', '<', '<=', '>', '>=', '><', '!><', '?', '=', '!=', '%', '!%', '');

	public static function OnRestServiceBuildDescription()
	{
		if(CModule::IncludeModule('iblock'))
		{
			return array(
				'entity' => array(
					'entity.add' => array(__CLASS__, 'entityAdd'),
					'entity.get' => array(__CLASS__, 'entityGet'),
					'entity.update' => array(__CLASS__, 'entityUpdate'),
					'entity.delete' => array(__CLASS__, 'entityDelete'),
					'entity.rights' => array(__CLASS__, 'entityRights'),

					'entity.section.add' => array(__CLASS__, 'entitySectionAdd'),
					'entity.section.get' => array(__CLASS__, 'entitySectionGet'),
					'entity.section.update' => array(__CLASS__, 'entitySectionUpdate'),
					'entity.section.delete' => array(__CLASS__, 'entitySectionDelete'),

					'entity.item.add' => array(__CLASS__, 'entityItemAdd'),
					'entity.item.get' => array(__CLASS__, 'entityItemGet'),
					'entity.item.update' => array(__CLASS__, 'entityItemUpdate'),
					'entity.item.delete' => array(__CLASS__, 'entityItemDelete'),

					'entity.item.property.add' => array(__CLASS__, 'entityItemPropertyAdd'),
					'entity.item.property.get' => array(__CLASS__, 'entityItemPropertyGet'),
					'entity.item.property.update' => array(__CLASS__, 'entityItemPropertyUpdate'),
					'entity.item.property.delete' => array(__CLASS__, 'entityItemPropertyDelete'),
				),
			);
		}
	}

	public static function entityAdd($params, $n, $server)
	{
		global $USER;

		if(self::checkParams($params))
		{
			if(!self::checkEntity($params['ENTITY'], $server))
			{
				if(!isset($params['ACCESS']) || !is_array($params['ACCESS']))
				{
					$params['ACCESS'] = array();
				}

				$params['ACCESS']['U'.$USER->GetID()] = 'X';

				$arIBlockFields = array(
					'IBLOCK_TYPE_ID' => self::getIBlockType(),
					"NAME" => trim($params['NAME']),
					"CODE" => self::getEntityIBlockCode($params['ENTITY'], $server),
					"ACTIVE" => "Y",
					'WORKFLOW' => 'N',
					'INDEX_SECTION' => 'N',
					'INDEX_ELEMENT' => 'N',
					'VERSION' => 1,
					'RIGHTS_MODE' => 'E',
					'RIGHTS' => self::checkRights($params['ACCESS']),
					'SITE_ID' => CSite::GetDefSite()
				);

				$ib = new \CIBlock();

				$conn = Application::getConnection();
				$conn->startTransaction();
				$error = '';
				try
				{
					$ID = $ib->Add($arIBlockFields);
					if (!$ID)
					{
						$error = $ib->getLastError();
					}
				}
				catch (SqlQueryException)
				{
					$error = 'Internal error adding entity. Try adding again.';
				}
				if ($error === '')
				{
					$conn->commitTransaction();

					return true;
				}
				else
				{
					$conn->rollbackTransaction();
					throw new RestException($error, RestException::ERROR_CORE);
				}
			}
			else
			{
				throw new RestException('Entity already exists', self::ERROR_ENTITY_ALREADY_EXISTS);
			}
		}
	}

	public static function entityGet($params, $n, $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);
		if(isset($params['ENTITY']))
		{
			if(self::checkParams($params))
			{
				$arRes = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
				if($arRes)
				{
					return array(
						'ID' => $arRes['ID'],
						'IBLOCK_TYPE_ID' => $arRes['IBLOCK_TYPE_ID'],
						'ENTITY' => $params['ENTITY'],
						'NAME' => $arRes['NAME'],
					);
				}
				else
				{
					throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
				}
			}
		}
		else
		{
			$res = array();
			$dbRes = self::getIBlocks($server);
			while($arRes = $dbRes->Fetch())
			{
				$entity = self::parseEntity($arRes['CODE'], $server);
				if($entity)
				{
					$res[] = array(
						'ID' => $arRes['ID'],
						'IBLOCK_TYPE_ID' => $arRes['IBLOCK_TYPE_ID'],
						'ENTITY' => $entity,
						'NAME' => $arRes['NAME'],
					);
				}
			}

			return $res;
		}
	}

	public static function entityRights($params, $n, $server)
	{
		global $USER;

		if(self::checkParams($params))
		{
			$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
			if($arIBlock)
			{
				$obIBlockRights = new \CIBlockRights($arIBlock['ID']);

				if(isset($params['ACCESS']) && is_array($params['ACCESS']) && count($params['ACCESS']) > 0)
				{
					if(\CIBlockRights::UserHasRightTo($arIBlock['ID'], $arIBlock['ID'], 'iblock_edit'))
					{
						$params['ACCESS']['U'.$USER->GetID()] = 'X';
						$arRights = self::checkRights($params['ACCESS']);

						$obIBlockRights->SetRights($arRights);
						$obIBlockRights->Recalculate();
					}
					else
					{
						throw new AccessException();
					}
				}

				$arRights = $obIBlockRights->GetRights();
				$res = array();

				foreach($arRights as $arRight)
				{
					$res[$arRight['GROUP_CODE']] = \CIBlockRights::TaskToLetter($arRight['TASK_ID']);
				}

				return $res;
			}
		}
	}

	public static function entityUpdate($params, $n, $server)
	{
		global $USER;

		if(self::checkParams($params))
		{
			$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
			if($arIBlock)
			{
				if(\CIBlockRights::UserHasRightTo($arIBlock['ID'], $arIBlock['ID'], 'iblock_edit'))
				{
					$recalcRights = false;

					$arIBlockFields = array();
					if(isset($params['NAME']))
					{
						$arIBlockFields["NAME"] = trim($params['NAME']);
					}

					if(isset($params['ENTITY_NEW']) && $params['ENTITY_NEW'] != $params['ENTITY'])
					{
						if (self::checkEntity($params['ENTITY_NEW'], $server))
						{
							throw new RestException('Entity already exists', self::ERROR_ENTITY_ALREADY_EXISTS);
						}

						$arIBlockFields["CODE"] = self::getEntityIBlockCode($params['ENTITY_NEW'], $server);
					}

					if(isset($params['ACCESS']) && is_array($params['ACCESS']) && count($params['ACCESS']) > 0)
					{
						$recalcRights = true;
						$params['ACCESS']['U'.$USER->GetID()] = 'X';
						$arIBlockFields['RIGHTS'] = self::checkRights($params['ACCESS']);
					}

					if(count($arIBlockFields) > 0)
					{
						$ib = new \CIBlock();

						$conn = Application::getConnection();
						$conn->startTransaction();
						$error = '';
						try
						{
							if (!$ib->Update($arIBlock['ID'], $arIBlockFields))
							{
								$error = $ib->getLastError();
							}
							if ($error === '' && $recalcRights)
							{
								$obIBlockRights = new CIBlockRights($arIBlock['ID']);
								$obIBlockRights->Recalculate();
								unset($obIBlockRights);
							}
						}
						catch (SqlQueryException)
						{
							$error = 'Internal error updating entity. Try updating again.';
						}
						if ($error === '')
						{
							$conn->commitTransaction();
						}
						else
						{
							$conn->rollbackTransaction();
							throw new RestException($error, RestException::ERROR_CORE);
						}
					}
					return true;
				}
				else
				{
					throw new AccessException();
				}
			}
			else
			{
				throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
			}
		}
	}

	public static function entityDelete($params, $n, $server)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if(self::checkParams($params))
		{
			$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
			if($arIBlock)
			{
				if(\CIBlockRights::UserHasRightTo($arIBlock['ID'], $arIBlock['ID'], 'iblock_edit'))
				{
					$conn = Application::getConnection();
					$conn->startTransaction();
					$error = '';
					try
					{
						if (!CIBlock::Delete($arIBlock['ID']))
						{
							$ex = $APPLICATION->GetException();
							$error =
								$ex
									? $ex->GetString()
									: 'Unable to delete iblock'
							;
							unset(
								$ex,
							);
						}
					}
					catch (SqlQueryException)
					{
						$error = 'Internal error deleting entity. Try deleting again.';
					}
					if ($error === '')
					{
						$conn->commitTransaction();
					}
					else
					{
						$conn->rollbackTransaction();
						throw new RestException($error, RestException::ERROR_CORE);
					}

					return true;
				}
				else
				{
					throw new AccessException();
				}
			}
			else
			{
				throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
			}
		}
	}

	public static function entitySectionGet($params, $n, $server)
	{
		if(self::checkSectionParams($params))
		{
			$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
			if($arIBlock)
			{
				$arSort = array('ID' => 'ASC');
				$arFilter = array();

				if(isset($params['SORT']) && is_array($params['SORT']))
				{
					$arSort = array_change_key_case($params['SORT'], CASE_UPPER);
				}

				if(isset($params['FILTER']) && is_array($params['FILTER']))
				{
					$arFilter = array_change_key_case($params['FILTER'], CASE_UPPER);
				}

				$arFilter = self::checkSectionFilter($arFilter);
				$arFilter['IBLOCK_ID'] = $arIBlock['ID'];
				$arFilter['CHECK_PERMISSIONS'] = 'Y';

				$dbRes = \CIBlockSection::GetList(
					$arSort,
					$arFilter,
					false,
					array('ID', 'IBLOCK_ID', 'CODE', 'TIMESTAMP_X', 'DATE_CREATE', 'CREATED_BY', 'MODIFIED_BY', 'ACTIVE', 'SORT', 'NAME', 'PICTURE', 'DETAIL_PICTURE', 'DESCRIPTION', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID'),
					self::getNavData($n)
				);

				$result = array();
				while ($res = $dbRes->Fetch(false, false))
				{
					$res['ENTITY'] = $params['ENTITY'];
					$res['SECTION'] = $res['IBLOCK_SECTION_ID'];

					$res['TIMESTAMP_X'] = CRestUtil::ConvertDateTime($res['TIMESTAMP_X']);
					$res['DATE_CREATE'] = CRestUtil::ConvertDateTime($res['DATE_CREATE']);

					if($res['PICTURE'] > 0)
						$res['PICTURE'] = self::getFile($res['PICTURE']);

					if($res['DETAIL_PICTURE'] > 0)
						$res['DETAIL_PICTURE'] = self::getFile($res['DETAIL_PICTURE']);

					unset($res['IBLOCK_ID']);
					unset($res['IBLOCK_SECTION_ID']);
					unset($res['DETAIL_TEXT_TYPE']);
					unset($res['DESCRIPTION_TYPE']);
					$result[] = $res;
				}

				return self::setNavData($result, $dbRes);
			}
			else
			{
				throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
			}
		}
	}

	public static function entitySectionAdd($params, $n, $server)
	{
		if(self::checkSectionParams($params))
		{
			$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
			if($arIBlock)
			{
				if(\CIBlockRights::UserHasRightTo($arIBlock['ID'], $arIBlock['ID'], 'section_edit'))
				{
					$arSectionFields = self::prepareSection($params, $arIBlock, $server);

					$ib = new \CIBlockSection();

					$conn = Application::getConnection();
					$conn->startTransaction();
					$ID = false;
					$error = '';
					try
					{
						$ID = $ib->Add($arSectionFields);
						if (!$ID)
						{
							$error = $ib->getLastError();
						}
					}
					catch (SqlQueryException)
					{
						$error = 'Internal error adding entity section. Try adding again.';
					}
					if ($ID)
					{
						$conn->commitTransaction();

						return $ID;
					}
					else
					{
						$conn->rollbackTransaction();
						throw new RestException($error, RestException::ERROR_CORE);
					}
				}
				else
				{
					throw new AccessException();
				}
			}
			else
			{
				throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
			}
		}
	}

	public static function entitySectionUpdate($params, $n, $server)
	{
		if(self::checkSectionParams($params))
		{
			$params['ID'] = intval($params['ID']);
			if($params['ID'] <= 0)
			{
				throw new ArgumentNullException("ID");
			}
			else
			{
				$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
				if($arIBlock)
				{
					if(\CIBlockRights::UserHasRightTo($arIBlock['ID'], $arIBlock['ID'], 'section_edit'))
					{
						$dbRes = \CIBlockSection::GetList(array(), array(
							'ID' => $params['ID'],
							'IBLOCK_ID' => $arIBlock['ID']
						), false, array('ID'));
						$arRes = $dbRes->Fetch();

						if($arRes)
						{
							$arSectionFields = self::prepareSection($params, $arIBlock, $server);

							if(count($arSectionFields) > 0)
							{
								$ib = new \CIBlockSection();

								$conn = Application::getConnection();
								$conn->startTransaction();
								$error = '';
								try
								{
									if (!$ib->Update($arRes['ID'], $arSectionFields))
									{
										$error = $ib->getLastError();
									}
								}
								catch (SqlQueryException)
								{
									$error = 'Internal error updating entity section. Try updating again.';
								}
								if ($error === '')
								{
									$conn->commitTransaction();
								}
								else
								{
									$conn->rollbackTransaction();
									throw new RestException($error, RestException::ERROR_CORE);
								}
							}

							return true;
						}
						else
						{
							throw new RestException('Section not found', self::ERROR_SECTION_NOT_FOUND);
						}
					}
					else
					{
						throw new AccessException();
					}
				}
				else
				{
					throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
				}
			}
		}
	}

	public static function entitySectionDelete($params, $n, $server)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if(self::checkSectionParams($params))
		{
			$params['ID'] = intval($params['ID']);
			if($params['ID'] <= 0)
			{
				throw new ArgumentNullException("ID");
			}
			else
			{
				$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
				if($arIBlock)
				{
					if(\CIBlockRights::UserHasRightTo($arIBlock['ID'], $arIBlock['ID'], 'section_delete'))
					{
						$dbRes = \CIBlockSection::GetList(array(), array(
							'ID' => $params['ID'],
							'IBLOCK_ID' => $arIBlock['ID']
						), false, array('ID'));
						$arRes = $dbRes->Fetch();
						if($arRes)
						{
							$conn = Application::getConnection();
							$conn->startTransaction();
							$error = '';
							try
							{
								if (!\CIBlockSection::Delete($params['ID']))
								{
									$ex = $APPLICATION->GetException();
									$error =
										$ex
											? $ex->GetString()
											: 'Unable to delete section'
									;
									unset(
										$ex,
									);
								}
							}
							catch (SqlQueryException)
							{
								$error = 'Internal error deleting entity section. Try deleting again.';
							}
							if ($error === '')
							{
								$conn->commitTransaction();
							}
							else
							{
								$conn->rollbackTransaction();
								throw new RestException($error, RestException::ERROR_CORE);
							}

							return true;
						}
						else
						{
							throw new RestException('Section not found', self::ERROR_SECTION_NOT_FOUND);
						}
					}
					else
					{
						throw new AccessException();
					}
				}
				else
				{
					throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
				}
			}
		}
	}

	public static function entityItemGet($params, $n, $server)
	{
		if(!self::checkItemParams($params))
		{
			return;
		}
		$iBlockCode = self::getEntityIBlockCode($params['ENTITY'], $server);

		$iBlockId = \Bitrix\Iblock\IblockTable::resolveIdByCode($iBlockCode);
		if(is_null($iBlockId))
		{
			throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
		}
		$arFields = array();

		$dbRes = self::getItemProperties($params['ENTITY'], $server);
		while ($arField = $dbRes->Fetch())
		{
			$arFields[$arField['CODE']] = $arField['ID'];
		}

		$arSort = array('ID' => 'ASC');
		$arFilter = array();

		if(isset($params['SORT']) && is_array($params['SORT']))
		{
			$arSort = array_change_key_case($params['SORT'], CASE_UPPER);
		}

		if(isset($params['FILTER']) && is_array($params['FILTER']))
		{
			$arFilter = array_change_key_case($params['FILTER'], CASE_UPPER);
		}

		$arFilter = self::checkFilter($arFilter);
		$arFilter['IBLOCK_ID'] = $iBlockId;
		$arFilter['CHECK_PERMISSIONS'] = 'Y';

		$dbRes = \CIBlockElement::GetList(
			$arSort,
			$arFilter,
			false,
			self::getNavData($n),
			array('ID', 'IBLOCK_ID', 'TIMESTAMP_X', 'MODIFIED_BY', 'DATE_CREATE', 'CREATED_BY', 'ACTIVE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', 'SORT', 'NAME', 'PREVIEW_PICTURE', 'PREVIEW_TEXT', 'DETAIL_PICTURE', 'DETAIL_TEXT', 'CODE', 'IBLOCK_SECTION_ID')
		);

		$result = array();
		while ($el = $dbRes->GetNextElement(false))
		{
			$res = $el->GetFields();
			$arProps = $el->GetProperties();

			foreach($res as $key => $value)
			{
				if(array_key_exists('~'.$key, $res))
				{
					$res[$key] = $res['~'.$key];
					unset($res['~'.$key]);
				}
			}

			$res['ENTITY'] = $params['ENTITY'];
			$res['SECTION'] = $res['IBLOCK_SECTION_ID'];

			if(!empty($arProps))
			{
				$res['PROPERTY_VALUES'] = array();
				foreach($arProps as $prop)
				{
					if($prop['PROPERTY_TYPE'] == 'F')
					{
						if($prop['VALUE'] > 0)
						{
							$prop['~VALUE'] = self::getFile($prop['~VALUE']);
						}
					}

					$res['PROPERTY_VALUES'][$prop['CODE']] = $prop['~VALUE'];
				}
			}

			$res['DATE_ACTIVE_FROM'] = CRestUtil::ConvertDateTime($res['DATE_ACTIVE_FROM']);
			$res['DATE_ACTIVE_TO'] = CRestUtil::ConvertDateTime($res['DATE_ACTIVE_TO']);
			$res['TIMESTAMP_X'] = CRestUtil::ConvertDateTime($res['TIMESTAMP_X']);
			$res['DATE_CREATE'] = CRestUtil::ConvertDateTime($res['DATE_CREATE']);

			if($res['PREVIEW_PICTURE'] > 0)
				$res['PREVIEW_PICTURE'] = self::getFile($res['PREVIEW_PICTURE']);

			if($res['DETAIL_PICTURE'] > 0)
				$res['DETAIL_PICTURE'] = self::getFile($res['DETAIL_PICTURE']);

			unset($res['IBLOCK_ID']);
			unset($res['IBLOCK_SECTION_ID']);
			unset($res['DETAIL_TEXT_TYPE']);
			unset($res['PREVIEW_TEXT_TYPE']);
			unset($res['ACTIVE_FROM']);
			unset($res['ACTIVE_TO']);

			$result[] = $res;
		}

		return self::setNavData($result, $dbRes);
	}

	public static function entityItemAdd($params, $n, $server)
	{
		if(self::checkItemParams($params))
		{
			$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
			if($arIBlock)
			{
				if(\CIBlockRights::UserHasRightTo($arIBlock['ID'], $arIBlock['ID'], 'element_edit'))
				{
					$arItemFields = self::prepareItem($params, $arIBlock, $server);

					$ib = new \CIBlockElement();

					$conn = Application::getConnection();
					$conn->startTransaction();
					$ID = false;
					$error = '';
					try
					{
						$ID = $ib->Add($arItemFields);
						if (!$ID)
						{
							$error = $ib->getLastError();
						}
					}
					catch (SqlQueryException)
					{
						$error = 'Internal error adding entity item. Try adding again.';
					}
					if ($error === '')
					{
						$conn->commitTransaction();

						return $ID;
					}
					else
					{
						$conn->rollbackTransaction();
						throw new RestException($error, RestException::ERROR_CORE);
					}
				}
				else
				{
					throw new AccessException();
				}
			}
			else
			{
				throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
			}
		}
	}

	public static function entityItemUpdate($params, $n, $server)
	{
		if(self::checkItemParams($params))
		{
			$params['ID'] = intval($params['ID']);
			if($params['ID'] <= 0)
			{
				throw new ArgumentNullException("ID");
			}
			else
			{
				$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
				if($arIBlock)
				{
					if(\CIBlockRights::UserHasRightTo($arIBlock['ID'], $arIBlock['ID'], 'element_edit'))
					{
						$dbRes = \CIBlockElement::GetList(array(), array(
							'ID' => $params['ID'],
							'IBLOCK_ID' => $arIBlock['ID']
						), false, false, array('ID'));
						$arRes = $dbRes->Fetch();

						if($arRes)
						{
							$arItemFields = self::prepareItem($params, $arIBlock, $server);

							if(count($arItemFields) > 0)
							{
								$ib = new \CIBlockElement();
								$PROPS = false;

								if(isset($arItemFields['PROPERTY_VALUES']))
								{
									$PROPS = $arItemFields['PROPERTY_VALUES'];
									unset($arItemFields['PROPERTY_VALUES']);
								}

								if(isset($arItemFields["PREVIEW_PICTURE"]) && $arItemFields["PREVIEW_PICTURE"] == false)
								{
									$arItemFields["PREVIEW_PICTURE"] = array("del" => "Y");
								}

								if(isset($arItemFields["DETAIL_PICTURE"]) && $arItemFields["DETAIL_PICTURE"] == false)
								{
									$arItemFields["DETAIL_PICTURE"] = array("del" => "Y");
								}

								$conn = Application::getConnection();
								$conn->startTransaction();
								$error = '';
								try
								{
									$res = $ib->Update($arRes['ID'], $arItemFields);
									if ($res)
									{
										if ($PROPS)
										{
											\CIBlockElement::SetPropertyValuesEx($arRes['ID'], $arIBlock['ID'], $PROPS);
										}
									}
									else
									{
										$error = $ib->getLastError();
									}
								}
								catch (SqlQueryException)
								{
									$error = 'Internal error updating entity item. Try updating again.';
								}
								if ($error === '')
								{
									$conn->commitTransaction();
								}
								else
								{
									$conn->rollbackTransaction();
									throw new RestException($error, RestException::ERROR_CORE);
								}
							}

							return true;
						}
						else
						{
							throw new RestException('Item not found', self::ERROR_ITEM_NOT_FOUND);
						}
					}
					else
					{
						throw new AccessException();
					}
				}
				else
				{
					throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
				}
			}
		}
	}

	public static function entityItemDelete($params, $n, $server)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if(self::checkItemParams($params))
		{
			$params['ID'] = intval($params['ID']);
			if($params['ID'] <= 0)
			{
				throw new ArgumentNullException("ID");
			}
			else
			{
				$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
				if($arIBlock)
				{
					if(\CIBlockRights::UserHasRightTo($arIBlock['ID'], $arIBlock['ID'], 'element_delete'))
					{
						$dbRes = \CIBlockElement::GetList(
							[],
							[
								'ID' => $params['ID'],
								'IBLOCK_ID' => $arIBlock['ID']
							],
							false,
							false,
							[
								'ID',
							]
						);
						$arRes = $dbRes->Fetch();
						if($arRes)
						{
							$conn = Application::getConnection();
							$conn->startTransaction();
							$error = '';
							try
							{
								if (!\CIBlockElement::Delete($params['ID']))
								{
									$ex = $APPLICATION->GetException();
									$error =
										$ex
											? $ex->GetString()
											: 'Unable to delete item'
									;
									unset(
										$ex,
									);
								}
							}
							catch (SqlQueryException)
							{
								$error = 'Internal error deleting entity item. Try deleting again.';
							}
							if ($error === '')
							{
								$conn->commitTransaction();
							}
							else
							{
								$conn->rollbackTransaction();
								throw new RestException($error, RestException::ERROR_CORE);
							}

							return true;
						}
						else
						{
							throw new RestException('Item not found', self::ERROR_ITEM_NOT_FOUND);
						}
					}
					else
					{
						throw new AccessException();
					}
				}
				else
				{
					throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
				}
			}
		}
	}

	public static function entityItemPropertyGet($params, $n, $server)
	{
		if(self::checkItemPropertyParams($params))
		{
			if(self::checkEntity($params['ENTITY'], $server))
			{
				if(isset($params['PROPERTY']) && $params['PROPERTY'] != '')
				{
					$arField = self::getItemProperty($params['PROPERTY'], $params['ENTITY'], $server);
					if(is_array($arField))
					{
						return array(
							'PROPERTY' => $arField['CODE'],
							'NAME' => $arField['NAME'],
							'TYPE' => $arField['PROPERTY_TYPE'],
							'SORT' => $arField['SORT'],
						);
					}
					else
					{
						throw new RestException('Property not found', self::ERROR_PROPERTY_NOT_FOUND);
					}
				}
				else
				{
					$result = array();
					$dbRes = self::getItemProperties($params['ENTITY'], $server);
					while ($arField = $dbRes->Fetch())
					{
						$result[] = array(
							'PROPERTY' => $arField['CODE'],
							'NAME' => $arField['NAME'],
							'TYPE' => $arField['PROPERTY_TYPE'],
							'SORT' => $arField['SORT'],
						);
					}
					return $result;
				}
			}
			else
			{
				throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
			}
		}
	}

	public static function entityItemPropertyAdd($params, $n, $server)
	{
		if(self::checkItemPropertyParams($params))
		{
			if(!self::checkItemProperty($params['PROPERTY'], $params['ENTITY'], $server))
			{
				if(!isset($params['PROPERTY']))
				{
					throw new ArgumentNullException('PROPERTY');
				}

				$arIBlock = self::getIBlock(self::getEntityIBlockCode($params['ENTITY'], $server));
				if($arIBlock)
				{
					if(\CIBlockRights::UserHasRightTo($arIBlock['ID'], $arIBlock['ID'], 'iblock_edit'))
					{
						$arFields = array(
							"IBLOCK_ID" => $arIBlock['ID'],
							"CODE" => $params['PROPERTY'],
							"NAME" => $params['NAME'],
							"ACTIVE" => "Y",
							"PROPERTY_TYPE" => $params['TYPE'],
							"SORT" => $params['SORT'],
						);

						if($params['TYPE'] == 'L')
						{
							throw new \Bitrix\Main\NotSupportedException(self::ERROR_UNSUPPORTED_PROPERTY_TYPE);
						}

						$ibp = new \CIBlockProperty;

						$conn = Application::getConnection();
						$conn->startTransaction();
						$propId = false;
						$error = '';
						try
						{
							$propId = $ibp->Add($arFields);
							if (!$propId)
							{
								$error = $ibp->getLastError();
							}
						}
						catch (SqlQueryException)
						{
							$error = 'Internal error adding entity property. Try adding again.';
						}
						if ($error === '')
						{
							$conn->commitTransaction();
						}
						else
						{
							$conn->rollbackTransaction();
							throw new RestException($error, RestException::ERROR_CORE);
						}

						return true;
					}
					else
					{
						throw new AccessException();
					}
				}
				else
				{
					throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
				}
			}
			else
			{
				throw new RestException ('Property already exists', self::ERROR_PROPERTY_ALREADY_EXISTS);
			}
		}
	}

	public static function entityItemPropertyUpdate($params, $n, $server)
	{
		if(self::checkItemPropertyParams($params))
		{
			if(self::checkEntity($params['ENTITY'], $server))
			{
				$arField = self::getItemProperty($params['PROPERTY'], $params['ENTITY'], $server);
				if($arField)
				{
					if(\CIBlockRights::UserHasRightTo($arField['IBLOCK_ID'], $arField['IBLOCK_ID'], 'iblock_edit'))
					{
						$arPropFields = array();

						if(isset($params['PROPERTY_NEW']) && $params['PROPERTY_NEW'] != $params['PROPERTY'])
						{
							if (self::checkItemProperty($params['PROPERTY_NEW'], $params['ENTITY'], $server))
							{
								throw new RestException ('Property '.$params['PROPERTY_NEW'].' already exists', self::ERROR_PROPERTY_ALREADY_EXISTS);
							}

							$arPropFields["CODE"] = $params['PROPERTY_NEW'];
						}

						if(isset($params['NAME']))
						{
							$arPropFields['NAME'] = trim($params['NAME']);
						}

						if(isset($params['SORT']))
						{
							$arPropFields['SORT'] = trim($params['SORT']);
						}

						if(isset($params['TYPE']) && $arField['PROPERTY_TYPE'] != $params['TYPE'])
						{
							if($params['TYPE'] == 'F')
							{
								throw new \Bitrix\Main\ArgumentException('Cannot change property type to File', "TYPE");
							}
							// elseif($params['TYPE'] == 'L')
							// {
							// 	throw new \Bitrix\Main\NotSupportedException(self::ERROR_UNSUPPORTED_PROPERTY_TYPE);
							// }
							else
							{
								$arPropFields['PROPERTY_TYPE'] = $params['TYPE'];
							}
						}

						$ibp = new \CIBlockProperty;

						$conn = Application::getConnection();
						$conn->startTransaction();
						$error = '';
						try
						{
							if (!$ibp->Update($arField['ID'], $arPropFields))
							{
								$error = $ibp->getLastError();
							}
						}
						catch (SqlQueryException)
						{
							$error = 'Internal error updating entity property. Try updating again.';
						}
						if ($error === '')
						{
							$conn->commitTransaction();
						}
						else
						{
							$conn->rollbackTransaction();
							throw new RestException($error, RestException::ERROR_CORE);
						}

						return true;
					}
					else
					{
						throw new AccessException();
					}
				}
				else
				{
					throw new RestException('Property not found', self::ERROR_PROPERTY_NOT_FOUND);
				}
			}
			else
			{
				throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
			}
		}
	}

	public static function entityItemPropertyDelete($params, $n, $server)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if(self::checkItemPropertyParams($params))
		{
			if(self::checkEntity($params['ENTITY'], $server))
			{
				$arField = self::getItemProperty($params['PROPERTY'], $params['ENTITY'], $server);
				if($arField)
				{
					if(\CIBlockRights::UserHasRightTo($arField['IBLOCK_ID'], $arField['IBLOCK_ID'], 'iblock_edit'))
					{
						$conn = Application::getConnection();
						$conn->startTransaction();
						$error = '';
						try
						{
							if (!CIBlockProperty::Delete($arField['ID']))
							{
								$ex = $APPLICATION->GetException();
								$error =
									$ex
										? $ex->GetString()
										: 'Unable to delete item'
								;
								unset(
									$ex,
								);
							}
						}
						catch (SqlQueryException)
						{
							$error = 'Internal error deleting entity property. Try deleting again.';
						}
						if ($error === '')
						{
							$conn->commitTransaction();
						}
						else
						{
							$conn->rollbackTransaction();
							throw new RestException($error, RestException::ERROR_CORE);
						}

						return true;
					}
					else
					{
						throw new AccessException();
					}
				}
				else
				{
					throw new RestException('Property not found', self::ERROR_PROPERTY_NOT_FOUND);
				}
			}
			else
			{
				throw new RestException('Entity not found', self::ERROR_ENTITY_NOT_FOUND);
			}
		}
	}

	public static function Clean($appId)
	{
		if(CModule::IncludeModule('iblock'))
		{
			$dbRes = \CIBlock::GetList(array(), array(
				'=TYPE' => self::getIBlockType(),
				'CODE' => self::ENTITY_IBLOCK_CODE_PREFIX."_".$appId.'%'
			));
			while ($arRes = $dbRes->Fetch())
			{
				\CIBlock::Delete($arRes['ID']);
			}
		}
	}

	protected static function checkIblockType()
	{
		$iblockType = COption::GetOptionString("rest", "entity_iblock_type", "rest_entity");

		$arIBlockTypeData = array(
			'ID' => $iblockType,
			'SECTIONS' => 'Y',
			'IN_RSS' => 'N',
		);

		$obBlocktype = new \CIBlockType();
		if($obBlocktype->Add($arIBlockTypeData))
		{
			COption::SetOptionString("rest", "entity_iblock_type", $iblockType);

			$dbRes = \CIBlock::GetList(array(), array('TYPE' => $iblockType));

			$taskId = \CIBlockRights::LetterToTask('X');

			while ($arIBlock = $dbRes->Fetch())
			{
				$obIBlockRights = new \CIBlockRights($arIBlock['ID']);
				$arRights = $obIBlockRights->GetRights();

				foreach($arRights as $key => $arRight)
				{
					if($arRight['GROUP_CODE'] == 'U1')
					{
						unset($arRights[$key]);
					}
				}

				$arRights['n0'] = array(
					'GROUP_CODE' => 'U1',
					'TASK_ID' => $taskId,
					'DO_CLEAN' => 'N',
				);

				$obIBlockRights->SetRights($arRights);
			}
		}
	}

	protected static function getIBlock($code, $bSkipCheck = false)
	{
		$dbRes = \CIBlock::GetList(array(), array(
			'=TYPE' => self::getIBlockType(),
			'=CODE' => $code,
		));

		$arRes = $dbRes->Fetch();
		if(!$arRes && !$bSkipCheck)
		{
			self::checkIblockType();
			return self::getIBlock($code, true);
		}

		return $arRes;
	}

	protected static function getIBlocks($server)
	{
		return \CIBlock::GetList(array(), array(
			'=TYPE' => self::getIBlockType(),
			'CODE' => self::getEntityIBlockCode('%', $server)
		));
	}

	protected static function getItemProperty($property, $entity, $server)
	{
		$dbRes = \CIBlockProperty::GetByID($property, false, self::getEntityIBlockCode($entity, $server));
		return $dbRes->Fetch();
	}

	protected static function getItemProperties($entity, $server)
	{
		return \CIBlockProperty::GetList(
			array('SORT' => 'ASC'),
			array('IBLOCK_CODE' => self::getEntityIBlockCode($entity, $server))
		);
	}

	protected static function checkParams(&$params)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if(isset($params['ENTITY']))
		{
			$params['ENTITY'] = preg_replace('/[^a-zA-Z0-9_]/i', '', trim(strval($params['ENTITY'])));

			if($params['ENTITY'] == '')
			{
				throw new ArgumentNullException("ENTITY");
			}

			if(isset($params['ENTITY_NEW']))
			{
				$params['ENTITY_NEW'] = preg_replace('/[^a-zA-Z0-9_]/i', '', trim(strval($params['ENTITY_NEW'])));
			}
		}
		else
		{
			throw new ArgumentNullException("ENTITY");
		}

		return true;
	}

	protected static function checkItemParams(&$params)
	{
		if(self::checkParams($params))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	protected static function checkSectionParams(&$params)
	{
		if(self::checkParams($params))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	protected static function checkItemPropertyParams(&$params)
	{
		if(self::checkParams($params))
		{
			if(isset($params['PROPERTY']))
			{
				$params['PROPERTY'] = preg_replace('/[^a-zA-Z0-9_]/i', '', trim(strval($params['PROPERTY'])));
			}

			if(isset($params['PROPERTY_NEW']))
			{
				$params['PROPERTY_NEW'] = preg_replace('/[^a-zA-Z0-9_]/i', '', trim(strval($params['PROPERTY_NEW'])));
			}

			if(isset($params['TYPE']))
			{
				$params['TYPE'] = mb_strtoupper($params['TYPE']);
				if(!in_array($params['TYPE'], array('S', 'N', 'F'/*, 'L'*/)))
				{
					throw new ArgumentException('Wrong entity item property type', "TYPE");
				}
			}

			if(isset($params['SORT']))
			{
				$params['SORT'] = intval($params['SORT']);
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	protected static function checkEntity($entity, $server)
	{
		if(self::getIBlock(self::getEntityIBlockCode($entity, $server)))
			return true;
		else
			return false;
	}

	protected static function checkItemProperty($property, $entity, $server)
	{
		if(self::getItemProperty($property, $entity, $server))
			return true;
		else
			return false;
	}

	protected static function parseEntity($iblock, \CRestServer $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		$str = self::ENTITY_IBLOCK_CODE_PREFIX."_".$server->getClientId()."_";

		if(mb_substr($iblock, 0, mb_strlen($str)) === $str)
		{
			return mb_substr($iblock, mb_strlen($str));
		}
		else
		{
			return false;
		}
	}

	protected static function getEntityIBlockCode($entity, \CRestServer $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		return self::ENTITY_IBLOCK_CODE_PREFIX."_".$server->getClientId()."_".$entity;
	}

	protected static function getIBlockType()
	{
		return COption::GetOptionString("rest", "entity_iblock_type", "rest_entity");
	}

	protected static function prepareItem($params, $arIBlock, $server)
	{
		$arItemFields = array();

		foreach($params as $key => $param)
		{
			switch($key)
			{
				case 'ENTITY':
				case 'IBLOCK_ID':
				case 'ID':

				case 'RIGHTS':
				case 'PREVIEW_TEXT_TYPE':
				case 'DETAIL_TEXT_TYPE':
				case 'CREATED_BY':
				case 'MODIFIED_BY':
				case 'DATE_CREATE':
				case 'SHOW_COUNTER':
				case 'SHOW_COUNTER_START':
				case 'TAGS':

				break;

				case 'PROPERTY_VALUES':
					$PROPS = array();
					$dbRes = self::getItemProperties($params['ENTITY'], $server);
					while ($arField = $dbRes->Fetch())
					{
						if(isset($param[$arField['CODE']]))
						{
							if($arField['PROPERTY_TYPE'] == 'F')
							{
								$PROPS[$arField['CODE']] = CRestUtil::saveFile($param[$arField['CODE']]);
							}
							else
							{
								$PROPS[$arField['CODE']] = $param[$arField['CODE']];
							}
						}
					}
					$arItemFields['PROPERTY_VALUES'] = $PROPS;
				break;

				case 'DATE_ACTIVE_FROM':
				case 'DATE_ACTIVE_TO':
					$arItemFields[$key] = CRestUtil::unConvertDateTime($param, true);
				break;

				case 'PREVIEW_PICTURE':
				case 'DETAIL_PICTURE':
					$arItemFields[$key] = CRestUtil::saveFile($param);
				break;

				case 'SECTION':
					$arItemFields['IBLOCK_SECTION_ID'] = $param;
				break;

				default:
					if(!preg_match('/[^a-zA-Z0-9_]/', $key))
						$arItemFields[$key] = $param;
				break;
			}
		}

		$arItemFields['IBLOCK_ID'] = $arIBlock['ID'];

		return $arItemFields;
	}

	protected static function prepareSection($params, $arIBlock, $server)
	{
		$arSectionFields = array();

		foreach($params as $key => $param)
		{
			switch($key)
			{
				case 'ENTITY':
				case 'IBLOCK_ID':
				case 'ID':

				case 'RIGHTS':
				case 'DESCRIPTION_TYPE':
				case 'CREATED_BY':
				case 'MODIFIED_BY':
				case 'DATE_CREATE':
				case 'XML_ID':
				case 'EXTERNAL_ID':
				case 'TIMESTAMP_X_UNIX':

				break;

				case 'PICTURE':
				case 'DETAIL_PICTURE':
					$arSectionFields[$key] = CRestUtil::saveFile($param);
				break;

				case 'SECTION':
					$arSectionFields['IBLOCK_SECTION_ID'] = $param;
				break;

				default:
					if(!preg_match('/[^a-zA-Z0-9_]/', $key))
						$arSectionFields[$key] = $param;
				break;
			}
		}

		$arSectionFields['IBLOCK_ID'] = $arIBlock['ID'];

		return $arSectionFields;
	}

	protected static function checkFilter($arFilter, $bChangeLogic = true)
	{
		if(is_array($arFilter))
		{
			$arFilter = array_change_key_case($arFilter, CASE_UPPER);

			foreach ($arFilter as $key => $value)
			{
				if(is_numeric($key) && is_array($value))
				{
					$arFilter[$key] = self::checkFilter($value, false);
				}
				elseif(preg_match('/^([^a-zA-Z]*)(.*)/', $key, $matches))
				{
					$operation = $matches[1];
					$field = $matches[2];

					if(!in_array($operation, self::$arAllowedOperations))
					{
						unset($arFilter[$key]);
					}
					else
					{
						switch($field)
						{
							case 'DATE_ACTIVE_FROM':
							case 'DATE_ACTIVE_TO':
							case 'TIMESTAMP_X':
							case 'DATE_CREATE':
								$arFilter[$key] = CRestUtil::unConvertDateTime($value, true);
							break;

							case 'SECTION':
								$arFilter[$operation.'IBLOCK_SECTION_ID'] = $value;
								unset($arFilter[$key]);
							break;

							case 'IBLOCK_ID':
							case 'CHECK_PERMISSIONS':
								unset($arFilter[$key]);
							break;

							case 'LOGIC':
								if($bChangeLogic)
									unset($arFilter[$key]);
							break;

							default:

							break;
						}
					}
				}
				else
				{
					unset($arFilter[$key]);
				}
			}
		}

		return $arFilter;
	}

	protected static function checkSectionFilter($arFilter, $bChangeLogic = true)
	{
		if(is_array($arFilter))
		{
			$arFilter = array_change_key_case($arFilter, CASE_UPPER);

			foreach ($arFilter as $key => $value)
			{
				if(preg_match('/^([^a-zA-Z]*)(.*)/', $key, $matches))
				{
					$operation = $matches[1];
					$field = $matches[2];

					if(!in_array($operation, self::$arAllowedOperations))
					{
						unset($arFilter[$key]);
					}
					else
					{
						switch($field)
						{
							case 'TIMESTAMP_X':
							case 'DATE_CREATE':
								$arFilter[$key] = CRestUtil::unConvertDateTime($value, true);
							break;

							case 'SECTION':
								$arFilter[$operation.'IBLOCK_SECTION_ID'] = $value;
								unset($arFilter[$key]);
							break;

							case 'IBLOCK_ID':
							case 'CHECK_PERMISSIONS':
								unset($arFilter[$key]);
							break;

							default:
							break;
						}
					}
				}
				else
				{
					unset($arFilter[$key]);
				}
			}
		}

		return $arFilter;
	}

	protected static function checkRights($arRights)
	{
		$res = array();
		$i = 0;

		foreach($arRights as $rightCode => $access)
		{
			if($access == 'W' || $access == 'R' || $access == 'X')
			{
				$res['n'.($i++)] = array(
					'GROUP_CODE' => $rightCode,
					'TASK_ID' => \CIBlockRights::LetterToTask($access),
					'DO_CLEAN' => 'N',
				);
			}
		}

		return $res;
	}

	protected static function getFile($fileId)
	{
		$arFile = CFile::GetFileArray($fileId);
		if(is_array($arFile))
		{
			return \CHTTP::URN2URI($arFile['SRC']);
		}
		else
		{
			return '';
		}
	}
}
