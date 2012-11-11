<?php

namespace DPB\Bundle\CoreDevBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;

class IndexController extends ContainerAware
{
    public function indexAction()
    {
        $bundles = $this->container->get('dpb.core_dev.bundle_utility')->getBundles();
        ksort($bundles);

        $packages = $this->container->get('dpb.core_dev.composer_utility')->getInstalled();
        usort(
            $packages,
            function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            }
        );

        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:Index:index.html.twig',
            array(
                'bundles' => $bundles,
                'packages' => $packages,
            )
        );
    }
}
