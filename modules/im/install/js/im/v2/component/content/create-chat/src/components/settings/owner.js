import {TagSelector} from 'ui.entity-selector';

// @vue/component
export const OwnerSelector = {
	name: 'OwnerSelector',
	props:
	{
		ownerId: {
			type: Number,
			required: true
		},
	},
	emits: ['ownerChange'],
	data()
	{
		return {};
	},
	created()
	{
		this.membersSelector = new TagSelector({
			multiple: false,
			maxHeight: 33,
			placeholder: '',
			addButtonCaption: this.loc('IM_CREATE_CHAT_USER_SELECTOR_CHANGE_OWNER'),
			addButtonCaptionMore: this.loc('IM_CREATE_CHAT_USER_SELECTOR_CHANGE_OWNER'),
			showCreateButton: false,
			dialogOptions: {
				enableSearch: false,
				context: 'IM_CHAT_CREATE',
				entities: [
					{id: 'user'},
					{id: 'department'},
				],
				preselectedItems: [['user', this.ownerId]]
			},
			events: {
				onBeforeTagAdd: (event) => {
					const {tag} = event.getData();
					tag.setDeselectable(false);
				},
				onAfterTagAdd: (event) => {
					const {tag} = event.getData();
					this.$emit('ownerChange', tag.id);
				}
			}
		});
	},
	mounted()
	{
		this.membersSelector.renderTo(this.$refs['owner']);
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div class="bx-im-content-create-chat__owner" ref="owner"></div>
	`
};