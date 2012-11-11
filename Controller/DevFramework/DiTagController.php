<?php

namespace DPB\Bundle\CoreDevBundle\Controller\DevFramework;

use DPB\Bundle\CoreDevBundle\Utility\ArrayUtility;
use Symfony\Component\DependencyInjection\ContainerAware;

class DiTagController extends ContainerAware
{
    public function indexAction($tag)
    {
        $xml = new \DOMDocument();
        $xml->load($this->container->getParameter('debug.container.dump'));

        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('dic', 'http://symfony.com/schema/dic/services');

        $tags = array();

        foreach ($xpath->query('//dic:services/dic:service/dic:tag[@name="' . $tag . '"]') as $tagXml) {
            $tagAttrs = array();

            for ($i = 0; $i < $tagXml->attributes->length; $i ++) {
                $tagAttr = $tagXml->attributes->item($i);

                if ('name' == $tagAttr->nodeName) {
                    continue;
                }

                $tagAttrs[$tagAttr->nodeName] = $tagAttr->nodeValue;
            }

            ksort($tagAttrs);

            $tags[] = array(
                'service' => $tagXml->parentNode->attributes->getNamedItem('id')->nodeValue,
                'attrs' => $tagAttrs,
            );
        }

        usort(
            $tags,
            function ($a, $b) {
                if (isset($a['attrs']['event'], $b['attrs']['event']) && ($a['attrs']['event'] != $b['attrs']['event'])) {
                    return strcmp($a['attrs']['event'], $b['attrs']['event']);
                } else if (isset($a['attrs']['priority'], $b['attrs']['priority']) && ($a['attrs']['priority'] != $b['attrs']['priority'])) {
                    return ($a['attrs']['priority'] < $b['attrs']['priority']) ? -1 : 1;
                }

                return 0;
            }
        );

        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:DevFramework/DiTag:index.html.twig',
            array(
                'tag' => $tag,
                'data' => $tags,
            )
        );
    }
}
