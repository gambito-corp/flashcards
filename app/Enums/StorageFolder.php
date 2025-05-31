<?php

namespace App\Enums;

/**
 * Enum que representa todas las carpetas principales usadas en el almacenamiento del sistema.
 * Incluye rutas originales y sugeridas para futuros escenarios.
 */
enum StorageFolder: string
{
    // MedByStudentes
    case MedByStudentesImages = 'MedByStudentes/Images';
    case MedByStudentesBackups = 'MedByStudentes/Backups';
    case MedByStudentesLogs = 'MedByStudentes/Logs';
    case MedByStudentesSoporte = 'MedByStudentes/Soporte';
    case Temp = 'Temp';

    // Usuarios
    case UsuariosPerfil = 'Usuarios/Perfil';
    case UsuariosPersonal = 'Usuarios/Personal';
    case UsuariosDocumentos = 'Usuarios/Documentos';
    case UsuariosConfiguracion = 'Usuarios/Configuracion';

    // MedFlash
    case MedFlashPreguntas = 'MedFlash/Preguntas';
    case MedFlashRespuestas = 'MedFlash/Respuestas';

    // MedBanks
    case MedBanksCSV = 'MedBanks/CSV';
    case MedBanksExamen = 'MedBanks/Examen';
    case MedBanksResultados = 'MedBanks/Resultados';

    // MedChat
    case MedChatConversaciones = 'MedChat/Conversaciones';
    case MedChatAdjuntos = 'MedChat/Adjuntos';

    /**
     * Retorna la ruta completa agregando un identificador específico (usuario, examen, etc.)
     */
    public function path(null|int|string $id = null): string
    {
        if ($id !== null) {
            return "{$this->value}/{$id}";
        }
        return $this->value;
    }

    public static function fromType(string $type): ?self
    {
        return match ($type) {
            'images' => self::MedByStudentesImages,
            'backups' => self::MedByStudentesBackups,
            'logs' => self::MedByStudentesLogs,
            'soporte' => self::MedByStudentesSoporte,
            'temp' => self::Temp,
            'perfil' => self::UsuariosPerfil,
            'personal' => self::UsuariosPersonal,
            'documentos' => self::UsuariosDocumentos,
            'configuracion' => self::UsuariosConfiguracion,
            'pregunta' => self::MedFlashPreguntas,
            'respuesta' => self::MedFlashRespuestas,
            'csv' => self::MedBanksCSV,
            'examen' => self::MedBanksExamen,
            'resultados' => self::MedBanksResultados,
            'conversaciones' => self::MedChatConversaciones,
            'adjuntos' => self::MedChatAdjuntos,
            default => null,
        };
    }

}
