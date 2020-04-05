<?
/** @global CMain $APPLICATION */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
/** @global CDatabase $DB */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$ID = IntVal($ID);
$PERSON_TYPE_ID = IntVal($PERSON_TYPE_ID);

if (($ID>0) && ($arOrderProps = CSaleOrderProps::GetByID($ID)))
{
	$PERSON_TYPE_ID = $arOrderProps["PERSON_TYPE_ID"];
}
elseif (($PERSON_TYPE_ID>0) && ($arPersonType = CSalePersonType::GetByID($PERSON_TYPE_ID)))
{
	$ID = 0;
}
else
{
	LocalRedirect("sale_order_props.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
}


$strError = "";
$bInitVars = false;
if ((strlen($save)>0 || strlen($apply)>0) && $_SERVER['REQUEST_METHOD'] == "POST" && $saleModulePermissions=="W" && strlen($propeditmore)<=0 && check_bitrix_sessid())
{
	$PERSON_TYPE_ID = IntVal($PERSON_TYPE_ID);
	if ($PERSON_TYPE_ID<=0)
		$strError .= GetMessage("ERROR_NO_PERS_TYPE")."<br>";

	$NAME = Trim($NAME);
	if (strlen($NAME)<=0)
		$strError .= GetMessage("ERROR_NO_NAME")."<br>";

	$TYPE = Trim($TYPE);
	if (strlen($TYPE)<=0)
		$strError .= GetMessage("ERROR_NO_TYPE")."<br>";

	if ($REQUIED!="Y") $REQUIED = "N";
	if ($USER_PROPS!="Y") $USER_PROPS = "N";

	if ($IS_LOCATION!="Y") $IS_LOCATION = "N";
	if ($IS_LOCATION4TAX!="Y") $IS_LOCATION4TAX = "N";
	if ($IS_EMAIL!="Y") $IS_EMAIL = "N";
	if ($IS_PROFILE_NAME!="Y") $IS_PROFILE_NAME = "N";
	if ($IS_PAYER!="Y") $IS_PAYER = "N";
	if ($IS_FILTERED!="Y") $IS_FILTERED = "N";

	if ($IS_LOCATION=="Y" && $TYPE!="LOCATION")
		$strError .= GetMessage("ERROR_NOT_LOCATION")."<br>";
	if ($IS_LOCATION4TAX=="Y" && $TYPE!="LOCATION")
		$strError .= GetMessage("ERROR_NOT_LOCATION")."<br>";

	$SORT = IntVal($SORT);
	if ($SORT<=0) $SORT = 100;

	$PROPS_GROUP_ID = IntVal($PROPS_GROUP_ID);
	if ($PROPS_GROUP_ID<=0)
		$strError .= GetMessage("ERROR_NO_GROUP")."<br>";

	if (strlen($strError)<=0)
	{
		unset($arFields);
		$arFields = array(
			"PERSON_TYPE_ID" => $PERSON_TYPE_ID,
			"NAME" => $NAME,
			"TYPE" => $TYPE,
			"REQUIED" => $REQUIED,
			"DEFAULT_VALUE" => $DEFAULT_VALUE,
			"SORT" => $SORT,
			"CODE" => (strlen($CODE)<=0 ? False : $CODE),
			"USER_PROPS" => $USER_PROPS,
			"IS_LOCATION" => $IS_LOCATION,
			"IS_LOCATION4TAX" => $IS_LOCATION4TAX,
			"PROPS_GROUP_ID" => $PROPS_GROUP_ID,
			"SIZE1" => $SIZE1,
			"SIZE2" => $SIZE2,
			"DESCRIPTION" => $DESCRIPTION,
			"IS_EMAIL" => $IS_EMAIL,
			"IS_PROFILE_NAME" => $IS_PROFILE_NAME,
			"IS_PAYER" => $IS_PAYER,
			"IS_FILTERED" => $IS_FILTERED
		);

		if ($ID>0)
		{
			if (!CSaleOrderProps::Update($ID, $arFields))
				$strError .= GetMessage("ERROR_EDIT_PROP")."<br>";

			if (strlen($strError)<=0)
			{
				$db_order_props_tmp = CSaleOrderPropsValue::GetList(($b="NAME"), ($o="ASC"), Array("ORDER_PROPS_ID"=>$ID));
				while ($ar_order_props_tmp = $db_order_props_tmp->Fetch())
				{
					CSaleOrderPropsValue::Update($ar_order_props_tmp["ID"], array("CODE"=>(strlen($CODE)<=0 ? False : $CODE)));
				}
			}
		}
		else
		{
			$ID = CSaleOrderProps::Add($arFields);
			if ($ID<=0)
				$strError .= GetMessage("ERROR_ADD_PROP")."<br>";
		}
	}

	if (strlen($strError)<=0)
	{
		if ($TYPE=="SELECT" || $TYPE=="MULTISELECT" || $TYPE=="RADIO")
		{
			$numpropsvals = IntVal($numpropsvals);
			for ($i = 0; $i<=$numpropsvals; $i++)
			{
				$strError1 = "";

				$CF_ID = IntVal(${"ID_".$i});
				$CF_DEL = ${"DELETE_".$i};
				unset($arFieldsV);
				$arFieldsV = array(
					"ORDER_PROPS_ID" => $ID,
					"VALUE" => Trim(${"VALUE_".$i}),
					"NAME" => Trim(${"NAME_".$i}),
					"SORT" => ( (IntVal(${"SORT_".$i})>0) ? IntVal(${"SORT_".$i}) : 100 ),
					"DESCRIPTION" => Trim(${"DESCRIPTION_".$i})
					);

				if ($CF_ID<=0)
				{
					if (strlen($arFieldsV["VALUE"])>0 && strlen($arFieldsV["NAME"])>0)
					{
						if (!CSaleOrderPropsVariant::Add($arFieldsV))
							$strError1 .= GetMessage("ERROR_ADD_VARIANT")." (".$arFieldsV["VALUE"].", ".$arFieldsV["NAME"].", ".$arFieldsV["SORT"].", ".$arFieldsV["DESCRIPTION"].").<br>";
					}
				}
				elseif ($CF_DEL=="Y")
				{
					CSaleOrderPropsVariant::Delete($CF_ID);
				}
				else
				{
					if (strlen($arFieldsV["VALUE"])<=0)
						$strError1 .= GetMessage("ERROR_EMPTY_VAR_CODE")." (".$arFieldsV["NAME"].", ".$arFieldsV["SORT"].", ".$arFieldsV["DESCRIPTION"].").<br>";

					if (strlen($arFieldsV["NAME"])<=0)
						$strError1 .= GetMessage("ERROR_EMPTY_VAR_NAME")." (".$arFieldsV["VALUE"].", ".$arFieldsV["SORT"].", ".$arFieldsV["DESCRIPTION"].").<br>";

					if (strlen($strError1)<=0)
					{
						if (!CSaleOrderPropsVariant::Update($CF_ID, $arFieldsV))
							$strError .= GetMessage("ERROR_EDIT_VARIANT")." (".$arFieldsV["VALUE"].", ".$arFieldsV["NAME"].", ".$arFieldsV["SORT"].", ".$arFieldsV["DESCRIPTION"].").<br>";
					}
				}
				$strError .= $strError1;
			}
		}
		else
		{
			CSaleOrderPropsVariant::DeleteAll($ID);
		}
	}

	if (strlen($strError)>0) $bInitVars = True;

	if (strlen($save)>0 && strlen($strError)<=0)
		LocalRedirect("sale_order_props.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
}
ClearVars();
if ($ID>0)
{
	$db_orderProps = CSaleOrderProps::GetList(array("ID" => "ASC"), array("ID" => $ID));
	$db_orderProps->ExtractFields("str_");
}

if (strlen($propeditmore)>0) $bInitVars = True;

if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_sale_order_props", "", "str_");
}

if($ID > 0)
	$sDocTitle = GetMessage("SALE_EDIT_RECORD", array("#ID#"=>$ID));
else
	$sDocTitle = GetMessage("SALE_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("SOPEN_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/sale_order_props.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
	)
);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$arDDMenu = array();

	$arDDMenu[] = array(
		"TEXT" => "<b>".GetMessage("SOPEN_4NEW_PROMT")."</b>",
		"ACTION" => false
	);

	$dbRes = CSalePersonType::GetList(
		array("NAME" => "ASC"),
		array(),
		false,
		false,
		array("ID", "NAME", "LID")
	);
	while ($arRes = $dbRes->GetNext())
	{
		$arDDMenu[] = array(
			"TEXT" => "[".$arRes["ID"]."] ".$arRes["NAME"]." (".$arRes["LID"].")",
			"ACTION" => "window.location = 'sale_order_props_edit.php?lang=".LANGUAGE_ID."&PERSON_TYPE_ID=".$arRes["ID"]."';"
		);
	}

	$aMenu[] = array(
		"TEXT" => GetMessage("SOPEN_NEW_PROPS"),
		"ICON" => "btn_new",
		"MENU" => $arDDMenu
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("SOPEN_DELETE_PROPS"),
		"LINK" => "javascript:if(confirm('".GetMessage("SOPEN_DELETE_PROPS_CONFIRM")."')) window.location='/bitrix/admin/sale_order_props.php?action=delete&ID[]=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

CAdminMessage::ShowMessage($strError);?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<input type="hidden" name="PERSON_TYPE_ID" value="<?echo $PERSON_TYPE_ID ?>">
<?=bitrix_sessid_post();

$arPersonType = CSalePersonType::GetByID($PERSON_TYPE_ID);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SOPEN_TAB_PROPS"), "ICON" => "sale", "TITLE" => str_replace("#PTYPE#", htmlspecialcharsEx($arPersonType["NAME"])." (".htmlspecialcharsEx($arPersonType["LID"]).")", GetMessage("SOPEN_TAB_PROPS_DESCR")))
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
	if ($ID>0):?>
	<tr>
		<td width="40%">ID:</td>
		<td width="60%"><?echo $ID ?></td>
	</tr>
	<?endif;?>

	<tr>
		<td width="40%"><?echo GetMessage("SALE_PERS_TYPE")?>:</td>
		<td width="60%">
			[<?echo $arPersonType["ID"] ?>] <?echo htmlspecialcharsEx($arPersonType["NAME"]) ?> (<?echo htmlspecialcharsEx($arPersonType["LID"]) ?>)
		</td>
	</tr>

	<tr>
		<td width="40%"><span class="required">*</span><?echo GetMessage("F_NAME") ?>:</td>
		<td width="60%">
			<input type="text" name="NAME" value="<?echo $str_NAME ?>">
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_CODE") ?>:</td>
		<td width="60%">
			<input type="text" name="CODE" value="<?echo $str_CODE ?>">
		</td>
	</tr>
	<tr>
		<td width="40%"><span class="required">*</span><?echo GetMessage("F_TYPE") ?>:</td>
		<td width="60%">
			<?
				foreach(\Bitrix\Sale\Internals\Input\Manager::getTypes() as $k => $v)
					$arSaleFieldType[$k] = Array("DESCRIPTION"=>$v['NAME']);

				//$dbUType = CUserTypeManager::GetUserType(false);
				$arUType = $USER_FIELD_MANAGER->GetUserType();

				$arSaleFieldType["--"] = Array("DESCRIPTION"=>"---User types---");
				foreach($arUType as $k => $v)
					$arSaleFieldType["UF_".$k] = $v;

			?>
			<select name="TYPE">
				<?
				foreach ($arSaleFieldType as $key => $value):
					?><option value="<?echo $key?>"<?if ($str_TYPE==$key) echo " selected"?>>[<?echo htmlspecialcharsbx($key) ?>] <?echo htmlspecialcharsbx($value["DESCRIPTION"]) ?></option><?
				endforeach;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_REQUIED");?>:</td>
		<td width="60%">
			<input type="checkbox" name="REQUIED" value="Y" <?if ($str_REQUIED=="Y") echo "checked"?>>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_DEFAULT_VALUE");?>:</td>
		<td width="60%">
			<input type="text" name="DEFAULT_VALUE" value="<?echo $str_DEFAULT_VALUE ?>">
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_SORT");?>:</td>
		<td width="60%">
			<input type="text" name="SORT" value="<?echo $str_SORT ?>">
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_USER_PROPS");?>:</td>
		<td width="60%">
			<input type="checkbox" name="USER_PROPS" value="Y" <?if ($str_USER_PROPS=="Y") echo "checked"?>>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_PROPS_GROUP_ID") ?>:</td>
		<td width="60%">
			<select name="PROPS_GROUP_ID">
				<?
				$l = CSaleOrderPropsGroup::GetList(($b="NAME"), ($o="ASC"), Array("PERSON_TYPE_ID"=>$PERSON_TYPE_ID));
				while ($l->ExtractFields("l_")):
					?><option value="<?echo $l_ID?>"<?if (IntVal($str_PROPS_GROUP_ID)==IntVal($l_ID)) echo " selected"?>>[<?echo $l_ID ?>] <?echo $l_NAME?></option><?
				endwhile;
				?>
			</select>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="sale_order_props_group.php?lang=<?echo LANGUAGE_ID?>" target="_blank"><b><?echo GetMessage("SALE_PROPS_GROUP")?> &gt;&gt;</b></a>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top"><?echo GetMessage("F_SIZE1");?>:</td>
		<td width="60%" valign="top">
			<input type="text" name="SIZE1" value="<?echo $str_SIZE1 ?>"><br>
			<small><?echo GetMessage("F_SIZE1_DESCR");?></small><br>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top"><?echo GetMessage("F_SIZE2");?>:</td>
		<td width="60%" valign="top">
			<input type="text" name="SIZE2" value="<?echo $str_SIZE2 ?>"><br>
			<small><?echo GetMessage("F_SIZE2_DESCR");?></small><br>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top">
			<?echo GetMessage("F_DESCRIPTION");?>:
		</td>
		<td width="60%" valign="top">
			<textarea rows="3" cols="40" name="DESCRIPTION"><?echo $str_DESCRIPTION;?></textarea>
		</td>
	</tr>

	<tr>
		<td width="40%" valign="top">
			<?echo GetMessage("F_IS_LOCATION");?>:
		</td>
		<td width="60%" valign="top">
			<input type="checkbox" name="IS_LOCATION" value="Y" <?if ($str_IS_LOCATION=="Y") echo "checked"?>><br>
			<small><?echo GetMessage("F_IS_LOCATION_DESCR");?></small><br>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top">
			<?echo GetMessage("F_IS_LOCATION4TAX");?>:
		</td>
		<td width="60%" valign="top">
			<input type="checkbox" name="IS_LOCATION4TAX" value="Y" <?if ($str_IS_LOCATION4TAX=="Y") echo "checked"?>><br>
			<small><?echo GetMessage("F_IS_LOCATION4TAX_DESCR");?></small><br>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top">
			<?echo GetMessage("F_IS_EMAIL");?>:
		</td>
		<td width="60%" valign="top">
			<input type="checkbox" name="IS_EMAIL" value="Y" <?if ($str_IS_EMAIL=="Y") echo "checked"?>><br>
			<small><?echo GetMessage("F_IS_EMAIL_DESCR");?></small><br>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top">
			<?echo GetMessage("F_IS_PROFILE_NAME");?>:
		</td>
		<td width="60%" valign="top">
			<input type="checkbox" name="IS_PROFILE_NAME" value="Y" <?if ($str_IS_PROFILE_NAME=="Y") echo "checked"?>><br>
			<small><?echo GetMessage("F_IS_PROFILE_NAME_DESCR");?></small><br>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top">
			<?echo GetMessage("F_IS_PAYER");?>:
		</td>
		<td width="60%" valign="top">
			<input type="checkbox" name="IS_PAYER" value="Y" <?if ($str_IS_PAYER=="Y") echo "checked"?>><br>
			<small><?echo GetMessage("F_IS_PAYER_DESCR");?></small><br>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top">
			<?echo GetMessage("F_IS_FILTERED");?>
		</td>
		<td width="60%" valign="top">
			<input type="checkbox" name="IS_FILTERED" value="Y" <?if ($str_IS_FILTERED=="Y") echo "checked"?>><br>
			<small><?echo GetMessage("F_IS_FILTERED_DESCR");?></small><br>
		</td>
	</tr>

<?if ($str_TYPE=="SELECT" || $str_TYPE=="MULTISELECT" || $str_TYPE=="RADIO"):?>
	<tr class="heading">
		<td colspan="2">
			<?if (strlen($propeditmore)>0):?><a name="tb"></a><?endif;?>
			<?echo GetMessage("SALE_VARIANTS")?>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table cellspacing="0" class="internal">
				<tr class="heading">
					<td align="center"><?echo GetMessage("SALE_VARIANTS_CODE")?></td>
					<td align="center"><?echo GetMessage("SALE_VARIANTS_NAME")?></td>
					<td align="center"><?echo GetMessage("SALE_VARIANTS_SORT")?></td>
					<td align="center"><?echo GetMessage("SALE_VARIANTS_DESCR")?></td>
					<td align="center"><?echo GetMessage("SALE_VARIANTS_DEL")?></td>
				</tr>
			<?
			$db_propsVars = CSaleOrderPropsVariant::GetList(($b="SORT"), ($o="ASC"), Array("ORDER_PROPS_ID"=>$ID));
			$ind = -1;
			$oldind = -1;
			while ($db_propsVars->ExtractFields("f_"))
			{
				$ind++;
				$oldind++;
				if ($bInitVars)
				{
					$DB->InitTableVarsForEdit("b_sale_order_props_variant", "", "f_", "_".$oldind);
				}
				?>
				<tr>
					<td>
						<input type="hidden" name="ID_<?echo $ind;?>" value="<?echo $f_ID;?>">
						<input type="text" name="VALUE_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_VALUE);?>" size="5">
					</td>
					<td>
						<input type="text" name="NAME_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_NAME);?>" size="30">
					</td>
					<td>
						<input type="text" name="SORT_<?echo $ind;?>" value="<?echo IntVal($f_SORT);?>" size="3">
					</td>
					<td>
						<input type="text" name="DESCRIPTION_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_DESCRIPTION);?>" size="30">
					</td>
					<td>
						<input type="checkbox" name="DELETE_<?echo $ind;?>" value="Y"<?if (${"DELETE_".$ind}=="Y") echo " checked"?>>
					</td>
				</tr>
				<?
			}

			$numpropsvals = IntVal($numpropsvals);
			$numnewpropsvals = $numpropsvals-$oldind;
			if ($bInitVars && $numnewpropsvals>0)
			{
				for ($i = 0; $i<$numnewpropsvals; $i++)
				{
					$oldind++;
					$DB->InitTableVarsForEdit("b_sale_order_props_variant", "", "f_", "_".$oldind);
					if (strlen($f_VALUE)<=0 || strlen($f_NAME)<=0) continue;
					$ind++;
					?>
					<tr>
						<td>
							<input type="hidden" name="ID_<?echo $ind;?>" value="new">
							<input type="text" name="VALUE_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_VALUE);?>" size="5">
						</td>
						<td>
							<input type="text" name="NAME_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_NAME);?>" size="30">
						</td>
						<td>
							<input type="text" name="SORT_<?echo $ind;?>" value="<?echo IntVal($f_SORT);?>" size="3">
						</td>
						<td>
							<input type="text" name="DESCRIPTION_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_DESCRIPTION);?>" size="30">
						</td>
						<td>
							<input type="checkbox" name="DELETE_<?echo $ind;?>" value="Y"<?if (${"DELETE_".$ind}=="Y") echo " checked"?>>
						</td>
					</tr>
					<?
				}
			}

			for ($i=0; $i<5; $i++)
			{
				$ind++;
				$oldind++;
				?>
				<tr>
					<td>
						<input type="hidden" name="ID_<?echo $ind;?>" value="new">
						<input type="text" name="VALUE_<?echo $ind;?>" value="" size="5">
					</td>
					<td>
						<input type="text" name="NAME_<?echo $ind;?>" value="" size="30">
					</td>
					<td>
						<input type="text" name="SORT_<?echo $ind;?>" value="" size="3">
					</td>
					<td>
						<input type="text" name="DESCRIPTION_<?echo $ind;?>" value="" size="30">
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
				<?
			}
			?>
				<tr>
					<td colspan="4" align="right">
						<input type="hidden" name="numpropsvals" value="<?echo $ind; ?>">
						<input type="submit" name="propeditmore" value="<?echo GetMessage("SALE_VARIANTS_MORE")?>">
					</td>
					<td align="right">
						&nbsp;
					</td>
				</tr>
			</table>
		</td>
	</tr>
<?endif;?>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_order_props.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
			)
	);
?>

<?
$tabControl->End();
?>

</form>

<?echo BeginNote();?>
<span class="required">*</span> <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();

require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_admin.php");?>