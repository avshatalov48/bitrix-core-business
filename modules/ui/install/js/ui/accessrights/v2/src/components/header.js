import { Members } from './header/members';
import { RoleHeading } from './header/role-heading';
import { RolesControl } from './header/roles-control';
import { CellLayout } from './layout/cell-layout';
import { ColumnLayout } from './layout/column-layout';
import { SyncHorizontalScroll } from './util/sync-horizontal-scroll';

/**
 * A special case of Section
 */
export const Header = {
	name: 'Header',
	components: { RoleHeading, SyncHorizontalScroll, Members, RolesControl, ColumnLayout, CellLayout },
	props: {
		userGroups: {
			type: Map,
			required: true,
		},
	},
	// data attributes are needed for e2e automated tests
	template: `
		<div class="ui-access-rights-v2-section ui-access-rights-v2--head-section">
			<div class='ui-access-rights-v2-section-container'>
				<div class='ui-access-rights-v2-section-head'>
					<RolesControl :user-groups="userGroups"/>
				</div>
				<div class='ui-access-rights-v2-section-content'>
					<SyncHorizontalScroll class='ui-access-rights-v2-section-wrapper'>
						<ColumnLayout
							v-for="[groupId, group] in userGroups" 
							:key="groupId"
							:data-accessrights-user-group-id="groupId"
						>
							<CellLayout class="ui-access-rights-v2-header-role-cell">
								<RoleHeading :user-group="group"/>
								<Members :user-group="group"/>
							</CellLayout>
						</ColumnLayout>
					</SyncHorizontalScroll>
				</div>
			</div>
		</div>
	`,
};
