import {Cache, Tag} from 'main.core';
import {Loc} from 'landing.loc';

import './css/style.css';

type TypeSeparatorOptions = {
	typeId: number,
};

export default class TypeSeparator
{
	cache = new Cache.MemoryCache();

	constructor(options: TypeSeparatorOptions)
	{
		this.options = {...options};
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry-type-separator">
					<div class="landing-ui-rule-entry-type-separator-inner">
						${this.getSeparatorLabel()}
					</div>
				</div>
			`;
		});
	}

	getSeparatorLabel(): string
	{
		if (String(this.options.typeId) === String(2))
		{
			return Loc.getMessage('LANDING_RULE_TYPE_SEPARATOR_TYPE_2');
		}

		return Loc.getMessage('LANDING_RULE_TYPE_SEPARATOR_TYPE_1');
	}
}