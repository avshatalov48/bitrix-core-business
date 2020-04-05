/**
 * Class BX.Sale.Company
 */
(function(window)
{
	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.Company)
		return;

	BX.Sale.Company =
	{
		ajaxUrl: "/bitrix/admin/sale_company_ajax.php",

		getRuleParamsHtml: function (params)
		{
			if (!params.class)
				return;

			params.params = params.params || {};
			params.ruleId = params.ruleId || 0;
			params.sort = params.sort || 100;

			ShowWaitWindow();

			var postData = {
				action: "get_rule_params_html",
				className: params.class,
				params: params.params,
				companyId: params.companyId,
				sort: params.sort,
				lang: params.lang,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: postData,

				onsuccess: function (result)
				{
					CloseWaitWindow();

					if (result && result.RULE_HTML && !result.ERROR)
					{
						var data = BX.processHTML(result.RULE_HTML);
						BX.Sale.Company.showRuleParamsDialog(data['HTML'], params);
						window["companyGetRuleHtmlScriptsLoadingStarted"] = false;

						//process scripts
						var scr = function (loadScripts)
						{
							if (!loadScripts)
								BX.removeCustomEvent('companyGetRuleHtmlScriptsReady', scr);

							for (var i in data['SCRIPT'])
							{
								BX.evalGlobal(data['SCRIPT'][i]['JS']);
								delete(data['SCRIPT'][i]);

								//It can be nesessary  at first to load some JS for rule form
								if (loadScripts && window["companyGetRuleHtmlScriptsLoadingStarted"])
									return;
							}
						};

						BX.addCustomEvent('companyGetRuleHtmlScriptsReady', scr);
						scr(true);
						BX.loadCSS(data['STYLE']);
					}
					else if (result && result.ERROR)
					{
						BX.debug("Error receiving rule params html: " + result.ERROR);
					}
					else
					{
						BX.debug("Error receiving rule params html!");
					}
				},

				onfailure: function ()
				{
					CloseWaitWindow();
					BX.debug("Error adding rule!");
				}
			});
		},

		showRuleParamsDialog: function (content, ruleParams)
		{
			if(ruleParams.class == '\\Bitrix\\Sale\\Services\\Company\\Restrictions\\Location')
				var width = 1030;
			else
				width = 400;

			var dialog = new BX.CDialog({
					'content': '<form id="sale-company_rule-edit-form">' +
					content +
					'</form>',
					'title': BX.message("SALE_COMPANY_RULE_TITLE") + ": " + ruleParams.title,
					'width': width,
					'height': 500,
					'resizable': true
				});

			dialog.ClearButtons();
			dialog.SetButtons([
				{
					'title': BX.message("SALE_COMPANY_RULE_SAVE"),
					'action': function ()
					{

						var form = BX("sale-company_rule-edit-form"),
							prepared = BX.ajax.prepareForm(form),
							values = !!prepared && prepared.data ? prepared.data : {};

						BX.Sale.Company.saveRule(ruleParams, values);
						this.parentWindow.Close();
					}
				},
				BX.CDialog.prototype.btnCancel
			]);

			BX.addCustomEvent(dialog, 'onWindowClose', function (dialog)
			{
				dialog.DIV.parentNode.removeChild(dialog.DIV);
			});

			dialog.Show();
			dialog.adjustSizeEx();
		},

		saveRule: function (ruleParams, values)
		{
			ShowWaitWindow();

			var params = values.RULE || {},
				postData = {
					action: "save_rule",
					params: params,
					sort: values.SORT,
					className: ruleParams.class,
					companyId: ruleParams.companyId,
					ruleId: ruleParams.ruleId,
					sessid: BX.bitrix_sessid(),
					lang: BX.message('LANGUAGE_ID')
				};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: postData,

				onsuccess: function (result)
				{
					CloseWaitWindow();

					if (result && !result.ERROR)
					{
						if (result.HTML)
							BX.Sale.Company.insertAjaxRuleHtml(result.HTML);
					}
					else
					{
						alert(result.ERROR);
					}
				},

				onfailure: function ()
				{
					CloseWaitWindow();
				}
			});
		},

		deleteRule: function (ruleId, companyId)
		{
			if (!ruleId)
				return;

			ShowWaitWindow();

			var postData = {
				action: "delete_rule",
				ruleId: ruleId,
				companyId: companyId,
				sessid: BX.bitrix_sessid(),
				lang: BX.message('LANGUAGE_ID')
			};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: postData,

				onsuccess: function (result)
				{
					CloseWaitWindow();

					if (result && !result.ERROR)
					{
						if (result.HTML)
							BX.Sale.Company.insertAjaxRuleHtml(result.HTML);

						if (result.ERROR)
							BX.debug("Error deleting rule: " + result.ERROR);
					}
					else
					{
						BX.debug("Error deleting rule!");
					}
				},

				onfailure: function ()
				{
					CloseWaitWindow();
					BX.debug("Error refreshing rule!");
				}
			});
		},

		insertAjaxRuleHtml: function (html)
		{
			var data = BX.processHTML(html),
				container = BX("sale-company-rules-container");

			if (!container)
				return;

			BX.loadCSS(data['STYLE']);

			container.innerHTML = data['HTML'];

			for (var i in data['SCRIPT'])
				BX.evalGlobal(data['SCRIPT'][i]['JS']);
		}
	}
})(window);
