<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix

 * @global CMain $APPLICATION
 * @global CUser $USER
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("USMP_TITLE"));

$sort = "sort";
$category = "";
$arResult = array();

if(in_array(LANGUAGE_ID, array("ru", "ua", "bg")))
{
	$arShow = array("all", "not_free", "free", "action");
	$arSort = array("sort", "date", "price", "alfa");
	$show = "all";
	$moduleCode = "";

	if(!empty($_REQUEST["show"]) && in_array($_REQUEST["show"], $arShow))
		$show = $_REQUEST["show"];
	elseif(!empty($_SESSION["mp_show"]) && in_array($_SESSION["mp_show"], $arShow))
		$show = $_SESSION["mp_show"];

	if(!empty($_REQUEST["sort"]) && in_array($_REQUEST["sort"], $arSort))
		$sort = $_REQUEST["sort"];
	elseif(!empty($_SESSION["mp_sort"]) && in_array($_SESSION["mp_sort"], $arSort))
		$sort = $_SESSION["mp_sort"];

	if(isset($_REQUEST["category"]) && intval($_REQUEST["category"]) > 0)
		$category = intval($_REQUEST["category"]);

	if(!empty($_REQUEST["module"]))
	{
		$moduleCode = $_REQUEST["module"];
		$moduleCode = preg_replace("/[^a-zA-Z0-9.]/is", "", $moduleCode);
	}

	$_SESSION["mp_sort"] = $sort;
	$_SESSION["mp_show"] = $show;

	$sTableID = "tbl_main_mp";
	$lAdmin = new CAdminList($sTableID);
	$aContext = array();
	foreach($arShow as $val)
	{
		$aContext[] = array(
			"TEXT" => (($val == "action") ? "<span style=\"color:#ba2211;\">" : "").GetMessage("USM_SHOW_".strtoupper($val)).(($val == "action") ? "</span>" : ""),
			"ONCLICK" => $lAdmin->ActionDoGroup(0, "", "show=".$val.(($category) > 0 ? "&category=".$category : "")),
			"ICON" => (($val == $show) ? "btn_active" : ""),
		);
	}

	$arDDSort = array();
	foreach($arSort as $val)
	{
		$arDDSort[] = array(
			"TEXT" => GetMessage("USM_SORT_".strtoupper($val)),
			"ACTION" => $lAdmin->ActionDoGroup(0, "", "sort=".$val.(($category) > 0 ? "&category=".$category : ""))
		);
	}

	$aContext[] = array(
			"TEXT" => GetMessage("USM_SORT")." ".GetMessage("USM_SORT_".strtoupper($sort)),
			"TITLE" => "",
			"MENU" => $arDDSort,
		);
	$lAdmin->AddAdminContextMenu($aContext, false, false);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");


if(!in_array(LANGUAGE_ID, array("ru", "ua", "bg")))
{
	if(!$USER->CanDoOperation('install_updates'))
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/update_system_market_notru.php");
}
else
{
	$m = array();
	$arClientModules = CUpdateClientPartner::GetCurrentModules($strError_tmp);
	if(is_array($arClientModules) && !empty($arClientModules))
	{
		foreach($arClientModules as $k => $v)
		{
			if(strpos($k, ".") !== false)
				$m[htmlspecialcharsbx($k)] = $v["IS_DEMO"];
		}
	}

	$url = "solutions/";
	if(intval($category) > 0)
		$url = "solutions/category/".intval($category)."/";
	if(!empty($_REQUEST["search_mp"]))
	{
		$url = "search/";
	}
	if($moduleCode <> '')
	{
		$url = "solutions/".htmlspecialcharsbx($moduleCode)."/";
	}

	$arFields = array("update_sys_new" => "Y");
	switch ($show) {
		case 'free':
			$arFields["PAYMENT_SHOW"] = "FREE";
			break;
		case 'not_free':
			$arFields["PAYMENT_SHOW"] = "NOT_FREE";
			break;
		case 'action':
			$arFields["PAYMENT_SHOW"] = "ACTION";
			break;
		default:
			$arFields["PAYMENT_SHOW"] = "ALL";
			break;
	}
	switch ($sort) {
		case 'date':
			$arFields["MODULE_SORT"] = "DATE_PUBLISH";
			break;
		case 'price':
			$arFields["MODULE_SORT"] = "PRICE";
			break;
		case 'alfa':
			$arFields["MODULE_SORT"] = "ABC";
			break;
		default:
			$arFields["MODULE_SORT"] = "SORT";
			break;
	}
	$ht = new CHTTP();

	if(isset($_REQUEST["PAGEN_1"]) && intval($_REQUEST["PAGEN_1"]) > 0)
		$arFields["PAGEN_1"] = intval($_REQUEST["PAGEN_1"]);
	if(!empty($_REQUEST["search_mp"]))
		$arFields["q"] = \Bitrix\Main\Text\Encoding::convertEncoding(htmlspecialcharsbx($_REQUEST["search_mp"]), SITE_CHARSET, "windows-1251");

	$getData = "";
	if (is_array($arFields))
	{
		foreach ($arFields as $k => $v)
		{
			if(is_array($v))
			{
				foreach($v as $kk => $vv)
					$getData .= urlencode($k."[".$kk."]").'='.urlencode($vv)."&";
			}
			else
				$getData .= urlencode($k).'='.urlencode($v)."&";
		}
	}

	$sectionName = GetMessage("USM_ALL");
	if(!empty($_REQUEST["search_mp"]))
		$sectionName = GetMessage("USM_SEARCH");

	$arModules = array();
	if($res = $ht->Get("https://marketplace.1c-bitrix.ru/".$url."?".$getData))
	{
		if(in_array($ht->status, array("200")))
		{
			$res = \Bitrix\Main\Text\Encoding::convertEncoding($res, "windows-1251", SITE_CHARSET);

			$objXML = new CDataXML();
			$objXML->LoadString($res);
			$arResult = $objXML->GetArray();

			if(!empty($arResult) && is_array($arResult))
			{
				if(!empty($arResult["modules"]["#"]))
				{
					$arModules = $arResult["modules"]["#"]["items"][0]["#"]["item"];
					if(!empty($arResult["modules"]["#"]["categoryName"][0]["#"]))
						$sectionName = $arResult["modules"]["#"]["categoryName"][0]["#"];
				}
			}
		}
	}

	$curPage = $APPLICATION->GetCurPageParam("module=#module#", array("sort", "show", "category", "module"));
	$APPLICATION->SetAdditionalCSS("/bitrix/panel/main/marketplace.css");

	?>
	<script>
	function mp_hl(el, show)
	{
		if(show)
			BX.addClass(el, 'mp-over-over');
		else
			BX.removeClass(el, 'mp-over-over');

	}
	</script>
	<?
	$lAdmin->BeginCustomContent();
	?>
	<div class="adm-detail-content-wrap">
		<div class="adm-detail-content">
			<div class="adm-detail-title" style="border-bottom: 1px solid #f9eaea; padding-right: 0px;"><?=htmlspecialcharsbx($sectionName)?>
				<div style="float:right; padding-bottom:1px;">
					<form action="" method="GET">
						<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>"><input type="text" value="<?=GetMessage("USM_SEARCH")?>" name="search_mp" size="30" onclick="if (this.value=='<?=GetMessageJS("USM_SEARCH")?>')this.value=''" onblur="if (this.value=='')this.value='<?=GetMessageJS("USM_SEARCH")?>'">
					</form>
				</div>
			</div>
			<div class="mp-list-div">
				<?
				if(is_array($arModules) && !empty($arModules))
				{
					if($moduleCode == '')
					{
						$inRow = 0;
						?>
						<table class="mp-list" cellpadding="0" cellspacing="0">
						<?
					}

					function convert2normalArray($ar)
					{
						$res = array();
						foreach($ar as $kk => $vv)
						{
							if(empty($vv[0]) && !empty($vv["#"]))
							{
								if(is_array($vv["#"]))
								{
									$res[$kk] = convert2normalArray($vv["#"]);
								}
								else
									$res[$kk] = $vv["#"];
							}
							else
							{
								if(count($vv) > 1)
								{
									$res[$kk] = convert2normalArray($vv);
								}
								else
								{
									if(!empty($vv[0]["#"]))
									{
										if(is_array($vv[0]["#"]))
										{
											$res[$kk] = convert2normalArray($vv[0]["#"]);
										}
										else
										{
											$res[$kk] = $vv[0]["#"];
										}
									}
								}
							}
						}
						return $res;
					}


					foreach($arModules as $Item)
					{
						$arM = array();
						$arM = convert2normalArray($Item["#"]);

						$arM["url"] = str_replace("#module#", $arM["code"], "update_system_market.php?module=#module#&lang=".LANGUAGE_ID);

						if($USER->CanDoOperation('install_updates'))
							$arM["urlInstall"] = "update_system_partner.php?lang=".LANGUAGE_ID."&addmodule=".$arM["code"];
						else
							$arM["urlInstall"] = $arM["url"]."&instadm=Y&".bitrix_sessid_get();

						if(!empty($m[$arM["code"]]))
						{
							$arM["installed"] = "Y";
							if($m[$arM["code"]] == "Y")
							{
								$arM["installedDemo"] = "Y";
							}
						}
						$arM["canDemo"] =
							isset($arM["freeModule"])
							&& $arM["freeModule"] == "D"
							&& (!isset($arM["installed"]) || $arM["installed"] != "Y")
								? "Y"
								: "N"
						;

						if($moduleCode == '')
						{
							if($inRow++%3==0)
							{
								echo "<tr>";
							}
							?>
							<td valign="top" width="33%" style="padding: 0 13px 10px 0;">
								<div class="mp-over" onmouseover="mp_hl(this, true)" onmouseout="mp_hl(this, false)">
									<div class="mp-over-inner">
										<div class="mp-name"><a href="<?=$arM["url"]?>" title="<?=$arM["name"]?>"><?=$arM["name"]?></a></div>
										<a href="<?=$arM["url"]?>" title="<?=$arM["name"]?>"><span class="mp-list-slide-block-image" style="background: url('<?=$arM["logo"]["src"]?>') center center no-repeat; width:<?=$arM["logo"]["width"]?>px; height:<?=$arM["logo"]["height"]?>px; display:block; border: 1px solid #c0c0c0; float: left;">
												<?
												if(!empty($arM["icons"]) > 0)
												{
													foreach($arM["icons"] as $v)
													{
														?>
														<img src="<?=$v["src"]?>" border="0" style="<?=$v["styles"]?>" width="<?=$v["width"]?>" height="<?=$v["height"]?>" />
														<?
													}
												}
												?>
										</span></a>
										<div class="mp-content">
											<span class="mp-ilike"><?=intval($arM["votes"] ?? 0)?></span>
											<div>
												<?
												if(isset($arM["installed"]) && $arM["installed"] == "Y")
												{
													?><div class="mp-grey"><?=GetMessage("USM_INSTALLED")?></div><?
												}

												if(isset($arM["freeModule"]) && $arM["freeModule"] == "Y")
												{
													if(!isset($arM["installed"]) || $arM["installed"] != "Y")
													{
														?><div class="mp-install"><a href="<?=$arM["urlInstall"]?>"><?=GetMessage("USM_INSTALL")?></a></div>
														<div class="mp-grey"><small><?=GetMessage("USM_FREE")?></small></div><?
													}
												}
												else
												{
													if(isset($arM["oldPrice"]) && intval($arM["oldPrice"]) > 0)
													{
														?><div class="mp-price"><s><?=intval($arM["oldPrice"])?></s>&nbsp;&nbsp;<span style="color:red;"><?=$arM["price"]?></span></div><?
													}
													else
													{
														?><div class="mp-price"><?=$arM["price"];?></div><?
													}

													if(!isset($arM["installedDemo"]) || $arM["installedDemo"] != "Y")
													{
														?><div class="mp-buy"><a href="<?=$arM["url2basket"]?>" target="_blank"><?=GetMessage("USM_BUY")?></a></div><?
													}
													if($arM["canDemo"] == "Y")
													{
														?><div class="mp-test"><a href="<?=$arM["urlInstall"]?>" target="_blank"><?=GetMessage("USM_TEST")?></a></div><?
													}
												}
												?>
											</div>
										</div>
									</div>
								</div>
							</td>
							<?
							if($inRow%3 == 0)
							{
								$inRow = 0;
								echo "</tr>";
							}
						}
						else
						{
							?>
							<div style="float:left; width:190px; padding-right: 15px;">
								<div style="display: inline-block;"><span title="<?=htmlspecialcharsbx($arM["name"] ?? '')?>" class="mp-list-slide-block-image" style="background: url('<?=$arM["logo"]["src"]?>') center center no-repeat; width:<?=$arM["logo"]["width"]?>px; height:<?=$arM["logo"]["height"]?>px; display:block; border: 1px solid #c0c0c0; float: left;">
										<?
										if(!empty($arM["icons"]) > 0)
										{
											foreach($arM["icons"] as $v)
											{
												?>
												<img src="<?=$v["src"]?>" border="0" style="<?=$v["styles"]?>" width="<?=$v["width"]?>" height="<?=$v["height"]?>" />
												<?
											}
										}
										?>
								</span></a></div>
								<div id="mp-info-bar">
									<div class="mp-item">
										<p class="mp-title"><?=GetMessage("USM_RATING")?></p>
										<span class="mp-ilike"><?=intval($arM["votes"] ?? 0)?></span>
									</div>
									<div class="mp-item">
										<p class="mp-title"><?=GetMessage("USM_DEVELOPER")?></p>
										<p><?if(!empty($arM["partner"]["href"])):?>
											<a href="<?=htmlspecialcharsbx($arM["partner"]["href"])?>" target="_blank"><?=htmlspecialcharsbx($arM["partner"]["name"])?></a>
											<?else:?>
												<?=htmlspecialcharsbx($arM["partner"]["name"])?>
											<?endif;?>
										</p>
									</div>
									<div class="mp-item">
										<p class="mp-title"><?=GetMessage("USM_DATE_ADD")?></p>
										<p><?=$arM["date"]?></p>
									</div>
									<?if($arM["version"] <> ''):?>
										<div class="mp-item">
											<p class="mp-title"><?=GetMessage("USM_VERSION")?></p>
											<p><?=$arM["version"]?></p>
										</div>
									<?endif;?>
									<div class="mp-item">
										<p class="mp-title"><?=GetMessage("USM_INSTALL_CNT")?></p>
										<p><?=$arM["instCnt"]?></p>
									</div>
								</div>
							</div>
							<div style="float:left; width:595px;">
								<div id="mp-price-bar">
									<div style="float:left;">
										<?
										if(isset($arM["freeModule"]) && $arM["freeModule"] == "Y")
										{
											?><div class="mp-grey"><?=GetMessage("USM_FREE")?></div><?
										}
										else
										{
											?><div class="mp-grey"><?=GetMessage("USM_PAID")?></div>
											<div class="mp-price">
											<?
											if(isset($arM["oldPrice"]) && intval($arM["oldPrice"]) > 0)
											{
												?><s><?=intval($arM["oldPrice"])?></s>&nbsp;&nbsp;<span style="color:red;"><?=$arM["price"]?></span><?
											}
											else
											{
												?><?=$arM["price"];?><?
											}
											?></div><?
										}
										?>
									</div>
									<div class="mp-buttons">
										<?if(isset($arM["installed"]) && $arM["installed"] == "Y")
										{
											?><div class="mp-grey"><?=GetMessage("USM_INSTALLED")?></div><?
										}

										if(isset($arM["freeModule"]) && $arM["freeModule"] == "Y")
										{
											if(!isset($arM["installed"]) || $arM["installed"] != "Y")
											{
												?><a href="<?=$arM["urlInstall"]?>" class="adm-btn adm-btn-green"><?=GetMessage("USM_INSTALL")?></a><?
											}
										}
										else
										{
											if(!isset($arM["installedDemo"]) || $arM["installedDemo"] != "Y")
											{
												?><a href="<?=$arM["url2basket"]?>" target="_blank" class="adm-btn adm-btn-green"><?=GetMessage("USM_BUY")?></a><?
											}
											if($arM["canDemo"] == "Y")
											{
												?><a href="<?=$arM["urlInstall"]?>" target="_blank" class="adm-btn"><?=GetMessage("USM_TEST")?></a><?
											}
										}
										if(!empty($arM["demoLink"]))
										{
											?><a class="adm-btn" href="<?=htmlspecialcharsbx($arM["demoLink"])?>" target="_blank"><?=GetMessage("USM_ONLINE_DEMO")?></a><?
										}

										?>
									</div>
								</div>
								<script>
								function SlideDescription(obj, btnlnk)
								{
									if(obj.style.overflow == 'hidden')
									{
										BX('mp-detail-descripiption-fade').style.display = 'none';
										obj.style.height = '';
										obj.style.overflow = 'visible';
										btnlnk.className = 'mp-more-description-btn-close';
									}
									else
									{
										BX('mp-detail-descripiption-fade').style.display = 'block';
										obj.style.height = '100px';
										obj.style.overflow = 'hidden';
										btnlnk.className = 'mp-more-description-btn';
									}
									BX.onCustomEvent('onAdminTabsChange');
								}
								</script>
								<div id="mp-detail-description" style="overflow: hidden; height:100px; border-bottom:1px solid #e5e5e5;line-height: 18px; text-align: justify;padding-bottom: 5px;"><?=$arM["descr"]?></div>
								<div class="mp-detail-more"><a class="mp-more-description-btn" onfocus="this.blur();" onclick="SlideDescription(BX('mp-detail-description'), this)" href="javascript:void(0)"><?=GetMessage("USM_DETAIL")?></a></div>
								<div id="mp-detail-descripiption-fade"></div>

								<div class="mp-tabs">
									<?
									$aTabs1 = array();
									if(!empty($arM["action"]))
										$aTabs1[] = array("DIV"=>"oedit1", "TAB" => GetMessage("USM_ACTIONS"), "TITLE" => GetMessage("USM_ACTIONS"));
									if(!empty($arM["images"]))
										$aTabs1[] = array("DIV"=>"oedit2", "TAB" => GetMessage("USM_IMAGES"), "TITLE" => GetMessage("USM_IMAGES"));
									if(!empty($arM["updates"]))
										$aTabs1[] = array("DIV"=>"oedit3", "TAB" => GetMessage("USM_UPDATES"), "TITLE" => GetMessage("USM_UPDATES"));
									if(!empty($arM["support"]))
										$aTabs1[] = array("DIV"=>"oedit4", "TAB" => GetMessage("USM_SUPPORT"), "TITLE" => GetMessage("USM_SUPPORT"));
									if(!empty($arM["install"]))
										$aTabs1[] = array("DIV"=>"oedit5", "TAB" => GetMessage("USM_INSTALL_MODULE"), "TITLE" => GetMessage("USM_INSTALL_MODULE"));

									$tabControl1 = new CAdminViewTabControl("tabControl1", $aTabs1);
									$tabControl1->Begin();
									if(!empty($arM["action"]))
									{
										$tabControl1->BeginNextTab();
										if($arM["action"]["descr"] <> '')
											echo "<div>".$arM["action"]["descr"]."</div>";
										echo $arM["action"]["date"];
									}
									if(!empty($arM["images"]))
									{
										$tabControl1->BeginNextTab();

										if(!isset($arM["images"]["image"][0]) || !is_array($arM["images"]["image"][0]))
											$arM["images"]["image"] = array($arM["images"]["image"]);

										if(!empty($arM["styles"]["style"]))
										{
											if(!is_array($arM["styles"]["style"]))
												$arM["styles"]["style"] = array($arM["styles"]["style"]);
											foreach($arM["styles"]["style"] as $v)
											{
												?><link href="<?=$v?>" type="text/css" rel="stylesheet"><?
											}
										}
										if(!empty($arM["scripts"]["script"]))
										{
											if(!is_array($arM["scripts"]["script"]))
												$arM["scripts"]["script"] = array($arM["scripts"]["script"]);
											foreach($arM["scripts"]["script"] as $v)
											{
												?><script type="text/javascript" src="<?=$v?>"></script><?
											}
										}
										?>
										<div class="screenshot-block">
											<a class="scroll-prev screenshot-prev disabled"></a>
											<div id="scrollable-screenshot" class="scrollable" style="visibility: visible; overflow: hidden; position: relative; z-index: 2; left: 0px; width: 507px;">
												<ul style="margin: 0pt; padding: 0pt; position: relative; list-style-type: none; z-index: 1; width: 676px; left: 0px;">
													<?foreach($arM["images"]["image"] as $val)
													{
														if(isset($val["video"]) && $val["video"] == "Y")
														{
															?><li style="overflow: hidden; float: left; width: <?=$val["width"]?>px; height: <?=$val["height"]?>px; border: 1px solid #cfcfcf;">
																<div style="display:none; width:645px; height:490px;" id="module-video"><?$APPLICATION->IncludeComponent(
																	"bitrix:player",
																	"",
																	array(
																			"PLAYER_TYPE" => "auto",
																			"USE_PLAYLIST" => "N",
																			"PATH" => $val["big"],
																			"PROVIDER" => "video",
																			"STREAMER" => "",
																			"WIDTH" => "640",
																			"HEIGHT" => "480",
																			"PREVIEW" => "",
																			"FILE_TITLE" => "",
																			"FILE_DURATION" => "",
																			"FILE_AUTHOR" => "",
																			"FILE_DATE" => "",
																			"FILE_DESCRIPTION" => "",
																			"SKIN_PATH" => "/bitrix/components/bitrix/player/mediaplayer/skins",
																			"SKIN" => "",
																			"CONTROLBAR" => "bottom",
																			"WMODE" => "opaque",
																			"LOGO" => "",
																			"LOGO_LINK" => "",
																			"LOGO_POSITION" => "none",
																			"PLUGINS" => array(),
																			"ADDITIONAL_FLASHVARS" => "",
																			"WMODE_WMV" => "window",
																			"SHOW_CONTROLS" => "Y",
																			"SHOW_DIGITS" => "Y",
																			"CONTROLS_BGCOLOR" => "FFFFFF",
																			"CONTROLS_COLOR" => "000000",
																			"CONTROLS_OVER_COLOR" => "000000",
																			"SCREEN_COLOR" => "000000",
																			"AUTOSTART" => "N",
																			"REPEAT" => "N",
																			"VOLUME" => "90",
																			"MUTE" => "N",
																			"ADVANCED_MODE_SETTINGS" => "N",
																			"PLAYER_ID" => "",
																			"BUFFER_LENGTH" => "10",
																			"DOWNLOAD_LINK" => "",
																			"DOWNLOAD_LINK_TARGET" => "_self",
																			"ADDITIONAL_WMVVARS" => "",
																			"ALLOW_SWF" => "N"
																	),
															false
															);?></div>
																<a class="screenshot-video"><img width="<?=$val["width"]?>" height="<?=$val["height"]?>" alt="" src="<?=$val["small"]?>"></a>
															</li>
															<?
														}
														else
														{
															?><li style="overflow: hidden; float: left; width: <?=$val["width"]?>px; height: <?=$val["height"]?>px; border: 1px solid #cfcfcf;">
																<a rel="module_screenshots" href="<?=$val["big"]?>" class="screenshot-image"><img width="<?=$val["width"]?>" height="<?=$val["height"]?>" alt="" src="<?=$val["small"]?>"></a>
															</li><?
														}
													}
													?>
												</ul>
											</div>
											<a class="scroll-next screenshot-next"></a>
										</div>
										<?

									}
									if(!empty($arM["updates"]))
									{
										$tabControl1->BeginNextTab();
										if(!isset($arM["updates"]["version"][0]) || !is_array($arM["updates"]["version"][0]))
											$arM["updates"]["version"] = array($arM["updates"]["version"]);

										?><table width="100%" border="0" cellpadding="2" cellspacing="2"><?
										foreach($arM["updates"]["version"] as $arVersion)
										{
											?><tr>
												<td valign="top" style="padding-right:10px;"><b><?=$arVersion["id"]?></b></td>
												<td valign="top" style="padding-bottom:10px;padding-left:10px;"><?=$arVersion["descr"]?></td>
											</tr><?
										}
										?></table><?
									}
									if(!empty($arM["support"]))
									{
										$tabControl1->BeginNextTab();
										echo $arM["support"];

									}
									if(!empty($arM["install"]))
									{
										$tabControl1->BeginNextTab();
										echo $arM["install"];

									}
									$tabControl1->End();

									if(!empty($arM["moreItems"]["item"]))
									{
										if(!isset($arM["moreItems"]["item"][0]) || !is_array($arM["moreItems"]["item"][0]))
											$arM["moreItems"]["item"] = array($arM["moreItems"]["item"]);
										?>
										<h3><?=GetMessage("USM_MORE_MODULES")?></h3>
										<div id="similar-solutions">
										<table class="mp-list">
											<tbody>
												<tr>
													<td>
														<a class="scroll-prev solutions-prev"></a>
														<div id="scrollable" class="scrollable" style="visibility: visible; overflow: hidden; position: relative; z-index: 2; left: 0px; width: 735px;">
															<ul style="margin: 0pt; padding: 0pt; position: relative; list-style-type: none; z-index: 1; width: 1470px; left: -490px;">
																<?foreach($arM["moreItems"]["item"] as $moreItem)
																{
																	$moreItem["url"] = str_replace("#module#", $moreItem["code"], "update_system_market.php?module=#module#&lang=".LANGUAGE_ID);
																	$moreItem["urlClick"] = str_replace("#module#", $moreItem["code"], $sTableID.".GetAdminList('/bitrix/admin/update_system_market.php?module=#module#&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&table_id=".$sTableID.((intval($category) > 0) ? "&category=".$category : "")."'); return false;");

																	$moreItem["urlInstall"] = "update_system_partner.php?lang=".LANGUAGE_ID."&addmodule=".$moreItem["code"];
																	if(!empty($m[$moreItem["code"]]))
																	{
																		$moreItem["installed"] = "Y";
																		if($m[$moreItem["code"]] == "Y")
																			$moreItem["installedDemo"] = "Y";
																	}
																	$moreItem["canDemo"] =
																		isset($moreItem["freeModule"])
																		&& $moreItem["freeModule"] == "D"
																		&& (!isset($moreItem["installed"]) || $moreItem["installed"] != "Y")
																			? "Y"
																			: "N"
																	;

																	?>
																	<li style="overflow: hidden; float: left; width: 225px; height: 114px;">
																		<div class="mp-name"><a href="<?=$moreItem["url"]?>" title="<?=$moreItem["name"]?>"><?=$moreItem["name"]?></a></div>
																		<a href="<?=$moreItem["url"]?>" title="<?=$moreItem["name"]?>"><span class="mp-list-slide-block-image" style="background: url('<?=$moreItem["logo"]["src"]?>') center center no-repeat; width:<?=$moreItem["logo"]["width"]?>px; height:<?=$moreItem["logo"]["height"]?>px; display:block; border: 1px solid #c0c0c0; float: left;">
																				<?
																				if(!empty($moreItem["icons"]) > 0)
																				{
																					foreach($moreItem["icons"] as $v)
																					{
																						?>
																						<img src="<?=$v["src"]?>" border="0" style="<?=$v["styles"]?>" width="<?=$v["width"]?>" height="<?=$v["height"]?>" />
																						<?
																					}
																				}
																				?>
																		</span></a>
																		<div class="mp-content">
																			<span class="mp-ilike"><?=intval($moreItem["votes"] ?? 0)?></span>
																			<div>
																				<?
																				if(isset($moreItem["installed"]) && $moreItem["installed"] == "Y")
																				{
																					?><div class="mp-grey"><?=GetMessage("USM_INSTALLED")?></div><?
																				}

																				if(isset($moreItem["freeModule"]) && $moreItem["freeModule"] == "Y")
																				{
																					if(!isset($moreItem["installed"]) || $moreItem["installed"] != "Y")
																					{
																						?><div class="mp-install"><a href="<?=$moreItem["urlInstall"]?>"><?=GetMessage("USM_INSTALL")?></a></div>
																						<div class="mp-grey"><small><?=GetMessage("USM_FREE")?></small></div><?
																					}
																				}
																				else
																				{
																					if(isset($moreItem["oldPrice"]) && intval($moreItem["oldPrice"]) > 0)
																					{
																						?><div class="mp-price"><s><?=intval($moreItem["oldPrice"])?></s>&nbsp;&nbsp;<span style="color:red;"><?=$moreItem["price"]?></span></div><?
																					}
																					else
																					{
																						?><div class="mp-price"><?=$moreItem["price"];?></div><?
																					}

																					if(!isset($moreItem["installedDemo"]) || $moreItem["installedDemo"] != "Y")
																					{
																						?><div class="mp-buy"><a href="<?=$moreItem["url2basket"]?>" target="_blank"><?=GetMessage("USM_BUY")?></a></div><?
																					}
																					if($moreItem["canDemo"] == "Y")
																					{
																						?><div class="mp-test"><a href="<?=htmlspecialcharsbx($moreItem["urlInstall"])?>" target="_blank"><?=GetMessage("USM_TEST")?></a></div><?
																					}
																				}
																				?>
																			</div>
																		</div>
																	</li>
																	<?
																}
																?>
															</ul>
														</div>
														<a class="scroll-next solutions-next"></a>
													</td>
												</tr>
											</tbody>
										</table>
									</div>

									<?
									}

									if(!empty($arM["comments"]) > 0)
									{
										if(!isset($arM["comments"]["comment"][0]) || !is_array($arM["comments"]["comment"][0]))
											$arM["comments"]["comment"] = array($arM["comments"]["comment"]);
										?>
										<h3><?=GetMessage("USM_COMMENTS")?></h3>
										<div id="comments">
											<hr class="comments-delimiter" noshade>
											<div style="text-align:center;"><a target="_blank" href="<?=$arM["url2module"]?>"><?=GetMessage("USM_COMMENTS_ADD")?></a></div>
										<?
										foreach($arM["comments"]["comment"] as $v)
										{
											?>
											<hr class="comments-delimiter" noshade>
											<div>
												<div class="mp-comment-title"><b><?=$v["author"]?></b> <span class="mp-grey comment-created"><?=$v["date"]?></span></div>
												<?=$v["text"]?>
											</div>
											<?
										}
										?></div><?
									}?>


								</div>
							</div>



							<?
						}
					}

					if($moduleCode == '')
					{
						if($inRow !=0 || $inRow!=3)
						{
							echo str_repeat("<td></td>", 3-$inRow);
							echo "</tr>";
						}
						echo "</table>";
					}
				}
				else
				{
					echo GetMessage("USM_EMPTY_CATEGORY");
				}
				?>
			</div>
		</div>
	</div>
	<?

	if(!empty($arResult["modules"]["#"]["navData"][0]["#"]))
	{
		$dat = unserialize($arResult["modules"]["#"]["navData"][0]["#"], ['allowed_classes' => false]);
		if(isset($dat["NavPageCount"]) && intval($dat["NavPageCount"]) > 1)
		{
			$dbRes = new CDBResult;
			foreach($dat as $k => $v)
				$dbRes->{$k} = $v;

			$dbResultList = new CAdminResult($dbRes, $sTableID);
			$dbResultList->NavRecordCountChangeDisable = true;
			$dbResultList->NavPrint(GetMessage("USM_NAV"));
		}
	}

	$lAdmin->EndCustomContent();

	if (isset($_REQUEST["mode"]) && ($_REQUEST["mode"]=='list' || $_REQUEST["mode"]=='frame'))
	{
		$APPLICATION->RestartBuffer();
		$lAdmin->Display();
		define("ADMIN_AJAX_MODE", true);
		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");
		die();
	}

	if(isset($_REQUEST["instadm"]) && $_REQUEST["instadm"] == "Y" && check_bitrix_sessid() && $moduleCode <> '' && !empty($arM))
	{
		CAdminNotify::Add([
			'MODULE_ID' => 'main',
			'TAG' => 'mp_inst_' . $moduleCode,
			'MESSAGE' => GetMessage("USM_NOTIF_INST", [
				"#USER#" => htmlspecialchars($USER->GetFullName() . " (" . $USER->GetLogin() . ")"),
				"#MODULE_CODE#" => htmlspecialcharsbx($arM["code"]),
				"#MODULE_NAME#" => htmlspecialcharsbx($arM["name"]),
				"#LANG#" => LANG,
			]),
			'NOTIFY_TYPE' => CAdminNotify::TYPE_NORMAL,
			'PUBLIC_SECTION' => 'N',
		]);

		$m = new CAdminMessage([
			"TYPE" => "OK",
			"MESSAGE" => GetMessage("USM_NOTIF_INST_OK"),
			"HTML" => false,
		]);
		echo $m->Show();
	}
	$lAdmin->DisplayList();
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>