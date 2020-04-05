var mp3Encoder;
var mp3Buffer = [];

onmessage = function(e)
{
	switch (e.data.action)
	{
		case 'init':
			init(e.data);
			break;
		case 'start':
			start();
			break;
		case 'record':
			record(e.data);
			break;
		case 'stop':
			stop();
			break;
	}
};

function init(data)
{
	var channels = data.channels || 1;
	var sampleRate = data.sampleRate || 44100;
	var bitRate = data.bitRate || 128;
	if(data.type === 'audio/mp3')
	{
		importScripts('/bitrix/js/main/recorder/lame.min.js');
		mp3Encoder = new lamejs.Mp3Encoder(channels, sampleRate, bitRate);
	}
}

function start()
{
	mp3Buffer = [];
}

function record(data)
{
	var buffer = floatTo16BitPCM(data.input);
	var chunk = mp3Encoder.encodeBuffer(buffer);
	mp3Buffer.push(chunk);
}

function stop()
{
	mp3Buffer.push(mp3Encoder.flush);
	var resultBlob = new Blob(mp3Buffer, {type: 'audio/mpeg'});
	postMessage({action: 'result', result: resultBlob});
	mp3Buffer = [];
}

//todo: add dithering
function floatTo16BitPCM(input)
{
	var length = input.length;
	var result = new Int16Array(length);
	var value;
	for(var i = 0; i < length; i++)
	{
		if(input[i] > 1)
			value = 1;
		else if(input[i] < -1)
			value = -1;
		else
			value = input[i];

		result[i] = (value > 0 ? value * 32767 : value * 32768);
	}
	return result;
}

