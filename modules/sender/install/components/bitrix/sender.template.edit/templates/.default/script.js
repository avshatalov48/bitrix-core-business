;(function ()
{

	BX.namespace('BX.Sender.Message');
	if (BX.Sender.Message.Editor)
	{
		return;
	}

	const Page = BX.Sender.Page;
	const Helper = BX.Sender.Helper;

	/**
	 * Editor.
	 *
	 */
	function Editor()
	{
		this.context = null;
		this.editor = null;
	}

	Editor.prototype.init = function(params)
	{
		this.toolbarId = params.toolbarId;
		this.isFrame = params.isFrame || false;
		this.isSaved = params.isSaved || false;
		this.prettyDateFormat = params.prettyDateFormat;
		this.mess = params.mess || {};

		this.context = BX(params.containerId);
		this.selectorNode = this.context.querySelector('[data-bx-selector]');
		this.editorNode = this.context.querySelector('[data-bx-editor]');
		this.currTemplateNode = this.editorNode.querySelector('[data-bx-curr-templ]');
		this.changeTemplateBtnNode = this.editorNode.querySelector('[data-bx-change-btn]');
		this.titleNode = Helper.getNode('templates-title', this.context);
		this.uiToolbar = BX.UI.ToolbarManager.get(this.toolbarId);

		this.initUi();
		this.bindNodes();
		Page.initButtons();
	};

	Editor.prototype.initUi = function()
	{
		this.ui = { title: this.titleNode };
	};

	Editor.prototype.setAdaptedInstance = function(editor)
	{
		this.editor = editor;
	};

	Editor.prototype.bindNodes = function()
	{
		if (this.uiToolbar && this.isFrame)
		{
			this.uiToolbar.subscribe(BX.UI.ToolbarEvents.finishEditing, (event) => {
				const updatedTitle = event.getData().updatedTitle;

				if (updatedTitle && this.titleNode)
				{
					this.titleNode.value = updatedTitle;
				}
			});
		}

		if (BX.Sender.Template && BX.Sender.Template.Selector)
		{
			const selector = BX.Sender.Template.Selector;
			BX.addCustomEvent(selector, selector.events.templateSelect, this.onTemplateSelect.bind(this));
			BX.addCustomEvent(selector, selector.events.selectorClose, this.closeTemplateSelector.bind(this));
		}

		BX.bind(this.changeTemplateBtnNode, 'click', this.showTemplateSelector.bind(this));

		if (!this.ui.title.value.trim())
		{
			this.ui.title.value = Helper.replace(
				this.mess.patternTitle,
				{
					name: this.mess.newTitle,
					date: BX.date.format(this.prettyDateFormat),
				},
			);
		}

		if (this.isFrame && this.isSaved)
		{
			Page.slider.close();
		}
	};

	Editor.prototype.onTemplateSelect = function(template)
	{
		this.closeTemplateSelector();

		if (this.currTemplateNode)
		{
			this.currTemplateNode.innerText = template.name;
		}
	};

	Editor.prototype.closeTemplateSelector = function()
	{
		Helper.changeDisplay(this.selectorNode, false);
		Helper.changeDisplay(this.editorNode, true);
	};

	Editor.prototype.showTemplateSelector = function()
	{
		Helper.changeDisplay(this.selectorNode, true);
		Helper.changeDisplay(this.editorNode, false);
	};

	Editor.prototype.setTemplate = function(template)
	{
		if (!this.editor)
		{
			return;
		}

		if (this.editor.isSupportedTemplateUri && this.editor.isSupportedTemplateUri())
		{
			const uri = BX.Sender.Template.Selector.getTemplateRequestingUri(template);
			this.editor.setTemplateUri(uri);
		}
		else
		{
			BX.Sender.Template.Selector.getTemplate(template, this.editor.setContent.bind(this.editor));
		}
	};

	BX.Sender.Message.Editor = new Editor();
})(window);
