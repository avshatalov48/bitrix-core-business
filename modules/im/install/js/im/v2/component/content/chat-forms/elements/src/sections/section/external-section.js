import './css/section.css';

// @vue/component
export const CreateChatExternalSection = {
	props:
	{
		name: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			required: true,
		},
	},
	template: `
		<div :class="'--' + name" class="bx-im-content-create-chat__section bx-im-content-create-chat__section_scope --external">
			<div class="bx-im-content-create-chat__section_header">
				<div class="bx-im-content-create-chat__section_left">
					<div class="bx-im-content-create-chat__section_icon"></div>
					<div class="bx-im-content-create-chat__section_text">{{ title }}</div>
				</div>
				<div class="bx-im-content-create-chat__section_right"></div>	
			</div>
		</div>
	`,
};
