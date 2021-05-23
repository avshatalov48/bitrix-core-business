<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
	<?//Work Field?>
	<?$this->SetViewTarget("idea_body", 100)?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:idea.edit",
			".default",
			Array(
				"POST_BIND_STATUS_DEFAULT" 			=> $arParams["POST_BIND_STATUS_DEFAULT"],
				"POST_BIND_USER"				=> $arParams["POST_BIND_USER"],
				"BLOG_VAR"					=> $arResult["ALIASES"]["blog"],
				"POST_VAR"					=> $arResult["ALIASES"]["post_id"],
				"USER_VAR"					=> $arResult["ALIASES"]["user_id"],
				"PAGE_VAR"					=> $arResult["ALIASES"]["page"],
				"PATH_TO_BLOG"				=> $arResult["PATH_TO_BLOG"],
				"PATH_TO_POST"				=> $arResult["PATH_TO_POST"],
				"PATH_TO_USER"				=> $arResult["PATH_TO_USER"],
				"PATH_TO_POST_EDIT"			=> $arResult["PATH_TO_POST_EDIT"],
				"PATH_TO_DRAFT"				=> $arResult["PATH_TO_DRAFT"],
				"PATH_TO_SMILE"				=> $arResult["PATH_TO_SMILE"],
				"BLOG_URL"					=> $arResult["VARIABLES"]["blog"],
				"ID"						=> $arResult["VARIABLES"]["post_id"],
				"SET_TITLE"					=> $arResult["SET_TITLE"],
				"SET_NAV_CHAIN"					=> $arParams["SET_NAV_CHAIN"],
				"POST_PROPERTY"				=> $arParams["POST_PROPERTY"],
				"GROUP_ID" 					=> $arParams["GROUP_ID"],
				"SMILES_COLS" 				=> $arParams["SMILES_COLS"],
				"SMILES_COUNT" 				=> $arParams["SMILES_COUNT"],
				"SHOW_LOGIN" 				=> $arParams["SHOW_LOGIN"],
				"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
				"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
				"EDITOR_RESIZABLE" => $arParams["EDITOR_RESIZABLE"],
				"EDITOR_DEFAULT_HEIGHT" => $arParams["EDITOR_DEFAULT_HEIGHT"],
				"EDITOR_CODE_DEFAULT" => $arParams["EDITOR_CODE_DEFAULT"],
				"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
				"USE_GOOGLE_CODE" => $arParams["USE_GOOGLE_CODE"],
				"POST_BIND_USER" => $arParams["POST_BIND_USER"],
				"SHOW_RATING" => $arParams["SHOW_RATING"],
			),
			$component
		);
		?>
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