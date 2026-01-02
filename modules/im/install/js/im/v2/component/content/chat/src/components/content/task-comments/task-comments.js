import { BaseChatContent } from 'im.v2.component.content.elements';

export const TaskCommentsContent = {
	name: 'TaskCommentsContent',
	components: { BaseChatContent },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	template: `
		<BaseChatContent :dialogId="dialogId" />
	`,
};
