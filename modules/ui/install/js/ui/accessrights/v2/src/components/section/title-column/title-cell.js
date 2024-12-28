import { Hint } from '../../util/hint';

export const TitleCell = {
	name: 'TitleCell',
	components: { Hint },
	props: {
		right: {
			/** @type AccessRightItem */
			type: Object,
			required: true,
		},
	},
	inject: ['section'],
	methods: {
		toggleGroup(): void
		{
			if (!this.right.groupHead)
			{
				return;
			}

			this.$store.dispatch('accessRights/toggleGroup', { sectionCode: this.section.sectionCode, groupId: this.right.id });
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
	`,
};
