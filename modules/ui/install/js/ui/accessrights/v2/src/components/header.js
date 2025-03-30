import { mapGetters, mapState } from 'ui.vue3.vuex';
import { Members } from './header/members';
import { RoleHeading } from './header/role-heading';
import { RolesControl } from './header/roles-control';
import { CellLayout } from './layout/cell-layout';
import { ColumnLayout } from './layout/column-layout';
import { SyncHorizontalScroll } from './util/sync-horizontal-scroll';
import { hint } from 'ui.vue3.directives.hint';
import 'ui.buttons';

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
	directives: {
		hint,
	},
	computed: {
		...mapState({
			maxVisibleUserGroups: (state) => state.application.options.maxVisibleUserGroups,
		}),
		...mapGetters({
			isMaxVisibleUserGroupsReached: 'userGroups/isMaxVisibleUserGroupsReached',
		}),
	},
	methods: {
		onCreateNewRoleClick(): void
		{
			if (this.isMaxVisibleUserGroupsReached)
			{
				return;
			}

			this.$store.dispatch('userGroups/addUserGroup');
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
						<ColumnLayout>
							<div class="ui-access-rights-v2-header-role-add">
								<button class="ui-btn ui-btn-light-border ui-btn-round ui-btn-disabled"
										v-if="isMaxVisibleUserGroupsReached"
										v-hint="$Bitrix.Loc.getMessage(
									 'JS_UI_ACCESSRIGHTS_V2_ROLE_ADDING_DISABLED', 
									 {'#COUNT#': maxVisibleUserGroups,})"
								>
									<div class="ui-icon-set --plus-20"/>
								</button>
								<button class="ui-btn ui-btn-light-border ui-btn-round"
										v-else
										@click="onCreateNewRoleClick"
								>
									<div class="ui-icon-set --plus-20"/>
								</button>
							</div>
						</ColumnLayout>
					</SyncHorizontalScroll>
				</div>
			</div>
		</div>
	`,
};
