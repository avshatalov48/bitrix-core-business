<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if($arResult['IFRAME'])
{
	$APPLICATION->RestartBuffer();?>
<!DOCTYPE html>
<!--
Copyright 2012 Mozilla Foundation

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

Adobe CMap resources are covered by their own copyright but the same license:

    Copyright 1990-2015 Adobe Systems Incorporated.

See https://github.com/adobe-type-tools/cmap-resources
-->
<html dir="ltr" mozdisallowselectionprint moznomarginboxes lang="<?=LANGUAGE_ID;?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="google" content="notranslate">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?=$arResult['TITLE'];?></title>
	<?
	foreach($arResult['CSS_FILES'] as $file)
	{
		echo '<link rel="stylesheet" href="'.$file.'">';
	}
	if($arResult['LOCALE_FILES'])
	{
		echo '<link rel="resource" type="application/l10n" href="'.$arResult['LOCALE_FILES'].'">';
	}
	foreach($arResult['JS_FILES'] as $file)
	{
		echo '<script src="'.$file.'"></script>';
	}
	?>
	<style>
		* {
			margin: 0;
			padding: 0;
		}
	</style>
	<script>
		window.pdfJsFilePath = '<?=CUtil::JSEscape($arResult['PATH']);?>';
		window.pdfJsPathToWorker = '<?=CUtil::JSEscape($this->arResult['PATH_TO_WORKER']);?>';
		window.pdfJsLangCharset = '<?=LANG_CHARSET;?>';
		window.pdfJsViewerId = '_<?=CUtil::JSEscape($arResult['ID']);?>';
		<?if($arResult['PRINT'])
		{?>
		window.addEventListener('pagesloaded', function(params){
			setTimeout(function(){
				var printButton = document.getElementById('print_<?=CUtil::JSEscape($arResult['ID']);?>');
				if(!!printButton)
				{
					printButton.dispatchEvent(new Event('click'));
				}
			}, 100);
		}, true);
		<?}?>
	</script>
</head>

<body tabindex="1" class="loadingInProgress">
<?
	$outerContainerStyles = 'style="display: block; position: absolute;"';
}
else
{
	if($arResult['LOCALE_FILES'])
	{
		echo '<link rel="resource" type="application/l10n" href="'.$arResult['LOCALE_FILES'].'">';
	}
	?>
	<script>
		window.pdfJsFilePath = '<?=CUtil::JSEscape($arResult['PATH']);?>';
		window.pdfJsPathToWorker = '<?=CUtil::JSEscape($arResult['PATH_TO_WORKER']);?>';
		window.pdfJsViewerId = '_<?=CUtil::JSEscape($arResult['ID']);?>';
		function showOnTimeout()
		{
			setTimeout(function(){
				BX.show(BX('outerContainer_<?=CUtil::JSEscape($arResult['ID']);?>'));
			}, 50);
		}
		if(!!window.PdfJsLoaded)
		{
			BX.fireEvent(document, 'PdfJsChangeSource');
			showOnTimeout();
		}
		else
		{
			BX.ready(function(){
				BX.loadCSS(<?=\CUtil::PhpToJSObject($arResult['CSS_FILES']);?>);
				BX.loadScript(<?=\CUtil::PhpToJSObject($arResult['JS_FILES']);?>, function(){
					PDFJS.locale = '<?=LANGUAGE_ID;?>';
					showOnTimeout();
				});
			});
			window.PdfJsLoaded = true;
		}
		<?if(isset($arResult['PRINT_URL']))
		{?>
		function openPrintInNewWindow()
		{
			window.open('<?=CUtil::JSEscape($arResult['PRINT_URL']);?>', '_blank');
		}
		window.pdfJsPrintDisabled = true;
		<?}?>
	</script>
	<div class="bx-pdf-container" style="
		height: <?=intval($arResult['HEIGHT']);?>px;
		width: <?=intval($arResult['WIDTH']);?>px;
		">
	<?
	$outerContainerStyles = 'style="display: none;"';
}?>
<div class="outerContainer" id="outerContainer_<?=htmlspecialcharsbx($arResult['ID']);?>" <?=$outerContainerStyles;?>">
	<div id="sidebarContainer_<?=htmlspecialcharsbx($arResult['ID']);?>" class="sidebarContainer">
		<div id="toolbarSidebar_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarSidebar">
			<div class="splitToolbarButton toggled">
				<button id="viewThumbnail_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton toggled viewThumbnail" title="Show Thumbnails" tabindex="2" data-l10n-id="thumbs">
					<span data-l10n-id="thumbs_label">Thumbnails</span>
				</button>
				<button id="viewOutline_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton viewOutline" title="Show Document Outline (double-click to expand/collapse all items)" tabindex="3" data-l10n-id="document_outline">
					<span data-l10n-id="document_outline_label">Document Outline</span>
				</button>
				<button id="viewAttachments_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton viewAttachments" title="Show Attachments" tabindex="4" data-l10n-id="attachments">
					<span data-l10n-id="attachments_label">Attachments</span>
				</button>
			</div>
		</div>
		<div id="sidebarContent_<?=htmlspecialcharsbx($arResult['ID']);?>" class="sidebarContent">
			<div id="thumbnailView_<?=htmlspecialcharsbx($arResult['ID']);?>" class="thumbnailView">
			</div>
			<div id="outlineView_<?=htmlspecialcharsbx($arResult['ID']);?>" class="hidden outlineView">
			</div>
			<div id="attachmentsView_<?=htmlspecialcharsbx($arResult['ID']);?>" class="hidden attachmentsView">
			</div>
		</div>
	</div>  <!-- sidebarContainer -->

	<div id="mainContainer_<?=htmlspecialcharsbx($arResult['ID']);?>" class="mainContainer">
		<div class="findbar hidden doorHanger" id="findbar_<?=htmlspecialcharsbx($arResult['ID']);?>">
			<div id="findbarInputContainer_<?=htmlspecialcharsbx($arResult['ID']);?>">
				<input id="findInput_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarField findInput" title="Find" placeholder="Find in document..." tabindex="91" data-l10n-id="find_input">
				<div class="splitToolbarButton">
					<button id="findPrevious_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton findPrevious" title="Find the previous occurrence of the phrase" tabindex="92" data-l10n-id="find_previous">
						<span data-l10n-id="find_previous_label">Previous</span>
					</button>
					<div class="splitToolbarButtonSeparator"></div>
					<button id="findNext_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton findNext" title="Find the next occurrence of the phrase" tabindex="93" data-l10n-id="find_next">
						<span data-l10n-id="find_next_label">Next</span>
					</button>
				</div>
			</div>

			<div id="findbarOptionsContainer_<?=htmlspecialcharsbx($arResult['ID']);?>">
				<input type="checkbox" id="findHighlightAll_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarField" tabindex="94">
				<label for="findHighlightAll" class="toolbarLabel" data-l10n-id="find_highlight">Highlight all</label>
				<input type="checkbox" id="findMatchCase_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarField" tabindex="95">
				<label for="findMatchCase" class="toolbarLabel" data-l10n-id="find_match_case_label">Match case</label>
				<span id="findResultsCount_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarLabel hidden findResultsCount"></span>
			</div>

			<div id="findbarMessageContainer_<?=htmlspecialcharsbx($arResult['ID']);?>" class="findbarMessageContainer">
				<span id="findMsg_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarLabel findMsg"></span>
			</div>
		</div>  <!-- findbar -->

		<div id="secondaryToolbar_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbar hidden doorHangerRight">
			<div id="secondaryToolbarButtonContainer_<?=htmlspecialcharsbx($arResult['ID']);?>">
				<button id="secondaryPresentationMode_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton presentationMode visibleLargeView" title="Switch to Presentation Mode" tabindex="51" data-l10n-id="presentation_mode">
					<span data-l10n-id="presentation_mode_label">Presentation Mode</span>
				</button>

				<button id="secondaryOpenFile_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton openFile visibleLargeView" title="Open File" tabindex="52" data-l10n-id="open_file">
					<span data-l10n-id="open_file_label">Open</span>
				</button>

				<?if($arResult['IFRAME'])
				{?>
					<button id="secondaryPrint_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton print visibleMediumView" title="Print" tabindex="53" data-l10n-id="print">
						<span data-l10n-id="print_label">Print</span>
					</button>
				<?}
				elseif(isset($arResult['PRINT_URL']))
				{?>
					<button id="secondaryPrint_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton print visibleMediumView" title="Print" tabindex="53" data-l10n-id="print" onclick="openPrintInNewWindow(); return false;">
						<span data-l10n-id="print_label">Print</span>
					</button>
				<?}
				else
				{?>
					<button id="secondaryPrint_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton print visibleMediumView" title="Print" tabindex="53" data-l10n-id="print" style="display: none !important;">
						<span data-l10n-id="print_label">Print</span>
					</button>
				<?}?>
				<button id="secondaryDownload_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton download visibleMediumView" title="Download" tabindex="54" data-l10n-id="download">
					<span data-l10n-id="download_label">Download</span>
				</button>

				<a href="#" id="secondaryViewBookmark_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton bookmark visibleSmallView" title="Current view (copy or open in new window)" tabindex="55" data-l10n-id="bookmark">
					<span data-l10n-id="bookmark_label">Current View</span>
				</a>

				<div class="horizontalToolbarSeparator visibleLargeView"></div>

				<button id="firstPage_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton firstPage" title="Go to First Page" tabindex="56" data-l10n-id="first_page">
					<span data-l10n-id="first_page_label">Go to First Page</span>
				</button>
				<button id="lastPage_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton lastPage" title="Go to Last Page" tabindex="57" data-l10n-id="last_page">
					<span data-l10n-id="last_page_label">Go to Last Page</span>
				</button>

				<div class="horizontalToolbarSeparator"></div>

				<button id="pageRotateCw_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton rotateCw" title="Rotate Clockwise" tabindex="58" data-l10n-id="page_rotate_cw">
					<span data-l10n-id="page_rotate_cw_label">Rotate Clockwise</span>
				</button>
				<button id="pageRotateCcw_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton rotateCcw" title="Rotate Counterclockwise" tabindex="59" data-l10n-id="page_rotate_ccw">
					<span data-l10n-id="page_rotate_ccw_label">Rotate Counterclockwise</span>
				</button>

				<div class="horizontalToolbarSeparator"></div>

				<button id="cursorSelectTool_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton selectTool toggled" title="Enable Text Selection Tool" tabindex="60" data-l10n-id="cursor_text_select_tool">
					<span data-l10n-id="cursor_text_select_tool_label">Text Selection Tool</span>
				</button>
				<button id="cursorHandTool_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton handTool" title="Enable Hand Tool" tabindex="61" data-l10n-id="cursor_hand_tool">
					<span data-l10n-id="cursor_hand_tool_label">Hand Tool</span>
				</button>

				<div class="horizontalToolbarSeparator"></div>

				<button id="documentProperties_<?=htmlspecialcharsbx($arResult['ID']);?>" class="secondaryToolbarButton documentProperties" title="Document Properties..." tabindex="61" data-l10n-id="document_properties">
					<span data-l10n-id="document_properties_label">Document Properties...</span>
				</button>
			</div>
		</div>  <!-- secondaryToolbar -->

		<div class="toolbar">
			<div id="toolbarContainer_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarContainer">
				<div id="toolbarViewer_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarViewer">
					<div id="toolbarViewerLeft_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarViewerLeft">
						<button id="sidebarToggle_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton sidebarToggle" title="Toggle Sidebar" tabindex="11" data-l10n-id="toggle_sidebar">
							<span data-l10n-id="toggle_sidebar_label">Toggle Sidebar</span>
						</button>
						<div class="toolbarButtonSpacer"></div>
						<button id="viewFind_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton viewFind" title="Find in Document" tabindex="12" data-l10n-id="findbar">
							<span data-l10n-id="findbar_label">Find</span>
						</button>
						<div class="splitToolbarButton hiddenSmallView">
							<button class="toolbarButton pageUp" title="Previous Page" id="previous_<?=htmlspecialcharsbx($arResult['ID']);?>" tabindex="13" data-l10n-id="previous">
								<span data-l10n-id="previous_label">Previous</span>
							</button>
							<div class="splitToolbarButtonSeparator"></div>
							<button class="toolbarButton pageDown" title="Next Page" id="next_<?=htmlspecialcharsbx($arResult['ID']);?>" tabindex="14" data-l10n-id="next">
								<span data-l10n-id="next_label">Next</span>
							</button>
						</div>
						<input type="number" id="pageNumber_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarField pageNumber" title="Page" value="1" size="4" min="1" tabindex="15" data-l10n-id="page">
						<span id="numPages_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarLabel"></span>
					</div>
					<div id="toolbarViewerRight_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarViewerRight">
						<button id="presentationMode_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton presentationMode hiddenLargeView" title="Switch to Presentation Mode" tabindex="31" data-l10n-id="presentation_mode">
							<span data-l10n-id="presentation_mode_label">Presentation Mode</span>
						</button>

						<button id="openFile_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton openFile hiddenLargeView" title="Open File" tabindex="32" data-l10n-id="open_file">
							<span data-l10n-id="open_file_label">Open</span>
						</button>

						<?if($arResult['IFRAME'])
						{?>
							<button id="print_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton print hiddenMediumView" title="Print" tabindex="33" data-l10n-id="print">
								<span data-l10n-id="print_label">Print</span>
							</button>
						<?}
						elseif(isset($arResult['PRINT_URL']))
						{?>
							<button id="print_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton print hiddenMediumView" title="Print" tabindex="33" data-l10n-id="print" onclick="openPrintInNewWindow(); return false;">
								<span data-l10n-id="print_label">Print</span>
							</button>
						<?}
						else
						{?>
							<button id="print_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton print hiddenMediumView" title="Print" tabindex="33" data-l10n-id="print" style="display: none !important;">
								<span data-l10n-id="print_label">Print</span>
							</button>
						<?}?>

						<button id="download_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton download hiddenMediumView" title="Download" tabindex="34" data-l10n-id="download">
							<span data-l10n-id="download_label">Download</span>
						</button>
						<a href="#" id="viewBookmark_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton bookmark hiddenSmallView" title="Current view (copy or open in new window)" tabindex="35" data-l10n-id="bookmark">
							<span data-l10n-id="bookmark_label">Current View</span>
						</a>

						<div class="verticalToolbarSeparator hiddenSmallView"></div>

						<button id="secondaryToolbarToggle_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton secondaryToolbarToggle" title="Tools" tabindex="36" data-l10n-id="tools">
							<span data-l10n-id="tools_label">Tools</span>
						</button>
					</div>
					<div id="toolbarViewerMiddle_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarViewerMiddle">
						<div class="splitToolbarButton">
							<button id="zoomOut_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton zoomOut" title="Zoom Out" tabindex="21" data-l10n-id="zoom_out">
								<span data-l10n-id="zoom_out_label">Zoom Out</span>
							</button>
							<div class="splitToolbarButtonSeparator"></div>
							<button id="zoomIn_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarButton zoomIn" title="Zoom In" tabindex="22" data-l10n-id="zoom_in">
								<span data-l10n-id="zoom_in_label">Zoom In</span>
							</button>
						</div>
						<span id="scaleSelectContainer_<?=htmlspecialcharsbx($arResult['ID']);?>" class="dropdownToolbarButton scaleSelectContainer">
							<select id="scaleSelect_<?=htmlspecialcharsbx($arResult['ID']);?>" title="Zoom" tabindex="23" data-l10n-id="zoom">
								<option id="pageAutoOption_<?=htmlspecialcharsbx($arResult['ID']);?>" title="" value="auto" selected="selected" data-l10n-id="page_scale_auto">Automatic Zoom</option>
								<option id="pageActualOption_<?=htmlspecialcharsbx($arResult['ID']);?>" title="" value="page-actual" data-l10n-id="page_scale_actual">Actual Size</option>
								<option id="pageFitOption_<?=htmlspecialcharsbx($arResult['ID']);?>" title="" value="page-fit" data-l10n-id="page_scale_fit">Fit Page</option>
								<option id="pageWidthOption_<?=htmlspecialcharsbx($arResult['ID']);?>" title="" value="page-width" data-l10n-id="page_scale_width" class="pageWidthOption">Full Width</option>
								<option id="customScaleOption_<?=htmlspecialcharsbx($arResult['ID']);?>" title="" value="custom" disabled="disabled" hidden="true" class="customScaleOption"></option>
								<option title="" value="0.5" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 50 }'>50%</option>
								<option title="" value="0.75" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 75 }'>75%</option>
								<option title="" value="1" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 100 }'>100%</option>
								<option title="" value="1.25" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 125 }'>125%</option>
								<option title="" value="1.5" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 150 }'>150%</option>
								<option title="" value="2" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 200 }'>200%</option>
								<option title="" value="3" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 300 }'>300%</option>
								<option title="" value="4" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 400 }'>400%</option>
							</select>
						</span>
					</div>
				</div>
				<div id="loadingBar_<?=htmlspecialcharsbx($arResult['ID']);?>" class="loadingBar">
					<div class="progress">
						<div class="glimmer">
						</div>
					</div>
				</div>
			</div>
		</div>

		<menu type="context" id="viewerContextMenu_<?=htmlspecialcharsbx($arResult['ID']);?>">
			<menuitem id="contextFirstPage_<?=htmlspecialcharsbx($arResult['ID']);?>" label="First Page" data-l10n-id="first_page"></menuitem>
			<menuitem id="contextLastPage_<?=htmlspecialcharsbx($arResult['ID']);?>" label="Last Page" data-l10n-id="last_page"></menuitem>
			<menuitem id="contextPageRotateCw_<?=htmlspecialcharsbx($arResult['ID']);?>" label="Rotate Clockwise" data-l10n-id="page_rotate_cw"></menuitem>
			<menuitem id="contextPageRotateCcw_<?=htmlspecialcharsbx($arResult['ID']);?>" label="Rotate Counter-Clockwise" data-l10n-id="page_rotate_ccw"></menuitem>
		</menu>

		<div class="viewerContainer" id="viewerContainer_<?=htmlspecialcharsbx($arResult['ID']);?>" tabindex="0">
			<div id="viewer_<?=htmlspecialcharsbx($arResult['ID']);?>" class="pdfViewer"></div>
		</div>

		<div id="errorWrapper_<?=htmlspecialcharsbx($arResult['ID']);?>" hidden='true' class="errorWrapper">
			<div id="errorMessageLeft_<?=htmlspecialcharsbx($arResult['ID']);?>" class="errorMessageLeft">
				<span id="errorMessage_<?=htmlspecialcharsbx($arResult['ID']);?>"></span>
				<button id="errorShowMore_<?=htmlspecialcharsbx($arResult['ID']);?>" data-l10n-id="error_more_info">
					More Information
				</button>
				<button id="errorShowLess_<?=htmlspecialcharsbx($arResult['ID']);?>" data-l10n-id="error_less_info" hidden='true'>
					Less Information
				</button>
			</div>
			<div id="errorMessageRight_<?=htmlspecialcharsbx($arResult['ID']);?>" class="errorMessageRight">
				<button id="errorClose_<?=htmlspecialcharsbx($arResult['ID']);?>" data-l10n-id="error_close">
					Close
				</button>
			</div>
			<div class="clearBoth"></div>
			<textarea id="errorMoreInfo_<?=htmlspecialcharsbx($arResult['ID']);?>" hidden='true' readonly="readonly" class="errorMoreInfo"></textarea>
		</div>
	</div> <!-- mainContainer -->

	<div id="overlayContainer_<?=htmlspecialcharsbx($arResult['ID']);?>" class="hidden overlayContainer">
		<div id="passwordOverlay_<?=htmlspecialcharsbx($arResult['ID']);?>" class="container hidden passwordOverlay">
			<div class="dialog">
				<div class="row">
					<p id="passwordText_<?=htmlspecialcharsbx($arResult['ID']);?>" data-l10n-id="password_label">Enter the password to open this PDF file:</p>
				</div>
				<div class="row">
					<input type="password" id="password_<?=htmlspecialcharsbx($arResult['ID']);?>" class="toolbarField" autocomplete="new-password">
				</div>
				<div class="buttonRow">
					<button id="passwordCancel_<?=htmlspecialcharsbx($arResult['ID']);?>" class="overlayButton"><span data-l10n-id="password_cancel">Cancel</span></button>
					<button id="passwordSubmit_<?=htmlspecialcharsbx($arResult['ID']);?>" class="overlayButton"><span data-l10n-id="password_ok">OK</span></button>
				</div>
			</div>
		</div>
		<div id="documentPropertiesOverlay_<?=htmlspecialcharsbx($arResult['ID']);?>" class="container hidden documentPropertiesOverlay">
			<div class="dialog">
				<div class="row">
					<span data-l10n-id="document_properties_file_name">File name:</span> <p id="fileNameField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="row">
					<span data-l10n-id="document_properties_file_size">File size:</span> <p id="fileSizeField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="separator"></div>
				<div class="row">
					<span data-l10n-id="document_properties_title">Title:</span> <p id="titleField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="row">
					<span data-l10n-id="document_properties_author">Author:</span> <p id="authorField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="row">
					<span data-l10n-id="document_properties_subject">Subject:</span> <p id="subjectField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="row">
					<span data-l10n-id="document_properties_keywords">Keywords:</span> <p id="keywordsField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="row">
					<span data-l10n-id="document_properties_creation_date">Creation Date:</span> <p id="creationDateField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="row">
					<span data-l10n-id="document_properties_modification_date">Modification Date:</span> <p id="modificationDateField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="row">
					<span data-l10n-id="document_properties_creator">Creator:</span> <p id="creatorField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="separator"></div>
				<div class="row">
					<span data-l10n-id="document_properties_producer">PDF Producer:</span> <p id="producerField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="row">
					<span data-l10n-id="document_properties_version">PDF Version:</span> <p id="versionField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="row">
					<span data-l10n-id="document_properties_page_count">Page Count:</span> <p id="pageCountField_<?=htmlspecialcharsbx($arResult['ID']);?>">-</p>
				</div>
				<div class="buttonRow">
					<button id="documentPropertiesClose_<?=htmlspecialcharsbx($arResult['ID']);?>" class="overlayButton"><span data-l10n-id="document_properties_close">Close</span></button>
				</div>
			</div>
		</div>
		<div id="printServiceOverlay_<?=htmlspecialcharsbx($arResult['ID']);?>" class="container hidden">
			<div class="dialog">
				<div class="row">
					<span data-l10n-id="print_progress_message">Preparing document for printing...</span>
				</div>
				<div class="row">
					<progress value="0" max="100"></progress>
					<span data-l10n-id="print_progress_percent" data-l10n-args='{ "progress": 0 }' class="relative-progress">0%</span>
				</div>
				<div class="buttonRow">
					<button id="printCancel_<?=htmlspecialcharsbx($arResult['ID']);?>" class="overlayButton"><span data-l10n-id="print_progress_close">Cancel</span></button>
				</div>
			</div>
		</div>
	</div>  <!-- overlayContainer -->

</div> <!-- outerContainer -->
<div class="printContainer" id="printContainer_<?=htmlspecialcharsbx($arResult['ID']);?>"></div>
<?
if($arResult['IFRAME'])
{?>
</body>
</html>
<?}
else
{?>
	</div>
<?}
