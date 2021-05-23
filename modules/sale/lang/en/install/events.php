<?php
$MESS["SALE_CHECK_PRINT_ERROR_HTML_SUB_TITLE"] = "Welcome!";
$MESS["SALE_CHECK_PRINT_ERROR_HTML_TEXT"] = "Cannot print receipt ##CHECK_ID# for order ##ORDER_ACCOUNT_NUMBER# dated #ORDER_DATE#.

Click here to fix the problem:
http://#SERVER_NAME#/bitrix/admin/sale_order_view.php?ID=#ORDER_ID#
";
$MESS["SALE_CHECK_PRINT_ERROR_HTML_TITLE"] = "Error printing receipt";
$MESS["SALE_CHECK_PRINT_ERROR_SUBJECT"] = "Error printing receipt";
$MESS["SALE_CHECK_PRINT_ERROR_TYPE_DESC"] = "#ORDER_ACCOUNT_NUMBER# - order id
#ORDER_DATE# - order date
#ORDER_ID# - order id
#CHECK_ID# - receipt id";
$MESS["SALE_CHECK_PRINT_ERROR_TYPE_NAME"] = "Receipt printout error notification";
$MESS["SALE_CHECK_PRINT_HTML_SUB_TITLE"] = "Dear #ORDER_USER#,";
$MESS["SALE_CHECK_PRINT_HTML_TEXT"] = "your payment has been processed and a respective receipt has been created. To view the receipt, use the link:

#CHECK_LINK#

To get more details on your order ##ORDER_ID# or #ORDER_DATE# please follow this link: http://#SERVER_NAME#/personal/order/detail/#ORDER_ACCOUNT_NUMBER_ENCODE#/
";
$MESS["SALE_CHECK_PRINT_HTML_TITLE"] = "Your payment for order with #SITE_NAME#";
$MESS["SALE_CHECK_PRINT_SUBJECT"] = "Receipt link";
$MESS["SALE_CHECK_PRINT_TYPE_DESC"] = "#ORDER_ID# - order ID
#ORDER_DATE# - order date
#ORDER_USER# - customer
#ORDER_ACCOUNT_NUMBER_ENCODE# - order Id for use in links
#ORDER_PUBLIC_URL# - order view link for unauthorized users (requires configuration in the e-Store module settings)
#CHECK_LINK# - receipt link";
$MESS["SALE_CHECK_PRINT_TYPE_NAME"] = "Receipt printout notification";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_SUB_TITLE"] = "Hello!";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_TEXT"] = "
There was an issue creating receipt for order ##ORDER_ACCOUNT_NUMBER# from #ORDER_DATE#!

Click this link to fix the problem:
#LINK_URL#
";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_TITLE"] = "Error creating receipt";
$MESS["SALE_CHECK_VALIDATION_ERROR_SUBJECT"] = "Error creating receipt";
$MESS["SALE_CHECK_VALIDATION_ERROR_TYPE_DESC"] = "#ORDER_ACCOUNT_NUMBER# - order #
#ORDER_DATE# - order date
#ORDER_ID# - order ID";
$MESS["SALE_CHECK_VALIDATION_ERROR_TYPE_NAME"] = "Receipt create error notification";
$MESS["SALE_MAIL_EVENT_TEMPLATE"] = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html;charset=#SITE_CHARSET#\"/>
	<style>
		body
		{
			font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
			font-size: 14px;
			color: #000;
		}
	</style>
</head>
<body>
<table cellpadding=\"0\" cellspacing=\"0\" width=\"850\" style=\"background-color: #d1d1d1; border-radius: 2px; border:1px solid #d1d1d1; margin: 0 auto;\" border=\"1\" bordercolor=\"#d1d1d1\">
	<tr>
		<td height=\"83\" width=\"850\" bgcolor=\"#eaf3f5\" style=\"border: none; padding-top: 23px; padding-right: 17px; padding-bottom: 24px; padding-left: 17px;\">
			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
				<tr>
					<td bgcolor=\"#ffffff\" height=\"75\" style=\"font-weight: bold; text-align: center; font-size: 26px; color: #0b3961;\">#TITLE#</td>
				</tr>
				<tr>
					<td bgcolor=\"#bad3df\" height=\"11\"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width=\"850\" bgcolor=\"#f7f7f7\" valign=\"top\" style=\"border: none; padding-top: 0; padding-right: 44px; padding-bottom: 16px; padding-left: 44px;\">
			<p style=\"margin-top:30px; margin-bottom: 28px; font-weight: bold; font-size: 19px;\">#SUB_TITLE#</p>
			<p style=\"margin-top: 0; margin-bottom: 20px; line-height: 20px;\">#TEXT#</p>
		</td>
	</tr>
	<tr>
		<td height=\"40px\" width=\"850\" bgcolor=\"#f7f7f7\" valign=\"top\" style=\"border: none; padding-top: 0; padding-right: 44px; padding-bottom: 30px; padding-left: 44px;\">
			<p style=\"border-top: 1px solid #d1d1d1; margin-bottom: 5px; margin-top: 0; padding-top: 20px; line-height:21px;\">#FOOTER_BR# <a href=\"http://#SERVER_NAME#\" style=\"color:#2e6eb6;\">#FOOTER_SHOP#</a><br />
				E-mail: <a href=\"mailto:#SALE_EMAIL#\" style=\"color:#2e6eb6;\">#SALE_EMAIL#</a>
			</p>
		</td>
	</tr>
</table>
</body>
</html>";
$MESS["SALE_NEW_ORDER_DESC"] = "#ORDER_ID# - order ID
#ORDER_ACCOUNT_NUMBER_ENCODE# - order ID (for URL's)
#ORDER_REAL_ID# - real order ID
#ORDER_DATE# - order date
#ORDER_USER# - customer
#PRICE# - order amount
#EMAIL# - customer e-mail
#BCC# - BCC e-mail
#ORDER_LIST# - order contents
#ORDER_PUBLIC_URL# - order view link for unauthorized users (requires configuration in the e-Store module settings)
#SALE_EMAIL# - sales dept. e-mail";
$MESS["SALE_NEW_ORDER_HTML_SUB_TITLE"] = "Dear #ORDER_USER#,";
$MESS["SALE_NEW_ORDER_HTML_TEXT"] = "We have received your order ##ORDER_ID# of #ORDER_DATE#.

Order total: #PRICE#.

Order items:
#ORDER_LIST#

You can track your order by logging in your account at #SITE_NAME#. You will have to provide your login and password you used when registering with #SITE_NAME#.

If for some reason you want to cancel your order, use the appropriate command in your account at #SITE_NAME#.

Please refer to your order number (##ORDER_ID#) when contacting us.

Thank you for your order!
";
$MESS["SALE_NEW_ORDER_HTML_TITLE"] = "You have placed an order with #SITE_NAME#";
$MESS["SALE_NEW_ORDER_MESSAGE"] = "Order confirmation from #SITE_NAME#
------------------------------------------

Dear #ORDER_USER#,

Your order #ORDER_ID# from #ORDER_DATE# has been accepted.

Order value: #PRICE#.

Ordered items:
#ORDER_LIST#

You can monitor processing of your order (view current status 
of order) by entering your personal site section at  #SITE_NAME#.
Note that that you will need login and password for entering this
site section at #SITE_NAME#.

To cancel your order please use special option available in your
personal section at #SITE_NAME#.

Please note that you should specify your order ID:  #ORDER_ID#
when requesting any information from site administration at  #SITE_NAME#.

Thanks for ordering!
";
$MESS["SALE_NEW_ORDER_NAME"] = "New order";
$MESS["SALE_NEW_ORDER_RECURRING_DESC"] = "#ORDER_ID# - order ID
#ORDER_ACCOUNT_NUMBER_ENCODE# - order ID (for URL's)
#ORDER_REAL_ID# - real order ID
#ORDER_DATE# - order date
#ORDER_USER# - customer
#PRICE# - order amount
#EMAIL# - customer e-mail
#BCC# - BCC e-mail
#ORDER_LIST# - order contents
#SALE_EMAIL# - sales dept. e-mail";
$MESS["SALE_NEW_ORDER_RECURRING_MESSAGE"] = "Information from #SITE_NAME#\\r\\n------------------------------------------\\r\\n\\r\\nDear #ORDER_USER#,\\r\\n\\r\\nYour order ##ORDER_ID# of #ORDER_DATE# for subscription renewal has been accepted.\\r\\n\\r\\nOrder amount: #PRICE#.\\r\\n\\r\\nOrder items:\\r\\n#ORDER_LIST#\\r\\n\\r\\nYou can track the status of your order in your private area at #SITE_NAME#. Note that you will have to enter your login and password you usually use to log in to #SITE_NAME#.\\r\\n\\r\\nYou can cancel your order in your private area at #SITE_NAME#.\\r\\n\\r\\nYou are kindly asked to include your order number #ORDER_ID# in all messages you send to the #SITE_NAME# administration.\\r\\n\\r\\nThank you for you purchase!";
$MESS["SALE_NEW_ORDER_RECURRING_NAME"] = "New Order for Subscription Renewal";
$MESS["SALE_NEW_ORDER_RECURRING_SUBJECT"] = "#SITE_NAME#: New order ##ORDER_ID# for subscription renewal";
$MESS["SALE_NEW_ORDER_SUBJECT"] = "#SITE_NAME#: New order N#ORDER_ID#";
$MESS["SALE_ORDER_CANCEL_DESC"] = "#ORDER_ID# - order ID
#ORDER_ACCOUNT_NUMBER_ENCODE# - order ID (for URL's)
#ORDER_REAL_ID# - real order ID
#ORDER_DATE# - order date
#EMAIL# - customer e-mail
#ORDER_LIST# - order contents
#ORDER_CANCEL_DESCRIPTION# - reason for cancellation
#ORDER_PUBLIC_URL# - order view link for unauthorized users (requires configuration in the e-Store module settings)
#SALE_EMAIL# - sales dept. e-mail
";
$MESS["SALE_ORDER_CANCEL_HTML_SUB_TITLE"] = "Order ##ORDER_ID# of #ORDER_DATE# has been canceled.";
$MESS["SALE_ORDER_CANCEL_HTML_TEXT"] = "#ORDER_CANCEL_DESCRIPTION#

To view order details, please click here: http://#SERVER_NAME#/personal/order/#ORDER_ID#/
";
$MESS["SALE_ORDER_CANCEL_HTML_TITLE"] = "#SITE_NAME#: Cancel order ##ORDER_ID#";
$MESS["SALE_ORDER_CANCEL_MESSAGE"] = "Informational message from #SITE_NAME#
------------------------------------------

Order ##ORDER_ID# from #ORDER_DATE# is canceled.

#ORDER_CANCEL_DESCRIPTION#

#SITE_NAME#
";
$MESS["SALE_ORDER_CANCEL_NAME"] = "Cancel order";
$MESS["SALE_ORDER_CANCEL_SUBJECT"] = "#SITE_NAME#: Order N#ORDER_ID# was canceled";
$MESS["SALE_ORDER_DELIVERY_DESC"] = "#ORDER_ID# - order ID
#ORDER_ACCOUNT_NUMBER_ENCODE# - order ID (for URL's)
#ORDER_REAL_ID# - real order ID
#ORDER_DATE# - order date
#EMAIL# - customer e-mail
#ORDER_PUBLIC_URL# - order view link for unauthorized users (requires configuration in the e-Store module settings)
#SALE_EMAIL# - sales dept. e-mail";
$MESS["SALE_ORDER_DELIVERY_HTML_SUB_TITLE"] = "Order ##ORDER_ID# of #ORDER_DATE# has shipped.";
$MESS["SALE_ORDER_DELIVERY_HTML_TEXT"] = "To view order details, please click here: http://#SERVER_NAME#/personal/order/#ORDER_ID#/
";
$MESS["SALE_ORDER_DELIVERY_HTML_TITLE"] = "You order with #SITE_NAME# has shipped.";
$MESS["SALE_ORDER_DELIVERY_MESSAGE"] = "Informational message from #SITE_NAME#
------------------------------------------

Delivery of order ##ORDER_ID# from #ORDER_DATE# is allowed.

#SITE_NAME#
";
$MESS["SALE_ORDER_DELIVERY_NAME"] = "Order delivery allowed";
$MESS["SALE_ORDER_DELIVERY_SUBJECT"] = "#SITE_NAME#: Delivery of order N#ORDER_ID# is allowed";
$MESS["SALE_ORDER_PAID_DESC"] = "#ORDER_ID# - order ID
#ORDER_ACCOUNT_NUMBER_ENCODE# - order ID (for URL's)
#ORDER_REAL_ID# - real order ID
#ORDER_DATE# - order date
#EMAIL# - customer e-mail
#ORDER_PUBLIC_URL# - order view link for unauthorized users (requires configuration in the e-Store module settings)
#SALE_EMAIL# - sales dept. e-mail";
$MESS["SALE_ORDER_PAID_HTML_SUB_TITLE"] = "Your order ##ORDER_ID# of #ORDER_DATE# has been paid.";
$MESS["SALE_ORDER_PAID_HTML_TEXT"] = "To view order details, please click here: http://#SERVER_NAME#/personal/order/#ORDER_ID#/";
$MESS["SALE_ORDER_PAID_HTML_TITLE"] = "Your payment for order with #SITE_NAME#";
$MESS["SALE_ORDER_PAID_MESSAGE"] = "Informational message from #SITE_NAME#
------------------------------------------

Order ##ORDER_ID# from #ORDER_DATE# was paid.

#SITE_NAME#
";
$MESS["SALE_ORDER_PAID_NAME"] = "Paid order";
$MESS["SALE_ORDER_PAID_SUBJECT"] = "#SITE_NAME#: Order N#ORDER_ID# was paid";
$MESS["SALE_ORDER_REMIND_PAYMENT_DESC"] = "#ORDER_ID# - order ID
#ORDER_ACCOUNT_NUMBER_ENCODE# - order ID (for URL's)
#ORDER_REAL_ID# - real order ID
#ORDER_DATE# - order date
#ORDER_USER# - customer
#PRICE# - order amount
#EMAIL# - customer e-mail
#BCC# - BCC e-mail
#ORDER_LIST# - order contents
#ORDER_PUBLIC_URL# - order view link for unauthorized users (requires configuration in the e-Store module settings)
#SALE_EMAIL# - sales dept. e-mail";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_SUB_TITLE"] = "Dear #ORDER_USER#,";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_TEXT"] = "You have placed an order ##ORDER_ID# for #PRICE# on #ORDER_DATE#.

Unfortunately we have not yet received your payment. 

You can track your order by logging in your account at #SITE_NAME#. You will have to provide your login and password you used when registering with #SITE_NAME#.

If for some reason you want to cancel your order, use the appropriate command in your account at #SITE_NAME#.

Please refer to your order number (##ORDER_ID#) when contacting us.

Thank you for your order!";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_TITLE"] = "Don't forget to pay your order with #SITE_NAME#";
$MESS["SALE_ORDER_REMIND_PAYMENT_MESSAGE"] = "Information from #SITE_NAME#
------------------------------------------

Dear #ORDER_USER#,

You have placed an order ##ORDER_ID# of #ORDER_DATE#, amount: #PRICE#.

Unfortunately, it looks like your payment has not been completed. No funds has been transfered to our account. 

You can track the status of your order in your private area 
at #SITE_NAME#. Note that you will have to enter your login 
and password you usually use to log in to #SITE_NAME#.

You can cancel your order in your private area at #SITE_NAME#.

You are kindly asked to include your order number #ORDER_ID# in all messages you send to the #SITE_NAME# administration.

Thank you for your purchase!
";
$MESS["SALE_ORDER_REMIND_PAYMENT_NAME"] = "Order Payment Reminder";
$MESS["SALE_ORDER_REMIND_PAYMENT_SUBJECT"] = "#SITE_NAME#: Payment reminder for order ##ORDER_ID#";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_SUB_TITLE"] = "Dear #ORDER_USER#,";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_TEXT"] = "Status of your shipment for order ##ORDER_NO# of #ORDER_DATE# has been updated to 

\"#STATUS_NAME#\" (#STATUS_DESCRIPTION#).

Tracking number: #TRACKING_NUMBER#.

Shipped with: #DELIVERY_NAME#.

#DELIVERY_TRACKING_URL##ORDER_DETAIL_URL#
";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_TITLE"] = "Tracking information for your shipment from #SITE_NAME# has been updated";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_SUBJECT"] = "Status of your shipment from #SITE_NAME# has updated";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_TYPE_DESC"] = "#SHIPMENT_NO# - shipment ID
#SHIPMENT_DATE# - shipped on
#ORDER_NO# - order #
#ORDER_DATE# - order date
#STATUS_NAME# - status name
#STATUS_DESCRIPTION# - status description
#TRACKING_NUMBER# - tracking number
#EMAIL# - notify e-mail address
#BCC# - send copy to address
#ORDER_USER# - customer
#DELIVERY_NAME# - delivery service name
#DELIVERY_TRACKING_URL# - delivery service website for more tracking details
#ORDER_ACCOUNT_NUMBER_ENCODE# - order ID (for links)
#ORDER_PUBLIC_URL# - order view link for unauthorized users (requires configuration in the e-Store module settings)
#ORDER_DETAIL_URL# - order details URL";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_TYPE_NAME"] = "Package status update";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_SUB_TITLE"] = "Dear #ORDER_USER#,";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_TEXT"] = "Your order #ORDER_ID# from #ORDER_DATE# has been shipped.

The tracking number is: #ORDER_TRACKING_NUMBER#.

For detailed information about the order, see: http://#SERVER_NAME#/personal/order/detail/#ORDER_ID#/

E-mail: #SALE_EMAIL#
";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_TITLE"] = "The shipment number of your order from #SITE_NAME#";
$MESS["SALE_ORDER_TRACKING_NUMBER_MESSAGE"] = "Order N #ORDER_ID# from #ORDER_DATE# has been shipped by mail.

The tracking number is: #ORDER_TRACKING_NUMBER#.

For informaiton about the order, see http://#SERVER_NAME#/personal/order/detail/#ORDER_ID#/

E-mail: #SALE_EMAIL#
";
$MESS["SALE_ORDER_TRACKING_NUMBER_SUBJECT"] = "Tracking number for your order from #SITE_NAME#";
$MESS["SALE_ORDER_TRACKING_NUMBER_TYPE_DESC"] = "#ORDER_ID# - order ID
#ORDER_ACCOUNT_NUMBER_ENCODE# - order ID (for URL's)
#ORDER_REAL_ID# - real order ID
#ORDER_DATE# - order date
#ORDER_USER# - customer
#ORDER_TRACKING_NUMBER# - tracking number
#ORDER_PUBLIC_URL# - order view link for unauthorized users (requires configuration in the e-Store module settings)
#EMAIL# - customer e-mail
#BCC# - BCC e-mail
#SALE_EMAIL# - sales dept. e-mail";
$MESS["SALE_ORDER_TRACKING_NUMBER_TYPE_NAME"] = "Notification of change in tracking number ";
$MESS["SALE_RECURRING_CANCEL_DESC"] = "#ORDER_ID# - order ID
#ORDER_ACCOUNT_NUMBER_ENCODE# - order ID (for URL's)
#ORDER_REAL_ID# - real order ID
#ORDER_DATE# - order date
#EMAIL# - customer e-mail
#CANCELED_REASON# - reason for cancellation
#SALE_EMAIL# - sales dept. e-mail";
$MESS["SALE_RECURRING_CANCEL_MESSAGE"] = "Informational message from #SITE_NAME#
------------------------------------------

Recurring payment was canceled

#CANCELED_REASON#
#SITE_NAME#
";
$MESS["SALE_RECURRING_CANCEL_NAME"] = "Recurring payment canceled";
$MESS["SALE_RECURRING_CANCEL_SUBJECT"] = "#SITE_NAME#: Recurring payment was canceled";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_SUB_TITLE"] = "Dear #USER_NAME#!";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_TEXT"] = "\"#NAME#\" (#PAGE_URL#) is now back in stock.

Click here to order it now: http://#SERVER_NAME#/personal/cart/

Please remember to log yourself in before ordering.

You are receiving this message because you have asked us to keep you informed.
This message was sent by the robot; don't reply to it.

Thank you for shopping with us!
";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_TITLE"] = "Product is back in stock at #SITE_NAME#";
$MESS["SALE_SUBSCRIBE_PRODUCT_SUBJECT"] = "#SITE_NAME#: Product is back in stock";
$MESS["SKGS_STATUS_MAIL_HTML_TITLE"] = "Order updated at #SITE_NAME#";
$MESS["SMAIL_FOOTER_BR"] = "Kind regards,<br />support staff.";
$MESS["SMAIL_FOOTER_SHOP"] = "Web store";
$MESS["UP_MESSAGE"] = "Message from #SITE_NAME#
------------------------------------------

Dear #USER_NAME#,

The product you are interested in, \"#NAME#\" (#PAGE_URL#) is back in stock now.
We recommend that you place your order (http://#SERVER_NAME#/personal/cart/) as soon as possible.

You are receiving this message because you've asked to be informed when this product is available.

Sincerely,

#SITE_NAME# Customer Service";
$MESS["UP_SUBJECT"] = "#SITE_NAME#: Product is back in stock";
$MESS["UP_TYPE_SUBJECT"] = "Back in stock notification";
$MESS["UP_TYPE_SUBJECT_DESC"] = "#USER_NAME# - user name
#EMAIL# - user e-mail 
#NAME# - product name
#PAGE_URL# - product details page";
