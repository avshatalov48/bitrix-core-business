import { Stubs, GroupData, States } from 'ui.entity-catalog';
import { mapState } from 'ui.vue3.pinia';

export const EmptyGroupStub = {
	name: 'bizproc-robot-selector-empty-group-stub',
	components: {
		EmptyContent: Stubs.EmptyContent,
	},
	props: {
		group: {
			type: GroupData,
			required: true,
		},
	},
	computed: {
		isRecentGroup()
		{
			return this.currentGroup?.id === 'recent';
		},
		...mapState(States.useGlobalState, ['currentGroup']),
	},
	template: `
		<EmptyContent>
			<div v-if="isRecentGroup">
				<b>{{$Bitrix.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_EMPTY_RECENT_GROUP_STUB_TITLE')}}</b><br/>
				{{$Bitrix.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_EMPTY_RECENT_GROUP_STUB_TEXT')}}
			</div>
			<div v-else>
				{{$Bitrix.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_EMPTY_GROUP_STUB_TITLE')}}
			</div>
		</EmptyContent>
	`,
};