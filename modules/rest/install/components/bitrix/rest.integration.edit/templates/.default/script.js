;(function ()
{
	var paramsWebHook = [];
	BX.namespace('BX.rest.integration');
	if (BX.rest.integration.InitHint)
	{
		return;
	}

	// Hints processing
	function InitHint()
	{
		this.items = null;
	}

	InitHint.prototype =
		{
			init: function ()
			{
				this.getItems();
			},

			getItems: function ()
			{
				if (this.items)
				{
					return this.items;
				}

				var items = BX('rest-integration-form').querySelectorAll('[data-role="integration-hint"]');
				this.items = [];

				for (var i = 0; i < items.length; i++)
				{
					var item = {};

					item.id = i;
					item.container = items[i];
					item.text = items[i].getAttribute('data-integration-hint-text');

					this.items.push(item);
				}

				this.loadData();

				return this.items;
			},

			loadData: function ()
			{
				for (var i = 0; i < this.items.length; i++)
				{
					this.addItem(this.items[i]);
				}
			},

			addItem: function (options)
			{
				var item = new InitHintItem(options);
				item.hint = this;
				this.items[options.id] = item;
			}
		};

	BX.rest.integration.InitHint = InitHint;

	function InitHintItem(options)
	{
		this.id = options.id;
		this.container = options.container;
		this.text = options.text;
		this.popup = null;

		this.init();
	}

	InitHintItem.prototype =
		{
			init: function ()
			{
				this.addEvents();
			},

			addEvents: function ()
			{
				BX.bind(
					this.container,
					'mouseenter',
					function ()
					{
						this.getPopup().show();
					}.bind(this)
				);

				BX.bind(
					this.container,
					'mouseleave',
					function ()
					{
						this.getPopup().close();
					}.bind(this)
				);
			},

			getPopup: function ()
			{
				if (this.popup)
				{
					return this.popup;
				}

				this.popup = new BX.PopupWindow('integration-popup-hit-' + this.id, this.container,
					{
						className: 'integration-popup-hit',
						darkMode: true,
						maxWidth: 330,
						angle: true,
						content: this.text,
						offsetLeft: 11,
						offsetTop: -12,
						bindOptions:
							{
								position: 'top'
							},
						animationOptions:
							{
								show:
									{
										type: 'opacity-transform'
									},
								close:
									{
										type: 'opacity'
									}
							}
					});

				return this.popup;
			}
		};
	BX.rest.integration.InitHintItem = InitHintItem;

	function ParamFieldsControl(options)
	{
		this.target = options.target;
		this.items = options.items;
		this.section = options.section;
		this.code = options.code;
		this.container = null;

		this.init();
	}

	ParamFieldsControl.prototype =
		{
			init: function ()
			{
				this.loadData();
			},

			getItems: function ()
			{
				return this.items;
			},

			removeItem: function (item)
			{
				this.items.splice(this.items.indexOf(item), 1);
			},

			appendItem: function (options)
			{
				var item = new ParamFieldsControlItem(options);
				item.paramControl = this;

				this.items[this.items.indexOf(this.getItemById(options.id))] = item;
			},

			getItemById: function (value)
			{
				for (var i = 0; i < this.items.length; i++)
				{
					if (this.items[i].id === value)
					{
						return this.items[i]
					}
				}
			},

			loadData: function ()
			{
				for (var i = 0; i < this.items.length; i++)
				{
					if (!this.items[i].id)
					{
						this.items[i].id = 'unknown-integration-param-id-' + i;
					}

					this.appendItem(this.items[i])
				}
			},

			addItem: function (item)
			{
				this.items.push(item);
				this.appendItem(item);

				this.container.appendChild(this.getItemById(item.id).render(true));
			},

			getContainer: function ()
			{
				if (this.container)
				{
					return this.container;
				}

				this.container = BX.create(
					'div',
					{
						props:
						{
							className: 'integration-table'
						}
					}
				);

				return this.container;
			},

			render: function ()
			{
				for (var i = 0; i < this.items.length; i++)
				{
					this.getContainer().appendChild(this.items[i].render());
				}

				BX.cleanNode(this.target);

				this.target.appendChild(this.getContainer());
			}

		};
	BX.rest.integration.ParamFieldsControl = ParamFieldsControl;

	function ParamFieldsControlItem(options)
	{
		this.id = options.id;
		this.title = options.title;
		this.value = options.value;
		this.layout = {
			container: null,
			title: null,
			titleValue: null,
			titleEdit: null,
			inputWrapper: null,
			input: null,
			remove: null
		};
		this.editMode = false;

		this.init();
	}

	ParamFieldsControlItem.prototype =
		{
			init: function ()
			{
				this.addEvents();
			},
			addEvents: function ()
			{
				BX.bind(
					window,
					'click',
					function (ev)
					{
						if (ev.target === this.layout.titleEdit)
						{
							this.setEditMode();
							return;
						}
						var parent = ev.target.closest(".integration-table-title");
						if (!parent || ev.target !== this.layout.title)
						{
							if (this.editMode)
							{
								if (parent !== this.layout.title)
								{
									this.resetEditMode();
									if (this.layout.titleInput.value.length === 0)
									{
										this.returnTitle();
									}
									else
									{
										this.updateTitle();
									}
								}
							}
							else
							{
								this.resetEditMode();
							}
						}
					}.bind(this)
				)
			},

			getTitle: function ()
			{
				if (this.layout.title)
				{
					return this.layout.title;
				}
				this.layout.title = BX.create(
					'div',
					{
						props:
							{
								className: 'integration-table-title'
							}
					}
				);

				this.layout.titleValue = BX.create(
					'div',
					{
						props:
							{
								className: 'integration-table-title-value'
							},
						text: this.title
					}
				);

				this.layout.titleEdit = BX.create(
					'div',
					{
						props:
							{
								className: 'integration-table-title-edit'
							},
						events:
							{
								click: function ()
								{
									this.setEditMode();
									this.layout.titleInput.focus();
								}.bind(this)
							}
					}
				);

				this.layout.titleInput = BX.create(
					'input',
					{
						props:
							{
								type: 'text',
								className: 'integration-table-title-input',
								value: this.title,
								name: 'QUERY['+this.paramControl.code+'][ITEMS][title][]'
							},
						events:
							{
								keydown: function (ev)
								{
									if (ev.code === "Escape")
									{
										this.resetEditMode();
										this.returnTitle();
									}

									if (ev.code === "Enter")
									{
										if (this.layout.titleInput.value.length === 0)
										{
											this.resetEditMode();
											this.returnTitle();

											return;
										}

										this.resetEditMode();
										this.updateTitle();
									}
								}.bind(this)
							}
					}
				);

				this.layout.title.appendChild(this.layout.titleValue);
				this.layout.title.appendChild(this.layout.titleEdit);
				this.layout.title.appendChild(this.layout.titleInput);

				return this.layout.title;
			},

			getInput: function ()
			{
				if (this.layout.inputWrapper)
				{
					return this.layout.inputWrapper;
				}

				this.layout.inputWrapper = BX.create(
					'div',
					{
						props:
							{
								className: 'ui-ctl ui-ctl-textbox ui-ctl-w100'
							}
					}
				);

				if (typeof this.value === 'undefined')
				{
					this.value = '';
				}
				this.layout.input = BX.create(
					'input',
					{
						props:
							{
								type: 'text',
								className: 'ui-ctl-element',
								value: this.value,
								name: 'QUERY['+this.paramControl.code+'][ITEMS][value][]'
							},
						events:
							{
								blur: function ()
								{
									BX.rest.integration.makeCurlString();
								}.bind(this)
							}
					}
				);

				this.layout.inputWrapper.appendChild(this.layout.input);

				return this.layout.inputWrapper;
			},

			getRemove: function ()
			{
				if (this.layout.remove)
				{
					return this.layout.remove;
				}

				this.layout.remove = BX.create(
					'div',
					{
						props:
							{
								className: 'integration-table-td-remove'
							},
						events:
							{
								click: function ()
								{
									this.remove();
									this.paramControl.removeItem(this);
								}.bind(this)
							}
					}
				);

				return this.layout.remove;
			},

			remove: function ()
			{
				var firstItem;

				if (this.paramControl.getItems().indexOf(this) === 0)
				{
					firstItem = true;
				}

				this.layout.container.style.height = this.layout.container.offsetHeight + 'px';
				BX.addClass(this.layout.container, 'integration-table-row-hide');

				setTimeout(
					function ()
					{
						this.layout.container.style.height = '0px';
						this.layout.container.style.paddingTop = '0px';

						if (firstItem)
						{
							this.layout.container.style.marginBottom = '-30px';
						}
					}.bind(this),
					250
				);

				setTimeout(
					function ()
					{
						this.layout.container.parentNode.removeChild(this.layout.container);
						BX.rest.integration.makeCurlString();
					}.bind(this),
					500
				);
			},

			setEditMode: function ()
			{
				this.editMode = true;
				BX.addClass(this.layout.title, 'integration-table-title-edit-mode');
			},

			resetEditMode: function ()
			{
				this.editMode = false;
				BX.removeClass(this.layout.title, 'integration-table-title-edit-mode');
			},

			updateTitle: function ()
			{
				this.title = this.layout.titleInput.value;
				this.layout.titleValue.innerText = this.layout.titleInput.value;
				BX.rest.integration.makeCurlString();
			},

			returnTitle: function ()
			{
				this.layout.titleInput.value = this.layout.titleValue.innerText;
			},

			render: function (newItem)
			{
				if (this.layout.container)
				{
					return this.layout.container;
				}

				this.layout.container = BX.create(
					'div',
					{
						props:
							{
								className: newItem ? 'integration-table-row integration-table-row-show' : 'integration-table-row'
							},
						children:
							[
								BX.create(
									'div',
									{
										props:
											{
												className: 'integration-table-cell'
											},
										children:
											[
												this.getTitle()
											]
									}
								),
								BX.create(
									'div',
									{
										props:
											{
												className: 'integration-table-cell'
											},
										children: [
											this.getInput()
										]
									}
								),
								BX.create(
									'div',
									{
										props:
											{
												className: 'integration-table-cell'
											},
										children:
											[
												this.getRemove()
											]
									}
								)
							]
					});

				if (newItem)
				{
					setTimeout(
						function ()
						{
							BX.removeClass(this.layout.container, 'integration-table-row-show')
						}.bind(this),
						450
					)
				}

				return this.layout.container;
			}
		};
	BX.rest.integration.ParamFieldsControlItem = ParamFieldsControlItem;

	function InitTabs()
	{
		this.items = [];
	}

	InitTabs.prototype =
		{
			init: function ()
			{
				var items = BX('rest-integration-form').querySelectorAll('[data-role="integration-tab"]');

				for (var i = 0; i < items.length; i++)
				{
					var item = {};
					item.id = i;
					item.container = items[i];

					item = new InitTabsItem(item);
					this.items.push(item);
					if ((item.container.className).replace(/[\n\t]/g, " ").indexOf("integration-tab-auto-open") > -1)
					{
						item.openTab();
					}
				}
			}
		};
	BX.rest.integration.InitTabs = InitTabs;

	function InitTabsItem(options)
	{
		this.id = options.id;
		this.container = options.container;
		this.layout = {
			title: null,
			container: null,
			wrapper: null,
			checkbox: null
		};
		this.open = false;

		this.init();
	}

	InitTabsItem.prototype =
		{
			init: function ()
			{
				this.initTitle();
				this.initWrapper();
				this.initContainer();
				this.initCheckbox();
			},
			setAutoHeight: function ()
			{
				this.layout.wrapper.style.height = null;
				this.layout.wrapper.classList.add('integration-tab-wrapper-height-auto');
				BX.unbind(this.layout.wrapper, 'transitionend', this.setAutoHeight.bind(this));
			},
			openTab: function (click)
			{
				if (click)
				{
					BX.bind(this.layout.wrapper, 'transitionend', this.setAutoHeight.bind(this));
				}
				else
				{
					BX.bind(
						this.layout.wrapper,
						'transitionend',
						function ()
						{
							this.layout.wrapper.style.height = null;
							this.layout.wrapper.classList.add('integration-tab-wrapper-height-auto');
							BX.unbindAll(this.layout.wrapper);
						}.bind(this)
					);
				}
				this.open = true;
				BX.addClass(this.container, 'integration-tab-open');

				if (this.layout.checkbox !== null)
				{
					this.layout.checkbox.checked = true;
				}

				var reqInputList = this.container.querySelectorAll('.integration-required');
				if (reqInputList.length > 0)
				{
					reqInputList.forEach(
						function (item)
						{
							item.required = true;
						}
					);
				}
				this.layout.wrapper.style.height = this.layout.container.offsetHeight + 'px';
			},

			closeTab: function ()
			{
				this.layout.wrapper.style.height = this.layout.wrapper.offsetHeight + 'px';
				this.layout.wrapper.classList.remove('integration-tab-wrapper-height-auto');
				var urlDom = this.container.querySelectorAll('input[type=url]');
				urlDom.forEach(
					function (item)
					{
						item.value = '';
					}
				);

				this.open = false;
				BX.removeClass(this.container, 'integration-tab-open');
				BX.addClass(this.container, 'integration-tab-close');
				var reqInputList = this.container.querySelectorAll('.integration-required');
				if (reqInputList.length > 0)
				{
					reqInputList.forEach(
						function (item)
						{
							item.required = false;
						}
					);
				}
				setTimeout(
					function ()
					{
						this.layout.wrapper.style.height = 0;
						BX.removeClass(this.container, 'integration-tab-close');
						this.layout.checkbox.checked = false;
					}.bind(this),
					200
				);
			},

			initWrapper: function ()
			{
				this.layout.wrapper = this.container.querySelector('.integration-tab-wrapper');
			},

			initCheckbox: function ()
			{
				this.layout.checkbox = this.container.querySelector('.integration-tab-checkbox');
			},

			initContainer: function ()
			{
				this.layout.container = this.container.querySelector('.integration-tab-container');

			},

			initTitle: function ()
			{
				this.layout.title = this.container.querySelector('.integration-tab-title');

				BX.bind(
					this.layout.title,
					'click',
					function ()
					{
						if (this.open)
						{
							this.closeTab();
						}
						else
						{
							this.openTab(true);
							BX.bind(
								this.layout.wrapper,
								'animationend',
								function ()
								{
									this.scrollToItem(this.container);
									BX.unbindAll(this.layout.wrapper);
								}.bind(this)
							);
						}
					}.bind(this)
				);

			},

			scrollToItem: function (node)
			{
				if (window.safari)
				{
					var body = document.body,
						html = document.documentElement,
						scrollY = window.pageYOffset,
						height;

					var interval = setInterval(
						function ()
						{
							height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
							scrollY += 10;

							window.scrollTo(0, scrollY);

							if (scrollY >= html.scrollTop + html.clientHeight)
							{
								clearInterval(interval);
							}
						},
						10
					);

					return;
				}

				window.scrollTo({
					top: BX.pos(node).top,
					behavior: 'smooth'
				});
			}
		};

	BX.rest.integration.InitTabsItem = InitTabsItem;



	BX.rest.integration.makeCurlString = function ()
	{
		BX.rest.integration.setMethodLink();
		var webhookUrl = BX('rest-integration-form').querySelector('#webhookURL');

		if (typeof paramsWebHook === 'object' && webhookUrl !== null)
		{
			var selectMethod, inputUri, paramsString;
			for (var key in paramsWebHook)
			{
				paramsString = '';
				if (
					paramsWebHook.hasOwnProperty(key)
					&& typeof paramsWebHook[key]['items'] === 'object'
					&& paramsWebHook[key]['items'].length > 0
				)
				{
					for (var i = 0; i < paramsWebHook[key]['items'].length; i++)
					{
						if (paramsString !== '')
						{
							paramsString += '&';
						}
						paramsString += paramsWebHook[key]['items'][i]['title'] + '=';
						if (typeof paramsWebHook[key]['items'][i]['layout']['input']['value'] === 'string')
						{
							paramsString += paramsWebHook[key]['items'][i]['layout']['input']['value'];
						}
					}
				}
				selectMethod = BX('rest-integration-form').querySelector('.integration-webhook-method-api-select input[name="QUERY['+key+'][METHOD]"]');
				inputUri = BX('rest-integration-form').querySelector('.integration-curl-uri[data-key="'+key+'"]');
				if (selectMethod !== null && inputUri !== null)
				{
					if (paramsString !== '')
					{
						paramsString = '?' + paramsString;
					}
					inputUri.value = webhookUrl.value + selectMethod.value + '.json' + paramsString;
				}
			}
		}

	};

	BX.rest.integration.setMethodLink = function ()
	{
		BX('rest-integration-form').querySelectorAll('.integration-method-url').forEach(
			function (item)
			{
				var input = BX('rest-integration-form').querySelector('input[name="QUERY['+item.dataset.key+'][METHOD]"]');
				if (input !== null)
				{
					BX.adjust(
						item,
						{
							props:
								{
									href: restIntegrationEditComponent.uriToMethodInfo+input.value
								}
						}
					);
				}
			}
		);
	};
	BX.rest.integration.inputApiOnlyChange = function()
	{
		var value = BX('rest-integration-form').querySelector('#rest-integration-form input[name="APPLICATION_ONLY_API"]');
		if (value !== null)
		{
			var inputLangList = BX('rest-integration-form').querySelectorAll('#applicationLang .integration-required-lang');
			if (!value.checked)
			{
				BX.show(BX('applicationLang'));
				inputLangList.forEach(
					function (input)
					{
						input.required = true;
					}
				);
			}
			else
			{
				BX.hide(BX('applicationLang'));
				inputLangList.forEach(
					function (input)
					{
						input.required = false;
					}
				);

				var appMode = BX('rest-integration-form').querySelector('#rest-integration-form #applicationType input[value="SERVER"]');
				if (appMode !== null)
				{
					if (!appMode.checked)
					{
						appMode.checked = true;
						BX.fireEvent(appMode, 'change');
					}
				}
			}
		}
	};
	BX.rest.integration.initParams = function ()
	{
		this.savedDo = false;
		var tabs = new BX.rest.integration.InitTabs();
		var hints = new BX.rest.integration.InitHint({
			items: BX('rest-integration-form').querySelectorAll('[data-role="integration-hint"]')
		});
		BX.rest.integration.inputApiOnlyChange();
		BX.bind(
			BX('rest-integration-form'),
			'submit',
			BX.delegate(BX.rest.integration.actionSaveForm, this)
		);

		var btnDownloadList = BX('rest-integration-form').querySelectorAll('#rest-integration-form .integration-post-open');
		if (btnDownloadList.length > 0)
		{
			for(var i = 0; i < btnDownloadList.length; i++)
			{
				BX.bind(
					btnDownloadList[i],
					'click',
					BX.delegate(BX.rest.integration.openPostUri, this)
				);
			}
		}

		btnDownloadList = BX('rest-integration-form').querySelectorAll('#rest-integration-form .integration-example-url');
		if (btnDownloadList.length > 0)
		{
			for(var j = 0; j < btnDownloadList.length; j++)
			{
				BX.bind(
					btnDownloadList[j],
					'click',
					BX.delegate(BX.rest.integration.openUri, this)
				);
			}
		}

		BX.bind(
			BX('rest-integration-form').querySelector('#rest-integration-form input[name="APPLICATION_ONLY_API"]'),
			'change',
			BX.delegate(BX.rest.integration.inputApiOnlyChange, this)
		);
		BX('rest-integration-form').querySelectorAll('#rest-integration-form .integration-curl-uri-button').forEach(
		function (value)
			{
				BX.bind(
					value,
					'click',
					BX.rest.integration.actionCurlQuery
				);
			}
		);

		BX('rest-integration-form').querySelectorAll('#rest-integration-form #applicationType input').forEach(
			function (value)
			{
				BX.bind(
					value,
					'change',
					BX.rest.integration.changeAppType
				);
			}
		);

		BX.bind(
			BX('rest-integration-form').querySelector('#rest-integration-form #integrationSaveBot'),
			'click',
			BX.delegate(BX.rest.integration.actionSaveBtnClick, this)
		);
		BX.bind(
			BX('rest-integration-form').querySelector('#rest-integration-form #integrationGenerateWebhook'),
			'click',
			BX.delegate(BX.rest.integration.actionSaveRegenBtnClick, this)
		);

		BX.bind(
			BX('rest-integration-form').querySelector('#rest-integration-form input[name="APP_ZIP"]'),
			'change',
			BX.delegate(
				BX.rest.integration.loadFileApplication,
				this
			)
		);

		BX.addCustomEvent(
			'SidePanel.Slider:onClose',
			this.onCloseSlider.bind(this)
		);

		if (typeof restIntegrationEditComponent === 'object' && typeof restIntegrationEditComponent.queryProps === 'object')
		{
			var code;
			for (var key in restIntegrationEditComponent.queryProps)
			{
				if (restIntegrationEditComponent.queryProps.hasOwnProperty(key))
				{
					code = restIntegrationEditComponent.queryProps[key].CODE;
					paramsWebHook[code] = new ParamFieldsControl({
						target: document.getElementById('integration-webhook-param-' + code),
						items: restIntegrationEditComponent.queryProps[key].ITEMS,
						section: restIntegrationEditComponent.queryProps[key].TITLE,
						code: restIntegrationEditComponent.queryProps[key].CODE
					});
					paramsWebHook[code].render();
					BX.bind(
						BX('integration-webhook-add-param-' + code),
						'click',
						function ()
						{
							var val = this.dataset.key;
							paramsWebHook[val].addItem(
								{
									id: 'newparam' + Math.random(),
									title: 'NEW_PARAM'
								}
							);
							BX.rest.integration.makeCurlString();
						}
					);
				}
			}
			BX.rest.integration.makeCurlString();
		}
		hints.init();
		tabs.init();
	};

	BX.rest.integration.onCloseSlider = function(event)
	{
		var slider = event.getSlider();

		if (
			!!restIntegrationEditComponent.needConfirmCloseSliderWithDelete
			&& (typeof slider.data.NEW_OPEN === 'undefined' || slider.data.NEW_OPEN !== 'N')
		)
		{
			this.popupWindow = BX.PopupWindowManager.create(
				'rest-integration-edit-on-slider-close',
				null,
				{
					content: BX.message('REST_INTEGRATION_EDIT_CLOSE_SLIDER_CLOSE'),
					titleBar: BX.message('REST_INTEGRATION_EDIT_CLOSE_SLIDER_CLOSE_TITLE'),
					width: 400,
					height: 200,
					padding: 10,
					closeByEsc: true,
					contentColor: 'white',
					angle: false,
					buttons: [
						new BX.PopupWindowButton({
							text: BX.message('REST_INTEGRATION_EDIT_CLOSE_SLIDER_YES'),
							className: "popup-window-button-accept",
							events: {
								click: function() {
									BX.ajax.runComponentAction(
										'bitrix:rest.integration.edit',
										'delete',
										{
											mode: 'class',
											signedParameters: restIntegrationEditComponent.signetParameters,
											data: {},
											analyticsLabel:
												{
													type: 'integrationDownloadExample',
													integrationCode: restIntegrationEditComponent.integrationCode
												}
										}
									).then(
										function (response)
										{
											slider.data = {close: true}
											slider.close();
										}
									);
								}
							}
						}),
						new BX.PopupWindowButton({
							text: BX.message('REST_INTEGRATION_EDIT_CLOSE_SLIDER_CANCEL'),
							className: "popup-window-button-cancel",
							events: {
								click: function() {
									this.popupWindow.close();
								}
							}
						})
					]
				}
			).show();

			if(typeof slider.data.close === 'undefined' || slider.data.close === false)
			{
				event.denyAction();
			}
		}
	};

	BX.rest.integration.loadFileApplication = function(event)
	{
		if (event.target.files.length > 0 && event.target.files[0]['size'] > 0)
		{
			var item = BX('rest-integration-form').querySelector('#applicationZip .ui-ctl-label-text');
			BX.cleanNode(item);
			item.textContent = event.target.files[0]['name'];
		}
		else
		{
			BX.UI.Notification.Center.notify(
				{
					content: BX.message('REST_INTEGRATION_EDIT_TAB_APPLICATION_ZIP_INPUT_LABEL')
				}
			);
		}
	};

	BX.rest.integration.changeAppType = function(event)
	{
		var reqInputList, noReqInputList, clearInputList;
		var type = event.target.value;
		var modeServer = BX('rest-integration-form').querySelector('#applicationServer');
		var modeZip = BX('rest-integration-form').querySelector('#applicationZip');
		var appOnlyApi = BX('rest-integration-form').querySelector('#rest-integration-form input[name="APPLICATION_ONLY_API"]');
		if (type === 'ZIP')
		{
			BX.hide(modeServer);
			BX.show(modeZip);

			reqInputList = modeZip.querySelectorAll('.integration-required');
			noReqInputList = modeServer.querySelectorAll('.integration-required');
			clearInputList = modeServer.querySelectorAll('input');
			appOnlyApi.checked = false;
			BX.fireEvent(appOnlyApi, 'change');
		}
		else
		{
			BX.hide(modeZip);
			BX.show(modeServer);
			reqInputList = modeServer.querySelectorAll('.integration-required');
			noReqInputList = modeZip.querySelectorAll('.integration-required');
			clearInputList = modeZip.querySelectorAll('input');
			var inputFileLabel = BX('rest-integration-form').querySelector('#applicationZip .ui-ctl-label-text');
			if (inputFileLabel)
			{
				BX.cleanNode(inputFileLabel);
				inputFileLabel.textContent = BX.message('REST_INTEGRATION_EDIT_TAB_APPLICATION_ZIP_INPUT_LABEL');
			}
		}

		if (reqInputList.length > 0)
		{
			reqInputList.forEach(
				function (item)
				{
					item.required = true;
				}
			);
		}

		if (noReqInputList.length > 0)
		{
			noReqInputList.forEach(
				function (item)
				{
					item.required = false;
				}
			);
		}

		if (clearInputList.length > 0)
		{
			clearInputList.forEach(
				function (item)
				{
					item.value = '';
					//hack clear input file
					if (item.type === 'file')
					{
						item.type = 'text';
						item.type = 'file';
					}
				}
			);
		}
	};

	BX.rest.integration.actionCurlQuery = function ()
	{
		if (restIntegrationEditComponent.hasOwnProperty('pathIframe') && restIntegrationEditComponent.pathIframe !== '')
		{
			var webhookUrl = BX('rest-integration-form').querySelector('input[data-key="'+this.dataset.key+'"]');
			if (webhookUrl !== null)
			{
				var analytic = 'type=integrationRun';
				if (restIntegrationEditComponent.hasOwnProperty('integrationCode') && restIntegrationEditComponent.integrationCode !== '')
				{
					analytic += '&integrationCode=' + restIntegrationEditComponent.integrationCode
				}

				BX.SidePanel.Instance.open(
					restIntegrationEditComponent.pathIframe + '?' + analytic,
					{
						'requestMethod': 'post',
						'requestParams': {
							'query': webhookUrl.value,
							'sessid': BX.bitrix_sessid(),
						},
						'allowChangeHistory': false,
						'cacheable': false
					}
				);
			}
		}
	};

	BX.rest.integration.actionSaveBtnClick = function ()
	{
		BX.rest.integration.savedDo = false;
		BX('integration-save-mode').value = 'SAVE';
		BX.submit(
			BX('rest-integration-form'),
			'save',
			'Y',
			function ()
			{
				if (!BX.rest.integration.savedDo)
				{
					BX.removeClass(BX('ui-button-panel-save'), 'ui-btn-wait');
				}
			}
		);
	};

	BX.rest.integration.actionSaveRegenBtnClick = function ()
	{
		BX.addClass(BX('integrationGenerateWebhook'), 'ui-btn-wait');
		BX.UI.Dialogs.MessageBox.show(
		{
			message: BX.create(
				'div',
				{
					children:
						[
							BX.create(
								"p",
								{
									text: BX.message('REST_INTEGRATION_EDIT_CONFIRM_POPUP_DESCRIPTION'),
								}
							),
						]
				}
			),
			title: BX.message('REST_INTEGRATION_EDIT_CONFIRM_POPUP_TITLE'),
			popupOptions:
			{
				closeIcon: true,
			},
			modal: true,
			bindElement: BX(this.id),
			buttons: [
				new BX.UI.Button(
					{
						color: BX.UI.Button.Color.PRIMARY,
						state: BX.UI.Button.State.ACTIVE,
						text: BX.message('REST_INTEGRATION_EDIT_CONFIRM_POPUP_BTN_CONTINUE'),
						onclick: BX.delegate(
							function (btn)
							{
								BX('integration-save-mode').value = 'GEN_SAVE';
								BX.submit(
									BX('rest-integration-form'),
									'SAVE',
									'Y',
									function ()
									{
										BX.removeClass(BX('integrationGenerateWebhook'), 'ui-btn-wait');
										BX.removeClass(BX('ui-button-panel-save'), 'ui-btn-wait');
									}
								);
								btn.context.close();
							},
							this
						)
					}
				),
				new BX.UI.CancelButton({
					events:
						{
							click: function()
							{
								BX.removeClass(BX('integrationGenerateWebhook'), 'ui-btn-wait');
								BX.removeClass(BX('ui-button-panel-save'), 'ui-btn-wait');
								this.getContext().close();
							}
						}
				})
			],
		});

	};

	BX.rest.integration.openPostUri = function(event)
	{
		event.preventDefault();
		var form = document.getElementById('rest-integration-form');
		var sessId = form.querySelector('input[name="sessid"]');
		sessId.value = '';
		form.setAttribute("action", event.target.href);
		form.setAttribute("target", "_blank");
		form.submit();
		form.setAttribute("action", '');
		form.setAttribute("target", '');
		sessId.value = BX.bitrix_sessid();
	};

	BX.rest.integration.openUri = function(event)
	{
		if (restIntegrationEditComponent.hasOwnProperty('integrationCode') && restIntegrationEditComponent.integrationCode !== '')
		{
			BX.ajax.runComponentAction(
				'bitrix:rest.integration.edit',
				'analytic',
				{
					mode: 'class',
					signedParameters: restIntegrationEditComponent.signetParameters,
					data: {},
					analyticsLabel:
						{
							type: 'integrationDownloadExample',
							integrationCode: restIntegrationEditComponent.integrationCode
						}
				}
			);
		}
	};

	BX.rest.integration.actionSaveForm = function (event)
	{
		event.preventDefault();
		var appZipMode = BX('rest-integration-form').querySelector('#rest-integration-form #applicationType input[value="ZIP"]');
		if (appZipMode !== null && !!appZipMode.checked)
		{
			var inputZip = BX('rest-integration-form').querySelector('#applicationZip input[type="file"]');
			if (inputZip !== null && (!(inputZip.files.length > 0) || !(inputZip.files[0]['size'] > 0)))
			{
				BX.UI.Notification.Center.notify(
					{
						content: BX.message('REST_INTEGRATION_EDIT_TAB_APPLICATION_ZIP_NO_FILE')
					}
				);
				return false;
			}
		}

		BX.rest.integration.savedDo = true;
		BX.addClass(BX('ui-button-panel-save'), 'ui-btn-wait');
		var errorDom = BX('rest-integration-form-error');
		errorDom.style.display = 'none';
		BX.cleanNode(errorDom);
		//hack BX.ajax.prepareForm input type=url not access
		var inputsUrl = BX('rest-integration-form').querySelectorAll('input[type="url"]');
		inputsUrl.forEach(
			function (value)
			{
				value.type = 'text';
			}
		);

		var urlData = {
			mode: 'class',
			c: 'bitrix:rest.integration.edit',
			action: 'saveData'
		};
		var url = '/bitrix/services/main/ajax.php?' + BX.ajax.prepareData(urlData);

		BX.ajax.submitAjax(
			BX('rest-integration-form'),
			{
				method : 'POST',
				url: url,
				processData : true,
				data: {
					signedParameters: restIntegrationEditComponent.signetParameters
				},
				onsuccess: function (response)
				{
					response = BX.parseJSON(response, {});
					if (typeof response.data == 'object' && response.data.status === true)
					{
						var slider = BX.SidePanel.Instance.getTopSlider();
						slider.data = {NEW_OPEN: 'N'}
						slider.reload();
					}
					else
					{
						response.data.errors.forEach(
							function (error)
							{
								errorDom.style.display = 'block';
								BX.ready(
									function ()
									{
										BX.append(
											BX.create(
												'div',
												{
													props:
														{
															className: 'ui-alert ui-alert-danger'
														},
													children:
														[
															BX.create(
																'span',
																{
																	className: 'ui-alert-message',
																	text: error
																}
															)
														]
												}
											),
											errorDom
										);
									}
								);
							}
						);
						BX.scrollToNode(
							BX('pagetitle')
						);
					}
					BX.removeClass(BX('ui-button-panel-save'), 'ui-btn-wait');

					inputsUrl.forEach(
						function (value)
						{
							value.type = 'url';
						}
					);
				}
			}
		);
	};
})(window);

BX.ready(
	function ()
	{
		BX.rest.integration.initParams();
	}
);