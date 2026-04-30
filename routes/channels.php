<?php

use Illuminate\Support\Facades\Broadcast;

// 預設 User 頻道
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// 私人聊天頻道：只有本人才能訂閱自己的頻道
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});