# Pakasir for WooCommerce

- Contributors: hdrxs312
- Tags: payment gateway, qris, woocommerce
- Requires at least: 4.7
- Tested up to: 6.9.4
- Stable tag: 1.3.2
- Requires PHP: 7.0
- License: GPLv2 or later
- License URI: https://www.gnu.org/licenses/gpl-2.0.html
- Pakasir Payment Gateway (QRIS, Virtual Account, etc) for WooComerce. (compatible with Indonesia banks/e-wallets only)

## External services

This plugin connects to Pakasir API to create a payment link and check it's status. Once the payment status become paid, it will make the related Woocomerce order become completed.

It doesn't send any user's data. It's only send the order id and the total amount needs to be paid.

This service is provided by "Pakasir": [terms of use](https://pakasir.com/p/tos), [privacy policy](https://pakasir.com/p/privacy).

## Prerequisite

This plugin requires WooCommerce installed and active in your Wordpress.

Also, it needs an active Pakasir project. If you didn't have it yet, please create one at [Pakasir website](https://app.pakasir.com/projects)

After creating a project, please save the `slug` and the `api key`. You will need it.

## How to install

1. After installing and activating the plugin, you can click the Manage link under the Plugin name, in the "Installed Plugins" page.

2. Paste the `slug` and `api key` that you have from the Pakasir website. (In the project detail page).

[See image here](https://github.com/user-attachments/assets/c3cdd2f1-a59d-48ec-9387-14dd7ea1f76a)


## Add webhook to Pakasir

1. Open your project in the [Pakasir website](https://app.pakasir.com/projects). Go to the project detail page.

2. Click `Edit Proyek` button, input the following webhook URL:
```
https://example.com/wp-json/pakasir/v1/webhook
```
_Replace `example.com` with you actual wordpress domain._

[See image here](https://github.com/user-attachments/assets/01357210-7840-4983-a8ee-91bfe095aef9)


## Demo

For a demo, please go to [http://knbr.wuaze.com/shop](http://knbr.wuaze.com/shop). (Thanks to [Infinityfree](https://infinityfree.com) for the free hosting).

## Resources

- [Pakasir docs](https://pakasir.com/p/docs)

