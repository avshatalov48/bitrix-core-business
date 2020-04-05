BX.namespace("BX.Landing");

/**
 * Bbase script for component.
 * @param {Object} params Some params.
 * @returns {void}
 */
BX.Landing.EditComponent = function ()
{
	this.actionCloseId = BX("action-close");

	if (this.actionCloseId)
	{
		BX.bind(this.actionCloseId, "click", BX.delegate(this.actionClose, this));
	}
};

BX.Landing.EditComponent.prototype = {
	/**
	 * Close the slider.
	 * @returns {void}
	 */
	actionClose: function ()
	{
		if (
			typeof top.BX.Bitrix24 !== 'undefined' &&
			typeof top.BX.Bitrix24.PageSlider !== 'undefined'
		)
		{
			top.BX.Bitrix24.PageSlider.close();
		}
		else if (typeof top.BX.SidePanel !== 'undefined')
		{
			setTimeout(function() {
				top.BX.SidePanel.Instance.close();
			}, 300);
		}
	}
};


/**
 * SELECT control with color preview
 * @param params
 * @constructor
 */
BX.Landing.SelectColor = function (params)
{
	this.id = params.id ? params.id : '';
	this.options = params.options ? params.options : [];
	this.value = params.value ? params.value : '';
	this.DOM = {};
};

BX.Landing.SelectColor.prototype = {
	show: function ()
	{
		this.checkValue();
		this.initSectionSelector();
	},

	/**
	 * If not exist color for this value - get default (any)
	 */
	checkValue: function ()
	{
		if(!this.options[this.value])
		{
			this.value = Object.keys(this.options)[0];
		}
	},

	initSectionSelector: function ()
	{
		this.DOM.sectionWrap = BX(this.id + '_select_color_wrap');
		this.DOM.sectionInput = BX(this.id + '_select_color');

		this.DOM.sectionSelect = this.DOM.sectionWrap.appendChild(BX.create('DIV', {
			props: {className: 'select-color-field'}
		}));
		this.DOM.sectionSelectInner = this.DOM.sectionSelect.appendChild(BX.create('DIV', {
			props: {className: 'select-color-field-icon'},
			style: {backgroundColor: this.options[this.value].color}
		}));
		this.DOM.sectionSelectInnerText = this.DOM.sectionSelect.appendChild(BX.create('SPAN', {
			text: this.options[this.value].name
		}));

		BX.bind(this.DOM.sectionSelect, 'click', showPopup);

		var _this = this,
			options = this.options;

		function showPopup()
		{
			if (_this.sectionMenu && _this.sectionMenu.popupWindow && _this.sectionMenu.popupWindow.isShown())
			{
				return _this.sectionMenu.close();
			}

			var i, menuItems = [], icon;

			for (var id in options)
			{
				menuItems.push({
					id: 'bx-select-color-option-' + id,
					text: BX.util.htmlspecialchars(options[id].name),
					color: options[id].color,
					className: 'select-color-popup-menu-item ' + (options[id].class ? options[id].class : ''),
					onclick: (function (value)
					{
						return function ()
						{
							var section = options[value];
							_this.DOM.sectionInput.value = value;
							_this.DOM.sectionSelectInner.style.backgroundColor = section.color;
							_this.DOM.sectionSelectInnerText.innerHTML = BX.util.htmlspecialchars(section.name);
							_this.sectionMenu.close();
						}
					})(id)
				});
			};

			_this.sectionMenu = BX.PopupMenu.create(
				"selectColor" + _this.id,
				_this.DOM.sectionSelect,
				menuItems,
				{
					closeByEsc: true,
					autoHide: true,
					offsetTop: 0,
					offsetLeft: 0
				}
			);

			_this.sectionMenu.popupWindow.contentContainer.style.maxHeight = "300px";
			_this.sectionMenu.popupWindow.setWidth(_this.DOM.sectionSelect.offsetWidth - 2);
			_this.sectionMenu.show();

			// Paint round icons for section menu
			for (i = 0; i < _this.sectionMenu.menuItems.length; i++)
			{
				if (_this.sectionMenu.menuItems[i].layout.item)
				{
					icon = _this.sectionMenu.menuItems[i].layout.item.querySelector('.menu-popup-item-icon');
					if (icon)
					{
						icon.style.backgroundColor = _this.sectionMenu.menuItems[i].color;
					}
				}
			}

			BX.addClass(_this.DOM.sectionSelect, 'active');

			BX.addCustomEvent(_this.sectionMenu.popupWindow, 'onPopupClose', function ()
			{
				BX.removeClass(_this.DOM.sectionSelect, 'active');
				_this.sectionMenu = null;
				BX.PopupMenu.destroy("selectColor" + _this.id);
			});
		}
	}
};

/**
 * SELECT control with lang
 * @param params
 * @constructor
 */
BX.Landing.SelectLang = function (params)
{
	this.id = params.id ? params.id : '';
	this.options = params.options ? params.options : [];
	this.value = params.value ? params.value : '';
	this.DOM = {};
};

BX.Landing.SelectLang.prototype = {
	show: function ()
	{
		this.initSectionSelector();

	},

	initSectionSelector: function ()
	{
		this.DOM.sectionWrap = BX(this.id + '_select_lang_wrap');
		this.DOM.sectionInput = BX(this.id + '_select_lang');

		this.DOM.sectionSelect = this.DOM.sectionWrap.appendChild(BX.create('DIV', {
			props: {className: 'select-lang-field'}
		}));
		this.DOM.sectionSelectInnerText = this.DOM.sectionSelect.appendChild(BX.create('SPAN', {
			text: this.options[this.value]
		}));

		BX.bind(this.DOM.sectionSelect, 'click', showPopup);

		var _this = this,
			options = this.options;

		function showPopup()
		{
			if (_this.sectionMenu && _this.sectionMenu.popupWindow && _this.sectionMenu.popupWindow.isShown())
			{
				return _this.sectionMenu.close();
			}

			var menuItems = [];

			for (var id in options)
			{
				menuItems.push({
					id: 'bx-select-color-option-' + id,
					text: BX.util.htmlspecialchars(options[id]),
					className: 'language-icon menu-popup-no-icon',
					onclick: (function (value)
					{
						return function ()
						{
							var section = options[value];
							_this.DOM.sectionInput.value = value;
							_this.DOM.sectionSelectInnerText.innerHTML = BX.util.htmlspecialchars(section);
							_this.sectionMenu.close();
						}
					})(id)
				});
			}

			_this.sectionMenu = BX.PopupMenu.create(
				"selectLang" + _this.id,
				_this.DOM.sectionSelect,
				menuItems,
				{
					closeByEsc: true,
					autoHide: true,
					offsetTop: 0,
					offsetLeft: 0
				}
			);

			_this.sectionMenu.popupWindow.contentContainer.style.maxHeight = "300px";
			_this.sectionMenu.popupWindow.setWidth(_this.DOM.sectionSelect.offsetWidth - 2);
			_this.sectionMenu.show();

			BX.addClass(_this.DOM.sectionSelect, 'active');

			BX.addCustomEvent(_this.sectionMenu.popupWindow, 'onPopupClose', function ()
			{
				BX.removeClass(_this.DOM.sectionSelect, 'active');
				_this.sectionMenu = null;
				BX.PopupMenu.destroy("selectLang" + _this.id);
			});
		}
	}
};

