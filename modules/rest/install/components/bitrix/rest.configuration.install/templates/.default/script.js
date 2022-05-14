;(function ()
{
	BX.namespace('BX.Rest.Configuration.Install');
	if (!BX.Rest.Configuration.Install)
	{
		return;
	}

	/**
	 * Install.
	 *
	 */
	function Install()
	{
	}

	Install.prototype =
	{
		init: function (params)
		{
			this.id = params.id;
			this.importByProcessId = params.importByProcessId;
			this.signedParameters = params.signedParameters;
			this.next = '';
			this.section = [];
			this.progressDescriptionContainer = BX.findChildByClassName( BX(this.id), 'rest-configuration-info');
			this.needClearFull = params.needClearFull;
			this.needClearFullConfirm = params.needClearFullConfirm;
			this.skipClearing = params.skipClearing;
			this.errors = [];
			this.loaderPointSymbol = '.';
			this.loaderPointCount = 3;
			this.closeSliderPopup = false;
			this.showCloseConfirmation = true;

			var startBtn = BX.findChildByClassName( BX(this.id),'start_btn');
			if (startBtn !== null)
			{
				BX.bind(
					startBtn,
					'click',
					BX.delegate(
						function ()
						{
							if (this.needClearFullConfirm === true)
							{
								this.showConfirmClearAll(startBtn);
							}
							else if (this.importByProcessId === true)
							{
								this.run();
							}
							else
							{
								this.start();
							}
						},
						this
					)
				);
			}
			else
			{
				if (this.importByProcessId === true)
				{
					this.run();
				}
				else
				{
					this.start();
				}
			}


			var startLaterBtn = BX.findChildByClassName( BX(this.id),'start_later_btn');
			if (startLaterBtn !== null)
			{
				BX.bind(
					startLaterBtn,
					'click',
					BX.delegate(
						function()
						{
							this.showPopupInstallLater(startLaterBtn);
						},
						this
					)
				);

			}
			var slider = BX.SidePanel.Instance.getTopSlider();
			if (slider)
			{
				BX.addCustomEvent(slider, "SidePanel.Slider:onClose", this.handleSliderClose.bind(this));
			}
		},

		handleSliderClose: function(event)
		{
			if (this.showCloseConfirmation)
			{
				event.denyAction();
			}
			if (!this.closeSliderPopup)
			{
				this.closeSliderPopup = new top.BX.PopupWindow(
					{
						titleBar: BX.message('REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_TITLE'),
						content: BX.message('REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_DESCRIPTION'),
						closeIcon: false,
						buttons: [
							new top.BX.PopupWindowButton(
								{
									text: BX.message('REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_BTN_CONTINUE'),
									className: 'popup-window-button-accept',
									events: {
										click: function()
										{
											this.closeSliderPopup.close();
										}.bind(this)
									}
								}
							),
							new top.BX.PopupWindowButton(
								{
									className: 'popup-window-button popup-window-button-link',
									text: BX.message('REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_BTN_CLOSE'),
									events: {
										click: function()
										{
											this.showCloseConfirmation = false;
											event.slider.close();
											this.closeSliderPopup.close();
										}.bind(this)
									}
								}
							)
						]
					}
				);
			}
			if (this.showCloseConfirmation)
			{
				this.closeSliderPopup.show();
			}
		},

		showPopupInstallLater: function(eventBtn)
		{
			this.sendAjax(
				'preInstallOff',
				{},
				BX.delegate(
					function (response)
					{
						BX.UI.Dialogs.MessageBox.show(
							{
								message:
									BX.create(
										"p",
										{
											html: BX.message('REST_CONFIGURATION_IMPORT_PRE_INSTALL_LATER_APP_POPUP_DESCRIPTION'),
										}
									),
								modal: true,
								bindElement: eventBtn,
								buttons: [
									new BX.UI.Button(
										{
											text: BX.message('REST_CONFIGURATION_IMPORT_INSTALL_LATER_POPUP_CLOSE_BTN'),
											color: BX.UI.Button.Color.PRIMARY,
											onclick: function (btn) {
												btn.context.close();
												BX.SidePanel.Instance.close();
											}
										}
									),
								],
							}
						);
					},
					this
				)
			);
		},

		showConfirmClearAll: function(startBtn)
		{
			var btnConfirm = new BX.UI.Button({
				color: BX.UI.Button.Color.PRIMARY,
				state: BX.UI.Button.State.DISABLED,
				text: BX.message('REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_BTN_CONTINUE'),
				onclick: BX.delegate(
					function (btn)
					{
						if (!BX('CONFIGURATION_ACCEPT_CLEAR_ALL').checked)
						{
							return false;
						}
						btn.context.close();
						this.start();
					},
					this
				)
			});
			var message = BX.create(
				'div',
				{
					children: [
						BX.create(
							"p",
							{
								text: BX.message('REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_TEXT'),
							}
						),
						BX.create(
							"INPUT",
							{
								attrs: {
									id: "CONFIGURATION_ACCEPT_CLEAR_ALL",
									type: "checkbox",
									name: 'ACCEPT_CLEAR_ALL',
									value: 'Y'
								},
								events: {
									change: function (event) {
										btnConfirm.setState(
											this.checked ? BX.UI.Button.State.ACTIVE : BX.UI.Button.State.DISABLED
										);
									}
								}
							}
						),
						BX.create(
							'label',
							{
								attrs: {
									for: "CONFIGURATION_ACCEPT_CLEAR_ALL"
								},
								text: BX.message('REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_CHECKBOX_LABEL'),
							}
						)
					]
				}
			);

			BX.UI.Dialogs.MessageBox.show({
				message: message,
				modal: true,
				bindElement: startBtn,
				buttons: [
					btnConfirm,
					new BX.UI.Button({
						text: BX.message('REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_BTN_CANCEL'),
						onclick: function(btn) {
							btn.context.close();
						}
					}),
				],
			});
		},

		setDescription: function (code, step)
		{
			code = 'REST_CONFIGURATION_IMPORT_INSTALL_STEP_'+code;
			var mess = BX.message[code]? BX.message(code): BX.message('REST_CONFIGURATION_IMPORT_INSTALL_STEP');

			if (this.loaderPointCount > 0 && BX.type.isInteger(step))
			{
				var space = '&nbsp;';
				var countPoint = step % this.loaderPointCount + 1;
				var countSpace = this.loaderPointCount - countPoint;
				mess += this.loaderPointSymbol.repeat(countPoint) + space.repeat(countSpace);
			}

			BX.html(this.progressDescriptionContainer, mess);
		},

		showLoader: function ()
		{
			BX.addClass(BX.findChildByClassName(BX(this.id), 'rest-configuration-start-icon-main'), 'rest-configuration-start-icon-main-loading');
			BX.style(BX.findChildByClassName(BX(this.id), 'start-btn-block'), 'display', 'none');
			this.setDescription('');
		},

		showFinish: function (result)
		{
			this.setDescription('FINISH');
			var barContainer = BX.findChildByClassName(BX(this.id), 'rest-configuration-start-icon-main');
			BX.removeClass(barContainer, 'rest-configuration-start-icon-main-loading');

			var text = '';
			if (this.errors.length === 0)
			{
				text = BX.message('REST_CONFIGURATION_IMPORT_FINISH_DESCRIPTION');
				BX.addClass(barContainer, 'rest-configuration-start-icon-main-success');
			}
			else
			{
				text = BX.message('REST_CONFIGURATION_IMPORT_FINISH_ERROR_DESCRIPTION');
				BX.addClass(barContainer, 'rest-configuration-start-icon-main-error');
			}

			BX.cleanNode(this.progressDescriptionContainer);
			this.progressDescriptionContainer.appendChild(
				BX.create(
					'p',
					{
						attrs: {
							className: '',
						},
						text: text,
					}
				)
			);

			if (this.errors.length !== 0)
			{
				this.progressDescriptionContainer.appendChild(
					BX.create(
						'div',
						{
							attrs: {
								className: 'rest-configuration-links',
							},
							children: [
								BX.create(
									'a',
									{
										attrs: {
											'data-slider-ignore-autobinding': 'true',
											href: '',
										},
										events: {
											click: BX.delegate(this.openPopupErrors, this),
										},
										text: BX.message('REST_CONFIGURATION_IMPORT_ERRORS_REPORT_BTN'),
									}
								)
							]
						}
					)
				);
			}

			BX.insertAfter(
				BX.create(
					'div',
					{
						attrs: {
							className: 'rest-configuration-action-block',
						},
						children: [],
					}
				),
				BX.findChildByClassName(BX(this.id), 'rest-configuration-start-icon-main')
			);


			if (this.errors.length === 0)
			{
				BX(this.id).appendChild(
					BX.create(
						'p',
						{
							attrs: {
								className: 'rest-configuration-import-finish rest-configuration-info',
							},
							html: BX.message('REST_CONFIGURATION_IMPORT_INSTALL_FINISH_TEXT'),
						}
					)
				);
			}

			var elementList = [];
			if (!!result.createItemList && result.createItemList.length > 0)
			{
				for (var i = 0; i < result.createItemList.length; i++)
				{
					if (!result.createItemList[i]['DATA'])
					{
						result.createItemList[i]['DATA'] = {
							'events': {},
						};
					}
					var tag = result.createItemList[i]['TAG'];
					var data = result.createItemList[i]['DATA'];

					if (!data['events'])
					{
						data['events'] = {};
					}
					if (!data['events']['click'])
					{
						data['events']['click'] = function (event)
						{
							if (event.target.localName === 'a' && event.target.href.indexOf(event.target.baseURI) === 0)
							{
								event.preventDefault();
								BX.remove(event.target);
							}
						};
					}

					elementList[i] = BX.create(
						tag,
						data
					);
					this.progressDescriptionContainer.appendChild(
						elementList[i]
					);
				}
			}

			top.BX.Event.EventEmitter.emit(
				'BX.Rest.Configuration.Install:onFinish',
				new top.BX.Event.BaseEvent(
					{
						data: {
							finishResponse: result,
							errors: this.errors,
							elementList: elementList,
						},
					}
				)
			);
			this.showCloseConfirmation = false;
		},

		finish: function ()
		{
			var self = this;
			this.sendAjax(
				'finish',
				{},
				BX.delegate(
					function (response)
					{
						if (response.data.result === true)
						{
							this.showFinish(response.data);
						}
						else
						{
							this.finish();
						}
					},
					this
				)
			);
		},

		addErrors: function (errors)
		{
			for (var i = 0; i < errors.length; i++)
			{
				this.errors.push(errors[i]);
			}
		},

		openPopupErrors: function ()
		{
			var errorText = '';
			this.errors.forEach(function(item) {
				if (BX.Type.isString(item))
				{
					errorText += item + '\r\n'
				}
				else
				{
					if ('message' in item)
					{
						errorText += '[' + item['code'] + '] ' + item['message'] + '\r\n'
					}
				}
			});
			var errorTextArea = BX.create('textarea', {
				props: {
					className: 'rest-configuration-popup-textarea',
					placeholder: BX.message('REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TEXT_PLACEHOLDER')
				},
				html: errorText
			});
			var restConfigWindowContent = BX.create('div', {
				children: [
					BX.create('div', {
						props: {
							className: 'rest-configuration-popup-textarea-title'
						},
						text: BX.message('REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TEXT_LABEL')
					}),
					errorTextArea
				]
			});

			var restConfigWindow = BX.PopupWindowManager.create('rest-configuration-popup', null, {
				className: 'rest-configuration-popup',
				titleBar: BX.message('REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TITLE'),
				content: restConfigWindowContent,
				contentBackground: 'transparent',
				contentPadding: 10,
				minWidth:250,
				maxWidth: 450,
				autoHide: true,
				closeIcon: true,
				animation: 'fading-slide',
				buttons: [
					new BX.UI.Button(
						{
							text: BX.message('REST_CONFIGURATION_IMPORT_ERRORS_POPUP_BTN_COPY'),
							color: BX.UI.Button.Color.LINK,
							events: {
								click: function () {
									errorTextArea.select();
									document.execCommand("copy");
								}
							}
						}
					)

				],
				onPopupClose: function () {
					this.destroy();
				},
			});
			restConfigWindow.show();

		},

		run: function (step)
		{
			if (!BX.type.isInteger(step))
			{
				this.showLoader();
				step = 0;
			}
			else
			{
				step++;
			}

			this.sendAjax(
				'run',
				{},
				BX.delegate(
					function (response)
					{
						if (!response.data.finish)
						{
							if (!!response.data.step)
							{
								this.setDescription(response.data.step.toString().toUpperCase(), step);
							}
							this.run(step);
						}
						else
						{
							this.showFinish(response.data);
						}
					},
					this
				)
			);
		},

		start: function ()
		{
			this.showLoader();
			this.setDescription('START');
			this.sendAjax(
				'start',
				{},
				BX.delegate(
					function (response)
					{
						if(response.data.section.length > 0)
						{
							this.section = response.data.section;
							if(!!response.data.next && response.data.next === 'save')
							{
								this.loadManifest(0, '', 'export');
							}
							else
							{
								this.loadManifest(0, '', 'import');
							}
						}
					},
					this
				)
			);
		},

		finishSave: function()
		{
			this.sendAjax(
				'finishSave',
				{},
				BX.delegate(
					function (response)
					{
						this.loadManifest(0, '', 'import');
					},
					this
				)
			);
		},

		save: function (section, step)
		{
			this.sendAjax(
				'save',
				{
					code: this.section[section],
					step: step,
					next: this.next
				},
				BX.delegate(
					function (response)
					{
						if(!!response.data)
						{
							this.next = response.data.next;
							step++;
							if(this.next === false)
							{
								section++;
								step = 0;
							}

							if(section >= this.section.length)
							{
								this.finishSave(0, '', 'import');
							}
							else
							{
								this.save(section, step);
							}
						}
						else
						{
							this.showFatalError();
						}
					},
					this
				)
			);
		},

		loadManifest: function (step, next, type)
		{
			this.sendAjax(
				'loadManifest',
				{
					step: step,
					next: next,
					type: type
				},
				BX.delegate(
					function (response)
					{
						if(!!response.data)
						{
							step++;
							if(response.data.next === false)
							{
								if(type === 'export')
								{
									this.save(0, 0);
								}
								else
								{
									this.clear(0, 0, 0);
								}
							}
							else
							{
								this.loadManifest(step, response.data.next, type);
							}
						}
						else
						{
							this.showFatalError();
						}
					},
					this
				)
			);
		},

		clear: function (section, step, next)
		{
			if (this.skipClearing)
			{
				this.import(0, 0);
			}
			else
			{
				this.setDescription('CLEAR');
				this.sendAjax(
					'clear',
					{
						code: this.section[section],
						step: step,
						next: next
					},
					BX.delegate(
						function (response)
						{
							step++;
							next = response.data.next;
							if (next === false)
							{
								section++;
								step = 0;
								next = 0;
							}

							if (section < this.section.length)
							{
								this.clear(section, step, next);
							}
							else
							{
								this.import(0, 0);
							}
						},
						this
					)
				);
			}
		},

		import: function (section, step)
		{
			this.sendAjax(
				'import',
				{
					code: this.section[section],
					step: step
				},
				BX.delegate(
					function (response)
					{
						step++;
						if(!response.data.errors)
						{
							this.setDescription(this.section[section]);
						}
						if(response.data.result === true)
						{
							section++;
							step = 0;
						}
						if(section < this.section.length)
						{
							this.import(section, step);
						}
						else
						{
							this.finish();
						}
					},
					this
				)
			);
		},

		showFatalError: function (messageList)
		{
			var message = '';
			var barContainer = BX.findChildByClassName( BX(this.id),'rest-configuration-start-icon-main');
			BX.removeClass(barContainer,'rest-configuration-start-icon-main-zip rest-configuration-start-icon-main-loading');
			BX.addClass(barContainer,'rest-configuration-start-icon-main-error');

			if (messageList.length > 0)
			{
				for (var i = 0; i < messageList.length; i++)
				{
					if (message !== '')
					{
						message += "\n";
					}
					message = messageList[i].message;
				}
			}
			else if (BX.type.isString(messageList))
			{
				message = messageList;
			}
			BX.cleanNode(this.progressDescriptionContainer);
			this.progressDescriptionContainer.appendChild(
				BX.create('div', {
					attrs: {
						className: 'rest-configuration-fatal-error-block'
					},
					children:[
					],
					'text': (message !== '') ? message : BX.message("REST_CONFIGURATION_IMPORT_INSTALL_FATAL_ERROR")
				})
			);
			this.showCloseConfirmation = false;
		},

		sendAjax: function (action, data, callback)
		{
			data.clear = this.needClearFull;
			BX.ajax.runComponentAction(
				'bitrix:rest.configuration.install',
				action,
				{
					mode: 'class',
					signedParameters: this.signedParameters,
					data: data
				}
			).then(
				BX.delegate(
					function(response)
					{
						if(!!response.data.exception)
						{
							this.showFatalError(response.data.exception);
						}
						else
						{
							callback(response);
						}
						if(!!response.data.errors)
						{
							this.addErrors(response.data.errors);
						}
						if(!!response.data['notice'])
						{
							console.log({
								errors: response.data['notice'],
								action: action,
								data: data,
								response: response
							});
						}
					},
					this
				)
			).catch(
				function(response)
				{
					this.showFatalError(false);
				}.bind(this)
			);
		}
	};

	BX.Rest.Configuration.Install =  new Install();

})(window);