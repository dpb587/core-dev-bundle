<?php

namespace DPB\Bundle\CoreDevBundle\Controller\DevFramework;

use DPB\Bundle\CoreDevBundle\Utility\ArrayUtility;
use Symfony\Component\DependencyInjection\ContainerAware;

class DiServiceController extends ContainerAware
{
    public function indexAction($service)
    {
        $xml = new \DOMDocument();
        $xml->load($this->container->getParameter('debug.container.dump'));

        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('dic', 'http://symfony.com/schema/dic/services');

        $serviceXml = $xpath->query('//dic:services/dic:service[@id="' . $service . '"]');

        if (1 !== $serviceXml->length) {
            throw new \InvalidArgumentException(sprintf('Service "%s" not found.', $service));
        }

        $serviceXml = $serviceXml->item(0);

        $public = $serviceXml->attributes->getNamedItem('public');
        $public = $public ? $public->nodeValue : null;

        $scope = $serviceXml->attributes->getNamedItem('scope');
        $scope = $scope ? $scope->nodeValue : null;

        $class = $serviceXml->attributes->getNamedItem('class');
        $class = $class ? $class->nodeValue : null;

        $depsLocal = array();

        foreach ($xpath->query('.//dic:argument[@type="service" and @id]', $serviceXml) as $node) {
            $depsLocal[$node->attributes->getNamedItem('id')->nodeValue] = true;
        }

        ksort($depsLocal);

        $depsForeign = array();

        foreach ($xpath->query('//dic:argument[@type="service" and @id="' . $service . '"]') as $node) {
            while ($node->parentNode->nodeName != 'services') {
                $node = $node->parentNode;
            }

            $depsForeign[$node->attributes->getNamedItem('id')->nodeValue] = true;
        }

        ksort($depsForeign);

        $tags = array();

        foreach ($xpath->query('dic:tag', $serviceXml) as $tag) {
            $tagName = $tag->attributes->getNamedItem('name')->nodeValue;
            $tagAttrs = array();

            for ($i = 0; $i < $tag->attributes->length; $i ++) {
                $tagAttr = $tag->attributes->item($i);

                if ('name' == $tagAttr->nodeName) {
                    continue;
                }

                $tagAttrs[$tagAttr->nodeName] = $tagAttr->nodeValue;
            }

            ksort($tagAttrs);

            $tags[] = array(
                'name' => $tagName,
                'attrs' => $tagAttrs,
            );
        }

        usort(
            $tags,
            function ($a, $b) {
                $cmp = strcmp($a['name'], $b['name']);

                if (!$cmp) {
                    if (isset($a['attrs']['event'], $b['attrs']['event']) && ($a['attrs']['event'] != $b['attrs']['event'])) {
                        return strcmp($a['attrs']['event'], $b['attrs']['event']);
                    } else if (isset($a['attrs']['priority'], $b['attrs']['priority']) && ($a['attrs']['priority'] != $b['attrs']['priority'])) {
                        return ($a['attrs']['priority'] < $b['attrs']['priority']) ? -1 : 1;
                    }
                }

                return $cmp;
            }
        );

        $serviceReflection = new \ReflectionMethod(
            $this->container,
            'get' . strtr($service, array('_' => '', '.' => '_')) . 'Service'
        );

        $servicePhp = implode(
            "\n",
            array_slice(
                explode(
                    "\n",
                    file_get_contents($serviceReflection->getFileName())
                ),
                $serviceReflection->getStartLine() + 1,
                $serviceReflection->getEndLine() - ($serviceReflection->getStartLine() + 2)
            )
        );

        $router = $this->container->get('router');

        $servicePhpPretty = htmlentities(preg_replace('/^        /m', '', $servicePhp));
        $servicePhpPretty = preg_replace_callback(
            '/(\$this-&gt;get\(\')([^\']+)(\'\))/',
            function (array $m) use ($router) {
                return $m[1] . '<a href="' . $router->generate('dpb_coredev_devframework_diservice_index', array('service' => $m[2])) . '">' . $m[2] . '</a>' . $m[3];
            },
            $servicePhpPretty
        );
        $servicePhpPretty = '// \Symfony\Component\DependencyInjection\ContainerInterface $this' . "\n\n" . $servicePhpPretty;

        return $this->container->get('templating')->renderResponse(
            'DPBCoreDevBundle:DevFramework/DiService:index.html.twig',
            array(
                'id' => $service,
                'class' => $class,
                'scope' => $scope,
                'public' => $public,
                'deps_local' => $depsLocal,
                'deps_foreign' => $depsForeign,
                'tags' => $tags,
                'code' => $servicePhp,
                'code_pretty' => $servicePhpPretty,
            )
        );
    }
}
