function _BXRemoveAllChild(pNode)
{
	try
	{
		while(pNode.childNodes.length>0)
			pNode.removeChild(pNode.childNodes[0]);
	}
	catch(e)
	{}
}

function CFormValidatorSettings(bSimple)
{
	var oCFVS = this;
	this.bSimple = bSimple;

	var sType;

	var wnd = null;

	CFormValidatorSettings.prototype.OpenDialog = function(url)
	{
		BX.showWait();
		BX.ajax.get(url, function(result)
		{
			BX.closeWait();

			if(!wnd)
			{
				wnd = new BX.PopupWindow(Math.random(), null, {
					closeByEsc: true,
					titleBar: {content: BX.create('SPAN',{text:BX.message('WND_TITLE')})},
					content: BX.create('DIV', {
						html: result
					}),
					buttons: [
						new BX.PopupWindowButton({
							text : BX.message('JS_CORE_WINDOW_SAVE'),
							className : "popup-window-button-accept",
							events : {click :jsFormValidatorSettings.SaveSettings}
						}),
						new BX.PopupWindowButtonLink({
							text : BX.message('JS_CORE_WINDOW_CLOSE'),
							className : "popup-window-button-link-cancel",
							events : {click : function(){wnd.close()}}
						})
					]
				});
			}
			else
			{
				wnd.setContent(BX.create('DIV', {
					html: result
				}));
			}

			wnd.show();
		});
	}

	CFormValidatorSettings.prototype.CloseDialog = function()
	{
		if(wnd)
		{
			wnd.close();
		}
	}

	CFormValidatorSettings.prototype.UpdateAll = function()
	{
		oCFVS.UpdateSelect();
		oCFVS.UpdateListStatus();
		oCFVS.UpdateList();
	}

	CFormValidatorSettings.prototype.UpdateListStatus = function()
	{
		for (var i=0; i<arCurrentValidators.length; i++)
		{
			arCurrentValidators[i].VALID = false;
			for (var j in arValidatorsType[oCFVS.sType])
			{
				if (arValidatorsType[oCFVS.sType][j] == arCurrentValidators[i].NAME)
				{
					arCurrentValidators[i].VALID = true;
					continue;
				}
			}
		}
	}

	CFormValidatorSettings.prototype.UpdateSelect = function()
	{
		oValidatorsList = document.getElementById('validators_list');
		_BXRemoveAllChild(oValidatorsList);

		if (!oCFVS.bSimple)
		{
			var arAnswers = document.forms.form1["ANSWER[]"];

			var sType = "";
			for (var i=1; i<=arAnswers.length; i++)
			{
				var oTypeSelect = document.forms.form1["FIELD_TYPE_" + i];
				var oMessage = document.forms.form1["MESSAGE_" + i];
				if (oTypeSelect && oMessage && oMessage.value.length != 0)
				{
					if (sType == "") sType = oTypeSelect.value;
					else
					{
						if (sType != oTypeSelect.value)
						{
							var _container = document.createElement('SPAN');
							_container.appendChild(document.createTextNode(BX.message('ERROR_MULTITYPE')));

							oTypeTitle = document.getElementById('type_title');
							oTypeTitle.style.fontWeight = 'bold';
							_BXRemoveAllChild(oTypeTitle);
							oTypeTitle.appendChild(document.createTextNode("multiple"));

							oValidatorsList.appendChild(_container);
							return;
						}
					}
				}
			}

			if (!sType)
			{
				sType = document.forms.form1["FIELD_TYPE_1"].value;
			}
		}
		else
		{
			var oTypeSelector = document.getElementById('selected_type');

			if (oTypeSelector)
			{
				var sType = oTypeSelector.value;
				var sTypeTitle = oTypeSelector.options[oTypeSelector.selectedIndex].text;
			}
		}

		oCFVS.sType = sType;

		if (!sType) return;

		oTypeTitle = document.getElementById('type_title');
		oTypeTitle.style.fontWeight = 'bold';
		_BXRemoveAllChild(oTypeTitle);
		if (oCFVS.bSimple)
			oTypeTitle.appendChild(document.createTextNode(sTypeTitle));
		else
			oTypeTitle.appendChild(document.createTextNode(sType));

		if (arValidatorsType[sType] && arValidatorsType[sType].length > 0)
		{
			_selector = document.createElement('SELECT');
			_selector.name = 'validators_selector';
			_selector.id = 'validators_selector';

			for (i=0; i<arValidatorsType[sType].length; i++)
			{
				var _option = document.createElement('OPTION');
				_option.value = arValidatorsType[sType][i];
				_inner = document.createTextNode(
					'[' + arValidatorsType[sType][i] + '] ' + arValidators[arValidatorsType[sType][i]].DESCRIPTION
				);
				_option.appendChild(_inner);
				_selector.appendChild(_option);
			}

			var _button = document.createElement('DIV');
			_button.id = "btn_new";
			_button.style.height = '20px';
			_button.style.width = '20px';

			_button.style.cursor = 'pointer';
			_button.setAttribute('TITLE', BX.message('ADD_TITLE'));
			_button.onclick = oCFVS.addValidator;

			var __tab = document.createElement('TABLE');
			__tab.setAttribute('cellspacing', "5");
			__tab.setAttribute('cellpadding', "0");
			__tab.setAttribute('border', '0');

			var __row = __tab.insertRow(-1);

			var __cell = __row.insertCell(-1);
			__cell.appendChild(_selector);
			var __cell = __row.insertCell(-1);
			__cell.appendChild(_button);
			var __cell = __row.insertCell(-1);
			__cell.appendChild(document.createTextNode(BX.message('ADD_TITLE')));

			oValidatorsList.appendChild(__tab);
		}
		else
		{
			var _container = document.createElement('SPAN');
			_container.appendChild(document.createTextNode(BX.message('NO_REGISTERED_VALS_TYPE')));

			oValidatorsList.appendChild(_container);
		}
	}

	CFormValidatorSettings.prototype.addValidator = function()
	{
		var oValidatorList = document.getElementById('validators_selector');
		if (oValidatorList)
		{
			var current_item = oValidatorList.value;
			if (arValidators[current_item])
			{
				if (arValidators[current_item].HAS_SETTINGS == "Y")
				{
					var url = '/bitrix/admin/form_validator_props.php?validator=' + current_item;
					if (phpVars.LANGUAGE_ID) url += '&lang=' + phpVars.LANGUAGE_ID;
					oCFVS.OpenDialog(url);
				}
				else
				{
					oCFVS.Save(current_item);
				}
			}
		}
	}

	CFormValidatorSettings.prototype.Save = function(name, arParams)
	{
		var index = arCurrentValidators.length;
		if (typeof(arParams) == "undefined") arParams = [];
		arCurrentValidators[index] = {NAME:name,PARAMS:arParams};
		oCFVS.UpdateAll();
	}

	CFormValidatorSettings.prototype.UpdateList = function()
	{
		var oOutput = document.getElementById('validators_current');
		if (oOutput)
		{
			_BXRemoveAllChild(oOutput);

			if (arCurrentValidators.length > 0)
			{
				var _tab = document.createElement("TABLE");
				_tab.className = "internal";
				var _row = _tab.insertRow(-1);
				_row.className = 'heading';
				_cell = _row.insertCell(-1);
				_cell.appendChild(document.createTextNode(''));
				_cell = _row.insertCell(-1);
				_cell.appendChild(document.createTextNode(BX.message('LIST_HEAD_VAL')));
				_cell = _row.insertCell(-1);
				_cell.appendChild(document.createTextNode(BX.message('LIST_HEAD_PARAMS')));
				_cell = _row.insertCell(-1);
				_cell.appendChild(document.createTextNode(BX.message('LIST_HEAD_REMOVE')));


				for (var i=0; i<arCurrentValidators.length; i++)
				{
					_row = _tab.insertRow(-1);

					_cell = _row.insertCell(-1);
					_cell.setAttribute("VALIGN", "TOP");
					_cell.setAttribute("ALIGN", "CENTER");

					var _img = document.createElement('IMG');
					_img.src = '/bitrix/images/form/' + (arCurrentValidators[i].VALID ? "green.gif" : "red.gif");
					_img.style.height = "14px";
					_img.style.width = "14px";
					_img.alt = BX.message(arCurrentValidators[i].VALID ? 'VALID' : 'INVALID');

					_cell.appendChild(_img);

					_cell = _row.insertCell(-1);
					_cell.setAttribute("VALIGN", "TOP");
					_cell.appendChild(document.createTextNode('[' + arCurrentValidators[i].NAME + '] ' + arValidators[arCurrentValidators[i].NAME].DESCRIPTION));
					_cell = _row.insertCell(-1);
					_cell.setAttribute("VALIGN", "TOP");
					if (arCurrentValidators[i].PARAMS)
					{
						for (var j in arCurrentValidators[i].PARAMS)
						{
							_cell.appendChild(document.createTextNode(arCurrentValidators[i].PARAMS[j].TITLE + arCurrentValidators[i].PARAMS[j].VALUE));
							_cell.appendChild(document.createElement('BR'));
						}
					}
					_cell = _row.insertCell(-1);
					_cell.setAttribute("VALIGN", "TOP");
					_cell.setAttribute("ALIGN", "CENTER");

					var _button = document.createElement('DIV');
					_button.id = "btn_delete";
					_button.style.height = '20px';
					_button.style.width = '20px';
					_button.style.cursor = 'pointer';
					_button.setAttribute('title', BX.message('DEL_TITLE'));
					//_button.setAttribute('onClick', 'jsFormValidatorSettings.RemoveValidator(' + i + ')');

					_button.setAttribute('__BX_val_index', i);
					_button.onclick = oCFVS.RemoveValidator;

					_cell.appendChild(_button);
					//_cell.innerHTML = _cell.innerHTML; // arggghhhh damned ie
				}

				oOutput.appendChild(_tab);
			}
			else
			{
				oOutput.appendChild(document.createTextNode(BX.message('NO_CURRENT')));
			}
		}
	}

	CFormValidatorSettings.prototype.SaveSettings = function()
	{
		oForm = document.forms['val_settings_form'];
		if (oForm)
		{
			var arParams = [];
			for (var i = 0; i < oForm.elements.length; i++)
			{
				if (oForm.elements[i].name.substring(0, 6) == "PARAMS")
				{
					param_name = oForm.elements[i].name.substring(7, oForm.elements[i].name.length - 1);
					var oParamHandle = document.getElementById('PARAM_' + param_name);
					var oParamTitleHandle = document.getElementById('PARAM_TITLE_' + param_name);
					switch (oParamHandle.type)
					{
						case "checkbox":
							arParams[arParams.length] = {
								NAME: param_name,
								TITLE: oParamTitleHandle.innerHTML,
								VALUE: oParamHandle.checked ? "Y" : "N"
							}
						break;

						default:
							arParams[arParams.length] = {
								NAME: param_name,
								TITLE: oParamTitleHandle.innerHTML,
								VALUE: oForm['PARAMS[' + param_name + ']'].value
							}
					}
				}
			}

			oCFVS.Save(oForm.VALIDATOR.value, arParams);
			oCFVS.CloseDialog();
		}
	}

	CFormValidatorSettings.prototype.RemoveValidator = function()
	{
		var index = this.getAttribute('__BX_val_index');
		if (arCurrentValidators[index])
		{
			arTmp = arCurrentValidators;
			arTmp.splice(index, 1);

			arCurrentValidators = [];
			for (var i in arTmp)
			{
				arCurrentValidators[arCurrentValidators.length] = arTmp[i];
			}

			arTmp = null;
			oCFVS.UpdateAll();
		}
	}

	CFormValidatorSettings.prototype.__serialize = function(obj)
	{
		if (typeof(obj) == 'object')
		{
			var str = '', cnt = 0;
			for (var i in obj)
			{
				++cnt;
				str += oCFVS.__serialize(i) + oCFVS.__serialize(obj[i]);
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
			return parseInt(obj) == obj ? 'i:' + obj + ';' : 'd:' + obj + ';';
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

			return 's:' + (obj.length + offset) + ':"' + obj + '";';
		}
	}

	CFormValidatorSettings.prototype.PrepareToSubmit = function()
	{
		var arStruct = [];
		for (var i=0; i<arCurrentValidators.length; i++)
		{
			arStruct[i] = {NAME:arCurrentValidators[i].NAME};
			if (arCurrentValidators[i].PARAMS && arCurrentValidators[i].PARAMS.length > 0)
			{
				arStruct[i].PARAMS = [];
				for (var j=0; j<arCurrentValidators[i].PARAMS.length; j++)
				{
					arStruct[i].PARAMS[j] = {
						NAME:arCurrentValidators[i].PARAMS[j].NAME,
						VALUE:arCurrentValidators[i].PARAMS[j].VALUE
					}
				}
			}
		}

		var oStructureSaver = document.forms.form1.VAL_STRUCTURE;
		var struct_serialized = oCFVS.__serialize(arStruct);

		oStructureSaver.value = struct_serialized;
		return true;
	}
}