this.BX = this.BX || {};
this.BX.Location = this.BX.Location || {};
(function (exports,location_core,location_source,main_loader,main_core) {
	'use strict';

	class LocationHelper {
	  static makeObjectFromLocation(location) {
	    return JSON.parse(location.toJson());
	  }
	  static makeAddressFromObject(address) {
	    return new location_core.Address(address);
	  }
	  static makeObjectFromAddress(address) {
	    return JSON.parse(address.toJson());
	  }
	  static makeFormatFromObject(format) {
	    return new location_core.Format(format);
	  }
	  static makeSource() {
	    return location_source.Factory.create(BX.message('LOCATION_MOBILE_SOURCE_CODE'), BX.message('LOCATION_MOBILE_LANGUAGE_ID'), BX.message('LOCATION_MOBILE_SOURCE_LANGUAGE_ID'), BX.message('LOCATION_MOBILE_SOURCE_PARAMS'));
	  }
	  static makeRepository() {
	    return new location_core.LocationRepository();
	  }
	  static getTextAddress(address, addressFormat, template) {
	    if (address === null) {
	      return '';
	    }
	    const locAddressFormat = LocationHelper.makeFormatFromObject(addressFormat);
	    return location_core.AddressStringConverter.convertAddressToStringTemplate(LocationHelper.makeAddressFromObject(address), locAddressFormat.getTemplate(template), 'text', ', ', locAddressFormat);
	  }
	  static getTextAddressForAutocomplete(address, addressFormat) {
	    return LocationHelper.getTextAddress(address, addressFormat, location_core.FormatTemplateType.AUTOCOMPLETE);
	  }
	  static getTextAddressForDefault(address, addressFormat) {
	    return LocationHelper.getTextAddress(address, addressFormat, location_core.FormatTemplateType.DEFAULT);
	  }
	  static getTextAddressForMap(address, addressFormat) {
	    let result = LocationHelper.getTextAddressForAutocomplete(address, addressFormat);
	    if (result.trim() === '') {
	      result = LocationHelper.getTextAddressForDefault(address, addressFormat);
	    }
	    return result;
	  }
	  static makeMapRenderProps(address, deviceGeoPosition, isEditable, mapContainer) {
	    const result = {
	      zoomControl: false,
	      mode: isEditable ? location_core.ControlMode.edit : location_core.ControlMode.view,
	      mapContainer
	    };
	    if (!LocationHelper.isAddressValidForMap(address)) {
	      if (deviceGeoPosition) {
	        result.location = {
	          latitude: deviceGeoPosition.latitude,
	          longitude: deviceGeoPosition.longitude,
	          type: location_core.LocationType.BUILDING
	        };
	        result.searchOnRender = !address;
	      } else {
	        const defaultLocationPoint = JSON.parse(BX.message('LOCATION_MOBILE_DEFAULT_LOCATION_POINT'));
	        if (defaultLocationPoint) {
	          result.location = {
	            latitude: defaultLocationPoint.latitude,
	            longitude: defaultLocationPoint.longitude,
	            type: location_core.LocationType.BUILDING
	          };
	          result.searchOnRender = !address;
	        }
	      }
	    } else {
	      result.location = {
	        latitude: address.latitude,
	        longitude: address.longitude,
	        type: Math.max(...Object.keys(address.fieldCollection).map(Number))
	      };
	    }
	    return result;
	  }
	  static makeAutocompleteParams(address, deviceGeoPosition) {
	    const result = {};
	    const biasPoint = LocationHelper.getAutocompleteBiasPoint(address, deviceGeoPosition);
	    if (biasPoint) {
	      result.biasPoint = biasPoint;
	    }
	    return result;
	  }
	  static getAutocompleteBiasPoint(address, deviceGeoPosition) {
	    if (address !== null && address.latitude !== '' && address.longitude !== '') {
	      return new location_core.Point(address.latitude, address.longitude);
	    }
	    if (deviceGeoPosition) {
	      return new location_core.Point(deviceGeoPosition.latitude, deviceGeoPosition.longitude);
	    }
	    return null;
	  }
	  static search(query, address, deviceGeoPosition) {
	    return new Promise((resolve, reject) => {
	      const source = LocationHelper.makeSource();
	      source.autocompleteService.autocomplete(query, LocationHelper.makeAutocompleteParams(address, deviceGeoPosition)).then(searchResults => {
	        resolve(searchResults.map(LocationHelper.makeObjectFromLocation));
	      }, () => {
	        reject();
	      });
	    });
	  }
	  static makeAddressFromText(line2Value, addressFormat) {
	    const locAddressFormat = LocationHelper.makeFormatFromObject(addressFormat);
	    const locAddress = LocationHelper.makeAddressFromObject({
	      languageId: locAddressFormat.languageId
	    });
	    locAddress.fieldCollection.setFieldValue(location_core.AddressType.ADDRESS_LINE_2, line2Value);
	    return LocationHelper.makeObjectFromAddress(locAddress);
	  }
	  static findAddressByLocation(location) {
	    return new Promise((resolve, reject) => {
	      const repository = LocationHelper.makeRepository();
	      repository.findByExternalId(location.externalId, location.sourceCode, location.languageId).then(foundLocation => {
	        resolve(foundLocation ? LocationHelper.makeObjectFromAddress(foundLocation.address) : null);
	      }, () => {
	        reject();
	      });
	    });
	  }
	  static getAddressFieldsValues(address, addressFormat) {
	    const locAddress = address ? LocationHelper.makeAddressFromObject(address) : null;
	    const locAddressFormat = LocationHelper.makeFormatFromObject(addressFormat);
	    const result = [];
	    for (const type in locAddressFormat.fieldCollection.fields) {
	      const field = locAddressFormat.fieldCollection.fields[type];
	      result.push({
	        name: field.name,
	        type: field.type,
	        value: locAddress ? locAddress.fieldCollection.getFieldValue(type) : ''
	      });
	    }
	    return result;
	  }
	  static applyFieldsToAddress(address, addressFormat, fields) {
	    const locAddressFormat = LocationHelper.makeFormatFromObject(addressFormat);
	    const locAddress = LocationHelper.makeAddressFromObject(address || {
	      languageId: locAddressFormat.languageId
	    });
	    let allFieldsAreEmpty = true;
	    for (const field of fields) {
	      const currentValue = locAddress.fieldCollection.getFieldValue(field.type);
	      if (currentValue !== field.value) {
	        locAddress.fieldCollection.setFieldValue(field.type, field.value);
	      }
	      if (field.value !== null && field.value !== '') {
	        allFieldsAreEmpty = false;
	      }
	    }
	    if (allFieldsAreEmpty) {
	      return null;
	    }
	    return LocationHelper.makeObjectFromAddress(locAddress);
	  }
	  static applyDetailsToAddress(address, details) {
	    const locAddress = LocationHelper.makeAddressFromObject(address);
	    locAddress.fieldCollection.setFieldValue(location_core.AddressType.ADDRESS_LINE_2, details);
	    return LocationHelper.makeObjectFromAddress(locAddress);
	  }
	  static getAddressDetails(address) {
	    const locAddress = LocationHelper.makeAddressFromObject(address);
	    return locAddress.fieldCollection.getFieldValue(location_core.AddressType.ADDRESS_LINE_2) || '';
	  }
	  static getLocationTypeClarification(location) {
	    return location.fieldCollection[location_core.LocationType.TMP_TYPE_CLARIFICATION] || '';
	  }
	  static isAddressValidForMap(address) {
	    return address && address.latitude !== '' && address.longitude !== '' && address.latitude !== '0' && address.longitude !== '0';
	  }
	  static getLine2FieldName(addressFormat) {
	    const locAddressFormat = LocationHelper.makeFormatFromObject(addressFormat);
	    const field = locAddressFormat.getField(location_core.AddressType.ADDRESS_LINE_2);
	    if (!field) {
	      return '';
	    }
	    return field.name;
	  }
	}

	var MapMode = {
	  props: {
	    address: {
	      type: Object,
	      required: false
	    },
	    addressFormat: {
	      type: Object,
	      required: true
	    },
	    deviceGeoPosition: {
	      type: Object,
	      required: false
	    },
	    isEditable: {
	      type: Boolean,
	      required: false,
	      default: true
	    }
	  },
	  data: () => {
	    return {
	      isSearchingMap: false,
	      isSettingDetails: false,
	      addressDetailsDraft: ''
	    };
	  },
	  mounted() {
	    this.source = LocationHelper.makeSource();
	    this.renderMap();
	    if (this.isEditable) {
	      this.subscribeToMapEvents();
	    }
	  },
	  methods: {
	    renderMap() {
	      setTimeout(() => {
	        this.source.mapMobile.render(LocationHelper.makeMapRenderProps(this.address, this.deviceGeoPosition, this.isEditable, this.$refs['map']));
	      }, 500);
	    },
	    subscribeToMapEvents() {
	      this.source.mapMobile.onLocationChangedEventSubscribe(event => {
	        const address = event.data.location.address;
	        this.$emit('address-changed', LocationHelper.makeObjectFromAddress(address));
	      });
	      this.source.mapMobile.onStartChangingSubscribe(event => {
	        this.lastMapSearchRequestId = event.data.requestId;
	      });
	      this.source.mapMobile.onEndChangingSubscribe(event => {
	        if (this.lastMapSearchRequestId !== null && event.data.requestId !== this.lastMapSearchRequestId) {
	          return;
	        }
	        this.isSearchingMap = false;
	        this.lastMapSearchRequestId = null;
	      });
	      this.source.mapMobile.onMapViewChangedSubscribe(() => {
	        this.lastMapSearchRequestId = '';
	        this.isSearchingMap = true;
	      });
	    },
	    onAddressClicked() {
	      this.$emit('address-clicked');
	    },
	    onMarkerClicked(event) {
	      if (!this.isEditable && this.address) {
	        this.source.mapMobile.panTo(this.address.latitude, this.address.longitude);
	        event.stopPropagation();
	      }
	    },
	    openSetDetails() {
	      this.addressDetailsDraft = this.addressDetails;
	      this.isSettingDetails = true;
	      this.showOverlay();
	      setTimeout(() => {
	        const addressDetailsNode = this.getAddressDetailsNode();
	        if (addressDetailsNode) {
	          addressDetailsNode.focus();
	        }
	      }, 0);
	      this.adjustAddressDetailsNodeHeight();
	    },
	    onDoneWithSettingDetails() {
	      this.$emit('address-changed', LocationHelper.applyDetailsToAddress(this.address, this.addressDetailsDraft));
	      this.isSettingDetails = false;
	      this.hideOverlay();
	    },
	    onSearchClicked() {
	      this.$emit('search-clicked');
	    },
	    onDone() {
	      this.$emit('done-clicked');
	    },
	    getOverlayNode() {
	      return this.$refs['overlay'];
	    },
	    showOverlay() {
	      const overlayNode = this.getOverlayNode();
	      if (overlayNode) {
	        main_core.Dom.style(overlayNode, 'display', 'block');
	      }
	    },
	    hideOverlay() {
	      const overlayNode = this.getOverlayNode();
	      if (overlayNode) {
	        main_core.Dom.style(overlayNode, 'display', 'none');
	      }
	    },
	    getAddressDetailsNode() {
	      return this.$refs['address-details'];
	    },
	    adjustAddressDetailsNodeHeight() {
	      setTimeout(() => {
	        const addressDetailsNode = this.getAddressDetailsNode();
	        if (addressDetailsNode) {
	          main_core.Dom.style(addressDetailsNode, 'height', 'auto');
	          main_core.Dom.style(addressDetailsNode, 'height', `${addressDetailsNode.scrollHeight}px`);
	        }
	      }, 0);
	    }
	  },
	  computed: {
	    addressText() {
	      return LocationHelper.getTextAddressForMap(this.address, this.addressFormat);
	    },
	    addressDetails() {
	      return LocationHelper.getAddressDetails(this.address);
	    },
	    addressContainerClasses() {
	      return {
	        'mobile-address-map-address-container': true,
	        'mobile-address-map-address-container-map-searching': this.isSearchingMap
	      };
	    },
	    line2FieldName() {
	      return LocationHelper.getLine2FieldName(this.addressFormat);
	    }
	  },
	  template: `
		<div class="mobile-address-map-container">
			<div ref="overlay" class="mobile-address-map-overlay"></div>
			<div ref="map" class="mobile-address-map-map"></div>
			<div
				v-if="isEditable"
				@click="onSearchClicked"
				class="mobile-address-map-search"
			>
				<div class="mobile-address-map-search-icon"></div>
				<div class="mobile-address-map-search-text">
					{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_SEARCH') }}
				</div>
            </div>
            <div
                v-show="address"
                :class="addressContainerClasses"
            >
				<div
					@click="onAddressClicked"
					class="mobile-address-map-address-address"
				>
					<div class="mobile-address-map-address-address-text">
						<div
							@click="onMarkerClicked"
							class="mobile-address-map-address-address-marker">
						</div>
						{{ addressText }}
					</div>
					<div
						v-if="isEditable"
						@click="openSetDetails"
						class="mobile-address-map-open-set-details"
					></div>
				</div>
				<div
					v-if="isEditable"
					@click="onDone"
					class="mobile-address-map-address-done"
				>
					{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_DONE') }}
				</div>
			</div>				
			<div
				v-if="isSettingDetails"
				class="mobile-address-map-address-details"
			>
				<div class="mobile-address-map-address-details-title">
					{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_DETAILS_TITLE') }}
				</div>
				<div class="mobile-address-map-address-details-textarea-container">
					<textarea
						v-model="addressDetailsDraft"
						:placeholder="line2FieldName"
						@input="adjustAddressDetailsNodeHeight"
						ref="address-details"
						rows="1"
						class="mobile-address-map-address-details-textarea"
					></textarea>
				</div>
				<div
					@click="onDoneWithSettingDetails"
					class="mobile-address-map-address-details-done"
				>
					{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_SAVE_DETAILS') }}
				</div>
			</div>
		</div>
	`
	};

	var Keyboard = {
	  data: () => {
	    return {
	      isKeyboardShown: false
	    };
	  },
	  methods: {
	    subscribeToKeyboardEvents() {
	      window.app.exec('enableCaptureKeyboard', true);
	      BXMobileApp.addCustomEvent('onKeyboardWillShow', () => {
	        this.isKeyboardShown = true;
	      });
	      BXMobileApp.addCustomEvent('onKeyboardWillHide', () => {
	        this.isKeyboardShown = false;
	      });
	    },
	    adjustWindowHeight() {
	      const currentHeight = window.innerHeight;
	      document.body.style.height = `${currentHeight}px`;
	    }
	  }
	};

	var AutocompleteMode = {
	  mixins: [Keyboard],
	  props: {
	    address: {
	      type: Object,
	      required: false
	    },
	    addressFormat: {
	      type: Object,
	      required: true
	    },
	    deviceGeoPosition: {
	      type: Object,
	      required: false
	    },
	    recentAddresses: {
	      type: Array,
	      required: false,
	      default: []
	    },
	    isEditable: {
	      type: Boolean,
	      required: false,
	      default: true
	    }
	  },
	  data: () => {
	    return {
	      searchQuery: '',
	      isSearching: false,
	      searchTookPlace: false,
	      inputTookPlace: false,
	      searchResults: []
	    };
	  },
	  created() {
	    this.locationRepository = LocationHelper.makeRepository();
	    this.debouncedSearch = main_core.Runtime.debounce(this.search, 500, this);
	    this.loader = new main_loader.Loader();
	    this.subscribeToKeyboardEvents();
	  },
	  mounted() {
	    this.adjustSearchQueryNodeHeight();
	    this.refreshSearchQuery();
	  },
	  methods: {
	    search(query) {
	      this.lastQuery = query;
	      this.isSearching = true;
	      LocationHelper.search(query, this.address, this.deviceGeoPosition).then(searchResults => {
	        if (query !== this.lastQuery) {
	          return;
	        }
	        this.searchResults = searchResults;
	        this.searchTookPlace = true;
	        this.isSearching = false;
	      }, () => {
	        if (query !== this.lastQuery) {
	          return;
	        }
	        this.searchResults = [];
	        this.searchTookPlace = true;
	        this.isSearching = false;
	      });
	    },
	    onFoundItemClicked(location) {
	      this.loader.show(this.$refs['search-results-loader']);
	      LocationHelper.findAddressByLocation(location).then(address => {
	        this.loader.hide();
	        this.$emit('address-picked', address);
	      }, () => {
	        this.loader.hide();
	      });
	    },
	    onRecentAddressItemClicked(address) {
	      this.$emit('address-picked', address);
	    },
	    refreshSearchQuery() {
	      this.searchQuery = this.getTextAddressForAutocomplete(this.address);
	    },
	    onMapClicked() {
	      if (this.inputTookPlace && this.searchResults.length > 0) {
	        this.onFoundItemClicked(this.searchResults[0]);
	      } else {
	        this.$emit('map-clicked');
	      }
	    },
	    onAddressNotFoundClicked() {
	      this.$emit('address-not-found-clicked');
	    },
	    onClearAddress() {
	      this.$emit('clear-address');
	      this.searchTookPlace = false;
	      this.searchResults = [];
	      this.adjustSearchQueryNodeHeight();
	    },
	    onSearchQueryInput() {
	      this.inputTookPlace = true;
	      this.adjustSearchQueryNodeHeight();
	      this.$emit('address-changed', LocationHelper.makeAddressFromText(this.searchQuery, this.addressFormat));
	    },
	    adjustSearchQueryNodeHeight() {
	      setTimeout(() => {
	        const searchQueryNode = this.$refs['search-query'];
	        if (searchQueryNode) {
	          main_core.Dom.style(searchQueryNode, 'height', 'auto');
	          main_core.Dom.style(searchQueryNode, 'height', `${searchQueryNode.scrollHeight}px`);
	        }
	      }, 0);
	    },
	    getLocationTypeClarification(location) {
	      return LocationHelper.getLocationTypeClarification(location);
	    },
	    getTextAddressForAutocomplete(address) {
	      return LocationHelper.getTextAddressForAutocomplete(address, this.addressFormat);
	    },
	    getRecentResultItemClasses(index) {
	      const isLastItem = this.recentAddresses.length - 1 === index;
	      return {
	        'mobile-address-autocomplete-results-result-item': !isLastItem,
	        'mobile-address-autocomplete-results-result-item-no-bottom-border': isLastItem
	      };
	    }
	  },
	  computed: {
	    searchResultsContainerStyle() {
	      return {
	        opacity: this.isSearching ? 0.5 : 1
	      };
	    },
	    shouldShowAddressNotFound() {
	      return this.address && this.searchTookPlace;
	    },
	    hasRecentAddresses() {
	      return this.recentAddresses.length > 0;
	    },
	    noMatchesFoundText() {
	      const locPhrase = this.searchResults.length === 0 ? 'LOCATION_MOBILE_APP_MATCHES_NOT_FOUND_MSGVER_1' : 'LOCATION_MOBILE_APP_MATCHES_NO_DESIRED_MATCHES';
	      return this.$Bitrix.Loc.getMessage(locPhrase);
	    }
	  },
	  watch: {
	    isSearching(newValue) {
	      if (newValue === true) {
	        this.loader.show(this.$refs['search-results-loader']);
	      } else {
	        this.loader.hide();
	      }
	    },
	    searchQuery(newValue) {
	      if (newValue === '') {
	        return;
	      }
	      this.debouncedSearch(newValue);
	    },
	    address() {
	      this.refreshSearchQuery();
	    },
	    isKeyboardShown(newValue) {
	      if (newValue) {
	        setTimeout(() => this.adjustWindowHeight(), 0);
	      } else {
	        document.body.style.height = '';
	      }
	    }
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

	var Field = {
	  mixins: [Keyboard],
	  props: {
	    name: {
	      type: String,
	      required: true
	    },
	    type: {
	      type: Number,
	      required: true
	    },
	    value: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    isEditable: {
	      type: Boolean,
	      required: false,
	      default: true
	    }
	  },
	  data: () => {
	    return {
	      isFocused: false
	    };
	  },
	  mounted() {
	    this.adjustQueryNodeHeight();
	  },
	  computed: {
	    isTitleVisible() {
	      return this.isFocused || this.value;
	    },
	    titleClasses() {
	      return {
	        'mobile-address-field-title': true,
	        'mobile-address-field-title-focused': this.isFocused
	      };
	    },
	    placeholder() {
	      return this.isTitleVisible ? this.$Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_NOT_ENTERED') : this.name;
	    }
	  },
	  methods: {
	    onFocusIn() {
	      this.isFocused = true;
	      if (window.platform === 'android') {
	        setTimeout(() => {
	          this.adjustWindowHeight();
	          const container = this.$refs['container'];
	          const buttonOffset = 50;
	          const containerPosition = container.getBoundingClientRect().top;
	          const offsetPosition = containerPosition - buttonOffset;
	          window.scrollTo({
	            top: offsetPosition,
	            behavior: 'smooth'
	          });
	        }, 300);
	      }
	    },
	    onFocusOut() {
	      this.isFocused = false;
	    },
	    onInput(event) {
	      this.$emit('input', {
	        type: this.type,
	        value: event.target.value
	      });
	      this.adjustQueryNodeHeight();
	    },
	    adjustQueryNodeHeight() {
	      setTimeout(() => {
	        const queryNode = this.$refs['textarea-query'];
	        if (queryNode) {
	          main_core.Dom.style(queryNode, 'height', 'auto');
	          main_core.Dom.style(queryNode, 'height', `${queryNode.scrollHeight}px`);
	        }
	      }, 0);
	    }
	  },
	  template: `
		<div ref="container" class="mobile-address-field-container">
			<div
				v-show="isTitleVisible"
				:class="titleClasses"
			>
				{{name}}
			</div>
			<textarea
				:placeholder="placeholder"
				:value="value"
				:disabled="!isEditable"
				@focus="onFocusIn"
				@focusout="onFocusOut"
				@input="onInput"
				class="mobile-address-field"
				ref="textarea-query"
				type="text"
				rows="1"
			></textarea>
		</div>
	`
	};

	var FieldsMode = {
	  mixins: [Keyboard],
	  components: {
	    'field': Field
	  },
	  props: {
	    address: {
	      type: Object,
	      required: false
	    },
	    addressFormat: {
	      type: Object,
	      required: true
	    },
	    isEditable: {
	      type: Boolean,
	      required: false,
	      default: true
	    }
	  },
	  data: () => {
	    return {
	      fields: []
	    };
	  },
	  created() {
	    this.fields = LocationHelper.getAddressFieldsValues(this.address, this.addressFormat);
	    this.subscribeToKeyboardEvents();
	  },
	  methods: {
	    saveValues() {
	      this.$emit('address-changed', LocationHelper.applyFieldsToAddress(this.address, this.addressFormat, this.fields));
	    },
	    onNewSearchClicked() {
	      this.$emit('new-search-clicked');
	    },
	    onBackToMapClicked() {
	      this.$emit('back-to-map-clicked');
	    },
	    onFieldInput(event) {
	      const field = this.fields.find(field => field.type === event.type);
	      if (field) {
	        field.value = event.value;
	      }
	    },
	    onDone() {
	      this.$emit('done', LocationHelper.applyFieldsToAddress(this.address, this.addressFormat, this.fields));
	    },
	    hasSource() {
	      const source = LocationHelper.makeSource();
	      return !!source;
	    }
	  },
	  computed: {
	    shouldShowNewSearchButton() {
	      return !this.isKeyboardShown && this.hasSource();
	    }
	  },
	  watch: {
	    isKeyboardShown(newValue) {
	      const mobileWrapper = document.querySelector('.mobile-address-container');
	      if (newValue) {
	        setTimeout(() => {
	          main_core.Dom.style(mobileWrapper, 'height', `calc(100% + ${this.$refs['save-values'].offsetHeight}px)`);
	          this.adjustWindowHeight();
	        }, 0);
	      } else {
	        setTimeout(() => {
	          main_core.Dom.style(mobileWrapper, 'height', '');
	          document.body.style.height = '';
	        }, 0);
	      }
	    }
	  },
	  template: `
		<div class="mobile-address-fields-fields-inner-container">
			<div class="mobile-address-fields-fields-container">
				<div class="mobile-address-fields-item-wrap">
					<field						
						v-for="field in fields"
						:name="field.name"
						:type="field.type"
						:value="field.value"
						:isEditable="isEditable"
						@input="onFieldInput"
					>
					</field>
				</div>				  
				<div
					v-if="isEditable"
					v-show="shouldShowNewSearchButton"
					@click="onNewSearchClicked"
					class="mobile-address-fields-search-container"
				>
					<div
						data-test-id="newSearchButton"
						class="mobile-address-fields-search"
					>
						<div class="mobile-address-fields-search-icon-search"></div>
						{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_NEW_SEARCH') }}
					</div>
				</div>
				<div
					v-if="!isEditable"
					@click="onBackToMapClicked"
					class="mobile-address-fields-search-container"
				>
					<div
						data-test-id="backToMapButton"
						class="mobile-address-fields-search"
					>
						{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_BACK_TO_MAP') }}
					</div>
				</div>
			</div>
			<div
				v-if="isEditable"
				v-show="!isKeyboardShown"
				@click="onDone"
				class="mobile-address-fields-use-this-address-container"
			>
				<div class="mobile-address-fields-use-this-address">
					{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_DONE') }}
				</div>
			</div>
		</div>
		<div
			v-show="isKeyboardShown"
			@click="saveValues"
			class="mobile-address-fields-save-values"
			ref="save-values"
		>
			{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_SAVE_VALUES') }}
		</div>
	`
	};

	const ModeList = {
	  map: 'map',
	  autocomplete: 'autocomplete',
	  fields: 'fields'
	};
	var addressEditor = {
	  props: {
	    initialAddress: {
	      type: Object,
	      required: false
	    },
	    addressFormat: {
	      type: Object,
	      required: false,
	      default: JSON.parse(BX.message('LOCATION_WIDGET_DEFAULT_FORMAT'))
	    },
	    deviceGeoPosition: {
	      type: Object,
	      required: false
	    },
	    recentAddresses: {
	      type: Array,
	      required: false,
	      default: []
	    },
	    isEditable: {
	      type: Boolean,
	      required: false,
	      default: true
	    },
	    uid: {
	      type: String,
	      required: false
	    }
	  },
	  components: {
	    'map-mode': MapMode,
	    'autocomplete-mode': AutocompleteMode,
	    'fields-mode': FieldsMode
	  },
	  data: () => {
	    return {
	      address: null,
	      mode: null
	    };
	  },
	  created() {
	    if (this.initialAddress) {
	      this.setAddress(this.initialAddress);
	    }
	    this.initializeMode();
	  },
	  computed: {
	    isModeMap() {
	      return this.mode === ModeList.map;
	    },
	    isModeAutocomplete() {
	      return this.mode === ModeList.autocomplete;
	    },
	    isModeFields() {
	      return this.mode === ModeList.fields;
	    }
	  },
	  methods: {
	    initializeMode() {
	      const source = LocationHelper.makeSource();
	      if (source) {
	        if (this.isEditable) {
	          this.setMode(ModeList.autocomplete);
	        } else {
	          if (LocationHelper.isAddressValidForMap(this.address)) {
	            this.setMode(ModeList.map);
	          } else {
	            this.setMode(ModeList.fields);
	          }
	        }
	      } else {
	        this.setMode(ModeList.fields);
	      }
	    },
	    setAddress(address) {
	      this.address = address;
	    },
	    setMode(mode) {
	      this.mode = mode;
	    },
	    emitAddressUsed() {
	      if (!this.isEditable) {
	        return;
	      }
	      const emitAddressValue = this.getEmitAddressValue();
	      if (emitAddressValue.value !== null) {
	        this.saveRecentAddress();
	      }
	      const params = {
	        address: emitAddressValue
	      };
	      if (this.uid) {
	        params.uid = this.uid;
	      }
	      BXMobileApp.Events.postToComponent('Location::MobileAddressEditor::AddressSelected', params);
	    },
	    getEmitAddressValue() {
	      if (this.address === null) {
	        return {
	          value: null,
	          text: '',
	          coords: []
	        };
	      }
	      const address = LocationHelper.makeAddressFromObject(this.address);
	      if (this.initialAddress) {
	        address.id = this.initialAddress.id || 0;
	      }
	      return {
	        value: address.toJson(),
	        text: LocationHelper.getTextAddressForDefault(this.address, this.addressFormat),
	        coords: address.latitude !== '' && address.longitude !== '' ? [address.latitude, address.longitude] : []
	      };
	    },
	    saveRecentAddress() {
	      main_core.ajax.runAction('location.api.recentaddress.save', {
	        data: {
	          address: this.address
	        }
	      });
	    },
	    onMapSearchClicked() {
	      if (!this.isEditable) {
	        return;
	      }
	      this.setMode(ModeList.autocomplete);
	    },
	    onMapAddressChanged(address) {
	      this.setAddress(address);
	    },
	    onMapDone() {
	      this.emitAddressUsed();
	    },
	    onMapAddressClicked() {
	      if (this.isEditable) {
	        return;
	      }
	      this.setMode(ModeList.fields);
	    },
	    onAutocompleteAddressPicked(address) {
	      this.setAddress(address);
	      this.setMode(ModeList.map);
	    },
	    onAutocompleteAddressChanged(address) {
	      this.setAddress(address);
	    },
	    onAutocompleteMapClicked() {
	      this.setMode(ModeList.map);
	    },
	    onAutocompleteAddressNotFoundClicked() {
	      this.setMode(ModeList.fields);
	    },
	    onAutocompleteClearAddress() {
	      this.setAddress(null);
	    },
	    onFieldsAddressChanged(address) {
	      this.setAddress(address);
	    },
	    onFieldsNewSearchClicked() {
	      this.setMode(ModeList.autocomplete);
	    },
	    onFieldsDone(address) {
	      this.setAddress(address);
	      this.emitAddressUsed();
	    },
	    onFieldsBackToMapClicked() {
	      this.setMode(ModeList.map);
	    }
	  },
	  template: `
		<div class="mobile-address-container mobile-address--scope">
			<map-mode
				v-if="isModeMap"
				:address="address"
				:addressFormat="addressFormat"
				:deviceGeoPosition="deviceGeoPosition"
				:isEditable="isEditable"
				@search-clicked="onMapSearchClicked"
				@address-changed="onMapAddressChanged"
				@done-clicked="onMapDone"
				@address-clicked="onMapAddressClicked"
				ref="mobile-container"
			>
			</map-mode>
			<autocomplete-mode
				v-if="isModeAutocomplete"
				:address="address"
				:addressFormat="addressFormat"
				:deviceGeoPosition="deviceGeoPosition"
				:recentAddresses="recentAddresses"
				:isEditable="isEditable"
				@address-picked="onAutocompleteAddressPicked"
				@address-changed="onAutocompleteAddressChanged"
				@map-clicked="onAutocompleteMapClicked"
				@address-not-found-clicked="onAutocompleteAddressNotFoundClicked"
				@clear-address="onAutocompleteClearAddress"
			>
			</autocomplete-mode>
			<fields-mode
				v-if="isModeFields"
				:address="address"
			   	:addressFormat="addressFormat"
				:isEditable="isEditable"
				@address-changed="onFieldsAddressChanged"
				@new-search-clicked="onFieldsNewSearchClicked"
			   	@done="onFieldsDone"
				@back-to-map-clicked="onFieldsBackToMapClicked"
			>
			</fields-mode>
		</div>
	`
	};

	exports.AddressEditor = addressEditor;

}((this.BX.Location.Mobile = this.BX.Location.Mobile || {}),BX.Location.Core,BX.Location.Source,BX,BX));
//# sourceMappingURL=mobile.bundle.js.map
