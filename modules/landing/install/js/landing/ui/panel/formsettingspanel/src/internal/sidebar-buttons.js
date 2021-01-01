import {Loc} from 'landing.loc';
import {SidebarButton} from 'landing.ui.button.sidebarbutton';

const sidebarButtons = [
	new SidebarButton({
		id: 'fields',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_FIELDS'),
		child: true,
	}),
	new SidebarButton({
		id: 'agreements',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_AGREEMENT'),
		child: true,
		important: true,
	}),
	new SidebarButton({
		id: 'crm',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_ENTITY'),
		child: true,
	}),
	new SidebarButton({
		id: 'identify',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_CLIENT_IDENTIFY'),
		child: true,
	}),
	new SidebarButton({
		id: 'button_and_header',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_HEADER_AND_BUTTON'),
		child: true,
	}),
	new SidebarButton({
		id: 'spam_protection',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_SPAM_PROTECTION'),
		child: true,
	}),
	new SidebarButton({
		id: 'fields_rules',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_FIELDS_RULES'),
		child: true,
	}),
	new SidebarButton({
		id: 'actions',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_ACTIONS'),
		child: true,
	}),
	new SidebarButton({
		id: 'default_values',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_DEFAULT_VALUES'),
		child: true,
	}),
	new SidebarButton({
		id: 'analytics',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_DEFAULT_ANALYTICS'),
		child: true,
	}),
	new SidebarButton({
		id: 'facebook',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_FACEBOOK'),
		child: true,
	}),
	new SidebarButton({
		id: 'vk',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_VK'),
		child: true,
	}),
	new SidebarButton({
		id: 'callback',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_CALLBACK'),
		child: true,
	}),
	new SidebarButton({
		id: 'embed',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_EMBED'),
		child: true,
	}),
	new SidebarButton({
		id: 'more',
		text: Loc.getMessage('LANDING_FORM_SETTINGS_MORE_BUTTON_TEXT'),
		className: 'landing-ui-button-sidebar-more',
		child: true,
	}),
	new SidebarButton({
		id: 'other',
		text: Loc.getMessage('LANDING_SIDEBAR_BUTTON_OTHER'),
		child: true,
	}),
];

export default sidebarButtons;