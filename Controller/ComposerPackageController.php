<?php

namespace DPB\Bundle\CoreDevBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;

class ComposerPackageController extends ContainerAware
{
    public function indexAction($package)
    {
        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:ComposerPackage:index.html.twig',
            array(
                'package' => $this->container->get('dpb.core_dev.composer_utility')->findByName($package),
            )
        );
    }
}
