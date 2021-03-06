<?php

namespace DPB\Bundle\CoreDevBundle\Controller\DevDoctrine;

use Symfony\Component\DependencyInjection\ContainerAware;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as SensioExtra;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SensioExtra\Route("/bundle/doctrine/entity-manager/{em}")
 */
class EmController extends ContainerAware
{
    /**
     * @SensioExtra\Route("/repositories.{_format}")
     * @SensioExtra\Template
     */
    public function repositoriesAction($em)
    {
        $bundleUtility = $this->container->get('dpb.core_dev.bundle_utility');

        $results = array();

        foreach ($this->container->get('doctrine.orm.' . $em . '_entity_manager')->getMetadataFactory()->getAllMetadata() as $entity) {
            $class = $entity->getName();

            $results[] = array(
                'class' => $class,
                'bundle' => $bundleUtility->findByNamespace($class),
                'table' => $entity->getTableName(),
            );
        }

        return array(
            'results' => $results,
        );
    }
}
