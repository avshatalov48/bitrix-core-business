<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if($arParams["SET_NAV_CHAIN"]=="Y")
	$APPLICATION->AddChainItem(GetMessage("IDEA_SUBSCRIBE_MINE_TITLE"), $arResult["~PATH_TO_USER_SUBSCRIBE"]);
if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("IDEA_SUBSCRIBE_MINE_TITLE"));
?>
<div class="idea-managment-content">
	<?if(!empty($arResult["ACTIONS"])):?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS" => $arResult["ACTIONS"]
		),
		$component
	);?>
	<?endif;?>
	<?//Side bar tools?>
	<?$this->SetViewTarget("sidebar", 100)?>
		<?$APPLICATION->IncludeComponent(
				"bitrix:idea.category.list",
				"",
				Array(
					"IBLOCK_CATEGORIES" => $arParams["IBLOCK_CATEGORIES"],
					"PATH_TO_CATEGORY_1" => $arResult["PATH_TO_CATEGORY_1"],
					"PATH_TO_CATEGORY_2" => $arResult["PATH_TO_CATEGORY_2"],
				),
				$component
		);
		?>
		<?$APPLICATION->IncludeComponent(
				"bitrix:idea.statistic",
				"",
				Array(
					"BLOG_URL" => $arResult["VARIABLES"]["blog"],
					"PATH_WITH_STATUS" => $arResult["PATH_TO_STATUS_0"],
					"PATH_TO_INDEX" => $arResult["PATH_TO_INDEX"],
				),
				$component
		);
		?>
		<?$APPLICATION->IncludeComponent(
				"bitrix:idea.tags",
				"",
				Array(
					"BLOG_URL" => $arParams["BLOG_URL"],
					"PATH_TO_BLOG_CATEGORY" => $arResult["PATH_TO_BLOG_CATEGORY"],
					"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"],
					"TAGS_COUNT" => $arParams["TAGS_COUNT"]
				),
				$component
		);
		?>
	<?$this->EndViewTarget();?>
	<?$this->SetViewTarget("idea_body", 100)?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:idea.subscribe",
			"",
			array(
				"PATH_TO_USER_IDEAS" => $arResult["PATH_TO_USER_IDEAS"],
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
			),
			$component
		);?>
	<?$this->EndViewTarget();?>
	<?if($arResult["IS_CORPORTAL"] != "Y"):?>
		<div class="idea-managment-content-left">
			<?$APPLICATION->ShowViewContent("sidebar")?>
		</div>
	<?endif;?>
	<div class="idea-managment-content-right">
		<?$APPLICATION->ShowViewContent("idea_filter")?>
		<?$APPLICATION->ShowViewContent("idea_body")?>
	</div>
	<div style="clear:both;"></div>
</div>