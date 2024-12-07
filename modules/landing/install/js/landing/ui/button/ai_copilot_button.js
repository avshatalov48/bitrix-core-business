(function() {
	'use strict';

	BX.namespace('BX.Landing.UI.Button');

	const COPILOT_WIDTH = 980;
	const COPILOT_INPUT_HEIGHT = 42;
	const COPILOT_PADDING = 4;
	const EDITOR_HEADER_HEIGHT = 66;

	/**
	 * Implements interface for works with AI (text) button.
	 *
	 * @extends {BX.Landing.UI.Button.EditorAction}
	 *
	 * @param {string} id
	 * @param {object} options
	 * @constructor
	 */
	BX.Landing.UI.Button.AiCopilot = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.options = options;
		this.onReplace = options.onReplace;
		this.onReplaceContext = options.onReplaceContext;
		this.onAddBelow = options.onAddBelow;
		this.editor = options.editor;
		this.clientHeight = document.documentElement.clientHeight;
		this.clientWidth = document.documentElement.clientWidth;
	};

	BX.Landing.UI.Button.AiCopilot.getInstance = function(id, options)
	{
		if (!BX.Landing.UI.Button.AiCopilot.instance)
		{
			BX.Landing.UI.Button.AiCopilot.instance = new BX.Landing.UI.Button.AiCopilot(id, options);
		}

		return BX.Landing.UI.Button.AiCopilot.instance;
	};

	BX.Landing.UI.Button.AiCopilot.prototype = {
		constructor: BX.Landing.UI.Button.AiCopilot,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick()
		{
			if (
				!BX.Landing.Env.getInstance().getOptions()["ai_text_active"]
				&& BX.Landing.Main.getInstance()["options"]["ai_unactive_info_code"]
			)
			{
				BX.UI.InfoHelper.show(BX.Landing.Main.getInstance()["options"]["ai_unactive_info_code"]);

				return;
			}
			
			if (!this.isInit)
			{
				BX.Dom.addClass(this.layout, 'active');
				this.clickBeforeInit = true;
			}

			this.context = ' ';
			const selectedText = window.getSelection().toString();
			if (selectedText !== '')
			{
				this.context = selectedText;
				this.selectedContext = true;
			}

			if (this.context === ' ' && this.editor.currentElement)
			{
				const fieldInput = this.editor.currentElement.querySelector('.landing-ui-field-input');
				if (fieldInput)
				{
					this.context = fieldInput.innerHTML;
				}
				else
				{
					this.context = this.editor.currentElement.innerHTML;
				}
			}

			const currentElement = this.editor.currentElement;
			if (this.copilot && this.currentElement === this.editor.currentElement)
			{
				this.hideEditorPanel(true);
				this.copilot.setSelectedText(this.context);
				this.copilot.show({
					currentElement,
					width: COPILOT_WIDTH,
				});

				return;
			}
			this.currentElement = this.editor.currentElement;
			if (this.isInit)
			{
				this.hideEditorPanel(true);
				this.copilot.setSelectedText(this.context);
				this.copilot.show({
					currentElement,
					width: COPILOT_WIDTH,
				});
			}
		},

		onMouseOver()
		{
			if (!this.copilot)
			{
				const copilot = top.BX.AI ? top.BX.AI.Copilot : BX.AI.Copilot;
				this.copilot = new copilot({
					moduleId: 'landing',
					contextId: this.getContext(),
					category: 'landing',
				});
				this.copilot.subscribe('finish-init', this.finishInitHandler.bind(this));
				this.copilot.subscribe('save', this.saveHandler.bind(this));
				this.copilot.subscribe('add_below', this.addBelowHandler.bind(this));
				BX.Event.EventEmitter.subscribe('BX.Landing.Node.Text:onMousedown', this.onClickHandler.bind(this));
				BX.Event.EventEmitter.subscribe('BX.Landing.Node.Img:onClick', this.onClickHandler.bind(this));
				BX.Event.EventEmitter.subscribe('BX.Landing.Node.Icon:onClick', this.onClickHandler.bind(this));
				BX.Event.EventEmitter.subscribe('BX.Landing.UI.Panel.ContentEdit:onClick', this.onClickHandler.bind(this));
				BX.Event.bind(document, 'keydown', this.onWindowKeyDownHandler.bind(this));
				BX.Event.bind(document, 'click', this.onClickHandler.bind(this));
				BX.Event.bind(document, 'scroll', this.onScrollHandler.bind(this));
				this.copilot.init();
			}
		},

		finishInitHandler()
		{
			this.copilot.setSelectedText(this.context);
			this.copilotPositionTop = (this.clientHeight - COPILOT_INPUT_HEIGHT) / 2;
			this.copilotPositionLeft = (this.clientWidth - COPILOT_WIDTH) / 2;
			this.isInit = true;
			if (this.clickBeforeInit === true)
			{
				this.hideEditorPanel(true);
				const currentElement = this.editor.currentElement;

				this.copilot.show({
					currentElement,
					width: COPILOT_WIDTH,
				});
				BX.Dom.removeClass(this.layout, 'active');
			}
		},

		saveHandler(event)
		{
			if (this.selectedContext === true)
			{
				this.onReplaceContext(event.data.result);
			}
			else
			{
				this.onReplace(event.data.result);
			}

			if (this.copilot.isShown())
			{
				this.copilot.hide();
			}
		},

		addBelowHandler(event)
		{
			this.onAddBelow(event.data.result);
			if (this.copilot.isShown())
			{
				this.copilot.hide();
			}
		},

		onWindowKeyDownHandler(event)
		{
			if (event.key === 'Escape' && this.copilot.isShown())
			{
				this.copilot.hide();
			}
		},

		onClickHandler()
		{
			if (this.copilot.isShown())
			{
				this.copilot.hide();
			}
		},

		onScrollHandler()
		{
			const currentScrollY = window.scrollY;
			if (!this.lastScrollY)
			{
				this.lastScrollY = currentScrollY;
			}

			const diffScroll = currentScrollY - this.lastScrollY;
			this.lastScrollY += diffScroll;
			this.copilotPositionTop -= diffScroll;
			let top = this.copilotPositionTop;
			if (this.copilotPositionTop < EDITOR_HEADER_HEIGHT + COPILOT_PADDING)
			{
				top = EDITOR_HEADER_HEIGHT + COPILOT_PADDING;
			}

			const maxAllowedHeight = this.clientHeight + EDITOR_HEADER_HEIGHT - (COPILOT_INPUT_HEIGHT + COPILOT_PADDING);
			if (this.copilotPositionTop > maxAllowedHeight)
			{
				top = maxAllowedHeight;
			}

			this.copilot.adjust(
				{
					position: {
						top,
						left: this.copilotPositionLeft,
					},
				},
			);
		},

		getContext()
		{
			if (this.editor.currentElement)
			{
				return 'editor';
			}

			return 'edit_block_slider';
		},

		hideEditorPanel(strictMode)
		{
			if (strictMode)
			{
				const fieldInput = this.editor.currentElement.querySelector('.landing-ui-field-input');
				if (!fieldInput)
				{
					BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				}
			}
			else
			{
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			}
		},
	};
})();
