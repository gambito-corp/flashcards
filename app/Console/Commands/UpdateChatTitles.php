<?php

namespace App\Console\Commands;

use App\Models\MedisearchChat;
use Illuminate\Console\Command;

class UpdateChatTitles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-chat-titles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Comenzando actualización de títulos...');

        $chats = MedisearchChat::query()->whereHas('questions')->get();
        $bar = $this->output->createProgressBar(count($chats));

        $actualizados = 0;

        foreach ($chats as $chat) {
            // Obtener la primera pregunta del chat
            $primeraQuestion = $chat->questions()->orderBy('created_at')->first();

            if ($primeraQuestion) {
                $query = $primeraQuestion->query;
                $currentTitle = $chat->title;

                $patronChat = '/^Chat \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
                $patronNuevoChat = '/^Nuevo Chat \d{2}\/\d{2} \d{2}:\d{2}$/';

                // Verifica si el título actual coincide con alguno de los patrones
                if (preg_match($patronChat, $currentTitle) || preg_match($patronNuevoChat, $currentTitle)) {
                    $nuevoTitulo = mb_strlen($query) > 40 ? mb_substr($query, 0, 37) . '...' : $query;

                    if ($currentTitle !== $nuevoTitulo) {
                        $chat->title = $nuevoTitulo;
                        $chat->save();
                        $actualizados++;
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info("¡Proceso completado! Se actualizaron {$actualizados} chats.");
    }

}
