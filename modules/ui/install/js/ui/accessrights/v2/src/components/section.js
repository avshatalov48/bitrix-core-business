import { computed } from 'ui.vue3';
import { Column } from './section/column';
import { ColumnList } from './section/column-list';
import { Header } from './section/header';
import { TitleColumn } from './section/title-column';
import { SyncHorizontalScroll } from './util/sync-horizontal-scroll';

export const Section = {
	name: 'Section',
	components: { Column, SyncHorizontalScroll, TitleColumn, Header, ColumnList },
	props: {
		userGroups: {
			type: Map,
			required: true,
		},
		rights: {
			type: Map,
			required: true,
		},
		code: {
			type: String,
			required: true,
		},
		isExpanded: {
			type: Boolean,
			required: true,
		},
		title: {
			type: String,
			required: true,
		},
		subTitle: {
			type: String,
		},
		hint: {
			type: String,
		},
		icon: {
			/** @type AccessRightSectionIcon */
			type: Object,
		},
	},
	provide(): Object {
		return {
			section: computed(() => {
				return {
					sectionCode: this.code,
					sectionTitle: this.title,
					sectionSubTitle: this.subTitle,
					sectionIcon: this.icon,
					sectionHint: this.hint,
					isExpanded: this.isExpanded,
					rights: this.rights,
				};
			}),
		};
	},
	// data attributes are needed for e2e automated tests
	template: `
		<div class="ui-access-rights-v2-section" :data-accessrights-section-code="code">
			<Header/>
			<div v-if="isExpanded" class='ui-access-rights-v2-section-container'>
				<div class='ui-access-rights-v2-section-head'>
					<TitleColumn :rights="rights" />
				</div>
				<ColumnList :rights="rights" :user-groups="userGroups"/>
			</div>
		</div>
	`,
};
