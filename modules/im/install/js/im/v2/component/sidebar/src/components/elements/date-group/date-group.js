import './date-group.css';

// @vue/component
export const DateGroup = {
	name: 'DateGroup',
	props: {
		dateText: {
			type: String,
			required: true,
		},
	},
	template: `
		<div class="bx-im-sidebar-date-group__container bx-im-sidebar-date-group__scope">
			<div class="bx-im-sidebar-date-group__text">
				{{ dateText }}
			</div>
		</div>
	`,
};
