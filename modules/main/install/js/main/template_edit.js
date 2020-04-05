function encodeContentForLCA(str)
{
	_$arComponents = {}; // Global JS array of components
	_$compLength = 0; // Count of elements in  _$arComponents

	var arScripts = [];
	var p = 0, i, bSlashed, bInString, ch, posnext, ti, quote_ch, mm=0, mm2=0;
	while((p = str.indexOf("<?", p))>=0)
	{
		mm=0;
		i = p + 2;
		bSlashed = false;
		bInString = false;
		while(i<str.length-1)
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
					if(str.substr(i+1, 1)=="*" && (ti = str.indexOf("*/", i+2))>=0)
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

	// Replace components2 by #COMPONENTXXXX#
	if(arScripts.length>0)
	{
		var newstr = "";
		var plast = 0, arPHPScript = [], arRes, arTemplate, arScript, str1, strParsed;
		var c2 = '<?$APPLICATION->INCLUDECOMPONENT';
		var c2len = c2.length;
		for(i=0; i<arScripts.length; i++)
		{
			arScript = arScripts[i];
			strParsed = false;
			if (arScript[2].substr(0, c2len).toUpperCase() == c2)
			{
				var key = str_pad_left(++_$compLength, 4, '0');
				_$arComponents[key] = arScript[2];
				newstr += str.substr(plast, arScript[0] - plast) + '#COMPONENT' + String(key) + '#';
			}
			else
				newstr += str.substr(plast, arScript[0] - plast) + arScript[2];

			plast = arScript[1];
		}
		str = newstr + str.substr(plast);
	}

	return str;
}

function decodeContentFromLCA(str)
{
	var replaceLCA = function(str, key)
	{
		var cCode = _$arComponents[key]; // Code of component: $APLICATION->IncludeComponent( .........
		if (!cCode)
			return '';
		return cCode;
	}
	str = str.replace(/#COMPONENT(\d{4})#/ig, replaceLCA);
	return str;
}

function str_pad_left (input, pad_length, pad_string)
{
	input = String (input);
	if (pad_string.length > 0)
	{
		var buffer = "";
		var padi = 0;
		pad_length = parseInt(pad_length);
		for (var i = 0, z = pad_length - input.length; i < z; i++)
			buffer += pad_string;
		input = buffer + input;
	}
	return input;
}

function preview_template(ID, sess_id, sign)
{
	if (window.GLOBAL_pMainObj)
	{
		var pMainObj = GLOBAL_pMainObj['CONTENT'];
		if (pMainObj)
			pMainObj.SaveContent();
	}

	var content = BX('bxed_CONTENT').value;
	if(content.indexOf('#WORK_AREA#') == -1)
	{
		alert(messErrorWA);
		return;
	}

	BX.showWait();
	BX.ajax.post('/bitrix/admin/template_create_preview.php?ID='+ID,
		{
			CONTENT : decodeContentFromLCA(content),
			STYLES : BX('__STYLES').value,
			TEMPLATE_STYLES : BX('__TEMPLATE_STYLES').value,
			sessid: sess_id
		},
		function()
		{
			BX.closeWait();
			setTimeout(function()
				{
					if (window.__status)
						window.open('/?bitrix_preview_site_template='+sign+'&bx_template_preview_mode=Y');
					else if (window.strWarning)
						alert(window.strWarning);
				}, 10
			);

		}
	);
}
