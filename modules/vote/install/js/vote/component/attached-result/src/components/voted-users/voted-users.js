import { Loc } from 'main.core';
import type { PopupOptions } from 'main.popup';
import { Popup } from 'ui.vue3.components.popup';
import { BIcon } from 'ui.icon-set.api.vue';
import 'ui.icon-set.main';
import 'ui.icon-set.animated';

import { VoteResultService } from '../../classes/service';
import type { BackendVotedUser } from '../../types';

import './style.css';

export const VotedUsersList = {
	name: 'VotedUsersList',
	components: { Popup, BIcon },
	props: {
		count: {
			type: Number,
			required: true,
		},
		votedUsers: {
			/** @type BackendVotedUser[] */
			type: Array,
			required: true,
		},
		signedAttachId: {
			type: String,
			required: true,
		},
		answerId: {
			type: Number,
			required: true,
		},
		maxVisibleAvatarsCount:	{
			type: Number,
			required: false,
			default: 3,
		},
		pageSize: {
			type: Number,
			required: true,
		},
		showUsers: {
			type: Boolean,
			required: true,
		},
	},
	data(): { users: BackendVotedUser[], page: number, loading: boolean, isShowPopup: boolean }
	{
		return {
			users: this.votedUsers,
			page: 1,
			loading: false,
			isShowPopup: false,
		};
	},
	computed: {
		summaryText(): string
		{
			return Loc.getMessagePlural('VOTE_JS_ATTACHED_RESULT_ANSWER_VOTED_COUNT', this.count, {
				'#COUNT#': this.count,
			});
		},
		visibleVotedUsers(): BackendVotedUser[]
		{
			return this.votedUsers.slice(0, this.maxVisibleAvatarsCount);
		},
		popupOptions(): PopupOptions
		{
			return {
				bindElement: this.$refs.showPopupBtn,
				borderRadius: '18px',
				autoHide: true,
			};
		},
	},
	methods: {
		async popupScrollHandler(event): Promise<void>
		{
			if (this.loading)
			{
				return;
			}

			if (this.count <= this.users.length)
			{
				return;
			}

			if (event.target.scrollHeight - event.target.scrollTop > event.target.clientHeight)
			{
				return;
			}

			this.loading = true;
			const nextPage = this.page + 1;
			const service = new VoteResultService(this.signedAttachId, this.pageSize);
			try
			{
				const nextPageUsers: BackendVotedUser[] = await service.loadAnswer(this.answerId, nextPage);
				this.page = nextPage;
				this.users = [...this.users, ...nextPageUsers];
			}
			catch (error)
			{
				console.error(error);
			}
			finally
			{
				this.loading = false;
			}
		},
		getUserImage(user: BackendVotedUser): string
		{
			return `background-image: url('${encodeURI(user.IMAGE)}')`;
		},
	},
	template: `
		<div class="vote-result__users">
			<div class="vote-result__avatars" v-if="showUsers">
				<span class="vote-result__avatar" v-for="user in visibleVotedUsers" :key="user.INDEX" >
					<i v-if="user.IMAGE" :style="getUserImage(user)" :title="user.NAME" class="vote-result__avatar-img"/>
				</span>
			</div>
			<div @click="isShowPopup = true" ref="showPopupBtn" class="vote-result__users-more" :class = "{ '--disable': !showUsers }">
				{{ summaryText }}
			</div>
			<Popup v-if="isShowPopup" :options="popupOptions" @close="isShowPopup = false">
				<div class="vote-result__users-popup" @scroll="this.popupScrollHandler($event)">
					<div v-for="(user, index) in users" :key="index" class="vote-result__users-popup-item">
						<img v-if="user.IMAGE" class="vote-result__users-popup-avatar" :src="user.IMAGE" alt=""/>
						<BIcon v-else
						   class="vote-result__users-popup-avatar"
						   :name="'person'"
						   :size="26"
						/>
						<div class="vote-result__users-popup-name">{{ user.NAME }}</div>
					</div>
					<BIcon v-if="loading" :name="'loader-wait'" :size="20" />
				</div>
			</Popup>
		</div>
	`,
};
