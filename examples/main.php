<?php

const a = 1;
define('b', 2);

remap(PS3_CROSS, PS3_TRIANGLE);
unmap(PS3_CROSS);

function foo()
{
    $x = 10;
    $y = 20;
    $z = $x + $y;
    return $z;
}

function main()
{
    // comment
    foo();
    set_val(PS3_CROSS, 1);
    
    if (get_val(PS3_CROSS) === true) {
        call('foo');
    }
    
    combo_run('super');
        
    set_bit('z', 5);
}

$super = function() {
    set_val(PS4_CROSS, 100);
};
