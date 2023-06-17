import 'im.v2.test';
import {SmileManager} from 'im.v2.lib.smile-manager';
import {Dexie} from 'ui.dexie';
import {Extension} from 'main.core';
import {Core} from 'im.v2.application.core';
import {LocalStorageManager} from 'im.v2.lib.local-storage';

describe('Smile Manager', () => {
	let smileManager;
	let localStorageManager;
	let restStub;
	const data = {
		sets: [
			{id: '1', name: 'smiles'},
			{id: '2', name: 'emoji'}
		],
		smiles: [
			{
				id: '1',
				setId: '1',
				width: 20,
				height: 20,
				definition: 'HD',
				image: '#icon1.svg'
			},
			{
				id: '2',
				setId: '2',
				width: 20,
				height: 20,
				definition: 'UHD',
				image: '#icon2.svg'
			}
		]
	};
	beforeEach(() => {
		const smiles = {
			clear: sinon.stub().resolves(),
			bulkAdd: sinon.stub().resolves()
		};
		const sets = {...smiles};
		sinon.stub(Dexie.prototype, 'version')
			.withArgs(1)
			.callsFake(function() {
				return {
					stores: () => {
						this.smiles = smiles;
						this.sets = sets;
					}
				}
			});
		restStub = sinon.stub();
		restStub
			.withArgs('smile.get')
			.resolves({data: () => data});
		sinon.stub(Core, 'getRestClient')
			.returns({
				callMethod: restStub
			});
		sinon.stub(Extension, 'getSettings')
			.withArgs('im.v2.lib.smile-manager')
			.returns({
				lastUpdate: '2023-01-10T11:00'
			});
		sinon.stub(Dexie.prototype, 'transaction')
			.withArgs('r', sets, smiles, sinon.match.func)
			.resolves({
				sets: [
					{
						...data.sets[0],
						image: '#icon1.svg'
					},
					{
						...data.sets[1],
						image: '#icon2.svg'
					}
				],
				smiles: [
					{
						...data.smiles[0],
						originalWidth: 40,
						originalHeight: 40
					},
					{
						...data.smiles[1],
						originalWidth: 80,
						originalHeight: 80
					}
				]
			});

		smileManager = new SmileManager();
		localStorageManager = LocalStorageManager.getInstance();
	});

	afterEach(() => {
		localStorageManager.remove('lastUpdateTime');
		sinon.restore();
	});

	it('Should get data from server if it is updated', async () => {
		await smileManager.initSmileList();
		const actualResult = smileManager.smileList;
		const expectedResult = {
			sets: [
				{id: '1', name: 'smiles', image: '#icon1.svg'},
				{id: '2', name: 'emoji', image: '#icon2.svg'}
			],
			smiles: [
				{
					id: '1',
					setId: '1',
					width: 20,
					height: 20,
					definition: 'HD',
					image: '#icon1.svg',
					originalWidth: 40,
					originalHeight: 40
				},
				{
					id: '2',
					setId: '2',
					width: 20,
					height: 20,
					definition: 'UHD',
					image: '#icon2.svg',
					originalWidth: 80,
					originalHeight: 80
				}
			]
		};

		assert.deepEqual(actualResult, expectedResult);
	});

	it('Should get data from local db if it is not updated', async () => {
		localStorageManager.set('lastUpdateTime', Date.parse('2023-01-10T11:00'));
		await smileManager.initSmileList();
		const actualResult = smileManager.smileList;

		const expectedResult = {
			sets: [
				{id: '1', name: 'smiles', image: '#icon1.svg'},
				{id: '2', name: 'emoji', image: '#icon2.svg'}
			],
			smiles: [
				{
					id: '1',
					setId: '1',
					width: 20,
					height: 20,
					definition: 'HD',
					image: '#icon1.svg',
					originalWidth: 40,
					originalHeight: 40
				},
				{
					id: '2',
					setId: '2',
					width: 20,
					height: 20,
					definition: 'UHD',
					image: '#icon2.svg',
					originalWidth: 80,
					originalHeight: 80
				}
			]
		};

		assert.deepEqual(actualResult, expectedResult);
	});

	it('Should not fetch data if it is already set', async () => {
		const smileManager = SmileManager.getInstance();
		await smileManager.initSmileList();

		restStub.reset();

		const otherInstance = SmileManager.getInstance();

		assert.equal(smileManager.smileList, otherInstance.smileList);
	});

	it('Should clear local storage if there is an fetch error', async () => {
		restStub
			.withArgs('smile.get')
			.callsFake(() => new Error('Error'));
		sinon.stub(console, 'error');

		const smileManager = SmileManager.getInstance();
		await smileManager.initSmileList();

		assert.equal(localStorageManager.get('lastUpdateTime'), null);
	});
});