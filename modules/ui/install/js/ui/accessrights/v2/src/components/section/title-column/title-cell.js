import '../../../css/section/title-column/title-cell.css';
import { RichMenuItem, RichMenuItemIcon, RichMenuPopup } from 'ui.vue3.components.rich-menu';
import { ServiceLocator } from '../../../service/service-locator';
import { Hint } from '../../util/hint';
import { RowValue } from './row-value';

export const TitleCell = {
	name: 'TitleCell',
	components: { Hint, RowValue, RichMenuItem, RichMenuPopup },
	props: {
		right: {
			/** @type AccessRightItem */
			type: Object,
			required: true,
		},
	},
	inject: ['section'],
	provide(): Object {
		return {
			right: this.right,
		};
	},
	data(): Object {
		return {
			isMenuShown: false,
			isRowValueShown: false,
		};
	},
	computed: {
		RichMenuItemIcon: () => RichMenuItemIcon,
		isMinValueSet(): boolean
		{
			return this.$store.getters['accessRights/isMinValueSet'](this.section.sectionCode, this.right.id);
		},
		isMaxValueSet(): boolean
		{
			return this.$store.getters['accessRights/isMaxValueSet'](this.section.sectionCode, this.right.id);
		},
		isRowValueConfigurable(): boolean
		{
			return ServiceLocator.getValueTypeByRight(this.right)?.isRowValueConfigurable() ?? false;
		},
	},
	methods: {
		toggleGroup(): void
		{
			if (!this.right.groupHead)
			{
				return;
			}

			this.$store.dispatch('accessRights/toggleGroup', { sectionCode: this.section.sectionCode, groupId: this.right.id });
		},
		toggleMenu(): void
		{
			this.isMenuShown = !this.isMenuShown;
		},
		setMaxValuesForRight(): void
		{
			this.isRowValueShown = false;
			this.isMenuShown = false;

			this.$store.dispatch('userGroups/setMaxAccessRightValuesForRight', {
				sectionCode: this.section.sectionCode,
				rightId: this.right.id,
			});
		},
		setMinValuesForRight(): void
		{
			this.isRowValueShown = false;
			this.isMenuShown = false;

			this.$store.dispatch('userGroups/setMinAccessRightValuesForRight', {
				sectionCode: this.section.sectionCode,
				rightId: this.right.id,
			});
		},
		openRowValue(): void
		{
			this.isMenuShown = false;

			this.isRowValueShown = true;
		},
	},
	// data attributes are needed for e2e automated tests
	template: `
		<div
			class='ui-access-rights-v2-column-item-text ui-access-rights-v2-column-item-title'
			@click="toggleGroup"
			:title="right.title"
			:style="{
				cursor: right.groupHead ? 'pointer' : null,
			}"
			v-memo="[right.isGroupExpanded]"
			:data-accessrights-right-id="right.id"
		>
			<span
				v-if="right.groupHead"
				class="ui-icon-set"
				:class="{
					'--minus-in-circle': right.isGroupExpanded,
					'--plus-in-circle': !right.isGroupExpanded,
				}"
			></span>
			<span class="ui-access-rights-v2-text-ellipsis" :style="{
				'margin-left': !right.groupHead && !right.group ? '23px' : null,
			}">{{ right.title }}</span>
			<Hint v-once v-if="right.hint" :html="right.hint" />
		</div>
		<div
			ref="icon" 
			class="ui-icon-set --more ui-access-rights-v2-icon-more ui-access-rights-v2-title-column-menu" 
			@click="toggleMenu"
		>
			<RichMenuPopup
				v-if="isMenuShown"
				@close="isMenuShown = false"
				:popup-options="{bindElement: $refs.icon, width: 300}"
			>
				<RichMenuItem
					v-if="isMaxValueSet"
					:icon="RichMenuItemIcon.check"
					:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS_ROW')"
					:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MAX_ACCESS_RIGHTS_ROW_SUBTITLE')"
					@click="setMaxValuesForRight"
				/>
				<RichMenuItem
					v-if="isMinValueSet"
					:icon="RichMenuItemIcon['red-lock']"
					:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS_ROW')"
					:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SET_MIN_ACCESS_RIGHTS_ROW_SUBTITLE')"
					@click="setMinValuesForRight"
				/>
				<RichMenuItem
					v-if="isRowValueConfigurable"
					:icon="RichMenuItemIcon.settings"
					:title="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_OPEN_ROW_VALUE')"
					:subtitle="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_OPEN_ROW_VALUE_SUBTITLE')"
					@click="openRowValue"
				/>
			</RichMenuPopup>
			<RowValue v-if="isRowValueShown" @close="isRowValueShown = false"/>
		</div>
	`,
};
