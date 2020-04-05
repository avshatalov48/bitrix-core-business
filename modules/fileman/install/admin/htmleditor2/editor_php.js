var
	one_gif_src = '/bitrix/images/1.gif',
	image_path = '/bitrix/images/fileman/htmledit2',
	c2wait_path = image_path + '/c2waiter.gif',
	global_iconkit_path = image_path + '/_global_iconkit.gif',
	settings_page_path = '/bitrix/admin/fileman_manage_settings.php?sessid=' + BX.bitrix_sessid(),
	editor_action_path = '/bitrix/admin/fileman_editor_action.php?sessid=' + BX.bitrix_sessid(),
	editor_dialog_path = '/bitrix/admin/fileman_editor_dialog.php',
	flash_preview_path = '/bitrix/admin/fileman_flash_preview.php',
	manage_snippets_path = '/bitrix/admin/fileman_manage_snippets.php?lang=' + window.BXLang + '&site=' + window.BXSite + '&sessid=' + BX.bitrix_sessid(),
	to_template_path = window.BX_PERSONAL_ROOT + "/templates/",
	dxShadowImgPath = '';

// Methods for PHP version
BXHTMLEditor.prototype.LoadComponents2 = function(oCallBack)
{
	var callback = function(oCallBack)
	{
		if (!oCallBack.params)
			oCallBack.func.apply(oCallBack.obj);
		else
			oCallBack.func.apply(oCallBack.obj, oCallBack.params);
	};

	if (window.as_arComp2Elements)
		return callback(oCallBack);

	var lc = new JCHttpRequest();
	var count = 0;
	lc.Action = function(result)
	{
		var interval = setInterval
		(
			function()
			{
				if (window.arComp2Elements)
				{
					clearInterval(interval);
					window.as_arComp2Elements = [];
					var l = window.arComp2Elements.length;
					for (var i = 0; i < l; i++)
						as_arComp2Elements[window.arComp2Elements[i].name] = window.arComp2Elements[i];
					callback(oCallBack);
					return;
				}
				if (count > 20)
				{
					clearInterval(interval);
					err_text = "ERROR in pMainObj.LoadComponents2()";
					if ((eind = result.indexOf('Fatal error')) != -1)
						err_text += "\n PHP error: \n\n....." + result.substr(eind - 10);
					alert(err_text);
					callback(oCallBack);
				}
				count++;
			}, 10
		);
	};
	lc.Send('/bitrix/admin/fileman_load_components2.php?lang='+BXLang+'&site='+BXSite+'&load_tree=Y');
};

BXHTMLEditor.prototype.IsSessionExpired = function(result)
{
	if (result.indexOf(this.SessionLostStr) == -1)
		return false;
	var
		i1 = result.indexOf(this.SessionLostStr) + this.SessionLostStr.length,
		sessid = result.substr(i1, result.indexOf('-->') - i1);

	return sessid;
}

BXHTMLEditor.prototype.OnLoad_ex = function()
{
	var _this = this;
	if((!this.arConfig["bWithoutPHP"] || this.limit_php_access) && this.arConfig["use_advanced_php_parser"] == 'Y')
	{
		this.bUseAPP = true; // APP - AdvancedPHPParser
		this.APPConfig =
		{
			arTags_before : ['tbody','thead','tfoot','tr','td','th'],
			arTags_after : ['tbody','thead','tfoot','tr','td','th'],
			arTags :
			{
				'a' : ['href','title','class','style'],
				'img' : ['src','alt','class','style']
			}
		};
	}
	else
		this.bUseAPP = false;

	if (this.limit_php_access)
	{
		oBXEditorUtils.addUnParser(function(_node)
		{
			var id, bxTag;
			if (id = _node.arAttributes["id"])
			{
				bxTag = _this.pMainObj.GetBxTag(id);
				if(bxTag.tag && bxTag.tag == 'php_disabled' && bxTag.params && bxTag.params.value)
					return '#PHP' + bxTag.params.value + '#';
			}
			return false;
		});

		oBXEditorUtils.addPropertyBarHandler('php_disabled', function(bNew, pTaskbar, pElement)
		{
			BX.cleanNode(pTaskbar.pCellProps);
			pTaskbar.pCellProps.appendChild(BX.create("SPAN", {text: BX_MESS.LPA_WARNING, style: {padding: '10px'}}));
		});
	}
};


BXHTMLEditor.prototype.APP_Parse = function(sContent)
{
	if (!this.bUseAPP)
		return sContent;

	this.arAPPFragments = [];
	sContent = this.APP_ParseBetweenTableTags(sContent);

	//sContent = this.APP_ParseInAttributes(sContent);
	return sContent;
};

BXHTMLEditor.prototype.APP_ParseBetweenTableTags = function(str)
{
	var _this = this;
	var replacePHP_before = function(str,b1,b2,b3,b4)
	{
		_this.arAPPFragments.push(JS_addslashes(b1));
		return b2+b3+' __bx_php_before=\"#APP'+(_this.arAPPFragments.length-1)+'#\" '+b4;
	};
	var replacePHP_after = function(str,b1,b2,b3,b4)
	{
		_this.arAPPFragments.push(JS_addslashes(b4));
		return b1+'>'+b3+'<'+b2+' style="display:none;"__bx_php_after=\"#APP'+(_this.arAPPFragments.length-1)+'#\"></'+b2+'>';
	};
	var arTags_before = _this.APPConfig.arTags_before;
	var arTags_after = _this.APPConfig.arTags_after;
	var tagName,re;
	// PHP fragments before tags
	for (var i = 0,l = arTags_before.length; i<l; i++)
	{
		tagName = arTags_before[i];
		if (_this.limit_php_access)
			re = new RegExp('#(PHP(?:\\d{4}))#(\\s*)(<'+tagName+'[^>]*?)(>)',"ig");
		else
			re = new RegExp('<\\?(.*?)\\?>(\\s*)(<'+tagName+'[^>]*?)(>)',"ig");
		str = str.replace(re, replacePHP_before);
	}
	// PHP fragments after tags
	for (var i = 0,l = arTags_after.length; i<l; i++)
	{
		tagName = arTags_after[i];
		if (_this.limit_php_access)
			re = new RegExp('(</('+tagName+')[^>]*?)>(\\s*)#(PHP(?:\\d{4}))#',"ig");
		else
			re = new RegExp('(</('+tagName+')[^>]*?)>(\\s*)<\\?(.*?)\\?>',"ig");
		str = str.replace(re, replacePHP_after);
	}
	return str;
};

BXHTMLEditor.prototype.APP_ParseInAttributes = function(str)
{
	var _this = this;
	var replacePHP_inAtr = function(str,b1,b2,b3,b4,b5,b6)
	{
		_this.arAPPFragments.push(JS_addslashes(b5));
		return b1+b2+b3+'""'+' __bx_ex_'+b2+b3+'\"#APP'+(_this.arAPPFragments.length-1)+'#\"'+b6;
	};
	var arTags = _this.APPConfig.arTags;
	var tagName, atrName, atr, i;
	for (tagName in arTags)
	{
		for (i = 0, cnt = arTags[tagName].length; i < cnt; i++)
		{
			atrName = arTags[tagName][i];
			re = new RegExp('(<'+tagName+'(?:[^>](?:\\?>)*?)*?)('+atrName+')(\\s*=\\s*)((?:"|\')?)<\\?(.*?)\\?>\\4((?:[^>](?:\\?>)*?)*?>)',"ig");
			str = str.replace(re, replacePHP_inAtr);
		}
	}
	return str;
};

BXHTMLEditor.prototype.SystemParse_ex = function(sContent)
{
	if(window.lca)
	{
		if (_$arComponents !== false) // _$arComponents - is not empty
		{
			_$lca_only = true;
		}
		else
		{
			_$arComponents = {};
			_$compLength = 0;
		}
	}

	if (this.limit_php_access)
	{
		var _this = this;
		sContent = sContent.replace(/#PHP(\d{4})#/ig, function(s, s1){
			return "<img src=\"/bitrix/images/fileman/htmledit2/php.gif\" id=\"" + _this.SetBxTag(false, {tag: 'php_disabled', params: {value: s1}}) + "\" border=\"0\"/>";});
	}

	//Replacing PHP by IMG
	if(!this.arConfig["bWithoutPHP"] || this.limit_php_access)
		sContent = this.pParser.ParsePHP(sContent);

	return sContent;
};


BXHTMLEditor.prototype.SetCodeEditorContent_ex = function(sContent)
{
	sContent = sContent.replace(/(^[\s\S]*?)(<body.*?>)/i, "");
	if (this.fullEdit)
	{
		this._head = RegExp.$1;
		if (this._body != RegExp.$2)
		{
			this._body = RegExp.$2;
			this.updateBody(this._body);
		}
	}
	sContent = sContent.replace(/(<\/body>[\s\S]*?$)/i, "");

	if (this.fullEdit)
		this._footer = RegExp.$1;

	if (this.fullEdit)
		return this._head+this._body+sContent+this._footer;

	return sContent;
};

BXHTMLEditor.prototype.APP_Unparse = function(sContent)
{
	sContent = this.APP_UnparseBetweenTableTags(sContent);
	sContent = this.APP_UnparseInAttributes(sContent);
	return sContent;
};

BXHTMLEditor.prototype.APP_UnparseBetweenTableTags = function(str)
{
	var _this = this;
	var unreplacePHP_before = function(str, b1, b2, b3)
	{
		if (_this.limit_php_access)
			return '#'+JS_stripslashes(b2)+'#'+b1+b3;
		else
			return '<?'+JS_stripslashes(_this.arAPPFragments[parseInt(b2)])+'?>'+b1+b3;
	};
	var unreplacePHP_after = function(str, b1, b2)
	{
		if (_this.limit_php_access)
			return b1+'#'+JS_stripslashes(b2)+'#';
		else
			return b1+'<?'+JS_stripslashes(_this.arAPPFragments[parseInt(b2)])+'?>';
	}

	var arTags_before = _this.APPConfig.arTags_before;
	var arTags_after = _this.APPConfig.arTags_after;
	var tagName,re;
	// PHP fragments before tags
	for (var i = 0,l = arTags_before.length; i<l; i++)
	{
		tagName = arTags_before[i];
		re = new RegExp('(<'+tagName+'[^>]*?)__bx_php_before="#APP(\\d+)#"([^>]*?>)',"ig");
		str = str.replace(re, unreplacePHP_before);
	}
	// PHP fragments after tags
	for (var i = 0,l = arTags_after.length; i<l; i++)
	{
		tagName = arTags_after[i];
		re = new RegExp('(</'+tagName+'[^>]*?>\\s*)<'+tagName+'[^>]*?__bx_php_after="#APP(\\d+)#"[^>]*?>(?:.|\\s)*?</'+tagName+'>',"ig");
		str = str.replace(re, unreplacePHP_after);
	}
	return str;
};

BXHTMLEditor.prototype.APP_UnparseInAttributes = function(str)
{
	var _this = this;
	un_replacePHP_inAtr = function(str,b1,b2,b3,b4,b5,b6,b7)
	{
		return b1+'"<?'+JS_stripslashes(_this.arAPPFragments[parseInt(b6)])+'?>" '+b3+b7;
	}
	un_replacePHP_inAtr2 = function(str,b1,b2,b3,b4,b5,b6)
	{
		return b1+b4+'"<?'+JS_stripslashes(_this.arAPPFragments[parseInt(b3)])+'?>" '+b6;
	}
	var arTags = _this.APPConfig.arTags;

	var tagName, atrName, atr, i;
	for (tagName in arTags)
	{
		for (i = 0, cnt = arTags[tagName].length; i < cnt; i++)
		{
			atrName = arTags[tagName][i];
			re = new RegExp('(<'+tagName+'(?:[^>](?:\\?>)*?)*?'+atrName+'\\s*=\\s*)("|\')[^>]*?\\2((?:[^>](?:\\?>)*?)*?)(__bx_ex_'+atrName+')(?:\\s*=\\s*)("|\')#APP(\\d+)#\\5((?:[^>](?:\\?>)*?)*?>)',"ig");
			re2 = new RegExp('(<'+tagName+'(?:[^>](?:\\?>)*?)*?)__bx_ex_'+atrName+'\\s*=\\s*("|\')#APP(\\d+)#\\2((?:[^>](?:\\?>)*?)*?'+atrName+'\\s*=\\s*)("|\').*?\\5((?:[^>](?:\\?>)*?)*?>)',"ig");
			str = str.replace(re, un_replacePHP_inAtr);
			str = str.replace(re2, un_replacePHP_inAtr2);
		}
	}
	return str;
};

BXNode.prototype.__ReturnPHPStr = function(arVals, arParams)
{
	var res = "";
	var un = Math.random().toString().substring(2);
	var i=0, val, comm, zn, p, j;
	for(var key in arVals)
	{
		val = arVals[key];
		i++;
		comm = (arParams && arParams[key] && arParams[key].length > 0 ? un + 'x' + i + 'x/' + '/ '+arParams[key] : '');
		res += '\r\n\t\''+key+'\'\t=>\t';

		if(typeof(val)=='object' && val.length>1)
		{
			res += "Array("+comm+"\r\n";
			zn = '';
			for(j=0; j<val.length; j++)
			{
				p = val[j];
				if(zn!='') zn+=',\r\n';
				zn += "\t\t\t\t\t"+this.__PreparePHP(p);
			}
			res += zn+"\r\n\t\t\t\t),";
		}
		else if(typeof(val)=='object' && val[0])
			res += "Array("+this.__PreparePHP(val[0])+"),"+comm;
		else
			res += this.__PreparePHP(val)+","+comm;
	}

	var max = 0;
	var lngth = [], pn, l;
	for(j=1; j<=i; j++)
	{
		p = res.indexOf(un+'|'+j+'|');
		pn = res.substr(0, p).lastIndexOf("\n");
		l = (p-pn);
		lngth[j] = l;
		if(max<l)
			max = l;
	}

	var k;
	for(j=1; j<=i; j++)
	{
		val = '';
		for(k=0; k<(max-lngth[j]+7)/8; k++)
			val += '\t';
		l = new RegExp(un+'x'+j+'x', "g")
		res = res.replace(l, val);
	}

	res = res.replace(/^[ \t,\r\n]*/g, '');
	res = res.replace(/[ \t,\r\n]*$/g, '');
	return res;
};

BXNode.prototype.__PreparePHP = function (str)
{
	str = str.toString();
	if (isPHPBracket(str))
		return trimPHPBracket(str);

	str = str.replace(/\\/g, "\\\\");
	str = str.replace(/'/g, "\\'");
	return "'"+str+"'";
};

BXParser.prototype.ParsePHP = function (str)
{

	var arScripts = [];
	var p = 0, i, bSlashed, bInString, ch, posnext, ti, quote_ch, mm=0, mm2=0;
	while((p = str.indexOf("<?", p)) >= 0)
	{
		mm = 0;
		i = p + 2;
		bSlashed = false;
		bInString = false;
		while(i < str.length-1)
		{
			i++;
			ch = str.substr(i, 1);

			if(!bInString)
			{
				//if it's not comment
				if(ch == "/" && i+1<str.length)
				{
					//find end of php fragment php
					posnext = str.indexOf("?>", i);
					if(posnext==-1)
					{
						//if it's no close tag - so script is unfinished
						p = str.length;
						break;
					}
					posnext += 2;

					ti = 0;
					if(str.substr(i + 1, 1)=="*" && (ti = str.indexOf("*/", i+2))>=0)
						ti += 2;
					else if(str.substr(i+1, 1)=="/" && (ti = str.indexOf("\n", i+2))>=0)
						ti += 1;

					if(ti>0)
					{
						//find begin - "i" and end - "ti" of comment
						// check: what is coming sooner: "END of COMMENT" or "END of SCRIPT"
						if(ti>posnext && str.substr(i+1, 1)!="*")
						{

							//if script is finished - CUT THE SCRIPT
							arScripts.push([p, posnext, str.substr(p, posnext-p)]);
							p = posnext;
							break;
						}
						else
							i = ti - 1; //End of comment come sooner
					}
					continue;
				}
				if(ch == "?" && i+1<str.length && str.substr(i+1, 1)==">")
				{
					i = i+2;
					arScripts.push([p, i, str.substr(p, i-p)]);
					p = i+1;
					break;
				}
			}

			//if(bInString && ch == "\\" && bSlashed)
			if(bInString && ch == "\\")
			{
				bSlashed = true;
				continue;
			}

			if(ch == "\"" || ch == "'")
			{
				if(bInString)
				{
					if(!bSlashed && quote_ch == ch)
						bInString = false;
				}
				else
				{
					bInString = true;
					quote_ch = ch;
				}
			}

			bSlashed = false;
		}

		if(i>=str.length)
			break;

		p = i;
	}
	this.arScripts = [];

	if(arScripts.length > 0)
	{
		var newstr = "";
		var plast = 0, arPHPScript = [], arRes, arTemplate, arScript, str1, strParsed;

		arComponents2 = [];
		arComponents2Length = 0;
		for(i = 0; i < arScripts.length; i++)
		{
			arScript = arScripts[i];
			strParsed = false;
			try{
				for (var j = 0; j < arPHPParsers.length;j++)
				{
					str1 = arPHPParsers[j](arScript[2], this.pMainObj)
					if (str1 && str1.indexOf("<?") == -1)
					{
						strParsed = true;
						break;
					}
				}
			}catch(e) {_alert('ERROR: '+e.message+'\n'+'BXParser.prototype.ParsePHP'+'\n'+'Type: '+e.name);}

			if (strParsed)
				newstr += str.substr(plast, arScript[0] - plast) + str1;
			else if(!this.pMainObj.limit_php_access || (this.pMainObj.limit_php_access && !this.pMainObj.pComponent2Taskbar))
			{
				var id = this.pMainObj.SetBxTag(false, {tag: "php", params: {value : arScript[2]}});
				newstr += str.substr(plast, arScript[0] - plast) + '<img id="' + id + '" src="/bitrix/images/fileman/htmledit2/php.gif" border="0"/>';
			}
			else
			{
				if (window.BS_MESS)
					alert(BS_MESS.LPA_WARNING);
				else
					setTimeout(function(){if(window.BS_MESS){alert(BS_MESS.LPA_WARNING);}}, 1000);

				newstr += str.substr(plast, arScript[0]-plast);
			}

			plast = arScript[1];
		}
		str = newstr + str.substr(plast);
	}
	return str;
};

function isYes(val)
{
	return val && val.toUpperCase() == "Y";
}

function isPHPBracket(val)
{
	return val.substr(0, 2) =='={';
}

function trimPHPBracket(val)
{
	return val.substr(2, val.length - 3);
}

function isNum(val)
{
	var _val = val;
	val = parseFloat(_val);
	if (isNaN(val))
		val = parseInt(_val);
	if (!isNaN(val))
		return _val == val;
	return false;
}

// API BXEditorUtils for PHP
arPHPParsers = [];

BXEditorUtils.prototype.addPHPParser = function(func, pos, extra_access)
{
	if (!extra_access)
		extra_access == false;
	if (!extra_access && limit_php_access)
		return;

	if (pos==undefined || pos ===false)
		arPHPParsers.push(func);
	else
	{
		if (pos<0)
			pos = 0;
		else if (pos > arPHPParsers.length+1)
			pos = arPHPParsers.length+1;

		var newAr = arPHPParsers.slice(0,pos);
		newAr.push(func);
		newAr = newAr.concat(arPHPParsers.slice(pos));
		arPHPParsers = newAr;
		newAr = null;
	}
}


function __PHPParser(){}
__PHPParser.prototype.trimPHPTags = function(str)
{
	if (str.substr(0, 2)!="<?")
		return str;

	if(str.substr(0, 5).toLowerCase()=="<?php")
		str = str.substr(5);
	else
		str = str.substr(2);

	str = str.substr(0, str.length-2);
	return str;
}

__PHPParser.prototype.trimQuotes = function(str, qoute)
{
	str = str.trim();
	if (qoute == undefined)
	{
		f_ch = str.substr(0, 1);
		l_ch = str.substr(0, 1);
		if ((f_ch == '"' && l_ch == '"') || (f_ch == '\'' && l_ch == '\''))
			str = str.substring(1, str.length - 1);
	}
	else
	{
		if (!qoute.length)
			return str;
		f_ch = str.substr(0, 1);
		l_ch = str.substr(0, 1);
		qoute = qoute.substr(0, 1);
		if (f_ch == qoute && l_ch == qoute)
			str = str.substring(1, str.length - 1);
	}
	return str;
}

__PHPParser.prototype.cleanCode = function(str)
{
	var bSlashed = false;
	var bInString = false;
	var new_str = "";
	var i=-1, ch, string_tmp = "", ti, quote_ch, max_i=-1;

	while(i<str.length-1)
	{
		i++;
		ch = str.substr(i, 1);
		if(!bInString)
		{
			if(ch == "/" && i+1<str.length)
			{
				ti = 0;
				if(str.substr(i+1, 1)=="*" && ((ti = str.indexOf("*/", i+2))>=0))
					ti += 2;
				else if(str.substr(i+1, 1)=="/" && ((ti = str.indexOf("\n", i+2))>=0))
					ti += 1;

				if(ti>0)
				{
					if(i>ti)
						alert('iti='+i+'='+ti);
					i = ti;
				}

				continue;
			}

			if(ch == " " || ch == "\r" || ch == "\n" || ch == "\t")
				continue;
		}

		//if(bInString && ch == "\\" && !bSlashed)
		if(bInString && ch == "\\")
		{
			bSlashed = true;
			new_str += ch;
			continue;
		}

		if(ch == "\"" || ch == "'")
		{
			if(bInString)
			{
				if(!bSlashed && quote_ch == ch)
				{
					bInString = false;
					//new_str += ch;
					//continue;
				}
			}
			else
			{
				bInString = true;
				quote_ch = ch;
				//new_str += ch;
				//continue;
			}
		}
		bSlashed = false;
		new_str += ch;
	}
	return new_str;
};


__PHPParser.prototype.parseFunction = function(str)
{
	var pos = str.indexOf("(");
	var lastPos = str.lastIndexOf(")");
	if(pos>=0 && lastPos>=0 && pos<lastPos)
		return {name:str.substr(0, pos),params:str.substring(pos+1,lastPos)};
	else
		return false;
};


__PHPParser.prototype.parseParameters = function(str)
{
	str = this.cleanCode(str);
	var
		prevAr = this.getParams(str),
		tq, j, l = prevAr.length;

	for (j = 0; j < l; j++)
	{
		if (prevAr[j].substr(0, 6).toLowerCase()=='array(')
		{
			prevAr[j] = this.getArray(prevAr[j]);
		}
		else
		{
			tq = this.trimQuotes(prevAr[j]);
			if (isNum(tq) || prevAr[j] != tq)
				prevAr[j] = tq;
			else
				prevAr[j] = this.wrapPHPBrackets(prevAr[j]);
		}
	}
	return prevAr;
};


__PHPParser.prototype.getArray = function(_str)
{
	var resAr = {}; //var resAr = [];
	if (_str.substr(0, 6).toLowerCase()!='array(')
		return _str;
	_str = _str.substring(6, _str.length-1);
	var tempAr = this.getParams(_str);
	var f_ch, l_ch, prop_name, prop_val;

	var len = tempAr.length;

	for (var y=0; y < len; y++)
	{
		if (tempAr[y].substr(0, 6).toLowerCase()=='array(')
		{
			resAr[y] = this.getArray(tempAr[y]);
			continue;
		}

		var p = tempAr[y].indexOf("=>");

		if (p==-1)
		{
			if (tempAr[y] == this.trimQuotes(tempAr[y]))
				resAr[y] = this.wrapPHPBrackets(tempAr[y]);
			else
				resAr[y] = this.trimQuotes(tempAr[y]);
		}
		else
		{
			prop_name = this.trimQuotes(tempAr[y].substr(0,p));
			prop_val = tempAr[y].substr(p+2);
			if (prop_val == this.trimQuotes(prop_val))
				prop_val = this.wrapPHPBrackets(prop_val);
			else
				prop_val = this.trimQuotes(prop_val);

			if (prop_val.substr(0, 6).toLowerCase()=='array(')
				prop_val = this.getArray(prop_val);

			resAr[prop_name] = prop_val;
		}
	}
	return resAr;
};


__PHPParser.prototype.wrapPHPBrackets = function(str)
{
	str = str.trim();
	f_ch = str.substr(0,1);
	l_ch = str.substr(0,1);
	if ((f_ch=='"' && l_ch=='"') || (f_ch=='\'' && l_ch=='\''))
		return str;

	return "={"+str+"}";
};

__PHPParser.prototype.getParams = function(params)
{
	var arParams = [];
	var sk = 0, ch, sl, q1=1,q2=1;
	var param_tmp = "";
	for(var i=0; i<params.length; i++)
	{
		ch = params.substr(i, 1);
		if (ch=="\"" && q2==1 && !sl)
			q1 *=-1;
		else if (ch=="'" && q1==1  && !sl)
			q2 *=-1;
		else if(ch=="\\"  && !sl)
		{
			sl = true;
			param_tmp += ch;
			continue;
		}


		if (sl)
			sl = false;

		if (q2==-1 || q1==-1)
		{
			param_tmp += ch;
			continue;
		}

		if(ch=="(")
			sk++;
		else if(ch==")")
			sk--;
		else if(ch=="," && sk==0)
		{
			arParams.push(param_tmp);
			param_tmp = "";
			continue;
		}

		if(sk<0)
			break;

		param_tmp += ch;
	}
	if(param_tmp!="")
		arParams.push(param_tmp);

	return arParams;
};

function _GAttrEx(pEl, atrName, atrNameEX,pTaskbar)
{
	var
		returnPHP_atr = function(str, b1){return '<?'+JS_stripslashes(pTaskbar.pMainObj.arAPPFragments[parseInt(b1)])+'?>';},
		v = GAttr(pEl, atrNameEX);

	if (v.length > 0 && pTaskbar.pMainObj.bUseAPP)
		return  v.replace(/#APP(\d+)#/ig, returnPHP_atr);

	return GAttr(pEl, atrName);
}

function _SAttrEx(pEl,atrName,atrNameEX,val,pTaskbar)
{
	if (pTaskbar.pMainObj.bUseAPP && val.substr(0, 2) == '<?' && val.substr(val.length-2,2) == '?>')
	{
		var rep = function(str,b1)
		{
			var v = GAttr(pEl, atrNameEX), i;
			if (v.length > 0)
				i = parseInt(v.slice(4,-1));
			else
			{
				pTaskbar.pMainObj.arAPPFragments.push(JS_addslashes(b1));
				i = pTaskbar.pMainObj.arAPPFragments.length - 1;
				SAttr(pEl, atrNameEX, '#APP'+i+'#');
				SAttr(pEl, atrName, " ");
			}
			pTaskbar.pMainObj.arAPPFragments[i] = JS_addslashes(b1);
		}
		val.replace(/<\?((?:.|\s)*?)\?>/ig, rep);
	}
	else
	{
		pEl.removeAttribute(atrNameEX);
		SAttr(pEl, atrName, val);
	}
}


