import { Loc } from 'main.core';
import { type BaseEvent } from 'main.core.event';
import { Dialog, type ItemOptions } from 'ui.entity-selector';
import { RichMenuItem, RichMenuItemIcon, RichMenuPopup } from 'ui.vue3.components.rich-menu';
import { mapGetters, mapState } from 'ui.vue3.vuex';
import { EntitySelectorContext } from '../../integration/entity-selector/dictionary';
import { ItemsMapper } from '../../integration/entity-selector/items-mapper';
import { SELECTED_ALL_USER_ID } from '../../store/model/user-groups-model';
import { saveSortConfigForAllUserGroups } from '../../utils';
import { CellLayout } from '../layout/cell-layout';
import { ColumnLayout } from '../layout/column-layout';
import '../../css/header/roles-control.css';
import { ItemListSelector } from 'ui.accessrights.v2.item-list-selector';

export const RolesControl = {
	name: 'RolesControl',
	components: { CellLayout, ColumnLayout, RichMenuPopup, RichMenuItem },
	props: {
		userGroups: {
			type: Map,
			required: true,
		},
	},
	viewDialog: null,
	computed: {
		RichMenuItemIcon: () => RichMenuItemIcon,
		...mapState({
			allUserGroups: (state) => state.userGroups.collection,
			maxVisibleUserGroups: (state) => state.application.options.maxVisibleUserGroups,
			guid: (state) => state.application.guid,
			userSortConfigName: (state) => state.application.options.userSortConfigName,
			selectedMember: (state) => state.userGroups.selectedMember,
			sortConfig: (state) => state.userGroups.sortConfig,
		}),
		...mapGetters({
			isMaxVisibleUserGroupsSet: 'application/isMaxVisibleUserGroupsSet',
			isMaxVisibleUserGroupsReached: 'userGroups/isMaxVisibleUserGroupsReached',
			userGroupsBySelectedMember: 'userGroups/userGroupsBySelectedMember',
		}),
		shownGroupsCounter(): string {
			return this.$Bitrix.Loc.getMessage(
				'JS_UI_ACCESSRIGHTS_V2_ROLE_COUNTER',
				{
					'#VISIBLE_ROLES#': this.userGroups.size,
					'#ALL_ROLES#': this.userGroupsBySelectedMember.size,
					'#GREY_START#': '<span style="opacity: var(--ui-opacity-30)">',
					'#GREY_FINISH#': '</span>',
				},
			);
		},
		copyDialogItems(): ItemOptions[] {
			return ItemsMapper.mapUserGroups(this.userGroupsBySelectedMember);
		},
		viewDialogItems(): ItemOptions[] {
			const result: ItemOptions[] = [];
			const selectedMemberId = this.selectedMember?.id ? this.selectedMember.id : SELECTED_ALL_USER_ID;

			for (const copyDialogItem of ItemsMapper.mapUserGroups(this.userGroupsBySelectedMember))
			{
				result.push({
					...copyDialogItem,
					selected: this.userGroups.has(copyDialogItem.id),
					sort: this.sortConfig[selectedMemberId] ? this.sortConfig[selectedMemberId][copyDialogItem.id] : null,
				});
			}

			return result;
		},
	},
	data(): Object {
		return {
			isPopupShown: false,
		};
	},
	methods: {
		onCreateNewRoleClick(): void {
			if (this.isMaxVisibleUserGroupsReached)
			{
				return;
			}

			this.isPopupShown = false;

			this.$store.dispatch('userGroups/addUserGroup');
		},
		onRoleViewClick(): void {
			this.isPopupShown = false;

			this.showViewDialog(this.$refs.configure);
		},
		onCopyRoleClick(): void {
			if (this.isMaxVisibleUserGroupsReached)
			{
				return;
			}

			this.isPopupShown = false;

			this.showCopyDialog();
		},
		showCopyDialog(): void {
			const copyDialog = new Dialog({
				context: EntitySelectorContext.ROLE,
				targetNode: this.$refs.configure,
				multiple: false,
				dropdownMode: true,
				enableSearch: true,
				cacheable: false,
				items: this.copyDialogItems,
				events: {
					'Item:onSelect': (dialogEvent: BaseEvent) => {
						const { item } = dialogEvent.getData();

						this.$store.dispatch('userGroups/copyUserGroup', { userGroupId: item.getId() });
					},
				},
			});

			copyDialog.show();
		},
		showViewDialog(target: HTMLElement): void {
			this.viewDialog = new ItemListSelector({
				title: this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLES_SELECTOR_TITLE'),
				subtitle: this.maxVisibleUserGroups ? Loc.getMessagePlural(
					'JS_UI_ACCESSRIGHTS_V2_ROLES_SELECTOR_SUBTITLE',
					this.maxVisibleUserGroups,
					{ '#COUNT#': this.maxVisibleUserGroups },
				)
					: null,
				targetNode: target,
				items: this.viewDialogItems,
				maxSelected: this.maxVisibleUserGroups,
				events: {
					onSave: () => {
						const selectedItems = this.viewDialog.getSelectedItems();
						const userSortConfig = {};

						selectedItems.forEach((item, index) => {
							userSortConfig[item.id] = index;
						});
						this.$store.dispatch('userGroups/updateSortConfigForSelectedMember', { sortConfigForSelectedMember: userSortConfig });

						if (!this.selectedMember?.id || this.selectedMember.id === SELECTED_ALL_USER_ID)
						{
							saveSortConfigForAllUserGroups(this.userSortConfigName, userSortConfig);
						}

						this.viewDialog = null;
					},
					onHide: () => {
						this.viewDialog = null;
					},
				},
			});

			this.viewDialog.show();
		},
		toggleViewDialog(target: HTMLElement): void {
			if (this.viewDialog)
			{
				this.viewDialog.hide();
			}
			else
			{
				this.showViewDialog(target);
			}
		},
	},
	template: `
		<ColumnLayout>
			<CellLayout class="ui-access-rights-v2-header-roles-control">
				<div class='ui-access-rights-v2-column-item-text ui-access-rights-v2-header-roles-control-header'>
					<div>{{ $Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLES') }}</div>
					<div
						ref="configure"
						class="ui-icon-set --more-l ui-access-rights-v2-icon-more"
						style="position: absolute; right: 11px; top: 5px;"
						@click="isPopupShown = true"
					>
						<RichMenuPopup v-if="isPopupShown" @close="isPopupShown = false" :popup-options="{bindElement: $refs.configure}">
							<RichMenuItem
								:icon="RichMenuItemIcon.role"
								:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_NEW_ROLE')"
								:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_NEW_ROLE_SUBTITLE')"
								:disabled="isMaxVisibleUserGroupsReached"
								:hint="
									isMaxVisibleUserGroupsReached
										? $Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_ADDING_DISABLED', {
											'#COUNT#': maxVisibleUserGroups,
										})
										: null
								"
								@click="onCreateNewRoleClick"
							/>
							<RichMenuItem
								:icon="RichMenuItemIcon.copy"
								:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COPY_ROLE')"
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
								:icon="RichMenuItemIcon['opened-eye']"
								:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_VIEW')"
								:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_VIEW_SUBTITLE_MSGVER_1')"
								@click="onRoleViewClick"
							/>
						</RichMenuPopup>
					</div>
				</div>
				<div class="ui-access-rights-v2-header-roles-control-actions">
					<div
						ref="counter"
						class="ui-access-rights-v2-header-roles-control-counter"
						@click="toggleViewDialog($refs.counter)"
					>
						<div class="ui-icon-set --o-observer" style="--ui-icon-set__icon-size: 18px;"></div>
						<span v-html="shownGroupsCounter"></span>
						<div class="ui-icon-set --chevron-down ui-access-rights-v2-header-roles-control-chevron"></div>
					</div>
					<div class="ui-access-rights-v2-header-roles-control-expander">
						<div class="ui-access-rights-v2-header-roles-control-expander-button">
							<div
								class="ui-icon-set --collapse"
								:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COLLAPSE_ALL_SECTIONS')"
								@click="$store.dispatch('accessRights/collapseAllSections')"
							></div>
						</div>
						<div class="ui-access-rights-v2-header-roles-control-expander-button">
							<div
								class="ui-icon-set --expand-1"
								:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_EXPAND_ALL_SECTIONS')"
								@click="$store.dispatch('accessRights/expandAllSections')"
							></div>
						</div>
					</div>
				</div>
			</CellLayout>
		</ColumnLayout>
	`,
};
