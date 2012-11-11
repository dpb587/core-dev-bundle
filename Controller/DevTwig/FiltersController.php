<?php

namespace DPB\Bundle\CoreDevBundle\Controller\DevTwig;

use DPB\Bundle\CoreDevBundle\Utility\ArrayUtility;
use Symfony\Component\DependencyInjection\ContainerAware;

class FiltersController extends ContainerAware
{
    public function indexAction()
    {
        $filters = array();

        foreach ($this->container->get('twig')->getFilters() as $name => $filter) {
            $filters[$name] = FilterController::buildProfile($filter);
        }

        ksort($filters);

        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:DevTwig/Filters:index.html.twig',
            array(
                'jump' => ArrayUtility::keysTOC($filters),
                'data' => $filters,
            )
        );
    }

    public function topicsAction()
    {
        $filters = array();

        foreach ($this->container->get('twig')->getFilters() as $name => $filter) {
            $filters[] = $name;
        }

        sort($filters);

        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:DevTwig/Filters:topics.html.twig',
            array(
                'data' => $filters,
            )
        );
    }
}
