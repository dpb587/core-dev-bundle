<?php

namespace DPB\Bundle\CoreDevBundle\Controller\DevDoctrine;

use Symfony\Component\DependencyInjection\ContainerAware;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as SensioExtra;

/**
 * @SensioExtra\Route("/bundle/doctrine/entity-managers")
 */
class EmsController extends ContainerAware
{
    /**
     * @SensioExtra\Route("/list.{_format}")
     * @SensioExtra\Template
     */
    public function listAction()
    {
        $manager = $this->container->get('doctrine');
        $default = $manager->getDefaultEntityManagerName();

        $results = array();

        foreach ($this->container->get('doctrine')->getEntityManagers() as $name => $em) {
            $results[$name] = array(
                'name' => $name,
                'default' => ($default == $name),
            );
        }

        return array(
            'results' => $results,
        );
    }
}
