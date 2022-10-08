// @flow
'use strict';

import { Loc, Tag, Event } from 'main.core';
import SyncWizard from './syncwizard';
import SyncStageUnit from './syncstageunit';
import { Util } from 'calendar.util';


export default class GoogleSyncWizard extends SyncWizard
{
	TYPE = 'google';
	SLIDER_NAME = 'calendar:sync-wizard-google';
	STAGE_1_CODE = 'google-to-b24';
	STAGE_2_CODE = 'b24-to-google';
	STAGE_3_CODE = 'b24-events-to-google';
	GOOGLE_ON_MOBILE_HELPDESK = 15456338;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Sync.Interface.GoogleSyncWizard');
		this.setAccountName(Loc.getMessage('CALENDAR_TITLE_GOOGLE'));
		this.setSyncStages();
		this.logoIconClass = '--google';
	}

	getHelpLinkWrapper()
	{
		let link;
		this.helpLinkWrapper = Tag.render`
			<div class="calendar-sync__content-block --align-center --space-bottom" style="display: none;">
				${link = Tag.render`<a href="#" class="calendar-sync__content-link">
					${Loc.getMessage('CAL_SYNC_NO_GOOGLE_ON_PHONE')}
				</a>`}
			</div>
		`;

		Event.bind(link, 'click', () => {
			const helper = Util.getBX().Helper;
			if(helper)
			{
				helper.show("redirect=detail&code=" + this.GOOGLE_ON_MOBILE_HELPDESK);
			}
		});

		return this.helpLinkWrapper;
	}

	getFinalCheckWrapper()
	{
		this.finalCheckWrapper = Tag.render`
			<div style="display: none;">
				<div class="calendar-sync__content-block --space-bottom">
					<div class="calendar-sync__balloon --progress">
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-progress">${Loc.getMessage('CAL_SYNC_LETS_CHECK')}</div>
						<div class="calendar-sync__content-text calendar-sync__content-subtitle --show-for-progress">${Loc.getMessage('CAL_SYNC_CREATE_EVENT_GOOGLE')}</div>
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-done">${Loc.getMessage('CAL_SYNC_NEW_EVENT_ADDED_GOOGLE')}</div>
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
				title: Loc.getMessage('CAL_SYNC_STAGE_GOOGLE_1')
			}),
			new SyncStageUnit({
				name: this.STAGE_2_CODE,
				title: Loc.getMessage('CAL_SYNC_STAGE_GOOGLE_2')
			}),
			new SyncStageUnit({
				name: this.STAGE_3_CODE,
				title: Loc.getMessage('CAL_SYNC_STAGE_GOOGLE_3')
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
		return Loc.getMessage('CAL_SYNC_NEW_EVENT_GOOGLE_TITLE');
	}
}
