var MainUserConsentEditManager = function(params)
{
	this.init = function (params)
	{
		this.mess = params.mess;

		var container = BX('USER_CONSENT_CONTAINER');
		this.container = container;

		this.typeNode = container.querySelector('[data-bx-type-input]');
		this.langNode = container.querySelector('[data-bx-lang-input]');

		this.typeSelectorNode = container.querySelector('[data-bx-type-selector]');
		this.typeViewNode = container.querySelector('[data-bx-type-view]');
		this.dataProviderNode = container.querySelector('[data-bx-data-provider]');
		this.dataProviderInputNode = container.querySelector('[data-bx-data-provider-input]');
		this.dataProviderUrlNode = container.querySelector('[data-bx-data-provider-url]');
		this.fieldListNodes = container.querySelectorAll('[data-bx-fields]');
		this.fieldListNodes = BX.convert.nodeListToArray(this.fieldListNodes);

		BX.bind(this.typeViewNode, 'click', this.showAgreementText.bind(this));

		BX.bind(this.typeSelectorNode, 'change', this.onTypeChange.bind(this));
		BX.fireEvent(this.typeSelectorNode, 'change');

		BX.bind(this.dataProviderInputNode, 'change', this.onDataProviderChange.bind(this));
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

	};

	this.onDataProviderChange = function()
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
		this.dataProviderUrlNode.href = editUrl;

		this.showFieldsDataProvider(data, provider);
	};

	this.onTypeChange = function()
	{
		var option = this.typeSelectorNode.options[this.typeSelectorNode.selectedIndex];
		var type = option.getAttribute('data-bx-type');
		var lang = option.getAttribute('data-bx-lang');
		var isSupportDataProviders = option.getAttribute('data-bx-supp-provider') == 'Y';

		var agreementText = option.getAttribute('data-bx-agreement-text');
		this.typeViewNode.style.display = agreementText ? '' : 'none';
		this.typeViewNode.setAttribute('data-bx-text', agreementText);

		this.typeNode.value = type;
		this.langNode.value = lang;

		this.dataProviderNode.style.display = isSupportDataProviders ? '' : 'none';

		this.fieldListNodes.forEach(function (fieldListNode) {
			var fieldType = fieldListNode.getAttribute('data-bx-type');
			var fieldLang = fieldListNode.getAttribute('data-bx-lang');
			var isVisible = type == fieldType && lang == fieldLang;
			fieldListNode.style.display = isVisible ? '' : 'none';
		});
	};

	this.showAgreementText = function()
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

	this.showFieldsDataProvider = function(data, provider)
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

	this.getFields = function(context)
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

	this.getFieldNodes = function(context)
	{
		var fieldNodes = context.querySelectorAll('[data-bx-field]');
		return BX.convert.nodeListToArray(fieldNodes);
	};

	this.init(params);
};