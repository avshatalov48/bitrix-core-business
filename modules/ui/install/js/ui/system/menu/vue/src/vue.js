import { Text } from 'main.core';
import { Menu, type MenuOptions } from 'ui.system.menu';
export * from 'ui.system.menu';

export const BMenu = {
	name: 'BMenu',
	props: {
		id: {
			type: String,
			default: () => `ui-vue3-menu-${Text.getRandom()}`,
		},
		options: {
			/** @type MenuOptions */
			type: Object,
			required: true,
		},
	},
	emits: ['close'],
	computed: {
		menuOptions(): MenuOptions
		{
			return { ...this.defaultOptions, ...this.options };
		},
		defaultOptions(): MenuOptions
		{
			return {
				id: this.id,
				cacheable: false,
				animation: 'fading',
				events: {
					onClose: this.handleClose,
					onDestroy: this.handleClose,
				},
			};
		},
	},
	mounted(): void
	{
		this.menu = new Menu(this.menuOptions);
		this.menu.show();
	},
	unmounted(): void
	{
		this.menu?.close();
	},
	methods: {
		handleClose(): void
		{
			this.$emit('close');
		},
	},
	template: `
		<div v-if="false"></div>
	`,
};
