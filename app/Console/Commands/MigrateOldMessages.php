<?php
// app/Console/Commands/MigrateOldMessages.php

namespace App\Console\Commands;

use App\Models\MedisearchChat;
use App\Models\MedisearchQuestion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateOldMessages extends Command
{
    protected $signature = 'medchat:migrate-old-messages {--debug : Mostrar información detallada de debug} {--clean : Eliminar mensajes inválidos automáticamente}';
    protected $description = 'Migrar mensajes antiguos de medisearch_questions a nuevo formato';

    public function handle()
    {
        $this->info('Iniciando migración de mensajes antiguos...');

        if ($this->option('clean')) {
            $this->cleanInvalidMessages();
        }

        $oldMessages = DB::table('medisearch_questions')
            ->whereNotNull('response')
            ->where('response', '!=', '')
            ->where('response', '!=', '{}')
            ->get();

        $this->info("Encontrados {$oldMessages->count()} mensajes válidos para migrar");

        $migratedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $failedConversationIds = [];
        $invalidStructures = [];

        foreach ($oldMessages as $oldMessage) {
            try {
                $oldData = json_decode($oldMessage->response, true);

                // ✅ DEBUGGING MEJORADO
                if ($this->option('debug') && ($migratedCount + $skippedCount) < 5) {
                    $this->info("=== DEBUG Mensaje {$oldMessage->id} ===");
                    $this->info("Raw response: " . substr($oldMessage->response, 0, 200) . "...");
                    $this->info("JSON decode result: " . ($oldData ? 'SUCCESS' : 'FAILED'));
                    if ($oldData) {
                        $this->info("Keys: " . implode(', ', array_keys($oldData)));
                        if (isset($oldData['data'])) {
                            $this->info("Data keys: " . implode(', ', array_keys($oldData['data'])));
                        }
                    }
                }

                // ✅ VERIFICACIONES MÚLTIPLES DE ESTRUCTURA
                if (!$oldData) {
                    $invalidStructures[] = $oldMessage->id . " (JSON inválido)";
                    $skippedCount++;
                    continue;
                }

                // ✅ DETECTAR DIFERENTES ESTRUCTURAS
                $hasValidStructure = false;
                $newData = null;
                $queryText = $oldMessage->query ?? 'Pregunta migrada';

                // Estructura 1: {"data": {"query": "...", "resultados": [...]}} - FORMATO ANTIGUO
                if (isset($oldData['data']['query']) && isset($oldData['data']['resultados'])) {
                    $hasValidStructure = true;
                    $queryText = $oldData['data']['query'];
                    $newData = [
                        'data' => [
                            'resultados' => $this->adaptArticles($oldData['data']['resultados'])
                        ]
                    ];
                } // Estructura 2: {"data": {"resultados": [...]}} (ya migrado) - FORMATO NUEVO
                elseif (isset($oldData['data']['resultados']) && !isset($oldData['data']['query'])) {
                    // ✅ VERIFICAR SI NECESITA ADAPTACIÓN DE ARTÍCULOS
                    $needsAdaptation = $this->needsArticleAdaptation($oldData['data']['resultados']);
                    if ($needsAdaptation) {
                        $hasValidStructure = true;
                        $newData = [
                            'data' => [
                                'resultados' => $this->adaptArticles($oldData['data']['resultados'])
                            ]
                        ];
                    } else {
                        $skippedCount++;
                        continue; // Ya está migrado y adaptado
                    }
                } // Estructura 3: {"resultados": [...]} (sin wrapper data)
                elseif (isset($oldData['resultados'])) {
                    $hasValidStructure = true;
                    $newData = [
                        'data' => [
                            'resultados' => $this->adaptArticles($oldData['resultados'])
                        ]
                    ];
                } // Estructura 4: Respuesta directa como string
                elseif (is_string($oldMessage->response) && !empty(trim($oldMessage->response))) {
                    $hasValidStructure = true;
                    $newData = [
                        'data' => [
                            'resultados' => [
                                [
                                    'tipo' => 'llm_response',
                                    'respuesta' => trim($oldMessage->response)
                                ]
                            ]
                        ]
                    ];
                }

                if (!$hasValidStructure) {
                    $invalidStructures[] = $oldMessage->id . " (estructura no reconocida)";
                    $this->warn("Mensaje {$oldMessage->id} tiene estructura no reconocida, saltando...");
                    $skippedCount++;
                    continue;
                }

                // ✅ ACTUALIZAR REGISTRO
                MedisearchQuestion::updateOrCreate(
                    ['id' => $oldMessage->id],
                    [
                        'user_id' => $oldMessage->user_id,
                        'chat_id' => $oldMessage->chat_id,
                        'query' => $queryText,
                        'response' => $newData,
                        'created_at' => $oldMessage->created_at,
                        'updated_at' => $oldMessage->updated_at
                    ]
                );

                $migratedCount++;

                if ($migratedCount % 10 === 0) {
                    $this->info("✅ Migrados {$migratedCount} mensajes...");
                }

            } catch (\Exception $e) {
                $this->error("❌ Error migrando mensaje {$oldMessage->id}: " . $e->getMessage());
                $errorCount++;

                if ($oldMessage->chat_id) {
                    $failedConversationIds[] = $oldMessage->chat_id;
                }
            }
        }

        // ✅ RESUMEN DETALLADO
        $this->info('✅ Migración completada');
        $this->info("📊 Resumen:");
        $this->info("   - Migrados: {$migratedCount}");
        $this->info("   - Saltados: {$skippedCount}");
        $this->info("   - Errores: {$errorCount}");

        // ✅ MOSTRAR ESTRUCTURAS INVÁLIDAS
        if (count($invalidStructures) > 0) {
            $this->info('');
            $this->info('📋 Primeros 10 mensajes con estructura inválida:');
            foreach (array_slice($invalidStructures, 0, 10) as $invalid) {
                $this->info("   - {$invalid}");
            }

            if (count($invalidStructures) > 10) {
                $this->info("   ... y " . (count($invalidStructures) - 10) . " más");
            }
        }

        // ✅ MANEJAR CONVERSACIONES FALLIDAS
        $this->handleFailedConversations($failedConversationIds);

        $this->info('🎉 Proceso de migración finalizado');
    }

    /**
     * ✅ VERIFICAR SI LOS ARTÍCULOS NECESITAN ADAPTACIÓN
     */
    private function needsArticleAdaptation(array $resultados): bool
    {
        foreach ($resultados as $resultado) {
            if ($resultado['tipo'] === 'articles' && isset($resultado['articulos'])) {
                foreach ($resultado['articulos'] as $articulo) {
                    // Si tiene 'titulo' en lugar de 'title', necesita adaptación
                    if (isset($articulo['titulo']) && !isset($articulo['title'])) {
                        return true;
                    }
                    // Si tiene 'autores' en lugar de 'authors', necesita adaptación
                    if (isset($articulo['autores']) && !isset($articulo['authors'])) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * ✅ LIMPIAR MENSAJES INVÁLIDOS
     */
    private function cleanInvalidMessages(): void
    {
        $this->info('🧹 Limpiando mensajes inválidos...');

        $invalidMessages = DB::table('medisearch_questions')
            ->whereNull('response')
            ->orWhere('response', '')
            ->orWhere('response', '{}')
            ->get();

        if ($invalidMessages->count() > 0) {
            $this->info("Encontrados {$invalidMessages->count()} mensajes inválidos para eliminar");

            foreach ($invalidMessages as $message) {
                $question = MedisearchQuestion::find($message->id);
                if ($question) {
                    $question->delete(); // Soft delete
                }
            }

            $this->info("✅ Eliminados {$invalidMessages->count()} mensajes inválidos");
        }
    }

    /**
     * ✅ ADAPTAR ARTÍCULOS A FORMATO UNIFICADO
     */
    private function adaptArticles(array $resultados): array
    {
        $cleanedResultados = [];

        foreach ($resultados as $resultado) {
            if ($resultado['tipo'] === 'articles' && isset($resultado['articulos'])) {
                $adaptedArticulos = [];

                foreach ($resultado['articulos'] as $articulo) {
                    // ✅ DETECTAR SI ES FORMATO ANTIGUO O NUEVO
                    $isOldFormat = isset($articulo['titulo']) || isset($articulo['autores']);

                    if ($isOldFormat) {
                        // ✅ CONVERTIR FORMATO ANTIGUO A NUEVO
                        $adaptedArticulos[] = [
                            // Campos del formato nuevo (estructura principal)
                            'pmid' => $this->extractPmidFromUrl($articulo['url'] ?? ''),
                            'title' => $articulo['titulo'] ?? 'Sin título',
                            'authors' => $articulo['autores'] ?? 'Sin autores',
                            'journal' => 'Sin revista', // Los antiguos no tienen journal
                            'year' => $this->extractYearFromDate($articulo['fecha'] ?? ''),
                            'abstract' => $articulo['resumen'] ?? 'Sin resumen',
                            'url' => $articulo['url'] ?? '',
                            'doi' => $this->cleanDoi($articulo['doi'] ?? ''),
                            'relevance_score' => 5, // Valor por defecto
                            'abstract_url' => $articulo['url'] ?? '',

                            // Campos adicionales del formato antiguo (para compatibilidad)
                            'fecha' => $articulo['fecha'] ?? '',
                            'fuente' => $articulo['fuente'] ?? 'PubMed',
                            'tipo_estudio' => $articulo['tipo_estudio'] ?? 'Sin clasificar',

                            // Metadatos
                            '_migrated_from_old' => true,
                            '_original_format' => 'old'
                        ];
                    } else {
                        // ✅ YA ESTÁ EN FORMATO NUEVO, SOLO VERIFICAR CAMPOS
                        $adaptedArticulos[] = array_merge([
                            'pmid' => '',
                            'title' => 'Sin título',
                            'authors' => 'Sin autores',
                            'journal' => 'Sin revista',
                            'year' => 'Sin año',
                            'abstract' => 'Sin resumen',
                            'url' => '',
                            'doi' => '',
                            'relevance_score' => 5,
                            'abstract_url' => '',
                            'fecha' => '',
                            'fuente' => 'PubMed',
                            'tipo_estudio' => 'Sin clasificar',
                            '_migrated_from_old' => false,
                            '_original_format' => 'new'
                        ], $articulo);
                    }
                }

                $cleanedResultados[] = [
                    'tipo' => 'articles',
                    'articulos' => $adaptedArticulos
                ];
            } else {
                $cleanedResultados[] = $resultado;
            }
        }

        return $cleanedResultados;
    }

    /**
     * ✅ MANEJAR CONVERSACIONES FALLIDAS
     */
    private function handleFailedConversations(array $failedConversationIds): void
    {
        $failedConversationIds = array_unique($failedConversationIds);

        if (count($failedConversationIds) > 0) {
            $this->info('');
            $this->info('🚨 Las siguientes conversaciones tuvieron errores en la migración:');
            foreach ($failedConversationIds as $convId) {
                $conversation = MedisearchChat::find($convId);
                $messageCount = MedisearchQuestion::where('chat_id', $convId)->count();
                $this->info(" - Conversación ID: {$convId} (Título: " . ($conversation->title ?? 'Sin título') . ") - {$messageCount} mensajes");
            }

            if ($this->confirm('¿Deseas eliminar estas conversaciones con soft delete?')) {
                foreach ($failedConversationIds as $convId) {
                    try {
                        $conversation = MedisearchChat::find($convId);
                        if ($conversation) {
                            $conversation->delete();
                            $this->info("✅ Conversación ID {$convId} eliminada (soft delete)");

                            $remainingMessages = MedisearchQuestion::where('chat_id', $convId)->count();
                            if ($remainingMessages === 0) {
                                $conversation->forceDelete();
                                $this->info("🗑️ Conversación ID {$convId} eliminada permanentemente");
                            }
                        }
                    } catch (\Exception $e) {
                        $this->error("❌ Error eliminando conversación {$convId}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    private function extractPmidFromUrl(string $url): string
    {
        if (preg_match('/\/(\d+)\/?$/', $url, $matches)) {
            return $matches[1];
        }
        return '';
    }

    private function extractYearFromDate(string $fecha): string
    {
        if ($fecha) {
            try {
                return date('Y', strtotime($fecha));
            } catch (\Exception $e) {
                return 'Sin año';
            }
        }
        return 'Sin año';
    }

    private function cleanDoi(string $doi): string
    {
        if (!$doi) return '';

        $dois = explode("\n", $doi);
        $cleanDoi = trim($dois[0]);

        if (!str_starts_with($cleanDoi, 'doi:')) {
            $cleanDoi = 'doi: ' . $cleanDoi;
        }

        return $cleanDoi;
    }
}
