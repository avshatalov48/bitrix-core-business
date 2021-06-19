import { Vuex } from "ui.vue.vuex";
import { BitrixVue } from "ui.vue";
import { Timer } from "im.lib.timer";
import {DialogState} from 'im.const';

/**
 * @notice you need to provide this.userId and this.dialogId
 */
export const DialogCore = {
	data()
	{
		return {
			dialogState: DialogState.loading
		}
	},
	created()
	{
		this.timer = new Timer();
	},
	methods: {
		getController()
		{
			return this.$Bitrix.Data.get('controller');
		},
		getApplicationController()
		{
			return this.getController().application;
		},
		getApplication()
		{
			return this.$Bitrix.Application.get();
		},
		getRestClient()
		{
			return this.$Bitrix.RestClient.get();
		},
		getCurrentUser()
		{
			return this.$store.getters['users/get'](this.application.common.userId, true);
		},
		executeRestAnswer(method, queryResult, extra)
		{
			this.getController().executeRestAnswer(method, queryResult, extra);
		},
		isUnreadMessagesLoaded()
		{
			if (!this.dialog)
			{
				return true;
			}

			if (this.dialog.lastMessageId <= 0)
			{
				return true;
			}

			if (!this.messageCollection || this.messageCollection.length <= 0)
			{
				return true;
			}

			let lastElementId = 0;
			for (let index = this.messageCollection.length-1; index >= 0; index--)
			{
				const lastElement = this.messageCollection[index];
				if (typeof lastElement.id === "number")
				{
					lastElementId = lastElement.id;
					break;
				}
			}

			return lastElementId >= this.dialog.lastMessageId;
		},
		//methods used in several mixins
		openDialog()
		{
			//TODO
		}
	},
	computed: {
		dialog()
		{
			const dialog = this.$store.getters['dialogues/get'](this.application.dialog.dialogId);

			return dialog? dialog: this.$store.getters['dialogues/getBlank']();
		},
		chatId()
		{
			// if (this.dialog)
			// {
			// 	return this.dialog.chatId;
			// }

			if (this.application)
			{
				return this.application.dialog.chatId;
			}
		},
		// userId()
		// {
		// 	return this.application.common.userId;
		// },
		diskFolderId()
		{
			return this.application.dialog.diskFolderId;
		},
		messageCollection()
		{
			return this.$store.getters['messages/get'](this.application.dialog.chatId);
		},
		isDialogShowingMessages()
		{
			const messagesNotEmpty = this.messageCollection && this.messageCollection.length > 0;
			if (messagesNotEmpty)
			{
				this.dialogState = DialogState.show;
			}
			else if (this.dialog && this.dialog.init)
			{
				this.dialogState = DialogState.empty;
			}
			else
			{
				this.dialogState = DialogState.loading;
			}

			return messagesNotEmpty;
		},
		isDarkBackground()
		{
			return this.application.options.darkBackground;
		},
		...Vuex.mapState({
			application: state => state.application,
		}),
		localize()
		{
			return BitrixVue.getFilteredPhrases(['IM_DIALOG_', 'IM_UTILS_', 'IM_MESSENGER_DIALOG_', 'IM_QUOTE_'], this);
		},
	}
};