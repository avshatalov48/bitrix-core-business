import { Tag, Loc, Dom, Event, Type } from 'main.core';
import { Util } from 'calendar.util';
import { MenuManager } from 'main.popup';
import { EventEmitter, BaseEvent } from 'main.core.events';

export class ColorSelector extends EventEmitter
{
	LINE_MODE = 'line';
	SELECTOR_MODE = 'selector';
	VIEW_MODE = 'view';

	constructor(params)
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.ColorSelector');
		this.id = params.id || 'color-select-' + Math.round(Math.random() * 1000000);
		this.defaultColors = Util.getDefaultColorList();
		this.colors = [];
		this.zIndex = 3100;
		this.mode = params.mode || this.LINE_MODE;

		this.DOM = {
			wrap: params.wrap
		};
		this.create();

		this.setViewMode(params.viewMode || false);
	}

	create()
	{
		if (this.mode === this.LINE_MODE)
		{
			for (let i = 0; i < this.defaultColors.length; i++)
			{
				this.colors.push(
					{
						color: this.defaultColors[i],
						node: this.DOM.wrap.appendChild(Dom.create('LI',
							{
								props: { className: 'calendar-field-colorpicker-color-item' },
								attrs: { 'data-bx-calendar-color': this.defaultColors[i] },
								style: { backgroundColor: this.defaultColors[i] },
								html: '<span class="calendar-field-colorpicker-color"></span>'
							}))
					});
			}

			this.DOM.customColorNode = this.DOM.wrap.appendChild(Dom.create('LI',
				{
					props: { className: 'calendar-field-colorpicker-color-item' },
					style:
						{
							backgroundColor: 'transparent',
							width: 0
						},
					html: '<span class="calendar-field-colorpicker-color"></span>'
				}
			));

			this.DOM.customColorLink = this.DOM.wrap.appendChild(Dom.create('LI', {
				props: { className: 'calendar-field-colorpicker-color-item-more' },
				html: '<span class="calendar-field-colorpicker-color-item-more-link">' + Loc.getMessage('EC_COLOR') + '</span>',
				events: {
					click: () => {
						if (!this.colorPickerPopup)
						{
							this.colorPickerPopup = new BX.ColorPicker({
								bindElement: this.DOM.customColorLink,
								onColorSelected: this.setValue.bind(this),
								popupOptions: { zIndex: this.zIndex }
							});
						}
						this.colorPickerPopup.open();
					}
				}
			}));
			Event.bind(this.DOM.wrap, 'click', this.handleColorClick.bind(this));
		}
		else if (this.mode === this.SELECTOR_MODE)
		{
			this.DOM.colorIcon = this.DOM.wrap.appendChild(Tag.render`
				<div style="background-color: #000;" class="calendar-field-select-icon"></div>
			`);
			Event.bind(this.DOM.wrap, 'click', this.openPopup.bind(this));
		}
		else if (this.mode === this.VIEW_MODE)
		{
			this.DOM.colorIcon = this.DOM.wrap.appendChild(Tag.render`
				<div style="background-color: #000;" class="calendar-field-select-icon"></div>
			`);
		}
	}

	handleColorClick(e)
	{
		if (this.viewMode)
		{
			return;
		}
		let target = Util.findTargetNode(e.target || e.srcElement, this.DOM.wrap);

		if (target && target.getAttribute)
		{
			let value = target.getAttribute('data-bx-calendar-color');
			if (value !== null)
			{
				this.setValue(value);
			}
		}
	}

	setValue(color, emitChanges = true)
	{
		if (this.viewMode)
		{
			return;
		}

		this.activeColor = color;

		if (this.mode === this.LINE_MODE)
		{
			if (this.DOM.activeColorNode)
			{
				BX.removeClass(this.DOM.activeColorNode, 'active');
			}

			if (!BX.util.in_array(this.activeColor, this.defaultColors) && this.activeColor)
			{
				this.DOM.customColorNode.style.backgroundColor = this.activeColor;
				this.DOM.customColorNode.style.width = '';

				this.DOM.activeColorNode = this.DOM.customColorNode;
				BX.addClass(this.DOM.activeColorNode, 'active');
			}

			let i;
			for (i = 0; i < this.colors.length; i++)
			{
				if (this.colors[i].color === this.activeColor)
				{
					this.DOM.activeColorNode = this.colors[i].node;
					BX.addClass(this.DOM.activeColorNode, 'active');
					break;
				}
			}
		}
		else if (this.mode === this.SELECTOR_MODE || this.mode === this.VIEW_MODE)
		{
			if (this.DOM.colorIcon)
			{
				this.DOM.colorIcon.style.backgroundColor = this.activeColor;
			}
			if (this.viewMode)
			{
				this.DOM.wrap.style.backgroundColor = this.activeColor;
			}
		}

		if (emitChanges)
		{
			this.emit('onChange', new BaseEvent({ data: { value: this.activeColor } }));
		}
	}

	getValue()
	{
		return this.activeColor;
	}

	openPopup()
	{
		if (this.viewMode)
		{
			return;
		}

		if (this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown())
		{
			return this.popup.close();
		}

		let
			i, menuItems = [], icon;

		this.defaultColors.forEach((color) => {
			menuItems.push({
					text: color,
					color: color,
					className: 'calendar-add-popup-color-menu-item',
					onclick: ((color) => {
						return () => {
							this.setValue(color);
							this.popup.close();
						};
					})(color)
				}
			);
		});

		this.popup = MenuManager.create(
			this.id,
			this.DOM.colorIcon,
			menuItems,
			{
				className: 'calendar-color-popup-wrap',
				width: 162,
				closeByEsc: true,
				autoHide: true,
				zIndex: this.zIndex,
				offsetTop: 0,
				offsetLeft: 52,
				angle: true,
				cacheable: false
			}
		);

		this.popup.show();

		// Paint round icons for section menu
		for (i = 0; i < this.popup.menuItems.length; i++)
		{
			if (this.popup.menuItems[i].layout.item)
			{
				icon = this.popup.menuItems[i].layout.item.querySelector('.menu-popup-item-icon');
				if (Type.isDomNode(icon))
				{
					icon.style.backgroundColor = this.popup.menuItems[i].color;
				}
			}
		}

		this.popup.popupWindow.angle.element.style.left = '6px';
	}

	setViewMode(viewMode)
	{
		this.viewMode = viewMode;
		if (this.viewMode)
		{
			Dom.clean(this.DOM.wrap);
			this.DOM.wrap.className = 'calendar-field-select-icon';
			this.DOM.wrap.style.backgroundColor = this.activeColor;
		}
		else
		{
			//Dom.removeClass(this.DOM.wrap, 'calendar-colorpicker-readonly');
		}
	}
}