import { Util } from 'calendar.util';
import { DateTimeFormat } from "main.date";
import { Loc } from 'main.core';
import { Header } from "../header";
import { StartInfo } from "./startinfo";
import { HeaderTitle } from "../headertitle";
import { SharingLoader } from '../loader';

export const AddForm = {
	components: {
		Header,
		HeaderTitle,
		StartInfo,
		SharingLoader,
	},
	name: 'EventAddForm',
	props: {
		event: {
			type: Object,
			required: true,
		},
		owner: {
			type: Object,
			required: true,
		},
		phoneFeatureEnabled: {
			type: Boolean,
			required: true,
		},
		sharingUser: Object,
		lastEventName: String,
		userLinkHash: String,
	},
	data()
	{
		return {
			eventName: this.lastEventName,
			authorName: this.sharingUser.userName,
			contactData: this.sharingUser.personalMailbox || this.sharingUser.personalPhone,
			isEmptyContactName: false,
			contactDataError: false,
			isEmptyContactData: false,
			saveButton: {
				text: Loc.getMessage('CALENDAR_SHARING_SEND_REQUEST'),
				disabled: false,
			},
			validated: true,
			loadingProcess: false,
		};
	},
	mounted()
	{
		this.DOM = {
			inputContact: document.getElementById('calendar-sharing-event-add-form_input-contact'),
			calendarContainer: document.querySelector('.calendar-sharing__calendar'),
			addFormElement: document.querySelector('.calendar-sharing__add-form').firstElementChild,
		};
	},
	updated()
	{
		if (this.DOM.addFormElement.offsetHeight > 0)
		{
			this.DOM.calendarContainer.style.height = this.DOM.addFormElement.offsetHeight + 'px';
		}
	},
	methods: {
		async handleSaveEventButton()
		{
			this.fillEventNameIfEmpty();

			this.clearContactDataError();
			this.clearContactNameError();

			if (!this.validateData())
			{
				this.validated = false;
				return;
			}

			this.validated = true;
			this.disableSaveButton();
			this.loadingProcess = true;
			const isSuccessful = await this.saveEvent();
			this.enableSaveButton();
			this.loadingProcess = false;

			if (!isSuccessful && !this.validated)
			{
				return;
			}

			this.$Bitrix.eventEmitter.emit('calendar:sharing:onEventAdd');

			this.$Bitrix.eventEmitter.emit('calendar:sharing:setEventViewError', { viewFormError: !isSuccessful });

			this.$Bitrix.eventEmitter.emit('calendar:sharing:updateAddFormDefaultParams', {
				userName: this.authorName,
				contactData: this.contactData,
				eventName: this.eventName,
			});

			this.$Bitrix.eventEmitter.emit('calendar:sharing:changeApplicationType', { type: 'viewForm' });
		},
		clearContactNameError()
		{
			this.isEmptyContactName = false;
		},
		clearContactDataError()
		{
			this.contactDataError = false;
			this.isEmptyContactData = false;
		},
		validateData(): boolean
		{
			if (this.authorName.length === 0)
			{
				this.isEmptyContactName = true;
			}
			if (this.contactData.length === 0)
			{
				this.isEmptyContactData = true;
			}
			if (!this.isEmptyContactData)
			{
				this.contactDataError = !this.validatePhone() && !this.validateEmail();
			}

			return !this.isEmptyContactName && !this.isEmptyContactData && !this.contactDataError;
		},
		async saveEvent(): boolean
		{
			let response = null;
			this.loadingProcess = true;
			try
			{
				response = await BX.ajax.runAction('calendar.api.sharingajax.saveEvent', {
					data: {
						ownerCreated: this.sharingUser.ownerCreated,
						ownerId: this.owner.id,
						eventName: this.eventName,
						userName: this.authorName,
						userContact: this.contactData,
						dateFrom: this.parseDate(this.event.timeFrom),
						dateTo: this.parseDate(this.event.timeTo),
						timezone: this.event.timezone.timezone_id,
						userLinkHash: this.userLinkHash,
					}
				});
			}
			catch (e)
			{
				response = e;
			}

			if (response.errors.length === 0)
			{
				this.$Bitrix.eventEmitter.emit('calendar:sharing:onSetEventData', {
					eventId: response.data.eventId,
					eventLinkId: response.data.eventLinkId,
					eventLinkHash: response.data.eventLinkHash,
					eventLinkShortUrl: response.data.eventLinkShortUrl,
					eventName: response.data.eventName,
				});

				return true;
			}
			else
			{
				this.$Bitrix.eventEmitter.emit('calendar:sharing:onSetEventData', {
					eventName: this.eventName,
				});
			}

			if (response.data.contactDataError === true)
			{
				this.contactDataError = true;
				this.validated = false;
			}

			if (response.data.isEmptyContactName === true)
			{
				this.isEmptyContactName = true;
				this.validated = false;
			}

			return false;
		},
		disableSaveButton()
		{
			this.saveButton.text = this.$Bitrix.Loc.getMessage('CALENDAR_SHARING_SEND_REQUEST_PROCESSING');
			this.saveButton.disabled = true;
		},
		enableSaveButton()
		{
			this.saveButton.text = this.$Bitrix.Loc.getMessage('CALENDAR_SHARING_SEND_REQUEST');
			this.saveButton.disabled = false;
		},
		parseDate(date): string
		{
			const dateInFormat = DateTimeFormat.format(Util.getDateFormat(), date.getTime() / 1000);
			const timeInFormat = DateTimeFormat.format(Util.getTimeFormat(), date.getTime() / 1000);
			return dateInFormat + ' ' + timeInFormat;
		},
		returnToDateSelector()
		{
			this.$Bitrix.eventEmitter.emit('calendar:sharing:changeApplicationType', {type: 'calendar'});
		},
		fillEventNameIfEmpty(): boolean
		{
			if (this.eventName.length === 0)
			{
				this.eventName = Loc.getMessage('CALENDAR_SHARING_NEW_EVENT');
			}

			return true;
		},
		validatePhone(): boolean
		{
			let isValidated = false
			if (this.phoneFeatureEnabled)
			{
				const phone = this.contactData.replace(/[()\s\-]+/g, '');
				const match = phone.match(/(^\+?\d{4,25}$)/i);
				isValidated = match?.[0] === phone;
			}

			return isValidated;
		},
		validateEmail(): boolean
		{
			const match = this.contactData.match(/(^[^@]+@.+$)/i);
			return match?.[0] === this.contactData;
		},
		getContactDataPlaceholder(): string
		{
			let messageCode = 'CALENDAR_SHARING_AUTHOR_CONTACT_DATA_PLACEHOLDER_PHONE_FEATURE_DISABLED'
			if (this.phoneFeatureEnabled)
			{
				messageCode = 'CALENDAR_SHARING_AUTHOR_CONTACT_DATA_PLACEHOLDER_PHONE_FEATURE_ENABLED';
			}

			return this.$Bitrix.Loc.getMessage(messageCode);
		},
		onPhoneInput()
		{
			this.clearContactDataError();
			if (!this.isPhoneTypeInput())
			{
				return;
			}

			const textBeforeCursor = this.getTextBeforeCursor(this.DOM.inputContact);

			this.contactData = this.formatPhone(this.contactData);
			this.DOM.inputContact.value = this.contactData;

			this.setCursorToFormattedPosition(this.DOM.inputContact, textBeforeCursor);
		},
		getTextBeforeCursor(input)
		{
			const selectionStart = input.selectionStart;
			return input.value.slice(0, selectionStart);
		},
		setCursorToFormattedPosition(input, textBeforeCursor)
		{
			const firstPart = this.getTextEscapedForRegex(textBeforeCursor.slice(0, -1));
			const lastCharacter = this.getTextEscapedForRegex(textBeforeCursor.slice(-1));
			const match = input.value.match(`${firstPart}.*?${lastCharacter}`)[0];
			const formattedPosition = input.value.indexOf(match) + match.length;
			input.setSelectionRange(formattedPosition, formattedPosition);
		},
		getTextEscapedForRegex(text)
		{
			return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
		},
		onPhoneInputKeyDown(e)
		{
			if (!this.isPhoneTypeInput())
			{
				return;
			}

			if (!this.isDigit(e.key) && !this.isControlKey(e.key) && !Util.isAnyModifierKeyPressed(e))
			{
				e.preventDefault();
			}
		},
		isPhoneTypeInput()
		{
			return this.contactData.slice(0, 1) === '+';
		},
		isDigit(key): boolean
		{
			return /^\d+$/.test(key);
		},
		isControlKey(key): boolean
		{
			return ['Esc', 'Delete', 'Backspace', 'Tab'].indexOf(key) >= 0 || key.includes('Arrow');
		},
		formatPhone(value): string
		{
			value ??= '';

			let hasPlus = value.indexOf('+') === 0;
			value = value.replace(/\D/g, '');

			if (!this.phoneDb)
			{
				this.phoneDb = "247,ac,___-____|376,ad,___-___-___|971,ae,___-_-___-____|93,af,__-__-___-____|1268,ag,_ (___) ___-____|1264,ai,_ (___) ___-____|355,al,___ (___) ___-___|374,am,___-__-___-___|599,bq,___-___-____|244,ao,___ (___) ___-___|6721,aq,___-___-___|54,ar,__ (___) ___-____|1684,as,_ (___) ___-____|43,at,__ (___) ___-____|61,au,__-_-____-____|297,aw,___-___-____|994,az,___ (__) ___-__-__|387,ba,___-__-____|1246,bb,_ (___) ___-____|880,bd,___-__-___-___|32,be,__ (___) ___-___|226,bf,___-__-__-____|359,bg,___ (___) ___-___|973,bh,___-____-____|257,bi,___-__-__-____|229,bj,___-__-__-____|1441,bm,_ (___) ___-____|673,bn,___-___-____|591,bo,___-_-___-____|55,br,__-(__)-____-____|1242,bs,_ (___) ___-____|975,bt,___-_-___-___|267,bw,___-__-___-___|375,by,___ (__) ___-__-__|501,bz,___-___-____|243,cd,___ (___) ___-___|236,cf,___-__-__-____|242,cg,___-__-___-____|41,ch,__-__-___-____|225,ci,___-__-___-___|682,ck,___-__-___|56,cl,__-_-____-____|237,cm,___-____-____|86,cn,__ (___) ____-___|57,co,__ (___) ___-____|506,cr,___-____-____|53,cu,__-_-___-____|238,cv,___ (___) __-__|357,cy,___-__-___-___|420,cz,___ (___) ___-___|49,de,__-___-___|253,dj,___-__-__-__-__|45,dk,__-__-__-__-__|1767,dm,_ (___) ___-____|1809,do,_ (___) ___-____|,do,_ (___) ___-____|213,dz,___-__-___-____|593,ec,___-_-___-____|372,ee,___-___-____|20,eg,__ (___) ___-____|291,er,___-_-___-___|34,es,__ (___) ___-___|251,et,___-__-___-____|358,fi,___ (___) ___-__-__|679,fj,___-__-_____|500,fk,___-_____|691,fm,___-___-____|298,fo,___-___-___|262,fr,___-_____-____|33,fr,__ (___) ___-___|508,fr,___-__-____|590,fr,___ (___) ___-___|241,ga,___-_-__-__-__|1473,gd,_ (___) ___-____|995,ge,___ (___) ___-___|594,gf,___-_____-____|233,gh,___ (___) ___-___|350,gi,___-___-_____|299,gl,___-__-__-__|220,gm,___ (___) __-__|224,gn,___-__-___-___|240,gq,___-__-___-____|30,gr,__ (___) ___-____|502,gt,___-_-___-____|1671,gu,_ (___) ___-____|245,gw,___-_-______|592,gy,___-___-____|852,hk,___-____-____|504,hn,___-____-____|385,hr,___-__-___-___|509,ht,___-__-__-____|36,hu,__ (___) ___-___|62,id,__-__-___-__|353,ie,___ (___) ___-___|972,il,___-_-___-____|91,in,__ (____) ___-___|246,io,___-___-____|964,iq,___ (___) ___-____|98,ir,__ (___) ___-____|354,is,___-___-____|39,it,__ (___) ____-___|1876,jm,_ (___) ___-____|962,jo,___-_-____-____|81,jp,__ (___) ___-___|254,ke,___-___-______|996,kg,___ (___) ___-___|855,kh,___ (__) ___-___|686,ki,___-__-___|269,km,___-__-_____|1869,kn,_ (___) ___-____|850,kp,___-___-___|82,kr,__-__-___-____|965,kw,___-____-____|1345,ky,_ (___) ___-____|77,kz,_ (___) ___-__-__|856,la,___-__-___-___|961,lb,___-_-___-___|1758,lc,_ (___) ___-____|423,li,___ (___) ___-____|94,lk,__-__-___-____|231,lr,___-__-___-___|266,ls,___-_-___-____|370,lt,___ (___) __-___|352,lu,___ (___) ___-___|371,lv,___-__-___-___|218,ly,___-__-___-___|212,ma,___-__-____-___|377,mc,___-__-___-___|373,md,___-____-____|382,me,___-__-___-___|261,mg,___-__-__-_____|692,mh,___-___-____|389,mk,___-__-___-___|223,ml,___-__-__-____|95,mm,__-___-___|976,mn,___-__-__-____|853,mo,___-____-____|1670,mp,_ (___) ___-____|596,mq,___ (___) __-__-__|222,mr,___ (__) __-____|1664,ms,_ (___) ___-____|356,mt,___-____-____|230,mu,___-___-____|960,mv,___-___-____|265,mw,___-_-____-____|52,mx,__-__-__-____|60,my,__-_-___-___|258,mz,___-__-___-___|264,na,___-__-___-____|687,nc,___-__-____|227,ne,___-__-__-____|6723,nf,___-___-___|234,ng,___-__-___-__|505,ni,___-____-____|31,nl,__-__-___-____|47,no,__ (___) __-___|977,np,___-__-___-___|674,nr,___-___-____|683,nu,___-____|64,nz,__-__-___-___|968,om,___-__-___-___|507,pa,___-___-____|51,pe,__ (___) ___-___|689,pf,___-__-__-__|675,pg,___ (___) __-___|63,ph,__ (___) ___-____|92,pk,__ (___) ___-____|48,pl,__ (___) ___-___|970,ps,___-__-___-____|351,pt,___-__-___-____|680,pw,___-___-____|595,py,___ (___) ___-___|974,qa,___-____-____|40,ro,__-__-___-____|381,rs,___-__-___-____|7,ru,_ (___) ___-__-__|250,rw,___ (___) ___-___|966,sa,___-_-___-____|677,sb,___-_____|248,sc,___-_-___-___|249,sd,___-__-___-____|46,se,__-__-___-____|65,sg,__-____-____|386,si,___-__-___-___|421,sk,___ (___) ___-___|232,sl,___-__-______|378,sm,___-____-______|221,sn,___-__-___-____|252,so,___-_-___-___|597,sr,___-___-___|211,ss,___-__-___-____|239,st,___-__-_____|503,sv,___-__-__-____|1721,sx,_ (___) ___-____|963,sy,___-__-____-___|268,sz,___ (__) __-____|1649,tc,_ (___) ___-____|235,td,___-__-__-__-__|228,tg,___-__-___-___|66,th,__-__-___-___|992,tj,___-__-___-____|690,tk,___-____|670,tl,___-___-____|993,tm,___-_-___-____|216,tn,___-__-___-___|676,to,___-_____|90,tr,__ (___) ___-____|1868,tt,_ (___) ___-____|688,tv,___-_____|886,tw,___-____-____|255,tz,___-__-___-____|380,ua,___ (__) ___-__-__|256,ug,___ (___) ___-___|44,gb,__-__-____-____|598,uy,___-_-___-__-__|998,uz,___-__-___-____|396698,va,__-_-___-_____|1784,vc,_ (___) ___-____|58,ve,__ (___) ___-____|1284,vg,_ (___) ___-____|1340,vi,_ (___) ___-____|84,vn,__-__-____-___|678,vu,___-_____|681,wf,___-__-____|685,ws,___-__-____|967,ye,___-_-___-___|27,za,__-__-___-____|260,zm,___ (__) ___-____|263,zw,___-_-______|1,us,_ (___) ___-____|"
					.split('|')
					.map(item => {
						item = item.split(',');
						return {
							code: item[0],
							id: item[1],
							mask: item[2],
						};
					});
			}

			if (value.length > 0)
			{
				let mask = this.findMask(value);
				mask += ((mask.indexOf('-') >= 0 ? '-' : ' ') + '__').repeat(10);
				for (let i = 0; i < value.length; i++)
				{
					mask = mask.replace('_', value.slice(i, i + 1));
				}
				value = mask.replace(/\D+$/,'').replace(/_/g, '0');
			}

			if (hasPlus || value.length > 0)
			{
				value = '+' + value;
			}

			return value;
		},
		findMask(value)
		{
			let r = this.phoneDb.filter(item => {
				return value.indexOf(item.code) === 0;
			}).sort((a, b) => {
				return b.code.length - a.code.length;
			})[0];
			return r ? r.mask : '_ ___ __ __ __';
		}
	},
	template: `
		<Header>
			<template v-slot>
				<HeaderTitle
					:has-back-button="true"
					:back-button-callback="returnToDateSelector"
					:text="$Bitrix.Loc.getMessage('CALENDAR_SHARING_ADD_FORM_HEADER_TITLE')"
				/>
				<StartInfo
					:show-clock-icon="false"
					:event="this.event"
				/>
			</template>
		</Header>
		<div class="calendar-sharing-event-add-form">
			<div class="calendar-sharing-event-add-form_content">
				<div class="calendar-sharing-event-add-form_input_box">
					<input
						class="calendar-sharing-event-add-form_input"
						type="text"
						:placeholder="$Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_NAME_PLACEHOLDER')"
						v-model="eventName"
					>
				</div>
				<div class="calendar-sharing-event-add-form_input_box" :class="{
					'--error': isEmptyContactName
				}">
					<input
						class="calendar-sharing-event-add-form_input"
						type="text"
						:placeholder="$Bitrix.Loc.getMessage('CALENDAR_SHARING_AUTHOR_NAME_PLACEHOLDER')"
						v-model="authorName"
						@input="clearContactNameError"
						@focus="clearContactNameError"
					>
					<span class="calendar-sharing-event-add-form_input_note" v-if="isEmptyContactName">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_REQUIRED_FIELD') }}
					</span>
				</div>
				<div class="calendar-sharing-event-add-form_input_box" :class="{
					'--error': contactDataError || isEmptyContactData
				}">
					<input
						id="calendar-sharing-event-add-form_input-contact"
						class="calendar-sharing-event-add-form_input"
						type="text"
						:placeholder="getContactDataPlaceholder()"
						v-model="contactData"
						@focus="clearContactDataError"
						@keydown="onPhoneInputKeyDown"
						@input="onPhoneInput"
					>
					<span class="calendar-sharing-event-add-form_input_note" v-if="contactDataError">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ERROR_FIELD') }}
					</span>
					<span class="calendar-sharing-event-add-form_input_note" v-if="isEmptyContactData">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_REQUIRED_FIELD') }}
					</span>
				</div>
			</div>
			<div class="calendar-sharing-event-add-form_buttons">
				<button
					class="ui-btn ui-btn-success ui-btn-round"
					@click="handleSaveEventButton"
					:disabled="saveButton.disabled"
				>
					{{ saveButton.text }}
				</button>
			</div>
			<SharingLoader v-if="loadingProcess"/>
		</div>
	`
};
