import {EventEmitter} from 'main.core.events';

import {EventType} from 'im.old-chat-embedding.const';
import {Utils} from 'im.old-chat-embedding.lib.utils';

import '../css/search.css';

export const SearchResultNetworkItem = {
	name: 'SearchResultNetworkItem',
	props: {
		item: {
			type: Object,
			required: true
		},
	},
	data: function() {
		return {
			isLoading: false
		};
	},
	computed:
	{
		hasAvatar()
		{
			return this.item.getAvatar() !== '';
		},
		avatarStyle()
		{
			if (!this.hasAvatar)
			{
				return {backgroundColor: this.item.getAvatarOptions().color};
			}

			return {backgroundImage: `url('${this.item.getAvatar()}')`};
		},
		title()
		{
			return Utils.text.htmlspecialcharsback(this.item.getTitle());
		}
	},
	methods:
	{
		onClick(event)
		{
			this.isLoading = true;
			const networkCode = this.item.getId().replace('networkLines', '');

			EventEmitter.emitAsync(EventType.search.openNetworkItem, networkCode).then(eventResult => {
				if (eventResult[0].error)
				{
					console.error('Error:', eventResult[0].error);
					this.isLoading = false;

					return;
				}
				const dialogId = eventResult[0].id.toString();
				const user = this.$store.getters['users/get'](dialogId, true);
				const dialog = this.$store.getters['dialogues/get'](dialogId, true);

				EventEmitter.emit(EventType.dialog.open, {
					dialogId: dialogId,
					chat: dialog,
					user: user
				});

				this.isLoading = false;
				if (!event.altKey)
				{
					BX.MessengerProxy.clearSearchInput();
				}
			}).catch((error) => {
				console.error(error);
				this.isLoading = false;
			});
		},
	},
	template: `
		<div @click="onClick" class="bx-im-search-item">
			<div class="bx-im-search-avatar-wrap">
				<div :title="item.getTitle()" class="bx-im-component-avatar-wrap bx-im-component-avatar-size-l">
					<div
						class="bx-im-component-avatar-content bx-im-component-avatar-image"
						:class="[hasAvatar ? '' : 'bx-im-search-network-icon']"
						:style="avatarStyle"
					></div>
				</div>
			</div>
			<div class="bx-im-search-result-item-content">
				<div v class="bx-im-component-chat-title-wrap">
					<div class="bx-im-component-chat-name-left-icon bx-im-component-chat-name-left-icon-network"></div>
					<div class="bx-im-component-chat-name-text bx-im-search-network-title">
						{{title}}
					</div>
				</div>
				<div class="bx-im-search-item-content-bottom">
					<div class="bx-im-search-result-item-text-wrap">
						<div class="bx-im-search-result-item-text">{{ item.getSubtitle() }}</div>
					</div>
					<div v-if="isLoading" class="bx-search-loader bx-search-loader-small-size"></div>
				</div>
			</div>
		</div>
	`
};