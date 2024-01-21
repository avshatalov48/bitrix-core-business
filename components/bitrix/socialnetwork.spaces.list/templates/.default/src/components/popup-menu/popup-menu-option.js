// @vue/component

export const PopupMenuOption = {
	props: {
		option: {
			type: Object,
			default: () => {},
		},
		isSelected: Boolean,
	},
	emits: ['changeSelectedOption'],
	computed: {
		active(): string
		{
			return this.isSelected ? '--active' : '';
		},
		iconClass(): string
		{
			return `--${this.option.type}-spaces`;
		},
		dataId(): string
		{
			return `spaces-list-popup-menu-option-${this.option.type}`;
		},
	},
	template: `
		<div
			@click="this.$emit('changeSelectedOption', option.type)"
			class="sn-spaces__popup-menu_item"
			:class="active"
			:data-id="dataId"
		>
			<div class="sn-spaces__popup-menu_item-icon" :class="iconClass"/>
			<div class="sn-spaces__popup-menu_item-info">
				<div class="sn-spaces__popup-menu_item-name">{{ option.name }}</div>
				<div class="sn-spaces__popup-menu_item-description">{{ option.description }}</div>
			</div>
		</div>
	`,
};
