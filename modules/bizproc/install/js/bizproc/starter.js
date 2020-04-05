;(function()
{
	'use strict';
	BX.namespace('BX.Bizproc');

	if (BX.Bizproc.Starter)
	{
		return;
	}

	var increment = 0;
	var onAjaxFailure = function()
	{
		window.alert(BX.message('BIZPROC_JS_BP_STARTER_REQUEST_FAILURE'));
	};

	var Manager = {
		instances: [],

		put: function(starter)
		{
			this.instances.push(starter);
			return this;
		},

		/**
		 * @param {Starter} target
		 */
		findSimilar: function(target)
		{
			var result = [target];

			for (var i = 0; i < this.instances.length; ++i)
			{
				var ins = this.instances[i];
				if (ins !== target
					&& ins.moduleId === target.moduleId
					&& ins.entity === target.entity
					&& ins.documentType === target.documentType
				)
				{
					result.push(ins);
				}
			}

			return result;
		},

		/**
		 * @param {Starter} starter
		 * @param {string} event
		 * @param {mixed} parameters
		 */
		fireEvent: function(starter, event, parameters)
		{
			var instances = this.findSimilar(starter);
			for (var i = 0; i < instances.length; ++i)
			{
				BX.onCustomEvent(instances[i], event, parameters);
			}
		}
	};

	var Starter = function(config)
	{
		this.id = ++increment;

		this.templates = config.templates || null;

		this.moduleId = config.moduleId;
		this.entity = config.entity;
		this.documentType = config.documentType;
		this.documentId = config.documentId;

		this.ajaxUrl = config.ajaxUrl || '/bitrix/components/bitrix/bizproc.workflow.start/ajax.php';

		Manager.put(this);
	};

	Starter.singleStart = function(config, cb)
	{
		var starter = new Starter(config);

		if (BX.type.isFunction(cb))
		{
			BX.addCustomEvent(starter, 'onAfterStartWorkflow', cb);
		}

		if (config.hasParameters)
		{
			starter.showParametersPopup(config.templateId, {title: config.templateName});
		}
		else
		{
			starter.startWorkflow(config.templateId);
		}
	};

	Starter.prototype =
	{
		showTemplatesMenu: function(buttonNode)
		{
			if (this.templates === null)
			{
				this.loadTemplates(
					this.showTemplatesPopupMenu.bind(this, buttonNode)
				);
			}
			else
			{
				this.showTemplatesPopupMenu(buttonNode);
			}
		},

		showTemplatesPopupMenu: function(buttonNode)
		{
			var me = this, i, template, menuItem, menuItems = [];
			var onMenuClick = function(e, item)
			{
				this.popupWindow.close();
				e.preventDefault();
				me.onTemplateMenuItemClick(item.template)
			};

			for (i = 0; i < this.templates.length; ++i)
			{
				template = this.templates[i];
				menuItem = {
					text: template['name'],
					template: template,
					title: template['description'],
					onclick: onMenuClick
				};
				menuItems.push(menuItem);
			}

			if (!menuItems.length)
			{
				this.showEmptyTemplatesHint(buttonNode);
			}
			else
			{
				BX.PopupMenu.show(
					'bp-starter-tpl-menu-' + this.id,
					buttonNode,
					menuItems,
					{
						closeByEsc: true,
						zIndex: 200,
						autoHide: true
					}
				);
			}
		},

		showEmptyTemplatesHint: function(node)
		{
			var text = BX.message('BIZPROC_JS_BP_STARTER_NO_TEMPLATES');
			var popupHint = new BX.PopupWindow('bp-starter-tpl-empty' + this.id, node, {
				lightShadow: true,
				autoHide: true,
				darkMode: true,
				offsetLeft: 40,
				angle: { position: 'top', offset: 40 },
				bindOptions: {position: "bottom"},
				zIndex: 1100,
				events : {
					onPopupClose : function() {this.destroy()}
				},
				content : BX.create("div", { attrs : { style : "padding-right: 5px; width: 200px;" }, text: text})
			});
			popupHint.show();
		},

		loadTemplates: function(callback)
		{
			var me = this;
			this.callAction('get_templates', {}, function(data)
			{
				me.templates = data.templates;
				callback(data);
			});
		},

		onTemplateMenuItemClick: function(template)
		{
			if (!template.hasParameters)
			{
				this.startWorkflow(template['id']);
			}
			else
			{
				this.showParametersPopup(template['id'], {title: template['name']});
			}
		},

		startWorkflow: function(templateId)
		{
			var me = this;
			this.callAction('start_workflow', {template_id: templateId}, function(data)
			{
				Manager.fireEvent(me, 'onAfterStartWorkflow', data);
			});
		},

		showParametersPopup: function(templateId, params)
		{
			var me = this;

			if (!BX.type.isPlainObject(params))
			{
				params = {};
			}

			this.loadParametersHtml({template_id: templateId}, function(html)
			{
				var popup, wrapper = BX.create('div', {html: html});
				var form = wrapper.querySelector('[data-role="bizproc-start-form"]');
				me.prepareParametersForm(form, templateId);

				var buttons = form.querySelector('[data-role="bizproc-form-buttons"]');
				if (buttons)
				{
					BX.remove(buttons);
				}

				var formDecorator = new ParametersFormDecorator(form);
				formDecorator.init();

				var startButton = new BX.PopupWindowButton({
					text      :  BX.message('BIZPROC_JS_BP_STARTER_START'),
					className : 'popup-window-button-accept',
					events    : {
						click : function(e)
						{
							BX.fireEvent(form, 'submit');
						}
					}
				});

				popup = new BX.PopupWindow("bp-starter-parameters-popup-" + me.id, null, {
					content: wrapper,
					width: 600,
					closeIcon: true,
					titleBar: params.title || '',
					closeByEsc: true,
					draggable: {restrict: false},
					events: {
						onPopupClose: function (popup)
						{
							popup.destroy();
						}
					},
					buttons: [
						startButton,
						new BX.PopupWindowButtonLink({
							text      :  BX.message('BIZPROC_JS_BP_STARTER_CANCEL'),
							className : 'popup-window-button-link-cancel',
							events    : {
								click : function(e)
								{
									popup.close();
								}
							}
						})
					]
				});

				BX.bind(form, 'submit', function(e)
				{
					e.preventDefault();

					startButton.addClassName('popup-window-button-wait');
					me.submitParametersForm(form, function(response)
					{
						startButton.removeClassName('popup-window-button-wait');
						if (response.success)
						{
							popup.close();
							Manager.fireEvent(me, 'onAfterStartWorkflow', response.data);
						}
					});
				});

				popup.show();
			});
		},

		showAutoStartParametersPopup: function(execType, params)
		{
			var me = this;

			if (!BX.type.isPlainObject(params))
			{
				params = {};
			}

			var showForm = function(html)
			{
				var wrapper;
				if (BX.type.isDomNode(html))
				{
					wrapper = html.firstChild;
				}
				else
				{
					wrapper = BX.create('div', {html: html});
				}

				var form = wrapper.querySelector('[data-role="bizproc-start-form"]');
				me.prepareParametersForm(form, null, 'check_parameters');

				var formDecorator = new ParametersFormDecorator(form);
				formDecorator.init();

				var startButton = new BX.PopupWindowButton({
					text      :  BX.message('BIZPROC_JS_BP_STARTER_SAVE'),
					className : 'popup-window-button-accept',
					events    : {
						click : function(e)
						{
							BX.fireEvent(form, 'submit');
						}
					}
				});

				var popup = new BX.PopupWindow("bp-starter-parameters-popup-" + me.id, null, {
					content: wrapper,
					width: 600,
					closeIcon: true,
					titleBar: params.title || BX.message('BIZPROC_JS_BP_STARTER_AUTOSTART'),
					closeByEsc: true,
					draggable: {restrict: false},
					events: {
						onPopupClose: function (popup)
						{
							popup.destroy();
						}
					},
					buttons: [
						startButton,
						new BX.PopupWindowButtonLink({
							text      :  BX.message('BIZPROC_JS_BP_STARTER_CANCEL'),
							className : 'popup-window-button-link-cancel',
							events    : {
								click : function(e)
								{
									popup.close();
								}
							}
						})
					]
				});
				BX.bind(form, 'submit', function(e)
				{
					e.preventDefault();

					startButton.addClassName('popup-window-button-wait');
					me.submitParametersForm(form, function(response)
					{
						startButton.removeClassName('popup-window-button-wait');
						if (response.success)
						{
							popup.close();
							if (params.callback)
							{
								params.callback(response.data);
							}
						}
					});
				});
				popup.show();
			};

			if (params.contentNode)
			{
				showForm(params.contentNode);
			}
			else
			{
				this.loadParametersHtml({auto_execute_type: execType}, showForm.bind(this));
			}
		},

		loadParametersHtml: function(params, callback)
		{
			params['sessid'] = BX.bitrix_sessid();
			params['site_id'] = BX.message('SITE_ID');
			params['module_id'] = this.moduleId;
			params['entity'] = this.entity;
			params['document_type'] = this.documentType;
			if (this.documentId)
			{
				params['document_id'] = this.documentId;
			}

			BX.ajax({
				method: 'POST',
				dataType: 'html',
				url: '/bitrix/components/bitrix/bizproc.workflow.start/popup.php',
				data: params,
				onsuccess: function (html)
				{
					callback(html);
				},
				onfailure: onAjaxFailure
			});
		},

		/**
		 * @private
		 * @param form
		 * @param templateId
		 * @param action
		 */
		prepareParametersForm: function(form, templateId, action)
		{
			if (templateId)
			{
				form.appendChild(BX.create('input', {
					attrs: {
						type: 'hidden',
						name: 'template_id',
						value: templateId
					}
				}));
			}
			form.appendChild(BX.create('input', {attrs: {
					type: 'hidden',
					name: 'module_id',
					value: this.moduleId
			}}));
			form.appendChild(BX.create('input', {attrs: {
					type: 'hidden',
					name: 'entity',
					value: this.entity
			}}));
			form.appendChild(BX.create('input', {attrs: {
				type: 'hidden',
				name: 'ajax_action',
				value: action ? action : 'start_workflow'
			}}));
			form.action = this.ajaxUrl;
		},

		submitParametersForm: function(form, callback)
		{
			if (form.__requestInProgress)
			{
				return;
			}
			form.__requestInProgress = true;

			var formData = new FormData(form);

			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: formData,
				preparePost: false,
				onsuccess: function (response)
				{
					delete form.__requestInProgress;
					if (!response.success)
					{
						window.alert(response.errors.join('\n'));
					}

					if (callback)
					{
						callback(response)
					}
				},
				onfailure: onAjaxFailure
			});
		},

		callAction: function(actionName, params, callback)
		{
			params['sessid'] = BX.bitrix_sessid();
			params['site'] = BX.message('SITE_ID');
			params['ajax_action'] = actionName;
			params['module_id'] = this.moduleId;
			params['entity'] = this.entity;
			params['document_type'] = this.documentType;
			params['document_id'] = this.documentId;
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: params,
				onsuccess: function(response)
				{
					if (response.success)
					{
						callback(response.data, response);
					}
					else
					{
						window.alert(response.errors.join('\n'));
					}
				},
				onfailure: onAjaxFailure
			});
		}
	};

	BX.Bizproc.Starter = Starter;

	var ParametersFormDecorator = function(formNode, config)
	{
		this.node = formNode;
		this.config = config || {};
	};

	ParametersFormDecorator.prototype =
	{
		init: function()
		{
			this.replaceControls();
		},

		replaceControls: function()
		{
			var i;
			if (Destination.canUse())
			{
				var userControls = this.node.querySelectorAll('.bizproc-modern-type-control-wrapper-user');
				for (i = 0; i < userControls.length; ++i)
				{
					this.replaceUserControl(userControls[i]);
				}
			}

			var fileControls = this.node.querySelectorAll('.bizproc-modern-type-control-wrapper-file');
			for (i = 0; i < fileControls.length; ++i)
			{
				this.replaceFileControl(fileControls[i]);
			}
		},

		replaceUserControl: function(controlWrapperNode)
		{
			var config = {};
			var input = controlWrapperNode.querySelector('.bizproc-modern-type-control');

			config.valueInputName = input.name;
			config.multiple = BX.hasClass(input, 'bizproc-modern-type-control-multiple');
			config.required = BX.hasClass(input, 'bizproc-modern-type-control-required');

			BX.cleanNode(controlWrapperNode);

			new Destination(this, controlWrapperNode, config);
		},

		replaceFileControl: function(controlWrapperNode)
		{
			var input = controlWrapperNode.querySelector('.bizproc-modern-type-control');
			var isMultiple = BX.hasClass(input, 'bizproc-modern-type-control-multiple');
			var inputName = isMultiple ? input.name.replace('[n0]', '[]') : input.name;

			BX.cleanNode(controlWrapperNode);

			controlWrapperNode.appendChild(this.createFileControlNode(inputName));

			if (isMultiple)
			{
				var cloneButton = BX.create('span', {
					attrs: {
						className: 'webform-small-button webform-small-button-accept bizproc-modern-type-control-file-clone-button'
					},
					text: BX.message('BIZPROC_JS_BP_STARTER_CONTROL_CLONE'),
					events: {
						click: this.cloneFileControl.bind(this, controlWrapperNode, inputName)
					}
				});

				controlWrapperNode.appendChild(cloneButton);
			}
		},

		createFileControlNode: function(inputName)
		{
			var input = BX.create('input', {
				props: {
					type: 'file',
					name: inputName
				}
			});

			var buttonWrapper = BX.create('span', {
				attrs: {
					className: 'bizproc-modern-type-control-button'
				},
				children: [BX.create('span', {
					attrs: {
						className: 'webform-small-button'
					},
					text: BX.message('BIZPROC_JS_BP_STARTER_FILE_CHOOSE')
				}), input]
			});

			var label = BX.create('span', {
				attrs: {
					className: 'bizproc-modern-type-control-file-value-name'
				}
			});

			BX.bind(input, 'change', function()
				{
					label.textContent = this.parseFileLabel(input.value);
				}.bind(this)
			);

			return BX.create('div', {
				children: [buttonWrapper, label],
				attrs: {
					className: 'bizproc-modern-type-control-file-replaced'
				}
			})
		},

		cloneFileControl: function(controlWrapperNode, inputName)
		{
			controlWrapperNode.insertBefore(
				this.createFileControlNode(inputName),
				controlWrapperNode.lastChild
			);
		},

		parseFileLabel: function(str)
		{
			var i;
			if (str.lastIndexOf('\\'))
			{
				i = str.lastIndexOf('\\')+1;
			}
			else
			{
				i = str.lastIndexOf('/')+1;
			}
			return str.slice(i);
		},

		getAjaxUrl: function()
		{
			return this.config.ajaxUrl || '/bitrix/components/bitrix/bizproc.workflow.start/ajax.php';
		}
	};

	// -> Destination
	var Destination = function(component, container, config)
	{
		var me = this;

		this.container = container;
		this.itemsNode = BX.create('span');
		this.inputBoxNode = BX.create('span', {
			attrs: {
				className: 'feed-add-destination-input-box'
			}
		});
		this.inputNode = BX.create('input', {
			props: {
				type: 'text'
			},
			attrs: {
				className: 'feed-add-destination-inp'
			}
		});

		this.inputBoxNode.appendChild(this.inputNode);

		this.tagNode = BX.create('a', {
			attrs: {
				className: 'feed-add-destination-link'
			}
		});

		BX.addClass(container, 'bizproc-modern-destination');

		container.appendChild(this.itemsNode);
		container.appendChild(this.inputBoxNode);
		container.appendChild(this.tagNode);

		this.component = component;

		this.data = null;
		this.dialogId = BX.util.getRandomString(7);
		this.createValueNode(config.valueInputName || '');
		this.selected = config.selected ? BX.clone(config.selected) : [];
		this.selectOne = !config.multiple;
		this.required = config.required || false;

		BX.bind(this.tagNode, 'focus', function(e) {
			me.openDialog({bByFocusEvent: true});
			return BX.PreventDefault(e);
		});
		BX.bind(this.container, 'click', function(e) {
			me.openDialog();
			return BX.PreventDefault(e);
		});

		this.addItems(this.selected);

		this.tagNode.innerHTML = (
			this.selected.length <= 0
				? BX.message('BIZPROC_JS_BP_STARTER_DESTINATION_CHOOSE')
				: BX.message('BIZPROC_JS_BP_STARTER_DESTINATION_EDIT')
		);
	};

	Destination.canUse = function()
	{
		return !!BX.SocNetLogDestination;
	};

	Destination.prototype = {
		getData: function(next)
		{
			var me = this;

			if (me.ajaxProgress)
				return;

			me.ajaxProgress = true;
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: me.component.getAjaxUrl(),
				data: {
					ajax_action: 'get_destination_data',
					sessid: BX.bitrix_sessid(),
					site: BX.message('SITE_ID')
				},
				onsuccess: function (response)
				{
					me.data = response.data || {};
					me.ajaxProgress = false;
					me.initDialog(next);
				}
			});
		},
		initDialog: function(next)
		{
			var i, me = this, data = this.data;

			if (!data)
			{
				me.getData(next);
				return;
			}

			var itemsSelected = {};
			for (i = 0; i < me.selected.length; ++i)
			{
				itemsSelected[me.selected[i].id] = me.selected[i].entityType
			}

			var items = {
				users : data.USERS || {},
				department : data.DEPARTMENT || {},
				departmentRelation : data.DEPARTMENT_RELATION || {}
			};
			var itemsLast =  {
				users: data.LAST.USERS || {}
			};

			if (!items["departmentRelation"])
			{
				items["departmentRelation"] = BX.SocNetLogDestination.buildDepartmentRelation(items["department"]);
			}

			if (!me.inited)
			{
				me.inited = true;
				var destinationInput = me.inputNode;
				destinationInput.id = me.dialogId + 'input';

				var destinationInputBox = me.inputBoxNode;
				destinationInputBox.id = me.dialogId + 'input-box';

				var tagNode = this.tagNode;
				tagNode.id = this.dialogId + 'tag';

				var itemsNode = me.itemsNode;

				BX.SocNetLogDestination.init({
					name : me.dialogId,
					searchInput : destinationInput,
					extranetUser :  false,
					bindMainPopup : {node: me.container, offsetTop: '5px', offsetLeft: '15px'},
					bindSearchPopup : {node: me.container, offsetTop : '5px', offsetLeft: '15px'},
					departmentSelectDisable: true,
					sendAjaxSearch: true,
					callback : {
						select : function(item, type, search, bUndeleted)
						{
							me.addItem(item, type);
							if (me.selectOne)
								BX.SocNetLogDestination.closeDialog();
						},
						unSelect : function (item)
						{
							if (me.selectOne)
								return;
							me.unsetValue(item.entityId);
							BX.SocNetLogDestination.BXfpUnSelectCallback.call({
								formName: me.dialogId,
								inputContainerName: itemsNode,
								inputName: destinationInput.id,
								tagInputName: tagNode.id,
								tagLink1: BX.message('BIZPROC_JS_BP_STARTER_DESTINATION_CHOOSE'),
								tagLink2: BX.message('BIZPROC_JS_BP_STARTER_DESTINATION_EDIT')
							}, item)
						},
						openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
							inputBoxName: destinationInputBox.id,
							inputName: destinationInput.id,
							tagInputName: tagNode.id
						}),
						closeDialog : BX.delegate(BX.SocNetLogDestination.BXfpCloseDialogCallback, {
							inputBoxName: destinationInputBox.id,
							inputName: destinationInput.id,
							tagInputName: tagNode.id
						}),
						openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
							inputBoxName: destinationInputBox.id,
							inputName: destinationInput.id,
							tagInputName: tagNode.id
						}),
						closeSearch : BX.delegate(BX.SocNetLogDestination.BXfpCloseSearchCallback, {
							inputBoxName: destinationInputBox.id,
							inputName: destinationInput.id,
							tagInputName: tagNode.id
						})
					},
					items : items,
					itemsLast : itemsLast,
					itemsSelected : itemsSelected,
					useClientDatabase: false,
					destSort: data.DEST_SORT || {},
					allowAddUser: false
				});

				BX.bind(destinationInput, 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
					formName: me.dialogId,
					inputName: destinationInput.id,
					tagInputName: tagNode.id
				}));
				BX.bind(destinationInput, 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
					formName: me.dialogId,
					inputName: destinationInput.id
				}));

				BX.SocNetLogDestination.BXfpSetLinkName({
					formName: me.dialogId,
					tagInputName: tagNode.id,
					tagLink1: BX.message('BIZPROC_JS_BP_STARTER_DESTINATION_CHOOSE'),
					tagLink2: BX.message('BIZPROC_JS_BP_STARTER_DESTINATION_EDIT')
				});
			}
			next();
		},
		addItem: function(item, type)
		{
			var me = this;
			var destinationInput = this.inputNode;
			var tagNode = this.tagNode;
			var items = this.itemsNode;

			if (!BX.findChild(items, { attr : { 'data-id' : item.id }}, false, false))
			{
				if (me.selectOne && me.inited)
				{
					var toRemove = [];
					for (var i = 0; i < items.childNodes.length; ++i)
					{
						toRemove.push({
							itemId: items.childNodes[i].getAttribute('data-id'),
							itemType: items.childNodes[i].getAttribute('data-type')
						})
					}

					me.initDialog(function() {
						for (var i = 0; i < toRemove.length; ++i)
						{
							BX.SocNetLogDestination.deleteItem(toRemove[i].itemId, toRemove[i].itemType, me.dialogId);
						}
					});

					BX.cleanNode(items);
					me.cleanValue();
				}

				var container = this.createItemNode({
					text: item.name,
					deleteEvents: {
						click: function(e) {
							if (me.selectOne && me.required)
							{
								me.openDialog();
							}
							else
							{
								me.initDialog(function() {
									BX.SocNetLogDestination.deleteItem(item.id, type, me.dialogId);
									BX.remove(container);
									me.unsetValue(item.entityId);
								});
							}
							BX.PreventDefault(e);
						}
					}
				});

				this.setValue(item.entityId);

				container.setAttribute('data-id', item.id);
				container.setAttribute('data-type', type);

				items.appendChild(container);

				if (!item.entityType)
					item.entityType = type;
			}

			destinationInput.value = '';
			tagNode.innerHTML = BX.message('BIZPROC_JS_BP_STARTER_DESTINATION_EDIT');
		},
		addItems: function(items)
		{
			for(var i = 0; i < items.length; ++i)
			{
				this.addItem(items[i], items[i].entityType)
			}
		},
		openDialog: function(params)
		{
			var me = this;
			this.initDialog(function()
			{
				BX.SocNetLogDestination.openDialog(me.dialogId, params);
			})
		},
		destroy: function()
		{
			if (this.inited)
			{
				if (BX.SocNetLogDestination.isOpenDialog())
				{
					BX.SocNetLogDestination.closeDialog();
				}
				BX.SocNetLogDestination.closeSearch();
			}
		},
		createItemNode: function(options)
		{
			return BX.create('span', {
				attrs: {
					className: 'bizproc-modern-destination-item'
				},
				children: [
					BX.create('span', {
						attrs: {
							className: 'bizproc-modern-destination-name'
						},
						html: options.text || ''
					}),
					BX.create('span', {
						attrs: {
							className: 'bizproc-modern-destination-delete'
						},
						events: options.deleteEvents
					})
				]
			});
		},
		createValueNode: function(valueInputName)
		{
			this.valueNode = BX.create('input', {
				props: {
					type: 'hidden',
					name: valueInputName
				}
			});

			this.container.appendChild(this.valueNode);
		},
		setValue: function(value)
		{
			if (/^\d+$/.test(value))
				value = '['+ value +']';

			if (this.selectOne)
				this.valueNode.value = value;
			else
			{
				var i, newVal = [], pairs = this.valueNode.value.split(';');
				for (i = 0; i < pairs.length; ++i)
				{
					if (!pairs[i] || value == pairs[i])
						continue;
					newVal.push(pairs[i]);
				}
				newVal.push(value);
				this.valueNode.value = newVal.join(';');
			}

		},
		unsetValue: function(value)
		{
			if (/^\d+$/.test(value))
				value = '['+ value +']';

			if (this.selectOne)
				this.valueNode.value = '';
			else
			{
				var i, newVal = [], pairs = this.valueNode.value.split(';');
				for (i = 0; i < pairs.length; ++i)
				{
					if (!pairs[i] || value == pairs[i])
						continue;
					newVal.push(pairs[i]);
				}
				this.valueNode.value = newVal.join(';');
			}
		},
		cleanValue: function()
		{
			this.valueNode.value = '';
		}
	};
	// <- Destination
})();