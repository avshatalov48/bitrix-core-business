import '../../../../../css/value/popup-header/master-switcher/all-roles-title.css';

export const AllRolesTitle = {
	name: 'AllRolesTitle',
	template: `
		<div class="ui-access-rights-v2-cell-popup-header-all-role-container">
			<div class="ui-icon-set --persons-3" style="margin-right: 4px;"></div>
			<div class="ui-access-rights-v2-cell-popup-header-all-roles-caption">{{ 
				$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ALL_ROLES')
			}}</div>
		</div>
	`,
};
