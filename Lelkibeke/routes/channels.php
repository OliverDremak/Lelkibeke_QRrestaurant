<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('tables', function () {
    return true; // Allow all authenticated users
});

Broadcast::channel('orders', function () {
    return true; // Allow all authenticated users
});