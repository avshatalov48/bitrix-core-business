import type { MemberCollection } from '../../store/model/user-groups-model';
import { Selector } from './members/selector';
import { SingleMember } from './members/single-member';

const MAX_SHOWN_MEMBERS = 5;

export const Members = {
	name: 'Members',
	components: { SingleMember, Selector },
	props: {
		userGroup: {
			/** @type UserGroup */
			type: Object,
			required: true,
		},
	},
	data(): Object {
		return {
			isSelectorShown: false,
			isSelectedMembersPopupShown: false,
		};
	},
	popup: null,
	computed: {
		shownMembers(): MemberCollection
		{
			if (this.userGroup.members.size <= MAX_SHOWN_MEMBERS)
			{
				return this.userGroup.members;
			}

			const shownKeyValuePairs = [...this.userGroup.members].slice(0, MAX_SHOWN_MEMBERS);

			return new Map(shownKeyValuePairs);
		},
		notShownMembersCount(): number
		{
			if (this.userGroup.members.size > MAX_SHOWN_MEMBERS)
			{
				return this.userGroup.members.size - MAX_SHOWN_MEMBERS;
			}

			return 0;
		},
		bindNode(): HTMLElement
		{
			return this.$refs.container;
		},
	},
	template: `
		<div ref="container" class="ui-access-rights-v2-members-container"  @click="isSelectorShown = true">
			<div v-if="userGroup.members.size > 0" class='ui-access-rights-v2-members'>
				<SingleMember v-for="[accessCode, member] in shownMembers" :key="accessCode" :member="member"/>
				<span v-if="notShownMembersCount > 0" class="ui-access-rights-v2-members-more">
					+ {{ notShownMembersCount }}
				</span>
			</div>
			<div
				class='ui-access-rights-v2-members-item ui-access-rights-v2-members-item-add'
				:class="{
					'--show-always': userGroup.members.size <= 0,
					'--has-siblings': userGroup.members.size > 0,
				}"
			>
				<div class="ui-icon-set --plus-30"></div>
			</div>
			<Selector
				v-if="isSelectorShown"
				:user-group="userGroup"
				:bind-node="bindNode"
				@close="isSelectorShown = false"
			/>
		</div>
	`,
};
