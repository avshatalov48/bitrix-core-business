import { Dom } from 'main.core';
import { mapGetters, mapState } from 'ui.vue3.vuex';
import { ServiceLocator } from '../service/service-locator';
import { Header } from './header';
import { SearchBox } from './searchbox';
import { Section } from './section';

export const Grid = {
	name: 'Grid',
	components: { Section, Header, SearchBox },
	loader: null,
	computed: {
		...mapState({
			isSaving: (state) => state.application.isSaving,
			guid: (state) => state.application.guid,
			searchContainerSelector: (state) => state.application.options.searchContainerSelector,
		}),
		...mapGetters({
			shownSections: 'accessRights/shown',
			shownUserGroups: 'userGroups/shown',
		}),
	},
	mounted()
	{
		ServiceLocator.getHint(this.guid).initOwnerDocument(this.$refs.container);
	},
	methods: {
		scrollToSection(sectionCode: string) {
			const section = this.$refs.sections.find((item) => item.code === sectionCode);
			if (section)
			{
				scrollTo({
					top: Dom.getPosition(section.$el).top - 155,
					behavior: 'smooth',
				});
			}
		},
	},
	template: `
		<Teleport v-if="searchContainerSelector" :to="searchContainerSelector">
			<SearchBox/>
		</Teleport>
		<div ref="container" class='ui-access-rights-v2' :class="{
			'ui-access-rights-v2-block': isSaving,
		}">
			<Header :user-groups="shownUserGroups"/>
			<Section
				v-for="[sectionCode, accessRightSection] in shownSections"
				:key="sectionCode"
				:code="accessRightSection.sectionCode"
				:is-expanded="accessRightSection.isExpanded"
				:title="accessRightSection.sectionTitle"
				:sub-title="accessRightSection.sectionSubTitle"
				:hint="accessRightSection.sectionHint"
				:icon="accessRightSection.sectionIcon"
				:rights="accessRightSection.rights"
				:user-groups="shownUserGroups"
				ref="sections"
			/>
		</div>
	`,
};
