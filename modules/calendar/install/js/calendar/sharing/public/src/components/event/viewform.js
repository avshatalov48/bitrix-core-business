import {Header} from "../header";
import {StartInfo} from "./startinfo";
import {HeaderTitle} from "../headertitle";
import { Util } from 'calendar.util';
import { Dom, Tag, Event, Loc } from 'main.core';

export const ViewForm = {
	components: {
		Header,
		HeaderTitle,
		StartInfo,
	},
	name: 'ViewForm',
	props: {
		event: {
			type: Object,
			required: true,
		},
		owner: {
			type: Object,
			required: true,
		},
		viewFormError: Boolean,
	},
	data()
	{
		return {
			backButton: null,
			icsFileSrc: null,
		};
	},
	created()
	{
		const htmlNode = document.querySelector('html');
		const bodyNode = document.querySelector('body');

		this.backButton = Tag.render`<div class="calendar-sharing-view-form__back-button">
			<div class="calendar-sharing_previous-month-arrow"></div>
			<div class="calendar-sharing-view-form__text">${Loc.getMessage('CALENDAR_SHARING_BACK')}</div>
		</div>`;

		Event.bind(this.backButton, 'click', this.returnToDateSelector.bind(this));

		Dom.append(this.backButton, bodyNode);

		if (Dom.hasClass(htmlNode, 'calendar-sharing--bg-gray'))
		{
			Dom.removeClass(htmlNode, 'calendar-sharing--bg-gray');
		}
		if (!Dom.hasClass(htmlNode, 'calendar-sharing-html-body-center'))
		{
			Dom.addClass(htmlNode, 'calendar-sharing-html-body-center');
		}
		if (!Dom.hasClass(bodyNode, 'calendar-sharing-html-body-center'))
		{
			Dom.addClass(bodyNode, 'calendar-sharing-html-body-center');
		}

		if (this.viewFormError)
		{
			Dom.addClass(htmlNode, 'calendar-sharing--bg-red');
		}
		else
		{
			Dom.addClass(htmlNode, 'calendar-sharing--bg-green');
		}
	},
	methods: {
		async downloadIcsFile()
		{
			if (!this.icsFile)
			{
				const response = await BX.ajax.runAction('calendar.api.sharingajax.getIcsFileContent', {
					data: {
						eventLinkHash: this.event.linkHash,
					},
				});
				this.icsFile = response.data;
			}

			Util.downloadIcsFile(this.icsFile, 'event');
		},
		returnToDateSelector()
		{
			if (this.backButton)
			{
				Dom.remove(this.backButton);
			}

			this.$Bitrix.eventEmitter.emit('calendar:sharing:changeApplicationType', {type: 'calendar'});
		},
	},
	template: `
		<div 
			class="calendar-sharing-main__container calendar-sharing--subtract"
			:class="{
				'calendar-sharing--success': !viewFormError,
				'calendar-sharing--error': viewFormError
			}"
		>
			<Header>
				<template v-slot>
					<HeaderTitle
						:has-back-button="false"
						:back-button-callback="returnToDateSelector"
						:text="$Bitrix.Loc.getMessage('CALENDAR_SHARING_VIEW_FORM_HEADER_TITLE_ERROR')"
						v-if="viewFormError"
					/>
					<HeaderTitle
						:has-back-button="false"
						:back-button-callback="returnToDateSelector"
						:text="$Bitrix.Loc.getMessage('CALENDAR_SHARING_VIEW_FORM_HEADER_TITLE')"
						v-else
					/>
					<StartInfo
						:event="this.event"
						:show-clock-icon="!viewFormError"
					/>
					<div class="calendar-sharing-view-form__owner_container">
						<div class="calendar-sharing-view-form__owner_icon_container ui-icon ui-icon-common-user">
							<img class="calendar-sharing-view-form__owner_icon" :src="owner.photo" alt="" v-if="owner.photo">
							<i class="calendar-sharing-view-form__owner_icon" v-else></i>
							<div class="calendar-sharing-view-form__owner_icon_status" v-if="!viewFormError"></div>
						</div>
						<div>
							<div class="calendar-sharing-view-form__owner_name">
								{{ owner.name }} {{ owner.lastName }}
							</div>
							<div class="calendar-sharing-view-form__owner_status" v-if="viewFormError">
								{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_CREATE_ERROR') }}
							</div>
							<div class="calendar-sharing-view-form__owner_status" v-else>
								{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_CREATE_SUCCESS') }}
							</div>
						</div>
					</div>
				</template>
			</Header>
			<div class="calendar-sharing-event-add-form">
				<div class="calendar-sharing-event-add-form_buttons">
					<button class="ui-btn ui-btn-success ui-btn-round" @click="returnToDateSelector" v-if="viewFormError">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_RETURN_TO_CALENDAR') }}
					</button>
					<button class="ui-btn ui-btn-success ui-btn-round" @click="downloadIcsFile" v-else>
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_DOWNLOAD_ICS') }}
					</button>
				</div>
			</div>
		</div>
	`
};