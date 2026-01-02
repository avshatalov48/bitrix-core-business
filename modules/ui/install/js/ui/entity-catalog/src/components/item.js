import { Type } from 'main.core';
import { Button } from './button';
import { ItemData } from '@/types/item';
import { ButtonData } from '@/types/button';

import '../css/item.css';

export const Item = {
	name: 'ui-entity-catalog-item',
	components:{
		Button,
	},
	props: {
		itemData: {
			type: ItemData,
			required: true,
		},
	},
	computed: {
		buttonData(): ButtonData
		{
			if (!Type.isPlainObject(this.itemData.button))
			{
				this.itemData.button = {};
			}

			return this.itemData.button;
		},
		topText(): ?string
		{
			const custom = this.itemData?.customData ?? {};
			if (Type.isStringFilled(custom.topText))
			{
				return custom.topText;
			}

			return null;
		}
	},
	template: `
		<slot name="item" v-bind:itemData="itemData">
			<div 
				class="ui-entity-catalog__option"
				:data-item-id="String(itemData.id)"
			>
				<div class="ui-entity-catalog__option-info">
					<div
						v-if="topText"
						class="ui-entity-catalog__option-info_top_text"
					>
						{{ topText }}
					</div>
					<div class="ui-entity-catalog__option-info_name">
						<span>{{itemData.title}}</span>
						<span class="ui-entity-catalog__option-info_label" v-if="itemData.subtitle">{{itemData.subtitle}}</span>
					</div>
					<div class="ui-entity-catalog__option-info_description">
						{{itemData.description}}
					</div>
				</div>
				<Button :buttonData="buttonData" :event-data="itemData"/>
			</div>
		</slot>
	`,
}
