<?php
namespace Console;

return array(
    
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view' 
        ) 
    ),
    
    'console' => array(
        'router' => array(
            'routes' => array(
                
                array(
                    'options' => array(
                        'route' => 'clean-zip',
                        'defaults' => array(
                            'controller' => 'Console\Controller\CleanZip',
                            'action' => 'index' 
                        ) 
                    ) 
                ),
            ) 
        )
    ) 
)
;