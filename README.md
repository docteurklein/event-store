docteurklein/event-store
========================

a php5.5+ event store.


Install
-------

    git clone git@github.com:docteurklein/event-store.git
    composer install


Use
---

    php example/import_products.php
    php example/shop.php <product-uuid> <cart-uuid = null> <quantity>


Test
----

    bin/phpspec run
    bin/funk funk


Contribute
----------

    bin/phpspec desc Knp\\Event\\Contributed
    bin/phpspec run

