import { Dom, Tag, Type } from 'main.core';
import { Button, SplitButton } from 'ui.buttons';
import 'ui.fonts.opensans';
import './style.css';

export default class ButtonsPanel
{
	constructor(options)
	{
		options = Type.isPlainObject(options) ? options : {};
		this.target = Type.isDomNode(options.target) ? options.target : null;
		const buttons = Type.isArray(options.buttons) ? options.buttons : [];

		this.container = null;
		this.buttons = [];

		buttons.forEach(button => {
			if (button instanceof Button)
			{
				this.buttons.push(button);
			}
			else if (Type.isPlainObject(button))
			{
				if (button.splitButton)
				{
					this.buttons.push(new SplitButton(button));
				}
				else
				{
					this.buttons.push(new Button(button));
				}
			}
		});
	}

	#getContainer()
	{
		if (!this.container)
		{
			this.container = Tag.render`
				<div class="ui-button-panel__container ui-button-panel__scope"></div>
			`;
		}

		return this.container;
	}

	#getButtons()
	{
		return this.buttons;
	}

	collapse()
	{
		const buttons = Object.values(this.#getButtons());
		for (let i = buttons.length - 1; i >= 0; i--)
		{
			let button = buttons[i];
			if (!button.getIcon() && !Type.isStringFilled(button.getDataSet()['buttonCollapsedIcon']))
			{
				continue;
			}

			if (button.isCollapsed())
			{
				continue;
			}

			button.setCollapsed(true);

			if (!button.getIcon())
			{
				button.setIcon(button.getDataSet()['buttonCollapsedIcon']);
			}

			break;
		}
	}

	expand()
	{

	}

	#render()
	{
		Dom.append(this.#getContainer(), this.target);

		if (this.#getButtons().length > 0)
		{
			this.#getButtons().forEach(button => {
				Dom.append(button.getContainer(), this.#getContainer());
			})
		}
	}

	init()
	{
		this.#render();
	}
}
