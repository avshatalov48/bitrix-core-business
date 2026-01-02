import { BaseEvent } from 'main.core.events';
import { type ItemId } from 'ui.entity-selector';
import { mapState } from 'ui.vue3.vuex';
import { type SelectorService } from '../../../service/selector-service';
import { ServiceLocator } from '../../../service/service-locator';
import type { Member } from '../../../store/model/user-groups-model';

export const Selector = {
	name: 'Selector',
	emits: ['close'],
	props: {
		userGroup: {
			/** @type UserGroup */
			type: Object,
			required: true,
		},
		bindNode: {
			type: HTMLElement,
			required: true,
		},
	},
	computed: {
		selectedItems(): ItemId[] {
			const result = [];
			for (const accessCode of this.userGroup.members.keys())
			{
				result.push(
					this.getSelectorService().getItemIdByAccessCode(accessCode),
				);
			}

			return result;
		},
		...mapState({
			options: (state) => state.application.options,
			memberOptions: (state) => state.application.options.additionalMembersParams,
		}),
	},
	mounted()
	{
		this.getSelectorService()
			.createDialog({
				targetNode: this.bindNode,
				preselectedItems: this.selectedItems,
				events: {
					onHide: this.onHide,
				},
			})
			.show();
	},
	methods: {
		onHide(event: BaseEvent): void {
			const dialog: Dialog = event.getTarget();
			const members = [];
			dialog.selectedItems.forEach((item) => {
				members.push(this.getSelectorService().getMemberByItem(item));
			});

			this.$store.dispatch('userGroups/updateMembersForUserGroup', {
				userGroupId: this.userGroup.id,
				members,
			});

			this.$emit('close');
		},
		getMemberFromEvent(event: BaseEvent): ?Member {
			const { item } = event.getData();

			return this.getSelectorService().getMemberByItem(item);
		},
		getSelectorService(): SelectorService
		{
			return ServiceLocator.getSelectorService(this.memberOptions);
		},
	},
	// just a template stub
	template: '<div hidden></div>',
};
