<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => "/bitrix/js/ui/entity-editor/entity-editor.css",
	"js" => [
		"/bitrix/js/ui/entity-editor/js/config.js",
		"/bitrix/js/ui/entity-editor/js/config-enum.js",
		"/bitrix/js/ui/entity-editor/js/control.js",
		"/bitrix/js/ui/entity-editor/js/dialog.js",
		"/bitrix/js/ui/entity-editor/js/editor.js",
		"/bitrix/js/ui/entity-editor/js/editor-enum.js",
		"/bitrix/js/ui/entity-editor/js/editor-controller.js",
		"/bitrix/js/ui/entity-editor/js/drag-drop.js",
		"/bitrix/js/ui/entity-editor/js/factory.js",
		"/bitrix/js/ui/entity-editor/js/field-selector.js",
		"/bitrix/js/ui/entity-editor/js/form.js",
		"/bitrix/js/ui/entity-editor/js/helper.js",
		"/bitrix/js/ui/entity-editor/js/model.js",
		"/bitrix/js/ui/entity-editor/js/scheme.js",
		"/bitrix/js/ui/entity-editor/js/selector.js",
		"/bitrix/js/ui/entity-editor/js/tool-panel.js",
		"/bitrix/js/ui/entity-editor/js/field-configurator.js",
		"/bitrix/js/ui/entity-editor/js/field-icon.js",
		"/bitrix/js/ui/entity-editor/js/user-field.js",
		"/bitrix/js/ui/entity-editor/js/validator.js",
		"/bitrix/js/ui/entity-editor/js/pull.js",
	],
	"rel" => [
		"ajax",
		"dnd",
		"date",
		"uf",
		"uploader",
		"tooltip",
		"helper",
		"core_money_editor",
		"ui",
		"ui.analytics",
		"ui.hint",
		"ui.notification",
		"ui.dropdown",
		"ui.buttons",
		"ui.forms",
		"ui.draganddrop.draggable",
		"ui.entity-selector",
		"ui.design-tokens",
		"ui.fonts.opensans",
	],
];
