import { BaseSettingsElement } from './base-settings-element';
import { Section } from 'ui.section';
import { Dom, Type } from 'main.core';

export class SettingsSection extends BaseSettingsElement
{
	#sectionView: Section;
	#sectionSort: number = 100;

	constructor(params)
	{
		super(params);

		this.#sectionView = params.section instanceof Section
			? params.section : new Section(Type.isPlainObject(params.section) ? params.section : {})
		;
		this.#sectionSort = Type.isNumber(params.sort) ? params.sort : 100;
	}

	getSectionView(): Section
	{
		return this.#sectionView;
	}

	getSectionSort(): number
	{
		return this.#sectionSort;
	}

	render(): HTMLElement
	{
		for (let element of this.getChildrenElements())
		{
			this.getSectionView().append(element.render());
		}

		return this.getSectionView().render();
	}

	renderTo(targetNode: HTMLElement): HTMLElement
	{
		return Dom.append(this.render(), targetNode);
	}

	highlight(): boolean
	{
		this.highlightElement(this.getSectionView().render());

		return true;
	}
}