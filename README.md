# woocommerce-auto-order
Easy to use internal tool for operators to place orders on behalf of others (does not handle any payment process)

## Description
### ENG

The plugin provides the ability to easily and effortlessly place orders on the admin interface directly on behalf of another user.
> [!IMPORTANT]
> **It's important to note that it does not handle payment processes.**

The plugin supports both  existing users, and custom (registering method option) and allows placing an order for only one product at a time (variation products can also be selected).

Optionally, you can specify the quantity, choose the status, order date, and add a private or customer note. It also supports custom statuses, and you can make a purchase with zero price.

Starting with the new version (1.6), you now have the option to register a user while placing an order on their behalf. The process works with a standard Wordpress solution, ensuring compatibility with other extensions. 
Simply enter your email address and other details in the form. The plugin will first register and then place the order on behalf of the registered user.

On the order interface, a note is always displayed if the order was placed using the plugin (on the right-hand side order notes notification bar).

All automation runs if the order is marked as completed (for example, the user receives the membership set in WooCommerce Membership, if it has been set up), and the email is also sent out.

Navigate to the **Auto Order** menu under the WooCommerce menu item, where you can place the order.

The plugin automatically uses the existing user's default saved data (address, city, phone number, etc.) for the order. The new registration mode does not fill in any address information for the user.

> [!IMPORTANT]
> If you have registered a new user through the system, the role of the new user will automatically be Wordpress standard Subscriber. 

> [!IMPORTANT]
> If you have registered a new user through the system, You will also receive an automatically generated password and email, as this works through Wordpress's own mechanism.

## Leírás
### HUN

A bővítmény lehetőséget ad arra, hogy egyszerűen és könnyedén leadjunk rendelést az admin felületen közvetlenül más  felhasználó "nevében".

> [!IMPORTANT]
> **Fontos, hogy nem kezel fizetési folyamatokat.**

A rendszerben meglévő felhasználót mellett az új verziótól kezdve most már új felhasználói regisztrációt is elvégezhetsz, így a rendelést az új fiókhoz köti. A reigsztrációs mód a Wprdpress szabványa alapján a saját mehanikájával történik. Továbbra is egyszerre csak egy terméket adhatunk le rendelésként. (variációs terméket is lehet választani)

Opcionálisan megadható a mennyiség, és státusz választás is, rendelési dátum, valamint privát és vásárlói megjegyzés.
Támogatja az egyedi státuszokat is, továbbá nullás termékként is leadható a rendelés.

A rendelési felületen minden esetben egy megjegyzés mutatja, ha a rendelést a pluginnal adták le.  (jobb oldali rendelési jegyezetek értesítő sáv).

Minden automatizáció lefut, ha a rendelést úgy adjuk le, hogy az teljesítve van. (pl megkapja a WooCommerce membershipben beállítotott membershipet, ha az lett beállítva), továbbá az email is kimegy. 

A Woocommerce menüpont alatt navigálj az Auto Order menüpontra, ahol a rendelést leadhatod.

A plugin automatikusan a meglévő felhasználó alapértelmezett, már elmentett adatait kapcsolja be (cím, város, telszám, stb). Új regisztráció esetén ezek az adatok nem kerülnek kitöltésre. A felhasználó username = lesz a rendeléskor leadott email címmel. A jelszót a WordPress automatikusan fogja generálni.

> [!IMPORTANT]
> Ha a bővítményen keresztül egyedi regisztrációt is leadsz, a regisztrált user szerepköre automatikusan subscriver (feliratkozó lesz)

> [!IMPORTANT]
> Ha a bővítményen keresztül egyedi regisztrációt is leadsz, automatikusan generál jelszót, továbbá küldi az email-t is.

## Changelog

### v 1.6 2024-02-23
* TWEAK - All text translated to english
* New User register option before place the order
* New Customer note option
* New form submission messages

### v 1.5 2024-02-14
* TWEAK - Code Refactored

### v 1.3

* TWEAK - Plugin renamed
* TWEAK - HPOS compatibility

### V 1.2

* Added - New Order date field. You can now also specify the order date.
* Tweak - In the order status field, the default status is now completed

### V 1.1

* Added - Zero price order function
