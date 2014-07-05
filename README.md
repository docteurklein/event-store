event-store
===========

a php5.4+ event store.


Install
-------

    git clone git@github.com:docteurklein/event-store.git
    composer install


Usage
-----

    php example/import_products.php
    php example/shop.php <product-uuid> <cart-uuid> <quantity>


Test
----

    bin/phpspec run


Contribute
----------

    bin/phpspec desc Knp\\Event\\Contributed
    bin/phpspec run

