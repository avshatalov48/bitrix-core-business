;(function ()
{
	BX.namespace('BX.Rest.Configuration.Export');
	if (!BX.Rest.Configuration.Export)
	{
		return;
	}

	/**
	 * Export.
	 *
	 */
	function Export()
	{
	}

	Export.prototype =
	{
		init: function (params)
		{
			this.next = '';
			this.section = [];
			this.errors = [];
			this.id = params.id;
			this.signedParameters = params.signedParameters;

			BX.bind(
				BX.findChildByClassName( BX(this.id),'start-btn'),
				'click',
				BX.delegate(
					function(e){
						this.start();
					},
					this
				)
			);
		},

		start: function ()
		{
			BX.addClass(BX.findChildByClassName( BX(this.id),'rest-configuration-start-icon-main'), 'rest-configuration-start-icon-main-loading');
			BX.style(BX.findChildByClassName( BX(this.id),'start-btn-block'), 'display', 'none');
			BX.insertAfter(
				BX.create('div', {
					attrs: {
						className: 'rest-configuration-info'
					},
					children:[
						BX.create('p', {
							attrs: {
								className: ''
							},
							text: BX.message("REST_CONFIGURATION_EXPORT_START_DESCRIPTION")
						})
					]
				}),
				BX.findChildByClassName( BX(this.id),'rest-configuration-info')
			);
			BX.findChildByClassName( BX(this.id),'rest-configuration-info').remove();

			this.sendAjax(
				'start',
				{},
				BX.delegate(
					function (response)
					{
						if(response.data.length > 0)
						{
							this.section = response.data;
							this.loadManifest(0, '');
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

		loadManifest: function (step, next)
		{
			this.sendAjax(
				'loadManifest',
				{
					step: step,
					next: next
				},
				BX.delegate(
					function (response)
					{
						if(!!response.data)
						{
							step++;
							if(response.data.next === false)
							{
								this.load(0, 0);
							}
							else
							{
								this.loadManifest(step, response.data.next);
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

		load: function (section, step)
		{
			this.sendAjax(
				'load',
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
								this.finish();
							}
							else
							{
								this.load(section, step);
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

		finish: function ()
		{
			this.sendAjax(
				'finish',
				{},
				BX.delegate(
					function (response)
					{
						var barContainer = BX.findChildByClassName( BX(this.id),'rest-configuration-start-icon-main');
						var infoContainer = BX.findChildByClassName( BX(this.id),'rest-configuration-info');
						BX.removeClass(barContainer,'rest-configuration-start-icon-main-zip rest-configuration-start-icon-main-loading');

						var download = '';
						if(!!response.data && !!response.data.download)
						{
							download = response.data.download;
						}

						var text = '';
						if(this.errors.length === 0 && download !== '')
						{
							text = BX.message("REST_CONFIGURATION_EXPORT_FINISH_DESCRIPTION");
							BX.addClass(barContainer,'rest-configuration-start-icon-main-success');
						}
						else
						{
							text = BX.message("REST_CONFIGURATION_EXPORT_FINISH_ERROR_DESCRIPTION");
							BX.addClass(barContainer,'rest-configuration-start-icon-main-error');
						}

						BX.cleanNode(infoContainer);
						infoContainer.appendChild(
							BX.create('p', {
								attrs: {
									className: ''
								},
								text: text
							})
						);
						if(download !== '')
						{
							infoContainer.appendChild(
								BX.create('a', {
									attrs: {
										className: 'ui-btn ui-btn-lg ui-btn-primary start-btn',
										href: download,
										'data-slider-ignore-autobinding': 'true'
									},
									text: BX.message("REST_CONFIGURATION_DOWNLOAD_BTN")
								})
							);
						}

						if(this.errors.length !== 0)
						{
							infoContainer.appendChild(
								BX.create('div', {
									attrs: {
										className: 'rest-configuration-links'
									},
									children: [
										BX.create('a', {
											attrs: {
												'data-slider-ignore-autobinding': 'true',
												href: ''
											},
											events: {
												click: BX.delegate(this.openPopupErrors, this)
											},
											text: BX.message("REST_CONFIGURATION_EXPORT_ERRORS_REPORT_BTN")
										})
									]
								})
							);
						}
					},
					this
				)
			);
		},

		sendAjax: function (action, data, callback)
		{
			BX.ajax.runComponentAction(
				'bitrix:rest.configuration.export',
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
						callback(response);
						if(!!response.data.errors)
						{
							this.addErrors(response.data.errors);
						}
						if(!!response.data['errorsNotice'])
						{
							console.log({
								errors: response.data['errorsNotice'],
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
					this.showFatalError();
				}.bind(this)
			);
		},

		addErrors: function (errors)
		{
			for (var i = 0; i < errors.length; i++)
			{
				this.errors.push(errors[i]);
			}
		},

		showFatalError: function ()
		{
			var infoContainer = BX.findChildByClassName( BX(this.id),'rest-configuration-info');
			var barContainer = BX.findChildByClassName( BX(this.id),'rest-configuration-start-icon-main');
			BX.removeClass(barContainer,'rest-configuration-start-icon-main-zip rest-configuration-start-icon-main-loading');
			BX.addClass(barContainer,'rest-configuration-start-icon-main-error');

			BX.cleanNode(infoContainer);
			infoContainer.appendChild(
				BX.create('div', {
					attrs: {
						className: 'rest-configuration-fatal-error-block'
					},
					children:[
					],
					'text': BX.message("REST_CONFIGURATION_FATAL_ERROR")
				})
			);
		},

		openPopupErrors: function ()
		{
			var errorText = '';
			this.errors.forEach(function(item) {
				errorText += item + '\r\n'
			});

			var errorTextArea = BX.create('textarea', {
				props: {
					className: 'rest-configuration-popup-textarea',
					placeholder: BX.message('REST_CONFIGURATION_EXPORT_ERRORS_POPUP_TEXT_PLACEHOLDER')
				},
				html: errorText
			});

			var restConfigWindowContent = BX.create('div', {
				children: [
					BX.create('div', {
						props: {
							className: 'rest-configuration-popup-textarea-title'
						},
						text: BX.message('REST_CONFIGURATION_EXPORT_ERRORS_POPUP_TEXT_LABEL')
					}),
					errorTextArea
				]
			});

			var restConfigWindow = BX.PopupWindowManager.create('rest-configuration-popup', null, {
				className: 'rest-configuration-popup',
				titleBar: BX.message('REST_CONFIGURATION_EXPORT_ERRORS_POPUP_TITLE'),
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
							text: BX.message('REST_CONFIGURATION_EXPORT_ERRORS_POPUP_BTN_COPY'),
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
		}
	};

	BX.Rest.Configuration.Export =  new Export();

})(window);