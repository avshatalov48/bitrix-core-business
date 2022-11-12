import ColumnItemOptions from "../columnitem";
import {Tag} from 'main.core';

export default class Base {
	constructor(options: ColumnItemOptions)
	{
		this.currentValue = options.currentValue || null;
		this.identificator = 'col-' + Math.random();
		this.parentContainer = options.container;
		this.grid = options.grid;
		this.text = options.text;
		this.userGroup = options.userGroup;
		this.access = options.access;

		this.bindEvents();
	}

	bindEvents()
	{
	}

	render(): HTMLElement
	{
		return Tag.render`<div></div>`;
	}

	getId(): string
	{
		return this.identificator;
	}
}
