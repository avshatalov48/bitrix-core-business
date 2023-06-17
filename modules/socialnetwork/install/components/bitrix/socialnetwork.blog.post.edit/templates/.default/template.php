<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Bitrix24\Feature;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\FrameStatic;
use Bitrix\Main\UI;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Integration\Calendar\ApiVersion;
use Bitrix\Main\Web\Json;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$arParams["FORM_ID"] = "blogPostForm";
$jsObjName = "oPostFormLHE_".$arParams["FORM_ID"];
$id = "idPostFormLHE_".$arParams["FORM_ID"];

$extensionsList = [
	'ui.design-tokens',
	'main.popup',
	'sidepanel',
	'videorecorder',
	'ui.entity-selector',
	'ui.common',
	'ui.forms',
	'ui.buttons',
	'ui.alerts',
	'ui_date',
	'ui.notification',
	'ui.info-helper',
];

if (in_array('tasks', $arResult['tabs'], true))
{
	Loader::includeModule('tasks');
	$extensionsList[] = 'tasks_component';
	$extensionsList[] = 'tasks_integration_socialnetwork';
}

if (in_array('lists', $arResult['tabs'], true))
{
	$extensionsList[] = 'lists';
}

UI\Extension::load($extensionsList);

$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css');
$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css');

if (
	isset($arResult["delete_blog_post"])
	&& $arResult["delete_blog_post"] === "Y"
)
{
	$APPLICATION->RestartBuffer();
	if (!empty($arResult["ERROR_MESSAGE"]))
	{
		?>
		<div class="feed-add-error">
			<span class="feed-add-info-icon"></span><span class="feed-add-info-text"><?=$arResult["ERROR_MESSAGE"]?></span>
		</div>
		<?php
	}

	if (!empty($arResult["OK_MESSAGE"]))
	{
		?><div class="feed-add-successfully">
			<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["OK_MESSAGE"]?></span>
		</div><?php
	}
	die();
}

if (!empty($arResult["FATAL_MESSAGE"]))
{
	ob_start();

	?><div class="feed-add-error">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["FATAL_MESSAGE"]?></span>
	</div><?php

	$strFullForm = ob_get_clean();

	if (isset($_POST["action"]) && $_POST["action"] === "SBPE_get_full_form")
	{
		while (ob_end_clean()) {}

		echo CUtil::PhpToJSObject([
			"PROPS" => [
				"CONTENT" => $strFullForm,
				"STRINGS" => [],
				"JS" => [],
				"CSS" => []
			],
			"success" => true
		]);
		die();
	}

	echo $strFullForm;

	return false;
}

?><div class="feed-wrap">
	<div id="feed-add-post-block<?=$arParams["FORM_ID"]?>" class="feed-add-post-block blog-post-edit"><?php
if (!empty($arResult["OK_MESSAGE"]) || !empty($arResult["ERROR_MESSAGE"]))
{
	?><div id="feed-add-post-form-notice-block<?= $arParams["FORM_ID"] ?>" class="feed-notice-block feed-post-form-block-hidden"><?php
		if (!empty($arResult["OK_MESSAGE"]))
		{
			?><div class="feed-add-successfully">
				<span class="feed-add-info-icon"></span><span class="feed-add-info-text"><?= $arResult["OK_MESSAGE"] ?></span>
			</div><?php
		}
		if (!empty($arResult["ERROR_MESSAGE"]))
		{
			?><div class="feed-add-error">
				<span class="feed-add-info-icon"></span><span class="feed-add-info-text"><?= $arResult["ERROR_MESSAGE"] ?></span>
			</div><?php
		}
	?></div><?php
}
if (!empty($arResult["UTIL_MESSAGE"]))
{
	?>
	<div class="feed-add-successfully">
		<span class="feed-add-info-icon"></span><span class="feed-add-info-text"><?=$arResult["UTIL_MESSAGE"]?></span>
	</div>
	<?php
}
elseif (
	isset($arResult["imageUploadFrame"])
	&& $arResult["imageUploadFrame"] === "Y"
) // Frame with file input to ajax uploading in WYSIWYG editor dialog
{
	?><script><?php
	if (!empty($arResult["Image"]))
	{
		?>
		var imgTable = top.BX('blog-post-image');
		if (imgTable)
		{
			imgTable.innerHTML += '<span class="feed-add-photo-block"><span class="feed-add-img-wrap"><?=$arResult["ImageModified"]?></span><span class="feed-add-img-title"><?=$arResult["Image"]["fileName"]?></span><span class="feed-add-post-del-but" onclick="DeleteImage(\'<?=$arResult["Image"]["ID"]?>\', this)"></span><input type="hidden" id="blgimg-<?=$arResult["Image"]["ID"]?>" value="<?=$arResult["Image"]["source"]["src"]?>"></span>';
			imgTable.parentNode.parentNode.style.display = 'block';
		}

		top.bxPostFileId = '<?= $arResult["Image"]["ID"] ?>';
		top.bxPostFileIdSrc = '<?= CUtil::JSEscape($arResult["Image"]["source"]["src"]) ?>';
		top.bxPostFileIdWidth = '<?= CUtil::JSEscape($arResult["Image"]["source"]["width"]) ?>';
		<?php
	}
	elseif ((string)$arResult["ERROR_MESSAGE"] !== '')
	{
		?>
		window.bxPostFileError = top.bxPostFileError = '<?=CUtil::JSEscape($arResult["ERROR_MESSAGE"])?>';
		<?php
	}
	?></script><?php
	die();
}
else
{
	$userOption = CUserOptions::GetOption("socialnetwork", "postEdit");
	$bShowTitle = (
		(
			($arResult["PostToShow"]["MICRO"] ?? '') !== "Y"
			&& !empty($arResult["PostToShow"]["TITLE"])
		)
		|| (
			isset($userOption["showTitle"])
			&& $userOption["showTitle"] === "Y"
			&& ($arResult["PostToShow"]["MICRO"] ?? '') !== "Y"
		)
	);

	ob_start();

	$arTabs = [
		[
			"ID" => "message",
			"NAME" => Loc::getMessage("BLOG_TAB_POST")
		]
	];

	if (in_array('tasks', $arResult['tabs'], true))
	{
		$arTabs[] = [
			"ID" => "tasks",
			"NAME" => Loc::getMessage("BLOG_TAB_TASK"),
			"ONCLICK" => "BX.Socialnetwork.Livefeed.PostFormTabs.getInstance().getTaskForm();"
		];
	}

	if (in_array('calendar', $arResult['tabs'], true))
	{
		$arTabs[] = [
			"ID" => "calendar",
			"NAME" => Loc::getMessage("SBPE_CALENDAR_EVENT"),
			(ApiVersion::isEventEditFormAvailable() ? "ONCLICK_SLIDER" : "ONCLICK") => ApiVersion::getAddEventInLivefeedJs()
		];
	}

	if (in_array('vote', $arResult['tabs'], true))
	{
		$limited = (
			Loader::includeModule('bitrix24')
			&& !Feature::isFeatureEnabled('socialnetwork_livefeed_vote')
		);

		$arTabs[] = [
			"ID" => "vote",
			"NAME" => Loc::getMessage("BLOG_TAB_VOTE"),
			"ICON" => "feed-add-post-form-polls-link-icon",
			"ONCLICK" => (
			$limited
				? "BX.UI.InfoHelper.show('limit_crm_interview');"
				: ""
			),
			"LIMITED" => $limited ? 'Y' : 'N',
		];
	}

	if (in_array('file', $arResult['tabs'], true))
	{
		$arTabs[] = [
			"ID" => "file",
			"NAME" => Loc::getMessage("BLOG_TAB_FILE")
		];
	}

	if (in_array('grat', $arResult['tabs'], true))
	{
		$arTabs[] = [
			"ID" => "grat",
			"NAME" => Loc::getMessage("BLOG_TAB_GRAT")
		];
	}

	$limited = (
		Loader::includeModule('bitrix24')
		&& !Feature::isFeatureEnabled('socialnetwork_livefeed_important')
	);

	$arTabs[] = [
		"ID" => "important",
		"NAME" => Loc::getMessage("SBPE_IMPORTANT_MESSAGE"),
		"ONCLICK" => (
			$limited
				? "BX.UI.InfoHelper.show('limit_crm_important_message');"
				: ""
		),
		"LIMITED" => $limited ? 'Y' : 'N',
	];

	if (in_array('lists', $arResult['tabs'], true))
	{
		$arTabs[] = [
			"ID" => "lists",
			"NAME" => Loc::getMessage("BLOG_TAB_LISTS"),
			"ONCLICK" => (
				!CLists::isFeatureEnabled()
					? "BX.UI.InfoHelper.show('limit_office_bp_stream');"
					: "BX.Socialnetwork.Livefeed.PostFormTabs.getInstance().getLists();"
			)
		];
	}

	$maxTabs = 4;

	$tabsCnt = count($arTabs);
	for ($i = 0; $i < $maxTabs; $i++)
	{
		$arTab = $arTabs[$i];
		$moreClass = ($arResult['tabActive'] === $arTab["ID"] ? " feed-add-post-form-link-active" : "");
		if ($arTab["ID"] === "lists")
		{
			?><span class="feed-add-post-form-link<?=$moreClass?>" id="feed-add-post-form-tab-<?=$arTab["ID"]?>"><?php
				?><span id="feed-add-post-form-tab-lists" class="feed-add-post-form-link-text"><?=$arTab["NAME"]?></span><?php
				?><span class="feed-add-post-more-icon-lists"></span><?php
			?></span><?php
			?><script>
				BX.bind(BX('feed-add-post-form-tab-<?=$arTab["ID"]?>'), 'click', function() {
					BX.Socialnetwork.Livefeed.PostForm.getInstance().get({
						callback: function() {
							BX.Socialnetwork.Livefeed.PostFormTabs.getInstance().getLists();
						}
					});
				});
			</script><?php
		}
		else
		{
			?><span class="feed-add-post-form-link<?=$moreClass?>" id="feed-add-post-form-tab-<?=$arTab["ID"]?>"><?php
				?><span><?=$arTab["NAME"]?></span><?php
			?></span><?php
			?><script>
				BX.bind(BX('feed-add-post-form-tab-<?=$arTab["ID"]?>'), 'click', function() {

					var noticeNode = document.getElementById('feed-add-post-form-notice-blockblogPostForm');
					if (noticeNode)
					{
						BX.Dom.clean(noticeNode);
					}

					<?php
					if (isset($arTab["ONCLICK_SLIDER"]))
					{
						?><?= $arTab["ONCLICK_SLIDER"] ?><?php
					}
					elseif (
						isset($arTab["LIMITED"])
						&& $arTab["LIMITED"] === 'Y'
					)
					{
						?><?= ($arTab["ONCLICK"] ?? '') ?><?php
					}
					else
					{
						?>
						BX.Socialnetwork.Livefeed.PostForm.getInstance().get({
							callback: function() {
								setTimeout(function() {
									BX.Socialnetwork.Livefeed.PostFormTabs.getInstance().changePostFormTab('<?= $arTab["ID"] ?>');
									<?= ($arTab["ONCLICK"] ?? '') ?>
								}, 10);
							}
						});
						<?php
					}
					?>
				});
			</script><?php
		}
	}

	if ($tabsCnt > $maxTabs)
	{
		$moreCaption = Loc::getMessage('SBPE_MORE');
		$moreClass = "";
		$pseudoTabs = "";

		for ($i = $maxTabs; $i < $tabsCnt; $i++)
		{
			$arTab = $arTabs[$i];
			$limited = $arTab["LIMITED"] ?? '';
			$pseudoTabs .= '<span class="feed-add-post-form-link" data-onclick="'.($arTab["ONCLICK"] ?? '').'" data-name="'.$arTab["NAME"].'" data-limited="'.$limited.'" id="feed-add-post-form-tab-'.$arTab["ID"].'" style="display:none;"></span>';
			if (
				$arResult['tabActive'] === $arTab["ID"]
				&& $maxTabs > 0
			)
			{
				$moreCaption = $arTab["NAME"];
				$moreClass = " feed-add-post-form-".$arTab["ID"]."-link";
			}
		}

		?><span id="feed-add-post-form-link-more" class="feed-add-post-form-link feed-add-post-form-link-more<?= $moreClass ?>"><?php
			?><span id="feed-add-post-form-link-text" class="feed-add-post-form-link-text"><?= $moreCaption ?></span><?php
			?><span id="feed-add-post-more-icon" class="feed-add-post-more-icon"></span><?php
			?><span id="feed-add-post-more-icon-waiter" class="feed-add-post-more-icon-waiter"><?php
				?><svg class="feed-add-post-loader" viewBox="25 25 50 50"><circle class="feed-add-post-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle><circle class="feed-add-post-loader-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle></svg><?php
			?></span><?php
			?><?= $pseudoTabs ?><?php
		?></span><?php
		?><script>
			BX.bind(BX('feed-add-post-form-link-more'), 'click', function() {
				BX.Socialnetwork.Livefeed.PostForm.getInstance().get({
					callback: function() {
						BX.Socialnetwork.Livefeed.PostFormTabs.getInstance().showMoreMenu();
					},
					loaderType: 'tab'
				});
			});
		</script><?php
	}

	$strGratVote = ob_get_clean();

	if (
		($arParams["TOP_TABS_VISIBLE"] ?? null) === "Y"
		&& (
			!isset($arParams["PAGE_ID"])
			|| !in_array($arParams["PAGE_ID"], [ "user_blog_post_edit_profile", "user_blog_post_edit_grat", "user_grat", "user_blog_post_edit_post" ])
		)
	)
	{
		?><div class="microblog-top-tabs-visible"><?php
			?><div class="feed-add-post-form-variants" id="feed-add-post-form-tab"><?php
				echo $strGratVote;
				if ($arParams["SHOW_BLOG_FORM_TARGET"])
				{
					$APPLICATION->ShowViewContent("sonet_blog_form");
				}
				?><div id="feed-add-post-form-tab-arrow" class="feed-add-post-form-arrow" style="left: 31px;"></div><?php
			?></div><?php
		?></div><?php
	}
	$htmlAfterTextarea = "";
	if (!empty($arResult["Images"]))
	{
		$arFile = reset($arResult["Images"]);
		$arJSFiles = [];
		while ($arFile)
		{
			$arJSFiles[(string)$arFile["ID"]] = [
				"element_id" => $arFile["ID"],
				"element_name" => $arFile["FILE_NAME"],
				"element_size" => $arFile["FILE_SIZE"],
				"element_url" => $arFile["URL"],
				"element_content_type" => $arFile["CONTENT_TYPE"],
				"element_thumbnail" => $arFile["SRC"],
				"element_image" => $arFile["THUMBNAIL"],
				"isImage" => (mb_strpos($arFile["CONTENT_TYPE"], 'image/') === 0),
				"del_url" => $arFile["DEL_URL"]
			];
			$title = Loc::getMessage("MPF_INSERT_FILE");
			$arFile["DEL_URL"] = CUtil::JSEscape($arFile["DEL_URL"]);
$htmlAfterTextarea .= <<<HTML
<span class="feed-add-photo-block" id="wd-doc{$arFile["ID"]}">
	<span class="feed-add-img-wrap" title="{$title}">
		<img src="{$arFile["THUMBNAIL"]}" border="0" width="90" height="90" />
	</span>
	<span class="feed-add-img-title" title="{$title}">{$arFile["NAME"]}</span>
	<span class="feed-add-post-del-but"></span>
</span>
HTML;
			$arFile = next($arResult["Images"]);
		}
		if ($htmlAfterTextarea !== "")
		{
			$arJSFiles = CUtil::PhpToJSObject($arJSFiles);
$htmlAfterTextarea .= <<<HTML
<script>window['{$id}Files']={$arJSFiles};</script>
HTML;
		}
	}

	?><div class="feed-add-post-micro" id="micro<?=$jsObjName?>" onclick="

		BX.Socialnetwork.Livefeed.PostForm.getInstance().get({
			callback: function() {
				BX.onCustomEvent(BX('div<?=$jsObjName?>'), 'OnControlClick');
				if (BX('div<?=$jsObjName?>').style.display == 'none')
				{
					BX.onCustomEvent(BX('div<?=$jsObjName?>'), 'OnShowLHE', ['show']);
				}
			}
		});

		"><div id="micro<?=$jsObjName?>_inner"><?php
			?><span class="feed-add-post-micro-title"><?= Loc::getMessage('BLOG_LINK_SHOW_NEW') ?></span><?php
			?><span class="feed-add-post-micro-dnd"><?= Loc::getMessage('MPF_DRAG_ATTACHMENTS2') ?></span><?php
		?></div><?php
	?></div><?php

	if (
		$arParams["LAZY_LOAD"] === 'Y'
		&& !$arResult["SHOW_FULL_FORM"]
	) // lazyloadmode on + not ajax
	{
		?><div id="full<?=$jsObjName?>"></div><?php
	}

	?><script>
		BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);

		BX.message({
			PATH_TO_USER_TASKS_TASK : '<?=CUtil::JSEscape($arParams['PATH_TO_USER_TASKS_TASK'])?>',
		});

		new BX.Socialnetwork.Livefeed.PostForm({
			lazyLoad: <?=(!$arResult["SHOW_FULL_FORM"] ? 'true' : 'false')?>,
			ajaxUrl : '<?=CUtil::JSEscape(htmlspecialcharsBack(POST_FORM_ACTION_URI))?>',
			container: <?=(!$arResult["SHOW_FULL_FORM"] ? "BX('full".$jsObjName."')" : "false")?>,
			containerMicro: <?=(!$arResult["SHOW_FULL_FORM"] ? "BX('micro".$jsObjName."')" : "false")?>,
			containerMicroInner: <?=(!$arResult["SHOW_FULL_FORM"] ? "BX('micro".$jsObjName."_inner')" : "false")?>,
			successPostId: <?= (int)$request->get('successPostId') ?>,
		});
	</script><?php

	$dynamicArea = new FrameStatic("sbpe_dynamic");
	$dynamicArea->startDynamicArea();
	?><script>
		BX.ready(function() {
			<?php
			if (
				isset($_SESSION["SL_TASK_ID_CREATED"])
				&& in_array('tasks', $arResult['tabs'], true)
			)
			{
				if ((int)$_SESSION["SL_TASK_ID_CREATED"] > 0)
				{
					?>
					BX.Socialnetwork.Livefeed.PostForm.getInstance().tasksTaskEvent(<?=(int)$_SESSION["SL_TASK_ID_CREATED"]?>);
					<?php
				}
				unset($_SESSION["SL_TASK_ID_CREATED"]);
			}
			?>
			BX.Socialnetwork.Livefeed.PostForm.getInstance().setOption('startVideoRecorder', '<?=($arResult['startVideoRecorder'] ? 'Y' : 'N')?>');
			BX.Socialnetwork.Livefeed.PostForm.getInstance().onShow();
		});
	</script><?php
	$dynamicArea->finishDynamicArea();


	if ($arResult["SHOW_FULL_FORM"]) // lazyloadmode on + ajax
	{
		if (
			isset($_POST["action"])
			&& $_POST["action"] === "SBPE_get_full_form"
		)
		{
			$APPLICATION->ShowAjaxHead();
		}

		$postFormActionUri = ($arParams["POST_FORM_ACTION_URI"] ?? htmlspecialcharsback(POST_FORM_ACTION_URI));
		$uri = new Bitrix\Main\Web\Uri($postFormActionUri);
		$uri->deleteParams([ "b24statAction", "b24statTab", "b24statAddEmailUserCrmContact" ]);
		$uri->addParams([
			"b24statAction" => ($arParams["ID"] > 0 ? "editLogEntry" : "addLogEntry"),
		]);
		$postFormActionUri = $uri->getUri();

		$selectorId = 'dest-selector-blog-post-form';

		?><div id="microblog-form">
		<form action="<?=htmlspecialcharsbx($postFormActionUri)?>" id="blogPostForm" name="blogPostForm" method="POST" enctype="multipart/form-data" target="_self" data-bx-selector-id="<?=htmlspecialcharsbx($selectorId)?>">
			<input type="hidden" name="show_title" id="show_title" value="<?=($bShowTitle ? "Y" : "N")?>">
			<?= bitrix_sessid_post() ?>
			<div class="feed-add-post-form-wrap"><?php
				if (
					($arParams["TOP_TABS_VISIBLE"] ?? null) !== "Y"
					&& (
						!isset($arParams["PAGE_ID"])
						|| !in_array($arParams["PAGE_ID"], [ "user_blog_post_edit_profile", "user_blog_post_edit_grat", "user_grat", "user_blog_post_edit_post" ])
					)
				)
				{
					?><div class="feed-add-post-form-variants" id="feed-add-post-form-tab"><?php
						echo $strGratVote;

						if ($arParams["SHOW_BLOG_FORM_TARGET"] ?? null)
						{
							$APPLICATION->ShowViewContent("sonet_blog_form");
						}
						?><div id="feed-add-post-form-tab-arrow" class="feed-add-post-form-arrow" style="left: 31px;"></div><?php
					?></div><?php
				}

				?><div id="feed-add-post-content-message">
					<div class="feed-add-post-title" id="blog-title" style="display: none;">
						<input id="POST_TITLE" name="POST_TITLE" class="feed-add-post-inp feed-add-post-inp-active" <?php
						?>type="text" value="<?=$arResult["PostToShow"]["TITLE"]?>" placeholder="<?= Loc::getMessage('BLOG_TITLE') ?>" />
						<div class="feed-add-close-icon" onclick="BX.Socialnetwork.Livefeed.PostFormEditor.getInstance('<?= $arParams["FORM_ID"] ?>').showPanelTitle(false);"></div>
					</div>
					<?php
					$APPLICATION->IncludeComponent(
						"bitrix:main.post.form",
						"",
						($formParams = [
							"FORM_ID" => "blogPostForm",
							"DEST_SELECTOR_ID" => $selectorId,
							"SHOW_MORE" => "Y",
							"PARSER" => [
								"Bold", "Italic", "Underline", "Strike", "ForeColor",
								"FontList", "FontSizeList", "RemoveFormat", "Quote", "Code",
								(($arParams["USE_CUT"] === "Y") ? "InsertCut" : ""),
								"CreateLink",
								"Image",
								"Table",
								"Justify",
								"InsertOrderedList",
								"InsertUnorderedList",
								"SmileList",
								"Source",
								"UploadImage",
								(($arResult["allowVideo"] === "Y") ? "InputVideo" : ""),
								"MentionUser",
							],
							"BUTTONS" => [
								"UploadImage",
								"UploadFile",
								"CreateLink",
								(($arResult["allowVideo"] === "Y") ? "InputVideo" : ""),
								"Quote",
								"MentionUser",
								"InputTag",
								"VideoMessage",
							],
							"BUTTONS_HTML" => [
								"VideoMessage" => '<span class="feed-add-post-form-but-cnt feed-add-videomessage" onclick="BX.VideoRecorder.start(\''.$arParams["FORM_ID"].'\', \'post\');">' . Loc::getMessage('BLOG_VIDEO_RECORD_BUTTON') . '</span>'
							],
							"ADDITIONAL" => [
								"<span title=\"" . Loc::getMessage('BLOG_TITLE') . "\" ".
								"onclick=\"BX.Socialnetwork.Livefeed.PostFormEditor.getInstance('".$arParams["FORM_ID"]."').showPanelTitle(this);\" ".
								"class=\"feed-add-post-form-title-btn".($bShowTitle ? " feed-add-post-form-btn-active" : "")."\" ".
								"id=\"lhe_button_title_".$arParams["FORM_ID"]."\" ".
								"></span>"
							],

							"TEXT" => [
								"NAME" => "POST_MESSAGE",
								"VALUE" => \Bitrix\Main\Text\Emoji::decode(htmlspecialcharsBack($arResult["PostToShow"]["~DETAIL_TEXT"])),
								"HEIGHT" => "120px"
							],

							"PROPERTIES" => [
								array_key_exists("UF_BLOG_POST_FILE", $arResult["POST_PROPERTIES"]["DATA"]) ?
									array_merge(
										(is_array($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"]) ? $arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"] : []),
										($arResult['bVarsFromForm'] && is_array($_POST["UF_BLOG_POST_FILE"]) ? [ "VALUE" => $_POST["UF_BLOG_POST_FILE"] ] : []))
									:
									array_merge(
										(is_array($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_DOC"]) ? $arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_DOC"] : []),
										($arResult['bVarsFromForm'] && is_array($_POST["UF_BLOG_POST_DOC"]) ? [ "VALUE" => $_POST["UF_BLOG_POST_DOC"] ] : []),
										[ "POSTFIX" => "file"]
									),
								array_key_exists("UF_BLOG_POST_URL_PRV", $arResult["POST_PROPERTIES"]["DATA"]) ?
									array_merge(
										$arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_URL_PRV"],
										[
											'ELEMENT_ID' => 'url_preview_'.$id,
											'STYLE' => 'margin: 0 18px'
										]
									)
									: []
							],
							"UPLOAD_FILE_PARAMS" => [ 'width' => $arParams["IMAGE_MAX_WIDTH"], 'height' => $arParams["IMAGE_MAX_HEIGHT"] ],

							"DESTINATION" => [
								"VALUE" => $arResult["PostToShow"]["FEED_DESTINATION"],
								"SHOW" => (!isset($arParams["PAGE_ID"]) || $arParams["PAGE_ID"] !== "user_blog_post_edit_profile" ? 'Y' : 'N')
							],
							"DEST_SORT" => $arResult["DEST_SORT"] ?? [],
							"SELECTOR_CONTEXT" => "BLOG_POST",
							'SELECTOR_VERSION' => $arResult['SELECTOR_VERSION'],
							"TAGS" => [
								"ID" => "TAGS",
								"NAME" => "TAGS",
								"VALUE" => explode(",", trim($arResult["PostToShow"]["CategoryText"] ?? '')),
								"USE_SEARCH" => "Y",
								"FILTER" => "blog",
							],
							"SMILES" => (int)Option::get('blog', 'smile_gallery_id', 0),
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
							"AT_THE_END_HTML" => $htmlAfterTextarea,
							"LHE" => [
								"id" => $id,
								"documentCSS" => "body {color:#434343;}",
								"iframeCss" => "html body { line-height: 20px!important;}",
								"ctrlEnterHandler" => "submitBlogPostForm",
								"jsObjName" => $jsObjName,
								"fontSize" => "14px",
								"bInitByJS" => (
									!$arResult['bVarsFromForm']
									&& ($arParams["TOP_TABS_VISIBLE"] ?? null) === "Y"
								),
							],
							"USE_CLIENT_DATABASE" => "Y",
							"DEST_CONTEXT" => "BLOG_POST",
							"ALLOW_EMAIL_INVITATION" => ($arResult["ALLOW_EMAIL_INVITATION"] ? 'Y' : 'N'),
							'MENTION_ENTITIES' => [
								[
									'id' => 'user',
									'options' => [
										'emailUsers' => true,
										'inviteEmployeeLink' => false,
									],
								],
								[
									'id' => 'department',
									'options' => [
										'selectMode' => 'usersAndDepartments',
										'allowFlatDepartments' => false,
									],
								],
								[
									'id' => 'project',
									'options' => [
										'features' => [
											'blog' =>  [ 'premoderate_post', 'moderate_post', 'write_post', 'full_post' ],
										],
									],
								],
							],
						]),
						false,
						[ "HIDE_ICONS" => "Y" ]
					);?>
				</div><?php
				if (
					isset($arParams["PAGE_ID"])
					&& $arParams["PAGE_ID"] === "user_blog_post_edit_profile"
					&& $arResult["perms"] === BLOG_PERMS_FULL
				)
				{
					?><input type="hidden" name="DEST_CODES[]" value="UP<?=(int)$arParams['USER_ID']?>" /><?php
				}
			?></div><?php //feed-add-post-form-wrap
			?><div id="feed-add-post-content-message-add-ins" class="feed-add-post-content-message-add-ins feed-post-form-block-hidden"><?php
				if (in_array('vote', $arResult['tabs'], true))
				{
					?><div id="feed-add-post-content-vote" style="display: none;"><?php
					if (ModuleManager::isModuleInstalled("vote"))
					{
						$APPLICATION->IncludeComponent(
							"bitrix:system.field.edit",
							"vote",
							[
								"bVarsFromForm" => $arResult['bVarsFromForm'],
								"arUserField" => $arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_VOTE"]
							],
							null,
							[ "HIDE_ICONS" => "Y" ]
						);
					}
					?></div><?php
				}
				?><div id="feed-add-post-content-important" style="display: none;"><?php
					?><span style="display: none;"><?php
						$APPLICATION->IncludeComponent(
							"bitrix:system.field.edit",
							"integer",
							[
								"bVarsFromForm" => $arResult['bVarsFromForm'],
								"arUserField" => $arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_IMPRTNT"]
							],
							null,
							[ "HIDE_ICONS" => "Y" ]
						);
					?></span><?php

					if (
						isset($arResult["POST_PROPERTIES"]["DATA"]["UF_IMPRTANT_DATE_END"])
						&& !empty($arResult["POST_PROPERTIES"]["DATA"]["UF_IMPRTANT_DATE_END"])
					)
					{
						$dateTillPostIsShowing = false;
						if (
							isset($arResult["POST_PROPERTIES"]["DATA"]["UF_IMPRTANT_DATE_END"]["VALUE"])
							&& $arResult["POST_PROPERTIES"]["DATA"]["UF_IMPRTANT_DATE_END"]["VALUE"]
						)
						{
							$dateTillPostIsShowing = $arResult["POST_PROPERTIES"]["DATA"]["UF_IMPRTANT_DATE_END"]["VALUE"];
						}
						$ufPostEndTimeEditing = $dateTillPostIsShowing ?: '';
						?>
						<div class="feed-add-post-expire-date">
							<div class="feed-add-post-expire-date-wrap">
								<div class="feed-add-post-expire-date-inner js-post-expire-date-block
								<?= ($ufPostEndTimeEditing ? 'feed-add-post-expire-date-customize' : '') ?>">
									<span class="feed-add-post-expire-date-text"><?= htmlspecialcharsbx(Loc::getMessage('IMPORTANT_TILL_TITLE')) ?></span>
									<span id="js-post-expire-date-wrapper" class="feed-add-post-expire-date-period ">
										<span class="feed-add-post-expire-date-duration js-important-till-popup-trigger"><?php
											?><?= htmlspecialcharsbx(
												$dateTillPostIsShowing
													? Loc::getMessage("IMPORTANT_FOR_CUSTOM")
													: Loc::getMessage($arResult["REMAIN_IMPORTANT_DEFAULT_OPTION"]["TEXT_KEY"])
											) ?><?php
										?></span>
										<div class="js-post-showing-duration-options-container main-ui-hide">
											<?php
											foreach ($arResult["REMAIN_IMPORTANT_TILL"] as $periodAttributes)
											{
												?>
												<span class="main-ui-hide js-post-showing-duration-option"
													  data-value="<?= htmlspecialcharsbx($periodAttributes['VALUE']) ?>"
													  data-class="<?= htmlspecialcharsbx($periodAttributes['CLASS']) ?>"
													  data-text="<?= htmlspecialcharsbx(Loc::getMessage($periodAttributes['TEXT_KEY'])) ?>"></span><?php
											}
											?>
										</div>
										<span class="js-date-post-showing-custom feed-add-post-expire-date-final"><?= htmlspecialcharsbx($ufPostEndTimeEditing) ?></span>
										<input class="js-form-editing-post-end-time" type="hidden" name="UF_IMPRTANT_DATE_END_SAVED" value="<?= htmlspecialcharsbx($ufPostEndTimeEditing) ?>">
										<input class="js-form-post-end-time" type="hidden" name="UF_IMPRTANT_DATE_END" value="<?= htmlspecialcharsbx($ufPostEndTimeEditing) ?>">
										<input class="js-form-post-end-period" type="hidden" name="postShowingDuration"
											   value="<?= htmlspecialcharsbx($dateTillPostIsShowing ? "CUSTOM" : $arResult["REMAIN_IMPORTANT_DEFAULT_OPTION"]["VALUE"]) ?>">
									</span>
								</div>
							</div>
						</div>
						<script>
							BX.ready(function(){
								new BX.Socialnetwork.Livefeed.PostFormDateEnd();
							});
						</script><?php
					}
				?></div><?php
				if (in_array('grat', $arResult['tabs'], true))
				{
					?><div id="feed-add-post-content-grat" style="display: <?=($arResult['tabActive'] === "grat" ? "block" : "none")?>;"><?php

						?><div class="feed-add-grat-block feed-add-grat-star"><?php

						$grat_type = ""; $title_default = "";

						if (
							isset($arResult["PostToShow"]["GRAT_CURRENT"])
							&& isset($arResult["PostToShow"]["GRAT_CURRENT"]["TYPE"])
							&& is_array($arResult["PostToShow"]["GRAT_CURRENT"])
							&& is_array($arResult["PostToShow"]["GRAT_CURRENT"]["TYPE"])
						)
						{
							$grat_type = htmlspecialcharsbx($arResult["PostToShow"]["GRAT_CURRENT"]["TYPE"]["XML_ID"]);
							$class_default = "feed-add-grat-medal-".htmlspecialcharsbx($arResult["PostToShow"]["GRAT_CURRENT"]["TYPE"]["XML_ID"]);
							$title_default = htmlspecialcharsbx($arResult["PostToShow"]["GRAT_CURRENT"]["TYPE"]["VALUE_ENUM"] ?? null);
						}
						elseif (is_array($arResult["PostToShow"]["GRATS_DEF"]))
						{
							$grat_type = htmlspecialcharsbx($arResult["PostToShow"]["GRATS_DEF"]["XML_ID"]);
							$class_default = "feed-add-grat-medal-".htmlspecialcharsbx($arResult["PostToShow"]["GRATS_DEF"]["XML_ID"]);
							$title_default = htmlspecialcharsbx($arResult["PostToShow"]["GRATS_DEF"]["VALUE"]);
						}

						?><div id="feed-add-post-grat-type-selected" class="feed-add-grat-medal"<?=($title_default ? ' title="'.$title_default.'"' : '')?>>
							<span class="feed-add-grat-box<?=($class_default ? " ".$class_default : "")?>"></span>
							<div id="feed-add-post-grat-others" class="feed-add-grat-medal-other"><?= Loc::getMessage('BLOG_TITLE_GRAT_OTHER') ?></div>
							<div class="feed-add-grat-medal-arrow"></div>
						</div>
						<input type="hidden" name="GRAT_TYPE" value="<?=htmlspecialcharsbx($grat_type)?>" id="feed-add-post-grat-type-input">
						<script>

							BX.ready(function(){

								var arGrats = [];
								var	BXSocNetLogGratFormName = '<?=$this->randString(6)?>';
								<?php
								if (is_array($arResult["PostToShow"]["GRATS"]))
								{
									foreach ($arResult["PostToShow"]["GRATS"] as $i => $arGrat)
									{
										?>
										arGrats[<?=CUtil::JSEscape($i)?>] = {
											'title': '<?=CUtil::JSEscape($arGrat["VALUE"])?>',
											'code': '<?=CUtil::JSEscape($arGrat["XML_ID"])?>',
											'style': 'feed-add-grat-medal-<?=CUtil::JSEscape($arGrat["XML_ID"])?>'
										};
										<?php
									}
								}

								$selectorId = 'dest-selector-blog-post-form-grat';
								?>

								new BX.Socialnetwork.Livefeed.PostFormGratSelector({
									name: BXSocNetLogGratFormName,
									gratsList: arGrats,
									itemSelectedImageItem: document.getElementById('feed-add-post-grat-type-selected'),
									itemSelectedInput: document.getElementById('feed-add-post-grat-type-input'),
									entitySelectorParams: {
										id: '<?=CUtil::JSescape($selectorId)?>',
										tagNodeId: 'entity-selector-<?=CUtil::JSescape($selectorId)?>',
										inputNodeId: 'entity-selector-data-<?=CUtil::JSescape($selectorId)?>',
										preselectedItems: <?=CUtil::PhpToJSObject($arResult['selectedGratitudeEntities'])?>,
									}
								});
							});

						</script>
						<div class="feed-add-grat-right">
							<div class="feed-add-grat-label"><?= Loc::getMessage("BLOG_TITLE_GRAT") ?></div>
							<div class="feed-add-grat-form">
								<input type="hidden" id="entity-selector-data-<?= htmlspecialcharsbx($selectorId) ?>" name="GRAT_DEST_DATA" value="<?= htmlspecialcharsbx(Json::encode($arResult['selectedGratitudeEntities'])) ?>" />
								<div id="entity-selector-<?=htmlspecialcharsbx($selectorId)?>"></div>
							</div>
						</div>
					</div><?php
					?></div><?php
				}

				if (!empty($arResult['POST_PROPERTIES']['DATA']['UF_MAIL_MESSAGE']['VALUE']))
				{
					?>
					<div id="blog-post-user-fields-UF_MAIL_MESSAGE" style="padding-top: 15px; padding-bottom: 15px; ">
						<?php
						$APPLICATION->includeComponent(
							'bitrix:system.field.edit',
							$arResult['POST_PROPERTIES']['DATA']['UF_MAIL_MESSAGE']['USER_TYPE']['USER_TYPE_ID'],
							[ 'arUserField' => $arResult['POST_PROPERTIES']['DATA']['UF_MAIL_MESSAGE'] ],
							null,
							[ 'HIDE_ICONS' => 'Y' ]
						);
						?>
					</div>
					<div class="blog-clear-float"></div>
					<?php
				}

				foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField)
				{
					if (in_array($FIELD_NAME, $arParams["POST_PROPERTY_SOURCE"], true))
					{
						?>
						<div id="blog-post-user-fields-<?=$FIELD_NAME?>"><?=$arPostField["EDIT_FORM_LABEL"].":"?>
							<?php
							$APPLICATION->IncludeComponent(
								"bitrix:system.field.edit",
								$arPostField["USER_TYPE"]["USER_TYPE_ID"],
								[ "arUserField" => $arPostField ],
								null,
								[ "HIDE_ICONS"=>"Y" ]
							);
							?>
						</div>
						<div class="blog-clear-float"></div>
						<?php
					}
				}

				if (in_array('calendar', $arResult['tabs'], true))
				{
					?><div id="feed-add-post-content-calendar" style="display: none;"></div><?php
				}

				if (in_array('lists', $arResult['tabs'], true))
				{
					?>
					<div id="feed-add-post-content-lists" style="display: none;">
						<?php
						$APPLICATION->IncludeComponent("bitrix:lists.live.feed", "",
							[
								"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"],
								"DESTINATION" => $arResult["PostToShow"],
								"IBLOCK_ID" => $_GET['bp_setting'] ?? 0
							],
							null,
							[ "HIDE_ICONS" => "Y" ]
						);
						?>
					</div>
					<?php
				}

				if (in_array('tasks', $arResult['tabs'], true))
				{
					?><div id="feed-add-post-content-tasks" style="display: none;"><div id="feed-add-post-content-tasks-container"><?php

						$taskSubmitted = false;

						if (
							isset($_REQUEST['ACTION'])
							&& is_array($_REQUEST['ACTION'])
						)
						{
							foreach ($_REQUEST['ACTION'] as $taskAction)
							{
								if (
									!empty($taskAction['OPERATION'])
									&& $taskAction['OPERATION'] === 'task.add'
									&& Loader::includeModule('tasks')
								)
								{
									$taskSubmitted = true;
									break;
								}
							}
						}

						if ($taskSubmitted)
						{
							CTaskNotifications::enableSonetLogNotifyAuthor();

							$componentParameters = [
								'ID' => 0,
								'GROUP_ID' => $arParams['SOCNET_GROUP_ID'],
								'PATH_TO_USER_PROFILE' => $arParams['PATH_TO_USER_PROFILE'],
								'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
								'PATH_TO_USER_TASKS' => $arParams['PATH_TO_USER_TASKS'],
								'PATH_TO_USER_TASKS_TASK' => $arParams['PATH_TO_USER_TASKS_TASK'],
								'PATH_TO_GROUP_TASKS' => $arParams['PATH_TO_GROUP_TASKS'],
								'PATH_TO_GROUP_TASKS_TASK' => $arParams['PATH_TO_GROUP_TASKS_TASK'],
								'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
								'PATH_TO_USER_TASKS_TEMPLATES' => $arParams['PATH_TO_USER_TASKS_TEMPLATES'],
								'PATH_TO_USER_TEMPLATES_TEMPLATE' => $arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'],
								'SET_NAVCHAIN' => 'N',
								'SET_TITLE' => 'N',
								'SHOW_RATING' => 'N',
								'NAME_TEMPLATE' => $arParams["NAME_TEMPLATE"],
								'ENABLE_FOOTER' => 'N',
								'ENABLE_MENU_TOOLBAR' => 'N',
								'SUB_ENTITY_SELECT' => [
									'TAG',
									'CHECKLIST',
									'REMINDER',
									'PROJECTDEPENDENCE',
									'TEMPLATE',
									'RELATEDTASK'
								], // change to API call
								'AUX_DATA_SELECT' => [
									'COMPANY_WORKTIME',
									'USER_FIELDS',
									'TEMPLATE'
								], // change to API call
								'BACKURL' => $arParams['TASK_SUBMIT_BACKURL'],
								'ACTION' => 'edit'
							];

							$APPLICATION->IncludeComponent('bitrix:tasks.task', '',
								$componentParameters,
								null,
								[ "HIDE_ICONS" => "Y" ]
							);

							CTaskNotifications::disableSonetLogNotifyAuthor();
						}
						?></div></div><?php
				}

				?></div>
				<script>
					BX.message({
						'BLOG_TITLE' : '<?= GetMessageJS("BLOG_TITLE") ?>',
						'BLOG_TAB_GRAT': '<?= GetMessageJS("BLOG_TAB_GRAT") ?>',
						'BLOG_TAB_VOTE': '<?= GetMessageJS("BLOG_TAB_VOTE") ?>',
						'SBPE_IMPORTANT_MESSAGE': '<?= GetMessageJS("SBPE_IMPORTANT_MESSAGE") ?>',
						'BLOG_POST_AUTOSAVE':'<?= GetMessageJS("BLOG_POST_AUTOSAVE") ?>',
						'BLOG_POST_AUTOSAVE2' : '<?= GetMessageJS("BLOG_POST_AUTOSAVE2") ?>',
						'SBPE_CALENDAR_EVENT': '<?= GetMessageJS("SBPE_CALENDAR_EVENT") ?>',
						'LISTS_CATALOG_PROCESSES_ACCESS_DENIED' : '<?= GetMessageJS("LISTS_CATALOG_PROCESSES_ACCESS_DENIED") ?>',
					});
					<?php
					if (in_array('tasks', $arResult['tabs'], true))
					{
						?>
						BX.message({
							'TASK_SOCNET_GROUP_ID' : <?=(int)$arParams['SOCNET_GROUP_ID']?>,
							'PATH_TO_USER_PROFILE' : '<?=CUtil::JSEscape($arParams['PATH_TO_USER_PROFILE'])?>',
							'PATH_TO_GROUP' : '<?=CUtil::JSEscape($arParams['PATH_TO_GROUP'])?>',
							'PATH_TO_USER_TASKS' : '<?=CUtil::JSEscape($arParams['PATH_TO_USER_TASKS'])?>',
							'PATH_TO_GROUP_TASKS' : '<?=CUtil::JSEscape($arParams['PATH_TO_GROUP_TASKS'])?>',
							'PATH_TO_GROUP_TASKS_TASK' : '<?=CUtil::JSEscape($arParams['PATH_TO_GROUP_TASKS_TASK'])?>',
							'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' : '<?=CUtil::JSEscape($arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'])?>',
							'PATH_TO_USER_TASKS_TEMPLATES' : '<?=CUtil::JSEscape($arParams['PATH_TO_USER_TASKS_TEMPLATES'])?>',
							'PATH_TO_USER_TEMPLATES_TEMPLATE' : '<?=CUtil::JSEscape($arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'])?>',
							'LOG_EXPERT_MODE' : '<?=(isset($arParams["LOG_EXPERT_MODE"]) ? CUtil::JSEscape($arParams['LOG_EXPERT_MODE']) : 'N')?>',
							'TASK_SUBMIT_BACKURL' : '<?=CUtil::JSEscape($arParams['TASK_SUBMIT_BACKURL'])?>'
						});
						<?php
					}
					?>
					new BX.Socialnetwork.Livefeed.PostFormEditor('<?=$arParams["FORM_ID"]?>', {
						editorID: '<?=$id?>',
						showTitle: '<?=$bShowTitle?>',
						autoSave: '<?=(COption::GetOptionString("blog", "use_autosave", "Y") === "Y" ? ($arParams["ID"] > 0 ? "onDemand" : "Y") : 'N')?>',
						activeTab: '<?=($arResult['bVarsFromForm'] || $arParams["ID"] > 0 ? CUtil::JSEscape($arResult['tabActive']) : '')?>',
						text: '<?=CUtil::JSEscape($formParams["TEXT"]["VALUE"])?>',
						restoreAutosave: <?=(empty($arResult["ERROR_MESSAGE"]) ? 'true' : 'false')?>,
						createdFromEmail: <?= (!empty($arResult['POST_PROPERTIES']['DATA']['UF_MAIL_MESSAGE']['VALUE']) ? 'true' : 'false') ?>,
					});

				</script>
				<?php
				if (Option::get('blog', 'use_autosave', 'Y') === "Y")
				{
					$dynamicArea = new FrameStatic("post-autosave");
					$dynamicArea->startDynamicArea();
					$as = new CAutoSave();
					$as->Init(false);
					$dynamicArea->finishDynamicArea();
				}
				$arButtons = [
					[
						"NAME" => "save",
						"TEXT" => GetMessage(!empty($arResult["Post"]) && !empty($arResult["Post"]["PUBLISH_STATUS"]) && $arResult["Post"]["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_DRAFT ? "BLOG_BUTTON_PUBLISH" : "BLOG_BUTTON_SEND"),
						"CLICK" => "submitBlogPostForm();",
					],
				];

				if (
					($arParams["MICROBLOG"] ?? null) !== "Y"
					&& (int) $arResult['UserID'] === (int) $arResult['Blog']['OWNER_ID']
					&& !in_array(
						$arParams["PAGE_ID"] ?? '',
						[
							"user_blog_post_edit_profile",
							"user_blog_post_edit_grat",
							"user_grat",
							"user_blog_post_edit_post",
						]
					)
				)
				{
					$arButtons[] = [
						"NAME" => "draft",
						"TEXT" => Loc::getMessage('BLOG_BUTTON_DRAFT')
					];
				}
				else
				{
					$arButtons[] = [
						"NAME" => "cancel",
						"TEXT" => Loc::getMessage('BLOG_BUTTON_CANCEL'),
						"CLICK" => "BX.Socialnetwork.Livefeed.PostFormTabs.getInstance().collapse({ userId: " . (int)$arParams['USER_ID'] . "})",
						"CLEAR_CANCEL" => "Y",
					];
				}

				?><div class="feed-add-post-bottom-alert feed-post-form-block-hidden" id="feed-add-post-bottom-alert<?= $arParams['FORM_ID'] ?>"></div><?php

				?><div class="feed-buttons-block feed-post-form-block-hidden" id="feed-add-buttons-block<?=$arParams["FORM_ID"]?>"><?php
					$scriptFunc = [];
					foreach ($arButtons as $val)
					{
						$onclick = $val["CLICK"] ?? '';
						if ((string) $onclick === '')
						{
							$onclick = "submitBlogPostForm('" . $val["NAME"] . "'); ";
						}
						$scriptFunc[$val["NAME"]] = $onclick;
						if (
							isset($val["CLEAR_CANCEL"])
							&& $val["CLEAR_CANCEL"] === "Y"
						)
						{
							?><span class="ui-btn ui-btn-lg ui-btn-link" id="blog-submit-button-<?= $val["NAME"] ?>"><?= $val["TEXT"] ?></span><?php
						}
						else
						{
							?><span class="ui-btn ui-btn-lg ui-btn-primary" id="blog-submit-button-<?= $val["NAME"] ?>"><?= $val["TEXT"] ?></span><?php
						}
					}
					if (!empty($scriptFunc))
					{
						?><script>
						BX.ready(function(){
							<?php
							foreach ($scriptFunc as $id => $handler)
							{
								?>BX.bind(BX("blog-submit-button-<?=$id?>"), "click", function(e) {
									<?= $handler ?>;
									return e.preventDefault();
								});
								<?php
							}
							?>
						});
						</script><?php
					}
				?></div>
			<input type="hidden" name="blog_upload_cid" id="upload-cid" value="">
		</form><?php
		?><div id="task_form_hidden" style="display: none;"></div><?php
		?></div><?php

		if (
			isset($_POST["action"])
			&& $_POST["action"] === "SBPE_get_full_form"
		)
		{
			$strFullForm = ob_get_contents();
			while (ob_end_clean()) {}

			$JSList = $stringsList = [];

			Asset::getInstance()->getJs();
			$CSSStrings = Asset::getInstance()->getCss();
			Asset::getInstance()->getStrings();

			$targetTypeList = [ 'JS'/*, 'CSS'*/ ];
			foreach ($targetTypeList as $targetType)
			{
				$targetAssetList = Asset::getInstance()->getTargetList($targetType);

				foreach ($targetAssetList as $targetAsset)
				{
					$assetInfo = Asset::getInstance()->getAssetInfo($targetAsset['NAME'], \Bitrix\Main\Page\AssetMode::ALL);
					if (!empty($assetInfo['JS']))
					{
						$JSList = array_merge($JSList, $assetInfo['JS']);
					}
					if (!empty($assetInfo['STRINGS']))
					{
						$stringsList = array_merge($stringsList, $assetInfo['STRINGS']);
					}
				}
			}

			$JSList = array_unique($JSList);

			echo CUtil::PhpToJSObject([
				"PROPS" => [
					"CONTENT" => $CSSStrings.implode('', $stringsList).$strFullForm,
					"STRINGS" => [],
					"JS" => $JSList,
					"CSS" => []
				],
				"success" => true
			]);
			die();
		}
	}
}
?>

</div>
</div>
