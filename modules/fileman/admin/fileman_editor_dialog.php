<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!check_bitrix_sessid())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

CJSCore::Init(array("admin_interface"));

define("FROMDIALOGS", true);
?>
<script>
var iNoOnSelectionChange = 1;
var iNoOnChange = 2;
</script>

<?if($name == "anchor"):?>
<script>
var pElement = null;
function OnLoad()
{
	pElement = pObj.pMainObj.GetSelectionObject();
	window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_LINK_TITLE")?>');

	var el = BX("anchor_value"), value = "";
	if (pElement)
	{
		var bxTag = pObj.pMainObj.GetBxTag(pElement);
		if (bxTag && bxTag.tag == "anchor")
			value = pObj.pMainObj.pParser.GetAnchorName(bxTag.params.value);
	}

	el.value = value;
	el.focus();
	window.oBXEditorDialog.adjustSizeEx();
}

function OnSave()
{
	BXSelectRange(oPrevRange, pObj.pMainObj.pEditorDocument, pObj.pMainObj.pEditorWindow);
	pElement = pObj.pMainObj.GetSelectionObject();
	pObj.pMainObj.bSkipChanges = true;
	var anchor_value = BX("anchor_value"), bxTag = false;

	if (pElement)
	{
		bxTag = pObj.pMainObj.GetBxTag(pElement);
		if (!bxTag || bxTag.tag != "anchor")
			pElement = false;
	}

	if(pElement && bxTag) // Modify or del anchor
	{
		if(anchor_value.value.length <= 0)
		{
			pObj.pMainObj.executeCommand('Delete');
		}
		else
		{
			bxTag.params.value = pObj.pMainObj.pParser.GetAnchorName(bxTag.params.value, anchor_value.value);
			pObj.pMainObj.SetBxTag(false, bxTag);
		}
	}
	else if(anchor_value.value.length > 0) // New anchor
	{
		var id = pObj.pMainObj.SetBxTag(false, {tag: "anchor", params: {value : '<a name="' + anchor_value.value + '"></a>'}});
		pObj.pMainObj.insertHTML('<img id="' + id + '" src="' + one_gif_src + '" class="bxed-anchor" />');

		var pEl = pObj.pMainObj.pEditorDocument.getElementById(id);
		if(pObj.pMainObj.pEditorWindow.getSelection)
			pObj.pMainObj.pEditorWindow.getSelection().selectAllChildren(pEl);
	}
	pObj.pMainObj.bSkipChanges = false;
	pObj.pMainObj.OnChange("anchor");
}
</script>
<?ob_start();?>
<div style="padding: 5px;">
<label for="anchor_value"><?= GetMessage("FILEMAN_ED_ANCHOR_NAME")?>&nbsp;</label><input type="text" size="25" value="" id="anchor_value" />
</div>
<?$dialogHTML = ob_get_contents(); ob_end_flush();?>

<?elseif($name == "editlink"):?>
<script>
var pElement = null;
var pImage = false;
var curLinkType = 't1';
function OnLoad()
{
	var bWasSelectedElement = false, bxTag = false;
	pElement = pObj.pMainObj.GetSelectionObject();

	if (pElement && pElement.nodeName && pElement.nodeName.toUpperCase() != 'A')
	{
		var nodeName = pElement.nodeName.toUpperCase();
		if (nodeName == 'IMG')
		{
			pImage = pElement;
			bWasSelectedElement = true;
		}
		pElement = BXFindParentByTagName(pElement, 'A');
	}

	if (pElement)
	{
		bxTag = pObj.pMainObj.GetBxTag(pElement);
		if (!bxTag || bxTag.tag != "a")
			bxTag = false;
	}

	// Set title
	window.oBXEditorDialog.SetTitle((pElement && bxTag) ? '<?=GetMessage("FILEMAN_ED_LE_TITLE")?>' : '<?=GetMessage("FILEMAN_ED_LN_TITLE")?>');

	if (BX("OpenFileBrowserWindLink_button"))
		BX("OpenFileBrowserWindLink_button").onclick = OpenFileBrowserWindFile;

	// Set styles
	var
		arStFilter = ['A', 'DEFAULT'], i, j,
		elStyles = BX("bx_classname"),
		arStyles;

	for(i = 0; i < arStFilter.length; i++)
	{
		arStyles = pObj.pMainObj.oStyles.GetStyles(arStFilter[i]);
		for(j = 0; j < arStyles.length; j++)
		{
			if(arStyles[j].className.length<=0)
				continue;
			oOption = new Option(arStyles[j].className, arStyles[j].className, false, false);
			elStyles.options.add(oOption);
		}
	}

	// Fetch anchors
	var
		pAnchorSelect = BX('bx_url_3'),
		i, l, anc, ancName, anchorBxTag,
		arImgs = pObj.pMainObj.pEditorDocument.getElementsByTagName('IMG');

	for(i = 0, l = arImgs.length; i < l; i++)
	{
		anchorBxTag = pObj.pMainObj.GetBxTag(arImgs[i]);
		if (anchorBxTag && anchorBxTag.tag == "anchor" && (ancName = pObj.pMainObj.pParser.GetAnchorName(anchorBxTag.params.value)))
			pAnchorSelect.options.add(new Option(ancName, '#' + ancName, false, false));
	}

	if (pAnchorSelect.options.length <= 0)
	{
		pAnchorSelect.options.add(new Option('<?= GetMessage("FILEMAN_ED_NOANCHORS")?>', '', true, true));
		pAnchorSelect.disabled = true;
	}

	if (BX.browser.IsIE())
		pAnchorSelect.style.width = "220px";

	var tip = pObj.pMainObj._dialogLinkTip || "t1";
	var selectedText = false;
	if(pElement && bxTag) /* Link selected*/
	{
		oPrevRange = pObj.pMainObj.SelectElement(pElement);
		if (pElement.childNodes && pElement.childNodes.length == 1 && pElement.childNodes[0].nodeType == 3)
			selectedText = pElement.innerHTML;

		//var href = pElement.getAttribute("href", 2), el;
		var href = bxTag.params.href;
		if(href.substring(0, 7).toLowerCase() == 'mailto:') // email
		{
			tip = "t4";
			BX("bx_url_4").value = href.substring('mailto:'.length);
		}
		else if(href.substr(0, 1) == '#') // anchor
		{
			BX("bx_url_3").value = href;
			if(BX("bx_url_3").value == href)
			{
				tip = "t3";
			}
			else
			{
				tip = "t1";
				BX("bx_url_1").value = href;
			}
		}
		else if (href.indexOf("://") !== -1 || href.substr(0, 'www.'.length) == 'www.' || href.indexOf("&goto=") !== -1)
		{
			tip = "t2";
			// Fix link in statistic
			if(href.substr(0, '/bitrix/redirect.php'.length) == '/bitrix/redirect.php')
			{
				BX("bx_fixstat").checked = true;
				ChangeFixStat();
				var sParams = href.substring('/bitrix/redirect.php'.length);

				var __ExtrParam = function (p, s)
				{
					var pos = s.indexOf(p + '=');
					if(pos < 0)
						return '';
					var pos2 = s.indexOf('&', pos + p.length+1);
					if(pos2 < 0)
						s = s.substring(pos + p.length + 1);
					else
						s = s.substr(pos+p.length+1, pos2 - pos - 1 - p.length);
					return unescape(s);
				};

				BX("event1").value = __ExtrParam('event1', sParams);
				BX("event2").value = __ExtrParam('event2', sParams);
				BX("event3").value = __ExtrParam('event3', sParams);

				href = __ExtrParam('goto', sParams);
			}

			if (href.substr(0, 'www.'.length) == 'www.')
				href = "http://" + href;

			var sProt = href.substr(0, href.indexOf("://") + 3);

			BX("bx_url_type").value = sProt;
			if (BX("bx_url_type").value != sProt)
				BX("bx_url_type").value = '';

			BX("bx_url_2").value = href.substring(href.indexOf("://") + 3);
		}
		else // link to page on server
		{
			tip = "t1";
			BX("bx_url_1").value = href;
		}

		var className = pElement.className;
		if(className)
		{
			var pClassSel = BX("bx_classname");
			pClassSel.value = className;
			if (pClassSel.value != className) // Add class to select if it's not exsist here
				pClassSel.options.add(new Option(className, className, true, true));
		}

		BX("bx_targ_list").value = bxTag.params.target || '';
		BX("__bx_id").value = bxTag.params.id || '';
		BX("BXEditorDialog_title").value = bxTag.params.title || '';

		var rel = bxTag.params.rel || '';
		if (bxTag.params.noindex || rel == 'nofollow')
		{
			BX("bx_noindex").checked = true;
			BX("bx_link_rel").disabled = true;
		}

		if (rel)
			BX("bx_link_rel").value = rel;
	}
	else if (!bWasSelectedElement)/* NO selected link*/
	{
		// Get selected text
		if (oPrevRange.startContainer && oPrevRange.endContainer) // DOM Model
		{
			if (oPrevRange.startContainer == oPrevRange.endContainer && (oPrevRange.endContainer.nodeType == 3 || oPrevRange.endContainer.nodeType == 1))
			{
				selectedText = oPrevRange.startContainer.textContent.substring(oPrevRange.startOffset, oPrevRange.endOffset) || '';
			}
		}
		else // IE
		{
			if (oPrevRange.text == oPrevRange.htmlText)
				selectedText = oPrevRange.text || '';
		}
	}

	if (selectedText === false)
		BX('bx_link_text_tr').style.display = "none";
	else
		BX('bx_link_text').value = selectedText || '';

	BX('bx_link_type').value = tip;
	ChangeLinkType();
}

function OnSave()
{
	var
		href='',
		target='',
		bText = (BX('bx_link_text_tr').style.display !== 'none');

	switch(BX('bx_link_type').value)
	{
		case 't1':
			href = BX('bx_url_1').value;
			break;
		case 't2':
			href = BX('bx_url_2').value;

			if (BX("bx_url_type").value && href.indexOf('://') == -1)
				href = BX("bx_url_type").value + href;

			if(BX("bx_fixstat").checked)
				href = '/bitrix/redirect.php?event1=' + escape(BX("event1").value) + '&event2=' + escape(BX("event2").value) + '&event3=' + escape(BX("event3").value) + '&goto=' + escape(href);
			break;
		case 't3':
			href = BX('bx_url_3').value;
			break;
		case 't4':
			if(BX('bx_url_4').value)
				href = 'mailto:' + BX('bx_url_4').value;
			break;
	}

	BXSelectRange(oPrevRange, pObj.pMainObj.pEditorDocument, pObj.pMainObj.pEditorWindow);
	pObj.pMainObj.bSkipChanges = true;

	if(href.length > 0)
	{
		var arlinks = [];
		if (window.pElement)
		{
			arlinks.push(pElement);
		}
		else if(window.pImage && window.pImage.parentNode) // Link around image
		{
			var plink = BX.create("A", {}, pObj.pMainObj.pEditorDocument);
			window.pImage.parentNode.insertBefore(plink, window.pImage);
			plink.appendChild(window.pImage);
			arlinks.push(plink);
		}
		else
		{
			var sRand = '#'+Math.random().toString().substring(5);
			if (bText) // Simple case
			{
				pObj.pMainObj.insertHTML('<a id="bx_lhe_' + sRand + '">#</a>');
				arlinks[0] = pObj.pMainObj.pEditorDocument.getElementById('bx_lhe_' + sRand);
				arlinks[0].removeAttribute("id");
			}
			else
			{
				pObj.pMainObj.pEditorDocument.execCommand('CreateLink', false, sRand);
				var arLinks_ = pObj.pMainObj.pEditorDocument.getElementsByTagName('A');
				for(var i = 0; i < arLinks_.length; i++)
					if(arLinks_[i].getAttribute('href', 2) == sRand)
						arlinks.push(arLinks_[i]);
			}
		}

		var oTag, i, l = arlinks.length, link;
		for (i = 0;  i < l; i++)
		{
			link = arlinks[i];
			oTag = false;

			if (window.pElement && i == 0)
			{
				oTag = pObj.pMainObj.GetBxTag(pElement);
				if (oTag.tag != 'a' || !oTag.params)
					oTag = false;
			}

			if (!oTag)
				oTag = {tag: 'a', params: {}};

			oTag.params.href = href;
			oTag.params.title = BX("BXEditorDialog_title").value;
			oTag.params.id = BX("__bx_id").value;
			oTag.params.target = BX("bx_targ_list").value;
			oTag.params.noindex = !!BX("bx_noindex").checked;
			oTag.params.rel = BX("bx_link_rel").value;

			var arEls = ['href', 'title', 'id', 'rel', 'target'], i, l = arEls.length;
			for (i = 0; i < l; i++)
				if (!pObj.pMainObj.pParser.isPhpAttribute(oTag.params[arEls[i]]))
					SAttr(link, arEls[i], oTag.params[arEls[i]]);

			pObj.pMainObj.SetBxTag(link, oTag);
			SAttr(link, 'className', BX("bx_classname").value);

			// Add text
			if (bText)
				link.innerHTML = BX.util.htmlspecialchars(BX('bx_link_text').value || href);
		}
	}

	pObj.pMainObj.bSkipChanges = false;
	pObj.pMainObj.OnChange("link");
}

function showAddSect()
{
	var pCont = BX('bx_link_dialog_tbl').parentNode;
	var bShow = pCont.className.indexOf('bx-link-simple') == -1;

	if (bShow)
		BX.addClass(pCont, 'bx-link-simple');
	else
		BX.removeClass(pCont, 'bx-link-simple');

	window.oBXEditorDialog.adjustSizeEx();
}

function ChangeLinkType()
{
	var
		pTbl = BX('bx_link_dialog_tbl'),
		val = BX('bx_link_type').value;

	if (curLinkType == 't1' && val == 't2')
	{
		var url1 = BX('bx_url_1').value;
		if (url1 != '' && url1.indexOf('://') != -1)
		{
			BX('bx_url_2').value = url1.substr(url1.indexOf('://') + 3);
			BX('bx_url_type').value = url1.substr(0, url1.indexOf('://') + 3);
		}
	}
	curLinkType = val;
	pObj.pMainObj._dialogLinkTip = val;

	var pUrl = BX('bx_url_' + val.substr(1));
	if(pUrl && !pUrl.disabled)
		setTimeout(function(){pUrl.focus();}, 300);

	pTbl.className = ("bx-link-dialog-tbl bx--t1 bx--t2 bx--t3 bx--t4 bx-only-" + val).replace(' bx--' + val, '');
	window.oBXEditorDialog.adjustSizeEx();
}

function ChangeFixStat()
{
	var bFix = BX("bx_fixstat").checked;
	BX("bx_fixstat_div").style.display = bFix ? 'block' : 'none';
	BX("event1").disabled = BX("event2").disabled = BX("event3").disabled = !bFix;
	window.oBXEditorDialog.adjustSizeEx();
}

function SetUrl(filename, path, site)
{
	var
		url,
		pInput = BX("bx_url_1"),
		pText = BX("bx_link_text"),
		pTitle = BX("BXEditorDialog_title");
	if (typeof filename == 'object') // Using medialibrary
	{
		url = filename.src;
		if (pText.value == '')
			pText.value = filename.description || filename.name;
		pTitle.value = filename.description || filename.name;
	}
	else // Using file dialog
	{
		url = (path == '/' ? '' : path) + '/' + filename;
	}

	pInput.value = url;
	pInput.focus();
	pInput.select();
}
</script>

<?ob_start();?>

<table class="bx-link-dialog-tbl bx--t1 bx--t2 bx--t3 bx--t4" id="bx_link_dialog_tbl">
	<tr class="bx-link-type">
		<td class="bx-par-title"><label for="bx_link_type"><?= GetMessage("FILEMAN_ED_LINK_TYPE")?></label></td>
		<td class="bx-par-val">
			<select id='bx_link_type' onchange="ChangeLinkType();">
				<option value='t1'><?= GetMessage("FILEMAN_ED_LINK_TYPE1")?></option>
				<option value='t2'><?= GetMessage("FILEMAN_ED_LINK_TYPE2")?></option>
				<option value='t3'><?= GetMessage("FILEMAN_ED_LINK_TYPE3")?></option>
				<option value='t4'><?= GetMessage("FILEMAN_ED_LINK_TYPE4")?></option>
			</select>
		</td>
	</tr>

	<tr><td colSpan="2" class="bx-link-sep"></td></tr>

	<tr id="bx_link_text_tr">
		<td class="bx-par-title"><label for="bx_link_text"><?= GetMessage("FILEMAN_LINK_TEXT")?>:</label></td>
		<td class="bx-par-val"><input type="text" size="30" value="" id="bx_link_text" /></td>
	</tr>

	<tr class="bx-link-t1">
		<td class="bx-par-title"><label for="bx_url_1"><?= GetMessage("FILEMAN_ED_LINK_DOC")?>:</label></td>
		<td class="bx-par-val">
			<input type="text" size="30" value="" id="bx_url_1" style="float: left;">
			<?
			CMedialib::ShowBrowseButton(
				array(
					'value' => '...',
					'event' => 'OpenFileBrowserWindFile',
					'id' => 'OpenFileBrowserWindLink_button',
					'MedialibConfig' => array("arResultDest" => Array("FUNCTION_NAME" => "SetUrl")),
					'useMLDefault' => false
				)
			);
			?>
		</td>
	</tr>

	<!-- Link to external site -->
	<tr class="bx-link-t2">
		<td class="bx-par-title"><label for="bx_url_2"><?= GetMessage("FILEMAN_ED_LINK_DOC")?>:</label></td>
		<td class="bx-par-val">
			<select id='bx_url_type' style="vertical-align: top; margin-top: 1px;">
				<option value="http://">http://</option>
				<option value="ftp://">ftp://</option>
				<option value="https://">https://</option>
				<option value=""></option>
			</select>
			<input type="text" size="25" value="" id="bx_url_2">
		</td>
	</tr>

	<tr class="bx-link-t2">
		<td style="text-align: right; vertical-align: top;"><input type="checkbox" id="bx_fixstat" value="" onclick="ChangeFixStat();"></td>
		<td>
			<label for="bx_fixstat" style="display: block; margin-top: 3px;"><?= GetMessage("FILEMAN_ED_LINK_STAT")?></label>
			<div id="bx_fixstat_div" style="margin: 8px 5px; display: none;">
				<label for="event1">Event1:</label> <input type="event1" id="event1" size="10" value=""><br/>
				<label for="event2">Event2:</label> <input type="event2" id="event2" size="10" value=""><br/>
				<label for="event3">Event3:</label> <input type="event3" id="event3" size="10" value=""><br/>
			</div>
		</td>
	</tr>

	<!-- anchor -->
	<tr class="bx-link-t3">
		<td class="bx-par-title"><label for="bx_url_3"><?= GetMessage("FILEMAN_ED_LINK_ACH")?></label></td>
		<td class="bx-par-val">
			<select id="bx_url_3" style="max-width: 240px;"></select>
		</td>
	</tr>

	<!-- email -->
	<tr class="bx-link-t4">
		<td class="bx-par-title"><label for="bx_url_4">EMail:</label></td>
		<td class="bx-par-val">
			<input type="text" size="30" value="" id="bx_url_4">
		</td>
	</tr>

	<tr class="bx-header"><td colSpan="2"><a  class="bx-adv-link" onclick="showAddSect(); return false;" href="javascript: void(0);"><?= GetMessage("FILEMAN_ED_ADDITIONAL")?> <span>(<?= GetMessage("FILEMAN_ED_HIDE")?>)</span></a></td></tr>

	<tr id="bx_target_row" class="bx-adv bx-hide-in-t3 bx-hide-in-t4">
		<td class="bx-par-title"><label for="bx_targ_list"><?= GetMessage("FILEMAN_ED_LINK_WIN")?>:</label></td>
		<td class="bx-par-val">
			<select id='bx_targ_list'>
				<option value=""> - <?= GetMessage("FILEMAN_NO_VAL")?> -</option>
				<option value="_blank"><?= GetMessage("FILEMAN_ED_LINK_WIN_BLANK")?></option>
				<option value="_parent"><?= GetMessage("FILEMAN_ED_LINK_WIN_PARENT")?></option>
				<option value="_self"><?= GetMessage("FILEMAN_ED_LINK_WIN_SELF")?></option>
				<option value="_top"><?= GetMessage("FILEMAN_ED_LINK_WIN_TOP")?></option>
			</select>
		</td>
	</tr>
	<tr class="bx-adv bx-hide-in-t3 bx-hide-in-t4">
		<td class="bx-par-title"><input type="checkbox" value="Y" id="bx_noindex" onclick="var rel = BX('bx_link_rel'); if (this.checked){rel.value='nofollow'; rel.disabled=true;}else{rel.disabled=false;rel.value='';}" /></td>
		<td class="bx-par-val"><label for="bx_noindex"><?= GetMessage("FILEMAN_ED_LINK_NOINDEX")?></label></td>
	</tr>
	<tr class="bx-adv">
		<td class="bx-par-title"><label for="BXEditorDialog_title"><?= GetMessage("FILEMAN_ED_LINK_ATITLE")?></label></td>
		<td class="bx-par-val">
			<input type="text" size="30" value="" id="BXEditorDialog_title">
		</td>
	</tr>
	<tr class="bx-adv">
		<td class="bx-par-title"><label for="bx_classname"><?= GetMessage("FILEMAN_ED_STYLE")?>:</label></td>
		<td class="bx-par-val">
			<select id='bx_classname'><option value=""> - <?= GetMessage("FILEMAN_NO_VAL")?> -</option></select>
		</td>
	</tr>
	<tr class="bx-adv">
		<td class="bx-par-title"><label for="__bx_id">ID:</label></td>
		<td class="bx-par-val"><input type="text" size="30" value="" id="__bx_id" /></td>
	</tr>
	<tr class="bx-adv">
		<td class="bx-par-title"><label for="bx_link_rel"><?= GetMessage("FILEMAN_REL")?>:</label></td>
		<td class="bx-par-val"><input type="text" size="30" value="" id="bx_link_rel" /></td>
	</tr>
</table>

<?$dialogHTML = ob_get_contents(); ob_end_flush();?>

<?
CAdminFileDialog::ShowScript(Array
	(
		"event" => "OpenFileBrowserWindFile",
		"arResultDest" => Array("FUNCTION_NAME" => "SetUrl"),
		"arPath" => Array("SITE" => $_GET["site"]),
		"select" => 'F',
		"operation" => 'O',
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'php, html',
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
?>

<?elseif($name == "image"):?>
<script>
var pElement = null;
function OnLoad(params)
{
	if (params && params.pElement)
		pElement = params.pElement;
	else
		pElement = pObj.pMainObj.GetSelectionObject();

	var
		bxTag = false,
		preview = BX("bx_img_preview"),
		pWidth = BX("bx_width"),
		pHeight = BX("bx_height");

	preview.onload = PreviewOnLoad;

	if (pElement)
	{
		bxTag = pObj.pMainObj.GetBxTag(pElement);
		if (!bxTag || bxTag.tag != "img")
			bxTag = false;
	}

	if(!pElement || !bxTag)
	{
		pElement = null;
		window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_NEW_IMG")?>');
	}
	else
	{
		var w = parseInt(pElement.style.width || pElement.getAttribute('width') || pElement.offsetWidth);
		var h = parseInt(pElement.style.height || pElement.getAttribute('height') || pElement.offsetHeight);
		if (w && h)
		{
			pObj.iRatio = w / h; // Remember proportion
			pObj.curWidth = pWidth.value = w;
			pObj.curHeight = pHeight.value = h;
		}

		window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_EDIT_IMG")?>');

		BX("bx_src").value = bxTag.params.src || "";
		BX("bx_img_title").value = bxTag.params.title || "";
		BX("bx_alt").value = bxTag.params.alt || "";
		BX("bx_border").value = bxTag.params.border || "";
		BX("bx_align").value = bxTag.params.align || "";
		BX("bx_hspace").value = bxTag.params.hspace || "";
		BX("bx_vspace").value = bxTag.params.vspace || "";

		preview.style.display = "";
		pObj.prevsrc = preview.src = BX("bx_src").value;
		preview.alt = BX("bx_alt").value;
		preview.border = BX("bx_border").value;
		preview.align = BX("bx_align").value;
		preview.hspace = BX("bx_hspace").value;
		preview.vspace = BX("bx_vspace").value;

		preview.onload = function(){PreviewReload(); preview.onload = PreviewOnLoad;};
	}

	if (BX("OpenFileBrowserWindImage_button"))
		BX("OpenFileBrowserWindImage_button").onclick = OpenFileBrowserWindImage;

	BX("bx_src").onchange = BX("bx_hspace").onchange =
	BX("bx_vspace").onchange = BX("bx_border").onchange =
	BX("bx_align").onchange = PreviewReload;

	var pSaveProp = BX("save_props");
	pSaveProp.onclick = function()
	{
		if (this.checked)
			pWidth.onchange();
	};

	pWidth.onchange = function()
	{
		var w = parseInt(this.value);
		if (isNaN(w))
			return;
		pObj.curWidth = pWidth.value = w;
		if (pSaveProp.checked)
		{
			var h = Math.round(w / pObj.iRatio);
			pObj.curHeight = pHeight.value = h;
		}
		PreviewReload();
	};

	pHeight.onchange = function()
	{
		var h = parseInt(this.value);
		if (isNaN(h))
			return;
		pObj.curHeight = pHeight.value = h;
		if (pSaveProp.checked)
		{
			var w = parseInt(h * pObj.iRatio);
			pObj.curWidth = pWidth.value = w;
		}
		PreviewReload();
	};

	window.oBXEditorDialog.adjustSizeEx();
}

function OnSave()
{
	pObj.pMainObj.bSkipChanges = true;
	var
		src = BX("bx_src").value,
		oTag = false;

	if (!src)
		return;

	if (window.pElement)
	{
		oTag = pObj.pMainObj.GetBxTag(pElement);
		if (oTag.tag != 'img' || !oTag.params)
			oTag = false;
	}

	if (!oTag)
	{
		oTag = {tag: 'img', params: {}};
		BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
		pObj.pMainObj.insertHTML('<img id="__bx_img_temp_id" src="" />');
		pElement = pObj.pMainObj.pEditorDocument.getElementById("__bx_img_temp_id");
	}

	oTag.params.src = src;
	oTag.params.title = BX("bx_img_title").value;
	oTag.params.hspace = BX("bx_hspace").value;
	oTag.params.vspace = BX("bx_vspace").value;
	oTag.params.border = BX("bx_border").value;
	oTag.params.align = BX("bx_align").value;
	oTag.params.alt = BX("bx_alt").value;

	var arEls = ['src', 'alt', 'title', 'hspace', 'vspace', 'border', 'align'], i, l = arEls.length;
	for (i = 0; i < l; i++)
	{
		if (!pObj.pMainObj.pParser.isPhpAttribute(oTag.params[arEls[i]]))
			SAttr(pElement, arEls[i], oTag.params[arEls[i]]);
	}

	pElement.id = '';
	pElement.removeAttribute('id');
	pObj.pMainObj.SetBxTag(pElement, oTag);

	SAttr(pElement, "width", BX("bx_width").value);
	SAttr(pElement, "height", BX("bx_height").value);

	pObj.pMainObj.bSkipChanges = false;
	pObj.pMainObj.OnChange("image");
}

function PreviewOnLoad()
{
	var w = parseInt(this.style.width || this.getAttribute('width') || this.offsetWidth);
	var h = parseInt(this.style.height || this.getAttribute('hright') || this.offsetHeight);
	if (!w || !h)
		return;
	pObj.iRatio = w / h; // Remember proportion
	pObj.curWidth = BX("bx_width").value = w;
	pObj.curHeight = BX("bx_height").value = h;
};

function PreviewReload(bFirst)
{
	var el = BX("bx_img_preview");
	if(pObj.prevsrc != BX("bx_src").value)
	{
		el.style.display="";
		el.removeAttribute("width");
		el.removeAttribute("height");
		pObj.prevsrc = BX("bx_src").value;
		el.src=BX("bx_src").value;
	}

	if (pObj.curWidth && pObj.curHeight)
	{
		el.style.width = pObj.curWidth + 'px';
		el.style.height = pObj.curHeight + 'px';
	}

	el.alt = BX("bx_alt").value;
	el.title = BX("bx_img_title").value;
	el.border = BX("bx_border").value;
	el.align = BX("bx_align").value;
	el.hspace = BX("bx_hspace").value;
	el.vspace = BX("bx_vspace").value;
}

function SetUrl(filename, path, site)
{
	var url, srcInput = BX("bx_src");

	if (typeof filename == 'object') // Using medialibrary
	{
		url = filename.src;
		var pTitle = BX("bx_img_title");
		if (pTitle.value == '')
			pTitle.value = filename.description || filename.name;
		BX("bx_alt").value = filename.description || filename.name;
	}
	else // Using file dialog
	{
		url = (path == '/' ? '' : path) + '/'+filename;
	}

	srcInput.value = url;
	if(srcInput.onchange)
		srcInput.onchange();
	srcInput.focus();
	srcInput.select();
}
</script>

<?
CAdminFileDialog::ShowScript(Array
	(
		"event" => "OpenFileBrowserWindImage",
		"arResultDest" => Array("FUNCTION_NAME" => "SetUrl"),
		"arPath" => Array("SITE" => $_GET["site"], "PATH" =>($str_FILENAME <> '' ? GetDirPath($str_FILENAME) : '')),
		"select" => 'F',// F - file only, D - folder only
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'image',//'' - don't shjow select, 'image' - only images; "ext1,ext2" - Only files with ext1 and ext2 extentions;
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
?>

<?ob_start();?>
<table class="bx-image-dialog-tbl">
	<tr>
		<td class="bx-par-title"><label for="bx_src"><?= GetMessage("FILEMAN_ED_IMG_PATH")?></label></td>
		<td class="bx-par-val">
			<input type="text" size="25" value="" id="bx_src" style="float: left;" />
			<?
			CMedialib::ShowBrowseButton(
				array(
					'value' => '...',
					'event' => 'OpenFileBrowserWindImage',
					'id' => 'OpenFileBrowserWindImage_button',
					'MedialibConfig' => array(
						"arResultDest" => Array("FUNCTION_NAME" => "SetUrl"),
						"types" => array('image')
					)
				)
			);
			?>
		</td>
	</tr>
	<tr>
		<td class="bx-par-title"><label for="bx_img_title"><?= GetMessage("FILEMAN_ED_IMG_TITLE")?></label></td>
		<td class="bx-par-val"><input type="text" size="30" value="" id="bx_img_title" /></td>
	</tr>
	<tr>
		<td class="bx-par-title"><label for="bx_width"><?= GetMessage("FILEMAN_SIZES")?>:</label></td>
		<td class="bx-par-val">
		<input type="text" size="4" id="bx_width" /> x <input type="text" size="4" id="bx_height" />
		<input type="checkbox" value="Y" checked="checked" id="save_props" /> <label for="save_props"><?= GetMessage("FILEMAN_SAVE_PROPORTIONS")?></label>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<table class="bx-img-side">
				<tr>
					<td><label for="bx_hspace"><?= GetMessage("FILEMAN_ED_IMG_ALT")?></label>
					<br />
					<input type="text" size="20" value="" id="bx_alt" />
					</td>
				</tr>
				<tr>
					<td><label for="bx_align"><?= GetMessage("FILEMAN_ED_IMG_AL")?></label>
					<br />
					<select id="bx_align">
						<option value=""> - <?= GetMessage("FILEMAN_NO_VAL")?> -</option>
						<option value="top"><?= GetMessage("FILEMAN_ALIGN_TOP")?></option>
						<option value="bottom"><?= GetMessage("FILEMAN_ALIGN_BOTTOM")?></option>
						<option value="left"><?= GetMessage("FILEMAN_ALIGN_LEFT")?></option>
						<option value="middle"><?= GetMessage("FILEMAN_ALIGN_MIDDLE")?></option>
						<option value="right"><?= GetMessage("FILEMAN_ALIGN_RIGHT")?></option>
					</select>
					</td>
				</tr>
				<tr>
					<td><label for="bx_hspace"><?= GetMessage("FILEMAN_ED_IMG_HSp")?></label>
					<br />
					<input type="text" id="bx_hspace" size="10">px</td>
				</tr>
				<tr>
					<td><label for="bx_vspace"><?= GetMessage("FILEMAN_ED_IMG_HVp")?></label>
					<br />
					<input type="text" id="bx_vspace" size="10">px</td>
				</tr>
				<tr>
					<td><label for="bx_border"><?= GetMessage("FILEMAN_ED_IMG_BORD")?></label>
					<br />
					<input type="text" id="bx_border" size="10" value="0">px</td>
				</tr>
			</table>
		</td>
		<td valign="top" style="padding-top: 2px;"><?= GetMessage("FILEMAN_ED_IMG_PREV")?>
		<div class="bx-preview"><img id="bx_img_preview" style="display:none"/><?= str_repeat('text ', 200)?></div>
		</td>
	</tr>
</table>
<?$dialogHTML = ob_get_contents(); ob_end_flush();?>

<?elseif($name == "table"):?>
<script>
var pElement = null;
function OnLoad()
{
	if(pObj.params.check_exists)
	{
		window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_TABLE_PROP")?>');
		pElement = BXFindParentByTagName(pObj.pMainObj.GetSelectionObject(), 'TABLE');
	}
	else
	{
		window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_NEW_TABLE")?>');
	}

	var
		arStFilter = ['TABLE', 'DEFAULT'], i, arStyles, j,
		elStyles = BX("bx_classname");

	for(i = 0; i < arStFilter.length; i++)
	{
		arStyles = pObj.pMainObj.oStyles.GetStyles(arStFilter[i]);
		for(j = 0; j < arStyles.length; j++)
		{
			if(arStyles[j].className != "")
				elStyles.options.add(new Option(arStyles[j].className, arStyles[j].className, false, false));
		}
	}

	if(pElement)
	{
		BX("rows").value=pElement.rows.length;
		BX("rows").disabled = true;
		BX("cols").value=pElement.rows[0].cells.length;
		BX("cols").disabled = true;
		BX("cellpadding").value = GAttr(pElement, "cellPadding");
		BX("cellspacing").value = GAttr(pElement, "cellSpacing");
		BX("bx_border").value = GAttr(pElement, "border");
		BX("bx_align").value = GAttr(pElement, "align");
		BX("bx_classname").value = GAttr(pElement, "className");
		var v = GAttr(pElement, "width");

		if(v.substr(-1, 1) == "%")
		{
			BX("bx_width").value = v.substr(0, v.length-1);
			BX("width_unit").value = "%";
		}
		else
		{
			if(v.substr(-2, 2) == "px")
				v = v.substr(0, v.length-2);

			BX("bx_width").value = v
		}

		v = GAttr(pElement, "height");
		if(v.substr(-1, 1) == "%")
		{
			BX("bx_height").value = v.substr(0, v.length-1);
			BX("height_unit").value = "%";
		}
		else
		{
			if(v.substr(-1, 2) == "px")
				v = v.substr(0, v.length-2);

			BX("bx_height").value = v
		}
	}
	else
	{
		BX("rows").value="2";
		BX("cols").value="3";
		BX("cellpadding").value="1";
		BX("cellspacing").value="1";
		BX("bx_border").value="0";
	}

	window.oBXEditorDialog.adjustSizeEx();
}

function OnSave()
{
	pObj.pMainObj.bSkipChanges = true;
	if(!pElement)
	{
		var tmpid = Math.random().toString().substring(2);
		var str = '<table id="'+tmpid+'"/><br/>';
		BXSelectRange(oPrevRange, pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
		pObj.pMainObj.insertHTML(str);

		pElement = pObj.pMainObj.pEditorDocument.getElementById(tmpid);
		pElement.removeAttribute("id");

		var i, j, row, cell;
		for(i=0; i < BX("rows").value; i++)
		{
			row = pElement.insertRow(-1);
			for(j = 0; j < BX("cols").value; j++)
			{
				cell = row.insertCell(-1);
				cell.innerHTML = '<br _moz_editor_bogus_node="on">';
			}
		}
	}
	else
	{
		if(pObj.pMainObj.bTableBorder)
			pObj.pMainObj.__ShowTableBorder(pElement, false);
	}

	SAttr(pElement, "width", (BX("bx_width").value.length>0?BX("bx_width").value+''+(BX("width_unit").value=='%'?'%':''):''));
	SAttr(pElement, "height", (BX("bx_height").value.length>0?BX("bx_height").value+''+(BX("height_unit").value=='%'?'%':''):''));
	SAttr(pElement, "border", BX("bx_border").value);
	SAttr(pElement, "cellPadding", BX("cellpadding").value);
	SAttr(pElement, "cellSpacing", BX("cellspacing").value);
	SAttr(pElement, "align", BX("bx_align").value);
	SAttr(pElement, 'className', BX("bx_classname").value);

	pObj.pMainObj.OnChange("table");

	if(pObj.pMainObj.bTableBorder)
		pObj.pMainObj.__ShowTableBorder(pElement, true);
}

</script>
<?ob_start();?>
<table class="bx-dialog-table">
	<tr>
		<td align="right"><label for="rows"><?= GetMessage("FILEMAN_ED_TBL_R")?></label></td>
		<td><input type="text" size="3" id="rows"></td>
		<td>&nbsp;</td>
		<td align="right"><label for="bx_width"><?= GetMessage("FILEMAN_ED_TBL_W")?></label></td>
		<td nowrap><input type="text" size="3" id="bx_width"><select id="width_unit"><option value="px">px</option><option value="%">%</option></select></td>
	</tr>
	<tr>
		<td align="right"><label for="cols"><?= GetMessage("FILEMAN_ED_TBL_COL")?></label></td>
		<td><input type="text" size="3" id="cols"></td>
		<td>&nbsp;</td>
		<td align="right"><label for="bx_height"><?= GetMessage("FILEMAN_ED_TBL_H")?></label></td>
		<td nowrap><input type="text" size="3" id="bx_height"><select id="height_unit"><option value="px">px</option><option value="%">%</option></td>
	</tr>
	<tr>
		<td colspan="5">&nbsp;</td>
	</tr>
	<tr>
		<td align="right" nowrap><label for="bx_border"><?= GetMessage("FILEMAN_ED_IMG_BORD")?></label></td>
		<td><input type="text" id="bx_border" size="3"></td>
		<td>&nbsp;</td>
		<td align="right" nowrap><label for="cellpadding">Cell padding:</label></td>
		<td><input type="text" id="cellpadding" size="3"></td>
	</tr>
	<tr>
		<td align="right"><label for="bx_align"><?= GetMessage("FILEMAN_ED_TBL_AL")?></label></td>
		<td>
			<select id="bx_align">
				<option value=""></option>
				<option value="left"><?= GetMessage("FILEMAN_ALIGN_LEFT")?></option>
				<option value="center"><?= GetMessage("FILEMAN_ALIGN_MIDDLE")?></option>
				<option value="right"><?= GetMessage("FILEMAN_ALIGN_RIGHT")?></option>
			</select>
		</td>
		<td>&nbsp;</td>
		<td align="right" nowrap><label for="cellspacing">Cell spacing:</label></td>
		<td><input type="text" id="cellspacing" size="3"></td>
	</tr>
	<tr>
		<td align="right"><label for="bx_classname"><?= GetMessage("FILEMAN_ED_STYLE")?>:</label></td>
		<td colspan="4"><select id='bx_classname'><option value=""> - <?= GetMessage("FILEMAN_NO_VAL")?> -</option></select></td>
	</tr>
</table>
<?$dialogHTML = ob_get_contents(); ob_end_flush();?>

<?elseif($name == "pasteastext"):?>
<script>
function OnLoad()
{
	window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_PASTE_TEXT")?>');
	BX("BXInsertAsText").focus();

	window.oBXEditorDialog.adjustSizeEx();
}

function OnSave()
{
	BXSelectRange(oPrevRange, pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
	pObj.pMainObj.PasteAsText(BX("BXInsertAsText").value);
}
</script>
<?ob_start();?>
<table style="width: 100%;">
	<tr>
		<td><?= GetMessage("FILEMAN_ED_FF")?> "<?= GetMessage("FILEMAN_ED_SAVE")?>":</td>
	</tr>
	<tr><td>
		<textarea id="BXInsertAsText" style="width:100%; height:200px;"></textarea>
	</td></tr>
</table>
<?$dialogHTML = ob_get_contents(); ob_end_flush();?>

<?elseif($name == "pasteword"):?>
<script>
var pFrame = null;
function OnLoad()
{
	window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_PASTE_WORD")?>');
	pFrame = BX("bx_word_text");

	if(pFrame.contentDocument)
		pFrame.pDocument = pFrame.contentDocument;
	else
		pFrame.pDocument = pFrame.contentWindow.document;
	pFrame.pWindow = pFrame.contentWindow;

	pFrame.pDocument.open();
	pFrame.pDocument.write('<html><head><style>BODY{margin:0px; padding:0px; border:0px;}</style></head><body></body></html>');
	pFrame.pDocument.close();

	if(pFrame.pDocument.addEventListener)
		pFrame.pDocument.addEventListener('keydown', dialog_OnKeyDown, false);
	else if (pFrame.pDocument.attachEvent)
		pFrame.pDocument.body.attachEvent('onpaste', dialog_OnPaste);

	if(BX.browser.IsIE())
	{
		BX("bx_word_ff").style.display = 'none';
		pFrame.pDocument.body.contentEditable = true;
		pFrame.pDocument.body.innerHTML = pObj.pMainObj.GetClipboardHTML();
		dialog_OnPaste();
	}
	else
		pFrame.pDocument.designMode='on';

	setTimeout(function()
	{
		var
			wnd = pFrame.contentWindow,
			doc = pFrame.contentDocument || pFrame.contentWindow.document;
		if(wnd.focus)
			wnd.focus();
		else
			doc.body.focus();
	},
	10);

	//attaching events
	BX("bx_word_removeFonts").onclick =
	BX("bx_word_removeStyles").onclick =
	BX("bx_word_removeIndents").onclick =
	BX("bx_word_removeSpaces").onclick =
	BX("bx_word_removeTableAtr").onclick =
	BX("bx_word_removeTrTdAtr").onclick =
	dialog_cleanAndShow;

	window.oBXEditorDialog.adjustSizeEx();
}

function dialog_OnKeyDown(e)
{
	if (e.ctrlKey && !e.shiftKey && !e.altKey)
	{
		if (!BX.browser.IsIE())
		{
			switch (e.which)
			{
				case 86: // "V" and "v"
				case 118:
					dialog_OnPaste(e);
					break ;
			}
		}
	}
	dialog_cleanAndShow();
}

function dialog_OnPaste(e)
{
	this.pOnChangeTimer = setTimeout(dialog_cleanAndShow, 10);
}

function dialog_cleanAndShow()
{
	dialog_showClenedHtml(pObj.pMainObj.CleanWordText(pFrame.pDocument.body.innerHTML,
	{
		fonts: BX('bx_word_removeFonts').checked,
		styles: BX('bx_word_removeStyles').checked,
		indents: BX('bx_word_removeIndents').checked,
		spaces: BX('bx_word_removeSpaces').checked,
		tableAtr: BX('bx_word_removeTableAtr').checked,
		trtdAtr: BX('bx_word_removeTrTdAtr').checked
	}));
}

function dialog_showClenedHtml(html)
{
	taSourse = BX('bx_word_sourse');
	taSourse.value = html;
}

function OnSave()
{
	BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
	pObj.pMainObj.PasteWord(pFrame.pDocument.body.innerHTML,
	{
		fonts: BX('bx_word_removeFonts').checked,
		styles: BX('bx_word_removeStyles').checked,
		indents: BX('bx_word_removeIndents').checked,
		spaces: BX('bx_word_removeSpaces').checked,
		tableAtr: BX('bx_word_removeTableAtr').checked,
		trtdAtr: BX('bx_word_removeTrTdAtr').checked
	});
}
</script>
<?ob_start();?>
<table class="bx-dialog-pasteword">
	<tr id="bx_word_ff">
		<td><?= GetMessage("FILEMAN_ED_FF")?> "<?= GetMessage("FILEMAN_ED_SAVE")?>":</td>
	</tr>
	<tr>
		<td><iframe id="bx_word_text" src="javascript:void(0)" style="width:98%; height:150px; border:1px solid #CCCCCC;"></iframe></td>
	</tr>
	<tr>
		<td><?= GetMessage("FILEMAN_ED_HTML_AFTER_CLEANING")?></td>
	</tr>
	<tr>
		<td><textarea id="bx_word_sourse" style="width:96%; height:100px; border:1px solid #CCCCCC;" readonly="true"></textarea></td>
	</tr>
	<tr>
		<td>
			<input id="bx_word_removeFonts" type="checkbox" checked="checked"> <label for="bx_word_removeFonts"><?= GetMessage("FILEMAN_ED_REMOVE_FONTS")?></label><br>
			<input id="bx_word_removeStyles" type="checkbox" checked="checked"> <label for="bx_word_removeStyles"><?= GetMessage("FILEMAN_ED_REMOVE_STYLES")?></label><br>
			<input id="bx_word_removeIndents" type="checkbox" checked="checked"> <label for="bx_word_removeIndents"><?= GetMessage("FILEMAN_ED_REMOVE_INDENTS")?></label><br>
			<input id="bx_word_removeSpaces" type="checkbox" checked="checked"> <label for="bx_word_removeSpaces"><?= GetMessage("FILEMAN_ED_REMOVE_SPACES")?></label><br>
			<input id="bx_word_removeTableAtr" type="checkbox" checked="checked"> <label for="bx_word_removeTableAtr"><?= GetMessage("FILEMAN_ED_REMOVE_TABLE_ATR")?></label><br>
			<input id="bx_word_removeTrTdAtr" type="checkbox" checked="checked"> <label for="bx_word_removeTrTdAtr"><?= GetMessage("FILEMAN_ED_REMOVE_TR_TD_ATR")?></label><br>
		</td>
	</tr>
</table>
<?$dialogHTML = ob_get_contents(); ob_end_flush();?>

<?elseif($name == "pageprops"):?>

<script>
var finput = false;
function OnLoad()
{
	window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_EDITOR_PAGE_PROP")?>');
	BX.addClass(window.oBXEditorDialog.PARTS.CONTENT_DATA, "bxed-dialog-props");

	BX('BX_dialog_title').value = BX('title').value;
	BX("BX_more_prop_but").onclick = function(e) {AppendRow('', '');};
	var tag_property = "<? if(CModule::IncludeModule("search")){echo htmlspecialcharsbx(COption::GetOptionString("search", "page_tag_property"));}?>";

	var i, code, val, name, cnt = parseInt(BX("maxind").value)+1;
	for(i=0; i<cnt; i++)
	{
		code = BX("CODE_" + i);
		val = BX("VALUE_" + i);
		name = BX("NAME_"+i);
		if (tag_property == code.value)
			AppendTagPropertyRow(code.value, (val?val.value:null), (name?name.value:null));
		else
			AppendRow(code.value, (val?val.value:null), (name?name.value:null));
	}

	if(finput)
		finput.focus();

	window.oBXEditorDialog.adjustSizeEx();
}

function AppendRow(code, value, name)
{
	var
		tbl = BX('pageprops_t1'),
		cnt = parseInt(BX("BX_dialog_maxind").value) + 1;
		r = tbl.insertRow(tbl.rows.length - 1),
		c = r.insertCell(-1);

	c.className = "bx-par-title";
	if(name)
		c.innerHTML = '<input type="hidden" id="BX_dialog_CODE_'+cnt+'" name="BX_dialog_CODE_'+cnt+'" value="'+bxhtmlspecialchars(code)+'">'+bxhtmlspecialchars(name)+':';
	else
	{
		c.innerHTML = '<input type="text" id="BX_dialog_CODE_'+cnt+'" name="BX_dialog_CODE_'+cnt+'" value="'+bxhtmlspecialchars(code)+'" size="30">:';
		if(!finput)
			finput = BX('BX_dialog_CODE_'+cnt);
	}

	c = r.insertCell(-1);
	c.className = "bx-par-val";
	c.innerHTML = '<input type="text" name="BX_dialog_VALUE_'+cnt+'" id="BX_dialog_VALUE_'+cnt+'" value="'+bxhtmlspecialchars(value)+'" size="55">';

	if(!finput)
		finput = BX('BX_dialog_VALUE_'+cnt);

	BX("BX_dialog_maxind").value = cnt;

	window.oBXEditorDialog.adjustSizeEx();
}

function AppendTagPropertyRow(code, value, name)
{
	var tbl = BX('pageprops_t1');

	var cnt = parseInt(BX("BX_dialog_maxind").value)+1;
	var r = tbl.insertRow(tbl.rows.length-1);
	var c = r.insertCell(-1);
	c.className = "bx-par-title";

	if(name)
	{
		c.innerHTML = '<input type="hidden" id="BX_dialog_CODE_'+cnt+'" name="BX_dialog_CODE_'+cnt+'" value="'+bxhtmlspecialchars(code)+'">'+bxhtmlspecialchars(name)+':';
	}
	else
	{
		c.innerHTML = '<input type="text" id="BX_dialog_CODE_'+cnt+'" name="BX_dialog_CODE_'+cnt+'" value="'+bxhtmlspecialchars(code)+'" size="30">:';
		if(!finput)
			finput = BX('BX_dialog_CODE_'+cnt);
	}

	c = r.insertCell(-1);
	c.className = "bx-par-val";
	id = 'BX_dialog_VALUE_' + cnt;
	name = 'BX_dialog_VALUE_' + cnt;
	c.innerHTML =  '<input name="'+name+'" id="'+id+'" type="text" autocomplete="off" value="'+value+'" onfocus="window.oObject[this.id] = new JsTc(this, []);"  size="50"/><input type="checkbox" id="ck_'+id+'" name="ck_'+name+'" <? echo (CUserOptions::GetOption("search_tags", "order", "CNT") == "NAME" ? "checked": "");?> title="<?=GetMessage("SEARCH_TAGS_SORTING_TIP")?>">';

	if(!finput)
		finput = BX('BX_dialog_VALUE_' + cnt);

	BX("BX_dialog_maxind").value = cnt;

	window.oBXEditorDialog.adjustSizeEx();
}

function OnSave()
{
	var edcnt = parseInt(BX("maxind").value);
	var cnt = parseInt(BX("BX_dialog_maxind").value);

	for(var i=0; i<=edcnt; i++)
	{
		if(BX("CODE_"+i).value != BX("BX_dialog_CODE_"+i).value)
			BX("CODE_"+i).value = BX("BX_dialog_CODE_"+i).value;
		if(BX("VALUE_"+i).value != BX("BX_dialog_VALUE_"+i).value)
			BX("VALUE_"+i).value = BX("BX_dialog_VALUE_"+i).value;
	}

	for(i = edcnt+1; i<=cnt; i++)
		window._MoreRProps(BX("BX_dialog_CODE_"+i).value, BX("BX_dialog_VALUE_"+i).value);

	BX("maxind").value = cnt;
	BX('title').value = BX('BX_dialog_title').value;

	pObj.pMainObj.bNotSaved = true;

	return iNoOnSelectionChange;
}
</script>
<?ob_start();?>
<table id="pageprops_t1" class="bx-par-tbl">
	<tr>
		<td class="bx-par-title"><label for="BX_dialog_title"><b><?= GetMessage("FILEMAN_DIALOG_TITLE")?></b></label></td>
		<td class="bx-par-val"><input type="text" id="BX_dialog_title" value="" size="30"></td>
	</tr>
	<tr>
		<td></td>
		<td class="bx-par-val"><input id="BX_more_prop_but" type="button" value="<?= GetMessage("FILEMAN_DIALOG_MORE_PROP")?>"></td>
	</tr>
</table>
<input type="hidden" value="-1" id="BX_dialog_maxind">

<?$dialogHTML = ob_get_contents(); ob_end_flush();?>

<?elseif($name == "spellcheck"):?>

<script>
var pElement = null;
function OnLoad()
{
	window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_SPELLCHECKING")?>');
	pElement = pObj.pMainObj.GetSelectionObject();
	var BXLang = pObj.params.BXLang;
	var usePspell = pObj.params.usePspell;
	var useCustomSpell = pObj.params.useCustomSpell;
	oBXSpellChecker = new BXSpellChecker(pObj.pMainObj, BXLang, usePspell, useCustomSpell);
	oBXSpellChecker.parseDocument();
	oBXSpellChecker.spellCheck();

	window.oBXEditorDialog.adjustSizeEx();
}

</script>
<?ob_start();?>
<div>
<div id="BX_dialog_waitWin" style="display: block; text-align: center; vertical-align: middle;">
	<table border="0" width="100%" height="100%" style="vertical-align: middle">
		<tr><td height="60"></td></tr>
		<tr>
			<td align="center" valign="top">
				<img style="vertical-align: middle;" src="/bitrix/themes/.default/images/wait.gif" />
				<span style="vertical-align: middle;"><?= GetMessage("FILEMAN_ED_WAIT_LOADING")?></span>
			</td>
		</tr>
	</table>
</div>
<div id="BX_dialog_okMessWin" style="display: none;">
	<table border="0" width="100%" height="100%">
		<tr>
			<td align="center">
				<span style="vertical-align: middle;"><?= GetMessage("FILEMAN_ED_SPELL_FINISHED")?></span>
				<br><br>
				<input id="BX_dialog_butClose" type="button" value="<?= GetMessage("FILEMAN_ED_CLOSE")?>" style="width:150">
			</td>
		</tr>
	</table>
</div>
<div id="BX_dialog_spellResultWin" style="display: none">
<table width="380" border="0" align="center" cellpadding="0" cellspacing="0">
	<tr><td colspan="4" height="5"></td></tr>
	<tr>
		<td width="224" valign="top"><input id="BX_dialog_wordBox" type="text" style="width:100%;"></td>
		<td width="8"></td>
		<td width="140" valign="top"><input id="BX_dialog_butSkip" type="button" value="<?= GetMessage("FILEMAN_ED_SKIP")?>" style="width:100%;"></td>
		<td width="8"></td>
	</tr>
	<tr><td colspan="4" height="7"></td></tr>
	<tr>
		<td rowspan="9" valign="top"><select id="BX_dialog_suggestionsBox" size="8" style="width:100%;"></select></td>
		<td></td>
		<td><input id="BX_dialog_butSkipAll" type="button" value="<?= GetMessage("FILEMAN_ED_SKIP_ALL")?>" style="width:100%;"></td>
		<td></td>
	</tr>
	<tr height="5"><td colspan="2" height="5"></td></tr>
	<tr>
		<td></td>
		<td><input id="BX_dialog_butReplace" type="button" value="<?= GetMessage("FILEMAN_ED_REPLACE")?>" style="width:100%;"></td>
		<td></td>
	</tr>
	<tr height="5"><td colspan="2" height="5"></td></tr>
	<tr>
		<td></td>
		<td><input id="BX_dialog_butReplaceAll" type="button" value="<?= GetMessage("FILEMAN_ED_REPLACE_ALL")?>" style="width:100%;"></td>
		<td></td>
	</tr>
	<tr height="5"><td colspan="2" height="5"></td></tr>
	<tr>
		<td></td>
		<td><input id="BX_dialog_butAdd" type="button" value="<?= GetMessage("FILEMAN_ED_ADD")?>" style="width:100%;"></td>
		<td></td>
	</tr>
	<tr height="5"><td colspan="2" height="5"></td></tr>
	<tr>
		<td></td>
		<td><input id="BX_dialog_butClose" type="button" value="<?= GetMessage("FILEMAN_ED_CLOSE")?>" style="width:100%;" onClick="pObj.Close();"></td>
		<td></td>
	</tr>
</table>
</div>
</div>
<?$dialogHTML = ob_get_contents(); ob_end_flush();?>

<?elseif($name == "specialchar"):?>

<script>
function OnLoad()
{
	window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_EDITOR_SPES_CHAR")?>');

	arEntities_dialog = ['&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&OElig;','&oelig;','&Scaron;','&scaron;','&Yuml;','&circ;','&tilde;','&ndash;','&mdash;','&lsquo;','&rsquo;','&sbquo;','&ldquo;','&rdquo;','&bdquo;','&dagger;','&Dagger;','&permil;','&lsaquo;','&rsaquo;','&euro;','&Alpha;','&Beta;','&Gamma;','&Delta;','&Epsilon;','&Zeta;','&Eta;','&Theta;','&Iota;','&Kappa;','&Lambda;','&Mu;','&Nu;','&Xi;','&Omicron;','&Pi;','&Rho;','&Sigma;','&Tau;','&Upsilon;','&Phi;','&Chi;','&Psi;','&Omega;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigmaf;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&bull;','&hellip;','&prime;','&Prime;','&oline;','&frasl;','&trade;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&part;','&sum;','&minus;','&radic;','&infin;','&int;','&asymp;','&ne;','&equiv;','&le;','&ge;','&loz;','&spades;','&clubs;','&hearts;'];

	if(!BX.browser.IsIE())
	{
		arEntities_dialog = arEntities_dialog.concat('&thetasym;','&upsih;','&piv;','&weierp;','&image;','&real;','&alefsym;','&crarr;','&lArr;','&uArr;','&rArr;','&dArr;','&hArr;','&forall;','&exist;','&empty;','&nabla;','&isin;','&notin;','&ni;','&prod;','&lowast;','&prop;','&ang;','&and;','&or;','&cap;','&cup;','&there4;','&sim;','&cong;','&sub;','&sup;','&nsub;','&sube;','&supe;','&oplus;','&otimes;','&perp;','&sdot;','&lceil;','&rceil;','&lfloor;','&rfloor;','&lang;','&rang;','&diams;');
	}

	var
		charCont = BX("charCont"),
		charPreview = BX('charPrev'),
		charEntName = BX('entityName'),
		chTable = charCont.appendChild(BX.create("TABLE")),
		i, r, c, lEn = arEntities_dialog.length,
		elEntity = document.createElement("span");

	for(i = 0; i < lEn; i++)
	{
		if (i%19 == 0)
			r = chTable.insertRow(-1);

		elEntity.innerHTML = arEntities_dialog[i];
		c = BX.adjust(r.insertCell(-1), {
			props: {id: 'e_' + i},
			html: elEntity.innerHTML,
			events: {
				mouseover: function(e){
					var entInd = this.id.substring(2);
					BX.addClass(this, 'bx-over');
					charPreview.innerHTML = this.innerHTML;
					charEntName.innerHTML = arEntities_dialog[entInd].substr(1, arEntities_dialog[entInd].length - 2);
				},
				mouseout: function(e){BX.removeClass(this, 'bx-over');},
				click: function(e){
					var entInd = this.id.substring(2);
					BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument,pObj.pMainObj.pEditorWindow);
					pObj.pMainObj.insertHTML(arEntities_dialog[entInd]);
					window.oBXEditorDialog.Close();
				}
			}
		});
	}

	window.oBXEditorDialog.SetButtons([window.oBXEditorDialog.btnCancel]);
	window.oBXEditorDialog.adjustSizeEx();
}
</script>

<?ob_start();?>
<div style="height: 285px;">
	<div id="charCont" class="bx-d-char-cont"></div>
	<div id="charPrev" class="bx-d-prev-char"></div>
	<div id="entityName" class="bx-d-ent-name">&nbsp;</div>
</div>
<?$dialogHTML = ob_get_contents(); ob_end_flush();?>

<?elseif($name == "settings"):?>

<script>
function OnLoad()
{
	window.oBXEditorDialog.PARTS.CONTENT_DATA.style.height = 'auto';
	window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_SETTINGS")?>');
	if (!pObj.params.lightMode)
	{
		// TAB #1: Toolbar settings
		window.temp_arToolbarSettings = copyObj(SETTINGS[pObj.pMainObj.name].arToolbarSettings);
		_displayToolbarList(BX("__bx_set_1_toolbar"));
	}

	// TAB #2: Taskbar settings
	window.temp_arTaskbarSettings = copyObj(SETTINGS[pObj.pMainObj.name].arTaskbarSettings);
	_displayTaskbarList(BX("__bx_set_2_taskbar"));

	// TAB #3: Additional Properties
	_displayAdditionalProps(BX("__bx_set_3_add_props"));

	window.oBXEditorDialog.SetButtons([
		new BX.CWindowButton(
		{
			title: '<?= GetMessage("FILEMAN_ED_SAVE")?>',
			id: 'save',
			name: 'save',
			className: 'adm-btn-save',
			action: function()
			{
				var r;
				if(window.OnSave && typeof window.OnSave == 'function')
					r = window.OnSave();

				window.oBXEditorDialog.Close();
			}
		}),
		new BX.CWindowButton(
		{
			title: '<?= GetMessage("FILEMAN_ED_RESTORE")?>',
			id: 'restore',
			name: 'restore',
			action: function()
			{
				restoreSettings();
				window.oBXEditorDialog.Close();
			}
		}),
		window.oBXEditorDialog.btnClose
	]);

	window.oBXEditorDialog.adjustSizeEx();
}

function _displayToolbarList(oCont)
{
	var oTable = oCont.appendChild(BX.create("TABLE", {style: {width: "100%"}}));
	_displayTitle(oTable, '<?=GetMessage("FILEMAN_ED_TLBR_DISP")?>');
	pObj.arToolbarCheckboxes = [];

	for(var sToolBarId in arToolbars)
		if (arToolbars[sToolBarId] && typeof arToolbars[sToolBarId] == 'object')
			_displayToolbarRow(oTable, sToolBarId, SETTINGS[pObj.pMainObj.name].arToolbarSettings[sToolBarId].show);
}

function _displayToolbarRow(oTb, toolbarId, _show)
{
	var pCh = _displayRow(oTb, arToolbars[toolbarId][0], '__bx_' + toolbarId);
	SAttr(pCh, "__bxid", toolbarId);
	oBXEditorUtils.setCheckbox(pCh, _show);
	if (toolbarId != "standart")
		pObj.arToolbarCheckboxes.push(pCh);

	if (toolbarId == "standart")
		pCh.disabled = "disabled";
	pCh.onchange = function(e) {window.temp_arToolbarSettings[this.getAttribute("__bxid")].show = this.checked;}
}

function _displayTaskbarList(oCont)
{
	var oTable = oCont.appendChild(BX.create("TABLE", {style: {width: "100%"}}));
	_displayTitle(oTable,'<?=GetMessage("FILEMAN_ED_TSKBR_DISP")?>');
	pObj.arTaskbarCheckboxes = [];

	// TODO: bugs with two editors on page - fix IT
	var arTBAdded = {}, k, i, l;

	for(k in ar_BXTaskbarS)
	{
		if (ar_BXTaskbarS[k] && ar_BXTaskbarS[k].pMainObj && ar_BXTaskbarS[k].pMainObj.name == pObj.pMainObj.name)
		{
			arTBAdded[ar_BXTaskbarS[k].name] = true;
			_displayTaskbarRow(oTable, ar_BXTaskbarS[k], pObj.pMainObj.GetTaskbarConfig(ar_BXTaskbarS[k].name));
		}
	}

	//COMPONENTS 2.0
	if(pObj.pMainObj.allowedTaskbars['BXComponents2Taskbar'])
	{
		BXComponents2Taskbar_need_preload = false;
		if (!window.BXComponents2Taskbar || !ar_BXTaskbarS["BXComponents2Taskbar_" + pObj.pMainObj.name])
		{
			BXComponents2Taskbar_need_preload = true;
			var settings = pObj.pMainObj.GetTaskbarConfig('BXComponents2Taskbar');
			if (!settings.show || !arTBAdded["BXComponents2Taskbar"])
			{
				_displayTaskbarRow(oTable,{name:'BXComponents2Taskbar', title:BX_MESS.CompTBTitle}, settings);
				arTBAdded["BXComponents2Taskbar"] = true;
			}
		}
	}

	//SNIPPETS
	if(pObj.pMainObj.allowedTaskbars['BXSnippetsTaskbar'])
	{
		BXSnippetsTaskbar_need_preload = false;
		if (!ar_BXTaskbarS["BXSnippetsTaskbar_" + pObj.pMainObj.name])
		{
			BXSnippetsTaskbar_need_preload = true;
			var settings = pObj.pMainObj.GetTaskbarConfig('BXSnippetsTaskbar');
			if (!settings.show && !arTBAdded["BXSnippetsTaskbar"])
			{
				_displayTaskbarRow(oTable,{name:'BXSnippetsTaskbar',title:BX_MESS.SnippetsTB}, settings);
				arTBAdded["BXSnippetsTaskbar"] = true;
			}
		}
	}

	for (i = 0, l = arBXTaskbars.length; i < l; i++)
	{
		k = arBXTaskbars[i].name;
		if(pObj.pMainObj.allowedTaskbars[k] && !arTBAdded[k])
		{
			var settings = pObj.pMainObj.GetTaskbarConfig(k);
			if (!settings.show)
			{
				_displayTaskbarRow(oTable, {name: k, title: arBXTaskbars[i].title}, settings);
				arTBAdded[k] = true;
			}
		}
	}

	oCont.appendChild(oTable);
}

function _displayTaskbarRow(pTb, oTaskbar, arSettings)
{
	var pCh = _displayRow(pTb, oTaskbar.title, '__bx_' + oTaskbar.name);
	SAttr(pCh, "__bxid", oTaskbar.name);

	if (oTaskbar.name == "BXPropertiesTaskbar")
	{
		arSettings.show = true;
		pCh.disabled = true;
	}

	oBXEditorUtils.setCheckbox(pCh, arSettings.show);
	pObj.arTaskbarCheckboxes.push(pCh);
	pCh.onchange = function(e)
	{
		var id = this.getAttribute("__bxid");
		if (!window.temp_arTaskbarSettings[id])
			window.temp_arTaskbarSettings[id] = pObj.pMainObj.GetTaskbarConfig(id);
		window.temp_arTaskbarSettings[this.getAttribute("__bxid")].show = this.checked;
	}
}

function _displayRow(pTb, label, id)
{
	var pTr = pTb.insertRow(-1);
	var pTd = BX.adjust(pTr.insertCell(-1), {props: {className: "bx-par-title"}});

	BX.adjust(pTr.insertCell(-1), {props: {className: "bx-par-val"}, html: '<label for="' + id + '">' + label + '</label>'});
	return pTd.appendChild(BX.create("INPUT", {props: {type: 'checkbox', id: id}}));
}

function _displayTitle(pTb, sTitle)
{
	var pTr = pTb.insertRow(-1);
	pTr.className = "heading_dialog";
	BX.adjust(pTr.insertCell(-1), {props: {colSpan: 2}, text: sTitle});
}

function _displayAdditionalProps(oCont)
{
	var oTable = oCont.appendChild(pObj.pMainObj.CreateElement('TABLE', {width: '100%'}));
	_displayTitle(oTable,'<?=GetMessage("FILEMAN_ED_ADDITIONAL_PROPS")?>');

	oBXEditorUtils.setCheckbox(_displayRow(oTable, '<?=GetMessage("FILEMAN_ED_SHOW_TOOLTIPS")?>', '__bx_show_tooltips'), pObj.pMainObj.showTooltips4Components);

	oBXEditorUtils.setCheckbox(_displayRow(oTable, '<?=GetMessage("FILEMAN_ED_VIS_EFFECTS")?>', '__bx_visual_effects'), pObj.pMainObj.visualEffects);

	if (pObj.pMainObj.arConfig.allowRenderComp2)
		oBXEditorUtils.setCheckbox(_displayRow(oTable, '<?=GetMessage("FILEMAN_ED_RENDER_COMPONENTS2")?>', '__bx_render_comp2'), pObj.pMainObj.bRenderComponents);
}

function restoreSettings()
{
	pObj.pMainObj.RestoreConfig();
	var RSPreloader = new BXPreloader(
		[{func: BX.proxy(pObj.pMainObj.GetConfig, pObj.pMainObj), params: []}],
		{
			func: function()
			{
				if (!lightMode)
					BXRefreshToolbars(pObj.pMainObj);
				BXRefreshTaskbars(pObj.pMainObj);
				pObj.Close();
			}
		}
	);
	RSPreloader.LoadStep();
}

function OnSave()
{
	var Settings = SETTINGS[pObj.pMainObj.name];
	if (!lightMode)
	{
		if (!compareObj(Settings.arToolbarSettings,window.temp_arToolbarSettings))
		{
			Settings.arToolbarSettings = temp_arToolbarSettings;
			pObj.pMainObj.SaveConfig("toolbars", {tlbrset: temp_arToolbarSettings});
			BXRefreshToolbars(pObj.pMainObj);
		}
	}

	var showTooltips = !!BX("__bx_show_tooltips").checked;
	if (showTooltips != pObj.pMainObj.showTooltips4Components)
	{
		pObj.pMainObj.showTooltips4Components = showTooltips;
		pObj.pMainObj.SaveConfig("tooltips");
	}

	var visEff = !!BX("__bx_visual_effects").checked;
	if (visEff != pObj.pMainObj.visualEffects)
	{
		pObj.pMainObj.visualEffects = visEff;
		pObj.pMainObj.SaveConfig("visual_effects");
	}

	if (pObj.pMainObj.arConfig.allowRenderComp2)
	{
		var bRendComp2 = !!BX("__bx_render_comp2").checked;
		if (bRendComp2 != pObj.pMainObj.bRenderComponents)
		{
			pObj.pMainObj.bRenderComponents = bRendComp2;
			pObj.pMainObj.SetEditorContent(pObj.pMainObj.GetContent());
			if (!pObj.pMainObj.pComponent2Taskbar.C2Parser.bInited)
				pObj.pMainObj.pComponent2Taskbar.C2Parser.InitRenderingSystem();
			else
				pObj.pMainObj.pComponent2Taskbar.C2Parser.COnChangeView();
			pObj.pMainObj.SaveConfig("render_components");
		}
	}

	if (!compareObj(Settings.arTaskbarSettings, window.temp_arTaskbarSettings))
	{
		SETTINGS[pObj.pMainObj.name].arTaskbarSettings = temp_arTaskbarSettings;
		var arScripts = [];

		//Display SNIPPETS taskbar
		if(temp_arTaskbarSettings['BXSnippetsTaskbar'].show)
		{
			if (window.BXSnippetsTaskbar_need_preload)
				arScripts.push("/bitrix/admin/htmleditor2/snippets.js");
		}
		else if(ar_BXTaskbarS["BXSnippetsTaskbar_" + pObj.pMainObj.name])
		{
			ar_BXTaskbarS["BXSnippetsTaskbar_" + pObj.pMainObj.name].Close(false, false);
		}

		//Display COMPONENTS 2.0 taskbar
		if (temp_arTaskbarSettings['BXComponents2Taskbar'].show)
		{
			if (window.BXComponents2Taskbar_need_preload)
				arScripts.push("/bitrix/admin/htmleditor2/components2.js");
			pObj.pMainObj.LoadComponents2({func: BXCreateTaskbars, params: [pObj.pMainObj]})
		}
		else if(ar_BXTaskbarS["BXComponents2Taskbar_" + pObj.pMainObj.name])
		{
			ar_BXTaskbarS["BXComponents2Taskbar_" + pObj.pMainObj.name].Close(false, false);
		}

		if (arScripts.length > 0)
			BX.loadScript(arScripts, function(){BXCreateTaskbars(pObj.pMainObj);});
		else
			BXCreateTaskbars(pObj.pMainObj);

		pObj.pMainObj.SaveConfig("taskbars", {tskbrset: temp_arTaskbarSettings});
	}
}
</script>

<?
	$arTabs = array();
	if (!isset($_GET['light_mode']) || $_GET['light_mode'] != 'Y')
		$arTabs[] = array("DIV" => "__bx_set_1_toolbar", "TAB" => GetMessage("FILEMAN_ED_TOOLBARS"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_TOOLBARS_SETTINGS"), "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();");

	$arTabs[] = array("DIV" => "__bx_set_2_taskbar", "TAB" => GetMessage("FILEMAN_ED_TASKBARS"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_TASKBARS_SETTINGS"), "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();");

	$arTabs[] = array("DIV" => "__bx_set_3_add_props", "TAB" => GetMessage("FILEMAN_ED_ADDITIONAL_PROPS"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_ADDITIONAL_PROPS"), "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();");

$tabControlDialog = new CAdmintabControl("tabControlDialog_opt", $arTabs, false, true);
$tabControlDialog->Begin();
$tabControlDialog->BeginNextTab();?>
<tr><td></td></tr>
<?$tabControlDialog->BeginNextTab();?>
<tr><td></td></tr>
<?$tabControlDialog->BeginNextTab();?>
<tr><td></td></tr>
<?$tabControlDialog->EndTab();?>
<?$tabControlDialog->End();?>

<?elseif($name == "flash"):?>
<script>
// F L A S H
function OnLoad()
{
	window.oBXEditorDialog.PARTS.CONTENT_DATA.style.height = 'auto';
	// ************************ TAB #1: Base params *************************************
	var oDiv = BX("__bx_base_params");
	oDiv.style.padding = "5px";
	oDiv.innerHTML = '<table width="100%" border="0" height="260">'+
					'<tr>'+
						'<td align="right" width="40%">' + BX_MESS.PATH2SWF + ':</td>'+
						'<td width="60%">'+
							'<input type="text" size="30" value="" id="flash_src" name="bx_src">'+
							'<input type="button" value="..." id="OpenFileBrowserWindFlash_button">'+
						'</td>'+
					'</tr>'+
					'<tr>'+
						'<td align="right">' + BX_MESS.TPropSize + ':</td>'+
						'<td align="left"><input type="text" size="4" id="flash_width" /> x <input type="text" size="4" id="flash_height" /></td>' +
					'</tr>'+
					'<tr>'+
						'<td align="right" valign="top"><?=GetMessage("FILEMAN_ED_IMG_PREV")?></td>'+
						'<td>'+
							'<div id="flash_preview_cont" style="height:200px; width:95%; overflow: hidden; border: 1px #999999 solid; overflow-y: auto; overflow-x: auto;">'+
							'</div>'+
						'</td>'+
					'</tr>'+
				'</table>';

	//Attaching Events
	BX("OpenFileBrowserWindFlash_button").onclick = OpenFileBrowserWindFlash;
	var oPreviewCont = BX("flash_preview_cont");
	BX("flash_src").onchange = function(){Flash_Reload(oPreviewCont, BX("flash_src").value, 150, 150)};

	// ************************ TAB #2: Additional params ***********************************
	var oDiv = BX("__bx_additional_params");
	oDiv.style.padding = "5px";
	oDiv.innerHTML = '<table width="100%" border="0" height="260">'+
				'<tr>'+
					'<td align="right" width="40%" colspan="2">' + BX_MESS.SWF_ID + ':</td>'+
					'<td width="60%" colspan="2">'+
						'<input type="text" size="30" value="" id="_flash_id">'+
					'</td>'+
				'</tr>'+
				'<tr>'+
					'<td align="right" colspan="2">' + BX_MESS.SWF_TITLE + ':</td>'+
					'<td colspan="2">'+
						'<input type="text" size="30" value="" id="_flash_title">'+
					'</td>'+
				'</tr>'+
				'<tr>'+
					'<td align="right" colspan="2">' + BX_MESS.SWF_CLASSNAME + ':</td>'+
					'<td colspan="2">'+
						'<input type="text" size="30" value="" id="_flash_classname">'+
					'</td>'+
				'</tr>'+
				'<tr>'+
					'<td align="right" colspan="2">' + BX_MESS.TPropStyle + '</td>'+
					'<td colspan="2">'+
						'<input type="text" size="30" value="" id="_flash_style">'+
					'</td>'+
				'</tr>'+
				'<tr>'+
					'<td align="right" colspan="2">' + BX_MESS.SWF_QUALITY + ':</td>'+
					'<td colspan="2">'+
						'<select id="_flash_quality" style="width:100px">'+
							'<option value=""></option>'+
							'<option value="low">low</option>'+
							'<option value="medium">medium</option>'+
							'<option value="high">high</option>'+
							'<option value="autolow">autolow</option>'+
							'<option value="autohigh">autohigh</option>'+
							'<option value="best">best</option>'+
						'</select>'+
					'</td>'+
				'</tr>'+
				'<tr>'+
					'<td align="right" colspan="2">' + BX_MESS.SWF_WMODE + ':</td>'+
					'<td colspan="2">'+
						'<select id="_flash_wmode" style="width:100px">'+
							'<option value=""></option>'+
							'<option value="window">window</option>'+
							'<option value="opaque">opaque</option>'+
							'<option value="transparent">transparent</option>'+
						'</select>'+
					'</td>'+
				'</tr>'+
				'<tr>'+
					'<td align="right" colspan="2">' + BX_MESS.SWF_SCALE + ':</td>'+
					'<td colspan="2">'+
						'<select id="_flash_scale"style="width:100px">'+
							'<option value=""></option>'+
							'<option value="showall">showall</option>'+
							'<option value="noborder">noborder</option>'+
							'<option value="exactfit">exactfit</option>'+
						'</select>'+
					'</td>'+
				'</tr>'+
				'<tr>'+
					'<td align="right" colspan="2">' + BX_MESS.SWF_SALIGN + ':</td>'+
					'<td colspan="2">'+
						'<select id="_flash_salign" style="width:100px">'+
							'<option value=""></option> '+
							'<option value="left">left</option> '+
							'<option value="top">top</option> '+
							'<option value="right">right</option> '+
							'<option value="bottom">bottom</option> '+
							'<option value="top left">top left</option>'+
							'<option value="top right">top right</option>'+
							'<option value="bottom left">bottom left</option>'+
							'<option value="bottom right">bottom right</option>'+
						'</select>'+
					'</td>'+
				'</tr>'+
				'<tr>'+
					'<td align="right" colspan="2">' + BX_MESS.SWF_AUTOPLAY + ':</td>'+
					'<td colspan="2">'+
						'<input type="checkbox" value="" id="_flash_autoplay">'+
					'</td>'+
				'</tr>'+
				'<tr>'+
					'<td align="right" colspan="2">' + BX_MESS.SWF_LOOP + ':</td>'+
					'<td colspan="2">'+
						'<input type="checkbox" value="" id="_flash_loop">'+
					'</td>'+
				'</tr>'+
				'<tr>'+
					'<td align="right" colspan="2">' + BX_MESS.SWF_SHOW_MENU + ':</td>'+
					'<td colspan="2">'+
						'<input type="checkbox" value="" id="_flash_showmenu">'+
					'</td>'+
				'</tr>'+
			'</table>';

	// ************************ TAB #3: HTML Code *************************************
	var oDiv = BX("__bx_code");
	oDiv.style.padding = "5px";
	oDiv.innerHTML = '<table width="100%" border="0" height="260">'+
					'<tr>'+
						'<td align="left" width="100%" style="padding-left: 30px !important;"><?=GetMessage("FILEMAN_ED_SWF_HTML_CODE")?>:<br />'+
							'<textarea id="bx_flash_html_code" cols="49" rows="12"></textarea>'+
						'</td>'+
					'</tr>'+
				'</table>';

	var applyParams = function(arParams)
	{
		var re, _p, i, l;
		for(var i in pObj.bx_swf_arParams)
		{
			_p = pObj.bx_swf_arParams[i].p;
			if (!_p)
				continue;

			if (_p.type.toLowerCase() == 'checkbox')
				_p.checked = (arParams[i]);
			else
				_p.value = arParams[i] || '';
		}
	};

	pObj.bx_swf_source = BX("bx_flash_html_code");
	pObj.bx_swf_source.onblur = function()
	{


		var s = this.value;
		if (s.length <= 0)
			return;
		var flash_parser = function(str, attr)
		{
			if (attr.indexOf('.swf') === false || attr.indexOf('flash') === false) // not a flash
				return;

			attr = attr.replace(/[\r\n]+/ig, ' ');
			attr = attr.replace(/\s+/ig, ' ');
			attr = attr.trim();

			var _params = ['src', 'width', 'height', 'id', 'title', 'class', 'style', 'quality', 'wmode', 'scale', 'salign', 'autoplay', 'loop', 'showmenu' ];
			var arParams = {};
			var re, _p, i, l;
			for (i = 0, l = _params.length; i < l; i++)
			{
				_p = _params[i];
				re = new RegExp(_p+'\\s*=\\s*("|\')([^\\1]+?)\\1', "ig");
				attr = attr.replace(re, function(s, b1, value){arParams[_p] = value;});
			}
			applyParams(arParams);
		};
		s = s.replace(/<embed([^>]*?)>[^>]*?<\/embed>/ig, flash_parser);
		Flash_Reload(oPreviewCont, BX("flash_src").value, 150, 150);
	};

	pObj.bx_swf_arParams = {
		src : {p : BX("flash_src")},
		width : {p : BX("flash_width")},
		height : {p : BX("flash_height")},
		id : {p : BX("_flash_id")},
		title : {p : BX("_flash_title")},
		classname : {p : BX("_flash_classname")},
		style : {p : BX("_flash_style")},
		quality : {p : BX("_flash_quality")},
		wmode : {p : BX("_flash_wmode")},
		scale : {p : BX("_flash_scale")},
		salign : {p : BX("_flash_salign")},
		autoplay : {p : BX("_flash_autoplay")},
		loop : {p : BX("_flash_loop")},
		showmenu : {p : BX("_flash_showmenu")}
	};

	pElement = pObj.pMainObj.GetSelectionObject();
	pObj.bxTag = false;

	if (pElement)
	{
		bxTag = pObj.pMainObj.GetBxTag(pElement);
		if (!bxTag || bxTag.tag != "flash")
			pElement = false;
	}

	if(pElement && bxTag) // Edit flash
	{
		pObj.bxTag = bxTag;

		//var id  = pElement.id;
		pObj.bx_swf_source.disabled = true;
		window.oBXEditorDialog.SetTitle(BX_MESS.FLASH_MOV);


		//applyParams(pObj.pMainObj.arFlashParams[id]);
		applyParams(bxTag.params);
		Flash_Reload(oPreviewCont, BX("flash_src").value, 150, 150);
	}
	else // insert flash
	{
		window.oBXEditorDialog.SetTitle('<?=GetMessage("FILEMAN_ED_FLASH")?>');
	}

	window.oBXEditorDialog.adjustSizeEx();
}

function SetUrl(filename, path, site)
{
	var url = (path == '/' ? '' : path) + '/'+filename;
	BX("flash_src").value = url;
	if(BX("flash_src").onchange)
		BX("flash_src").onchange();
}

function OnSave()
{
	pObj.pMainObj.bSkipChanges = true;
	BXSelectRange(oPrevRange,pObj.pMainObj.pEditorDocument, pObj.pMainObj.pEditorWindow);
	var html, i, p;

	if (!pObj.bx_swf_arParams.src.p.value && pObj.bx_swf_source.value !== '')
	{
		html = pObj.bx_swf_source.value;
	}
	else
	{
		if (pObj.bxTag)
		{
			for(i in pObj.bx_swf_arParams)
			{
				p = pObj.bx_swf_arParams[i].p;
				if (p)
				{
					if (p.type.toLowerCase() == 'checkbox' && p.checked)
						pObj.bxTag.params[i] = p.checked || null;
					else if(p.type.toLowerCase() != 'checkbox' && p.value.length > 0)
						pObj.bxTag.params[i] = p.value;
				}
			}

			pElement.style.width = (parseInt(pObj.bxTag.params.width) || 50) + 'px';
			pElement.style.height = (parseInt(pObj.bxTag.params.height) || 25) + 'px';
			pObj.pMainObj.bSkipChanges = false;
			pObj.pMainObj.SetBxTag(pElement, pObj.bxTag);
			return;
		}

		if (pObj.bx_swf_source.value.length > 0)
		{
			html = pObj.bx_swf_source.value;
		}
		else
		{
			html = '<EMBED ';
			for(var i in pObj.bx_swf_arParams)
			{
				_p = pObj.bx_swf_arParams[i].p;
				if (!_p) continue;

				if (_p.type.toLowerCase() == 'checkbox' && _p.checked)
					html += i + '="true" ';
				else if(_p.type.toLowerCase() != 'checkbox' && _p.value.length > 0)
					html += i + '="' + _p.value + '" ';
			}
			html += 'type = "application/x-shockwave-flash" '+
			'pluginspage = "http://www.macromedia.com/go/getflashplayer" '+
			'></EMBED>';
		}
	}

	var html = pObj.pMainObj.pParser.SystemParse(html);
	pObj.pMainObj.insertHTML(html);
	pObj.pMainObj.bSkipChanges = false;
}
</script>

<?
CAdminFileDialog::ShowScript(Array
	(
		"event" => "OpenFileBrowserWindFlash",
		"arResultDest" => Array("FUNCTION_NAME" => "SetUrl"),
		"arPath" => Array("SITE" => $_GET["site"], "PATH" =>($str_FILENAME <> '' ? GetDirPath($str_FILENAME) : '')),
		"select" => 'F',// F - file only, D - folder only,
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'swf',//'' - don't shjow select, 'image' - only images; "ext1,ext2" - Only files with ext1 and ext2 extentions;
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);

$tabControlDialog = new CAdminTabControl("tabControlDialog_flash", array(
	array("DIV" => "__bx_base_params", "TAB" => GetMessage("FILEMAN_ED_BASE_PARAMS"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_BASE_PARAMS"), "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();"),
	array("DIV" => "__bx_additional_params", "TAB" => GetMessage("FILEMAN_ED_ADD_PARAMS"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_ADD_PARAMS"), "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();"),
	array("DIV" => "__bx_code", "TAB" => GetMessage("FILEMAN_ED_HTML_CODE"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_SWF_HTML_CODE"), "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();")
), false, true);
$tabControlDialog->Begin();?>

<?$tabControlDialog->BeginNextTab();?>
<tr><td></td></tr>
<?$tabControlDialog->BeginNextTab();?>
<tr><td></td></tr>
<?$tabControlDialog->BeginNextTab();?>
<tr><td></td></tr>
<?$tabControlDialog->End();
?>

<?elseif($name == "snippets"):?>
<script>
function OnLoad()
{
	window.oBXEditorDialog.PARTS.CONTENT_DATA.style.height = 'auto';
	window.oBXEditorDialog.SetTitle(pObj.params.mode == 'add' ? '<?=GetMessage("FILEMAN_ED_ADD_SNIPPET")?>' : '<?=GetMessage("FILEMAN_ED_EDIT_SNIPPET")?>');

	window.arBXSnippetsTaskbars = [];
	for (var k in ar_BXTaskbarS)
	{
		if (k.substr(0, 'BXSnippetsTaskbar'.length) == 'BXSnippetsTaskbar')
			window.arBXSnippetsTaskbars.push(ar_BXTaskbarS[k]);
	}

	BX("__bx_sn_base_params").appendChild(BX("__bx_temp_sn_base_params"));
	BX("__bx_sn_location").appendChild(BX("__bx_temp_sn_location"));
	BX("__bx_sn_additional_params").appendChild(BX("__bx_temp_sn_additional_params"));
	var pTemplate = BX("__snippet_template");
	pTemplate.options[1].value = pTemplate.options[1].innerHTML = pObj.pMainObj.templateID;

	window.arSnGroups = {};
	window.rootDefaultName = {};

	if (pObj.params.mode == 'add')
	{
		pTemplate.onchange = fillLocation;
		fillLocation();
		BX("__create_new_subfolder").onclick = function(e)
		{
			displayRow('_new_group_row', !!this.checked);
			window.oBXEditorDialog.adjustSizeEx();
		}
	}
	else if (pObj.params.mode == 'edit')
	{
		var oEl = pObj.params.oEl;
		BX("__snippet_title").value = oEl.title;
		BX("__snippet_code").value = oEl.code;
		BX("__snippet_description").value = oEl.description;

		var
			_pref = '&nbsp;<span style="color:#525355">',
			_suf = '</span>';

		pTemplate.parentNode.style.height = '30px';
		pTemplate.parentNode.innerHTML = _pref + oEl.template + _suf;

		var name = BX("__snippet_name");
		name.parentNode.style.height = '30px';
		name.parentNode.innerHTML = _pref + oEl.name + _suf;

		var group_sel = BX("__snippet_group");
		group_sel.parentNode.style.height = '30px';
		group_sel.parentNode.vAlign = 'middle';
		group_sel.parentNode.previousSibling.vAlign = 'middle';
		var _path = oEl.path.replace(/,/g,'/');
		group_sel.parentNode.innerHTML = _pref+'snippets'+(_path == '' ? '' : '/'+_path)+_suf;

		displayRow('_new_group_chck_row', false);

		// ***** IMAGE *****
		if (oEl.thumb != '')
		{
			displayRow('__bx_snd_exist_image_tr',true);
			var old_img_tr = BX("__bx_snd_exist_image_tr");
			old_img_tr.cells[1].innerHTML = _pref + ('snippets/images/'+( _path == '' ? '' : _path + '/') + oEl.thumb) + _suf;
			displayRow('__bx_snd_new_image_chbox_tr',true);
			displayRow('__bx_snd_new_image_tr',false);
			BX("thumb_src_label").innerHTML = '<?=GetMessage("FILEMAN_ED_SN_NEW_IMG")?>:';

			BX("__new_image_chbox").onclick = function()
			{
				displayRow('__bx_snd_new_image_tr', !!this.checked);
				window.oBXEditorDialog.adjustSizeEx();
			}
		}

	}

	window.oBXEditorDialog.adjustSizeEx();
}

function SetUrl(filename, path, site)
{
	var url = path+'/'+filename;
	BX("thumb_src").value = url;
	if(BX("thumb_src").onchange)
		BX("thumb_src").onchange();
}

function fillLocation()
{
	var template = BX("__snippet_template").value;
	if (window.arSnGroups[template])
		return _fillLocation(template);

	var _r = new JCHttpRequest();
	_r.Action = function(result)
	{
		try
		{
			setTimeout(function ()
				{
					_fillLocation(template);
				}, 5
			);
		}
		catch(e)
		{
			_alert('error: loadGroups');
		}
	}
	window.arSnGroups[template] = {};
	window.rootDefaultName[template] = '';
	_r.Send(manage_snippets_path + '&templateID='+template+'&target=getgroups');
}

function _fillLocation(template)
{
	var _arGroups = window.arSnGroups[template];
	var file_name = BX("__snippet_name");
	file_name.value = window.rootDefaultName[template];
	var group_sel = BX("__snippet_group");
	group_sel.options.length = 0;
	group_sel.onchange = function()
	{
		var chbox = BX("__create_new_subfolder");

		if (this.value == '..')
		{
			file_name.value = window.rootDefaultName[template];
			var _level = -1;
		}
		else
		{
			file_name.value = _arGroups[this.value].default_name;
			var _level = _arGroups[this.value].level;
		}

		if (_level >= 1)
		{
			chbox.checked = false;
			chbox.disabled = 'disabled';
			chbox.onclick();
		}
		else
		{
			chbox.disabled = '';
		}
	}

	var _addOption = function(key,name,level,select)
	{
		var oOpt = document.createElement('OPTION');
		var strPref = '';
		oOpt.value = key;
		for (var _i=-1; _i < level; _i++)
			strPref += '&nbsp;&nbsp;.&nbsp;&nbsp;';

		if (select)
			oOpt.selected = "selected";
		oOpt.innerHTML = strPref+name;
		group_sel.appendChild(oOpt);
		oOpt = null;
	};

	_addOption('..','snippets',-1,true);
	for (var key in _arGroups)
		_addOption(key,_arGroups[key].name,_arGroups[key].level,false);

	return;

	var url = path+'/'+filename;
	BX("thumb_src").value = url;
	if(BX("thumb_src").onchange)
		BX("thumb_src").onchange();
}

function displayRow(rowId, bDisplay)
{
	var row = BX(rowId);
	if (row)
		row.style.display = bDisplay ? '' : 'none';
}

function Get_arSnGroups(template)
{
	var _r = new JCHttpRequest();
	_r.Action = function(result)
	{
		try
		{
			setTimeout(function ()
				{
					_fillLocation(template);
				}, 5
			);
		}
		catch(e)
		{
			_alert('error: loadGroups');
		}
	}
	window.arSnGroups[template] = {};
	window.rootDefaultName[template] = '';
	_r.Send(manage_snippets_path + '&templateID='+template+'&target=getgroups');
}

function OnSave()
{
	var title = BX("__snippet_title").value;
	var code = BX("__snippet_code").value;

	if (title == "")
	{
		alert("<?=GetMessage("FILEMAN_ED_WRONG_PARAM_TITLE")?>");
		return false;
	}
	if (code == "")
	{
		alert("<?=GetMessage("FILEMAN_ED_WRONG_PARAM_CODE")?>");
		return false;
	}

	if (pObj.params.mode == 'add')
	{
		var name = BX("__snippet_name").value;
		name = name.replace(/[^a-z0-9\s!\$\(\)\[\]\{\}\-\.;=@\^_\~]/gi, "");

		var templateId = BX("__snippet_template").value;
		if (templateId == "")
			templateId = ".default";

		var new_group = '';
		if (BX("__create_new_subfolder").checked)
			new_group = BX("__new_subfolder_name").value.replace(/\\/ig, '/');

		new_group = new_group.replace(/[^a-z0-9\s!\$\(\)\[\]\{\}\-\.;=@\^_\~]/gi, "");

		var res = saveSnippet(name, templateId, new_group);
		if (res !== true)
			return false;
	}
	else if (pObj.params.mode == 'edit')
	{
		editSnippet(title, code);
	}
}

function saveSnippet(fileName, templateId, new_group)
{
	if (new_group.length > 0)
	{
		var _arGroups = window.arSnGroups[templateId];
		if (new_group.substr(0,1) == '/')
			new_group = new_group.substr(1);

		if (new_group.substr(new_group.length - 1, 1) == '/')
			new_group = new_group.substr(0, new_group.length - 1);

		var ar_d = new_group.split('/');
		if (ar_d.length > 2)
			return alert("<?=GetMessage("FILEMAN_ED_WRONG_PARAM_SUBGROUP2")?>");

		if (_arGroups[ar_d[0]] || _arGroups[new_group])
			return alert("<?=GetMessage("FILEMAN_ED_WRONG_PARAM_SUBGROUP")?>");
	}

	var
		title = BX("__snippet_title").value,
		code = BX("__snippet_code").value,
		thumb = BX("thumb_src").value,
		description = BX("__snippet_description").value,
		location = BX("__snippet_group").value;

	if (location.indexOf('..') != -1)
		location = '';

	var path = location + '/' + new_group;
	path = path.replace(/\\/ig, '/');
	if (path == '/' || path == '//')
		path = fileName + '.snp';
	else
		path += '/' + fileName + '.snp';

	path = path.replace(/\/+/ig, '/');
	if (window.arSnippets[path])
		return alert("<?=GetMessage("FILEMAN_ED_FILE_EXISTS")?>");

	window.__bx_res_sn_filename = null;
	BX.ajax.post(manage_snippets_path + '&target=add',
		{
			sessid: BX.bitrix_sessid(),
			title: title,
			code: code,
			name: fileName,
			description: description,
			location: location,
			new_group: new_group,
			thumb: thumb,
			templateID: templateId
		},
		function()	{setTimeout(function(){
			if (window.__bx_res_sn_filename)
				fileName = window.__bx_res_sn_filename;

			var _path = location + ((location != '' && new_group != '') ? '/' : '')+new_group;
			var createGroup = function(name, path)
			{
				name = bxhtmlspecialchars(name);
				for (var i = 0, l = arBXSnippetsTaskbars.length; i < l; i++)
					arBXSnippetsTaskbars[i].AddElement({name : name, tagname : '', isGroup : true, childElements : [], icon : '', path : path, code : ''}, arBXSnippetsTaskbars[i].pCellSnipp, path);
			};

			reappend_rot_el = false;
			if(location != '')
			{
				var ar_groups = location.split('/');
				var len = ar_groups.length;
				var _loc = '';
				for (var _j = 0; _j<len; _j++)
				{
					_loc += ar_groups[_j];
					if (!pObj.params.BXSnippetsTaskbar.GetGroup(pObj.params.BXSnippetsTaskbar.pCellSnipp,_loc))
					{
						createGroup(ar_groups[_j], (_j>0 ? ar_groups[_j-1] : ''));
						reappend_rot_el = true;
					}
					_loc += ',';
				}
			}

			if (new_group != '')
			{
				var ar_groups = new_group.split('/');
				var len = ar_groups.length;

				if (len>2)
					return;
				else if(len>0)
					reappend_rot_el = true;

				for (var _j = 0; _j<len; _j++)
					createGroup(ar_groups[_j],(_j>0 ? ar_groups[_j-1] : location));
			}

			if (thumb != '')
				thumb = fileName + thumb.substr(thumb.lastIndexOf('.'));

			var c = "sn_" + Math.round(Math.random()*1000000);
			var __arEl =
			{
				name: fileName + '.snp',
				title: title,
				tagname:'snippet',
				description: description,
				template: templateId,
				thumb:thumb,
				isGroup:false,
				icon:'/bitrix/images/fileman/htmledit2/snippet.gif',
				path: _path.replace(/\//ig, ","),
				code:code,
				params:{c:c}
			};

			var key = (__arEl.path == '' ? '' : __arEl.path.replace(/,/ig, '/') + '/') + __arEl.name;

			arSnippets[key] = __arEl;

			var _ar, el;
			for (el in GLOBAL_pMainObj)
			{
				_ar = GLOBAL_pMainObj[el].arSnippetsCodes;
				if (_ar)
					_ar[c] = key;
			}

			for (var i = 0, l = arBXSnippetsTaskbars.length; i < l; i++)
			{
				arBXSnippetsTaskbars[i].AddElement(__arEl, arBXSnippetsTaskbars[i].pCellSnipp, __arEl.path);
				arBXSnippetsTaskbars[i].AddSnippet_button();
			}
		}, 50);}
	);

	return true;
}

function editSnippet(title, code)
{
	var
		oEl = pObj.params.oEl,
		description = BX("__snippet_description").value,
		elNode = pObj.params.elNode,
		thumb = oEl.thumb || '',
		post = {
			name: oEl.name,
			path: oEl.path.replace(/,/g,'/'),
			templateID: oEl.template,
			sessid: BX.bitrix_sessid()
		};

	if (oEl.thumb != '' && BX("__new_image_chbox").checked || oEl.thumb == '')
		thumb = BX("thumb_src").value;
	thumb = BX.util.trim(thumb);

	if (title != oEl.title)
	{
		oEl.title = post.title = title;
		var titleCell = elNode.parentNode.parentNode.cells[1];
		if (titleCell)
			titleCell.innerHTML = bxhtmlspecialchars(oEl.title);
	}

	if (code != oEl.code)
		post.code = oEl.code = code;

	if (description != oEl.description)
		post.description = oEl.description = description;

	if (thumb != oEl.thumb)
	{
		post.thumb = thumb;
		if (thumb != '' && thumb != '' && thumb.lastIndexOf('.') > 0)
			oEl.thumb = oEl.name.substr(0, oEl.name.lastIndexOf('.')) + thumb.substr(thumb.lastIndexOf('.')).toLowerCase() + '?v=' + Math.random().toString().substring(5);
		else
			oEl.thumb = '';
	}

	BX.ajax.post(manage_snippets_path + '&target=edit',
		post,
		function()
		{
			setTimeout(function()
			{
				elNode.onclick();
			}, 500);
		}
	);
}
</script>

<?
CAdminFileDialog::ShowScript(Array
	(
		"event" => "OpenFileDialog_thumb",
		"arResultDest" => Array("FUNCTION_NAME" => "SetUrl"),
		"arPath" => Array(),
		"select" => 'F',
		"operation" => 'O',
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'image',
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);

$tabControlDialog = new CAdmintabControl("tabControlDialog_sn", array(
	array("DIV" => "__bx_sn_base_params", "TAB"=>GetMessage("FILEMAN_ED_BASE_PARAMS"), "ICON" => "", "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();"),
	array("DIV" => "__bx_sn_location", "TAB"=>GetMessage("FILEMAN_ED_LOCATION"), "ICON" => "", "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();"),
	array("DIV" => "__bx_sn_additional_params", "TAB"=>GetMessage("FILEMAN_ED_ADD_PARAMS"), "ICON" => "", "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();"),
), false, true);

$tabControlDialog->Begin();
$tabControlDialog->BeginNextTab();?>
<tr><td></td></tr>
<?$tabControlDialog->BeginNextTab();?>
<tr><td></td></tr>
<?$tabControlDialog->BeginNextTab();?>
<tr><td></td></tr>
<?$tabControlDialog->End();?>

<table id="__bx_temp_sn_base_params" class="add_snippet">
	<tr>
		<td align="right" style="width: 40%;"><?=GetMessage("FILEMAN_ED_TITLE")?>:</td>
		<td style="width: 60%;"><input id="__snippet_title" type="text" /></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?=GetMessage("FILEMAN_ED_CODE")?>:</td>
		<td><textarea id="__snippet_code" rows="10"></textarea></td>
	</tr>
</table>

<table id="__bx_temp_sn_location" class="add_snippet">
	<tr>
		<td width="40%" align="right"><?=GetMessage("FILEMAN_ED_TEMPLATE")?>:</td>
		<td width="60%">
			<select id="__snippet_template" style="width: 160px;">
				<option value=".default">.default</option>
				<option value="111">222</option>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right"><?=GetMessage("FILEMAN_ED_NAME")?>:</td>
		<td><input id="__snippet_name" style="width:135px" type="text">.snp</td>
	</tr>
	<tr>
		<td align="right" valign="middle"><?=GetMessage("FILEMAN_ED_FILE_LOCATION")?>:</td>
		<td valign="top">
			<select id="__snippet_group" size="6" style="width: 160px;height: 120px!important;"></select>
		</td>
	</tr>
	<tr id='_new_group_chck_row'>
		<td align="right"><label for="__create_new_subfolder"><?=GetMessage("FILEMAN_ED_CREATE_SUBGROUP")?>:</label></td>
		<td align="left"><input style="width:18px" id="__create_new_subfolder" type="checkbox"></td>
	</tr>
	<tr id='_new_group_row' style="display:none;">
		<td align="right"><?=GetMessage("FILEMAN_ED_SUBGROUP_NAME")?>:</td>
		<td><input style="width:160px" id="__new_subfolder_name" type="text"></td>
	</tr>
	<tr><td colspan="2"></td></tr>
</table>

<table id="__bx_temp_sn_additional_params" class="add_snippet">
	<tr style="height:0%; display:none;" id="__bx_snd_exist_image_tr">
		<td width="40%"align="right"><?=GetMessage("FILEMAN_ED_SN_IMAGE")?>:</td>
		<td width="60%"></td>
	</tr>
	<tr style="height:0%; display:none;" id="__bx_snd_new_image_chbox_tr">
		<td width="40%" align="right"><label for='__new_image_chbox'><?=GetMessage("FILEMAN_ED_SN_DEL_IMG")?>:</label></td>
		<td width="60%"><input style="width:18px" id="__new_image_chbox" type="checkbox"></input></td>
	</tr>
	<tr id="__bx_snd_new_image_tr">
		<td align="right">
			<label id="thumb_src_label" for="thumb_src"><?=GetMessage("FILEMAN_ED_SN_IMAGE")?>:</label>
		</td>
		<td>
			<input type="text" size="25" value="" id="thumb_src" style="width: 75%"><input id="OpenFileDialog_button" type="button" value="..." onclick="OpenFileDialog_thumb()" style="width: 10%">
		</td>
	</tr>
	<tr>
		<td align="right" valign="top"><?=GetMessage("FILEMAN_ED_DESCRIPTION")?>:</td>
		<td><textarea id="__snippet_description" rows="9"></textarea></td>
	</tr>
</table>

<?elseif($name == "edit_hbf"):?>
<script>
function OnLoad()
{
	window.oBXEditorDialog.SetTitle('<?= GetMessageJS("FILEMAN_ED_EDIT_HBF")?>');
	// TAB #1: HEAD
	BX.addClass(window.oBXEditorDialog.PARTS.CONTENT_DATA, "bxed-dialog");

	var oDiv = BX("__bx_head");
	oDiv.appendChild(BX.create("TEXTAREA", {props: {id: "__bx_head_ta", value: pObj.pMainObj._head + pObj.pMainObj._body}, style: {width: "99%", height: "280px"}}));
	oDiv.appendChild(BX.create("A", {props: {href: 'javascript: void("")', title: '<?= GetMessageJS("FILEMAN_ED_INSERT_DEF")?>'}, text: '<?= GetMessageJS("FILEMAN_ED_INSERT_DEF")?>', style: {marginTop: '13px', display: 'inline-block'}})).onclick = insertDefault_head;

	// TAB #2: Footer
	oDiv = BX("__bx_footer");
	oDiv.appendChild(BX.create("TEXTAREA", {props: {id: "__bx_footer_ta", value: pObj.pMainObj._footer}, style:{width: "99%", height: "280px"}}));
	oDiv.appendChild(BX.create("A", {props: {href: 'javascript: void("")', title: '<?= GetMessageJS("FILEMAN_ED_INSERT_DEF")?>'}, text: '<?= GetMessageJS("FILEMAN_ED_INSERT_DEF")?>', style: {marginTop: '13px', display: 'inline-block'}})).onclick = insertDefault_footer;

	window.oBXEditorDialog.adjustSizeEx();
}

function OnSave()
{
	BX("__bx_head_ta").value.replace(/(^[\s\S]*?)(<body.*?>)/i, "");
	pObj.pMainObj._head = RegExp.$1;
	pObj.pMainObj._body = RegExp.$2;

	pObj.pMainObj._footer = BX("__bx_footer_ta").value;
	pObj.pMainObj.updateBody();
}

function insertDefault_head()
{
	if (!confirm("<?=GetMessage("FILEMAN_ED_CONFIRM_HEAD")?>"))
		return;

	var oTA = BX("__bx_head_ta");
	var s60 = String.fromCharCode(60);
	var s62 = String.fromCharCode(62);
	oTA.value = s60 + '?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?' + s62 + '<' + '!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'+"\n"+
	'<html>'+"\n"+
	'<head>'+"\n"+
	'<meta http-equiv="Content-Type" content="text/html; charset='+s60+'?echo LANG_CHARSET;?'+s62+'">'+"\n"+
	s60+'?$APPLICATION->ShowMeta("keywords")?'+s62+"\n"+
	s60+'?$APPLICATION->ShowMeta("description")?'+s62+"\n"+
	'<title>'+s60+'?$APPLICATION->ShowTitle()?'+s62+'</title>'+"\n"+
	s60+'?$APPLICATION->ShowCSS();?'+s62+"\n"+
	s60+'?$APPLICATION->ShowHeadStrings()?'+s62+"\n"+
	s60+'?$APPLICATION->ShowHeadScripts()?'+s62+"\n"+
	"</head>\n"+
	'<body>';
}

function insertDefault_footer()
{
	if (!confirm("<?=GetMessage("FILEMAN_ED_CONFIRM_FOOTER")?>"))
		return;
	var oTA = BX("__bx_footer_ta");
	oTA.value = "</body>\n</html>";
}
</script>
<?
$aTabs_dialog = array(
array("DIV" => "__bx_head", "TAB" => GetMessage("FILEMAN_ED_TOP_AREA"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_EDIT_HEAD"), "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();"),
array("DIV" => "__bx_footer", "TAB" => GetMessage("FILEMAN_ED_BOTTOM_AREA"), "ICON" => "", "TITLE" => GetMessage("FILEMAN_ED_EDIT_FOOTER"), "ONSELECT" => "window.oBXEditorDialog.adjustSizeEx();")
);
$tabControlDialog = new CAdminTabControl("tabControlDialog_templ", $aTabs_dialog, false, true);

$tabControlDialog->Begin();?>
<?$tabControlDialog->BeginNextTab();?>
<div id="__bx_head"></div>
<?$tabControlDialog->BeginNextTab();?>
<div id="__bx_footer"></div>
<?$tabControlDialog->End();?>
<?endif;?>

<script>
	if (!window.oBXEditorDialog.bUseTabControl)
	{
		window.oBXEditorDialog.Show();
		window.oBXEditorDialog.SetContent('<?= CUtil::JSEscape($dialogHTML)?>');
		OnLoad(window.oBXEditorDialog.editorParams || {});
	}
	else
	{
		CloseWaitWindow();
		OnLoad();
	}
	BX.addClass(window.oBXEditorDialog.PARTS.CONTENT_DATA, "bxed-dialog");
	window.oBXEditorDialog.PARTS.CONTENT_DATA.style.height = 'auto';

	BX.addCustomEvent(window.oBXEditorDialog, 'onWindowUnRegister', function()
	{
		if (window.oBXEditorDialog && window.oBXEditorDialog.DIV && window.oBXEditorDialog.DIV.parentNode)
			window.oBXEditorDialog.DIV.parentNode.removeChild(window.oBXEditorDialog.DIV);
	});

	// Set default buttons
	if (!window.oBXEditorDialog.PARAMS.buttons || !window.oBXEditorDialog.PARAMS.buttons.length)
	{
		window.oBXEditorDialog.SetButtons([
			new BX.CWindowButton(
			{
				title: '<?= GetMessage("FILEMAN_ED_SAVE")?>',
				id: 'save',
				name: 'save',
				className: 'adm-btn-save',
				action: function()
				{
					var r;
					if(window.OnSave && typeof window.OnSave == 'function')
						r = window.OnSave();
					//if((r & 'NoOnSelectionChange') != 0)
					//	pObj.pMainObj.OnEvent("OnSelectionChange", ["always"]);
					if (r !== false)
						window.oBXEditorDialog.Close();
				}
			}),
			window.oBXEditorDialog.btnClose
		]);
	}
</script>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
