// @vue/component
export const SpaceListAddButton = {
	methods: {
		loc(message: string): string
		{
			return this.$bitrix.Loc.getMessage(message);
		},
	},
	template: `
		<div class="sn-spaces__list-item --add-btn">
			<div class="sn-spaces__list-item_icon">
			</div>
			<div class="sn-spaces__list-item_info">
				<div class="sn-spaces__list-item_title">
					{{loc('SOCIALNETWORK_SPACES_LIST_ADD_SPACE_ITEM_TITLE')}}
				</div>
				<div class="sn-spaces__list-item_description">
					{{loc('SOCIALNETWORK_SPACES_LIST_ADD_SPACE_ITEM_DESCRIPTION')}}
				</div>
			</div>
		</div>
	`,
};
