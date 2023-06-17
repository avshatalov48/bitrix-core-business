import {Logger} from 'im.v2.lib.logger';

// @vue/component
export const OpenlineSidebar = {
	name: 'OpenlineSidebar',
	created()
	{
		Logger.warn('Sidebar: Openline Sidebar created');
	},
	template: `
		<div class="bx-im-content-openline__sidebar_container">
			<div class="bx-im-content-openline__sidebar_content">
				<div class="bx-im-content-openline__sidebar_item">Openline Right Panel</div>
				<div class="bx-im-content-openline__sidebar_item">Some specific openline info</div>
				<div class="bx-im-content-openline__sidebar_item">Some additional openline info</div>
				<div class="bx-im-content-openline__sidebar_item">And more</div>
			</div>
		</div>
	`
};