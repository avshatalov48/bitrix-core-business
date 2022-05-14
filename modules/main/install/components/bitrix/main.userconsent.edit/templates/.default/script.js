;(function ()
{
	BX.namespace('BX.Main.UserConsent');

	BX.Main.UserConsent.Edit = function (params)
	{
		this.isSaved = params.isSaved;
		this.mess = params.mess;
		this.listDomIds = params.listDomIds;

		this.container = BX(this.listDomIds['formContainerId']);

		this.typeNode = this.container.querySelector('[data-bx-type-input]');
		this.langNode = this.container.querySelector('[data-bx-lang-input]');
		this.initialLang = (this.langNode.value && this.typeNode.value === 'C')
			? this.langNode.value
			: null;

		this.typeSelectorNode = this.container.querySelector('[data-bx-type-selector]');
		this.typeViewNode = this.container.querySelector('[data-bx-type-view]');
		this.dataProviderNode = this.container.querySelector('[data-bx-data-provider]');
		this.dataProviderInputNode = this.container.querySelector('[data-bx-data-provider-input]');
		this.dataProviderUrlNode = this.container.querySelector('[data-bx-data-provider-url]');
		this.fieldListNodes = this.container.querySelectorAll('[data-bx-fields]');
		this.fieldListNodes = BX.convert.nodeListToArray(this.fieldListNodes);

		BX.bind(this.typeViewNode, 'click', this.showAgreementText.bind(this));

		BX.bind(this.typeSelectorNode, 'change', this.onTypeChange.bind(this));
		BX.fireEvent(this.typeSelectorNode, 'change');

		BX.bind(this.dataProviderInputNode, 'change', this.onDataProviderChange.bind(this));
		BX.bind(this.dataProviderUrlNode, 'click', this.onDataProviderUrlClick.bind(this));
		BX.fireEvent(this.dataProviderInputNode, 'change');

		this.getFields(this.container).forEach(function (field) {
			if (!field.togglerNode || !field.toggledNode || !field.inputNode)
			{
				return;
			}

			BX.bind(field.togglerNode, 'click', function () {
				field.toggledNode.style.display = field.togglerNode.checked ? '' : 'none';
				field.inputNode.disabled = !field.togglerNode.checked;
			});
		});

		if (top != window && top.BX)
		{
			BX.bind(BX('MAIN_USER_CONSENT_EDIT_BACK_TO_LIST'), 'click', function (e) {
				if (!top || !top.BX)
				{
					return;
				}
				top.BX.onCustomEvent(top, 'main-user-consent-to-list', []);
				e.preventDefault();
			});
		}

		if (this.isSaved)
		{
			this.sendEventMessageAboutSave();
		}
	};

	BX.Main.UserConsent.Edit.prototype.showTextTab = function ()
	{
		this.container.style.display = '';
		if (BX(this.listDomIds['listContainerId']))
		{
			BX(this.listDomIds['listContainerId']).style.display = 'none';
		}

		BX(this.listDomIds['fieldNameId']).style.display = '';
		BX(this.listDomIds['fieldTypeId']).style.display = '';
		BX(this.listDomIds['fieldProviderId']).style.display = '';

		this.showTab('text');
	};

	BX.Main.UserConsent.Edit.prototype.showSettingsTab = function ()
	{
		this.container.style.display = '';
		if (BX(this.listDomIds['listContainerId']))
		{
			BX(this.listDomIds['listContainerId']).style.display = 'none';
		}

		BX(this.listDomIds['fieldNameId']).style.display = 'none';
		BX(this.listDomIds['fieldTypeId']).style.display = 'none';
		BX(this.listDomIds['fieldProviderId']).style.display = 'none';

		this.showTab('settings');
	};

	BX.Main.UserConsent.Edit.prototype.showListTab = function ()
	{
		this.container.style.display = 'none';
		if (BX(this.listDomIds['listContainerId']))
		{
			BX(this.listDomIds['listContainerId']).style.display = '';
		}
	};

	BX.Main.UserConsent.Edit.prototype.showTab = function (type)
	{
		this.fieldListNodes.forEach(function (fieldListNode) {
			if (this.isTypeVisible(fieldListNode))
			{
				var fieldNodes = fieldListNode.querySelectorAll('[data-bx-field]');
				var listFields = BX.convert.nodeListToArray(fieldNodes);
				listFields.forEach(function (fieldNode) {
					if (fieldNode.getAttribute('data-bx-tab') === type)
					{
						fieldNode.style.display = '';
					}
					else
					{
						fieldNode.style.display = 'none';
					}
				}.bind(this));
			}
		}.bind(this));
	}

	BX.Main.UserConsent.Edit.prototype.submit = function ()
	{
		BX(this.listDomIds['formId']).submit();
	};

	BX.Main.UserConsent.Edit.prototype.sendEventMessageAboutSave = function ()
	{
		if (window.top === window)
		{
			return;
		}
		if (!window.top.BX)
		{
			return;
		}
		window.top.BX.onCustomEvent(window.top, 'main-user-consent-saved', []);
	}

	BX.Main.UserConsent.Edit.prototype.onDataProviderChange = function()
	{
		var option = this.dataProviderInputNode.options[this.dataProviderInputNode.selectedIndex];
		var data = option.getAttribute('data-bx-data');
		var editUrl = option.getAttribute('data-bx-edit-url');
		if (data)
		{
			try
			{
				data = JSON.parse(data);
			}
			catch (e)
			{
				data = null;
			}
		}

		var provider = {
			name: option.innerText
		};

		this.dataProviderUrlNode.style.display = editUrl ? '' : 'none';
		this.dataProviderUrlNode.dataset.href = editUrl;

		this.showFieldsDataProvider(data, provider);
	};

	BX.Main.UserConsent.Edit.prototype.onDataProviderUrlClick = function()
	{
		BX.SidePanel.Instance.open(this.dataProviderUrlNode.dataset.href);
	};

	BX.Main.UserConsent.Edit.prototype.onTypeChange = function()
	{
		var option = this.typeSelectorNode.options[this.typeSelectorNode.selectedIndex];
		var type = option.getAttribute('data-bx-type');
		var lang = option.getAttribute('data-bx-lang');
		var isSupportDataProviders = option.getAttribute('data-bx-supp-provider') === 'Y';

		var agreementText = option.getAttribute('data-bx-agreement-text');
		this.typeViewNode.style.display = agreementText ? '' : 'none';
		this.typeViewNode.setAttribute('data-bx-text', agreementText);

		if (type === 'C' && !lang && this.initialLang)
		{
			lang = this.initialLang;
		}

		this.typeNode.value = type;
		this.langNode.value = lang;

		this.dataProviderNode.style.display = isSupportDataProviders ? '' : 'none';

		this.fieldListNodes.forEach(function (fieldListNode) {
			fieldListNode.style.display = this.isTypeVisible(fieldListNode) ? '' : 'none';
		}.bind(this));
	};

	BX.Main.UserConsent.Edit.prototype.isTypeVisible = function(fieldListNode)
	{
		var fieldType = fieldListNode.getAttribute('data-bx-type');
		var fieldLang = fieldListNode.getAttribute('data-bx-lang');
		return (this.typeNode.value === fieldType && (!fieldLang || this.langNode.value === fieldLang));
	};

	BX.Main.UserConsent.Edit.prototype.showAgreementText = function()
	{
		var text = this.typeViewNode.getAttribute('data-bx-text');
		if (!text)
		{
			return;
		}

		if (!this.agreementViewPopup)
		{
			var node = document.createElement('TEXTAREA');
			BX.addClass(node, 'main-user-consent-edit-popup-textarea');
			node.disabled = true;
			this.agreementViewPopupContentNode = node;

			this.agreementViewPopup = BX.PopupWindowManager.create(
				'main-user-consent-edit-view-agreement',
				null,
				{
					titleBar: this.mess.viewTitle,
					content: this.agreementViewPopupContentNode,
					autoHide: true,
					lightShadow: true,
					closeByEsc: true,
					closeIcon: true,
					overlay: {backgroundColor: 'black', opacity: 500},
					buttons: [
						new BX.PopupWindowButton({
							'text': this.mess.close,
							'events': {
								'click': function ()
								{
									this.popupWindow.close();
								}
							}
						})
					]
				}
			);
		}

		this.agreementViewPopupContentNode.textContent = text;
		this.agreementViewPopup.show();
	};

	BX.Main.UserConsent.Edit.prototype.showFieldsDataProvider = function(data, provider)
	{
		var hideAttribute = 'data-bx-is-data-prov-hide';
		var changeDisplay = function (isInputVisible, field) {
			field.node.setAttribute(hideAttribute, isInputVisible ? 'N' : 'Y');
			field.view.node.style.display = !isInputVisible ? '' : 'none';
			field.toggledNode.style.display = isInputVisible ? '' : 'none';
			field.inputNode.disabled = !isInputVisible;
		};

		var fields = this.getFields(this.container);
		fields.filter(function (field) {
			return field.node.getAttribute(hideAttribute) == 'Y';
		}).forEach(changeDisplay.bind(this, true));

		if (!data)
		{
			return;
		}

		data.forEach(function (item) {
			fields.filter(function (field) {
				return item.CODE == field.code;
			}).forEach(function (field) {
				field.view.nameNode.textContent = provider.name.trim() + ':';
				field.view.valueNode.textContent = item.VALUE;
				changeDisplay(false, field);
			});
		});
	};

	BX.Main.UserConsent.Edit.prototype.getFields = function(context)
	{
		return this.getFieldNodes(context).map(function (fieldNode) {
			return {
				'code': fieldNode.getAttribute('data-bx-field'),
				'node': fieldNode,
				'inputNode': fieldNode.querySelector('[data-bx-input]'),
				'view': {
					'node': fieldNode.querySelector('[data-bx-view]'),
					'nameNode': fieldNode.querySelector('[data-bx-view-name]'),
					'valueNode': fieldNode.querySelector('[data-bx-view-value]')
				},
				'togglerNode': fieldNode.querySelector('[data-bx-toggler]'),
				'toggledNode': fieldNode.querySelector('[data-bx-toggled]')
			};
		});
	};

	BX.Main.UserConsent.Edit.prototype.getFieldNodes = function(context)
	{
		var fieldNodes = context.querySelectorAll('[data-bx-field]');
		return BX.convert.nodeListToArray(fieldNodes);
	};

})();