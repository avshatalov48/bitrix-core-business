
	Installation

1. Ensure that you have the e-Store module version 4.0.10 or higher installed.

2. Copy the folder contents to any public folder of your site (e.g. you can create folder mp3 in the site root). This folder will be hereinafter referred to as the MP3 folder.

3. Create file /bitrix/php_interface/init.php (if it does exist). Insert the following code in this file:
<?
include_once($_SERVER["DOCUMENT_ROOT"]."/mp3/init_vars.php");
?>
Change the path to init_vars.php to the real path (file init_vars.php is located in the MP3 folder).

4. If required, set the following variable at the beginning of the file init_vars.php:
$mp3Price - price of one MP3 track
$mp3Currency - currency of price of one MP3 track
$mp3AccessTimeLength - period of time during which the track can be accessed
$mp3AccessTimeType - time unit of the above period (possible values: I - minute, H - hour, D - day, W - week, M - month, Q - quarter, S - half year, Y - year)
$arMP3Sums - array of possible amounts a user can add to their account; has the following format:
	array(
			product_ID = "> array(";
					"PRICE" = "> top_up_amount,";
					"CURRENCY" = "> currency_of_top_up_amount";
				),
			...
		)
	The system treats each amount as a separate product.

5. Change the path to download_private.php to the correct one in the file .htaccess in the MP3 folder.
If the MP3 folder is the folder /mp3/ (relative to the site root), the file .htaccess in the MP3 folder must contain line 
ErrorDocument 404 /mp3/download_private.php

6. If you do not provide physical delivery, you need to ensure an order does not have properties with the flag "Use as location". This will make ordering simplier. Providing only one payer type for the site will also simplify ordering.

7. Copy your MP3 files to /original/files/ (relative to the MP3 folder).

8. Open the MP3 folder in the browser.



Description

This package demonstrates an example of setting up the content selling (MP3 files); also it describes how to provide sales using static pages without having to create catalogs with either the Information Blocks or the Commercial Catalog modules.

Selling files is implemented as granting temporary access to download the purchased files. Users pay for the access using their internal accounts. Before purchasing the access, clients have to add some money to their internal accounts. The account amount defines the number of files a client can purchase.

List of files on sale is created automatically each time the file list page is accessed. A special scripts scans the folder containing files (by default, folder /original/files/ relative to the MP3 folder). Media information is extracted from the standard MP3 tags.
Therefore, to put a file for sell, you just have to copy it to the folder mentioned above. You are recommended to check for the presence of ID3 tags in your files. Otherwise, your visitors will not be able to view full track information.
Though the example is fully functional, the following limitations apply:
- the example is designed to sell MP3 files only. To support and sell other media files, you will have to alter scripts a bit;
- all files have common price;
- MP3 files are not categorized; they are displayed in a plain list. If you need to divide files into groups, you will have to enhance the example script so that it would scan subfolders appropriately.

Customers credit their accounts by purchasing one of the amounts specified in $arMP3Sums (which is defined at the installation step 4). Purchasing amount is a common procedure undertaken using standard functions by creating a record in the client’s cart and redirecting a user to the check-out page. If the site has only one payer type assigned, and none of the order properties has the flag “Use as location” set (which makes sense because physical delivery is not required), the ordering procedure will include three steps only (filling in the order form; choosing the payment system; order confirmation). This is applicable to the standard (non-customized) ordering procedure.
The user’s internal account gets credited as soon as the order becomes approved for delivery (the flag “Delivery allowed” is set).
