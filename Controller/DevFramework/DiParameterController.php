<?php

namespace DPB\Bundle\CoreDevBundle\Controller\DevFramework;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class DiParameterController extends ContainerAware
{
    public function exportAction(Request $request, $parameter, $_format)
    {
        $value = $this->container->getParameterBag()->get($parameter);

        switch ($_format) {
            case 'html':
                break;
            case 'json':
                $content = json_encode($value);

                break;
            case 'php':
                $request->setFormat('php', 'text/plain');
                $content = var_export($value);

                break;
            case 'xml':
                $content = '<?xml version="1.0" encoding="UTF-8" ?>';

                if (is_array($value)) {
                    $content .= '<parameter type="collection">';
                    $content .= $this->exportXml($value);
                    $content .= '</parameter>';
                } else {
                    $content .= '<parameter>' . $this->exportXml($value) . '</parameter>';
                }

                break;
            case 'yml':
                $request->setFormat('yml', 'text/plain');
                $content = Yaml::dump($value, 4);

                break;
            default:
                throw new \LogicException(sprintf('Unexpected format (%s).', $_format));
        }

        return new Response($content, 200, array('content-type' => $request->getMimetype($request->getRequestFormat())));
    }

    private function exportXml($data)
    {
        if (is_array($data)) {
            $content = '';
            $withKeys = array_keys($data) !== range(0, count($data) - 1);
    
            foreach ($data as $key => $value) {
                $content .= '<parameter' . ($withKeys ? (' key="' . htmlentities($key) . '"') : '');
    
                if (is_array($value)) {
                    $content .= ' type="collection">' . $this->exportXml($value);
                } else {
                    $content .= '>' . $this->exportXml($value);
                }
    
                $content .= '</parameter>';
            }
    
            return $content;
        } elseif (is_bool($data)) {
            return $data ? 'true' : 'false';
        } else {
            return htmlentities($data);
        }
    }
}
