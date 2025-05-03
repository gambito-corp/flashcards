<?php

namespace App\Services\Chat;

use App\Models\MedisearchChat;
use App\Models\MedisearchQuestion;

class chatService
{
    public function findChat($id)
    {
        return MedisearchChat::query()->find($id);
    }
    public function loadChats($userId, $orderType = 'desc')
    {
        return MedisearchChat::where('user_id', $userId)
            ->orderBy('created_at', $orderType )
            ->get();
    }

    public function updateTitle(?int $editChatId, string $editChatName, int|string|null $id)
    {
        $chat = $this->findChat($editChatId);
        if ($chat && $chat->user_id == $id)
            $chat->title = $editChatName;
            $chat->save();
    }

    public function createNewChat(int $userId, string $title): MedisearchChat
    {
        return MedisearchChat::create([
            'user_id' => $userId,
            'title' => $title,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function loadMessages($chatId)
    {
        return MedisearchQuestion::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
