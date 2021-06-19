import Type from '../type';
import ZIndexStack from './z-index-stack';
import type ZIndexComponent from './z-index-component';
import type { ZIndexComponentOptions } from './z-index-component-options';

/**
 * @memberof BX
 */
export default class ZIndexManager
{
	static stacks: WeakMap<HTMLElement, ZIndexStack> = new WeakMap();

	static register(element: HTMLElement, options: ZIndexComponentOptions = {}): ?ZIndexComponent
	{
		const parentNode = this.#getParentNode(element);
		if (!parentNode)
		{
			return null;
		}

		const stack = this.getOrAddStack(parentNode);

		return stack.register(element, options);
	}

	static unregister(element: HTMLElement)
	{
		const parentNode = this.#getParentNode(element);
		const stack = this.getStack(parentNode);
		if (stack)
		{
			stack.unregister(element);
		}
	}

	static addStack(container: HTMLElement): ZIndexStack
	{
		const stack = new ZIndexStack(container);
		this.stacks.set(container, stack);

		return stack;
	}

	static getStack(container: HTMLElement): ?ZIndexStack
	{
		return this.stacks.get(container) || null;
	}

	static getOrAddStack(container: HTMLElement): ?ZIndexStack
	{
		return this.getStack(container) || this.addStack(container);
	}

	static getComponent(element: HTMLElement): ?ZIndexComponent
	{
		const parentNode = this.#getParentNode(element, true);
		if (!parentNode)
		{
			return null;
		}

		const stack = this.getStack(parentNode);

		return stack ? stack.getComponent(element) : null;
	}

	static bringToFront(element: HTMLElement): ?ZIndexComponent
	{
		const parentNode = this.#getParentNode(element);
		const stack = this.getStack(parentNode);

		if (stack)
		{
			return stack.bringToFront(element);
		}

		return null;
	}

	static #getParentNode(element: HTMLElement, suppressWarnings: boolean = false): ?HTMLElement
	{
		if (!Type.isElementNode(element))
		{
			if (!suppressWarnings)
			{
				console.error('ZIndexManager: The argument \'element\' must be a DOM element.', element);
			}

			return null;
		}
		else if (!Type.isElementNode(element.parentNode))
		{
			if (!suppressWarnings)
			{
				console.error('ZIndexManager: The \'element\' doesn\'t have a parent node.', element);
			}

			return null;
		}

		return element.parentNode;
	}
}