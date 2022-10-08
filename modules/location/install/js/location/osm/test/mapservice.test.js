/* global assert */
import {ControlMode, Location} from 'location.core';
import MapService from '../src/mapservice';
import MapServiceStubFactory from './stubs/mapservicestubfactory';
import MapStub from './stubs/mapstub';
import MarkerStub from './stubs/markerstub';

describe('MapService', () => {

	it('Should be a function', () => {
		assert(typeof MapService === 'function');
	});

	describe('set mode', () => {

		it('Should set mode', () => {
			const mapService = MapServiceStubFactory.create();
			mapService.mode = ControlMode.edit;
			assert.equal(mapService.mode, ControlMode.edit);
		});

		it('Should set marker draggable', () => {
			const mapService = MapServiceStubFactory.create();

			// Inside the mapService.render()
			mapService.marker = new MarkerStub();

			mapService.mode = ControlMode.edit;
			assert.equal(mapService.marker.draggable, true);
		});

		it('Should not set marker draggable', () => {
			const mapService = MapServiceStubFactory.create();

			// Inside the mapService.render()
			mapService.marker = new MarkerStub();

			mapService.mode = ControlMode.view;
			assert.equal(mapService.marker.draggable, false);
		});

		it('Should not set marker draggable', () => {
			const mapService = MapServiceStubFactory.create();

			// Inside the mapService.render()
			mapService.marker = new MarkerStub();

			mapService.mode = ControlMode.edit;
			assert.equal(mapService.marker.draggable, true);
		});
	});

	describe('set location', () => {
		it('Should set location', () => {
			const mapService = MapServiceStubFactory.create();
			mapService.location = new Location({id: 666});
			assert.ok(mapService.location instanceof Location);
			assert.equal(mapService.location.id, 666);
		});

		it('Should set marker lat lon', () => {
			const mapService = MapServiceStubFactory.create();

			// Inside the mapService.render()
			mapService.map = new MapStub();
			mapService.marker = new MarkerStub();

			mapService.location = new Location({latitude: 111, longitude: 222});
			assert.equal(mapService.marker.lat, 111);
			assert.equal(mapService.marker.lon, 222);
		});

		it('Should add marker to map', () => {
			const mapService = MapServiceStubFactory.create();

			// Inside the mapService.render()
			mapService.map = new MapStub();
			mapService.marker = new MarkerStub();

			mapService.location = new Location();
			assert.ok(mapService.map instanceof MapStub);
		});

		it('Should pan map to', () => {
			const mapService = MapServiceStubFactory.create();

			// Inside the mapService.render()
			mapService.map = new MapStub();
			mapService.marker = new MarkerStub();

			mapService.location = new Location({latitude: 111, longitude: 222});
			assert.equal(mapService.map.lat, 111);
			assert.equal(mapService.map.lon, 222);
		});

		it('Should move marker from map if location === null', () => {
			const mapService = MapServiceStubFactory.create();

			// Inside the mapService.render()
			mapService.map = new MapStub();
			mapService.marker = new MarkerStub();
			mapService.marker.addTo(mapService.map);
			mapService.location = new Location({latitude: 111, longitude: 222});
			assert.equal(mapService.markert, null);
		});
	});

	describe('set zoom', () =>
	{
		it('Should set zoom', () =>
		{
			const mapService = MapServiceStubFactory.create();
			mapService.zoom = 10;
			assert.equal(mapService.zoom, 10);
		});

		it('Should set map zoom', () =>
		{
			const mapService = MapServiceStubFactory.create();

			// Inside the mapService.render()
			mapService.map = new MapStub();

			mapService.zoom = 10;
			assert.equal(mapService.map.zoom, 10);
		});

	});

});
