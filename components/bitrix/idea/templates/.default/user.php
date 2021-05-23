<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

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
			"bitrix:blog.user",
			"",
			Array(
					"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
					"USER_VAR"			=> $arResult["ALIASES"]["user_id"],
					"PAGE_VAR"			=> $arResult["ALIASES"]["page"],
					"PATH_TO_BLOG"		=> $arResult["PATH_TO_BLOG"],
					"PATH_TO_USER"		=> $arResult["PATH_TO_USER"],
					"PATH_TO_USER_IDEAS"	=> $arResult["PATH_TO_USER_IDEAS"],
					"PATH_TO_USER_EDIT"	=> $arResult["PATH_TO_USER_EDIT"],
					"PATH_TO_SEARCH"	=> $arResult["PATH_TO_SEARCH"],
					"ID"				=> $arResult["VARIABLES"]["user_id"],
					"SET_TITLE"			=> $arResult["SET_TITLE"],
					"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"],
					//"USER_PROPERTY" =>  $arParams["USER_PROPERTY"],
					"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
					"GROUP_ID" 			=> $arParams["GROUP_ID"],
					"NAME_TEMPLATE"	 => $arParams["NAME_TEMPLATE"]
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