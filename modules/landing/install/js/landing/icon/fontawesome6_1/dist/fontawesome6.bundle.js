this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,landing_loc) {
	'use strict';

	var FontAwesome = {
	  id: 'fontawesome',
	  name: 'Fontawesome 6',
	  categories: [{
	    id: 'accessibility',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_ACCESSIBILITY'),
	    items: [{
	      className: 'far fa-audio-description',
	      options: ['fat fa-audio-description', 'fal fa-audio-description', 'far fa-audio-description', 'fas fa-audio-description']
	    }, {
	      className: 'far fa-audio-description-slash',
	      options: ['fat fa-audio-description-slash', 'fal fa-audio-description-slash', 'far fa-audio-description-slash', 'fas fa-audio-description-slash']
	    }, {
	      className: 'far fa-braille',
	      options: ['fat fa-braille', 'fal fa-braille', 'far fa-braille', 'fas fa-braille']
	    }, {
	      className: 'far fa-brightness',
	      options: ['fat fa-brightness', 'fal fa-brightness', 'far fa-brightness', 'fas fa-brightness']
	    }, {
	      className: 'far fa-brightness-low',
	      options: ['fat fa-brightness-low', 'fal fa-brightness-low', 'far fa-brightness-low', 'fas fa-brightness-low']
	    }, {
	      className: 'far fa-circle-info',
	      options: ['fat fa-circle-info', 'fal fa-circle-info', 'far fa-circle-info', 'fas fa-circle-info']
	    }, {
	      className: 'far fa-circle-question',
	      options: ['fat fa-circle-question', 'fal fa-circle-question', 'far fa-circle-question', 'fas fa-circle-question']
	    }, {
	      className: 'far fa-closed-captioning',
	      options: ['fat fa-closed-captioning', 'fal fa-closed-captioning', 'far fa-closed-captioning', 'fas fa-closed-captioning']
	    }, {
	      className: 'far fa-closed-captioning-slash',
	      options: ['fat fa-closed-captioning-slash', 'fal fa-closed-captioning-slash', 'far fa-closed-captioning-slash', 'fas fa-closed-captioning-slash']
	    }, {
	      className: 'far fa-comment-captions',
	      options: ['fat fa-comment-captions', 'fal fa-comment-captions', 'far fa-comment-captions', 'fas fa-comment-captions']
	    }, {
	      className: 'far fa-dog-leashed',
	      options: ['fat fa-dog-leashed', 'fal fa-dog-leashed', 'far fa-dog-leashed', 'fas fa-dog-leashed']
	    }, {
	      className: 'far fa-ear',
	      options: ['fat fa-ear', 'fal fa-ear', 'far fa-ear', 'fas fa-ear']
	    }, {
	      className: 'far fa-ear-deaf',
	      options: ['fat fa-ear-deaf', 'fal fa-ear-deaf', 'far fa-ear-deaf', 'fas fa-ear-deaf']
	    }, {
	      className: 'far fa-ear-listen',
	      options: ['fat fa-ear-listen', 'fal fa-ear-listen', 'far fa-ear-listen', 'fas fa-ear-listen']
	    }, {
	      className: 'far fa-eye',
	      options: ['fat fa-eye', 'fal fa-eye', 'far fa-eye', 'fas fa-eye']
	    }, {
	      className: 'far fa-eye-low-vision',
	      options: ['fat fa-eye-low-vision', 'fal fa-eye-low-vision', 'far fa-eye-low-vision', 'fas fa-eye-low-vision']
	    }, {
	      className: 'far fa-fingerprint',
	      options: ['fat fa-fingerprint', 'fal fa-fingerprint', 'far fa-fingerprint', 'fas fa-fingerprint']
	    }, {
	      className: 'far fa-hands',
	      options: ['fat fa-hands', 'fal fa-hands', 'far fa-hands', 'fas fa-hands']
	    }, {
	      className: 'far fa-hands-asl-interpreting',
	      options: ['fat fa-hands-asl-interpreting', 'fal fa-hands-asl-interpreting', 'far fa-hands-asl-interpreting', 'fas fa-hands-asl-interpreting']
	    }, {
	      className: 'far fa-handshake-angle',
	      options: ['fat fa-handshake-angle', 'fal fa-handshake-angle', 'far fa-handshake-angle', 'fas fa-handshake-angle']
	    }, {
	      className: 'far fa-head-side-heart',
	      options: ['fat fa-head-side-heart', 'fal fa-head-side-heart', 'far fa-head-side-heart', 'fas fa-head-side-heart']
	    }, {
	      className: 'far fa-keyboard-brightness',
	      options: ['fat fa-keyboard-brightness', 'fal fa-keyboard-brightness', 'far fa-keyboard-brightness', 'fas fa-keyboard-brightness']
	    }, {
	      className: 'far fa-keyboard-brightness-low',
	      options: ['fat fa-keyboard-brightness-low', 'fal fa-keyboard-brightness-low', 'far fa-keyboard-brightness-low', 'fas fa-keyboard-brightness-low']
	    }, {
	      className: 'far fa-message-captions',
	      options: ['fat fa-message-captions', 'fal fa-message-captions', 'far fa-message-captions', 'fas fa-message-captions']
	    }, {
	      className: 'far fa-person-walking-with-cane',
	      options: ['fat fa-person-walking-with-cane', 'fal fa-person-walking-with-cane', 'far fa-person-walking-with-cane', 'fas fa-person-walking-with-cane']
	    }, {
	      className: 'far fa-phone-volume',
	      options: ['fat fa-phone-volume', 'fal fa-phone-volume', 'far fa-phone-volume', 'fas fa-phone-volume']
	    }, {
	      className: 'far fa-question',
	      options: ['fat fa-question', 'fal fa-question', 'far fa-question', 'fas fa-question']
	    }, {
	      className: 'far fa-square-info',
	      options: ['fat fa-square-info', 'fal fa-square-info', 'far fa-square-info', 'fas fa-square-info']
	    }, {
	      className: 'far fa-square-question',
	      options: ['fat fa-square-question', 'fal fa-square-question', 'far fa-square-question', 'fas fa-square-question']
	    }, {
	      className: 'far fa-tty',
	      options: ['fat fa-tty', 'fal fa-tty', 'far fa-tty', 'fas fa-tty']
	    }, {
	      className: 'far fa-tty-answer',
	      options: ['fat fa-tty-answer', 'fal fa-tty-answer', 'far fa-tty-answer', 'fas fa-tty-answer']
	    }, {
	      className: 'far fa-universal-access',
	      options: ['fat fa-universal-access', 'fal fa-universal-access', 'far fa-universal-access', 'fas fa-universal-access']
	    }, {
	      className: 'far fa-wheelchair',
	      options: ['fat fa-wheelchair', 'fal fa-wheelchair', 'far fa-wheelchair', 'fas fa-wheelchair']
	    }, {
	      className: 'far fa-wheelchair-move',
	      options: ['fat fa-wheelchair-move', 'fal fa-wheelchair-move', 'far fa-wheelchair-move', 'fas fa-wheelchair-move']
	    }]
	  }, {
	    id: 'alert',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_ALERT'),
	    items: [{
	      className: 'far fa-alarm-clock',
	      options: ['fat fa-alarm-clock', 'fal fa-alarm-clock', 'far fa-alarm-clock', 'fas fa-alarm-clock']
	    }, {
	      className: 'far fa-alarm-exclamation',
	      options: ['fat fa-alarm-exclamation', 'fal fa-alarm-exclamation', 'far fa-alarm-exclamation', 'fas fa-alarm-exclamation']
	    }, {
	      className: 'far fa-battery-exclamation',
	      options: ['fat fa-battery-exclamation', 'fal fa-battery-exclamation', 'far fa-battery-exclamation', 'fas fa-battery-exclamation']
	    }, {
	      className: 'far fa-bell',
	      options: ['fat fa-bell', 'fal fa-bell', 'far fa-bell', 'fas fa-bell']
	    }, {
	      className: 'far fa-bell-exclamation',
	      options: ['fat fa-bell-exclamation', 'fal fa-bell-exclamation', 'far fa-bell-exclamation', 'fas fa-bell-exclamation']
	    }, {
	      className: 'far fa-bell-on',
	      options: ['fat fa-bell-on', 'fal fa-bell-on', 'far fa-bell-on', 'fas fa-bell-on']
	    }, {
	      className: 'far fa-bell-school-slash',
	      options: ['fat fa-bell-school-slash', 'fal fa-bell-school-slash', 'far fa-bell-school-slash', 'fas fa-bell-school-slash']
	    }, {
	      className: 'far fa-bell-slash',
	      options: ['fat fa-bell-slash', 'fal fa-bell-slash', 'far fa-bell-slash', 'fas fa-bell-slash']
	    }, {
	      className: 'far fa-bells',
	      options: ['fat fa-bells', 'fal fa-bells', 'far fa-bells', 'fas fa-bells']
	    }, {
	      className: 'far fa-calendar-exclamation',
	      options: ['fat fa-calendar-exclamation', 'fal fa-calendar-exclamation', 'far fa-calendar-exclamation', 'fas fa-calendar-exclamation']
	    }, {
	      className: 'far fa-circle-exclamation',
	      options: ['fat fa-circle-exclamation', 'fal fa-circle-exclamation', 'far fa-circle-exclamation', 'fas fa-circle-exclamation']
	    }, {
	      className: 'far fa-circle-exclamation-check',
	      options: ['fat fa-circle-exclamation-check', 'fal fa-circle-exclamation-check', 'far fa-circle-exclamation-check', 'fas fa-circle-exclamation-check']
	    }, {
	      className: 'far fa-circle-quarters',
	      options: ['fat fa-circle-quarters', 'fal fa-circle-quarters', 'far fa-circle-quarters', 'fas fa-circle-quarters']
	    }, {
	      className: 'far fa-circle-radiation',
	      options: ['fat fa-circle-radiation', 'fal fa-circle-radiation', 'far fa-circle-radiation', 'fas fa-circle-radiation']
	    }, {
	      className: 'far fa-comment-exclamation',
	      options: ['fat fa-comment-exclamation', 'fal fa-comment-exclamation', 'far fa-comment-exclamation', 'fas fa-comment-exclamation']
	    }, {
	      className: 'far fa-diamond-exclamation',
	      options: ['fat fa-diamond-exclamation', 'fal fa-diamond-exclamation', 'far fa-diamond-exclamation', 'fas fa-diamond-exclamation']
	    }, {
	      className: 'far fa-engine-warning',
	      options: ['fat fa-engine-warning', 'fal fa-engine-warning', 'far fa-engine-warning', 'fas fa-engine-warning']
	    }, {
	      className: 'far fa-exclamation',
	      options: ['fat fa-exclamation', 'fal fa-exclamation', 'far fa-exclamation', 'fas fa-exclamation']
	    }, {
	      className: 'far fa-file-exclamation',
	      options: ['fat fa-file-exclamation', 'fal fa-file-exclamation', 'far fa-file-exclamation', 'fas fa-file-exclamation']
	    }, {
	      className: 'far fa-hexagon-exclamation',
	      options: ['fat fa-hexagon-exclamation', 'fal fa-hexagon-exclamation', 'far fa-hexagon-exclamation', 'fas fa-hexagon-exclamation']
	    }, {
	      className: 'far fa-light-emergency',
	      options: ['fat fa-light-emergency', 'fal fa-light-emergency', 'far fa-light-emergency', 'fas fa-light-emergency']
	    }, {
	      className: 'far fa-light-emergency-on',
	      options: ['fat fa-light-emergency-on', 'fal fa-light-emergency-on', 'far fa-light-emergency-on', 'fas fa-light-emergency-on']
	    }, {
	      className: 'far fa-lightbulb-exclamation',
	      options: ['fat fa-lightbulb-exclamation', 'fal fa-lightbulb-exclamation', 'far fa-lightbulb-exclamation', 'fas fa-lightbulb-exclamation']
	    }, {
	      className: 'far fa-lightbulb-exclamation-on',
	      options: ['fat fa-lightbulb-exclamation-on', 'fal fa-lightbulb-exclamation-on', 'far fa-lightbulb-exclamation-on', 'fas fa-lightbulb-exclamation-on']
	    }, {
	      className: 'far fa-location-exclamation',
	      options: ['fat fa-location-exclamation', 'fal fa-location-exclamation', 'far fa-location-exclamation', 'fas fa-location-exclamation']
	    }, {
	      className: 'far fa-message-exclamation',
	      options: ['fat fa-message-exclamation', 'fal fa-message-exclamation', 'far fa-message-exclamation', 'fas fa-message-exclamation']
	    }, {
	      className: 'far fa-octagon-exclamation',
	      options: ['fat fa-octagon-exclamation', 'fal fa-octagon-exclamation', 'far fa-octagon-exclamation', 'fas fa-octagon-exclamation']
	    }, {
	      className: 'far fa-party-bell',
	      options: ['fat fa-party-bell', 'fal fa-party-bell', 'far fa-party-bell', 'fas fa-party-bell']
	    }, {
	      className: 'far fa-party-horn',
	      options: ['fat fa-party-horn', 'fal fa-party-horn', 'far fa-party-horn', 'fas fa-party-horn']
	    }, {
	      className: 'far fa-question',
	      options: ['fat fa-question', 'fal fa-question', 'far fa-question', 'fas fa-question']
	    }, {
	      className: 'far fa-radiation',
	      options: ['fat fa-radiation', 'fal fa-radiation', 'far fa-radiation', 'fas fa-radiation']
	    }, {
	      className: 'far fa-rotate-exclamation',
	      options: ['fat fa-rotate-exclamation', 'fal fa-rotate-exclamation', 'far fa-rotate-exclamation', 'fas fa-rotate-exclamation']
	    }, {
	      className: 'far fa-seal-exclamation',
	      options: ['fat fa-seal-exclamation', 'fal fa-seal-exclamation', 'far fa-seal-exclamation', 'fas fa-seal-exclamation']
	    }, {
	      className: 'far fa-seal-question',
	      options: ['fat fa-seal-question', 'fal fa-seal-question', 'far fa-seal-question', 'fas fa-seal-question']
	    }, {
	      className: 'far fa-sensor',
	      options: ['fat fa-sensor', 'fal fa-sensor', 'far fa-sensor', 'fas fa-sensor']
	    }, {
	      className: 'far fa-sensor-cloud',
	      options: ['fat fa-sensor-cloud', 'fal fa-sensor-cloud', 'far fa-sensor-cloud', 'fas fa-sensor-cloud']
	    }, {
	      className: 'far fa-sensor-fire',
	      options: ['fat fa-sensor-fire', 'fal fa-sensor-fire', 'far fa-sensor-fire', 'fas fa-sensor-fire']
	    }, {
	      className: 'far fa-sensor-on',
	      options: ['fat fa-sensor-on', 'fal fa-sensor-on', 'far fa-sensor-on', 'fas fa-sensor-on']
	    }, {
	      className: 'far fa-sensor-triangle-exclamation',
	      options: ['fat fa-sensor-triangle-exclamation', 'fal fa-sensor-triangle-exclamation', 'far fa-sensor-triangle-exclamation', 'fas fa-sensor-triangle-exclamation']
	    }, {
	      className: 'far fa-shield-exclamation',
	      options: ['fat fa-shield-exclamation', 'fal fa-shield-exclamation', 'far fa-shield-exclamation', 'fas fa-shield-exclamation']
	    }, {
	      className: 'far fa-skull-crossbones',
	      options: ['fat fa-skull-crossbones', 'fal fa-skull-crossbones', 'far fa-skull-crossbones', 'fas fa-skull-crossbones']
	    }, {
	      className: 'far fa-square-exclamation',
	      options: ['fat fa-square-exclamation', 'fal fa-square-exclamation', 'far fa-square-exclamation', 'fas fa-square-exclamation']
	    }, {
	      className: 'far fa-star-exclamation',
	      options: ['fat fa-star-exclamation', 'fal fa-star-exclamation', 'far fa-star-exclamation', 'fas fa-star-exclamation']
	    }, {
	      className: 'far fa-triangle-exclamation',
	      options: ['fat fa-triangle-exclamation', 'fal fa-triangle-exclamation', 'far fa-triangle-exclamation', 'fas fa-triangle-exclamation']
	    }, {
	      className: 'far fa-wifi-exclamation',
	      options: ['fat fa-wifi-exclamation', 'fal fa-wifi-exclamation', 'far fa-wifi-exclamation', 'fas fa-wifi-exclamation']
	    }, {
	      className: 'far fa-wind-warning',
	      options: ['fat fa-wind-warning', 'fal fa-wind-warning', 'far fa-wind-warning', 'fas fa-wind-warning']
	    }]
	  }, {
	    id: 'alphabet',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_ALPHABET'),
	    items: [{
	      className: 'far fa-a',
	      options: ['fat fa-a', 'fal fa-a', 'far fa-a', 'fas fa-a']
	    }, {
	      className: 'far fa-b',
	      options: ['fat fa-b', 'fal fa-b', 'far fa-b', 'fas fa-b']
	    }, {
	      className: 'far fa-c',
	      options: ['fat fa-c', 'fal fa-c', 'far fa-c', 'fas fa-c']
	    }, {
	      className: 'far fa-circle-a',
	      options: ['fat fa-circle-a', 'fal fa-circle-a', 'far fa-circle-a', 'fas fa-circle-a']
	    }, {
	      className: 'far fa-circle-b',
	      options: ['fat fa-circle-b', 'fal fa-circle-b', 'far fa-circle-b', 'fas fa-circle-b']
	    }, {
	      className: 'far fa-circle-c',
	      options: ['fat fa-circle-c', 'fal fa-circle-c', 'far fa-circle-c', 'fas fa-circle-c']
	    }, {
	      className: 'far fa-circle-d',
	      options: ['fat fa-circle-d', 'fal fa-circle-d', 'far fa-circle-d', 'fas fa-circle-d']
	    }, {
	      className: 'far fa-circle-e',
	      options: ['fat fa-circle-e', 'fal fa-circle-e', 'far fa-circle-e', 'fas fa-circle-e']
	    }, {
	      className: 'far fa-circle-f',
	      options: ['fat fa-circle-f', 'fal fa-circle-f', 'far fa-circle-f', 'fas fa-circle-f']
	    }, {
	      className: 'far fa-circle-g',
	      options: ['fat fa-circle-g', 'fal fa-circle-g', 'far fa-circle-g', 'fas fa-circle-g']
	    }, {
	      className: 'far fa-circle-h',
	      options: ['fat fa-circle-h', 'fal fa-circle-h', 'far fa-circle-h', 'fas fa-circle-h']
	    }, {
	      className: 'far fa-circle-i',
	      options: ['fat fa-circle-i', 'fal fa-circle-i', 'far fa-circle-i', 'fas fa-circle-i']
	    }, {
	      className: 'far fa-circle-j',
	      options: ['fat fa-circle-j', 'fal fa-circle-j', 'far fa-circle-j', 'fas fa-circle-j']
	    }, {
	      className: 'far fa-circle-k',
	      options: ['fat fa-circle-k', 'fal fa-circle-k', 'far fa-circle-k', 'fas fa-circle-k']
	    }, {
	      className: 'far fa-circle-l',
	      options: ['fat fa-circle-l', 'fal fa-circle-l', 'far fa-circle-l', 'fas fa-circle-l']
	    }, {
	      className: 'far fa-circle-m',
	      options: ['fat fa-circle-m', 'fal fa-circle-m', 'far fa-circle-m', 'fas fa-circle-m']
	    }, {
	      className: 'far fa-circle-n',
	      options: ['fat fa-circle-n', 'fal fa-circle-n', 'far fa-circle-n', 'fas fa-circle-n']
	    }, {
	      className: 'far fa-circle-o',
	      options: ['fat fa-circle-o', 'fal fa-circle-o', 'far fa-circle-o', 'fas fa-circle-o']
	    }, {
	      className: 'far fa-circle-p',
	      options: ['fat fa-circle-p', 'fal fa-circle-p', 'far fa-circle-p', 'fas fa-circle-p']
	    }, {
	      className: 'far fa-circle-q',
	      options: ['fat fa-circle-q', 'fal fa-circle-q', 'far fa-circle-q', 'fas fa-circle-q']
	    }, {
	      className: 'far fa-circle-r',
	      options: ['fat fa-circle-r', 'fal fa-circle-r', 'far fa-circle-r', 'fas fa-circle-r']
	    }, {
	      className: 'far fa-circle-s',
	      options: ['fat fa-circle-s', 'fal fa-circle-s', 'far fa-circle-s', 'fas fa-circle-s']
	    }, {
	      className: 'far fa-circle-t',
	      options: ['fat fa-circle-t', 'fal fa-circle-t', 'far fa-circle-t', 'fas fa-circle-t']
	    }, {
	      className: 'far fa-circle-u',
	      options: ['fat fa-circle-u', 'fal fa-circle-u', 'far fa-circle-u', 'fas fa-circle-u']
	    }, {
	      className: 'far fa-circle-v',
	      options: ['fat fa-circle-v', 'fal fa-circle-v', 'far fa-circle-v', 'fas fa-circle-v']
	    }, {
	      className: 'far fa-circle-w',
	      options: ['fat fa-circle-w', 'fal fa-circle-w', 'far fa-circle-w', 'fas fa-circle-w']
	    }, {
	      className: 'far fa-circle-x',
	      options: ['fat fa-circle-x', 'fal fa-circle-x', 'far fa-circle-x', 'fas fa-circle-x']
	    }, {
	      className: 'far fa-circle-y',
	      options: ['fat fa-circle-y', 'fal fa-circle-y', 'far fa-circle-y', 'fas fa-circle-y']
	    }, {
	      className: 'far fa-circle-z',
	      options: ['fat fa-circle-z', 'fal fa-circle-z', 'far fa-circle-z', 'fas fa-circle-z']
	    }, {
	      className: 'far fa-d',
	      options: ['fat fa-d', 'fal fa-d', 'far fa-d', 'fas fa-d']
	    }, {
	      className: 'far fa-e',
	      options: ['fat fa-e', 'fal fa-e', 'far fa-e', 'fas fa-e']
	    }, {
	      className: 'far fa-f',
	      options: ['fat fa-f', 'fal fa-f', 'far fa-f', 'fas fa-f']
	    }, {
	      className: 'far fa-g',
	      options: ['fat fa-g', 'fal fa-g', 'far fa-g', 'fas fa-g']
	    }, {
	      className: 'far fa-h',
	      options: ['fat fa-h', 'fal fa-h', 'far fa-h', 'fas fa-h']
	    }, {
	      className: 'far fa-i',
	      options: ['fat fa-i', 'fal fa-i', 'far fa-i', 'fas fa-i']
	    }, {
	      className: 'far fa-j',
	      options: ['fat fa-j', 'fal fa-j', 'far fa-j', 'fas fa-j']
	    }, {
	      className: 'far fa-k',
	      options: ['fat fa-k', 'fal fa-k', 'far fa-k', 'fas fa-k']
	    }, {
	      className: 'far fa-l',
	      options: ['fat fa-l', 'fal fa-l', 'far fa-l', 'fas fa-l']
	    }, {
	      className: 'far fa-m',
	      options: ['fat fa-m', 'fal fa-m', 'far fa-m', 'fas fa-m']
	    }, {
	      className: 'far fa-n',
	      options: ['fat fa-n', 'fal fa-n', 'far fa-n', 'fas fa-n']
	    }, {
	      className: 'far fa-o',
	      options: ['fat fa-o', 'fal fa-o', 'far fa-o', 'fas fa-o']
	    }, {
	      className: 'far fa-p',
	      options: ['fat fa-p', 'fal fa-p', 'far fa-p', 'fas fa-p']
	    }, {
	      className: 'far fa-q',
	      options: ['fat fa-q', 'fal fa-q', 'far fa-q', 'fas fa-q']
	    }, {
	      className: 'far fa-r',
	      options: ['fat fa-r', 'fal fa-r', 'far fa-r', 'fas fa-r']
	    }, {
	      className: 'far fa-s',
	      options: ['fat fa-s', 'fal fa-s', 'far fa-s', 'fas fa-s']
	    }, {
	      className: 'far fa-square-a',
	      options: ['fat fa-square-a', 'fal fa-square-a', 'far fa-square-a', 'fas fa-square-a']
	    }, {
	      className: 'far fa-square-b',
	      options: ['fat fa-square-b', 'fal fa-square-b', 'far fa-square-b', 'fas fa-square-b']
	    }, {
	      className: 'far fa-square-c',
	      options: ['fat fa-square-c', 'fal fa-square-c', 'far fa-square-c', 'fas fa-square-c']
	    }, {
	      className: 'far fa-square-d',
	      options: ['fat fa-square-d', 'fal fa-square-d', 'far fa-square-d', 'fas fa-square-d']
	    }, {
	      className: 'far fa-square-e',
	      options: ['fat fa-square-e', 'fal fa-square-e', 'far fa-square-e', 'fas fa-square-e']
	    }, {
	      className: 'far fa-square-f',
	      options: ['fat fa-square-f', 'fal fa-square-f', 'far fa-square-f', 'fas fa-square-f']
	    }, {
	      className: 'far fa-square-g',
	      options: ['fat fa-square-g', 'fal fa-square-g', 'far fa-square-g', 'fas fa-square-g']
	    }, {
	      className: 'far fa-square-h',
	      options: ['fat fa-square-h', 'fal fa-square-h', 'far fa-square-h', 'fas fa-square-h']
	    }, {
	      className: 'far fa-square-i',
	      options: ['fat fa-square-i', 'fal fa-square-i', 'far fa-square-i', 'fas fa-square-i']
	    }, {
	      className: 'far fa-square-j',
	      options: ['fat fa-square-j', 'fal fa-square-j', 'far fa-square-j', 'fas fa-square-j']
	    }, {
	      className: 'far fa-square-k',
	      options: ['fat fa-square-k', 'fal fa-square-k', 'far fa-square-k', 'fas fa-square-k']
	    }, {
	      className: 'far fa-square-l',
	      options: ['fat fa-square-l', 'fal fa-square-l', 'far fa-square-l', 'fas fa-square-l']
	    }, {
	      className: 'far fa-square-m',
	      options: ['fat fa-square-m', 'fal fa-square-m', 'far fa-square-m', 'fas fa-square-m']
	    }, {
	      className: 'far fa-square-n',
	      options: ['fat fa-square-n', 'fal fa-square-n', 'far fa-square-n', 'fas fa-square-n']
	    }, {
	      className: 'far fa-square-o',
	      options: ['fat fa-square-o', 'fal fa-square-o', 'far fa-square-o', 'fas fa-square-o']
	    }, {
	      className: 'far fa-square-p',
	      options: ['fat fa-square-p', 'fal fa-square-p', 'far fa-square-p', 'fas fa-square-p']
	    }, {
	      className: 'far fa-square-q',
	      options: ['fat fa-square-q', 'fal fa-square-q', 'far fa-square-q', 'fas fa-square-q']
	    }, {
	      className: 'far fa-square-r',
	      options: ['fat fa-square-r', 'fal fa-square-r', 'far fa-square-r', 'fas fa-square-r']
	    }, {
	      className: 'far fa-square-s',
	      options: ['fat fa-square-s', 'fal fa-square-s', 'far fa-square-s', 'fas fa-square-s']
	    }, {
	      className: 'far fa-square-t',
	      options: ['fat fa-square-t', 'fal fa-square-t', 'far fa-square-t', 'fas fa-square-t']
	    }, {
	      className: 'far fa-square-u',
	      options: ['fat fa-square-u', 'fal fa-square-u', 'far fa-square-u', 'fas fa-square-u']
	    }, {
	      className: 'far fa-square-v',
	      options: ['fat fa-square-v', 'fal fa-square-v', 'far fa-square-v', 'fas fa-square-v']
	    }, {
	      className: 'far fa-square-w',
	      options: ['fat fa-square-w', 'fal fa-square-w', 'far fa-square-w', 'fas fa-square-w']
	    }, {
	      className: 'far fa-square-x',
	      options: ['fat fa-square-x', 'fal fa-square-x', 'far fa-square-x', 'fas fa-square-x']
	    }, {
	      className: 'far fa-square-y',
	      options: ['fat fa-square-y', 'fal fa-square-y', 'far fa-square-y', 'fas fa-square-y']
	    }, {
	      className: 'far fa-square-z',
	      options: ['fat fa-square-z', 'fal fa-square-z', 'far fa-square-z', 'fas fa-square-z']
	    }, {
	      className: 'far fa-t',
	      options: ['fat fa-t', 'fal fa-t', 'far fa-t', 'fas fa-t']
	    }, {
	      className: 'far fa-u',
	      options: ['fat fa-u', 'fal fa-u', 'far fa-u', 'fas fa-u']
	    }, {
	      className: 'far fa-v',
	      options: ['fat fa-v', 'fal fa-v', 'far fa-v', 'fas fa-v']
	    }, {
	      className: 'far fa-w',
	      options: ['fat fa-w', 'fal fa-w', 'far fa-w', 'fas fa-w']
	    }, {
	      className: 'far fa-x',
	      options: ['fat fa-x', 'fal fa-x', 'far fa-x', 'fas fa-x']
	    }, {
	      className: 'far fa-y',
	      options: ['fat fa-y', 'fal fa-y', 'far fa-y', 'fas fa-y']
	    }, {
	      className: 'far fa-z',
	      options: ['fat fa-z', 'fal fa-z', 'far fa-z', 'fas fa-z']
	    }]
	  }, {
	    id: 'animals',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_ANIMALS'),
	    items: [{
	      className: 'far fa-alicorn',
	      options: ['fat fa-alicorn', 'fal fa-alicorn', 'far fa-alicorn', 'fas fa-alicorn']
	    }, {
	      className: 'far fa-badger-honey',
	      options: ['fat fa-badger-honey', 'fal fa-badger-honey', 'far fa-badger-honey', 'fas fa-badger-honey']
	    }, {
	      className: 'far fa-bat',
	      options: ['fat fa-bat', 'fal fa-bat', 'far fa-bat', 'fas fa-bat']
	    }, {
	      className: 'far fa-bee',
	      options: ['fat fa-bee', 'fal fa-bee', 'far fa-bee', 'fas fa-bee']
	    }, {
	      className: 'far fa-cat',
	      options: ['fat fa-cat', 'fal fa-cat', 'far fa-cat', 'fas fa-cat']
	    }, {
	      className: 'far fa-cat-space',
	      options: ['fat fa-cat-space', 'fal fa-cat-space', 'far fa-cat-space', 'fas fa-cat-space']
	    }, {
	      className: 'far fa-cow',
	      options: ['fat fa-cow', 'fal fa-cow', 'far fa-cow', 'fas fa-cow']
	    }, {
	      className: 'far fa-crab',
	      options: ['fat fa-crab', 'fal fa-crab', 'far fa-crab', 'fas fa-crab']
	    }, {
	      className: 'far fa-crow',
	      options: ['fat fa-crow', 'fal fa-crow', 'far fa-crow', 'fas fa-crow']
	    }, {
	      className: 'far fa-deer',
	      options: ['fat fa-deer', 'fal fa-deer', 'far fa-deer', 'fas fa-deer']
	    }, {
	      className: 'far fa-deer-rudolph',
	      options: ['fat fa-deer-rudolph', 'fal fa-deer-rudolph', 'far fa-deer-rudolph', 'fas fa-deer-rudolph']
	    }, {
	      className: 'far fa-dog',
	      options: ['fat fa-dog', 'fal fa-dog', 'far fa-dog', 'fas fa-dog']
	    }, {
	      className: 'far fa-dog-leashed',
	      options: ['fat fa-dog-leashed', 'fal fa-dog-leashed', 'far fa-dog-leashed', 'fas fa-dog-leashed']
	    }, {
	      className: 'far fa-dolphin',
	      options: ['fat fa-dolphin', 'fal fa-dolphin', 'far fa-dolphin', 'fas fa-dolphin']
	    }, {
	      className: 'far fa-dove',
	      options: ['fat fa-dove', 'fal fa-dove', 'far fa-dove', 'fas fa-dove']
	    }, {
	      className: 'far fa-dragon',
	      options: ['fat fa-dragon', 'fal fa-dragon', 'far fa-dragon', 'fas fa-dragon']
	    }, {
	      className: 'far fa-duck',
	      options: ['fat fa-duck', 'fal fa-duck', 'far fa-duck', 'fas fa-duck']
	    }, {
	      className: 'far fa-elephant',
	      options: ['fat fa-elephant', 'fal fa-elephant', 'far fa-elephant', 'fas fa-elephant']
	    }, {
	      className: 'far fa-feather',
	      options: ['fat fa-feather', 'fal fa-feather', 'far fa-feather', 'fas fa-feather']
	    }, {
	      className: 'far fa-feather-pointed',
	      options: ['fat fa-feather-pointed', 'fal fa-feather-pointed', 'far fa-feather-pointed', 'fas fa-feather-pointed']
	    }, {
	      className: 'far fa-fish',
	      options: ['fat fa-fish', 'fal fa-fish', 'far fa-fish', 'fas fa-fish']
	    }, {
	      className: 'far fa-fish-bones',
	      options: ['fat fa-fish-bones', 'fal fa-fish-bones', 'far fa-fish-bones', 'fas fa-fish-bones']
	    }, {
	      className: 'far fa-frog',
	      options: ['fat fa-frog', 'fal fa-frog', 'far fa-frog', 'fas fa-frog']
	    }, {
	      className: 'far fa-hippo',
	      options: ['fat fa-hippo', 'fal fa-hippo', 'far fa-hippo', 'fas fa-hippo']
	    }, {
	      className: 'far fa-horse',
	      options: ['fat fa-horse', 'fal fa-horse', 'far fa-horse', 'fas fa-horse']
	    }, {
	      className: 'far fa-horse-head',
	      options: ['fat fa-horse-head', 'fal fa-horse-head', 'far fa-horse-head', 'fas fa-horse-head']
	    }, {
	      className: 'far fa-horse-saddle',
	      options: ['fat fa-horse-saddle', 'fal fa-horse-saddle', 'far fa-horse-saddle', 'fas fa-horse-saddle']
	    }, {
	      className: 'far fa-kiwi-bird',
	      options: ['fat fa-kiwi-bird', 'fal fa-kiwi-bird', 'far fa-kiwi-bird', 'fas fa-kiwi-bird']
	    }, {
	      className: 'far fa-lobster',
	      options: ['fat fa-lobster', 'fal fa-lobster', 'far fa-lobster', 'fas fa-lobster']
	    }, {
	      className: 'far fa-monkey',
	      options: ['fat fa-monkey', 'fal fa-monkey', 'far fa-monkey', 'fas fa-monkey']
	    }, {
	      className: 'far fa-narwhal',
	      options: ['fat fa-narwhal', 'fal fa-narwhal', 'far fa-narwhal', 'fas fa-narwhal']
	    }, {
	      className: 'far fa-otter',
	      options: ['fat fa-otter', 'fal fa-otter', 'far fa-otter', 'fas fa-otter']
	    }, {
	      className: 'far fa-paw',
	      options: ['fat fa-paw', 'fal fa-paw', 'far fa-paw', 'fas fa-paw']
	    }, {
	      className: 'far fa-paw-claws',
	      options: ['fat fa-paw-claws', 'fal fa-paw-claws', 'far fa-paw-claws', 'fas fa-paw-claws']
	    }, {
	      className: 'far fa-paw-simple',
	      options: ['fat fa-paw-simple', 'fal fa-paw-simple', 'far fa-paw-simple', 'fas fa-paw-simple']
	    }, {
	      className: 'far fa-pegasus',
	      options: ['fat fa-pegasus', 'fal fa-pegasus', 'far fa-pegasus', 'fas fa-pegasus']
	    }, {
	      className: 'far fa-pig',
	      options: ['fat fa-pig', 'fal fa-pig', 'far fa-pig', 'fas fa-pig']
	    }, {
	      className: 'far fa-rabbit',
	      options: ['fat fa-rabbit', 'fal fa-rabbit', 'far fa-rabbit', 'fas fa-rabbit']
	    }, {
	      className: 'far fa-rabbit-running',
	      options: ['fat fa-rabbit-running', 'fal fa-rabbit-running', 'far fa-rabbit-running', 'fas fa-rabbit-running']
	    }, {
	      className: 'far fa-ram',
	      options: ['fat fa-ram', 'fal fa-ram', 'far fa-ram', 'fas fa-ram']
	    }, {
	      className: 'far fa-sheep',
	      options: ['fat fa-sheep', 'fal fa-sheep', 'far fa-sheep', 'fas fa-sheep']
	    }, {
	      className: 'far fa-shrimp',
	      options: ['fat fa-shrimp', 'fal fa-shrimp', 'far fa-shrimp', 'fas fa-shrimp']
	    }, {
	      className: 'far fa-skull-cow',
	      options: ['fat fa-skull-cow', 'fal fa-skull-cow', 'far fa-skull-cow', 'fas fa-skull-cow']
	    }, {
	      className: 'far fa-snake',
	      options: ['fat fa-snake', 'fal fa-snake', 'far fa-snake', 'fas fa-snake']
	    }, {
	      className: 'far fa-spider',
	      options: ['fat fa-spider', 'fal fa-spider', 'far fa-spider', 'fas fa-spider']
	    }, {
	      className: 'far fa-spider-black-widow',
	      options: ['fat fa-spider-black-widow', 'fal fa-spider-black-widow', 'far fa-spider-black-widow', 'fas fa-spider-black-widow']
	    }, {
	      className: 'far fa-squid',
	      options: ['fat fa-squid', 'fal fa-squid', 'far fa-squid', 'fas fa-squid']
	    }, {
	      className: 'far fa-squirrel',
	      options: ['fat fa-squirrel', 'fal fa-squirrel', 'far fa-squirrel', 'fas fa-squirrel']
	    }, {
	      className: 'far fa-teddy-bear',
	      options: ['fat fa-teddy-bear', 'fal fa-teddy-bear', 'far fa-teddy-bear', 'fas fa-teddy-bear']
	    }, {
	      className: 'far fa-turtle',
	      options: ['fat fa-turtle', 'fal fa-turtle', 'far fa-turtle', 'fas fa-turtle']
	    }, {
	      className: 'far fa-unicorn',
	      options: ['fat fa-unicorn', 'fal fa-unicorn', 'far fa-unicorn', 'fas fa-unicorn']
	    }, {
	      className: 'far fa-whale',
	      options: ['fat fa-whale', 'fal fa-whale', 'far fa-whale', 'fas fa-whale']
	    }]
	  }, {
	    id: 'arrows',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_ARROWS'),
	    items: [{
	      className: 'far fa-angle-down',
	      options: ['fat fa-angle-down', 'fal fa-angle-down', 'far fa-angle-down', 'fas fa-angle-down']
	    }, {
	      className: 'far fa-angle-left',
	      options: ['fat fa-angle-left', 'fal fa-angle-left', 'far fa-angle-left', 'fas fa-angle-left']
	    }, {
	      className: 'far fa-angle-right',
	      options: ['fat fa-angle-right', 'fal fa-angle-right', 'far fa-angle-right', 'fas fa-angle-right']
	    }, {
	      className: 'far fa-angle-up',
	      options: ['fat fa-angle-up', 'fal fa-angle-up', 'far fa-angle-up', 'fas fa-angle-up']
	    }, {
	      className: 'far fa-angles-down',
	      options: ['fat fa-angles-down', 'fal fa-angles-down', 'far fa-angles-down', 'fas fa-angles-down']
	    }, {
	      className: 'far fa-angles-left',
	      options: ['fat fa-angles-left', 'fal fa-angles-left', 'far fa-angles-left', 'fas fa-angles-left']
	    }, {
	      className: 'far fa-angles-right',
	      options: ['fat fa-angles-right', 'fal fa-angles-right', 'far fa-angles-right', 'fas fa-angles-right']
	    }, {
	      className: 'far fa-angles-up',
	      options: ['fat fa-angles-up', 'fal fa-angles-up', 'far fa-angles-up', 'fas fa-angles-up']
	    }, {
	      className: 'far fa-arrow-down',
	      options: ['fat fa-arrow-down', 'fal fa-arrow-down', 'far fa-arrow-down', 'fas fa-arrow-down']
	    }, {
	      className: 'far fa-arrow-down-1-9',
	      options: ['fat fa-arrow-down-1-9', 'fal fa-arrow-down-1-9', 'far fa-arrow-down-1-9', 'fas fa-arrow-down-1-9']
	    }, {
	      className: 'far fa-arrow-down-9-1',
	      options: ['fat fa-arrow-down-9-1', 'fal fa-arrow-down-9-1', 'far fa-arrow-down-9-1', 'fas fa-arrow-down-9-1']
	    }, {
	      className: 'far fa-arrow-down-a-z',
	      options: ['fat fa-arrow-down-a-z', 'fal fa-arrow-down-a-z', 'far fa-arrow-down-a-z', 'fas fa-arrow-down-a-z']
	    }, {
	      className: 'far fa-arrow-down-arrow-up',
	      options: ['fat fa-arrow-down-arrow-up', 'fal fa-arrow-down-arrow-up', 'far fa-arrow-down-arrow-up', 'fas fa-arrow-down-arrow-up']
	    }, {
	      className: 'far fa-arrow-down-big-small',
	      options: ['fat fa-arrow-down-big-small', 'fal fa-arrow-down-big-small', 'far fa-arrow-down-big-small', 'fas fa-arrow-down-big-small']
	    }, {
	      className: 'far fa-arrow-down-from-dotted-line',
	      options: ['fat fa-arrow-down-from-dotted-line', 'fal fa-arrow-down-from-dotted-line', 'far fa-arrow-down-from-dotted-line', 'fas fa-arrow-down-from-dotted-line']
	    }, {
	      className: 'far fa-arrow-down-from-line',
	      options: ['fat fa-arrow-down-from-line', 'fal fa-arrow-down-from-line', 'far fa-arrow-down-from-line', 'fas fa-arrow-down-from-line']
	    }, {
	      className: 'far fa-arrow-down-left',
	      options: ['fat fa-arrow-down-left', 'fal fa-arrow-down-left', 'far fa-arrow-down-left', 'fas fa-arrow-down-left']
	    }, {
	      className: 'far fa-arrow-down-left-and-arrow-up-right-to-center',
	      options: ['fat fa-arrow-down-left-and-arrow-up-right-to-center', 'fal fa-arrow-down-left-and-arrow-up-right-to-center', 'far fa-arrow-down-left-and-arrow-up-right-to-center', 'fas fa-arrow-down-left-and-arrow-up-right-to-center']
	    }, {
	      className: 'far fa-arrow-down-long',
	      options: ['fat fa-arrow-down-long', 'fal fa-arrow-down-long', 'far fa-arrow-down-long', 'fas fa-arrow-down-long']
	    }, {
	      className: 'far fa-arrow-down-right',
	      options: ['fat fa-arrow-down-right', 'fal fa-arrow-down-right', 'far fa-arrow-down-right', 'fas fa-arrow-down-right']
	    }, {
	      className: 'far fa-arrow-down-short-wide',
	      options: ['fat fa-arrow-down-short-wide', 'fal fa-arrow-down-short-wide', 'far fa-arrow-down-short-wide', 'fas fa-arrow-down-short-wide']
	    }, {
	      className: 'far fa-arrow-down-small-big',
	      options: ['fat fa-arrow-down-small-big', 'fal fa-arrow-down-small-big', 'far fa-arrow-down-small-big', 'fas fa-arrow-down-small-big']
	    }, {
	      className: 'far fa-arrow-down-square-triangle',
	      options: ['fat fa-arrow-down-square-triangle', 'fal fa-arrow-down-square-triangle', 'far fa-arrow-down-square-triangle', 'fas fa-arrow-down-square-triangle']
	    }, {
	      className: 'far fa-arrow-down-to-bracket',
	      options: ['fat fa-arrow-down-to-bracket', 'fal fa-arrow-down-to-bracket', 'far fa-arrow-down-to-bracket', 'fas fa-arrow-down-to-bracket']
	    }, {
	      className: 'far fa-arrow-down-to-dotted-line',
	      options: ['fat fa-arrow-down-to-dotted-line', 'fal fa-arrow-down-to-dotted-line', 'far fa-arrow-down-to-dotted-line', 'fas fa-arrow-down-to-dotted-line']
	    }, {
	      className: 'far fa-arrow-down-to-line',
	      options: ['fat fa-arrow-down-to-line', 'fal fa-arrow-down-to-line', 'far fa-arrow-down-to-line', 'fas fa-arrow-down-to-line']
	    }, {
	      className: 'far fa-arrow-down-to-square',
	      options: ['fat fa-arrow-down-to-square', 'fal fa-arrow-down-to-square', 'far fa-arrow-down-to-square', 'fas fa-arrow-down-to-square']
	    }, {
	      className: 'far fa-arrow-down-triangle-square',
	      options: ['fat fa-arrow-down-triangle-square', 'fal fa-arrow-down-triangle-square', 'far fa-arrow-down-triangle-square', 'fas fa-arrow-down-triangle-square']
	    }, {
	      className: 'far fa-arrow-down-wide-short',
	      options: ['fat fa-arrow-down-wide-short', 'fal fa-arrow-down-wide-short', 'far fa-arrow-down-wide-short', 'fas fa-arrow-down-wide-short']
	    }, {
	      className: 'far fa-arrow-down-z-a',
	      options: ['fat fa-arrow-down-z-a', 'fal fa-arrow-down-z-a', 'far fa-arrow-down-z-a', 'fas fa-arrow-down-z-a']
	    }, {
	      className: 'far fa-arrow-left',
	      options: ['fat fa-arrow-left', 'fal fa-arrow-left', 'far fa-arrow-left', 'fas fa-arrow-left']
	    }, {
	      className: 'far fa-arrow-left-from-line',
	      options: ['fat fa-arrow-left-from-line', 'fal fa-arrow-left-from-line', 'far fa-arrow-left-from-line', 'fas fa-arrow-left-from-line']
	    }, {
	      className: 'far fa-arrow-left-long',
	      options: ['fat fa-arrow-left-long', 'fal fa-arrow-left-long', 'far fa-arrow-left-long', 'fas fa-arrow-left-long']
	    }, {
	      className: 'far fa-arrow-left-long-to-line',
	      options: ['fat fa-arrow-left-long-to-line', 'fal fa-arrow-left-long-to-line', 'far fa-arrow-left-long-to-line', 'fas fa-arrow-left-long-to-line']
	    }, {
	      className: 'far fa-arrow-left-to-line',
	      options: ['fat fa-arrow-left-to-line', 'fal fa-arrow-left-to-line', 'far fa-arrow-left-to-line', 'fas fa-arrow-left-to-line']
	    }, {
	      className: 'far fa-arrow-pointer',
	      options: ['fat fa-arrow-pointer', 'fal fa-arrow-pointer', 'far fa-arrow-pointer', 'fas fa-arrow-pointer']
	    }, {
	      className: 'far fa-arrow-right',
	      options: ['fat fa-arrow-right', 'fal fa-arrow-right', 'far fa-arrow-right', 'fas fa-arrow-right']
	    }, {
	      className: 'far fa-arrow-right-arrow-left',
	      options: ['fat fa-arrow-right-arrow-left', 'fal fa-arrow-right-arrow-left', 'far fa-arrow-right-arrow-left', 'fas fa-arrow-right-arrow-left']
	    }, {
	      className: 'far fa-arrow-right-from-bracket',
	      options: ['fat fa-arrow-right-from-bracket', 'fal fa-arrow-right-from-bracket', 'far fa-arrow-right-from-bracket', 'fas fa-arrow-right-from-bracket']
	    }, {
	      className: 'far fa-arrow-right-from-line',
	      options: ['fat fa-arrow-right-from-line', 'fal fa-arrow-right-from-line', 'far fa-arrow-right-from-line', 'fas fa-arrow-right-from-line']
	    }, {
	      className: 'far fa-arrow-right-long',
	      options: ['fat fa-arrow-right-long', 'fal fa-arrow-right-long', 'far fa-arrow-right-long', 'fas fa-arrow-right-long']
	    }, {
	      className: 'far fa-arrow-right-long-to-line',
	      options: ['fat fa-arrow-right-long-to-line', 'fal fa-arrow-right-long-to-line', 'far fa-arrow-right-long-to-line', 'fas fa-arrow-right-long-to-line']
	    }, {
	      className: 'far fa-arrow-right-to-bracket',
	      options: ['fat fa-arrow-right-to-bracket', 'fal fa-arrow-right-to-bracket', 'far fa-arrow-right-to-bracket', 'fas fa-arrow-right-to-bracket']
	    }, {
	      className: 'far fa-arrow-right-to-line',
	      options: ['fat fa-arrow-right-to-line', 'fal fa-arrow-right-to-line', 'far fa-arrow-right-to-line', 'fas fa-arrow-right-to-line']
	    }, {
	      className: 'far fa-arrow-rotate-left',
	      options: ['fat fa-arrow-rotate-left', 'fal fa-arrow-rotate-left', 'far fa-arrow-rotate-left', 'fas fa-arrow-rotate-left']
	    }, {
	      className: 'far fa-arrow-rotate-right',
	      options: ['fat fa-arrow-rotate-right', 'fal fa-arrow-rotate-right', 'far fa-arrow-rotate-right', 'fas fa-arrow-rotate-right']
	    }, {
	      className: 'far fa-arrow-trend-down',
	      options: ['fat fa-arrow-trend-down', 'fal fa-arrow-trend-down', 'far fa-arrow-trend-down', 'fas fa-arrow-trend-down']
	    }, {
	      className: 'far fa-arrow-trend-up',
	      options: ['fat fa-arrow-trend-up', 'fal fa-arrow-trend-up', 'far fa-arrow-trend-up', 'fas fa-arrow-trend-up']
	    }, {
	      className: 'far fa-arrow-turn-down',
	      options: ['fat fa-arrow-turn-down', 'fal fa-arrow-turn-down', 'far fa-arrow-turn-down', 'fas fa-arrow-turn-down']
	    }, {
	      className: 'far fa-arrow-turn-down-left',
	      options: ['fat fa-arrow-turn-down-left', 'fal fa-arrow-turn-down-left', 'far fa-arrow-turn-down-left', 'fas fa-arrow-turn-down-left']
	    }, {
	      className: 'far fa-arrow-turn-down-right',
	      options: ['fat fa-arrow-turn-down-right', 'fal fa-arrow-turn-down-right', 'far fa-arrow-turn-down-right', 'fas fa-arrow-turn-down-right']
	    }, {
	      className: 'far fa-arrow-turn-up',
	      options: ['fat fa-arrow-turn-up', 'fal fa-arrow-turn-up', 'far fa-arrow-turn-up', 'fas fa-arrow-turn-up']
	    }, {
	      className: 'far fa-arrow-up',
	      options: ['fat fa-arrow-up', 'fal fa-arrow-up', 'far fa-arrow-up', 'fas fa-arrow-up']
	    }, {
	      className: 'far fa-arrow-up-1-9',
	      options: ['fat fa-arrow-up-1-9', 'fal fa-arrow-up-1-9', 'far fa-arrow-up-1-9', 'fas fa-arrow-up-1-9']
	    }, {
	      className: 'far fa-arrow-up-9-1',
	      options: ['fat fa-arrow-up-9-1', 'fal fa-arrow-up-9-1', 'far fa-arrow-up-9-1', 'fas fa-arrow-up-9-1']
	    }, {
	      className: 'far fa-arrow-up-a-z',
	      options: ['fat fa-arrow-up-a-z', 'fal fa-arrow-up-a-z', 'far fa-arrow-up-a-z', 'fas fa-arrow-up-a-z']
	    }, {
	      className: 'far fa-arrow-up-arrow-down',
	      options: ['fat fa-arrow-up-arrow-down', 'fal fa-arrow-up-arrow-down', 'far fa-arrow-up-arrow-down', 'fas fa-arrow-up-arrow-down']
	    }, {
	      className: 'far fa-arrow-up-big-small',
	      options: ['fat fa-arrow-up-big-small', 'fal fa-arrow-up-big-small', 'far fa-arrow-up-big-small', 'fas fa-arrow-up-big-small']
	    }, {
	      className: 'far fa-arrow-up-from-bracket',
	      options: ['fat fa-arrow-up-from-bracket', 'fal fa-arrow-up-from-bracket', 'far fa-arrow-up-from-bracket', 'fas fa-arrow-up-from-bracket']
	    }, {
	      className: 'far fa-arrow-up-from-dotted-line',
	      options: ['fat fa-arrow-up-from-dotted-line', 'fal fa-arrow-up-from-dotted-line', 'far fa-arrow-up-from-dotted-line', 'fas fa-arrow-up-from-dotted-line']
	    }, {
	      className: 'far fa-arrow-up-from-line',
	      options: ['fat fa-arrow-up-from-line', 'fal fa-arrow-up-from-line', 'far fa-arrow-up-from-line', 'fas fa-arrow-up-from-line']
	    }, {
	      className: 'far fa-arrow-up-from-square',
	      options: ['fat fa-arrow-up-from-square', 'fal fa-arrow-up-from-square', 'far fa-arrow-up-from-square', 'fas fa-arrow-up-from-square']
	    }, {
	      className: 'far fa-arrow-up-left',
	      options: ['fat fa-arrow-up-left', 'fal fa-arrow-up-left', 'far fa-arrow-up-left', 'fas fa-arrow-up-left']
	    }, {
	      className: 'far fa-arrow-up-left-from-circle',
	      options: ['fat fa-arrow-up-left-from-circle', 'fal fa-arrow-up-left-from-circle', 'far fa-arrow-up-left-from-circle', 'fas fa-arrow-up-left-from-circle']
	    }, {
	      className: 'far fa-arrow-up-long',
	      options: ['fat fa-arrow-up-long', 'fal fa-arrow-up-long', 'far fa-arrow-up-long', 'fas fa-arrow-up-long']
	    }, {
	      className: 'far fa-arrow-up-right',
	      options: ['fat fa-arrow-up-right', 'fal fa-arrow-up-right', 'far fa-arrow-up-right', 'fas fa-arrow-up-right']
	    }, {
	      className: 'far fa-arrow-up-right-and-arrow-down-left-from-center',
	      options: ['fat fa-arrow-up-right-and-arrow-down-left-from-center', 'fal fa-arrow-up-right-and-arrow-down-left-from-center', 'far fa-arrow-up-right-and-arrow-down-left-from-center', 'fas fa-arrow-up-right-and-arrow-down-left-from-center']
	    }, {
	      className: 'far fa-arrow-up-right-from-square',
	      options: ['fat fa-arrow-up-right-from-square', 'fal fa-arrow-up-right-from-square', 'far fa-arrow-up-right-from-square', 'fas fa-arrow-up-right-from-square']
	    }, {
	      className: 'far fa-arrow-up-short-wide',
	      options: ['fat fa-arrow-up-short-wide', 'fal fa-arrow-up-short-wide', 'far fa-arrow-up-short-wide', 'fas fa-arrow-up-short-wide']
	    }, {
	      className: 'far fa-arrow-up-small-big',
	      options: ['fat fa-arrow-up-small-big', 'fal fa-arrow-up-small-big', 'far fa-arrow-up-small-big', 'fas fa-arrow-up-small-big']
	    }, {
	      className: 'far fa-arrow-up-square-triangle',
	      options: ['fat fa-arrow-up-square-triangle', 'fal fa-arrow-up-square-triangle', 'far fa-arrow-up-square-triangle', 'fas fa-arrow-up-square-triangle']
	    }, {
	      className: 'far fa-arrow-up-to-dotted-line',
	      options: ['fat fa-arrow-up-to-dotted-line', 'fal fa-arrow-up-to-dotted-line', 'far fa-arrow-up-to-dotted-line', 'fas fa-arrow-up-to-dotted-line']
	    }, {
	      className: 'far fa-arrow-up-to-line',
	      options: ['fat fa-arrow-up-to-line', 'fal fa-arrow-up-to-line', 'far fa-arrow-up-to-line', 'fas fa-arrow-up-to-line']
	    }, {
	      className: 'far fa-arrow-up-triangle-square',
	      options: ['fat fa-arrow-up-triangle-square', 'fal fa-arrow-up-triangle-square', 'far fa-arrow-up-triangle-square', 'fas fa-arrow-up-triangle-square']
	    }, {
	      className: 'far fa-arrow-up-wide-short',
	      options: ['fat fa-arrow-up-wide-short', 'fal fa-arrow-up-wide-short', 'far fa-arrow-up-wide-short', 'fas fa-arrow-up-wide-short']
	    }, {
	      className: 'far fa-arrow-up-z-a',
	      options: ['fat fa-arrow-up-z-a', 'fal fa-arrow-up-z-a', 'far fa-arrow-up-z-a', 'fas fa-arrow-up-z-a']
	    }, {
	      className: 'far fa-arrows-cross',
	      options: ['fat fa-arrows-cross', 'fal fa-arrows-cross', 'far fa-arrows-cross', 'fas fa-arrows-cross']
	    }, {
	      className: 'far fa-arrows-from-dotted-line',
	      options: ['fat fa-arrows-from-dotted-line', 'fal fa-arrows-from-dotted-line', 'far fa-arrows-from-dotted-line', 'fas fa-arrows-from-dotted-line']
	    }, {
	      className: 'far fa-arrows-from-line',
	      options: ['fat fa-arrows-from-line', 'fal fa-arrows-from-line', 'far fa-arrows-from-line', 'fas fa-arrows-from-line']
	    }, {
	      className: 'far fa-arrows-left-right',
	      options: ['fat fa-arrows-left-right', 'fal fa-arrows-left-right', 'far fa-arrows-left-right', 'fas fa-arrows-left-right']
	    }, {
	      className: 'far fa-arrows-maximize',
	      options: ['fat fa-arrows-maximize', 'fal fa-arrows-maximize', 'far fa-arrows-maximize', 'fas fa-arrows-maximize']
	    }, {
	      className: 'far fa-arrows-minimize',
	      options: ['fat fa-arrows-minimize', 'fal fa-arrows-minimize', 'far fa-arrows-minimize', 'fas fa-arrows-minimize']
	    }, {
	      className: 'far fa-arrows-repeat',
	      options: ['fat fa-arrows-repeat', 'fal fa-arrows-repeat', 'far fa-arrows-repeat', 'fas fa-arrows-repeat']
	    }, {
	      className: 'far fa-arrows-repeat-1',
	      options: ['fat fa-arrows-repeat-1', 'fal fa-arrows-repeat-1', 'far fa-arrows-repeat-1', 'fas fa-arrows-repeat-1']
	    }, {
	      className: 'far fa-arrows-retweet',
	      options: ['fat fa-arrows-retweet', 'fal fa-arrows-retweet', 'far fa-arrows-retweet', 'fas fa-arrows-retweet']
	    }, {
	      className: 'far fa-arrows-rotate',
	      options: ['fat fa-arrows-rotate', 'fal fa-arrows-rotate', 'far fa-arrows-rotate', 'fas fa-arrows-rotate']
	    }, {
	      className: 'far fa-arrows-to-dotted-line',
	      options: ['fat fa-arrows-to-dotted-line', 'fal fa-arrows-to-dotted-line', 'far fa-arrows-to-dotted-line', 'fas fa-arrows-to-dotted-line']
	    }, {
	      className: 'far fa-arrows-to-line',
	      options: ['fat fa-arrows-to-line', 'fal fa-arrows-to-line', 'far fa-arrows-to-line', 'fas fa-arrows-to-line']
	    }, {
	      className: 'far fa-arrows-up-down',
	      options: ['fat fa-arrows-up-down', 'fal fa-arrows-up-down', 'far fa-arrows-up-down', 'fas fa-arrows-up-down']
	    }, {
	      className: 'far fa-arrows-up-down-left-right',
	      options: ['fat fa-arrows-up-down-left-right', 'fal fa-arrows-up-down-left-right', 'far fa-arrows-up-down-left-right', 'fas fa-arrows-up-down-left-right']
	    }, {
	      className: 'far fa-caret-down',
	      options: ['fat fa-caret-down', 'fal fa-caret-down', 'far fa-caret-down', 'fas fa-caret-down']
	    }, {
	      className: 'far fa-caret-left',
	      options: ['fat fa-caret-left', 'fal fa-caret-left', 'far fa-caret-left', 'fas fa-caret-left']
	    }, {
	      className: 'far fa-caret-right',
	      options: ['fat fa-caret-right', 'fal fa-caret-right', 'far fa-caret-right', 'fas fa-caret-right']
	    }, {
	      className: 'far fa-caret-up',
	      options: ['fat fa-caret-up', 'fal fa-caret-up', 'far fa-caret-up', 'fas fa-caret-up']
	    }, {
	      className: 'far fa-chevron-down',
	      options: ['fat fa-chevron-down', 'fal fa-chevron-down', 'far fa-chevron-down', 'fas fa-chevron-down']
	    }, {
	      className: 'far fa-chevron-left',
	      options: ['fat fa-chevron-left', 'fal fa-chevron-left', 'far fa-chevron-left', 'fas fa-chevron-left']
	    }, {
	      className: 'far fa-chevron-right',
	      options: ['fat fa-chevron-right', 'fal fa-chevron-right', 'far fa-chevron-right', 'fas fa-chevron-right']
	    }, {
	      className: 'far fa-chevron-up',
	      options: ['fat fa-chevron-up', 'fal fa-chevron-up', 'far fa-chevron-up', 'fas fa-chevron-up']
	    }, {
	      className: 'far fa-chevrons-down',
	      options: ['fat fa-chevrons-down', 'fal fa-chevrons-down', 'far fa-chevrons-down', 'fas fa-chevrons-down']
	    }, {
	      className: 'far fa-chevrons-left',
	      options: ['fat fa-chevrons-left', 'fal fa-chevrons-left', 'far fa-chevrons-left', 'fas fa-chevrons-left']
	    }, {
	      className: 'far fa-chevrons-right',
	      options: ['fat fa-chevrons-right', 'fal fa-chevrons-right', 'far fa-chevrons-right', 'fas fa-chevrons-right']
	    }, {
	      className: 'far fa-chevrons-up',
	      options: ['fat fa-chevrons-up', 'fal fa-chevrons-up', 'far fa-chevrons-up', 'fas fa-chevrons-up']
	    }, {
	      className: 'far fa-circle-arrow-down',
	      options: ['fat fa-circle-arrow-down', 'fal fa-circle-arrow-down', 'far fa-circle-arrow-down', 'fas fa-circle-arrow-down']
	    }, {
	      className: 'far fa-circle-arrow-down-left',
	      options: ['fat fa-circle-arrow-down-left', 'fal fa-circle-arrow-down-left', 'far fa-circle-arrow-down-left', 'fas fa-circle-arrow-down-left']
	    }, {
	      className: 'far fa-circle-arrow-down-right',
	      options: ['fat fa-circle-arrow-down-right', 'fal fa-circle-arrow-down-right', 'far fa-circle-arrow-down-right', 'fas fa-circle-arrow-down-right']
	    }, {
	      className: 'far fa-circle-arrow-left',
	      options: ['fat fa-circle-arrow-left', 'fal fa-circle-arrow-left', 'far fa-circle-arrow-left', 'fas fa-circle-arrow-left']
	    }, {
	      className: 'far fa-circle-arrow-right',
	      options: ['fat fa-circle-arrow-right', 'fal fa-circle-arrow-right', 'far fa-circle-arrow-right', 'fas fa-circle-arrow-right']
	    }, {
	      className: 'far fa-circle-arrow-up',
	      options: ['fat fa-circle-arrow-up', 'fal fa-circle-arrow-up', 'far fa-circle-arrow-up', 'fas fa-circle-arrow-up']
	    }, {
	      className: 'far fa-circle-arrow-up-left',
	      options: ['fat fa-circle-arrow-up-left', 'fal fa-circle-arrow-up-left', 'far fa-circle-arrow-up-left', 'fas fa-circle-arrow-up-left']
	    }, {
	      className: 'far fa-circle-arrow-up-right',
	      options: ['fat fa-circle-arrow-up-right', 'fal fa-circle-arrow-up-right', 'far fa-circle-arrow-up-right', 'fas fa-circle-arrow-up-right']
	    }, {
	      className: 'far fa-circle-caret-down',
	      options: ['fat fa-circle-caret-down', 'fal fa-circle-caret-down', 'far fa-circle-caret-down', 'fas fa-circle-caret-down']
	    }, {
	      className: 'far fa-circle-caret-left',
	      options: ['fat fa-circle-caret-left', 'fal fa-circle-caret-left', 'far fa-circle-caret-left', 'fas fa-circle-caret-left']
	    }, {
	      className: 'far fa-circle-caret-right',
	      options: ['fat fa-circle-caret-right', 'fal fa-circle-caret-right', 'far fa-circle-caret-right', 'fas fa-circle-caret-right']
	    }, {
	      className: 'far fa-circle-caret-up',
	      options: ['fat fa-circle-caret-up', 'fal fa-circle-caret-up', 'far fa-circle-caret-up', 'fas fa-circle-caret-up']
	    }, {
	      className: 'far fa-circle-chevron-down',
	      options: ['fat fa-circle-chevron-down', 'fal fa-circle-chevron-down', 'far fa-circle-chevron-down', 'fas fa-circle-chevron-down']
	    }, {
	      className: 'far fa-circle-chevron-left',
	      options: ['fat fa-circle-chevron-left', 'fal fa-circle-chevron-left', 'far fa-circle-chevron-left', 'fas fa-circle-chevron-left']
	    }, {
	      className: 'far fa-circle-chevron-right',
	      options: ['fat fa-circle-chevron-right', 'fal fa-circle-chevron-right', 'far fa-circle-chevron-right', 'fas fa-circle-chevron-right']
	    }, {
	      className: 'far fa-circle-chevron-up',
	      options: ['fat fa-circle-chevron-up', 'fal fa-circle-chevron-up', 'far fa-circle-chevron-up', 'fas fa-circle-chevron-up']
	    }, {
	      className: 'far fa-circle-down',
	      options: ['fat fa-circle-down', 'fal fa-circle-down', 'far fa-circle-down', 'fas fa-circle-down']
	    }, {
	      className: 'far fa-circle-down-left',
	      options: ['fat fa-circle-down-left', 'fal fa-circle-down-left', 'far fa-circle-down-left', 'fas fa-circle-down-left']
	    }, {
	      className: 'far fa-circle-down-right',
	      options: ['fat fa-circle-down-right', 'fal fa-circle-down-right', 'far fa-circle-down-right', 'fas fa-circle-down-right']
	    }, {
	      className: 'far fa-circle-left',
	      options: ['fat fa-circle-left', 'fal fa-circle-left', 'far fa-circle-left', 'fas fa-circle-left']
	    }, {
	      className: 'far fa-circle-right',
	      options: ['fat fa-circle-right', 'fal fa-circle-right', 'far fa-circle-right', 'fas fa-circle-right']
	    }, {
	      className: 'far fa-circle-up',
	      options: ['fat fa-circle-up', 'fal fa-circle-up', 'far fa-circle-up', 'fas fa-circle-up']
	    }, {
	      className: 'far fa-circle-up-left',
	      options: ['fat fa-circle-up-left', 'fal fa-circle-up-left', 'far fa-circle-up-left', 'fas fa-circle-up-left']
	    }, {
	      className: 'far fa-circle-up-right',
	      options: ['fat fa-circle-up-right', 'fal fa-circle-up-right', 'far fa-circle-up-right', 'fas fa-circle-up-right']
	    }, {
	      className: 'far fa-clock-rotate-left',
	      options: ['fat fa-clock-rotate-left', 'fal fa-clock-rotate-left', 'far fa-clock-rotate-left', 'fas fa-clock-rotate-left']
	    }, {
	      className: 'far fa-cloud-arrow-down',
	      options: ['fat fa-cloud-arrow-down', 'fal fa-cloud-arrow-down', 'far fa-cloud-arrow-down', 'fas fa-cloud-arrow-down']
	    }, {
	      className: 'far fa-cloud-arrow-up',
	      options: ['fat fa-cloud-arrow-up', 'fal fa-cloud-arrow-up', 'far fa-cloud-arrow-up', 'fas fa-cloud-arrow-up']
	    }, {
	      className: 'far fa-down',
	      options: ['fat fa-down', 'fal fa-down', 'far fa-down', 'fas fa-down']
	    }, {
	      className: 'far fa-down-from-dotted-line',
	      options: ['fat fa-down-from-dotted-line', 'fal fa-down-from-dotted-line', 'far fa-down-from-dotted-line', 'fas fa-down-from-dotted-line']
	    }, {
	      className: 'far fa-down-from-line',
	      options: ['fat fa-down-from-line', 'fal fa-down-from-line', 'far fa-down-from-line', 'fas fa-down-from-line']
	    }, {
	      className: 'far fa-down-left',
	      options: ['fat fa-down-left', 'fal fa-down-left', 'far fa-down-left', 'fas fa-down-left']
	    }, {
	      className: 'far fa-down-left-and-up-right-to-center',
	      options: ['fat fa-down-left-and-up-right-to-center', 'fal fa-down-left-and-up-right-to-center', 'far fa-down-left-and-up-right-to-center', 'fas fa-down-left-and-up-right-to-center']
	    }, {
	      className: 'far fa-down-long',
	      options: ['fat fa-down-long', 'fal fa-down-long', 'far fa-down-long', 'fas fa-down-long']
	    }, {
	      className: 'far fa-down-right',
	      options: ['fat fa-down-right', 'fal fa-down-right', 'far fa-down-right', 'fas fa-down-right']
	    }, {
	      className: 'far fa-down-to-dotted-line',
	      options: ['fat fa-down-to-dotted-line', 'fal fa-down-to-dotted-line', 'far fa-down-to-dotted-line', 'fas fa-down-to-dotted-line']
	    }, {
	      className: 'far fa-down-to-line',
	      options: ['fat fa-down-to-line', 'fal fa-down-to-line', 'far fa-down-to-line', 'fas fa-down-to-line']
	    }, {
	      className: 'far fa-download',
	      options: ['fat fa-download', 'fal fa-download', 'far fa-download', 'fas fa-download']
	    }, {
	      className: 'far fa-inbox-in',
	      options: ['fat fa-inbox-in', 'fal fa-inbox-in', 'far fa-inbox-in', 'fas fa-inbox-in']
	    }, {
	      className: 'far fa-inbox-out',
	      options: ['fat fa-inbox-out', 'fal fa-inbox-out', 'far fa-inbox-out', 'fas fa-inbox-out']
	    }, {
	      className: 'far fa-left',
	      options: ['fat fa-left', 'fal fa-left', 'far fa-left', 'fas fa-left']
	    }, {
	      className: 'far fa-left-from-line',
	      options: ['fat fa-left-from-line', 'fal fa-left-from-line', 'far fa-left-from-line', 'fas fa-left-from-line']
	    }, {
	      className: 'far fa-left-long',
	      options: ['fat fa-left-long', 'fal fa-left-long', 'far fa-left-long', 'fas fa-left-long']
	    }, {
	      className: 'far fa-left-long-to-line',
	      options: ['fat fa-left-long-to-line', 'fal fa-left-long-to-line', 'far fa-left-long-to-line', 'fas fa-left-long-to-line']
	    }, {
	      className: 'far fa-left-right',
	      options: ['fat fa-left-right', 'fal fa-left-right', 'far fa-left-right', 'fas fa-left-right']
	    }, {
	      className: 'far fa-left-to-line',
	      options: ['fat fa-left-to-line', 'fal fa-left-to-line', 'far fa-left-to-line', 'fas fa-left-to-line']
	    }, {
	      className: 'far fa-location-arrow',
	      options: ['fat fa-location-arrow', 'fal fa-location-arrow', 'far fa-location-arrow', 'fas fa-location-arrow']
	    }, {
	      className: 'far fa-maximize',
	      options: ['fat fa-maximize', 'fal fa-maximize', 'far fa-maximize', 'fas fa-maximize']
	    }, {
	      className: 'far fa-recycle',
	      options: ['fat fa-recycle', 'fal fa-recycle', 'far fa-recycle', 'fas fa-recycle']
	    }, {
	      className: 'far fa-repeat',
	      options: ['fat fa-repeat', 'fal fa-repeat', 'far fa-repeat', 'fas fa-repeat']
	    }, {
	      className: 'far fa-repeat-1',
	      options: ['fat fa-repeat-1', 'fal fa-repeat-1', 'far fa-repeat-1', 'fas fa-repeat-1']
	    }, {
	      className: 'far fa-reply',
	      options: ['fat fa-reply', 'fal fa-reply', 'far fa-reply', 'fas fa-reply']
	    }, {
	      className: 'far fa-reply-all',
	      options: ['fat fa-reply-all', 'fal fa-reply-all', 'far fa-reply-all', 'fas fa-reply-all']
	    }, {
	      className: 'far fa-retweet',
	      options: ['fat fa-retweet', 'fal fa-retweet', 'far fa-retweet', 'fas fa-retweet']
	    }, {
	      className: 'far fa-right',
	      options: ['fat fa-right', 'fal fa-right', 'far fa-right', 'fas fa-right']
	    }, {
	      className: 'far fa-right-from-bracket',
	      options: ['fat fa-right-from-bracket', 'fal fa-right-from-bracket', 'far fa-right-from-bracket', 'fas fa-right-from-bracket']
	    }, {
	      className: 'far fa-right-from-line',
	      options: ['fat fa-right-from-line', 'fal fa-right-from-line', 'far fa-right-from-line', 'fas fa-right-from-line']
	    }, {
	      className: 'far fa-right-left',
	      options: ['fat fa-right-left', 'fal fa-right-left', 'far fa-right-left', 'fas fa-right-left']
	    }, {
	      className: 'far fa-right-long',
	      options: ['fat fa-right-long', 'fal fa-right-long', 'far fa-right-long', 'fas fa-right-long']
	    }, {
	      className: 'far fa-right-long-to-line',
	      options: ['fat fa-right-long-to-line', 'fal fa-right-long-to-line', 'far fa-right-long-to-line', 'fas fa-right-long-to-line']
	    }, {
	      className: 'far fa-right-to-bracket',
	      options: ['fat fa-right-to-bracket', 'fal fa-right-to-bracket', 'far fa-right-to-bracket', 'fas fa-right-to-bracket']
	    }, {
	      className: 'far fa-right-to-line',
	      options: ['fat fa-right-to-line', 'fal fa-right-to-line', 'far fa-right-to-line', 'fas fa-right-to-line']
	    }, {
	      className: 'far fa-rotate',
	      options: ['fat fa-rotate', 'fal fa-rotate', 'far fa-rotate', 'fas fa-rotate']
	    }, {
	      className: 'far fa-rotate-left',
	      options: ['fat fa-rotate-left', 'fal fa-rotate-left', 'far fa-rotate-left', 'fas fa-rotate-left']
	    }, {
	      className: 'far fa-rotate-right',
	      options: ['fat fa-rotate-right', 'fal fa-rotate-right', 'far fa-rotate-right', 'fas fa-rotate-right']
	    }, {
	      className: 'far fa-share',
	      options: ['fat fa-share', 'fal fa-share', 'far fa-share', 'fas fa-share']
	    }, {
	      className: 'far fa-share-all',
	      options: ['fat fa-share-all', 'fal fa-share-all', 'far fa-share-all', 'fas fa-share-all']
	    }, {
	      className: 'far fa-share-from-square',
	      options: ['fat fa-share-from-square', 'fal fa-share-from-square', 'far fa-share-from-square', 'fas fa-share-from-square']
	    }, {
	      className: 'far fa-shuffle',
	      options: ['fat fa-shuffle', 'fal fa-shuffle', 'far fa-shuffle', 'fas fa-shuffle']
	    }, {
	      className: 'far fa-sort',
	      options: ['fat fa-sort', 'fal fa-sort', 'far fa-sort', 'fas fa-sort']
	    }, {
	      className: 'far fa-sort-down',
	      options: ['fat fa-sort-down', 'fal fa-sort-down', 'far fa-sort-down', 'fas fa-sort-down']
	    }, {
	      className: 'far fa-sort-up',
	      options: ['fat fa-sort-up', 'fal fa-sort-up', 'far fa-sort-up', 'fas fa-sort-up']
	    }, {
	      className: 'far fa-split',
	      options: ['fat fa-split', 'fal fa-split', 'far fa-split', 'fas fa-split']
	    }, {
	      className: 'far fa-square-arrow-down',
	      options: ['fat fa-square-arrow-down', 'fal fa-square-arrow-down', 'far fa-square-arrow-down', 'fas fa-square-arrow-down']
	    }, {
	      className: 'far fa-square-arrow-down-left',
	      options: ['fat fa-square-arrow-down-left', 'fal fa-square-arrow-down-left', 'far fa-square-arrow-down-left', 'fas fa-square-arrow-down-left']
	    }, {
	      className: 'far fa-square-arrow-down-right',
	      options: ['fat fa-square-arrow-down-right', 'fal fa-square-arrow-down-right', 'far fa-square-arrow-down-right', 'fas fa-square-arrow-down-right']
	    }, {
	      className: 'far fa-square-arrow-left',
	      options: ['fat fa-square-arrow-left', 'fal fa-square-arrow-left', 'far fa-square-arrow-left', 'fas fa-square-arrow-left']
	    }, {
	      className: 'far fa-square-arrow-right',
	      options: ['fat fa-square-arrow-right', 'fal fa-square-arrow-right', 'far fa-square-arrow-right', 'fas fa-square-arrow-right']
	    }, {
	      className: 'far fa-square-arrow-up',
	      options: ['fat fa-square-arrow-up', 'fal fa-square-arrow-up', 'far fa-square-arrow-up', 'fas fa-square-arrow-up']
	    }, {
	      className: 'far fa-square-arrow-up-left',
	      options: ['fat fa-square-arrow-up-left', 'fal fa-square-arrow-up-left', 'far fa-square-arrow-up-left', 'fas fa-square-arrow-up-left']
	    }, {
	      className: 'far fa-square-arrow-up-right',
	      options: ['fat fa-square-arrow-up-right', 'fal fa-square-arrow-up-right', 'far fa-square-arrow-up-right', 'fas fa-square-arrow-up-right']
	    }, {
	      className: 'far fa-square-caret-down',
	      options: ['fat fa-square-caret-down', 'fal fa-square-caret-down', 'far fa-square-caret-down', 'fas fa-square-caret-down']
	    }, {
	      className: 'far fa-square-caret-left',
	      options: ['fat fa-square-caret-left', 'fal fa-square-caret-left', 'far fa-square-caret-left', 'fas fa-square-caret-left']
	    }, {
	      className: 'far fa-square-caret-right',
	      options: ['fat fa-square-caret-right', 'fal fa-square-caret-right', 'far fa-square-caret-right', 'fas fa-square-caret-right']
	    }, {
	      className: 'far fa-square-caret-up',
	      options: ['fat fa-square-caret-up', 'fal fa-square-caret-up', 'far fa-square-caret-up', 'fas fa-square-caret-up']
	    }, {
	      className: 'far fa-square-chevron-down',
	      options: ['fat fa-square-chevron-down', 'fal fa-square-chevron-down', 'far fa-square-chevron-down', 'fas fa-square-chevron-down']
	    }, {
	      className: 'far fa-square-chevron-left',
	      options: ['fat fa-square-chevron-left', 'fal fa-square-chevron-left', 'far fa-square-chevron-left', 'fas fa-square-chevron-left']
	    }, {
	      className: 'far fa-square-chevron-right',
	      options: ['fat fa-square-chevron-right', 'fal fa-square-chevron-right', 'far fa-square-chevron-right', 'fas fa-square-chevron-right']
	    }, {
	      className: 'far fa-square-chevron-up',
	      options: ['fat fa-square-chevron-up', 'fal fa-square-chevron-up', 'far fa-square-chevron-up', 'fas fa-square-chevron-up']
	    }, {
	      className: 'far fa-square-down',
	      options: ['fat fa-square-down', 'fal fa-square-down', 'far fa-square-down', 'fas fa-square-down']
	    }, {
	      className: 'far fa-square-down-left',
	      options: ['fat fa-square-down-left', 'fal fa-square-down-left', 'far fa-square-down-left', 'fas fa-square-down-left']
	    }, {
	      className: 'far fa-square-down-right',
	      options: ['fat fa-square-down-right', 'fal fa-square-down-right', 'far fa-square-down-right', 'fas fa-square-down-right']
	    }, {
	      className: 'far fa-square-left',
	      options: ['fat fa-square-left', 'fal fa-square-left', 'far fa-square-left', 'fas fa-square-left']
	    }, {
	      className: 'far fa-square-right',
	      options: ['fat fa-square-right', 'fal fa-square-right', 'far fa-square-right', 'fas fa-square-right']
	    }, {
	      className: 'far fa-square-up',
	      options: ['fat fa-square-up', 'fal fa-square-up', 'far fa-square-up', 'fas fa-square-up']
	    }, {
	      className: 'far fa-square-up-left',
	      options: ['fat fa-square-up-left', 'fal fa-square-up-left', 'far fa-square-up-left', 'fas fa-square-up-left']
	    }, {
	      className: 'far fa-square-up-right',
	      options: ['fat fa-square-up-right', 'fal fa-square-up-right', 'far fa-square-up-right', 'fas fa-square-up-right']
	    }, {
	      className: 'far fa-turn-down',
	      options: ['fat fa-turn-down', 'fal fa-turn-down', 'far fa-turn-down', 'fas fa-turn-down']
	    }, {
	      className: 'far fa-turn-down-left',
	      options: ['fat fa-turn-down-left', 'fal fa-turn-down-left', 'far fa-turn-down-left', 'fas fa-turn-down-left']
	    }, {
	      className: 'far fa-turn-down-right',
	      options: ['fat fa-turn-down-right', 'fal fa-turn-down-right', 'far fa-turn-down-right', 'fas fa-turn-down-right']
	    }, {
	      className: 'far fa-turn-up',
	      options: ['fat fa-turn-up', 'fal fa-turn-up', 'far fa-turn-up', 'fas fa-turn-up']
	    }, {
	      className: 'far fa-up',
	      options: ['fat fa-up', 'fal fa-up', 'far fa-up', 'fas fa-up']
	    }, {
	      className: 'far fa-up-down',
	      options: ['fat fa-up-down', 'fal fa-up-down', 'far fa-up-down', 'fas fa-up-down']
	    }, {
	      className: 'far fa-up-down-left-right',
	      options: ['fat fa-up-down-left-right', 'fal fa-up-down-left-right', 'far fa-up-down-left-right', 'fas fa-up-down-left-right']
	    }, {
	      className: 'far fa-up-from-dotted-line',
	      options: ['fat fa-up-from-dotted-line', 'fal fa-up-from-dotted-line', 'far fa-up-from-dotted-line', 'fas fa-up-from-dotted-line']
	    }, {
	      className: 'far fa-up-from-line',
	      options: ['fat fa-up-from-line', 'fal fa-up-from-line', 'far fa-up-from-line', 'fas fa-up-from-line']
	    }, {
	      className: 'far fa-up-left',
	      options: ['fat fa-up-left', 'fal fa-up-left', 'far fa-up-left', 'fas fa-up-left']
	    }, {
	      className: 'far fa-up-long',
	      options: ['fat fa-up-long', 'fal fa-up-long', 'far fa-up-long', 'fas fa-up-long']
	    }, {
	      className: 'far fa-up-right',
	      options: ['fat fa-up-right', 'fal fa-up-right', 'far fa-up-right', 'fas fa-up-right']
	    }, {
	      className: 'far fa-up-right-and-down-left-from-center',
	      options: ['fat fa-up-right-and-down-left-from-center', 'fal fa-up-right-and-down-left-from-center', 'far fa-up-right-and-down-left-from-center', 'fas fa-up-right-and-down-left-from-center']
	    }, {
	      className: 'far fa-up-right-from-square',
	      options: ['fat fa-up-right-from-square', 'fal fa-up-right-from-square', 'far fa-up-right-from-square', 'fas fa-up-right-from-square']
	    }, {
	      className: 'far fa-up-to-dotted-line',
	      options: ['fat fa-up-to-dotted-line', 'fal fa-up-to-dotted-line', 'far fa-up-to-dotted-line', 'fas fa-up-to-dotted-line']
	    }, {
	      className: 'far fa-up-to-line',
	      options: ['fat fa-up-to-line', 'fal fa-up-to-line', 'far fa-up-to-line', 'fas fa-up-to-line']
	    }, {
	      className: 'far fa-upload',
	      options: ['fat fa-upload', 'fal fa-upload', 'far fa-upload', 'fas fa-upload']
	    }]
	  }, {
	    id: 'astronomy',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_ASTRONOMY'),
	    items: [{
	      className: 'far fa-alien',
	      options: ['fat fa-alien', 'fal fa-alien', 'far fa-alien', 'fas fa-alien']
	    }, {
	      className: 'far fa-cat-space',
	      options: ['fat fa-cat-space', 'fal fa-cat-space', 'far fa-cat-space', 'fas fa-cat-space']
	    }, {
	      className: 'far fa-comet',
	      options: ['fat fa-comet', 'fal fa-comet', 'far fa-comet', 'fas fa-comet']
	    }, {
	      className: 'far fa-eclipse',
	      options: ['fat fa-eclipse', 'fal fa-eclipse', 'far fa-eclipse', 'fas fa-eclipse']
	    }, {
	      className: 'far fa-galaxy',
	      options: ['fat fa-galaxy', 'fal fa-galaxy', 'far fa-galaxy', 'fas fa-galaxy']
	    }, {
	      className: 'far fa-globe',
	      options: ['fat fa-globe', 'fal fa-globe', 'far fa-globe', 'fas fa-globe']
	    }, {
	      className: 'far fa-meteor',
	      options: ['fat fa-meteor', 'fal fa-meteor', 'far fa-meteor', 'fas fa-meteor']
	    }, {
	      className: 'far fa-moon',
	      options: ['fat fa-moon', 'fal fa-moon', 'far fa-moon', 'fas fa-moon']
	    }, {
	      className: 'far fa-moon-over-sun',
	      options: ['fat fa-moon-over-sun', 'fal fa-moon-over-sun', 'far fa-moon-over-sun', 'fas fa-moon-over-sun']
	    }, {
	      className: 'far fa-moon-stars',
	      options: ['fat fa-moon-stars', 'fal fa-moon-stars', 'far fa-moon-stars', 'fas fa-moon-stars']
	    }, {
	      className: 'far fa-planet-moon',
	      options: ['fat fa-planet-moon', 'fal fa-planet-moon', 'far fa-planet-moon', 'fas fa-planet-moon']
	    }, {
	      className: 'far fa-planet-ringed',
	      options: ['fat fa-planet-ringed', 'fal fa-planet-ringed', 'far fa-planet-ringed', 'fas fa-planet-ringed']
	    }, {
	      className: 'far fa-radar',
	      options: ['fat fa-radar', 'fal fa-radar', 'far fa-radar', 'fas fa-radar']
	    }, {
	      className: 'far fa-satellite',
	      options: ['fat fa-satellite', 'fal fa-satellite', 'far fa-satellite', 'fas fa-satellite']
	    }, {
	      className: 'far fa-satellite-dish',
	      options: ['fat fa-satellite-dish', 'fal fa-satellite-dish', 'far fa-satellite-dish', 'fas fa-satellite-dish']
	    }, {
	      className: 'far fa-shuttle-space',
	      options: ['fat fa-shuttle-space', 'fal fa-shuttle-space', 'far fa-shuttle-space', 'fas fa-shuttle-space']
	    }, {
	      className: 'far fa-solar-system',
	      options: ['fat fa-solar-system', 'fal fa-solar-system', 'far fa-solar-system', 'fas fa-solar-system']
	    }, {
	      className: 'far fa-star-shooting',
	      options: ['fat fa-star-shooting', 'fal fa-star-shooting', 'far fa-star-shooting', 'fas fa-star-shooting']
	    }, {
	      className: 'far fa-stars',
	      options: ['fat fa-stars', 'fal fa-stars', 'far fa-stars', 'fas fa-stars']
	    }, {
	      className: 'far fa-telescope',
	      options: ['fat fa-telescope', 'fal fa-telescope', 'far fa-telescope', 'fas fa-telescope']
	    }, {
	      className: 'far fa-ufo',
	      options: ['fat fa-ufo', 'fal fa-ufo', 'far fa-ufo', 'fas fa-ufo']
	    }, {
	      className: 'far fa-ufo-beam',
	      options: ['fat fa-ufo-beam', 'fal fa-ufo-beam', 'far fa-ufo-beam', 'fas fa-ufo-beam']
	    }, {
	      className: 'far fa-user-alien',
	      options: ['fat fa-user-alien', 'fal fa-user-alien', 'far fa-user-alien', 'fas fa-user-alien']
	    }, {
	      className: 'far fa-user-astronaut',
	      options: ['fat fa-user-astronaut', 'fal fa-user-astronaut', 'far fa-user-astronaut', 'fas fa-user-astronaut']
	    }]
	  }, {
	    id: 'automotive',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_AUTOMOTIVE'),
	    items: [{
	      className: 'far fa-brake-warning',
	      options: ['fat fa-brake-warning', 'fal fa-brake-warning', 'far fa-brake-warning', 'fas fa-brake-warning']
	    }, {
	      className: 'far fa-bus',
	      options: ['fat fa-bus', 'fal fa-bus', 'far fa-bus', 'fas fa-bus']
	    }, {
	      className: 'far fa-bus-simple',
	      options: ['fat fa-bus-simple', 'fal fa-bus-simple', 'far fa-bus-simple', 'fas fa-bus-simple']
	    }, {
	      className: 'far fa-car',
	      options: ['fat fa-car', 'fal fa-car', 'far fa-car', 'fas fa-car']
	    }, {
	      className: 'far fa-car-battery',
	      options: ['fat fa-car-battery', 'fal fa-car-battery', 'far fa-car-battery', 'fas fa-car-battery']
	    }, {
	      className: 'far fa-car-bolt',
	      options: ['fat fa-car-bolt', 'fal fa-car-bolt', 'far fa-car-bolt', 'fas fa-car-bolt']
	    }, {
	      className: 'far fa-car-building',
	      options: ['fat fa-car-building', 'fal fa-car-building', 'far fa-car-building', 'fas fa-car-building']
	    }, {
	      className: 'far fa-car-bump',
	      options: ['fat fa-car-bump', 'fal fa-car-bump', 'far fa-car-bump', 'fas fa-car-bump']
	    }, {
	      className: 'far fa-car-bus',
	      options: ['fat fa-car-bus', 'fal fa-car-bus', 'far fa-car-bus', 'fas fa-car-bus']
	    }, {
	      className: 'far fa-car-circle-bolt',
	      options: ['fat fa-car-circle-bolt', 'fal fa-car-circle-bolt', 'far fa-car-circle-bolt', 'fas fa-car-circle-bolt']
	    }, {
	      className: 'far fa-car-crash',
	      options: ['fat fa-car-crash', 'fal fa-car-crash', 'far fa-car-crash', 'fas fa-car-crash']
	    }, {
	      className: 'far fa-car-garage',
	      options: ['fat fa-car-garage', 'fal fa-car-garage', 'far fa-car-garage', 'fas fa-car-garage']
	    }, {
	      className: 'far fa-car-mirrors',
	      options: ['fat fa-car-mirrors', 'fal fa-car-mirrors', 'far fa-car-mirrors', 'fas fa-car-mirrors']
	    }, {
	      className: 'far fa-car-rear',
	      options: ['fat fa-car-rear', 'fal fa-car-rear', 'far fa-car-rear', 'fas fa-car-rear']
	    }, {
	      className: 'far fa-car-side',
	      options: ['fat fa-car-side', 'fal fa-car-side', 'far fa-car-side', 'fas fa-car-side']
	    }, {
	      className: 'far fa-car-side-bolt',
	      options: ['fat fa-car-side-bolt', 'fal fa-car-side-bolt', 'far fa-car-side-bolt', 'fas fa-car-side-bolt']
	    }, {
	      className: 'far fa-car-tilt',
	      options: ['fat fa-car-tilt', 'fal fa-car-tilt', 'far fa-car-tilt', 'fas fa-car-tilt']
	    }, {
	      className: 'far fa-car-wash',
	      options: ['fat fa-car-wash', 'fal fa-car-wash', 'far fa-car-wash', 'fas fa-car-wash']
	    }, {
	      className: 'far fa-car-wrench',
	      options: ['fat fa-car-wrench', 'fal fa-car-wrench', 'far fa-car-wrench', 'fas fa-car-wrench']
	    }, {
	      className: 'far fa-caravan',
	      options: ['fat fa-caravan', 'fal fa-caravan', 'far fa-caravan', 'fas fa-caravan']
	    }, {
	      className: 'far fa-caravan-simple',
	      options: ['fat fa-caravan-simple', 'fal fa-caravan-simple', 'far fa-caravan-simple', 'fas fa-caravan-simple']
	    }, {
	      className: 'far fa-cars',
	      options: ['fat fa-cars', 'fal fa-cars', 'far fa-cars', 'fas fa-cars']
	    }, {
	      className: 'far fa-charging-station',
	      options: ['fat fa-charging-station', 'fal fa-charging-station', 'far fa-charging-station', 'fas fa-charging-station']
	    }, {
	      className: 'far fa-engine',
	      options: ['fat fa-engine', 'fal fa-engine', 'far fa-engine', 'fas fa-engine']
	    }, {
	      className: 'far fa-engine-warning',
	      options: ['fat fa-engine-warning', 'fal fa-engine-warning', 'far fa-engine-warning', 'fas fa-engine-warning']
	    }, {
	      className: 'far fa-flux-capacitor',
	      options: ['fat fa-flux-capacitor', 'fal fa-flux-capacitor', 'far fa-flux-capacitor', 'fas fa-flux-capacitor']
	    }, {
	      className: 'far fa-garage',
	      options: ['fat fa-garage', 'fal fa-garage', 'far fa-garage', 'fas fa-garage']
	    }, {
	      className: 'far fa-garage-car',
	      options: ['fat fa-garage-car', 'fal fa-garage-car', 'far fa-garage-car', 'fas fa-garage-car']
	    }, {
	      className: 'far fa-garage-open',
	      options: ['fat fa-garage-open', 'fal fa-garage-open', 'far fa-garage-open', 'fas fa-garage-open']
	    }, {
	      className: 'far fa-gas-pump',
	      options: ['fat fa-gas-pump', 'fal fa-gas-pump', 'far fa-gas-pump', 'fas fa-gas-pump']
	    }, {
	      className: 'far fa-gas-pump-slash',
	      options: ['fat fa-gas-pump-slash', 'fal fa-gas-pump-slash', 'far fa-gas-pump-slash', 'fas fa-gas-pump-slash']
	    }, {
	      className: 'far fa-gauge',
	      options: ['fat fa-gauge', 'fal fa-gauge', 'far fa-gauge', 'fas fa-gauge']
	    }, {
	      className: 'far fa-gauge-low',
	      options: ['fat fa-gauge-low', 'fal fa-gauge-low', 'far fa-gauge-low', 'fas fa-gauge-low']
	    }, {
	      className: 'far fa-gauge-max',
	      options: ['fat fa-gauge-max', 'fal fa-gauge-max', 'far fa-gauge-max', 'fas fa-gauge-max']
	    }, {
	      className: 'far fa-gauge-med',
	      options: ['fat fa-gauge-med', 'fal fa-gauge-med', 'far fa-gauge-med', 'fas fa-gauge-med']
	    }, {
	      className: 'far fa-gauge-min',
	      options: ['fat fa-gauge-min', 'fal fa-gauge-min', 'far fa-gauge-min', 'fas fa-gauge-min']
	    }, {
	      className: 'far fa-gauge-simple',
	      options: ['fat fa-gauge-simple', 'fal fa-gauge-simple', 'far fa-gauge-simple', 'fas fa-gauge-simple']
	    }, {
	      className: 'far fa-gauge-simple-low',
	      options: ['fat fa-gauge-simple-low', 'fal fa-gauge-simple-low', 'far fa-gauge-simple-low', 'fas fa-gauge-simple-low']
	    }, {
	      className: 'far fa-gauge-simple-max',
	      options: ['fat fa-gauge-simple-max', 'fal fa-gauge-simple-max', 'far fa-gauge-simple-max', 'fas fa-gauge-simple-max']
	    }, {
	      className: 'far fa-gauge-simple-med',
	      options: ['fat fa-gauge-simple-med', 'fal fa-gauge-simple-med', 'far fa-gauge-simple-med', 'fas fa-gauge-simple-med']
	    }, {
	      className: 'far fa-gauge-simple-min',
	      options: ['fat fa-gauge-simple-min', 'fal fa-gauge-simple-min', 'far fa-gauge-simple-min', 'fas fa-gauge-simple-min']
	    }, {
	      className: 'far fa-moped',
	      options: ['fat fa-moped', 'fal fa-moped', 'far fa-moped', 'fas fa-moped']
	    }, {
	      className: 'far fa-motorcycle',
	      options: ['fat fa-motorcycle', 'fal fa-motorcycle', 'far fa-motorcycle', 'fas fa-motorcycle']
	    }, {
	      className: 'far fa-oil-can',
	      options: ['fat fa-oil-can', 'fal fa-oil-can', 'far fa-oil-can', 'fas fa-oil-can']
	    }, {
	      className: 'far fa-oil-can-drip',
	      options: ['fat fa-oil-can-drip', 'fal fa-oil-can-drip', 'far fa-oil-can-drip', 'fas fa-oil-can-drip']
	    }, {
	      className: 'far fa-oil-temperature',
	      options: ['fat fa-oil-temperature', 'fal fa-oil-temperature', 'far fa-oil-temperature', 'fas fa-oil-temperature']
	    }, {
	      className: 'far fa-pump',
	      options: ['fat fa-pump', 'fal fa-pump', 'far fa-pump', 'fas fa-pump']
	    }, {
	      className: 'far fa-rv',
	      options: ['fat fa-rv', 'fal fa-rv', 'far fa-rv', 'fas fa-rv']
	    }, {
	      className: 'far fa-spray-can-sparkles',
	      options: ['fat fa-spray-can-sparkles', 'fal fa-spray-can-sparkles', 'far fa-spray-can-sparkles', 'fas fa-spray-can-sparkles']
	    }, {
	      className: 'far fa-steering-wheel',
	      options: ['fat fa-steering-wheel', 'fal fa-steering-wheel', 'far fa-steering-wheel', 'fas fa-steering-wheel']
	    }, {
	      className: 'far fa-tank-water',
	      options: ['fat fa-tank-water', 'fal fa-tank-water', 'far fa-tank-water', 'fas fa-tank-water']
	    }, {
	      className: 'far fa-taxi',
	      options: ['fat fa-taxi', 'fal fa-taxi', 'far fa-taxi', 'fas fa-taxi']
	    }, {
	      className: 'far fa-tire',
	      options: ['fat fa-tire', 'fal fa-tire', 'far fa-tire', 'fas fa-tire']
	    }, {
	      className: 'far fa-tire-flat',
	      options: ['fat fa-tire-flat', 'fal fa-tire-flat', 'far fa-tire-flat', 'fas fa-tire-flat']
	    }, {
	      className: 'far fa-tire-pressure-warning',
	      options: ['fat fa-tire-pressure-warning', 'fal fa-tire-pressure-warning', 'far fa-tire-pressure-warning', 'fas fa-tire-pressure-warning']
	    }, {
	      className: 'far fa-tire-rugged',
	      options: ['fat fa-tire-rugged', 'fal fa-tire-rugged', 'far fa-tire-rugged', 'fas fa-tire-rugged']
	    }, {
	      className: 'far fa-trailer',
	      options: ['fat fa-trailer', 'fal fa-trailer', 'far fa-trailer', 'fas fa-trailer']
	    }, {
	      className: 'far fa-truck',
	      options: ['fat fa-truck', 'fal fa-truck', 'far fa-truck', 'fas fa-truck']
	    }, {
	      className: 'far fa-truck-bolt',
	      options: ['fat fa-truck-bolt', 'fal fa-truck-bolt', 'far fa-truck-bolt', 'fas fa-truck-bolt']
	    }, {
	      className: 'far fa-truck-medical',
	      options: ['fat fa-truck-medical', 'fal fa-truck-medical', 'far fa-truck-medical', 'fas fa-truck-medical']
	    }, {
	      className: 'far fa-truck-monster',
	      options: ['fat fa-truck-monster', 'fal fa-truck-monster', 'far fa-truck-monster', 'fas fa-truck-monster']
	    }, {
	      className: 'far fa-truck-pickup',
	      options: ['fat fa-truck-pickup', 'fal fa-truck-pickup', 'far fa-truck-pickup', 'fas fa-truck-pickup']
	    }, {
	      className: 'far fa-van-shuttle',
	      options: ['fat fa-van-shuttle', 'fal fa-van-shuttle', 'far fa-van-shuttle', 'fas fa-van-shuttle']
	    }, {
	      className: 'far fa-wagon-covered',
	      options: ['fat fa-wagon-covered', 'fal fa-wagon-covered', 'far fa-wagon-covered', 'fas fa-wagon-covered']
	    }]
	  }, {
	    id: 'buildings',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_BUILDINGS'),
	    items: [{
	      className: 'far fa-archway',
	      options: ['fat fa-archway', 'fal fa-archway', 'far fa-archway', 'fas fa-archway']
	    }, {
	      className: 'far fa-bank',
	      options: ['fat fa-bank', 'fal fa-bank', 'far fa-bank', 'fas fa-bank']
	    }, {
	      className: 'far fa-building',
	      options: ['fat fa-building', 'fal fa-building', 'far fa-building', 'fas fa-building']
	    }, {
	      className: 'far fa-buildings',
	      options: ['fat fa-buildings', 'fal fa-buildings', 'far fa-buildings', 'fas fa-buildings']
	    }, {
	      className: 'far fa-campground',
	      options: ['fat fa-campground', 'fal fa-campground', 'far fa-campground', 'fas fa-campground']
	    }, {
	      className: 'far fa-car-building',
	      options: ['fat fa-car-building', 'fal fa-car-building', 'far fa-car-building', 'fas fa-car-building']
	    }, {
	      className: 'far fa-castle',
	      options: ['fat fa-castle', 'fal fa-castle', 'far fa-castle', 'fas fa-castle']
	    }, {
	      className: 'far fa-chimney',
	      options: ['fat fa-chimney', 'fal fa-chimney', 'far fa-chimney', 'fas fa-chimney']
	    }, {
	      className: 'far fa-church',
	      options: ['fat fa-church', 'fal fa-church', 'far fa-church', 'fas fa-church']
	    }, {
	      className: 'far fa-city',
	      options: ['fat fa-city', 'fal fa-city', 'far fa-city', 'fas fa-city']
	    }, {
	      className: 'far fa-container-storage',
	      options: ['fat fa-container-storage', 'fal fa-container-storage', 'far fa-container-storage', 'fas fa-container-storage']
	    }, {
	      className: 'far fa-dungeon',
	      options: ['fat fa-dungeon', 'fal fa-dungeon', 'far fa-dungeon', 'fas fa-dungeon']
	    }, {
	      className: 'far fa-farm',
	      options: ['fat fa-farm', 'fal fa-farm', 'far fa-farm', 'fas fa-farm']
	    }, {
	      className: 'far fa-fence',
	      options: ['fat fa-fence', 'fal fa-fence', 'far fa-fence', 'fas fa-fence']
	    }, {
	      className: 'far fa-ferris-wheel',
	      options: ['fat fa-ferris-wheel', 'fal fa-ferris-wheel', 'far fa-ferris-wheel', 'fas fa-ferris-wheel']
	    }, {
	      className: 'far fa-garage',
	      options: ['fat fa-garage', 'fal fa-garage', 'far fa-garage', 'fas fa-garage']
	    }, {
	      className: 'far fa-garage-car',
	      options: ['fat fa-garage-car', 'fal fa-garage-car', 'far fa-garage-car', 'fas fa-garage-car']
	    }, {
	      className: 'far fa-garage-open',
	      options: ['fat fa-garage-open', 'fal fa-garage-open', 'far fa-garage-open', 'fas fa-garage-open']
	    }, {
	      className: 'far fa-gopuram',
	      options: ['fat fa-gopuram', 'fal fa-gopuram', 'far fa-gopuram', 'fas fa-gopuram']
	    }, {
	      className: 'far fa-hospital',
	      options: ['fat fa-hospital', 'fal fa-hospital', 'far fa-hospital', 'fas fa-hospital']
	    }, {
	      className: 'far fa-hospital-user',
	      options: ['fat fa-hospital-user', 'fal fa-hospital-user', 'far fa-hospital-user', 'fas fa-hospital-user']
	    }, {
	      className: 'far fa-hospital-wide',
	      options: ['fat fa-hospital-wide', 'fal fa-hospital-wide', 'far fa-hospital-wide', 'fas fa-hospital-wide']
	    }, {
	      className: 'far fa-hospitals',
	      options: ['fat fa-hospitals', 'fal fa-hospitals', 'far fa-hospitals', 'fas fa-hospitals']
	    }, {
	      className: 'far fa-hotel',
	      options: ['fat fa-hotel', 'fal fa-hotel', 'far fa-hotel', 'fas fa-hotel']
	    }, {
	      className: 'far fa-house',
	      options: ['fat fa-house', 'fal fa-house', 'far fa-house', 'fas fa-house']
	    }, {
	      className: 'far fa-house-blank',
	      options: ['fat fa-house-blank', 'fal fa-house-blank', 'far fa-house-blank', 'fas fa-house-blank']
	    }, {
	      className: 'far fa-house-building',
	      options: ['fat fa-house-building', 'fal fa-house-building', 'far fa-house-building', 'fas fa-house-building']
	    }, {
	      className: 'far fa-house-chimney',
	      options: ['fat fa-house-chimney', 'fal fa-house-chimney', 'far fa-house-chimney', 'fas fa-house-chimney']
	    }, {
	      className: 'far fa-house-chimney-blank',
	      options: ['fat fa-house-chimney-blank', 'fal fa-house-chimney-blank', 'far fa-house-chimney-blank', 'fas fa-house-chimney-blank']
	    }, {
	      className: 'far fa-house-chimney-crack',
	      options: ['fat fa-house-chimney-crack', 'fal fa-house-chimney-crack', 'far fa-house-chimney-crack', 'fas fa-house-chimney-crack']
	    }, {
	      className: 'far fa-house-chimney-medical',
	      options: ['fat fa-house-chimney-medical', 'fal fa-house-chimney-medical', 'far fa-house-chimney-medical', 'fas fa-house-chimney-medical']
	    }, {
	      className: 'far fa-house-chimney-window',
	      options: ['fat fa-house-chimney-window', 'fal fa-house-chimney-window', 'far fa-house-chimney-window', 'fas fa-house-chimney-window']
	    }, {
	      className: 'far fa-house-crack',
	      options: ['fat fa-house-crack', 'fal fa-house-crack', 'far fa-house-crack', 'fas fa-house-crack']
	    }, {
	      className: 'far fa-house-day',
	      options: ['fat fa-house-day', 'fal fa-house-day', 'far fa-house-day', 'fas fa-house-day']
	    }, {
	      className: 'far fa-house-flood',
	      options: ['fat fa-house-flood', 'fal fa-house-flood', 'far fa-house-flood', 'fas fa-house-flood']
	    }, {
	      className: 'far fa-house-medical',
	      options: ['fat fa-house-medical', 'fal fa-house-medical', 'far fa-house-medical', 'fas fa-house-medical']
	    }, {
	      className: 'far fa-house-night',
	      options: ['fat fa-house-night', 'fal fa-house-night', 'far fa-house-night', 'fas fa-house-night']
	    }, {
	      className: 'far fa-house-tree',
	      options: ['fat fa-house-tree', 'fal fa-house-tree', 'far fa-house-tree', 'fas fa-house-tree']
	    }, {
	      className: 'far fa-house-turret',
	      options: ['fat fa-house-turret', 'fal fa-house-turret', 'far fa-house-turret', 'fas fa-house-turret']
	    }, {
	      className: 'far fa-house-window',
	      options: ['fat fa-house-window', 'fal fa-house-window', 'far fa-house-window', 'fas fa-house-window']
	    }, {
	      className: 'far fa-igloo',
	      options: ['fat fa-igloo', 'fal fa-igloo', 'far fa-igloo', 'fas fa-igloo']
	    }, {
	      className: 'far fa-industry',
	      options: ['fat fa-industry', 'fal fa-industry', 'far fa-industry', 'fas fa-industry']
	    }, {
	      className: 'far fa-industry-windows',
	      options: ['fat fa-industry-windows', 'fal fa-industry-windows', 'far fa-industry-windows', 'fas fa-industry-windows']
	    }, {
	      className: 'far fa-kaaba',
	      options: ['fat fa-kaaba', 'fal fa-kaaba', 'far fa-kaaba', 'fas fa-kaaba']
	    }, {
	      className: 'far fa-landmark',
	      options: ['fat fa-landmark', 'fal fa-landmark', 'far fa-landmark', 'fas fa-landmark']
	    }, {
	      className: 'far fa-landmark-dome',
	      options: ['fat fa-landmark-dome', 'fal fa-landmark-dome', 'far fa-landmark-dome', 'fas fa-landmark-dome']
	    }, {
	      className: 'far fa-monument',
	      options: ['fat fa-monument', 'fal fa-monument', 'far fa-monument', 'fas fa-monument']
	    }, {
	      className: 'far fa-mosque',
	      options: ['fat fa-mosque', 'fal fa-mosque', 'far fa-mosque', 'fas fa-mosque']
	    }, {
	      className: 'far fa-place-of-worship',
	      options: ['fat fa-place-of-worship', 'fal fa-place-of-worship', 'far fa-place-of-worship', 'fas fa-place-of-worship']
	    }, {
	      className: 'far fa-roller-coaster',
	      options: ['fat fa-roller-coaster', 'fal fa-roller-coaster', 'far fa-roller-coaster', 'fas fa-roller-coaster']
	    }, {
	      className: 'far fa-school',
	      options: ['fat fa-school', 'fal fa-school', 'far fa-school', 'fas fa-school']
	    }, {
	      className: 'far fa-shop',
	      options: ['fat fa-shop', 'fal fa-shop', 'far fa-shop', 'fas fa-shop']
	    }, {
	      className: 'far fa-store',
	      options: ['fat fa-store', 'fal fa-store', 'far fa-store', 'fas fa-store']
	    }, {
	      className: 'far fa-synagogue',
	      options: ['fat fa-synagogue', 'fal fa-synagogue', 'far fa-synagogue', 'fas fa-synagogue']
	    }, {
	      className: 'far fa-torii-gate',
	      options: ['fat fa-torii-gate', 'fal fa-torii-gate', 'far fa-torii-gate', 'fas fa-torii-gate']
	    }, {
	      className: 'far fa-vihara',
	      options: ['fat fa-vihara', 'fal fa-vihara', 'far fa-vihara', 'fas fa-vihara']
	    }, {
	      className: 'far fa-warehouse',
	      options: ['fat fa-warehouse', 'fal fa-warehouse', 'far fa-warehouse', 'fas fa-warehouse']
	    }, {
	      className: 'far fa-warehouse-full',
	      options: ['fat fa-warehouse-full', 'fal fa-warehouse-full', 'far fa-warehouse-full', 'fas fa-warehouse-full']
	    }]
	  }, {
	    id: 'business',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_BUSINESS'),
	    items: [{
	      className: 'far fa-address-book',
	      options: ['fat fa-address-book', 'fal fa-address-book', 'far fa-address-book', 'fas fa-address-book']
	    }, {
	      className: 'far fa-address-card',
	      options: ['fat fa-address-card', 'fal fa-address-card', 'far fa-address-card', 'fas fa-address-card']
	    }, {
	      className: 'far fa-badge',
	      options: ['fat fa-badge', 'fal fa-badge', 'far fa-badge', 'fas fa-badge']
	    }, {
	      className: 'far fa-badge-check',
	      options: ['fat fa-badge-check', 'fal fa-badge-check', 'far fa-badge-check', 'fas fa-badge-check']
	    }, {
	      className: 'far fa-badge-dollar',
	      options: ['fat fa-badge-dollar', 'fal fa-badge-dollar', 'far fa-badge-dollar', 'fas fa-badge-dollar']
	    }, {
	      className: 'far fa-badge-percent',
	      options: ['fat fa-badge-percent', 'fal fa-badge-percent', 'far fa-badge-percent', 'fas fa-badge-percent']
	    }, {
	      className: 'far fa-bars-progress',
	      options: ['fat fa-bars-progress', 'fal fa-bars-progress', 'far fa-bars-progress', 'fas fa-bars-progress']
	    }, {
	      className: 'far fa-bars-staggered',
	      options: ['fat fa-bars-staggered', 'fal fa-bars-staggered', 'far fa-bars-staggered', 'fas fa-bars-staggered']
	    }, {
	      className: 'far fa-book',
	      options: ['fat fa-book', 'fal fa-book', 'far fa-book', 'fas fa-book']
	    }, {
	      className: 'far fa-book-section',
	      options: ['fat fa-book-section', 'fal fa-book-section', 'far fa-book-section', 'fas fa-book-section']
	    }, {
	      className: 'far fa-box-archive',
	      options: ['fat fa-box-archive', 'fal fa-box-archive', 'far fa-box-archive', 'fas fa-box-archive']
	    }, {
	      className: 'far fa-brain-arrow-curved-right',
	      options: ['fat fa-brain-arrow-curved-right', 'fal fa-brain-arrow-curved-right', 'far fa-brain-arrow-curved-right', 'fas fa-brain-arrow-curved-right']
	    }, {
	      className: 'far fa-briefcase',
	      options: ['fat fa-briefcase', 'fal fa-briefcase', 'far fa-briefcase', 'fas fa-briefcase']
	    }, {
	      className: 'far fa-briefcase-arrow-right',
	      options: ['fat fa-briefcase-arrow-right', 'fal fa-briefcase-arrow-right', 'far fa-briefcase-arrow-right', 'fas fa-briefcase-arrow-right']
	    }, {
	      className: 'far fa-briefcase-blank',
	      options: ['fat fa-briefcase-blank', 'fal fa-briefcase-blank', 'far fa-briefcase-blank', 'fas fa-briefcase-blank']
	    }, {
	      className: 'far fa-building',
	      options: ['fat fa-building', 'fal fa-building', 'far fa-building', 'fas fa-building']
	    }, {
	      className: 'far fa-bullhorn',
	      options: ['fat fa-bullhorn', 'fal fa-bullhorn', 'far fa-bullhorn', 'fas fa-bullhorn']
	    }, {
	      className: 'far fa-bullseye',
	      options: ['fat fa-bullseye', 'fal fa-bullseye', 'far fa-bullseye', 'fas fa-bullseye']
	    }, {
	      className: 'far fa-business-time',
	      options: ['fat fa-business-time', 'fal fa-business-time', 'far fa-business-time', 'fas fa-business-time']
	    }, {
	      className: 'far fa-cabinet-filing',
	      options: ['fat fa-cabinet-filing', 'fal fa-cabinet-filing', 'far fa-cabinet-filing', 'fas fa-cabinet-filing']
	    }, {
	      className: 'far fa-cake-candles',
	      options: ['fat fa-cake-candles', 'fal fa-cake-candles', 'far fa-cake-candles', 'fas fa-cake-candles']
	    }, {
	      className: 'far fa-calculator',
	      options: ['fat fa-calculator', 'fal fa-calculator', 'far fa-calculator', 'fas fa-calculator']
	    }, {
	      className: 'far fa-calendar',
	      options: ['fat fa-calendar', 'fal fa-calendar', 'far fa-calendar', 'fas fa-calendar']
	    }, {
	      className: 'far fa-calendar-days',
	      options: ['fat fa-calendar-days', 'fal fa-calendar-days', 'far fa-calendar-days', 'fas fa-calendar-days']
	    }, {
	      className: 'far fa-certificate',
	      options: ['fat fa-certificate', 'fal fa-certificate', 'far fa-certificate', 'fas fa-certificate']
	    }, {
	      className: 'far fa-chart-area',
	      options: ['fat fa-chart-area', 'fal fa-chart-area', 'far fa-chart-area', 'fas fa-chart-area']
	    }, {
	      className: 'far fa-chart-bar',
	      options: ['fat fa-chart-bar', 'fal fa-chart-bar', 'far fa-chart-bar', 'fas fa-chart-bar']
	    }, {
	      className: 'far fa-chart-bullet',
	      options: ['fat fa-chart-bullet', 'fal fa-chart-bullet', 'far fa-chart-bullet', 'fas fa-chart-bullet']
	    }, {
	      className: 'far fa-chart-candlestick',
	      options: ['fat fa-chart-candlestick', 'fal fa-chart-candlestick', 'far fa-chart-candlestick', 'fas fa-chart-candlestick']
	    }, {
	      className: 'far fa-chart-column',
	      options: ['fat fa-chart-column', 'fal fa-chart-column', 'far fa-chart-column', 'fas fa-chart-column']
	    }, {
	      className: 'far fa-chart-gantt',
	      options: ['fat fa-chart-gantt', 'fal fa-chart-gantt', 'far fa-chart-gantt', 'fas fa-chart-gantt']
	    }, {
	      className: 'far fa-chart-line',
	      options: ['fat fa-chart-line', 'fal fa-chart-line', 'far fa-chart-line', 'fas fa-chart-line']
	    }, {
	      className: 'far fa-chart-line-down',
	      options: ['fat fa-chart-line-down', 'fal fa-chart-line-down', 'far fa-chart-line-down', 'fas fa-chart-line-down']
	    }, {
	      className: 'far fa-chart-line-up',
	      options: ['fat fa-chart-line-up', 'fal fa-chart-line-up', 'far fa-chart-line-up', 'fas fa-chart-line-up']
	    }, {
	      className: 'far fa-chart-network',
	      options: ['fat fa-chart-network', 'fal fa-chart-network', 'far fa-chart-network', 'fas fa-chart-network']
	    }, {
	      className: 'far fa-chart-pie',
	      options: ['fat fa-chart-pie', 'fal fa-chart-pie', 'far fa-chart-pie', 'fas fa-chart-pie']
	    }, {
	      className: 'far fa-chart-pie-simple',
	      options: ['fat fa-chart-pie-simple', 'fal fa-chart-pie-simple', 'far fa-chart-pie-simple', 'fas fa-chart-pie-simple']
	    }, {
	      className: 'far fa-chart-pyramid',
	      options: ['fat fa-chart-pyramid', 'fal fa-chart-pyramid', 'far fa-chart-pyramid', 'fas fa-chart-pyramid']
	    }, {
	      className: 'far fa-chart-radar',
	      options: ['fat fa-chart-radar', 'fal fa-chart-radar', 'far fa-chart-radar', 'fas fa-chart-radar']
	    }, {
	      className: 'far fa-chart-scatter',
	      options: ['fat fa-chart-scatter', 'fal fa-chart-scatter', 'far fa-chart-scatter', 'fas fa-chart-scatter']
	    }, {
	      className: 'far fa-chart-scatter-3d',
	      options: ['fat fa-chart-scatter-3d', 'fal fa-chart-scatter-3d', 'far fa-chart-scatter-3d', 'fas fa-chart-scatter-3d']
	    }, {
	      className: 'far fa-chart-scatter-bubble',
	      options: ['fat fa-chart-scatter-bubble', 'fal fa-chart-scatter-bubble', 'far fa-chart-scatter-bubble', 'fas fa-chart-scatter-bubble']
	    }, {
	      className: 'far fa-chart-tree-map',
	      options: ['fat fa-chart-tree-map', 'fal fa-chart-tree-map', 'far fa-chart-tree-map', 'fas fa-chart-tree-map']
	    }, {
	      className: 'far fa-chart-user',
	      options: ['fat fa-chart-user', 'fal fa-chart-user', 'far fa-chart-user', 'fas fa-chart-user']
	    }, {
	      className: 'far fa-chart-waterfall',
	      options: ['fat fa-chart-waterfall', 'fal fa-chart-waterfall', 'far fa-chart-waterfall', 'fas fa-chart-waterfall']
	    }, {
	      className: 'far fa-city',
	      options: ['fat fa-city', 'fal fa-city', 'far fa-city', 'fas fa-city']
	    }, {
	      className: 'far fa-clipboard',
	      options: ['fat fa-clipboard', 'fal fa-clipboard', 'far fa-clipboard', 'fas fa-clipboard']
	    }, {
	      className: 'far fa-cloud-word',
	      options: ['fat fa-cloud-word', 'fal fa-cloud-word', 'far fa-cloud-word', 'fas fa-cloud-word']
	    }, {
	      className: 'far fa-coffee-pot',
	      options: ['fat fa-coffee-pot', 'fal fa-coffee-pot', 'far fa-coffee-pot', 'fas fa-coffee-pot']
	    }, {
	      className: 'far fa-compass',
	      options: ['fat fa-compass', 'fal fa-compass', 'far fa-compass', 'fas fa-compass']
	    }, {
	      className: 'far fa-computer-classic',
	      options: ['fat fa-computer-classic', 'fal fa-computer-classic', 'far fa-computer-classic', 'fas fa-computer-classic']
	    }, {
	      className: 'far fa-copy',
	      options: ['fat fa-copy', 'fal fa-copy', 'far fa-copy', 'fas fa-copy']
	    }, {
	      className: 'far fa-copyright',
	      options: ['fat fa-copyright', 'fal fa-copyright', 'far fa-copyright', 'fas fa-copyright']
	    }, {
	      className: 'far fa-diagram-lean-canvas',
	      options: ['fat fa-diagram-lean-canvas', 'fal fa-diagram-lean-canvas', 'far fa-diagram-lean-canvas', 'fas fa-diagram-lean-canvas']
	    }, {
	      className: 'far fa-diagram-nested',
	      options: ['fat fa-diagram-nested', 'fal fa-diagram-nested', 'far fa-diagram-nested', 'fas fa-diagram-nested']
	    }, {
	      className: 'far fa-diagram-project',
	      options: ['fat fa-diagram-project', 'fal fa-diagram-project', 'far fa-diagram-project', 'fas fa-diagram-project']
	    }, {
	      className: 'far fa-diagram-sankey',
	      options: ['fat fa-diagram-sankey', 'fal fa-diagram-sankey', 'far fa-diagram-sankey', 'fas fa-diagram-sankey']
	    }, {
	      className: 'far fa-diagram-venn',
	      options: ['fat fa-diagram-venn', 'fal fa-diagram-venn', 'far fa-diagram-venn', 'fas fa-diagram-venn']
	    }, {
	      className: 'far fa-envelope',
	      options: ['fat fa-envelope', 'fal fa-envelope', 'far fa-envelope', 'fas fa-envelope']
	    }, {
	      className: 'far fa-envelope-dot',
	      options: ['fat fa-envelope-dot', 'fal fa-envelope-dot', 'far fa-envelope-dot', 'fas fa-envelope-dot']
	    }, {
	      className: 'far fa-envelope-open',
	      options: ['fat fa-envelope-open', 'fal fa-envelope-open', 'far fa-envelope-open', 'fas fa-envelope-open']
	    }, {
	      className: 'far fa-envelopes',
	      options: ['fat fa-envelopes', 'fal fa-envelopes', 'far fa-envelopes', 'fas fa-envelopes']
	    }, {
	      className: 'far fa-eraser',
	      options: ['fat fa-eraser', 'fal fa-eraser', 'far fa-eraser', 'fas fa-eraser']
	    }, {
	      className: 'far fa-fax',
	      options: ['fat fa-fax', 'fal fa-fax', 'far fa-fax', 'fas fa-fax']
	    }, {
	      className: 'far fa-file',
	      options: ['fat fa-file', 'fal fa-file', 'far fa-file', 'fas fa-file']
	    }, {
	      className: 'far fa-file-chart-column',
	      options: ['fat fa-file-chart-column', 'fal fa-file-chart-column', 'far fa-file-chart-column', 'fas fa-file-chart-column']
	    }, {
	      className: 'far fa-file-chart-pie',
	      options: ['fat fa-file-chart-pie', 'fal fa-file-chart-pie', 'far fa-file-chart-pie', 'fas fa-file-chart-pie']
	    }, {
	      className: 'far fa-file-lines',
	      options: ['fat fa-file-lines', 'fal fa-file-lines', 'far fa-file-lines', 'fas fa-file-lines']
	    }, {
	      className: 'far fa-file-spreadsheet',
	      options: ['fat fa-file-spreadsheet', 'fal fa-file-spreadsheet', 'far fa-file-spreadsheet', 'fas fa-file-spreadsheet']
	    }, {
	      className: 'far fa-file-user',
	      options: ['fat fa-file-user', 'fal fa-file-user', 'far fa-file-user', 'fas fa-file-user']
	    }, {
	      className: 'far fa-floppy-disk',
	      options: ['fat fa-floppy-disk', 'fal fa-floppy-disk', 'far fa-floppy-disk', 'fas fa-floppy-disk']
	    }, {
	      className: 'far fa-floppy-disk-circle-arrow-right',
	      options: ['fat fa-floppy-disk-circle-arrow-right', 'fal fa-floppy-disk-circle-arrow-right', 'far fa-floppy-disk-circle-arrow-right', 'fas fa-floppy-disk-circle-arrow-right']
	    }, {
	      className: 'far fa-floppy-disk-circle-xmark',
	      options: ['fat fa-floppy-disk-circle-xmark', 'fal fa-floppy-disk-circle-xmark', 'far fa-floppy-disk-circle-xmark', 'fas fa-floppy-disk-circle-xmark']
	    }, {
	      className: 'far fa-folder',
	      options: ['fat fa-folder', 'fal fa-folder', 'far fa-folder', 'fas fa-folder']
	    }, {
	      className: 'far fa-folder-arrow-down',
	      options: ['fat fa-folder-arrow-down', 'fal fa-folder-arrow-down', 'far fa-folder-arrow-down', 'fas fa-folder-arrow-down']
	    }, {
	      className: 'far fa-folder-arrow-up',
	      options: ['fat fa-folder-arrow-up', 'fal fa-folder-arrow-up', 'far fa-folder-arrow-up', 'fas fa-folder-arrow-up']
	    }, {
	      className: 'far fa-folder-minus',
	      options: ['fat fa-folder-minus', 'fal fa-folder-minus', 'far fa-folder-minus', 'fas fa-folder-minus']
	    }, {
	      className: 'far fa-folder-open',
	      options: ['fat fa-folder-open', 'fal fa-folder-open', 'far fa-folder-open', 'fas fa-folder-open']
	    }, {
	      className: 'far fa-folder-plus',
	      options: ['fat fa-folder-plus', 'fal fa-folder-plus', 'far fa-folder-plus', 'fas fa-folder-plus']
	    }, {
	      className: 'far fa-folder-tree',
	      options: ['fat fa-folder-tree', 'fal fa-folder-tree', 'far fa-folder-tree', 'fas fa-folder-tree']
	    }, {
	      className: 'far fa-folder-xmark',
	      options: ['fat fa-folder-xmark', 'fal fa-folder-xmark', 'far fa-folder-xmark', 'fas fa-folder-xmark']
	    }, {
	      className: 'far fa-folders',
	      options: ['fat fa-folders', 'fal fa-folders', 'far fa-folders', 'fas fa-folders']
	    }, {
	      className: 'far fa-glasses',
	      options: ['fat fa-glasses', 'fal fa-glasses', 'far fa-glasses', 'fas fa-glasses']
	    }, {
	      className: 'far fa-globe',
	      options: ['fat fa-globe', 'fal fa-globe', 'far fa-globe', 'fas fa-globe']
	    }, {
	      className: 'far fa-highlighter',
	      options: ['fat fa-highlighter', 'fal fa-highlighter', 'far fa-highlighter', 'fas fa-highlighter']
	    }, {
	      className: 'far fa-house-laptop',
	      options: ['fat fa-house-laptop', 'fal fa-house-laptop', 'far fa-house-laptop', 'fas fa-house-laptop']
	    }, {
	      className: 'far fa-inbox-full',
	      options: ['fat fa-inbox-full', 'fal fa-inbox-full', 'far fa-inbox-full', 'fas fa-inbox-full']
	    }, {
	      className: 'far fa-inboxes',
	      options: ['fat fa-inboxes', 'fal fa-inboxes', 'far fa-inboxes', 'fas fa-inboxes']
	    }, {
	      className: 'far fa-industry',
	      options: ['fat fa-industry', 'fal fa-industry', 'far fa-industry', 'fas fa-industry']
	    }, {
	      className: 'far fa-industry-windows',
	      options: ['fat fa-industry-windows', 'fal fa-industry-windows', 'far fa-industry-windows', 'fas fa-industry-windows']
	    }, {
	      className: 'far fa-keynote',
	      options: ['fat fa-keynote', 'fal fa-keynote', 'far fa-keynote', 'fas fa-keynote']
	    }, {
	      className: 'far fa-lamp-desk',
	      options: ['fat fa-lamp-desk', 'fal fa-lamp-desk', 'far fa-lamp-desk', 'fas fa-lamp-desk']
	    }, {
	      className: 'far fa-landmark',
	      options: ['fat fa-landmark', 'fal fa-landmark', 'far fa-landmark', 'fas fa-landmark']
	    }, {
	      className: 'far fa-list-check',
	      options: ['fat fa-list-check', 'fal fa-list-check', 'far fa-list-check', 'fas fa-list-check']
	    }, {
	      className: 'far fa-list-dropdown',
	      options: ['fat fa-list-dropdown', 'fal fa-list-dropdown', 'far fa-list-dropdown', 'fas fa-list-dropdown']
	    }, {
	      className: 'far fa-list-radio',
	      options: ['fat fa-list-radio', 'fal fa-list-radio', 'far fa-list-radio', 'fas fa-list-radio']
	    }, {
	      className: 'far fa-list-timeline',
	      options: ['fat fa-list-timeline', 'fal fa-list-timeline', 'far fa-list-timeline', 'fas fa-list-timeline']
	    }, {
	      className: 'far fa-list-tree',
	      options: ['fat fa-list-tree', 'fal fa-list-tree', 'far fa-list-tree', 'fas fa-list-tree']
	    }, {
	      className: 'far fa-marker',
	      options: ['fat fa-marker', 'fal fa-marker', 'far fa-marker', 'fas fa-marker']
	    }, {
	      className: 'far fa-money-check-dollar-pen',
	      options: ['fat fa-money-check-dollar-pen', 'fal fa-money-check-dollar-pen', 'far fa-money-check-dollar-pen', 'fas fa-money-check-dollar-pen']
	    }, {
	      className: 'far fa-money-check-pen',
	      options: ['fat fa-money-check-pen', 'fal fa-money-check-pen', 'far fa-money-check-pen', 'fas fa-money-check-pen']
	    }, {
	      className: 'far fa-mug-saucer',
	      options: ['fat fa-mug-saucer', 'fal fa-mug-saucer', 'far fa-mug-saucer', 'fas fa-mug-saucer']
	    }, {
	      className: 'far fa-network-wired',
	      options: ['fat fa-network-wired', 'fal fa-network-wired', 'far fa-network-wired', 'fas fa-network-wired']
	    }, {
	      className: 'far fa-note-sticky',
	      options: ['fat fa-note-sticky', 'fal fa-note-sticky', 'far fa-note-sticky', 'fas fa-note-sticky']
	    }, {
	      className: 'far fa-notebook',
	      options: ['fat fa-notebook', 'fal fa-notebook', 'far fa-notebook', 'fas fa-notebook']
	    }, {
	      className: 'far fa-paperclip',
	      options: ['fat fa-paperclip', 'fal fa-paperclip', 'far fa-paperclip', 'fas fa-paperclip']
	    }, {
	      className: 'far fa-paperclip-vertical',
	      options: ['fat fa-paperclip-vertical', 'fal fa-paperclip-vertical', 'far fa-paperclip-vertical', 'fas fa-paperclip-vertical']
	    }, {
	      className: 'far fa-paste',
	      options: ['fat fa-paste', 'fal fa-paste', 'far fa-paste', 'fas fa-paste']
	    }, {
	      className: 'far fa-pen',
	      options: ['fat fa-pen', 'fal fa-pen', 'far fa-pen', 'fas fa-pen']
	    }, {
	      className: 'far fa-pen-clip',
	      options: ['fat fa-pen-clip', 'fal fa-pen-clip', 'far fa-pen-clip', 'fas fa-pen-clip']
	    }, {
	      className: 'far fa-pen-fancy',
	      options: ['fat fa-pen-fancy', 'fal fa-pen-fancy', 'far fa-pen-fancy', 'fas fa-pen-fancy']
	    }, {
	      className: 'far fa-pen-nib',
	      options: ['fat fa-pen-nib', 'fal fa-pen-nib', 'far fa-pen-nib', 'fas fa-pen-nib']
	    }, {
	      className: 'far fa-pen-to-square',
	      options: ['fat fa-pen-to-square', 'fal fa-pen-to-square', 'far fa-pen-to-square', 'fas fa-pen-to-square']
	    }, {
	      className: 'far fa-pencil',
	      options: ['fat fa-pencil', 'fal fa-pencil', 'far fa-pencil', 'fas fa-pencil']
	    }, {
	      className: 'far fa-percent',
	      options: ['fat fa-percent', 'fal fa-percent', 'far fa-percent', 'fas fa-percent']
	    }, {
	      className: 'far fa-phone',
	      options: ['fat fa-phone', 'fal fa-phone', 'far fa-phone', 'fas fa-phone']
	    }, {
	      className: 'far fa-phone-flip',
	      options: ['fat fa-phone-flip', 'fal fa-phone-flip', 'far fa-phone-flip', 'fas fa-phone-flip']
	    }, {
	      className: 'far fa-phone-intercom',
	      options: ['fat fa-phone-intercom', 'fal fa-phone-intercom', 'far fa-phone-intercom', 'fas fa-phone-intercom']
	    }, {
	      className: 'far fa-phone-office',
	      options: ['fat fa-phone-office', 'fal fa-phone-office', 'far fa-phone-office', 'fas fa-phone-office']
	    }, {
	      className: 'far fa-phone-slash',
	      options: ['fat fa-phone-slash', 'fal fa-phone-slash', 'far fa-phone-slash', 'fas fa-phone-slash']
	    }, {
	      className: 'far fa-phone-volume',
	      options: ['fat fa-phone-volume', 'fal fa-phone-volume', 'far fa-phone-volume', 'fas fa-phone-volume']
	    }, {
	      className: 'far fa-podium',
	      options: ['fat fa-podium', 'fal fa-podium', 'far fa-podium', 'fas fa-podium']
	    }, {
	      className: 'far fa-presentation-screen',
	      options: ['fat fa-presentation-screen', 'fal fa-presentation-screen', 'far fa-presentation-screen', 'fas fa-presentation-screen']
	    }, {
	      className: 'far fa-print',
	      options: ['fat fa-print', 'fal fa-print', 'far fa-print', 'fas fa-print']
	    }, {
	      className: 'far fa-print-magnifying-glass',
	      options: ['fat fa-print-magnifying-glass', 'fal fa-print-magnifying-glass', 'far fa-print-magnifying-glass', 'fas fa-print-magnifying-glass']
	    }, {
	      className: 'far fa-print-slash',
	      options: ['fat fa-print-slash', 'fal fa-print-slash', 'far fa-print-slash', 'fas fa-print-slash']
	    }, {
	      className: 'far fa-projector',
	      options: ['fat fa-projector', 'fal fa-projector', 'far fa-projector', 'fas fa-projector']
	    }, {
	      className: 'far fa-rectangle-pro',
	      options: ['fat fa-rectangle-pro', 'fal fa-rectangle-pro', 'far fa-rectangle-pro', 'fas fa-rectangle-pro']
	    }, {
	      className: 'far fa-registered',
	      options: ['fat fa-registered', 'fal fa-registered', 'far fa-registered', 'fas fa-registered']
	    }, {
	      className: 'far fa-router',
	      options: ['fat fa-router', 'fal fa-router', 'far fa-router', 'fas fa-router']
	    }, {
	      className: 'far fa-scale-balanced',
	      options: ['fat fa-scale-balanced', 'fal fa-scale-balanced', 'far fa-scale-balanced', 'fas fa-scale-balanced']
	    }, {
	      className: 'far fa-scale-unbalanced',
	      options: ['fat fa-scale-unbalanced', 'fal fa-scale-unbalanced', 'far fa-scale-unbalanced', 'fas fa-scale-unbalanced']
	    }, {
	      className: 'far fa-scale-unbalanced-flip',
	      options: ['fat fa-scale-unbalanced-flip', 'fal fa-scale-unbalanced-flip', 'far fa-scale-unbalanced-flip', 'fas fa-scale-unbalanced-flip']
	    }, {
	      className: 'far fa-scanner',
	      options: ['fat fa-scanner', 'fal fa-scanner', 'far fa-scanner', 'fas fa-scanner']
	    }, {
	      className: 'far fa-scissors',
	      options: ['fat fa-scissors', 'fal fa-scissors', 'far fa-scissors', 'fas fa-scissors']
	    }, {
	      className: 'far fa-shredder',
	      options: ['fat fa-shredder', 'fal fa-shredder', 'far fa-shredder', 'fas fa-shredder']
	    }, {
	      className: 'far fa-signature',
	      options: ['fat fa-signature', 'fal fa-signature', 'far fa-signature', 'fas fa-signature']
	    }, {
	      className: 'far fa-signature-lock',
	      options: ['fat fa-signature-lock', 'fal fa-signature-lock', 'far fa-signature-lock', 'fas fa-signature-lock']
	    }, {
	      className: 'far fa-signature-slash',
	      options: ['fat fa-signature-slash', 'fal fa-signature-slash', 'far fa-signature-slash', 'fas fa-signature-slash']
	    }, {
	      className: 'far fa-sitemap',
	      options: ['fat fa-sitemap', 'fal fa-sitemap', 'far fa-sitemap', 'fas fa-sitemap']
	    }, {
	      className: 'far fa-slot-machine',
	      options: ['fat fa-slot-machine', 'fal fa-slot-machine', 'far fa-slot-machine', 'fas fa-slot-machine']
	    }, {
	      className: 'far fa-socks',
	      options: ['fat fa-socks', 'fal fa-socks', 'far fa-socks', 'fas fa-socks']
	    }, {
	      className: 'far fa-square-envelope',
	      options: ['fat fa-square-envelope', 'fal fa-square-envelope', 'far fa-square-envelope', 'fas fa-square-envelope']
	    }, {
	      className: 'far fa-square-pen',
	      options: ['fat fa-square-pen', 'fal fa-square-pen', 'far fa-square-pen', 'fas fa-square-pen']
	    }, {
	      className: 'far fa-square-phone',
	      options: ['fat fa-square-phone', 'fal fa-square-phone', 'far fa-square-phone', 'fas fa-square-phone']
	    }, {
	      className: 'far fa-square-phone-flip',
	      options: ['fat fa-square-phone-flip', 'fal fa-square-phone-flip', 'far fa-square-phone-flip', 'fas fa-square-phone-flip']
	    }, {
	      className: 'far fa-table',
	      options: ['fat fa-table', 'fal fa-table', 'far fa-table', 'fas fa-table']
	    }, {
	      className: 'far fa-table-columns',
	      options: ['fat fa-table-columns', 'fal fa-table-columns', 'far fa-table-columns', 'fas fa-table-columns']
	    }, {
	      className: 'far fa-table-layout',
	      options: ['fat fa-table-layout', 'fal fa-table-layout', 'far fa-table-layout', 'fas fa-table-layout']
	    }, {
	      className: 'far fa-table-pivot',
	      options: ['fat fa-table-pivot', 'fal fa-table-pivot', 'far fa-table-pivot', 'fas fa-table-pivot']
	    }, {
	      className: 'far fa-table-rows',
	      options: ['fat fa-table-rows', 'fal fa-table-rows', 'far fa-table-rows', 'fas fa-table-rows']
	    }, {
	      className: 'far fa-table-tree',
	      options: ['fat fa-table-tree', 'fal fa-table-tree', 'far fa-table-tree', 'fas fa-table-tree']
	    }, {
	      className: 'far fa-tag',
	      options: ['fat fa-tag', 'fal fa-tag', 'far fa-tag', 'fas fa-tag']
	    }, {
	      className: 'far fa-tags',
	      options: ['fat fa-tags', 'fal fa-tags', 'far fa-tags', 'fas fa-tags']
	    }, {
	      className: 'far fa-thumbtack',
	      options: ['fat fa-thumbtack', 'fal fa-thumbtack', 'far fa-thumbtack', 'fas fa-thumbtack']
	    }, {
	      className: 'far fa-timeline',
	      options: ['fat fa-timeline', 'fal fa-timeline', 'far fa-timeline', 'fas fa-timeline']
	    }, {
	      className: 'far fa-timeline-arrow',
	      options: ['fat fa-timeline-arrow', 'fal fa-timeline-arrow', 'far fa-timeline-arrow', 'fas fa-timeline-arrow']
	    }, {
	      className: 'far fa-trademark',
	      options: ['fat fa-trademark', 'fal fa-trademark', 'far fa-trademark', 'fas fa-trademark']
	    }, {
	      className: 'far fa-user-hair-mullet',
	      options: ['fat fa-user-hair-mullet', 'fal fa-user-hair-mullet', 'far fa-user-hair-mullet', 'fas fa-user-hair-mullet']
	    }, {
	      className: 'far fa-user-tie-hair',
	      options: ['fat fa-user-tie-hair', 'fal fa-user-tie-hair', 'far fa-user-tie-hair', 'fas fa-user-tie-hair']
	    }, {
	      className: 'far fa-user-tie-hair-long',
	      options: ['fat fa-user-tie-hair-long', 'fal fa-user-tie-hair-long', 'far fa-user-tie-hair-long', 'fas fa-user-tie-hair-long']
	    }, {
	      className: 'far fa-vault',
	      options: ['fat fa-vault', 'fal fa-vault', 'far fa-vault', 'fas fa-vault']
	    }, {
	      className: 'far fa-wallet',
	      options: ['fat fa-wallet', 'fal fa-wallet', 'far fa-wallet', 'fas fa-wallet']
	    }]
	  }, {
	    id: 'camping',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_CAMPING'),
	    items: [{
	      className: 'far fa-acorn',
	      options: ['fat fa-acorn', 'fal fa-acorn', 'far fa-acorn', 'fas fa-acorn']
	    }, {
	      className: 'far fa-axe',
	      options: ['fat fa-axe', 'fal fa-axe', 'far fa-axe', 'fas fa-axe']
	    }, {
	      className: 'far fa-backpack',
	      options: ['fat fa-backpack', 'fal fa-backpack', 'far fa-backpack', 'fas fa-backpack']
	    }, {
	      className: 'far fa-bench-tree',
	      options: ['fat fa-bench-tree', 'fal fa-bench-tree', 'far fa-bench-tree', 'fas fa-bench-tree']
	    }, {
	      className: 'far fa-binoculars',
	      options: ['fat fa-binoculars', 'fal fa-binoculars', 'far fa-binoculars', 'fas fa-binoculars']
	    }, {
	      className: 'far fa-boot',
	      options: ['fat fa-boot', 'fal fa-boot', 'far fa-boot', 'fas fa-boot']
	    }, {
	      className: 'far fa-campfire',
	      options: ['fat fa-campfire', 'fal fa-campfire', 'far fa-campfire', 'fas fa-campfire']
	    }, {
	      className: 'far fa-campground',
	      options: ['fat fa-campground', 'fal fa-campground', 'far fa-campground', 'fas fa-campground']
	    }, {
	      className: 'far fa-caravan',
	      options: ['fat fa-caravan', 'fal fa-caravan', 'far fa-caravan', 'fas fa-caravan']
	    }, {
	      className: 'far fa-caravan-simple',
	      options: ['fat fa-caravan-simple', 'fal fa-caravan-simple', 'far fa-caravan-simple', 'fas fa-caravan-simple']
	    }, {
	      className: 'far fa-cauldron',
	      options: ['fat fa-cauldron', 'fal fa-cauldron', 'far fa-cauldron', 'fas fa-cauldron']
	    }, {
	      className: 'far fa-compass',
	      options: ['fat fa-compass', 'fal fa-compass', 'far fa-compass', 'fas fa-compass']
	    }, {
	      className: 'far fa-faucet',
	      options: ['fat fa-faucet', 'fal fa-faucet', 'far fa-faucet', 'fas fa-faucet']
	    }, {
	      className: 'far fa-faucet-drip',
	      options: ['fat fa-faucet-drip', 'fal fa-faucet-drip', 'far fa-faucet-drip', 'fas fa-faucet-drip']
	    }, {
	      className: 'far fa-fire',
	      options: ['fat fa-fire', 'fal fa-fire', 'far fa-fire', 'fas fa-fire']
	    }, {
	      className: 'far fa-fire-flame-curved',
	      options: ['fat fa-fire-flame-curved', 'fal fa-fire-flame-curved', 'far fa-fire-flame-curved', 'fas fa-fire-flame-curved']
	    }, {
	      className: 'far fa-fire-smoke',
	      options: ['fat fa-fire-smoke', 'fal fa-fire-smoke', 'far fa-fire-smoke', 'fas fa-fire-smoke']
	    }, {
	      className: 'far fa-fishing-rod',
	      options: ['fat fa-fishing-rod', 'fal fa-fishing-rod', 'far fa-fishing-rod', 'fas fa-fishing-rod']
	    }, {
	      className: 'far fa-flashlight',
	      options: ['fat fa-flashlight', 'fal fa-flashlight', 'far fa-flashlight', 'fas fa-flashlight']
	    }, {
	      className: 'far fa-frog',
	      options: ['fat fa-frog', 'fal fa-frog', 'far fa-frog', 'fas fa-frog']
	    }, {
	      className: 'far fa-kit-medical',
	      options: ['fat fa-kit-medical', 'fal fa-kit-medical', 'far fa-kit-medical', 'fas fa-kit-medical']
	    }, {
	      className: 'far fa-map',
	      options: ['fat fa-map', 'fal fa-map', 'far fa-map', 'fas fa-map']
	    }, {
	      className: 'far fa-map-location',
	      options: ['fat fa-map-location', 'fal fa-map-location', 'far fa-map-location', 'fas fa-map-location']
	    }, {
	      className: 'far fa-map-location-dot',
	      options: ['fat fa-map-location-dot', 'fal fa-map-location-dot', 'far fa-map-location-dot', 'fas fa-map-location-dot']
	    }, {
	      className: 'far fa-mountain',
	      options: ['fat fa-mountain', 'fal fa-mountain', 'far fa-mountain', 'fas fa-mountain']
	    }, {
	      className: 'far fa-mountains',
	      options: ['fat fa-mountains', 'fal fa-mountains', 'far fa-mountains', 'fas fa-mountains']
	    }, {
	      className: 'far fa-person-biking-mountain',
	      options: ['fat fa-person-biking-mountain', 'fal fa-person-biking-mountain', 'far fa-person-biking-mountain', 'fas fa-person-biking-mountain']
	    }, {
	      className: 'far fa-person-hiking',
	      options: ['fat fa-person-hiking', 'fal fa-person-hiking', 'far fa-person-hiking', 'fas fa-person-hiking']
	    }, {
	      className: 'far fa-route',
	      options: ['fat fa-route', 'fal fa-route', 'far fa-route', 'fas fa-route']
	    }, {
	      className: 'far fa-rv',
	      options: ['fat fa-rv', 'fal fa-rv', 'far fa-rv', 'fas fa-rv']
	    }, {
	      className: 'far fa-shish-kebab',
	      options: ['fat fa-shish-kebab', 'fal fa-shish-kebab', 'far fa-shish-kebab', 'fas fa-shish-kebab']
	    }, {
	      className: 'far fa-shovel',
	      options: ['fat fa-shovel', 'fal fa-shovel', 'far fa-shovel', 'fas fa-shovel']
	    }, {
	      className: 'far fa-signs-post',
	      options: ['fat fa-signs-post', 'fal fa-signs-post', 'far fa-signs-post', 'fas fa-signs-post']
	    }, {
	      className: 'far fa-squirrel',
	      options: ['fat fa-squirrel', 'fal fa-squirrel', 'far fa-squirrel', 'fas fa-squirrel']
	    }, {
	      className: 'far fa-sunrise',
	      options: ['fat fa-sunrise', 'fal fa-sunrise', 'far fa-sunrise', 'fas fa-sunrise']
	    }, {
	      className: 'far fa-sunset',
	      options: ['fat fa-sunset', 'fal fa-sunset', 'far fa-sunset', 'fas fa-sunset']
	    }, {
	      className: 'far fa-table-picnic',
	      options: ['fat fa-table-picnic', 'fal fa-table-picnic', 'far fa-table-picnic', 'fas fa-table-picnic']
	    }, {
	      className: 'far fa-teddy-bear',
	      options: ['fat fa-teddy-bear', 'fal fa-teddy-bear', 'far fa-teddy-bear', 'fas fa-teddy-bear']
	    }, {
	      className: 'far fa-toilet-paper',
	      options: ['fat fa-toilet-paper', 'fal fa-toilet-paper', 'far fa-toilet-paper', 'fas fa-toilet-paper']
	    }, {
	      className: 'far fa-toilet-paper-blank',
	      options: ['fat fa-toilet-paper-blank', 'fal fa-toilet-paper-blank', 'far fa-toilet-paper-blank', 'fas fa-toilet-paper-blank']
	    }, {
	      className: 'far fa-trailer',
	      options: ['fat fa-trailer', 'fal fa-trailer', 'far fa-trailer', 'fas fa-trailer']
	    }, {
	      className: 'far fa-tree',
	      options: ['fat fa-tree', 'fal fa-tree', 'far fa-tree', 'fas fa-tree']
	    }, {
	      className: 'far fa-tree-deciduous',
	      options: ['fat fa-tree-deciduous', 'fal fa-tree-deciduous', 'far fa-tree-deciduous', 'fas fa-tree-deciduous']
	    }, {
	      className: 'far fa-tree-large',
	      options: ['fat fa-tree-large', 'fal fa-tree-large', 'far fa-tree-large', 'fas fa-tree-large']
	    }, {
	      className: 'far fa-trees',
	      options: ['fat fa-trees', 'fal fa-trees', 'far fa-trees', 'fas fa-trees']
	    }]
	  }, {
	    id: 'charity',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_CHARITY'),
	    items: [{
	      className: 'far fa-book-heart',
	      options: ['fat fa-book-heart', 'fal fa-book-heart', 'far fa-book-heart', 'fas fa-book-heart']
	    }, {
	      className: 'far fa-box-dollar',
	      options: ['fat fa-box-dollar', 'fal fa-box-dollar', 'far fa-box-dollar', 'fas fa-box-dollar']
	    }, {
	      className: 'far fa-box-heart',
	      options: ['fat fa-box-heart', 'fal fa-box-heart', 'far fa-box-heart', 'fas fa-box-heart']
	    }, {
	      className: 'far fa-circle-dollar',
	      options: ['fat fa-circle-dollar', 'fal fa-circle-dollar', 'far fa-circle-dollar', 'fas fa-circle-dollar']
	    }, {
	      className: 'far fa-circle-dollar-to-slot',
	      options: ['fat fa-circle-dollar-to-slot', 'fal fa-circle-dollar-to-slot', 'far fa-circle-dollar-to-slot', 'fas fa-circle-dollar-to-slot']
	    }, {
	      className: 'far fa-circle-heart',
	      options: ['fat fa-circle-heart', 'fal fa-circle-heart', 'far fa-circle-heart', 'fas fa-circle-heart']
	    }, {
	      className: 'far fa-dollar-sign',
	      options: ['fat fa-dollar-sign', 'fal fa-dollar-sign', 'far fa-dollar-sign', 'fas fa-dollar-sign']
	    }, {
	      className: 'far fa-dove',
	      options: ['fat fa-dove', 'fal fa-dove', 'far fa-dove', 'fas fa-dove']
	    }, {
	      className: 'far fa-gift',
	      options: ['fat fa-gift', 'fal fa-gift', 'far fa-gift', 'fas fa-gift']
	    }, {
	      className: 'far fa-globe',
	      options: ['fat fa-globe', 'fal fa-globe', 'far fa-globe', 'fas fa-globe']
	    }, {
	      className: 'far fa-hand-heart',
	      options: ['fat fa-hand-heart', 'fal fa-hand-heart', 'far fa-hand-heart', 'fas fa-hand-heart']
	    }, {
	      className: 'far fa-hand-holding-dollar',
	      options: ['fat fa-hand-holding-dollar', 'fal fa-hand-holding-dollar', 'far fa-hand-holding-dollar', 'fas fa-hand-holding-dollar']
	    }, {
	      className: 'far fa-hand-holding-droplet',
	      options: ['fat fa-hand-holding-droplet', 'fal fa-hand-holding-droplet', 'far fa-hand-holding-droplet', 'fas fa-hand-holding-droplet']
	    }, {
	      className: 'far fa-hand-holding-heart',
	      options: ['fat fa-hand-holding-heart', 'fal fa-hand-holding-heart', 'far fa-hand-holding-heart', 'fas fa-hand-holding-heart']
	    }, {
	      className: 'far fa-hand-holding-seedling',
	      options: ['fat fa-hand-holding-seedling', 'fal fa-hand-holding-seedling', 'far fa-hand-holding-seedling', 'fas fa-hand-holding-seedling']
	    }, {
	      className: 'far fa-hands-holding-dollar',
	      options: ['fat fa-hands-holding-dollar', 'fal fa-hands-holding-dollar', 'far fa-hands-holding-dollar', 'fas fa-hands-holding-dollar']
	    }, {
	      className: 'far fa-hands-holding-heart',
	      options: ['fat fa-hands-holding-heart', 'fal fa-hands-holding-heart', 'far fa-hands-holding-heart', 'fas fa-hands-holding-heart']
	    }, {
	      className: 'far fa-handshake',
	      options: ['fat fa-handshake', 'fal fa-handshake', 'far fa-handshake', 'fas fa-handshake']
	    }, {
	      className: 'far fa-handshake-angle',
	      options: ['fat fa-handshake-angle', 'fal fa-handshake-angle', 'far fa-handshake-angle', 'fas fa-handshake-angle']
	    }, {
	      className: 'far fa-handshake-simple',
	      options: ['fat fa-handshake-simple', 'fal fa-handshake-simple', 'far fa-handshake-simple', 'fas fa-handshake-simple']
	    }, {
	      className: 'far fa-heart',
	      options: ['fat fa-heart', 'fal fa-heart', 'far fa-heart', 'fas fa-heart']
	    }, {
	      className: 'far fa-house-chimney-heart',
	      options: ['fat fa-house-chimney-heart', 'fal fa-house-chimney-heart', 'far fa-house-chimney-heart', 'fas fa-house-chimney-heart']
	    }, {
	      className: 'far fa-house-heart',
	      options: ['fat fa-house-heart', 'fal fa-house-heart', 'far fa-house-heart', 'fas fa-house-heart']
	    }, {
	      className: 'far fa-leaf',
	      options: ['fat fa-leaf', 'fal fa-leaf', 'far fa-leaf', 'fas fa-leaf']
	    }, {
	      className: 'far fa-leaf-heart',
	      options: ['fat fa-leaf-heart', 'fal fa-leaf-heart', 'far fa-leaf-heart', 'fas fa-leaf-heart']
	    }, {
	      className: 'far fa-money-check-dollar-pen',
	      options: ['fat fa-money-check-dollar-pen', 'fal fa-money-check-dollar-pen', 'far fa-money-check-dollar-pen', 'fas fa-money-check-dollar-pen']
	    }, {
	      className: 'far fa-money-check-pen',
	      options: ['fat fa-money-check-pen', 'fal fa-money-check-pen', 'far fa-money-check-pen', 'fas fa-money-check-pen']
	    }, {
	      className: 'far fa-parachute-box',
	      options: ['fat fa-parachute-box', 'fal fa-parachute-box', 'far fa-parachute-box', 'fas fa-parachute-box']
	    }, {
	      className: 'far fa-piggy-bank',
	      options: ['fat fa-piggy-bank', 'fal fa-piggy-bank', 'far fa-piggy-bank', 'fas fa-piggy-bank']
	    }, {
	      className: 'far fa-ribbon',
	      options: ['fat fa-ribbon', 'fal fa-ribbon', 'far fa-ribbon', 'fas fa-ribbon']
	    }, {
	      className: 'far fa-seedling',
	      options: ['fat fa-seedling', 'fal fa-seedling', 'far fa-seedling', 'fas fa-seedling']
	    }, {
	      className: 'far fa-square-dollar',
	      options: ['fat fa-square-dollar', 'fal fa-square-dollar', 'far fa-square-dollar', 'fas fa-square-dollar']
	    }, {
	      className: 'far fa-square-heart',
	      options: ['fat fa-square-heart', 'fal fa-square-heart', 'far fa-square-heart', 'fas fa-square-heart']
	    }]
	  }, {
	    id: 'childhood',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_CHILDHOOD'),
	    items: [{
	      className: 'far fa-apple-whole',
	      options: ['fat fa-apple-whole', 'fal fa-apple-whole', 'far fa-apple-whole', 'fas fa-apple-whole']
	    }, {
	      className: 'far fa-baby',
	      options: ['fat fa-baby', 'fal fa-baby', 'far fa-baby', 'fas fa-baby']
	    }, {
	      className: 'far fa-baby-carriage',
	      options: ['fat fa-baby-carriage', 'fal fa-baby-carriage', 'far fa-baby-carriage', 'fas fa-baby-carriage']
	    }, {
	      className: 'far fa-backpack',
	      options: ['fat fa-backpack', 'fal fa-backpack', 'far fa-backpack', 'fas fa-backpack']
	    }, {
	      className: 'far fa-ball-pile',
	      options: ['fat fa-ball-pile', 'fal fa-ball-pile', 'far fa-ball-pile', 'fas fa-ball-pile']
	    }, {
	      className: 'far fa-balloon',
	      options: ['fat fa-balloon', 'fal fa-balloon', 'far fa-balloon', 'fas fa-balloon']
	    }, {
	      className: 'far fa-balloons',
	      options: ['fat fa-balloons', 'fal fa-balloons', 'far fa-balloons', 'fas fa-balloons']
	    }, {
	      className: 'far fa-baseball-bat-ball',
	      options: ['fat fa-baseball-bat-ball', 'fal fa-baseball-bat-ball', 'far fa-baseball-bat-ball', 'fas fa-baseball-bat-ball']
	    }, {
	      className: 'far fa-basketball-hoop',
	      options: ['fat fa-basketball-hoop', 'fal fa-basketball-hoop', 'far fa-basketball-hoop', 'fas fa-basketball-hoop']
	    }, {
	      className: 'far fa-bath',
	      options: ['fat fa-bath', 'fal fa-bath', 'far fa-bath', 'fas fa-bath']
	    }, {
	      className: 'far fa-bell-school',
	      options: ['fat fa-bell-school', 'fal fa-bell-school', 'far fa-bell-school', 'fas fa-bell-school']
	    }, {
	      className: 'far fa-bell-school-slash',
	      options: ['fat fa-bell-school-slash', 'fal fa-bell-school-slash', 'far fa-bell-school-slash', 'fas fa-bell-school-slash']
	    }, {
	      className: 'far fa-block-question',
	      options: ['fat fa-block-question', 'fal fa-block-question', 'far fa-block-question', 'fas fa-block-question']
	    }, {
	      className: 'far fa-cake-candles',
	      options: ['fat fa-cake-candles', 'fal fa-cake-candles', 'far fa-cake-candles', 'fas fa-cake-candles']
	    }, {
	      className: 'far fa-cake-slice',
	      options: ['fat fa-cake-slice', 'fal fa-cake-slice', 'far fa-cake-slice', 'fas fa-cake-slice']
	    }, {
	      className: 'far fa-candy',
	      options: ['fat fa-candy', 'fal fa-candy', 'far fa-candy', 'fas fa-candy']
	    }, {
	      className: 'far fa-candy-bar',
	      options: ['fat fa-candy-bar', 'fal fa-candy-bar', 'far fa-candy-bar', 'fas fa-candy-bar']
	    }, {
	      className: 'far fa-cookie',
	      options: ['fat fa-cookie', 'fal fa-cookie', 'far fa-cookie', 'fas fa-cookie']
	    }, {
	      className: 'far fa-cookie-bite',
	      options: ['fat fa-cookie-bite', 'fal fa-cookie-bite', 'far fa-cookie-bite', 'fas fa-cookie-bite']
	    }, {
	      className: 'far fa-creemee',
	      options: ['fat fa-creemee', 'fal fa-creemee', 'far fa-creemee', 'fas fa-creemee']
	    }, {
	      className: 'far fa-cupcake',
	      options: ['fat fa-cupcake', 'fal fa-cupcake', 'far fa-cupcake', 'fas fa-cupcake']
	    }, {
	      className: 'far fa-duck',
	      options: ['fat fa-duck', 'fal fa-duck', 'far fa-duck', 'fas fa-duck']
	    }, {
	      className: 'far fa-family',
	      options: ['fat fa-family', 'fal fa-family', 'far fa-family', 'fas fa-family']
	    }, {
	      className: 'far fa-family-dress',
	      options: ['fat fa-family-dress', 'fal fa-family-dress', 'far fa-family-dress', 'fas fa-family-dress']
	    }, {
	      className: 'far fa-family-pants',
	      options: ['fat fa-family-pants', 'fal fa-family-pants', 'far fa-family-pants', 'fas fa-family-pants']
	    }, {
	      className: 'far fa-ferris-wheel',
	      options: ['fat fa-ferris-wheel', 'fal fa-ferris-wheel', 'far fa-ferris-wheel', 'fas fa-ferris-wheel']
	    }, {
	      className: 'far fa-flashlight',
	      options: ['fat fa-flashlight', 'fal fa-flashlight', 'far fa-flashlight', 'fas fa-flashlight']
	    }, {
	      className: 'far fa-game-console-handheld',
	      options: ['fat fa-game-console-handheld', 'fal fa-game-console-handheld', 'far fa-game-console-handheld', 'fas fa-game-console-handheld']
	    }, {
	      className: 'far fa-gamepad',
	      options: ['fat fa-gamepad', 'fal fa-gamepad', 'far fa-gamepad', 'fas fa-gamepad']
	    }, {
	      className: 'far fa-gamepad-modern',
	      options: ['fat fa-gamepad-modern', 'fal fa-gamepad-modern', 'far fa-gamepad-modern', 'fas fa-gamepad-modern']
	    }, {
	      className: 'far fa-globe-snow',
	      options: ['fat fa-globe-snow', 'fal fa-globe-snow', 'far fa-globe-snow', 'fas fa-globe-snow']
	    }, {
	      className: 'far fa-gun-squirt',
	      options: ['fat fa-gun-squirt', 'fal fa-gun-squirt', 'far fa-gun-squirt', 'fas fa-gun-squirt']
	    }, {
	      className: 'far fa-ice-cream',
	      options: ['fat fa-ice-cream', 'fal fa-ice-cream', 'far fa-ice-cream', 'fas fa-ice-cream']
	    }, {
	      className: 'far fa-lollipop',
	      options: ['fat fa-lollipop', 'fal fa-lollipop', 'far fa-lollipop', 'fas fa-lollipop']
	    }, {
	      className: 'far fa-mitten',
	      options: ['fat fa-mitten', 'fal fa-mitten', 'far fa-mitten', 'fas fa-mitten']
	    }, {
	      className: 'far fa-person-biking',
	      options: ['fat fa-person-biking', 'fal fa-person-biking', 'far fa-person-biking', 'fas fa-person-biking']
	    }, {
	      className: 'far fa-person-sledding',
	      options: ['fat fa-person-sledding', 'fal fa-person-sledding', 'far fa-person-sledding', 'fas fa-person-sledding']
	    }, {
	      className: 'far fa-pinata',
	      options: ['fat fa-pinata', 'fal fa-pinata', 'far fa-pinata', 'fas fa-pinata']
	    }, {
	      className: 'far fa-pool-8-ball',
	      options: ['fat fa-pool-8-ball', 'fal fa-pool-8-ball', 'far fa-pool-8-ball', 'fas fa-pool-8-ball']
	    }, {
	      className: 'far fa-popsicle',
	      options: ['fat fa-popsicle', 'fal fa-popsicle', 'far fa-popsicle', 'fas fa-popsicle']
	    }, {
	      className: 'far fa-pretzel',
	      options: ['fat fa-pretzel', 'fal fa-pretzel', 'far fa-pretzel', 'fas fa-pretzel']
	    }, {
	      className: 'far fa-puzzle',
	      options: ['fat fa-puzzle', 'fal fa-puzzle', 'far fa-puzzle', 'fas fa-puzzle']
	    }, {
	      className: 'far fa-puzzle-piece',
	      options: ['fat fa-puzzle-piece', 'fal fa-puzzle-piece', 'far fa-puzzle-piece', 'fas fa-puzzle-piece']
	    }, {
	      className: 'far fa-robot',
	      options: ['fat fa-robot', 'fal fa-robot', 'far fa-robot', 'fas fa-robot']
	    }, {
	      className: 'far fa-roller-coaster',
	      options: ['fat fa-roller-coaster', 'fal fa-roller-coaster', 'far fa-roller-coaster', 'fas fa-roller-coaster']
	    }, {
	      className: 'far fa-school',
	      options: ['fat fa-school', 'fal fa-school', 'far fa-school', 'fas fa-school']
	    }, {
	      className: 'far fa-shapes',
	      options: ['fat fa-shapes', 'fal fa-shapes', 'far fa-shapes', 'fas fa-shapes']
	    }, {
	      className: 'far fa-snowman',
	      options: ['fat fa-snowman', 'fal fa-snowman', 'far fa-snowman', 'fas fa-snowman']
	    }, {
	      className: 'far fa-snowman-head',
	      options: ['fat fa-snowman-head', 'fal fa-snowman-head', 'far fa-snowman-head', 'fas fa-snowman-head']
	    }, {
	      className: 'far fa-teddy-bear',
	      options: ['fat fa-teddy-bear', 'fal fa-teddy-bear', 'far fa-teddy-bear', 'fas fa-teddy-bear']
	    }, {
	      className: 'far fa-thought-bubble',
	      options: ['fat fa-thought-bubble', 'fal fa-thought-bubble', 'far fa-thought-bubble', 'fas fa-thought-bubble']
	    }]
	  }, {
	    id: 'clothing-fashion',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_CLOTHING_FASHION'),
	    items: [{
	      className: 'far fa-boot',
	      options: ['fat fa-boot', 'fal fa-boot', 'far fa-boot', 'fas fa-boot']
	    }, {
	      className: 'far fa-boot-heeled',
	      options: ['fat fa-boot-heeled', 'fal fa-boot-heeled', 'far fa-boot-heeled', 'fas fa-boot-heeled']
	    }, {
	      className: 'far fa-clothes-hanger',
	      options: ['fat fa-clothes-hanger', 'fal fa-clothes-hanger', 'far fa-clothes-hanger', 'fas fa-clothes-hanger']
	    }, {
	      className: 'far fa-ear-muffs',
	      options: ['fat fa-ear-muffs', 'fal fa-ear-muffs', 'far fa-ear-muffs', 'fas fa-ear-muffs']
	    }, {
	      className: 'far fa-graduation-cap',
	      options: ['fat fa-graduation-cap', 'fal fa-graduation-cap', 'far fa-graduation-cap', 'fas fa-graduation-cap']
	    }, {
	      className: 'far fa-hat-cowboy',
	      options: ['fat fa-hat-cowboy', 'fal fa-hat-cowboy', 'far fa-hat-cowboy', 'fas fa-hat-cowboy']
	    }, {
	      className: 'far fa-hat-cowboy-side',
	      options: ['fat fa-hat-cowboy-side', 'fal fa-hat-cowboy-side', 'far fa-hat-cowboy-side', 'fas fa-hat-cowboy-side']
	    }, {
	      className: 'far fa-hat-santa',
	      options: ['fat fa-hat-santa', 'fal fa-hat-santa', 'far fa-hat-santa', 'fas fa-hat-santa']
	    }, {
	      className: 'far fa-hat-winter',
	      options: ['fat fa-hat-winter', 'fal fa-hat-winter', 'far fa-hat-winter', 'fas fa-hat-winter']
	    }, {
	      className: 'far fa-hat-witch',
	      options: ['fat fa-hat-witch', 'fal fa-hat-witch', 'far fa-hat-witch', 'fas fa-hat-witch']
	    }, {
	      className: 'far fa-hat-wizard',
	      options: ['fat fa-hat-wizard', 'fal fa-hat-wizard', 'far fa-hat-wizard', 'fas fa-hat-wizard']
	    }, {
	      className: 'far fa-hood-cloak',
	      options: ['fat fa-hood-cloak', 'fal fa-hood-cloak', 'far fa-hood-cloak', 'fas fa-hood-cloak']
	    }, {
	      className: 'far fa-ice-skate',
	      options: ['fat fa-ice-skate', 'fal fa-ice-skate', 'far fa-ice-skate', 'fas fa-ice-skate']
	    }, {
	      className: 'far fa-mitten',
	      options: ['fat fa-mitten', 'fal fa-mitten', 'far fa-mitten', 'fas fa-mitten']
	    }, {
	      className: 'far fa-reel',
	      options: ['fat fa-reel', 'fal fa-reel', 'far fa-reel', 'fas fa-reel']
	    }, {
	      className: 'far fa-scarf',
	      options: ['fat fa-scarf', 'fal fa-scarf', 'far fa-scarf', 'fas fa-scarf']
	    }, {
	      className: 'far fa-shirt',
	      options: ['fat fa-shirt', 'fal fa-shirt', 'far fa-shirt', 'fas fa-shirt']
	    }, {
	      className: 'far fa-shirt-long-sleeve',
	      options: ['fat fa-shirt-long-sleeve', 'fal fa-shirt-long-sleeve', 'far fa-shirt-long-sleeve', 'fas fa-shirt-long-sleeve']
	    }, {
	      className: 'far fa-shirt-running',
	      options: ['fat fa-shirt-running', 'fal fa-shirt-running', 'far fa-shirt-running', 'fas fa-shirt-running']
	    }, {
	      className: 'far fa-shirt-tank-top',
	      options: ['fat fa-shirt-tank-top', 'fal fa-shirt-tank-top', 'far fa-shirt-tank-top', 'fas fa-shirt-tank-top']
	    }, {
	      className: 'far fa-shoe-prints',
	      options: ['fat fa-shoe-prints', 'fal fa-shoe-prints', 'far fa-shoe-prints', 'fas fa-shoe-prints']
	    }, {
	      className: 'far fa-ski-boot',
	      options: ['fat fa-ski-boot', 'fal fa-ski-boot', 'far fa-ski-boot', 'fas fa-ski-boot']
	    }, {
	      className: 'far fa-socks',
	      options: ['fat fa-socks', 'fal fa-socks', 'far fa-socks', 'fas fa-socks']
	    }, {
	      className: 'far fa-stocking',
	      options: ['fat fa-stocking', 'fal fa-stocking', 'far fa-stocking', 'fas fa-stocking']
	    }, {
	      className: 'far fa-sunglasses',
	      options: ['fat fa-sunglasses', 'fal fa-sunglasses', 'far fa-sunglasses', 'fas fa-sunglasses']
	    }, {
	      className: 'far fa-uniform-martial-arts',
	      options: ['fat fa-uniform-martial-arts', 'fal fa-uniform-martial-arts', 'far fa-uniform-martial-arts', 'fas fa-uniform-martial-arts']
	    }, {
	      className: 'far fa-user-tie',
	      options: ['fat fa-user-tie', 'fal fa-user-tie', 'far fa-user-tie', 'fas fa-user-tie']
	    }]
	  }, {
	    id: 'coding',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_CODING'),
	    items: [{
	      className: 'far fa-barcode',
	      options: ['fat fa-barcode', 'fal fa-barcode', 'far fa-barcode', 'fas fa-barcode']
	    }, {
	      className: 'far fa-bars',
	      options: ['fat fa-bars', 'fal fa-bars', 'far fa-bars', 'fas fa-bars']
	    }, {
	      className: 'far fa-bars-filter',
	      options: ['fat fa-bars-filter', 'fal fa-bars-filter', 'far fa-bars-filter', 'fas fa-bars-filter']
	    }, {
	      className: 'far fa-bars-sort',
	      options: ['fat fa-bars-sort', 'fal fa-bars-sort', 'far fa-bars-sort', 'fas fa-bars-sort']
	    }, {
	      className: 'far fa-bars-staggered',
	      options: ['fat fa-bars-staggered', 'fal fa-bars-staggered', 'far fa-bars-staggered', 'fas fa-bars-staggered']
	    }, {
	      className: 'far fa-bath',
	      options: ['fat fa-bath', 'fal fa-bath', 'far fa-bath', 'fas fa-bath']
	    }, {
	      className: 'far fa-binary',
	      options: ['fat fa-binary', 'fal fa-binary', 'far fa-binary', 'fas fa-binary']
	    }, {
	      className: 'far fa-binary-circle-check',
	      options: ['fat fa-binary-circle-check', 'fal fa-binary-circle-check', 'far fa-binary-circle-check', 'fas fa-binary-circle-check']
	    }, {
	      className: 'far fa-binary-lock',
	      options: ['fat fa-binary-lock', 'fal fa-binary-lock', 'far fa-binary-lock', 'fas fa-binary-lock']
	    }, {
	      className: 'far fa-binary-slash',
	      options: ['fat fa-binary-slash', 'fal fa-binary-slash', 'far fa-binary-slash', 'fas fa-binary-slash']
	    }, {
	      className: 'far fa-box-archive',
	      options: ['fat fa-box-archive', 'fal fa-box-archive', 'far fa-box-archive', 'fas fa-box-archive']
	    }, {
	      className: 'far fa-bracket-curly',
	      options: ['fat fa-bracket-curly', 'fal fa-bracket-curly', 'far fa-bracket-curly', 'fas fa-bracket-curly']
	    }, {
	      className: 'far fa-bracket-curly-right',
	      options: ['fat fa-bracket-curly-right', 'fal fa-bracket-curly-right', 'far fa-bracket-curly-right', 'fas fa-bracket-curly-right']
	    }, {
	      className: 'far fa-bracket-round',
	      options: ['fat fa-bracket-round', 'fal fa-bracket-round', 'far fa-bracket-round', 'fas fa-bracket-round']
	    }, {
	      className: 'far fa-bracket-round-right',
	      options: ['fat fa-bracket-round-right', 'fal fa-bracket-round-right', 'far fa-bracket-round-right', 'fas fa-bracket-round-right']
	    }, {
	      className: 'far fa-bracket-square',
	      options: ['fat fa-bracket-square', 'fal fa-bracket-square', 'far fa-bracket-square', 'fas fa-bracket-square']
	    }, {
	      className: 'far fa-bracket-square-right',
	      options: ['fat fa-bracket-square-right', 'fal fa-bracket-square-right', 'far fa-bracket-square-right', 'fas fa-bracket-square-right']
	    }, {
	      className: 'far fa-brackets-curly',
	      options: ['fat fa-brackets-curly', 'fal fa-brackets-curly', 'far fa-brackets-curly', 'fas fa-brackets-curly']
	    }, {
	      className: 'far fa-brackets-round',
	      options: ['fat fa-brackets-round', 'fal fa-brackets-round', 'far fa-brackets-round', 'fas fa-brackets-round']
	    }, {
	      className: 'far fa-brackets-square',
	      options: ['fat fa-brackets-square', 'fal fa-brackets-square', 'far fa-brackets-square', 'fas fa-brackets-square']
	    }, {
	      className: 'far fa-brain-circuit',
	      options: ['fat fa-brain-circuit', 'fal fa-brain-circuit', 'far fa-brain-circuit', 'fas fa-brain-circuit']
	    }, {
	      className: 'far fa-browser',
	      options: ['fat fa-browser', 'fal fa-browser', 'far fa-browser', 'fas fa-browser']
	    }, {
	      className: 'far fa-browsers',
	      options: ['fat fa-browsers', 'fal fa-browsers', 'far fa-browsers', 'fas fa-browsers']
	    }, {
	      className: 'far fa-bug',
	      options: ['fat fa-bug', 'fal fa-bug', 'far fa-bug', 'fas fa-bug']
	    }, {
	      className: 'far fa-code',
	      options: ['fat fa-code', 'fal fa-code', 'far fa-code', 'fas fa-code']
	    }, {
	      className: 'far fa-code-branch',
	      options: ['fat fa-code-branch', 'fal fa-code-branch', 'far fa-code-branch', 'fas fa-code-branch']
	    }, {
	      className: 'far fa-code-commit',
	      options: ['fat fa-code-commit', 'fal fa-code-commit', 'far fa-code-commit', 'fas fa-code-commit']
	    }, {
	      className: 'far fa-code-compare',
	      options: ['fat fa-code-compare', 'fal fa-code-compare', 'far fa-code-compare', 'fas fa-code-compare']
	    }, {
	      className: 'far fa-code-fork',
	      options: ['fat fa-code-fork', 'fal fa-code-fork', 'far fa-code-fork', 'fas fa-code-fork']
	    }, {
	      className: 'far fa-code-merge',
	      options: ['fat fa-code-merge', 'fal fa-code-merge', 'far fa-code-merge', 'fas fa-code-merge']
	    }, {
	      className: 'far fa-code-pull-request',
	      options: ['fat fa-code-pull-request', 'fal fa-code-pull-request', 'far fa-code-pull-request', 'fas fa-code-pull-request']
	    }, {
	      className: 'far fa-code-pull-request-closed',
	      options: ['fat fa-code-pull-request-closed', 'fal fa-code-pull-request-closed', 'far fa-code-pull-request-closed', 'fas fa-code-pull-request-closed']
	    }, {
	      className: 'far fa-code-pull-request-draft',
	      options: ['fat fa-code-pull-request-draft', 'fal fa-code-pull-request-draft', 'far fa-code-pull-request-draft', 'fas fa-code-pull-request-draft']
	    }, {
	      className: 'far fa-code-simple',
	      options: ['fat fa-code-simple', 'fal fa-code-simple', 'far fa-code-simple', 'fas fa-code-simple']
	    }, {
	      className: 'far fa-cube',
	      options: ['fat fa-cube', 'fal fa-cube', 'far fa-cube', 'fas fa-cube']
	    }, {
	      className: 'far fa-cubes',
	      options: ['fat fa-cubes', 'fal fa-cubes', 'far fa-cubes', 'fas fa-cubes']
	    }, {
	      className: 'far fa-diagram-project',
	      options: ['fat fa-diagram-project', 'fal fa-diagram-project', 'far fa-diagram-project', 'fas fa-diagram-project']
	    }, {
	      className: 'far fa-file',
	      options: ['fat fa-file', 'fal fa-file', 'far fa-file', 'fas fa-file']
	    }, {
	      className: 'far fa-file-code',
	      options: ['fat fa-file-code', 'fal fa-file-code', 'far fa-file-code', 'fas fa-file-code']
	    }, {
	      className: 'far fa-file-lines',
	      options: ['fat fa-file-lines', 'fal fa-file-lines', 'far fa-file-lines', 'fas fa-file-lines']
	    }, {
	      className: 'far fa-filter',
	      options: ['fat fa-filter', 'fal fa-filter', 'far fa-filter', 'fas fa-filter']
	    }, {
	      className: 'far fa-fire-extinguisher',
	      options: ['fat fa-fire-extinguisher', 'fal fa-fire-extinguisher', 'far fa-fire-extinguisher', 'fas fa-fire-extinguisher']
	    }, {
	      className: 'far fa-folder',
	      options: ['fat fa-folder', 'fal fa-folder', 'far fa-folder', 'fas fa-folder']
	    }, {
	      className: 'far fa-folder-open',
	      options: ['fat fa-folder-open', 'fal fa-folder-open', 'far fa-folder-open', 'fas fa-folder-open']
	    }, {
	      className: 'far fa-font-awesome',
	      options: ['fat fa-font-awesome', 'fal fa-font-awesome', 'far fa-font-awesome', 'fas fa-font-awesome']
	    }, {
	      className: 'far fa-gears',
	      options: ['fat fa-gears', 'fal fa-gears', 'far fa-gears', 'fas fa-gears']
	    }, {
	      className: 'far fa-key-skeleton-left-right',
	      options: ['fat fa-key-skeleton-left-right', 'fal fa-key-skeleton-left-right', 'far fa-key-skeleton-left-right', 'fas fa-key-skeleton-left-right']
	    }, {
	      className: 'far fa-keyboard',
	      options: ['fat fa-keyboard', 'fal fa-keyboard', 'far fa-keyboard', 'fas fa-keyboard']
	    }, {
	      className: 'far fa-laptop-code',
	      options: ['fat fa-laptop-code', 'fal fa-laptop-code', 'far fa-laptop-code', 'fas fa-laptop-code']
	    }, {
	      className: 'far fa-laptop-mobile',
	      options: ['fat fa-laptop-mobile', 'fal fa-laptop-mobile', 'far fa-laptop-mobile', 'fas fa-laptop-mobile']
	    }, {
	      className: 'far fa-microchip',
	      options: ['fat fa-microchip', 'fal fa-microchip', 'far fa-microchip', 'fas fa-microchip']
	    }, {
	      className: 'far fa-mug-saucer',
	      options: ['fat fa-mug-saucer', 'fal fa-mug-saucer', 'far fa-mug-saucer', 'fas fa-mug-saucer']
	    }, {
	      className: 'far fa-network-wired',
	      options: ['fat fa-network-wired', 'fal fa-network-wired', 'far fa-network-wired', 'fas fa-network-wired']
	    }, {
	      className: 'far fa-notdef',
	      options: ['fat fa-notdef', 'fal fa-notdef', 'far fa-notdef', 'fas fa-notdef']
	    }, {
	      className: 'far fa-octagon-exclamation',
	      options: ['fat fa-octagon-exclamation', 'fal fa-octagon-exclamation', 'far fa-octagon-exclamation', 'fas fa-octagon-exclamation']
	    }, {
	      className: 'far fa-qrcode',
	      options: ['fat fa-qrcode', 'fal fa-qrcode', 'far fa-qrcode', 'fas fa-qrcode']
	    }, {
	      className: 'far fa-rectangle-code',
	      options: ['fat fa-rectangle-code', 'fal fa-rectangle-code', 'far fa-rectangle-code', 'fas fa-rectangle-code']
	    }, {
	      className: 'far fa-rectangle-terminal',
	      options: ['fat fa-rectangle-terminal', 'fal fa-rectangle-terminal', 'far fa-rectangle-terminal', 'fas fa-rectangle-terminal']
	    }, {
	      className: 'far fa-rectangle-xmark',
	      options: ['fat fa-rectangle-xmark', 'fal fa-rectangle-xmark', 'far fa-rectangle-xmark', 'fas fa-rectangle-xmark']
	    }, {
	      className: 'far fa-shield',
	      options: ['fat fa-shield', 'fal fa-shield', 'far fa-shield', 'fas fa-shield']
	    }, {
	      className: 'far fa-shield-blank',
	      options: ['fat fa-shield-blank', 'fal fa-shield-blank', 'far fa-shield-blank', 'fas fa-shield-blank']
	    }, {
	      className: 'far fa-shield-check',
	      options: ['fat fa-shield-check', 'fal fa-shield-check', 'far fa-shield-check', 'fas fa-shield-check']
	    }, {
	      className: 'far fa-sidebar',
	      options: ['fat fa-sidebar', 'fal fa-sidebar', 'far fa-sidebar', 'fas fa-sidebar']
	    }, {
	      className: 'far fa-sidebar-flip',
	      options: ['fat fa-sidebar-flip', 'fal fa-sidebar-flip', 'far fa-sidebar-flip', 'fas fa-sidebar-flip']
	    }, {
	      className: 'far fa-sitemap',
	      options: ['fat fa-sitemap', 'fal fa-sitemap', 'far fa-sitemap', 'fas fa-sitemap']
	    }, {
	      className: 'far fa-square-code',
	      options: ['fat fa-square-code', 'fal fa-square-code', 'far fa-square-code', 'fas fa-square-code']
	    }, {
	      className: 'far fa-square-terminal',
	      options: ['fat fa-square-terminal', 'fal fa-square-terminal', 'far fa-square-terminal', 'fas fa-square-terminal']
	    }, {
	      className: 'far fa-terminal',
	      options: ['fat fa-terminal', 'fal fa-terminal', 'far fa-terminal', 'fas fa-terminal']
	    }, {
	      className: 'far fa-user-secret',
	      options: ['fat fa-user-secret', 'fal fa-user-secret', 'far fa-user-secret', 'fas fa-user-secret']
	    }, {
	      className: 'far fa-window',
	      options: ['fat fa-window', 'fal fa-window', 'far fa-window', 'fas fa-window']
	    }, {
	      className: 'far fa-window-flip',
	      options: ['fat fa-window-flip', 'fal fa-window-flip', 'far fa-window-flip', 'fas fa-window-flip']
	    }, {
	      className: 'far fa-window-maximize',
	      options: ['fat fa-window-maximize', 'fal fa-window-maximize', 'far fa-window-maximize', 'fas fa-window-maximize']
	    }, {
	      className: 'far fa-window-minimize',
	      options: ['fat fa-window-minimize', 'fal fa-window-minimize', 'far fa-window-minimize', 'fas fa-window-minimize']
	    }, {
	      className: 'far fa-window-restore',
	      options: ['fat fa-window-restore', 'fal fa-window-restore', 'far fa-window-restore', 'fas fa-window-restore']
	    }, {
	      className: 'far fa-wrench-simple',
	      options: ['fat fa-wrench-simple', 'fal fa-wrench-simple', 'far fa-wrench-simple', 'fas fa-wrench-simple']
	    }]
	  }, {
	    id: 'communication',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_COMMUNICATION'),
	    items: [{
	      className: 'far fa-address-book',
	      options: ['fat fa-address-book', 'fal fa-address-book', 'far fa-address-book', 'fas fa-address-book']
	    }, {
	      className: 'far fa-address-card',
	      options: ['fat fa-address-card', 'fal fa-address-card', 'far fa-address-card', 'fas fa-address-card']
	    }, {
	      className: 'far fa-at',
	      options: ['fat fa-at', 'fal fa-at', 'far fa-at', 'fas fa-at']
	    }, {
	      className: 'far fa-blender-phone',
	      options: ['fat fa-blender-phone', 'fal fa-blender-phone', 'far fa-blender-phone', 'fas fa-blender-phone']
	    }, {
	      className: 'far fa-bullhorn',
	      options: ['fat fa-bullhorn', 'fal fa-bullhorn', 'far fa-bullhorn', 'fas fa-bullhorn']
	    }, {
	      className: 'far fa-circle-envelope',
	      options: ['fat fa-circle-envelope', 'fal fa-circle-envelope', 'far fa-circle-envelope', 'fas fa-circle-envelope']
	    }, {
	      className: 'far fa-circle-phone',
	      options: ['fat fa-circle-phone', 'fal fa-circle-phone', 'far fa-circle-phone', 'fas fa-circle-phone']
	    }, {
	      className: 'far fa-circle-phone-flip',
	      options: ['fat fa-circle-phone-flip', 'fal fa-circle-phone-flip', 'far fa-circle-phone-flip', 'fas fa-circle-phone-flip']
	    }, {
	      className: 'far fa-circle-phone-hangup',
	      options: ['fat fa-circle-phone-hangup', 'fal fa-circle-phone-hangup', 'far fa-circle-phone-hangup', 'fas fa-circle-phone-hangup']
	    }, {
	      className: 'far fa-comment',
	      options: ['fat fa-comment', 'fal fa-comment', 'far fa-comment', 'fas fa-comment']
	    }, {
	      className: 'far fa-comment-arrow-down',
	      options: ['fat fa-comment-arrow-down', 'fal fa-comment-arrow-down', 'far fa-comment-arrow-down', 'fas fa-comment-arrow-down']
	    }, {
	      className: 'far fa-comment-arrow-up',
	      options: ['fat fa-comment-arrow-up', 'fal fa-comment-arrow-up', 'far fa-comment-arrow-up', 'fas fa-comment-arrow-up']
	    }, {
	      className: 'far fa-comment-arrow-up-right',
	      options: ['fat fa-comment-arrow-up-right', 'fal fa-comment-arrow-up-right', 'far fa-comment-arrow-up-right', 'fas fa-comment-arrow-up-right']
	    }, {
	      className: 'far fa-comment-check',
	      options: ['fat fa-comment-check', 'fal fa-comment-check', 'far fa-comment-check', 'fas fa-comment-check']
	    }, {
	      className: 'far fa-comment-code',
	      options: ['fat fa-comment-code', 'fal fa-comment-code', 'far fa-comment-code', 'fas fa-comment-code']
	    }, {
	      className: 'far fa-comment-dots',
	      options: ['fat fa-comment-dots', 'fal fa-comment-dots', 'far fa-comment-dots', 'fas fa-comment-dots']
	    }, {
	      className: 'far fa-comment-exclamation',
	      options: ['fat fa-comment-exclamation', 'fal fa-comment-exclamation', 'far fa-comment-exclamation', 'fas fa-comment-exclamation']
	    }, {
	      className: 'far fa-comment-image',
	      options: ['fat fa-comment-image', 'fal fa-comment-image', 'far fa-comment-image', 'fas fa-comment-image']
	    }, {
	      className: 'far fa-comment-lines',
	      options: ['fat fa-comment-lines', 'fal fa-comment-lines', 'far fa-comment-lines', 'fas fa-comment-lines']
	    }, {
	      className: 'far fa-comment-medical',
	      options: ['fat fa-comment-medical', 'fal fa-comment-medical', 'far fa-comment-medical', 'fas fa-comment-medical']
	    }, {
	      className: 'far fa-comment-middle',
	      options: ['fat fa-comment-middle', 'fal fa-comment-middle', 'far fa-comment-middle', 'fas fa-comment-middle']
	    }, {
	      className: 'far fa-comment-middle-top',
	      options: ['fat fa-comment-middle-top', 'fal fa-comment-middle-top', 'far fa-comment-middle-top', 'fas fa-comment-middle-top']
	    }, {
	      className: 'far fa-comment-minus',
	      options: ['fat fa-comment-minus', 'fal fa-comment-minus', 'far fa-comment-minus', 'fas fa-comment-minus']
	    }, {
	      className: 'far fa-comment-music',
	      options: ['fat fa-comment-music', 'fal fa-comment-music', 'far fa-comment-music', 'fas fa-comment-music']
	    }, {
	      className: 'far fa-comment-pen',
	      options: ['fat fa-comment-pen', 'fal fa-comment-pen', 'far fa-comment-pen', 'fas fa-comment-pen']
	    }, {
	      className: 'far fa-comment-plus',
	      options: ['fat fa-comment-plus', 'fal fa-comment-plus', 'far fa-comment-plus', 'fas fa-comment-plus']
	    }, {
	      className: 'far fa-comment-question',
	      options: ['fat fa-comment-question', 'fal fa-comment-question', 'far fa-comment-question', 'fas fa-comment-question']
	    }, {
	      className: 'far fa-comment-quote',
	      options: ['fat fa-comment-quote', 'fal fa-comment-quote', 'far fa-comment-quote', 'fas fa-comment-quote']
	    }, {
	      className: 'far fa-comment-slash',
	      options: ['fat fa-comment-slash', 'fal fa-comment-slash', 'far fa-comment-slash', 'fas fa-comment-slash']
	    }, {
	      className: 'far fa-comment-smile',
	      options: ['fat fa-comment-smile', 'fal fa-comment-smile', 'far fa-comment-smile', 'fas fa-comment-smile']
	    }, {
	      className: 'far fa-comment-sms',
	      options: ['fat fa-comment-sms', 'fal fa-comment-sms', 'far fa-comment-sms', 'fas fa-comment-sms']
	    }, {
	      className: 'far fa-comment-text',
	      options: ['fat fa-comment-text', 'fal fa-comment-text', 'far fa-comment-text', 'fas fa-comment-text']
	    }, {
	      className: 'far fa-comment-xmark',
	      options: ['fat fa-comment-xmark', 'fal fa-comment-xmark', 'far fa-comment-xmark', 'fas fa-comment-xmark']
	    }, {
	      className: 'far fa-comments',
	      options: ['fat fa-comments', 'fal fa-comments', 'far fa-comments', 'fas fa-comments']
	    }, {
	      className: 'far fa-comments-question',
	      options: ['fat fa-comments-question', 'fal fa-comments-question', 'far fa-comments-question', 'fas fa-comments-question']
	    }, {
	      className: 'far fa-comments-question-check',
	      options: ['fat fa-comments-question-check', 'fal fa-comments-question-check', 'far fa-comments-question-check', 'fas fa-comments-question-check']
	    }, {
	      className: 'far fa-crystal-ball',
	      options: ['fat fa-crystal-ball', 'fal fa-crystal-ball', 'far fa-crystal-ball', 'fas fa-crystal-ball']
	    }, {
	      className: 'far fa-ear-listen',
	      options: ['fat fa-ear-listen', 'fal fa-ear-listen', 'far fa-ear-listen', 'fas fa-ear-listen']
	    }, {
	      className: 'far fa-envelope',
	      options: ['fat fa-envelope', 'fal fa-envelope', 'far fa-envelope', 'fas fa-envelope']
	    }, {
	      className: 'far fa-envelope-dot',
	      options: ['fat fa-envelope-dot', 'fal fa-envelope-dot', 'far fa-envelope-dot', 'fas fa-envelope-dot']
	    }, {
	      className: 'far fa-envelope-open',
	      options: ['fat fa-envelope-open', 'fal fa-envelope-open', 'far fa-envelope-open', 'fas fa-envelope-open']
	    }, {
	      className: 'far fa-envelopes',
	      options: ['fat fa-envelopes', 'fal fa-envelopes', 'far fa-envelopes', 'fas fa-envelopes']
	    }, {
	      className: 'far fa-face-awesome',
	      options: ['fat fa-face-awesome', 'fal fa-face-awesome', 'far fa-face-awesome', 'fas fa-face-awesome']
	    }, {
	      className: 'far fa-face-frown',
	      options: ['fat fa-face-frown', 'fal fa-face-frown', 'far fa-face-frown', 'fas fa-face-frown']
	    }, {
	      className: 'far fa-face-meh',
	      options: ['fat fa-face-meh', 'fal fa-face-meh', 'far fa-face-meh', 'fas fa-face-meh']
	    }, {
	      className: 'far fa-face-smile',
	      options: ['fat fa-face-smile', 'fal fa-face-smile', 'far fa-face-smile', 'fas fa-face-smile']
	    }, {
	      className: 'far fa-face-smile-plus',
	      options: ['fat fa-face-smile-plus', 'fal fa-face-smile-plus', 'far fa-face-smile-plus', 'fas fa-face-smile-plus']
	    }, {
	      className: 'far fa-fax',
	      options: ['fat fa-fax', 'fal fa-fax', 'far fa-fax', 'fas fa-fax']
	    }, {
	      className: 'far fa-hands-asl-interpreting',
	      options: ['fat fa-hands-asl-interpreting', 'fal fa-hands-asl-interpreting', 'far fa-hands-asl-interpreting', 'fas fa-hands-asl-interpreting']
	    }, {
	      className: 'far fa-hundred-points',
	      options: ['fat fa-hundred-points', 'fal fa-hundred-points', 'far fa-hundred-points', 'fas fa-hundred-points']
	    }, {
	      className: 'far fa-icons',
	      options: ['fat fa-icons', 'fal fa-icons', 'far fa-icons', 'fas fa-icons']
	    }, {
	      className: 'far fa-inbox',
	      options: ['fat fa-inbox', 'fal fa-inbox', 'far fa-inbox', 'fas fa-inbox']
	    }, {
	      className: 'far fa-inbox-in',
	      options: ['fat fa-inbox-in', 'fal fa-inbox-in', 'far fa-inbox-in', 'fas fa-inbox-in']
	    }, {
	      className: 'far fa-inbox-out',
	      options: ['fat fa-inbox-out', 'fal fa-inbox-out', 'far fa-inbox-out', 'fas fa-inbox-out']
	    }, {
	      className: 'far fa-language',
	      options: ['fat fa-language', 'fal fa-language', 'far fa-language', 'fas fa-language']
	    }, {
	      className: 'far fa-message',
	      options: ['fat fa-message', 'fal fa-message', 'far fa-message', 'fas fa-message']
	    }, {
	      className: 'far fa-message-arrow-down',
	      options: ['fat fa-message-arrow-down', 'fal fa-message-arrow-down', 'far fa-message-arrow-down', 'fas fa-message-arrow-down']
	    }, {
	      className: 'far fa-message-arrow-up',
	      options: ['fat fa-message-arrow-up', 'fal fa-message-arrow-up', 'far fa-message-arrow-up', 'fas fa-message-arrow-up']
	    }, {
	      className: 'far fa-message-arrow-up-right',
	      options: ['fat fa-message-arrow-up-right', 'fal fa-message-arrow-up-right', 'far fa-message-arrow-up-right', 'fas fa-message-arrow-up-right']
	    }, {
	      className: 'far fa-message-bot',
	      options: ['fat fa-message-bot', 'fal fa-message-bot', 'far fa-message-bot', 'fas fa-message-bot']
	    }, {
	      className: 'far fa-message-check',
	      options: ['fat fa-message-check', 'fal fa-message-check', 'far fa-message-check', 'fas fa-message-check']
	    }, {
	      className: 'far fa-message-code',
	      options: ['fat fa-message-code', 'fal fa-message-code', 'far fa-message-code', 'fas fa-message-code']
	    }, {
	      className: 'far fa-message-dots',
	      options: ['fat fa-message-dots', 'fal fa-message-dots', 'far fa-message-dots', 'fas fa-message-dots']
	    }, {
	      className: 'far fa-message-exclamation',
	      options: ['fat fa-message-exclamation', 'fal fa-message-exclamation', 'far fa-message-exclamation', 'fas fa-message-exclamation']
	    }, {
	      className: 'far fa-message-image',
	      options: ['fat fa-message-image', 'fal fa-message-image', 'far fa-message-image', 'fas fa-message-image']
	    }, {
	      className: 'far fa-message-lines',
	      options: ['fat fa-message-lines', 'fal fa-message-lines', 'far fa-message-lines', 'fas fa-message-lines']
	    }, {
	      className: 'far fa-message-medical',
	      options: ['fat fa-message-medical', 'fal fa-message-medical', 'far fa-message-medical', 'fas fa-message-medical']
	    }, {
	      className: 'far fa-message-middle',
	      options: ['fat fa-message-middle', 'fal fa-message-middle', 'far fa-message-middle', 'fas fa-message-middle']
	    }, {
	      className: 'far fa-message-middle-top',
	      options: ['fat fa-message-middle-top', 'fal fa-message-middle-top', 'far fa-message-middle-top', 'fas fa-message-middle-top']
	    }, {
	      className: 'far fa-message-minus',
	      options: ['fat fa-message-minus', 'fal fa-message-minus', 'far fa-message-minus', 'fas fa-message-minus']
	    }, {
	      className: 'far fa-message-pen',
	      options: ['fat fa-message-pen', 'fal fa-message-pen', 'far fa-message-pen', 'fas fa-message-pen']
	    }, {
	      className: 'far fa-message-plus',
	      options: ['fat fa-message-plus', 'fal fa-message-plus', 'far fa-message-plus', 'fas fa-message-plus']
	    }, {
	      className: 'far fa-message-question',
	      options: ['fat fa-message-question', 'fal fa-message-question', 'far fa-message-question', 'fas fa-message-question']
	    }, {
	      className: 'far fa-message-quote',
	      options: ['fat fa-message-quote', 'fal fa-message-quote', 'far fa-message-quote', 'fas fa-message-quote']
	    }, {
	      className: 'far fa-message-slash',
	      options: ['fat fa-message-slash', 'fal fa-message-slash', 'far fa-message-slash', 'fas fa-message-slash']
	    }, {
	      className: 'far fa-message-smile',
	      options: ['fat fa-message-smile', 'fal fa-message-smile', 'far fa-message-smile', 'fas fa-message-smile']
	    }, {
	      className: 'far fa-message-sms',
	      options: ['fat fa-message-sms', 'fal fa-message-sms', 'far fa-message-sms', 'fas fa-message-sms']
	    }, {
	      className: 'far fa-message-text',
	      options: ['fat fa-message-text', 'fal fa-message-text', 'far fa-message-text', 'fas fa-message-text']
	    }, {
	      className: 'far fa-message-xmark',
	      options: ['fat fa-message-xmark', 'fal fa-message-xmark', 'far fa-message-xmark', 'fas fa-message-xmark']
	    }, {
	      className: 'far fa-messages',
	      options: ['fat fa-messages', 'fal fa-messages', 'far fa-messages', 'fas fa-messages']
	    }, {
	      className: 'far fa-messages-question',
	      options: ['fat fa-messages-question', 'fal fa-messages-question', 'far fa-messages-question', 'fas fa-messages-question']
	    }, {
	      className: 'far fa-microphone',
	      options: ['fat fa-microphone', 'fal fa-microphone', 'far fa-microphone', 'fas fa-microphone']
	    }, {
	      className: 'far fa-microphone-lines',
	      options: ['fat fa-microphone-lines', 'fal fa-microphone-lines', 'far fa-microphone-lines', 'fas fa-microphone-lines']
	    }, {
	      className: 'far fa-microphone-lines-slash',
	      options: ['fat fa-microphone-lines-slash', 'fal fa-microphone-lines-slash', 'far fa-microphone-lines-slash', 'fas fa-microphone-lines-slash']
	    }, {
	      className: 'far fa-microphone-slash',
	      options: ['fat fa-microphone-slash', 'fal fa-microphone-slash', 'far fa-microphone-slash', 'fas fa-microphone-slash']
	    }, {
	      className: 'far fa-mobile',
	      options: ['fat fa-mobile', 'fal fa-mobile', 'far fa-mobile', 'fas fa-mobile']
	    }, {
	      className: 'far fa-mobile-button',
	      options: ['fat fa-mobile-button', 'fal fa-mobile-button', 'far fa-mobile-button', 'fas fa-mobile-button']
	    }, {
	      className: 'far fa-mobile-notch',
	      options: ['fat fa-mobile-notch', 'fal fa-mobile-notch', 'far fa-mobile-notch', 'fas fa-mobile-notch']
	    }, {
	      className: 'far fa-mobile-screen',
	      options: ['fat fa-mobile-screen', 'fal fa-mobile-screen', 'far fa-mobile-screen', 'fas fa-mobile-screen']
	    }, {
	      className: 'far fa-mobile-screen-button',
	      options: ['fat fa-mobile-screen-button', 'fal fa-mobile-screen-button', 'far fa-mobile-screen-button', 'fas fa-mobile-screen-button']
	    }, {
	      className: 'far fa-paper-plane',
	      options: ['fat fa-paper-plane', 'fal fa-paper-plane', 'far fa-paper-plane', 'fas fa-paper-plane']
	    }, {
	      className: 'far fa-paper-plane-top',
	      options: ['fat fa-paper-plane-top', 'fal fa-paper-plane-top', 'far fa-paper-plane-top', 'fas fa-paper-plane-top']
	    }, {
	      className: 'far fa-phone',
	      options: ['fat fa-phone', 'fal fa-phone', 'far fa-phone', 'fas fa-phone']
	    }, {
	      className: 'far fa-phone-arrow-down-left',
	      options: ['fat fa-phone-arrow-down-left', 'fal fa-phone-arrow-down-left', 'far fa-phone-arrow-down-left', 'fas fa-phone-arrow-down-left']
	    }, {
	      className: 'far fa-phone-arrow-up-right',
	      options: ['fat fa-phone-arrow-up-right', 'fal fa-phone-arrow-up-right', 'far fa-phone-arrow-up-right', 'fas fa-phone-arrow-up-right']
	    }, {
	      className: 'far fa-phone-flip',
	      options: ['fat fa-phone-flip', 'fal fa-phone-flip', 'far fa-phone-flip', 'fas fa-phone-flip']
	    }, {
	      className: 'far fa-phone-hangup',
	      options: ['fat fa-phone-hangup', 'fal fa-phone-hangup', 'far fa-phone-hangup', 'fas fa-phone-hangup']
	    }, {
	      className: 'far fa-phone-intercom',
	      options: ['fat fa-phone-intercom', 'fal fa-phone-intercom', 'far fa-phone-intercom', 'fas fa-phone-intercom']
	    }, {
	      className: 'far fa-phone-missed',
	      options: ['fat fa-phone-missed', 'fal fa-phone-missed', 'far fa-phone-missed', 'fas fa-phone-missed']
	    }, {
	      className: 'far fa-phone-plus',
	      options: ['fat fa-phone-plus', 'fal fa-phone-plus', 'far fa-phone-plus', 'fas fa-phone-plus']
	    }, {
	      className: 'far fa-phone-slash',
	      options: ['fat fa-phone-slash', 'fal fa-phone-slash', 'far fa-phone-slash', 'fas fa-phone-slash']
	    }, {
	      className: 'far fa-phone-volume',
	      options: ['fat fa-phone-volume', 'fal fa-phone-volume', 'far fa-phone-volume', 'fas fa-phone-volume']
	    }, {
	      className: 'far fa-phone-xmark',
	      options: ['fat fa-phone-xmark', 'fal fa-phone-xmark', 'far fa-phone-xmark', 'fas fa-phone-xmark']
	    }, {
	      className: 'far fa-poo',
	      options: ['fat fa-poo', 'fal fa-poo', 'far fa-poo', 'fas fa-poo']
	    }, {
	      className: 'far fa-quote-left',
	      options: ['fat fa-quote-left', 'fal fa-quote-left', 'far fa-quote-left', 'fas fa-quote-left']
	    }, {
	      className: 'far fa-quote-right',
	      options: ['fat fa-quote-right', 'fal fa-quote-right', 'far fa-quote-right', 'fas fa-quote-right']
	    }, {
	      className: 'far fa-square-envelope',
	      options: ['fat fa-square-envelope', 'fal fa-square-envelope', 'far fa-square-envelope', 'fas fa-square-envelope']
	    }, {
	      className: 'far fa-square-phone',
	      options: ['fat fa-square-phone', 'fal fa-square-phone', 'far fa-square-phone', 'fas fa-square-phone']
	    }, {
	      className: 'far fa-square-phone-flip',
	      options: ['fat fa-square-phone-flip', 'fal fa-square-phone-flip', 'far fa-square-phone-flip', 'fas fa-square-phone-flip']
	    }, {
	      className: 'far fa-square-phone-hangup',
	      options: ['fat fa-square-phone-hangup', 'fal fa-square-phone-hangup', 'far fa-square-phone-hangup', 'fas fa-square-phone-hangup']
	    }, {
	      className: 'far fa-square-quote',
	      options: ['fat fa-square-quote', 'fal fa-square-quote', 'far fa-square-quote', 'fas fa-square-quote']
	    }, {
	      className: 'far fa-square-rss',
	      options: ['fat fa-square-rss', 'fal fa-square-rss', 'far fa-square-rss', 'fas fa-square-rss']
	    }, {
	      className: 'far fa-symbols',
	      options: ['fat fa-symbols', 'fal fa-symbols', 'far fa-symbols', 'fas fa-symbols']
	    }, {
	      className: 'far fa-thought-bubble',
	      options: ['fat fa-thought-bubble', 'fal fa-thought-bubble', 'far fa-thought-bubble', 'fas fa-thought-bubble']
	    }, {
	      className: 'far fa-tty',
	      options: ['fat fa-tty', 'fal fa-tty', 'far fa-tty', 'fas fa-tty']
	    }, {
	      className: 'far fa-tty-answer',
	      options: ['fat fa-tty-answer', 'fal fa-tty-answer', 'far fa-tty-answer', 'fas fa-tty-answer']
	    }, {
	      className: 'far fa-video',
	      options: ['fat fa-video', 'fal fa-video', 'far fa-video', 'fas fa-video']
	    }, {
	      className: 'far fa-video-plus',
	      options: ['fat fa-video-plus', 'fal fa-video-plus', 'far fa-video-plus', 'fas fa-video-plus']
	    }, {
	      className: 'far fa-video-slash',
	      options: ['fat fa-video-slash', 'fal fa-video-slash', 'far fa-video-slash', 'fas fa-video-slash']
	    }, {
	      className: 'far fa-voicemail',
	      options: ['fat fa-voicemail', 'fal fa-voicemail', 'far fa-voicemail', 'fas fa-voicemail']
	    }, {
	      className: 'far fa-walkie-talkie',
	      options: ['fat fa-walkie-talkie', 'fal fa-walkie-talkie', 'far fa-walkie-talkie', 'fas fa-walkie-talkie']
	    }]
	  }, {
	    id: 'connectivity',
	    name: landing_loc.Loc.getMessage('LANDING_ICONS_SECTION_CONNECTIVITY'),
	    items: [{
	      className: 'far fa-bluetooth',
	      options: ['fat fa-bluetooth', 'fal fa-bluetooth', 'far fa-bluetooth', 'fas fa-bluetooth']
	    }, {
	      className: 'far fa-cloud',
	      options: ['fat fa-cloud', 'fal fa-cloud', 'far fa-cloud', 'fas fa-cloud']
	    }, {
	      className: 'far fa-cloud-arrow-down',
	      options: ['fat fa-cloud-arrow-down', 'fal fa-cloud-arrow-down', 'far fa-cloud-arrow-down', 'fas fa-cloud-arrow-down']
	    }, {
	      className: 'far fa-cloud-arrow-up',
	      options: ['fat fa-cloud-arrow-up', 'fal fa-cloud-arrow-up', 'far fa-cloud-arrow-up', 'fas fa-cloud-arrow-up']
	    }, {
	      className: 'far fa-cloud-check',
	      options: ['fat fa-cloud-check', 'fal fa-cloud-check', 'far fa-cloud-check', 'fas fa-cloud-check']
	    }, {
	      className: 'far fa-cloud-minus',
	      options: ['fat fa-cloud-minus', 'fal fa-cloud-minus', 'far fa-cloud-minus', 'fas fa-cloud-minus']
	    }, {
	      className: 'far fa-cloud-plus',
	      options: ['fat fa-cloud-plus', 'fal fa-cloud-plus', 'far fa-cloud-plus', 'fas fa-cloud-plus']
	    }, {
	      className: 'far fa-cloud-slash',
	      options: ['fat fa-cloud-slash', 'fal fa-cloud-slash', 'far fa-cloud-slash', 'fas fa-cloud-slash']
	    }, {
	      className: 'far fa-cloud-xmark',
	      options: ['fat fa-cloud-xmark', 'fal fa-cloud-xmark', 'far fa-cloud-xmark', 'fas fa-cloud-xmark']
	    }, {
	      className: 'far fa-globe',
	      options: ['fat fa-globe', 'fal fa-globe', 'far fa-globe', 'fas fa-globe']
	    }, {
	      className: 'far fa-house-signal',
	      options: ['fat fa-house-signal', 'fal fa-house-signal', 'far fa-house-signal', 'fas fa-house-signal']
	    }, {
	      className: 'far fa-link-horizontal',
	      options: ['fat fa-link-horizontal', 'fal fa-link-horizontal', 'far fa-link-horizontal', 'fas fa-link-horizontal']
	    }, {
	      className: 'far fa-link-horizontal-slash',
	      options: ['fat fa-link-horizontal-slash', 'fal fa-link-horizontal-slash', 'far fa-link-horizontal-slash', 'fas fa-link-horizontal-slash']
	    }, {
	      className: 'far fa-mobile-signal',
	      options: ['fat fa-mobile-signal', 'fal fa-mobile-signal', 'far fa-mobile-signal', 'fas fa-mobile-signal']
	    }, {
	      className: 'far fa-mobile-signal-out',
	      options: ['fat fa-mobile-signal-out', 'fal fa-mobile-signal-out', 'far fa-mobile-signal-out', 'fas fa-mobile-signal-out']
	    }, {
	      className: 'far fa-nfc-signal',
	      options: ['fat fa-nfc-signal', 'fal fa-nfc-signal', 'far fa-nfc-signal', 'fas fa-nfc-signal']
	    }, {
	      className: 'far fa-rss',
	      options: ['fat fa-rss', 'fal fa-rss', 'far fa-rss', 'fas fa-rss']
	    }, {
	      className: 'far fa-satellite-dish',
	      options: ['fat fa-satellite-dish', 'fal fa-satellite-dish', 'far fa-satellite-dish', 'fas fa-satellite-dish']
	    }, {
	      className: 'far fa-signal',
	      options: ['fat fa-signal', 'fal fa-signal', 'far fa-signal', 'fas fa-signal']
	    }, {
	      className: 'far fa-signal-bars',
	      options: ['fat fa-signal-bars', 'fal fa-signal-bars', 'far fa-signal-bars', 'fas fa-signal-bars']
	    }, {
	      className: 'far fa-signal-bars-fair',
	      options: ['fat fa-signal-bars-fair', 'fal fa-signal-bars-fair', 'far fa-signal-bars-fair', 'fas fa-signal-bars-fair']
	    }, {
	      className: 'far fa-signal-bars-good',
	      options: ['fat fa-signal-bars-good', 'fal fa-signal-bars-good', 'far fa-signal-bars-good', 'fas fa-signal-bars-good']
	    }, {
	      className: 'far fa-signal-bars-slash',
	      options: ['fat fa-signal-bars-slash', 'fal fa-signal-bars-slash', 'far fa-signal-bars-slash', 'fas fa-signal-bars-slash']
	    }, {
	      className: 'far fa-signal-bars-weak',
	      options: ['fat fa-signal-bars-weak', 'fal fa-signal-bars-weak', 'far fa-signal-bars-weak', 'fas fa-signal-bars-weak']
	    }, {
	      className: 'far fa-signal-fair',
	      options: ['fat fa-signal-fair', 'fal fa-signal-fair', 'far fa-signal-fair', 'fas fa-signal-fair']
	    }, {
	      className: 'far fa-signal-good',
	      options: ['fat fa-signal-good', 'fal fa-signal-good', 'far fa-signal-good', 'fas fa-signal-good']
	    }, {
	      className: 'far fa-signal-slash',
	      options: ['fat fa-signal-slash', 'fal fa-signal-slash', 'far fa-signal-slash', 'fas fa-signal-slash']
	    }, {
	      className: 'far fa-signal-stream',
	      options: ['fat fa-signal-stream', 'fal fa-signal-stream', 'far fa-signal-stream', 'fas fa-signal-stream']
	    }, {
	      className: 'far fa-signal-stream-slash',
	      options: ['fat fa-signal-stream-slash', 'fal fa-signal-stream-slash', 'far fa-signal-stream-slash', 'fas fa-signal-stream-slash']
	    }, {
	      className: 'far fa-signal-strong',
	      options: ['fat fa-signal-strong', 'fal fa-signal-strong', 'far fa-signal-strong', 'fas fa-signal-strong']
	    }, {
	      className: 'far fa-signal-weak',
	      options: ['fat fa-signal-weak', 'fal fa-signal-weak', 'far fa-signal-weak', 'fas fa-signal-weak']
	    }, {
	      className: 'far fa-tower-broadcast',
	      options: ['fat fa-tower-broadcast', 'fal fa-tower-broadcast', 'far fa-tower-broadcast', 'fas fa-tower-broadcast']
	    }, {
	      className: 'far fa-wifi',
	      options: ['fat fa-wifi', 'fal fa-wifi', 'far fa-wifi', 'fas fa-wifi']
	    }, {
	      className: 'far fa-wifi-exclamation',
	      options: ['fat fa-wifi-exclamation', 'fal fa-wifi-exclamation', 'far fa-wifi-exclamation', 'fas fa-wifi-exclamation']
	    }, {
	      className: 'far fa-wifi-fair',
	      options: ['fat fa-wifi-fair', 'fal fa-wifi-fair', 'far fa-wifi-fair', 'fas fa-wifi-fair']
	    }, {
	      className: 'far fa-wifi-weak',
	      options: ['fat fa-wifi-weak', 'fal fa-wifi-weak', 'far fa-wifi-weak', 'fas fa-wifi-weak']
	    }]
	  }]
	};

	exports.FontAwesome = FontAwesome;

}((this.BX.Landing.Icon = this.BX.Landing.Icon || {}),BX.Landing));
//# sourceMappingURL=fontawesome6.bundle.js.map
