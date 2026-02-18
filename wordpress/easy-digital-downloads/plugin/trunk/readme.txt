=== Zeno Crypto Checkout for Easy Digital Downloads ===
Contributors: zenobank, kprajapati22
Tags: crypto, web3, bitcoin, ethereum
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accept crypto on Easy Digital Downloads with 0.1% fee

== Description ==

Accept crypto on Easy Digital Downloads with 0.1% fee

Zeno Crypto Checkout for Easy Digital Downloads lets your store receive crypto payments directly through **on-chain transactions**


See exactly what your customers will experience at checkout:  
[Try the checkout demo](https://pay.zenobank.io/demo)

Questions? Check the [FAQs](https://zenobank.io/#faqs)  
Or reach out through [Telegram](https://t.me/zenobank)

== Installation ==

1. Install via the **WordPress Plugin Installer** or upload manually.  
2. Activate the plugin under **Plugins → Installed Plugins**.  
3. Go to [dashboard.zenobank.io](https://dashboard.zenobank.io/) and create your account.  
4. Add a wallet address (this is where you will receive funds).  
5. In your dashboard, go to **Integrations → Easy Digital Download**, and click **Connect**.  
6. Copy the generated **API key**.  
7. In your EDD store, open the **Downloads -> Settings -> Payments -> Zeno** settings and paste the API key.  
8. Save changes, enable the gateway, and start accepting crypto payments.

== External services ==

This plugin connects to the [Zeno](https://zenobank.io/) API to process cryptocurrency payments. When a customer chooses to pay with crypto at checkout, the plugin sends order details (order ID, total amount, currency, product information, and a callback URL) to the Zeno API to create a checkout session and generate a payment page.

= Service details =

* **Service provider:** Zeno Bank ([zenobank.io](https://zenobank.io/))
* **API endpoint used:** `https://api.zenobank.io/api/v1/checkouts`
* **When data is sent:** Every time a customer selects the Zeno crypto payment method and proceeds to checkout.
* **What data is sent:** Order ID, payment amount, currency, product names, customer email, and a callback URL for payment status notifications. Additionally, the API key is sent as an HTTP header for authentication, and the following HTTP headers are sent for backward compatibility: plugin name, plugin version, platform (WordPress), and WordPress version.
* **Terms and Conditions:** [https://zenobank.io/terms-and-conditions/](https://zenobank.io/terms-and-conditions/)
* **Privacy Policy:** [https://zenobank.io/privacy-policy/](https://zenobank.io/privacy-policy/)

== Changelog ==

= 1.0.0 =

* Release