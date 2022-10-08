// @flow
'use strict';

import { Loc, Tag } from 'main.core';
import SyncWizard from './syncwizard';
import SyncStageUnit from './syncstageunit';

export default class Office365SyncWizard extends SyncWizard
{
	TYPE = 'office365';
	SLIDER_NAME = 'calendar:sync-wizard-office365';
	STAGE_1_CODE = 'office365-to-b24';
	STAGE_2_CODE = 'sections_sync_finished';
	STAGE_3_CODE = 'events_sync_finished';

	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Sync.Interface.Office365SyncWizard');
		this.setAccountName(Loc.getMessage('CALENDAR_TITLE_OFFICE365'));
		this.setSyncStages();
		this.logoIconClass = '--office365';
	}

	getHelpLinkWrapper()
	{
		return '';
	}

	getFinalCheckWrapper()
	{
		this.finalCheckWrapper = Tag.render`
			<div style="display: none;">
				<div class="calendar-sync__content-block --space-bottom">
					<div class="calendar-sync__balloon --progress">
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-progress">${Loc.getMessage('CAL_SYNC_LETS_CHECK')}</div>
						<div class="calendar-sync__content-text calendar-sync__content-subtitle --show-for-progress">${Loc.getMessage('CAL_SYNC_CREATE_EVENT_OFFICE365')}</div>
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-done">${Loc.getMessage('CAL_SYNC_NEW_EVENT_ADDED_FROM_OFFICE365')}</div>
						<div class="calendar-sync__content-text calendar-sync__content-subtitle --show-for-done">${Loc.getMessage('CAL_SYNC_NEW_EVENT_YOULL_SEE')}</div>
						<div class="calendar-sync__balloon--icon"></div>
					</div>
				</div>
				${this.getSkeletonWrapper()}
				${this.getNewEventCardWrapper()}
			</div>
		`;
		return this.finalCheckWrapper;
	}

	setSyncStages()
	{
		this.syncStagesList = [
			new SyncStageUnit({
				name: this.STAGE_1_CODE,
				title: Loc.getMessage('CAL_SYNC_STAGE_OFFICE365_1')
			}),
			new SyncStageUnit({
				name: this.STAGE_2_CODE,
				title: Loc.getMessage('CAL_SYNC_STAGE_OFFICE365_2')
			}),
			new SyncStageUnit({
				name: this.STAGE_3_CODE,
				title: Loc.getMessage('CAL_SYNC_STAGE_OFFICE365_3')
			})
		];
	}

	updateState(stateData)
	{
		super.updateState(stateData);

		this.getSyncStages().forEach(stage => {
			if (stateData.stage === 'connection_created'
				&& stage.name === this.STAGE_1_CODE)
			{
				stage.setDone();
			}
			else if (stateData.stage === this.STAGE_2_CODE
			 && (stage.name === this.STAGE_1_CODE || stage.name === this.STAGE_2_CODE)
			)
			{
				stage.setDone();
			}
			else if (stateData.stage === this.STAGE_3_CODE)
			{
				stage.setDone();
				this.setActiveStatusFinished();
				this.showButtonWrapper();
				this.showInfoStatusWrapper();
				this.showConfetti();

				this.emit('onConnectionCreated');
			}
		});
	}

	getSkeletonTitle()
	{
		return Loc.getMessage('CAL_SYNC_NEW_EVENT_OFFICE365_TITLE');
	}
}
