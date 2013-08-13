<?php
namespace Cpt\EventBundle\Twig;

class MakeJsStringExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'jsstring' => new \Twig_Filter_Method($this, 'makeJsString'),
        );
    }

    public function makeJsString($input_string)
    {
      $input_string = str_replace("\t", "", $input_string);
      $input_string = str_replace("\n", "", $input_string); 
        return $input_string;
    }

    public function getName()
    {
        return 'make_js_string_extension';
    }
}
?>
