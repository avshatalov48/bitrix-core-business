function deleteAccessRow(link)
{
	landingAccessSelected[BX.data(BX(link), 'id')] = false;
	BX.remove(BX.findParent(BX(link), {tag: 'tr'}, true));
}

(function() {

	'use strict';

	BX.namespace('BX.Landing');

	var slice = BX.Landing.Utils.slice;
	var proxy = BX.Landing.Utils.proxy;
	var bind = BX.Landing.Utils.bind;
	var unbind = BX.Landing.Utils.unbind;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var isNumber = BX.Landing.Utils.isNumber;
	var style = BX.Landing.Utils.style;
	var data = BX.Landing.Utils.data;

	/**
	 * For setting color palette
	 */
	BX.Landing.ColorPalette = function(params)
	{
		this.themesPalete = document.querySelector(".landing-template-themes");
		this.themesSiteCustomColorNode = document.querySelector(".landing-template-custom-color");
		this.init();

		return this;
	};

	BX.Landing.ColorPalette.getInstance = function(params)
	{
		return (
			BX.Landing.ColorPalette.instance ||
			(BX.Landing.ColorPalette.instance = new BX.Landing.ColorPalette(params))
		);
	};


	BX.Landing.ColorPalette.prototype = {
		/**
		 * Initializes template preview elements
		 */
		init: function()
		{
			// themes
			var colorItems = slice(this.themesPalete.children);
			if(this.themesSiteColorNode)
			{
				colorItems = colorItems.concat(slice(this.themesSiteColorNode.children));
			}
			if(this.themesSiteCustomColorNode)
			{
				colorItems = colorItems.concat(slice(this.themesSiteCustomColorNode.children));
			}
			colorItems.forEach(this.initSelectableItem, this);

			// site group
			if(this.siteGroupPalette )
			{
				var siteGroupItems = slice(this.siteGroupPalette.children);
				siteGroupItems.forEach(this.initSelectableItem, this);
			}

			bind(this.previewFrame, "load", this.onFrameLoad);
			bind(this.closeButton, "click", this.onCancelButtonClick);

			if (!this.disableClickHandler)
			{
				bind(this.createButton, "click", this.onCreateButtonClick);
			}

			this.setColor();
		},

		setColor: function(theme) {
			if(theme === undefined)
			{
				this.color = data(this.getActiveColorNode(), "data-value");
			}
			else
			{
				this.color = theme;
			}

			var colorpicker = document.getElementById('colorpicker');
			if(colorpicker)
			{
				colorpicker.setAttribute('value', this.color);
			}
		},

		getActiveColorNode: function()
		{
			var active = this.themesPalete.querySelector(".active");
			if(!active && this.themesSiteColorNode)
			{
				active = this.themesSiteColorNode.querySelector(".active");
			}
			if(!active && this.themesSiteCustomColorNode)
			{
				active = this.themesSiteCustomColorNode.querySelector(".active");
			}
			// by default - first
			if(!active)
			{
				active = this.themesPalete.firstElementChild;
			}

			return active;
		},

		getActiveSiteGroupItem: function()
		{
			return this.siteGroupPalette.querySelector(".active");
		},

		/**
		 * Loads template preview
		 * @param {string} src
		 * @return {Function}
		 */
		loadPreview: function(src)
		{
			return function()
			{
				return new Promise(function(resolve) {
					if (this.previewFrame.src !== src)
					{
						this.previewFrame.src = src;
						this.previewFrame.onload = function() {
							resolve(this.previewFrame);
						}.bind(this);
						return;
					}

					resolve(this.previewFrame);
				}.bind(this));
			}.bind(this)
		},

		/**
		 * Shows preview loader
		 * @return {Promise}
		 */
		showLoader: function()
		{
			return new Promise(function(resolve) {
				void this.loader.show(this.loaderContainer);
				addClass(this.imageContainer, "landing-template-preview-overlay");
				resolve();
			}.bind(this));
		},

		/**
		 * Hides loader
		 * @return {Function}
		 */
		hideLoader: function()
		{
			return function(iframe)
			{
				return new Promise(function(resolve) {
					void this.loader.hide();
					removeClass(this.imageContainer, "landing-template-preview-overlay");
					resolve(iframe);
				}.bind(this));
			}.bind(this);
		},

		/**
		 * Creates delay
		 * @param delay
		 * @return {Function}
		 */
		delay: function(delay)
		{
			delay = isNumber(delay) ? delay : 0;

			return function(image)
			{
				return new Promise(function(resolve) {
					setTimeout(resolve.bind(null, image), delay);
				});
			}
		},

		/**
		 * Gets value
		 * @return {Object}
		 */
		getValue: function()
		{
			var result = {};

			if(this.themesSiteColorNode && this.getActiveColorNode().parentElement === this.themesSiteColorNode)
			{
				// add theme_use_site flag
				result[data(this.themesSiteColorNode, "data-name")] = 'Y';
			}
			if(this.siteGroupPalette)
			{
				result[data(this.siteGroupPalette, "data-name")] = data(this.getActiveSiteGroupItem(), "data-value");
			}
			result[data(this.themesPalete, "data-name")] = data(this.getActiveColorNode(), "data-value");
			result[data(this.themesSiteCustomColorNode, "data-name")] = data(this.getActiveColorNode(), "data-value");
			result[data(this.title, "data-name")] = this.title.value;
			result[data(this.description, "data-name")] = this.description.value;

			return result;
		},

		/**
		 * Handles click event on close button
		 * @param {MouseEvent} event
		 */
		onCancelButtonClick: function(event)
		{
			event.preventDefault();
			top.BX.SidePanel.Instance.close();
		},

		/**
		 * Handles click event on create button
		 * @param {MouseEvent} event
		 */
		onCreateButtonClick: function(event)
		{
			event.preventDefault();

			if(this.isStore() && this.IsLoadedFrame) {
				this.loaderText = BX.create("div", { props: { className: "landing-template-preview-loader-text"},
					text: this.messages.LANDING_LOADER_WAIT});

				this.progressBar = new BX.UI.ProgressBar({
					column: true
				});

				this.progressBar.getContainer().classList.add("ui-progressbar-landing-preview");

				this.loaderContainer.appendChild(this.loaderText);
				this.loaderContainer.appendChild(this.progressBar.getContainer());
			}

			if (this.isStore())
			{
				if (this.IsLoadedFrame)
				{
					this.showLoader();
					this.initCatalogParams();
					this.createCatalog();
				}
			}
			else
			{
				this.showLoader()
					.then(this.delay(200))
					.then(function() {
						this.finalRedirectAjax(
							this.getCreateUrl()
						);
					}.bind(this));
			}
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
			if (
				event.currentTarget.parentElement === this.themesPalete ||
				(this.themesSiteColorNode && event.currentTarget.parentElement === this.themesSiteColorNode) ||
				(this.themesSiteCustomColorNode && event.currentTarget.parentElement === this.themesSiteCustomColorNode)
			)
			{
				if (event.currentTarget.hasAttribute('data-value'))
				{
					removeClass(this.getActiveColorNode(), "active");
					addClass(event.currentTarget, "active");
					this.setColor(data(event.currentTarget, 'data-value'));
				}
			}
		},

		isStore: function()
		{
			return this.createStore;
		}
	};

	/**
	 * For edit title.
	 */
	BX.Landing.EditTitleForm = function (node, additionalWidth, isEventTargetNode, display)
	{
		this.btn = node.querySelector('.ui-title-input-btn-js');
		this.label = node.querySelector('.ui-editable-field-label-js');
		this.input = node.querySelector('.ui-editable-field-input-js');
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
	 * For additional fields.
	 */
	BX.Landing.ToggleFormFields = function (node)
	{
		this.form = node;
		this.toggleBtn = node.querySelector('.landing-form-collapse-block-js');
		this.formInner = node.querySelector('.landing-form-inner-js');
		this.tableWparp = node.querySelector('.landing-form-table-wrap-js');
		this.sectionWrap = node.querySelector('.landing-additional-alt-promo-wrap');
		this.startHeight = 0;
		this.endHeight = 0;
		this.isHidden = true;

		this.clickHandler = this.clickHandler.bind(this);
		this.setHeightAuto = this.setHeightAuto.bind(this);
		this.removeClassName = this.removeClassName.bind(this);


		this.attributeMainOption = 'data-landing-main-option';
		this.attributeOption = 'data-landing-additional-option';
		this.attributeDetail = 'data-landing-additional-detail';

		var sectionList = this.sectionWrap.children;
		sectionList = BX.convert.nodeListToArray(sectionList);
		sectionList.forEach(this.initSection, this);

		BX.bind(this.toggleBtn, 'click', this.clickHandler);

		if(window.location.hash)
		{
			var anchor = window.location.hash.substr(1);

			sectionList.forEach(function (section) {
				var id = section.getAttribute(this.attributeOption);

				if (id === anchor)
				{
					BX.fireEvent(section, 'click');
				}
			}, this);

			var neededMainOption = this.formInner.querySelector('[' + this.attributeMainOption + '="' + anchor + '"]');
			if(neededMainOption)
			{
				this.highlightSection(neededMainOption);
			}
		}
	};
	BX.Landing.ToggleFormFields.prototype =
	{
		showRows : function ()
		{
			this.startHeight = this.formInner.offsetHeight;
			this.formInner.style.height = this.startHeight + 'px';
			this.form.classList.add('landing-form-collapsed-open');
			this.endHeight = this.tableWparp.offsetHeight + parseInt(BX.style(this.tableWparp, 'marginBottom'));
			this.formInner.style.height = this.endHeight + 'px';

			BX.bind(this.formInner, 'transitionend', this.setHeightAuto);

			this.isHidden = false;
		},

		closeRows : function ()
		{
			this.formInner.style.height = this.endHeight + 'px';

			setTimeout(function () {
				this.formInner.style.height = this.startHeight + 'px';
			}.bind(this),70);

			BX.bind(this.formInner, 'transitionend', this.removeClassName);

			this.isHidden = true;
		},

		clickHandler : function ()
		{
			if(this.isHidden)
				this.showRows();
			else
				this.closeRows();
		},

		setHeightAuto : function ()
		{
			this.formInner.style.height = 'auto';
			BX.unbind(this.formInner, 'transitionend', this.setHeightAuto);
		},

		removeClassName : function ()
		{
			this.form.classList.remove('landing-form-collapsed-open');
			BX.unbind(this.formInner, 'transitionend', this.removeClassName);
		},

		initSection : function (section)
		{
			BX.bind(section, "click", BX.delegate(function(e){
				e.stopPropagation();
				this.showSection(section);
			}, this))
		},

		showSection : function(section)
		{
			this.showRows();
			var id = section.getAttribute(this.attributeOption);
			var detailNode = this.formInner.querySelector('[' + this.attributeDetail + '="' + id + '"]');
			this.highlightSection(detailNode);
		},

		highlightSection: function(node)
		{
			BX.addClass(node, "landing-form-hidden-row-highlight");

			setTimeout(function(){
				var position = BX.pos(node);

				window.scrollTo({
					top: position.top,
					behavior: "smooth"
				});
			}, 300);

			setTimeout(function(){
				BX.removeClass(node, "landing-form-hidden-row-highlight");
			}, 1500);
		}
	};

	/**
	 * Colorpicker.
	 */
	BX.Landing.ColorPicker = function(node)
	{
		this.picker = new BX.ColorPicker({
			bindElement: node,
			popupOptions: {angle: false, offsetTop: 5},
			onColorSelected: this.onColorSelected.bind(this),
			colors: this.setColors()
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
		},
		show: function(event)
		{
			if (event.target == this.clearBtn)
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

	/**
	 * Some additional JS.
	 */
	BX.Landing.CustomFields = function (list)
	{
		list.forEach(function(item)
		{
			BX.bind(item.field, 'keyup', function ()
			{
				if(item.length)
				{
					if(item.field.value.length <= item.length)
					{
						item.node.textContent = item.field.value;
					}
					else
					{
						item.node.textContent = item.field.value.substring(0, item.length);
					}
				}
				else {
					item.node.textContent = item.field.value;
				}
			});
		})
	};

	/**
	 * Favicon change.
	 */
	BX.Landing.Favicon = function()
	{
		var editLink = BX('landing-form-favicon-change');
		var editInput = BX('landing-form-favicon-input');
		var editValue = BX('landing-form-favicon-value');
		var editForm = BX('landing-form-favicon-form');
		var editSrc = BX('landing-form-favicon-src');
		var editError = BX('landing-form-favicon-error');

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
	 * Custom 404.
	 */
	BX.Landing.Custom404 = function()
	{
		var select = BX('landing-form-404-select');
		BX.bind(select, 'change', function ()
		{
			if(this.value === '')
			{
				this.parentNode.style.height = getComputedStyle(this.parentNode).height;
				BX('checkbox-404-use').checked = false;
			}
		});
		BX.bind(BX('checkbox-404-use'), 'change', function ()
		{
			if(!this.checked)
			{
				select.value = ''
			}
		});
	};

	/**
	 * Custom 503.
	 */
	BX.Landing.Custom503 = function()
	{
		var select = BX('landing-form-503-select');
		if (!select)
		{
			return;
		}
		BX.bind(select, 'change', function ()
		{
			if(this.value === '')
			{
				this.parentNode.style.height = getComputedStyle(this.parentNode).height;
				BX('checkbox-503-use').checked = false;
			}
		});
		BX.bind(BX('checkbox-503-use'), 'change', function ()
		{
			if(!this.checked)
			{
				select.value = ''
			}
		});
	};

	/**
	 * Copyright on/off.
	 */
	BX.Landing.Copyright = function()
	{
		BX.bind(BX('checkbox-copyright'), 'change', function ()
		{
			var formAction = BX('landing-site-set-form').getAttribute('action');
			formAction = formAction.replace(/&feature_copyright=[YN]/, '');
			formAction += '&feature_copyright=' + (this.checked ? 'Y' : 'N');
			BX('landing-site-set-form').setAttribute('action', formAction)
		});
	};

	/**
	 * Rights.
	 */
	BX.Landing.Access = function(params)
	{
		var selected = landingAccessSelected;
		var name = 'RIGHTS';
		var tbl = BX('landing-' + name.toLowerCase() + '-table');
		var select = params.select;
		var inc = params.inc;

		BX.Access.Init({
			other: {
				disabled_cr: true
			}
		});

		BX.Access.SetSelected(selected, name);

		function showForm()
		{
			BX.Access.ShowForm({callback: function(obSelected)
				{
					for (var provider in obSelected)
					{
						if (obSelected.hasOwnProperty(provider))
						{
							for (var id in obSelected[provider])
							{
								if (obSelected[provider].hasOwnProperty(id))
								{
									var cnt = tbl.rows.length;
									var row = tbl.insertRow(cnt-1);
									row.classList.add("landing-form-rights");

									selected[id] = true;
									row.insertCell(-1);
									row.insertCell(-1);
										row.cells[0].innerHTML = BX.Access.GetProviderName(provider) + ' ' +
										BX.util.htmlspecialchars(obSelected[provider][id].name) + ':' +
										'<input type="hidden" name="fields[' + name + '][ACCESS_CODE][]" value="' + id + '">';
									row.cells[0].classList.add("landing-form-rights-right");
									row.cells[1].classList.add("landing-form-rights-left");
									row.cells[1].innerHTML = select.replace('#inc#', inc++) + ' ' + '<a href="javascript:void(0);" onclick="deleteAccessRow(this);" data-id="' + id + '" class="landing-form-rights-delete"></a>';
								}
							}
						}
					}
				}, bind: name})
		}

		BX('landing-rights-form').addEventListener('click', showForm.bind(this));
	};

	/**
	 * Layout.
	 */
	BX.Landing.Layout = function(params)
	{
		var layoutBlockContainer = document.querySelector('.landing-form-layout-block-container');
		var area = [];
		var layouts = document.querySelectorAll('.landing-form-layout-item');
		var detailLayoutContainer = document.querySelector('.landing-form-layout-detail');
		var layoutForm = document.querySelector('.landing-form-page-layout');
		var gaSendClickCheckbox = document.getElementById('field-gacounter_send_click-use');
		var gaSendClickSelect = document.getElementById('field-gacounter_click_type-use');
		layouts = Array.prototype.slice.call(layouts, 0);
		params.messages = params.messages || {};

		createBlocks(layouts[0].dataset.block);

		layouts.forEach(function (item)
		{
			item.addEventListener('click', handleLayoutClick.bind(this));
		});

		function handleLayoutClick(event) {
			var layoutItem = event.target.parentNode;

			var layoutItemSelected = document.querySelector('.landing-form-layout-item-selected');
			if(layoutItemSelected) {
				layoutItemSelected.classList.remove('landing-form-layout-item-selected');
			}

			changeLayout (layoutItem.dataset.block, layoutItem.dataset.layout);
		}

		function changeLayout(block, layout)
		{
			layoutForm.classList.remove('landing-form-page-layout-short');
			detailLayoutContainer.classList.remove('landing-form-layout-detail-hidden');

			createBlocks(block);

			if (typeof layout !== 'undefined')
			{
				changeLayoutImg(layout);
			}

			BX('layout-tplrefs').value = '';
		}

		if (typeof params.areasCount !== 'undefined')
		{
			changeLayout(params.areasCount, params.current);
		}

		function changeLayoutImg(layout)
		{

			var layoutDetail = document.querySelectorAll('.landing-form-layout-img');
			for (var i = 0; i < layoutDetail.length; i++)
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
		}

		function createBlocks(blocks)
		{
			var saveRefs = BX('layout-tplrefs').value.split(',');
			area = [];
			layoutBlockContainer.innerHTML = '';
			var rebuildHiddenField = function()
			{
				var refs = '';
				for (var i= 0, c = area.length; i < c; i++)
				{
					refs += (i+1) + ':' +
						(area[i].getValue() ? area[i].getValue().substr(8) : 0) +
						',';
				}
				BX('layout-tplrefs').value = refs;
			};
			for (var i = 0; i < blocks; i++)
			{
				var block = BX.create('div', {
					attrs: {
						className: 'landing-form-layout-block-item'
					}
				});

				var numberBlock = i + 1;
				var linkContent = '';

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

				var layoutField = new BX.Landing.UI.Field.LinkURL({
					title: params.messages.area + ' #' + numberBlock,
					content: linkContent,
					textOnly: true,
					disableCustomURL: true,
					disableBlocks: true,
					disallowType: true,
					enableAreas: true,
					allowedTypes: [
						BX.Landing.UI.Field.LinkURL.TYPE_PAGE
					],
					options: {
						siteId: params.siteId,
						landingId: params.landingId,
						filter: {
							'=TYPE': params.type
						}
					},
					onInit: BX.delegate(rebuildHiddenField),
					onInput: BX.delegate(rebuildHiddenField)
				});

				area[i] = layoutField;
				block.appendChild(layoutField.layout);
				layoutBlockContainer.appendChild(block);
			}
		}

		var tplCheck = BX('layout-tplrefs-check');

		tplCheck.addEventListener('click', handleCheckBoxClick.bind(this));

		function handleCheckBoxClick()
		{

				BX('layout-tplrefs').value = '';
				detailLayoutContainer.classList.add('landing-form-layout-detail--hidden');
				layoutForm.classList.add('landing-form-page-layout-short');

				var inputs = document.querySelectorAll('.layout-switcher');
				inputs = Array.prototype.slice.call(inputs, 0);

				inputs.forEach(function (item)
				{
					item.checked = false;
				});
		}

		var arrowContainer = document.querySelector('.landing-form-select-buttons');
		var layoutContainer = document.querySelector('.landing-form-list-inner');
		arrowContainer.addEventListener('click', handlerOnArrowClick.bind(this));

		function handlerOnArrowClick(event) {
			if (event.target.classList.contains('landing-form-select-next'))
			{
				layoutContainer.classList.add('landing-form-list-inner-prev');
			}
			else
			{
				layoutContainer.classList.remove('landing-form-list-inner-prev');
			}
		}

		function checkGaSendClickCheckbox() {

			var parentNode = gaSendClickCheckbox.closest('.ui-checkbox-hidden-input-inner');

			if (gaSendClickCheckbox.checked)
			{
				gaSendClickSelect.classList.add('ui-select-gacounter-show');
				parentNode.classList.add('ui-checkbox-hidden-input-inner-gacounter');
			}
			else
			{
				gaSendClickSelect.classList.remove('ui-select-gacounter-show');
				parentNode.classList.remove('ui-checkbox-hidden-input-inner-gacounter');
			}
		}

		if (gaSendClickCheckbox)
		{
			gaSendClickCheckbox.addEventListener('click', function()
			{
				checkGaSendClickCheckbox();
			}.bind(this));

			checkGaSendClickCheckbox();
		}
	};

	/**
	 * GA metrika.
	 */

	BX.Landing.ExternalMetrika = function()
	{
		if (!BX('field-gacounter_counter-use'))
		{
			return;
		}

		var inputGa = BX('field-gacounter_counter-use');
		var inputGaClick = BX('field-gacounter_send_click-use');
		var inputGaShow = BX('field-gacounter_send_show-use');

		if (inputGa.value === '')
		{
			inputGaClick.disabled = true;
			inputGaShow.disabled = true;
		}

		inputGa.addEventListener('input', onInput.bind(this));

		function onInput() {
			if (inputGa.value === '')
			{
				inputGaClick.disabled = true;
				inputGaClick.checked = false;

				inputGaShow.disabled = true;
				inputGaShow.checked = false;
			}
			else
			{
				inputGaClick.disabled = false;
				inputGaShow.disabled = false;
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
	 * Change iblock select.
	 */
	BX.Landing.IblockSelect = function()
	{
		this.section = BX("row_section_id");
		this.init(this.section);
	};

	BX.Landing.IblockSelect.prototype = {

		init: function(section) {
			if (!BX("settings_iblock_id").value)
			{
				section.classList.add("landing-form-field-section-hidden");
			}
			else
			{
				section.classList.remove("landing-form-field-section-hidden");
			}
		}
	};

	/**
	 * Cookies.
	 */
	BX.Landing.Cookies = function(params)
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
		this.hideCookiesSettings(this.inputInfo);

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

		hideCookiesSettings: function(event) {
			var hiddenBlock;

			if (event.target)
			{
				hiddenBlock = event.target.closest('.ui-checkbox-hidden-input-inner');
			}
			else
			{
				hiddenBlock = event.closest('.ui-checkbox-hidden-input-inner');
			}

			if (this.inputInfo.checked)
			{
				hiddenBlock.style.height = '330px';
				this.settings.style.opacity = '0';
			}
		},

		showCookiesSettings: function(event) {
			var hiddenBlock = event.target.closest('.ui-checkbox-hidden-input-inner');

			if (this.inputApp.checked)
			{
				hiddenBlock.style.height = '617px';
				this.settings.style.opacity = '1';
			}
		}

	}

	BX.Landing.B24ButtonColor = function(typeControl, valueControl)
	{
		this.colorCustomType = 'custom';

		this.typeControl = typeControl;
		this.valueControl = valueControl;
		this.valueControlWrap = BX.findParent(valueControl, {class:'ui-control-wrap'});

		bind(typeControl, "change", BX.delegate(this.checkVisibility, this));

		this.checkVisibility();
	};

	BX.Landing.B24ButtonColor.prototype = {
		checkVisibility: function()
		{
			if(this.typeControl.value === 'custom')
			{
				this.valueControlWrap.hidden = false;
			}
			else
			{
				this.valueControlWrap.hidden = true;
			}
		}
	};

	BX.Landing.NeedPublicationField = function(inputIds)
	{
		inputIds.forEach(function(id)
		{
			var input = BX(id);
			if (input)
			{
				BX.bind(input, 'click', function ()
				{
					BX.UI.Dialogs.MessageBox.alert(BX.message('LANDING_EDIT_NEED_PUBLICATION'));
				});
			}
		})
	};
})();
