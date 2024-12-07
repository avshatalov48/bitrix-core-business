import {
	type,
	append,
	remove,
	addClass,
	removeClass,
} from 'main.core';
import layout from './layout';
import {show, hide} from './utils';
import './loader.css';

const defaultOptions = {
	size: 110,
};

const STATE_READY = 'ready';
const STATE_SHOWN = 'shown';
const STATE_HIDDEN = 'hidden';

export class Loader
{
	data = layout();
	state = STATE_READY;
	currentTarget = null;

	constructor(options = {})
	{
		const currentOptions = {...defaultOptions, ...options};
		this.currentTarget = currentOptions.target;
		this.setOptions(currentOptions);
	}

	get layout()
	{
		return this.data.container;
	}

	get circle()
	{
		return this.data.circle;
	}

	createLayout()
	{
		return this.layout;
	}

	show(target = null)
	{
		return new Promise(() => {
			const targetElement = target || this.currentTarget;

			if (
				type.isDomNode(targetElement)
				&& targetElement !== this.layout.parentNode
			)
			{
				this.currentTarget = targetElement;
				append(this.layout, targetElement);
			}

			if (this.state !== STATE_SHOWN)
			{
				this.state = STATE_SHOWN;
				return show(this.layout);
			}

			return false;
		});
	}

	hide()
	{
		return new Promise(() => {
			if (this.state !== STATE_HIDDEN)
			{
				this.state = STATE_HIDDEN;
				return hide(this.layout);
			}

			return false;
		});
	}

	isShown()
	{
		return this.state === STATE_SHOWN;
	}

	destroy()
	{
		remove(this.layout);
	}

	setOptions({target, size, color, offset, mode, strokeWidth})
	{
		const layoutStyles = new Map();
		const circleStyles = new Map();

		if (type.isDomNode(target))
		{
			this.currentTarget = target;
		}

		if (type.isNumber(size))
		{
			layoutStyles.set('width', `${size}px`);
			layoutStyles.set('height', `${size}px`);
		}

		if (type.isString(color))
		{
			circleStyles.set('stroke', color);
		}

		if (type.isObjectLike(offset))
		{
			const prefix = /^inline$|^custom$/.test(mode) ? '' : 'margin-';

			if (type.isString(offset.top))
			{
				layoutStyles.set(`${prefix}top`, offset.top);
			}

			if (type.isString(offset.left))
			{
				layoutStyles.set(`${prefix}left`, offset.left);
			}
		}

		if (mode === 'inline')
		{
			addClass(this.layout, 'main-ui-loader-inline');
		}
		else
		{
			removeClass(this.layout, 'main-ui-loader-inline');
		}

		if (mode === 'custom')
		{
			addClass(this.layout, 'main-ui-loader-custom');
			removeClass(this.layout, 'main-ui-loader-inline');
		}

		if (type.isNumber(strokeWidth))
		{
			circleStyles.set('stroke-width', strokeWidth);
		}

		layoutStyles.forEach((value, key) => {
			this.layout.style[key] = value;
		});

		circleStyles.forEach((value, key) => {
			this.circle.style[key] = value;
		});
	}
}
