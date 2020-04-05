<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<table cellpadding="0" cellspacing="0" border="0" align="center" style="border-collapse: collapse;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
	<tr>
		<td valign="top" style="border-collapse: collapse;border-spacing: 0;text-align: left;vertical-align: top;">
			<table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;mso-table-lspace: 0;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 14px;width: 100%;">
				<tr>
					<td align="left" valign="top" style="border-collapse: collapse;border-spacing: 0;padding: 3px 0 8px;text-align: left;">
						<table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
							<tr>
								<td align="left" valign="middle" style="border-collapse: collapse;border-spacing: 0;padding: 3px 10px 8px 0;text-align: left;"><?
									$src = $arResult["AUTHORS"][$arResult["POST"]["AUTHOR_ID"]]["AVATAR_URL"];
									?><img height="50" width="50" src="<?=$src?>" alt="user" style="outline: none;text-decoration: none;border-radius: 50%;display: block;">
								</td>
								<td align="left" valign="middle" style="border-collapse: collapse;border-spacing: 0;padding: 3px 0 8px;text-align: left;">
									<span style="color:#586777;font-size: 14px;font-weight: bold;"><?
									?><?=$arResult["AUTHORS"][$arResult["POST"]["AUTHOR_ID"]]["NAME_FORMATTED"]?></span><?
									if (!empty($arResult["DESTINATIONS"]))
									{
										$src = $this->getFolder()."/images/arrow.gif";
										?><img height="16" width="20" src="<?=$src?>" alt="&rarr;" style="outline: none;text-decoration: none;font-size: 19px;line-height: 15px;"><?
										?><span style="color: #7f7f7f;font-size: 14px;"><?
										$i = 0;
										foreach ($arResult["DESTINATIONS"] as $destinationName)
										{
											if ($i > 0)
											{
											?>, <?
											}
											?><span style="color: #7f7f7f;font-size: 14px;"><?=($destinationName == "#ALL#" ? GetMessage("BLOG_DESTINATION_ALL") : $destinationName)?></span><?
											$i++;
										}
										?></span><?
									}
								?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="border-collapse: collapse;border-spacing: 0;">
						<table cellspacing="0" cellpadding="0" border="0" align="left" style="border-collapse: collapse;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
							<tr>
								<td valign="top" style="border-collapse: collapse;border-spacing: 0;padding: 13px 0 14px;vertical-align: top;"><?
									$src = $this->getFolder()."/images/post-icon.gif";
									?><img width="30" height="29" alt="" src="<?=$src?>" style="outline: none;text-decoration: none;vertical-align: top;"><?
								?></td>
								<td valign="middle" style="border-collapse: collapse;border-spacing: 0;padding: 9px 0 14px 16px;vertical-align: top;">
									<a href="<?=$arResult["POST_URL_COMMENT"]?>" style="color: #0067a3;border-bottom: 1px solid #0067a3;font-size: 14px;line-height: 18px;text-decoration: none;">
										<!--[if gte mso 9]>
										<span style="text-decoration: underline;">
										<![endif]-->
										<?=$arResult["POST"]["TITLE_FORMATTED"]?>
										<!--[if gte mso 9]>
										</span>
										<![endif]-->
									</a>
								</td>
							</tr>
						</table>
					</td>
				</tr><?
				$arResult["OUTPUT_LIST"] = $APPLICATION->IncludeComponent(
					"bitrix:main.post.list",
					"mail",
					array(
						"RECORDS" => $arResult["RECORDS"],
						"RECIPIENT_ID" => $arParams["RECIPIENT_ID"],
						"SITE_ID" => $arResult["POST"]["BLOG_GROUP_SITE_ID"],
						"PREORDER" => "Y",
						"AVATAR_SIZE" => 39,
						"NAME_TEMPLATE" => CSite::getNameFormat(false),
						"SHOW_LOGIN" => "Y",

						"NOTIFY_TAG" => "",
						"NOTIFY_TEXT" => "",
						"SHOW_MINIMIZED" => "Y",
						"SHOW_POST_FORM" => "N",

						"COMMENTS_COUNT" => $arParams["COMMENTS_COUNT"],
						"COMMENTS_ALL_COUNT" => $arResult["COMMENTS_ALL_COUNT"],
						"POST_URL" => $arResult["POST_URL"],
						"HIGHLIGHT" => "Y"
					)
				);

				?><?=$arResult["OUTPUT_LIST"]["HTML"]?><?
				?><tr>
					<td valign="top" align="center" style="border-collapse: collapse;border-spacing: 0;border-top: 1px solid #edeef0;padding: 33px 0 20px;">
						<table cellspacing="0" cellpadding="0" border="0" align="center" style="border-collapse: collapse;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
							<tr>
								<td style="border-collapse: collapse;border-spacing: 0;background-color: #44c8f2;padding: 0;">
									<a target="_blank" href="<?=$arResult["POST_URL"]?>" style="color: #ffffff;background-color: #44c8f2;border: 8px solid #44c8f2;border-radius: 2px;display: block;font-family: Helvetica, Arial, sans-serif;font-size: 12px;font-weight: bold;padding: 4px;text-transform: uppercase;text-decoration: none;"><?=GetMessage('SBPCM_TEMPLATE_ADD_COMMENT_BUTTON')?></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" style="border-collapse: collapse;border-spacing: 0;color: #8b959d;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 11px;text-align: center;padding: 14px 0 0;">
						<?=GetMessage('SBPCM_TEMPLATE_DESCRIPTION', array("#BR#" => '<br/>'))?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>