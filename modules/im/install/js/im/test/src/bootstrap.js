import BX from '../../../../../../main/install/js/main/core/test/old/core/internal/bootstrap';
import '../../../../../../main/install/js/main/date/main.date';
import '../../../../../../rest/install/js/rest/client/rest.client.js';
import protobuf from 'pull.protobuf';

const Date = window.BX.Main.Date;
global.BX = BX;
global.window.BX = BX;
global.BX.Main = {};
global.BX.Main.Date = Date;
global.protobuf = protobuf;