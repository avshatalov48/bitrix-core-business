import { AddToChatContent } from '../../elements/add-to-chat-content/add-to-chat-content';
import { CollabInvitationService } from '../classes/collab-invitation-service';

// @vue/component
export const AddEmployeesTab = {
	name: 'AddEmployeesTab',
	components: { AddToChatContent },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		height: {
			type: Number,
			required: true,
		},
	},
	emits: ['close'],
	methods:
	{
		inviteMembers({ members })
		{
			void (new CollabInvitationService()).addEmployees({ dialogId: this.dialogId, members });
			this.$emit('close');
		},
	},
	template: `
		<div class="bx-im-add-to-collab__employees-tab-container">
			<AddToChatContent
				:dialogId="dialogId"
				:height="height"
				@inviteMembers="inviteMembers"
				@close="$emit('close')"
			/>
		</div>
	`,
};
