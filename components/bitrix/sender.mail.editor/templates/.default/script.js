;(function (window)
{
	BX.namespace('BX.Sender.Mail');
	if (BX.Sender.Mail.Editor)
	{
		return;
	}


	var Helper = {
		changeDisplay: function (node, isShow)
		{
			if (!node)
			{
				return;
			}

			node.style.display = isShow ? '' : 'none';
		}
	};

	/**
	 * Editor.
	 *
	 */
	function Editor()
	{
	}
	Editor.prototype.init = function (params)
	{
		this.id = params.id;
		this.input = BX(params.inputId);
		this.placeHolders = params.placeHolders;
		this.mess = params.mess;

		this.context = BX(params.containerId);
		this.blockNode = this.context.querySelector('[data-bx-editor-block]');
		this.plainNode = this.context.querySelector('[data-bx-editor-plain]');
		this.inputNode = this.plainNode.querySelector('[data-bx-input]');


		BX.addCustomEvent('OnEditorInitedBefore', this.onEditorInitedBefore.bind(this));
		BX.addCustomEvent('OnEditorInitedAfter', this.onEditorInitedAfter.bind(this));

		if (BX.Sender.Template && BX.Sender.Template.Selector)
		{
			var selector = BX.Sender.Template.Selector;
			BX.addCustomEvent(selector, selector.events.templateSelect, this.onTemplateSelect.bind(this));
		}
	};
	Editor.prototype.onTemplateSelect = function (template)
	{
		var isOnDemand = template.messageFields.some(function (field) {
			return (field.code === 'MESSAGE' && field.onDemand);
		});
		if (isOnDemand)
		{
			var uri = BX.Sender.Template.Selector.getTemplateRequestingUri(template);
			this.setTemplateUri(uri);
		}
		else
		{
			template.messageFields.forEach(function (field) {
				if(field.code !== 'MESSAGE' )
				{
					return;
				}
				this.setContent(field.value);
			}, this);
		}
	};
	Editor.prototype.isTargetEditor = function (editor)
	{
		if (!editor)
		{
			return false;
		}

		return editor.id.indexOf('BX_BLOCK_EDITOR_CONTENT') === 0;
	};
	Editor.prototype.onEditorInitedAfter = function (editor)
	{
		if (!this.isTargetEditor(editor))
		{
			return;
		}

		editor.components.SetComponentIcludeMethod('EventMessageThemeCompiler::includeComponent');
	};
	Editor.prototype.onEditorInitedBefore = function (editor)
	{
		if (!this.isTargetEditor(editor))
		{
			return;
		}

		BX.extend(PlaceHolderSelectorButton, window.BXHtmlEditor.Button);
		window.BXHtmlEditor.Controls['placeholder_selector'] = PlaceHolderSelectorButton;
		buildPrototypes();

		//
		BX.addCustomEvent(
			editor,
			"PlaceHolderSelectorButtonCreate",
			this.onPlaceHolderSelectorButtonCreate.bind(this)
		);

		BX.addCustomEvent(
			editor,
			"GetControlsMap",
			this.onGetControlsMap.bind(this)
		);
	};
	Editor.prototype.onGetControlsMap = function (controlsMap)
	{
		controlsMap.push({
			id: 'placeholder_selector',
			compact: true,
			hidden: false,
			sort: 1,
			checkWidth: false,
			offsetWidth: 32
		});
	};
	Editor.prototype.onPlaceHolderSelectorButtonCreate = function (PlaceHolderSelectorButton)
	{
		PlaceHolderSelectorButton.placeHolders = this.placeHolders;
	};
	Editor.prototype.isSupportedTemplateUri = function ()
	{
		return true;
	};
	Editor.prototype.setTemplateUri = function(uri)
	{
		if (this.input.value && !this.isShowedBlock() && !this.confirmTemplateChange())
		{
			return;
		}

		BX.BlockEditorManager.get(this.id).load(uri);
		this.switchView(true);
	};
	Editor.prototype.isShowedBlock = function()
	{
		return this.blockNode.style.display !== 'none';
	};
	Editor.prototype.confirmTemplateChange = function()
	{
		return confirm(this.mess.changeTemplate);
	};
	Editor.prototype.switchView = function(isShowBlock)
	{
		Helper.changeDisplay(this.blockNode, isShowBlock);
		Helper.changeDisplay(this.plainNode, !isShowBlock);
		BX.BlockEditorManager.get(this.id).resultNode = isShowBlock ? this.inputNode : null;
	};
	Editor.prototype.setContent = function(content)
	{
		if (this.isShowedBlock() && !this.confirmTemplateChange())
		{
			return;
		}

		this.inputNode.value = content;
		this.switchView(false);
	};


	PlaceHolderSelectorButton = function(editor, wrap)
	{
		// Call parent constructor
		PlaceHolderSelectorButton.superclass.constructor.apply(this, arguments);
		this.id = 'placeholder_selector';
		this.title = '\#';
		// this.action = 'insertHTML';
		this.placeHolders = [];
		
		this.className = 'bxhtmled-top-bar-btn';
		this.activeClassName = 'bxhtmled-top-bar-btn-active';
		this.disabledClassName = 'bxhtmled-top-bar-btn-disabled';

		editor.On('PlaceHolderSelectorButtonCreate', [this]);

		this.disabledForTextarea = false;
		this.arValues = [];

		for (var i in this.placeHolders)
		{
			var value = this.placeHolders[i];
			this.arValues.push(this.buildPlaceHolders(value));
		}

		this.Create();
		this.pCont.innerHTML = this.title;

		this.menu = new BX.Main.Menu({
			id: this.id,
			bindElement: this.GetCont(),
			items: this.arValues,
			maxHeight: 300
		});

		this.menuItem = new BX.Main.MenuItem();
		BX.bind(this.pCont, 'click', BX.proxy(this.OnClick, this));
		if (wrap)
		{
			wrap.appendChild(this.GetCont());
		}
	}

	buildPrototypes = function () {
		PlaceHolderSelectorButton.prototype.buildPlaceHolders = function(placeHolder, title) {
				var _this = this;
				var value = {
					text: placeHolder.NAME,
					topName: title,
					title: placeHolder.DESC,
					className: '',
					style: '',
					action: 'insertHTML',
				};

				if (typeof placeHolder.ITEMS !== 'undefined')
				{
					value['items'] = [];
					value['className'] = 'bxhtmled-style-heading-more';

					for (var i in placeHolder.ITEMS)
					{
						value['items'].push(this.buildPlaceHolders(placeHolder.ITEMS[i]))
					}
				}

				if (typeof value['items'] === 'undefined')
				{
					value['dataset'] = {
						value: '#' + placeHolder.CODE + '#'
					};
					value['onclick'] = function(event, item)
					{
						if (typeof item.dataset.value !== 'undefined' &&
							_this.editor.action.IsSupported('insertHTML'))
						{
							_this.editor.action.Exec('insertHTML', item.dataset.value);
							_this.menu.close();
						}
					};
				}
				else
				{
					value['id'] = placeHolder.CODE;
				}

				return value;
			};

			PlaceHolderSelectorButton.prototype.Check= BX.DoNothing;
			PlaceHolderSelectorButton.prototype.GetValue= BX.DoNothing;
			PlaceHolderSelectorButton.prototype.SetValue= BX.DoNothing;
			PlaceHolderSelectorButton.prototype.OnMouseUp= BX.DoNothing;
			PlaceHolderSelectorButton.prototype.OnMouseDown= BX.DoNothing;

			PlaceHolderSelectorButton.prototype.OnClick = function()
			{
				this.menu.show();
			}
	}

	setTimeout(function () {
		if (window.BXHtmlEditor)
		{
			BX.extend(PlaceHolderSelectorButton, window.BXHtmlEditor.Button);
			window.BXHtmlEditor.Controls['placeholder_selector'] = PlaceHolderSelectorButton;
		}
	}, 300);

	BX.Sender.Mail.Editor = new Editor();

	if (BX.Sender.Message.Editor.setAdaptedInstance)
	{
		BX.Sender.Message.Editor.setAdaptedInstance(BX.Sender.Mail.Editor);
	}

})(window);