if (typeof oObject != "object")
	window.oObject = {};
var Errors = {
	"result_unval" : "Error in result",
	"result_empty" : "Empty result"
};

function SonetJsTc(oHandler, sParams, sParser, oBtn, bHint)
{
	var 
		t = this,
		tmp = 0;

	t.oObj = typeof oHandler == 'object' ? oHandler : document.getElementById("TAGS");
	t.sParams = sParams;
	t.oBtn = oBtn;
	t.bHint = bHint;
	// Arrays for data
	t.sExp = new RegExp("[\040]*[\000-\037\041-\054\057\072-\077\133-\136\140\173-\177\230\236\246-\377\240]+[\040]*", "i");
	t.oLast = {"str":false, "arr":false};
	t.oThis = {"str":false, "arr":false};
	t.oEl = {"start":false, "end":false};
	t.oUnfinedWords = {};
	// Flags 
	t.bReady = true, t.eFocus = true; 
	// Array with results & it`s showing
	t.aDiv = null, t.oDiv = null;
	// Pointers
	t.oActive = null, t.oPointer = Array(), t.oPointer_default = Array(), t.oPointer_this = 'input_field';

	t.oObj.onblur = function(){t.eFocus = false;}
	t.oObj.onfocus = function(){if (t.bHint){t.oObj.value = ''; t.bHint = false;} if (!t.eFocus){t.eFocus = true; setTimeout(function(){t.CheckModif('focus')}, 500);}}

	if (sParser)
	{
		t.sExp = new RegExp("["+sParser+"]+", "i");
	}

	t.oLast["arr"] = t.oObj.value.split(t.sExp);
	t.oLast["str"] = t.oLast["arr"].join(":");

	setTimeout(function(){t.CheckModif('this')}, 500);

	this.CheckModif = function(__data)
	{
		var 
			sThis = false, tmp = 0, 
			bUnfined = false, word = "",
			cursor = {};

		if (!t.eFocus)
			return;

		if (t.oObj.value.length <= 0 && t.oBtn)
		{
			t.oBtn.disabled = true;
		}

		if (!t.bHint && t.bReady && t.oObj.value.length > 0)
		{
			//t.bHint = false;

			// Preparing input data
			t.oThis["arr"] = t.oObj.value.split(t.sExp);
			t.oThis["str"] = t.oThis["arr"].join(":");

			// Getting modificated element
			if (t.oThis["str"] && (t.oThis["str"] != t.oLast["str"]))
			{
				cursor['position'] = SonetTCJsUtils.getCursorPosition(t.oObj);
				if (cursor['position']['end'] > 0 && !t.sExp.test(t.oObj.value.substr(cursor['position']['end']-1, 1)))
				{
					cursor['arr'] = t.oObj.value.substr(0, cursor['position']['end']).split(t.sExp);
					sThis = t.oThis["arr"][cursor['arr'].length - 1];
					t.oEl['start'] = cursor['position']['end'] - cursor['arr'][cursor['arr'].length - 1].length;
					t.oEl['end'] = t.oEl['start'] + sThis.length;
					t.oEl['content'] = sThis;

					t.oLast["arr"] = t.oThis["arr"];
					t.oLast["str"] = t.oThis["str"];
				}
			}
			if (sThis)
			{
				// Checking for UnfinedWords
				for (tmp = 2; tmp <= sThis.length; tmp++)
				{
					word = sThis.substr(0, tmp);
					if (t.oUnfinedWords[word] == '!fined')
					{
						bUnfined = true;
						break;
					}
				}
				if (!bUnfined)
				{
					if (t.oBtn)
						t.oBtn.disabled = false;
					t.Send(sThis);
				}
			}
			if (t.oBtn)
				t.oBtn.disabled = false;
		}

		setTimeout(function(){t.CheckModif('this')}, 500);
	},

	t.Send = function(sSearch)
	{
		t.bReady = false;
		var oError = Array();

		var arData = {
			"search" : sSearch,
			"params" : t.sParams
		};
		var data = '';

		for(var i in arData)
		{
			if (data.length > 0) data += '&';
			var name = escape(i).replace(/\+/g, '%2B');
			data += name + '=' + escape(arData[i]).replace(/\+/g, '%2B');
		}

		BX.ajax({
			url: '/bitrix/components/bitrix/socialnetwork.user_search_input/search.php' + '?' + data,
			method: 'GET',
			dataType: 'json',
			data: {},
			onsuccess: function(result) {
				t.bReady = true;
				if (
					typeof result == 'object'
					&& result.length != 0 
					&& !(
						result.length == 1 
						&& result[0]['NAME'] == t.oEl['content']
					)
				)
				{
					t.Show(result);
					oError['result_empty'] = Errors['result_empty'];
				}
			},
			onfailure: function(result) {
				t.bReady = true;
				t.oUnfinedWords[t.oEl['content']] = '!fined';
			}
		});
	},

	t.Show = function(result)
	{
		t.Destroy();
		t.oDiv = document.body.appendChild(document.createElement("DIV"));
		t.oDiv.id = t.oObj.id+'_div';

		// This operation very importaint to do there.
		var oFrame = document.getElementById(t.oDiv.id+"_frame");
		if(!oFrame)
		{
			oFrame = document.createElement("IFRAME");
			oFrame.src = "javascript:''";
			oFrame.id = t.oDiv.id+"_frame";
			oFrame.style.width = "0px";
			oFrame.style.height = "0px";
			oFrame.style.visibility = 'hidden';
			document.body.appendChild(oFrame);
		}

		t.oDiv.className = "search-popup";
		t.oDiv.style.position = 'absolute';
		t.oDiv.style.zIndex = "1000";					

		t.aDiv = t.Print(result);
		var pos = SonetTCJsUtils.GetRealPos(t.oObj);
		if (pos["width"] < 400)
			pos["width"] = 400;

		v = (document.compatMode=='CSS1Compat' && !window.opera ? document.documentElement.clientWidth : document.body.clientWidth);
		if (v < pos["width"] + pos["left"])
		{
			pos["left"] = v - pos["width"];
			if (pos["left"] < 0)
				pos["left"] = 0;
		}

		t.oDiv.style.width = parseInt(pos["width"]) + "px";
		SonetTCJsUtils.show(t.oDiv, pos["left"], pos["bottom"]);
		SonetTCJsUtils.addEvent(document, "click", t.CheckMouse);
		SonetTCJsUtils.addEvent(document, "keydown", t.CheckKeyword);
	},

	t.Print = function(aArr)
	{
		var 
			aEl = null, sPrefix = '', sColumn = '',
			aResult = Array(), aRes = Array(),
			iCnt = 0, tmp = 0, tmp_ = 0, bFirst = true, 
			oDiv = null, oSpan = null;

		sPrefix = t.oDiv.id;

		for (tmp_ in aArr)
		{
			// Math
			aEl = aArr[tmp_];
			aRes = Array();
			aRes['ID'] = (aEl['ID'] && aEl['ID'].length > 0) ? aEl['ID'] : iCnt++;
			aRes['GID'] = sPrefix + '_' + aRes['ID'];
			aRes['NAME'] = aEl['NAME'];
			aRes['ADD'] = aEl['ADD'];
			aResult[aRes['GID']] = aRes;
			t.oPointer.push(aRes['GID']);
			// Graph
			oDiv = t.oDiv.appendChild(document.createElement("DIV"));
			oDiv.id = aRes['GID'];
			oDiv.name = sPrefix + '_div';

			oDiv.className = 'search-popup-row';

			oDiv.onmouseover = function(){t.Init(); this.className='search-popup-row-active';};
			oDiv.onmouseout = function(){t.Init(); this.className='search-popup-row';};
			oDiv.onclick = function(){
				t.oActive = this.id
				BX.fireEvent(BX(BX.message("sonetUSIInputID")), 'change');
			};

			//oSpan = oDiv.appendChild(document.createElement("DIV"));
			//oSpan.id = oDiv.id + '_NAME';
			//oSpan.className = "search-popup-el search-popup-el-cnt";
			//oSpan.innerHTML = aRes['ADD'];

			oSpan = oDiv.appendChild(document.createElement("DIV"));
			oSpan.id = oDiv.id + '_NAME';
			oSpan.className = "search-popup-el search-popup-el-name";
			oSpan.innerHTML = aRes['NAME'];
		}
		t.oPointer.push('input_field');
		t.oPointer_default = t.oPointer;
		return aResult;
	},

	t.Destroy = function()
	{
		if (t.oDiv && t.oDiv.parentNode)
		{
			SonetTCJsUtils.hide(t.oDiv);
			t.oDiv.parentNode.removeChild(t.oDiv);
		}
		t.aDiv = Array();
		t.oPointer = Array(), t.oPointer_default = Array(), t.oPointer_this = 'input_field';
		t.bReady = true, t.eFocus = true, oError = {}, 
		t.oActive = null;

		SonetTCJsUtils.removeEvent(document, "click", t.CheckMouse);
		SonetTCJsUtils.removeEvent(document, "keydown", t.CheckKeyword);
	},

	t.Replace = function()
	{
		if (typeof t.oActive == 'string')
		{
			var tmp = t.aDiv[t.oActive];
			var tmp1 = '';
			if (typeof tmp == 'object')	
			{
//				tmp1 = tmp['NAME'].replace("&lt;", "<").replace("&gt;", ">");
				tmp1 = tmp['NAME'].replace(/\&lt;/g, "<").replace(/\&gt;/g, ">").replace(/\&quot;/g, "\"").replace(/\&amp;/g, "\&");
			}
			tmp = t.oObj.value.substring(0, t.oEl['start']) + tmp1;
			t.oObj.value = t.oObj.value.substring(0, t.oEl['start']) + tmp1 + t.oObj.value.substr(t.oEl['end']);
			SonetTCJsUtils.setCursorPosition(t.oObj, tmp.length);
			if (t.oBtn)
				t.oBtn.disabled = false;
		}
		return;
	},

	t.Init = function()
	{
		t.oActive = false;
		t.oPointer = t.oPointer_default;
		t.Clear();
		t.oPointer_this	= 'input_pointer';
	},
	
	t.Clear = function()
	{
		var oEl = {}, ii = '';
		oEl = t.oDiv.getElementsByTagName("div");
		if (oEl.length > 0 && typeof oEl == 'object')
		{
			for (ii in oEl)
			{
				var oE = oEl[ii];
				if (oE && (typeof oE == 'object') && (oE.name == t.oDiv.id + '_div'))
				{
					oE.className = "search-popup-row";
				}
			}
		}
		return;
	},

	t.CheckMouse = function()
	{
		t.Replace();
		t.Destroy();
	},

	t.CheckKeyword = function(e)
	{
		if (!e)
			e = window.event;
		var 
			oP = null,
			oEl = null,
			ii = null;
		if ((37 < e.keyCode && e.keyCode <41) || (e.keyCode == 13))
		{
			t.Clear();

			switch (e.keyCode)
			{
				case 38:
					oP = t.oPointer.pop();
					if (t.oPointer_this == oP)
					{
						t.oPointer.unshift(oP);
						oP = t.oPointer.pop();
					}

					if (oP != 'input_field')
					{
						t.oActive = oP;
						oEl = document.getElementById(oP);
						if (typeof oEl == 'object')
						{
							oEl.className = "search-popup-row-active";
						}
					}
					t.oPointer.unshift(oP);
					break;
				case 40:
					oP = t.oPointer.shift();
					if (t.oPointer_this == oP)
					{
						t.oPointer.push(oP);
						oP = t.oPointer.shift();
					}
					if (oP != 'input_field')
					{
						t.oActive = oP;
						oEl = document.getElementById(oP);
						if (typeof oEl == 'object')
						{
							oEl.className = "search-popup-row-active";
						}
					}
					t.oPointer.push(oP);
					break;
				case 39:
					t.Replace();
					t.Destroy();
					break;
				case 13:
					t.Replace();
					t.Destroy();
					if (SonetTCJsUtils.IsIE())
					{
						e.returnValue = false;
						e.cancelBubble = true;
					}
					else
					{
						e.preventDefault();
						e.stopPropagation();
					}
					break;
			}
			t.oPointer_this	= oP;
		}
		else
		{
			t.Destroy();
		}
//		return false;
	}
}

var SonetTCJsUtils =
{
	arEvents: Array(),

	addEvent: function(el, evname, func)
	{
		if(el.attachEvent) // IE
			el.attachEvent("on" + evname, func);
		else if(el.addEventListener) // Gecko / W3C
			el.addEventListener(evname, func, false);
		else
			el["on" + evname] = func;
		this.arEvents[this.arEvents.length] = {'element': el, 'event': evname, 'fn': func};
	},

	removeEvent: function(el, evname, func)
	{
		if(el.detachEvent) // IE
			el.detachEvent("on" + evname, func);
		else if(el.removeEventListener) // Gecko / W3C
			el.removeEventListener(evname, func, false);
		else
			el["on" + evname] = null;
	},

	getCursorPosition: function(oObj)
	{
		var result = {'start': 0, 'end': 0};
		if (!oObj || (typeof oObj != 'object'))
			return result;
		try
		{
			if (document.selection != null && oObj.selectionStart == null)
			{
				oObj.focus();
				var 
					oRange = document.selection.createRange(),
					oParent = oRange.parentElement(),
					sBookmark = oRange.getBookmark(),
					sContents = sContents_ = oObj.value,
					sMarker = '__' + Math.random() + '__';

				while(sContents.indexOf(sMarker) != -1)
				{
					sMarker = '__' + Math.random() + '__';
				}
				
				if (!oParent || oParent == null || (oParent.type != "textarea" && oParent.type != "text"))
				{
					return result;
				}
				
				oRange.text = sMarker + oRange.text + sMarker;
				sContents = oObj.value;
				result['start'] = sContents.indexOf(sMarker);
				sContents = sContents.replace(sMarker, "");
				result['end'] = sContents.indexOf(sMarker);
				oObj.value = sContents_;
				oRange.moveToBookmark(sBookmark);
				oRange.select();
				return result;
			}
			else 
			{
				return {
				 	'start': oObj.selectionStart, 
					'end': oObj.selectionEnd 
				};
			}
		}
		catch(e){}
		return result;
	},

	setCursorPosition: function(oObj, iPosition)
	{
		var result = false;
		if (typeof oObj != 'object')
			return false;

		oObj.focus();

		try 
		{
			if (document.selection != null && oObj.selectionStart == null) 
			{
				var oRange = document.selection.createRange();
				oRange.select();
			}
			else 
			{
				oObj.selectionStart = iPosition;
				oObj.selectionEnd = iPosition;
			}
			return true;
		}
		catch(e)
		{
			return false;
		}
	},

	printArray: function (oObj, sParser, iLevel)
	{
	    try
	    {
	        var result = '', 
	        	space = '',
	        	i=null, j=0;

	        if (iLevel==undefined)
	            iLevel = 0;
	        if (!sParser)
	        	sParser = "\n";

	        for (j=0; j<=iLevel; j++)
	            space += '  ';

	        for (i in oObj)
	        {
	            if (typeof oObj[i] == 'object')
	                result += space+i + " = {"+ sParser + SonetTCJsUtils.printArray(oObj[i], sParser, iLevel+1) + ", " + sParser + "}" + sParser;
	            else
	                result += space+i + " = " + oObj[i] + "; " + sParser;
	        }
	        return result;
	    }
	    catch(e)
	    {
	        return;
	    }
	},

	empty: function(oObj)
	{
		var result = true;
		if (oObj)
		{
		    for (i in oObj)
		    {
		    	 result = false;
		    	 break;
		    }
		}
		return result;
	},

	show: function(oDiv, iLeft, iTop)
	{
		if (typeof oDiv != 'object')	
			return;
		var zIndex = parseInt(oDiv.style.zIndex);
		if(zIndex <= 0 || isNaN(zIndex))
			zIndex = 1000;
		oDiv.style.zIndex = zIndex;
		oDiv.style.left = iLeft + "px";
		oDiv.style.top = iTop + "px";
		if(SonetTCJsUtils.IsIE())
		{
			var oFrame = document.getElementById(oDiv.id+"_frame");
			if(!oFrame)
			{
				oFrame = document.createElement("IFRAME");
				oFrame.src = "javascript:''";
				oFrame.id = oDiv.id+"_frame";
				oFrame.style.position = 'absolute';
				oFrame.style.zIndex = zIndex-1;
				document.body.appendChild(oFrame);
			}
			oFrame.style.position = 'absolute';
			oFrame.style.zIndex = zIndex-1;
			oFrame.style.width = oDiv.offsetWidth + "px";
			oFrame.style.height = oDiv.offsetHeight + "px";
			oFrame.style.left = oDiv.style.left;
			oFrame.style.top = oDiv.style.top;
			oFrame.style.visibility = 'visible';
			oFrame.style.display = 'inline';

		}
		return oDiv;
	},

	hide: function(oDiv)
	{
		if(!oDiv)
			return;
		var oFrame = document.getElementById(oDiv.id+"_frame");
		if(oFrame)
		{
			oFrame.style.visibility = 'hidden';
			oFrame.style.display = 'none';
		}
		oDiv.style.display = 'none';
	},

	GetRealPos: function(el)
	{
		if(!el || !el.offsetParent)
			return false;
		var res=Array();
		var objParent = el.offsetParent;
		res["left"] = el.offsetLeft;
		res["top"] = el.offsetTop;
		while(objParent && objParent.tagName != "BODY")
		{
			res["left"] += objParent.offsetLeft;
			res["top"] += objParent.offsetTop;
			objParent = objParent.offsetParent;
		}
		res["right"]=res["left"] + el.offsetWidth;
		res["bottom"]=res["top"] + el.offsetHeight;
		res["width"]=el.offsetWidth;
		res["height"]=el.offsetHeight;
		return res;
	},

	IsIE: function()
	{
		return (document.attachEvent && !SonetTCJsUtils.IsOpera());
	},

	IsOpera: function()
	{
		return (navigator.userAgent.toLowerCase().indexOf('opera') != -1);
	}
}
SonetTcLoadTI = true;