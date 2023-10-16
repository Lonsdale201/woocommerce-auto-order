# woocommerce-auto-order
Easy to use internal tool for operators to place orders on behalf of others (does not handle any payment process)

## Description
### ENG

The plugin provides the ability to easily and effortlessly place orders on the admin interface directly on behalf of another user.
> [!IMPORTANT]
> **It's important to note that it does not handle payment processes.**

The plugin supports only existing users within the system, and allows placing an order for only one product at a time (variation products can also be selected).

Optionally, you can specify the quantity, choose the status, and add a private note. It also supports custom statuses, and you can make a purchase with zero price.

On the order interface, a note is always displayed if the order was placed using the plugin (on the right-hand side order notes notification bar).

All automation runs if the order is marked as completed (for example, the user receives the membership set in WooCommerce Membership, if it has been set up), and the email is also sent out.

Navigate to the **Auto Order** menu under the WooCommerce menu item, where you can place the order.

The plugin automatically uses the existing user's default saved data (address, city, phone number, etc.) for the order.

HPOS: Not supported yet

## Leírás
### HUN

A bővítmény lehetőséget ad arra, hogy egyszerűen és könnyedén leadjunk rendelést az admin felületen közvetlenül más  felhasználó "nevében".

> [!IMPORTANT]
> **Fontos, hogy nem kezel fizetési folyamatokat.**

Csak a rendszerben meglévő felhasználót támogat, és egyszerre csak egy terméket adhatunk le rendelésként. (variációs terméket is lehet választani)

Opcionálisan megadható a mennyiség, és státusz választás is, valamint privát megjegyzés.
Támogatja az egyedi státuszokat is, továbbá nullás termékként is leadható a rendelés.

A rendelési felületen minden esetben egy megjegyzés mutatja, ha a rendelést a pluginnal adták le.  (jobb oldali rendelési jegyezetek értesítő sáv).

Minden automatizáció lefut, ha a rendelést úgy adjuk le, hogy az teljesítve van. (pl megkapja a WooCommerce membershipben beállítotott membershipet, ha az lett beállítva), továbbá az email is kimegy. 

A Woocommerce menüpont alatt navigálj az Auto Order menüpontra, ahol a rendelést leadhatod.

A plugin automatikusan a meglévő felhasználó alapértelmezett, már elmentett adatait kapcsolja be (cím, város, telszám, stb)

HPOS: Jelenleg még nem támogatott

### Changelog

### V 1.1

* Added - Zero price order function
