export const Icon = {
	name: 'Icon',
	inject: ['section'],
	computed: {
		iconBgColor(): string {
			if (this.section.sectionIcon.bgColor.startsWith('--'))
			{
				// css variable
				return `var(${this.section.sectionIcon.bgColor})`;
			}

			// we assume its hex
			return this.section.sectionIcon.bgColor;
		},
	},
	template: `
		<div v-if="section.sectionIcon" class="ui-access-rights-v2-section-header-icon" :style="{
			backgroundColor: iconBgColor,
		}">
			<div class="ui-icon-set" :class="'--' + section.sectionIcon.type"></div>
		</div>
	`,
};
