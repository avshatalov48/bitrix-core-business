import { type BaseEvent } from 'main.core.event';
import { Dialog, type ItemOptions } from 'ui.entity-selector';
import { RichMenuItem, RichMenuItemIcon, RichMenuPopup } from 'ui.vue3.components.rich-menu';
import { mapGetters, mapState } from 'ui.vue3.vuex';
import { EntitySelectorContext } from '../../integration/entity-selector/dictionary';
import { ItemsMapper } from '../../integration/entity-selector/items-mapper';
import { CellLayout } from '../layout/cell-layout';
import { ColumnLayout } from '../layout/column-layout';
import '../../css/header/roles-control.css';

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
		}),
		...mapGetters({
			isMaxVisibleUserGroupsSet: 'application/isMaxVisibleUserGroupsSet',
			isMaxVisibleUserGroupsReached: 'userGroups/isMaxVisibleUserGroupsReached',
		}),
		shownGroupsCounter(): string {
			return this.$Bitrix.Loc.getMessage(
				'JS_UI_ACCESSRIGHTS_V2_ROLE_COUNTER',
				{
					'#VISIBLE_ROLES#': this.userGroups.size,
					'#ALL_ROLES#': this.allUserGroups.size,
					'#GREY_START#': '<span style="opacity: var(--ui-opacity-30)">',
					'#GREY_FINISH#': '</span>',
				},
			);
		},
		copyDialogItems(): ItemOptions[] {
			return ItemsMapper.mapUserGroups(this.allUserGroups);
		},
		viewDialogItems(): ItemOptions[] {
			const result: ItemOptions[] = [];

			for (const copyDialogItem of this.copyDialogItems)
			{
				result.push({
					...copyDialogItem,
					selected: this.userGroups.has(copyDialogItem.id),
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
			this.viewDialog = new Dialog({
				context: EntitySelectorContext.ROLE,
				footer: this.isMaxVisibleUserGroupsSet ? this.$Bitrix.Loc.getMessage(
					'JS_UI_ACCESSRIGHTS_V2_ROLE_SELECTOR_MAX_VISIBLE_WARNING',
					{
						'#COUNT#': this.maxVisibleUserGroups,
					},
				) : null,
				targetNode: target,
				multiple: true,
				dropdownMode: true,
				enableSearch: true,
				cacheable: false,
				items: this.viewDialogItems,
				events: {
					'Item:onBeforeSelect': (dialogEvent: BaseEvent) => {
						if (
							this.isMaxVisibleUserGroupsSet
							&& this.viewDialog.getSelectedItems().length >= this.maxVisibleUserGroups
						)
						{
							dialogEvent.preventDefault();
						}
					},
					'Item:onSelect': (dialogEvent: BaseEvent) => {
						const { item } = dialogEvent.getData();

						this.$store.dispatch('userGroups/showUserGroup', { userGroupId: item.getId() });
					},
					'Item:onDeselect': (dialogEvent: BaseEvent) => {
						const { item } = dialogEvent.getData();

						this.$store.dispatch('userGroups/hideUserGroup', { userGroupId: item.getId() });
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
						class="ui-icon-set --more ui-access-rights-v2-icon-more"
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
						<div class="ui-icon-set --opened-eye" style="--ui-icon-set__icon-size: 15px;"></div>
						<span v-html="shownGroupsCounter"></span>
						<div class="ui-icon-set --chevron-down ui-access-rights-v2-header-roles-control-chevron"></div>
					</div>
					<div class="ui-access-rights-v2-header-roles-control-expander">
						<div
							class="ui-icon-set --collapse"
							:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COLLAPSE_ALL_SECTIONS')"
							@click="$store.dispatch('accessRights/collapseAllSections')"
						></div>
						<div 
							class="ui-icon-set --expand-1"
							:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_EXPAND_ALL_SECTIONS')"
							@click="$store.dispatch('accessRights/expandAllSections')"
						></div>
					</div>
				</div>
			</CellLayout>
		</ColumnLayout>
	`,
};
