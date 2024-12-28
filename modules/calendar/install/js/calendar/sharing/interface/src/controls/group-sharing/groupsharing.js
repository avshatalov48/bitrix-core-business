import DialogNew from '../dialog-new';
import { EventEmitter } from 'main.core.events';
import GroupSharingButton from '../groupsharingbutton';

export default class GroupSharing extends GroupSharingButton
{
	constructor(options = {})
	{
		super(options);

		this.bindElement = options.bindElement;
		this.calendarSettings = options.calendarSettings;
		this.context = options.context;

		if (options.sharingConfig)
		{
			this.sharingConfig = options.sharingConfig;
			this.sharingUrl = this.sharingConfig?.url || null;
			this.linkHash = this.sharingConfig?.hash || null;
			this.sharingRule = this.sharingConfig?.rule || null;
		}
	}

	/**
	 * @override
	 */
	openDialog()
	{
		if (!this.newDialog)
		{
			this.newDialog = new DialogNew({
				bindElement: this.bindElement,
				sharingUrl: this.sharingUrl,
				linkHash: this.linkHash,
				sharingRule: this.sharingRule,
				context: this.context,
				calendarSettings: {
					weekHolidays: this.calendarSettings.week_holidays,
					weekStart: this.calendarSettings.week_start,
					workTimeStart: this.calendarSettings.work_time_start,
					workTimeEnd: this.calendarSettings.work_time_end,
				},
				userInfo: this.userInfo,
				settingsCollapsed: this.sharingSettingsCollapsed,
				sortJointLinksByFrequentUse: this.sortJointLinksByFrequentUse,
				calendarContext: this.calendarContext,
			});
		}

		if (!this.newDialog.isShown())
		{
			this.newDialog.show();
		}
	}

	/**
	 * @override
	 */
	enableSharing()
	{
		const event = 'Calendar.Sharing.copyLinkButton:onSharingEnabled';
		const action = 'calendar.api.sharinggroupajax.enableSharing';
		const data = { groupId: this.calendarContext.sharingObjectId };

		BX.ajax.runAction(action, { data })
			.then((response) => {
				EventEmitter.emit(
					event,
					{
						isChecked: true,
						url: response.data.url,
					},
				);
			})
		;
	}
}
