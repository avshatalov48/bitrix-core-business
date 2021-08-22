import { Vuex } from 'ui.vue.vuex';
import { NotificationItem } from './notification-item';
import { Logger } from 'im.lib.logger';
import { MountingPortal } from 'ui.vue.portal';
import { Popup } from "im.view.popup";
import { NotificationCore } from '../mixin/notificationCore';
import { Utils } from 'im.lib.utils';

export const NotificationSearchResult = {
	components:
	{
		NotificationItem,
		MountingPortal,
		Popup
	},
	mixins: [
		NotificationCore
	],
	props: [
		'searchQuery',
		'searchType',
		'searchDate',
	],
	data()
	{
		return {
			pageLimit: 50,
			lastId: 0,
			initialDataReceived: false,
			isLoadingNewPage: false,
			contentPopupType: '',
			contentPopupValue: '',
			popupInstance: null,
			popupIdSelector: '',
			searchResultsTotal: 0,
			searchPageLoaded: 0,
			searchPagesRequested: 0,
		};
	},
	computed:
	{
		remainingPages()
		{
			return Math.ceil(
				(this.searchResultsTotal - this.searchResults.length) / this.pageLimit
			);
		},
		...Vuex.mapState({
			notification: state => state.notifications.collection,
			searchResults: state => state.notifications.searchCollection,
		})
	},
	watch:
	{
		searchQuery(value)
		{
			if (value.length >= 3 || value === '')
			{
				this.search();
			}
		},
		searchType()
		{
			this.search();
		},
		searchDate(value)
		{
			if (BX.parseDate(value) instanceof Date || value === '')
			{
				this.search();
			}
		},
	},
	created()
	{
		this.searchServerDelayed = Utils.debounce(this.getSearchResultsFromServer, 1500, this);
		this.search();
	},
	beforeDestroy()
	{
		this.$store.dispatch('notifications/deleteSearchResults');
	},
	methods:
	{
		search()
		{
			this.resetSearchState();

			const localResults = this.notification.filter((item) => {
				let result = false;
				if (this.searchQuery.length >= 3)
				{
					 result = item.textConverted.toLowerCase().includes(this.searchQuery.toLowerCase());
					 if (!result)
					 {
					 	return result;
					 }
				}
				if (this.searchType !== '')
				{
					result = item.settingName === this.searchType;
					if (!result)
					{
						return result;
					}
				}
				if (this.searchDate !== '')
				{
					const date = BX.parseDate(this.searchDate);
					if (date instanceof Date)
					{
						// compare dates excluding time.
						const itemDateForCompare = (new Date(item.date.getTime())).setHours(0,0,0,0);
						const dateFromInput = date.setHours(0,0,0,0);

						result = itemDateForCompare === dateFromInput;
					}
				}

				return result;
			});
			if (localResults.length > 0)
			{
				this.$store.dispatch('notifications/setSearchResults', {notification: localResults, type: 'local'});
			}

			const isNeedPlaceholders = this.pageLimit - localResults.length > 0;
			if (isNeedPlaceholders > 0)
			{
				this.drawPlaceholders(this.pageLimit).then(() => {
					this.searchServerDelayed();
				});
			}
			else
			{
				this.searchServerDelayed();
			}
		},
		getSearchResultsFromServer()
		{
			const queryParams = this.getSearchRequestParams();
			this.getRestClient().callMethod('im.notify.history.search', queryParams).then(result => {
				Logger.warn('im.notify.history.search: first page results', result.data());
				this.processHistoryData(result.data());
				this.initialDataReceived = true;
				this.isLoadingNewPage = false;
				this.searchPageLoaded++;
			}).catch(result => {
				Logger.warn('History request error', result)
			});
		},
		processHistoryData(data)
		{
			this.$store.dispatch('notifications/clearPlaceholders');
			if (data.notifications.length <= 0)
			{
				return false;
			}
			this.lastId = this.getLastItemId(data.notifications);
			this.searchResultsTotal = data.total_results;

			this.$store.dispatch('notifications/setSearchResults', {
				notification: data.notifications,
			});
			this.$store.dispatch('users/set', data.users);
			this.isLoadingNewPage = false;
		},
		loadNextPage()
		{
			Logger.warn(`Loading more search results!`);

			const queryParams = this.getSearchRequestParams();

			this.getRestClient().callMethod('im.notify.history.search', queryParams)
				.then(result => {
					Logger.warn('im.notify.history.search: new page results', result.data());

					const newUsers = result.data().users;
					const newItems = result.data().notifications;
					if (!newItems || newItems.length === 0)
					{
						this.$store.dispatch('notifications/clearPlaceholders');
						this.searchResultsTotal = this.searchResults.length;

						return false;
					}

					this.lastId = this.getLastItemId(newItems);

					this.$store.dispatch('users/set', newUsers);
					return this.$store.dispatch('notifications/updatePlaceholders', {
						searchCollection: true,
						items: newItems,
						firstItem: this.searchPageLoaded * this.pageLimit,
					});

				}).then(() => {
					this.searchPageLoaded++;

					return this.onAfterLoadNextPageRequest();
				}).catch(result => {
					this.$store.dispatch('notifications/clearPlaceholders');
					Logger.warn('History request error', result);
				});
		},
		onAfterLoadNextPageRequest()
		{
			Logger.warn('onAfterLoadNextPageRequest');
			if (this.searchPagesRequested > 0)
			{
				Logger.warn('We have delayed requests -', this.searchPagesRequested);
				this.searchPagesRequested--;

				return this.loadNextPage();
			}
			else
			{
				Logger.warn('No more delayed requests, clearing placeholders');
				this.$store.dispatch('notifications/clearPlaceholders');
				this.isLoadingNewPage = false;

				return true;
			}
		},
		getSearchRequestParams()
		{
			const params = {
				'SEARCH_TEXT': this.searchQuery,
				'SEARCH_TYPE': this.searchType,
				'LIMIT': this.pageLimit,
				'CONVERT_TEXT': 'Y'
			};
			if (BX.parseDate(this.searchDate) instanceof Date)
			{
				params['SEARCH_DATE'] = BX.parseDate(this.searchDate).toISOString();
			}
			if (this.lastId > 0)
			{
				params['LAST_ID'] = this.lastId;
			}

			return params;
		},
		resetSearchState()
		{
			this.$store.dispatch('notifications/deleteSearchResults');
			this.initialDataReceived = false;
			this.lastId = 0;
			this.isLoadingNewPage = true;
			this.placeholderCount = 0;
			this.searchPageLoaded = 0;
		},
		drawPlaceholders(amount = 0)
		{
			const placeholders = this.generatePlaceholders(amount);

			return this.$store.dispatch('notifications/setSearchResults', {notification: placeholders});
		},

		//events
		onScroll(event)
		{
			if (!this.isReadyToLoadNewPage(event) || !this.initialDataReceived || this.remainingPages <= 0)
			{
				return;
			}

			if (this.isLoadingNewPage)
			{
				this.drawPlaceholders(this.pageLimit).then(() => {
					this.searchPagesRequested++;
					Logger.warn('Already loading! Draw placeholders and add request, total - ', this.pagesRequested);
				});
			}
			else //if (!this.isLoadingNewPage)
			{
				Logger.warn('Starting new request');

				this.isLoadingNewPage = true;

				this.drawPlaceholders(this.pageLimit).then(() => {
					this.loadNextPage();
				});
			}
		},
		onButtonsClick(event)
		{
			const params = this.getConfirmRequestParams(event);
			const itemId = +params.NOTIFY_ID;
			const notification = this.$store.getters['notifications/getById'](itemId)

			this.getRestClient().callMethod('im.notify.confirm', params)
				.then(() => {
					this.$store.dispatch('notifications/delete', {
						id: itemId,
					});
					if (notification.unread)
					{
						this.$store.dispatch('notifications/setCounter', { unreadTotal: this.unreadCounter - 1});
					}
				})
				.catch(() => {
					this.$store.dispatch('notifications/update', {
						id: itemId,
						fields: { display: true }
					});
				});

			this.$store.dispatch('notifications/update', {
				id: itemId,
				fields: { display: false }
			});
		},
		onDeleteClick(event)
		{
			const itemId = +event.item.id;
			const notification = this.$store.getters['notifications/getSearchItemById'](itemId)

			this.getRestClient().callMethod('im.notify.delete', { id: itemId })
				.then(() => {
					this.$store.dispatch('notifications/delete', { id: itemId, searchMode: true});
					//we need to load more, if we are on the first page and we have not enough elements (~15).
					if (!this.isLoadingNewPage && this.remainingPages > 0 && this.searchResults.length < 15)
					{
						this.isLoadingNewPage = true;

						this.drawPlaceholders(this.pageLimit).then(() => {
							this.loadNextPage();
						});
					}
					if (notification.unread)
					{
						this.$store.dispatch('notifications/setCounter', { unreadTotal: this.unreadCounter - 1});
					}
				})
				.catch((error) => {
					console.error(error)
					this.$store.dispatch('notifications/update', {
						id: itemId,
						fields: { display: true },
						searchMode: true
					});
				});

			this.$store.dispatch('notifications/update', {
				id: itemId,
				fields: { display: false },
				searchMode: true
			});
		},
	},
	//language=Vue
	template: `
		<div class="bx-messenger-notifications-search-results-wrap" @scroll.passive="onScroll">
			<notification-item
				v-for="listItem in searchResults"
				v-if="listItem.display"
				:key="listItem.id"
				:data-id="listItem.id"
				:rawListItem="listItem"
				searchMode="true"
				@buttonsClick="onButtonsClick"
				@contentClick="onContentClick"
				@deleteClick="onDeleteClick"
			/>
			<mounting-portal :mount-to="popupIdSelector" append v-if="popupInstance">
				<popup :type="contentPopupType" :value="contentPopupValue" :popupInstance="popupInstance"/>
			</mounting-portal>
			<div 
				v-if="searchResults.length <= 0" 
				style="padding-top: 210px; margin-bottom: 20px;"
				class="bx-messenger-box-empty bx-notifier-content-empty" 
			>
				{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_RESULTS_NOT_FOUND') }}
			</div>
		</div>
	`
};