<?php

require(__DIR__."/../general/usertype.php");

class CUserTypeEntity extends CAllUserTypeEntity
{
	function CreatePropertyTables($entity_id)
	{
		global $DB, $APPLICATION;
		if(!$DB->TableExists("b_utm_".strtolower($entity_id)))
		{
			if(defined("MYSQL_TABLE_TYPE"))
				$DB->Query("SET storage_engine = '".MYSQL_TABLE_TYPE."'", true);
			$rs = $DB->Query("
				create table IF NOT EXISTS b_utm_".strtolower($entity_id)." (
					ID int(11) not null auto_increment,
					VALUE_ID int(11) not null,
					FIELD_ID int(11) not null,
					VALUE text,
					VALUE_INT int,
					VALUE_DOUBLE float,
					VALUE_DATE datetime,
					INDEX ix_utm_".$entity_id."_2(VALUE_ID),
					INDEX ix_utm_".$entity_id."_3(FIELD_ID, VALUE_INT, VALUE_ID),
					PRIMARY KEY (ID)
				)
			", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if(!$rs)
			{
				$APPLICATION->ThrowException(GetMessage("USER_TYPE_TABLE_CREATION_ERROR",array(
					"#ENTITY_ID#"=>htmlspecialcharsbx($entity_id),
				)));
				return false;
			}
		}
		if(!$DB->TableExists("b_uts_".strtolower($entity_id)))
		{
			if(defined("MYSQL_TABLE_TYPE"))
				$DB->Query("SET storage_engine = '".MYSQL_TABLE_TYPE."'", true);

			$rs = $DB->Query("
				create table IF NOT EXISTS b_uts_".strtolower($entity_id)." (
					VALUE_ID int(11) not null,
					PRIMARY KEY (VALUE_ID)
				)
			", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if(!$rs)
			{
				$APPLICATION->ThrowException(GetMessage("USER_TYPE_TABLE_CREATION_ERROR",array(
					"#ENTITY_ID#"=>htmlspecialcharsbx($entity_id),
				)));
				return false;
			}
		}
		return true;
	}

	function DropColumnSQL($strTable, $arColumns)
	{
		return array("ALTER TABLE ".$strTable." DROP ".implode(", DROP ", $arColumns));
	}
}

/**
 * Ёта переменна€ содержит экземпл€р класса через API которого
 * и происходит работа с пользовательскими свойствами.
 * @global CUserTypeManager $GLOBALS['USER_FIELD_MANAGER']
 * @name $USER_FIELD_MANAGER
 */
$GLOBALS['USER_FIELD_MANAGER'] = new CUserTypeManager;
