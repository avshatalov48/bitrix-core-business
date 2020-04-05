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

		BX.extend(PlaceHolderSelectorList, window.BXHtmlEditor.DropDownList);
		window.BXHtmlEditor.Controls['placeholder_selector'] = PlaceHolderSelectorList;

		BX.addCustomEvent(
			editor,
			"PlaceHolderSelectorListCreate",
			this.onPlaceHolderSelectorListCreate.bind(this)
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
	Editor.prototype.onPlaceHolderSelectorListCreate = function (placeHolderSelectorList)
	{
		placeHolderSelectorList.placeHolders = this.placeHolders;
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


	function PlaceHolderSelectorList(editor, wrap)
	{
		var title = BX.Sender.Mail.Editor.mess.placeHolderTitle;
		// Call parent constructor
		PlaceHolderSelectorList.superclass.constructor.apply(this, arguments);
		this.id = 'placeholder_selector';
		this.title = title;
		this.action = 'insertHTML';
		this.zIndex = 3008;

		this.placeHolders = [];
		editor.On('PlaceHolderSelectorListCreate', [this]);

		this.disabledForTextarea = false;
		this.arValues = [];

		for (var i in this.placeHolders)
		{
			var value = this.placeHolders[i];
			value.value = '#' + value.CODE + '#';
			this.arValues.push(
				{
					id: value.CODE,
					name: value.NAME,
					topName: title,
					title: value.value + ' - ' + value.DESC,
					className: '',
					style: '',
					action: 'insertHTML',
					value: value.value
				}
			);
		}

		this.Create();
		this.pCont.innerHTML = title;

		if (wrap)
		{
			wrap.appendChild(this.GetCont());
		}
	}

	setTimeout(function () {
		if (window.BXHtmlEditor)
		{
			BX.extend(PlaceHolderSelectorList, window.BXHtmlEditor.DropDownList);
			window.BXHtmlEditor.Controls['placeholder_selector'] = PlaceHolderSelectorList;
		}
	}, 300);

	BX.Sender.Mail.Editor = new Editor();


	if (BX.Sender.Message.Editor.setAdaptedInstance)
	{
		BX.Sender.Message.Editor.setAdaptedInstance(BX.Sender.Mail.Editor);
	}

})(window);