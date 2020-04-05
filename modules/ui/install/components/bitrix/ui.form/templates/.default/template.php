<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\UI\Extension;

/** @var array $arParams */
/** @var array $arResult */

Extension::load("ui.forms");
Extension::load("ui.buttons");

if (!is_array($arParams["SECTIONS"]) || empty($arParams["SECTIONS"]))
	return;
?>

<form name="form_<?=$arParams["FORM_ID"]?>" id="form_<?=$arParams["FORM_ID"]?>" action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data">
	<?=bitrix_sessid_post();?>
	<?
	foreach ($arParams["SECTIONS"] as $section)
	{
	?>
		<div class="ui-form-section" <?if (isset($section["ID"])):?>id="<?=$section["ID"]?>"<?endif?>>
			<?if (isset($section["TITLE"])):?>
				<div class="ui-form-section-title">
					<?=$section["TITLE"]?>
				</div>
			<?endif?>

			<?
			if (is_array($section["FIELDS"]) && !empty($section["FIELDS"]))
			{
				foreach ($section["FIELDS"] as $field)
				{
					switch ($field["type"])
					{
						case "text":
						?>
							<div class="ui-form-block">
								<label for="<?=$field["id"]?>" class="ui-ctl-label-text"><?=$field["title"]?></label>
								<div class="ui-ctl ui-ctl-textbox">
									<input
										id="<?=$field["id"]?>"
										name="<?=$field["id"]?>"
										type="text"
										class="ui-ctl-element"
										value="<?=isset($field["value"]) ? htmlspecialcharsbx($field["value"]) : ""?>"
									/>
								</div>
							</div>
						<?
						break;

						case "list":
						?>
							<div class="ui-form-block">
								<label for="<?=$field["id"]?>" class="ui-ctl-label-text"><?=$field["title"]?></label>
								<div id="list-<?=$field["id"]?>" class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
									<div class="ui-ctl-after ui-ctl-icon-angle"></div>
									<div class="ui-ctl-element js-list-title">
										<?=!empty($field["value"]) ? htmlspecialcharsbx($field["items"][$field["value"]]) : ""?>
									</div>
									<input
										id="<?=$field["id"]?>"
										name="<?=$field["id"]?>"
										type="hidden"
										value="<?=isset($field["value"]) ? htmlspecialcharsbx($field["value"]) : ""?>"
									/>
								</div>
							</div>
							<script>
								BX.ready(function () {
									var fieldId = "<?=CUtil::JSEscape($field["id"])?>";
									BX.bind(BX("list-" + fieldId), "click", function () {
										var listItems = [];
										var fields = <?=CUtil::PhpToJSObject($field["items"])?>;
										for (var value in fields)
										{
											if (!fields.hasOwnProperty(value))
												continue;

											listItems.push({
												text: fields[value],
												className: "menu-popup-no-icon",
												onclick: BX.proxy(function () {
													BX(fieldId).value = this.value;
													var title = BX.findChild(BX("list-" + fieldId), {className: "js-list-title"}, true, false);
													if (BX.type.isDomNode(title))
													{
														title.innerHTML = this.title;
													}
													var currentContext = BX.proxy_context;
													currentContext.popupWindow.close();
												}, {title: fields[value], value: value})
											});
										}
										BX.PopupMenu.show("form-popup-" + fieldId, this, listItems,
										{
											offsetTop: 0,
											offsetLeft: 0,
											width: 320,
											angle: false
										});
									});
								});
							</script>
						<?
						break;

						case "custom":
						?>
							<div class="ui-form-block">
								<?echo $field["value"];?>
							</div>
						<?
							break;
					}
				}
			}
			?>
		</div>
		<?
	}

	if(isset($arParams["BUTTONS"]))
	{
	?>
		<div>
			<?
			if(isset($arParams["BUTTONS"]["standard_buttons"]))
			{
			?>
				<?if(in_array("save", $arParams["BUTTONS"]["standard_buttons"])):?>
					<input
						type="submit"
						name="save"
						class="ui-btn ui-btn-success"
						value="<?echo GetMessage("UI_FORM_BUTTON_SAVE")?>"
						onclick="BX.addClass(this, 'ui-btn-wait')"
					/>
				<?endif?>
				<?if(in_array("cancel", $arParams["BUTTONS"]["standard_buttons"])):?>
					<input
						type="button"
						value="<?echo GetMessage("UI_FORM_BUTTON_CANCEL")?>"
						name="cancel"
						onclick="window.location='<?=htmlspecialcharsbx(CUtil::addslashes($arParams["BUTTONS"]["back_url"]))?>'"
					/>
				<?endif?>
			<?
			}
			?>
			<?=$arParams["BUTTONS"]["custom_html"]?>
		</div>
	<?
	}
	?>
</form>