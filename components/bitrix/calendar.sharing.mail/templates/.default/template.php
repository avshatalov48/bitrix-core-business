<?php

use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * @var array $arParams
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
															<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="width: 100%;border: 1px solid #e2e3e6;border-radius: 6px; margin: 0 auto;">
																<tbody>
																	<tr>
																		<td valign="top" align="left" style="padding-top:20px;padding-bottom:20px;padding-left:20px;">
																			<div class="calendar" style="display: inline-block;width:56px;vertical-align: top;height: max-content;margin-right: 4px; box-sizing: border-box;">
																				<div style="font-size: 10px;border-top-left-radius:4px;border-top-right-radius:4px;background-color:#415c6f;color: #fff;text-align: center;box-sizing: border-box;text-transform: lowercase;"><?= $arParams['CALENDAR_MONTH_NAME']?></div>
																				<div style="border: 1px solid #d5d7db;border-top:none; border-bottom-left-radius:4px;border-bottom-right-radius:4px;">
																					<div style="font-size: 22px;font-weight: 400;line-height: 27px;background: #fff;padding: 0 8px;color: #415C6f;text-align: center;white-space: nowrap;"><?= $arParams['CALENDAR_DAY']?></div>
																					<div style="display: block;font-size: 11px;font-weight: 600;color: #83919b;text-align: center"><?= $arParams['CALENDAR_TIME']?></div>
																				</div>
																			</div>
																		</td>
																		<td align="left" style="padding-right: 20px;">
																			<div class="header-info" style="display: inline-block;">
																				<div class="datetime">
																					<div style="display: block;margin-bottom:5px;line-height:initial;font-size:14px;font-weight:400;color:#333333;"><?= $arParams['EVENT_DATE']?></div>
																					<div class="interval" style="display: block;margin-bottom:5px;font-weight: 400;font-size: 14px;color: #333333;"><?= $arParams['EVENT_TIME']?></div>
																					<div class="timezone" style="display: block;color: #828b95;font-weight: 400;font-size: 12px;"><?= $arParams['TIMEZONE']?></div>
																				</div>
																			</div>
																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>
													<?php if (isset($arParams['CANCEL_LINK'])):?>
														<tr>
															<td align="left" style="">
																<div class="buttons" style="display:block; margin-top: 20px; text-align: center;">
																	<div>
																		<div style="margin-bottom:60px;">
																			<img src="<?= $arParams['ICON_BUTTON_CANCEL']?>" class="icon-cancel" style="display: inline-block;width: 13px;height: 13px;vertical-align: middle;"/>
																			<a href="<?= $arParams['CANCEL_LINK']?>"
																			   style="color:#A8ADB4;display:inline-block;border-bottom: 1px dotted rgba(168, 173, 180, .8);font-size:14px;text-align:center;text-decoration:none;vertical-align: middle;text-transform: lowercase;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_CANCEL_EVENT')?></a>
																		</div>
																	</div>
																</div>
															</td>
														</tr>
													<?php endif?>
													<?php if ($arParams['IS_CANCELLED']):?>
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

													<?php if ($arParams['IS_CANCELLED']):?>
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
													<?php if ($arParams['IS_CREATED']):?>
														<tr>
															<td align="center" style="padding-bottom: 10px;">
																<div><!--[if mso]>
																	<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://" style="height:36px;v-text-anchor:middle;width:300px;" arcsize="12%" strokecolor="#DFE0E3" fillcolor="#ffffff">
																	<w:anchorlock/>
																	<center style="color:#333333;font-family:sans-serif;font-size:15px;">Show me the button!</center>
																	</v:roundrect>
																	<![endif]-->
																	<a href="<?= $arParams['ICS_LINK'] ?>"
																	   style="background-color:#ffffff;border:1px solid #DFE0E3;border-radius:4px;color:#333333;display:inline-block;font-family:sans-serif;font-size:15px;line-height:36px;text-align:center;text-decoration:none;width:300px;-webkit-text-size-adjust:none;mso-hide:all;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_ADD_TO_CALENDAR')?></a>
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
			<?php if (is_string($arParams['ABUSE_LINK']) && $arParams['ABUSE_LINK'] !== ''): ?>
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
