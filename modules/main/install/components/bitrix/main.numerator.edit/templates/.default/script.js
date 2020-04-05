;(function ()
{
	BX.namespace('BX.Numerator');
	BX.Numerator = function (options)
	{
		this.isSlider = options.isSlider;
		this.isEdit = options.isEdit;
		this.isMultipleSequences = options.isMultipleSequences;
		this.defaultDelimiter = options.defaultDelimiter;
		this.roles = {
			templateInput: "numerator-template-input",
			btnSave: "btn-save",
			btnCancel: "btn-cancel",
			form: "numerator-edit-form",
			wordBtn: "numerator-template-word-btn",
			wordText: "template-word-text",
			timezoneToggle: "numerator-timezoneToggle",
			showSetNextNumberToggle: "numerator-set-next-number-toggle",
			timezones: "numerator-timezone",
			sequenceBlock: "nextNumberForSequence-wrapper",
			nameInput: "numerator-name-input",
			error: 'numerator-error',
			startBlockWrapper: 'start-wrapper',
			periodSelect: "numerator-periodicBy-select",
			timezoneSelect: "numerator-timezone-select",
			wordBtnWrapper: "numerator-edit-word-btn-wrapper"
		};
		this.templateInput = this.selectByRoles(this.roles.templateInput);
		this.helpArticleToggles = this.selectByRoles('help-article-toggle', 'all');
		this.wordBtnWrapper = this.selectByRoles(this.roles.wordBtnWrapper);
		this.startBlockWrapper = this.selectByRoles(this.roles.startBlockWrapper);
		this.hiddenTemplateInput = this.selectByRoles('numerator-hidden-template-input');
		this.periodSelect = this.selectByRoles(this.roles.periodSelect);
		this.timezoneSelect = this.selectByRoles(this.roles.timezoneSelect);
		this.timezoneToggle = this.selectByRoles(this.roles.timezoneToggle);
		this.showSetNextNumberToggle = this.selectByRoles(this.roles.showSetNextNumberToggle);
		this.timezones = this.selectByRoles(this.roles.timezones);
		this.sequenceBlock = this.selectByRoles(this.roles.sequenceBlock);
		this.saveButton = this.selectByRoles(this.roles.btnSave);
		this.cancelButton = this.selectByRoles(this.roles.btnCancel);
		this.numeratorForm = this.selectByRoles(this.roles.form);
		this.nameInput = this.selectByRoles(this.roles.nameInput);
		this.settingsBlock = {};
		this.errors = [];
		this.errorMessages = options && options.errors ? options.errors : {};
		this.wordButtons = this.selectByRoles(this.roles.wordBtn, 'all');
		for (var k = 0; k < this.wordButtons.length; k++)
		{
			var settingsBlock = this.selectByRoles('settings-type-' + this.wordButtons[k].dataset['type']);
			if (settingsBlock)
			{
				this.settingsBlock[this.wordButtons[k].dataset['type']] = settingsBlock;
			}
		}
		this.hideInitial();

		this.addEventHandlers();
		this.fillTemplate();
		this.updateTemplateHiddenInput();
		BX.UI.Hint.init(this.selectByRoles('numerator-container'));
	};
	BX.Numerator.prototype = {
		addEventHandlers: function ()
		{
			BX.bind(this.saveButton, 'click', BX.delegate(this.onSaveClick, this));
			BX.bind(this.cancelButton, 'click', BX.delegate(this.onCancelClick, this));
			BX.bind(this.templateInput, 'keyup', BX.delegate(this.handleKeyPress, this));
			for (var i = 0; i < this.wordButtons.length; i++)
			{
				BX.bind(this.wordButtons[i], 'click', BX.delegate(this.onTemplateWordBtnClick, this));
			}
			BX.bind(this.periodSelect, 'change', BX.delegate(this.onPeriodOptionClick, this));
			BX.bind(this.timezoneToggle, 'click', BX.delegate(this.onTimezoneToggleClick, this));
			BX.bind(this.showSetNextNumberToggle, 'click', BX.delegate(this.onShowSetNextNumberToggleClick, this));
			for (var i = 0; i < this.wordButtons.length; i++)
			{
				BX.bind(this.helpArticleToggles[i], 'click', BX.delegate(this.onHelpArticleToggleClick, this));
			}
		},
		hideInitial: function ()
		{
			this.hideElement(this.timezones);
			if (this.sequenceBlock)
			{
				this.hideElement(this.sequenceBlock);
			}
			if (!this.periodSelect.value)
			{
				this.hideElement(this.timezoneToggle);
			}
			else if (this.timezoneSelect.value)
			{
				this.showElement(this.timezones);
			}
			if (this.isEdit && !this.periodSelect.value)
			{
				this.hideElement(this.startBlockWrapper);
			}
			if (!this.showSetNextNumberToggle && !this.sequenceBlock)
			{
				this.showElement(this.startBlockWrapper);
			}
		},
		onPeriodOptionClick: function (event)
		{
			if (this.periodSelect.value)
			{
				this.showElement(this.timezoneToggle);
				if (this.isEdit)
				{
					this.showElement(this.startBlockWrapper)
				}
				if (this.timezoneSelect.value)
				{
					this.showElement(this.timezones)
				}
			}
			else
			{
				this.hideElement(this.timezoneToggle);
				this.hideElement(this.timezones);
				if (this.isEdit)
				{
					this.hideElement(this.startBlockWrapper)
				}
			}
		},
		fillTemplate: function ()
		{
			var remainingTemplate;
			var templateText = remainingTemplate = (this.templateInput.dataset['value']);
			if (templateText)
			{
				var words = templateText.match(/{[a-z0-9_:!@#%^&\(*;\)]+?}/gi);
				if (words)
				{
					for (var d = 0; d < words.length; d++)
					{
						var word = words[d];
						var button = this.selectByRoles([[this.roles.wordBtn, ''], [word, '', 'word']]);
						if (button)
						{
							this.activateButton(button);
							this.changeSettingsVisibility(button.dataset['type']);
							remainingTemplate = this.appendTextToTemplate(word, remainingTemplate);
							this.templateInput.appendChild(this.getTemplateWordNode(button.dataset['type'], word));
						}
					}
				}
				if (remainingTemplate)
				{
					this.templateInput.appendChild(document.createTextNode(remainingTemplate));
				}
			}
			else
			{
				var wordDefault = '{NUMBER}';
				var buttonDefault = this.selectByRoles([[this.roles.wordBtn, ''], [wordDefault, '', 'word']]);
				if (buttonDefault)
				{
					this.activateButton(buttonDefault);
					this.changeSettingsVisibility(buttonDefault.dataset['type']);
					this.templateInput.appendChild(this.getDefaultDelimiterNode());
					this.templateInput.appendChild(this.getTemplateWordNode(buttonDefault.dataset['type'], wordDefault));
				}
				this.deleteFirstDelimiter();
			}
		},
		onHelpArticleToggleClick: function (event)
		{
			top.BX.Helper.show("redirect=detail&code=7486453");
		},
		onShowSetNextNumberToggleClick: function (event)
		{
			var numId = this.selectByRoles('numerator-hidden-id-input');
			if (numId && numId.value && this.isMultipleSequences)
			{
				var urlNumEdit = BX.util.add_url_param("/bitrix/components/bitrix/main.numerator.edit.sequence/slider.php", {NUMERATOR_ID: numId.value});
				BX.SidePanel.Instance.open(urlNumEdit, {width: 650, cacheable: false});
			}
			else if (this.sequenceBlock)
			{
				if (this.isVisibleElement(this.sequenceBlock))
				{
					this.hideElement(this.sequenceBlock)
				}
				else
				{
					this.showElement(this.sequenceBlock);
				}
			}
		},
		onTimezoneToggleClick: function (event)
		{
			if (this.isVisibleElement(this.timezones))
			{
				this.hideElement(this.timezones);
			}
			else
			{
				this.showElement(this.timezones);
			}
		},
		checkForErrors: function ()
		{
			if (this.nameInput && !(this.nameInput.value && this.nameInput.value.trim() !== ''))
			{
				this.errors.push({
					field: this.nameInput,
					text: this.errorMessages && this.errorMessages.emptyField ? this.errorMessages.emptyField : ''
				})
			}
			if (!(this.templateInput.innerText && this.templateInput.innerText.trim() !== ''))
			{
				this.errors.push({
					field: this.templateInput,
					text: this.errorMessages && this.errorMessages.emptyField ? this.errorMessages.emptyField : ''
				})
			}
		},
		clearErrors: function ()
		{
			this.errors = [];
			var errorBlocks = this.selectByRoles('numerator-error-block', 'all');
			for (var i = errorBlocks.length - 1; i >= 0; i--)
			{
				errorBlocks[i].parentNode.removeChild(errorBlocks[i]);
			}
			var inputWithErrors = document.querySelectorAll('.main-numerator-edit-input-alert');
			for (var j = 0; j < inputWithErrors.length; j++)
			{
				inputWithErrors[j].classList.remove('main-numerator-edit-input-alert');
			}
		},
		showErrors: function ()
		{
			for (var t = 0; t < this.errors.length; t++)
			{
				var errorData = this.errors[t];
				var errorBlock = BX.create('div', {
					attrs:
						{
							class: 'main-numerator-edit-alert-text'
						},
					dataset: {role: 'numerator-error-block'},
					text: errorData.text
				});
				if (!errorData.field)
				{
					errorData.field = this.numeratorForm;
					errorData.field.parentNode.insertBefore(errorBlock, errorData.field);
				}
				else
				{
					errorData.field.parentNode.insertBefore(errorBlock, errorData.field.nextSibling);
				}
				if (!errorData.field.classList.contains('main-numerator-edit-input-alert'))
				{
					errorData.field.classList.add('main-numerator-edit-input-alert');
				}
			}
		},
		updateErrors: function ()
		{
			this.clearErrors();
			this.checkForErrors();
			this.showErrors();
		},
		closeSlider: function ()
		{
			if (this.isSlider)
			{
				var slider = BX.SidePanel.Instance.getTopSlider();
				if (slider)
				{
					slider.close();
				}
			}
		},
		onCancelClick: function (event)
		{
			event.preventDefault();
			this.closeSlider();
		},
		onSaveClick: function (event)
		{
			event.stopPropagation();
			event.preventDefault();
			this.updateErrors();
			if (this.errors && this.errors.length)
			{
				return;
			}
			var formData = new FormData(this.numeratorForm);
			BX.ajax.runAction('main.api.numerator.save', {
				data: formData
			}).then(
				function (response)
				{
					BX.SidePanel.Instance.postMessageAll(window, "numerator-saved-event", {
						id: response.data.id,
						name: this.nameInput ? this.nameInput.value : '',
						template: this.hiddenTemplateInput ? this.hiddenTemplateInput.value : '',
						type: response.data.type
					});
					this.closeSlider();
				}.bind(this),
				function (response)
				{
					for (var j = 0; j < response.errors.length; j++)
					{
						this.errors.push({text: response.errors[j].message})
					}
					this.showErrors();
				}.bind(this));
		},
		updateTemplateHiddenInput: function ()
		{
			this.hiddenTemplateInput.value = this.templateInput.innerText;
		},
		handleKeyPress: function (e)
		{
			this.updateTemplateHiddenInput();
			if (e.keyCode === 8 || e.keyCode === 46)
			{
				for (var settingsType in this.settingsBlock)
				{
					this.hideSettingsBlockByType(settingsType);
				}
				for (var k = 0; k < this.wordButtons.length; k++)
				{
					this.disActivateButton(this.wordButtons[k]);
				}
				var wordsInsideTemplate = this.selectByRoles([this.roles.templateInput, this.roles.wordText], 'all');
				for (var m = 0; m < wordsInsideTemplate.length; m++)
				{
					var word = wordsInsideTemplate[m];
					var settingsForWord = this.settingsBlock[word.dataset['settingsType']];
					this.activateButton(this.selectByRoles([[this.roles.wordBtn, ''], [word.dataset['word'], '', 'word']]));
					this.showElement(settingsForWord);
				}
				this.deleteFirstDelimiter();
			}
		},
		onTemplateWordBtnClick: function (event)
		{
			event.preventDefault();
			event.stopPropagation();
			var button = event.currentTarget;
			if (this.isActiveBtn(button))
			{
				this.disActivateButton(button);
				this.removeWordsFromTemplate(button.dataset['word']);
				this.hideSettingsBlockByType(button.dataset['type']);
			}
			else
			{
				this.activateButton(button);
				this.insertWordIntoTemplate(button.dataset['type'], button.dataset['word']);
				this.changeSettingsVisibility(button.dataset['type']);
			}
			this.deleteFirstDelimiter();
			this.updateTemplateHiddenInput();
		},
		deleteFirstDelimiter: function ()
		{
			if (this.templateInput.children
				&& this.templateInput.children.length
				&& this.templateInput.children[0].dataset
				&& this.templateInput.children[0].dataset.role === 'default-delimiter')
			{
				this.templateInput.children[0].parentNode.removeChild(this.templateInput.children[0]);
			}
		},
		hideSettingsBlockByType: function (type)
		{
			this.hideElement(this.settingsBlock[type]);
		},
		getDefaultDelimiterNode: function ()
		{
			return BX.create('span', {text: this.defaultDelimiter, dataset: {role: 'default-delimiter'}});
		},
		insertWordIntoTemplate: function (type, word)
		{
			var el = this.getTemplateWordNode(type, word);
			var selection = window.getSelection();
			if (selection.getRangeAt && selection.rangeCount)
			{
				var range = selection.getRangeAt(0);
				if (this.elementContainsSelection(this.templateInput))
				{
					range.insertNode(this.getDefaultDelimiterNode());
					range.collapse(false);
					range.insertNode(el);
				}
				else
				{
					this.templateInput.appendChild(this.getDefaultDelimiterNode());
					this.templateInput.appendChild(el);
				}
				range.collapse(false);
			}
			else
			{
				this.templateInput.appendChild(this.getDefaultDelimiterNode());
				this.templateInput.appendChild(el);
			}
		},
		getTemplateWordNode: function (type, word)
		{
			var notEditableSpan = BX.create('span', {
				props:
					{
						className: 'main-numerator-edit-template-word'
					},
				dataset: {settingsType: type, word: word, role: this.roles.wordText},
				text: word
			});
			notEditableSpan.setAttribute("contenteditable", false);
			return BX.create('span', {
				props:
					{
						className: 'main-numerator-edit-template-word-wrap'
					},
				events: {keyup: BX.delegate(this.handleKeyPress, this)},
				children: [notEditableSpan]
			});
		},
		changeSettingsVisibility: function (settingsType)
		{
			if (this.templateInput)
			{
				var settingsField = this.settingsBlock[settingsType];
				if (settingsField && !this.isVisibleElement(settingsField))
				{
					this.showElement(settingsField);
					var node = settingsField;
					var parent = node.parentNode;
					var oldChild = parent.removeChild(node);
					parent.insertBefore(oldChild, parent.childNodes[0]);
				}
			}
		},
		appendTextToTemplate: function (word, templateText)
		{
			var beforeText = templateText.substring(0, templateText.indexOf(word));
			if (beforeText)
			{
				this.templateInput.appendChild(document.createTextNode(beforeText));
			}
			return templateText.replace((beforeText + word), '');
		},
		removeWordsFromTemplate: function (word)
		{
			var wordsInsideTemplate = this.selectByRoles([[this.roles.templateInput], [this.roles.wordText, ''], [word, '', 'word']], 'all');
			for (var k = 0; k < wordsInsideTemplate.length; k++)
			{
				var wordBlock = wordsInsideTemplate[k];
				if (wordBlock.previousSibling && wordBlock.previousSibling.dataset && wordBlock.previousSibling.dataset.role === 'default-delimiter')
				{
					wordBlock.previousSibling.parentNode.removeChild(wordBlock.previousSibling);
				}
				if (wordBlock.parentNode.previousSibling && wordBlock.parentNode.previousSibling.dataset && wordBlock.parentNode.previousSibling.dataset.role === 'default-delimiter')
				{
					wordBlock.parentNode.previousSibling.parentNode.removeChild(wordBlock.parentNode.previousSibling);
				}
				var wrapper = wordBlock.parentNode;
				if (wrapper)
				{
					wrapper.removeChild(wordBlock);
				}
				if (wrapper.classList.contains('main-numerator-edit-template-word-wrap') && wrapper.children.length === 0)
				{
					wrapper.parentNode.removeChild(wrapper);
				}
			}
		},
		isActiveBtn: function (btn)
		{
			if (btn)
			{
				return btn.classList.contains('main-numerator-edit-template-word-btn-clicked');
			}
		},
		selectByRoles: function (roles, all)
		{
			var selector = '';
			if (!Array.isArray(roles))
			{
				roles = [roles];
			}
			for (var c = 0; c < roles.length; c++)
			{
				if (Array.isArray(roles[c]))
				{
					selector += '[data-' + (roles[c][2] ? roles[c][2] : 'role') + '="' + roles[c][0] + '"]' + (roles[c][1] === '' ? '' : ' ');
					continue;
				}
				selector += '[data-role="' + roles[c] + '"] ';
			}
			return all ? document.querySelectorAll(selector) : document.querySelector(selector);
		},
		isVisibleElement: function (element)
		{
			if (element)
			{
				return element.classList.contains('main-numerator-edit-hide') === false;
			}
		},
		showElement: function (element)
		{
			if (element)
			{
				element.classList.remove('main-numerator-edit-hide');
			}
		},
		hideElement: function (element)
		{
			if (element)
			{
				element.classList.add('main-numerator-edit-hide');
			}
		},
		disActivateButton: function (btn)
		{
			if (btn)
			{
				btn.classList.remove('main-numerator-edit-template-word-btn-clicked');
			}
		},
		activateButton: function (btn)
		{
			if (btn)
			{
				btn.classList.add('main-numerator-edit-template-word-btn-clicked');
			}
		},
		isOrContains: function (node, container)
		{
			var limit = 10;
			var count = 0;
			while (node)
			{
				count++;
				if (count > limit)
				{
					break;
				}
				if (node === container)
				{
					return true;
				}
				node = node.parentNode;
			}
			return false;
		},
		elementContainsSelection: function (el)
		{
			var sel;
			if (window.getSelection)
			{
				sel = window.getSelection();
				if (sel.rangeCount > 0)
				{
					for (var i = 0; i < sel.rangeCount; ++i)
					{
						if (!this.isOrContains(sel.getRangeAt(i).commonAncestorContainer, el))
						{
							return false;
						}
					}
					return true;
				}
			}
			else if ((sel = document.selection) && sel.type !== "Control")
			{
				return this.isOrContains(sel.createRange().parentElement(), el);
			}
			return false;
		}
	};
})();