<?php

use Illuminate\Support\Facades\Broadcast;

// Default Laravel user channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// User notification channel
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Company notification channel
Broadcast::channel('company.{id}', function ($user, $id) {
    return $user->currentCompany && (int) $user->currentCompany->id === (int) $id;
});
