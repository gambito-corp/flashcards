<?php

namespace App\Http\Controllers\Api\Commons;

use App\Http\Controllers\Controller;
use App\Services\Api\Commons\CommonService;
use Illuminate\Http\Request;

class MenuController extends Controller
{

    protected CommonService $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }


    public function getMenu(Request $request)
    {
        try {
            $data = $this->commonService->getCompleteMenuData();
            return response()->json([
                'success' => true,
                'menu' => $data['menu'],
                'user_info' => $data['user_info']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener el menú',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getTeams(Request $request)
    {
        try {
            $teamsData = $this->commonService->getUserTeams();

            return response()->json([
                'success' => true,
                'teams' => $teamsData['teams'],
                'current_team' => $teamsData['current_team']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener equipos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function switchTeam(Request $request)
    {
        try {
            $request->validate([
                'team_id' => 'required|integer|exists:teams,id'
            ]);

            $teamId = $request->input('team_id');
            $currentTeam = $this->commonService->switchUserTeam($teamId);

            return response()->json([
                'success' => true,
                'message' => 'Equipo cambiado exitosamente',
                'current_team' => $currentTeam
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cambiar equipo',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
