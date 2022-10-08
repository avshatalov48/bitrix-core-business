import { Type } from 'main.core';
import { BaseContext } from './base-context';
import { Field } from '../selectors/types';

export class SelectorContext extends BaseContext
{
	constructor(props: {
		fields: Array<Field>,
		useSwitcherMenu: boolean,
		rootGroupTitle: string,
	})
	{
		super(props);
	}

	get fields(): Array<Field>
	{
		const fields = this.get('fields');

		return Type.isArray(fields) ? fields : [];
	}

	get useSwitcherMenu(): boolean
	{
		return Type.isBoolean(this.get('useSwitcherMenu')) ? this.get('useSwitcherMenu') : false;
	}

	set useSwitcherMenu(value: boolean)
	{
		this.set('useSwitcherMenu', value);
	}

	get rootGroupTitle(): string
	{
		return this.get('rootGroupTitle') ?? '';
	}
}