	function GroupManager(isAdd, $controlName)
	{
		var groupExists = BX($controlName+'_EXISTS');
		var groupSelected = BX($controlName);
		var groupSelectedHidden = BX($controlName+'_HIDDEN');
		var groupSelectedOptions = BX.findChildren(groupSelected, {"tag" : "option"}, true);

		if(isAdd)
		{
			var groupExistsOptions = BX.findChildren(groupExists, {"tag" : "option"}, true);
			if(groupExistsOptions && groupExistsOptions.length > 0)
			{
				var arSelectedValues = [];
				var elementFor;
				for(var i in groupSelectedOptions)
				{
					elementFor = groupSelectedOptions[i];
					if(!elementFor) continue;
					arSelectedValues.push(elementFor.value);
				}


				var elementAdd;
				for(var i in groupExistsOptions)
				{
					elementFor = groupExistsOptions[i];
					if(!elementFor || !elementFor.selected) continue;
					if(!BX.util.in_array(elementFor.value, arSelectedValues))
					{
						elementAdd = elementFor.cloneNode(true);
						groupSelected.appendChild(elementAdd);
					}
				}
			}
		}
		else
		{
			var elementDelete;
			var elementDeleteParent;
			var groupSelectedSelectedOptions = [];
			if(groupSelectedOptions && groupSelectedOptions.length > 0)
			{
				for(var i in groupSelectedOptions)
				{
					if(groupSelectedOptions[i] && groupSelectedOptions[i].selected)
					{
						groupSelectedSelectedOptions.push(groupSelectedOptions[i]);
					}
				}
			}

			while(groupSelectedSelectedOptions.length>0)
			{
				elementDelete = groupSelectedSelectedOptions.pop();
				elementDeleteParent = elementDelete.parentNode;
				if(elementDeleteParent)
					elementDeleteParent.removeChild(elementDelete);
			}
		}

		var element;
		var selectedGroupId = '';
		var arSelectedGroupId = [];
		groupSelectedOptions = BX.findChildren(groupSelected, {"tag" : "option"}, true);
		for(var i in groupSelectedOptions)
		{
			element = groupSelectedOptions[i];
			if(element && element != 'undefined')
			{
				if(element.value != 'undefined' && parseInt(element.value)>0 && !BX.util.in_array(element.value, arSelectedGroupId))
				{
					selectedGroupId = selectedGroupId+element.value+',';
					arSelectedGroupId.push(element.value);
				}
			}
		}
		groupSelectedHidden.value = selectedGroupId;
	}


	function ConnectorGetHtmlForm(data)
	{
		var templ = document.getElementById('connector-template');
		var connectorFormHtml = templ.innerHTML;

		for(var key in data)
		{
			connectorFormHtml = connectorFormHtml.replace(new RegExp(key,'g'), data[key]);
		}

		return connectorFormHtml;
	}
	function ConnectorSettingWatch()
	{
        var arConForms = document.getElementsByName('post_form');
        var controls = arConForms[arConForms.length - 1].elements;
		var ctrl;
		for(var i in controls){
			ctrl = controls[i];
			if(ctrl && ctrl.name && BX.type.isString(ctrl.name) && ctrl.name.substring(0,11)=='CONNECTOR_S'){
				BX.unbindAll(BX(ctrl));
				BX.bind(BX(ctrl), 'change', function() {ConnectorSettingGetCount(this);});
			}
		}
	}

	function ConnectorSettingShowToggle(element, elementParent)
	{
		if(element)
			elementParent = BX.findParent(element, {"tag" : "div", "className": "connector_form"}, true);

		BX.toggleClass(elementParent, 'sender-box-list-item-hidden');
		/*
		 var elementContainer = BX.findChild(elementParent, {"tag" : "div", "className": "connector_form_container"}, true);
		 elementContainer.style.display = BX.toggle(elementContainer.style.display, ['block', 'none']);
		 */
	}
	function ConnectorSettingDelete(element)
	{
		var elementDelete = BX.findParent(element, {"tag" : "div", "className": "connector_form"}, true);

		var easing = new BX.easing({
			duration : 500,
			start : { height : 100, opacity: 100 },
			finish : { height : 0, opacity : 0 },
			transition : BX.easing.transitions.quart,
			step : function(state){
				elementDelete.style.opacity = state.opacity/100;
			},
			complete : function() {
				BX.remove(elementDelete);
				ConnectorCounterSummary();
			}
		});
		easing.animate();
	}

	function ConnectorSettingGetCount(element, form)
	{
		var elementParent;
		if(form)
		{
			elementParent = form;
		}
		else
		{
			elementParent = BX.findParent(element, {"tag" : "div", "className": "connector_form"}, true);
		}

		var arConForms = document.getElementsByName('post_form');
		var controls = arConForms[arConForms.length - 1].elements;
		var ctrl;
		var filteredControls = [];
		var currentParent;
		for(var i in controls)
		{
			ctrl = controls[i];

			if(!ctrl || !ctrl.name || !BX.type.isString(ctrl.name))
			{
				continue;
			}

			if(ctrl.name.substring(0,11) != 'CONNECTOR_S')
			{
				continue;
			}

			currentParent = BX.findParent(ctrl, {"tag" : "div", "className": "connector_form"}, true);
			if(currentParent != elementParent)
			{
				continue;
			}

			if (ctrl.disabled)
			{
				continue;
			}

			var found = filteredControls.filter(function (filteredCtrl) {
				return filteredCtrl == ctrl;
			});
			if (found.length == 0)
			{
				filteredControls.push(ctrl);
			}
		}

		var arAjaxQueryFieldsData = [];
		for(var i = 0; i < filteredControls.length; i++)
		{
			ctrl = filteredControls[i];
			switch(ctrl.type.toLowerCase())
			{
				case 'text':
				case 'textarea':
				case 'password':
				case 'number':
				case 'hidden':
				case 'select-one':
					arAjaxQueryFieldsData.push({name: ctrl.name, value: ctrl.value});
					break;
				case 'file':
					break;
				case 'radio':
				case 'checkbox':
					if(ctrl.checked)
					{
						arAjaxQueryFieldsData.push({name: ctrl.name, value: ctrl.value});
					}
					break;
				case 'select-multiple':
					var multipleValues = [];
					for (var j = 0; j < ctrl.options.length; j++)
					{
						if (ctrl.options[j].selected)
						{
							multipleValues.push(ctrl.options[j].value);
						}
					}
					if (multipleValues.length > 0)
					{
						arAjaxQueryFieldsData.push({name : ctrl.name, value : multipleValues});
					}

					break;
				default:
					break;
			}
		}

		var arAjaxQueryFields = {};
		for(var k = 0; k < arAjaxQueryFieldsData.length; k++)
		{

			var _data = arAjaxQueryFieldsData[k];
			if(BX.type.isString(arAjaxQueryFields[_data.name]))
			{
				arAjaxQueryFields[_data.name] = [arAjaxQueryFields[_data.name]];
			}

			if(BX.type.isArray(arAjaxQueryFields[_data.name]))
			{
				if(!BX.util.in_array(_data.value, arAjaxQueryFields[_data.name]))
				{
					arAjaxQueryFields[_data.name].push(_data.value);
				}
			}
			else
			{
				arAjaxQueryFields[_data.name] = _data.value;
			}
		}

		var counter = BX.findChild(elementParent, {
			"className": "connector_form_counter"
		}, true);
		if(counter)
		{
			counter.innerHTML = '';
			BX.addClass(counter.parentNode, 'loading');
		}

		BX.ajax({
			url: 'sender_group_count.php',
			method: 'POST',
			data: arAjaxQueryFields,
			dataType: 'json',
			timeout: 30,
			async: true,
			processData: true,
			onsuccess: function(data){
				if(counter)
				{
					BX.removeClass(counter.parentNode, 'loading');
					counter.innerHTML = data.COUNT;
					ConnectorCounterSummary();
				}
			},
			onfailure: function(){
				var dialog = new BX.CDialog({
					height: 100,
					width: 500,
					'title': BX.message('GROUP_ADDRESS_CALC_TITLE'),
					'content': BX.message('GROUP_ADDRESS_CALC_TEXT'),
					'buttons': [BX.CDialog.prototype.btnClose]
				});
				dialog.ShowError(BX.message('GROUP_ADDRESS_CALC_ERROR'));
				dialog.Show();
				if(counter)
				{
					BX.removeClass(counter.parentNode, 'loading');
					counter.innerHTML = BX.message('GROUP_ADDRESS_CALC_ERROR').toLowerCase();
				}
			}
		});

	}

	function addNewConnector()
	{
		var name = connectorListToAdd[BX('connector_list_to_add').value]['NAME'];
		var htmlForm = connectorListToAdd[BX('connector_list_to_add').value]['FORM'];
		htmlForm = htmlForm.replace(new RegExp("%CONNECTOR_NUM%",'g'), (Math.floor(Math.random() * (10000 - 100 + 1)) + 100) );

		var html = ConnectorGetHtmlForm({'%CONNECTOR_NAME%':  name, '%CONNECTOR_COUNT%':  '0', '%CONNECTOR_FORM%':  htmlForm});

		var parsedHtml = BX.processHTML(html);


		var newParentElement = document.createElement('div');
		newParentElement.innerHTML = parsedHtml.HTML;

		var newConnectorNode = BX.findChild(newParentElement, {'tag': 'div'});
		var connector_form_container = BX('connector_form_container');
		newConnectorNodeDisplay = newConnectorNode.style.display;
		newConnectorNode.style.display = 'none';

		connector_form_container.insertBefore(newConnectorNode, connector_form_container.firstChild);
		if(parsedHtml.SCRIPT.length>0)
		{
			var script;
			for(var i in parsedHtml['SCRIPT'])
			{
				script = parsedHtml['SCRIPT'][i];
				BX.evalGlobal(script.JS);
			}
		}

		ConnectorSettingShowToggle(false, newConnectorNode);

		var easing = new BX.easing({
			duration : 500,
			start : { height : 0, opacity : 0 },
			finish : { height : 100, opacity: 100 },
			transition : BX.easing.transitions.quart,
			step : function(state){
				newConnectorNode.style.opacity = state.opacity/100;
				newConnectorNode.style.display = newConnectorNodeDisplay;
			},
			complete : function() {
			}
		});
		easing.animate();

		ConnectorSettingGetCount(null, newConnectorNode);
		ConnectorSettingWatch();
	}

	function ConnectorCounterSummary()
	{
		var cnt = 0;
		var cntSummary = 0;
		var findContainer = BX('connector_form_container');
		var counterList = BX.findChildren(findContainer, {"className": "connector_form_counter"}, true);

		for(var i in counterList)
		{
			cnt = parseInt(counterList[i].innerHTML);
			if(!isNaN(cnt))
				cntSummary += cnt;
		}

		BX('sender_group_address_counter').innerHTML = cntSummary;
	}


	function SetAddressToControl(controlName, address, bAdd)
	{
		var control = BX(controlName);
		if(bAdd)
			control.value += address;
		else
			control.value = address;
	}
	function ProcessAddressToControl(controlName, address, deleteAddress)
	{
		address = BX.util.trim(address);
		var control = BX(controlName);
		var addressList = [];
		var addressListNew = [];
		if(control.value)
			addressList = control.value.split(',');

		var bFind = false;
		for(var addr in addressList)
		{
			addressFromList = BX.util.trim(addressList[addr]);

			if(addressFromList == address)
			{
				bFind = true;
				if(!deleteAddress)
					addressListNew.push(addressFromList);
			}
			else
			{
				addressListNew.push(addressFromList);
			}
		}

		if(!bFind && !deleteAddress)
			addressListNew.push(address);

		control.value = addressListNew.join(', ');
	}
	function DeleteAddressFromControl(controlName, address)
	{
		ProcessAddressToControl(controlName, address, true)
	}
	function AddAddressToControl(controlName, address)
	{
		ProcessAddressToControl(controlName, address, false)
	}

	function SetSendType()
	{
		var sendType = BX('chain_send_type').value;
		var typeContList = BX.findChildren(
			BX('chain_send_type_list_container'),
			{'className': 'sender-box-list-item'},
			true
		);
		for(var i in typeContList){
			if(typeContList[i].id != 'chain_send_type_'+sendType)
				typeContList[i].style.display = 'none';
			else
				typeContList[i].style.display = 'block';
		}

		BX('SEND_TYPE').value = sendType;
		BX('sender_wizard_chain_send_type_btn').disabled = true;
		BX('chain_send_type').disabled = true;
	}

	function DeleteSelectedSendType(obj)
	{
		BX.findParent(obj, {'className':'sender-box-list-item'}).style.display='none';
		BX('SEND_TYPE').value = '';
		BX('sender_wizard_chain_send_type_btn').disabled = false;
		BX('chain_send_type').disabled = false;
	}

	function SenderLetter()
	{
		var id;
		var container;
		var isBlockCurrentEditorVersion;
		var editorBlockContainer;


		this.init = function(params)
		{
			this.container = BX(params.container);
			var container = BX.findChild(this.container, {'className': 'typearea'}, true);
			this.id = container.getAttribute('name');
			this.textareaId = container.getAttribute('name');
			this.editorBlockContainer = BX('bx-sender-block-editor-' + this.id);

			var _this = this;

			var childList, child, i;

			_this.changeTemplateList('BASE');

			child = BX.findChild(this.container, {'className': 'sender-template-btn-close'}, true);
			if(child)
			{
				BX.bind(child, 'click', function()
				{
					BX.onCustomEvent(_this.container, 'onSenderMailingTemplateListHide');
				});
			}

			childList = BX.findChildren(this.container, {'className': 'sender-template-type-selector-button'}, true);
			for(i in childList)
			{
				if(!childList[i]) continue;

				child = childList[i];
				BX.bind(child, 'click', function()
				{
					var bxsendertype = 'BASE';
					if(this.getAttribute && this.getAttribute('data-bx-sender-tmpl-type'))
					{
						bxsendertype = this.getAttribute('data-bx-sender-tmpl-type');
					}

					_this.changeTemplateList(bxsendertype);
				});
			}

			childList = BX.findChildren(this.container, {'className': 'sender-template-list-block-selector'}, true);
			for(i in childList)
			{
				if(!childList[i]) continue;

				child = childList[i];
				BX.bind(child, 'click', function()
				{
					var bxsenderversion = 'block', bxsendername = '', bxsendertype = 'BASE', bxsendernum = 0, bxsenderlang = '';
					if(this.getAttribute && this.getAttribute('data-bx-sender-tmpl-type'))
					{
						bxsenderversion = this.getAttribute('data-bx-sender-tmpl-version');
						bxsendername = this.getAttribute('data-bx-sender-tmpl-name');
						bxsendertype = this.getAttribute('data-bx-sender-tmpl-type');
						bxsendernum = this.getAttribute('data-bx-sender-tmpl-code');
						bxsenderlang = this.getAttribute('data-bx-sender-tmpl-lang');
					}

					_this.setTemplate({'lang': bxsenderlang, 'version': bxsenderversion, 'name': bxsendername, 'type': bxsendertype, 'num': bxsendernum});
				});
			}

			childList = BX.findChildren(this.container, {'className': 'sender-template-message-caption-container-btn'}, true);
			for(i in childList)
			{
				if(!childList[i]) continue;

				child = childList[i];
				BX.bind(child, 'click', function()
				{
					BX.onCustomEvent(_this.container, 'onSenderMailingTemplateListShow');
				});
			}

			childList = BX.findChildren(this.container, {'className': 'sender-template-message-preview-btn'}, true);
			for(i in childList)
			{
				if(!childList[i]) continue;

				child = childList[i];
				BX.bind(child, 'click', function()
				{
					if(this.getAttribute && this.getAttribute('data-bx-sender-tmpl-type'))
					{
						var bxsendertype = this.getAttribute('data-bx-sender-tmpl-type');
						var bxsendernum = this.getAttribute('data-bx-sender-tmpl-code');
						var bxsenderlang = this.getAttribute('data-bx-sender-tmpl-lang');
						var url = '/bitrix/admin/sender_template_admin.php?action=get_template';
						url = url + '&lang=' + bxsenderlang + '&template_type=' + bxsendertype + '&template_id=' + bxsendernum;
						BX.util.popup(url, 800, 800);
					}
				});
			}

			return this;
		};

		this.setTemplateContainer = function(container)
		{
			this.container = BX(container);

			return this;
		};


		this.onSetTemplate = function(func)
		{
			BX.addCustomEvent(this.container, 'onSenderMailingTemplateSet', func);
		};

		this.setTemplate = function(param)
		{
			if(!this.container) return;

			BX.onCustomEvent(this.container, 'onSenderMailingTemplateSet');

			var canSaveContent = false;
			var isBlockEditorShow = this.editorBlockContainer.style.display !== 'none';
			var isBlockEditorNeedShow = param.version !== 'visual';
			var isExistsMessage = !!BX(this.getTextAreaAttributeId()).value;
			if((isBlockEditorNeedShow && isBlockEditorShow))
			{
				canSaveContent = true;
			}
			if(!isExistsMessage || canSaveContent || confirm(BX.message("SENDER_SHOW_TEMPLATE_LIST")))
			{
				//var letterManager = new SenderLetterManager;
				//letterManager.setContent(this.id, param.version, param.type, param.num);
				this.setContent(this.textareaId, param.version, param.type, param.num, param.lang);

				var containerTemplateCaption = BX.findChild(this.container, {'className': 'sender-template-message-caption-container'}, true);
				if (containerTemplateCaption) containerTemplateCaption.innerText = param.name;

				return true;
			}
			else
			{
				return false;
			}
		};

		this.changeTemplateList = function(type)
		{
			if(!this.container) return;

			container = BX.findChild(this.container, {'className': 'sender-template-cont'}, true);
			if(!container) return;

			var tmplTypeContList = BX.findChildren(container, {'className': 'sender-template-list-type-container'}, true);
			for(var i in tmplTypeContList)
				tmplTypeContList[i].style.display = 'none';

			var typeContainer = BX.findChild(container, {'className': 'sender-template-list-type-container-'+type}, true);
			typeContainer.style.display = 'block';

			var buttonList = BX.findChildren(container, {'className': 'sender-template-type-selector-button'}, true);
			for(var j in buttonList)
			{
				if(!BX.hasClass(buttonList[j], 'sender-template-type-selector-button-type-'+type))
					BX.removeClass(buttonList[j], 'sender-template-type-selector-button-selected');
				else
					BX.addClass(buttonList[j], 'sender-template-type-selector-button-selected');
			}
		};

		this.getHtmlEditor = function()
		{
			var container = BX.findChild(this.container, {'className': 'typearea'}, true);
			var name = container.getAttribute('name');

			return window.BXHtmlEditor.Get(name);
		};

		this.getTextAreaAttributeId = function()
		{
			var container = BX.findChild(this.container, {'className': 'typearea'}, true);
			return container.getAttribute('id');
		};

		this.putMessage = function(str, bChangeAllContent)
		{
			var bMessageHtmlEditorVisible = false;

			if(!this.container) return;

			var id = this.getTextAreaAttributeId();

			var messageHtmlEditor;
			if(window.BXHtmlEditor)
				messageHtmlEditor = this.getHtmlEditor();

			var messageContainer = BX(id);

			if(messageHtmlEditor) bMessageHtmlEditorVisible = messageHtmlEditor.IsShown();

			if(bMessageHtmlEditorVisible)
			{
				if(bChangeAllContent)
				{
					messageHtmlEditor.SetContent(str, true);
				}
				else
				{
					messageHtmlEditor.InsertHtml(str);
				}
			}
			else
			{
				if(bChangeAllContent)
				{
					messageContainer.value = str;
				}
				else
				{
					messageContainer.value += str;
				}


				BX.fireEvent(messageContainer, 'change');
			}
		};

		this.setContent = function(id, version, type, num, lang)
		{
			var url = '/bitrix/admin/sender_template_admin.php?action=get_template';
			url = url + '&lang=' + lang + '&template_type=' + type + '&template_id=' + num;

			var blockContainer = BX('bx-sender-block-editor-' + id);
			var typeInput = blockContainer.querySelector('input[name*="TEMPLATE_TYPE"]');
			var idInput = blockContainer.querySelector('input[name*="TEMPLATE_ID"]');

			if(version == 'block')
			{
				if(typeInput && idInput)
				{
					typeInput.value = type;
					idInput.value = num;
				}

				var blockEditor = BX.BlockEditorManager.get(id);
				blockEditor.load(url);
				this.switchView(id, true);
			}
			else
			{
				BX.ajax({
					'url': url,
					'method': 'GET',
					'dataType': 'html',
					'data': {},
					'onsuccess': BX.delegate(function(content)
					{
						if(typeInput && idInput)
						{
							typeInput.value = '';
							idInput.value = '';
						}

						this.putMessage(content, true);
						this.switchView(id, false);
					}, this)
				});
			}
		};

		this.switchView = function(id, isShowBlock)
		{
			var block = BX('bx-sender-block-editor-' + id);
			var visual = BX('bx-sender-visual-editor-' + id);
			var htmlEditor = BXHtmlEditor.Get(id);

			if(isShowBlock)
			{
				block.style.display = 'block';
				visual.style.display = 'none';
				if(htmlEditor) htmlEditor.Hide();
			}
			else
			{
				visual.style.display = 'block';
				if(htmlEditor) htmlEditor.Show();
				block.style.display = 'none';
			}

			this.isBlockCurrentEditorVersion = isShowBlock;
		};
	}

	function SenderLetterManager()
	{
		if (SenderLetterManager.instance)
		{
			return SenderLetterManager.instance;
		}

		this.list = {};
		this.templateListByType = {};
		this.mailBlockList = {};
		this.placeHolderList = {};

		this.onPlaceHolderSelectorListCreate = function (placeHolderSelectorList)
		{
			placeHolderSelectorList.placeHolderList = this.getPlaceHolderList();
		};
		this.onGetControlsMap = function(controlsMap)
		{
			controlsMap.push({
				id: 'placeholder_selector',
				compact: true,
				hidden: false,
				sort: 1,
				checkWidth: false,
				offsetWidth: 32
			});
		};
		this.onEditorInitedBefore = function(editor)
		{
			BX.addCustomEvent(
				editor,
				"PlaceHolderSelectorListCreate",
				this.onPlaceHolderSelectorListCreate.bind(this)
			);
			BX.addCustomEvent(
				editor,
				"GetControlsMap",
				this.onGetControlsMap.bind(this)
			);
		};

		this.onEditorParse = function(mode)
		{
			if (!mode)
			{
				var content = this.content;

				content.replace(/(^[\s\S]*?)(<body.*?>)/i, BX.delegate(function(str){
						this.mailContentParsed.header = str;
						return '';
					}, this)
				);

				content = content.replace(/(<\/body>[\s\S]*?$)/i,  BX.delegate(function(str){
						this.mailContentParsed.footer = str;
						return '';
					}, this)
				);

				this.content = content;
			}
		};

		this.onEditorAfterParse = function(editor, mode)
		{
			if (mode)
			{
				var content = this.content;

				content = content.replace(/^[\s\S]*?<body.*?>/i, "");
				content = content.replace(/<\/body>[\s\S]*?$/i, "");

				if(this.mailContentParsed.header != "" && this.mailContentParsed.footer != "")
				{
					content = this.mailContentParsed.header + content + this.mailContentParsed.footer;
				}
				else
				{
					content = editor.content;
				}

				this.content = content;
			}
		};

		this.onEditorInitedAfter = function(editor)
		{
			editor.components.SetComponentIcludeMethod('EventMessageThemeCompiler::includeComponent');

			editor.config.mailblocks = this.getMailBlockList();
			editor.mailblocks = new BXHtmlEditor.BXEditorMailBlocks(editor);
			editor.mailblocksTaskbar = new BXHtmlEditor.MailBlocksControl(editor, editor.taskbarManager);
			editor.taskbarManager.AddTaskbar(editor.mailblocksTaskbar);
			editor.taskbarManager.ShowTaskbar(editor.mailblocksTaskbar.GetId());

			editor.mailContentParsed = {'header': '', 'footer': ''};
			BX.addCustomEvent(editor, "OnParse", this.onEditorParse.bind(editor));
			BX.addCustomEvent(editor, "OnAfterParse", this.onEditorAfterParse.bind(editor, editor));
		};

		this.add = function(id, params)
		{
			var obj = new SenderLetter;
			obj.id = id;
			obj.init(params);

			this.list[id] = obj;

			return obj;
		};

		this.get = function(id)
		{
			if(this.list[id])
				return this.list[id];
			else
				return null;
		};

		this.onSetTemplate = function(func)
		{
			BX.addCustomEvent('onSenderMailingTemplateSet', func);
		};

		this.onShowTemplateList = function(func)
		{
			BX.addCustomEvent('onSenderMailingTemplateListShow', func);
		};
		this.onHideTemplateList = function(func)
		{
			BX.addCustomEvent('onSenderMailingTemplateListHide', func);
		};

		this.setMailBlockList = function(mailBlockList){
			this.mailBlockList = mailBlockList;
		};
		this.getMailBlockList = function(){
			return this.mailBlockList;
		};
		this.setPlaceHolderList = function(placeHolderList){
			this.placeHolderList = placeHolderList;
		};
		this.getPlaceHolderList = function(){
			return this.placeHolderList;
		};


		BX.addCustomEvent(
			'OnEditorInitedBefore',
			this.onEditorInitedBefore.bind(this)
		);
		BX.addCustomEvent(
			'OnEditorInitedAfter',
			this.onEditorInitedAfter.bind(this)
		);

		SenderLetterManager.instance = this;
	}

	function SenderLetterContainer(params)
	{
		if (SenderLetterContainer.instance)
		{
			return SenderLetterContainer.instance;
		}

		this.deleteItem = function (elementDelete)
		{
			var easing = new BX.easing({
				duration : 500,
				start : { height : 100, opacity: 100 },
				finish : { height : 0, opacity : 0 },
				transition : BX.easing.transitions.quart,
				step : function(state){
					elementDelete.style.opacity = state.opacity/100;
				},
				complete : BX.delegate(function() {
					this.removeDraggableItem(elementDelete);
					BX.remove(elementDelete);
					this.sortItems();
				}, this)
			});
			easing.animate();
		};

		this.addItem = function (obj)
		{
			formContainer = this.container;

			var num = (Math.floor(Math.random() * (10000 - 100 + 1)) + 100);
			var message = letterTemplate.FIELDS.MESSAGE;
			message = message.replace(new RegExp("SENDER_LETTER_TEMPLATE_MESSAGE",'g'), 'CHAIN_MESSAGE_'+num );
			message = message.replace(new RegExp("sender_letter_template_message",'g'), 'chain_message_'+num );
			message = message.replace(new RegExp("%SENDER_LETTER_TEMPLATE_BODY_NUM%",'g'), num );
			var htmlForm = letterTemplate.BODY.replace(new RegExp("%SENDER_LETTER_TEMPLATE_BODY_NUM%",'g'), num );
			htmlForm = htmlForm.replace(new RegExp("%SENDER_LETTER_TEMPLATE_MESSAGE%",'g'), message );

			var parsedHtml = BX.processHTML(htmlForm);

			var newParentElement = document.createElement('div');
			newParentElement.innerHTML = parsedHtml.HTML;
			var newNode = BX.findChild(newParentElement, {'tag': 'div'});

			var target;
			if(obj)
				target = BX.findNextSibling(obj);

			newNode.style.display = 'none';
			if(target)
			{
				formContainer.insertBefore(newNode, target);
			}
			else
			{
				formContainer.appendChild(newNode);
			}

			this.addListenerControlItem(newNode);
			this.setTimeText(newNode);
			this.addDraggableItem(newNode);

			this.sortItems();

			var easing = new BX.easing({
				duration : 500,
				start : { height : 0, opacity : 0 },
				finish : { height : 100, opacity: 100 },
				transition : BX.easing.transitions.quart,
				step : function(state){
					newNode.style.opacity = state.opacity/100;
					newNode.style.display = 'block';
				},
				complete : function() {
				}
			});
			easing.animate();


			if(parsedHtml.SCRIPT.length>0)
			{
				var script;
				for(var i in parsedHtml['SCRIPT'])
				{
					script = parsedHtml['SCRIPT'][i];
					BX.evalGlobal(script.JS);
				}
			}
		};

		this.toggleShow = function (body, button, item, isShow)
		{
			if(!body && item)
			{
				body = item.querySelector('.sender_letter_container_body');
			}
			if(!button && item)
			{
				button = item.querySelector('.sender_letter_container_button_show');
			}

			if(body && button)
			{
				if(isShow === null)
				{
					if(body.style.display == 'none')
						isShow = true;
					else
						isShow = false;
				}

				BX.removeClass(button, 'sender_letter_container_button_hide');
				if(isShow)
				{
					body.style.display = '';
					button.innerHTML = BX.message("SENDER_MAILING_TRIG_LETTER_MESSAGE_HIDE");
					BX.addClass(button, 'sender_letter_container_button_hide');
				}
				else
				{
					body.style.display = 'none';
					button.innerHTML = BX.message("SENDER_MAILING_TRIG_LETTER_MESSAGE_SHOW");
				}

			}
		};

		this.addListenerControlItem = function(item)
		{
			if(!item || !item.querySelector)
				return;

			var buttonToggleShow = item.querySelector('.sender_letter_container_button_show');
			var contToggleShow = item.querySelector('.sender_letter_container_body');
			if(buttonToggleShow && contToggleShow)
			{
				BX.bind(buttonToggleShow, 'click', BX.delegate(function(){
					this.toggleShow(contToggleShow, buttonToggleShow, null, null);
				}, this));
			}

			var buttonDeleteItem = item.querySelector('.sender_letter_container_button_delete');
			if(buttonDeleteItem)
			{
				BX.bind(buttonDeleteItem, 'click', BX.delegate(function(){
					this.deleteItem(item);
				}, this));
			}

			var subject = item.querySelector('.sender_letter_container_subject');
			var caption = item.querySelector('.sender_letter_container_caption');
			if(subject && caption)
			{
				BX.bind(subject, 'input', function(){
					caption.innerHTML = subject.value;
				});
				BX.bind(subject, 'change', function(){
					caption.innerHTML = subject.value;
				});
			}

			var showTimeDialogButton = item.querySelector('.sender_letter_container_time_button');
			if(showTimeDialogButton)
			{
				BX.bind(showTimeDialogButton, 'click', BX.delegate(function(){
					this.showTimeDialog(item, showTimeDialogButton);
				}, this));
			}
		};

		this.initListenerControls = function()
		{
			var itemList = this.container.children;
			for(var i in itemList)
			{
				this.addListenerControlItem(itemList[i]);
			}
		};

		this.addDraggableItem = function(item)
		{
			if(!this.dragdrop) return;
			this.dragdrop.addSortableItem(item);
			this.dragdrop.bindDragItem([item]);
		};

		this.removeDraggableItem = function(item)
		{
			if(!this.dragdrop) return;
			this.dragdrop.removeSortableItem(item);
		};

		this.initDraggableItems = function()
		{
			//var itemList = this.container.children;
			var _this = this;
			this.dragdrop = BX.DragDrop.create({
				dragItemClassName: 'sender-trigger-chain-container-letter',
				dragItemControlClassName: 'sender_letter_container_head',
				sortable: {
					rootElem: BX('SENDER_TRIGGER_CHAIN_CONTAINER'),
					gagClass: 'senderdrag',
					gagHtml: ''
				},
				dragStart: function(eventObj, dragElement, event){
					_this.toggleShow(null, null, dragElement, false);
					BX.addClass(_this.container, 'sendercontdrag');
				},
				dragEnd: function(eventObj, dragElement, event){
					BX.removeClass(_this.container, 'sendercontdrag');
					_this.sortItems();
					_this.initTimeText();
					//_this.toggleShow(null, null, dragElement, true);
					_this.repairEditor(dragElement);
				}
			});
		};

		this.repairEditor = function(item)
		{
			var container = BX.findChild(item, {'className': 'typearea'}, true);
			var id, name;
			var attr;
			for(var i in container.attributes)
			{
				if (!container.attributes[i]) continue;
				attr = container.attributes[i];

				if(attr.nodeName == 'id')
					id = attr.nodeValue;
				else if(attr.nodeName == 'name')
					name = attr.nodeValue;
			}

			var messageHtmlEditor;
			if(window.BXHtmlEditor)
				messageHtmlEditor = window.BXHtmlEditor.Get(name);

			var messageContainer = BX(id);
			if(!messageHtmlEditor)
			{
				return;
			}

			setTimeout(
				function(){
					messageHtmlEditor.CheckAndReInit();
				}, 100
			);
		};

		this.initTimeText = function()
		{
			var itemList = this.container.children;
			for(var i in itemList)
			{
				this.setTimeText(itemList[i]);
			}

		};

		this.sortItems = function()
		{
			var itemList = this.container.children;
			var elementSort;
			var elementSortText;

			var sort = 1;
			for(var i in itemList)
			{
				if(!itemList[i] || !itemList[i].querySelectorAll)
					continue;

				elementSort = itemList[i].querySelector('input.sender_letter_container_sorter[type=hidden]');
				elementSortText = itemList[i].querySelector('.sender_letter_container_sorter_text');
				if(elementSort && elementSortText)
				{
					elementSort.value = sort;
					elementSortText.innerHTML = sort;

					sort++;
				}
			}
		};

		this.showTimeDialog = function(item, button)
		{
			var popupWindow = BX.PopupWindowManager.create(
				'sender-letter-container-time-dialog',
				button,
				{
					'darkMode': false,
					'closeIcon': true,
					'content': BX('SENDER_TIME_DIALOG'),
					'className': 'adm-workarea'
				}
			);
			popupWindow.close();
			popupWindow.setBindElement(button);

			var btnTimeCancel = BX('SENDER_TIME_DIALOG_BTN_CANCEL');
			var btnTimeSave = BX('SENDER_TIME_DIALOG_BTN_SAVE');

			popupWindow.close();

			BX.unbindAll(btnTimeCancel);
			BX.bind(btnTimeCancel, 'click', function(){popupWindow.close();});

			BX.unbindAll(btnTimeSave);
			BX.bind(btnTimeSave, 'click', BX.delegate(function(){
				this.setTimeItem(item);
				this.setTimeText(item);
				popupWindow.close();
			}, this));

			this.setTimeToDialog(item);
			popupWindow.show();
		};

		this.setTimeText = function(item)
		{
			if(!item || !item.querySelector)
				return;

			var time = item.querySelector('.sender_letter_container_time');
			var timeText = item.querySelector('.sender_letter_container_time_text');
			var timeAfterEvent = item.querySelector('.sender_letter_container_time_text_first');
			var timeAfterLetter = item.querySelector('.sender_letter_container_time_text_nonfirst');

			var timeObj = this.convertTime(time.value);

			timeText.innerHTML = timeObj.VALUE + ' ' + timeObj.TEXT;
			if(this.container.children[0] == item)
			{
				BX(timeAfterEvent).style.display = '';
				timeAfterLetter.style.display = 'none';
			}
			else
			{
				BX(timeAfterEvent).style.display = 'none';
				timeAfterLetter.style.display = '';
			}
		};

		this.setTimeToDialog = function(item)
		{
			if(!item || !item.querySelector)
				return;

			var dlgTimeType = BX('SENDER_TIME_DIALOG_TYPE');
			var dlgTimeValue = BX('SENDER_TIME_DIALOG_VALUE');
			var time = item.querySelector('.sender_letter_container_time');
			var timeObj = this.convertTime(time.value);

			dlgTimeType.value = timeObj.TYPE;
			dlgTimeValue.value = timeObj.VALUE;
		};

		this.setTimeItem = function(item)
		{
			if(!item || !item.querySelector)
				return;

			var dlgTimeType = BX('SENDER_TIME_DIALOG_TYPE');
			var dlgTimeValue = BX('SENDER_TIME_DIALOG_VALUE');
			var time = item.querySelector('.sender_letter_container_time');

			time.value = this.convertTime(null, {'TYPE': dlgTimeType.value, 'VALUE': dlgTimeValue.value});
		};

		this.convertTime = function(minutes, timeObj)
		{
			var i;
			if(minutes !== null)
			{
				minutes = parseInt(minutes);
				if(isNaN(minutes) || minutes == 0)
					minutes = 0;

				if(minutes != 0) for(i in dictionarySenderTime)
				{
					if((minutes % dictionarySenderTime[i].VALUE) === 0)
					{
						return {
							'TYPE': dictionarySenderTime[i].TYPE,
							'VALUE': minutes/dictionarySenderTime[i].VALUE,
							'TEXT': dictionarySenderTime[i].TEXT
						};
					}
				}

				var result = dictionarySenderTime[dictionarySenderTime.length-1];
				return {
					'TYPE': result.TYPE,
					'VALUE': 0,
					'TEXT': result.TEXT
				};
			}
			else
			{
				var value = parseInt(timeObj.VALUE);
				if(isNaN(value))
					value = 0;

				for(i in dictionarySenderTime)
				{
					if(dictionarySenderTime[i].VALUE && dictionarySenderTime[i].TYPE == timeObj.TYPE)
					{
						return value * dictionarySenderTime[i].VALUE;
					}
				}

				return 0;
			}
		};

		this.container = params.container;
		this.initListenerControls();
		this.initTimeText();
		this.initDraggableItems();

		SenderLetterContainer.instance = this;
	}