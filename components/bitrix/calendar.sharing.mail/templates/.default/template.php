<?php

use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 */

$hasBitrix24Link = is_string($arParams['BITRIX24_LINK']) && $arParams['BITRIX24_LINK'] !== '';

?>

<html>
<head>
</head>
<body>
<style type="text/css">
	/* ======================================= DESKTOP STYLES */
	* { -webkit-text-size-adjust: none; }
	body { margin: 0 !important; padding: 0 !important; }
	body,table,td,p,a { -ms-text-size-adjust: 100% !important; -webkit-text-size-adjust: 100% !important; }
	table, tr, td { border-spacing: 0 !important; mso-table-lspace: 0px !important; mso-table-rspace: 0pt !important; border-collapse: collapse !important; mso-line-height-rule:exactly !important;}
	.ExternalClass * { line-height: 100% }
	.mobile-link a, .mobile-link span { text-decoration:none !important; color: inherit !important; border: none !important; }
	pre {margin-top:0;margin-bottom:0;}
	/* ======================================= CUSTOM DESKTOP STYLES */

	/* ======================================= MOBILE STYLES */
	@media only screen and (max-width: 640px) {
		body { min-width: 320px; margin: 0; }
		.hide-m { display: none !important; }
		.show-for-small { display: block !important; overflow: visible !important; width: auto !important; max-height: inherit !important; }
		.no-float { float: none !important; }
		.block { display: block !important; }
		.resize-image { width: 100%; height: auto; }
		.center-image { display: block; margin: 0 auto; }

		.text-center { text-align: center !important; }
		.font-14 { font-size: 14px !important; line-height: 16px !important; }
		.font-16 { font-size: 16px !important; line-height: 18px !important; }
		.font-18 { font-size: 18px !important; line-height: 20px !important; }
		.font-20 { font-size: 20px !important; line-height: 22px !important; }
		.font-22 { font-size: 22px !important; line-height: 24px !important; }

		.pad-t-0 { padding-top: 0px !important; }
		.pad-r-0 { padding-right: 0px !important; }
		.pad-b-0 { padding-bottom: 0px !important; }
		.pad-l-0 { padding-left: 0px !important; }
		.pad-t-20 { padding-top: 20px !important; }
		.pad-r-20 { padding-right: 20px !important; }
		.pad-b-20 { padding-bottom: 20px !important; }
		.pad-l-20 { padding-left: 20px !important; }
		.pad-0 { padding-top: 0px !important; padding-right: 0px !important; padding-bottom: 0px !important; padding-left: 0px !important; }
		.pad-10 { padding-top: 10px !important; padding-right: 10px !important; padding-bottom: 10px !important; padding-left: 10px !important; }
		.pad-20 { padding-top: 20px !important; padding-right: 20px !important; padding-bottom: 20px !important; padding-left: 20px !important; }
		.pad-sides-0 { padding-right: 0px !important; padding-left: 0px !important; }
		.pad-sides-10 { padding-right: 10px !important; padding-left: 10px !important; }
		.pad-sides-20 { padding-right: 20px !important; padding-left: 20px !important; }
		.pad-sides-30 { padding-right: 30px !important; padding-left: 30px !important; }

		.w100 { width: 100% !important; min-width: initial !important; }
		.w90 { width: 90% !important; min-width: initial !important; }
		.w50 { width: 50% !important; min-width: initial !important; }
		/* ======================================= CUSTOM MOBILE STYLES */

	}
</style>
<!-- WRAPPER -->
<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="width: 100%; background-color: #f2f6f7; background-image: url(<?= $arParams['MAIL_BG_GRAY']?>); background-repeat: no-repeat; background-position: center; background-size: cover;">
	<tbody>
	<tr>
		<td align="center" class="pad-sides-20" style="padding-top: 60px; padding-bottom: 14px;">
			<!-- CONTAINER -->
			<table cellspacing="0" cellpadding="0" border="0" align="center" width="343" style="width: 343px; min-width: 343px; margin: 0 auto;">
				<tbody>
				<tr>
					<td align="center">
						<!-- CONTAINER -->
						<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" bgcolor="#ffffff" style="width: 100%; border-radius: 6px; background-color: #ffffff; margin: 0 auto;box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);">
							<tbody>
								<tr>
									<td align="center" style="padding-top: 50px; padding-left: 28px; padding-right: 28px; padding-bottom: 28px;">

										<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="width: 100%; margin: 0 auto;">
											<tbody>
													<tr>
														<td align="center" style="padding-bottom: 30px;">
															<img src="<?= $arParams['ICON']?>" class="icon" style="display: block;width: 70px;height: 70px;border-radius: 50%;"/>
														</td>
													</tr>
													<tr>
														<td align="center" style="">
															<div style="margin-bottom: 24px;">
																<div class="title" style="display: block;font-size: 18px;margin-bottom: 12px;font-weight: 600;color: #151515;text-align: center;"><?= $arParams['EVENT_NAME']?></div>
																<div class="subtitle" style="display: block;font-weight: 400;font-size: 15px;color: #525c69;"><?= $arParams['LOC_MEETING_STATUS']?></div>
															</div>
														</td>
													</tr>
													<tr>
														<td align="left" style="">
															<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="width: 100%;border: 1px solid #e2e3e6;border-radius: 6px; margin: 0 auto; margin-bottom: 20px;">
																<tbody>
																	<tr>
																		<td valign="top" align="left" style="vertical-align:top; min-width: 300px;">
																			<div class="calendar" style="display: inline-block;padding-top:20px;padding-left:20px;width:70px;vertical-align: top;height: max-content;margin-right: 14px; box-sizing: border-box;">
																				<div style="font-size: 10px;border-top-left-radius:4px;border-top-right-radius:4px;background-color:#415c6f;color: #fff;text-align: center;box-sizing: border-box;text-transform: lowercase;"><?= $arParams['CALENDAR_MONTH_NAME']?></div>
																				<div style="border: 1px solid #d5d7db;border-top:none; border-bottom-left-radius:4px;border-bottom-right-radius:4px;">
																					<div style="font-size: 22px;font-weight: 400;line-height: 27px;background: #fff;padding: 0 8px;color: #415C6f;text-align: center;white-space: nowrap;"><?= $arParams['CALENDAR_DAY']?></div>
																					<div style="display: block;font-size: 10px;font-weight: 600;color: #83919b;text-align: center; white-space: nowrap;"><?= $arParams['CALENDAR_TIME']?></div>
																				</div>
																			</div>
																			<div class="header-info" style="display: inline-block;padding-top:20px;padding-right: 20px;padding-bottom:16px; width: 190px;box-sizing: content-box;">
																				<div class="datetime">
																					<div style="display: block;margin-bottom:5px;line-height:initial;font-size:14px;font-weight:400;color:#333333;"><?= $arParams['EVENT_DATE']?></div>
																					<div class="interval" style="display: block;margin-bottom:5px;font-weight: 400;font-size: 14px;color: #333333;">
																						<?= $arParams['EVENT_TIME']?>
																						<?php if (!empty($arParams['RRULE'])):?>
																							<img src="<?= $arParams['ICON_RRULE']?>" title="<?= $arParams['RRULE'] ?>" alt="<?= $arParams['RRULE'] ?>" class="icon-rrule" style="display: inline-block;width: 11px;height: 11px;vertical-align: middle;margin: 0 0 2px 5px;"/>
																						<?php endif ?>
																					</div>
																					<div class="timezone" style="display: block;color: #828b95;font-weight: 400;font-size: 12px;"><?= $arParams['TIMEZONE']?></div>
																					<div class="users" style="display: block;padding-top:13px;">
																						<?php if (!empty($arParams['AVATARS'])): ?>
																							<div style="margin-bottom: 6px;font-size:12px;font-weight:400;color:#959ca4;letter-spacing:0.5px;">
																								<?= $arResult['IS_EVENT_STATUS']
																									? Loc::getMessage('CALENDAR_SHARING_MAIL_MEETING_ATTENDEES')
																									: Loc::getMessage('CALENDAR_SHARING_MAIL_MEETING_HAS_MORE_USERS')
																								?>
																							</div>
																							<table cellspacing="0" cellpadding="0" border="0" align="left" style="">
																								<tbody>
																								<tr>
																									<?php foreach($arParams['AVATARS'] as $avatar): ?>
																										<td width="30" height="30" align="left" style="">
																											<!--[if (gte mso 9)|(IE)]>
																											  <v:oval xmlns:v="urn:schemas-microsoft-com:vml" fill="true" style='width:30px;height:30px' stroke="false">
																												 <v:fill type="tile" src="[URL]" />
																											  </v:oval>
																										   <![endif]-->
																											<!--[if !mso]>-->
																											<img style="border-radius: 600px;" width="30" height="30" src="<?= $avatar ?>" />
																											<!--<![endif]-->
																										</td>
																									<?php endforeach; ?>
																									<?php if ($arParams['SHOW_DOTS'] ?? false): ?>
																										<td width="30" height="30" align="left" style="">
																											<!--[if (gte mso 9)|(IE)]>
																											  <v:oval xmlns:v="urn:schemas-microsoft-com:vml" fill="true" style='width:30px;height:30px' stroke="false">
																												 <v:fill type="tile" src="[URL]" />
																											  </v:oval>
																										   <![endif]-->
																											<!--[if !mso]>-->
																											<img style="border-radius: 600px;" width="30" height="30" src="<?= $arParams['ICON_DOTS'] ?>" />
																											<!--<![endif]-->
																										</td>
																									<?php endif; ?>
																								</tr>
																								</tbody>
																							</table>
																						<?php endif; ?>
																					</div>
																				</div>
																			</div>
																		</td>
																	</tr>
																	<?php if ($arResult['SHOW_DETAIL_BUTTON']):?>
																		<tr>
																			<td align="center" style="padding-bottom: 20px;">
																				<!--[if mso]>
																				<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?= $arParams['DETAIL_LINK'] ?>" style="height:26px;v-text-anchor:middle;width:260px;" arcsize="12%" strokecolor="#DFE0E3" fillcolor="#ffffff">
																				<w:anchorlock/>
																				<center style="color:#333333;font-family:sans-serif;font-size:14px;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_OPEN_DETAIL_PAGE') ?></center>
																				</v:roundrect>
																				<![endif]-->
																				<a
																					href="<?= $arParams['DETAIL_LINK'] ?>"
																					style="background-color: #ffffff;border: 1px solid #DFE0E3;border-radius:4px;color: #333333;display:inline-block;font-family:sans-serif;font-size: 14px;line-height: 26px;text-align:center;text-decoration:none;width: 260px;-webkit-text-size-adjust:none;mso-hide:all;"
																				>
																					<?= Loc::getMessage('CALENDAR_SHARING_MAIL_OPEN_DETAIL_PAGE') ?>
																				</a>
																			</td>
																		</tr>
																	<?php endif; ?>
																</tbody>
															</table>
														</td>
													</tr>
													<?php if ($arResult['SHOW_CANCEL_LINK']):?>
														<tr>
															<td align="left" style="">
																<div class="buttons" style="display:block; text-align: center;">
																	<table cellspacing="0" cellpadding="0" border="0" align="center" style="margin-bottom: 20px;">
																		<tbody>
																		<tr>
																			<td valign="center" align="left" style="padding-right: 5px;">
																				<img src="<?= $arParams['ICON_BUTTON_CANCEL']?>" class="icon-cancel" style="display: inline-block;width: 13px;height: 13px;vertical-align: middle;" />
																			</td>
																			<td valign="center" align="left">
																				<a href="<?= $arParams['CANCEL_LINK']?>"
																				   style="color:#A8ADB4;display:inline-block;border-bottom: 1px dotted rgba(168, 173, 180, .8);font-size:14px;text-align:center;text-decoration:none;vertical-align: middle;text-transform: lowercase;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_CANCEL_EVENT')?></a>
																			</td>
																		</tr>
																		</tbody>
																	</table>
																</div>
															</td>
														</tr>
													<?php endif?>
													<?php if ($arResult['SHOW_DECLINE_LINK']): ?>
														<tr>
															<td align="left" style="">
																<div class="buttons" style="display:block; text-align: center;">
																	<table cellspacing="0" cellpadding="0" border="0" align="center" style="margin-bottom: 20px;">
																		<tbody>
																		<tr>
																			<td valign="center" align="left" style="padding-right: 5px;">
																				<img src="<?= $arParams['ICON_BUTTON_DECLINE']?>" class="icon-cancel" style="display: inline-block;width: 11px;height: 13px;vertical-align: middle;" />
																			</td>
																			<td valign="center" align="left">
																				<a href="<?= $arParams['DECISION_NO_LINK']?>"
																				   style="color:#A8ADB4;display:inline-block;border-bottom: 1px dotted rgba(168, 173, 180, .8);font-size:14px;text-align:center;text-decoration:none;vertical-align: middle;text-transform: lowercase;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_DECLINE_MEETING')?></a>
																			</td>
																		</tr>
																		</tbody>
																	</table>
																</div>
															</td>
														</tr>
													<?php endif ?>
													<?php if ($arResult['SHOW_WHEN_CANCELLED']):?>
														<tr>
															<td align="center" style="padding-bottom: 50px; padding-top: 10px;">
																<div style="font-size:13px;color:#a8adb4;">
																	<div>
																		<span style="display: inline-block; margin-right:5px;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_WHO_CANCELLED', ['#NAME#' => $arParams['WHO_CANCELLED']])?></span>
																	</div>
																	<div><?= $arParams['WHEN_CANCELLED'] ?></div>
																</div>
															</td>
														</tr>
													<?php endif ?>

													<?php if ($arResult['SHOW_CALENDAR_BUTTON']):?>
														<tr>
															<td align="center" style="padding-bottom: 10px;">
																<div><!--[if mso]>
																	<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://" style="height:36px;v-text-anchor:middle;width:300px;" arcsize="12%" strokecolor="#DFE0E3" fillcolor="#ffffff">
																	<w:anchorlock/>
																	<center style="color:#333333;font-family:sans-serif;font-size:15px;">Show me the button!</center>
																	</v:roundrect>
																	<![endif]-->
																	<a href="<?= $arParams['CALENDAR_LINK'] ?>"
																	   style="background-color:#ffffff;border:1px solid #DFE0E3;border-radius:4px;color:#333333;display:inline-block;font-family:sans-serif;font-size:15px;line-height:36px;text-align:center;text-decoration:none;width:300px;-webkit-text-size-adjust:none;mso-hide:all;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_CREATE_NEW_MEETING')?></a>
																</div>
															</td>
														</tr>
													<?php endif ?>
													<?php if ($arResult['SHOW_VIDEOCONFERENCE_BUTTON']):?>
														<tr>
															<td align="center" style="padding-bottom: 10px;">
																<div><!--[if mso]>
																	<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?= $arParams['VIDEOCONFERENCE_LINK'] ?>" style="height:36px;v-text-anchor:middle;width:300px;" arcsize="12%" strokecolor="#DFE0E3" fillcolor="#ffffff">
																	<w:anchorlock/>
																	<center style="color:#333333;font-family:sans-serif;font-size:15px;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_OPEN_VIDEOCONFERENCE')?></center>
																	</v:roundrect>
																	<![endif]-->
																	<a href="<?= $arParams['VIDEOCONFERENCE_LINK'] ?>"
																	   style="background-color:#ffffff;border:1px solid #DFE0E3;border-radius:4px;color:#333333;display:inline-block;font-family:sans-serif;font-size:15px;line-height:36px;text-align:center;text-decoration:none;width:300px;-webkit-text-size-adjust:none;mso-hide:all;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_OPEN_VIDEOCONFERENCE')?></a>
																</div>
															</td>
														</tr>
													<?php endif ?>
													<?php if ($arResult['SHOW_ICS_BUTTON']):?>
														<tr>
															<td align="center" style="padding-bottom: 10px;">
																<div><!--[if mso]>
																	<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?= $arParams['ICS_LINK'] ?>" style="height:36px;v-text-anchor:middle;width:300px;" arcsize="12%" strokecolor="#DFE0E3" fillcolor="#ffffff">
																	<w:anchorlock/>
																	<center style="color:#333333;font-family:sans-serif;font-size:15px;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_ADD_TO_CALENDAR')?></center>
																	</v:roundrect>
																	<![endif]-->
																	<a href="<?= $arParams['ICS_LINK'] ?>"
																	   style="background-color:#ffffff;border:1px solid #DFE0E3;border-radius:4px;color:#333333;display:inline-block;font-family:sans-serif;font-size:15px;line-height:36px;text-align:center;text-decoration:none;width:300px;-webkit-text-size-adjust:none;mso-hide:all;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_ADD_TO_CALENDAR')?></a>
																</div>
															</td>
														</tr>
													<?php endif ?>
													<?php if ($arResult['SHOW_ACCEPT_BUTTON']):?>
														<tr>
															<td align="center" style="padding-bottom: 10px;">
																<div><!--[if mso]>
																	<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?= $arParams['DECISION_YES_LINK'] ?>" style="height:36px;v-text-anchor:middle;width:300px;" arcsize="12%" strokecolor="#415C6F" fillcolor="#ffffff">
																	<w:anchorlock/>
																	<center style="color:#ffffff;font-family:sans-serif;font-size:15px;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_ACCEPT')?></center>
																	</v:roundrect>
																	<![endif]-->
																	<a href="<?= $arParams['DECISION_YES_LINK'] ?>"
																	   style="background-color:#415C6F;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:15px;line-height:36px;text-align:center;text-decoration:none;width:300px;-webkit-text-size-adjust:none;mso-hide:all;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_ACCEPT')?></a>
																</div>
															</td>
														</tr>
													<?php endif ?>
													<?php if ($arResult['SHOW_DECLINE_BUTTON']):?>
														<tr>
															<td align="center" style="padding-bottom: 10px;">
																<div><!--[if mso]>
																	<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?= $arParams['DECISION_NO_LINK'] ?>" style="height:36px;v-text-anchor:middle;width:300px;" arcsize="12%" strokecolor="#DFE0E3" fillcolor="#ffffff">
																	<w:anchorlock/>
																	<center style="color:#333333;font-family:sans-serif;font-size:15px;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_DECLINE')?></center>
																	</v:roundrect>
																	<![endif]-->
																	<a href="<?= $arParams['DECISION_NO_LINK'] ?>"
																	   style="background-color:#ffffff;border:1px solid #DFE0E3;border-radius:4px;color:#333333;display:inline-block;font-family:sans-serif;font-size:15px;line-height:36px;text-align:center;text-decoration:none;width:300px;-webkit-text-size-adjust:none;mso-hide:all;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_DECLINE')?></a>
																</div>
															</td>
														</tr>
													<?php endif ?>
												</tbody>
										</table>

									</td>
								</tr>
							</tbody>
						</table>
						<!-- /CONTAINER -->
					</td>
				</tr>
				</tbody>
			</table>
			<!-- /CONTAINER -->
		</td>
	</tr>
	<tr>
		<td align="center" class="pad-sides-20" style="padding-bottom: 5px;">
			<span style="display: inline-block; color: #a8adb4;font-size: 15px;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_FOOTER_LOGO_FREE_SITES_AND_CRM', [
				'#TAG#' => ($hasBitrix24Link ? 'a' : 'span'),
				'#HREF#' => ($hasBitrix24Link ? $arParams['BITRIX24_LINK'] : '#'),
				'#STYLE#' => 'display: inline-block; font-size: 15px;color: #a8adb4;',
			])?></span>
		</td>
	</tr>
	<tr>
		<td align="center" style="padding-bottom: 24px;">
			<?php if (is_string($arParams['ABUSE_LINK'] ?? null) && $arParams['ABUSE_LINK'] !== ''): ?>
				<span style="display: inline-block; margin-right: 6px; font-size: 15px;color: #a8adb4;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_FOOTER_REPORT', [
					'#HREF#' => $arParams['ABUSE_LINK'],
					'#STYLE#' => 'display: inline-block; font-size: 15px;color: #a8adb4;',
				])?></span>
			<?php endif ?>
		</td>
	</tr>
	</tbody>
</table>
<!-- /WRAPPER -->
</body>
</html>
