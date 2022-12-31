import {Action} from '../classes/items/action';

// @vue/component
export const ActionComponent = {
	props:
	{
		element: {
			type: Object,
			required: true
		},
		isSelected: {
			type: Boolean,
			required: true
		}
	},
	data()
	{
		return {};
	},
	computed:
	{
		action(): Action
		{
			return this.element;
		},
		containerClasses(): string[]
		{
			const classes = [`--${this.action.id}`];
			if (this.isSelected)
			{
				classes.push('--selected');
			}

			return classes;
		}
	},
	template:
	`
		<div :class="containerClasses" class="bx-im-call-background__item --action">
			<div class="bx-im-call-background__action_icon"></div>
			<div class="bx-im-call-background__action_title">
				{{ action.title }}
			</div>
		</div>
	`
};