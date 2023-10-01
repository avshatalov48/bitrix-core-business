import {
	Dom,
	Runtime,
} from 'main.core';
import { Loader } from 'main.loader';
import { LocationHelper } from '../location-helper';
import Keyboard from '../mixins/keyboard';

export default
{
	mixins: [Keyboard],
	props: {
		address: {
			type: Object,
			required: false,
		},
		addressFormat: {
			type: Object,
			required: true,
		},
		deviceGeoPosition: {
			type: Object,
			required: false,
		},
		recentAddresses: {
			type: Array,
			required: false,
			default: [],
		},
		isEditable: {
			type: Boolean,
			required: false,
			default: true,
		},
	},
	data: () => {
		return {
			searchQuery: '',
			isSearching: false,
			searchTookPlace: false,
			inputTookPlace: false,
			searchResults: [],
		};
	},
	created()
	{
		this.locationRepository = LocationHelper.makeRepository();
		this.debouncedSearch = Runtime.debounce(this.search, 500, this);
		this.loader = new Loader();
		this.subscribeToKeyboardEvents();
	},
	mounted()
	{
		this.adjustSearchQueryNodeHeight();
		this.refreshSearchQuery();
	},
	methods: {
		search(query): void
		{
			this.lastQuery = query;
			this.isSearching = true;

			LocationHelper.search(query, this.address, this.deviceGeoPosition).then(
				(searchResults) => {
					if (query !== this.lastQuery)
					{
						return;
					}

					this.searchResults = searchResults;
					this.searchTookPlace = true;
					this.isSearching = false;
				},
				() => {
					if (query !== this.lastQuery)
					{
						return;
					}

					this.searchResults = [];
					this.searchTookPlace = true;
					this.isSearching = false;
				},
			);
		},
		onFoundItemClicked(location): void
		{
			this.loader.show(this.$refs['search-results-loader']);

			LocationHelper.findAddressByLocation(location).then(
				(address) => {
					this.loader.hide();

					this.$emit('address-picked', address);
				},
				() => {
					this.loader.hide();
				},
			);
		},
		onRecentAddressItemClicked(address): void
		{
			this.$emit('address-picked', address);
		},
		refreshSearchQuery(): void
		{
			this.searchQuery = this.getTextAddressForAutocomplete(this.address);
		},
		onMapClicked(): void
		{
			if (this.inputTookPlace && this.searchResults.length > 0)
			{
				this.onFoundItemClicked(this.searchResults[0]);
			}
			else
			{
				this.$emit('map-clicked');
			}
		},
		onAddressNotFoundClicked(): void
		{
			this.$emit('address-not-found-clicked');
		},
		onClearAddress(): void
		{
			this.$emit('clear-address');
			this.searchTookPlace = false;
			this.searchResults = [];

			this.adjustSearchQueryNodeHeight();
		},
		onSearchQueryInput(): void
		{
			this.inputTookPlace = true;
			this.adjustSearchQueryNodeHeight();

			this.$emit(
				'address-changed',
				LocationHelper.makeAddressFromText(this.searchQuery, this.addressFormat)
			);
		},
		adjustSearchQueryNodeHeight(): void
		{
			setTimeout(() => {
				const searchQueryNode = this.$refs['search-query'];
				if (searchQueryNode)
				{
					Dom.style(searchQueryNode, 'height', 'auto');
					Dom.style(searchQueryNode, 'height', `${searchQueryNode.scrollHeight}px`);
				}
			}, 0);
		},
		getLocationTypeClarification(location): string
		{
			return LocationHelper.getLocationTypeClarification(location);
		},
		getTextAddressForAutocomplete(address)
		{
			return LocationHelper.getTextAddressForAutocomplete(address, this.addressFormat);
		},
		getRecentResultItemClasses(index)
		{
			const isLastItem = this.recentAddresses.length - 1 === index;

			return {
				'mobile-address-autocomplete-results-result-item': !isLastItem,
				'mobile-address-autocomplete-results-result-item-no-bottom-border': isLastItem,
			};
		},
	},
	computed: {
		searchResultsContainerStyle(): Object
		{
			return {
				opacity: this.isSearching ? 0.5 : 1,
			};
		},
		shouldShowAddressNotFound(): boolean
		{
			return this.address && this.searchTookPlace;
		},
		hasRecentAddresses(): boolean
		{
			return this.recentAddresses.length > 0;
		},
		noMatchesFoundText(): string
		{
			const locPhrase =
				this.searchResults.length === 0
					? 'LOCATION_MOBILE_APP_MATCHES_NOT_FOUND_MSGVER_1'
					: 'LOCATION_MOBILE_APP_MATCHES_NO_DESIRED_MATCHES'
			;

			return this.$Bitrix.Loc.getMessage(locPhrase);
		},
	},
	watch: {
		isSearching(newValue): void
		{
			if (newValue === true)
			{
				this.loader.show(this.$refs['search-results-loader']);
			}
			else
			{
				this.loader.hide();
			}
		},
		searchQuery(newValue): void
		{
			if (newValue === '')
			{
				return;
			}

			this.debouncedSearch(newValue);
		},
		address(): void
		{
			this.refreshSearchQuery();
		},
		isKeyboardShown(newValue): void
		{
			if (newValue)
			{
				setTimeout(() => this.adjustWindowHeight(),0);
			}
			else
			{
				document.body.style.height = '';
			}
		},
	},
	template: `
		<div class="mobile-address-autocomplete-container">
			<div 
				ref="search-results-loader"
				class="mobile-address-autocomplete-results-search-loader"
			></div>
			<div class="mobile-address-autocomplete-query-container">
				<div class="mobile-address-autocomplete__inner"> 				
					<textarea
						v-model="searchQuery"
						:placeholder="$Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_ENTER_ADDRESS')"
						@input="onSearchQueryInput"
						ref="search-query"
						rows="1"
						class="mobile-address-autocomplete-query-query"
					></textarea>
					<div
						v-if="address"
						@click="onClearAddress"
						class="mobile-address-autocomplete-query-clear"
					></div>
					<div
						v-else
						class="mobile-address-autocomplete-query-search-icon"
					></div>
				</div>
				<div @click="onMapClicked" class="mobile-address-autocomplete-query-map-link"> 				
					<div class="mobile-address-autocomplete-query-map-icon"></div>
					<div class="mobile-address-autocomplete-query-map-text">
						{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_MAP') }}
					</div>					
				</div>
			</div>
			<div
				v-if="address"
				:style="searchResultsContainerStyle"
				class="mobile-address-autocomplete-results-container"
			>
				<div class="mobile-address-autocomplete-results-result-item-container">
					<div
						v-for="item in searchResults"							
						@click="onFoundItemClicked(item)"
						class="mobile-address-autocomplete-results-result-item"
					>
						<div class="mobile-address-autocomplete-results-result-item-name">
							{{ item.name }}
						</div>
						<div class="mobile-address-autocomplete-results-result-item-address-details">
							{{ getLocationTypeClarification(item) }}
						</div>
					</div>
				</div>				  
				<div
					v-show="shouldShowAddressNotFound"
					@click="onAddressNotFoundClicked"
					class="mobile-address-autocomplete-results-result-item-not-found-container"
				>
					<div class="mobile-address-autocomplete-results-result-item-not-found">
						<div class="mobile-address-autocomplete-results-result-item-not-found-text">
							{{ noMatchesFoundText }}
						</div>
						<div class="mobile-address-autocomplete-results-result-item-not-found-text">
							{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_ENTER_INFORMATION_MANUALLY_MSGVER_1') }}
						</div>
					</div>
				</div>
			</div>
			<div
				v-else-if="hasRecentAddresses"
				class="mobile-address-autocomplete-results-container"
			>
				<div class="mobile-address-autocomplete-results-title">
					{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_RECENT_ADDRESSES') }}
				</div>
				<div class="mobile-address-autocomplete-results-result-item-container">
					<div
						v-for="(item, index) in recentAddresses"
						@click="onRecentAddressItemClicked(item)"
						:class="getRecentResultItemClasses(index)"
					>
						<div class="mobile-address-autocomplete-results-result-item-name">
							{{ getTextAddressForAutocomplete(item) }}
						</div>
					</div>
				</div>
			</div>
			<div v-else class="mobile-address-autocomplete-results-empty-state">
				<div class="mobile-address-autocomplete-results-empty-state-icon">
				</div>
				<div class="mobile-address-autocomplete-results-empty-state-inner">
					<div class="mobile-address-autocomplete-results-empty-state-title">
						{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_EMPTY_STATE_TITLE') }}
					</div>
					<div class="mobile-address-autocomplete-results-empty-state-desc">
						{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_EMPTY_STATE_DESCRIPTION') }}
					</div>
				</div>
			</div>
		</div>
	`
};
