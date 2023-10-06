# woocommerce-auto-order
Easy to use internal tool for operators to place orders on behalf of others (does not handle any payment process)

## Description
The plugin provides the ability to easily and effortlessly place orders on the admin interface directly on behalf of another user.
> [!IMPORTANT]
> **It's important to note that it does not handle payment processes.**

The plugin supports only existing users within the system, and allows placing an order for only one product at a time (variation products can also be selected).

Optionally, you can specify the quantity, choose the status, and add a private note. It also supports custom statuses.

On the order interface, a note is always displayed if the order was placed using the plugin (on the right-hand side order notes notification bar).

All automation runs if the order is marked as completed (for example, the user receives the membership set in WooCommerce Membership, if it has been set up), and the email is also sent out.

Navigate to the **Auto Order** menu under the WooCommerce menu item, where you can place the order.

The plugin automatically uses the existing user's default saved data (address, city, phone number, etc.) for the order.
