import {Reflection, Type} from 'main.core';
import {Vue} from "ui.vue";
import 'im.component.conference.conference-edit';

const namespace = Reflection.namespace('BX.Messenger.PhpComponent');

class ConferenceEdit
{
	gridId = 'CONFERENCE_LIST_GRID';

	constructor(params)
	{
		this.id = params.id || 0;
		this.pathToList = params.pathToList;
		this.fieldsData = params.fieldsData;
		this.mode = params.mode;
		this.chatHost = params.chatHost;

		if (Type.isPlainObject(params.chatUsers))
		{
			params.chatUsers = Object.values(params.chatUsers);
		}
		this.chatUsers = params.chatUsers;
		this.presenters = params.presenters;
		this.publicLink = params.publicLink;
		this.chatId = params.chatId;
		this.invitation = params.invitation;
		this.broadcastingEnabled = params.broadcastingEnabled || false;

		this.formContainer = document.getElementById("im-conference-create-fields");

		this.init();
	}

	init()
	{
		this.initComponent();
	}

	initComponent()
	{
		Vue.create({
			el: this.formContainer,
			data: () =>
			{
				return {
					conferenceId: this.id,
					fieldsData: this.fieldsData,
					mode: this.mode,
					chatHost: this.chatHost,
					chatUsers: this.chatUsers,
					presenters: this.presenters,
					publicLink: this.publicLink,
					chatId: this.chatId,
					invitation: this.invitation,
					gridId: this.gridId,
					pathToList: this.pathToList,
					broadcastingEnabled: this.broadcastingEnabled
				};
			},
			template: `
				<bx-im-component-conference-edit
					:conferenceId="conferenceId"
					:fieldsData="fieldsData"
					:mode="mode"
					:chatHost="chatHost"
					:chatUsers="chatUsers"
					:presenters="presenters"
					:publicLink="publicLink"
					:chatId="chatId"
					:invitationText="invitation"
					:gridId="gridId"
					:pathToList="pathToList"
					:broadcastingEnabled="broadcastingEnabled"
				/>
			`,
		});
	}
}

namespace.ConferenceEdit = ConferenceEdit;