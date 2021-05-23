<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<table cellpadding="0" cellspacing="0" border="0" align="center" style="border-collapse: collapse;mso-table-lspace: 0;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
	<tr>
		<td valign="top" style="border-collapse: collapse;border-spacing: 0;text-align: left;vertical-align: top;">
			<table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;mso-table-lspace: 0;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 14px;width: 100%;">
				<tr>
					<td align="left" valign="top" style="border-collapse: collapse;border-spacing: 0;padding: 3px 0 8px;text-align: left;">
						<table style="border-collapse: collapse;mso-table-lspace: 0;">
							<tr>
								<td align="left" valign="middle" style="border-collapse: collapse;border-spacing: 0;padding: 3px 10px 8px 0;text-align: left;"><?
									$src = $arResult["AUTHOR"]["AVATAR_URL"];
									?><img height="50" width="50" src="<?=$src?>" alt="user" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;border-radius: 50%;display: block;">
								</td>
								<td style="border-collapse: collapse;border-spacing: 0;padding: 0;">
									<span style="color:#586777;font-size: 14px;font-weight: bold;vertical-align: top; text-decoration: none;"><?
									?><?=$arResult["AUTHOR"]["NAME_FORMATTED"]?></span><?
									if (!empty($arResult["DESTINATIONS"]))
									{
										$src = "/bitrix/components/bitrix/socialnetwork.blog.post.mail/templates/.default/images/arrow.gif";
										?><img height="16" width="20" src="<?=$src?>" alt="&rarr;" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;display: inline;font-size: 19px;vertical-align: top;line-height: 15px;"><?
										?><span style="color: #7f7f7f;font-size: 14px;vertical-align: top;"><?
										$i = 0;
										foreach ($arResult["DESTINATIONS"] as $destinationName)
										{
											if ($i > 0)
											{
												?>, <?
											}
											?><span style="color: #7f7f7f;font-size: 14px;vertical-align: top;"><?=($destinationName == "#ALL#" ? GetMessage("BLOG_DESTINATION_ALL") : $destinationName)?></span><?
											$i++;
										}
										?></span><?
									}
								?></td>
							</tr>
						</table>
					</td>
				</tr><?
				if ($arResult["POST"]["MICRO"] != "Y")
				{
					?><tr>
						<td valign="top" style="border-collapse: collapse;border-spacing: 0;color: #000000;font-size: 14px;font-weight: bold;padding: 0 0 14px;vertical-align: top;"><?=$arResult["POST"]["TITLE_FORMATTED"]?></td>
					</tr><?
				}
				?><tr>
					<td valign="top" style="border-collapse: collapse;border-spacing: 0;color: #000000;font-size: 14px;vertical-align: top;padding: 0 0 38px;"><?=$arResult["POST"]["DETAIL_TEXT_FORMATTED"]?></td>
				</tr><?
				if (!empty($arResult["POST"]["ATTACHMENTS"]))
				{
					?><tr>
						<td valign="top" style="border-collapse: collapse;border-spacing: 0;padding: 0 0 32px;">
							<table cellpadding="0" cellspacing="0" border="0" align="left" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 13px;">
								<tr>
									<td valign="top" style="border-collapse: collapse;border-spacing: 0;color: #8d8d8d;padding: 0 5px 0 0;vertical-align: top;"><?=GetMessage('SBPM_TEMPLATE_FILES')?></td>
									<td valign="top" style="border-collapse: collapse;border-spacing: 0; padding: 0;">
										<?
										$i = 0;
										foreach($arResult["POST"]["ATTACHMENTS"] as $attachment)
										{
											if ($i > 0)
											{
												?><br/><?
											}
											?><a href="<?=$attachment["URL"]?>" style="color: #146cc5;"><?=$attachment["NAME"]?> (<?=$attachment["SIZE"]?>)</a><?
											$i++;
										}
										?>
									</td>
								</tr>
							</table>
						</td>
					</tr><?
				}
				?><tr>
					<td valign="top" style="border-collapse: collapse;border-spacing: 0;padding: 0 0 20px;vertical-align: top;">
						<a href="<?=$arResult["POST_URL"]?>" style="color: #0b66c3;font-size: 12px;"><?=GetMessage('SBPM_TEMPLATE_ADD_COMMENT_LINK');?></a>
						&nbsp;
						<a href="<?=$arResult["POST_URL"]?>" style="color: #0b66c3;font-size: 12px;"><?=GetMessage('SBPM_TEMPLATE_LIKE')?></a><?

						if (false)
						{
							?>
							&nbsp;
							<a href="<?=$arResult["POST_URL"]?>" style="color: #0b66c3;font-size: 12px;"><?=GetMessage('SBPM_TEMPLATE_MORE')?></a>
							<?
						}
					?></td>
				</tr>
				<tr>
					<td valign="top" align="center" style="border-collapse: collapse;border-spacing: 0;border-top: 1px solid #edeef0;padding: 33px 0 20px;">
						<table cellspacing="0" cellpadding="0" border="0" align="center" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
							<tr>
								<td style="border-collapse: collapse;border-spacing: 0;background-color: #44c8f2;padding: 0;">
									<a target="_blank" href="<?=$arResult["POST_URL"]?>" style="color: #ffffff;background-color: #44c8f2;border: 8px solid #44c8f2;border-radius: 2px;display: block;font-family: Helvetica, Arial, sans-serif;font-size: 12px;font-weight: bold;padding: 4px;text-transform: uppercase;text-decoration: none;text-align:center;"><?=GetMessage('SBPM_TEMPLATE_ADD_COMMENT_BUTTON');?></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" style="border-collapse: collapse;border-spacing: 0;color: #8b959d;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 11px;text-align: center;padding: 14px 0 0;"><?=GetMessage('SBPM_TEMPLATE_DESCRIPTION', array("#BR#" => '<br/>'))?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>