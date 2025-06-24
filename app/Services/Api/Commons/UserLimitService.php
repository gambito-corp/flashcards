<?php

namespace App\Services\Api\Commons;

use App\Models\MedisearchQuestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class UserLimitService
{
    // ✅ LÍMITES POR TIPO DE USUARIO
    const LIMITS = [
        'normal' => [
            'simple' => 200,
            'standard' => 50,
            'deep_research' => 4,
        ],
        'premium' => [
            'simple' => -1, // -1 = ilimitado
            'standard' => -1,
            'deep_research' => 50,
        ],
        'admin' => [
            'simple' => -1,
            'standard' => -1,
            'deep_research' => -1,
        ],
    ];

    /**
     * ✅ DETERMINAR TIPO DE USUARIO
     */
    public function getUserType(User $user): string
    {
        // ✅ PRIORIDAD 1: ROLES ADMINISTRATIVOS (INDEPENDIENTE DEL STATUS)
        $adminRoles = ['root', 'admin', 'rector'];
        if ($user->hasAnyRole($adminRoles)) {
            return 'admin';
        }

        // ✅ PRIORIDAD 2: STATUS PREMIUM
        if ($user->status == 1) {
            return 'premium';
        }

        // ✅ PRIORIDAD 3: USUARIO NORMAL
        return 'normal';
    }

    /**
     * ✅ OBTENER USO ACTUAL DEL MES DESDE TU MODELO EXISTENTE
     */
    public function getCurrentUsage(int $userId, string $searchType): int
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return MedisearchQuestion::where('user_id', $userId)
            ->where('model', $searchType)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();
    }

    /**
     * ✅ OBTENER TODO EL USO DEL MES ACTUAL
     */
    public function getCurrentMonthUsage(int $userId): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $usage = MedisearchQuestion::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select('model', DB::raw('count(*) as count'))
            ->groupBy('model')
            ->pluck('count', 'model')
            ->toArray();

        return [
            'simple' => $usage['simple'] ?? 0,
            'standard' => $usage['standard'] ?? 0,
            'deep_research' => $usage['deep_research'] ?? 0,
        ];
    }

    /**
     * ✅ VERIFICAR SI EL USUARIO PUEDE HACER UNA BÚSQUEDA
     */
    public function canUserSearch(User $user, string $searchType): array
    {
        $userType = $this->getUserType($user);
        $limit = self::LIMITS[$userType][$searchType] ?? 0;

        // ✅ SI ES ILIMITADO (-1), PERMITIR
        if ($limit === -1) {
            return [
                'allowed' => true,
                'remaining' => -1,
                'limit' => -1,
                'user_type' => $userType,
                'message' => 'Acceso ilimitado',
                'reset_info' => $this->getResetInfo(),
            ];
        }

        // ✅ OBTENER USO ACTUAL DEL MES
        $currentUsage = $this->getCurrentUsage($user->id, $searchType);
        $remaining = max(0, $limit - $currentUsage);
        $allowed = $currentUsage < $limit;
        $resetInfo = $this->getResetInfo();

        $message = $allowed
            ? "Quedan {$remaining} búsquedas de tipo {$searchType} este mes"
            : "Has alcanzado el límite de {$limit} búsquedas de tipo {$searchType} este mes. Se resetea en {$resetInfo['days_until_reset']} días.";

        return [
            'allowed' => $allowed,
            'current_usage' => $currentUsage,
            'remaining' => $remaining,
            'limit' => $limit,
            'user_type' => $userType,
            'message' => $message,
            'reset_info' => $resetInfo,
        ];
    }

    /**
     * ✅ OBTENER INFORMACIÓN DEL RESETEO
     */
    public function getResetInfo(): array
    {
        $now = now();
        $endOfMonth = $now->copy()->endOfMonth();
        $daysUntilReset = $now->diffInDays($endOfMonth) + 1;
        $hoursUntilReset = $now->diffInHours($endOfMonth->startOfDay()->addDay());

        return [
            'current_month' => $now->format('F Y'),
            'reset_date' => $endOfMonth->addDay()->format('Y-m-d'),
            'days_until_reset' => $daysUntilReset,
            'hours_until_reset' => $hoursUntilReset,
            'reset_message' => "Los límites se resetean el 1 de " . $now->copy()->addMonth()->format('F Y'),
        ];
    }

    /**
     * ✅ OBTENER RESUMEN DE USO CON INFORMACIÓN DE RESETEO
     */
    public function getUserUsageSummary(User $user): array
    {
        $userType = $this->getUserType($user);
        $currentUsage = $this->getCurrentMonthUsage($user->id);
        $limits = self::LIMITS[$userType];
        $resetInfo = $this->getResetInfo();

        $summary = [];
        foreach (['simple', 'standard', 'deep_research'] as $searchType) {
            $limit = $limits[$searchType];
            $usage = $currentUsage[$searchType];

            $summary[$searchType] = [
                'current_usage' => $usage,
                'limit' => $limit,
                'remaining' => $limit === -1 ? -1 : max(0, $limit - $usage),
                'percentage_used' => $limit === -1 ? 0 : min(100, ($usage / $limit) * 100),
                'is_unlimited' => $limit === -1,
                'is_exhausted' => $limit !== -1 && $usage >= $limit,
            ];
        }

        return [
            'user_type' => $userType,
            'current_period' => $resetInfo['current_month'],
            'reset_info' => $resetInfo,
            'usage' => $summary,
        ];
    }

    /**
     * ✅ OBTENER HISTORIAL DE USO DE MESES ANTERIORES
     */
    public function getUserUsageHistory(User $user, int $months = 6): array
    {
        $history = [];
        $now = now();

        for ($i = 0; $i < $months; $i++) {
            $date = $now->copy()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $monthlyUsage = MedisearchQuestion::where('user_id', $user->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->select('model', DB::raw('count(*) as count'))
                ->groupBy('model')
                ->pluck('count', 'model')
                ->toArray();

            $history[] = [
                'period' => $date->format('F Y'),
                'month' => $date->month,
                'year' => $date->year,
                'usage' => [
                    'simple' => $monthlyUsage['simple'] ?? 0,
                    'standard' => $monthlyUsage['standard'] ?? 0,
                    'deep_research' => $monthlyUsage['deep_research'] ?? 0,
                ],
                'total' => array_sum($monthlyUsage),
            ];
        }

        return $history;
    }

    /**
     * ✅ VERIFICAR SI EL USUARIO NECESITA UPGRADE
     */
    public function shouldSuggestUpgrade(User $user): array
    {
        $userType = $this->getUserType($user);

        if ($userType !== 'normal') {
            return ['suggest' => false];
        }

        $currentUsage = $this->getCurrentMonthUsage($user->id);
        $limits = self::LIMITS['normal'];

        $suggestions = [];
        foreach (['simple', 'standard', 'deep_research'] as $searchType) {
            $usage = $currentUsage[$searchType];
            $limit = $limits[$searchType];
            $percentage = ($usage / $limit) * 100;

            if ($percentage >= 80) {
                $suggestions[] = [
                    'search_type' => $searchType,
                    'usage' => $usage,
                    'limit' => $limit,
                    'percentage' => round($percentage, 1),
                ];
            }
        }

        return [
            'suggest' => !empty($suggestions),
            'reasons' => $suggestions,
            'message' => !empty($suggestions)
                ? 'Considera actualizar a Premium para acceso ilimitado'
                : null
        ];
    }

    /**
     * ✅ OBTENER ESTADÍSTICAS GENERALES DEL USUARIO
     */
    public function getUserStats(User $user): array
    {
        $totalQuestions = MedisearchQuestion::where('user_id', $user->id)->count();

        $thisMonth = MedisearchQuestion::where('user_id', $user->id)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $lastMonth = MedisearchQuestion::where('user_id', $user->id)
            ->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            ])
            ->count();

        $modelUsage = MedisearchQuestion::where('user_id', $user->id)
            ->select('model', DB::raw('count(*) as count'))
            ->groupBy('model')
            ->pluck('count', 'model')
            ->toArray();

        return [
            'total_questions' => $totalQuestions,
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'growth' => $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0,
            'model_usage' => [
                'simple' => $modelUsage['simple'] ?? 0,
                'standard' => $modelUsage['standard'] ?? 0,
                'deep_research' => $modelUsage['deep_research'] ?? 0,
            ],
            'member_since' => $user->created_at->format('F Y'),
        ];
    }
}
