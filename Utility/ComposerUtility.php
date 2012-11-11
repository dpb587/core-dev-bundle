<?php

namespace DPB\Bundle\CoreDevBundle\Utility;

class ComposerUtility
{
    protected $composer;
    protected $composerInstalled;

    public function __construct($composer)
    {
        $this->composer = realpath($composer);
    }

    public function identifyPackageClass($class)
    {
        $file = \ComposerAutoloaderInit::getLoader()->findFile($class);

        return $file ? $this->identifyPackageFile($file) : null;
    }

    public function identifyPackageFile($path)
    {
        $path = realpath($path);

        if (!preg_match('#^' . preg_quote($this->composer, '#') . '/([^/]+)/([^/]+)/(.*)$#', $path, $match)) {
            return;
        }

        $packageName = $match[1] . '/' . $match[2];
        $packageFound = null;

        foreach ($this->getInstalled() as $package) {
            if ($packageName == $package['name']) {
                $packageFound = $package;

                break;
            }
        }

        if (null === $packageFound) {
            return;
        }

        return array(
            'package' => $packageFound,
            'abs_path' => $match[3],
            'rel_path' => $match[3],
        );
    }

    public function findByName($name)
    {
        return current(
            array_filter(
                $this->getInstalled(),
                function ($r) use ($name) {
                    return $name == $r['name'];
                }
            )
        );
    }

    public function getInstalled()
    {
        if (null === $this->composerInstalled) {
            $this->composerInstalled = json_decode(file_get_contents($this->composer . '/composer/installed.json'), true);
        }

        return $this->composerInstalled;
    }

    public function getInstalledNames()
    {
        return array_map(
            function ($r) {
                return $r['name'];
            },
            $this->getInstalled()
        );
    }
}
