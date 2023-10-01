import { Dom } from 'main.core';
import { LocationHelper } from '../location-helper';

export default
{
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
		isEditable: {
			type: Boolean,
			required: false,
			default: true,
		},
	},
	data: () => {
		return {
			isSearchingMap: false,
			isSettingDetails: false,
			addressDetailsDraft: '',
		};
	},
	mounted()
	{
		this.source = LocationHelper.makeSource();
		this.renderMap();
		if (this.isEditable)
		{
			this.subscribeToMapEvents();
		}
	},
	methods: {
		renderMap(): void
		{
			setTimeout(() => {
				this.source.mapMobile.render(
					LocationHelper.makeMapRenderProps(
						this.address,
						this.deviceGeoPosition,
						this.isEditable,
						this.$refs['map']
					)
				);
			}, 500);
		},
		subscribeToMapEvents(): void
		{
			this.source.mapMobile.onLocationChangedEventSubscribe((event) => {
				const address = event.data.location.address;

				this.$emit('address-changed', LocationHelper.makeObjectFromAddress(address));
			});

			this.source.mapMobile.onStartChangingSubscribe((event) => {
				this.lastMapSearchRequestId = event.data.requestId;
			});

			this.source.mapMobile.onEndChangingSubscribe((event) => {
				if (
					this.lastMapSearchRequestId !== null
					&& event.data.requestId !== this.lastMapSearchRequestId
				)
				{
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
		onAddressClicked(): void
		{
			this.$emit('address-clicked');
		},
		onMarkerClicked(event): void
		{
			if (!this.isEditable && this.address)
			{
				this.source.mapMobile.panTo(
					this.address.latitude,
					this.address.longitude,
				);

				event.stopPropagation();
			}
		},
		openSetDetails(): void
		{
			this.addressDetailsDraft = this.addressDetails;
			this.isSettingDetails = true;
			this.showOverlay();

			setTimeout(() => {
				const addressDetailsNode = this.getAddressDetailsNode();
				if (addressDetailsNode)
				{
					addressDetailsNode.focus();
				}
			}, 0);
			this.adjustAddressDetailsNodeHeight();
		},
		onDoneWithSettingDetails(): void
		{
			this.$emit(
				'address-changed',
				LocationHelper.applyDetailsToAddress(
					this.address,
					this.addressDetailsDraft
				)
			);
			this.isSettingDetails = false;
			this.hideOverlay();
		},
		onSearchClicked(): void
		{
			this.$emit('search-clicked');
		},
		onDone(): void
		{
			this.$emit('done-clicked');
		},
		getOverlayNode(): HTMLElement
		{
			return this.$refs['overlay'];
		},
		showOverlay()
		{
			const overlayNode = this.getOverlayNode();
			if (overlayNode)
			{
				Dom.style(overlayNode, 'display', 'block');
			}
		},
		hideOverlay()
		{
			const overlayNode = this.getOverlayNode();
			if (overlayNode)
			{
				Dom.style(overlayNode, 'display', 'none');
			}
		},
		getAddressDetailsNode(): HTMLElement
		{
			return this.$refs['address-details'];
		},
		adjustAddressDetailsNodeHeight()
		{
			setTimeout(() => {
				const addressDetailsNode = this.getAddressDetailsNode();
				if (addressDetailsNode)
				{
					Dom.style(addressDetailsNode, 'height', 'auto');
					Dom.style(addressDetailsNode, 'height', `${addressDetailsNode.scrollHeight}px`);
				}
			}, 0);
		},
	},
	computed: {
		addressText(): string
		{
			return LocationHelper.getTextAddressForMap(this.address, this.addressFormat);
		},
		addressDetails(): string
		{
			return LocationHelper.getAddressDetails(this.address);
		},
		addressContainerClasses(): Object
		{
			return {
				'mobile-address-map-address-container': true,
				'mobile-address-map-address-container-map-searching': this.isSearchingMap,
			};
		},
		line2FieldName(): string
		{
			return LocationHelper.getLine2FieldName(this.addressFormat);
		},
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
