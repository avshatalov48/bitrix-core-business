import { Icon as SectionIcon } from '../../icon';
import '../../../../css/value/popup-header/locator.css';

export const Locator = {
	name: 'Locator',
	components: { SectionIcon },
	props: {
		maxWidth: {
			type: Number,
			// same as value popup width
			default: 430,
		},
	},
	inject: ['section', 'right'],
	computed: {
		rightOrGroupTitle(): string {
			if (!this.right.group)
			{
				return this.right.title;
			}

			const groupHead = this.section.rights.get(this.right.group);

			return groupHead?.title;
		},
	},
	template: `
		<div class="ui-access-rights-v2-cell-popup-header-locator" :style="{
			maxWidth: maxWidth + 'px',
		}">
			<SectionIcon/>
			<span
				class="ui-access-rights-v2-text-ellipsis"
				:title="section.sectionTitle"
			>{{ section.sectionTitle }}</span>
			<span
				v-if="section.sectionSubTitle" 
				class="ui-access-rights-v2-text-ellipsis"
				:title="section.sectionSubTitle"
				style="margin-left: 5px; color: var(--ui-color-palette-gray-70);"
			>{{ section.sectionSubTitle }}</span>
			<div class="ui-icon-set --chevron-right ui-access-rights-v2-cell-popup-header-chevron"></div>
			<template v-if="rightOrGroupTitle !== right.title">
				<span class="ui-access-rights-v2-text-ellipsis" :title="right.title">{{ right.title }}</span>
				<div class="ui-icon-set --chevron-right ui-access-rights-v2-cell-popup-header-chevron"></div>
			</template>
			<span class="ui-access-rights-v2-text-ellipsis" :title="rightOrGroupTitle">{{ rightOrGroupTitle }}</span>
		</div>
	`,
};
