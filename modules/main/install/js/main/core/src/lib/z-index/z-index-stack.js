import Type from '../type';
import OrderedArray from '../collections/ordered-array';
import ZIndexComponent from './z-index-component';
import type { ZIndexComponentOptions } from './z-index-component-options';

export default class ZIndexStack
{
	container: HTMLElement = null;
	components: OrderedArray<ZIndexComponent> = null;
	elements: WeakMap<HTMLElement, ZIndexComponent> = new WeakMap();

	baseIndex: number = 1000;
	baseStep: number = 50;
	sortCount: number = 0;

	constructor(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('ZIndexManager.Stack: The \'container\' argument must be a DOM element.');
		}

		this.container = container;

		const comparator = (componentA: ZIndexComponent, componentB: ZIndexComponent) => {
			let result = (componentA.getAlwaysOnTop() || 0) - (componentB.getAlwaysOnTop() || 0);
			if (!result)
			{
				result = componentA.getSort() - componentB.getSort();
			}

			return result;
		};

		this.components = new OrderedArray(comparator);
	}

	getBaseIndex(): number
	{
		return this.baseIndex;
	}

	setBaseIndex(index: number): void
	{
		if (Type.isNumber(index) && index >= 0)
		{
			this.baseIndex = index;
			this.sort();
		}
	}

	setBaseStep(step: number): void
	{
		if (Type.isNumber(step) && step > 0)
		{
			this.baseStep = step;
			this.sort();
		}
	}

	getBaseStep(): number
	{
		return this.baseStep;
	}

	register(element: HTMLElement, options: ZIndexComponentOptions = {}): ZIndexComponent
	{
		if (this.getComponent(element))
		{
			console.warn('ZIndexManager: You cannot register the element twice.', element);

			return this.getComponent(element);
		}

		const component = new ZIndexComponent(element, options);
		component.setStack(this);
		component.setSort(++this.sortCount);

		this.elements.set(element, component);
		this.components.add(component);

		this.sort();

		return component;
	}

	unregister(element: HTMLElement): void
	{
		const component = this.elements.get(element);

		this.components.delete(component);
		this.elements.delete(element);

		this.sort();
	}

	getComponent(element: HTMLElement): ?ZIndexComponent
	{
		return this.elements.get(element) || null;
	}

	getComponents(): ZIndexComponent[]
	{
		return this.components.getAll();
	}

	getMaxZIndex(): number
	{
		const last = this.components.getLast();

		return last ? last.getZIndex() : this.baseIndex;
	}

	sort(): void
	{
		this.components.sort();

		let zIndex = this.baseIndex;
		this.components.forEach((component: ZIndexComponent) => {
			component.setZIndex(zIndex);
			zIndex += this.baseStep;
		});
	}

	bringToFront(element: HTMLElement): ?ZIndexComponent
	{
		const component = this.getComponent(element);
		if (!component)
		{
			console.error('ZIndexManager: element was not found in the stack.', element);
			return null;
		}

		component.setSort(++this.sortCount);

		this.sort();

		return component;
	}
}