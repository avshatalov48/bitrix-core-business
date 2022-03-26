'use strict';

BX.namespace("BX.Rest.Marketplace.Install");

BX.Rest.Marketplace.Install =
{
	init: function (params)
	{
		params = typeof params === "object" ? params : {};
		this.code = params.CODE || false;
		this.version = params.VERSION || false;
		this.checkHash = params.CHECK_HASH || false;
		this.installHash = params.INSTALL_HASH || false;
		this.from = params.FROM || false;
		this.iframe = params.IFRAME || false;
		this.redirectPriority = params.REDIRECT_PRIORITY || false;

		this.formNode = BX('restMarketAppInstallForm');
		this.buttonInstallNode = BX.findChildByClassName(this.formNode, 'rest-btn-start-install');
		this.buttonCloseNode = BX.findChildByClassName(this.formNode, 'rest-btn-close-install');
		BX.bind(this.formNode, 'submit', this.onSubmitForm.bind(this));
		BX.bind(this.buttonCloseNode, 'click', this.onClickClose.bind(this));

	},

	onClickClose: function (event)
	{
		event.preventDefault();
		if(!!this.iframe)
		{
			BX.SidePanel.Instance.close();
		}
	},

	onSubmitForm: function (event)
	{
		event.preventDefault();

		if (
			BX("mp_tos_license") && !BX("mp_tos_license").checked
		)
		{
			BX("rest_mp_install_error").innerHTML = BX.message("REST_MARKETPLACE_INSTALL_TOS_ERROR");
			BX.show(BX('rest_mp_install_error'));
			return;
		}

		if (
			BX("mp_detail_license") && !BX("mp_detail_license").checked
			|| BX("mp_detail_confidentiality") && !BX("mp_detail_confidentiality").checked
		)
		{
			BX("rest_mp_install_error").innerHTML = BX.message("REST_MARKETPLACE_INSTALL_LICENSE_ERROR");
			BX.show(BX('rest_mp_install_error'));
			return;
		}

		if (BX.hasClass(this.buttonInstallNode, "popup-window-button-wait"))
		{
			return;
		}

		BX.addClass(this.buttonInstallNode, "popup-window-button-wait");

		var queryParam = {
			code: this.code
		};

		if(!!this.version)
		{
			queryParam.version = this.version;
		}

		if(!!this.checkHash)
		{
			queryParam.check_hash = this.checkHash;
			queryParam.install_hash = this.installHash;
		}

		if (!!this.from)
		{
			queryParam.from = this.from;
		}
		BX.ajax.runAction(
			'rest.application.install',
			{
				data: queryParam
			}
		).then(
			function (response)
			{
				var result = !!response.data ? response.data : response;
				if (!!result.error)
				{
					if (!!result.helperCode && result.helperCode !== '')
					{
						top.BX.UI.InfoHelper.show(result.helperCode);
					}
					else
					{
						BX('rest_mp_install_error').innerHTML = '<div class="ui-alert-message">' + result.error
							+ (!!result.error_description
									? '<br />' + result.error_description
									: ''
							) + '</div>';

						BX.show(BX('rest_mp_install_error'));
					}
				}
				else if (!!result.redirect && this.redirectPriority === true)
				{
					top.location.href = result.redirect;
				}
				else if (!this.iframe)
				{
					if (!!result.redirect)
					{
						top.location.href = result.redirect;
					}
					else
					{
						top.location.href = BX.util.remove_url_param(top.location.href, ['install']);
					}
				}
				else
				{
					if (result.installed)
					{
						var eventResult = {};
						top.BX.onCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', [true, eventResult], false);
					}

					if (!!result.open)
					{
						BX.SidePanel.Instance.reload();
						top.BX.rest.AppLayout.openApplication(result.id, {});
					}
					else
					{
						BX.SidePanel.Instance.reload();
					}
				}
			}.bind(this)
		);

	},

	initHelper: function (params)
	{
		if (!window.BX.UI.InfoHelper.isInited())
		{
			window.BX.UI.InfoHelper.init(
				{
					frameUrlTemplate: params.frameUrlTemplate,
				}
			);
			window.BX.UI.InfoHelper.frameNode = BX(params.iframeId);
		}

		BX.bind(
			window,
			'message',
			BX.proxy(
				function (event)
				{
					if (!!event.origin && event.origin.indexOf('bitrix') === -1)
					{
						return;
					}

					if (!event.data || typeof(event.data) !== 'object')
					{
						return;
					}

					if (event.data.action === 'reloadParent')
					{
						var slider = BX.SidePanel.Instance.getTopSlider();
						if (!!slider)
						{
							slider.reload();
						}
						else
						{
							window.location.reload();
						}
					}
				}
			)
		);
	},
};
