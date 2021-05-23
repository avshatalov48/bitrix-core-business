;(function ()
{
	BX.namespace('BX.SocialServices.Auth');

	BX.SocialServices.Auth = {
		init: function(params)
		{
			this.signedParameters = params.signedParameters;
			this.componentName = params.componentName;

			var logoutButton = document.querySelector("[data-role='socserv-logout']");
			if (BX.type.isDomNode(logoutButton))
			{
				BX.bind(logoutButton, "click", function () {
					this.showConfirmLogoutPopup(this.logout.bind(this));
				}.bind(this));
			}
		},

		logout : function()
		{
			var block = document.getElementsByClassName("network-note");
			var loader = this.showLoader({node: block[0], loader: null, size: 100});

			BX.ajax.runComponentAction(this.componentName, "logout", {
				signedParameters: this.signedParameters,
				mode: 'ajax',
				data: {}
			}).then(function (result) {
				this.hideLoader({loader: loader});
				this.showSuccessPopup();
			}.bind(this), function (result) {
				this.hideLoader({loader: loader});
			}.bind(this));
		},

		showSuccessPopup: function()
		{
			BX.PopupWindowManager.create({
				id: "socserv-logout-success-popup",
				content:
					BX.create("div", {
						props : {
							style : "max-width: 450px"
						},
						html: BX.message("SOCSERV_LOGOUT_SUCCESS")
					}),
				closeIcon : true,
				lightShadow : true,
				offsetLeft : 100,
				overlay : false,
				contentPadding: 10
			}).show();
		},

		showConfirmLogoutPopup : function(confirmCallback)
		{
			BX.PopupWindowManager.create({
				id: "socserv-logout-confirm-popup",
				titleBar: BX.message("SOCSERV_LOGOUT_TITLE"),
				content:
					BX.create("div", {
						props : {
							style : "max-width: 450px"
						},
						html: BX.message("SOCSERV_LOGOUT_TEXT")
					}),
				closeIcon : false,
				lightShadow : true,
				contentColor: "white",
				offsetLeft : 100,
				overlay : false,
				contentPadding: 10,
				buttons: [
					new BX.UI.CreateButton({
						text: BX.message("SOCSERV_BUTTON_CONTINUE"),
						className: "ui-btn ui-btn-danger",
						events: {
							click: function () {
								this.context.close();
								confirmCallback();
							}
						}
					}),
					new BX.UI.CancelButton({
						text : BX.message("SOCSERV_BUTTON_CANCEL"),
						events : {
							click: function () {
								this.context.close();
							}
						}
					})
				],
				events : {
					onPopupClose: function ()
					{
						this.destroy();
					}
				}
			}).show();
		},

		showLoader: function(params)
		{
			var loader = null;

			if (params.node)
			{
				if (params.loader === null)
				{
					loader = new BX.Loader({
						target: params.node,
						size: params.hasOwnProperty("size") ? params.size : 40
					});
				}
				else
				{
					loader = params.loader;
				}

				loader.show();
			}

			return loader;
		},

		hideLoader: function(params)
		{
			if (params.loader !== null)
			{
				params.loader.hide();
			}

			if (params.node)
			{
				BX.cleanNode(params.node);
			}

			if (params.loader !== null)
			{
				params.loader = null;
			}
		}
	};
})();