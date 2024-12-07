export const EnableFeatures = Object.freeze({
	copilot: 'copilot',
	newsLine: 'news',
	chatCalls: 'instant_messenger',
	calendar: 'calendar',
	documents: 'docs',
	mail: 'mail',
	groups: 'workgroups',
	tasks: 'tasks',
	crm: 'crm',
	marketing: 'marketing',
	automation: 'automation',
	warehouseAccounting: 'inventory_management',
	sign: 'sign',
	scrum: 'scrum',
	invoices: 'invoices',
	saleshub: 'saleshub',
	websitesStores: 'sites',
	checkIn: 'checkIn',
	checkInGeo: 'checkInGeo',
});

export const UpdateFeatures = Object.freeze({
	collaborativeDocumentEditing: 'limit_office_no_document',
	leadsCRM: 'limit_crm_lead_unlimited',
	mailBoxNumber: 'limit_contact_center_mail_box_number',
	enterpriseAdmin: 'info_enterprise_admin',
	loginHistory: 'limit_office_login_history',
	crmHistory: 'limit_crm_history_view',
	tasksRobots: 'limit_tasks_robots',
	crmAnalytics: 'limit_crm_analytics_max_number',
	crmInvoices: 'limit_crm_free_invoices',
});

export type SupervisorButtonParams = {
	text: string,
	callback: (...rest: number[]) => void,
}

export type SupervisorComponentParams = {
	title: string,
	description: string,
	detailButton: SupervisorButtonParams,
	infoButton: SupervisorButtonParams,
};
