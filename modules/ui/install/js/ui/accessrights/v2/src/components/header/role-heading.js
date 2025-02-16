import { Event } from 'main.core';
import { Popup } from 'main.popup';
import { Button, ButtonColor, ButtonSize, CancelButton } from 'ui.buttons';
import { RichMenuItem, RichMenuItemIcon, RichMenuPopup } from 'ui.vue3.components.rich-menu';
import { mapGetters, mapState } from 'ui.vue3.vuex';

export const RoleHeading = {
	name: 'RoleHeading',
	components: { RichMenuPopup, RichMenuItem },
	props: {
		userGroup: {
			/** @type UserGroup */
			type: Object,
			required: true,
		},
	},
	data(): Object {
		return {
			isEdit: false,
			isPopupShown: false,
		};
	},
	computed: {
		RichMenuItemIcon: () => RichMenuItemIcon,
		...mapState({
			isSaving: (state) => state.application.isSaving,
			guid: (state) => state.application.guid,
			maxVisibleUserGroups: (state) => state.application.options.maxVisibleUserGroups,
		}),
		...mapGetters({
			isMaxVisibleUserGroupsReached: 'userGroups/isMaxVisibleUserGroupsReached',
			isMaxValueSetForAny: 'accessRights/isMaxValueSetForAny',
			isMinValueSetForAny: 'accessRights/isMinValueSetForAny',
		}),
		title: {
			get(): string {
				return this.userGroup.title;
			},
			set(title: string): void {
				this.$store.dispatch('userGroups/setRoleTitle', {
					userGroupId: this.userGroup.id,
					title,
				});
			},
		},
	},
	watch: {
		isEdit(newValue): void {
			if (newValue === true)
			{
				this.bindClickedOutsideHandler();

				void this.$nextTick(() => {
					this.$refs.input.scrollIntoView({
						behavior: 'smooth',
						block: 'nearest',
						inline: 'nearest',
					});

					this.$refs.input.focus();
					this.$refs.input.select();
				});
			}
			else
			{
				this.unbindClickedOutsideHandler();
			}
		},
	},
	mounted()
	{
		// todo fix hide/show new role
		if (this.userGroup.isNew)
		{
			// start editing a newly created role right away
			this.isEdit = true;
		}
	},
	beforeUnmount()
	{
		this.unbindClickedOutsideHandler();
	},
	methods: {
		bindClickedOutsideHandler(): void {
			Event.bind(window, 'click', this.turnOffEditWhenClickedOutside, {
				capture: true,
			});
		},
		unbindClickedOutsideHandler(): void {
			Event.unbind(window, 'click', this.turnOffEditWhenClickedOutside, {
				capture: true,
			});
		},
		turnOffEditWhenClickedOutside(event: PointerEvent): void {
			if (event.target !== this.$refs.input)
			{
				this.isEdit = false;
			}
		},
		showDeleteConfirmation(): void {
			const popup = new Popup({
				bindElement: this.$refs.container,
				width: 250,
				overlay: true,
				contentPadding: 10,
				content: this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_POPUP_REMOVE_ROLE'),
				className: 'ui-access-rights-v2-text-center',
				animation: 'fading-slide',
				cacheable: false,
				buttons: [
					new Button({
						text: this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_POPUP_REMOVE_ROLE_YES'),
						size: ButtonSize.SMALL,
						color: ButtonColor.PRIMARY,
						events: {
							click: () => {
								popup.destroy();
								this.$store.dispatch('userGroups/removeUserGroup', {
									userGroupId: this.userGroup.id,
								});
							},
						},
					}),
					new CancelButton({
						size: ButtonSize.SMALL,
						events: {
							click: () => {
								popup.destroy();
							},
						},
					}),
				],
			});

			popup.show();
		},
		showActionsMenu(): void {
			if (!this.isSaving)
			{
				this.isPopupShown = true;
			}
		},
		onSetMaxValuesClick(): void {
			this.isPopupShown = false;

			this.$store.dispatch('userGroups/setMaxAccessRightValues', {
				userGroupId: this.userGroup.id,
			});
		},
		onSetMinValuesClick(): void {
			this.isPopupShown = false;

			this.$store.dispatch('userGroups/setMinAccessRightValues', {
				userGroupId: this.userGroup.id,
			});
		},
		onEnableEditClick(): void {
			this.isPopupShown = false;

			this.isEdit = true;
		},
		onCopyRoleClick(): void {
			if (this.isMaxVisibleUserGroupsReached)
			{
				return;
			}

			this.isPopupShown = false;

			this.$store.dispatch('userGroups/copyUserGroup', { userGroupId: this.userGroup.id });
		},
		onDeleteRoleClick(): void {
			this.isPopupShown = false;

			this.showDeleteConfirmation();
		},
	},
	template: `
		<div ref="container" class='ui-access-rights-v2-role'>
			<div class="ui-access-rights-v2-role-value-container">
				<input
					v-if="isEdit && !isSaving"
					ref="input"
					type='text'
					class='ui-access-rights-v2-role-input'
					v-model="title"
					:placeholder="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_NAME')"
					@keydown.enter="isEdit = false"
				/>
				<div v-else class='ui-access-rights-v2-role-value' :title="title">{{ title }}</div>
			</div>
			<div 
				ref="menu"
				class="ui-icon-set --more ui-access-rights-v2-icon-more" 
				@click="showActionsMenu"
			>
				<RichMenuPopup v-if="isPopupShown" @close="isPopupShown = false" :popup-options="{bindElement: $refs.menu}">
					<RichMenuItem
						v-if="isMaxValueSetForAny"
						:icon="RichMenuItemIcon.check"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS_SUBTITLE')"
						@click="onSetMaxValuesClick"
					/>
					<RichMenuItem
						v-if="isMinValueSetForAny"
						:icon="RichMenuItemIcon['red-lock']"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS_SUBTITLE')"
						@click="onSetMinValuesClick"
					/>
					<RichMenuItem
						:icon="RichMenuItemIcon.pencil"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_RENAME')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_RENAME_SUBTITLE')"
						@click="onEnableEditClick"
					/>
					<RichMenuItem
						:icon="RichMenuItemIcon.copy"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COPY')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COPY_ROLE_SUBTITLE')"
						:disabled="isMaxVisibleUserGroupsReached"
						:hint="
							isMaxVisibleUserGroupsReached
								? $Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_COPYING_DISABLED', {
									'#COUNT#': maxVisibleUserGroups,
								})
								: null
						"
						@click="onCopyRoleClick"
					/>
					<RichMenuItem
						:icon="RichMenuItemIcon['trash-bin']"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_REMOVE')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_REMOVE_SUBTITLE')"
						@click="onDeleteRoleClick"
					/>
				</RichMenuPopup>
			</div>
		</div>
	`,
};
