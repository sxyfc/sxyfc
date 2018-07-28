<?php
namespace app\core\module;

class install
{
    public static function build_node_types()
    {
        /**
         *Step one
         */
        //get node types
        $node_types = get_module_config('node_types', SELF::MODULE);

        foreach ($node_types as $node_type) {
            test($node_type);
        }

        //create node types

        //create fields


    }
}