import Bar from './bar';
import { BookingBar } from './booking-bar';
import { DealBar } from './deal-bar';
import { Dom, Tag, Type } from 'main.core';
import Client from '../api/client';
import RelationCollection from '../collection/relation-collection';
import { EntityRelationsHeader } from './header';
import type { InterfaceOptions } from '../type/constructor-options';
import type { RelationData } from '../type/data';

export default class RelationInterface
{
	constructor(options: InterfaceOptions)
	{
		this.bar = new Bar({ parentNode: options.parentNode });

		this.eventId = options.eventId ?? null;
		this.relationData = RelationCollection.getRelation(this.eventId) || null;
		this.layout = null;
	}

	render(): HTMLElement | null
	{
		if (Type.isNil(this.relationData))
		{
			this.layout = this.bar.renderLoader();
			void this.showLazy();
		}
		else if (this.relationData)
		{
			this.layout = this.#renderBar(this.relationData);
		}

		return this.#wrapLayout(this.layout);
	}

	async showLazy()
	{
		this.relationData = await Client.getRelationData(this.eventId);

		if (this.relationData)
		{
			RelationCollection.setRelation(this.relationData);

			const barLayout = this.#renderBar(this.relationData);

			Dom.replace(this.layout, barLayout);
			this.layout = barLayout;
		}
		else
		{
			this.destroy();
		}
	}

	destroy(): void
	{
		Dom.remove(this.layout);
		this.layout = null;
	}

	#renderBar(relationData: RelationData): ?HTMLElement
	{
		if (relationData.entity.type === 'deal')
		{
			return new DealBar(this.bar).render(relationData);
		}

		if (relationData.entity.type === 'booking')
		{
			return new BookingBar(this.bar).render(relationData);
		}

		return null;
	}

	#wrapLayout(layout: HTMLElement | null): HTMLElement | null
	{
		if (layout === null)
		{
			return layout;
		}

		return Tag.render`
			<div class="calendar--relation-entities-wrapper">
				${new EntityRelationsHeader().render()}
				${this.layout}
			</div>
		`;
	}
}
