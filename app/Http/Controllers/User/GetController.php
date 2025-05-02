<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GetController extends Controller
{
    /**
     * Получение данных пользователя по ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(int $id): JsonResponse
    {
        try {
            // Ищем пользователя по ID
            $user = User::findOrFail($id);

            // Возвращаем только необходимые поля
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при получении данных пользователя: ' . $e->getMessage());
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }
    }
} 