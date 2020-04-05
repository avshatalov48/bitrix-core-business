<?php

use Bitrix\MobileApp\Designer\ParameterType;

$map = [
	"types" => [
		//Group parameters
		'controller_settings/main_background' => ParameterType::GROUP_BACKGROUND,
		'controller_settings/toolbar_background' => ParameterType::GROUP_BACKGROUND_LIGHT,
		'controller_settings/loading_background' => ParameterType::GROUP_BACKGROUND,
		'controller_settings/navigation_bar_background' => ParameterType::GROUP_BACKGROUND_LIGHT,
		'table/cell_background' => ParameterType::GROUP_BACKGROUND_LIGHT,
		'sliding_panel/background' => ParameterType::GROUP_BACKGROUND_LIGHT,
		'buttons/badge' => ParameterType::GROUP,
		'buttons/stretchable' => ParameterType::GROUP,
		'additional/push' => ParameterType::GROUP,
		'offline/files' => ParameterType::GROUP,


		//status bar
		'statusBar/use_top_offset' =>[
			"type"=>ParameterType::BOOLEAN,
			"default"=>"NO"
		],
		'statusBar/show_status_bar_without_nav_bar' => [
			"type"=>ParameterType::BOOLEAN,
			"default"=>"NO"
		],
		'statusBar/color' => ParameterType::COLOR,
		'statusBar/opacity' => [
			"type" => ParameterType::SIZE,
			"limits" => [
				"min" => 0.1,
				"max" => 1
			]
		],

		//main
		'controller_settings/main_background/color' => ParameterType::COLOR,
		'controller_settings/main_background/image' => ParameterType::IMAGE,
		'controller_settings/main_background/image_landscape' => ParameterType::IMAGE,
		'controller_settings/main_background/fill_mode' => [
			"type" => ParameterType::VALUE_LIST,
			"list" => ["repeat", "crop", "stretch"]
		],

		'controller_settings/toolbar_background/color' => ParameterType::COLOR,
		'controller_settings/toolbar_background/image' => ParameterType::IMAGE,

		'controller_settings/loading_background/color' => ParameterType::COLOR,
		'controller_settings/loading_background/image' => ParameterType::IMAGE,
		'controller_settings/loading_background/image_landscape' => ParameterType::IMAGE,
		'controller_settings/loading_background/fill_mode' => [
			"type" => ParameterType::VALUE_LIST,
			"list" => ["repeat", "crop", "stretch"]
		],


		'controller_settings/navigation_bar_background/color' => ParameterType::COLOR,
		'controller_settings/navigation_bar_background/image' => ParameterType::IMAGE,
		'controller_settings/navigation_bar_background/image_large' => ParameterType::IMAGE,


		'controller_settings/loading_text_color' => ParameterType::COLOR,//iOS only
		'controller_settings/progressbar_color' => ParameterType::COLOR,//Android only
		'controller_settings/title_color' => ParameterType::COLOR,

		//buttons
		'buttons/ios_use_square_buttons' => ParameterType::BOOLEAN,
		'buttons/default_back_button' => [
			"type" => ParameterType::VALUE_LIST,
			"list" => ["default", "back_text", "back"],
		],
		'buttons/text_color' => ParameterType::COLOR,
		'buttons/main_background_image' => ParameterType::IMAGE,
		'buttons/type' => ParameterType::IMAGE_SET,
		'buttons/badge/background_color' => ParameterType::COLOR,
		'buttons/badge/text_color' => ParameterType::COLOR,
		'buttons/badge/show_frame' => ParameterType::BOOLEAN,
		'buttons/badge/border_color' => ParameterType::COLOR,
		'table/sections_text_color' => ParameterType::COLOR,
		'table/sections_text_shadow_color' => ParameterType::COLOR,
		'table/sections_background_color' => ParameterType::COLOR,
		'table/cell_text_shadow_color' => ParameterType::COLOR,
		'table/cell_text_color' => ParameterType::COLOR,
		'table/cell_detail_text_color' => ParameterType::COLOR,

		'table/row_height' => [
			"type" => ParameterType::SIZE,
			"limits" => [
				"min" => 50
			]
		],
		'table/row_height_large' => [
			"type" => ParameterType::SIZE,
			"limits" => [
				"min" => 50
			]
		],
		'table/cell_background/color' => ParameterType::COLOR,
		'table/cell_background/image' => ParameterType::IMAGE,
		//pull to refresh controller

		'pull_down/background' => ParameterType::GROUP_BACKGROUND,
		'pull_down/background/color' => ParameterType::COLOR,
		'pull_down/background/image' => ParameterType::IMAGE,
		'pull_down/date_text_color' => ParameterType::COLOR,
		'pull_down/text_color' => ParameterType::COLOR,
		'pull_down/icon' => ParameterType::IMAGE,
		'pull_down/text_style' => [
			"type" => ParameterType::VALUE_LIST,
			"list" => ["normal", "bold"]
		],
		'pull_down/arrow_color' => ParameterType::COLOR,//android only

		//sliding panel
		'sliding_panel/text_color' => ParameterType::COLOR,
		'sliding_panel/background/color' => ParameterType::COLOR,
		'sliding_panel/background/image' => ParameterType::IMAGE,
		'sliding_panel/background/image_large' => ParameterType::IMAGE,

		//category switcher in a list controller
		'category_switcher/button_text_color_selected' => ParameterType::COLOR,
		'category_switcher/button_text_color' => ParameterType::COLOR,
		'category_switcher/button_background_color_selected' => ParameterType::COLOR,
		//additional
		'additional/use_top_bar' =>
		[
			"type"=>ParameterType::BOOLEAN,
			"default"=>"YES"
		],
		'additional/use_slider' => ParameterType::BOOLEAN,
		'additional/push/use_push' => [
			"type"=>ParameterType::BOOLEAN,
			"default"=>"NO"
		],
		'additional/push/app_push_id' => [
			"type" => ParameterType::STRING,
			"enabledIf" => [
				"additional/push/use_push" => "YES"
			]],
		//offline
		'offline/launch_mode' => [
			"type" => ParameterType::VALUE_LIST,
			"list" => ["offline_only", "online_only", "mixed"],
		],
		'offline/file_list' => ParameterType::VALUE_SET,
		'offline/main' => [
			"type" => ParameterType::VALUE_LIST,
			"list" => "offline/file_list"
		],
		'offline/left' => [
			"type" => ParameterType::VALUE_LIST,
			"list" => "offline/file_list"
		],
		'offline/right' => [
			"type" => ParameterType::VALUE_LIST,
			"list" => "offline/file_list"
		],
	],

	"defaults" => [],
];

return $map;