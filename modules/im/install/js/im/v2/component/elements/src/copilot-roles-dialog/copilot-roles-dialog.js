import { RolesDialog, RolesDialogEvents } from 'ai.roles-dialog';

// @vue/component
export const CopilotRolesDialog = {
	name: 'CopilotRolesDialog',
	props:
	{
		title: {
			type: String,
			default: '',
		},
	},
	emits: ['selectRole', 'close'],
	computed:
	{
		titleText(): string
		{
			return this.title || this.loc('IM_ELEMENTS_COPILOT_ROLES_DIALOG_DEFAULT_TITLE');
		},
	},
	created()
	{
		this.roleDialog = new RolesDialog({
			moduleId: 'im',
			contextId: 'im-copilot-create-chat',
			title: this.titleText,
		});

		this.subscribeToEvents();
	},
	mounted()
	{
		void this.roleDialog.show();
	},
	beforeUnmount()
	{
		if (!this.roleDialog)
		{
			return;
		}

		this.roleDialog.hide();
		this.unsubscribeFromEvents();
	},
	methods:
	{
		subscribeToEvents()
		{
			this.roleDialog.subscribe(RolesDialogEvents.SELECT_ROLE, this.onSelectRole);
			this.roleDialog.subscribe(RolesDialogEvents.HIDE, this.onHide);
		},
		unsubscribeFromEvents()
		{
			this.roleDialog.unsubscribe(RolesDialogEvents.SELECT_ROLE, this.onSelectRole);
			this.roleDialog.unsubscribe(RolesDialogEvents.HIDE, this.onHide);
		},
		onSelectRole(event)
		{
			const { role } = event.getData();
			if (!role)
			{
				return;
			}
			this.$emit('selectRole', role);
		},
		onHide()
		{
			this.$emit('close');
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: '<template></template>',
};
