import './css/skeleton.css';

// @vue/component
export const SidebarSkeleton = {
	name: 'SidebarSkeleton',
	template: `
		<div class="bx-im-sidebar-skeleton__container">
			<div class="bx-im-sidebar-skeleton__block">
				<div class="bx-im-sidebar-skeleton__avatar"></div>
				<div class="bx-im-sidebar-skeleton__invite-button"></div>
				<div class="bx-im-sidebar-skeleton__settings"></div>
			</div>
			<div class="bx-im-sidebar-skeleton__block">
				<div class="bx-im-sidebar-skeleton__info"></div>
			</div>
			<div class="bx-im-sidebar-skeleton__block">
				<div class="bx-im-sidebar-skeleton__files"></div>
			</div>
			<div class="bx-im-sidebar-skeleton__block">
				<div class="bx-im-sidebar-skeleton__tasks"></div>
			</div>
		</div>
	`,
};
