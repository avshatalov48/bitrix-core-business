import {EventEmitter} from 'main.core.events';
import {EventType} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';
import {AvatarOpenline} from './avatar-openline';
import {SearchUtils} from '../search-utils';
import {SearchItem} from '../search-item';
import '../css/search.css';

export const SearchResultOpenlineItem = {
	name: 'SearchResultOpenlineItem',
	components: {AvatarOpenline},
	props: {
		item: {
			type: SearchItem,
			required: true
		},
	},
	computed:
	{
		title()
		{
			return Utils.text.htmlspecialcharsback(this.item.getTitle());
		}
	},
	methods:
	{
		onClick(event)
		{
			EventEmitter.emit(EventType.dialog.open, {
				dialogId: this.item.getDialogId(),
				chat: SearchUtils.convertKeysToLowerCase(this.item.getChatCustomData())
			});

			if (!event.altKey)
			{
				BX.MessengerProxy.clearSearchInput();
			}
		},
	},
	template: `
		<div @click="onClick" class="bx-im-search-item">
			<div class="bx-im-search-avatar-wrap">
				<AvatarOpenline :item="item" size="L"></AvatarOpenline>
			</div>
			<div class="bx-im-search-result-item-content bx-im-search-result-item-department-content">
				<div v class="bx-im-component-chat-title-wrap">
					<div class="bx-im-component-chat-name-text" :title="item.getTitle()">{{title}}</div>
				</div>
			</div>
		</div>
	`
};