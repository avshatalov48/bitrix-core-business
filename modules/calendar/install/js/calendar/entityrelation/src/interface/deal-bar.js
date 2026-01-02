import { Loc } from 'main.core';
import type Bar from './bar';
import type { RelationData } from '../type/data';

export class DealBar
{
	#bar: Bar;

	constructor(bar: Bar)
	{
		this.#bar = bar;
	}

	render(relationData: RelationData): ?HTMLElement
	{
		return this.#bar.render(
			relationData,
			this.#getEntityLink(relationData),
		);
	}

	#getEntityLink(relationData: RelationData): ?HTMLElement
	{
		return this.#bar.getEntityLink({
			link: relationData.entity.link,
			text: Loc.getMessage('CALENDAR_RELATION_ENTITY_LINK_DEAL'),
			title: Loc.getMessage('CALENDAR_RELATION_OPEN_ENTITY_HINT_DEAL'),
		});
	}
}
