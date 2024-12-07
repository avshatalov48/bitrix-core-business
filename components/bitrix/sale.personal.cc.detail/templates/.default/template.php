<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<a name="tb"></a>
<a href="<?=$arParams["PATH_TO_LIST"]?>"><?=GetMessage("STPC_TO_LIST")?></a>
<br /><br />
<? ShowError($arResult["ERROR_MESSAGE"])?>
<form method="post" action="<?=POST_FORM_ACTION_URI?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value="<?=$arResult["ID"]?>">

<table class="sale_personal_cc_detail data-table">
	<?if ($arResult["ID"] > 0):?>
		<tr>
			<td align="right"><?echo GetMessage("STPC_TIMESTAMP")?></td>
			<td><?= $arResult["TIMESTAMP_X"] ?></td>
		</tr>
	<?endif;?>
	<tr>
		<td align="right"><?echo GetMessage("STPC_ACTIV")?></td>
		<td>
			<input type="hidden" name="ACTIVE" value="">
			<input type="checkbox" name="ACTIVE" value="Y"<?if ($arResult["ACTIVE"]=="Y") echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td width="50%" align="right"><?echo GetMessage("STPC_SORT")?></td>
		<td width="50%">
			<input type="text" name="SORT" size="10" maxlength="20" value="<?= $arResult["SORT"] ?>">
		</td>
	</tr>
	<tr>
		<td width="50%" align="right"><?echo GetMessage("STPC_PAY_SYSTEM")?></td>
		<td width="50%">
			<select name="PAY_SYSTEM_ACTION_ID">
				<?
				foreach($arResult["PAY_SYSTEM"] as $val)
				{
					?><option value="<?= $val["ID"] ?>"<?if (intval($arResult["PAY_SYSTEM_ACTION_ID"]) == intval($val["ID"])) echo " selected";?>><?= $val["PS_NAME"]." - ".$val["PT_NAME"]?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right"><?echo GetMessage("STPC_CURRENCY")?></td>
		<td>
			<select name="CURRENCY">
				<option value=""><?=GetMessage("STPC_ANY")?></option>
				<?
				foreach($arResult["CURRENCY_INFO"] as $val)
				{
					?><option value="<?= $val["CURRENCY"] ?>"<?if ($arResult["CURRENCY"] == $val["CURRENCY"]) echo " selected";?>><?= $val["FULL_NAME"]?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right"><?echo GetMessage("STPC_TYPE")?></td>
		<td>
			<select name="CARD_TYPE">
				<?foreach($arResult["CARD_TYPE_INFO"] as $k => $v)
				{
					?><option value="<?=$k?>"<?if ($arResult["CARD_TYPE"] == $k) echo " selected";?>><?=$v?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td width="50%" align="right"><span class="req">*</span><?echo GetMessage("STPC_CNUM")?></td>
		<td width="50%">
			<input type="text" name="CARD_NUM" size="30" maxlength="30" value="<?= $arResult["CARD_NUM"] ?>">
		</td>
	</tr>
	<tr>
		<td width="50%" align="right"><?echo GetMessage("STPC_CEXP")?></td>
		<td width="50%">
			<select name="CARD_EXP_MONTH">
				<?
				for ($i = 1; $i <= 12; $i++)
				{
					?><option value="<?= $i ?>"<?if (intval($arResult["CARD_EXP_MONTH"]) == $i) echo " selected";?>><?= ((mb_strlen($i) < 2) ? "0".$i : $i) ?></option><?
				}
				?>
			</select>
			<select name="CARD_EXP_YEAR">
				<?
				for ($i = 2007; $i <= 2100; $i++)
				{
					?><option value="<?= $i ?>"<?if (intval($arResult["CARD_EXP_YEAR"]) == $i) echo " selected";?>><?= $i ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td width="50%" align="right">CVC2:</td>
		<td width="50%">
			<input type="text" name="CARD_CODE" size="10" maxlength="10" value="<?= $arResult["CARD_CODE"] ?>">
		</td>
	</tr>
	<tr>
		<td width="50%" align="right"><?echo GetMessage("STPC_MIN_SUM")?></td>
		<td width="50%">
			<input type="text" name="SUM_MIN" size="10" maxlength="10" value="<?= ((DoubleVal($arResult["SUM_MIN"]) > 0) ? roundEx($arResult["SUM_MIN"], SALE_VALUE_PRECISION) : "") ?>">
		</td>
	</tr>
	<tr>
		<td width="50%" align="right"><?echo GetMessage("STPC_MAX_SUM")?></td>
		<td width="50%">
			<input type="text" name="SUM_MAX" size="10" maxlength="10" value="<?= ((DoubleVal($arResult["SUM_MAX"]) > 0) ? roundEx($arResult["SUM_MAX"], SALE_VALUE_PRECISION) : "") ?>">
		</td>
	</tr>
	<tr>
		<td align="right"><?echo GetMessage("STPC_SUM_CURR")?></td>
		<td>
			<select name="SUM_CURRENCY">
				<?
				foreach($arResult["CURRENCY_INFO"] as $val)
				{
					?><option value="<?= $val["CURRENCY"] ?>"<?if ($arResult["SUM_CURRENCY"] == $val["CURRENCY"]) echo " selected";?>><?= $val["FULL_NAME"]?></option><?
				}
				?>
			</select>
		</td>
	</tr>
</table>
<br />
<div align="left">
	<input type="submit" name="save" value="<?= GetMessage("STPC_SAVE") ?>">
	&nbsp;
	<input type="submit" name="apply" value="<?= GetMessage("STPC_APPLY") ?>">
	&nbsp;
	<input type="submit" name="reset" value="<?= GetMessage("STPC_CANCEL") ?>">
</div>
</form>
