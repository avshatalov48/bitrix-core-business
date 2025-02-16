import '../../../../../css/value/popup-header/master-switcher/single-role-title.css';

export const SingleRoleTitle = {
	name: 'SingleRoleTitle',
	props: {
		userGroupTitle: {
			type: String,
			required: true,
		},
	},
	template: `
		<div class="ui-access-rights-v2-cell-popup-header-role-container">
			<div>
				<div class="ui-access-rights-v2-cell-popup-header-role-caption">
					{{ $Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE') }}
				</div>
				<div
					class="ui-access-rights-v2-cell-popup-header-role-title ui-access-rights-v2-text-ellipsis"
					:title="userGroupTitle"
				>
					{{ userGroupTitle }}
				</div>
			</div>
		</div>
	`,
};
