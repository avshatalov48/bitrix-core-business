import { PopupManager } from "main.popup";
import { NotificationTypesCodes } from 'im.const';

export const NotificationCore = {
	data()
	{
		return {
			placeholderCount: 0,
		}
	},
	methods:
	{
		isReadyToLoadNewPage(event)
		{
			const leftSpaceBottom = event.target.scrollHeight - event.target.scrollTop - event.target.clientHeight;

			return leftSpaceBottom < 200; //pixels offset before load new page
		},
		getLastItemId(collection)
		{
			return collection[collection.length - 1].id;
		},
		generatePlaceholders(amount)
		{
			const placeholders = [];
			for (let i = 0; i < amount; i++)
			{
				placeholders.push({
					id: `placeholder${this.placeholderCount}`,
					type: NotificationTypesCodes.placeholder
				});
				this.placeholderCount++;
			}

			return placeholders;
		},
		getRestClient()
		{
			return this.$Bitrix.RestClient.get();
		},
		onContentClick(event)
		{
			this.contentPopupType = event.content.type.toLowerCase();
			this.contentPopupValue = event.content.value;

			if (this.popupInstance != null)
			{
				this.popupInstance.destroy();
				this.popupInstance = null;
			}

			// TODO: replace it with new popups.
			if (this.contentPopupType === 'user' || this.contentPopupType === 'chat')
			{
				const popupAngle = !this.isDarkTheme;

				BXIM.messenger.openPopupExternalData(
					event.event.target,
					this.contentPopupType,
					popupAngle,
					{'ID': this.contentPopupValue}
				);
			}
			else if (this.contentPopupType === 'openlines')
			{
				BX.MessengerCommon.linesGetSessionHistory(this.contentPopupValue);
			}
			else
			{
				const popup = PopupManager.create({
					id: "bx-messenger-popup-external-data",
					targetContainer: document.body,
					className: this.isDarkTheme ? 'bx-im-notifications-popup-window-dark' : '',
					bindElement: event.event.target,
					lightShadow : true,
					offsetTop: 0,
					offsetLeft: 10,
					autoHide: true,
					closeByEsc: true,
					bindOptions: {position: "top"},
					events: {
						onPopupClose: () => this.popupInstance.destroy(),
						onPopupDestroy: () => this.popupInstance = null
					},
				});
				if (!this.isDarkTheme)
				{
					popup.setAngle({});
				}

				this.popupIdSelector = `#${popup.getContentContainer().id}`;

				//little hack for correct open several popups in a row.
				this.$nextTick(() => this.popupInstance = popup);
			}
		},
	},
	computed:
	{
		isDarkTheme()
		{
			if (this.darkTheme === undefined)
			{
				return BX.MessengerTheme.isDark();
			}

			return this.darkTheme;
		},
	}
};