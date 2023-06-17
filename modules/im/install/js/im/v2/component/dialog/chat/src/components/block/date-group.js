// @vue/component
export const DateGroupTitle = {
	props:
	{
		title: {
			type: String,
			required: true
		}
	},
	data()
	{
		return {};
	},
	template: `
		<div class="bx-im-dialog-chat__date-group_title_container">
			<div class="bx-im-dialog-chat__date-group_title">{{ title }}</div>
		</div>
	`
};