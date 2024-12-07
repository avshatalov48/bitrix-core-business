import ColumnItemOptions from '../columnitem';
import { Tag } from 'main.core';

export interface ChangerOpts {
	replaceNullValueTo: ?string;
}

export default class Base
{
	changerOptions: ?ChangerOpts;

	constructor(options: ColumnItemOptions)
	{
		this.changerOptions = options.changerOptions || {};

		const defaultValue = this.changerOptions.replaceNullValueTo || null;

		this.currentValue = options.currentValue || defaultValue;
		this.identificator = `col-${Math.random()}`;
		this.parentContainer = options.container;
		this.grid = options.grid;
		this.text = options.text;
		this.userGroup = options.userGroup;
		this.access = options.access;

		this.bindEvents();
	}

	bindEvents()
	{}

	render(): HTMLElement
	{
		return Tag.render`<div></div>`;
	}

	getId(): string
	{
		return this.identificator;
	}
}
