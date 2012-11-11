<?php

namespace DPB\Bundle\CoreDevBundle\Controller\DevFramework;

use DPB\Bundle\CoreDevBundle\Utility\ArrayUtility;
use Symfony\Component\DependencyInjection\ContainerAware;

class DiController extends ContainerAware
{
    public function parametersAction()
    {
        $data = $this->container->getParameterBag()->all();
        ksort($data);

        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:DevFramework/Di:parameters.html.twig',
            array(
                'jump' => ArrayUtility::keysTOC($data),
                'data' => $data,
            )
        );
    }

    public function servicesAction()
    {
        $xml = new \DOMDocument();
        $xml->load($this->container->getParameter('debug.container.dump'));

        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('dic', 'http://symfony.com/schema/dic/services');

        $data = array();

        foreach ($xpath->query('//dic:services/dic:service') as $node) {
            $id = $node->attributes->getNamedItem('id')->nodeValue;
            $class = $node->attributes->getNamedItem('class');
            $tags = array();

            foreach ($xpath->query('dic:tag', $node) as $tag) {
                $tagName = $tag->attributes->getNamedItem('name')->nodeValue;

                if (!isset($tags[$tagName])) {
                    $tags[$tagName] = 0;
                }

                $tags[$tagName] += 1;
            }

            $data[$id] = array(
                'class' => $class ? $class->nodeValue : 'stdClass',
                'tags' => $tags,
            );
        }

        ksort($data);

        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:DevFramework/Di:services.html.twig',
            array(
                'jump' => ArrayUtility::keysTOC($data),
                'data' => $data,
            )
        );
    }

    public function tagsAction()
    {
        $xml = new \DOMDocument();
        $xml->load($this->container->getParameter('debug.container.dump'));

        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('dic', 'http://symfony.com/schema/dic/services');

        $data = array();

        foreach ($xpath->query('//dic:tag') as $node) {
            if ($node->parentNode->parentNode->nodeName != 'services') {
                // inlined and not applicable
                continue;
            }

            $name = $node->attributes->getNamedItem('name')->nodeValue;

            if (!isset($data[$name])) {
                $data[$name] = array();
            }

            $service = $node->parentNode->attributes->getNamedItem('id')->nodeValue;

            if (!isset($data[$name][$service])) {
                $data[$name][$service] = 0;
            }

            $data[$name][$service] += 1;
        }

        ksort($data);

        foreach ($data as $name => &$services) {
            ksort($services);
        }

        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:DevFramework/Di:tags.html.twig',
            array(
                'jump' => ArrayUtility::keysTOC($data),
                'data' => $data,
            )
        );
    }
}
