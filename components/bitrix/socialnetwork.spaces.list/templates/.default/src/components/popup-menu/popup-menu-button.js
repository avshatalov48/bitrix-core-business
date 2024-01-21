import { Type } from 'main.core';

// @vue/component
export const PopupMenuButton = {
	props: {
		config: {
			type: Object,
			required: true,
		},
	},
	emits: ['popupMenuButtonClick'],
	computed: {
		doShowIcon(): boolean
		{
			return Type.isStringFilled(this.config.class);
		},
	},
	template: `
		<div
			class="sn-spaces__popup-menu_item sn-spaces__popup-menu_item-btn"
			data-id="spaces-popup-menu-button"
			@click="this.$emit('popupMenuButtonClick')"
		>
			<div v-if="doShowIcon" class="ui-icon-set" :class="config.class"></div>
			{{config.text}}
		</div>
	`,
};
