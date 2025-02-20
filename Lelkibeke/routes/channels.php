<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tables',function(){
    return true;
});