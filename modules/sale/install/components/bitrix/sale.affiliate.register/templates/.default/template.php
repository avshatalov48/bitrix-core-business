<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if ($arResult["AFFILIATE"] == "Y")
{
	if ($arResult["UNACTIVE_AFFILIATE"] == "Y")
	{
		?><?=ShowError(GetMessage("SPCR1_UNACTIVE_AFF"))?><?
	}
}
else
{
	if ($arResult["USER_AUTHORIZED"] == "N")
	{
		?>
		<?=ShowError($arResult["ERROR_MESSAGE"])?>
		<table class="affiliate-formatting-table">
			<tbody>
				<tr>
					<td>
						<form method="post" action="<?=$arResult["CURRENT_PAGE"]?>" name="sale_auth_form">
						<?=bitrix_sessid_post()?>
						<table class="data-table">
							<thead>
								<tr>
									<td>
										<b><?=GetMessage("SPCR1_IF_REG")?></b>
									</td>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<?=GetMessage("SPCR1_IF_REMEMBER")?>
									</td>
								</tr>
								<tr>
									<td>
											<?=GetMessage("SPCR1_LOGIN")?> <span class="starrequired">*</span><br />
											<input type="text" name="USER_LOGIN" maxlength="25" size="25" value="<?=$arResult["DEFAULT_USER_LOGIN"]?>" />&nbsp;&nbsp;&nbsp;
									</td>
								</tr>
								<tr>
									<td>
											<?=GetMessage("SPCR1_PASSWORD")?> <span class="starrequired">*</span><br />
											<input type="password" name="USER_PASSWORD" maxlength="25" size="25" />&nbsp;&nbsp;&nbsp;
										</td>
									</tr>
									<?if (strlen($arParams["REGISTER_PAGE"]) > 0):?>
									<tr>
										<td>
											<a href="<?=$arParams["REGISTER_PAGE"]?>?forgot_password=yes&amp;back_url=<?=urlencode($arResult["CURRENT_PAGE"]) ?>"><?=GetMessage("SPCR1_FORG_PASSWORD")?></a>
										</td>
									</tr>
									<?endif?>
								</tbody>
								<tfoot>
									<tr>
										<td>
											<input type="submit" value="<?=GetMessage("SPCR1_NEXT")?>" />
											<input type="hidden" name="do_authorize" value="Y" />
											<input type="hidden" name="REDIRECT_PAGE" value="<?=$arResult["REDIRECT_PAGE"]?>" />
										</td>
									</tr>
								</tfoot>
						</table>
						</form>						
					</td>
					<td>
						&nbsp;
					</td>
					<td>
						<form method="post" action="<?=$arResult["CURRENT_PAGE"] ?>" name="sale_reg_form">
						<?=bitrix_sessid_post()?>
						<table class="data-table">
							<thead>
									<td>
										<b><?=GetMessage("SPCR1_IF_NOT_REG")?></b>
									</td>
								</tr>
							</thead>						
							<tbody>
							<tr>
								<td>
									<?=GetMessage("SPCR1_NAME")?> <span class="starrequired">*</span><br />
									<input type="text" name="NEW_NAME" size="30" value="<?=$arResult["NEW_NAME"]?>" />&nbsp;&nbsp;&nbsp;
								</td>
							</tr>
							<tr>
								<td>
									<?=GetMessage("SPCR1_LASTNAME")?> <span class="starrequired">*</span><br />
									<input type="text" name="NEW_LAST_NAME" size="30" value="<?=$arResult["NEW_LAST_NAME"]?>" />&nbsp;&nbsp;&nbsp;
								</td>
							</tr>
							<tr>
								<td>
									E-Mail <span class="starrequired">*</span><br />
									<input type="text" name="NEW_EMAIL" size="30" value="<?=$arResult["NEW_EMAIL"]?>" />&nbsp;&nbsp;&nbsp;
								</td>
							</tr>
							<tr>
								<td>
									<?echo GetMessage("SPCR1_LOGIN")?> <span class="starrequired">*</span><br />
									<input type="text" name="NEW_LOGIN" size="30" value="<?=$arResult["NEW_LOGIN"]?>" />&nbsp;&nbsp;&nbsp;
								</td>
							</tr>
							<tr>
								<td>
									<?=GetMessage("SPCR1_PASSWORD")?> <span class="starrequired">*</span><br />
									<input type="password" name="NEW_PASSWORD" size="30" />&nbsp;&nbsp;&nbsp;
								</td>
							</tr>
							<tr>
								<td>
									<?=GetMessage("SPCR1_PASS_CONF")?> <span class="starrequired">*</span><br />
									<input type="password" name="NEW_PASSWORD_CONFIRM" size="30" />&nbsp;&nbsp;&nbsp;
								</td>
							</tr>
							<?
							if ($arResult["CAPTCHA_CODE"])
							{
								?>
								<tr>
									<td>
										<b><?=GetMessage("SPCR1_CAPTCHA")?></b>
									</td>
								</tr>
								<tr>
									<td>
										<input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
										<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" />
									</td>
								</tr>
								<tr>
									<td>
										<span class="starrequired">*</span>&nbsp;<?=GetMessage("SPCR1_CAPTCHA_WRD")?><br />
										<input type="text" name="captcha_word" size="30" maxlength="50" value="" />
									</td>
								</tr>
								<?
							}
							?>
							</tbody>
							<tfoot>
								<tr>
									<td>
										<input type="submit" value="<?=GetMessage("SPCR1_NEXT")?>" />
										<input type="hidden" name="do_register" value="Y" />
										<input type="hidden" name="REDIRECT_PAGE" value="<?=$arResult["REDIRECT_PAGE"]?>" />
									</td>
								</tr>
							</tfoot>
						</table>
						</form>
					</td>
				</tr>
			</tbody>
		</table>
		<?
	}
	else
	{
		?>
		<?=ShowError($arResult["ERROR_MESSAGE"])?>
		<form method="post" action="<?=$arResult["CURRENT_PAGE"]?>">
			<?=bitrix_sessid_post()?>
			<table class="data-table">
				<tbody>
				<tr>
					<td>
						<?=GetMessage("SPCR1_SITE_URL")?> <span class="starrequired">*</span><br />
						<input type="text" name="AFF_SITE" maxlength="200" value="<?=$arResult["AFF_SITE"]?>" />&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
				<tr>
					<td>
						<?=GetMessage("SPCR1_SITE_DESCR")?> <span class="starrequired">*</span><br />
						<textarea name="AFF_DESCRIPTION"><?=$arResult["AFF_DESCRIPTION"]?></textarea>
					</td>
				</tr>

				<?
				if ($arResult["AGREEMENT_TEXT_FILE"])
				{
					?>
					<tr>
						<td><iframe class="affiliate-agreement-text" name="agreement_text" src="<?=$arResult["AGREEMENT_TEXT_FILE"]?>"></iframe>
						</td>
					</tr>
					<?
				}
				?>

				<tr>
					<td>
						<input type="checkbox" name="agree_agreement" value="Y" id="agree_agreement_id" />
						&nbsp;<label for="agree_agreement_id"><?=GetMessage("SPCR1_I_AGREE")?></label>
					</td>
				</tr>
				</tbody>

				<tfoot>
					<tr>
						<td>
							<input type="hidden" name="do_agree" value="Y" />
							<input type="hidden" name="REDIRECT_PAGE" value="<?=$arResult["REDIRECT_PAGE"]?>" />
							<input type="submit" value="<?=GetMessage("SPCR1_REGISTER")?>" />
						</td>
					</tr>
				</tfoot>
			</table>
		</form>
		<?
	}
}
?>