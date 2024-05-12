import {BaseSettingsVisitor} from './base-settings-visitor';
import {BaseSettingsElement} from '../base-settings-element';

export class RecursiveFilteringVisitor extends BaseSettingsVisitor
{
	#filterCallback = null;
	#result: BaseSettingsElement[] = [];

	setFilter(filterStrategy): RecursiveFilteringVisitor
	{
		this.#filterCallback = filterStrategy;
		return this;
	}

	#do(element: BaseSettingsElement): boolean
	{
		if (typeof this.#filterCallback === 'function')
		{
			return this.#filterCallback(element) === true;
		}

		return false;
	}

	restart(startElement: BaseSettingsElement): BaseSettingsElement[]
	{
		this.#result = [];
		this.visitSettingsElement(startElement);
		return this.#result;
	}

	visitSettingsElement(element: BaseSettingsElement): void
	{
		if (this.#do(element))
		{
			this.#result.push(element);
		}
		if (element.getChildrenElements().length > 0)
		{
			element
				.getChildrenElements()
				.forEach((childElement: BaseSettingsElement) => {
					this.visitSettingsElement(childElement);
				})
			;
		}
	}

	static startFrom(startElement: BaseSettingsElement, filterStrategy): BaseSettingsElement[]
	{
		return this.getInstance()
			.setFilter(filterStrategy)
			.restart(startElement)
		;
	}

	static getInstance(): RecursiveFilteringVisitor
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}
}
