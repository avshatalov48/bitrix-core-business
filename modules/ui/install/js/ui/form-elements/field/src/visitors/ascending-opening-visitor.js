import { SettingsRow } from '../settings-row';
import { SettingsSection } from '../settings-section';
import {BaseSettingsVisitor} from './base-settings-visitor';
import {BaseSettingsElement} from '../base-settings-element';

export class AscendingOpeningVisitor extends BaseSettingsVisitor
{
	#filterCallback = null;
	#result: BaseSettingsElement[] = [];

	setFilter(filterStrategy): this
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
		console.log('element.constructor.name: ', element.constructor.name);

		if (element instanceof SettingsRow)
		{
			element.getRowView().show();
		}
		else if (element instanceof SettingsSection)
		{
			element.getSectionView().toggle(true);
			console.log('element.getSectionView(): ', element.getSectionView());

		}
	}

	static startFrom(startElement: BaseSettingsElement): BaseSettingsElement
	{
		const instance = this.getInstance();

		let currentElement = startElement;
		let lastElement = startElement;

		while (currentElement)
		{
			lastElement = currentElement;
			instance.visitSettingsElement(currentElement);
			currentElement = currentElement.getParentElement();
		}

		return lastElement;
	}
}
