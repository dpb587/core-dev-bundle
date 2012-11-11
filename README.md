dpb587/core-dev-bundle
======================

Support development and describing available runtime functionality.


Overview
--------

Useful for describing:

 * dependency injection (parameters, services, tags)
 * routing (accessible urls and their requirements)
 * twig filters (usage and PHP invocations)
 * composer (installed package details)

Built-in symfony2 has some related commands for debugging, but I like the links
and additional metadata that web pages can provide for quickly traversing
references.


Installation
============

Install package:

    composer.phar install dpb587/core-dev-bundle

Register bundle (e.g. `app/AppKernel.php`):

    new DPB\Bundle\CoreDevBundle\DPBCoreDevBundle(),

Register routes (e.g. `app/config/routing_dev.yml`):

    _dpb_coredev_default:
        resource: "@DPBCoreDevBundle/Resources/config/routing/default.xml"
        prefix: "/dev"

Verify:

    ./app/console server:run &
    open http://localhost:8000/dev/


LICENSE
=======

Copyright 2012 Danny Berger <dpb587@gmail.com>

MIT License (see LICENSE file)
