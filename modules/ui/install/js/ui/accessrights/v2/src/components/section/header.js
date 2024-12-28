import { Hint } from '../util/hint';
import { Icon } from './icon';
import '../../css/section/header.css';

export const Header = {
	name: 'Header',
	components: { Hint, Icon },
	inject: ['section'],
	methods: {
		toggleSection(): void {
			this.$store.dispatch('accessRights/toggleSection', { sectionCode: this.section.sectionCode });
		},
	},
	template: `
		<div
			@click="toggleSection"
			class='ui-access-rights-v2-section-header'
			:class="{
				'--expanded': section.isExpanded,
			}" 
			v-memo="[section.isExpanded]"
		>
			<div class="ui-access-rights-v2-section-header-expander">
				<div class='ui-icon-set' :class="{
					'--chevron-up': section.isExpanded,
					'--chevron-down': !section.isExpanded,
				}"
				></div>
			</div>
			<Icon/>
			<span 
				class="ui-access-rights-v2-text-ellipsis ui-access-rights-v2-section-title"
				:title="section.sectionTitle"
			>{{ section.sectionTitle }}</span>
			<span
				v-if="section.sectionSubTitle"
				class="ui-access-rights-v2-text-ellipsis ui-access-rights-v2-section-subtitle"
				:title="section.sectionSubTitle"
			>
				{{ section.sectionSubTitle }}
			</span>
			<Hint v-if="section.sectionHint" :html="section.sectionHint"/>
		</div>
	`,
};
