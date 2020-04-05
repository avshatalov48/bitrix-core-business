<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="filter-box filter-people">
	<form action="<?= $arResult["Urls"]["UserSearch"] ?>" class="bx-selector-form filter-form" name="bx_users_filter_adv_form">
		<input type="hidden" name="current_view" value="<?=htmlspecialcharsbx($arResult['CURRENT_VIEW'])?>" />
		<input type="hidden" name="current_filter" value="adv" />
		<div class="filter-item filter-name">
			<label for="filter-name"><?= GetMessage("SONET_C241_T_FIO") ?>:</label>
			<input id="filter-name" name="FLT_FIO" class="filter-textbox" value="<?= htmlspecialcharsex($_REQUEST["fltx_fio"]) ?>"/>
		</div>
		<?foreach ($arResult["UserFieldsSearchAdv"] as $userFieldName => $userFieldDescr):?>
			<div class="filter-item">
				<label for="filter-<?= $userFieldName; ?>"><?= $userFieldDescr["TITLE"] ?>:</label>
				<?
				if (StrToLower(SubStr($userFieldDescr["NAME"], 0, 5)) == "fltx_")
				{
					$keyTmp = StrToUpper(SubStr($userFieldDescr["NAME"], 5));
					$userFieldDescr["NAME"] = "flt_".$keyTmp;
				}
				
				if ($userFieldDescr["TYPE"] == "exact"):
					echo $userFieldDescr["STRING"];
				elseif ($userFieldDescr["TYPE"] == "select"):
					?><select name="<?= $userFieldDescr["NAME"] ?>" id="filter-<?= $userFieldName; ?>" class="filter-select">
						<option value=""></option>
						<?foreach ($userFieldDescr["VALUES"] as $keyTmp => $valTmp):?>
							<option value="<?= $keyTmp ?>"<?= (($keyTmp == $userFieldDescr["VALUE"]) ? " selected" : "") ?>><?= $valTmp ?></option>
						<?endforeach;?>
					</select><?
				elseif ($userFieldDescr["TYPE"] == "string"):
					?><input type="text" id="filter-<?= $userFieldName; ?>" class="filter-textbox" name="<?= $userFieldDescr["NAME"] ?>" value="<?= $userFieldDescr["VALUE"] ?>" /><?
				elseif ($userFieldDescr["TYPE"] == "calendar"):
					echo "<nobr>";
					$APPLICATION->IncludeComponent(
						'bitrix:main.calendar', 
						'.default', 
						array(
							'SHOW_INPUT' => 'Y',
							'FORM_NAME' => 'bx_users_filter_adv_form',
							'INPUT_NAME' => $userFieldDescr["NAME"],
							'INPUT_VALUE' => $userFieldDescr["VALUE"],
							'SHOW_TIME' => 'N',
						), 
						null, 
						array('HIDE_ICONS' => 'Y')
					);
					echo "</nobr>";
				endif;
				?>
			</div>
		<?endforeach;?>
		<?foreach ($arResult["UserPropertiesSearchAdv"] as $userFieldName => $userFieldDescr):?>
			<div class="filter-item">
				<label for="filter-<?= $userFieldName; ?>"><?= $userFieldDescr["EDIT_FORM_LABEL"] ?>:</label>
				<?
				if (StrToLower(SubStr($userFieldDescr["FIELD_NAME"], 0, 5)) == "fltx_")
				{
					$keyTmp = StrToUpper(SubStr($userFieldDescr["FIELD_NAME"], 5));
					$userFieldDescr["FIELD_NAME"] = "flt_".$keyTmp;
				}
						
				if (StrToLower(SubStr($userFieldDescr["FIELD_NAME"], 0, 4)) == "flt_")
				{
					$keyTmp = StrToLower(SubStr($userFieldDescr["FIELD_NAME"], 4));
					if (array_key_exists("fltx_".$keyTmp, $_REQUEST))
					{
						$_REQUEST["flt_".StrToUpper($keyTmp)] = $_REQUEST["fltx_".$keyTmp];
						unset($_REQUEST["fltx_".StrToUpper($keyTmp)]);
					}
				}

				$APPLICATION->IncludeComponent(
					'bitrix:system.field.edit', 
					$userFieldDescr['USER_TYPE_ID'], 
					array(
						"arUserField" => $userFieldDescr,
						'form_name' => 'bx_users_filter_adv_form',
						"bVarsFromForm" => true
					),
					null,
					array('HIDE_ICONS' => 'Y')
				);

				if (StrToLower(SubStr($userFieldDescr["FIELD_NAME"], 0, 4)) == "flt_")
				{
					$keyTmp = StrToLower(SubStr($userFieldDescr["FIELD_NAME"], 4));
					if (array_key_exists("flt_".$keyTmp, $_REQUEST))
					{
						$_REQUEST["fltx_".StrToUpper($keyTmp)] = $_REQUEST["flt_".StrToUpper($keyTmp)];
						unset($_REQUEST["flt_".StrToUpper($keyTmp)]);
					}
				}
				?>
			</div>
		<?endforeach;?>
		
		<div class="filter-button">
			<input type="submit" name="set_filter" value="<?echo GetMessage('SONET_C241_T_DO_SEARCH')?>" class="filter-submit" />
			<input type="reset" name="del_filter" value="<?echo GetMessage('SONET_C241_T_DO_CANCEL')?>" class="filter-submit filter-inline" onclick="window.location='<?= $arResult["Urls"]["UserSearch"] ?>'" />
		</div>
	</form>
</div>