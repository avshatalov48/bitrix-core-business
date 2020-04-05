BX.namespace('BX.Sale.CrmSiteMasterComponent');

(function() {
	'use strict';

	BX.Sale.CrmSiteMasterComponent = {
		init: function (parametrs)
		{
			this.wizardSteps = parametrs.wizardSteps;
			this.formId = parametrs.formId || '';
			this.formNode = BX(parametrs.formId);
			this.documentRoot = parametrs.documentRoot || '';
			this.siteNameNode = BX(parametrs.siteNameId);
			this.docRootNode = BX(parametrs.docRootId);
			this.docRootLinkNode = BX(parametrs.docRootLinkId);
			this.createSiteNode = BX(parametrs.createSiteId);
			this.keyNode = BX(parametrs.keyId);
			this.keyButtonNode = BX(parametrs.keyButtonId);
			this.keyInputBlockNode = BX(parametrs.keyInputBlockId);
			this.confirmationCheckboxNode = BX(parametrs.confirmationCheckboxId);

			this.nextButtonNode = document.forms[this.formId].elements[parametrs.nextButtonId];
			this.prevButtonNode = document.forms[this.formId].elements[parametrs.prevButtonId];
			this.cancelButtonNode = document.forms[this.formId].elements[parametrs.cancelButtonId];
			this.finishButtonNode = document.forms[this.formId].elements[parametrs.finishButtonId];

			this.currentStepId = parametrs.currentStepId;

			this.licenseKeyRegExp = /^([A-Z0-9]{3}-[A-Z0-9]{2}-[A-Z0-9]{12,16})$/gm;

			if (this.currentStepId !== undefined)
			{
				if (this.wizardSteps.includes(this.currentStepId)
					&& this.currentStepId === "Bitrix\\Sale\\CrmSiteMaster\\Steps\\SiteStep"
				)
				{
					this.bindSiteEvents();

					if (this.siteNameNode)
					{
						this.saleNewSiteForm(this.siteNameNode);
						BX.bind(this.siteNameNode, 'change', BX.proxy(this.saleNewSiteForm.bind(this, this.siteNameNode), this));
					}
				}

				if (this.wizardSteps.includes(this.currentStepId)
					&& this.currentStepId === "Bitrix\\Sale\\CrmSiteMaster\\Steps\\ActivationKeyStep"
				)
				{
					this.bindKeyEvents();
				}

				if (this.wizardSteps.includes(this.currentStepId)
					&& (
						this.currentStepId === "Bitrix\\Sale\\CrmSiteMaster\\Steps\\SiteInstructionStep"
						|| this.currentStepId === "Bitrix\\Sale\\CrmSiteMaster\\Steps\\BackupStep"
					)
				)
				{
					this.bindConfirmationCheckboxEvents();
				}
			}
		},

		bindSiteEvents: function()
		{
			BX.bind(this.docRootLinkNode, "click", BX.proxy(this.setDocumentRoot, this));
		},

		bindKeyEvents: function()
		{
			BX.bind(this.keyButtonNode, "click", BX.proxy(this.applyKey, this));
			BX.bind(this.keyNode, "click", BX.proxy(this.inputKeyClick, this));
		},

		bindConfirmationCheckboxEvents: function()
		{
			BX.bind(this.confirmationCheckboxNode, "change", BX.proxy(this.onConfirmationCheckbox, this));
		},

		saleNewSiteForm: function(siteSelectNode)
		{
			var show = siteSelectNode.value === "new";
			this.createSiteNode.style.display = show ? "block" : "none";
		},

		setDocumentRoot: function()
		{
			if (this.documentRoot.length >= 0)
			{
				this.docRootNode.value = this.documentRoot;
				BX.fireEvent(this.docRootNode, 'change');
			}
		},

		applyKey: function(event)
		{
			var keyValue = this.keyNode.value;

			event.preventDefault();

			this.keyButtonNode.disabled = true;
			BX.addClass(this.keyButtonNode, "ui-btn-disabled");

			if (keyValue.match(this.licenseKeyRegExp))
			{
				this.changeLicenseKey(keyValue);
			}
			else
			{
				this.activateCoupon(keyValue)
			}
		},

		changeLicenseKey: function(key)
		{
			var that = this;

			BX.ajax.runComponentAction('bitrix:sale.crm.site.master', 'updateLicenseKey', {
				mode: 'ajax',
				data: {
					key: key
				},
			}).then(function (response) {
				that.checkUpdateSystem();
			}, function (response) {
				that.keyButtonNode.disabled = false;
				BX.removeClass(that.keyButtonNode, "ui-btn-disabled");

				var errors = "";
				for (var i in response.errors)
				{
					if (response.errors.hasOwnProperty(i))
					{
						errors += (response.errors[i].message + "<br>");
					}
				}

				if (errors.length > 0)
				{
					that.addKeyError(errors);
				}
			});
		},

		activateCoupon: function (coupon)
		{
			var that = this;

			CHttpRequest.Action = function(result)
			{
				result = that.prepareResponse(result);
				if (result === "Y")
				{
					that.autoSubmit();
				}
				else
				{
					that.addKeyError(BX.message('SALE_CSM_TEMPLATE_COUPON_ERROR'));
					that.keyButtonNode.disabled = false;
					BX.removeClass(that.keyButtonNode, "ui-btn-disabled");
				}
			};

			CHttpRequest.Send(
				'/bitrix/admin/update_system_act.php?query_type=coupon' +
				'&sessid=' + BX.bitrix_sessid() +
				'&COUPON=' + escape(coupon) +
				"&updRand=" + Math.random()
			);
		},

		inputKeyClick: function()
		{
			this.removeKeyError();
		},

		prepareResponse: function(str)
		{
			str = str.replace(/^\s+|\s+$/, '');
			while (str.length > 0 && str.charCodeAt(0) == 65279)
				str = str.substring(1);
			return str;
		},

		checkUpdateSystem: function()
		{
			var that = this;

			BX.ajax.runComponentAction('bitrix:sale.crm.site.master', 'checkUpdateSystem', {
				mode: 'ajax'
			}).then(function (response) {
				that.autoSubmit();
			}, function (response) {
				console.log(response);
			});
		},

		autoSubmit: function ()
		{
			if (this.nextButtonNode)
			{
				this.formNode.submit();
			}
		},

		addKeyError: function($error)
		{
			var inputWrap = BX(this.keyInputBlockNode.querySelector(".ui-ctl.ui-ctl-textbox"));
			if (BX.hasClass(inputWrap, "ui-ctl-active"))
			{
				BX.removeClass(inputWrap, "ui-ctl-active");
				BX.addClass(inputWrap, "ui-ctl-danger");

				inputWrap.insertAdjacentHTML(
					'afterend',
					'<div class="adm-crm-site-master-errors">' + $error + '</div>'
				);
			}
		},

		removeKeyError: function()
		{
			var inputWrap = BX(this.keyInputBlockNode.querySelector(".ui-ctl.ui-ctl-textbox"));

			if (BX.hasClass(inputWrap, "ui-ctl-danger"))
			{
				BX.removeClass(inputWrap, "ui-ctl-danger");
				BX.addClass(inputWrap, "ui-ctl-active");

				var errorBlock = BX(this.keyInputBlockNode.querySelector(".adm-crm-site-master-errors"));
				if (errorBlock !== undefined)
				{
					BX.remove(errorBlock);
				}
			}
		},

		onConfirmationCheckbox: function(e)
		{
			if (!this.nextButtonNode)
				return;

			if (e.target.checked)
			{
				this.nextButtonNode.disabled = false;
				BX.removeClass(this.nextButtonNode, "ui-btn-disabled")
			}
			else
			{
				this.nextButtonNode.disabled = true;
				BX.addClass(this.nextButtonNode, "ui-btn-disabled")
			}
		}
	};
})();