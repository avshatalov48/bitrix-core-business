<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

?>
<script>
<!--
function Form_Filter_Click_<?=$arResult["filter_id"]?>()
{
	var sName = "<?=$arResult["tf_name"]?>";
	var filter_id = "form_filter_<?=$arResult["filter_id"]?>";
	var form_handle = document.getElementById(filter_id);

	if (form_handle)
	{
		if (form_handle.className != "form-filter-none")
		{
			form_handle.className = "form-filter-none";
			document.cookie = sName+"="+"none"+"; expires=Fri, 31 Dec 2030 23:59:59 GMT;";
		}
		else
		{
			form_handle.className = "form-filter-inline";
			document.cookie = sName+"="+"inline"+"; expires=Fri, 31 Dec 2030 23:59:59 GMT;";
		}
	}
}
//-->
</script>
<p>
<?=($arResult["is_filtered"] ? "<span class='form-filteron'>".GetMessage("FORM_FILTER_ON") : "<span class='form-filteroff'>".GetMessage("FORM_FILTER_OFF"))?></span>&nbsp;&nbsp;&nbsp;
[ <a href="javascript:void(0)" OnClick="Form_Filter_Click_<?=$arResult["filter_id"]?>()"><?=GetMessage("FORM_FILTER")?></a> ]
</p>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPageParam("", array("sessid", "delete", "del_id", "action"), false)?>?" id="form_filter_<?=$arResult["filter_id"]?>" class="form-filter-<?=htmlspecialcharsbx($arResult["tf"])?>">
<input type="hidden" name="WEB_FORM_ID" value="<?=$arParams["WEB_FORM_ID"]?>" />
<?if ($arParams["SEF_MODE"] == "N"):?><input type="hidden" name="action" value="list" /><?endif?>
<table class="form-filter-table data-table">
	<thead>
		<tr>
			<th colspan="2">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?
		if ($arResult["str_error"] <> '')
		{
		?>
		<tr>
			<td class="errortext" colspan="2"><?=$arResult["str_error"]?></td>
		</tr>
		<?
		} // endif (strlen($str_error) > 0)
		?>
		<tr>
			<td><?=GetMessage("FORM_F_ID")?></td>
			<td><?=CForm::GetTextFilter("id", 45, "", "")?></td>
		</tr>
		<?
		if ($arParams["SHOW_STATUS"]=="Y")
		{
		?>
		<tr>
			<td><?=GetMessage("FORM_F_STATUS")?></td>
			<td><select name="find_status" id="find_status">
				<option value="NOT_REF"><?=GetMessage("FORM_ALL")?></option>
				<?
				foreach ($arResult["arStatuses_VIEW"] as $arStatus)
				{
				?>
				<option value="<?=$arStatus["REFERENCE_ID"]?>"<?=($arStatus["REFERENCE_ID"]==$arResult["__find"]["find_status"] ? " SELECTED=\"1\"" : "")?>><?=$arStatus["REFERENCE"]?></option>
				<?
				}
				?>
			</select></td>
		</tr>
		<tr>
			<td><?=GetMessage("FORM_F_STATUS_ID")?></td>
			<td><?echo CForm::GetTextFilter("status_id", 45, "", "");?></td>
		</tr>
		<?
		} //endif ($SHOW_STATUS=="Y");
		?>
		<tr>
			<td><?=GetMessage("FORM_F_DATE_CREATE")." (".CSite::GetDateFormat("SHORT")."):"?></td>
			<td><?=CForm::GetDateFilter("date_create", "form1", "Y", "", "")?></td>
		</tr>
		<tr>
			<td><?=GetMessage("FORM_F_TIMESTAMP")." (".CSite::GetDateFormat("SHORT")."):"?></td>
			<td><?=CForm::GetDateFilter("timestamp", "form1", "Y", "", "")?></td>
		</tr>
		<?
		if (is_array($arResult["arrFORM_FILTER"]) && count($arResult["arrFORM_FILTER"])>0)
		{
			foreach ($arResult["arrFORM_FILTER"] as $arrFILTER)
			{
				$prev_fname = "";

				foreach ($arrFILTER as $arrF)
				{
					if ($arParams["SHOW_ADDITIONAL"] == "Y" || $arrF["ADDITIONAL"] != "Y")
					{
						$i++;
						if ($arrF["SID"]!=$prev_fname)
						{
							if ($i>1)
							{
							?>
			</td>
		</tr>
							<?
							} //endif($i>1);
							?>
		<tr>
			<td>
				<?=htmlspecialcharsbx($arrF["FILTER_TITLE"] ? $arrF['FILTER_TITLE'] : $arrF['TITLE'])?>
				<?=($arrF["FILTER_TYPE"]=="date" ? " (".CSite::GetDateFormat("SHORT").")" : "")?>
			</td>
			<td>
			<?
						} //endif ($fname!=$prev_fname) ;
						switch($arrF["FILTER_TYPE"])
						{
							case "text":
								echo CForm::GetTextFilter($arrF["FID"]);
								break;
							case "date":
								echo CForm::GetDateFilter($arrF["FID"]);
								break;
							case "integer":
								echo CForm::GetNumberFilter($arrF["FID"]);
								break;
							case "dropdown":
								echo CForm::GetDropDownFilter($arrF["ID"], $arrF["PARAMETER_NAME"], $arrF["FID"]);
								break;
							case "exist":
							?>
								<?=CForm::GetExistFlagFilter($arrF["FID"])?>
								<?=GetMessage("FORM_F_EXISTS")?>
							<?
								break;
						} // endswitch
						if ($arrF["PARAMETER_NAME"]=="ANSWER_TEXT")
						{
						?>
				&nbsp;[<span class='form-anstext'>...</span>]
						<?
						}
						elseif ($arrF["PARAMETER_NAME"]=="ANSWER_VALUE")
						{
						?>
				&nbsp;(<span class='form-ansvalue'>...</span>)
						<?
						}
						?>
				<br />
						<?
						$prev_fname = $arrF["SID"];
					} //endif (($arrF["ADDITIONAL"]=="Y" && $SHOW_ADDITIONAL=="Y") || $arrF["ADDITIONAL"]!="Y");

				} // endwhile (list($key, $arrF) = each($arrFILTER));

			} // endwhile (list($key, $arrFILTER) = each($arrFORM_FILTER));
		} // endif(is_array($arrFORM_FILTER) && count($arrFORM_FILTER)>0);
		?></td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="2">
				<input type="submit" name="set_filter" value="<?=GetMessage("FORM_F_SET_FILTER")?>" /><input type="hidden" name="set_filter" value="Y" />&nbsp;&nbsp;<input type="submit" name="del_filter" value="<?=GetMessage("FORM_F_DEL_FILTER")?>" />
			</th>
		</tr>
	</tfoot>
</table>
</form>
<br />

<?
if ($arParams["can_delete_some"])
{
?>
<SCRIPT>
<!--
function OnDelete_<?=$arResult["filter_id"]?>()
{
	var show_conf;
	var arCheckbox = document.forms['rform_<?=$arResult["filter_id"]?>'].elements["ARR_RESULT[]"];
	if(!arCheckbox) return;
	if(arCheckbox.length>0 || arCheckbox.value>0)
	{
		show_conf = false;
		if (arCheckbox.value>0 && arCheckbox.checked) show_conf = true;
		else
		{
			for(i=0; i<arCheckbox.length; i++)
			{
				if (arCheckbox[i].checked)
				{
					show_conf = true;
					break;
				}
			}
		}
		if (show_conf)
			return confirm("<?=GetMessage("FORM_DELETE_CONFIRMATION")?>");
		else
			alert('<?=GetMessage("FORM_SELECT_RESULTS")?>');
	}
	return false;
}

function OnSelectAll_<?=$arResult["filter_id"]?>(fl)
{
	var arCheckbox = document.forms['rform_<?=$arResult["filter_id"]?>'].elements["ARR_RESULT[]"];
	if(!arCheckbox) return;
	if(arCheckbox.length>0)
		for(i=0; i<arCheckbox.length; i++)
			arCheckbox[i].checked = fl;
	else
		arCheckbox.checked = fl;
}
//-->
</SCRIPT>
<?
} //endif($can_delete_some);
?>

<?
if ($arResult["FORM_ERROR"] <> '') ShowError($arResult["FORM_ERROR"]);
if ($arResult["FORM_NOTE"] <> '') ShowNote($arResult["FORM_NOTE"]);
?>
<p>
<b><a href="<?=$arParams["NEW_URL"]?><?=$arParams["SEF_MODE"] != "Y" ? (mb_strpos($arParams["NEW_URL"], "?") === false ? "?" : "&")."WEB_FORM_ID=".$arParams["WEB_FORM_ID"] : ""?>"><?echo GetMessage('FORM_ADD')?>&nbsp;&nbsp;&gt;&gt;</a></b>
</p>
<form name="rform_<?=$arResult["filter_id"]?>" method="post" action="<?=POST_FORM_ACTION_URI?>#nav_start">
	<input type="hidden" name="WEB_FORM_ID" value="<?=$arParams["WEB_FORM_ID"]?>" />
	<?=bitrix_sessid_post()?>

	<?
	if ($arParams["can_delete_some"])
	{
	?>
	<p><input type="submit" name="delete" value="<?=GetMessage("FORM_DELETE_SELECTED")?>" onClick="return OnDelete_<?=$arResult["filter_id"]?>()"  /></p>
	<?
	} // endif($can_delete_some);
	?>

	<?
	if ($arResult["res_counter"] > 0 && $arParams["SHOW_STATUS"] == "Y" && $arParams["F_RIGHT"] >= 20)
	{
	?>
	<p><input type="submit" name="save" value="<?=GetMessage("FORM_SAVE")?>" /><input type="hidden" name="save" value="Y" />&nbsp;<input type="reset" value="<?=GetMessage("FORM_RESET")?>" /></p>
	<?
	} //endif(intval($res_counter)>0 && $SHOW_STATUS=="Y" && $F_RIGHT>=20);
	?>
	<p>
	<?=$arResult["pager"]?>
	</p>
	<table class="form-table data-table">
		<thead>
			<tr>
				<th>
					<table class="form-results-header-inline">
						<tr>
							<th>
							<?
							if ($arParams["can_delete_some"])
							{
							?>
							<input type="checkbox" name="selectall" value="Y" onclick="OnSelectAll_<?=$arResult["filter_id"]?>(this.checked)" />&nbsp;
							<?
							} //endif ($can_delete_some);
							?>ID<?
							if ($arParams["SHOW_STATUS"]!="Y")
							{
								?><br /><?=SortingEx("s_id")?><?
							} //endif($SHOW_STATUS!="Y");?></th>
							<?
							if ($arParams["SHOW_STATUS"]=="Y")
							{
							?>
							<td><?=SortingEx("s_id")?></td>
							<?
							} //endif($SHOW_STATUS=="Y");
							?>
						</tr>
						<?
						if ($arParams["SHOW_STATUS"]=="Y")
						{
						?>
						<tr>
							<th><?=GetMessage("FORM_STATUS")?></th>
							<td><?=SortingEx("s_status")?></td>
						</tr>
						<?
						} //endif($SHOW_STATUS=="Y");
						?>
					</table>
				</th>
				<th><?=GetMessage("FORM_TIMESTAMP")?><br /><?=SortingEx("s_timestamp")?></th>
<?
				$colspan = 4;
				if (is_array($arResult["arrColumns"]))
				{
					foreach ($arResult["arrColumns"] as $arrCol)
					{
						if (!is_array($arParams["arrNOT_SHOW_TABLE"]) || !in_array($arrCol["SID"], $arParams["arrNOT_SHOW_TABLE"]))
						{
							if (($arrCol["ADDITIONAL"]=="Y" && $arParams["SHOW_ADDITIONAL"]=="Y") || $arrCol["ADDITIONAL"]!="Y")
							{
								if (++$colspan > 6)
								{
									$colspan--;
									break;
								}
?>
				<th><?=$arrCol["RESULTS_TABLE_TITLE"]?></th>
<?
							} //endif(($arrCol["ADDITIONAL"]=="Y" && $SHOW_ADDITIONAL=="Y") || $arrCol["ADDITIONAL"]!="Y");
						} //endif(!is_array($arrNOT_SHOW_TABLE) || !in_array($arrCol["SID"],$arrNOT_SHOW_TABLE));
					} //foreach
				} //endif(is_array($arrColumns)) ;
?>
			</tr>
		</thead>
		<?
		if(count($arResult["arrResults"]) > 0)
		{
?>
			<tbody>
<?
			$j=0;
			foreach ($arResult["arrResults"] as $arRes)
			{
				$j++;
?>
				<tr>
					<td>
<?
				if ($arParams["can_delete_some"] && $arRes["can_delete"])
				{
					?><input type="checkbox" name="ARR_RESULT[]" value="<?=$arRes["ID"]?>" /><?
				} //endif ($can_delete_some && $can_delete);

				$href = '';
				if ($arRes["can_view"])
				{
					if (trim($arParams["VIEW_URL"]) <> '')
					{
						$href = $arParams["SEF_MODE"] == "Y" ? str_replace("#RESULT_ID#", $arRes["ID"], $arParams["VIEW_URL"]) : $arParams["VIEW_URL"].(mb_strpos($arParams["VIEW_URL"], "?") === false ? "?" : "&")."RESULT_ID=".$arRes["ID"]."&WEB_FORM_ID=".$arParams["WEB_FORM_ID"];
					} //endif (strlen(trim($VIEW_URL))>0);
				} //endif ($can_view);
?>
						<input type="hidden" name="RESULT_ID[]" value="<?=$arRes["ID"]?>" /><b><?echo $href ? '<a href="'.$href.'">' : ''?><?echo GetMessage('FORM_RESULT_TITLE')?><span class="form-result-id"><?echo $arRes["ID"]?></span><?echo $href ? '</a>' : ''?></b><br />
<?
				if ($arParams["SHOW_STATUS"] == "Y")
				{
?>
						<?=GetMessage("FORM_STATUS")?>:&nbsp;[<span class="<?=htmlspecialcharsbx($arRes["STATUS_CSS"])?>"><?=htmlspecialcharsbx($arRes["STATUS_TITLE"])?></span>]
<?
					if ($arRes["can_edit"] && $arParams["F_RIGHT"] >= 20)
					{
?>
						<br /><input type="hidden" name="STATUS_PREV_<?=intval($GLOBALS["f_ID"])?>" value="<?=$arRes["STATUS_ID"]?>" />
						<select name="STATUS_<?=$arRes["ID"]?>" id="STATUS_<?=$arRes["ID"]?>" class="form-status-select">
							<option value="NOT_REF"> </option>
<?
						foreach ($arResult["arStatuses_MOVE"] as $arStatus)
						{
?>
							<option value="<?=$arStatus["REFERENCE_ID"]?>"><?=$arStatus["REFERENCE"]?></option>
<?
						}
?>
						</select>
<?
					} // endif (in_array("EDIT",$arrRESULT_PERMISSION) && $F_RIGHT>=20);
				} // endif ($SHOW_STATUS == "Y")

				$arControls = array();
				if ($arRes["can_edit"])
				{
					if (trim($arParams["EDIT_URL"]) <> '')
					{
						$href = $arParams["SEF_MODE"] == "Y" ? str_replace("#RESULT_ID#", $arRes["ID"], $arParams["EDIT_URL"]) : $arParams["EDIT_URL"].(mb_strpos($arParams["EDIT_URL"], "?") === false ? "?" : "&")."RESULT_ID=".$arRes["ID"]."&WEB_FORM_ID=".$arParams["WEB_FORM_ID"];
						$arControls[] = '<a title="'.GetMessage("FORM_EDIT_ALT").'" href="'.$href.'">'.GetMessage("FORM_EDIT").'</a>';
					}// endif(strlen(trim($EDIT_URL))>0);
				}// endif($can_edit);

				if ($arRes["can_delete"])
				{
					$href = $arParams["LIST_URL"].(mb_strpos($arParams["LIST_URL"], "?") === false ? "?" : "&").($arParams["SEF_MODE"] == "Y" ? "" : "WEB_FORM_ID=".$arParams["WEB_FORM_ID"]."&")."del_id=".$arRes["ID"]."&".bitrix_sessid_get()."#nav_start";
					$arControls[] = '<a title="'.GetMessage("FORM_DELETE_ALT").'" href="javascript:if(confirm(\''.CUtil::JSEscape(GetMessage("FORM_CONFIRM_DELETE")).'\')) window.location=\''.$href.'\'">'.GetMessage("FORM_DELETE").'</a>';
				} //endif ($can_delete);

				if (count($arControls) > 0)
?>
						<div class="form-result-controls"><?echo implode('&nbsp;|&nbsp;', $arControls)?></div>
<?
?>
</div>
				</td>
				<td><?=$arRes["TSX_0"]?><br /><?=$arRes["TSX_1"]?></td>
				<?
				$cnt = 4;
				foreach ($arResult["arrColumns"] as $FIELD_ID => $arrC)
				{
					if (!is_array($arParams["arrNOT_SHOW_TABLE"]) || !in_array($arrC["SID"], $arParams["arrNOT_SHOW_TABLE"]))
					{
						if (($arrC["ADDITIONAL"]=="Y" && $arParams["SHOW_ADDITIONAL"]=="Y") || $arrC["ADDITIONAL"]!="Y")
						{
							if (++$cnt > 6)
								break;

				?>
				<td>
					<?
					$arrAnswer = $arResult["arrAnswers"][$arRes["ID"]][$FIELD_ID];
					if (is_array($arrAnswer))
					{
						foreach ($arrAnswer as $key => $arrA)
						{
							if (trim($arrA["USER_TEXT"]) <> '')
							{
								?><?=$arrA["USER_TEXT"]?><br /><?
							}
							if (trim($arrA["ANSWER_TEXT"]) <> '')
							{
								?><span class='form-anstext'><?=$arrA["ANSWER_TEXT"]?></span>&nbsp;<?
							}
							if (trim($arrA["ANSWER_VALUE"]) <> '' && $arParams["SHOW_ANSWER_VALUE"]=="Y")
							{
								?>(<span class='form-ansvalue'><?=$arrA["ANSWER_VALUE"]?></span>)<?
							}?>
									<br />
									<?
									if (intval($arrA["USER_FILE_ID"])>0)
									{
										if ($arrA["USER_FILE_IS_IMAGE"]=="Y")
										{
										?>
											<?=$arrA["USER_FILE_IMAGE_CODE"]?>
										<?
										}
										else
										{
										?>
										<a title="<?=GetMessage("FORM_VIEW_FILE")?>" target="_blank" href="/bitrix/tools/form_show_file.php?rid=<?=$arRes["ID"]?>&hash=<?=$arrA["USER_FILE_HASH"]?>&lang=<?=LANGUAGE_ID?>"><?=$arrA["USER_FILE_NAME"]?></a><br />
										(<?=$arrA["USER_FILE_SIZE_TEXT"]?>)<br />
										[&nbsp;<a title="<?=str_replace("#FILE_NAME#", $arrA["USER_FILE_NAME"], GetMessage("FORM_DOWNLOAD_FILE"))?>" href="/bitrix/tools/form_show_file.php?rid=<?=$arRes["ID"]?>&hash=<?=$arrA["USER_FILE_HASH"]?>&lang=<?=LANGUAGE_ID?>&action=download"><?=GetMessage("FORM_DOWNLOAD")?></a>&nbsp;]
										<?
										}
									}
									?>
						<?
						} //foreach
					} // endif (is_array($arrAnswer));
					?>
				</td>
				<?
					} //endif (($arrC["ADDITIONAL"]=="Y" && $SHOW_ADDITIONAL=="Y") || $arrC["ADDITIONAL"]!="Y") ;
					} // endif (!is_array($arrNOT_SHOW_TABLE) || !in_array($arrC["SID"],$arrNOT_SHOW_TABLE));
				} //foreach
				?>
			</tr>
			<?
			} //foreach
			?>
			</tbody>
		<?
		}
		?>
		<?
		if ($arParams["HIDE_TOTAL"]!="Y")
		{
		?>
		<tfoot>
			<tr>
				<th colspan="<?=$colspan?>"><?=GetMessage("FORM_TOTAL")?>&nbsp;<?=$arResult["res_counter"]?></th>
			</tr>
		</tfoot>
		<?
		} //endif ($HIDE_TOTAL!="Y");
		?>
	</table>

	<p><?=$arResult["pager"]?></p>
	<?
	if (intval($arResult["res_counter"])>0 && $arParams["SHOW_STATUS"]=="Y" && $arParams["F_RIGHT"] >= 20)
	{
	?>
	<p>
	<input type="submit" name="save" value="<?=GetMessage("FORM_SAVE")?>" /><input type="hidden" name="save" value="Y" />&nbsp;<input type="reset" value="<?=GetMessage("FORM_RESET")?>" />
	</p>
	<?
	} //endif (intval($res_counter)>0 && $SHOW_STATUS=="Y" && $F_RIGHT>=20);
	?>

	<?
	if ($arParams["can_delete_some"])
	{
	?>
	<p><input type="submit" name="delete" value="<?=GetMessage("FORM_DELETE_SELECTED")?>" onClick="return OnDelete_<?=$arResult["filter_id"]?>()" /></p>
	<?
	} //endif ($can_delete_some);
	?>
</form>