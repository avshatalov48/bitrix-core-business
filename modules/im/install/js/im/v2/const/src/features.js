export const EnableFeatures = Object.freeze({
	copilot: 'copilot',
	newsLine: 'newsLine',
	chatCalls: 'chatCalls',
	calendar: 'calendar',
	documents: 'documents',
	mail: 'mail',
	groups: 'groups',
	tasks: 'tasks',
	crm: 'crm',
	marketing: 'marketing',
	automation: 'automation',
	warehouseAccounting: 'warehouseAccounting',
	sign: 'sign',
	websitesStores: 'websitesStores',
});

export const UpdateFeatures = Object.freeze({
	tariff: 'tariff',
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
