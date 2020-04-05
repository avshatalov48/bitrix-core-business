;(function() {

"use strict";

BX.namespace("BX.ColorPicker");

/**
 *
 * @param {object} options
 * @param {Element} [options.bindElement]
 * @param {object} [options.popupOptions]
 * @param {string} [options.selectedColor]
 * @param {string} [options.defaultColor]
 * @param {bool} [options.allowCustomColor=true]
 * @param {bool} [options.colorPreview=true]
 * @param {function} [options.onColorSelected]
 * @param {Array[]} [options.colors]
 * @constructor
 */
BX.ColorPicker = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	/** @var {BX.PopupWindow} **/
	this.popupWindow = null;

	this.popupOptions = {
		angle: true,
		autoHide: true,
		closeByEsc: true,
		noAllPaddings: true
	};

	this.layout = {
		preview: null,
		customColor: null,
		defaultColor: null,
		actions: null,
		customColorInput: null,
		customColorTextBox: null
	};

	if (BX.type.isPlainObject(options.popupOptions))
	{
		BX.mergeEx(this.popupOptions, options.popupOptions);
	}

	this.bindElement = null;
	this.colors = [];
	this.selectedColor = null;
	this.defaultColor = null;
	this.allowCustomColor = true;
	this.colorPreview = true;

	/** @var {function} **/
	this.onColorSelected = null;

	this.setOptions(options);
};

BX.ColorPicker.prototype = {

	/**
	 *  Opens Color Picker Dialog
	 *
	 * @param {object} [options]
	 * @param {Element} [options.bindElement]
	 * @param {string} [options.selectedColor]
	 * @param {string} [options.defaultColor]
	 * @param {function} [options.onColorSelected]
	 * @param {Array[]} [options.colors]
	 */
	open: function(options)
	{
		this.setOptions(options);

		var popup = this.getPopupWindow();
		popup.setContent(this.getPopupContent());
		this.getPopupWindow().show();
	},

	/**
	 * Closes Color Picker Dialog
	 */
	close: function()
	{
		this.getPopupWindow().close();
	},

	/**
	 *
	 * @param {object} options
	 * @param {Element} [options.bindElement]
	 * @param {string} [options.selectedColor]
	 * @param {string} [options.defaultColor]
	 * @param {bool} [options.allowCustomColor]
	 * @param {bool} [options.colorPreview]
	 * @param {function} [options.onColorSelected]
	 * @param {Array[]} [options.colors]
	 */
	setOptions: function(options)
	{
		if (!BX.type.isPlainObject(options))
		{
			return;
		}

		if (BX.type.isArray(options.colors))
		{
			this.setColors(options.colors);
		}

		if ("bindElement" in options)
		{
			this.bindElement = options.bindElement;
			this.getPopupWindow().setBindElement(this.bindElement);
		}

		if (BX.type.isFunction(options.onColorSelected))
		{
			this.onColorSelected = options.onColorSelected;
		}

		this.setSelectedColor(options.selectedColor);
		this.setDefaultColor(options.defaultColor);

		if (BX.type.isBoolean(options.allowCustomColor))
		{
			this.allowCustomColor = options.allowCustomColor;
		}

		if (BX.type.isBoolean(options.colorPreview))
		{
			this.colorPreview = options.colorPreview;
		}
	},

	/**
	 *
	 * @returns {BX.PopupWindow}
	 */
	getPopupWindow: function()
	{
		if (this.popupWindow)
		{
			return this.popupWindow;
		}

		var popupId = "main-color-picker-" + BX.util.getRandomString(5);
		this.popupWindow = new BX.PopupWindow(popupId, this.bindElement, this.popupOptions);

		return this.popupWindow;
	},

	/**
	 *
	 * @param {Array[]} colors
	 */
	setColors: function(colors)
	{
		if (!BX.type.isArray(colors))
		{
			return;
		}

		this.colors = [];
		colors.forEach(function(/*Array*/colorRow) {

			if (!BX.type.isArray(colorRow))
			{
				return;
			}

			var row = [];
			colorRow.forEach(function(color) {

				color = this.getFullColorCode(color);
				if (color !== null)
				{
					row.push(color);
				}

			}, this);

			if (row.length)
			{
				this.colors.push(row);
			}

		}, this);
	},

	/**
	 *
	 * @returns {Array[]}
	 */
	getColors: function()
	{
		return this.colors.length ? this.colors : this.getDefaultColors();
	},

	/**
	 *
	 * @returns {Array[]}
	 */
	getDefaultColors: function()
	{
		return [
			[
				"#aae9fc", "#bbecf1", "#98e1dc", "#e3f299", "#ffee95", "#ffdd93", "#dfd3b6", "#e3c6bb",
				"#ffad97", "#ffbdbb", "#ffcbd8", "#ffc4e4", "#c4baed", "#dbdde0", "#bfc5cd", "#a2a8b0"
			],
			[
				"#ffffff", "#2eceff", "#10e5fc", "#a5de00", "#eec200", "#ffa801", "#ad8f47", "#b57051",
				"#ff5b55", "#ef3000", "#f968b6", "#6b52cc", "#06bab1", "#5cd1df", "#a1a6ac", "#949da9"
			],
			[
				"#ffb79f", "#ffbf99", "#f3e27c", "#e7d35d", "#00ff00", "#00a64c", "#48dfdf", "#b02fb0",
				"#ff00ff", "#ef008b", "#0000ff", "#ebebeb", "#acacac", "#898989", "#555555", "#000000"
			],
			[
				"#f89675", "#fdad7e", "#fec788", "#fff893", "#c5e099", "#a3d49b", "#8ed1a8", "#7ecb9c",
				"#78cdca", "#67cef9", "#7aa5da", "#887fc0", "#a284bf", "#bd8bc0", "#f69ac1", "#f6989c"
			],
			[
				"#f26b47", "#f78d4d", "#fdb051", "#fff55a", "#abd46c", "#7bc56f", "#00bbb4", "#00bef6",
				"#00bdb5", "#3fb2cd", "#3f8bcd", "#5471b9", "#865daa", "#a861ab", "#f16ca8", "#f26b7b"
			],
			[
				"#f11716", "#f36509", "#f99500", "#fff300", "#8ec82f", "#2fb644", "#00a74c", "#00a99d",
				"#00adf2", "#0070bf", "#0052a7", "#2e2d93", "#662793", "#922091", "#f0008c", "#f10057"
			],
			[
				"#9e0502", "#a34100", "#a46200", "#aba100", "#578520", "#107c2c", "#007333", "#00736a",
				"#0075a6", "#004982", "#003172", "#1c0d64", "#460663", "#630060", "#a0005c", "#9f0037"
			]
		];
	},

	/**
	 *
	 * @returns {Element}
	 */
	getPopupContent: function()
	{
		var container = BX.create("div", {
			props: {
				className: "main-color-picker-container"
			},
			events: {
				click: this.handleContainerClick.bind(this)
			}
		});

		var palette = BX.create("div", {
			props: {
				className: "main-color-picker-palette"
			},
			events: {
				click: this.handleBoxClick.bind(this),
				mouseover: this.isPreviewVisible() ? this.handleBoxOver.bind(this) : null
			}
		});

		this.getColors().forEach(function(/*Array*/colorRow) {

			var row = BX.create("div", { props: {
				className: "main-color-picker-row"
			}});

			palette.appendChild(row);

			colorRow.forEach(function(color) {

				if (!BX.type.isNotEmptyString(color))
				{
					return;
				}

				color = color.toLowerCase();

				var box = BX.create("div", {
					attrs: {
						className: "main-color-picker-box",
						"data-color": color
					},
					style: {
						backgroundColor: color
					}
				});

				if (this.getSelectedColor() === color)
				{
					box.classList.add("main-color-picker-box-selected");
				}

				if (color === "#ffffff")
				{
					box.classList.add("main-color-picker-box-white");
				}

				row.appendChild(box);

			}, this);

		}, this);

		container.appendChild(palette);

		var actions = this.getActions();
		if (actions)
		{
			container.appendChild(actions);
		}

		return container;
	},


	getActions: function()
	{
		this.layout.customColor = null;
		this.layout.defaultColor = null;
		this.layout.preview = null;
		this.layout.actions = null;

		if (!this.isPreviewVisible())
		{
			return null;
		}

		var singleAction = !this.isCustomColorAllowed() || this.getDefaultColor() === null;
		this.layout.actions = BX.create("div", {
			props: {
				className: "main-color-picker-actions" + (singleAction ? " main-color-picker-single-action" : "")
			},
			children: [
				this.layout.preview = BX.create("div", { props: {
					className: "main-color-picker-preview"
				}})
			]
		});

		if (this.isCustomColorAllowed())
		{
			this.layout.customColor = BX.create("div", {
				props: {
					className: "main-color-picker-custom"
				},
				children: [
					BX.create("span", {
						props: {
							className: "main-color-picker-custom-action"
						},
						text: BX.message("MAIN_COLORPICKER_SPECIFY_HEX_COLOR"),
						events: {
							click: this.handleCustomColorClick.bind(this)
						}
					}),

					BX.create("span", {
						props: {
							className: "main-color-picker-custom-form"
						},
						children: [
							BX.create("span", {
								props: {
									className: "main-color-picker-custom-label"
								},
								text: BX.message("MAIN_COLORPICKER_HEX_COLOR") + ":"
							}),

							this.layout.customColorTextBox = BX.create("span", {
								props: {
									className: "main-color-picker-custom-textbox"
								},
								children: [
									this.layout.customColorInput = BX.create("input", {
										props: {
											className: "main-color-picker-custom-input"
										},
										attrs: {
											maxlength: 6
										},
										events: {
											keypress: this.handleCustomInputKeyPress.bind(this),
											keyup: this.handleCustomInputKeyUp.bind(this),
											paste: this.handleCustomInputPaste.bind(this)
										}
									})
								]
							}),
							BX.create("span", {
								props: {
									className: "main-color-picker-custom-button"
								},
								events: {
									click: this.applyEditMode.bind(this)
								}
							})
						]
					})
				]
			});

			this.layout.actions.appendChild(this.layout.customColor);
		}

		if (this.getDefaultColor() !== null)
		{
			this.layout.defaultColor = BX.create("div", {
				props: {
					className: "main-color-picker-default"
				},
				children: [
					BX.create("span", {
						props: {
							className: "main-color-picker-default-action"
						},
						text: BX.message("MAIN_COLORPICKER_BY_DEFAULT"),
						events: {
							click: this.handleDefaultActionClick.bind(this)
						}
					})
				]
			});

			this.layout.actions.appendChild(this.layout.defaultColor);
		}

		return this.layout.actions;
	},

	/**
	 *
	 * @returns {bool}
	 */
	isCustomColorAllowed: function()
	{
		return this.allowCustomColor;
	},

	/**
	 *
	 * @returns {bool}
	 */
	isPreviewVisible: function()
	{
		return this.colorPreview || this.getDefaultColor() !== null || this.isCustomColorAllowed();
	},

	/**
	 *
	 * @returns {string|null}
	 */
	getSelectedColor: function()
	{
		return this.selectedColor;
	},

	/**
	 *
	 * @param {string|null} color
	 */
	setSelectedColor: function(color)
	{
		if (this.isValidColor(color))
		{
			this.selectedColor = this.getFullColorCode(color.toLowerCase());
		}
		else if (color === null)
		{
			this.selectedColor = null;
		}
	},

	/**
	 *
	 * @returns {string|null}
	 */
	getDefaultColor: function()
	{
		return this.defaultColor;
	},

	/**
	 *
	 * @param {string|null} color
	 */
	setDefaultColor: function(color)
	{
		if (this.isValidColor(color))
		{
			this.defaultColor = this.getFullColorCode(color.toLowerCase());
		}
		else if (color === null)
		{
			this.defaultColor = null;
		}
	},

	/**
	 *
	 * @param {MouseEvent} event
	 * @private
	 */
	handleContainerClick: function(event)
	{
		if (this.isEditMode() && event.target !== this.layout.customColorInput)
		{
			var textValue = BX.util.trim(this.layout.customColorInput.value);
			if (!textValue.length)
			{
				this.resetEditMode();
				this.resetError();
			}
		}
	},

	/**
	 *
	 * @param {MouseEvent} event
	 * @private
	 */
	handleCustomColorClick: function(event)
	{
		this.switchToEditMode();
		event.stopPropagation();
	},


	/**
	 *
	 * @param {MouseEvent} event
	 * @private
	 */
	handleBoxClick: function(event)
	{
		var target = event.target;
		if (!BX.type.isDomNode(target) || !target.dataset.color)
		{
			return;
		}

		var color = target.dataset.color;
		target.classList.add("main-color-picker-box-selected");

		this.applyColor(color);
	},

	/**
	 *
	 * @param {string} color
	 */
	applyColor: function(color)
	{
		if (this.isValidColor(color))
		{
			this.setSelectedColor(color);
			if (this.onColorSelected)
			{
				this.onColorSelected(this.getSelectedColor(), this);
			}
		}

		this.close();
	},

	/**
	 *
	 * @param {MouseEvent} event
	 * @private
	 */
	handleDefaultActionClick: function(event)
	{
		var color = this.getDefaultColor();
		this.applyColor(color);
	},

	/**
	 *
	 * @param {MouseEvent} event
	 * @private
	 */
	handleBoxOver: function(event)
	{
		var target = event.target;
		if (!BX.type.isDomNode(target) || !target.dataset.color)
		{
			return;
		}

		this.previewColor(target.dataset.color);
	},

	/**
	 *
	 * @param {MouseEvent} event
	 * @private
	 */
	handleCustomInputKeyPress: function(event)
	{
		if (!event.key.match("[A-Fa-f0-9]") && !event.ctrlKey && !event.metaKey)
		{
			event.preventDefault();
		}

		this.resetError();

		if (event.keyCode === 13)
		{
			this.applyEditMode();
		}
	},

	/**
	 *
	 * @param {MouseEvent} event
	 * @private
	 */
	handleCustomInputKeyUp: function(event)
	{
		this.previewColor("#" + this.layout.customColorInput.value);
	},

	/**
	 *
	 * @param {MouseEvent} event
	 * @private
	 */
	handleCustomInputPaste: function(event)
	{
		var clipboardData = event.clipboardData || window.clipboardData || null;
		if (clipboardData)
		{
			var text = clipboardData.getData("text");
			var color = BX.util.trim(text).replace(/#/g, "");
			
			if (this.isValidColor("#" + color))
			{
				this.layout.customColorInput.value = color;
			}

			event.preventDefault();
		}
	},

	switchToEditMode: function()
	{
		if (this.layout.actions)
		{
			this.layout.actions.classList.add("main-color-picker-edit-mode");
		}

		this.focusCustomInput();
	},

	resetEditMode: function()
	{
		if (this.layout.actions)
		{
			this.layout.actions.classList.remove("main-color-picker-edit-mode");
		}
	},

	isEditMode: function()
	{
		return this.layout.actions && this.layout.actions.classList.contains("main-color-picker-edit-mode");
	},

	applyEditMode: function()
	{
		var color = BX.util.trim(this.layout.customColorInput.value);
		color = color.replace(/#/g, "");
		color = "#" + color;

		if (!this.isValidColor(color))
		{
			this.highlightError();
			this.focusCustomInput();
			return;
		}

		this.applyColor(color);
	},

	previewColor: function(color)
	{
		if (this.layout.preview && this.isValidColor(color))
		{
			this.layout.preview.style.backgroundColor = color;
		}
	},


	focusCustomInput: function()
	{
		if (this.layout.customColorInput)
		{
			this.layout.customColorInput.focus();
		}
	},

	highlightError: function()
	{
		if (this.layout.customColorTextBox)
		{
			this.layout.customColorTextBox.classList.add("main-color-picker-custom-textbox-error");
		}
	},

	resetError: function()
	{
		if (this.layout.customColorTextBox)
		{
			this.layout.customColorTextBox.classList.remove("main-color-picker-custom-textbox-error");
		}
	},

	/**
	 *
	 * @param {string} color
	 * @returns {bool}
	 */
	isValidColor: function(color)
	{
		return BX.type.isNotEmptyString(color) && color.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/);
	},

	getFullColorCode: function(color)
	{
		if (!this.isValidColor(color))
		{
			return null;
		}

		if (color.length === 4)
		{
			return color.replace(/([a-f0-9])/gi, "$1$1");
		}

		return color;
	}

};

})();