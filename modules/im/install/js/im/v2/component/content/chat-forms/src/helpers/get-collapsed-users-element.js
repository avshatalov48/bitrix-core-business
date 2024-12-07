const ENTITY_ID = 'imUpdateChatCollapsedUsers';
const AVATAR_URL = '/bitrix/js/im/v2/component/content/chat-forms/src/css/images/collapsed-users-avatar.svg';

export type TagSelectorElement = {
	id: number,
	entityId: string,
	avatar: string,
	title: {
		text: string,
		type: 'html',
	},
	deselectable: boolean,
	tagOptions: {
		onclick: Function,
	},
};

export function getCollapsedUsersElement({ title, onclick }): TagSelectorElement
{
	const textNode = `
		<div class="bx-im-content-chat-forms__collapsed-users">
			${title}
			<div class="bx-im-content-chat-forms__collapsed-users-icon"></div>
		</div>
	`;

	return {
		id: 0,
		entityId: ENTITY_ID,
		avatar: AVATAR_URL,
		title: {
			text: textNode,
			type: 'html',
		},
		deselectable: false,
		tagOptions: { onclick },
	};
}
