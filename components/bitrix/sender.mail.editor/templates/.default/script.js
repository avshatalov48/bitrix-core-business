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
		this.AIImageContextId = params.AIImageContextId;
		this.AITextContextId = params.AITextContextId;
		this.isAIImageAvailable = params.isAIImageAvailable === 'Y';
		this.isAITextAvailable = params.isAITextAvailable === 'Y';

		this.context = BX(params.containerId);
		this.blockNode = this.context.querySelector('[data-bx-editor-block]');
		this.plainNode = this.context.querySelector('[data-bx-editor-plain]');
		this.inputNode = this.plainNode.querySelector('[data-bx-input]');

		BX.Event.EventEmitter.subscribe('OnEditorInitedBefore', (event) => {
			const [currentEditor] = event.getData();

			if (this.isAITextAvailable)
			{
				currentEditor.AddButton({
					id: 'ai-text-generator',
					name: 'Create text',
					iconClassName: 'marketing-letter-editor-btn-ai-text',
					toolbarSort: 16,
					disabledForTextarea: false,
					compact: true,
					handler: () => {
						const aiTextPicker = new BX.AI.Picker({
							moduleId: 'sender',
							contextId: this.AITextContextId,
							analyticLabel: 'sender_ai_text_marketing_letter',
							history: true,
							onSelect: (info) => {
								let data = info.data;
								if (data && data.replace)
								{
									data = BX.util.htmlspecialchars(data);
									const text = (data + '').replace(/(\r\n|\n\r|\r|\n)/g, '<br/>$1');

									if (currentEditor.bbCode && currentEditor.synchro.IsFocusedOnTextarea())
									{
										currentEditor.textareaView.WrapWith(false, false, text);
										currentEditor.textareaView.Focus();
									}
									else
									{
										currentEditor.action.actions.insertHTML.exec('insertHTML', text);
										currentEditor.Focus();
									}
								}
							},
							onTariffRestriction: () => {
								// BX.UI.InfoHelper.show(`limit_sender_ai_text`);
							},
						});
						aiTextPicker.setLangSpace(BX.AI.Picker.LangSpace.text);
						aiTextPicker.text();
					},
				})
			}

			// if (this.isAIImageAvailable)
			// {
			// 	currentEditor.AddButton({
			// 		id: 'ai-image-generator',
			// 		name: 'Create Image',
			// 		iconClassName: 'marketing-letter-editor-btn-ai-image',
			// 		toolbarSort: 17,
			// 		disabledForTextarea: false,
			// 		compact: true,
			// 		handler: () => {
			// 			const aiImagePicker = new BX.AI.Picker({
			// 				moduleId: 'sender',
			// 				contextId: this.AIImageContextId,
			// 				analyticLabel: 'sender_ai_image_merketing_letter',
			// 				history: true,
			// 				onSelect: (imageUrl) => {
			// 					debugger;
			// 					// eslint-disable-next-line promise/catch-or-return
			// 					// ajax.runAction('socialnetwork.api.livefeed.blogpost.uploadAIImage', {
			// 					// 	data: {
			// 					// 		imageUrl: imageUrl,
			// 					// 	},
			// 					// }).then((response) => {
			// 					// 	const userFieldControl = BX.Disk.Uploader.UserFieldControl.getById(this.formId);
			// 					// 	const uploader: Uploader = userFieldControl.getUploader();
			// 					// 	uploader.addFile(response.data.fileId, {
			// 					// 		events: {
			// 					// 			[FileEvent.LOAD_COMPLETE]: (event) => {
			// 					// 				const file = event.getTarget();
			// 					// 				const item = userFieldControl.getItem(file.getId());
			// 					// 				userFieldControl.getMainPostForm().getParser().insertFile(item);
			// 					// 				userFieldControl.showUploaderPanel();
			// 					// 			},
			// 					// 		},
			// 					// 	});
			// 					// });
			// 				},
			// 				onTariffRestriction: () => {
			// 					// BX.UI.InfoHelper.show(`limit_sonet_ai_image`);
			// 				},
			// 			});
			// 			aiImagePicker.setLangSpace(BX.AI.Picker.LangSpace.image);
			// 			aiImagePicker.image();
			// 		},
			// 	});
			// }
		});


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

		this.placeHolders = [];
		
		this.className = 'bxhtmled-top-bar-btn bxhtmled-top-bar-choose-template';
		this.activeClassName = 'bxhtmled-top-bar-btn-active';
		this.disabledClassName = 'bxhtmled-top-bar-btn-disabled';

		editor.On('PlaceHolderSelectorButtonCreate', [this]);

		this.disabledForTextarea = false;
		this.Create();
		// this.pCont.innerHTML = this.title;

		var _this = this;
		new BX.Sender.PersonalizationSelector({
			button: this.pCont,
			fields: this.placeHolders,
			onItemClick: function (event)
			{
				event.preventDefault();
				var value = event.getData().item.getCustomData().get('property');
				if (typeof value !== 'undefined' &&
					_this.editor.action.IsSupported('insertHTML'))
				{
					_this.editor.action.Exec('insertHTML', value.id);
				}
			}
		});
	}

	buildPrototypes = function ()
	{
		PlaceHolderSelectorButton.prototype.buildPlaceHolders = BX.DoNothing;
		PlaceHolderSelectorButton.prototype.Check = BX.DoNothing;
		PlaceHolderSelectorButton.prototype.GetValue = BX.DoNothing;
		PlaceHolderSelectorButton.prototype.SetValue = BX.DoNothing;
		PlaceHolderSelectorButton.prototype.OnMouseUp = BX.DoNothing;
		PlaceHolderSelectorButton.prototype.OnMouseDown = BX.DoNothing;

		PlaceHolderSelectorButton.prototype.OnClick = BX.DoNothing;
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
