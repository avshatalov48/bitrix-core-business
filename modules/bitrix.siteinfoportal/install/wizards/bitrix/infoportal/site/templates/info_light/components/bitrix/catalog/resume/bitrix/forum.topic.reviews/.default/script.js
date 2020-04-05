if (typeof oForumForm != "object")
	var oForumForm = {};
var MessageMax = 64000;

function quoteMessageEx(author, mid)
{
	if (typeof document.forms["REPLIER"] != "object" || document.forms["REPLIER"] == null)
		return false;
	init_form(document.forms["REPLIER"]);
	oForumForm[document.forms["REPLIER"].id].quote(author, mid);
}

function CreatePalette()
{
	if (oForumForm['PALETTE'])
		return oForumForm['PALETTE'];
	var color_range = {0 : "00", 1 : "33", 2 : "66", 3 : "99", 4 : "BB", 5 : "FF"};
	var rgb = {'R' : 0, 'G' : 0, 'B' : 0, 'color' : ''};
	var oDiv = document.body.appendChild(document.createElement("DIV"));
	oDiv.id = 'palette';
	oDiv.className = 'palette';
	oDiv.style.position = 'absolute';
	oDiv.style.width = '199px';
	oDiv.style.height = '133px';
	oDiv.style.border = 'none';
	oDiv.style.visibility = 'hidden';
	var table = document.createElement("TABLE");
	table.className = 'palette';
	var tbody = table.appendChild(document.createElement('TBODY'));
	for (var ii = 0; ii < 216; ii++)
	{
		if (ii%18 == 0)
			var row = tbody.appendChild(document.createElement('TR'));
		rgb['R'] = ii%6; rgb['G'] = Math.round(ii/36)%6; rgb['B'] = Math.round(ii/6)%6;
		rgb['color'] = '' + color_range[rgb['R']] + '' + color_range[rgb['G']] + '' + color_range[rgb['B']];
		var cell = row.appendChild(document.createElement('TD'));
		var img = cell.appendChild(document.createElement('IMG'));
		img.src = '/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif';
		cell.id = 'color_' + rgb['color'];
		cell.style.backgroundColor = '#' + rgb['color'];
		cell.onmousedown = function(e){
				e = (jsUtils.IsIE() || !e ? window.event : e); 
				jsUtils.PreventDefault(e);
				window.color_palette = '#' + this.id.replace('color_', '');}
	}
	oDiv.appendChild(table);
	oForumForm['PALETTE'] = oDiv;
	return oForumForm['PALETTE'];
}

/* Form functions */
function init_form(form)
{
	if (typeof(form) != "object")
		return false;
	if (!oForumForm[form.id] || typeof(oForumForm[form.id]) != "object")
	{
		oForumForm[form.id] = new PostForm(form);
		oForumForm[form.id].Init(form);
		oForumForm[form.id].onkeydown = function(e){};
		oForumForm[form.id].onmouseover = function(e){};
	}
	return;
}

function PostForm()
{
	this.real_tags = {};
	this.form = false; 
	this.stack = [];
	this.tags =  {
		"B" : "simple_tag", 
		"I" : "simple_tag", 
		"U" : "simple_tag",  
		"S" : "simple_tag",  
		"CODE" : "simple_tag", 
		"QUOTE" : "simple_tag", 
		"COLOR" : "simple_tag", 
		"FONT" : "simple_tag", 
		"SIZE" : "simple_tag", 
		"PALETTE" : "show_palette", 
		"URL" : "tag_url", 
		"IMG" : "tag_image", 
		"LIST" : "tag_list",
		"VIDEO" : "tag_video",
		"TRANSLIT" : "translit", 
		"SMILES_DINAMIC" : "show_smiles_dinamic", 
		"SMILES_STATIC" : "show_smiles_static"};
	this.template = '<form class="forum-form" id="forum_#ID#_form" name="forum_#ID#_form" onsubmit="return false;">' + 
		'<table class="forum-popup" cellpadding="0" cellspacing="0" border="0"><thead><tr><td>' + 
			'<div class="close" onmousedown="oForumForm[\'#FORM_ID#\'].check_submit(event, \'C\', \'#ID#\');" return false;">' + 
				'<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></div>' + 
			'#TITLE#</td></tr></thead>' + 
		'<tbody><tr><td>#BODY#</td></tr></tbody>' + 
		'<tfoot><tr><td>' + 
			'<button type="button" name="ok" onclick="oForumForm[\'#FORM_ID#\'].check_submit(event, \'OK\', \'#ID#\');">' + 
				oText['BUTTON_OK'] + '</button>' + 
			'<button type="button" name="cancel" onclick="oForumForm[\'#FORM_ID#\'].check_submit(event, \'C\', \'#ID#\');">' + 
				oText['BUTTON_CANCEL'] + '</button>' + 
/*			'<div class="button ok" onmouseover="this.className+=\' over\'" onmouseout="this.className=\'button ok\'" ' + 
				' onclick="this.className+=\' click\'; ">' + 
				'<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></div>' + 
			'<div class="button cancel" onmouseover="this.className+=\' over\'" onmouseout="this.className=\'button cancel\'" ' + 
				' onclick="this.className+=\' click\'; oForumForm[\'#FORM_ID#\'].check_submit(event, \'C\', \'#ID#\');">' + 
				'<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></div>' + */ 
		'</td></tr></tfoot></table></form>';
	this.item_template = '<div class="forum-field"><span class="title">#TITLE#</span>#BODY#</div>';
	
		
	this.additional_params = {"translit" : 0};
	this.oCursor = {"text" : "", "start" : 0, "end" : 0};
	this.nav = 'none';
	var t = this;
	this.popupMenu = false;
	this.bTranslited = false;
	this.active = null;

	this.Init = function(form)
	{
		if (this.form)
			return true;
		if (typeof(form) != "object" || typeof(form["REVIEW_TEXT"]) != "object")
			return false;
		this.form = form;
		/* Simple tags */ 
		var oDivs = this.form.getElementsByTagName('A');
		if (oDivs && oDivs.length > 0)
		{
			for (var ii = 0; ii < oDivs.length; ii++)
			{
				if (!(oDivs[ii] && oDivs[ii].id && oDivs[ii].id.substring(0, 5) == "form_"))
					continue;
				oDiv = oDivs[ii];
				if (!oDiv.id || oDiv.name == "smiles")
					continue;
				id = oDiv.id.substring(5).toUpperCase();
				
				oDiv.onclick = oDiv.onmousedown = function(e){
					e = (jsUtils.IsIE() || !e ? window.event : e); 
					jsUtils.PreventDefault(e);
					if (e.type == 'mousedown' && jsUtils.IsOpera())
					{
						t.saveSelection = t.GetSelection();
					}
					if (e.type == 'click')
					{
						t.format_text(this, e);
						t.saveSelection = false;
					}
				}
				oDiv.onmouseover = function(){this.className += ' marked';};
				oDiv.onmouseout = function(){this.className = this.className.replace(/marked/, '').replace('  ', ' ');};

				if (jsUtils.IsOpera() && oDiv.title)
					oDiv.title = oDiv.title.replace(/\(alt+([^)])+\)/gi, '');
				this.real_tags[id] = oDiv;
			}
		}
		if (this.form['FONT'])
		{
			this.form['FONT'].onmousedown = function(e){t.saveSelection = t.GetSelection();};
			this.form['FONT'].onchange = function(e){t.format_text(this, e)};
			this.form['FONT'].onmouseover = function(){this.className += ' marked';};
			this.form['FONT'].onmouseout = function(){this.className = this.className.replace(/marked/, '').replace('  ', ' ');};
		}
		// Smiles
		var image = this.form.getElementsByTagName("img");
		if (image && image.length > 0)
		{
			for (var ii = 0; ii < image.length; ii++ )
			{
				if (image[ii].className == "smiles" || image[ii].className == "smiles-list")
				{
					image[ii].onclick = image[ii].onmousedown = function(e){
						e = (jsUtils.IsIE() || !e ? window.event : e); 
						jsUtils.PreventDefault(e);
						if (e.type == 'click')
						{
							t.emoticon(this);
						}
					}
					this.real_tags["SMILES"] = true;
				}
			}
		}
		this.form["REVIEW_TEXT"].onkeyup = t.check_keyup;
		this.form["REVIEW_TEXT"].onkeypress = t.check_ctrl_enter;
		this.form["REVIEW_TEXT"].onfocus = function(e){this.hasfocus = true};
		this.form["REVIEW_TEXT"].onblur = function(e){this.hasfocus = false;};
		this.template = this.template.replace(/\#FORM_ID\#/g, this.form.id);
		return true;
	}, 
	this.check_submit = function(e, action, id)
	{
		e = (jsUtils.IsIE() || !e ? window.event : e); 
		if (e)
			jsUtils.PreventDefault(e);
		id = (id == false ? t.active : id);
		var close = true;
		if (action == 'OK')
		{
			close = t["tag_" + id]('format_text');
		}
		if (close)
			t.Hide();
	},
	this.check_keypress = function(e)
	{
		if(!e) e = window.event
		if(!e) return false;
		if(e.keyCode == 27)
			t.check_submit(false, 'C', false);
		return;
	}, 
	this.check_keyup = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(!e.altKey) return;
		if(e.keyCode == 73)
			t.format_text({'id' : 'form_i', 'value' : ''}, e);
		else if(e.keyCode == 85)
			t.format_text({'id' : 'form_u', 'value' : ''}, e);
		else if(e.keyCode == 66)
			t.format_text({'id' : 'form_b', 'value' : ''}, e);
		else if(e.keyCode == 81)
			t.format_text({'id' : 'form_quote', 'value' : ''}, e);
		else if(e.keyCode == 80)
			t.format_text({'id' : 'form_code', 'value' : ''}, e);
		else if(e.keyCode == 71)
			t.tag_image();
		else if(e.keyCode == 72)
			t.tag_url(true);
		else if(e.keyCode == 76)
			t.tag_list();
	},
	
	this.check_ctrl_enter = function(e)
	{
		if(!e) 
			e = window.event;
		if((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey && ValidateForm(t.form))
			t.form.submit();
		return;
	},
	
	this.GetSelection = function()
	{
		if (this.form["REVIEW_TEXT"].hasfocus == true && typeof(this.form["REVIEW_TEXT"].selectionStart) != 'undefined')
		{
			return this.form["REVIEW_TEXT"].value.substr(this.form["REVIEW_TEXT"].selectionStart, 
				this.form["REVIEW_TEXT"].selectionEnd - this.form["REVIEW_TEXT"].selectionStart);
		}
		else if (this.saveSelection)
		{
			return this.saveSelection;
		}
		else if (document.selection && document.selection.createRange)
		{
			return document.selection.createRange().text;
		}
		else if (window.getSelection)
		{
			return window.getSelection() + '';
		}
		else
		{
			return false;
		}
	}, 

	this.Show = function(id, data)
	{
		if (!data)
			return false;
		var oDiv = document.getElementById('forum_popup_' + this.form.id + '_post_form');
		if (!oDiv)
		{
			oDiv = document.body.appendChild(document.createElement("DIV"));
			oDiv.id = 'forum_popup_' + this.form.id + '_post_form';
		}
		oDiv.style.visible = 'hidden';
		oDiv.className = "forum-popup-postform";
		oDiv.style.position = 'absolute';
		oDiv.innerHTML = data;
		
		jsUtils.addEvent(document, "keypress", t.check_keypress);
		var res = jsUtils.GetWindowSize();
		var pos = {};
		if (t.active != id)
		{
			this.Hide();
			t.active = id;
			pos['top'] = parseInt(res["scrollTop"] + res["innerHeight"]/2 - oDiv.offsetHeight/2);
			pos['left'] = parseInt(res["scrollLeft"] + res["innerWidth"]/2 - oDiv.offsetWidth/2);
			FJCFloatDiv.Show(oDiv, pos["left"], pos["top"], false, true, true);
		}
		
		return false;
	}, 
	this.Hide = function()
	{
		if (t.active == null)
			return false;
		t.active = null;
		var oDiv = document.getElementById('forum_popup_' + t.form.id + '_post_form');
		FJCFloatDiv.Close(oDiv);
		jsUtils.removeEvent(document, "keypress", t.check_keypress);
		return false;
	}, 
	
	this.Insert = function (ibTag, ibClsTag, postText)
	{
		ibTag = (ibTag && ibTag.length > 0 ? ibTag : "");
		ibClsTag = (ibClsTag && ibClsTag.length > 0 ? ibClsTag : "");
		postText = (postText && postText.length > 0 ? postText : "");
		if (ibTag.length <= 0 && ibClsTag.length <= 0 && postText.length <= 0)
			return true;
		var bReplaceText = (!postText ? false : true);
		var sSelectionText = this.GetSelection();

		if (!this.form["REVIEW_TEXT"].hasfocus)
		{
			this.form["REVIEW_TEXT"].focus();
		}

		var isSelect = (sSelectionText ? 'select' : bReplaceText ? 'after' : 'in');
		if (bReplaceText)
			postText = ibTag + postText + ibClsTag;
		else if (sSelectionText)
			postText = ibTag + sSelectionText + ibClsTag;
		else
			postText = ibTag + ibClsTag;

		if (typeof(this.form["REVIEW_TEXT"].selectionStart) != 'undefined')
		{
			var currentScroll = this.form["REVIEW_TEXT"].scrollTop;
			var selection = {
				"start" : this.form["REVIEW_TEXT"].selectionStart, 
				"end" : this.form["REVIEW_TEXT"].selectionEnd};

			this.form["REVIEW_TEXT"].value = this.form["REVIEW_TEXT"].value.substr(0, selection["start"]) + 
				postText + this.form["REVIEW_TEXT"].value.substr(selection["end"]);

			if (isSelect == 'select')
			{
				this.form["REVIEW_TEXT"].selectionStart = selection["start"];
				this.form["REVIEW_TEXT"].selectionEnd = selection["start"] + postText.length;
			}
			else if (isSelect == 'in')
			{
				this.form["REVIEW_TEXT"].selectionStart = selection["start"] + ibTag.length;
				this.form["REVIEW_TEXT"].selectionEnd = selection["start"] + ibTag.length;
			}
			else
			{
				this.form["REVIEW_TEXT"].selectionStart = selection["start"] + postText.length;
				this.form["REVIEW_TEXT"].selectionEnd = selection["start"] + postText.length;
			}
			this.form["REVIEW_TEXT"].scrollTop = currentScroll;
		}
		else if (document.selection && document.selection.createRange)
		{
			var sel = document.selection.createRange();
			var selection_copy = sel.duplicate();
			postText = postText.replace(/\r?\n/g, '\r\n'); 
			sel.text = postText;
			sel.setEndPoint('StartToStart', selection_copy); 
			sel.setEndPoint('EndToEnd', selection_copy); 
			if (isSelect == 'select')
			{
				sel.collapse(true);
				postText = postText.replace(/\r\n/g, '1');
				sel.moveEnd('character', postText.length);
				
			}
			else if (isSelect == 'in')
			{
				sel.collapse(false);
				sel.moveEnd('character', ibTag.length);
				sel.collapse(false);
			}
			else
			{
				sel.collapse(false);
				sel.moveEnd('character', postText.length);
				sel.collapse(false);
			}
			sel.select();
		}
		else
		{
			// failed - just stuff it at the end of the message
			this.form["REVIEW_TEXT"].value += text;
		}
		return true;
	},
	
	this.format_text = function(oObj, e)
	{
		e = (jsUtils.IsIE() || !e ? window.event : e); 
		jsUtils.PreventDefault(e);
		if (!oObj || !oObj.id)
			return false;
		var id = oObj.id.substring(5).toUpperCase();

		if (this.tags[id] == 'simple_tag')
		{
			var tag_start = tag_name = id;
			if (tag_name == 'FONT' || tag_name == 'COLOR')
				tag_start += "=" + oObj.value;

			if ((!jsUtils.in_array(tag_name, this.stack) || this.GetSelection()) && 
				 !(tag_name == 'FONT' && oObj.value == 'none'))
			{
				if (!this.Insert("[" + tag_start + "]", "[/" + tag_name + "]"))
				{
					this.stack.push(tag_name);
					if (this.real_tags[id])
						this.real_tags[id].className += ' opened';
				}
			}
			else
			{
				var res = false;
				while (res = this.stack.pop())
				{
					this.Insert("[/" + res + "]", "");
					if (this.real_tags[res])
						this.real_tags[res].className = this.real_tags[res].className.replace(/opened/, '').replace('  ', ' ');
					if (res == tag_name)
						break;
				}
			}
		}
		else if (this.tags[id] == 'show_palette')
		{
			this.show_palette(oObj);
		}
		else if (this.tags[id] == 'show_smiles_dinamic')
		{
			this.show_smiles_dinamic(oObj);
		}
		else if (this.tags[id] == 'show_smiles_static')
		{
			this.show_smiles_static(oObj);
		}
		else if (this.tags[id] == 'translit')
		{
			res = this.translit();
			if (this.real_tags[id])
			{
				if (res)
					this.real_tags[id].className += ' opened translited';
				else
					this.real_tags[id].className = this.real_tags[id].className.replace(/opened/, '').replace(/translited/, '').replace('  ', ' ');
			}
		}
		else if (this.tags[id])
		{
			this[this.tags[id]](true);
		}
	}, 
	
	this.emoticon = function(element)
	{
		this.Insert(" ", " ", element.alt);
	},
	
	this.tag_image = function()
	{
		var FoundErrors = '';
		var need_loop = true;
		var oFields = {
			"URL" : {
				"text" : oText['enter_image'],
				"default" : "http://",
				"error" : oErrors['no_url'],
				"value" : ""}};
		var selection = t.GetSelection();
		if (selection != false && selection.search(/^(http|https|news|ftp|aim|mailto)\:\/\//gi) > -1)
		{
			oFields["URL"]["default"] = selection;
		}
		else
		{
			selection = false;
		}
		
		for (var ii in oFields)
		{
			need_loop = true;
			do 
			{
				var res = prompt(oFields[ii]["text"], oFields[ii]["default"]);
				if (res == null)
				{
					need_loop = false;
					return false;
				}
				else if (res.length <= 0)
				{
					alert("Error! " + oFields[ii]["error"]);
				}
				else
				{
					oFields[ii]["value"] = res;
					need_loop = false;
				}
			}
			while(need_loop);
		}
		this.Insert("[IMG]", "[/IMG]", oFields["URL"]["value"]);
	},
	
	this.tag_video = function(action)
	{
		action = (action == 'format_text' ? 'format_text' : 'show_data');
		var sError = "";
		var path = "";
		var selection = t.GetSelection();
		if (selection != false && selection.search(/^(http|https|news|ftp|aim|mailto)\:\/\//gi) > -1)
			path = selection.replace(/[\<\>]/gi, "");
		var sBody = "";
		if (action == 'format_text')
		{
			var form = false;
			for(var ii in document.forms)
			{
				if (document.forms[ii].name == 'forum_video_form')
					form = document.forms[ii];
			}
			if (!form)
				return true;
			var width = parseInt(form["width"].value);
			var height = parseInt(form["height"].value);
			var path = form["path"].value;
			var preview = form["preview"].value;
			if (path.length <= 0)
			{
				action = 'show_data';
				sBody = "<div class='forum-error'>" + oErrors['no_path'] + "</div>";
			}
		}
		if (action == 'show_data')
		{
			sBody = sBody + 
				this.item_template.replace(/\#TITLE\#/g, oText["path"]).replace(/\#BODY\#/g, '<input type="text" name="path" value="' + path + '">') + 
				this.item_template.replace(/\#TITLE\#/g, oText["preview"]).replace(/\#BODY\#/g, '<input type="text" name="preview" value="">') + 
				this.item_template.replace(/\#TITLE\#/g, oText["width"]).replace(/\#BODY\#/g, '<input type="text" name="width" value="400">') + 
				this.item_template.replace(/\#TITLE\#/g, oText["height"]).replace(/\#BODY\#/g, '<input type="text" name="height" value="300">');
			var sData = this.template.replace(/\#ID\#/g, "video").replace(/\#TITLE\#/g, oText["video"]).replace(/\#BODY\#/g, sBody);
			this.Show('video', sData);
			return false;
		}
		this.Insert("[VIDEO WIDTH=" + width + " HEIGHT=" + height +
			(preview.length > 0 ? ' PREVIEW="' + preview + '"' : '') + "]", "[/VIDEO]", path);
		return true;
	}, 
	
	this.tag_list = function()
	{ 
		var thelist = "";
		var need_loop = true;
		do 
		{
			var res = prompt(oText['list_prompt'], "");
			if (res == null)
			{
				need_loop = false;
				return false;
			}
			else if (res.length <= 0)
			{
				need_loop = false;
			}
			else
			{
				thelist = thelist + "[*]" + res + "\n";
			}
		}
		while(need_loop);
		this.Insert("[LIST]\n", "[/LIST]\n", thelist);
	},
	
	this.closeall = function()
	{
		var res = false;
		while(res = this.stack.pop())
		{
			this.Insert("[/" + res + "]", "");
			if (this.real_tags[res])
				this.real_tags[res].className = this.real_tags[res].className.replace(/opened/, '').replace('  ', ' ');
		}
	},
	
	this.tag_url = function()
	{
		var FoundErrors = '';
		var need_loop = true;
		var oFields = {
			"URL" : {
				"text" : oText['enter_url'],
				"default" : "http://",
				"error" : oErrors['no_url'],
				"value" : ""}, 
			"TITLE" : {
				"text" : oText['enter_url_name'],
				"default" : "My Webpage",
				"error" : oErrors['no_title'],
				"value" : ""}};
		var selection = t.GetSelection();
		if (selection != false)
		{
			if (selection.search(/^(http|https|news|ftp|aim|mailto)\:\/\//gi) > -1)
			{
				oFields["URL"]["default"] = selection;
			}
			else
			{
				oFields["TITLE"]["default"] = selection;
			}
		}
		
		for (var ii in oFields)
		{
			need_loop = true;
			do 
			{
				var res = prompt(oFields[ii]["text"], oFields[ii]["default"]);
				if (res == null)
				{
					need_loop = false;
					return false;
				}
				else if (res.length <= 0)
				{
					alert("Error! " + oFields[ii]["error"]);
				}
				else
				{
					oFields[ii]["value"] = res;
					need_loop = false;
				}
			}
			while(need_loop);
		}
		this.Insert("[URL=" + oFields["URL"]["value"] + "]", "[/URL]", oFields["TITLE"]["value"]);
		return false;
	},
	
	this.translit = function()
	{
		var i = 0;
		var textbody = this.form['REVIEW_TEXT'].value;
		var selection = this.GetSelection();
		if (selection != false)
		{
			textbody = selection;
		}

		if (this.bTranslited == false)
		{
			for (i=0; i<capitEngLettersReg.length; i++) textbody = textbody.replace(capitEngLettersReg[i], capitRusLetters[i]);
			for (i=0; i<smallEngLettersReg.length; i++) textbody = textbody.replace(smallEngLettersReg[i], smallRusLetters[i]);
			this.bTranslited = true;
		}
		else
		{
			for (i=0; i<capitRusLetters.length; i++) textbody = textbody.replace(capitRusLettersReg[i], capitEngLetters[i]);
			for (i=0; i<smallRusLetters.length; i++) textbody = textbody.replace(smallRusLettersReg[i], smallEngLetters[i]);
			this.bTranslited = false;
		}
		
		if (selection != false)
		{
			this.Insert("", "", textbody);
		}
		else
		{
			this.form['REVIEW_TEXT'].value = textbody;
		}
		return this.bTranslited;
	},
	
	this.quote = function (author, mid)
	{
		var selection = "";
		var message_id = 0;
		selection = this.GetSelection();
		if (document.getSelection)
		{
			selection = selection.replace(/\r\n\r\n/gi, "_newstringhere_").replace(/\r\n/gi, " ");
			selection = selection.replace(/  /gi, "").replace(/_newstringhere_/gi, "\r\n\r\n");
		}
		if (selection == "" && mid)
		{
			message_id = parseInt(mid.replace(/message_text_/gi, ""));
			if (message_id > 0)
			{
				var message = document.getElementById(mid);
				if (typeof(message) == "object" && message)
				{
					selection = message.innerHTML;
					selection = selection.replace(/\<br(\s)*(\/)*\>/gi, "\n").replace(/\<script[^\>]*>/gi, '\001').replace(/\<\/script[^\>]*>/gi, '\002');
					selection = selection.replace(/\<noscript[^\>]*>/gi, '\003').replace(/\<\/noscript[^\>]*>/gi, '\004');
					selection = selection.replace(/\001([^\002]*)\002/gi, " ").replace(/\003([^\004]*)\004/gi, " ");
					// Quote & Code
					selection = selection.replace(/\<table class\=\"forum-quote\"\>\<thead\>\<tr\>\<th\>([^<]+)\<\/th\>\<\/tr\>\<\/thead\>\<tbody\>\<tr\>\<td\>/gi, "\001").replace(/\<table class\=\"forum-code\"\>\<thead\>\<tr\>\<th\>([^<]+)\<\/th\>\<\/tr\>\<\/thead\>\<tbody\>\<tr\>\<td\>/gi, "\002").replace(/\<\/td\>\<\/tr\>\<\/tbody\>\<\/table\>/gi, "\003"); 
					var ii = 0;
					while(ii < 50 && (selection.search(/\002([^\002\003]*)\003/gi) >= 0 || selection.search(/\001([^\001\003]*)\003/gi) >= 0))
					{
						ii++;
						selection = selection.replace(/\002([^\002\003]*)\003/gi, "[CODE]$1[/CODE]").replace(/\001([^\001\003]*)\003/gi, "[QUOTE]$1[/QUOTE]");
					}
					selection = selection.replace(/[\001\002\003]/gi, "");
					// Smiles 
					if (this.real_tags["SMILES"])
						selection = selection.replace(/\<img[^>]+alt=\"smile([^\"]+)\"[^>]+\>/gi, "$1");
					// Hrefs 
					if (this.real_tags["URL"])
					{
						selection = selection.replace(/\<a[^>]+href=[\"]([^\"]+)\"[^>]+\>([^<]+)\<\/a\>/gi, "[URL=$1]$2[/URL]");
						selection = selection.replace(/\<a[^>]+href=[\']([^\']+)\'[^>]+\>([^<]+)\<\/a\>/gi, "[URL=$1]$2[/URL]");
					}
					selection = selection.replace(/\<[^\>]+\>/gi, " ").replace(/&lt;/gi, "<").replace(/&gt;/gi, ">").replace(/&quot;/gi, "\"");
				}
			}
			else if (mid.length > 0)
			{
				selection = mid;
			}
		}
		if (selection != "")
		{
			selection = selection.replace(/\&shy;/gi, "");
			if (author != null && author)
				selection = author + oText['author'] + selection;
			this.Insert("[QUOTE]", "[/QUOTE]", selection);
			return true;
		}
		return false;
	}, 
	
	this.show_palette = function(oObj)
	{
		if (!oObj){return false};
		var oPalette = CreatePalette();
		if (!this.popupMenu)
		{
			window.ForumPopupMenu.prototype.ShowMenu = function(control, div)
			{
				var pos = {"top" : 20, "left" : 20};
				this.PopupHide();
				if (typeof(control) == "object")
				{
					id = control.id;
					pos = jsUtils.GetRealPos(control);
					this.ControlPos = pos;
					this.oControl = control;
				}
				
				this.oDiv = div;
				if (this.oDiv)
				{
					pos["top"] = pos["bottom"];
					this.PopupShow(pos, this.oDiv);
				}
			}
			window.ForumPopupMenu.prototype.CheckClick = function(e)
			{
				if(!this.oDiv){return;}
				if (this.oDiv.style.visibility != 'visible' || this.oDiv.style.display == 'none')
					return;
		        var windowSize = jsUtils.GetWindowSize();
		        var x = e.clientX + windowSize.scrollLeft;
		        var y = e.clientY + windowSize.scrollTop;
		
				/*menu region*/
				pos = jsUtils.GetRealPos(this.oDiv);
				var posLeft = parseInt(pos["left"]);
				var posTop = parseInt(pos["top"])
				var posRight = posLeft + this.oDiv.offsetWidth;
				var posBottom = posTop + this.oDiv.offsetHeight;
				if(x >= posLeft && x <= posRight && y >= posTop && y <= posBottom)
				{
					if (window.color_palette)
					{
						t.format_text({'id' : 'form_color', 'value' : window.color_palette, 'className' : ''}, e);
						this.PopupHide();
					}
				}
		
				if(this.ControlPos)
				{
					var pos = this.ControlPos;
					if(x >= pos['left'] && x <= pos['right'] && y >= pos['top'] && y <= pos['bottom'])
						return;
				}
				this.PopupHide();
			}
			
			this.popupMenu = new ForumPopupMenu();
		}
		this.popupMenu.ShowMenu(oObj, oPalette);
	}, 
	
	this.show_smiles_dinamic = function(oObj, status, send_data)
	{
		if (!oObj || !this.form){return false};
		status = (status == 'hide' ? 'hide' : 'show');
		send_data = (send_data == "N" ? "N" : "Y");
		var index = this.form.name.replace("REPLIER", "");
		if (!this.oDivSmiles)
		{
			this.oDivSmiles = document.getElementById('forum_smiles_line' + index);
			this.real_tags['SMILES_DINAMIC'].style.visibility = 'hidden';
			this.real_tags['SMILES_DINAMIC'].style.display = '';
			this.params_smiles = {'passive':{'width' : (this.form.REVIEW_TEXT.offsetWidth - this.real_tags['SMILES_DINAMIC'].offsetWidth)}, 
				'active' : {'width' : this.form.REVIEW_TEXT.offsetWidth}};
			this.real_tags['SMILES_DINAMIC'].style.display = 'none';
			this.real_tags['SMILES_DINAMIC'].style.visibility = 'visible';

			var res = this.real_tags['SMILES_DINAMIC'].cloneNode(true);
			res.innerHTML = oText['smile_hide'];
			res.id = res.id + '_hide';
			res.onclick = function(){t.show_smiles_dinamic(this, 'hide'); return false;}
			res.style.display = 'block';
			
			var res1 = document.createElement('DIV');
			res1.className = "forum-reply-field forum-reply-field-hidesmiles";
			res1.appendChild(res);
			this.oDivSmiles.appendChild(res1);
		}
		if (status == 'show')
		{
			this.oDivSmiles.className = this.oDivSmiles.className.replace(/forum\-smiles\-corrected/gi, "");
			this.oDivSmiles.style.width = this.params_smiles['active']['width'] + 'px';
			this.real_tags['SMILES_DINAMIC'].style.display = 'none';
		}
		else
		{
			this.oDivSmiles.className += " forum-smiles-corrected";
			this.oDivSmiles.style.width = this.params_smiles['passive']['width'] + 'px';
			this.real_tags['SMILES_DINAMIC'].style.display = '';
		}
		if (phpVars['isAuthorized'] == "Y" && send_data == "Y")
		{
			var TID = CPHttpRequest.InitThread();
			CPHttpRequest.SetAction(TID, function(){});
			CPHttpRequest.Send(TID, '/bitrix/components/bitrix/forum/templates/.default/user_settings.php', 
			{"save":'smiles_position', "value":status, "sessid":t.form.sessid.value});
		}
		return false;
	}, 
	
	this.show_smiles_static = function(oObj, send_data)
	{
		if (!oObj || !this.form){return false};
		send_data = (send_data == "N" ? "N" : "Y");
		
		var index = this.form.name.replace("REPLIER", "");
		if (oObj.name == 'smile_hide')
		{
			oObj.parentNode.parentNode.previousSibling.style.display = 'block';
			oObj.parentNode.parentNode.style.display = 'none';
		}
		else
		{
			oObj.parentNode.parentNode.style.display = 'none';
			oObj.parentNode.parentNode.nextSibling.style.display = 'block';
		}
		if (phpVars['isAuthorized'] == "Y" && send_data == "Y")
		{
			var TID = CPHttpRequest.InitThread();
			CPHttpRequest.SetAction(TID, function(){});
			CPHttpRequest.Send(TID, '/bitrix/components/bitrix/forum/templates/.default/user_settings.php', 
			{"save":'smiles_position', "value":(oObj.name == 'smile_hide' ? 'hide' : 'show'), "sessid":t.form.sessid.value});
		}
		return false;
	}
}

function ValidateForm(form, ajax_type)
{
	if (typeof form != "object" || typeof form.REVIEW_TEXT != "object")
		return false;
	MessageMax = 64000;
		
	var errors = "";
	var MessageLength = form.REVIEW_TEXT.value.length;

	if (form.TITLE && (form.TITLE.value.length < 2))
		errors += oErrors['no_topic_name'];

	if (MessageLength < 2)
		errors += oErrors['no_message'];
    else if ((MessageMax != 0) && (MessageLength > MessageMax))
		errors += oErrors['max_len'].replace(/\#MAX_LENGTH\#/gi, MessageMax).replace(/\#LENGTH\#/gi, MessageLength);

	if (errors != "")
	{
		alert(errors);
		return false;
	}
	
	var arr = form.getElementsByTagName("submit")
	for (var butt in arr)
		butt.disabled = true;
		
	if (ajax_type == 'Y' && window['ForumPostMessage'])
	{
		ForumPostMessage(form);
	}
	return true;
}
function ShowLastEditReason(checked, div)
{
	if (div)
	{
		if (checked)
			div.style.display = 'block';
		else
			div.style.display = 'none';
	}
}