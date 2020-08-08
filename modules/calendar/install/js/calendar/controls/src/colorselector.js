import {Util} from "calendar.util";

export class ColorSelector
{
	constructor(params)
	{
		this.defaultColors = Util.getDefaultColorList();
		this.colors = [];
		this.zIndex = 3100;
		this.DOM = {
			wrap: params.wrap
		};
		this.create();
		this.bindEventHandlers();
	}

	create()
	{
		for (let i = 0; i < this.defaultColors.length; i++)
		{
			this.colors.push(
			{
				color: this.defaultColors[i],
				node: this.DOM.wrap.appendChild(BX.create('LI',
				{
					props: {className: 'calendar-field-colorpicker-color-item'},
					attrs: {'data-bx-calendar-color': this.defaultColors[i]},
					style: {backgroundColor: this.defaultColors[i]},
					html: '<span class="calendar-field-colorpicker-color"></span>'
				}))
			});
		}

		this.DOM.customColorNode = this.DOM.wrap.appendChild(BX.create('LI',
			{
				props: {className: 'calendar-field-colorpicker-color-item'},
				style:
				{
					backgroundColor: 'transparent',
					width: 0
				},
				html: '<span class="calendar-field-colorpicker-color"></span>'
			}
		));

		this.DOM.customColorLink = this.DOM.wrap.appendChild(BX.create('LI', {
			props: {className: 'calendar-field-colorpicker-color-item-more'},
			html: '<span class="calendar-field-colorpicker-color-item-more-link">' + BX.message('EC_COLOR') + '</span>',
			events: {click: BX.delegate(function(){
					if (!this.colorPickerPopup)
					{
						this.colorPickerPopup = new BX.ColorPicker({
							bindElement: this.DOM.customColorLink,
							onColorSelected: BX.proxy(this.setValue, this),
							popupOptions: {zIndex: this.zIndex}
						});
					}
					this.colorPickerPopup.open();
				}, this)}
		}));
	}

	bindEventHandlers()
	{
		BX.bind(this.DOM.wrap, 'click', BX.proxy(this.handleColorClick, this));
	}

	handleColorClick(e)
	{
		let target = Util.findTargetNode(e.target || e.srcElement, this.DOM.wrap);

		if (target && target.getAttribute)
		{
			let value = target.getAttribute('data-bx-calendar-color');
			if(value !== null)
			{
				this.setValue(value);
			}
		}
	}

	setValue(color)
	{
		this.activeColor = color;

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

	getValue()
	{
		return this.activeColor;
	}
}