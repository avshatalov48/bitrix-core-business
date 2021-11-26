(function (exports,main_core,main_core_events) {
	'use strict';

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<form action=\"\">", "</form>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input type=\"file\" name=\"\" accept=\"image/png\">"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var dataURLToBlob = function dataURLToBlob(dataURL) {
	  var marker = ';base64,',
	      parts,
	      contentType,
	      raw,
	      rawLength;

	  if (dataURL.indexOf(marker) === -1) {
	    parts = dataURL.split(',');
	    contentType = parts[0].split(':')[1];
	    raw = parts[1];
	    return new Blob([raw], {
	      type: contentType
	    });
	  }

	  parts = dataURL.split(marker);
	  contentType = parts[0].split(':')[1];
	  raw = window.atob(parts[1]);
	  rawLength = raw.length;
	  var uInt8Array = new Uint8Array(rawLength);

	  for (var i = 0; i < rawLength; ++i) {
	    uInt8Array[i] = raw.charCodeAt(i);
	  }

	  return new Blob([uInt8Array], {
	    type: contentType
	  });
	};

	describe('BX.Uploader', function () {
	  var uploaderId = 'testUploader';
	  var filesSource = [new File([new Blob(["<html>bad because of type</html>"], {
	    type: 'text/html'
	  })], 'bad.html'), new File([new Blob(["hello, world"], {
	    type: 'text/plain'
	  })], 'good.txt'), new File([dataURLToBlob('data:image/png;base64,R0lGODlhDAAMAKIFAF5LAP/zxAAAANyuAP/gaP///wAAAAAAACH5BAEAAAUALAAAAAAMAAwAAAMlWLPcGjDKFYi9lxKBOaGcF35DhWHamZUW0K4mAbiwWtuf0uxFAgA7')], 'good.png'), new File([dataURLToBlob('data:image/gif;base64,R0lGODlhFAAUAKUgAEyV9Zq98S+I7Hl7gWen9TZKiigxb4+PknuHlHd4fUSs9mad83Sr9XV5qkNZmU+N1S49dKKduuHh4cng9HBwbd7j9Dt5s3yZ1fz4/H+h1So9gH59fVtjk3+Bg1p+s7W2xv///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAABkALAAAAAAUABQAAAarwIxwSCwaj8ikEklpOp+RjTRSREyu2Gukg+l2qMPDZLFgLAgMRgLT2XA7RDGZzCAQDGzJOz4B+AEXAQoQGBAbFGx8An4KDhwPGhh4hHBhEwICCpAGBR4WBxgHX0QdEwELFw4aDgUGrhwGlUMbEx8YDZwFGrqgomBCpREfuKuwkoWJQwkTFbquEK2FTclCtFlYBhUUE3vKCd/g34hS3UsHG11sv0tRU0vv8EZBADs=')], 'bad.gif')];
	  var goodFilesCounter = 0;
	  var goodFilesCountFromEvent = 0;
	  filesSource.forEach(function (file) {
	    if (file.name.indexOf('good') === 0) {
	      goodFilesCounter++;
	    }
	  });
	  var testFiles = null;
	  var events = {
	    onUploaderIsInited: function onUploaderIsInited(_ref) {
	      var _ref$compatData = babelHelpers.slicedToArray(_ref.compatData, 2),
	          uploaderId = _ref$compatData[0],
	          uploader = _ref$compatData[1];

	      if (uploaderId === 'testUploader' && uploader instanceof BX.Uploader) {
	        delete events['onUploaderIsInited'];
	      }
	    },
	    onAttachFiles: function onAttachFiles(_ref2) {
	      var _ref2$compatData = babelHelpers.slicedToArray(_ref2.compatData, 2),
	          files = _ref2$compatData[0],
	          nodes = _ref2$compatData[1];

	      testFiles = files;

	      if (testFiles === filesSource) {
	        console.log('Ura!');
	      }

	      delete events['onAttachFiles'];
	    },
	    onItemIsAdded: function onItemIsAdded(_ref3) {
	      var _ref3$compatData = babelHelpers.slicedToArray(_ref3.compatData, 2),
	          file = _ref3$compatData[0],
	          node = _ref3$compatData[1];

	      if (file.name.indexOf('good') === 0) {
	        goodFilesCountFromEvent++;
	      }

	      if (goodFilesCountFromEvent === goodFilesCounter) {
	        delete events['onItemIsAdded'];
	      }
	    },
	    onPackageIsInitialized: function onPackageIsInitialized(_ref4) {
	      var _ref4$compatData = babelHelpers.slicedToArray(_ref4.compatData, 2),
	          somePostData = _ref4$compatData[0],
	          filesQueue = _ref4$compatData[1],
	          _ref4$data = _ref4.data,
	          formData = _ref4$data.formData,
	          data = _ref4$data.data,
	          files = _ref4$data.files;

	      it('Must be fired "onPackageIsInitialized" with special compatibility data', function () {
	        assert.equal(!!somePostData.post, true);
	        assert.equal(!!somePostData.post.data, true);
	        assert.equal(somePostData.post.filesCount, goodFilesCounter);
	        assert.equal(!!filesQueue, true);
	      });
	      it('Must be fired "onPackageIsInitialized" with new events', function () {
	        assert.equal(!!formData, true);
	        assert.equal(!!data, true);
	        assert.equal(!!files, true);
	      });
	      delete events['onPackageIsInitialized'];
	    },
	    onStart: function onStart(_ref5) {
	      var _ref5$compatData = babelHelpers.slicedToArray(_ref5.compatData, 3),
	          packageId = _ref5$compatData[0],
	          somePostData = _ref5$compatData[1],
	          packItem = _ref5$compatData[2],
	          packItemForNewEvents = _ref5.data.package;

	      it('Must be fired "onStart" with special compatibility data', function () {
	        assert.equal(packageId, packItem.getId());
	        assert.equal(!!somePostData.post, true);
	        assert.equal(!!somePostData.post.data, true);
	        assert.equal(somePostData.post.filesCount, goodFilesCounter);
	      });
	      it('Must be fired "onStart" with new events', function () {
	        assert.equal(packageId, packItemForNewEvents.getId());
	      });
	      delete events['onStart'];
	    },
	    onFinish: function onFinish(_ref6) {
	      var _ref6$compatData = babelHelpers.slicedToArray(_ref6.compatData, 4),
	          usedToBeStreams = _ref6$compatData[0],
	          packageId = _ref6$compatData[1],
	          packItem = _ref6$compatData[2],
	          response = _ref6$compatData[3],
	          _ref6$data = _ref6.data,
	          packItemForNewEvents = _ref6$data.package,
	          responseForNewEvents = _ref6$data.response;

	      it('Must be fired "onFinish" with special compatibility data', function () {
	        assert.equal(packageId, packItem.getId());
	      });
	      it('Must be fired "onFinish" with new events', function () {
	        assert.equal(packageId, packItemForNewEvents.getId());
	      });
	      delete events['onFinish']; //Todo make a differed test
	    }
	  };
	  main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'onUploaderIsInited', events.onUploaderIsInited);
	  var input = main_core.Tag.render(_templateObject());
	  var form = main_core.Tag.render(_templateObject2(), input);
	  var agent = new BX.Uploader({
	    id: uploaderId,
	    input: input,
	    uploadFileUrl: 'uploader.php',
	    dropZone: null,
	    placeHolder: null,
	    events: events,
	    uploadMaxFilesize: 1024,
	    uploadFileWidth: 10,
	    uploadFileHeight: 10,
	    allowUpload: 'I',
	    allowUploadExt: '.jpg png txt'
	  });
	  it('Should apply limits', function () {
	    assert.equal(agent.limits['uploadFile'], 'image/png, image/*, .jpg, .png, .txt');
	  });
	  it('Must be fired "onUploaderIsInited"', function () {
	    assert.equal(events['onUploaderIsInited'], undefined);
	  });
	  agent.onAttach(filesSource);
	  it('Must be fired "onAttachFiles"', function () {
	    assert.equal(events['onAttachFiles'], undefined);
	  });
	  var lengthFiles = agent.length;
	  it("Must be fired \"onItemIsAdded\" for ".concat(goodFilesCounter, " times"), function () {
	    assert.equal(lengthFiles, goodFilesCounter);
	  });
	  agent.submit();
	});

}((this.BX = this.BX || {}),BX,BX.Event));
//# sourceMappingURL=script.js.map
