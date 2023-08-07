<?php
$MESS['SALE_CONVERTER_STEP_BY_STEP_MANAGER'] = "New order management system migration wizard";
$MESS['SALE_CONVERTER_MESSAGE_TITLE'] = "e-Store Conversion Wizard";
$MESS['SALE_CONVERTER_MODULE_NOT_INSTALL'] = "The e-Store module is not installed";
$MESS['SALE_CONVERTER_ENTRY'] = "Data needs to be converted to migrate to the new version of e-Store.<br><br>The\r\nnew version implements dramatic improvements making order processing even easier than before. The new features will make your customers more confident in\r\nyour sales representatives' competence, the web store trustworthy.<br><br>Order\r\nmanagement technology has been updated and improved. It's now more than just\r\nmultiple shipments per order that is possible; you can now accept payments for\r\neach shipment individually, using different payment methods.<br><br>Web store manager has a clear view of all discounts applied, can\r\nmanage discounts on the order page and recalculate the order anytime if required.<br><br>The new order management strategy provides even more flexibility. You\r\ncan easily browse any large order that contains a lot of items - order\r\nessentials are always readily available in the order page header.";

$MESS['SALE_CONVERTER_AJAX_STEP_ORDER'] = "Change order table structure";
$MESS['SALE_CONVERTER_AJAX_STEP_PROPS_VALUE'] = "Update order properties table structure";
$MESS['SALE_CONVERTER_AJAX_STEP_PROPS'] = "Update order properties table structure";
$MESS['SALE_CONVERTER_AJAX_STEP_4'] = "Update structure of existing tables";
$MESS['SALE_CONVERTER_AJAX_STEP_5'] = "Create new tables";
$MESS['SALE_CONVERTER_AJAX_STEP_DISCOUNT'] = "Discounts";
$MESS['SALE_CONVERTER_AJAX_STEP_OTHER_ALTERS'] = "Update structure of other tables";
$MESS['SALE_CONVERTER_AJAX_STEP_BIZVAL'] = "Business meanings";
$MESS['SALE_CONVERTER_AJAX_STEP_DELIVERY'] = "Delivery services";
$MESS['SALE_CONVERTER_AJAX_STEP_OTHER_CREATE'] = "Create tables for other entities";
$MESS['SALE_CONVERTER_AJAX_STEP_COPY_FILES'] = "Copy module files";
$MESS['SALE_CONVERTER_AJAX_STEP_DELIVERY_CONVERT'] = "Convert delivery services";
$MESS['SALE_CONVERTER_AJAX_STEP_STATUS_CONVERT'] = "Migrate properties and statuses";
$MESS['SALE_CONVERTER_AJAX_STEP_PAY_SYSTEM_INNER'] = "Create payment system: \"Internal account\"";
$MESS['SALE_CONVERTER_AJAX_STEP_PAY_SYSTEM_INNER_ERROR'] = "Error creating internal account";
$MESS['SALE_CONVERTER_AJAX_STEP_INSTALLER'] = "Installer";
$MESS['SALE_CONVERTER_AJAX_STEP_INSERT_PAYMENT'] = "Preparing partial payments table";
$MESS['SALE_CONVERTER_AJAX_STEP_INSERT_SHIPMENT'] = "Preparing shipment table";
$MESS['SALE_CONVERTER_AJAX_STEP_INSERT_SHIPMENT_BASKET'] = "Populating shipment table";
$MESS['SALE_CONVERTER_AJAX_STEP_UPDATE_SHIPMENT_BASKET_BARCODE'] = "Update barcode bindings";
$MESS['SALE_CONVERTER_AJAX_STEP_UPDATE_ORDER_PAYMENT'] = "Update paid orders";
$MESS['SALE_CONVERTER_AJAX_STEP_TRANSACT'] = "Update transactions table structure";
$MESS['SALE_CONVERTER_AJAX_STEP_ORDER_CHANGE'] = "Update order history table structure";
$MESS['SALE_CONVERTER_AJAX_STEP_FINAL'] = "The conversion wizard has finished.";
$MESS['SALE_CONVERTER_AJAX_STEP_FINAL_MESSAGE'] = "Conversion completed.";
$MESS['SALE_CONVERTER_BUTTON_START_AJAX'] = "Convert";
$MESS['SALE_CONVERTER_BUTTON_START'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Next&nbsp;&nbsp;&gt;&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$MESS['SALE_CONVERTER_BUTTON_NEXT'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Next&nbsp;&nbsp;&gt;&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$MESS['SALE_CONVERTER_BUTTON_REPEAT'] = "Retry";
$MESS['SALE_CONVERTER_STEP_1_MESSAGE'] = "Attention!";
$MESS['SALE_CONVERTER_STEP_1_DETAILS'] = "<b>Attention!</b><br><br><b style='color:red; font-size:15px;'>We strongly advise that\r\nyou create a backup copy of your e-Store before proceeding!</b><br><br>During conversion your e-store will be offline.\r\nProcessing a lot of orders may take quite a while. It is recommended that you\r\nperform conversion when you have least visitors.<br><br>Public area\r\nwill be offline for the whole duration of conversion process. You can always\r\nbring it online should any error occurs:&nbsp; <br><i>Settings &gt; Module\r\nSettings &gt; Kernel &gt; Open public access</i>";

$MESS['SALE_CONVERTER_STEP_2_MESSAGE'] = "Attention!";
$MESS['SALE_CONVERTER_STEP_2_DETAILS'] = "<b>Attention!</b><br><br>After conversion has completed, the following event\r\nhandlers will not be called. Proceed to the next step only if you are absolutely\r\nsure this will not alter the logic of your store. For more information please\r\ncontact your e-store developers or helpdesk.";
$MESS['SALE_CONVERTER_STEP_3_MESSAGE'] = "Convert locations";
$MESS['SALE_CONVERTER_STEP_3_DETAILS'] = "Locations need to be converted to be used with the new e-Store version.<br>\r\n<br>\r\nTo convert with options, please proceed to <a href='sale_location_migration.php?lang=en'>Migrate to Locations 2.0</a> \r\npage. After Locations has been installed, run the e-Store conversion wizard again.<br><br>\r\nFor fully automated conversion, simply click &quot;Next &gt;&gt;&quot;";

$MESS['SALE_CONVERTER_STEP_4_MESSAGE'] = "Locations converted";
$MESS['SALE_CONVERTER_STEP_4_DETAILS'] = "Migration to Locations 2.0 is complete";
$MESS['SALE_CONVERTER_STEP_5_MESSAGE'] = "Select default delivery service";
$MESS['SALE_CONVERTER_STEP_6_MESSAGE'] = "Convert e-Store";
$MESS['SALE_CONVERTER_STEP_5_DETAILS'] = "Your e-Store uses warehouse control. <br>Your store has shipped orders some of which don't specify a delivery service.<br> To prevent data loss, select the delivery service to apply to all such orders.";
$MESS['SALE_CONVERTER_STEP_6_DETAILS'] = "Once you clicked \"Start\", the conversion process begins.<br>\r\n<br>\r\nAttention! During conversion your e-store will be offline. Processing a lot of\r\norders may take quite a while. It is recommended that you perform conversion\r\nwhen you have least visitors.<br><br>Conversion will:</p>\r\n<ul><li>update order\r\n    table structure;</li><li>update structure of order property value table;</li><li>update\r\n    structure of order property table;</li><li>create new tables;</li><li>update\r\n    discounts;</li><li>convert delivery services;</li>\r\n  <li>copy module files;</li>\r\n  <li>migrate properties and statuses;</li><li>migrate data.</li></ul><br><b>Please\r\nwait until conversion has completed.</b>";

$MESS['SALE_CONVERTER_COPY_FILES_ERROR'] = "Error copying files from #FROM# to #TO#";
$MESS['SALE_CONVERTER_CHOOSE_DELIVERY_SERVICE'] = "";
$MESS['SALE_CONVERTER_MODULE_DO_NOT_SUPPORT'] = "The \"Intranet\" module is temporarily not supported.";
$MESS['SALE_CONVERTER_DB_DO_NOT_SUPPORT'] = "Oracle and MSSQL are temporarily not supported.";
$MESS['SALE_CONVERTER_AJAX_STEP_UPDATE_REPORT'] = "Migrate reports";
$MESS['SALE_CONVERTER_EMPTY_DELIVERY_SERVICE'] = "No delivery";
$MESS['SALE_CONVERTER_TITLE'] = "New order management system migration wizard";
$MESS['SALE_CONVERTER_AJAX_STEP_UPDATE_BASKET'] = "Update table structure: shopping cart";
$MESS['SALE_CONVERTER_ADMIN_NOTIFY_CONVERT_BASKET_DISCOUNT'] = "Commercial Catalog coupon data needs to be converted for the existing orders. Please proceed to <a href=\"#LINK#\">the module settings page</a>, the \"Service Operations\" area, use the \"Data Conversion\" tab.";
