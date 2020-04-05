function BXFormElementsTaskbar()
{
	var oTaskbar = this;

	BXFormElementsTaskbar.prototype.OnTaskbarCreate = function ()
	{
		oTaskbar.pCellData = oTaskbar.CreateScrollableArea(oTaskbar.pWnd);

		oBXEditorUtils.addPropertyBarHandler('form_question', this.ShowProperties);
		oBXEditorUtils.addPropertyBarHandler('form_question_new', this.ShowProperties);
		oBXEditorUtils.addPropertyBarHandler('form_api', this.ShowAPIProperties);

		this.DisplayTree();
	}

	BXFormElementsTaskbar.prototype.DisplayTree = function ()
	{
		var arElements = [];
		var _arElement = {
			name: 'cformelements',
			title: __arr_messages['FORM_TASKBAR_CFORM'],
			tagname: 'cformelements',
			isGroup: true,
			childElements: []
		};

		for (var i=0; i < __arr_input_types.length; i++)
		{
			_arElement.childElements.push({
				group: 'cformelements',
				tagname: 'form_question_new',
				name: __arr_input_types[i],
				title: __arr_input_types_titles[i],
				icon: '/bitrix/images/form/visual/form_' + __arr_input_types[i] + '.gif',
				isGroup: false,
				params: {name:'element_type', value:__arr_input_types[i]}
			});
		}

		arElements.push(_arElement);

		if (oForm.arQuestions.length > 0)
		{
			var _arElement = {
				name: 'cformoutputelements',
				title: __arr_messages['FORM_TASKBAR_CFORMOUTPUT'],
				tagname: 'cformoutputelements',
				isGroup: true,
				childElements: []
			};

			for (var i=0; i<oForm.arQuestions.length; i++)
			{
				if (!oForm.arQuestions[i].isNew)
				{
					_arElement.childElements.push({
						group: 'cformoutputelements',
						tagname: 'form_question',
						name: oForm.arQuestions[i].FIELD_SID,
						title: oForm.arQuestions[i].CAPTION,
						icon: '/bitrix/images/form/visual/form_' + oForm.arQuestions[i].type + '.gif',
						isGroup: false,
						params: {name: 'element_index', value:i}
					});
				}
			}

			if (_arElement['childElements'].length > 0)
				arElements.push(_arElement);
		}

		var _arElement = {
			name: 'cformoutputapimethods',
			title: __arr_messages['FORM_TASKBAR_API'],
			tagname: 'cformoutputapimethods',
			isGroup: true,
			childElements: []
		};

		for (var i = 0; i < __arr_api_methods.length; i++)
		{
			_arElement.childElements.push({
				group: 'cformoutputapimethods',
				tagname: 'form_api',
				name: __arr_api_methods[i],
				title: __arr_api_methods_title[i],
				icon: '/bitrix/images/form/visual/form_api_' + __arr_api_methods[i].toLowerCase() + '.gif',
				isGroup: false,
				params: {name:'METHOD', value:__arr_api_methods[i]}
			});
		}
		arElements.push(_arElement);

		oTaskbar.DisplayElementList(arElements, oTaskbar.pCellData);
	}

	BXFormElementsTaskbar.prototype.ShowAPIProperties = function(_bNew, _pTaskbar, _pElement)
	{
		var
			oTag = _pTaskbar.pMainObj.GetBxTag(_pElement),
			arParams = oTag.params,
			__APIMethodName = oTag.params.value;

		var __saveParams = function(e)
		{
			__element_index = oTag.params[1].value;

			if (__arr_api_methods_params[__APIMethodName].length > 0)
			{
				for (var __i = 0; __i<__arr_api_methods_params[__APIMethodName].length; __i++)
				{
					__obj = document.getElementById('__param_' + __arr_api_methods_params[__APIMethodName][__i]);

					if (__obj.tagName == "INPUT" && __obj.type == "checkbox")
					{
						oTag.params[2].value[__i].value = __obj.checked ? "Y" : "N";
					}
					else if (__obj.tagName == "SELECT")
					{
						__obj_self = document.getElementById('__param_' + __arr_api_methods_params[__APIMethodName][__i] + '_self');

						oTag.params[2].value[__i].value = __obj_self && __obj_self.value != '' ? __obj_self.value : __obj.value;
					}
					else
					{
						oTag.params[2].value[__i].value = __obj.value;
					}
				}
			}

			oForm.arMethods[__element_index].params = oTag.params[2].value;
		}

		BX.cleanNode(_pTaskbar.pCellProps);

		var _div = document.createElement('DIV');
		_div.setAttribute("align", "center");

		if (oTag.params[1])
		{
			if (oForm.arMethods[oTag.params[1].value])
			{
				oTag.params[2] = {name:'PARAMS', value:oForm.arMethods[oTag.params[1].value].params}
			}
		}

		var __tn = document.createElement('B');
		var __method_caption = oTag.params.value;
		for (var __i=0; __i<__arr_api_methods.length; __i++)
		{
			if (__arr_api_methods[__i] == __method_caption)
			{
				__method_caption = __arr_api_methods_title[__i] + ' (' + __method_caption + ')';
			}
		}

		__tn.appendChild(document.createTextNode(__method_caption));
		_div.appendChild(__tn);
		_div.appendChild(document.createElement('BR'));

		if (!oTag.params[2] || oTag.params[2].value.length <= 0)
		{
			_div.appendChild(document.createTextNode(__arr_messages['FORM_METHOD_HAS_NO_PARAMS']));
		}
		else
		{
			_table = document.createElement('TABLE');
			_table.className = "bxtaskbarpropscomp";
			_table.setAttribute("width", "100%");

			for(var i=0; i<__arr_api_methods_params[__APIMethodName].length; i++)
			{
				param_name = __arr_api_methods_params[__APIMethodName][i];
				param_value = oForm.getMethodParamValue(oTag.params[1].value, param_name);

				_row = _table.insertRow(-1);
				_cell_title = _row.insertCell(-1);
				_cell_input = _row.insertCell(-1);

				_cell_title.setAttribute("align", "right");
				_cell_title.setAttribute("valign", "top");
				_cell_title.setAttribute("width", "40%");
				_cell_title.style.verticalAlign = "top";

				_cell_input.setAttribute("align", "left");
				_cell_input.setAttribute("valign", "top");
				_cell_input.setAttribute("width", "60%");
				_cell_input.style.verticalAlign = "top";

				_cell_title.appendChild(document.createTextNode(__arr_api_methods_params_captions[param_name] + ': '));

				switch (param_name)
				{
					case 'CSS_STYLE':
						if (__APIMethodName  == 'ShowApplyButton' || __APIMethodName  == 'ShowSubmitButton' || __APIMethodName  == 'ShowResetButton')
							__arStyles = GLOBAL_pMainObj['FORM_TEMPLATE'].oStyles.arStyles["INPUT"];
						else
							__arStyles = GLOBAL_pMainObj['FORM_TEMPLATE'].oStyles.arStyles["DEFAULT"];

						if (!__arStyles) __arStyles = [];

						_input = document.createElement('SELECT');
						_input.id = "__param_" + param_name;

						_option = document.createElement('OPTION');
						_option.value = '';
						_input.appendChild(_option);

						__selected = false;

						for (var __i=0; __i<__arStyles.length; __i++)
						{
							_option = document.createElement('OPTION');
							_option.value = __arStyles[__i].className;
							_option.selected = _option.value == param_value;
							//_option.className = _option.value;
							__selected |= _option.selected;

							__node_text = '[' + __arStyles[__i].className + '] ' + (GLOBAL_pMainObj['FORM_TEMPLATE'].arTemplateParams["STYLES_TITLE"][__arStyles[__i].className] ? GLOBAL_pMainObj['FORM_TEMPLATE'].arTemplateParams["STYLES_TITLE"][__arStyles[__i].className] : '');

							_option.appendChild(document.createTextNode(__node_text));
							_input.appendChild(_option);
						}

						_input.onchange = __saveParams;

						_cell_input.appendChild(_input);
						_cell_input.appendChild(document.createElement('BR'));

						_input = document.createElement('INPUT');
						_input.type = "text";
						_input.id = "__param_" + param_name + '_self';
						_input.value = __selected ? '' : param_value;
						_input.onchange = __saveParams;

						_cell_input.appendChild(_input);

						break;

					case 'ALIGN':
						_input = document.createElement('SELECT');
						_input.id = "__param_" + param_name;

						_option = document.createElement('OPTION');
						_option.value = '';
						_input.appendChild(_option);

						__arOptions = ['LEFT', 'RIGHT', 'CENTER'];

						for (var __j=0; __j<__arOptions.length; __j++)
						{
							_option = document.createElement('OPTION');
							_option.value = __arOptions[__j];
							_option.selected = _option.value == param_value;
							_option.appendChild(document.createTextNode(_option.value));

							_input.appendChild(_option);
						}

						_cell_input.appendChild(_input);

						break;

					case 'FIELD_SID':
						_input = document.createElement("SELECT");
						_input.id = '__param_'+param_name;
						_input.style.width = '100px';

						var __arr_sid_list = oForm.getFSIDList();

						var __option = document.createElement("OPTION");
						__option.value = '';

						_input.appendChild(__option);

						for (var _i=0; _i<__arr_sid_list.length; _i++)
						{
							var __option = document.createElement("OPTION");
							__option.value = __arr_sid_list[_i];
							__option.selected = __arr_sid_list[_i] == param_value;
							__option.appendChild(document.createTextNode(__arr_sid_list[_i]));

							_input.appendChild(__option);
						}
						_input.onchange = __saveParams;

						_cell_input.appendChild(_input);

						break;
					case 'ENLARGE_SHOW':
					case 'NOT_SHOW_CSS':
						_input = document.createElement('INPUT');
						_input.type = 'checkbox';
						_input.id = "__param_" + param_name;
						_input.defaultChecked = param_value == "Y";
						_input.checked = param_value == "Y";

						_input.onchange = __saveParams;

						_cell_input.appendChild(_input);

						break;

					default:
						_input = document.createElement('INPUT');
						_input.type = 'text';
						_input.id = "__param_" + param_name;
						_input.value = param_value;
						_input.onchange = __saveParams;

						_cell_input.appendChild(_input);

						break;
				}
			}

			_div.appendChild(_table);
		}

		_pTaskbar.pCellProps.appendChild(_div);
	}


	BXFormElementsTaskbar.prototype.ShowProperties = function(_bNew, _pTaskbar, _pElement)
	{
		var oTag = _pTaskbar.pMainObj.GetBxTag(_pElement);
		var element_index = oTag.params.value;

		var _input,_div;
		var __arr_structure_input_ids = [];
		var _q_type;

		var __saveParams = function(e)
		{
			for (var _i in oForm.arQuestions[element_index])
			{
				_input = document.getElementById('__param_'+_i);
				if (_input)
				{
					oForm.arQuestions[element_index][_i] = _input.type != "checkbox" ? _input.value : (_input.checked ? "Y" : "N");
				}
				else if (_i == "structure")
				{
					if (oForm.arQuestions[element_index][_i] == null) oForm.arQuestions[element_index][_i] = [];

					for (var _j=0; _j<__arr_structure_input_ids.length; _j++)
					{
						obj = document.getElementById(__arr_structure_input_ids[_j]);
						obj_params = obj.id.split('___');

						row_num = obj_params[obj_params.length-2];
						row_fld = obj_params[obj_params.length-1];

						var fld_value = (obj.type != "checkbox" && obj.type != "radio")
							? obj.value
							: (obj.checked ? "Y" : "N");

						if (oForm.arQuestions[element_index][_i][row_num] == null)
							oForm.arQuestions[element_index][_i]['value'][row_num] = [];
						oForm.arQuestions[element_index][_i][row_num][row_fld] = fld_value;
					}
				}
			}
		}

		if (_bNew)
		{
			BX.cleanNode(_pTaskbar.pCellProps);
			var __pt = document.createElement('TABLE');
			__pt.className = "bxtaskbarpropscomp";
			__pt.setAttribute("width", "100%");
		}

		for (var _i in oForm.arQuestions[element_index])
		{
			if (_bNew)
			{
				__pt_row = __pt.insertRow(-1);
			}

			if (_i == "structure")
			{
				_input = document.getElementById('_fld_structure');
				if (!_input)
				{
					_input = document.createElement('DIV');
					_input.setAttribute("align", "left");
					_input.id = "_fld_structure";
				}
				else
				{
					BX.cleanNode(_input);
				}

				var _tab = document.createElement('TABLE');
				_tab.id = 'fld_params_table';
				_tab.className = 'internal';
				_tab.style.width = '200px';

				var __tr_index = 0;
				_tab.setAttribute("width", "200");
				_tab.setAttribute("cellpadding", "0");
				_tab.setAttribute("cellspacing", "0");
				_tab.setAttribute("border", "0");

				_row = _tab.insertRow(-1);
				_row.className = 'heading';

				switch (_q_type)
				{
					case 'textarea':
						__arr_col_captions = [__arr_messages["FORM_FIELD_DEF_VAL"], __arr_messages["FORM_FIELD_WIDTH_VAL"], __arr_messages["FORM_FIELD_HEIGHT_VAL"]];
					break;

					case 'radio':
					case 'checkbox':
					case 'dropdown':
					case 'multiselect':
						__arr_col_captions = [__arr_messages["FORM_ANSWER_VAL"], __arr_messages["FORM_SORT_VAL"], __arr_messages["FORM_DEF_VAL"]];

					break;

					case 'hidden':
						__arr_col_captions = [__arr_messages["FORM_FIELD_DEF_VAL"]];
					break;

					default:
						__arr_col_captions = [__arr_messages["FORM_FIELD_DEF_VAL"], __arr_messages["FORM_FIELD_SIZE_VAL"]];
					break;
				}

				for (var __i=0; __i<__arr_col_captions.length; __i++)
				{
					_cell = _row.insertCell(-1);
					_cell.appendChild(document.createTextNode(__arr_col_captions[__i]));
				}

				switch (_q_type)
				{
					case 'textarea':
						_row = _tab.insertRow(-1);

						_cell = _row.insertCell(-1);

						__input = document.createElement("INPUT");
						__input.type = "text";
						__input.id = 'param___0___VALUE';
						__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
						__input.value = oForm.arQuestions[element_index][_i][0]['VALUE']
						__input.style.width = '90px';
						__input.onchange = __saveParams;

						_cell.appendChild(__input);
						_cell = _row.insertCell(-1);

						__input = document.createElement("INPUT");
						__input.type = "text";
						__input.id = 'param___0___FIELD_WIDTH';
						__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
						__input.value = oForm.arQuestions[element_index][_i][0]['FIELD_WIDTH']
						__input.style.width = '90px';
						__input.onchange = __saveParams;

						_cell.appendChild(__input);

						_cell = _row.insertCell(-1);
						__input = document.createElement("INPUT");
						__input.type = "text";
						__input.id = 'param___0___FIELD_HEIGHT';
						__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
						__input.value = oForm.arQuestions[element_index][_i][0]['FIELD_HEIGHT'];
						__input.style.width = '90px';
						__input.onchange = __saveParams;

						_cell.appendChild(__input);

						__input = document.createElement("INPUT");
						__input.type = "hidden";
						__input.id = 'param___0___ANS_NEW';
						__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
						__input.value = oForm.arQuestions[element_index][_i][0]['ANS_NEW'];

						_cell.appendChild(__input);


					break;

					case 'radio':
					case 'checkbox':
					case 'dropdown':
					case 'multiselect':
						for (var _j=0; _j<oForm.arQuestions[element_index][_i].length; _j++)
						{
							_row = _tab.insertRow(-1);

							_cell = _row.insertCell(-1);

							__input = document.createElement("INPUT");
							__input.type = "text";
							__input.id = 'param___' + _j + '___MESSAGE';
							__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
							__input.value = oForm.arQuestions[element_index][_i][_j]['MESSAGE']
							__input.style.width = '90px';
							__input.onchange = __saveParams;

							_cell.appendChild(__input);
							_cell = _row.insertCell(-1);

							__input = document.createElement("INPUT");
							__input.type = "text";
							__input.id = 'param___' + _j + '___C_SORT';
							__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
							__input.value = oForm.arQuestions[element_index][_i][_j]['C_SORT']
							__input.style.width = '90px';
							__input.onchange = __saveParams;

							_cell.appendChild(__input);

							_cell = _row.insertCell(-1);

							_cell.setAttribute("align", "center");

							__input = document.createElement("INPUT");
							if (_q_type == 'radio' || _q_type == 'dropdown')
							{
								__input.type = "radio";
								__input.name = 'param___' + _i + '___DEFAULT';
							}
							else
							{
								__input.type = "checkbox";
							}
							__input.id = 'param___' + _j + '___DEFAULT';
							__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;

							if (null != oForm.arQuestions[element_index][_i][_j]['DEFAULT'])
							{
								var bChecked = oForm.arQuestions[element_index][_i][_j]['DEFAULT'] == 'Y';
							}
							else
							{
								var bChecked = /CHECKED|SELECTED/i.test(oForm.arQuestions[element_index][_i][_j]['FIELD_PARAM']);
							}

							__input.checked = bChecked;
							__input.defaultChecked = bChecked;

							__input.onchange = __saveParams;

							_cell.appendChild(__input);

							__input = document.createElement("INPUT");
							__input.type = "hidden";
							__input.id = 'param___' + _j + '___ANS_NEW';
							__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
							__input.value = oForm.arQuestions[element_index][_i][_j]['ANS_NEW'];

							_cell.appendChild(__input);

						}

					break;

					case "multiple":
						_tab = null;
						_tab = document.createElement('B');
						_tab.appendChild(document.createTextNode(__arr_messages['FORM_FIELD_MULTIPLE_WARNING']));
					break;

					case "hidden":
						_row = _tab.insertRow(-1);
						_cell = _row.insertCell(-1);
						__input = document.createElement("INPUT");
						__input.type = "text";
						__input.id = 'param___0___VALUE';
						__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
						__input.value = oForm.arQuestions[element_index][_i][0]['VALUE'];
						__input.style.width = '90px';
						__input.onchange = __saveParams;
						_cell.appendChild(__input);

						__input = document.createElement("INPUT");
						__input.type = "hidden";
						__input.id = 'param___0___ANS_NEW';
						__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
						__input.value = oForm.arQuestions[element_index][_i][0]['ANS_NEW'];

						_cell.appendChild(__input);

					break;

					default:
						_row = _tab.insertRow(-1);
						_cell = _row.insertCell(-1);
						__input = document.createElement("INPUT");
						__input.type = "text";
						__input.id = 'param___0___VALUE';
						__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
						__input.value = oForm.arQuestions[element_index][_i][0]['VALUE'];
						__input.style.width = '90px';
						__input.onchange = __saveParams;
						_cell.appendChild(__input);

						_cell = _row.insertCell(-1);
						__input = document.createElement("INPUT");
						__input.type = "text";
						__input.id = 'param___0___FIELD_WIDTH';
						__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
						__input.value = oForm.arQuestions[element_index][_i][0]['FIELD_WIDTH']
						__input.style.width = '90px';
						__input.onchange = __saveParams;

						_cell.appendChild(__input);

						__input = document.createElement("INPUT");
						__input.type = "hidden";
						__input.id = 'param___0___ANS_NEW';
						__arr_structure_input_ids[__arr_structure_input_ids.length] = __input.id;
						__input.value = oForm.arQuestions[element_index][_i][0]['ANS_NEW'];

						_cell.appendChild(__input);

					break;
				}

				_input.appendChild(_tab);
			}
			else
			{
				_input = document.createElement('INPUT');
				switch (_i)
				{
					case 'CAPTION_UNFORM':
						_input = null;
						_input = document.createElement('TEXTAREA');
						_input.style.width = '500px';
						_input.style.width = '400px';
						_input.value = oForm.arQuestions[element_index][_i];

						break;
					case 'isHTMLCaption':
					case 'isRequired':
					case 'inResultsTable':
					case 'inExcelTable':
							_input.type = 'checkbox';
							_input.value = 'Y';
							_input.checked = oForm.arQuestions[element_index][_i] == 'Y';
							_input.defaultChecked = oForm.arQuestions[element_index][_i] == 'Y';

						break;
					case 'isNew':
					case 'CAPTION':
					case 'ID':
							_input.type = 'hidden';
							_input.value = oForm.arQuestions[element_index][_i];
						break;

					case 'type':
							_input.type = 'hidden';
							_input.disabled = true;
							_q_type = oForm.arQuestions[element_index][_i];
							_input.value =_q_type;
							_input.id = '__param_'+_i;

							__title = '';
							for (var __i = 0; __i < __arr_input_types.length; __i++)
							{
								if (_q_type == __arr_input_types[__i])
								{
									__title = __arr_input_types_titles[__i];
									break;
								}
							}

							__div = document.createElement("B");
							__div.style.lineHeight = "20px";
							__div.appendChild(_input);
							__div.appendChild(document.createTextNode('[' + _q_type + '] ' + __title));

							_input = __div;

					case 'FIELD_SID':
					default:
							_input.type = 'text';
							_input.value = oForm.arQuestions[element_index][_i];
						break;
				}

				if (_i != 'type')
				{
					_input.id = '__param_'+_i;
					_input.onchange = __saveParams;
				}
			}

			if (_bNew)
			{
				__pt_cell = __pt_row.insertCell(-1);
				__pt_cell.setAttribute("align", "right");
				__pt_cell.setAttribute("valign", "top");
				__pt_cell.style.verticalAlign = "top";

				if (__arr_field_titles[_i])
					__pt_cell.appendChild(document.createTextNode(__arr_field_titles[_i] + ': '));

				__pt_cell = __pt_row.insertCell(-1);
				__pt_cell.setAttribute("valign", "top");
				__pt_cell.style.verticalAlign = "top";
				__pt_cell.id = '__form_'+_i;
				__pt_cell.appendChild(_input);

				if (_input.type)
				{
					if(_input.type == "hidden")
					{
						__pt_row.style.display = 'none';
					}
				}
			}
			else
			{
				__pt_cell = document.getElementById('__form_'+_i);
				if (__pt_cell)
				{
					BX.cleanNode(__pt_cell);
					__pt_cell.appendChild(_input);
				}
			}
		}

		if (_bNew)
		{
			_pTaskbar.pCellProps.appendChild(__pt);
		}
	}

	BXFormElementsTaskbar.prototype.UnParseElement = function(node, pMainObj)
	{
		var id = node.arAttributes["id"];
		if (!id)
			return false;

		var bxTag = pMainObj.GetBxTag(id);

		if (bxTag.tag == 'form_question')
		{

			var element_index = bxTag.params.value;
			if (element_index != null && oForm.arQuestions[element_index].FIELD_SID != null)
			{
				var sAdditionalParams = bxTag.params.additional_params||'';
				if(!!sAdditionalParams && sAdditionalParams.length > 0)
				{
					sAdditionalParams = ', ' + sAdditionalParams;
				}

				return '<' + '?=$FORM->ShowInput(\'' + oForm.arQuestions[element_index].FIELD_SID + '\''+sAdditionalParams+')?' + '>';
			}
			else
			{
				return '<' + '?=$FORM->ShowInput(\'\')?' + '>';
			}
		}
		else if (bxTag.tag == 'form_api')
		{
			if (bxTag.params.value != null)
			{
				var _param = '';
				if (bxTag.params[1] && bxTag.params[1]["name"] == "INDEX" && oForm.arMethods[bxTag.params[1]["value"]])
				{
					if (oForm.arMethods[bxTag.params[1]["value"]].params)
					{
						__params = oForm.arMethods[bxTag.params[1]["value"]].params;
						for (var __i = 0; __i < __params.length; __i++)
						{
							if (_param != '')
								_param += ',';
							_param += '"' + __params[__i]["value"].replace(/"/g, "&quot;") + '"';
						}
					}
				}
				else
				{
					_param = null != bxTag.params[1] ? '"' + bxTag.params[1]["value"] + '"' : '';
				}

				return '<' + '?=$FORM->' + bxTag.params.value + '(' + _param + ')?' + '>';
			}
		}

		return false;
	}

	BXFormElementsTaskbar.prototype.OnElementDragEnd = function(oEl)
	{
		if (!oEl)
			return;

		// Run it only when dropped into editor doc
		if (oEl.ownerDocument != oTaskbar.pMainObj.pEditorDocument)
			return oTaskbar.OnElementDragEnd(oTaskbar.pMainObj.pEditorDocument.body.appendChild(oEl.cloneNode(false)));

		var oTag = oTaskbar.pMainObj.GetBxTag(oEl);

		oTag.id = null;
		delete oTag.id;
		oEl.id = '';
		oEl.removeAttribute('id');

		oTag = copyObj(oTag);
		var draggedElId = oTaskbar.pMainObj.SetBxTag(oEl, oTag);

		// Hack for safari
		if (BX.browser.IsSafari())
		{
			if (oEl && oEl.parentNode)
				oEl.parentNode.removeChild(oEl);
			oTaskbar.pMainObj.insertHTML('<img src="' + oEl.src + '" id="' + draggedElId + '">');

			setTimeout(function(){oTaskbar.pMainObj.SelectElement(oTaskbar.pMainObj.pEditorDocument.getElementById(draggedElId))}, 20);
		}

		this.nLastDragNDropElement = null;
		this.nLastDragNDropElementFire = false;

		if (oTag.tag == 'form_question')
		{
			var element_index = oTag.params.value;
			oEl.setAttribute('src', '/bitrix/images/form/visual/form_edit_' + oForm.arQuestions[element_index].type + '.gif');
			oEl.removeAttribute("height");
			oEl.removeAttribute("width");
			oEl.style.width = null;
			oEl.style.height = null;
		}
		else if (oTag.tag == 'form_question_new')
		{
			var element_type = oTag.params.value;
			var __arStructure = [];
			var __csort = 0;
			var __imax = 1;

			oTag.tag = 'form_question';
			oEl.setAttribute('src', '/bitrix/images/form/visual/form_edit_' + element_type + '.gif');
			oEl.removeAttribute("height");
			oEl.removeAttribute("width");
			oEl.style.width = null;
			oEl.style.height = null;

			if (element_type == 'dropdown' || element_type == 'checkbox' || element_type == 'multiselect' || element_type == 'radio')
			{
				__imax = 10;
			}
			for (var i=0; i<__imax; i++)
			{
				__csort += 100;
				__arStructure[i] = {ID:++_global_newanswer_counter,FIELD_ID:_global_newinput_counter,QUESTION_ID:_global_newinput_counter,TIMESTAMP_X:'',MESSAGE:(i==0 ? ' ' : ''),VALUE:'',FIELD_TYPE:element_type,FIELD_WIDTH:'',FIELD_HEIGHT:'',FIELD_PARAM:'',C_SORT:__csort.toString(),ACTIVE:'Y',ANS_NEW:true}
			}

			while (true)
			{
				new_element_id = 'new_field_' + Math.floor(Math.random() * 100000);
				if (oForm.getQuestionIndexBySID(new_element_id) === false) break;
			}

			var element_index = oForm.addQuestion(new_element_id, '', 'N', '', 'N', element_type, __arStructure, 'Y');

			oTag.params = {name:'element_index', value: element_index};
			oTaskbar.pMainObj.SetBxTag(false, oTag);
		}
		else if (oTag.tag == 'form_api')
		{
			var val = oTag.params.value;
			var element_type = val.toLowerCase();
			if (element_type == 'showsubmitbutton' || element_type == 'showapplybutton' || element_type == 'showresetbutton')
			{
				oEl.setAttribute('src', '/bitrix/images/form/visual/form_api_edit_' + element_type + '.gif');
				oEl.removeAttribute("height");
				oEl.removeAttribute("width");
				oEl.style.width = null;
				oEl.style.height = null;
			}

			__ar_params = [];
			for (var j = 0; j<__arr_api_methods_params[val].length; j++)
				__ar_params[j] = {name:__arr_api_methods_params[val][j], value: ''};

			var _index = oForm.addMethodInstance(val, __ar_params);
			oTag.params[1] = {name:'INDEX', value:_index};

			oTaskbar.pMainObj.SetBxTag(false, oTag);
		}
		else
		{
			return;
		}
	}
}