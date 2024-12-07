import { Tag, Type, Dom, Text } from 'main.core';
import ToolbarItem from './toolbar-item';

/**
 * @memberof BX.UI.TextEditor
 */
export default class Button extends ToolbarItem
{
	#format: string = null;
	#blockType: string = null;
	#active: boolean = false;
	#disabled: boolean = false;
	#disableInsideUnformatted = false;
	#disableCallback: Function = null;
	#container: HTMLElement = null;

	setContent(content: string | HTMLElement)
	{
		if (Type.isString(content))
		{
			this.getContainer().innerHTML = content;
		}
		else if (Type.isElementNode(content))
		{
			this.getContainer().append(content);
		}
	}

	setFormat(format: string)
	{
		this.#format = format;
	}

	getFormat(): string | null
	{
		return this.#format;
	}

	hasFormat(): string | null
	{
		return this.#format;
	}

	setBlockType(type: string)
	{
		this.#blockType = type;
	}

	getBlockType(): string | null
	{
		return this.#blockType;
	}

	setTooltip(tooltip: string | null)
	{
		if (Type.isStringFilled(tooltip))
		{
			Dom.attr(this.getContainer(), 'title', Text.encode(tooltip));
		}
		else if (tooltip === null)
		{
			Dom.attr(this.getContainer(), 'title', null);
		}
	}

	disableInsideUnformatted(): void
	{
		this.#disableInsideUnformatted = true;
	}

	enableInsideUnformatted(): void
	{
		this.#disableInsideUnformatted = false;
	}

	shouldDisableInsideUnformatted(): boolean
	{
		return this.#disableInsideUnformatted;
	}

	setActive(active: boolean = true): void
	{
		if (active === this.#active)
		{
			return;
		}

		this.#active = active;
		if (active)
		{
			Dom.addClass(this.getContainer(), '--active');
		}
		else
		{
			Dom.removeClass(this.getContainer(), '--active');
		}
	}

	isActive(): boolean
	{
		return this.#active;
	}

	setDisabled(disabled: boolean = true): void
	{
		if (disabled === this.#disabled)
		{
			return;
		}

		this.#disabled = disabled;
		if (disabled)
		{
			Dom.attr(this.getContainer(), { disabled: true });
		}
		else
		{
			Dom.attr(this.getContainer(), { disabled: null });
		}
	}

	disable(): void
	{
		this.setDisabled(true);
	}

	enable(): void
	{
		this.setDisabled(false);
	}

	isDisabled(): boolean
	{
		return this.#disabled;
	}

	hasOwnDisableCallback(): boolean
	{
		return this.#disableCallback !== null;
	}

	setDisableCallback(fn: Function): void
	{
		if (Type.isFunction(fn))
		{
			this.#disableCallback = fn;
		}
	}

	invokeDisableCallback(): boolean
	{
		return this.#disableCallback();
	}

	getContainer(): HTMLElement
	{
		if (this.#container === null)
		{
			this.#container = Tag.render`
				<button 
					type="button" 
					class="ui-text-editor-toolbar-button"
					onclick="${this.#handleClick.bind(this)}"
				>
				</button>
			`;
		}

		return this.#container;
	}

	render(): HTMLElement
	{
		return this.getContainer();
	}

	#handleClick(): void
	{
		this.emit('onClick');
	}
}
