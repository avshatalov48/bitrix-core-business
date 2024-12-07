function BXNode(parent)
{
	this.oParent = parent;
	this.iLevel = 0;
	if(parent)
	{
		parent.arNodes[parent.arNodes.length] = this;
		this.iLevel = parent.iLevel+1;
	}
	this.arNodes = Array();
	this.arAttributes = Array();
	this.type = null;
	this.text = "";
}
/*
BXNode.prototype.GetEditorCode = function()
{
	var i, res = "";
	if(this.type == 'text')
		 res = bxhtmlspecialchars(this.text);
	else if(this.type == 'element')
	{
		if(this.text=="a" && this.arNodes.length<=0 && !this.arAttributes["href"] && this.arAttributes["name"])
		{
			//anchor
			res = '<img src="/bitrix/images/fileman/htmledit2/anchor.gif" width="20" height="20" __bxtagname="anchor" __bxcontainer="'+bxhtmlspecialchars(BXSerialize(this.arAttributes))+'" />';
		}
		else
		{
			res = "<" + this.text;

			for(var attrName in this.arAttributes)
				res += ' '+attrName+'="'+bxhtmlspecialchars(this.arAttributes[attrName])+'"';

			if(this.arNodes.length<=0)
				res += " />";
			else
			{
				res += ">";

				for(i=0; i<this.arNodes.length; i++)
					res += this.arNodes[i].GetEditorCode();

				res += "</"+this.text+">";
			}
		}
	}
	else
	{
		for(i=0; i<this.arNodes.length; i++)
			res += this.arNodes[i].GetEditorCode();
	}
	return res;
}
*/
BXNode.prototype.__ReturnPHPStr = function(arVals, arParams)
{
	var res = "";
	var un = Math.random().toString().substring(2);
	var i=0, val, comm, zn, p, j;
	for(var key in arVals)
	{
		val = arVals[key];
		i++;
		comm = (arParams && arParams[key] && arParams[key].length>0
			?
				un+'x'+i+'x// '+arParams[key]
			:
				'');
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
}

BXNode.prototype.__PreparePHP = function (str)
{
	str = str.toString();
	if(str.substr(0, 2)=="={" && str.substr(str.length-1, 1)=="}" && str.length>3)
		return str.substring(2, str.length-1);

	str = str.replace(/\\/g, "\\\\");
	str = str.replace(/'/g, "\\'");
	return "'"+str+"'";
}


BXNode.prototype.GetHTML = function(bFormatted)
{
	//bFormatted = false;
	var res = "", ob, good_res;
	if(this.arAttributes["__bxtagname"])
	{
		switch(this.arAttributes["__bxtagname"])
		{
			case 'anchor':
				ob = BXUnSerialize(this.arAttributes["__bxcontainer"]);
				return '<a name="'+bxhtmlspecialchars(ob['name'])+'"></a>';
			case 'component':
				ob = BXUnSerialize(this.arAttributes["__bxcontainer"]);
				arTemplate = this.pParser.pMainObj.FindComponentByPath(ob["SCRIPT_NAME"]);
				good_res = this.__ReturnPHPStr(ob['PARAMS'], arTemplate["FIELDS"]);
				if(ob['ADD_PARAMS'])
					return '<?$APPLICATION->IncludeFile("'+ob['SCRIPT_NAME']+'", Array(\r\n\t'+good_res+'\r\n\t), '+ob['ADD_PARAMS']+'\r\n);?>';
				return '<?$APPLICATION->IncludeFile("'+ob['SCRIPT_NAME']+'", Array(\r\n\t'+good_res+'\r\n\t));?>';
				if(false)
				{
					if(ob['ADD_PARAMS'])
						return '<?$APPLICATION->IncludeFile(\r\n"'+ob['SCRIPT_NAME']+'",'+BXPHPValArray(ob['PARAMS'])+', '+ob['ADD_PARAMS']+'\r\n);?>';
					return '<?$APPLICATION->IncludeFile(\r\n"'+ob['SCRIPT_NAME']+'",'+BXPHPValArray(ob['PARAMS'])+'\r\n);?>';
				}

			case 'php':
				return BXUnSerialize(this.arAttributes["__bxcontainer"]).code;
		}

		return res;
	}

	if(this.arAttributes["_moz_editor_bogus_node"])
		return '';

	res = this.GetHTMLLeft(bFormatted);
	var bNewLine = false;
	var sIndent = '';

	if(bFormatted && this.type!='text')
	{
		var reBlockElements = /^(HTML|HEAD|BODY|TITLE|TABLE|TR|TBODY|P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI)$/i;
		if(reBlockElements.test(this.text))
		{
			for(var j=0; j<this.iLevel-1; j++)
				sIndent += "  ";
			bNewLine = true;
			res = "\r\n" + sIndent + res;
		}
	}

	for(var i=0; i<this.arNodes.length; i++)
		res += this.arNodes[i].GetHTML(bFormatted);

	res += this.GetHTMLRight(bFormatted);
	if(bNewLine)
		res += "\r\n" + (sIndent=='' ? '' : sIndent.substr(2));
	return res;
}

BXNode.prototype.IsPairNode = function()
{
	if(this.text.substr(0, 1) == 'h' || this.text == 'br' || this.text == 'img')
		return false;
	return true;
}

BXNode.prototype.GetHTMLLeft = function(bFormatted)
{
	if(this.type == 'text')
		return bxhtmlspecialchars(this.text);

	if(this.type == 'element')
	{
		var res = "<"+this.text;
		for(var attrName in this.arAttributes)
		{
			if(attrName.substring(0,4).toLowerCase() == '_moz') continue;
			if(attrName=='style' && this.arAttributes[attrName].length<=0) continue;
			res += ' '+attrName+'="'+bxhtmlspecialchars(this.arAttributes[attrName])+'"';
		}

		if(this.arNodes.length<=0 && !this.IsPairNode())
			return res+" />";

		return res+">";
	}

	return "";
}

BXNode.prototype.GetHTMLRight = function(bFormatted)
{
	if(this.type == 'element' && (this.arNodes.length>0 || this.IsPairNode()))
		return "</"+this.text+">";
	return "";
}


//////////////////////////////////////////////
function BXParser(pMainObj)
{
	this.pMainObj = pMainObj;
}

BXParser.prototype._RecursiveParse = function (oParentNode, oBXNode)
{
	//
	switch(oParentNode.nodeType)
	{
		case 9:
			oBXNode.type = 'document';
			break;
		case 1:
			if(oParentNode.__bxID && oParentNode.__bxID == this.__bxID)
				return;
			oParentNode.__bxID = this.__bxID;
			if(oParentNode.tagName.length<=0)
				return;
			oBXNode.type = 'element';
			oBXNode.text = oParentNode.tagName.toLowerCase();

			var attr = oParentNode.attributes;
			for(var j=0; j<attr.length; j++)
			{
				if(attr[j].specified)
				{
					var attrName = attr[j].nodeName.toLowerCase();
					if(attrName == '__bxid')
						continue;

					if(attrName=="style")
						oBXNode.arAttributes[attrName] = oParentNode.style.cssText;
					else if(attrName=="src" || attrName=="href")
						oBXNode.arAttributes[attrName] = oParentNode.getAttribute(attrName, 2);
					else
						oBXNode.arAttributes[attrName] = attr[j].nodeValue;
				}
			}
			//alert(oBXNode.arAttributes.length);
			break;
		case 3:
			oBXNode.type = 'text';
			oBXNode.text = oParentNode.nodeValue;
			break;

	}

	var arChilds = oParentNode.childNodes;
	var oNode, oBXChildNode;

	for(var i=0; i<arChilds.length; i++)
	{
		oNode = arChilds[i];
		oBXChildNode = new BXNode(oBXNode);
		oBXChildNode.pParser = this;
		this._RecursiveParse(oNode, oBXChildNode);
	}
}

function __CheckForComponent(str)
{
	if(str.substr(0, 5)=="<?php")
		str = str.substr(5);
	else
		str = str.substr(2);

	str = str.substr(0, str.length-2);

	var bSlashed = false;
	var bInString = false;
	var arAllStr = [];
	var new_str = "";
	var i=-1, ch, string_tmp = "", ti, quote_ch, max_i=-1;
	while(i<str.length-1)
	{
		i++;

		ch = str.substr(i, 1);
		if(!bInString)
		{
			if(string_tmp!="")
			{
				arAllStr.push(string_tmp);
				string_tmp = "";
				new_str += "\x01"+(arAllStr.length-1)+"\x02";
			}

			//проверяем что не начинается комментарий
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

		if(bInString && ch == "\\" && !bSlashed)
		{
			bSlashed = true;
			continue;
		}

		if(ch == "\"" || ch == "'")
		{
			if(bInString)
			{
				if(!bSlashed && quote_ch == ch)
				{
					bInString = false;
					continue;
				}
			}
			else
			{
				bInString = true;
				quote_ch = ch;
				continue;
			}
		}
		else if(bInString && ch == "\\")
			bSlashed = true;

		bSlashed = false;
		if(bInString)
		{
			string_tmp += ch;
			continue;
		}

		new_str += ch;
	}

	var pos, func_name, params, arParams, arParams2, arIncludeParams, el, p, el_ind, el_val, res_ar, arParamsN, j;
	if((pos = new_str.indexOf("(")))
	{
		func_name = new_str.substr(0, pos+1);
		//$func_name = preg_replace("'\\\$GLOBALS\[(\"|\')(.+?)(\"|\')\]'s", "\$\\2", $func_name);
		switch(func_name.toUpperCase())
		{
		case '$APPLICATION->INCLUDEFILE(':
			params = new_str.substr(pos+1);

			arParams = __GetParams(params);
			arIncludeParams = [];
			if(arParams.length<2)
			{
				return {
						"SCRIPT_NAME": __ReplString(arParams[0], arAllStr),
						"PARAMS": []
						};
			}
			else if(arParams[1].substr(0, 6).toLowerCase()=='array(')
			{
				arParams2 = __GetParams(arParams[1].substr(6));
				for(i=0; i<arParams2.length; i++)
				{
					el = arParams2[i];
					p = el.indexOf("=>");
					el_ind = __ReplString(el.substr(0, p), arAllStr);
					el_val = el.substr(p+2);
					if(el_val.substr(0, 6).toLowerCase()=='array(')
					{
						res_ar = [];
						arParamsN = __GetParams(el_val.substr(6));
						for(j = 0; j<arParamsN.length; j++)
							res_ar.push(__ReplString(arParamsN[j], arAllStr));

						arIncludeParams[el_ind] = res_ar;
					}
					else
						arIncludeParams[el_ind] = __ReplString(el_val, arAllStr);
				}
			}
			if(arParams.length>2)
			{
				return {
						"SCRIPT_NAME": __ReplString(arParams[0], arAllStr),
						"PARAMS": arIncludeParams,
						"ADD_PARAMS": __ReplString(arParams[2], arAllStr, true)
						};
			}

			return {
					"SCRIPT_NAME": __ReplString(arParams[0], arAllStr),
					"PARAMS": arIncludeParams
					};
		}
	}

	return false;
}

function __GetParams(params)
{
	var arParams = [];
	var sk = 0, ch;
	var param_tmp = "";
	for(var i=0; i<params.length; i++)
	{
		ch = params.substr(i, 1);
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
}

function __ReplString(str, arAllStr, bNotOb)
{
	if(str=="")
		return "";

	var arr, re = new RegExp("^\x01([0-9]+)\x02$", "");
	if((arr = re.exec(str)) != null)
	{
		return str.replace(re, arAllStr[arr[1]]);
	}

	re = new RegExp("\x01([0-9]+)\x02", "");
	while((arr = re.exec(str)) != null)
		str = str.replace(re, '"'+arAllStr[arr[1]]+'"');

	if(bNotOb)
		return str;

	return "={"+str+"}";
}

BXParser.prototype.ParsePHP = function (str)
{
	var arScripts = [];
	var p = 0, i, bSlashed, bInString, ch, posnext, ti, quote_ch, mm=0, mm2=0;
	while((p = str.indexOf("<?", p))>=0)
	{
		mm=0;

		i = p + 2;
		bSlashed = false;
		bInString = false;
	//if(mm2++>10) return;alert('p='+p);
		while(i<str.length-1)
		{
		//if(mm%10 == 0) alert('i='+i+' / '+str.substr(i-30, 30)+'^'+str.substr(i, 30));
		//if(mm++>10000) {return;}
			i++;
			ch = str.substr(i, 1);
			if(!bInString)
			{
				//проверяем что не начинается комментарий
				if(ch == "/" && i+1<str.length)
				{
					//найдем позицию окончания php
					posnext = str.indexOf("?>", i);
					if(posnext==-1)
					{
						//окончания нет - значит скрипт незакончен
						p = str.length;
						break;
					}
					posnext += 2;

					ti = 0;
					if(str.substr(i+1, 1)=="*" && (ti = str.indexOf("*/", i+2))>=0)
						ti += 2;
					else if(str.substr(i+1, 1)=="/" && (ti = str.indexOf("\n", i+2))>=0)
						ti += 1;

					if(ti>0)
					{
						// нашли начало(i) и конец комментария (ti)
						// проверим что раньше конец скрипта или конец комментария (например в одной строке "//comment ? >")
						if(ti>posnext && str.substr(i+1, 1)!="*")
						{
							// скрипт закончился раньше комментария
							// вырежем скрипт
							arScripts.push([p, posnext, str.substr(p, posnext-p)]);
							p = posnext;
							break;
						}
						else
						{
							// комментарий закончился раньше скрипта
							i = ti;
						}
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
			} // if(!bInString)

			if(bInString && ch == "\\" && bSlashed)
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
			else if(bInString && ch == "\\")
				bSlashed = true;

			bSlashed = false;
		}

		if(i>=str.length)
			break;

		p = i;
	}

	this.arScripts = [];
	if(arScripts.length>0)
	{
		var newstr = "";
		var plast = 0, arPHPScript = [], arRes, arTemplate, arScript;
		for(i=0; i<arScripts.length; i++)
		{
			arScript = arScripts[i];
			if((arRes = __CheckForComponent(arScript[2]))
				&& (arTemplate = this.pMainObj.FindComponentByPath(arRes["SCRIPT_NAME"])))
				newstr += str.substr(plast, arScript[0]-plast) + '<img src="' + arTemplate['ICON'] + '" border="0" __bxtagname="component" __bxcontainer="' + bxhtmlspecialchars(BXSerialize(arRes)) + '" />';
			else
				newstr += str.substr(plast, arScript[0]-plast) + '<img src="/bitrix/images/fileman/htmledit2/php.gif" border="0" __bxtagname="php" __bxcontainer="' + bxhtmlspecialchars(BXSerialize({'code':arScript[2]})) + '" />';
			plast = arScript[1];
		}
		str = newstr + str.substr(plast);
	}

	return str;
}

BXParser.prototype.Parse = function (str)
{
	this.arNodeParams = {};
	this.__bxID = parseInt(Math.random()*100000)+1;
	this.pNode = new BXNode(null);
	this.pNode.pParser = this;
	this._RecursiveParse(this.pMainObj.pEditorDocument, this.pNode);
}

BXParser.prototype.GetHTML = function (bFormatted)
{
	return this.pNode.GetHTML(bFormatted);
}
/*
BXParser.prototype.GetEditorCode = function()
{
	return this.pNode.GetEditorCode();
}
*/
BXParser.prototype.Optimize = function ()
{
	//return this.pNode.GetHTML(bFormatted);
}

BXParser.prototype.ConvertTags = function ()
{
	this.arNodeParams["phpscript"] = this.arScripts;
	var sName, pElement;
	for(sName in this.arNodeParams)
	{
		this.pMainObj.pEditorDocument.getElementById("{#"+sName+"#}");
	}
}

function BXParseContent(pMainObj, arParams)
{
	var allLinks = pMainObj.pEditorDocument.getElementsByTagName('A'), pNode, pImg;
	for(var i=0; i<allLinks.length; i++)
	{
		pNode = allLinks[i];

		if(pNode.childNodes.length<=0 && !pNode.getAttribute("href", 2) && pNode.getAttribute("name", 2))
		{
			pImg = pMainObj.createEditorElement('IMG', {'src': '/bitrix/images/fileman/htmledit2/anchor.gif', 'width': '20', 'height': '20', '__bxtagname': 'anchor', '__bxcontainer': BXSerialize({'name':pNode.getAttribute("name", 2)})});
			pNode.parentNode.insertBefore(pImg, pNode);
			pNode.parentNode.removeChild(pNode);
		}
	}
}

pBXEventDispatcher.AddEditorHandler("OnSetEditorContent", BXParseContent);
