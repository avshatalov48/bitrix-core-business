BX.namespace('BX.Sale.BsmSiteMasterComponent');

(function() {
	'use strict';

	BX.Sale.BsmSiteMasterComponent = {
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
					&& this.currentStepId === "Bitrix\\Sale\\BsmSiteMaster\\Steps\\SiteStep"
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
					&& (
						this.currentStepId === "Bitrix\\Sale\\BsmSiteMaster\\Steps\\SiteInstructionStep"
						|| this.currentStepId === "Bitrix\\Sale\\BsmSiteMaster\\Steps\\BackupStep"
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