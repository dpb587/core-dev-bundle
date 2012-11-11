<?php

namespace DPB\Bundle\CoreDevBundle\Controller;

use Symfony\Component\Config\Definition\ReferenceDumper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpFoundation\Response;

class BundleController extends ContainerAware
{
    public function indexAction($bundle)
    {
        $bundle = $this->container->get('dpb.core_dev.bundle_utility')->findBySlug($bundle);

        $bundle['object'] = $this->container->get('kernel')->getBundle($bundle['name']);
        $bundle['extension'] = $bundle['object']->getContainerExtension();
        $bundle['composer'] = $this->container->get('dpb.core_dev.composer_utility')->identifyPackageClass($bundle['class']);

        $override = array(
            'DoctrineBundle' => 'DPBCoreDevBundle:DevDoctrine:',
            'FrameworkBundle' => 'DPBCoreDevBundle:DevFramework:',
            'TwigBundle' => 'DPBCoreDevBundle:DevTwig:',
        );

        $topics = isset($override[$bundle['name']]) ? $override[$bundle['name']] : ($bundle['name'] . ':Dev:');

        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:Bundle:index.html.twig',
            array(
                'bundle' => $bundle,
                'topics' => $topics,
            )
        );
    }

    public function configurationAction($bundle)
    {
        $bundle = $this->container->get('dpb.core_dev.bundle_utility')->findBySlug($bundle);
        $bundles = $this->container->get('kernel')->getBundles();
        $extension = $bundles[$bundle['name']]->getContainerExtension();

        $container = new ContainerBuilder();

        $loader = new XmlFileLoader($container, new FileLocator());
        $loader->load($this->container->getParameter('debug.container.dump'));

        $configuration = $extension->getConfiguration(array(), $container);

        $dumper = new ReferenceDumper();

        return new Response($dumper->dump($configuration), 200, array('content-type' => 'text/plain'));
    }
}
