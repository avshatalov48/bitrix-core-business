// @flow

import {Tag} from 'main.core';

type UfMoneyOptions = {
	name: string;
};

export class UfMoney
{
	name: string;

	constructor(options: UfMoneyOptions = {name: 'UfMoney'})
	{
		this.name = options.name;
	}

	setName(name: string)
	{
		this.name = name;
	}

	getName()
	{
		return this.name;
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="ui-uf-money">
				<span class="ui-uf-money-name">${this.getName()}</span>
			</div>
		`;
	}
}