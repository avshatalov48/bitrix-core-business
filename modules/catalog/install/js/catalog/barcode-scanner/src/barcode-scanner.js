import {ajax, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';

const barcodeScannerPool = new Map();

export class BarcodeScanner
{
	constructor()
	{
		this.pool = [];
		EventEmitter.subscribe('onPullEvent-catalog', this.onPullEvent.bind(this));
	}

	static open(id = 'default')
	{
		if (!barcodeScannerPool.has(id))
		{
			const scanner = new BarcodeScanner();
			barcodeScannerPool.set(id, scanner);
		}

		ajax.runAction(
			'catalog.barcodescanner.sendMobilePush',
			{data: {id: id}}
		);
	}

	onPullEvent(event: BaseEvent)
	{
		const data = event.getData();
		const command = data[0];
		const params = Type.isObjectLike(data[1]) ? data[1] : {};

		switch(command)
		{
			case 'HandleBarcodeScanned':
				if (params.hasOwnProperty('id'))
				{
					const scanner = barcodeScannerPool.has(params.id);
					if (scanner)
					{
						EventEmitter.emit('BarcodeScanner::onScanEmit', params)
					}
				}
				break;
		}
	}
}
