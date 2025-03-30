if (typeof oObject != 'object')
{
	window.oObject = {};
}

var Errors = {
	'result_unval': 'Error in result',
	'result_empty': 'Empty result',
};

function JsTc(oHandler, arSites, sParser, arParams)
{
	var t = this;

	t.oObj = typeof oHandler == 'object' ? oHandler : document.getElementById('TAGS');
	t.arSites = arSites;
	t.arParams = (BX.type.isPlainObject(arParams) ? arParams : {});
	t.selfFolderUrl = t.arParams.selfFolderUrl || '/bitrix/admin/';
	// Arrays for data
	if (sParser)
	{
		t.sExp = new RegExp('[' + sParser + ']+', 'i');
	}
	else
	{
		t.sExp = new RegExp(',');
	}
	t.oLast = { 'str': false, 'arr': false };
	t.oThis = { 'str': false, 'arr': false };
	t.oEl = { 'start': false, 'end': false };
	t.oUnfinedWords = {};
	// Flags
	t.bReady = true;
	t.eFocus = true;
	// Array with results & it`s showing
	t.aDiv = null;
	t.oDiv = null;
	// Pointers
	t.oActive = null;
	t.oPointer = [];
	t.oPointer_default = [];
	t.oPointer_this = 'input_field';

	t.oObj.onblur = function()
	{
		t.eFocus = false;
	};

	t.oObj.onfocus = function()
	{
		if (!t.eFocus)
		{
			t.eFocus = true;
			setTimeout(
				function()
				{
					t.CheckModif('focus');
				}, 500);
		}
	};

	t.oLast['arr'] = t.oObj.value.split(t.sExp);
	t.oLast['str'] = t.oLast['arr'].join(':');

	setTimeout(function() { t.CheckModif('this'); }, 500);

	this.CheckModif = function(__data)
	{
		var sThis = false;
		var tmp = 0;
		var bUnfined = false;
		var word = '';
		var cursor = {};

		if (!t.eFocus)
		{
			return;
		}

		if (t.bReady && t.oObj.value.length > 0)
		{
			// Preparing input data
			t.oThis['arr'] = t.oObj.value.split(t.sExp);
			t.oThis['str'] = t.oThis['arr'].join(':');

			// Getting modificated element
			if (t.oThis['str'] && (t.oThis['str'] != t.oLast['str']))
			{
				cursor['position'] = TCJsUtils.getCursorPosition(t.oObj);
				if (cursor['position']['end'] > 0 && !t.sExp.test(t.oObj.value.substr(cursor['position']['end'] - 1, 1)))
				{
					cursor['arr'] = t.oObj.value.substr(0, cursor['position']['end']).split(t.sExp);
					sThis = t.oThis['arr'][cursor['arr'].length - 1];

					t.oEl['start'] = cursor['position']['end'] - cursor['arr'][cursor['arr'].length - 1].length;
					t.oEl['end'] = t.oEl['start'] + sThis.length;
					t.oEl['content'] = sThis;

					t.oLast['arr'] = t.oThis['arr'];
					t.oLast['str'] = t.oThis['str'];
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
					t.Send(sThis);
				}
			}
		}
		setTimeout(function() { t.CheckModif('this'); }, 500);
	};

	t.Send = function(sSearch)
	{
		if (!sSearch)
		{
			return;
		}

		var queryString = t.selfFolderUrl + 'search_tags.php?search=' + encodeURIComponent(sSearch);
		try
		{
			if (t.arSites && t.arSites.constructor.toString().indexOf('Array') != -1)
			{
				for (var i = 0, length = t.arSites.length; i < length; i++)
				{
					queryString += '&site_id[]=' + encodeURIComponent(t.arSites[i]);
				}
			}
			var checkBox = document.getElementById('ck_' + oHandler.id);
			if (checkBox && checkBox.checked)
			{
				queryString += '&order_by=NAME';
			}
		}
		catch
		{
		}

		BX.ajax.post(queryString, '', BX.delegate(function(data)
		{
			var oError = {};
			var result = {};

			t.bReady = true;
			try
			{
				eval('result = ' + data + ';');
			}
			catch (e)
			{
				oError['result_unval'] = e;
			}

			if (TCJsUtils.empty(result))
			{
				oError['result_empty'] = Errors['result_empty'];
			}

			try
			{
				if (TCJsUtils.empty(oError) && (typeof result == 'object'))
				{
					if (!(result.length == 1 && result[0]['NAME'] == t.oEl['content']))
					{
						t.Show(result);
					}
				}
				else
				{
					t.oUnfinedWords[t.oEl['content']] = '!fined';
				}
			}
			catch (e)
			{
				oError['unknown_error'] = e;
			}
		}, t));
	};

	t.Show = function(result)
	{
		t.Destroy();

		var pos = BX.pos(t.oObj);

		t.oDiv = document.body.appendChild(document.createElement('DIV'));
		t.oDiv.id = t.oObj.id + '_div';
		t.oDiv.className = 'bx-popup-menu';
		t.oDiv.style.position = 'absolute';
		t.aDiv = t.Print(result, ['NAME', 'CNT']);
		if (t.oDiv.offsetWidth < 300)
		{
			t.oDiv.style.width = t.oDiv.offsetWidth + 'px';
		}
		else
		{
			t.oDiv.style.width = '300px';
		}
		t.oDiv.style.zIndex = 5000;

		jsFloatDiv.Show(t.oDiv, pos['left'], pos['bottom']);

		BX.bind(document, 'click', t.CheckMouse);
		BX.bind(document, 'keydown', t.CheckKeyword);
	};

	t.Print = function(aArr, aColumn)
	{
		var aEl = null;
		var sPrefix = '';
		var aResult = [];
		var aRes = [];
		var iCnt = 0;

		sPrefix = t.oDiv.id;
		var str = '<table cellspacing="0" cellpadding="0" border="0"><tr><td class="popupmenu">' +
			'<table cellspacing="0" cellpadding="0" border="0" width="100%">';
		for (var i = 0, length = aArr.length; i < length; i++)
		{
			// Math
			aEl = aArr[i];
			aRes = [];
			aRes['ID'] = (aEl['ID'] && aEl['ID'].length > 0) ? aEl['ID'] : iCnt++;
			aRes['GID'] = sPrefix + '_' + aRes['ID'];
			aRes['NAME'] = TCJsUtils.htmlspecialcharsEx(aEl['NAME']);
			aRes['CNT'] = aEl['CNT'];
			aResult[aRes['GID']] = aRes;
			t.oPointer.push(aRes['GID']);
			// Graph
			str += '<tr><td>'
				+ '<table cellspacing="0" cellpadding="0" border="0" class="popupitem" '
				+ 'onmouseout="window.oObject.' + t.oObj.id + '.Init(); this.className=\'popupitem\';" '
				+ 'onmouseover="window.oObject.' + t.oObj.id + '.Init(); this.className=\'popupitem popupitemover\'" '
				+ 'onclick="window.oObject.' + t.oObj.id + '.oActive=this.id;" '
				+ 'id="' + aRes['GID'] + '" name="' + sPrefix + '_table">'
				+ '<tr><td class="gutter"><div></div></td>'
				+ '<td class="item" id="' + aRes['GID'] + '_NAME" width="90%">' + aRes['NAME'] + '</td>'
				+ '<td class="item" id="' + aRes['GID'] + '_CNT" width="10%" align="right">' + aRes['CNT'] + '</td>'
				+ '</tr></table></td></tr>'
			;
		}
		str += '</table></td></tr></table>';
		t.oPointer.push('input_field');
		t.oPointer_default = t.oPointer;
		t.oDiv.innerHTML = str;

		return aResult;
	};

	t.Destroy = function()
	{
		try
		{
			jsFloatDiv.Close(t.oDiv);
			t.oDiv.parentNode.removeChild(t.oDiv);
		}
		catch
		{
		}

		t.oPointer = [];
		t.oPointer_default = [];
		t.oPointer_this = 'input_field';
		t.oDiv = null;
		t.aDiv = null;
		t.oActive = null;

		BX.unbind(document, 'click', t.CheckMouse);
		BX.unbind(document, 'keydown', t.CheckKeyword);
	};

	t.Replace = function()
	{
		if (typeof t.oActive == 'string')
		{
			var tmp = t.aDiv[t.oActive];
			var tmp1 = '';
			if (typeof tmp == 'object')
			{
				var elEntities = document.createElement('span');
				elEntities.innerHTML = tmp['NAME'].replaceAll('&quot;', '"').replaceAll('&amp;', '&');
				tmp1 = elEntities.innerHTML;
			}
			// this preserves leading spaces
			var start = t.oEl['start'];
			while (start < t.oObj.value.length && t.oObj.value.substring(start, start + 1) == ' ')
			{
				start++;
			}

			t.oObj.value = t.oObj.value.substring(0, start) + tmp1.replaceAll('&lt;', '<').replaceAll('&gt;', '>') + t.oObj.value.substr(t.oEl['end']);
			TCJsUtils.setCursorPosition(t.oObj, start + tmp1.length);
		}
	};

	t.Init = function()
	{
		t.oActive = false;
		t.oPointer = t.oPointer_default;
		t.Clear();
		t.oPointer_this = 'input_pointer';
	};

	t.Clear = function()
	{
		var oEl = {};
		var ii = '';
		oEl = t.oDiv.getElementsByTagName('table');
		if (oEl.length > 0 && typeof oEl == 'object')
		{
			for (ii in oEl)
			{
				if (oEl.hasOwnProperty(ii))
				{
					var oE = oEl[ii];
					if (oE.name == (t.oDiv.id + '_table') || (t.aDiv[oE.id]))
					{
						oE.className = 'popupitem';
					}
				}
			}
		}
	};

	t.CheckMouse = function()
	{
		t.Replace();
		t.Destroy();
	};

	t.CheckKeyword = function(e)
	{
		if (!e)
		{
			e = window.event;
		}
		var oP = null;
		var oEl = null;

		if ((e.keyCode > 37 && e.keyCode < 41) || (e.keyCode == 13))
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
							oEl.className = 'popupitem popupitemover';
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
							oEl.className = 'popupitem popupitemover';
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
					if (BX.browser.IsIE())
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
				default:
					break;
			}
			t.oPointer_this	= oP;
		}
		else
		{
			t.Destroy();
		}

		return true;
	};
}
var TCJsUtils = {
	getCursorPosition: function(oObj)
	{
		var result = {
			'start': 0,
			'end': 0,
		};

		if (!oObj || (typeof oObj != 'object'))
		{
			return result;
		}

		try
		{
			if (document.selection != null && oObj.selectionStart == null)
			{
				oObj.focus();
				var oRange = document.selection.createRange();
				var oParent = oRange.parentElement();
				var sBookmark = oRange.getBookmark();
				var sContents = oObj.value;
				var sContentsSaved = oObj.value;
				var sMarker = '__' + Math.random() + '__';

				while (sContents.indexOf(sMarker) != -1)
				{
					sMarker = '__' + Math.random() + '__';
				}

				if (!oParent || oParent === null || (oParent.type != 'textarea' && oParent.type != 'text'))
				{
					return result;
				}

				oRange.text = sMarker + oRange.text + sMarker;
				sContents = oObj.value;
				result['start'] = sContents.indexOf(sMarker);
				sContents = sContents.replace(sMarker, '');
				result['end'] = sContents.indexOf(sMarker);
				oObj.value = sContentsSaved;
				oRange.moveToBookmark(sBookmark);
				oRange.select();

				return result;
			}

			return {
				'start': oObj.selectionStart,
				'end': oObj.selectionEnd
			};
		}
		catch
		{
		}

		return result;
	},

	setCursorPosition: function(oObj, iPosition)
	{
		if (typeof oObj != 'object')
		{
			return false;
		}

		oObj.focus();

		try
		{
			if (document.selection !== null && oObj.selectionStart === null)
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
		catch
		{
			return false;
		}
	},

	empty: function(oObj)
	{
		var result = true;
		if (oObj)
		{
			for (var i in oObj)
			{
				if (oObj.hasOwnProperty(i))
				{
					result = false;
					break;
				}
			}
		}

		return result;
	},

	htmlspecialcharsEx: function(str)
	{
		return str
			.replaceAll('&amp;', '&amp;amp;')
			.replaceAll('&lt;', '&amp;lt;')
			.replaceAll('&gt;', '&amp;gt;')
			.replaceAll('&quot;', '&amp;quot;')
			.replaceAll('<', '&lt;')
			.replaceAll('>', '&gt;')
			.replaceAll('"', '&quot;')
		;
	},

	htmlspecialcharsback: function(str)
	{
		return str
			.replaceAll('&lt;', '<')
			.replaceAll('&gt;', '>')
			.replaceAll('&quot;', '"')
			.replaceAll('&amp;', '&')
		;
	},
};
