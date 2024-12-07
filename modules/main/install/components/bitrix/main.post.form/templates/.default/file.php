<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @deprecated
 * @var array $arParams
 * @var array $arResult
 */
$handlers = array();
$arParams["UPLOADS"] = array();
if (isset($arParams["UPLOAD_FILE"]) && is_array($arParams["UPLOAD_FILE"]) && !empty($arParams["UPLOAD_FILE"]))
{
	if (array_key_exists("USER_TYPE_ID", $arParams["UPLOAD_FILE"]))
	{
		$arParams["UPLOAD_FILE"]["VALUE"] = array_merge(
			isset($arParams["UPLOAD_FILE"]["INPUT_VALUE"]) && is_array($arParams["UPLOAD_FILE"]["INPUT_VALUE"]) ? $arParams["UPLOAD_FILE"]["INPUT_VALUE"] : [],
			isset($arParams["UPLOAD_FILE"]["VALUE"]) && is_array($arParams["UPLOAD_FILE"]["VALUE"]) ? $arParams["UPLOAD_FILE"]["VALUE"] : []
		);

		if (array_key_exists("FILES", $arParams) && is_array($arParams["FILES"]) && array_key_exists("POSTFIX", $arParams["FILES"]))
			$arParams["UPLOAD_FILE"]["POSTFIX"] = $arParams["FILES"]["POSTFIX"];
		$arParams["PROPERTIES"][] = $arParams["UPLOAD_FILE"];
	}
	else if (array_key_exists("INPUT_NAME", $arParams["UPLOAD_FILE"]))
	{
		if (isset($arParams["UPLOAD_FILE"]["TAG"]))
			$arParams["PARSER"][] = ["file" => $arParams["UPLOAD_FILE"]["TAG"]];
		$arParams["UPLOADS"][] = $arParams["UPLOAD_FILE"];
	}
	unset($arParams["UPLOAD_FILE"]);
}
if (
	isset($arParams["UPLOAD_WEBDAV_ELEMENT"])
	&& is_array($arParams["UPLOAD_WEBDAV_ELEMENT"])
	&& !empty($arParams["UPLOAD_WEBDAV_ELEMENT"])
)
{
	$arParams["PROPERTIES"][] = $arParams["UPLOAD_WEBDAV_ELEMENT"];
	unset($arParams["UPLOAD_WEBDAV_ELEMENT"]);
}

if (isset($arParams["PROPERTIES"]) && is_array($arParams["PROPERTIES"]))
{
	$newParsers = [];
	foreach ($arParams["PROPERTIES"] as $val)
	{
		if (isset($val["USER_TYPE_ID"]) && in_array($val["USER_TYPE_ID"], array("disk_file", "webdav_element", "file")))
		{
			if (array_key_exists("TAG", $val["USER_TYPE"]) )
			{
				$newParsers[$val["USER_TYPE_ID"]] = [$val["USER_TYPE_ID"] => $val["USER_TYPE"]["TAG"]];
			}
			$arParams["UPLOADS"][] = $val;
		}
	}
	$arParams["PARSER"] += array_values($newParsers);
}
if (empty($arParams["UPLOADS"]))
	return;

$bNull = null;
__main_post_form_image_resize($bNull, $arParams["UPLOAD_FILE_PARAMS"] ?? null);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST['mfi_mode']) && ($_REQUEST['mfi_mode'] == "upload"))
{
	$handlers["main.file.input.upload"] = AddEventHandler('main',  "main.file.input.upload", '__main_post_form_image_resize');
}
ob_start();
foreach ($arParams["UPLOADS"] as $v)
{
	if (isset($v["USER_TYPE_ID"]) && in_array($v["USER_TYPE_ID"], array("file", "webdav_element", "disk_file")))
	{
		$additionalParameters = [
			'arUserField' => $v,
			'DISABLE_CREATING_FILE_BY_CLOUD' => $arParams['DISABLE_CREATING_FILE_BY_CLOUD'] ?? ($v['DISABLE_CREATING_FILE_BY_CLOUD'] ?? null),
			'DISABLE_LOCAL_EDIT' => $arParams['DISABLE_LOCAL_EDIT'] ?? $v['DISABLE_LOCAL_EDIT'] ?? '',
			'HIDE_CHECKBOX_ALLOW_EDIT' => $arParams['HIDE_CHECKBOX_ALLOW_EDIT'] ?? $v['HIDE_CHECKBOX_ALLOW_EDIT'] ?? '',
			'HIDE_CHECKBOX_PHOTO_TEMPLATE' => $arParams['HIDE_CHECKBOX_PHOTO_TEMPLATE'] ?? $v['HIDE_CHECKBOX_PHOTO_TEMPLATE'] ?? '',
			'MAIN_POST_FORM' => true,
			'MAIN_POST_FORM_ID' => $arParams['FORM_ID'] ?? '',
		];
		if ($v['USER_TYPE_ID'] === 'disk_file'
			&& isset($v['USER_TYPE'])
			&& isset($v['USER_TYPE']['TAG'])
			&& isset($v['USER_TYPE']['REGEXP'])
		)
		{
			$additionalParameters['PARSER_PARAMS'] = [
				'TAG' => $v['USER_TYPE']['TAG'],
				'REGEXP' => $v['USER_TYPE']['REGEXP'],
			];
			$handlerId = AddEventHandler("main", $val["USER_TYPE_ID"], "__main_post_form_replace_template");
		}
		elseif ($v['USER_TYPE_ID'] === 'file')
		{
			$additionalParameters['mode'] = 'main.drag_n_drop';
			$handlerId = AddEventHandler("main", $val["USER_TYPE_ID"], "__main_post_form_replace_template");
		}

		if ($val["USER_TYPE_ID"] == "file")
			$handlerId = AddEventHandler('main', 'main.file.input', "__main_post_form_replace_template");
		else
			$handlerId = AddEventHandler("main", $val["USER_TYPE_ID"], "__main_post_form_replace_template");

		$res = $APPLICATION->IncludeComponent(
			'bitrix:system.field.edit',
			$v['USER_TYPE_ID'],
			$additionalParameters,
			null,
			array("HIDE_ICONS" => "Y"),
			true
		);
		RemoveEventHandler("main",  $val["USER_TYPE_ID"], $handlerId);

		$cid = __main_post_form_replace_template();

		$arParams["UPLOADS_CID"][$cid] = array(
			"parser" => $v['USER_TYPE']['TAG'] ? $v["USER_TYPE_ID"] : null,
			"tag" => $v['USER_TYPE']['TAG'] ? $v["USER_TYPE_ID"] : null,
			"value" => ($v["USER_TYPE_ID"] == "file" ? $v["VALUE"] : array()),
			"postfix" => $v["POSTFIX"] ?? ''
		);
		$arParams['BUTTONS'][] = 'UploadFile';
	}
	else if (!empty($v["INPUT_NAME"]))
	{
		$cid =  $GLOBALS["APPLICATION"]->IncludeComponent(
			'bitrix:main.file.input',
			'drag_n_drop',
			array(
				'CONTROL_ID' => $v["CONTROL_ID"],
				'INPUT_NAME' => $v["INPUT_NAME"],
				'INPUT_NAME_UNSAVED' => 'FILE_NEW_TMP',
				'INPUT_VALUE' => $v["INPUT_VALUE"],
				'MAX_FILE_SIZE' => $v["MAX_FILE_SIZE"],
				'MULTIPLE' => $v["MULTIPLE"],
				'MODULE_ID' => $v["MODULE_ID"],
				'ALLOW_UPLOAD' => $v["ALLOW_UPLOAD"],
				'ALLOW_UPLOAD_EXT' => $v["ALLOW_UPLOAD_EXT"],
				'INPUT_CAPTION' => $v["INPUT_CAPTION"] ?? '',
			),
			null,
			array("HIDE_ICONS" => true)
		);
		$arParams['BUTTONS'][] = 'UploadFile';
		$arParams["UPLOADS_CID"][$cid] = array(
			"storage" => "bfile",
			"parser" => 'file',
			"postfix" => $v["POSTFIX"] ?? '',
		);
	}
}
$arParams["UPLOADS_HTML"] = ob_get_clean();

foreach($handlers as $eventName => $handlerID)
	if ($handlerID)
		RemoveEventHandler("main", $eventName, $handlerID);
?>
