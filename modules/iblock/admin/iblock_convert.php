<?php

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\IblockTable;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';
CJSCore::Init(array('ajax'));
CModule::IncludeModule('iblock');
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/prolog.php';

$request = Context::getCurrent()->getRequest();

$IBLOCK_ID = (int)$request->get('IBLOCK_ID');
$WAY = (int)$request->get('WAY');
$STEP = (int)$request->get('STEP');
$INTERVAL = 20;//sec TODO make setting?
$isSidePanel = $request->get('IFRAME') === 'Y';

if ($STEP == 0)
{
	$_SESSION["BX_IBLOCK_CONV"] = false;
}
elseif (is_array($_SESSION["BX_IBLOCK_CONV"] ?? null))
{
	$ar = array(
		"DONE" => intval($_SESSION["BX_IBLOCK_CONV"]["DONE"] ?? null),
		"TODO" => intval($_SESSION["BX_IBLOCK_CONV"]["TODO"] ?? null),
		"arMultiple" => array(),
		"arSingle" => array(),
		"arNumber" => array(),
	);
	if(is_array($_SESSION["BX_IBLOCK_CONV"]["arMultiple"]))
	{
		foreach($_SESSION["BX_IBLOCK_CONV"]["arMultiple"] as $id)
		{
			$id = intval($id);
			$ar["arMultiple"][$id] = $id;
		}
	}
	if(is_array($_SESSION["BX_IBLOCK_CONV"]["arSingle"]))
	{
		foreach($_SESSION["BX_IBLOCK_CONV"]["arSingle"] as $id)
		{
			$id = intval($id);
			$ar["arSingle"][$id] = $id;
		}
	}
	if(is_array($_SESSION["BX_IBLOCK_CONV"]["arNumber"]))
	{
		foreach($_SESSION["BX_IBLOCK_CONV"]["arNumber"] as $id)
		{
			$id = intval($id);
			$ar["arNumber"][$id] = $id;
		}
	}
	$_SESSION["BX_IBLOCK_CONV"] = $ar;
}
else
{
	$_SESSION["BX_IBLOCK_CONV"] = false;
}

$arErrors = array();
$arMessages = array();

$APPLICATION->SetTitle(Loc::getMessage("IBCONV_TITLE"));

$sTableID = "tbl_iblock_convert";
$lAdmin = new CAdminList($sTableID);
$lAdmin->BeginCustomContent();

$arIBlock = false;
if(!CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit") || (!check_bitrix_sessid() && $STEP > 0))
{
	$arErrors[]=Loc::getMessage("IBCONV_PERMISSION");
}
else
{
	$rsIBlock = CIBlock::GetList(array(), array("ID" => $IBLOCK_ID, "CHECK_PERMISSIONS" => "N"));
	if($arIBlock = $rsIBlock->Fetch())
	{
		if ($arIBlock["VERSION"] == IblockTable::PROPERTY_STORAGE_COMMON)
		{
			if($STEP!=0 && $WAY!=12)
				$arErrors[]=Loc::getMessage("IBCONV_WRONG_CONVERSION");
			elseif($STEP==1)
				$STEP = 2;
			elseif($STEP==2)
				$STEP = FirstStep12($arIBlock);
			elseif($STEP==3)
				$STEP = NextStep12($arIBlock);
			elseif($STEP==4)
				$STEP = 5;
			elseif($STEP==5)
				$STEP = LastStep12($arIBlock);
		}
		elseif($arIBlock["VERSION"] == IblockTable::PROPERTY_STORAGE_SEPARATE)
		{
			if($STEP!=0 && $WAY!=21)
				$arErrors[]=Loc::getMessage("IBCONV_WRONG_CONVERSION");
			elseif($STEP==1)
				$STEP = 2;
			elseif($STEP==2)
				$STEP = FirstStep21($arIBlock);
			elseif($STEP==3)
				$STEP = NextStep21($arIBlock);
			elseif($STEP==4)
				$STEP = 5;
			elseif($STEP==5)
				$STEP = LastStep21($arIBlock);
		}
		else
		{
			$arErrors[]=Loc::getMessage("IBCONV_WRONG_VERSION");
		}
	}
	else
	{
		$arErrors[]=Loc::getMessage("IBCONV_WRONG_IBLOCK");
	}
}

foreach($arErrors as $strError)
	CAdminMessage::ShowMessage($strError);
foreach($arMessages as $strMessage)
	CAdminMessage::ShowMessage(array("MESSAGE"=>$strMessage,"TYPE"=>"OK"));

if (empty($arErrors)):
	if ($STEP == 0):?>
		<p><span class="required"><?=Loc::getMessage("IBCONV_ATTENTION")?></span> <?=Loc::getMessage("IBCONV_WARNING_MESSAGE",array("#IBLOCK_NAME#"=>htmlspecialcharsbx($arIBlock["NAME"])))?>
		<input type="button" name="START" value="<?=Loc::getMessage("IBCONV_MOVE")?>"
			onclick="DoNext(<?= ($arIBlock["VERSION"] == IblockTable::PROPERTY_STORAGE_SEPARATE ? 21 : 12); ?>,1)"
		>
	<?php
	elseif ($STEP >= 1 && $STEP <= 5):
		if ($WAY == 12):
			?>
			<p><ul>
			<li><?=$STEP==2?'<b>':''?><?=Loc::getMessage("IBCONV_CREATE_TABLE")?><?=$STEP==2?'</b>':''?></li>
			<?php
			if (is_array($_SESSION["BX_IBLOCK_CONV"] ?? null)):
				?>
				<li><?=$STEP==3?'<b>':''?><?=Loc::getMessage("IBCONV_PROGRESS",array("#DONE#"=>$_SESSION["BX_IBLOCK_CONV"]["DONE"],"#TODO#"=>$_SESSION["BX_IBLOCK_CONV"]["TODO"]))?><?=$STEP==3?'</b>':''?></li>
				<?php
			else:
				?>
				<li><?=$STEP==3?'<b>':''?><?=Loc::getMessage("IBCONV_INPROGRESS")?><?=$STEP==3?'</b>':''?></li>
				<?php
			endif;
			?>
			<li><?=$STEP==5?'<b>':''?><?=Loc::getMessage("IBCONV_FINALIZE")?><?=$STEP==5?'</b>':''?></li>
			</ul></p>
			<?php
		else:
			?>
			<p><ul>
			<li><?= ($STEP == 2 ? '<b>' : ''); ?><?= Loc::getMessage("IBCONV_PREPARE"); ?><?= ($STEP == 2 ? '</b>' : ''); ?></li>
			<?php
			if (is_array($_SESSION["BX_IBLOCK_CONV"] ?? null)):
				?>
				<li><?= ($STEP == 3 ? '<b>' : ''); ?><?= Loc::getMessage("IBCONV_PROGRESS",array("#DONE#"=>$_SESSION["BX_IBLOCK_CONV"]["DONE"],"#TODO#"=>$_SESSION["BX_IBLOCK_CONV"]["TODO"]))?><?=$STEP==3?'</b>':''?></li>
				<?php
			else:
				?>
				<li><?=$STEP==3?'<b>':''?><?=Loc::getMessage("IBCONV_INPROGRESS")?><?=$STEP==3?'</b>':''?></li>
				<?php
			endif;
			?>
			<li><?=$STEP==5?'<b>':''?><?=Loc::getMessage("IBCONV_FINALIZE2")?><?=$STEP==5?'</b>':''?></li>
			</ul></p>
			<?php
		endif;
		?>
		<script>setTimeout('DoNext(<?=$WAY?>,<?=$STEP?>)', 500);</script>
		<?php
	else:
		?>
		<p><?=Loc::getMessage("IBCONV_FINISHED")?></p>
		<?php
		if ($isSidePanel):
			?>
			<p onclick="top.BX.onCustomEvent('SidePanel:close');"><a href="javascript:void(0)"><?=Loc::getMessage("IBCONV_FINISHED_HREF")?></a></p>
			<?php
		else:
			?>
			<p><a href="iblock_edit.php?ID=<?=$arIBlock["ID"]?>&amp;type=<?=$arIBlock["IBLOCK_TYPE_ID"]?>&amp;lang=<?=LANG?>&amp;admin=Y"><?=Loc::getMessage("IBCONV_FINISHED_HREF")?></a></p>
			<?php
		endif;
	endif;
endif;

$lAdmin->EndCustomContent();
if($STEP==0)
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
?>
<script>
function DoNext(way, step){
	var queryString='lang=<?= LANGUAGE_ID; ?>';
	queryString+='&IBLOCK_ID=<?= $IBLOCK_ID; ?>';
	queryString+='&WAY='+way;
	queryString+='&STEP='+step;
	queryString+='&<?= bitrix_sessid_get(); ?>';
	<?php
	if ($isSidePanel): ?>
	queryString+='&IFRAME=Y';<?php
	endif; ?>
	BX.showWait();
	BX.ajax.post('iblock_convert.php?'+queryString, null, function(result)
	{
		BX('tbl_iblock_convert_result_div').innerHTML = result;
		BX.closeWait();
	});
}
</script>
<?php
function FirstStep12($arIBlock): int
{
	//global $DB, $_SESSION, $arErrors, $arMessages, $INTERVAL;
	global $DB, $_SESSION, $arErrors;

	$arIBlock["ID"] = (int)$arIBlock["ID"];

	$obIBlock = new CIBlock;
	$LAST_CONV_ELEMENT = (int)($arIBlock["LAST_CONV_ELEMENT"] ?? 0);
	if ($LAST_CONV_ELEMENT == 0)
	{
		$strSql = "
			SELECT
				COUNT(*) CNT
			FROM
				b_iblock_property
			WHERE
				IBLOCK_ID = ".$arIBlock["ID"]."
		";
		$rs = $DB->Query($strSql);
		if($ar = $rs->Fetch())
		{
			if($ar["CNT"] > 50)
			{
				$arErrors[] = Loc::getMessage("IBCONV_TOO_MANY_PROPERTIES", array("#NUM#" => 50));
				return 3;
			}
		}

		$bOK = $obIBlock->_Add($arIBlock["ID"]);
		$DB->Query("UPDATE b_iblock SET LAST_CONV_ELEMENT = -1 WHERE ID = ".$arIBlock["ID"]);
	}
	else
	{
		$bOK = true;
	}

	if($bOK)
	{
		$obIBlockProperty = new CIBlockProperty;
		$arMultiple = array();
		$arSingle = array();
		$arNumber = array();
		$strSql = "
			SELECT
				ID
				,MULTIPLE
				,PROPERTY_TYPE
				,IBLOCK_ID
				,WITH_DESCRIPTION
			FROM
				b_iblock_property
			WHERE
				IBLOCK_ID=".$arIBlock["ID"]."
		";
		$rs = $DB->Query($strSql);
		while($ar = $rs->Fetch())
		{
			$id = intval($ar["ID"]);

			if($ar["MULTIPLE"]=="Y")
				$arMultiple[$id] = $id;
			else
				$arSingle[$id] = $id;

			if($ar["PROPERTY_TYPE"]=="N")
				$arNumber[$id] = $id;

			if($LAST_CONV_ELEMENT == 0)
				$obIBlockProperty->_Add($id, $ar);
		}
		$strSql = "
			SELECT
				COUNT('x') AS CNT
			FROM
				b_iblock_element
			WHERE
				IBLOCK_ID=".$arIBlock["ID"]."
				AND ID > ".$LAST_CONV_ELEMENT."
		";
		$rs = $DB->Query($strSql);
		$ar = $rs->Fetch();
		if ($ar && $ar["CNT"]>0)
		{
			$_SESSION["BX_IBLOCK_CONV"] = [
				"DONE" => 0,
				"TODO" => $ar["CNT"],
				"arMultiple" => $arMultiple,
				"arSingle" => $arSingle,
				"arNumber" => $arNumber,
			];
		}
		else
		{
			return 4;//nothing todo
		}
	}
	else
	{
		$arErrors[] = Loc::getMessage("IBCONV_TABLE_CREATION_ERROR");
	}

	return 3;
}
function NextStep12($arIBlock): int
{
	//global $DB, $_SESSION, $arErrors, $arMessages, $INTERVAL;
	global $DB, $_SESSION, $INTERVAL;

	$arIBlock["ID"] = (int)$arIBlock["ID"];
	$LAST_CONV_ELEMENT = (int)($arIBlock["LAST_CONV_ELEMENT"] ?? 0);
	$strSql = "
		SELECT ID
		FROM b_iblock_element
		WHERE IBLOCK_ID = ".$arIBlock["ID"]."
		AND ID>".$LAST_CONV_ELEMENT."
		ORDER BY ID
	";
	$rsID = $DB->Query($strSql);
	$t = microtime(true);
	$i = 0;
	while($arID = $rsID->Fetch())
	{
		$ELEMENT_ID=$arID["ID"];

		/** @noinspection SqlResolve */
		$strSql = "
			INSERT INTO b_iblock_element_prop_s".$arIBlock["ID"]."
			(IBLOCK_ELEMENT_ID)
			VALUES
			(".$ELEMENT_ID.")
		";
		$rs = $DB->Query($strSql, true);
		if(!$rs)
			continue;

		if (!empty($_SESSION["BX_IBLOCK_CONV"]["arMultiple"]))
		{
			$strSql = "
				INSERT INTO b_iblock_element_prop_m".$arIBlock["ID"]."
				(IBLOCK_ELEMENT_ID,IBLOCK_PROPERTY_ID,VALUE,VALUE_ENUM,VALUE_NUM,DESCRIPTION)
				SELECT IBLOCK_ELEMENT_ID,IBLOCK_PROPERTY_ID,VALUE,VALUE_ENUM,VALUE_NUM,DESCRIPTION
				FROM b_iblock_element_property
				WHERE IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
				AND IBLOCK_PROPERTY_ID IN (".implode(", ", $_SESSION["BX_IBLOCK_CONV"]["arMultiple"]).")
			";
			$DB->Query($strSql);
		}

		if (!empty($_SESSION["BX_IBLOCK_CONV"]["arSingle"]))
		{
			$arFields=array();
			$strSql = "
				SELECT *
				FROM b_iblock_element_property
				WHERE IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
				AND IBLOCK_PROPERTY_ID IN (".implode(", ", $_SESSION["BX_IBLOCK_CONV"]["arSingle"]).")
			";
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch())
			{
				$arFields["PROPERTY_".$ar["IBLOCK_PROPERTY_ID"]] = $ar["VALUE"];
				$arFields["DESCRIPTION_".$ar["IBLOCK_PROPERTY_ID"]] = $ar["DESCRIPTION"] ?? null;
			}
			if(count($arFields)>0)
			{
				$strUpdate = $DB->PrepareUpdateBind("b_iblock_element_prop_s".$arIBlock["ID"], $arFields, "iblock", false, $arBinds);
				if($strUpdate <> '')
				{
					$strSql = "UPDATE b_iblock_element_prop_s".$arIBlock["ID"]." SET ".$strUpdate." WHERE IBLOCK_ELEMENT_ID=".$ELEMENT_ID;
					$DB->QueryBind($strSql, $arBinds);
				}
			}
		}

		$strSql = "
			DELETE
			FROM b_iblock_element_property
			WHERE IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
		";
		$DB->Query($strSql);

		$DB->Query("UPDATE b_iblock SET LAST_CONV_ELEMENT = ".$ELEMENT_ID." WHERE ID = ".$arIBlock["ID"]);

		$i++;
		if ((microtime(true) - $t) > $INTERVAL)
		{
			break;
		}
	}
	$_SESSION["BX_IBLOCK_CONV"]["DONE"]+=$i;
	if($_SESSION["BX_IBLOCK_CONV"]["TODO"]<$_SESSION["BX_IBLOCK_CONV"]["DONE"])
		$_SESSION["BX_IBLOCK_CONV"]["TODO"]=$_SESSION["BX_IBLOCK_CONV"]["DONE"];
	//$_SESSION["BX_IBLOCK_CONV"]["STAT"][]=$i;$arMessages[]=implode(', ',$_SESSION["BX_IBLOCK_CONV"]["STAT"]);
	if($arID)
	{
		return 3;
	}
	else
	{
		return 4;
	}
}
function LastStep12($arIBlock): int
{
	//global $DB,$_SESSION,$arErrors,$arMessages,$INTERVAL;
	global $DB;

	$arIBlock['ID'] = (int)$arIBlock['ID'];
	$DB->Query('UPDATE b_iblock_property SET VERSION=2 WHERE IBLOCK_ID = '.$arIBlock['ID']);
	$DB->Query('UPDATE b_iblock SET VERSION=2,LAST_CONV_ELEMENT = 0 WHERE ID = '.$arIBlock['ID']);
	CIBlock::CleanCache($arIBlock['ID']);

	return 6;
}
function FirstStep21($arIBlock): int
{
	//global $DB,$_SESSION,$arErrors,$arMessages,$INTERVAL;
	global $DB,$_SESSION;

	$arIBlock["ID"] = (int)$arIBlock["ID"];
	$arMultiple = array();
	$arSingle = array();
	$arNumber = array();
	$strSql = "
		SELECT
			ID
			,MULTIPLE
			,PROPERTY_TYPE
			,IBLOCK_ID
		FROM
			b_iblock_property
		WHERE
			IBLOCK_ID=".$arIBlock["ID"]."
	";
	$rs = $DB->Query($strSql);
	while($ar = $rs->Fetch())
	{
		$id = intval($ar["ID"]);

		if($ar["MULTIPLE"]=="Y")
			$arMultiple[$id] = $id;
		else
			$arSingle[$id] = $id;

		if($ar["PROPERTY_TYPE"]=="N")
			$arNumber[$id] = $id;
	}
	$strSql = "
		SELECT
			COUNT('x') AS CNT
		FROM
			b_iblock_element_prop_s".$arIBlock["ID"]."
	";
	$rs = $DB->Query($strSql);
	$ar = $rs->Fetch();
	if($ar && $ar["CNT"]>0)
	{
		$_SESSION["BX_IBLOCK_CONV"] = [
			"DONE" => 0,
			"TODO" => $ar["CNT"],
			"arMultiple" => $arMultiple,
			"arSingle" => $arSingle,
			"arNumber" => $arNumber,
		];

		return 3;
	}
	else
	{
		return 4;//nothing todo
	}
}
function NextStep21($arIBlock): int
{
	//global $DB,$_SESSION,$arErrors,$arMessages,$INTERVAL;
	global $DB, $_SESSION, $INTERVAL;

	$arIBlock["ID"] = (int)$arIBlock["ID"];
	$strSql = "
		SELECT *
		FROM b_iblock_element_prop_s".$arIBlock["ID"]."
	";
	$rsElement = $DB->Query($strSql);
	$t = microtime(true);
	$i = 0;
	while($arElement = $rsElement->Fetch())
	{
		$ELEMENT_ID=$arElement["IBLOCK_ELEMENT_ID"];

		foreach($arElement as $key=>$value)
		{
			if(mb_substr($key, 0, 9) == "PROPERTY_" && $value <> '')
			{
				$ID = intval(mb_substr($key, 9));//TODO make conversion and check forsql and bind!!!!

				if($ID > 0 && array_key_exists($ID, $_SESSION["BX_IBLOCK_CONV"]["arSingle"]))
				{
					$arFields = array(
						"IBLOCK_ELEMENT_ID"=>$ELEMENT_ID,
						"IBLOCK_PROPERTY_ID"=>$ID,
						"VALUE"=>$value,
						"~VALUE_NUM"=>CIBlock::roundDB($value),
						"~VALUE_ENUM"=>intval($value),
						"DESCRIPTION"=> ($arElement["DESCRIPTION_".$ID] ?? null),
					);
					$arInsert = $DB->PrepareInsert("b_iblock_element_property", $arFields);
					$DB->QueryBind(
						"INSERT INTO b_iblock_element_property (".$arInsert[0].")VALUES(".$arInsert[1].")",
						[
							"DESCRIPTION"=>$arElement["DESCRIPTION_".$ID] ?? null,
						]
					);
				}
			}
		}

		$strSql = "
			INSERT INTO b_iblock_element_property
			(IBLOCK_ELEMENT_ID,IBLOCK_PROPERTY_ID,VALUE,VALUE_ENUM,VALUE_NUM,DESCRIPTION)
			SELECT IBLOCK_ELEMENT_ID,IBLOCK_PROPERTY_ID,VALUE,VALUE_ENUM,VALUE_NUM,DESCRIPTION
			FROM b_iblock_element_prop_m".$arIBlock["ID"]."
			WHERE IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
		";
		$DB->Query($strSql);

		$strSql = "
			DELETE
			FROM b_iblock_element_prop_m".$arIBlock["ID"]."
			WHERE IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
		";
		$DB->Query($strSql);

		$strSql = "
			DELETE
			FROM b_iblock_element_prop_s".$arIBlock["ID"]."
			WHERE IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
		";
		$DB->Query($strSql);

		$DB->Query("UPDATE b_iblock SET LAST_CONV_ELEMENT = ".$ELEMENT_ID." WHERE ID = ".$arIBlock["ID"]);

		$i++;
		if ((microtime(true) - $t) > $INTERVAL)
		{
			break;
		}
	}
	$_SESSION["BX_IBLOCK_CONV"]["DONE"]+=$i;
	if($_SESSION["BX_IBLOCK_CONV"]["TODO"]<$_SESSION["BX_IBLOCK_CONV"]["DONE"])
		$_SESSION["BX_IBLOCK_CONV"]["TODO"]=$_SESSION["BX_IBLOCK_CONV"]["DONE"];
	//$_SESSION["BX_IBLOCK_CONV"]["STAT"][]=$i;$arMessages[]=implode(', ',$_SESSION["BX_IBLOCK_CONV"]["STAT"]);
	if($arElement)
		return 3;
	else
		return 4;
}
function LastStep21($arIBlock): int
{
	//global $DB,$_SESSION,$arErrors,$arMessages,$INTERVAL;
	global $DB;

	$arIBlock["ID"] = (int)$arIBlock["ID"];
	$DB->Query("UPDATE b_iblock_property SET VERSION= 1 WHERE IBLOCK_ID = ".$arIBlock["ID"]);
	$DB->Query("UPDATE b_iblock SET VERSION=1,LAST_CONV_ELEMENT = 0 WHERE ID = ".$arIBlock["ID"]);
	CIBlock::CleanCache($arIBlock["ID"]);
	$DB->DDL("DROP TABLE b_iblock_element_prop_s".$arIBlock["ID"]);
	$DB->DDL("DROP TABLE b_iblock_element_prop_m".$arIBlock["ID"]);
	$DB->DDL("DROP SEQUENCE sq_b_iblock_element_prop_m".$arIBlock["ID"], true);

	return 6;
}

$lAdmin->DisplayList();

if ($STEP == 0)
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
}
else
{
	require $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_admin_js.php';
}
