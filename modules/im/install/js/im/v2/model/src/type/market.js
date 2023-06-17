type Context = 'user' | 'chat' | 'lines' | 'crm' | 'all';

type ApplicationOptions = {
	context?: Context[],
	width?: number,
	height?: number,
	color?: string,
	iconName?: string
}

type LoadConfiguration = {
	ID: number,
	PLACEMENT: string,
	PLACEMENT_ID: number,
}

export type MarketApplication = {
	id: string,
	title: string,
	options: ApplicationOptions,
	placement: string,
	order: number,
	loadConfiguration: LoadConfiguration,
}
