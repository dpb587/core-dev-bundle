<?php

namespace DPB\Bundle\CoreDevBundle\Utility;

class BundleUtility
{
    protected $bundles;

    public function __construct(array $bundles = array())
    {
        $this->setBundles($bundles);
    }

    public function setBundles(array $bundles = array())
    {
        $this->bundles = array();

        foreach ($bundles as $name => $class) {
            $this->bundles[$name] = array(
                'name' => $name,
                'short_name' => preg_replace('/(.*)Bundle$/', '$1', $name),
                'class' => $class,
                'namespace' => substr($class, 0, strrpos($class, '\\') + 1),
                'slug' => strtolower(
                    preg_replace(
                        '/([^A-Z\-])([A-Z])/m',
                        '$1-$2',
                        preg_replace(
                            '/([A-Z0-9]+)([A-Z])([^A-Z])/m',
                            '$1-$2$3',
                            preg_replace('/(.*)Bundle$/', '$1', $name)
                        )
                    )
                ),
            );
        }
    }

    public function getBundles()
    {
        return $this->bundles;
    }

    public function findByName($name)
    {
        return isset($this->bundles[$name]) ? $this->bundles[$name] : null;
    }

    public function findByNamespace($class)
    {
        return current(
            array_filter(
                $this->bundles,
                function ($r) use ($class) {
                    return 0 === strpos($class, $r['namespace']);
                }
            )
        );
    }

    public function findBySlug($slug)
    {
        return current(
            array_filter(
                $this->bundles,
                function ($r) use ($slug) {
                    return $slug == $r['slug'];
                }
            )
        );
    }
}
