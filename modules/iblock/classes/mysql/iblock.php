<?php

use Bitrix\Main\Application;
use Bitrix\Main\DB\MysqlCommonConnection;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Fields;

class CIBlock extends CAllIBlock
{
	///////////////////////////////////////////////////////////////////
	// List of blocks
	///////////////////////////////////////////////////////////////////
	public static function GetList($arOrder=Array("SORT"=>"ASC"), $arFilter=Array(), $bIncCnt = false)
	{
		global $DB, $USER;

		$strSqlSearch = "";
		$bAddSites = false;
		foreach($arFilter as $key => $val)
		{
			$res = CIBlock::MkOperationFilter($key);
			$key = mb_strtoupper($res["FIELD"]);
			$cOperationType = $res["OPERATION"];

			switch($key)
			{
			case "ACTIVE":
				$sql = CIBlock::FilterCreate("B.ACTIVE", $val, "string_equal", $cOperationType);
				break;
			case "LID":
			case "SITE_ID":
				$sql = CIBlock::FilterCreate("BS.SITE_ID", $val, "string_equal", $cOperationType);
				if($sql <> '')
				{
					$bAddSites = true;
				}
				break;
			case "NAME":
			case "CODE":
			case "XML_ID":
			case "PROPERTY_INDEX":
				$sql = CIBlock::FilterCreate("B.".$key, $val, "string", $cOperationType);
				break;
			case "EXTERNAL_ID":
				$sql = CIBlock::FilterCreate("B.XML_ID", $val, "string", $cOperationType);
				break;
			case "TYPE":
				$sql = CIBlock::FilterCreate("B.IBLOCK_TYPE_ID", $val, "string", $cOperationType);
				break;
			case "ID":
			case "VERSION":
			case "SOCNET_GROUP_ID":
				$sql = CIBlock::FilterCreate("B.".$key, $val, "number", $cOperationType);
				break;
			default:
				$sql = "";
				break;
			}

			if($sql <> '')
			{
				$strSqlSearch .= " AND  (".$sql.") ";
			}
		}

		$bCheckPermissions =
			!array_key_exists("CHECK_PERMISSIONS", $arFilter)
			|| $arFilter["CHECK_PERMISSIONS"] !== "N"
			|| array_key_exists("OPERATION", $arFilter)
		;
		$bIsAdmin = is_object($USER) && $USER->IsAdmin();
		$permissionsBy = null;
		if ($bCheckPermissions && isset($arFilter['PERMISSIONS_BY']))
		{
			$permissionsBy = (int)$arFilter['PERMISSIONS_BY'];
			if ($permissionsBy < 0)
				$permissionsBy = null;
		}
		if($bCheckPermissions && ($permissionsBy !== null || !$bIsAdmin))
		{
			$min_permission =
				isset($arFilter['MIN_PERMISSION']) && strlen($arFilter['MIN_PERMISSION']) === 1
					? $arFilter['MIN_PERMISSION']
					: \CIBlockRights::PUBLIC_READ
			;

			if ($permissionsBy !== null)
			{
				$iUserID = $permissionsBy;
				$strGroups = implode(',', CUser::GetUserGroup($permissionsBy));
				$bAuthorized = false;
			}
			else
			{
				if (is_object($USER))
				{
					$iUserID = (int)$USER->GetID();
					$strGroups = $USER->GetGroups();
					$bAuthorized = $USER->IsAuthorized();
				}
				else
				{
					$iUserID = 0;
					$strGroups = "2";
					$bAuthorized = false;
				}
			}

			$stdPermissions = "
				SELECT IBLOCK_ID
				FROM b_iblock_group IBG
				WHERE IBG.GROUP_ID IN (".$strGroups.")
				AND IBG.PERMISSION >= '".$min_permission."'
			";
			if(!defined("ADMIN_SECTION"))
				$stdPermissions .= "
					AND (IBG.PERMISSION='X' OR B.ACTIVE='Y')
				";

			if (!empty($arFilter["OPERATION"]))
			{
				$operation  = "'".$DB->ForSql($arFilter["OPERATION"])."'";
			}
			elseif($min_permission >= "X")
			{
				$operation = "'iblock_edit'";
			}
			elseif($min_permission >= "U")
			{
				$operation = "'element_edit'";
			}
			elseif($min_permission >= "S")
			{
				$operation = "'iblock_admin_display'";
			}
			else
			{
				$operation = "'section_read', 'element_read', 'section_element_bind', 'section_section_bind'";
			}

			if($operation)
			{
				$acc = new CAccess;
				$acc->UpdateCodes($permissionsBy !== null ? array('USER_ID' => $permissionsBy) : false);

				$extPermissions = "
					SELECT IBLOCK_ID
					FROM b_iblock_right IBR
					INNER JOIN b_task_operation T ON T.TASK_ID = IBR.TASK_ID
					INNER JOIN b_operation O ON O.ID = T.OPERATION_ID
					".($iUserID > 0? "LEFT": "INNER")." JOIN b_user_access UA ON UA.ACCESS_CODE = IBR.GROUP_CODE AND UA.USER_ID = ".$iUserID."
					WHERE IBR.ENTITY_TYPE = 'iblock'
					AND O.NAME in (".$operation.")
					".($bAuthorized || $iUserID > 0? "AND (UA.USER_ID IS NOT NULL OR IBR.GROUP_CODE = 'AU')": "")."
				";
				$sqlPermissions = "AND (
					B.ID IN ($stdPermissions)
					OR (B.RIGHTS_MODE = 'E' AND B.ID IN ($extPermissions))
				)";
			}
			else
			{
				$sqlPermissions = "AND (
					B.ID IN ($stdPermissions)
				)";
			}
		}
		else
		{
			$sqlPermissions = "";
		}

		if ($bAddSites)
			$sqlJoinSites = "LEFT JOIN b_iblock_site BS ON B.ID=BS.IBLOCK_ID
					LEFT JOIN b_lang L ON L.LID=BS.SITE_ID";
		else
			$sqlJoinSites = "INNER JOIN b_lang L ON L.LID=B.LID";

		if(!$bIncCnt)
		{
			$strSql = "
				SELECT DISTINCT
					B.*
					,B.XML_ID as EXTERNAL_ID
					,".$DB->DateToCharFunction("B.TIMESTAMP_X")." as TIMESTAMP_X
					,L.DIR as LANG_DIR
					,L.SERVER_NAME
				FROM
					b_iblock B
					".$sqlJoinSites."
				WHERE 1 = 1
					".$sqlPermissions."
					".$strSqlSearch."
			";
		}
		else
		{
			$strSql = "
				SELECT
					B.*
					,B.XML_ID as EXTERNAL_ID
					,".$DB->DateToCharFunction("B.TIMESTAMP_X")." as TIMESTAMP_X
					,L.DIR as LANG_DIR
					,L.SERVER_NAME
					,COUNT(DISTINCT BE.ID) as ELEMENT_CNT
				FROM
					b_iblock B
					".$sqlJoinSites."
					LEFT JOIN b_iblock_element BE ON (BE.IBLOCK_ID=B.ID
						AND (
							(BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
							".(($arFilter["CNT_ALL"] ?? 'N') === "Y"? " OR BE.WF_NEW='Y' ":"")."
						)
						".(($arFilter["CNT_ACTIVE"] ?? 'N') === "Y" ?
						"AND BE.ACTIVE='Y'
						AND (BE.ACTIVE_TO >= ".$DB->CurrentDateFunction()." OR BE.ACTIVE_TO IS NULL)
						AND (BE.ACTIVE_FROM <= ".$DB->CurrentDateFunction()." OR BE.ACTIVE_FROM IS NULL)
						":
						"")."
					)
				WHERE 1 = 1
					".$sqlPermissions."
					".$strSqlSearch."
				GROUP BY B.ID
			";
		}

		$arSqlOrder = Array();
		if(is_array($arOrder))
		{
			foreach($arOrder as $by=>$order)
			{
				$by = mb_strtolower($by);
				$order = mb_strtolower($order);
				if ($order!="asc")
					$order = "desc";

				if ($by == "id") $arSqlOrder[$by] = " B.ID ".$order." ";
				elseif ($by == "lid") $arSqlOrder[$by] = " B.LID ".$order." ";
				elseif ($by == "iblock_type") $arSqlOrder[$by] = " B.IBLOCK_TYPE_ID ".$order." ";
				elseif ($by == "name") $arSqlOrder[$by] = " B.NAME ".$order." ";
				elseif ($by == "active") $arSqlOrder[$by] = " B.ACTIVE ".$order." ";
				elseif ($by == "sort") $arSqlOrder[$by] = " B.SORT ".$order." ";
				elseif ($by == "code") $arSqlOrder[$by] = " B.CODE ".$order." ";
				elseif ($bIncCnt && $by == "element_cnt") $arSqlOrder[$by] = " ELEMENT_CNT ".$order." ";
				else
				{
					$by = "timestamp_x";
					$arSqlOrder[$by] = " B.TIMESTAMP_X ".$order." ";
				}
			}
		}

		if (!empty($arSqlOrder))
		{
			$strSqlOrder = " ORDER BY " . implode(",", $arSqlOrder);
		}
		else
		{
			$strSqlOrder = "";
		}

		return $DB->Query($strSql.$strSqlOrder);
	}

	public static function _Upper($str)
	{
		return $str;
	}

	public function _Add($ID)
	{
		$ID = (int)$ID;

		$connection = Application::getConnection();

		if (
			$connection instanceof MysqlCommonConnection
			&& defined('MYSQL_TABLE_TYPE')
			&& MYSQL_TABLE_TYPE !== ''
		)
		{
			// TODO: remove try-catch when mysql 8.0 will be minimal system requirement
			try
			{
				$connection->query('SET default_storage_engine = \'' . MYSQL_TABLE_TYPE . '\'');
			}
			catch (SqlQueryException)
			{
				try
				{
					$connection->query('SET storage_engine = \''.MYSQL_TABLE_TYPE.'\'');
				}
				catch (SqlQueryException)
				{

				}
			}
		}

		$singleTableName = static::getSinglePropertyValuesTableName($ID);
		$multiTableName = static::getMultiplePropertyValuesTableName($ID);

		if (!$connection->isTableExists($singleTableName))
		{
			$fields = [
				'IBLOCK_ELEMENT_ID' => (new Fields\IntegerField('IBLOCK_ELEMENT_ID'))
					->configurePrimary()
				,
			];
			$connection->createTable($singleTableName, $fields, ['IBLOCK_ELEMENT_ID']);
			if (!$connection->isTableExists($singleTableName))
			{
				return false;
			}
		}

		if (!$connection->isTableExists($multiTableName))
		{
			$fields = [
				'ID' => (new Fields\IntegerField('ID'))
					->configurePrimary()
					->configureAutocomplete()
				,
				'IBLOCK_ELEMENT_ID' => (new Fields\IntegerField('IBLOCK_ELEMENT_ID')),
				'IBLOCK_PROPERTY_ID' => (new Fields\IntegerField('IBLOCK_PROPERTY_ID')),
				'VALUE' => (new Fields\TextField('VALUE')),
				'VALUE_ENUM' => (new Fields\IntegerField('VALUE_ENUM'))
					->configureNullable()
				,
				'VALUE_NUM' => (new Fields\DecimalField('VALUE_NUM'))
					->configureNullable()
					->configurePrecision(18)
					->configureScale(4)
				,
				'DESCRIPTION' => (new Fields\StringField('DESCRIPTION'))
					->configureSize(255)
					->configureNullable()
				,
			];
			$connection->createTable($multiTableName, $fields, ['ID'], ['ID']);
			if (!$connection->isTableExists($multiTableName))
			{
				return false;
			}
			else
			{
				$connection->createIndex(
					$multiTableName,
					'ix_iblock_elem_prop_m' . $ID . '_1',
					[
						'IBLOCK_ELEMENT_ID',
						'IBLOCK_PROPERTY_ID',
					]
				);
				$connection->createIndex(
					$multiTableName,
					'ix_iblock_elem_prop_m' . $ID . '_2',
					[
						'IBLOCK_PROPERTY_ID',
					]
				);
				$connection->createIndex(
					$multiTableName,
					'ix_iblock_elem_prop_m' . $ID . '_3',
					[
						'VALUE_ENUM',
						'IBLOCK_PROPERTY_ID',
					]
				);
			}
		}

		return true;

		/*
		$strSql = '
			CREATE TABLE IF NOT EXISTS b_iblock_element_prop_s' . $ID . ' (
				IBLOCK_ELEMENT_ID int(11) not null,
				primary key (IBLOCK_ELEMENT_ID)
			)
		';
		$rs = $DB->DDL($strSql, false, $err_mess.__LINE__);
		$strSql = '
			CREATE TABLE IF NOT EXISTS b_iblock_element_prop_m' . $ID . ' (
				ID int(11) not null auto_increment,
				IBLOCK_ELEMENT_ID int(11) not null,
				IBLOCK_PROPERTY_ID int(11) not null,
				VALUE text not null,
				VALUE_ENUM int(11),
				VALUE_NUM numeric(18,4),
				DESCRIPTION VARCHAR(255) NULL,
				PRIMARY KEY (ID),
				INDEX ix_iblock_elem_prop_m' . $ID . '_1(IBLOCK_ELEMENT_ID,IBLOCK_PROPERTY_ID),
				INDEX ix_iblock_elem_prop_m' . $ID . '_2(IBLOCK_PROPERTY_ID),
				INDEX ix_iblock_elem_prop_m' . $ID . '_3(VALUE_ENUM,IBLOCK_PROPERTY_ID)
			)
		';
		if ($rs)
		{
			$rs = $DB->DDL($strSql, false, $err_mess . __LINE__);
		}

		return $rs;
		*/
	}

	public static function _Order($by, $order, $default_order, $nullable = true)
	{
		$o = parent::_Order($by, $order, $default_order, $nullable);
		//$o[0] - bNullsFirst
		//$o[1] - asc|desc
		if($o[0])
		{
			if($o[1] == "asc")
			{
				return $by." asc";
			}
			else
			{
				return "length(".$by.")>0 asc, ".$by." desc";
			}
		}
		else
		{
			if($o[1] == "asc")
			{
				return "length(".$by.")>0 desc, ".$by." asc";
			}
			else
			{
				return $by." desc";
			}
		}
	}

	public static function _NotEmpty($column)
	{
		return 'case when ' . $column . ' is null then 0 else 1 end';
	}
}
