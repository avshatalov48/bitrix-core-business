import { ActionHint } from './action-hint';
import { CardBoxHelp } from './card-box-help';

import 'ui.buttons';

export const CardBoxTrait = {
	props: {
		isActive: {
			type: Boolean,
			required: true,
		},
		isHovered: {
			type: Boolean,
			required: true,
			default: false,
		},
	},
	components: {
		CardBoxHelp,
		ActionHint,
	},
	computed: {
		cardItemClass(): Object
		{
			return {
				'--active': this.isHovered,
			};
		},
		cardItemStyle(): Object
		{
			return {
				cursor: this.isActive ? 'default' : 'pointer',
			};
		},
		langClass(): string
		{
			return `--${this.$Bitrix.Loc.getMessage('LANGUAGE_ID') || 'en'}`;
		},
	},
	methods: {
		onClick()
		{
			this.$emit('pick');
		},
		mouseenter()
		{
			this.$emit('enter');
		},
		mouseleave()
		{
			this.$emit('leave');
		},
	},
};
