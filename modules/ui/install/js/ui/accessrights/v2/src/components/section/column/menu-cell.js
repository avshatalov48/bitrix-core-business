import { Runtime } from 'main.core';
import { type PopupOptions } from 'main.popup';
import { Dialog, type ItemOptions } from 'ui.entity-selector';
import { RichMenuItem, RichMenuItemIcon, RichMenuPopup } from 'ui.vue3.components.rich-menu';
import { mapGetters } from 'ui.vue3.vuex';
import { EntitySelectorContext } from '../../../integration/entity-selector/dictionary';
import { ItemsMapper } from '../../../integration/entity-selector/items-mapper';
import type { UserGroupsCollection } from '../../../store/model/user-groups-model';
import { CellLayout } from '../../layout/cell-layout';
import '../../../css/section/menu-cell.css';

export const MenuCell = {
	name: 'MenuCell',
	components: {
		CellLayout,
		RichMenuPopup,
		RichMenuItem,
	},
	inject: ['section', 'userGroup'],
	data(): Object {
		return {
			isMenuShown: false,
		};
	},
	computed: {
		RichMenuItemIcon: () => RichMenuItemIcon,
		...mapGetters({
			isMaxValueSetForAny: 'accessRights/isMaxValueSetForAny',
			isMinValueSetForAny: 'accessRights/isMinValueSetForAny',
		}),
		menuPopupOptions(): PopupOptions {
			const width = 290;

			return {
				bindElement: this.$refs.icon,
				width,
				// by default popup is positioned so that the left top angle is below the bind element.
				// we need to position it in the center of the column
				offsetLeft: -Math.floor(width / 2) + 9,
			};
		},
		shownUserGroupsWithoutCurrent(): UserGroupsCollection {
			const shown: UserGroupsCollection = this.$store.getters['userGroups/shown'];

			const shownWithoutCurrent: UserGroupsCollection = Runtime.clone(shown);
			shownWithoutCurrent.delete(this.userGroup.id);

			return shownWithoutCurrent;
		},
		applyDialogItems(): ItemOptions[] {
			return ItemsMapper.mapUserGroups(this.shownUserGroupsWithoutCurrent);
		},
	},
	methods: {
		toggleMenu(): void
		{
			this.isMenuShown = !this.isMenuShown;
		},
		showApplyDialog(): void
		{
			this.isMenuShown = false;

			const applyDialog = new Dialog({
				context: EntitySelectorContext.ROLE,
				targetNode: this.$refs.icon,
				multiple: false,
				dropdownMode: true,
				enableSearch: true,
				cacheable: false,
				items: this.applyDialogItems,
				events: {
					'Item:onSelect': (dialogEvent: BaseEvent) => {
						const { item } = dialogEvent.getData();

						this.$store.dispatch('userGroups/copySectionValues', {
							srcUserGroupId: this.userGroup.id,
							dstUserGroupId: item.getId(),
							sectionCode: this.section.sectionCode,
						});
					},
				},
			});

			applyDialog.show();
		},
		setMaxValuesInSection(): void
		{
			this.isMenuShown = false;

			this.$store.dispatch('userGroups/setMaxAccessRightValuesInSection', {
				userGroupId: this.userGroup.id,
				sectionCode: this.section.sectionCode,
			});
		},
		setMinValuesInSection(): void
		{
			this.isMenuShown = false;

			this.$store.dispatch('userGroups/setMinAccessRightValuesInSection', {
				userGroupId: this.userGroup.id,
				sectionCode: this.section.sectionCode,
			});
		},
	},
	template: `
		<CellLayout class="ui-access-rights-v2-menu-cell" style="cursor: pointer" @click="toggleMenu">
			<div
				ref="icon"
				class="ui-icon-set --more ui-access-rights-v2-icon-more"
			>
				<RichMenuPopup
					v-if="isMenuShown"
					@close="isMenuShown = false"
					:popup-options="menuPopupOptions"
				>
					<RichMenuItem
						v-if="isMaxValueSetForAny"
						:icon="RichMenuItemIcon.check"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS')"
						:subtitle="$Bitrix.Loc.getMessage(
							'JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS_SUBTITLE_SECTION',
							{
								'#SECTION#': section.sectionTitle + (section.sectionSubTitle ? (' ' + section.sectionSubTitle) : ''),
							}
						)"
						@click="setMaxValuesInSection"
					/>
					<RichMenuItem
						v-if="isMinValueSetForAny"
						:icon="RichMenuItemIcon['red-lock']"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS')"
						:subtitle="$Bitrix.Loc.getMessage(
							'JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS_SUBTITLE_SECTION',
							{
								'#SECTION#': section.sectionTitle + (section.sectionSubTitle ? (' ' + section.sectionSubTitle) : ''),
							}
						)"
						@click="setMinValuesInSection"
					/>
					<RichMenuItem
						:icon="RichMenuItemIcon.copy"
						:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_APPLY_TO_ROLE')"
						:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_APPLY_TO_ROLE_SUBTITLE')"
						@click="showApplyDialog"
					/>
				</RichMenuPopup>
			</div>
		</CellLayout>
	`,
};
