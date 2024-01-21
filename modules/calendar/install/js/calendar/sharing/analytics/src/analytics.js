import { sendData } from 'ui.analytics';
import { Context, LinkType, LinkCreateMethod, Params, RuleChange } from './types';

export class Analytics
{
	static tool = 'calendar';
	static category = 'slots';

	static contexts: Map<string, Context> = {
		calendar: 'calendar',
		crm: 'crm',
	};

	static linkTypes: Map<string, LinkType> = {
		solo: 'solo',
		multiple: 'multiple',
	};

	static events = {
		form_open: 'form_open',
		setup: 'setup',
		adding_people: 'adding_people',
		link_created: 'link_created',
	};

	static linkCreateMethods: Map<string, LinkCreateMethod> = {
		crm_send: 'crm_send',
		crm_copy: 'crm_copy',
		calendar_copy_main: 'calendar_copy_main',
		calendar_copy_list: 'calendar_copy_list',
	};

	static ruleChanges: Map<string, RuleChange> = {
		custom_days: 'custom_days',
		custom_length: 'custom_length',
	};

	static sendPopupOpened(context: Context): void
	{
		this.sendAnalytics(Analytics.events.form_open, {
			c_section: context,
		});
	}

	static sendRuleUpdated(context: Context, changes: RuleChange[]): void
	{
		for (const type of changes)
		{
			this.sendAnalytics(Analytics.events.setup, {
				type,
				c_section: context,
			});
		}
	}

	static sendMembersAdded(context: Context, peopleCount: number): void
	{
		this.sendAnalytics(Analytics.events.adding_people, {
			c_section: context,
			p1: `peopleCount_${peopleCount}`,
		});
	}

	static sendLinkCopied(context: Context, type: LinkType, params: Params): void
	{
		let method = Analytics.linkCreateMethods.calendar_copy_main;
		if (context === Analytics.contexts.crm)
		{
			method = Analytics.linkCreateMethods.crm_copy;
		}

		this.sendLinkCreated(context, type, method, params);
	}

	static sendLinkCopiedList(context: Context, params: Params): void
	{
		const method = Analytics.linkCreateMethods.calendar_copy_list;

		this.sendLinkCreated(context, Analytics.linkTypes.multiple, method, params);
	}

	static sendLinkCreated(context: Context, type: LinkType, method: LinkCreateMethod, params: Params): void
	{
		const ruleChanges = {
			customDays: params.ruleChanges.includes(Analytics.ruleChanges.custom_days) ? 'Y' : 'N',
			customLength: params.ruleChanges.includes(Analytics.ruleChanges.custom_length) ? 'Y' : 'N',
		};

		this.sendAnalytics(Analytics.events.link_created, {
			type,
			c_section: context,
			c_element: method,
			p1: `peopleCount_${params.peopleCount}`,
			p2: `customDays_${ruleChanges.customDays}`,
			p3: `customLength_${ruleChanges.customLength}`,
		});
	}

	/**
	 * @private
	 */
	static sendAnalytics(event: string, params: {
		c_section: string,
		c_element: string,
		type: string,
		p1: string,
		p2: string,
		p3: string,
	}): void
	{
		sendData({
			tool: Analytics.tool,
			category: Analytics.category,
			event,
			...params,
		});
	}
}