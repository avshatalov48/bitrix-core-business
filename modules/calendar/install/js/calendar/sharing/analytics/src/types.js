export type Context = 'crm' | 'calendar';

export type LinkType = 'solo' | 'multiple';

export type LinkCreateMethod = 'crm_copy' | 'calendar_copy_main' | 'calendar_copy_list';

export type RuleChange = 'custom_days' | 'custom_length';

export type Params = {
	peopleCount: number,
	ruleChanges: RuleChange[],
};