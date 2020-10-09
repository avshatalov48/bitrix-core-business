<br>
<table cellpadding="1" cellspacing="0" width="35%" bgcolor="#9C9A9C">
	<tr>
		<td><table cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td bgcolor="#FFFFFF" align="center">
					<FONT face="Verdana, Arial, Helvetica, sans-serif" size="-1">
					<font color="#FF0000"><b><?echo "DB query error."?></b></font><br>
					Please try later.
					</font><br>
					<?if (is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin()):?>
						</form>
						<form method="post" action="https://www.1c-bitrix.ru/support/">
							<?
							$strSupportErrorText = "";
							$strSupportErrorText .= "File: ".__FILE__."\n";

							if ($error_position <> '')
								$strSupportErrorText .= "[".$error_position."]\n";
							if ($strSql <> '')
								$strSupportErrorText .= "Query: ".$strSql."\n";
							if (isset($this) && is_object($this) && $this->db_Error <> '')
								$strSupportErrorText .= "[".$this->db_Error."]\n";

							$d = Bitrix\Main\Diag\Helper::getBackTrace();
							$trace = array();  // due to memory limitations
							foreach($d as $tmp)
							{
								$trace[] = array(
									'file' => $tmp['file'],
									'line' => $tmp['line'],
									'class' =>  $tmp['class'],
									'function' => $tmp['function'],
									'args' => $tmp['args']
								);
							}
							$strSupportErrorText .= "debug_backtrace:\n".print_r($trace, True)."\n";
							?>
							<input type="hidden" name="last_error_query" value="<?= htmlspecialcharsbx($strSupportErrorText) ?>">
							<?echo bitrix_sessid_post();?>
							<input type="hidden" name="send_ticket" value="Y">
							<input type="submit" value="Send error report to support">
						</form>
					<?endif;?>
				</td>
			</tr>
		</table></td>
	</tr>
</table>
<br><br><br>
