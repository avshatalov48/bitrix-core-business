import { Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Util } from 'calendar.util';
import DialogNew from './dialog-new';
import SharingButton from './sharingbutton';

export default class GroupSharingButton extends SharingButton
{
	constructor(options = {})
	{
		super(options);

		this.calendarContext = options.calendarContext;
	}

	/**
	 * @override
	 */
	openDialog()
	{
		this.pulsar?.close();
		Dom.remove(this.counterNode);

		if (!this.newDialog)
		{
			this.newDialog = new DialogNew({
				bindElement: this.button.getContainer(),
				sharingUrl: this.sharingUrl,
				linkHash: this.linkHash,
				sharingRule: this.sharingRule,
				context: 'calendar',
				calendarSettings: {
					weekHolidays: Util.config.week_holidays,
					weekStart: Util.config.week_start,
					workTimeStart: Util.config.work_time_start,
					workTimeEnd: Util.config.work_time_end,
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

		BX.ajax
			.runAction(
				'calendar.api.sharinggroupajax.enableSharing',
				{ data: { groupId: this.calendarContext.sharingObjectId } },
			)
			.then((response) => {
				this.sharingUrl = response.data.url;
				this.linkHash = response.data.hash;
				this.sharingRule = response.data.rule;
				this.openDialog();

				EventEmitter.emit(
					event,
					{
						isChecked: this.switcher.isChecked(),
						url: response.data.url,
					},
				);
			})
			.catch(() => {
				this.switcher.toggle();
			})
		;
	}

	/**
	 * @override
	 */
	disableSharing()
	{
		const event = 'Calendar.Sharing.copyLinkButton:onSharingDisabled';
		this.warningPopup.close();

		BX.ajax
			.runAction(
				'calendar.api.sharinggroupajax.disableSharing',
				{ data: { groupId: this.calendarContext.sharingObjectId } },
			)
			.then(() => {
				this.sharingUrl = null;
				if (this.newDialog)
				{
					this.newDialog.destroy();
					this.newDialog = null;
				}
				EventEmitter.emit(event, { isChecked: this.switcher.isChecked() });
			})
			.catch(() => {
				this.switcher.toggle();
			})
		;
	}
}
