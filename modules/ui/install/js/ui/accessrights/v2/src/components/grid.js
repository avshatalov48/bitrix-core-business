import { Loader } from 'main.loader';
import { mapGetters, mapState } from 'ui.vue3.vuex';
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
			searchContainerSelector: (state) => state.application.options.searchContainerSelector,
		}),
		...mapGetters({
			shownSections: 'accessRights/shown',
			shownUserGroups: 'userGroups/shown',
		}),
	},
	mounted()
	{
		this.loader = new Loader({
			target: this.$refs.container,
		});
	},
	beforeUnmount()
	{
		this.loader.destroy();
	},
	watch: {
		isSaving(newValue): void {
			if (newValue)
			{
				this.loader.show();
			}
			else
			{
				this.loader.hide();
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
			/>
		</div>
	`,
};
