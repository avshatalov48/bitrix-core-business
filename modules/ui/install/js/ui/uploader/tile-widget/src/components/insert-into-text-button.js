import { Loc } from 'main.core';
import { Popup } from 'main.popup';
import type { BaseEvent } from 'main.core.events';

import { BIcon } from 'ui.icon-set.api.vue';
import { Outline } from 'ui.icon-set.api.core';
import 'ui.icon-set.outline';
import type { BitrixVueComponentProps } from 'ui.vue3';

import '../css/insert-into-text-button.css';

// @vue/component
export const InsertIntoTextButton: BitrixVueComponentProps = {
	name: 'InsertIntoTextButton',
	components: {
		BIcon,
	},
	inject: ['emitter'],
	props: {
		item: {
			type: Object,
			default: () => {},
		},
	},
	setup(): Object
	{
		return {
			Outline,
		};
	},
	computed: {
		isInserted(): boolean
		{
			return this.item.customData?.tileSelected === true;
		},
	},
	methods: {
		handleClick(): void
		{
			this.emitter.emit('onInsertIntoText', { item: this.item });
		},
		handleMouseEnter(event: MouseEvent): void
		{
			if (this.hintPopup)
			{
				return;
			}

			const targetNode: HTMLElement = event.currentTarget;
			const targetNodeWidth: number = targetNode.offsetWidth;

			this.hintPopup = new Popup({
				content: Loc.getMessage('TILE_UPLOADER_INSERT_INTO_THE_TEXT'),
				cacheable: false,
				animation: 'fading-slide',
				bindElement: targetNode,
				targetContainer: document.body,
				offsetTop: 0,
				bindOptions: {
					position: 'top',
					forceBindPosition: true,
				},
				darkMode: true,
				events: {
					onClose: (): void => {
						this.hintPopup.destroy();
						this.hintPopup = null;
					},
					onShow: (baseEvent: BaseEvent): void => {
						const popup = baseEvent.getTarget();
						const popupWidth = popup.getPopupContainer().offsetWidth;
						const offsetLeft: number = (targetNodeWidth / 2) - (popupWidth / 2);
						const angleShift: number = Popup.getOption('angleLeftOffset') - Popup.getOption('angleMinTop');

						popup.setAngle({ offset: popupWidth / 2 - angleShift });
						popup.setOffset({ offsetLeft: offsetLeft + Popup.getOption('angleLeftOffset') });
					},
				},
			});

			this.hintPopup.show();
		},
		handleMouseLeave(): void
		{
			if (this.hintPopup)
			{
				this.hintPopup.close();
				this.hintPopup = null;
			}
		},
	},
	template: `
		<BIcon
			class="ui-tile-uploader-insert-into-text-button"
			:class="{ '--inserted': isInserted }"
			:name="Outline.PROMPT_VAR"
			@click="handleClick"
			@mouseenter="handleMouseEnter"
			@mouseleave="handleMouseLeave"
		/>
	`,
};
