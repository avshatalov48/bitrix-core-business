(function() {

	'use strict';

	BX.namespace('BX.Landing');

	const slice = BX.Landing.Utils.slice;
	const proxy = BX.Landing.Utils.proxy;
	const bind = BX.Landing.Utils.bind;
	const addClass = BX.Landing.Utils.addClass;
	const removeClass = BX.Landing.Utils.removeClass;
	const isNumber = BX.Landing.Utils.isNumber;
	const data = BX.Landing.Utils.data;
	const onTransitionEnd = BX.Landing.Utils.onTransitionEnd;

	/**
	 * For edit title.
	 */
	BX.Landing.EditTitleForm = function (node, additionalWidth, isEventTargetNode, display)
	{
		this.btn = node.querySelector('.ui-title-input-btn-js');
		this.label = node.querySelector('.landing-editable-field-label-js');
		this.input = node.querySelector('.landing-editable-field-input-js');
		this.additionalWidth = additionalWidth || 0;
		this.input.IsWidthSet = false;
		this.display = display;

		this.hideInput = this.hideInput.bind(this);
		this.showInput = this.showInput.bind(this);

		if(isEventTargetNode) {
			BX.bind(node, 'click', this.showInput);
		} else {
			BX.bind(this.btn, 'click', this.showInput);
		}

		this.input.setAttribute("data-height", this.label.offsetHeight);
	};

	BX.Landing.EditTitleForm.prototype =
	{
		showInput : function (event)
		{
			event.stopPropagation();

			if(!this.input.IsWidthSet)
			{
				this.input.style.width = this.label.offsetWidth + this.additionalWidth + 17 + 'px';
			}

			if(this.input.tagName === 'TEXTAREA')
			{
				this.input.style.height = this.input.getAttribute("data-height") + 'px';
			}
			this.label.style.display = 'none';
			this.btn.style.display = 'none';
			this.input.style.display = 'block';

			this.input.focus();
			if (!BX.Dom.hasClass(this.input, 'landing-editable-field-input-js-init'))
			{
				this.input.selectionStart = this.input.value.length;
				BX.Dom.addClass(this.input, "landing-editable-field-input-js-init");
			}

			this.input.IsWidthSet = true;

			BX.bind(document, 'mousedown', this.hideInput);
		},
		hideInput : function (event)
		{
			if(event.target === this.input)
				return;

			this.label.textContent = this.input.value;

			if (this.display) {
				this.label.style.display = 'inline-block';
			} else {
				this.label.style.display = 'inline';
			}

			this.input.style.display = 'none';
			this.btn.style.display = 'inline-block';

			this.input.IsWidthSet = false;
			this.input.setAttribute("data-height", this.label.offsetHeight);

			BX.unbind(document, 'mousedown', this.hideInput);
		}
	};

	/**
-	 * Length limit for fields
-	 */
	BX.Landing.FieldLengthLimited = function (list)
	{
		list.forEach(function (item)
		{
			BX.bind(item.field, 'keyup', function ()
			{
				if (item.length)
				{
					if (item.field.value.length <= item.length)
					{
						item.node.textContent = item.field.value;
					}
					else
					{
						item.node.textContent = item.field.value.substring(0, item.length);
					}
				}
				else
				{
					item.node.textContent = item.field.value;
				}
			});
		});
	};

	/**
	 * Favicon change.
	 */
	BX.Landing.Favicon = function()
	{
		const editLink = BX('landing-form-favicon-change');
		const editInput = BX('landing-form-favicon-input');
		const editValue = BX('landing-form-favicon-value');
		const editForm = BX('landing-form-favicon-form');
		const editSrc = BX('landing-form-favicon-src');
		const editError = BX('landing-form-favicon-error');

		if (!editForm || !editInput ||!editLink)
		{
			return;
		}

		// open file dialog
		BX.bind(editLink, 'click', function(e)
		{
			BX.fireEvent(editInput, 'click');
			BX.PreventDefault(e);
		});
		// upload picture
		BX.bind(editInput, 'change', function(e)
		{
			BX.ajax.submitAjax(editForm, {
				method: 'POST',
				dataType: 'json',
				onsuccess: function (data) {
					if (
						data.type === 'success' &&
						typeof data.result !== 'undefined' &&
						data.result !== false
					)
					{
						editValue.value = data.result.id;
						editSrc.setAttribute('src', data.result.src);
					}
					else
					{
						editError.style.color = 'red';
					}
				}
			});
			BX.PreventDefault(e);
		});
	};

	/**
	 * Custom 503 or 404 page.
	 * @param HTMLSelectElement select
	 */
	BX.Landing.Custom404And503 = function(select, useField)
	{
		BX.bind(select, 'change', event => {
			if (event.currentTarget.value === '')
			{
				useField.checked = false;
				useField.click();
			}
			else
			{
				useField.checked = true;
			}
		});

		BX.addCustomEvent('BX.UI.LayoutForm:onToggle', event => {
			if (
				event.getData().checkbox
				&& event.getData().checkbox === useField
			)
			{
				if (!event.getData().checkbox.checked)
				{
					select.value = ''
				}
			}
		});
	};

	/**
	 * Copyright on/off.
	 */
	BX.Landing.Copyright = function(form, copyright)
	{
		BX.bind(copyright, 'change', function ()
		{
			let formAction = form.getAttribute('action');
			formAction = formAction.replace(/&feature_copyright=[YN]/, '');
			formAction += '&feature_copyright=' + (this.checked ? 'Y' : 'N');
			form.setAttribute('action', formAction)
		});
	};

	/**
	 * Rights.
	 */
	BX.Landing.Access = function(params)
	{
		BX.Landing.Access.selected = params.selected;
		this.table = params.table;
		const name = 'RIGHTS';
		const form = params.form;
		const select = params.select;
		let inc = params.inc;

		BX.Access.Init({
			other: {
				disabled_cr: true
			}
		});

		BX.Access.SetSelected(BX.Landing.Access.selected, name);

		function showForm()
		{
			BX.Access.ShowForm({
				callback: obSelected => {
					for (let provider in obSelected)
					{
						if (obSelected.hasOwnProperty(provider))
						{
							for (let id in obSelected[provider])
							{
								if (obSelected[provider].hasOwnProperty(id))
								{
									let cnt = this.table.rows.length;
									let row = this.table.insertRow(cnt-1);
									row.classList.add("landing-form-rights");

									BX.Landing.Access.selected[id] = true;
									row.insertCell(-1);
									row.insertCell(-1);
										row.cells[0].innerHTML = BX.Access.GetProviderName(provider) + ' ' +
										BX.util.htmlspecialchars(obSelected[provider][id].name) + ':' +
										'<input type="hidden" name="fields[' + name + '][ACCESS_CODE][' + inc + ']" value="' + id + '">';
									row.cells[0].classList.add("landing-form-rights-right");
									row.cells[1].classList.add("landing-form-rights-left");
									row.cells[1].innerHTML =
										select.replace('#inc#', inc)
										+ ' <a href="javascript:void(0);" onclick="BX.Landing.Access.onRowDelete(this);"'
										+ ' data-id="' + id + '" class="landing-form-rights-delete"></a>';
									inc++;
								}
							}
						}
					}
				},
				bind: name
			})
		}

		form.addEventListener('click', showForm.bind(this));
	};

	BX.Landing.Access.selected = [];

	BX.Landing.Access.onRowDelete = function(link) {
		BX.Landing.Access.selected[BX.data(BX(link), 'id')] = false;
		BX.remove(BX.findParent(BX(link), {tag: 'tr'}, true));
	}

	/**
	 * Layout.
	 */
	BX.Landing.Layout = function(params)
	{
		this.params = params;
		this.params.messages = this.params.messages || {};
		this.container = this.params.container;
		this.areas = [];

		const layouts = [].slice.call(this.container.querySelectorAll('.landing-form-layout-item'));
		layouts.forEach(item =>
		{
			item.addEventListener('click', this.handleLayoutClick.bind(this));
		});
		this.createBlocks(layouts[0].dataset.block);

		if (typeof this.params.areasCount !== 'undefined')
		{
			this.changeLayout(this.params.areasCount, this.params.current);
		}

		const arrowContainer = this.container.querySelector('.landing-form-select-buttons');
		arrowContainer.addEventListener('click', this.handlerOnArrowClick.bind(this));

		if (this.params.tplUse)
		{
			this.useCheck = this.params.tplUse;
			this.inputs = this.container.querySelectorAll('.layout-switcher');
			BX.addCustomEvent('BX.UI.LayoutForm:onToggle', event => {
				if (
					event.getData().checkbox
					&& event.getData().checkbox === this.useCheck
				)
				{
					this.container.classList.add('landing-form-page-layout-short');
					this.inputs.forEach(item => {
						item.checked = false;
					});
				}
			});
		}
	};

	BX.Landing.Layout.prototype = {
		handlerOnArrowClick: function (event)
		{
			const layoutContainer = this.container.querySelector('.landing-form-list-inner');

			if (event.target.classList.contains('landing-form-select-next'))
			{
				layoutContainer.classList.add('--prev');
			}
			else
			{
				layoutContainer.classList.remove('--prev');
			}
		},

		createBlocks: function(blocks)
		{
			const saveRefs = this.params.tplRefs.value.split(',');
			this.areas = []
			const layoutBlockContainer = this.container.querySelector('.landing-form-layout-block-container');
			layoutBlockContainer.innerHTML = '';

			for (let i = 0; i < blocks; i++)
			{
				const block = BX.create('div', {
					attrs: {
						className: 'landing-form-layout-block-item',
					},
				});

				let numberBlock = i + 1;
				let linkContent = '';

				if (
					typeof saveRefs[i] !== 'undefined' &&
					saveRefs[i].indexOf(':') !== -1
				)
				{
					linkContent = parseInt(saveRefs[i].split(':')[1]);
					if (linkContent > 0)
					{
						linkContent = '#landing' + linkContent;
					}
					else
					{
						linkContent = '';
					}
				}

				const layoutField = new BX.Landing.UI.Field.LinkUrl({
					title: this.params.messages.area + ' #' + numberBlock,
					content: linkContent,
					textOnly: true,
					disableCustomURL: true,
					disableBlocks: true,
					disallowType: true,
					enableAreas: true,
					allowedTypes: [
						BX.Landing.UI.Field.LinkUrl.TYPE_PAGE,
					],
					typeData: {
						button : {
							'className': 'fa fa-chevron-right',
							'text': '',
							'action': BX.Landing.UI.Field.LinkUrl.TYPE_PAGE,
						},
						hideInput : false,
						contentEditable : false,
					},
					settingMode: true,
					options: {
						siteId: this.params.siteId,
						landingId: this.params.landingId,
						filter: {
							'=TYPE': this.params.type,
						},
					},
					onInit: this.rebuildHiddenField.bind(this),
					onInput: this.rebuildHiddenField.bind(this),
					onValueChange: this.rebuildHiddenField.bind(this),
				});

				this.areas[i] = layoutField;
				block.appendChild(layoutField.layout);
				layoutBlockContainer.appendChild(block);
			}
		},

		rebuildHiddenField: function ()
		{
			let refs = '';
			for (let i = 0, c = this.areas.length; i < c; i++)
			{
				refs += (i + 1) + ':' +
					//todo: 13 -> 8
					(this.areas[i].getValue() ? this.areas[i].getValue().substr(13) : 0) +
					',';
			}
			this.params.tplRefs.value = refs;
		},

		handleLayoutClick: function (event)
		{
			const layoutItem = event.target.parentNode;

			const layoutItemSelected = this.container.querySelector('.landing-form-layout-item-selected');
			if (layoutItemSelected)
			{
				layoutItemSelected.classList.remove('landing-form-layout-item-selected');
			}

			this.changeLayout(layoutItem.dataset.block, layoutItem.dataset.layout);
		},

		changeLayout: function (block, layout)
		{
			const detailLayoutContainer = this.container.querySelector('.landing-form-layout-detail');
			this.container.classList.remove('landing-form-page-layout-short');
			detailLayoutContainer.classList.remove('landing-form-layout-detail-hidden');

			this.createBlocks(block);

			if (typeof layout !== 'undefined')
			{
				this.changeLayoutImg(layout);
			}

			this.params.tplRefs.value = '';
		},

		changeLayoutImg: function (layout)
		{
			const layoutDetail = this.container.querySelectorAll('.landing-form-layout-img');
			for (let i = 0; i < layoutDetail.length; i++)
			{
				if (layoutDetail[i].dataset.layout === layout)
				{
					layoutDetail[i].style.display = 'block';
				}
				else
				{
					layoutDetail[i].style.display = 'none';
				}
			}
		},
	};

	/**
	 * For show/hide additional fields.
	 * @param HTMLElement form
	 */
	BX.Landing.ToggleAdditionalFields = function (form)
	{
		this.isOpen = false;
		this.form = form;
		this.hiddenRows = BX.convert.nodeListToArray(
			this.form.querySelectorAll(BX.Landing.ToggleAdditionalFields.SELECTOR_ROWS)
		);

		this.toggleContainer = this.form.querySelector(BX.Landing.ToggleAdditionalFields.SELECTOR_CONTAINER);
		BX.Event.bind(this.toggleContainer, 'click', this.onToggleClick.bind(this));

		if (window.location.hash)
		{
			const anchor = window.location.hash.substr(1);

			this.hiddenRows.forEach(row => {
				const id = row.dataset[BX.Landing.ToggleAdditionalFields.DATA_ROW_OPTION];
				if (id && id === anchor)
				{
					this.highlightHiddenRow(row);
				}
			});

			const mainOptionRow = this.form.querySelector(
				'[' + BX.Landing.ToggleAdditionalFields.DATA_MAIN_OPTION_NAME + '="' + anchor + '"]'
			);
			if (mainOptionRow)
			{
				this.highlightRow(mainOptionRow);
			}
		}
	}

	BX.Landing.ToggleAdditionalFields.SELECTOR_ROWS = '.landing-form-additional-row';
	BX.Landing.ToggleAdditionalFields.SELECTOR_CONTAINER = '.landing-form-additional-fields-js';
	BX.Landing.ToggleAdditionalFields.DATA_OPTION = 'landingAdditionalOption';
	BX.Landing.ToggleAdditionalFields.DATA_ROW_OPTION = 'landingAdditionalDetail';
	BX.Landing.ToggleAdditionalFields.DATA_ROW_OPTION_NAME = 'data-landing-additional-detail';
	BX.Landing.ToggleAdditionalFields.DATA_MAIN_OPTION_NAME = 'data-landing-main-option';
	BX.Landing.ToggleAdditionalFields.CLASS_HIGHLIGHT = 'landing-form-row-highlight';

	BX.Landing.ToggleAdditionalFields.prototype = {
		onToggleClick: function(event)
		{
			if (event.target.dataset[BX.Landing.ToggleAdditionalFields.DATA_OPTION])
			{
				this.onHeaderClick(event);
			}
			else
			{
				this.toggleRows();
			}
		},

		toggleRows: function()
		{
			return this.isOpen ? this.hideRows() : this.showRows();
		},

		hideRows: function()
		{
			const promises = [];
			this.hiddenRows.forEach(row => {
				if (row.scrollHeight > 0)
				{
					row.style.height = 0;
					promises.push(onTransitionEnd(row));
				}
			});

			BX.Dom.removeClass(this.form, 'landing-form-additional-open');
			this.isOpen = false;

			return Promise.all(promises);
		},

		showRows: function()
		{
			const promises = [];
			this.hiddenRows.forEach(row => {
				if (row.scrollHeight > 0)
				{
					row.style.height = 'auto';
					promises.push(onTransitionEnd(row));
				}
			});

			BX.Dom.addClass(this.form, 'landing-form-additional-open');
			this.isOpen = true;

			return Promise.all(promises);
		},

		onHeaderClick: function(event) {
			const option = event.target.dataset[BX.Landing.ToggleAdditionalFields.DATA_OPTION];
			if (option)
			{
				const detailSelector = '[' + BX.Landing.ToggleAdditionalFields.DATA_ROW_OPTION_NAME + ' = "' + option + '"]';
				const detailRow = this.form.querySelector(detailSelector);
				if (detailRow)
				{
					this.highlightHiddenRow(detailRow)
				}
			}
		},

		highlightHiddenRow: function (node)
		{
			const promiseShow = this.isOpen ? Promise.resolve() : this.showRows();
			promiseShow.then(() => {
				this.highlightRow(node);
			});
		},

		highlightRow: function (node)
		{
			BX.Dom.addClass(node, BX.Landing.ToggleAdditionalFields.CLASS_HIGHLIGHT);

			window.scrollTo({
				top: BX.pos(node).top,
				behavior: "smooth",
			});

			setTimeout(() => {
				BX.Dom.removeClass(node, BX.Landing.ToggleAdditionalFields.CLASS_HIGHLIGHT);
			}, 2500);
		},
	}

	/**
	 * GA metrika.
	 */
	BX.Landing.ExternalMetrika = function(fieldUseId, fieldSendClickId, fieldSendShowId)
	{
		if (fieldUseId.value === '')
		{
			fieldSendClickId.disabled = true;
			fieldSendShowId.disabled = true;
		}

		fieldUseId.addEventListener('input', onInput.bind(this));

		function onInput() {
			if (fieldUseId.value === '')
			{
				fieldSendClickId.disabled = true;
				fieldSendClickId.checked = false;

				fieldSendShowId.disabled = true;
				fieldSendShowId.checked = false;
			}
			else
			{
				fieldSendClickId.disabled = false;
				fieldSendShowId.disabled = false;
			}
		}
	};

	/**
	 * Change save button.
	 */

	BX.Landing.SaveBtn = function(saveBtn)
	{
		saveBtn.addEventListener('click', changeSaveBtn.bind(this));

		function changeSaveBtn() {
			saveBtn.classList.add('ui-btn-clock');
			saveBtn.style.cursor = "default";
			saveBtn.style.pointerEvents = "none";
		}
	};

	/**
	 * Cookies.
	 */
	BX.Landing.Cookies = function()
	{
		this.bgPickerBtn = document.querySelector('.landing-form-cookies-color-bg');
		this.textPickerBtn = document.querySelector('.landing-form-cookies-color-text');
		this.simplePreview = document.querySelector('.landing-form-cookies-settings-type-simple');
		this.advancedPreview = document.querySelector('.landing-form-cookies-settings-type-advanced');
		this.positions = document.querySelectorAll('.landing-form-cookies-position-item');
		this.inputApp = document.querySelector('#radio-cookies-mode-A');
		this.inputInfo = document.querySelector('#radio-cookies-mode-I');
		this.settings = document.querySelector('.landing-form-cookies-settings-wrapper');

		this.bgPicker = new BX.ColorPicker({
			bindElement: this.bgPickerBtn,
			popupOptions: {angle: false, offsetTop: 5},
			onColorSelected: this.onBgColorSelected.bind(this),
			colors: BX.Landing.ColorPicker.prototype.setColors()
		});

		this.textPicker = new BX.ColorPicker({
			bindElement: this.textPickerBtn,
			popupOptions: {angle: false, offsetTop: 5},
			onColorSelected: this.onTextColorSelected.bind(this),
			colors: BX.Landing.ColorPicker.prototype.setColors()
		});

		this.setSelectedBgColor(this.bgPickerBtn.value);
		this.setSelectedTextColor(this.textPickerBtn.value);
		this.hideCookiesSettings();

		this.bindEvents();
	};

	BX.Landing.Cookies.prototype = {

		bindEvents: function () {
			this.positions.forEach(function (position) {
				position.addEventListener('click', this.onSelectCookiesPosition.bind(this));
			}.bind(this));

			this.bgPickerBtn.addEventListener('click', this.showBgPicker.bind(this));
			this.textPickerBtn.addEventListener('click', this.showTextPicker.bind(this));
			this.inputInfo.addEventListener('change', this.hideCookiesSettings.bind(this));
			this.inputApp.addEventListener('change', this.showCookiesSettings.bind(this));

		},

		onBgColorSelected: function() {
			var color = this.bgPicker.getSelectedColor();
			this.setSelectedBgColor(color);
		},

		onTextColorSelected: function() {
			var color = this.textPicker.getSelectedColor();
			this.setSelectedTextColor(color);
		},

		onSelectCookiesPosition: function(event) {
			this.positions.forEach(function (position) {
				if (position.classList.contains('landing-form-cookies-position-item-selected'))
				{
					position.classList.remove('landing-form-cookies-position-item-selected');
				}
			}.bind(this));
			event.currentTarget.classList.add('landing-form-cookies-position-item-selected');
		},

		showBgPicker: function() {
			this.bgPicker.open();
		},

		showTextPicker: function() {
			this.textPicker.open();
		},

		setSelectedBgColor: function(color) {
			this.bgPickerBtn.style.background = color;
			this.bgPickerBtn.value = color;
			this.simplePreview.style.background = color;
			this.advancedPreview.style.background = color;
		},

		setSelectedTextColor: function(color) {
			this.textPickerBtn.style.background = color;
			this.textPickerBtn.value = color;
			this.advancedPreview.style.color = color;

			var svgList = document.querySelectorAll('.landing-form-cookies-settings-preview-svg');
			svgList.forEach(function(svg)
			{
				svg.style.fill = color;
			});
		},

		hideCookiesSettings: function ()
		{
			if (this.inputInfo.checked)
			{
				this.settings.style.height = '0';
				this.settings.style.opacity = '0';
			}
		},

		showCookiesSettings: function() {
			if (this.inputApp.checked)
			{
				this.settings.style.height = this.settings.scrollHeight + 'px';
				this.settings.style.opacity = '1';
				onTransitionEnd(this.settings).then(() => {
					this.settings.height = 'auto';
				});
			}
		}

	}

	/**
	 * B24 widget change custom color
	 * @param typeSelector
	 * @param colorInput
	 * @constructor
	 */
	BX.Landing.B24ButtonColor = function(typeSelector, colorInput)
	{
		this.typeSelector = typeSelector;
		this.colorInput = colorInput;
		this.valueControlWrap = BX.findParent(colorInput, {class:'ui-ctl'});

		bind(typeSelector, "change", this.checkVisibility.bind(this));

		this.checkVisibility();
	};

	BX.Landing.B24ButtonColor.prototype = {
		checkVisibility: function()
		{
			this.valueControlWrap.hidden = this.typeSelector.value !== 'custom';
			this.colorInput.labels.forEach(label => {
				label.hidden = this.typeSelector.value !== 'custom';
			});
		}
	};

	/**
	 * Alert for fields, then need republication after change
	 * @param inputIds
	 * @constructor
	 */
	BX.Landing.NeedPublicationField = function(inputIds)
	{
		inputIds.forEach(function(id)
		{
			var input = BX(id);
			if (input)
			{
				BX.bind(input, 'click', function ()
				{
					BX.UI.Dialogs.MessageBox.alert(BX.Loc.getMessage('LANDING_EDIT_NEED_PUBLICATION'));
				});
			}
		})
	};

	/**
	 * For setting color palette
	 * @param HTMLElement allColorsNode
	 * @param ?HTMLElement customColorNode
	 */
	BX.Landing.ColorPalette = function(allColorsNode, customColorNode)
	{
		this.allColorsNode = allColorsNode;
		this.customColorNode = null;
		this.colorPickerNode = null;
		if (typeof customColorNode !== 'undefined' && customColorNode)
		{
			this.customColorNode = customColorNode;
			this.colorPickerNode = customColorNode.querySelector('input[type="text"]');
		}

		this.init();

		return this;
	};

	BX.Landing.ColorPalette.prototype = {
		/**
		 * Initializes template preview elements
		 */
		init: function()
		{
			// themes
			let colorItems;
			if (this.allColorsNode)
			{
				colorItems = slice(this.allColorsNode.children);
			}
			if (this.customColorNode)
			{
				colorItems = colorItems.concat([this.customColorNode]);
			}
			if (colorItems)
			{
				colorItems.forEach(this.initSelectableItem, this);
			}

			if (colorItems)
			{
				this.setColor();
			}
		},

		setColor: function(theme) {
			if (theme === undefined)
			{
				this.color = data(this.getActiveColorNode(), "data-value");
			}
			else
			{
				this.color = theme;
			}

			if (this.colorPickerNode)
			{
				this.colorPickerNode.setAttribute('value', this.color);
			}
		},

		getActiveColorNode: function()
		{
			let active;
			if (this.allColorsNode)
			{
				active = this.allColorsNode.querySelector(".active");
			}
			if (!active && this.customColorNode && BX.Dom.hasClass(this.customColorNode, 'active'))
			{
				active = this.customColorNode;
			}
			// by default - first
			if (!active && this.allColorsNode)
			{
				active = this.allColorsNode.firstElementChild;
			}
			return active;
		},

		/**
		 * Initializes selectable items
		 * @param {HTMLElement} item
		 */
		initSelectableItem: function(item)
		{
			bind(item, "click", proxy(this.onSelectableItemClick, this));
		},

		/**
		 * Handles click on selectable item
		 * @param event
		 */
		onSelectableItemClick: function(event)
		{
			event.preventDefault();

			// themes
			if (event.currentTarget.parentElement === this.allColorsNode)
			{
				if (event.currentTarget.hasAttribute('data-value'))
				{
					removeClass(this.getActiveColorNode(), "active");
					addClass(event.currentTarget, "active");
					this.setColor(data(event.currentTarget, 'data-value'));
				}
			}

			this.currentTarget = event.currentTarget;
			BX.Event.EventEmitter.subscribe('BX.Landing.ColorPickerTheme:onSelectColor', () => {
				if (this.currentTarget.hasAttribute('data-value'))
				{
					removeClass(this.getActiveColorNode(), "active");
					addClass(this.currentTarget, "active");
					this.setColor(data(this.currentTarget, 'data-value'));
				}
			});
		},
	};

	/**
	 * Extend main colorpicker for landings
	 */
	BX.Landing.ColorPicker = function(node, params)
	{
		let defaultColor;
		if (params)
		{
			defaultColor = params.defaultColor;
		}

		this.picker = new BX.ColorPicker({
			bindElement: node,
			popupOptions: {angle: false, offsetTop: 5},
			onColorSelected: this.onColorSelected.bind(this),
			colors: this.setColors(),
			selectedColor: defaultColor,
		});

		this.input = node;
		this.colorPickerNode = node.parentElement;
		BX.addClass(this.colorPickerNode, 'ui-colorpicker');

		this.colorIcon = BX.create('span', {
			props: {
				className: 'ui-colorpicker-color'
			}
		});
		BX.insertBefore(this.colorIcon, this.input);

		this.colorValue = node.value;
		if (!this.colorValue && defaultColor)
		{
			node.value = defaultColor;
			this.colorValue = node.value;
		}
		if (this.colorValue)
		{
			BX.adjust(this.colorIcon, {
				attrs: {
					style: 'background-color:' + this.colorValue
				}
			});

			BX.addClass(this.colorPickerNode, 'ui-colorpicker-selected');
		}

		this.clearBtn = BX.create('span', {
			props: {
				className: 'ui-colorpicker-clear'
			}
		});
		BX.insertAfter(this.clearBtn, this.input);

		BX.bind(this.colorPickerNode, 'click', this.show.bind(this));
		BX.bind(this.clearBtn, 'click', this.clear.bind(this));

	};

	BX.Landing.ColorPicker.prototype = {
		onColorSelected: function(color)
		{
			this.colorPickerNode.classList.add('ui-colorpicker-selected');
			this.colorIcon.style.backgroundColor = color;
			this.input.value = color;
			BX.Event.EventEmitter.emit(this, 'BX.Landing.ColorPicker:onSelectColor');
		},
		show: function(event)
		{
			if (event.target === this.clearBtn)
			{
				return;
			}

			this.picker.open();
		},
		clear: function()
		{
			this.colorPickerNode.classList.remove('ui-colorpicker-selected');
			this.input.value = '';
			this.picker.setSelectedColor(null);
			BX.Event.EventEmitter.emit(this, 'BX.Landing.ColorPicker:onClearColorPicker');
		},
		setColors: function()
		{
			return [
				["#f5f5f5", "#eeeeee", "#e0e0e0", "#9e9e9e", "#757575", "#616161", "#212121"],
				["#cfd8dc", "#b0bec5", "#90a4ae", "#607d8b", "#546e7a", "#455a64", "#263238"],
				["#d7ccc8", "#bcaaa4", "#a1887f", "#795548", "#6d4c41", "#5d4037", "#3e2723"],
				["#ffccbc", "#ffab91", "#ff8a65", "#ff5722", "#f4511e", "#e64a19", "#bf360c"],
				["#ffe0b2", "#ffcc80", "#ffb74d", "#ff9800", "#fb8c00", "#f57c00", "#e65100"],
				["#ffecb3", "#ffe082", "#ffd54f", "#ffc107", "#ffb300", "#ffa000", "#ff6f00"],
				["#fff9c4", "#fff59d", "#fff176", "#ffeb3b", "#fdd835", "#fbc02d", "#f57f17"],
				["#f0f4c3", "#e6ee9c", "#dce775", "#cddc39", "#c0ca33", "#afb42b", "#827717"],
				["#dcedc8", "#c5e1a5", "#aed581", "#8bc34a", "#7cb342", "#689f38", "#33691e"],
				["#c8e6c9", "#a5d6a7", "#81c784", "#4caf50", "#43a047", "#388e3c", "#1b5e20"],
				["#b2dfdb", "#80cbc4", "#4db6ac", "#009688", "#00897b", "#00796b", "#004d40"],
				["#b2ebf2", "#80deea", "#4dd0e1", "#00bcd4", "#00acc1", "#0097a7", "#006064"],
				["#b3e5fc", "#81d4fa", "#4fc3f7", "#03a9f4", "#039be5", "#0288d1", "#01579b"],
				["#bbdefb", "#90caf9", "#64b5f6", "#2196f3", "#1e88e5", "#1976d2", "#0d47a1"],
				["#c5cae9", "#9fa8da", "#7986cb", "#3f51b5", "#3949ab", "#303f9f", "#1a237e"],
				["#d1c4e9", "#b39ddb", "#9575cd", "#673ab7", "#5e35b1", "#512da8", "#311b92"],
				["#e1bee7", "#ce93d8", "#ba68c8", "#9c27b0", "#8e24aa", "#7b1fa2", "#4a148c"],
				["#f8bbd0", "#f48fb1", "#f06292", "#e91e63", "#d81b60", "#c2185b", "#880e4f"],
				["#ffcdd2", "#ef9a9a", "#e57373", "#f44336", "#e53935", "#d32f2f", "#b71c1c"]
			].map(function(item, index, arr)
			{
				return arr.map(function(row)
				{
					return row[index];
				});
			})
		}
	};
})();
