import { DateFormatter } from '../../util/date-formatter';
import { SpaceViewModeTypes, SpaceViewModes, SpaceUserRoles } from '../../const/space';
import { FilterModeTypes } from '../../const/filter-mode';
import { Avatar } from './avatar';
import { Modes } from '../../const/mode';
import { ajax, Type } from 'main.core';
import { Helper } from '../../store/helper';
import { BaseEvent } from 'main.core.events';

import type { SpaceModel } from '../../model/space-model';

// @vue/component
export const SpaceContent = {
	components: {
		Avatar,
	},
	props: {
		space: {
			type: Object,
			default: () => {},
		},
		mode: {
			type: String,
			required: true,
		},
		isInvitation: {
			type: Boolean,
			default: false,
		},
		showAvatar: {
			type: Boolean,
			default: true,
		},
	},
	data(): Object
	{
		return {
			modes: Modes,
			spaceUserRoles: SpaceUserRoles,
			doShowSuccessButton: false,
		};
	},
	computed: {
		spaceModel(): SpaceModel
		{
			return this.space;
		},
		selectedFilterModeType(): string
		{
			return this.$store.state.main.selectedFilterModeType;
		},
		doShowCounter(): boolean
		{
			return this.spaceModel.counter
				&& this.spaceModel.counter > 0
				&& this.mode === this.modes.recent
				&& !this.isApplicantButtonsShown
			;
		},
		doShowPin(): boolean
		{
			return this.spaceModel.isPinned
				&& this.mode === this.modes.recent
				&& !this.isApplicantButtonsShown
				&& !this.doShowCounter
				&& this.selectedFilterModeType === FilterModeTypes.my
			;
		},
		isApplicantButtonsShown(): boolean
		{
			return this.doShowJoinButton || this.doShowPendingButton || this.doShowSuccessButton;
		},
		doShowJoinButton(): boolean
		{
			return this.spaceModel.userRole === this.spaceUserRoles.nonMember && this.mode === this.modes.recent;
		},
		doShowPendingButton(): boolean
		{
			return this.spaceModel.userRole === this.spaceUserRoles.applicant && this.mode === this.modes.recent;
		},
		isFollowing(): boolean
		{
			return this.spaceModel.userRole !== this.spaceUserRoles.member || this.spaceModel.follow;
		},
		spaceDescription(): string
		{
			const doShowSpaceVisibilityType = this.isApplicantButtonsShown
				|| !this.spaceModel.recentActivity.description
				|| this.spaceModel.recentActivity.description.length === 0
			;

			return doShowSpaceVisibilityType ? this.getVisibilityTypeName() : this.spaceModel.recentActivity.description;
		},
		isCommon(): boolean
		{
			return this.spaceModel.id === 0;
		},
	},
	created()
	{
		this.$bitrix.eventEmitter.subscribe(`onSpaceUpdate_${this.spaceModel.id}`, this.onSpaceUpdate.bind(this));
	},
	beforeUnmount()
	{
		this.$bitrix.eventEmitter.unsubscribe(`onSpaceUpdate_${this.spaceModel.id}`, this.onSpaceUpdate.bind(this));
	},
	methods: {
		loc(message: string): string
		{
			return this.$bitrix.Loc.getMessage(message);
		},
		getVisibilityTypeName(): string
		{
			if (this.isCommon)
			{
				return '';
			}

			const spaceViewModeNameMessageId = SpaceViewModes.find((spaceViewMode) => {
				return spaceViewMode.type === this.spaceModel.visibilityType;
			})?.nameMessageId;

			return Type.isStringFilled(spaceViewModeNameMessageId)
				? this.loc(spaceViewModeNameMessageId)
				: ''
			;
		},
		formatDate(timestamp: number): string
		{
			return DateFormatter.formatDate(timestamp);
		},
		isSecretSpace(visibilityType: string): boolean
		{
			return visibilityType === SpaceViewModeTypes.secret;
		},
		formatCounter(counter: number): string
		{
			if (counter > 99)
			{
				return '99+';
			}

			if (counter === 0)
			{
				return '';
			}

			return counter.toString();
		},
		counterClass(follow: boolean): string
		{
			return follow ? 'sn-spaces__list-item_counter' : 'sn-spaces__list-item_counter --mute';
		},
		showSuccessButton()
		{
			this.doShowSuccessButton = true;
			setTimeout(() => {
				this.doShowSuccessButton = false;
			}, 1000);
		},
		onSpaceUpdate(event: BaseEvent)
		{
			if (this.mode === this.modes.recent)
			{
				const spaceData = event.data;
				const helper = Helper.getInstance();
				const space: SpaceModel = helper.buildSpaces([spaceData]).pop();

				const wasUserApplicant = this.spaceModel.userRole === this.spaceUserRoles.applicant;
				const isUserMember = space.userRole === this.spaceUserRoles.member;

				if (wasUserApplicant && isUserMember)
				{
					this.showSuccessButton();
				}
			}
		},
		async onJoinButtonClick(event)
		{
			event.stopPropagation();
			await ajax.runAction('socialnetwork.api.userToGroup.join', {
				data: {
					params: {
						groupId: this.spaceModel.id,
					},
				},
			}).then((response) => {
				const confirmationNeeded = response.data.confirmationNeeded;
				let userRole = this.spaceUserRoles.member;
				if (confirmationNeeded)
				{
					userRole = this.spaceUserRoles.applicant;
				}
				else
				{
					this.showSuccessButton();
				}
				this.$store.dispatch('changeUserRole', {
					spaceId: this.spaceModel.id,
					userRole,
				});
			}, (error) => {
				console.log(error);
			});
		},
		onPendingButtonClick(event)
		{
			event.stopPropagation();
		},
		onAcceptedButtonClick(event)
		{
			event.stopPropagation();
		},
		async acceptInvitationButtonClickHandler(event)
		{
			event.stopPropagation();
			await ajax.runAction('socialnetwork.api.userToGroup.acceptOutgoingRequest', {
				data: {
					groupId: this.spaceModel.id,
				},
			}).then((response) => {
				const isSuccess = response.data;
				if (isSuccess)
				{
					this.$store.dispatch('changeUserRole', {
						spaceId: this.spaceModel.id,
						userRole: this.spaceUserRoles.member,
					});
					this.$store.dispatch('deleteInvitationFromStore', {
						spaceId: this.spaceModel.id,
					});
				}
			}, (error) => {
				console.log(error);
			});
		},
		async declineInvitationButtonClickHandler(event)
		{
			event.stopPropagation();
			await ajax.runAction('socialnetwork.api.userToGroup.rejectOutgoingRequest', {
				data: {
					groupId: this.spaceModel.id,
				},
			}).then((response) => {
				const isSuccess = response.data;
				if (isSuccess)
				{
					this.$store.dispatch('deleteInvitationFromStore', {
						spaceId: this.spaceModel.id,
					});
					if (this.isSecretSpace(this.spaceModel.visibilityType))
					{
						this.$store.dispatch('deleteSpaceFromStore', {
							spaceId: this.spaceModel.id,
						});
					}
					else
					{
						this.$store.dispatch('changeUserRole', {
							spaceId: this.spaceModel.id,
							userRole: this.spaceUserRoles.nonMember,
						});
					}
				}
			}, (error) => {
				console.log(error);
			});
		},
	},
	template: `
		<Avatar
			v-if="showAvatar"
			:avatar="spaceModel.avatar"
			:isSecret="isSecretSpace(spaceModel.visibilityType)"
			:isInvitation="isInvitation"
		/>
		<div class="sn-spaces__list-item_info">
			<div class="sn-spaces__list-item_title" :title="spaceModel.name">
				<div class="sn-spaces__list-item_name">{{spaceModel.name}}</div>
				<div
					v-if="!isFollowing"
					class="sn-spaces__list-item_mute ui-icon-set --sound-off"
					style="--ui-icon-set__icon-size: 18px;"
					data-id="spaces-list-element-mute-icon"
				></div>
			</div>
			<div class="sn-spaces__list-item_description" data-id="spaces-list-element-description">
				{{spaceDescription}}
			</div>
		</div>
		<div class="sn-spaces__list-item_details">
			<div class="sn-spaces__list-item_time" data-id="spaces-list-element-activity-date">
				{{formatDate(spaceModel.recentActivity.date.getTime())}}
			</div>
			<div class="sn-spaces__list-item_changes">
				<div
					v-if="doShowPin"
					class="ui-icon-set --pin-1"
					style='--ui-icon-set__icon-size: 18px;'
				/>
				<div
					v-if="doShowCounter"
					:class="counterClass(isFollowing)"
					data-id="spaces-list-element-counter"
				>
					{{formatCounter(spaceModel.counter)}}
				</div>
				<button
					v-if="doShowJoinButton"
					class="ui-btn ui-btn-xs ui-btn-success ui-btn-no-caps ui-btn-round sn-spaces__list-item_btn-event"
					@click="onJoinButtonClick"
					data-id="spaces-list-element-join-button"
				>
					{{loc('SOCIALNETWORK_SPACES_LIST_JOIN_SPACE_BUTTON')}}
				</button>
				<button
					v-if="doShowPendingButton"
					class="ui-btn ui-btn-xs ui-btn-primary ui-btn-no-caps ui-btn-round sn-spaces__list-item_btn-event"
					@click="onPendingButtonClick"
					data-id="spaces-list-element-pending-button"
				>
					<div class="ui-icon-set --mail-out" style='--ui-icon-set__icon-color: white;'></div>
				</button>
				<button
					v-if="doShowSuccessButton"
					class="ui-btn ui-btn-xs ui-btn-primary ui-btn-no-caps ui-btn-round sn-spaces__list-item_btn-event"
					@click="onAcceptedButtonClick"
					data-id="spaces-list-element-success-button"
				>
					<div class="ui-icon-set --check" style='--ui-icon-set__icon-color: white;'></div>
				</button>
			</div>
		</div>
		<div v-if="isInvitation" class="sn-spaces__list-item_btns">
			<button
				class="ui-btn ui-btn-sm ui-btn-success ui-btn-no-caps ui-btn-round"
				@click="acceptInvitationButtonClickHandler"
				data-id="spaces-list-element-accept-invitation-button"
			>
				{{loc('SOCIALNETWORK_SPACES_LIST_ACCEPT_INVITATION_BUTTON')}}
			</button>
			<button
				class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-no-caps ui-btn-round"
				@click="declineInvitationButtonClickHandler"
				data-id="spaces-list-element-decline-invitation-button"
			>
				{{loc('SOCIALNETWORK_SPACES_LIST_DECLINE_INVITATION_BUTTON')}}
			</button>
		</div>
	`,
};
