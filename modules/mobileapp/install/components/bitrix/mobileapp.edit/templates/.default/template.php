<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(isset($arParams["HEAD"])):?>
	<div class="order_acceptpay_title"><?=$arParams["HEAD"]?></div>
<?endif;?>

<?if(!$arResult["SKIP_FORM"]):?>
	<form id="<?=$arResult["FORM_ID"]?>" name="<?=$arResult["FORM_NAME"]?>" enctype="multipart/form-data" action="<?=$arResult["FORM_ACTION"]?>" method="POST">
<?endif;?>

	<?if(is_array($arParams["DATA"])):?>
		<?foreach ($arParams["DATA"] as $arField):?>
				<?=CAdminMobileEdit::getFieldHtml($arField)?>
		<?endforeach;?>
	<?endif;?>

<?if(!$arResult["SKIP_FORM"]):?>
	</form>
<?endif;?>

<?if(isset($arParams["TITLE"])):?>
	<script>
		app.setPageTitle({title: "<?=$arParams["TITLE"]?>"});
	</script>
<?endif;?>

<?if(isset($arParams["BUTTONS"]) && is_array($arParams["BUTTONS"])):?>
	<script>
	<?if(in_array("SAVE", $arParams["BUTTONS"])):?>
		app.addButtons({
			saveButton:
			{
				type: "right_text",
				style: "custom",
				name: "<?=GetMessage('MAPP_ME_BUTT_SAVE')?>",
				callback: function()
				{
					var form = BX("<?=$arResult["FORM_ID"]?>");

					if(form)
					{
						<?if(isset($arParams["ON_JS_CLICK_SUBMIT_BUTTON"])):?>
							if(typeof window["<?=$arParams["ON_JS_CLICK_SUBMIT_BUTTON"]?>"] == "function")
								window["<?=$arParams["ON_JS_CLICK_SUBMIT_BUTTON"]?>"](form);
						<?else:?>
							<?if(isset($arResult["ON_BEFORE_FORM_SUBMIT"])):?>
								app.onCustomEvent("<?=$arResult["ON_BEFORE_FORM_SUBMIT"]?>");
								BX.onCustomEvent("<?=$arResult["ON_BEFORE_FORM_SUBMIT"]?>");
							<?endif;?>
							form.submit();
						<?endif;?>
					}
				}
			}
		});
	<?endif;?>
	</script>
<?endif;?>