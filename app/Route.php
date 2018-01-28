<?php
$route->get('marcus',function($id = 123){
    echo "get i am marcus {$id}";
});
$route->post('marcus',function($id = 123){
    echo "post i am marcus {$id}";
});

$route->delete('marcus',function($id = 123){
    echo "delete i am marcus {$id}";
});

$route->post('hello',function(){
    \app\Service\Index::getInstance()->hello();
});