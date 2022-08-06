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
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var isNumber = BX.Landing.Utils.isNumber;
	var data = BX.Landing.Utils.data;

	/**
	 * For setting color palette
	 */
	BX.Landing.ColorPalette = function()
	{
		this.themesPalete = document.querySelector(".landing-template-preview-themes");
		this.themesSiteCustomColorNode = document.querySelector(".landing-template-preview-site-custom-color");

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
			var colorItems;
			if (this.themesPalete)
			{
				colorItems = slice(this.themesPalete.children);
			}
			if(this.themesSiteColorNode)
			{
				colorItems = colorItems.concat(slice(this.themesSiteColorNode.children));
			}
			if(this.themesSiteCustomColorNode)
			{
				colorItems = colorItems.concat(slice(this.themesSiteCustomColorNode.children));
			}
			if (colorItems)
			{
				colorItems.forEach(this.initSelectableItem, this);
			}

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

			if (colorItems)
			{
				this.setColor();
			}
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
			var active;
			if (this.themesPalete)
			{
				active = this.themesPalete.querySelector(".active");
			}
			if (!active && this.themesSiteColorNode)
			{
				active = this.themesSiteColorNode.querySelector(".active");
			}
			if (!active && this.themesSiteCustomColorNode)
			{
				active = this.themesSiteCustomColorNode.querySelector(".active");
			}
			// by default - first
			if (!active && this.themesPalete)
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
	 * Colorpicker.
	 */
	BX.Landing.ColorPicker = function(node, params)
	{
		var defaultColor;
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
