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
					text: BX.util.htmlspecialchars(template['name']),
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
				var startButton;

				if (form)
				{
					me.prepareParametersForm(form, templateId);

					var buttons = form.querySelector('[data-role="bizproc-form-buttons"]');
					if (buttons)
					{
						BX.remove(buttons);
					}

					startButton = new BX.PopupWindowButton({
						text      :  BX.message('BIZPROC_JS_BP_STARTER_START'),
						className : 'popup-window-button-accept',
						events    : {
							click : function(e)
							{
								BX.fireEvent(form, 'submit');
							}
						}
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
				}

				popup = new BX.PopupWindow("bp-starter-parameters-popup-" + me.id, null, {
					content: wrapper,
					width: 600,
					closeIcon: true,
					titleBar: params.title ? BX.Text.decode(params.title) : '',
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
})();