
function CFormInfo(arQuestions)
{
	this.arQuestions = arQuestions;
	this.arMethods = [];

	var oCFI = this;

	CFormInfo.prototype.addQuestion = function (_SID, _Caption, _isHTMLCaption, _CaptionUnform, _isRequired, _type, _structure)
	{
		oCFI.arQuestions[oCFI.arQuestions.length] = new CFormAnswer(
			_SID, _Caption, _isHTMLCaption, _CaptionUnform, _isRequired, _type, _structure, true, '', 'Y', 'Y'
		);

		return oCFI.arQuestions.length-1;
	}

	CFormInfo.prototype.addMethodInstance = function(_METHOD, _arParams)
	{
		oCFI.arMethods[oCFI.arMethods.length] = new CFormMethod(
			_METHOD, _arParams
		);

		return oCFI.arMethods.length-1;
	}

	CFormInfo.prototype.updateMethodInstance = function (_index, arPar)
	{
		if (oCFI.arMethods[_index])
		{
			oCFI.arMethods[_index].params = arPar;
			return true;
		}

		return false;
	}

	CFormInfo.prototype.removeQuestionBySID = function(FIELD_SID)
	{
		for (i=0; i<oCFI.arQuestions.length; i++)
		{
			if (oCFI.arQuestions[i].FIELD_SID == FIELD_SID) arQuestions[i] = null;
			return true;
		}

		return false;
	}

	CFormInfo.prototype.getQuestionIndexBySID = function(FIELD_SID)
	{
		for (i=0; i<oCFI.arQuestions.length; i++)
		{
			if (oCFI.arQuestions[i].FIELD_SID == FIELD_SID) return i;
		}

		return false;
	}

	CFormInfo.prototype.getQuestionBySID = function(FIELD_SID)
	{
		for (i=0; i<oCFI.arQuestions.length; i++)
		{
			if (oCFI.arQuestions[i].FIELD_SID == FIELD_SID) return arQuestions[i];
		}

		return false;
	}

	CFormInfo.prototype.getMethodParamValue = function (element_index, param_name)
	{
		if (oCFI.arMethods[element_index])
		{
			for (var i = 0; i<oCFI.arMethods[element_index].params.length; i++)
			{
				if (param_name == oCFI.arMethods[element_index].params[i].name)
				{
					return oCFI.arMethods[element_index].params[i].value;
				}
			}
		}

		return false;
	}

	CFormInfo.prototype.PHPParser = function (str)
	{
		var code = oBXEditorUtils.PHPParser.trimPHPTags(str);
		var pMainObj = GLOBAL_pMainObj["FORM_TEMPLATE"];

		if (code.substring(0, 1) == "=") code = code.substring(1);

		code = oBXEditorUtils.PHPParser.cleanCode(code);
		func = oBXEditorUtils.PHPParser.parseFunction(code);

		if (func)
		{
			if (func.name.substr(0, 7) == '$FORM->')
			{
				func.name = func.name.substr(7);

				switch (func.name)
				{
					case 'ShowInput':
						var params = func.params.split(',');
						var __par_FIELD_SID = oBXEditorUtils.PHPParser.trimQuotes(params.shift());
						var element_index = oCFI.getQuestionIndexBySID(__par_FIELD_SID);

						if (element_index !== false)
						{
							var __q_obj = oCFI.arQuestions[element_index];

							for (var __i=0; __i<__q_obj.structure.length; __i++)
							{
								if (__q_obj.structure[__i].ID == '') __q_obj.structure[__i].ID = ++_global_newanswer_counter;
							}

							var funcstr = '<img id="' + pMainObj.SetBxTag(false, {tag: "form_question", params: {name:'element_index', value:element_index, additional_params:params}}) + '" src="/bitrix/images/form/visual/form_edit_' + __q_obj.type + '.gif"  border="0" />';
							return funcstr;
						}
						else
						{
							return '';
						}

					default:

						var arParams = oBXEditorUtils.PHPParser.parseParameters(func.params);

						var arPar = [];

						for (var __i = 0; __i<__arr_api_methods_params[func.name].length; __i++)
						{
							arPar[__i] = {
								name: __arr_api_methods_params[func.name][__i],
								value: ''
							};

							if (arParams[__i]) arPar[__i].value = arParams[__i];
						}

						var _index = oCFI.addMethodInstance(func.name, arPar);

						if (func.name.toLowerCase() == 'showsubmitbutton' || func.name.toLowerCase() == 'showapplybutton' || func.name.toLowerCase() == 'showresetbutton')
						{
							var funcstr = '<img id="' + pMainObj.SetBxTag(false, {tag: "form_api", params: {name: 'METHOD', value: func.name, '1': {name:'INDEX', value:_index}}}) + '" src="/bitrix/images/form/visual/form_api_edit_' + func.name.toLowerCase() + '.gif" border="0" />';
						}
						else
						{
							var funcstr = '<img id="' + pMainObj.SetBxTag(false, {tag: "form_api", params: {name: 'METHOD', value: func.name, '1': {name:'INDEX', value:_index}}}) + '" src="/bitrix/images/form/visual/form_api_' + func.name.toLowerCase() + '.gif" border="0" />';
						}
						return funcstr;
				}
			}

			return str;
		}
	}

	CFormInfo.prototype.__serialize = function(obj)
	{
		if (typeof(obj) == 'object' && obj.constructor == Array)
		{
			var str = '', cnt = 0;
			for (var i in obj)
			{
				++cnt;
				str += oCFI.__serialize(i) + oCFI.__serialize(obj[i]);
			}

			str = "a:" + cnt + ":{" + str + "}";

			return str;
		}
		else if (typeof(obj) == 'boolean')
		{
			return 'b:' + (obj ? 1 : 0) + ';';
		}
		else if (null == obj)
		{
			return 'N;'
		}
		else if (Number(obj) == obj && obj != '' && obj != ' ')
		{
    		return 'i:' + obj + ';';
    	}
  		else if(typeof(obj) == 'string')
  		{
			obj = obj.replace(/\r\n/g, "\n");
			obj = obj.replace(/\n/g, "\r\n");

			var offset = 0;
			if (window._global_BX_UTF)
			{
				for (var q = 0, cnt = obj.length; q < cnt; q++)
				{
					if (obj.charCodeAt(q) > 127) offset++;
				}
			}

  			return 's:' + (offset + obj.length) + ':"' + obj + '";';
		}
	}

	CFormInfo.prototype.getFSIDList = function()
	{
		var __res = [];
		for (i=0; i<oCFI.arQuestions.length; i++)
		{
			__res[i] = oCFI.arQuestions[i].FIELD_SID;
		}

		return __res;
	}

	CFormInfo.prototype.serializeForm = function()
	{
		var FS = new Array();
		for (var __i=0; __i<oCFI.arQuestions.length; __i++)
		{
			FS[__i] = new Array();
			for (var __j in oCFI.arQuestions[__i])
			{
				if (__j == 'structure')
				{
					FS[__i][__j] = new Array();
					for (var __k=0; __k<oCFI.arQuestions[__i][__j].length; __k++)
					{
						FS[__i][__j][__k] = new Array();

						var __fld_type = oCFI.arQuestions[__i][__j][__k]['FIELD_TYPE'];

						for (var __l in oCFI.arQuestions[__i][__j][__k])
						{
							if (__l == "ANS_NEW")
							{
								if (oCFI.arQuestions[__i][__j][__k][__l] == "false") oCFI.arQuestions[__i][__j][__k][__l] = false;
								FS[__i][__j][__k][__l] = oCFI.arQuestions[__i][__j][__k][__l] ? "Y" : "N";
							}
							else
							{
								FS[__i][__j][__k][__l] = oCFI.arQuestions[__i][__j][__k][__l].toString();
							}
						}
					}
				}
				else
				{
					if (__j == "isHTMLCaption" || __j == 'isRequired' || __j == 'inResultsTable' || __j == 'inExcelTable')
					{
						FS[__i][__j] = oCFI.arQuestions[__i][__j] == "Y" ? "Y" : "N";
					}
					else if (__j == 'isNew')
					{
						FS[__i][__j] = oCFI.arQuestions[__i][__j] == true ? "Y" : "N";
					}
					else
						FS[__i][__j] = oCFI.arQuestions[__i][__j].toString();
				}
			}
		}

		var __fs = oCFI.__serialize(FS);
		var __form = document.forms['form1'];
		var __input = __form['FORM_STRUCTURE'];
		__input.value = __fs;

		return true;
	}
}

function CFormAnswer(_SID, _Caption, _isHTMLCaption, _CaptionUnform, _isRequired, _type, _structure, _isNew, _ID, _inResultsTable, _inExcelTable)
{
	this.FIELD_SID = _SID;
	this.isNew = _isNew;
	this.ID = _ID;
	this.CAPTION = _Caption;
	this.CAPTION_UNFORM = _CaptionUnform;
	this.isHTMLCaption = _isHTMLCaption;
	this.isRequired = _isRequired;

	this.inResultsTable = _inResultsTable;
	this.inExcelTable = _inExcelTable;

	this.type = _type;
	this.structure = _structure;
}

function CFormMethod(_METHOD, _arParams)
{
	this.METHOD = _METHOD;
	this.params = _arParams;
}