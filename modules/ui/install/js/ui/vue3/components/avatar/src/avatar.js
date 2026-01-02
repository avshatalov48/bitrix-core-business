import { AvatarConcreteClassByType } from './const';
import type { AvatarType } from './type';
import { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const Avatar: BitrixVueComponentProps = {
	name: 'UiAvatar',
	props: {
		options: {
			/** @type AvatarOptions */
			type: Object,
			required: false,
			default: () => ({}),
		},
		type: {
			/** @type AvatarType */
			type: String,
			required: false,
			default: 'round',
			validator(val): boolean
			{
				return Object.keys(AvatarConcreteClassByType).includes(val);
			},
		},
	},
	data(): { size: number | null }
	{
		return {
			size: null,
		};
	},
	created(): void
	{
		const ConcreteClass = AvatarConcreteClassByType[this.type];

		/** @type AvatarBase */
		this.instance = new ConcreteClass(this.options);

		this.size = this.options.size ?? null;
	},
	mounted(): void
	{
		this.instance.renderTo(this.$refs.container);
	},
	template: `
		<div ref="container" :style="{ 'width': size + 'px', 'height': size + 'px'}"></div>
	`,
};

export type {
	AvatarType,
};
