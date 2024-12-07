<?php

use Bitrix\Main\Web\Json;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2016 Bitrix
 *
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var CBitrixComponent $component
 * @var string $templateFolder
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDataBase $DB
 *
TABS unused yet
SHOW_FORM_TAG true|false
FORM_ID
THEME_GRID_ID
~DATA - obsolete
MAX_FILE_SIZE unused yet
RESTRICTED_MODE true|false
BUTTONS
DATE_TIME_FORMAT - for datetimePicker
DATE_FORMAT - for datetimePicker
TIME_FORMAT - for datetimePicker
*/

if (empty($arParams["DATE_TIME_FORMAT"]) ||  $arParams["DATE_TIME_FORMAT"] === "FULL")
{
	$arParams["DATE_TIME_FORMAT"]= CDatabase::DateFormatToPHP(FORMAT_DATETIME);
}

$arParams["DATE_TIME_FORMAT"] = preg_replace('/[\/.,\s:][s]/', '', $arParams["DATE_TIME_FORMAT"]);
if (!$arParams["TIME_FORMAT"])
{
	$arParams["TIME_FORMAT"] = preg_replace(array('/[dDjlFmMnYyo]/', '/^[\/.,\s]+/', '/[\/.,\s]+$/'), "", $arParams["DATE_TIME_FORMAT"]);
}

if (!$arParams["DATE_FORMAT"])
{
	$arParams["DATE_FORMAT"] = trim(str_replace($arParams["TIME_FORMAT"], "", $arParams["DATE_TIME_FORMAT"]));
}

$uploadedFile = <<<HTML
<div class="mobile-grid-field-file-item mobile-grid-field-file-#class#" id="file-#id#">
	<div class="mobile-grid-field-file-item-inner">
		<del></del>
		<span class="mobile-grid-field-file-preview">
			<span class="files-preview-border"><span class="files-preview-alignment">
				<img class="files-preview" src="#preview_url#" />
			</span></span>
		</span>
		<span class="mobile-grid-field-file-icon icon icon-#ext#"></span>
		<span class="mobile-grid-field-file-name">#name#</span>
		<span class="mobile-grid-field-file-size">#size#</span>
		<input type="hidden" name="#control_name#" value="#id#" />
	</div>
</div>
HTML;
$m = GetMessage("MPF_ERROR1");
$thumb = <<<HTML
<div class="mobile-grid-field-file-item-inner">
	<i class="mobile-grid-wait"></i>
	<del></del>
	<span class="mobile-grid-field-file-preview">
		<span class="files-preview-border"><span class="files-preview-alignment">
			#preview#
		</span></span>
	</span>
	<span class="mobile-grid-field-file-icon icon icon-#ext#"></span>
	<span class="mobile-grid-field-file-name">#name#</span>
	<span class="mobile-grid-field-file-size">#size#</span>
	<span class="mobile-grid-field-file-error-text">$m</span>
</div>
HTML;
$arParams["DATE_TIME_FORMAT"] = array(
	"tomorrow" => "tomorrow, ".$arParams["TIME_FORMAT"],
	"today" => "today, ".$arParams["TIME_FORMAT"],
	"yesterday" => "yesterday, ".$arParams["TIME_FORMAT"],
	"" => $arParams["DATE_TIME_FORMAT"]
);
$arParams["DATE_FORMAT"] = array(
	"tomorrow" => "tomorrow",
	"today" => "today",
	"yesterday" => "yesterday",
	"" => $arParams["DATE_FORMAT"]
);

CJSCore::GetCoreMessages();

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ajax',
	'date',
	"mobile_fastclick",
	"uploader",
]);

global $APPLICATION;
$APPLICATION->SetPageProperty('BodyClass', 'mobile-grid-field-form');
$APPLICATION->SetAdditionalCSS($templateFolder."/style_add.css");

$userUrl = str_replace("//", "/", "/".SITE_DIR."mobile/users/?user_id=#ID#");
$groupUrl = str_replace("//", "/", "/".SITE_DIR."mobile/log/?group_id=#ID#");

?>
<div class="mobile-grid mobile-grid-entity <?php if ($arParams["RESTRICTED_MODE"]) { echo "mobile-grid-restricted"; } ?> ">
<?php
if ($arParams["SHOW_FORM_TAG"])
{
	?>
	<form name="<?=$arParams["FORM_ID"]?>" id="<?=$arParams["FORM_ID"]?>" action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data">
	<?= bitrix_sessid_post(); ?>
	<input type="hidden" id="<?=$arParams["FORM_ID"]?>_active_tab" name="<?=$arParams["FORM_ID"]?>_active_tab" value="<?= htmlspecialcharsbx($arResult["SELECTED_TAB"]) ?>">
	<?php
}
?><div class="bx-tabs"><?php
$i = 0;
$jsObjects = array();
foreach($arResult["TABS"] as $tab)
{
//	$bSelected = ($tab["id"] == $arResult["SELECTED_TAB"]);
	$bWasRequired = false;
	$sections = array();
	?>
	<div id="tab_<?=$tab["id"]?>">
		<?php
			if (!empty($tab['title']))
			{
				?>
				<div class="mobile-grid-tab-title"><?= \Bitrix\Main\Text\HtmlFilter::encode($tab['title']) ?></div>
				<?php
			}
		?>
		<div
			id="inner_tab_<?=$tab["id"]?>"
			class="bx-edit-tab-inner">
			<?php
			if ($tab["icon"] <> "")
			{
				?> <div class="bx-icon <?=htmlspecialcharsbx($tab["icon"])?>"></div> <?php
			}
			?>
			<div style="height: 100%;">
				<div class="bx-edit-table <?=(isset($tab["class"]) ? $tab['class'] : '')?>" id="<?=$tab["id"]?>_edit_table"><?php
/**
 * File
array_unshift($tab["fields"], array(
		"type" => "image",  // image||file
		"required" => true,
		"id" => "data[FILE][]",
		"maxCount" => 1,
		"name" => "File Title",
		"value" => array(
		"1", "2", "3", "4"   // File id from b_file or array of files
		),
//		"url" => "/url to upload file /", // not finished and not tested
	));
*/
					foreach ($tab["fields"] as $field)
					{
						if (!is_array($field))
						{
							continue;
						}

						if ($arParams["RESTRICTED_MODE"] && empty($field["value"]) && $field["value"] !== "0")
						{
							continue;
						}

						$style = '';
						if(isset($field["show"]))
						{
							if ($field["show"] === "N")
							{
								$style = "display:none;";
							}
						}

						if ($field["type"] === 'section')
						{
							if (!empty($field["id"]))
							{
								$expanded = ($field["expanded"] == "Y" || $field["expanded"] == true);

								?><div class="mobile-grid-field" id="<?=$field["id"]?>" <?php if(!empty($style)): ?> style="<?= $style ?>"<?php endif ?>><?php
									if(array_key_exists("name", $field))
									{
										?><div class="mobile-grid-title"><?=htmlspecialcharsEx($field["name"])?></div><?php
									}
									?><input id="checkbox_<?=$field["id"]?>" type="checkbox" <?=($expanded ? "checked" : "")?> value="section" /><?php
									?><label for="checkbox_<?=$field["id"]?>" class="mobile-grid-field-switcher"><?=htmlspecialcharsbx($field["value"])?></label><?php
									?><div class="mobile-grid-body" id="section_<?=$field["id"]?>_body"<?php if(!empty($style)): ?> style="<?= $style ?>"<?php endif ?>><?php
							}
							else
							{
								?><div class="mobile-grid-field<?php if(array_key_exists("class", $field)): ?> <?= htmlspecialcharsbx($field['class']) ?><?php endif ?>"<?php
									if(!empty($style)): ?> style="<?= $style ?>"<?php endif ?>><?php
									?><span class="mobile-grid-field-head"><?= htmlspecialcharsbx($field["name"]) ?></span><?php
							}
							$sections[] = $field["id"];
						}
						else
						{
							if (!empty($sections))
							{
								while (($section = end($sections)))
								{
									if ($field["section"] == $section)
									{
										break;
									}
									else
									{
										?></div>
									</div><?php
										array_pop($sections);
									}
								}
							}

							$val = ($field["value"] ?? $arParams["~DATA"][$field["id"]]);

							//default attributes
							if (!is_array($field["params"]))
							{
								$field["params"] = array();
							}

							if ($field["type"] == '' || $field["type"] === 'text')
							{
								if ($field["params"]["size"] == '')
								{
									$field["params"]["size"] = "30";
								}
							}
							elseif ($field["type"] === 'textarea')
							{
								if ($field["params"]["cols"] == '')
								{
									$field["params"]["cols"] = "40";
								}

								if ($field["params"]["rows"] == '')
								{
									$field["params"]["rows"] = "3";
								}
							}
							elseif ($field["type"] === 'date')
							{
								if($field["params"]["size"] == '')
								{
									$field["params"]["size"] = "10";
								}
							}

							$params = '';
							if (is_array($field["params"]) && $field["type"] <> 'file')
							{
								foreach($field["params"] as $p => $v)
								{
									$params .= ' ' . $p . '="' . $v . '"';
								}
							}
							$field["~id"] = "bx_".preg_replace("/[^a-z0-9_-]/i", "_", $field["id"]);

							$bWasRequired = ($bWasRequired ? : $field["required"]);
							$className = "";
							$html = "";

							if(!empty($field['userField']))
							{

								$className = $field['userField']['USER_TYPE_ID'];
								if ($className === \Bitrix\Main\UserField\Types\BooleanType::USER_TYPE_ID)
								{
									$i++;
									$field["~id"] .= $i;
								}

								$field['userField']['~id'] = $field["~id"];
								$userField = $field['userField'];

								$mode = (
									$arParams["RESTRICTED_MODE"]
										? [\Bitrix\Main\UserField\Types\BaseType::MODE_VIEW]
										: [\Bitrix\Main\UserField\Types\BaseType::MODE_EDIT]
								) ;

								$additionalParameters = [
									'mode' => $mode,
									'mediaType' => \Bitrix\Main\Component\BaseUfComponent::MEDIA_TYPE_MOBILE,
									'formId' => $arParams['FORM_ID'] ?? false,
									'gridId' => $arParams['THEME_GRID_ID'] ?? false,
									'componentName' => $arParams['COMPONENT_NAME'] ?? ''
								];

								$html = (new \Bitrix\Main\UserField\Renderer($userField, $additionalParameters))->render();
							}
							else
							{
								switch($field["type"])
								{
									case 'custom':
									case 'label':
										//html allowed
										$className = $field["type"];
										$html = $val;
										break;
									case 'text':
										$placeholder = htmlspecialcharsbx($field["placeholder"] ?: $field["name"]);
										$val = htmlspecialcharsbx($val);
										$className = "text";
										if($arParams["RESTRICTED_MODE"])
										{
											$html = "<input type=\"hidden\" data-bx-type=\"text\" placeholder=\"{$placeholder}\" name=\"{$field["id"]}\" id=\"{$field["~id"]}\" $params value=\"$val\" />" .
												"<span id=\"{$field["~id"]}_target\" class=\"text\">" . ($val == "" ? "<span class=\"placeholder\">" . $placeholder . "</span>" : $val) . "</span>";
										}
										else
										{
											$html = "<input class=\"mobile-grid-data-text\" type=\"text\" placeholder=\"{$placeholder}\" name=\"{$field["id"]}\" id=\"{$field["~id"]}\" $params value=\"$val\" />";
										}
										$jsObjects[] = $field["~id"];
										break;
									case 'number':
										$className = "number";
										$valFrom = $field["item"]["from"];
										$valTo = $field["item"]["to"];
										$html = "<input type=\"text\" class=\"mobile-grid-data-text\" name=\"" . $field["id"] . "_from\" id=\"{$field["~id"]}\" value=\"" . htmlspecialcharsbx($valFrom) . "\" style=\"border-bottom: 1px solid #dee0e3;\" placeholder=\"" . GetMessage("interface_form_from") . "\">
														<input type=\"text\" class=\"mobile-grid-data-text\" name=\"" . $field["id"] . "_to\" value=\"" . htmlspecialcharsbx($valTo) . "\" placeholder=\"" . GetMessage("interface_form_to") . "\">";
										break;
									case 'textarea':
										$placeholder = htmlspecialcharsbx($field["placeholder"] ?: $field["name"]);
										$val = htmlspecialcharsbx($val);
										$className = "textarea";
										if($arParams["RESTRICTED_MODE"])
										{
											$html = "<input type='hidden' data-bx-type='textarea' placeholder=\"{$placeholder}\" name=\"{$field["id"]}\" id=\"{$field["~id"]}\" $params value=\"$val\" />" .
												"<span id=\"{$field["~id"]}_target\">" . ($val == "" ? "<span class='placeholder'>" . $placeholder . "</span>" : $val) . "</span>";
										}
										else
										{
											$html = "<textarea class=\"mobile-grid-data-textarea\" name=\"{$field["id"]}\" id=\"{$field["~id"]}\" placeholder=\"$placeholder\" $params>$val</textarea>";
										}
										$jsObjects[] = $field["~id"];
										break;
									case 'select-group':
									case 'select-user':
										$url = ($field["type"] === 'select-user' ? $userUrl : $groupUrl);
										$className = "user";
										$jsObjects[] = $field["~id"];
										ob_start();

										if (is_array($field["item"]))
										{
											$item = array_change_key_case($field["item"], CASE_LOWER);
											$html .= "<option value=\"{$item["id"]}\" selected>{$item["id"]}</option>";

											?>
											<div class="mobile-grid-field-select-user-item"><?php
											if($field["canDrop"] !== false):
												?>
												<del id="<?= $field["~id"] ?>_del_<?= $item["id"] ?>"></del><?php
											endif;
											?>
											<div class="avatar"<?php
											if(!empty($item["avatar"])):?> style="background-image:url('<?= htmlspecialcharsbx($item["avatar"]) ?>')"<?php endif; ?>></div>
											<?php
											if ($field['type'] === 'select-user')
											{
												?>
												<span onclick="BXMobileApp.Events.postToComponent('onUserProfileOpen', [<?= $item["id"] ?>], 'communication');"><?= htmlspecialcharsbx($item["name"]) ?></span>
												<?php
											}
											elseif (
												$field['type'] === 'select-group'
												&& isset($field['useLink'])
												&& $field['useLink'] === 'Y'
											)
											{
												?>
												<a class="mobile-grid-field-select-user-item-link" href="<?= str_replace("#ID#", $item["id"], $url) ?>"><?= htmlspecialcharsbx($item["name"]) ?></a>
												<?php
											}
											else
											{
												?>
												<span onclick="BXMobileApp.PageManager.loadPageBlank({url: '<?= str_replace("#ID#", $item["id"], $url) ?>',bx24ModernStyle: true});"><?= htmlspecialcharsbx($item["name"]) ?></span>
												<?php
											}
											?>
											</div><?php
										}
										$users = ob_get_clean();
										$useLink = (isset($field['useLink']) && $field['useLink'] === 'Y' ? 'Y' : 'N');
										$html = "<select class=\"mobile-grid-data-select\" name=\"{$field["id"]}\" data-bx-type=\"{$field["type"]}\" data-bx-use-link=\"{$useLink}\" bx-can-drop=\"" . ($field["canDrop"] === false ? "false" : "") . "\" id=\"{$field["~id"]}\"{$params}>" . $html . "</select>" .
											"<div id=\"{$field["~id"]}_target\" class=\"mobile-grid-field-select-user-container\">" . $users . "</div>" .
											"<a class=\"mobile-grid-button select-user add\" id=\"{$field["~id"]}_select\" href=\"#\">" . ($users == '' ? GetMessage("interface_form_select") : GetMessage("interface_form_change")) . "</a>";
										break;
									case 'group':
									case 'user':
										$url = ($field["type"] === 'user' ? $userUrl : $groupUrl);
										$className = "user";

										ob_start();
										if (is_array($field["item"]))
										{
											$item = array_change_key_case($field["item"], CASE_LOWER);
											?><div class="mobile-grid-field-select-user-item">
												<div class="avatar"<?php if(!empty($item["avatar"])):?> style="background-image:url('<?=htmlspecialcharsbx($item["avatar"])?>')"<?php endif;?>></div>
												<?php
												if ($field['type'] === 'user')
												{
													?>
													<span onclick="BXMobileApp.Events.postToComponent('onUserProfileOpen', [<?=$item["id"]?>], 'communication');"><?=htmlspecialcharsbx($item["name"])?></span>
													<?php
												}
												elseif ($field['type'] === 'group')
												{
													?>
													<a class="mobile-grid-field-select-user-item-link" href="<?= str_replace("#ID#", $item["id"], $url) ?>"><?= htmlspecialcharsbx($item["name"]) ?></a>
													<?php
												}
												else
												{
													?>
													<span onclick="BXMobileApp.PageManager.loadPageBlank({url: '<?=str_replace("#ID#", $item["id"], $url)?>',bx24ModernStyle: true});"><?=htmlspecialcharsbx($item["name"])?></span>
													<?php
												}
												?>
											</div><?php
										}
										$users = ob_get_clean();
										$html = "<div class=\"mobile-grid-field-select-user-container\">".$users."</div>";
										break;
									case 'select-groups':
									case 'select-users':
										$field["type"] = ($field["type"] === "select-users" ? "select-user" : "select-group");
										$url = ($field["type"] === 'select-user' ? $userUrl : $groupUrl);
										$className = "user";
										$jsObjects[] = $field["~id"];
										$u = 0;
										ob_start();
										if ($field["items"])
										{
											$val = is_array($val) ? $val : array($val);
											foreach ($field["items"] as $item)
											{
												if (!is_array($item))
												{
													continue;
												}
												$item = array_change_key_case($item, CASE_LOWER);
												if (!in_array($item["id"], $val))
												{
													continue;
												}

												$u++;
												$html .= "<option value=\"{$item["id"]}\" selected>{$item["id"]}</option>";
												?><div class="mobile-grid-field-select-user-item"><?php
													if ($field["canDrop"] !== false):
														?><del id="<?=$field["~id"]?>_del_<?=$item["id"]?>"></del><?php
													elseif (is_array($field["menu"])):
														?><i class="mobile-grid-menu" id="<?=$field["~id"]?>_menu_<?=$item["id"]?>"></i><?php
													endif;
													?>
													<div class="avatar"<?php if (!empty($item["avatar"])): ?> style="background-image:url('<?=htmlspecialcharsbx($item["avatar"])?>')"<?php endif; ?>></div>
													<?php
													if ($field['type'] === 'select-user')
													{
														?>
														<span onclick="BXMobileApp.Events.postToComponent('onUserProfileOpen', [<?=$item["id"]?>], 'communication');"><?=htmlspecialcharsbx($item["name"])?></span>
														<?php
													}
													elseif ($field['type'] === 'select-group')
													{
														?>
														<a class="mobile-grid-field-select-user-item-link" href="<?= str_replace("#ID#", $item["id"], $url) ?>"><?= htmlspecialcharsbx($item["name"]) ?></a>
														<?php
													}
													else
													{
														?>
														<span onclick="BXMobileApp.PageManager.loadPageBlank({url: '<?=str_replace("#ID#", $item["id"], $url)?>',bx24ModernStyle: true});"><?=htmlspecialcharsbx($item["name"])?></span>
														<?php
													}
													?>
												</div><?php
											}
										}
										$users = ob_get_clean();

										$addButton = (
											!isset($field['canAdd']) || $field['canAdd']
												? "<a class=\"mobile-grid-button select-user add\" id=\"{$field["~id"]}_select\" href=\"#\">".GetMessage("interface_form_add")."</a>"
												: ''
										);
										$html = "<select class=\"mobile-grid-data-select\"  name=\"{$field["id"]}\" data-bx-type=\"{$field["type"]}\" bx-can-drop=\"".($field["canDrop"] === false ? "false" : "")."\" id=\"{$field["~id"]}\" multiple {$params}>".$html."</select>".
											"<div class=\"mobile-grid-field-select-user-wrap\">".
												"<input value=\"$u\" type=\"checkbox\" id=\"expand_{$field["~id"]}\" />".
												"<div class=\"mobile-grid-field-select-user-container\" id=\"{$field["~id"]}_target\">".$users."</div>".
												"<label class=\"mobile-grid-field-select-user-more\" for=\"expand_{$field["~id"]}\">".
													"<span class=\"unchecked\">".GetMessage("interface_form_show_more")." (<span id=\"count_{$field["~id"]}\">".($u-3)."</span>)</span>".
													"<span class=\"checked\">".GetMessage("interface_form_hide")."</span>".
												"</label>".
											"</div>".
											$addButton;
										break;
									case 'groups':
									case 'users':
										$field["type"] = ($field["type"] === "users" ? "user" : "group");
										$url = ($field["type"] === 'user' ? $userUrl : $groupUrl);
										$className = "user";
										$u = 0;
										$i++;
										ob_start();
										if (isset($field["items"]) && is_array($field["items"]))
										{
											foreach($field["items"] as $item)
											{
												if (!is_array($item))
												{
													continue;
												}
												$u++;
												$item = array_change_key_case($item, CASE_LOWER);

												?><div class="mobile-grid-field-select-user-item">
													<div class="avatar"<?php if (!empty($item["avatar"])): ?> style="background-image:url('<?= htmlspecialcharsbx($item["avatar"]) ?>')"<?php endif; ?>></div>
													<?php
													if ($field['type'] === 'users')
													{
														?>
														<span onclick="BXMobileApp.Events.postToComponent('onUserProfileOpen', [<?=$item["id"]?>], 'communication');"><?=htmlspecialcharsbx($item["name"])?></span>
														<?php
													}
													elseif ($field['type'] === 'groups')
													{
														?>
														<a class="mobile-grid-field-select-user-item-link" href="<?= str_replace("#ID#", $item["id"], $url) ?>"><?=htmlspecialcharsbx($item["name"])?></a>
														<?php
													}
													else
													{
														?>
														<span onclick="BXMobileApp.PageManager.loadPageBlank({url: '<?=str_replace("#ID#", $item["id"], $url)?>',bx24ModernStyle : true});"><?=htmlspecialcharsbx($item["name"])?></span>
														<?php
													}
													?>
												</div><?php
											}
										}
										$users = ob_get_clean();

										$html =
											"<div class=\"mobile-grid-field-select-user-wrap\">" .
											"<input value=\"$u\" type=\"checkbox\" id=\"expand_$i\" />" .
											"<div class=\"mobile-grid-field-select-user-container\">" . $users . "</div>" .
											"<label class=\"mobile-grid-field-select-user-more\" for=\"expand_$i\">" .
											"<span class=\"unchecked\">" . GetMessage("interface_form_show_more") . " (<span>" . ($u - 3) . "</span>)</span>" .
											"<span class=\"checked\">" . GetMessage("interface_form_hide") . "</span>" .
											"</label>" .
											"</div>";
										break;
									case 'list':
									case 'select':
										$className = "select";
										if(is_array($field["items"]))
										{
											if(!is_array($val))
												$val = array($val);
											$items = array();

											$selected = array_intersect(array_keys($field["items"]), $val);
											if(empty($selected) && !(is_array($field["params"]) && array_key_exists("multiple", $field["params"])))
											{
												$selected = array(reset(array_keys($field["items"])));
											}

											$html = "<select class=\"mobile-grid-data-select\" name=\"{$field["id"]}\" id=\"{$field["~id"]}\"{$params}>";
											foreach($field["items"] as $k => $v):
												$items[$k] = $v;
												$s = (in_array($k, $selected) ? " selected" : "");
												$k = htmlspecialcharsbx($k);
												$v = htmlspecialcharsbx($v);
												$html .= "<option value=\"{$k}\" {$s}>$v</option>";
											endforeach;
											$html .= "</select>";


											if(is_array($field["params"]) && array_key_exists("multiple", $field["params"]))
											{
												$html .= "<span id=\"{$field["~id"]}_target\">";
												foreach($selected as $k)
												{
													$v = htmlspecialcharsbx($items[$k]);
													$html .= "<a href=\"javascript:void();\">$v</a>"; // TODO we need to decide how should it looks
												}
												$html .= "</span>" .
													"<a class=\"mobile-grid-button select-change\" href=\"#\" id=\"{$field["~id"]}_select\">" . GetMessage("interface_form_change") . "</a>";
											}
											else if(empty($selected))
											{
												break;
											}
											else
											{
												$k = reset($selected);
												$v = htmlspecialcharsbx($items[$k]);
												$html .= "<a href='#' id=\"{$field["~id"]}_select\">$v</a>";
											}
											$jsObjects[] = $field["~id"];
										}
										break;
									case 'checkbox':
										$className = "checkbox";
										$items = (is_array($field["items"]) ? $field["items"] : array("Y" => "Y"));
										$val = (is_array($val) ? $val : array($val));
										foreach($items as $k => $v)
										{
											$i++;
											$k = htmlspecialcharsbx($k);
											$v = htmlspecialcharsbx($v);
											$checked = (in_array($k, $val) ? ' checked' : '');
											$html .= "<label for=\"{$field["~id"]}{$i}\">" .
												"<input type=\"checkbox\" id=\"{$field["~id"]}{$i}\" name=\"{$field["id"]}\" value=\"{$k}\" {$checked}{$params} />" .
												"<span>{$v}</span>" .
												"</label>";
											$jsObjects[] = $field["~id"] . $i;
										}
										break;
									case 'radio':
										$className = "radio";
										$items = (is_array($field["items"]) ? $field["items"] : array("Y" => "Y"));
										$val = (is_array($val) ? $val : array($val));
										foreach($items as $k => $v)
										{
											$i++;
											$k = htmlspecialcharsbx($k);
											$v = htmlspecialcharsbx($v);
											$checked = (in_array($k, $val) ? ' checked' : '');
											$html .= "<label for=\"{$field["~id"]}{$i}\">" .
												"<input type=\"radio\" id=\"{$field["~id"]}{$i}\" name=\"{$field["id"]}\" value=\"{$k}\" {$checked}{$params} />" .
												"<span>{$v}</span>" .
												"</label>";
											$jsObjects[] = $field["~id"] . $i;
										}
										break;
									case 'diskview':
									case 'disk_fileview':
									case 'crmview':
										$field["type"] = ($field["type"] === "diskview" ? "disk_fileview" : $field["type"]);
										$field["type"] = mb_substr($field["type"], 0, -4);
										if (
											($field["type"] === "crm" && !\Bitrix\Main\ModuleManager::isModuleInstalled("crm"))
											|| ($field["type"] === "disk_file" && !\Bitrix\Main\ModuleManager::isModuleInstalled("disk"))
										)
										{
											break;
										}

										$val = is_array($val) ? $val : array();
										$className = ($field["type"] === "disk_file" ? "file" : $field["type"]);
										ob_start();
										$APPLICATION->IncludeComponent("bitrix:system.field.view", $field["type"],
											array(
												"arUserField" => $val,
												"MOBILE" => "Y",
												"CAN_EDIT" => false,
												"formId" => $arParams["FORM_ID"],
												'mode' => 'main.view',
												'mediaType' => 'mobile'
											),
											$component,
											array("HIDE_ICONS" => "Y")
										);
										$html = ob_get_clean();
										if($html == '')
											break;
										break;
									case 'crm':
									case 'disk_file':
									case 'disk':
										$field["type"] = ($field["type"] === "disk" ? "disk_file" : $field["type"]);
										if(
											($field["type"] === "crm" && !\Bitrix\Main\ModuleManager::isModuleInstalled("crm"))
											|| ($field["type"] === "disk_file" && !\Bitrix\Main\ModuleManager::isModuleInstalled("disk"))
										)
										{
											break;
										}
										$val = is_array($val) ? $val : array();
										$className = ($field["type"] === "disk_file" ? "file" : $field["type"]);
										ob_start();
										$APPLICATION->IncludeComponent("bitrix:system.field.edit", $field["type"],
											array(
												"arUserField" => $val,
												"MOBILE" => "Y",
												"CAN_EDIT" => true,
												"formId" => $arParams["FORM_ID"],
												'mode' => 'main.edit',
												'mediaType' => 'mobile'
											),
											$component,
											array("HIDE_ICONS" => "Y")
										);
										$html = ob_get_clean();
										if ($html == '')
										{
											break;
										}

										$html .= '<input type="hidden" id="' . $field["~id"] . '" value="' . $val["FIELD_NAME"] . '" data-bx-type="' . $field["type"] . '" />';
										$jsObjects[] = $field["~id"];
										break;
									case 'file':
									case 'image':
										$className = "file";
										$val = (is_array($val) ? $val : array($val));
										$uploadedFile = preg_replace("/[\n\t]+/", "", $uploadedFile);
										ob_start();
										?>
										<input type="hidden" <?php
										?>name="<?= $field["id"] ?>" <?php
													 ?>value="0" <?php
													 ?>id="<?= $field["~id"] ?>" <?php
													 ?>data-bx-type="<?= $field["type"] ?>" <?php
													 ?>data-bx-extension="<?= $field["ext"] ?>" <?php
													 ?>data-bx-url="<?= $field["url"] ?>" <?php
													 ?>data-bx-name="<?= $field["id"] ?>" <?php
													 ?>data-bx-max="<?= $field["maxCount"] ?>" <?php
										?> /><?php
										?>
										<div id="file-placeholder-<?= $field["~id"] ?>"><?php
											foreach($val as $f)
											{
												$f = (is_array($f) ? $f : CFile::GetFileArray($f));
												if(is_array($f))
												{
													?><?= str_replace(
													array(
														"#id#",
														"#name#",
														"#size#",
														"#preview_url#",
														"#class#",
														"#control_name#"
													),
													array(
														$f["ID"],
														$f["FILE_NAME"],
														CFile::FormatSize($f["FILE_SIZE"]),
														(empty($f["SRC"]) ? "javascript:void(0);" : $f["SRC"]),
														(empty($f["SRC"]) ? "file" : "image"),
														$field["id"]
													),
													$uploadedFile
												) ?><?php
												}
											}
										?></div>
										<div class="mobile-grid-button file" id="file-eventnode-<?= $field["~id"] ?>"><?php
											?><?= ($field["maxCount"] != 1 ? GetMessage("interface_form_add") : GetMessage("interface_form_change")) ?><?php
											?>
											<input class="mobile-grid-button-file" type="file" id="<?= $field["~id"] ?>_file" size="1" <?php
											if($field["maxCount"] != 1)
											{
											?>multiple="multiple" <?php
											} ?>/>
										</div>
										<?php
										$html = ob_get_clean();
										$jsObjects[] = $field["~id"];
										break;
									case 'datetime':
									case 'date':
									case 'time':
										$className = "date";
										$placeholder = htmlspecialcharsbx($field["placeholder"] ?: $field["name"]);
										$html = "<input type='hidden' data-bx-type=\"{$field["type"]}\" id=\"{$field["~id"]}\" name=\"{$field["id"]}\" {$params} value=\"{$val}\" />";

										$format = ($field["type"] === "datetime" ? $arParams["DATE_TIME_FORMAT"] : ($field["type"] === "date" ? $arParams["DATE_FORMAT"] : $arParams["TIME_FORMAT"]));

										if ($val)
										{
											$val = FormatDate($format, MakeTimeStamp($val));
										}

										$html .= "<div placeholder=\"{$placeholder}\" id=\"{$field["~id"]}_container\">" . ($val ? $val : $placeholder) . "</div>";

										if($field["canDrop"] !== false)
											$html .= '<del id="' . $field["~id"] . '_del" ' . ($val ? '' : 'style="display:none"') . '></del>';

										$jsObjects[] = $field["~id"];
										break;
									default:
										$className = "text";
										$html = htmlspecialcharsbx($val);
										break;
								}
							}

							if ($html == '')
							{
								continue;
							}

							?><div class="mobile-grid-section<?=(!empty($sections) ? "-child" : "")?> <?= htmlspecialcharsbx($field['class']) ?>"<?php if(!empty($style)): ?> style="<?= $style ?>"<?php endif;
								if(!empty($field['fieldId'])): ?> id="<?= $field['fieldId'] ?>"<?php endif ?>><?php
									if(array_key_exists("name", $field))
									{
										?><div class="mobile-grid-title <?= ($field["required"] ? " mobile-grid-title-required" : "") ?>" <?php
										if($field["title"] <> '') echo ' title="' . htmlspecialcharsEx($field["title"]) . '"'?>><?php
										?><?php if($field["name"] <> ''):?><?= htmlspecialcharsEx($field["name"]) ?><?php endif ?>
										</div><?php
									}
									?>
									<div class="mobile-grid-block mobile-grid-data-<?=$className?>-container">
										<?= $html ?>
									</div>
									<?php
							?></div><?php
						}
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?></div><?php
if (isset($arParams["BUTTONS"]) && is_string($arParams["BUTTONS"]) && mb_strtolower($arParams["BUTTONS"]) === "app")
{

}
else if (isset($arParams["BUTTONS"]))
{
	?>
	<div class="mobile-grid-button-panel" id="buttons_<?=$arParams["FORM_ID"]?>">
		<?php
		if($arParams["~BUTTONS"]["standard_buttons"] !== false)
		{
			?>
			<a href="#" id="submit_<?=$arParams["FORM_ID"]?>"><?=GetMessage("interface_form_save")?></a>
			<a href="#" id="cancel_<?=$arParams["FORM_ID"]?>"><?=GetMessage("interface_form_cancel")?></a>
			<?php
		}
		?>
		<?=$arParams["~BUTTONS"]["custom_html"]?>
	</div>
	<?php
}
if($arParams["SHOW_FORM_TAG"]):?>
</form>
<?php endif;?>
<script>
BX.message({
	interface_form_select : '<?=GetMessageJS("interface_form_select")?>',
	interface_form_change : '<?=GetMessageJS("interface_form_change")?>',
	interface_form_save : '<?=GetMessageJS("interface_form_save")?>',
	interface_form_cancel : '<?=GetMessageJS("interface_form_cancel")?>',
	interface_form_user_url : '<?=CUtil::JSEscape($userUrl)?>',
	interface_form_group_url : '<?=CUtil::JSEscape($groupUrl)?>',
	MPF_CANCEL : '<?=GetMessageJS("MPF_CANCEL")?>',
	MPF_PHOTO_CAMERA : '<?=GetMessageJS("MPF_PHOTO_CAMERA")?>',
	MPF_PHOTO_GALLERY : '<?=GetMessageJS("MPF_PHOTO_GALLERY")?>',
	FILE_NODE : '<?=CUtil::JSEscape($thumb)?>'
});
BX.ready(function() {
	new BX.Mobile.Grid.Form(<?= Json::encode(array(
		"gridId" => $arParams["THEME_GRID_ID"],
		"formId" => $arParams["FORM_ID"],
		"restrictedMode" => $arParams["RESTRICTED_MODE"],
		"customElements" => $jsObjects,
		"buttons" => (isset($arParams["BUTTONS"]) && is_string($arParams["BUTTONS"])? mb_strtolower($arParams["BUTTONS"]) : "none"),
		"format" => (isset($arParams["~DATE_TIME_FORMAT"]) ? array("datetime" => $arParams["~DATE_TIME_FORMAT"]) : array()) +
			(isset($arParams["~DATE_FORMAT"]) ? array("date" => $arParams["~DATE_FORMAT"]) : array()) +
			(isset($arParams["~TIME_FORMAT"]) ? array("time" => $arParams["~TIME_FORMAT"]) : array()),
		"skipLoadingScreenHiding" => $arParams["SKIP_LOADING_SCREEN_HIDING"],
	)) ?>);
});
</script>
</div>
