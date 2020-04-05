
;(function() {

	if (window.BXMainMailForm)
		return;

	var BXMainMailForm = function(id, fields, options)
	{
		if (BXMainMailForm.__forms[id])
			return BXMainMailForm.__forms[id];

		this.id = id;
		this.fields = fields;
		this.options = options;

		BXMainMailForm.__forms[this.id] = this;
	};

	BXMainMailForm.__forms = {};

	BXMainMailForm.getForm = function (id)
	{
		return BXMainMailForm.__forms[id];
	};

	BXMainMailForm.prototype.getField = function (name)
	{
		for (var i = this.fields.length; i-- > 0;)
		{
			if (this.fields[i].params.name == name)
				return this.fields[i];
		}

		return false;
	};

	BXMainMailForm.prototype.onSubmit = function (event)
	{
		var form = this;

		var footer = BX.findChildByClassName(this.formWrapper, 'main-mail-form-footer', false);
		var button = BX.findChildByClassName(footer, 'main-mail-form-submit-button', true);

		if (button.disabled)
			return BX.PreventDefault();

		this.editor.OnSubmit();

		event = event || window.event;
		BX.onCustomEvent(this, 'MailForm:submit', [this, event]);

		if (!event.defaultPrevented && event.returnValue !== false)
		{
			this.hideError();
			BX.addClass(button, 'ui-btn-wait');
			button.disabled = true;
			button.offsetHeight; // hack to show loader

			if (this.options.submitAjax)
			{
				BX.ajax.submitAjax(this.htmlForm, {
					url: this.htmlForm.getAttribute('action'),
					method: 'POST',
					dataType: 'json',
					onsuccess: function(data)
					{
						button.disabled = false;
						BX.removeClass(button, 'ui-btn-wait');
						BX.onCustomEvent(form, 'MailForm:submit:ajaxSuccess', [form, data]);
					},
					onfailure: function(data)
					{
						button.disabled = false;
						BX.removeClass(button, 'ui-btn-wait');
						BX.onCustomEvent(form, 'MailForm:submit:ajaxFailure', [form, data]);
					}
				});

				return BX.PreventDefault(event);
			}
		}
	};

	BXMainMailForm.prototype.showError = function (html)
	{
		var errorNode = BX.findChildByClassName(this.formWrapper, 'main-mail-form-error', true);
		BX.adjust(errorNode, {
			html: html,
			style: {
				display: 'block'
			}
		});

		this.initScrollable();
		if (this.__scrollable)
		{
			var pos0 = BX.pos(this.__scrollable);
			var pos1 = BX.pos(this.formWrapper);
			var pos2 = BX.pos(errorNode);

			if (pos0.top > pos2.top-10-this.__scrollable.scrollTop)
				this.__scrollable.scrollTop = pos2.top-10;
			else if (pos0.bottom < pos1.bottom-10-this.__scrollable.scrollTop)
				this.__scrollable.scrollTop = pos1.bottom-10-pos0.bottom;
		}
	};

	BXMainMailForm.prototype.hideError = function ()
	{
		var errorNode = BX.findChildByClassName(this.formWrapper, 'main-mail-form-error', true);
		BX.adjust(errorNode, {
			style: {
				display: 'none'
			}
		});
	};

	BXMainMailForm.prototype.init = function()
	{
		var form = this;

		if (this.__inited)
			return;

		this.formId = 'main_mail_form_'+this.id;
		this.formWrapper = BX(this.formId);
		this.htmlForm = BX.findParent(this.formWrapper, {tag: 'form'});

		this.postForm = LHEPostForm.getHandler(this.formId+'_editor');
		this.editor = BXHtmlEditor.Get(this.formId+'_editor');

		this.initFields();
		this.initFooter();

		BX.bind(this.htmlForm, 'submit', this.onSubmit.bind(this));

		this.__inited = true;

		BX.onCustomEvent(BXMainMailForm, 'MailForm:init:'+this.id, [this]);
	}

	BXMainMailForm.prototype.initScrollable = function()
	{
		if (!this.__scrollable)
		{
			if (document.scrollingElement)
				this.__scrollable = document.scrollingElement;
		}

		if (!this.__scrollable)
		{
			if (document.documentElement.scrollTop > 0 || document.documentElement.scrollLeft > 0)
				this.__scrollable = document.documentElement;
			else if (document.body.scrollTop > 0 || document.body.scrollLeft > 0)
				this.__scrollable = document.body;
		}

		if (!this.__scrollable)
		{
			window.scrollBy(1, 1);

			if (document.documentElement.scrollTop > 0 || document.documentElement.scrollLeft > 0)
				this.__scrollable = document.documentElement;
			else if (document.body.scrollTop > 0 || document.body.scrollLeft > 0)
				this.__scrollable = document.body;

			window.scrollBy(-1, -1);
		}
	}

	BXMainMailForm.prototype.initFields = function()
	{
		for (var i = 0, fieldId; i < this.fields.length; i++)
		{
			this.fields[i] = new BXMainMailFormField(this, this.fields[i]);

			fieldId = this.fields[i].fieldId;
			this.fields[fieldId] = this.fields[i];
		}

		// hidden fields switches
		var fieldsFooter = BX(this.formId+'_fields_footer');
		var fieldsExtFooter = BX(this.formId+'_fields_ext_footer');
		var hiddenFields = []
			.concat(BX.findChildrenByClassName(fieldsFooter, 'main-mail-form-field-button', true) || [])
			.concat(BX.findChildrenByClassName(fieldsExtFooter, 'main-mail-form-field-button', true) || []);
		for (var i = 0, fieldId; i < hiddenFields.length; i++)
		{
			fieldId = hiddenFields[i].getAttribute('data-target');
			if (typeof this.fields[fieldId] != 'undefined')
			{
				this.fields[fieldId].__switch = hiddenFields[i];
				BX.bind(hiddenFields[i], 'click', this.fields[fieldId].unfold.bind(this.fields[fieldId]));
			}
		}
	}

	BXMainMailForm.prototype.initFooter = function()
	{
		var form = this;

		var footerWrapper = BX.findChildByClassName(this.formWrapper, 'main-mail-form-footer-wrapper', true);
		var footer = BX.findChildByClassName(footerWrapper, 'main-mail-form-footer', false);

		var footerButtons = BX.findChildrenByClassName(footer, 'main-mail-form-footer-button', true);
		for (var i in footerButtons)
		{
			(function(button)
			{
				BX.bind(button, 'click', function ()
				{
					BX.onCustomEvent(form, 'MailForm:footer:buttonClick', [form, button]);
					if (BX.hasClass(button, 'main-mail-form-submit-button'))
						BX.submit(form.htmlForm);
				});
			})(footerButtons[i]);
		}

		var resetFooter = function ()
		{
			if (BX.hasClass(footer, 'main-mail-form-footer-fixed'))
			{
				BX.removeClass(footer, 'main-mail-form-footer-fixed-hidden');
				BX.removeClass(footer, 'main-mail-form-footer-fixed');
				footer.style.left = '';
				footer.style.width = '';
				footerWrapper.style.height = '';
			}
		};

		var positionFooter = function()
		{
			form.initScrollable();

			if (form.formWrapper.offsetHeight > 0 && form.__scrollable)
			{
				var pos0 = BX.pos(form.__scrollable);
				var pos1 = BX.pos(form.formWrapper);

				if (pos0.bottom < pos1.bottom-10-form.__scrollable.scrollTop)
				{
					footer.style.left = (pos1.left-pos0.left-form.__scrollable.scrollLeft)+'px';
					footer.style.width = form.formWrapper.offsetWidth+'px';

					if (!BX.hasClass(footer, 'main-mail-form-footer-fixed'))
					{
						if (pos0.bottom < BX.pos(footerWrapper).top-form.__scrollable.scrollTop)
							BX.addClass(footer, 'main-mail-form-footer-fixed-hidden');
						footerWrapper.style.height = footerWrapper.offsetHeight+'px';
						BX.addClass(footer, 'main-mail-form-footer-fixed');
					}

					var editorWrapper = BX.findChildByClassName(form.formWrapper, 'main-mail-form-editor-wrapper', true);
					if (pos0.bottom < BX.pos(editorWrapper).top+footer.offsetHeight-form.__scrollable.scrollTop)
						BX.addClass(footer, 'main-mail-form-footer-fixed-hidden');
					else
						BX.removeClass(footer, 'main-mail-form-footer-fixed-hidden');

					return;
				}
			}

			resetFooter();
		};

		var startMonitoring = function ()
		{
			setTimeout(function ()
			{
				if (!form.__footerMonitoring)
				{
					form.__footerMonitoring = true;

					BX.bind(window, 'resize', positionFooter);
					BX.bind(window, 'scroll', positionFooter);

					positionFooter();
				}
			}, 400);
		};
		var stopMonitoring = function ()
		{
			form.__footerMonitoring = false;

			BX.unbind(window, 'resize', positionFooter);
			BX.unbind(window, 'scroll', positionFooter);

			resetFooter();
		};

		BX.addCustomEvent(this, 'MailForm:show', startMonitoring);
		BX.addCustomEvent(this, 'MailForm:hide', stopMonitoring);

		if (this.formWrapper.offsetHeight > 0)
			startMonitoring();
	}

	var BXMainMailFormField = function(form, params)
	{
		this.form = form;
		this.params = params;

		this.fieldId = this.form.formId+'_'+this.params.id;

		this.init();
	};

	BXMainMailFormField.prototype.init = function()
	{
		this.params.__row = BX(this.fieldId);

		if (BXMainMailFormField.__types[this.params.type] && BXMainMailFormField.__types[this.params.type].init)
			BXMainMailFormField.__types[this.params.type].init(this);

		if (this.params.menu)
		{
			var field = this;
			var menuExtButton = BX.findChildByClassName(this.params.__row, 'main-mail-form-field-value-menu-ext-button', true);

			BX.addCustomEvent(this.form, 'MailForm::editor:click', function ()
			{
				var menu = BX.PopupMenu.getMenuById(field.fieldId+'-menu-ext');

				if (menu)
					menu.close();
			});

			BX.addCustomEvent('onSubMenuShow', function ()
			{
				var menuWindow = this.menuWindow;
				while (menuWindow.parentMenuWindow)
					menuWindow = menuWindow.parentMenuWindow;

				if (field.fieldId+'-menu-ext' == menuWindow.id)
					BX.addClass(this.subMenuWindow.popupWindow.popupContainer, 'main-mail-form-field-value-menu-ext-content');
			});

			BX.bind(menuExtButton, 'click', function ()
			{
				BX.onCustomEvent(field.form, 'MailForm:field:setMenuExt', [field.form, field]);

				BX.PopupMenu.destroy(field.fieldId+'-menu-ext');
				BX.PopupMenu.show(
					field.fieldId+'-menu-ext',
					this, field.__menuExt,
					{
						className: 'main-mail-form-field-value-menu-ext-content',
						offsetTop: -8,
						offsetLeft: 13,
						angle: true,
						closeByEsc: true
					}
				);
			});
		}
	}

	BXMainMailFormField.prototype.setMenuExt = function(items)
	{
		this.__menuExt = items;
	}

	BXMainMailFormField.prototype.insert = function(text)
	{
		if (BXMainMailFormField.__types[this.params.type] && BXMainMailFormField.__types[this.params.type].insert)
			BXMainMailFormField.__types[this.params.type].insert(this, text);
	}

	BXMainMailFormField.prototype.setValue = function(value, options)
	{
		if (BXMainMailFormField.__types[this.params.type] && BXMainMailFormField.__types[this.params.type].setValue)
			BXMainMailFormField.__types[this.params.type].setValue(this, value, options);
	}

	BXMainMailFormField.prototype.show = function()
	{
		// @TODO: enable form fields
		this.params.hidden = false;

		BX.addClass(this.fieldId, 'main-mail-form-drop-animation');

		BX(this.fieldId).style.display = this.params.folded ? 'none' : '';
		this.__switch.style.display = this.params.folded ? '' : 'none';
	}

	BXMainMailFormField.prototype.hide = function()
	{
		// @TODO: disable form fields
		this.params.hidden = true;

		BX(this.fieldId).style.display = 'none';
		this.__switch.style.display = 'none';

		BX.removeClass(this.fieldId, 'main-mail-form-drop-animation');
	}

	BXMainMailFormField.prototype.fold = function()
	{
		this.params.folded = true;

		if (!this.params.hidden)
			this.__switch.style.display = '';

		BX(this.fieldId).style.display = 'none';
		BX.removeClass(this.fieldId, 'main-mail-form-drop-animation');
	}

	BXMainMailFormField.prototype.unfold = function()
	{
		this.params.folded = false;

		if (!this.params.hidden)
		{
			BX.addClass(this.fieldId, 'main-mail-form-drop-animation');
			BX(this.fieldId).style.display = '';
		}

		this.__switch.style.display = 'none';
	}

	BXMainMailFormField.__types = {
		'list': {},
		'text': {},
		'from': {},
		'rcpt': {},
		'editor': {},
		'files': {}
	};

	BXMainMailFormField.__types['list'].init = function(field)
	{
		BX.addCustomEvent(field.form, 'MailForm::editor:click', function ()
		{
			var menu = BX.PopupMenu.getMenuById(field.fieldId+'-menu');

			if (menu)
				menu.close();
		});

		var selector = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-value-menu', true);
		BX.bind(selector, 'click', function()
		{
			var input = BX(field.fieldId+'_value');
			var apply = function(value, text)
			{
				input.value = value;
				BX.adjust(selector, { html: text });
			};
			var handler = function(event, item)
			{
				apply(item.options.value, item.text);
				item.menuWindow.close();
			};

			var items = [];

			if (!field.params.required)
			{
				items.push({
					text: BX.util.htmlspecialchars(field.params.placeholder),
					title: field.params.placeholder,
					value: '',
					onclick: handler
				});
				items.push({ delimiter: true });
			}

			for (var i in field.params.list)
			{
				items.push({
					text: BX.util.htmlspecialchars(field.params.list[i]),
					title: field.params.list[i],
					value: i,
					onclick: handler
				});
			}

			BX.PopupMenu.show(
				field.fieldId+'-menu',
				selector, items,
				{
					className: 'main-mail-form-field-value-menu-content',
					offsetLeft: 40,
					angle: true,
					closeByEsc: true
				}
			);
		});
	};

	BXMainMailFormField.__types['from'].init = function(field)
	{
		BX.addCustomEvent(field.form, 'MailForm::editor:click', function ()
		{
			var menu = BX.PopupMenu.getMenuById(field.fieldId+'-menu');

			if (menu)
				menu.close();
		});

		var selector = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-value-menu', true);
		BX.bind(selector, 'click', function()
		{
			var input = BX(field.fieldId+'_value');
			var apply = function(value, text)
			{
				input.value = value;
				BX.adjust(selector, {html: text});
			};
			var handler = function(event, item)
			{
				apply(item.title, item.text);
				item.menuWindow.close();
			};

			var items = [];

			if (!field.params.required)
			{
				items.push({
					text: BX.util.htmlspecialchars(field.params.placeholder),
					title: '',
					onclick: handler
				});
				items.push({ delimiter: true });
			}

			if (field.params.mailboxes && field.params.mailboxes.length > 0)
			{
				for (var i in field.params.mailboxes)
				{
					items.push({
						text: BX.util.htmlspecialchars(field.params.mailboxes[i].formated),
						title: field.params.mailboxes[i].formated,
						onclick: handler
					});
				}

				items.push({ delimiter: true });
			}

			items.push({
				text: BX.util.htmlspecialchars(BX.message('MAIN_MAIL_CONFIRM_MENU')),
				onclick: function(event, item)
				{
					item.menuWindow.close();
					BXMainMailConfirm.showForm(function(mailbox, formated)
					{
						field.params.mailboxes.push({
							email: mailbox.email,
							name: mailbox.name,
							formated: formated
						});

						apply(formated, BX.util.htmlspecialchars(formated));
						BX.PopupMenu.destroy(field.fieldId+'-menu');
					});
				}
			});

			BX.PopupMenu.show(
				field.fieldId+'-menu',
				selector, items,
				{
					className: 'main-mail-form-field-value-menu-content',
					offsetLeft: 40,
					angle: true,
					closeByEsc: true
				}
			);
		});
	};

	BXMainMailFormField.__types['rcpt'].init = function(field)
	{
		var more    = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-rcpt-item-more', true);
		var wrapper = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-value-wrapper', true);
		var link    = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-rcpt-add-link', true);
		var input   = BX(field.fieldId+'_fvalue');

		field.selector = field.fieldId+'-selector';

		var select = function(item, type, search, undeleted, name, state)
		{
			// BX.hide(BX.findChildByClassName(createForm, 'crm-task-list-mail-reply-error', true));

			if (!field.params.multiple)
			{
				var selected = BX.SocNetLogDestination.getSelected(field.selector);
				for (var i in selected)
				{
					if (i != item.id || selected[i] != type)
						BX.SocNetLogDestination.deleteItem(i, selected[i], field.selector);
				}
			}

			item.showEmail = 'N';
			if (field.params.email && item.email && item.email.length > 0 && item.email != item.name)
			{
				item = BX.clone(item);
				item.name = item.name+' &lt;' + item.email + '&gt;';
			}

			var itemWrapper = document.createElement('SPAN');
			itemWrapper.setAttribute('data-id', item.id);
			BX.addClass(itemWrapper, 'main-mail-form-field-rcpt-item');
			wrapper.insertBefore(itemWrapper, more.parentNode);

			itemWrapper.appendChild(BX.create('INPUT', {
				'props': {
					'type': 'hidden',
					'name': field.params.name+'[]',
					'value': JSON.stringify(item)
				}
			}));

			BX.SocNetLogDestination.BXfpSelectCallback({
				item: item,
				type: type,
				varName: 'dummy_'+field.params.name,
				bUndeleted: false,
				containerInput: itemWrapper,
				valueInput: input,
				formName: name,
				tagInputName: link,
				tagLink1: field.params.placeholder,
				tagLink2: field.params.placeholder
			});

			if ('init' == state)
			{
				var limit = 9;
				var items = BX.findChildrenByClassName(wrapper, 'main-mail-form-field-rcpt-item', false);
				if (items.length > limit+1)
				{
					for (var i = limit; i < items.length; i++)
						items[i].style.display = 'none';

					more.setAttribute('title', more.getAttribute('title').replace(/-?\d+/, items.length-limit));
					more.parentNode.style.display = '';
				}
			}
		};

		var unselect = function(item, type, search, name)
		{
			var itemWrapper = BX.findChild(wrapper, {attribute: {'data-id': item.id}}, false);

			BX.SocNetLogDestination.BXfpUnSelectCallback.apply({
				formName: name,
				inputContainerName: itemWrapper,
				inputName: input,
				tagInputName: link,
				tagLink1: field.params.placeholder,
				tagLink2: field.params.placeholder
			}, [item]);

			if (itemWrapper && itemWrapper.parentNode == wrapper)
			{
				if (!BX.findChildByClassName(itemWrapper, 'feed-add-post-destination'))
					wrapper.removeChild(itemWrapper);
			}

			var limit = 9;
			var visible = 0;
			var items = BX.findChildrenByClassName(wrapper, 'main-mail-form-field-rcpt-item', false);
			for (var i = 0; i < items.length; i++)
			{
				if (items[i].offsetHeight > 0)
					visible++;
			}

			if (visible < items.length && (visible < limit || items.length <= limit+1))
			{
				for (var i = 0; i < items.length; i++)
				{
					if (items[i].offsetHeight > 0)
						continue;

					items[i].style.display = '';
					visible++;

					if (visible >= Math.min(limit, items.length) && items.length > limit+1)
						break;
				}

				more.setAttribute('title', more.getAttribute('title').replace(/-?\d+/, items.length-limit));
				if (visible >= items.length)
					more.parentNode.style.display = 'none';
			}
		};

		var selectorParams = {
			name: field.selector,
			searchInput: input,
			bindMainPopup: {
				node: wrapper,
				offsetTop: '5px',
				offsetLeft: '15px'
			},
			bindSearchPopup : {
				node: wrapper,
				offsetTop: '5px',
				offsetLeft: '15px'
			},
			callback: {
				select: select,
				unSelect: unselect,
				openDialog: BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: input.parentNode,
					inputName: input,
					tagInputName: link
				}),
				closeDialog: function()
				{
					BX.onCustomEvent(field.form, 'MailForm:field:rcptSelectorClose', [field.form, field]);
					BX.SocNetLogDestination.BXfpCloseDialogCallback.apply({
						inputBoxName: input.parentNode,
						inputName: input,
						tagInputName: link
					});
				},
				openSearch: BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
					inputBoxName: input.parentNode,
					inputName: input,
					tagInputName: link
				})
			},
			items: {},
			itemsLast: {},
			itemsSelected: {},
			destSort: {}
		};

		if (field.params.selector)
		{
			for (var i in field.params.selector)
				selectorParams[i] = field.params.selector[i];
		}

		BX.SocNetLogDestination.init(selectorParams);

		BX.bind(input, 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: field.selector,
			inputName: input
		}));
		BX.bind(input, 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: field.selector,
			inputName: input,
			tagInputName: link
		}));
		BX.bind(input, 'paste', BX.defer(BX.SocNetLogDestination.BXfpSearch, {
			formName: field.selector,
			inputName: input,
			tagInputName: link,
			onPasteEvent: true
		}));
		BX.bind(input, 'blur', BX.delegate(BX.SocNetLogDestination.BXfpBlurInput, {
			inputBoxName: input.parentNode,
			tagInputName: link
		}));

		BX.bind(wrapper, 'click', function(e)
		{
			BX.SocNetLogDestination.openDialog(field.selector);
			BX.PreventDefault(e);
		});

		BX.bind(more, 'click', function(e)
		{
			var items = BX.findChildrenByClassName(wrapper, 'main-mail-form-field-rcpt-item', false);
			for (var i = 0; i < items.length; i++)
				items[i].style.display = '';

			this.parentNode.style.display = 'none';

			BX.PreventDefault(e);
		});
	};

	BXMainMailFormField.__types['editor'].init = function(field)
	{
		var postForm = field.form.postForm;
		var editor = field.form.editor;

		if (field.params.value === null || field.params.value === undefined)
			field.params.value = '';

		field.quoteNode = document.createElement('DIV');
		field.quoteNode.innerHTML = field.params.value;
		field.quoteNode.__folded = field.form.options.foldQuote;

		//postForm.controllerInit('hide');
		BX.onCustomEvent(postForm.eventNode, 'OnShowLHE', ['justShow']);

		BX.addClass(editor.dom.cont, 'main-mail-form-editor');
		editor.dom.toolbarCont.style.opacity = 'inherit';

		// close rctp selectors on focus on html-editor
		BX.addCustomEvent(
			editor, 'OnIframeClick',
			function()
			{
				BX.SocNetLogDestination.abortSearchRequest();
				BX.SocNetLogDestination.closeSearch();
				BX.SocNetLogDestination.closeDialog();

				BX.onCustomEvent(field.form, 'MailForm::editor:click', []);
			}
		);

		var toolbarButton = BX.findChildByClassName(field.params.__row, 'feed-add-post-form-editor-btn', true);

		var toogleToolbar = function(show)
		{
			show = show ? true : false;

			editor.toolbar[show?'Show':'Hide']();
			BX[show?'addClass':'removeClass'](toolbarButton, 'feed-add-post-form-btn-active');
			BX[show?'removeClass':'addClass'](field.params.__row, 'main-mail-form-editor-no-toolbar');
		};

		toogleToolbar(editor.toolbar.shown);
		BX.bind(toolbarButton, 'click', function()
		{
			toogleToolbar(!editor.toolbar.shown);
		});

		// append original message quote
		var quoteButton = BX.findChildByClassName(field.form.htmlForm, 'main-mail-form-quote-button', true);
		var quoteHandler = function()
		{
			if (field.quoteNode.__folded)
			{
				field.quoteNode.__folded = false;

				field.setValue(editor.GetContent(), {quote: true});
				editor.Focus(false);

				var height0, height1;

				height0 = quoteButton.parentNode.offsetHeight;
				BX.hide(quoteButton, 'inline-block');
				height1 = quoteButton.parentNode.offsetHeight;

				editor.ResizeSceleton(0, editor.config.height+height0-height1);
			}
		};
		BX.bind(quoteButton, 'click', quoteHandler);

		// append original message quote on switch from wysiwyg mode
		var modeHandler = function ()
		{
			if (editor.GetViewMode() != 'wysiwyg')
			{
				BX.removeCustomEvent(editor, 'OnSetViewAfter', modeHandler);
				quoteHandler();
			}
		};
		BX.addCustomEvent(editor, 'OnSetViewAfter', modeHandler);

		// wysiwyg -> code inline-attachments parser
		postForm.parser.disk_file.regexp = /(bxacid):(n?\d+)/ig;
		editor.phpParser.AddBxNode('disk_file', {
			Parse: function (params, bxid)
			{
				var node = editor.GetIframeDoc().getElementById(bxid) || BX.findChild(field.quoteNode, {attr: {id: bxid}}, true);
				if (node)
				{
					var dummy = document.createElement('DIV');

					node = node.cloneNode(true);
					dummy.appendChild(node);

					if (node.tagName.toUpperCase() == 'IMG')
					{
						var image = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

						node.setAttribute('data-bx-orig-src', node.getAttribute('src'));
						node.setAttribute('src', image);

						return dummy.innerHTML.replace(image, 'bxacid:'+params.value);
					}

					return dummy.innerHTML;
				}

				return '[ ' + params.value + ' ]';
			}
		});

		// clear inline-attachments on attachment remove
		BX.addCustomEvent(
			postForm.eventNode, 'OnFileUploadRemove',
			function (result)
			{
				editor.synchro.Sync();

				for (i in editor.bxTags)
				{
					if (editor.bxTags[i].params && editor.bxTags[i].params.value == result)
					{
						var node = editor.GetIframeDoc().getElementById(editor.bxTags[i].id);
						if (node && node.parentNode)
							node.parentNode.removeChild(node);

						var node = BX.findChild(field.quoteNode, {attr: {id: editor.bxTags[i].id}}, true);
						if (node && node.parentNode)
							node.parentNode.removeChild(node);

						delete editor.bxTags[i];
					}
				}

				editor.synchro.FullSyncFromIframe();
			}
		);

		// initialize editor content
		BX.addCustomEvent(
			editor, 'OnCreateIframeAfter',
			function ()
			{
				field.setValue('', {quote: true});
			}
		);

		BX.addCustomEvent(field.form, 'MailForm:show', function ()
		{
			field.form.editor.CheckAndReInit();
			field.form.editor.ResizeSceleton();
			field.form.editor.Focus(true);
		});

		BX.addCustomEvent(field.form, 'MailForm:hide', function ()
		{
			field.form.editor.SaveContent();
		});

		BX.addCustomEvent(
			field.form, 'MailForm:submit',
			function ()
			{
				var value = editor.GetContent();
				if (field.quoteNode.__folded)
					value += editor.Parse(field.quoteNode.innerHTML, true, false);

				BX(field.fieldId+'_value').value = value;
			}
		);
	};

	BXMainMailFormField.__types['from'].setValue = function(field, value)
	{
		var input = BX(field.fieldId+'_value');
		var selector = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-value-menu', true);

		if (!value.trim())
		{
			if (!field.params.required)
			{
				input.value = '';
				BX.adjust(selector, {html: ''});
			}

			return;
		}

		if (field.params.mailboxes && field.params.mailboxes.length > 0)
		{
			var escRegex = new RegExp('[-\/\\^$*+?.()|[\]{}]', 'g');
			for (var i in field.params.mailboxes)
			{
				var pattern = new RegExp(
					'(^|<)' + field.params.mailboxes[i].email.replace(escRegex, '\\$&') + '(>|$)', 'i'
				);
				if (value.trim().match(pattern))
				{
					input.value = value;
					BX.adjust(selector, {html: BX.util.htmlspecialchars(value)});

					break;
				}
			}
		}
	};

	BXMainMailFormField.__types['rcpt'].setValue = function(field, value)
	{
		var selected = BX.SocNetLogDestination.getSelected(field.selector);
		for (var id in selected)
			BX.SocNetLogDestination.deleteItem(id, selected[id], field.selector);

		if (value && BX.type.isPlainObject(value))
		{
			for (var id in value)
			{
				if (value.hasOwnProperty(id))
				{
					BX.SocNetLogDestination.obItemsSelected[field.selector][id] = value[id];
					BX.SocNetLogDestination.runSelectCallback(id, value[id], field.selector, false, 'init');
				}
			}
		}
	};

	BXMainMailFormField.__types['text'].insert = function(field, text)
	{
		var input = BX(field.fieldId+'_value');

		if (typeof input.selectionStart != 'undefined')
		{
			var selection = {
				start: input.selectionStart,
				end: input.selectionEnd
			};

			input.value = input.value.substr(0, selection.start) + text + input.value.substr(selection.end);
			input.selectionStart = input.selectionEnd = selection.start + text.length;
		}
		else
		{
			input.value = input.value + text;
		}

		input.focus();
	};

	BXMainMailFormField.__types['text'].setValue = function(field, value)
	{
		var input = BX(field.fieldId+'_value');

		input.value = value;
	};

	BXMainMailFormField.__types['editor'].insert = function(field, text)
	{
		var editor = field.form.editor;

		if (editor.synchro.IsFocusedOnTextarea())
		{
			editor.textareaView.WrapWith('', '', text);
			if (editor.textareaView.element && typeof editor.textareaView.element.selectionStart != 'undefined')
				editor.textareaView.element.selectionStart = editor.textareaView.element.selectionEnd;
		}
		else
		{
			editor.selection.GetRange().deleteContents();
			editor.InsertHtml(text);
		}

		editor.Focus();
	};

	BXMainMailFormField.__types['editor'].setValue = function(field, value, options)
	{
		var postForm = field.form.postForm;
		var editor = field.form.editor;

		if (value.length > 0)
		{
			for (var uid in postForm.controllers)
			{
				if (!postForm.controllers.hasOwnProperty(uid))
					continue;

				var ctrl = postForm.controllers[uid];

				if (ctrl.storage != 'disk')
					continue;

				if (!ctrl.values)
					break;

				for (var id in ctrl.values)
				{
					if (ctrl.values.hasOwnProperty(id) && ctrl.values[id].src)
						value = value.replace('bxacid:'+id, ctrl.values[id].src+'&__bxacid='+id);
				}

				break;
			}
		}

		if (options && options.quote && !field.quoteNode.__folded)
			value += field.quoteNode.innerHTML;

		editor.SetContent(value, true);

		var regex = /[&?]__bxacid=(n?\d+)/;

		var types = {'IMG': 'src', 'A': 'href'};
		for (var name in types)
		{
			var nodeList = editor.GetIframeDoc().getElementsByTagName(name);
			for (var i = 0; i < nodeList.length; i++)
			{
				var matches = nodeList[i].getAttribute(types[name])
					? nodeList[i].getAttribute(types[name]).match(regex)
					: false;
				if (matches && postForm.arFiles['disk_file'+matches[1]])
				{
					nodeList[i].removeAttribute('id');
					nodeList[i].setAttribute(
						types[name],
						nodeList[i].getAttribute(types[name]).replace(regex, '')
					);

					editor.SetBxTag(nodeList[i], {'tag': 'disk_file', params: {'value': matches[1]}});

					postForm.monitoringSetStatus('disk_file', matches[1], true);
					postForm.monitoringStart();
				}
			}
		}

		editor.synchro.FullSyncFromIframe();
	};

	BXMainMailFormField.__types['files'].setValue = function(field, value)
	{
		var postForm = field.form.postForm;

		postForm.controllerInit('show');
		for (var uid in postForm.controllers)
		{
			if (!postForm.controllers.hasOwnProperty(uid))
				continue;

			var ctrl = postForm.controllers[uid];

			if (ctrl.storage != 'disk')
				continue;

			if (!ctrl.handler)
				break;

			value = BX.clone(value);

			if (ctrl.values)
			{
				for (var i = 0; i < value.length; i++)
				{
					if (ctrl.values[value[i].id])
						value.splice(i--, 1);
				}
			}

			ctrl.handler.selectFile({}, {}, value);

			break;
		}
	};

	window.BXMainMailForm = BXMainMailForm;

})();
