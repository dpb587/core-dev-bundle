<?php

namespace DPB\Bundle\CoreDevBundle\Controller\DevTwig;

use DPB\Bundle\CoreDevBundle\Utility\ArrayUtility;
use Symfony\Component\DependencyInjection\ContainerAware;

class FilterController extends ContainerAware
{
    public function indexAction($filter)
    {
        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:DevTwig/Filter:index.html.twig',
            array(
                'filter' => $filter,
                'profile' => static::buildProfile($this->container->get('twig')->getFilters()[$filter]),
            )
        );
    }

    static public function buildProfile(\Twig_Filter $filter)
    {
        $profile = array(
            'defn' => array(
                'class' => null,
                'function' => null,
                'file' => null,
                'file_pkg' => null,
                'line_start' => null,
                'line_end' => null,
            ),
            'args' => array(),
        );

        if ($filter instanceof \Twig_Filter_Function) {
            $reflProxyFunction = new \ReflectionProperty('Twig_Filter_Function', 'function');
            $reflProxyFunction->setAccessible(true);
            $refl = new \ReflectionFunction($reflProxyFunction->getValue($filter));

            $profile['defn']['function'] = $refl->getName();
            $profile['defn']['file'] = $refl->getFileName();
            $profile['defn']['line_start'] = $refl->getStartLine();
            $profile['defn']['line_end'] = $refl->getEndLine();
        } elseif ($filter instanceof \Twig_Filter_Method) {
            $reflProxyExtension = new \ReflectionProperty('Twig_Filter_Method', 'extension');
            $reflProxyExtension->setAccessible(true);

            $reflProxyMethod = new \ReflectionProperty('Twig_Filter_Method', 'method');
            $reflProxyMethod->setAccessible(true);

            try {
                $refl = new \ReflectionMethod($reflProxyExtension->getValue($filter), $reflProxyMethod->getValue($filter));
            } catch (\ReflectionException $e) {
                // @todo
                return $profile;
            }

            $profile['defn']['class'] = $refl->getDeclaringClass()->getName();
            $profile['defn']['function'] = $refl->getName();
            $profile['defn']['file'] = $refl->getFileName();
            $profile['defn']['line_start'] = $refl->getStartLine();
            $profile['defn']['line_end'] = $refl->getEndLine();
        } else {
            // @todo
            return $profile;
        }

        // @todo lazy; refactor
        $profile['defn']['file_pkg'] = $GLOBALS['kernel']->getContainer()->get('dpb.core_dev.composer_utility')->identifyPackageFile($profile['defn']['file']);

        $profile['args'] = array();

        foreach (array_slice($refl->getParameters(), $filter->needsEnvironment() ? 2 : 1) as $param) {
            $profile['args'][] = array(
                'name' => $param->getName(),
                'required' => !$param->isOptional(),
                'default' => $param->isDefaultValueAvailable() ? $param->getDefaultvalue() : null,
            );
        }

        return $profile;
    }
}
