import { BaseEvent } from 'main.core.events';
import { mapGetters } from 'ui.vue3.vuex';
import { Client } from '../../api/client';
import { EventTypes } from '../../const/event';
import { KeyboardCodes } from '../../const/keyboard-codes';
import { Modes } from '../../const/mode';
import { SpaceViewModeTypes, SpaceViewModes } from '../../const/space';
import { LinkManager } from '../../util/link-manager';
import { PopupMenu } from '../popup-menu/popup-menu';
import { Editor as AvatarEditor } from 'ui.avatar-editor';
import { Type, Event, ajax, Dom } from 'main.core';

// @vue/component
export const SpaceAddForm = {
	components: {
		PopupMenu,
	},
	data(): Object
	{
		return {
			modes: Modes,
			spaceViewModes: SpaceViewModes,
			spaceViewModeTypes: SpaceViewModeTypes,
			spaceData: {
				name: '',
				viewMode: SpaceViewModeTypes.open,
				image: null,
			},
			isFocusedOnNameInput: false,
			showViewModePopup: false,
			wasCreateGroupRequestSent: false,
			doShowNameAlreadyExistsError: false,
			avatarColor: '',
		};
	},
	computed: {
		...mapGetters({
			avatarColors: 'avatarColors',
			previousAvatarColor: 'previousAvatarColor',
		}),
		isDataValidated(): boolean
		{
			return this.spaceData.name.length > 0 && !this.doShowNameAlreadyExistsError;
		},
		confirmButtonClass(): string
		{
			return this.isDataValidated && !this.wasCreateGroupRequestSent ? '' : '--disabled';
		},
		popupMenuOptions(): Array
		{
			return this.spaceViewModes.map((spaceViewMode) => ({
				type: spaceViewMode.type,
				name: this.loc(spaceViewMode.nameMessageId),
				description: this.loc(spaceViewMode.descriptionMessageId),
			}));
		},
		selectedViewModeOptionName(): string
		{
			return this.loc(`SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_${this.spaceData.viewMode.toUpperCase()}_TITLE`);
		},
		nameInputModifier(): string
		{
			return this.doShowNameAlreadyExistsError ? 'sn-spaces__list-add-item_input-error' : '';
		},
		isNameInputReadOnly(): boolean
		{
			return this.wasCreateGroupRequestSent;
		},
	},
	mounted()
	{
		this.$refs.spaceAddFormNameInput.focus();
		Event.bind(document, 'click', this.handleAutoHide, true);
		Event.bind(document, 'keydown', this.handleKeyDown);
		this.$bitrix.eventEmitter.subscribe(EventTypes.showUpperSpaceAddForm, this.chooseRandomAvatarColor);
		this.chooseRandomAvatarColor();
	},
	unmounted()
	{
		Event.unbind(document, 'click', this.handleAutoHide, true);
		Event.unbind(document, 'keydown', this.handleKeyDown);
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.showUpperSpaceAddForm, this.chooseRandomAvatarColor);
	},
	methods: {
		loc(message: string): string
		{
			return this.$bitrix.Loc.getMessage(message);
		},
		openViewModePopup()
		{
			this.showViewModePopup = true;
		},
		onChangeSelectedOption(newOption)
		{
			this.spaceData.viewMode = newOption;
		},
		chooseSpaceImage()
		{
			const avatarEditor = this.getAvatarEditor();
			avatarEditor.show('file');
		},
		getAvatarEditor(): AvatarEditor
		{
			if (!this.avatarEditor)
			{
				this.avatarEditor = new AvatarEditor({
					enableCamera: false,
				});

				Dom.addClass(this.avatarEditor.popup.getPopupContainer(), 'sn-spaces__avatar-editor');

				this.avatarEditor.subscribe('onApply', (event: BaseEvent) => {
					const [file] = event.getCompatData();
					file.name ??= 'tmp.png';

					this.spaceData.image = file;
					this.$refs.groupImage.src = URL.createObjectURL(file);

					Dom.style(this.$refs.groupImageContainer, 'background', 'none');
					Dom.style(this.$refs.groupImage, 'background', 'none');
				});
			}

			return this.avatarEditor;
		},
		onAddSpaceClickHandler()
		{
			this.addSpace();
		},
		chooseRandomAvatarColor(): void
		{
			if (this.spaceData.image !== null)
			{
				return;
			}

			if (!Type.isArrayFilled(this.avatarColors))
			{
				return;
			}

			const colors = this.avatarColors.filter((color) => color !== this.previousAvatarColor);
			this.avatarColor = colors[Math.floor(Math.random() * colors.length)];
			Dom.style(this.$refs.groupImageContainer, 'backgroundColor', `#${this.avatarColor}`);
			this.$store.dispatch('setAvatarColor', this.avatarColor);
		},
		addSpace()
		{
			this.spaceData.name = this.spaceData.name.trim();
			if (this.spaceData.name.length === 0)
			{
				return;
			}

			const formData = new FormData();
			formData.append('groupName', this.spaceData.name);
			formData.append('viewMode', this.spaceData.viewMode);
			if (this.spaceData.image !== null)
			{
				formData.append('groupImage', this.spaceData.image, this.spaceData.image.name);
			}
			formData.append('avatarColor', this.avatarColor);

			this.wasCreateGroupRequestSent = true;
			ajax.runAction('socialnetwork.api.workgroup.createGroup', {
				data: formData,
			}).then((response) => {
				BX.Socialnetwork.Spaces.space.reloadPageContent(
					`${LinkManager.getSpaceLink(response.data.groupId)}?empty-state=enabled`,
				);
				this.$bitrix.eventEmitter.emit(EventTypes.hideSpaceAddForm);

				// eslint-disable-next-line promise/catch-or-return
				Client.loadSpaceData(response.data.groupId)
					// eslint-disable-next-line promise/no-nesting
					.then((data) => {
						this.$store.dispatch('addSpacesToView', { mode: Modes.recentSearch, spaces: [data.space] });
						this.$store.dispatch('setSelectedSpace', data.space.id);
					})
				;
			}, (errorResponse) => {
				errorResponse.errors.forEach((error) => {
					if (error.code === 'ERROR_GROUP_NAME_EXISTS')
					{
						this.doShowNameAlreadyExistsError = true;
					}
				});
				this.wasCreateGroupRequestSent = false;
				console.log(errorResponse);
			});
		},
		handleAutoHide(event)
		{
			if (this.shouldHideForm(event))
			{
				this.$bitrix.eventEmitter.emit(EventTypes.hideSpaceAddForm);
			}
		},
		handleKeyDown(event)
		{
			if (
				this.isFocusedOnNameInput
				&& event.keyCode === KeyboardCodes.enter
				&& this.isDataValidated
				&& !this.wasCreateGroupRequestSent
			)
			{
				this.addSpace();
			}
		},
		shouldHideForm(event): boolean
		{
			const notVisible = this.$refs.spaceAddForm.offsetHeight === 0;
			if (notVisible)
			{
				return false;
			}

			const clickOnSpace = event.target.closest('.sn-spaces__list-item') !== null;
			const clickOnSelf = this.$refs.spaceAddForm.contains(event.target);
			const avatarPopupShown = this.avatarEditor?.popup?.isShown();
			const viewModePopupShown = this.showViewModePopup;
			const anyPopupShown = avatarPopupShown || viewModePopupShown;

			return !clickOnSelf && !clickOnSpace && !anyPopupShown && !this.formDataChanged();
		},
		formDataChanged(): boolean
		{
			const isNameFilled = this.spaceData.name !== '';
			const isViewModeChanged = this.spaceData.viewMode !== SpaceViewModeTypes.open;
			const isImageChosen = this.$refs.groupImage.src !== '';

			return isNameFilled || isViewModeChanged || isImageChosen;
		},
	},
	template: `
		<div class="sn-spaces__list-item --add-active --error" ref="spaceAddForm" data-id="spaces-list-add-space-form">
			<div class="sn-spaces__list-item_add">
				<div class="sn-spaces__list-item_icon" ref="groupImageContainer">
					<img
						alt="" class="spaces-list-add-space-image"
						@click="chooseSpaceImage()"
						ref="groupImage"
						data-id="spaces-list-add-space-image"
					>
				</div>
				<div class="sn-spaces__list-item_info">
					<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-xs ui-ctl-no-padding ui-ctl-no-border">
						<input
							v-model="spaceData.name"
							type="text" class="ui-ctl-element"
							:placeholder="loc('SOCIALNETWORK_SPACES_LIST_ADD_SPACE_FORM_NAME_PLACEHOLDER')"
							ref="spaceAddFormNameInput"
							data-id="spaces-list-add-space-name-input"
							@focus="isFocusedOnNameInput=true"
							@blur="isFocusedOnNameInput=false"
							:class="nameInputModifier"
							@input="doShowNameAlreadyExistsError=false"
							:readonly="isNameInputReadOnly"
						>
					</div>
					<div @click="openViewModePopup" ref="privacySelector" class="sn-spaces__list-item_select-private">
						<div
							class="sn-spaces__list-item_select-private-text"
							data-id="spaces-list-add-space-view-mode"
						>{{selectedViewModeOptionName}}</div>
						<div class="ui-icon-set --chevron-down" style="--ui-icon-set__icon-size: 14px;"></div>
						<PopupMenu
							v-if="showViewModePopup"
							:options="popupMenuOptions"
							context="space-add-form"
							:bind-element="this.$refs.privacySelector || {}"
							:hint="loc('SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_HINT')"
							:selectedOption="this.spaceData.viewMode"
							@close="showViewModePopup = false"
							@changeSelectedOption="onChangeSelectedOption"
						/>
					</div>
				</div>
				<div class="sn-spaces__list-item_details">
					<div
						class="ui-icon-set --circle-check sn-spaces__list-item_save-btn" :class="confirmButtonClass"
						@click="onAddSpaceClickHandler()"
						data-id="spaces-list-add-space-create-button"
					></div>
				</div>
			</div>
			<div v-show="doShowNameAlreadyExistsError" class="sn-spaces__list-item_error">
				{{loc('SOCIALNETWORK_SPACES_LIST_NAME_ALREADY_EXISTS_ERROR')}}
			</div>
		</div>
	`,
};
