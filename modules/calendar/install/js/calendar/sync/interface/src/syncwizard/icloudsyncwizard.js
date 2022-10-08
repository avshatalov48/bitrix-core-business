// @flow
'use strict';

import { Loc, Tag } from 'main.core';
import SyncWizard from './syncwizard';
import SyncStageUnit from './syncstageunit';

export default class IcloudSyncWizard extends SyncWizard
{
	TYPE = 'icloud';
	SLIDER_NAME = 'calendar:sync-wizard-icloud';
	STAGE_1_CODE = 'icloud-to-b24';
	STAGE_2_CODE = 'b24-events-to-icloud';
	STAGE_3_CODE = 'b24-to-icloud';
	
	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Sync.Interface.IcloudSyncWizard');
		this.setAccountName(Loc.getMessage('CALENDAR_TITLE_ICLOUD'));
		this.setSyncStages();
		this.logoIconClass = '--icloud';
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
						<div class="calendar-sync__content-text calendar-sync__content-subtitle --show-for-progress">${Loc.getMessage('CAL_SYNC_CREATE_EVENT_ICLOUD')}</div>
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-done">${Loc.getMessage('CAL_SYNC_NEW_EVENT_ADDED_FROM_ICLOUD')}</div>
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
				title: Loc.getMessage('CAL_SYNC_STAGE_ICLOUD_1')
			}),
			new SyncStageUnit({
				name: this.STAGE_2_CODE,
				title: Loc.getMessage('CAL_SYNC_STAGE_ICLOUD_2')
			}),
			new SyncStageUnit({
				name: this.STAGE_3_CODE,
				title: Loc.getMessage('CAL_SYNC_STAGE_ICLOUD_3')
			})
		];
	}

	updateState(stateData)
	{
		super.updateState(stateData);

		this.getSyncStages().forEach(stage => {
			if (
				stateData.stage === 'connection_created'
				&& stage.name === this.STAGE_1_CODE
			)
			{
				stage.setDone();
			}
			else if (
				stateData.stage === 'import_finished'
			    && (stage.name === this.STAGE_1_CODE || stage.name === this.STAGE_2_CODE)
			)
			{
				stage.setDone();
			}
			else if (stateData.stage === 'export_finished')
			{
				stage.setDone();
				if (stage.name === this.STAGE_3_CODE)
				{
					this.setActiveStatusFinished();
					this.showButtonWrapper();
					this.showInfoStatusWrapper();
					this.showConfetti();

					this.emit('onConnectionCreated');
				}
			}
		});
	}

	getSkeletonTitle()
	{
		return Loc.getMessage('CAL_SYNC_NEW_EVENT_ICLOUD_TITLE');
	}
}