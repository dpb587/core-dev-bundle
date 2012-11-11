<?php

namespace DPB\Bundle\CoreDevBundle\Controller\DevFramework;

use Symfony\Component\DependencyInjection\ContainerAware;

class RoutingController extends ContainerAware
{
    public function routesAction()
    {
        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:DevFramework/Routing:routes.html.twig',
            array(
                'results' => $this->container->get('router')->getRouteCollection()->all(),
            )
        );
    }
}
