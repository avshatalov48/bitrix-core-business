;(function (window)
{

	BX.namespace('BX.Sender');
	if (BX.Sender.Letter)
	{
		return;
	}

	var Page = BX.Sender.Page;
	var Helper = BX.Sender.Helper;

	/**
	 * Letter.
	 *
	 */
	function Letter()
	{
		this.context = null;
	}
	Letter.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.actionUri = params.actionUri;
		this.isFrame = params.isFrame || false;
		this.prettyDateFormat = params.prettyDateFormat;
		this.isSaved = params.isSaved || false;
		this.isOutside = params.isOutside || false;
		this.mess = params.mess;
		this.letterTile = params.letterTile || {};
		this.hasBottomTextareaPanel = params.hasBottomTextareaPanel;
		this.AITextContextId = params.AITextContextId;
		this.isAITextAvailable = params.isAITextAvailable === 'Y';

		this.templateChangeButton = BX('SENDER_LETTER_BUTTON_CHANGE');
		this.selectorNode = Helper.getNode('template-selector', this.context);
		this.editorNode = Helper.getNode('letter-editor', this.context);
		this.titleNode = Helper.getNode('letter-title', this.context);
		this.buttonsNode = Helper.getNode('letter-buttons', this.context);

		this.templateNameNode = Helper.getNode('template-name', this.editorNode);
		this.templateTypeNode = Helper.getNode('template-type', this.editorNode);
		this.templateIdNode = Helper.getNode('template-id', this.editorNode);
		this.consentPreviewNodes = Helper.getNodes('consent-preview', this.editorNode);

		if (BX.Sender.Template && BX.Sender.Template.Selector)
		{
			var selector = BX.Sender.Template.Selector;
			BX.addCustomEvent(selector, selector.events.templateSelect, this.onTemplateSelect.bind(this));
			BX.addCustomEvent(selector, selector.events.selectorClose, this.closeTemplateSelector.bind(this));
		}

		if (this.templateChangeButton)
		{
			BX.bind(this.templateChangeButton, 'click', this.showTemplateSelector.bind(this));
		}

		if(this.consentPreviewNodes)
		{
			this.consentPreviewNodes.forEach((function(element) {
				BX.bind(element, 'click', this.showConsentPreview.bind(this));
			}).bind(this));
		}

		if (this.isFrame)
		{
			Helper.titleEditor.init({
				dataNode: this.titleNode,
				disabled: params.isTemplateShowed,
				defaultTitle: this.getPatternTitle(this.mess.name)
			});

			BX.addCustomEvent("SidePanel.Slider:onClose", this.onPopupClose.bind(this));
		}

		Page.initButtons();

		if (this.isFrame && this.isSaved)
		{
			top.BX.onCustomEvent(top, 'sender-letter-edit-change', [this.letterTile]);
			BX.Sender.Page.slider.close();

			if (this.isOutside)
			{
				BX.UI.Notification.Center.notify({
					content: this.mess.outsideSaveSuccess,
					autoHideDelay: 5000
				});
			}
		}

		if (this.isMSBrowser())
		{
			this.context.classList.add('bx-sender-letter-ms-ie');
		}

		if (this.hasBottomTextareaPanel)
		{
			this.createBottomTextareaPanel();
		}
	};
	Letter.prototype.createBottomTextareaPanel = function() {
		this.configurationMessageInput = document.getElementById('CONFIGURATION_COMMENT');
		if (this.configurationMessageInput)
		{
			const bottomPanel = BX.create('div', {
				'props': {
					'className': 'sender-letter-textarea-bottom-panel'
				},
				'children': [
					BX.create(
						'span',
						{
							'attrs': {
								'data-bx-sms-panel-tools-button': 'ai-text',
								'class': 'sender-letter-text-editor-panel-tools-item sender-letter-text-editor-panel-tools-ai-text',
							},
						}
					),
				],
			});
			this.configurationMessageInput.classList.add('sender-letter-text-editor-configuration-message-with-panel')
			const parentNode = this.configurationMessageInput.parentNode;
			parentNode.classList.add('sender-letter-text-editor-message-wrap');
			parentNode.appendChild(bottomPanel);
			this.initPanelToolsButtons();
		}
	}
	Letter.prototype.initPanelToolsButtons = function() {
		if (this.isAITextAvailable)
		{
			const aiTextButton = this.context.querySelector('[data-bx-sms-panel-tools-button="ai-text"]');
			aiTextButton.addEventListener('click', () => {
				const aiTextPicker = new BX.AI.Picker({
					moduleId: 'sender',
					contextId: this.AITextContextId,
					analyticLabel: 'sender_letter_ai_text',
					history: true,
					onSelect: (info) => {
						const text = info.data;
						this.configurationMessageInput.value = this.configurationMessageInput.value + text;
					},
					onTariffRestriction: () => {
						// BX.UI.InfoHelper.show(`limit_sender_ai_image`);
					},
				});
				aiTextPicker.setLangSpace(BX.AI.Picker.LangSpace.text);
				aiTextPicker.text();
			});
		}
	}
	Letter.prototype.onPopupClose = function(event) {
		var slider = event.getSlider();
		var _this = this;

		if(!this.isSaved)
		{
			self.popupWindow = BX.PopupWindowManager.create(
				'sender-letter-on-slider-close',
				null,
				{
					content: this.mess.applyClose,
					titleBar: this.mess.applyCloseTitle,
					width: 400,
					height: 200,
					padding: 10,
					closeByEsc: true,
					contentColor: 'white',
					angle: false,
					buttons: [
						new BX.PopupWindowButton({
							text: this.mess.applyYes,
							className: "popup-window-button-accept",
							events: {
								click: function() {
									BX.removeCustomEvent("SidePanel.Slider::onClose", _this.onPopupClose);
									event.allowAction();
									slider.close();
									setTimeout(function() {
										slider.destroy();
									}, 500);
								}
							}
						}),
						new BX.PopupWindowButton({
							text: this.mess.applyCancel,
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

	Letter.prototype.isMSBrowser = function ()
	{
		return window.navigator.userAgent.match(/(Trident\/|MSIE|Edge\/)/) !== null;
	};
	Letter.prototype.getPatternTitle = function (name)
	{
		return Helper.replace(
			this.mess.patternTitle,
			{
				'name': name,
				'date': BX.date.format(this.prettyDateFormat)
			}
		);
	};
	Letter.prototype.onTemplateSelect = function (template)
	{
		if (this.templateNameNode)
		{
			this.templateNameNode.textContent = template.name;
		}
		if (this.templateTypeNode)
		{
			this.templateTypeNode.value = template.type;
		}
		if (this.templateIdNode)
		{
			this.templateIdNode.value = template.code;
		}

		if (template.dispatch)
		{
			Helper.getNodes('dispatch', this.context).forEach(function (node) {
				var code = node.getAttribute('data-code');
				if (template.dispatch[code])
				{
					node.value = template.dispatch[code];
				}
			});
		}

		this.titleNode.value = this.getPatternTitle(template.name);
		BX.fireEvent(this.titleNode, 'change');

		this.closeTemplateSelector();
		window.scrollTo(0,0);
	};
	Letter.prototype.closeTemplateSelector = function ()
	{
		this.changeDisplayingTemplateSelector(false);
	};
	Letter.prototype.showTemplateSelector = function ()
	{
		this.changeDisplayingTemplateSelector(true);
	};
	Letter.prototype.showConsentPreview = function (event)
	{
		event.preventDefault();
		var element = event.target;

		var consent = document.getElementsByName(element.dataset.bxInputName)[0];
		var consentId = consent.value;
		BX.Sender.ConsentPreview.open(consentId);
	};
	Letter.prototype.changeDisplayingTemplateSelector = function (isShow)
	{
		var classShow = 'bx-sender-letter-show';
		var classHide = 'bx-sender-letter-hide';
		Helper.changeClass(this.selectorNode, classShow, isShow);
		Helper.changeClass(this.selectorNode, classHide, !isShow);

		Helper.changeClass(this.editorNode, classShow, !isShow);
		Helper.changeClass(this.editorNode, classHide, isShow);

		Helper.changeDisplay(this.templateChangeButton, !isShow);
		Helper.changeDisplay(this.buttonsNode, !isShow);

		isShow ? Helper.titleEditor.disable() : Helper.titleEditor.enable();
	};
	Letter.prototype.applyChanges = function()
	{
		var form = this.context.getElementsByTagName('form');
		if (form && form[0])
		{
			form[0].appendChild(BX.create('input', {
				attrs: {
					type: "hidden",
					name: "apply",
					value: "Y"
				}
			}));
		}
	};

	BX.Sender.Letter = new Letter();

})(window);
