<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//IncludeTemplateLangFile(__FILE__);
#######
$t_COL=array("00","33","66","99","CC","FF");
?>
<div id="ColorPick" style="visibility:hidden;position:absolute;top:0;left:0 ">
<table cellspacing="0" cellpadding="1" border="0" bgcolor="#666666">
<tr>
<td>
<table cellspacing="1" cellpadding="0" border="0" bgcolor="#FFFFFF">
<?
for($i=0;$i<216;$i++) 
{

$t_R=$i%6;
$t_G=floor($i/36)%6;
$t_B=floor($i/6)%6;

$t_curCOL="#".$t_COL[$t_R].$t_COL[$t_G].$t_COL[$t_B];
print ($i%18==0) ? "<tr>" : "";
print "<td bgcolor='$t_curCOL' onmousedown=\"alterfont('$t_curCOL','COLOR')\"><img src=/bitrix/images/1.gif border=0 width=10 height=10></td>";
}
?>
</table>
</td>
</tr>
</table></div><?
########
?>
<script language=JavaScript>

var B_open = 0;
var I_open = 0;
var U_open = 0;
var QUOTE_open = 0;
var CODE_open = 0;
var CUT_open = 0;
var text_enter_url = "<?echo GetMessage("BPC_TEXT_ENTER_URL");?>";
var text_enter_url_name = "<?echo GetMessage("BPC_TEXT_ENTER_URL_NAME");?>";
var text_enter_image = "<?echo GetMessage("BPC_TEXT_ENTER_IMAGE");?>";
var list_prompt = "<?echo GetMessage("BPC_LIST_PROMPT");?>";
var error_no_url = "<?echo GetMessage("BPC_ERROR_NO_URL");?>";
var error_no_title = "<?echo GetMessage("BPC_ERROR_NO_TITLE");?>";

var bbtags = new Array();

var myAgent = navigator.userAgent.toLowerCase();
var myVersion = parseInt(navigator.appVersion);

var is_ie = ((myAgent.indexOf("msie") != -1) && (myAgent.indexOf("opera") == -1));
var is_nav = ((myAgent.indexOf('mozilla')!=-1) && (myAgent.indexOf('spoofer')==-1)
 && (myAgent.indexOf('compatible') == -1) && (myAgent.indexOf('opera')==-1)
 && (myAgent.indexOf('webtv')==-1) && (myAgent.indexOf('hotjava')==-1));
var is_opera = (myAgent.indexOf("opera") != -1);

var is_win = ((myAgent.indexOf("win")!=-1) || (myAgent.indexOf("16bit") != -1));
var is_mac = (myAgent.indexOf("mac")!=-1);

if (!window.phpVars)  // For anonymus  users
	window.phpVars = {};
function quoteMessageEx(theAuthor, textMessage)
{
	var selection;
	if (document.getSelection)
	{
		selection = document.getSelection();
		selection = selection.replace(/\r\n\r\n/gi, "_newstringhere_");
		selection = selection.replace(/\r\n/gi, " ");
		selection = selection.replace(/ /gi, "");
		selection = selection.replace(/_newstringhere_/gi, "\r\n\r\n");
	}
	else
	{
		selection = document.selection.createRange().text;
	}
	
	if (selection == "")
	{
		selection = textMessage;
	}

	if (selection!="")
	{
		document.getElementById("MESSAGE").value += "[quote]"+theAuthor+":\n"+selection+"[/quote]\n";
	}
}

function emoticon(theSmilie)
{
	doInsert(" " + theSmilie + " ", "", false);
}

// Insert simple tags: B, I, U, CODE, QUOTE
function simpletag(thetag)
{
	var tagOpen = eval(thetag + "_open");

	if (tagOpen == 0)
	{
		if (doInsert("[" + thetag + "]", "[/" + thetag + "]", true))
		{
			eval(thetag + "_open = 1");
			// Change the button status

			pushstack(bbtags, thetag);
			cstat();
		}
	}
	else
	{
		// Find the last occurance of the opened tag
		lastindex = 0;

		for (i = 0 ; i < bbtags.length; i++ )
		{
			if ( bbtags[i] == thetag )
			{
				lastindex = i;
			}
		}

		// Close all tags opened up to that tag was opened
		while (bbtags[lastindex])
		{
			tagRemove = popstack(bbtags);
			doInsert("[/" + tagRemove + "]", "", false);

			// Change the button status
			eval(tagRemove + "_open = 0");
		}

		cstat();
	}
}

// Insert font tag
function alterfont(theval, thetag)
{
	if (theval == 0)
		return;

	if (doInsert("[" + thetag + "=" + theval + "]", "[/" + thetag + "]", true))
		pushstack(bbtags, thetag);

	document.getElementById("select_font").selectedIndex = 0;
	cstat();
	document.getElementById("MESSAGE").focus();
}

// Insert url tag
function tag_url()
{
	var textarea = document.getElementById("MESSAGE");
	var currentScroll = textarea.scrollTop;

	bTitleYes = false;
	if (is_ie)
	{
		textarea.focus();
		var sel = document.selection;
		var rng = sel.createRange();
		rng.colapse;
		if ((sel.type == "Text" || sel.type == "None") && rng.text.length > 0)
		{
			bTitleYes = true;
		}
	}
	else 
	{
		if(textarea.selectionEnd > textarea.selectionStart)
			bTitleYes = true;
	}

	var enterURL = prompt(text_enter_url, "http://");

	if(bTitleYes)
	{
		if(enterURL)
		{
			if(is_ie)
				rng.text = '[URL='+enterURL+']'+rng.text+'[/URL]';
			else
				mozillaWr(textarea, '[URL='+enterURL+']', '[/URL]');
		}
		else
		{
			if(is_ie)
				rng.text = '[URL]'+rng.text+'[/URL]';
			else
				mozillaWr(textarea, '[URL]', '[/URL]');
		}
	}
	else
	{
		if (enterURL)
		{
			var enterTITLE = prompt(text_enter_url_name, "");
			if (!enterTITLE)
				enterTITLE=enterURL;	
			doInsert("[URL="+enterURL+"]"+enterTITLE+"[/URL]", "", false);
		}
	}
	
	textarea.scrollTop = currentScroll;
	textarea.focus();
}

// Insert image tag
function tag_image()
{
	var textarea = document.getElementById("MESSAGE");
	var currentScroll = textarea.scrollTop;
	var enterURL = prompt(text_enter_image, "http://");

	if (enterURL)
	{
	
		try
		{
			var pMainObj = GLOBAL_pMainObj['POST_MESSAGE_HTML'];
		}
		catch(e)
		{
		}

		if(pMainObj && document.getElementById('blg-text-html').checked)
		{
			pMainObj.insertHTML('<img src="'+enterURL+'" __bxsrc="'+enterURL+'" />');
		}
		else
		{
			doInsert("[IMG]"+enterURL+"[/IMG]", "", false);
		}
	}
	textarea.scrollTop = currentScroll;
	textarea.focus();
}

// Insert list tag
function tag_list()
{
	var textarea = document.getElementById("MESSAGE");
	var currentScroll = textarea.scrollTop;

	var listvalue = "init";
	var thelist = "[LIST]\n";

	while ( (listvalue != "") && (listvalue != null) && (listvalue != " ")) 
	{
		listvalue = prompt(list_prompt, "");
		if ( (listvalue != "") && (listvalue != null) && (listvalue != " ")) 
		{
			thelist = thelist+"[*]"+listvalue+"\n";
		}
	}

	doInsert(thelist + "[/LIST]\n", "", false);
	textarea.scrollTop = currentScroll;
	textarea.focus();
}

// Close all tags
function closeall()
{
	if (bbtags[0]) 
	{
		while (bbtags[0]) 
		{
			tagRemove = popstack(bbtags);
			document.getElementById("MESSAGE").value += "[/" + tagRemove + "]";

			if ( (tagRemove != 'FONT') && (tagRemove != 'SIZE') && (tagRemove != 'COLOR') )
			{
				eval(tagRemove + "_open = 0");
			}
		}
	}

	bbtags = new Array();
	cstat();
}

// Stack functions
function pushstack(thearray, newval)
{
	arraysize = stacksize(thearray);
	thearray[arraysize] = newval;
}

function popstack(thearray)
{
	arraysize = stacksize(thearray);
	theval = thearray[arraysize - 1];
	delete thearray[arraysize - 1];
	return theval;
}

function stacksize(thearray)
{
	for (i = 0 ; i < thearray.length; i++ )
	{
		if ( (thearray[i] == "") || (thearray[i] == null) || (thearray == 'undefined') ) 
		{
			return i;
		}
	}

	return thearray.length;
}

// Show statistic
function cstat()
{
	var c = stacksize(bbtags);

	if ( (c < 1) || (c == null) )
	{
		c = 0;
	}

	if ( ! bbtags[0] )
	{
		c = 0;
	}

	if (c > 0)
		document.getElementById("close_all").style.visibility="visible";
	else
		document.getElementById("close_all").style.visibility="hidden";
	document.getElementById("MESSAGE").focus();
}

addEvent(document, "mousedown", function(e){hidePicker();});
addEvent(document, "keypress", function(e){hidePicker();});

var elem_id=0;

function addEvent(el, evname, func)
{
	if(el.attachEvent) // IE
		el.attachEvent("on" + evname, func);
	else if(el.addEventListener) // Gecko / W3C
		el.addEventListener(evname, func, false);
	else
		el["on" + evname] = func;
}

function GetStyleValue(el, styleProp)
{
	if(el.currentStyle)
		var res = el.currentStyle[styleProp];
	else if(window.getComputedStyle)
		var res = document.defaultView.getComputedStyle(el, null).getPropertyValue(styleProp);
	return res;
}
	
function GetRealPos(el)
{
	if(!el || !el.offsetParent)
		return false;
	var res=Array();
	res["left"] = el.offsetLeft;
	res["top"] = el.offsetTop;
	var objParent = el.offsetParent;
	while(objParent.tagName != "BODY")
	{
		if(GetStyleValue(objParent, 'position') == 'static')
		{
			res["left"] += objParent.offsetLeft;
			res["top"] += objParent.offsetTop;
		}
		objParent = objParent.offsetParent;
	}
	res["right"]=res["left"] + el.offsetWidth;
	res["bottom"]=res["top"] + el.offsetHeight;

	return res;
}

function hidePicker() 
{
	document.getElementById('ColorPick').style.visibility = "hidden";
}

function ColorPicker()
{
	try
	{
		var obj = document.getElementById("FontColor");

		res=GetRealPos(obj);
	
		document.getElementById('ColorPick').style.left=res["left"] + "px";
		document.getElementById('ColorPick').style.top=res["top"] + 20 + "px";
		document.getElementById('ColorPick').style.visibility = "visible";
	} catch(e){}
}


function AddCategory()
{
	document.getElementById('category-new').style.display = "block";
	document.getElementById('category-add').style.display = "none";
		/*
		if (res = prompt('<?=GetMessage("BLOG_CATEGORY_NAME")?>',''))
		{
			var exOpt = false;
			for(i=1; i<oSel.options.length; i++)
			{
				if(res == oSel.options[i].text)
				{
					var selectOpt = i;
					exOpt = true;
				}
			}
		
			if(!exOpt)
			{
				var oOption = new Option(res,"ADD");
				if (oSel.options[1].value != "ADD") 
				{
					oOption = oSel.insertBefore(oOption,oSel.options[1]);
					oOption.value = "ADD";
					oOption.innerText = res;
				}
				else
					oSel.options[1] = oOption;
				document.REPLIER.NEW_CATEGORY.value = res;
			}
			else
				oSel.options[selectOpt].selected = true;
		}
		

		if (oSel.options[1].value=="NEW")
			oSel.options[0].selected = true;
		else
		{
			if(exOpt)
				oSel.options[selectOpt].selected = true;
			else
				oSel.options[1].selected = true;
		}
		*/
}

function show_special()
{
	o = document.getElementById('special_perms');
	if (document.getElementById('blog_perms_1').checked==true)
		o.style.display='block';
	else
		o.style.display='none';
		
}

function mozillaWr(textarea, open, close)
{
	var selLength = textarea.textLength;
	var selStart = textarea.selectionStart;
	var selEnd = textarea.selectionEnd;
	
	if (selEnd == 1 || selEnd == 2)
	selEnd = selLength;

	var s1 = (textarea.value).substring(0,selStart);
	var s2 = (textarea.value).substring(selStart, selEnd)
	var s3 = (textarea.value).substring(selEnd, selLength);
	textarea.value = s1 + open + s2 + close + s3;

	textarea.selectionEnd = 0;
	textarea.selectionStart = selEnd + open.length + close.length;
	return;
}

function doInsert(ibTag, ibClsTag, isSingle, imgID)
{
	if(imgID > 0 && document.getElementById('blg-text-html'))
	{
		if( document.getElementById('blg-text-html').checked)
		{
			try
			{
				var pMainObj = GLOBAL_pMainObj['POST_MESSAGE_HTML'];
			}
			catch(e)
			{
			}

			if(pMainObj)
			{
				var imageSrc = document.getElementById(imgID).src;
				pMainObj.insertHTML('<img __bxtagname="blogImage" __bxcontainer="'+imgID+'" src="'+imageSrc+'" />');
				return true;
			}
		}
	}

	var isClose = false;
	var textarea = document.getElementById("MESSAGE");

	if (isSingle)
		isClose = true;
	if (is_ie)
	{
		textarea.focus();
		var sel = document.selection;
		var rng = sel.createRange();
		rng.colapse;
		if ((sel.type == "Text" || sel.type == "None") && rng != null)
		{
			if (ibClsTag != "" && rng.text.length > 0)
			{
				ibTag += rng.text + ibClsTag;
				isClose = false;
			}

			rng.text = ibTag;
		}
	}
	else 
	{
		var currentScroll = textarea.scrollTop;
		if (is_nav && document.getElementById)
		{
			if (ibClsTag != "" && textarea.selectionEnd > textarea.selectionStart)
			{
				mozillaWr(textarea, ibTag, ibClsTag);
				isClose = false;
			}
			else 
				mozillaWr(textarea, ibTag, '');
		}
		else
			textarea.value += ibTag;
		textarea.scrollTop = currentScroll;
	}

	textarea.focus();
	return isClose;
}

function quoteMessage()
{
	var selection;
	if (window.getSelection)
	{
		selection = window.getSelection();
	}
	else
	{
		selection = document.selection.createRange().text;
	}

	if (selection=="")
	{
		simpletag("QUOTE");
	}
	else
	{	
		document.getElementById("MESSAGE").value += "[QUOTE]"+selection+"[/QUOTE]\n";
	}
		
	document.getElementById("MESSAGE").focus();
}

function ShowImageUpload()
{
	win = window.open('<?=$APPLICATION->GetCurPageParam("image_upload=Y")?>',null,'height=150,width=400');
}

function AddCategoryToList()
{
	oSel = document.getElementById('CATEGORY_ID');
	val = document.REPLIER.TAGS.value
	if(val.length>0)
	{
		var exOpt = false;
		for(i=1; i<oSel.options.length; i++)
		{
			if(val == oSel.options[i].text)
			{
				var selectOpt = i;
				exOpt = true;
			}
		}
		oSel.options[0].selected = false;
		if(exOpt)
		{
			oSel.options[selectOpt].selected = true;
		}
		else
		{
			var oOption = new Option(val, "new_"+val);
			oSel.options.add(oOption);
			oSel.options[oOption.index].selected = true;
		}
	}
	document.getElementById('category-new').style.display = "none";
	document.getElementById('category-add').style.display = "block";
	document.REPLIER.TAGS.value = "";
}

function BXBlogSetEditorContent()
{
	try
	{
		var pMainObj = GLOBAL_pMainObj['POST_MESSAGE_HTML'];
	}
	catch(e){}

	if(pMainObj)
	{
		pMainObj.SetEditorContent(document.getElementById("MESSAGE").value);
	}

}

function showEditField(type, change)
{
	var oDivIDHtml = document.getElementById("edit-post-html");
	var oDivIDText = document.getElementById("edit-post-text");
	if(type == "html")
	{
		var oDivIDFlag = document.getElementById("editor_loaded");
		oDivIDText.style.display = "none";
		if(oDivIDHtml)
			oDivIDHtml.style.display = "block";
		if(oDivIDFlag.value == "N")
		{

		
			jsAjaxUtil.InsertDataToNode("<?=$APPLICATION->GetCurPageParam("load_editor=Y", Array("load_editor", "image_upload", "preview"))?>", oDivIDHtml, false);
			oDivIDFlag.value = "Y";
		}
		else
		{
			if(change == "Y")
			{
				try
				{
					var pMainObj = GLOBAL_pMainObj['POST_MESSAGE_HTML'];
				}
				catch(e){}

				if(pMainObj)
				{
					pMainObj.SetEditorContent(document.getElementById("MESSAGE").value);
				}
			}
		}
	}
	else
	{
		if(oDivIDHtml)
			oDivIDHtml.style.display = "none";
		oDivIDText.style.display = "block";
		if(change == "Y")
		{
			try
			{
				var pMainObj = GLOBAL_pMainObj['POST_MESSAGE_HTML'];
			}
			catch(e){}

			if(pMainObj)
			{
				pMainObj.SaveContent()
				document.getElementById("MESSAGE").value = pMainObj.GetContent();
			}
		}

	}
	return false;
}

blogVars = {'templatePath' : '/bitrix/components/bitrix/blog.post.edit/templates/.default/'};

function pJCFloatDiv() 
{
	var _this = this;
	this.floatDiv = null;
	this.x = this.y = 0;

	this.Show = function(div, left, top, dxShadow, bSubstrate, bIframe)
	{
		dxShadow = 5;
		var zIndex = parseInt(div.style.zIndex);
		if(zIndex <= 0 || isNaN(zIndex))
			zIndex = 100;
		div.style.zIndex = zIndex;
		div.style.left = left + "px";
		div.style.top = top + "px";

		if(jsUtils.IsIE() && bIframe != "N")
		{
			var frame = document.getElementById(div.id+"_frame");
			if(!frame)
			{
				frame = document.createElement("IFRAME");
				frame.src = "javascript:''";
				frame.id = div.id+"_frame";
				frame.style.position = 'absolute';
				frame.style.zIndex = zIndex-1;
				document.body.appendChild(frame);
			}
			frame.style.width = div.offsetWidth + "px";
			frame.style.height = div.offsetHeight + "px";
			frame.style.left = div.style.left;
			frame.style.top = div.style.top;
			frame.style.visibility = 'visible';
		}

		/*shadow*/			
		if(dxShadow > 0)
		{
			var img = document.getElementById(div.id+'_shadow');
			if(!img)
			{
				if(jsUtils.IsIE())
				{
		 			img = document.createElement("DIV");
		 			img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+blogVars.templatePath+"images/shadow.png',sizingMethod='scale')";
				}
				else
				{
		 			img = document.createElement("IMG");
					img.src = blogVars.templatePath + 'images/shadow.png';
				}
				img.id = div.id+'_shadow';
				img.style.position = 'absolute';
				img.style.zIndex = zIndex-2;
				document.body.appendChild(img);
			}
			img.style.width = div.offsetWidth+'px';
			img.style.height = div.offsetHeight+'px';
			img.style.left = parseInt(div.style.left)+dxShadow+'px';
			img.style.top = parseInt(div.style.top)+dxShadow+'px';
			img.style.visibility = 'visible';
		}
		
		if (bSubstrate != "N")
		{
			var substrate = document.getElementById("blog_substrate");
			if(!substrate)
			{
				substrate = document.createElement("DIV");
				substrate.id = 	"blog_substrate";
				substrate.style.zIndex = zIndex-3;
				substrate.style.position = 	'absolute';
				substrate.style.display = 'none';
				substrate.style.visibility = 'hidden';
				substrate.style.background = 'white';
				substrate.style.opacity = '0.5';
				if (substrate.style.MozOpacity)
					substrate.style.MozOpacity = '0.5';
				else if (substrate.style.KhtmlOpacity)
					substrate.style.KhtmlOpacity = '0.5';
				if (jsUtils.IsIE())
				{
			 		substrate.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity=50)";
				}
				document.body.appendChild(substrate);
			}
			substrate.style.display = 'block';
			substrate.style.left = 0;
			substrate.style.top = 0;
			var WindowSize = jsUtils.GetWindowSize();
			substrate.style.width = WindowSize["scrollWidth"] + "px";
			substrate.style.height = WindowSize["scrollHeight"] + "px";
			substrate.style.visibility = 'visible';
		}

		
	}
		
	this.Close = function(div)
	{
		if(!div)
			return;
		var sh = document.getElementById(div.id+"_shadow");
		if(sh)
			sh.style.visibility = 'hidden';

		var frame = document.getElementById(div.id+"_frame");
		if(frame)
			frame.style.visibility = 'hidden';
			
		var substrate = document.getElementById("blog_substrate");
		if(substrate)
		{
			substrate.style.display = 'none';
			substrate.style.visibility = 'hidden';
		}
	}
		
	this.AdjustShadow = function(div, dxShadow)
	{
		var sh = document.getElementById(div.id+"_shadow");
		if(sh)
		{
			if(isNaN(dxShadow))
				dxShadow = 5;

			sh.style.width = div.offsetWidth+'px';
			sh.style.height = div.offsetHeight+'px';
			sh.style.left = parseInt(div.style.left)+dxShadow+'px';
			sh.style.top = parseInt(div.style.top)+dxShadow+'px';
		}

		var frame = document.getElementById(div.id+"_frame");
		if(frame)
		{
			frame.style.width = div.offsetWidth + "px";
			frame.style.height = div.offsetHeight + "px";
			frame.style.left = div.style.left;
			frame.style.top = div.style.top;
		}
	}
}
var pjsFloatDiv = new pJCFloatDiv();

/************************************************/

function BlogPopupMenu()
{
	var _this = this;
	this.active = null;
	
	this.PopupShow = function(div, pos)
	{
		this.PopupHide();
		if(!div)
			return;
		if (typeof(pos) != "object")
			pos = {};
			
		this.active = div.id;
	    div.ondrag = jsUtils.False;
		
		jsUtils.addEvent(document, "keypress", _this.OnKeyPress);
		
		div.style.width = div.offsetWidth + 'px';
		div.style.visibility = 'visible';
		
		var res = jsUtils.GetWindowSize();
		pos['top'] = parseInt(res["scrollTop"] + res["innerHeight"]/2 - div.offsetHeight/2);
		pos['left'] = parseInt(res["scrollLeft"] + res["innerWidth"]/2 - div.offsetWidth/2);
		pjsFloatDiv.Show(div, pos["left"], pos["top"]);
	}

	this.PopupHide = function()
	{
		var div = document.getElementById(_this.active);
		if(div)
		{
			pjsFloatDiv.Close(div);
			div.parentNode.removeChild(div);
		}

		this.active = null;
		jsUtils.removeEvent(document, "keypress", _this.OnKeyPress);
	}

	this.OnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.PopupHide();
	},

	this.IsVisible = function()
	{
		return (document.getElementById(this.active).style.visibility != 'hidden');
	}
}


function insertVideo()
{
	var path = document.getElementById('videoPath').value;
	var width = document.getElementById('videoWidth').value;
	var height = document.getElementById('videoHeight').value;
	if(path.length <= 0)
	{
		document.getElementById('error').innerHTML = '<span class="blog-error-text"><?=GetMessage("BPC_VIDEO_ERROR_P")?></span>';
		return false;
	}
	if(width.length <= 0)
	{
		document.getElementById('error').innerHTML = '<span class="blog-error-text"><?=GetMessage("BPC_VIDEO_ERROR_W")?></span>';
		return false;
	}
	if(height.length <= 0)
	{
		document.getElementById('error').innerHTML = '<span class="blog-error-text"><?=GetMessage("BPC_VIDEO_ERROR_H")?></span>';
		return false;
	}
	BlogTool.PopupHide();


	var textarea = document.getElementById("MESSAGE");
	var currentScroll = textarea.scrollTop;

	try
	{
		var pMainObj = GLOBAL_pMainObj['POST_MESSAGE_HTML'];
	}
	catch(e)
	{
	}

	if(pMainObj && document.getElementById('blg-text-html').checked)
	{
		pMainObj.insertHTML('<img __bxtagname="blogVideo" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url('+document.getElementById('videoImg').src+'); background-position: center center; background-repeat: no-repeat; width: '+width+'px; height: '+height+'px;" __bxcontainer="'+path+'" width="'+width+'" height="'+height+'" />');

		var i = arVideo.length ++;
		arVideo[i] = '[video width='+width+' height='+height+']'+path+'[/video]';
		arVideoP[i] = path;
		arVideoW[i] = width;
		arVideoH[i] = height;
	}
	else
	{
		doInsert('[video width='+width+' height='+height+']'+path+'[/video]', "", true);
		textarea.scrollTop = currentScroll;
		textarea.focus();
	}
}

check_ctrl_enter = function(e)
{
	if(!e)
		e = window.event;

	if((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey)
	{
		document.REPLIER.submit();
	}
}

BlogTool = new BlogPopupMenu();
function ShowVideoInput()
{

	var data = '<div style="background-color:#FFF; border:1px solid #000; padding:5px;"><div id="error"></div><table width="0" cellpadding="5"><tr><tD><?=GetMessage("BPC_VIDEO_P")?>:</td><td><input type="text" name="videoPath" id="videoPath" value=""></td></tr><tr><tD><?=GetMessage("BPC_VIDEO_W")?>:</td><td><input type="text" name="videoWidth" id="videoWidth" value="400"></td></tr><tr><tD><?=GetMessage("BPC_VIDEO_H")?>:</td><td><input type="text" name="videoHeight" id="videoHeight" value="300"></td></tr></table><div align="center"><input type="button" name="insertVideo" value="<?=GetMessage("BPC_VIDEO_BUTTON")?>" onClick="insertVideo()">&nbsp;<input type="button" name="cancel" value="<?=GetMessage("BPC_VIDEO_BUTTON_CANCEL")?>" onClick="BlogTool.PopupHide()"></div></div>';
	var div = document.createElement("DIV");
	div.id = "blog_video";
	div.style.visible = 'hidden';
	div.className = "blog-video-popup";
	div.style.position = 'absolute';
	div.innerHTML = data;
	
	var scripts = div.getElementsByTagName('script');
	
	for (var i = 0; i < scripts.length; i++)
	{
		var thisScript = scripts[i];
		var text;
		var sSrc = thisScript.src.replace(/http\:\/\/[^\/]+\//gi, '');
		if (thisScript.src && sSrc != 'bitrix/js/main/utils.js' && sSrc != 'bitrix/js/main/admin_tools.js' &&
			sSrc != '/bitrix/js/main/utils.js' && sSrc != '/bitrix/js/main/admin_tools.js') 
		{
			var newScript = document.createElement("script");
			newScript.type = 'text/javascript';
			newScript.src = thisScript.src;
			document.body.appendChild(newScript);
		}
		else if (thisScript.text || thisScript.innerHTML) 
		{
			text = (thisScript.text ? thisScript.text : thisScript.innerHTML);
			text = (""+text).replace(/^\s*<!\-\-/, '').replace(/\-\->\s*$/, '');
			eval(text);
		}
	}
	
	data = data.replace(/\<script([^\>])*\>([^\<]*)\<\/script\>/gi, '');
	div.innerHTML = data;
	document.body.appendChild(div);
	BlogTool.PopupShow(div);
	document.getElementById('videoPath').focus();
}
</script>