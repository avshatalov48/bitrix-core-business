import { Util } from 'calendar.util';
import { EventEmitter } from 'main.core.events';
import { DateTimeFormat } from 'main.date';
import { Analytics } from 'calendar.sharing.analytics';
import { Context } from './context';
import { RuleModel, RuleParams } from './rule';
import { User } from './user';
import { CalendarContext } from './calendar-context';

export type CalendarSettings = {
	weekStart: string,
	weekHolidays: string[],
	workTimeStart: string,
	workTimeEnd: string,
};

export type SettingsParams = {
	context: Context,
	linkHash: string,
	sharingUrl: string,
	userInfo: User,

	rule: RuleParams,
	calendarSettings: CalendarSettings,
	collapsed: boolean,
	sortJointLinksByFrequentUse: boolean,
	calendarContext: CalendarContext | null,
};

export class SettingsModel
{
	#params: SettingsParams;
	#rule: RuleModel;
	#memberIds: number[];

	constructor(params: SettingsParams)
	{
		this.#params = params;

		const { rule, calendarSettings } = params;

		this.#rule = this.#createRuleModel(rule, calendarSettings);
	}

	#createRuleModel(rule: RuleParams, calendarSettings: CalendarSettings)
	{
		const { weekStart, weekHolidays, workTimeStart, workTimeEnd } = calendarSettings;

		return new RuleModel({
			rule,
			calendarSettings: {
				weekStart: Util.getIndByWeekDay(weekStart),
				workTimeStart: this.getMinutesFromTime(workTimeStart),
				workTimeEnd: this.getMinutesFromTime(workTimeEnd),
				workDays: this.getWorkingDays(weekHolidays),
			},
		});
	}

	getMinutesFromTime(time: string): number
	{
		const dateString = new Date().toDateString();
		const date = new Date(`${dateString} ${`${time}`.replace('.', ':')}:00`);

		const shortTimeFormat = DateTimeFormat.getFormat('SHORT_TIME_FORMAT');
		const parsedTime = Util.parseTime(DateTimeFormat.format(shortTimeFormat, date / 1000));

		return parsedTime.h * 60 + parsedTime.m;
	}

	getWorkingDays(weekHolidays): number[]
	{
		const weekHolidaysInt = new Set(weekHolidays.map((day) => Util.getIndByWeekDay(day)));

		return [0, 1, 2, 3, 4, 5, 6].filter((day) => !weekHolidaysInt.has(day));
	}

	isDefaultRule(): boolean
	{
		return !this.isDifferentFrom(this.getRule().getDefaultRule());
	}

	isDifferentFrom(anotherRule: RuleModel): boolean
	{
		return this.getChanges(anotherRule, this.getRule()).length > 0;
	}

	getChanges(rule: RuleModel): string[]
	{
		const currentRule = this.getRule().toArray();
		const anotherRule = (rule ?? this.getRule().getDefaultRule()).toArray();

		const sizeChanged = currentRule.slotSize !== anotherRule.slotSize;
		const daysChanged = JSON.stringify(currentRule.ranges) !== JSON.stringify(anotherRule.ranges);

		const changes = [];

		if (daysChanged)
		{
			changes.push(Analytics.ruleChanges.custom_days);
		}

		if (sizeChanged)
		{
			changes.push(Analytics.ruleChanges.custom_length);
		}

		return changes;
	}

	sortRanges(): void
	{
		this.getRule().sortRanges();
	}

	getRule(): RuleModel
	{
		return this.#rule;
	}

	getUserInfo(): User
	{
		return this.#params.userInfo;
	}

	getContext(): Context
	{
		return this.#params.context;
	}

	getLinkHash(): string
	{
		return this.#params.linkHash;
	}

	getSharingUrl(): string
	{
		return this.#params.sharingUrl;
	}

	isCollapsed(): boolean
	{
		return this.#params.collapsed;
	}

	sortJointLinksByFrequentUse(): boolean
	{
		return this.#params.sortJointLinksByFrequentUse;
	}

	getCalendarContext(): CalendarContext | null
	{
		return this.#params.calendarContext;
	}

	changeSortJointLinksByFrequentUse()
	{
		this.#params.sortJointLinksByFrequentUse = !this.#params.sortJointLinksByFrequentUse;
		this.#updateSortByFrequentUse();
	}

	setMemberIds(memberIds: number[]): void
	{
		this.#memberIds = memberIds;
	}

	getMemberIds(): number[]
	{
		return this.#memberIds;
	}

	async saveJointLink(): Promise
	{
		const action = this.#params.calendarContext?.sharingObjectType === 'group'
			? 'calendar.api.sharinggroupajax.generateJointSharingLink'
			: 'calendar.api.sharingajax.generateUserJointSharingLink'
		;
		const response = await BX.ajax.runAction(action, {
			data: {
				memberIds: this.getMemberIds(),
				groupId: this.#params.calendarContext?.sharingObjectId,
			},
		});

		return response.data;
	}

	save(): Promise
	{
		if (!this.isDifferentFrom(this.#createRuleModel(this.#params.rule, this.#params.calendarSettings)))
		{
			return null;
		}

		const changes = this.getChanges();
		Analytics.sendRuleUpdated(this.getContext(), changes);

		const newRule = this.getRule().toArray();

		return new Promise((resolve, reject) => {
			BX.ajax.runAction('calendar.api.sharingajax.saveLinkRule', {
				data: {
					linkHash: this.getLinkHash(),
					ruleArray: newRule,
				},
			}).then(() => {
				EventEmitter.emit('CalendarSharing:RuleUpdated');
				this.#params.rule = newRule;
				resolve();
			}, (error) => {
				// eslint-disable-next-line no-console
				console.error(error);
				reject();
			});
		});
	}

	increaseFrequentUse(): void
	{
		void BX.ajax.runAction('calendar.api.sharingajax.increaseFrequentUse', {
			data: {
				hash: this.getLinkHash(),
			},
		});
	}

	updateCollapsed(isCollapsed: boolean): void
	{
		void BX.ajax.runAction('calendar.api.sharingajax.updateSharingSettingsCollapsed', {
			data: {
				collapsed: isCollapsed ? 'Y' : 'N',
			},
		});
	}

	#updateSortByFrequentUse(): void
	{
		BX.ajax.runAction('calendar.api.sharingajax.setSortJointLinksByFrequentUse', {
			data: {
				sortByFrequentUse: this.#params.sortJointLinksByFrequentUse ? 'Y' : 'N',
			},
		});
	}
}
