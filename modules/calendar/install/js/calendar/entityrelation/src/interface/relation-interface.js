import Bar from './bar';
import { Dom, Type } from 'main.core';
import Client from '../api/client';
import RelationCollection from '../collection/relation-collection';

import type { InterfaceOptions } from '../type/constructor-options';

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
			this.showLazy();
		}
		else if (this.relationData)
		{
			this.layout = this.bar.render(this.relationData);
		}

		return this.layout;
	}

	async showLazy()
	{
		this.relationData = await Client.getRelationData(this.eventId);

		if (this.relationData)
		{
			RelationCollection.setRelation(this.relationData);

			const barLayout = this.bar.render(this.relationData);
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
}
