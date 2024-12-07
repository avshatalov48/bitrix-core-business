import { ChatLinks } from '../../../elements/chat-links/chat-links';
import { ChatFavourites } from '../../../elements/chat-favourites/chat-favourites';
import { ChatDescription } from '../../../elements/chat-description/chat-description';

import '../css/info.css';

// @vue/component
export const CopilotInfoPreview = {
	name: 'CopilotInfoPreview',
	components: { ChatDescription, ChatLinks, ChatFavourites },
	props:
		{
			dialogId: {
				type: String,
				required: true,
			},
		},
	template: `
		<div class="bx-im-sidebar-info-preview__container">
			<ChatDescription :dialogId="dialogId" />
			<ChatFavourites :dialogId="dialogId" />
		</div>
	`,
};
