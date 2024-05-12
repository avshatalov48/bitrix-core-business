import { TabField, TabsField } from 'ui.form-elements.field';
import { SettingsRow } from '../settings-row';
import { SettingsSection } from '../settings-section';
import {BaseSettingsVisitor} from './base-settings-visitor';
import {BaseSettingsElement} from '../base-settings-element';
import {Dom} from "main.core";

export class AscendingOpeningVisitor extends BaseSettingsVisitor
{
	#filterCallback = null;
	#result: BaseSettingsElement[] = [];
	colored;

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
		if (this.#do(element))
		{
			this.#result.push(element);
		}

		if (element.getParentElement())
		{
			this.visitSettingsElement(element.getParentElement());
		}
	}

	static startFrom(startElement: BaseSettingsElement, filterStrategy): BaseSettingsElement[]
	{
		return this.getInstance()
			.setFilter(filterStrategy)
			.restart(startElement);
	}

	static getInstance(): AscendingOpeningVisitor
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}
}
