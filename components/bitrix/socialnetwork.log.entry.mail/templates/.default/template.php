<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

?>
<table cellpadding="0" cellspacing="0" border="0" align="center" style="border-collapse: collapse;mso-table-lspace: 0;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
	<tr>
		<td valign="top" style="border-collapse: collapse;border-spacing: 0;text-align: left;vertical-align: top;">
			<table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;mso-table-lspace: 0;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 14px;width: 100%;">
				<tr>
					<td align="left" valign="top" style="border-collapse: collapse;border-spacing: 0;padding: 3px 0 8px;text-align: left;">
						<table style="border-collapse: collapse;mso-table-lspace: 0;">
							<tr>
								<td align="left" valign="middle" style="border-collapse: collapse;border-spacing: 0;padding: 3px 10px 8px 0;text-align: left;"><?
									$src = (
										isset($arResult["LOG_ENTRY"]["AVATAR_SRC"])
										&& $arResult["LOG_ENTRY"]["AVATAR_SRC"] <> ''
											? $arResult["LOG_ENTRY"]["AVATAR_SRC"]
											: $this->getFolder().'/images/userpic.gif'
									);
									?><img height="50" width="50" src="<?=$src?>" alt="user" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;border-radius: 50%;display: block;">
								</td>
								<td style="border-collapse: collapse;border-spacing: 0;padding: 0;">
									<span style="color:#586777;font-size: 14px;font-weight: bold;vertical-align: top; text-decoration: none;"><?

									$authorName = (
										isset($arResult["LOG_ENTRY"]["CREATED_BY"])
										&& isset($arResult["LOG_ENTRY"]["CREATED_BY"]["TOOLTIP_FIELDS"])
										&& !empty($arResult["LOG_ENTRY"]["CREATED_BY"]["TOOLTIP_FIELDS"])
											? CUser::FormatName(CSite::getNameFormat(false, $arResult["SITE"]["ID"]), $arResult["LOG_ENTRY"]["CREATED_BY"]["TOOLTIP_FIELDS"], true)
											: ''
									);

									?><?=$authorName?></span><?
									if (
										isset($arResult["DESTINATIONS"])
										&& !empty($arResult["DESTINATIONS"])
									)
									{
										$src = $this->getFolder().'/images/arrow.gif';
										?><img height="16" width="20" src="<?=$src?>" alt="&rarr;" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;display: inline;font-size: 19px;vertical-align: top;line-height: 15px;"><?
										?><span style="color: #7f7f7f;font-size: 14px;vertical-align: top;"><?
										$i = 0;
										foreach ($arResult["DESTINATIONS"] as $destination)
										{
											if ($i > 0)
											{
												?>, <?
											}
											?><span style="color: #7f7f7f;font-size: 14px;vertical-align: top;"><?=$destination["TITLE"]?></span><?
											$i++;
										}
										?></span><?
									}
								?></td>
							</tr>
						</table>
					</td>
				</tr><?
				if (empty($arResult["COMMENTS"]))
				{
					if (
						!empty($arResult["LOG_ENTRY"]["EVENT"]["TITLE"])
						&& $arResult["LOG_ENTRY"]["EVENT"]["TITLE"] != '__EMPTY__'
					)
					{
						?><tr>
						<td valign="top" style="border-collapse: collapse;border-spacing: 0;color: #000000;font-size: 14px;font-weight: bold;padding: 0 0 14px;vertical-align: top;"><?=$arResult["LOG_ENTRY"]["EVENT"]["TITLE"]?></td>
						</tr><?
					}
					?><tr>
						<td valign="top" style="border-collapse: collapse;border-spacing: 0;color: #000000;font-size: 14px;vertical-align: top;padding: 0 0 38px;"><?=$arResult["LOG_ENTRY"]["EVENT"]["MESSAGE_FORMATTED"]?></td>
					</tr><?
					if (!empty($arResult["LOG_ENTRY"]["ATTACHMENTS"]))
					{
						?><tr>
						<td valign="top" style="border-collapse: collapse;border-spacing: 0;padding: 0 0 32px;">
							<table cellpadding="0" cellspacing="0" border="0" align="left" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 13px;">
								<tr>
									<td valign="top" style="border-collapse: collapse;border-spacing: 0;color: #8d8d8d;padding: 0 5px 0 0;vertical-align: top;"><?=GetMessage('SLEM_TEMPLATE_FILES')?></td>
									<td valign="top" style="border-collapse: collapse;border-spacing: 0; padding: 0;">
										<?
										$i = 0;
										foreach($arResult["LOG_ENTRY"]["ATTACHMENTS"] as $attachment)
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
						<a href="<?=$arResult["LOG_ENTRY_URL"]?>" style="color: #0b66c3;font-size: 12px;"><?=GetMessage('SLEM_TEMPLATE_ADD_COMMENT_LINK');?></a>
						&nbsp;
						<a href="<?=$arResult["LOG_ENTRY_URL"]?>" style="color: #0b66c3;font-size: 12px;"><?=GetMessage('SLEM_TEMPLATE_LIKE')?></a><?
						?></td>
					</tr>
					<?
				}
				else
				{
					?>
					<tr>
						<td style="border-collapse: collapse;border-spacing: 0;">
							<table cellspacing="0" cellpadding="0" border="0" align="left" style="border-collapse: collapse;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
								<tr>
									<td valign="top" style="border-collapse: collapse;border-spacing: 0;padding: 13px 0 14px;vertical-align: top;"><?
										$src = $this->getFolder()."/images/post-icon.gif";
										?><img width="30" height="29" alt="" src="<?=$src?>" style="outline: none;text-decoration: none;vertical-align: top;"><?
									?></td>
									<td valign="middle" style="border-collapse: collapse;border-spacing: 0;padding: 9px 0 14px 16px;vertical-align: top;">
										<a href="<?=$arResult["LOG_ENTRY_URL_COMMENT"]?>" style="color: #0067a3;border-bottom: 1px solid #0067a3;font-size: 14px;line-height: 18px;text-decoration: none;">
											<!--[if gte mso 9]>
											<span style="text-decoration: underline;">
											<![endif]-->
											<?=$arResult["LOG_ENTRY"]["TITLE_FORMATTED"]?>
											<!--[if gte mso 9]>
											</span>
											<![endif]-->
										</a>
									</td>
								</tr>
							</table>
						</td>
					</tr><?
				}

				if (!empty($arResult["COMMENTS"]))
				{
					$commentsHtml = $APPLICATION->IncludeComponent(
						"bitrix:main.post.list",
						"mail",
						array(
							"RECORDS" => $arResult["COMMENTS"],
							"PREORDER" => "N",
							"AVATAR_SIZE" => $arParams["AVATAR_SIZE_COMMENT"],
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
							"RECIPIENT_ID" => $arParams["RECIPIENT_ID"],
							"SITE_ID" => $arResult["SITE"]["ID"],
							"SHOW_LOGIN" => "Y",
							"COMMENTS_COUNT" => count($arResult["COMMENTS"]),
							"COMMENTS_ALL_COUNT" => $arResult["COMMENTS_ALL_COUNT"],
							"POST_URL" => $arResult["LOG_ENTRY_URL"],
							"HIGHLIGHT" => "Y"
						)
					);

					?><?=$commentsHtml["HTML"]?><?
				}
				?>
				<tr>
					<td valign="top" align="center" style="border-collapse: collapse;border-spacing: 0;border-top: 1px solid #edeef0;padding: 33px 0 20px;">
						<table cellspacing="0" cellpadding="0" border="0" align="center" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
							<tr>
								<td style="border-collapse: collapse;border-spacing: 0;background-color: #44c8f2;padding: 0;">
									<a target="_blank" href="<?=$arResult["LOG_ENTRY_URL"]?>" style="color: #ffffff;background-color: #44c8f2;border: 8px solid #44c8f2;border-radius: 2px;display: block;font-family: Helvetica, Arial, sans-serif;font-size: 12px;font-weight: bold;padding: 4px;text-transform: uppercase;text-decoration: none;text-align:center;"><?=GetMessage('SLEM_TEMPLATE_ADD_COMMENT_BUTTON');?></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" style="border-collapse: collapse;border-spacing: 0;color: #8b959d;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 11px;text-align: center;padding: 14px 0 0;"><?=GetMessage('SLEM_TEMPLATE_DESCRIPTION', array("#BR#" => '<br/>'))?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>