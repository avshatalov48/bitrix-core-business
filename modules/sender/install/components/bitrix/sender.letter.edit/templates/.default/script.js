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
		this.isFrame = params.isFrame || false;
		this.isSaved = params.isSaved || false;
		this.mess = params.mess;
		this.letterTile = params.letterTile || {};

		this.templateChangeButton = BX('SENDER_LETTER_BUTTON_CHANGE');
		this.selectorNode = Helper.getNode('template-selector', this.context);
		this.editorNode = Helper.getNode('letter-editor', this.context);
		this.titleNode = Helper.getNode('letter-title', this.context);
		this.buttonsNode = Helper.getNode('letter-buttons', this.context);

		this.templateNameNode = Helper.getNode('template-name', this.editorNode);
		this.templateTypeNode = Helper.getNode('template-type', this.editorNode);
		this.templateIdNode = Helper.getNode('template-id', this.editorNode);

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

		if (this.isFrame)
		{
			Helper.titleEditor.init({
				dataNode: this.titleNode,
				disabled: params.isTemplateShowed,
				defaultTitle: this.getPatternTitle(this.mess.name)
			});
		}

		Page.initButtons();

		if (this.isFrame && this.isSaved)
		{
			top.BX.onCustomEvent(top, 'sender-letter-edit-change', [this.letterTile]);
			BX.Sender.Page.slider.close();
		}
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

	BX.Sender.Letter = new Letter();

})(window);