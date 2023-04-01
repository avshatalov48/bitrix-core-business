import { Loc, Type } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { ButtonData } from '@/types/button';

import '../css/button.css';

export const Button = {
	name: 'ui-entity-catalog-button',
	props: {
		buttonData: {
			type: ButtonData,
			required: true,
		},
		eventData: {
			type: Object,
			required: true,
		},
	},
	computed: {
		buttonText(): string
		{
			return (
				Type.isStringFilled(this.buttonData.text)
					? this.buttonData.text
					: Loc.getMessage('UI_JS_ENTITY_CATALOG_ITEM_DEFAULT_BUTTON_TEXT')
			);
		},
	},
	methods: {
		handleButtonClick(pointerEvent)
		{
			const event = new BaseEvent({
				data: {
					eventData: this.eventData,
					originalEvent: pointerEvent,
				}
			});

			if (Type.isFunction(this.buttonData.action))
			{
				this.buttonData.action.call(this, event);
			}
		}
	},
	template: `
		<div class="ui-entity-catalog__option-btn-block">
			<div 
				class="ui-entity-catalog__btn"
				:class="{'--lock': buttonData.locked}"
				@click="handleButtonClick"
			>{{buttonText}}</div>
		</div>
	`
};