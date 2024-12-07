<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<table>
<tr>
	<td valign="top" width="60%">
		<?
		if (!empty($arResult["ORDER"]))
		{
			?>
			<b><?echo GetMessage("STOF_ORDER_CREATED")?></b><br /><br />
			<table class="sale_order_full_table">
				<tr>
					<td>
						<?= str_replace("#ORDER_DATE#", $arResult["ORDER"]["DATE_INSERT_FORMATED"], str_replace("#ORDER_ID#", $arResult["ORDER"]["ACCOUNT_NUMBER"], GetMessage("STOF_ORDER_CREATED_DESCR"))); ?><br /><br />
						<?= str_replace("#LINK#", $arParams["PATH_TO_PERSONAL"], GetMessage("STOF_ORDER_VIEW")) ?>
					</td>
				</tr>
			</table>
			<?
			if (!empty($arResult["PAY_SYSTEM"]))
			{
				?>
				<br /><br />
				<b><?echo GetMessage("STOF_ORDER_PAY_ACTION")?></b><br /><br />

				<table class="sale_order_full_table">
					<tr>
						<td>
							<?echo GetMessage("STOF_ORDER_PAY_ACTION1")?> <?= $arResult["PAY_SYSTEM"]["NAME"] ?>
						</td>
					</tr>
					<?
					if ($arResult["PAY_SYSTEM"]["ACTION_FILE"] <> '')
					{
						?>
						<tr>
							<td>
								<?
								if ($arResult["PAY_SYSTEM"]["NEW_WINDOW"] == "Y")
								{
									?>
									<script>
										window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?=urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"]))?>');
									</script>
									<?= str_replace("#LINK#", $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"])), GetMessage("STOF_ORDER_PAY_WIN")) ?>
									<?
								}
								else
								{
									if ($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"] <> '')
									{
										try
										{
											include($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"]);
										}
										catch(\Bitrix\Main\SystemException $e)
										{
											if($e->getCode() == CSalePaySystemAction::GET_PARAM_VALUE)
												$message = GetMessage("SOA_TEMPL_ORDER_PS_ERROR");
											else
												$message = $e->getMessage();

											echo '<span style="color:red;">'.$message.'</span>';
										}
									}
								}
								?>
							</td>
						</tr>
						<?
					}
					?>
				</table>
				<?
			}
		}
		else
		{
			?>
			<b><?echo GetMessage("STOF_ERROR_ORDER_CREATE")?></b><br /><br />

			<table class="sale_order_full_table">
				<tr>
					<td>
						<?=str_replace("#ORDER_ID#", $arResult["ORDER_ID"], GetMessage("STOF_NO_ORDER"))?>
						<?=GetMessage("STOF_CONTACT_ADMIN")?>
					</td>
				</tr>
			</table>
			<?
		}
		?>
	</td>
	<td valign="top" width="5%">&nbsp;</td>
	<td valign="top" width="35%">
		<?= str_replace("#LINK#", $arParams["PATH_TO_PERSONAL"], GetMessage("STOF_ORDER_VIEW")) ?><br /><br />
		<?= str_replace("#LINK#", $arParams["PATH_TO_PERSONAL"], GetMessage("STOF_ANNUL_NOTES")) ?><br /><br />
		<?= str_replace("#ORDER_ID#", $arResult["ORDER_ID"], GetMessage("STOF_ORDER_ID_NOTES")) ?>
	</td>
</tr>
</table>