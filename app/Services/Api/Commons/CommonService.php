<?php

namespace App\Services\Api\Commons;

use Illuminate\Support\Facades\Auth;

class CommonService
{
    /**
     * Obtener el menú filtrado para el usuario autenticado
     */
    public function getFilteredMenu($user = null, bool $isAdminView = false): array
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $allMenuItems = config('menu');

        // Eliminar claves innecesarias
        if (isset($allMenuItems['active'])) {
            unset($allMenuItems['active']);
        }

        // Filtrar menú basado en roles y grupos
        $filteredMenu = array_filter($allMenuItems, function ($item) use ($isAdminView, $user) {
            // Filtrado de roles
            if (isset($item['roles']) && count($item['roles']) > 0) {
                if (!$user->hasAnyRole($item['roles'])) {
                    return false;
                }
            }

            // Filtrado por grupo
            if (!isset($item['group'])) {
                return true;
            }

            if ($item['group'] === 'common') {
                return true;
            }

            if ($isAdminView && $item['group'] === 'admin') {
                return true;
            }

            if (!$isAdminView && $item['group'] === 'user') {
                return true;
            }

            return false;
        });

        return array_values($filteredMenu);
    }

    /**
     * Formatear el menú para React
     */
    public function formatMenuForReact(array $menuItems): array
    {
        return array_map(function ($item) {
            return [
                'name' => $item['name'],
                'route' => $item['route'],
                'url' => route($item['route']),
                'active' => false,
                'need_premium' => $item['need_premium'] ?? false,
            ];
        }, $menuItems);
    }

    /**
     * Obtener información del usuario de forma segura
     */
    public function getUserInfo($user = null): array
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        // Obtener roles de forma segura
        $userRoles = $this->getUserRoles($user);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'has_premium' => $user->status == 1,
            'roles' => $userRoles,
            'profile_photo_url' => $user->profile_photo_url ?? null,
        ];
    }

    /**
     * Obtener roles del usuario de forma segura
     */
    private function getUserRoles($user): array
    {
        $userRoles = [];

        if (method_exists($user, 'getRoleNames')) {
            $roleNames = $user->getRoleNames();

            if (is_object($roleNames) && method_exists($roleNames, 'toArray')) {
                $userRoles = $roleNames->toArray();
            } elseif (is_array($roleNames)) {
                $userRoles = $roleNames;
            } else {
                $userRoles = is_string($roleNames) ? [$roleNames] : [];
            }
        }

        return $userRoles;
    }

    /**
     * Obtener teams del usuario
     */
    public function getUserTeams($user = null): array
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $teams = collect();
        $currentTeam = null;

        if (method_exists($user, 'teams') && $user->teams) {
            $teams = $user->teams->map(function ($team) use ($user) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'is_current' => $team->id === $user->current_team_id
                ];
            });

            $currentTeam = $user->currentTeam;
        }

        return [
            'teams' => $teams->toArray(),
            'current_team' => $currentTeam ? [
                'id' => $currentTeam->id,
                'name' => $currentTeam->name
            ] : null
        ];
    }

    /**
     * Cambiar team del usuario
     */
    public function switchUserTeam(int $teamId, $user = null): array
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        // Verificar que el usuario pertenece al equipo
        $team = $user->teams()->find($teamId);

        if (!$team) {
            throw new \Exception('No tienes acceso a este equipo');
        }

        // Cambiar equipo actual
        $user->switchTeam($team);

        return [
            'id' => $team->id,
            'name' => $team->name
        ];
    }

    /**
     * Determinar si estamos en vista de administración
     */
    public function isAdminView(): bool
    {
        return request()->is('admin/*') || request()->routeIs('admin.*');
    }

    /**
     * Obtener datos completos para React
     */
    public function getCompleteMenuData($user = null): array
    {
        $user = $user ?? Auth::user();
        $isAdminView = $this->isAdminView();

        // Obtener menú filtrado
        $filteredMenu = $this->getFilteredMenu($user, $isAdminView);
        $formattedMenu = $this->formatMenuForReact($filteredMenu);

        // Obtener información del usuario
        $userInfo = $this->getUserInfo($user);

        // Obtener teams
        $teamsData = $this->getUserTeams($user);

        return [
            'menu' => $formattedMenu,
            'user' => $userInfo,
            'teams' => $teamsData['teams'],
            'current_team' => $teamsData['current_team'],
            'user_info' => [
                'is_admin_view' => $isAdminView,
                'has_premium' => $userInfo['has_premium'],
                'roles' => $userInfo['roles']
            ]
        ];
    }
}
