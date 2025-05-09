<?php

namespace App\Services\Usuarios;

use App\Mail\QuotaExceededNotification;
use App\Models\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Client\RequestException;
use OpenAI\Laravel\Facades\OpenAI;

set_time_limit(360);

class MBIAService
{
    public function search(array $payload)
    {

        //Respuesta Estandar para no gastar mas Saldo de OpenAI
//        dd($this->pregenerateData());
//        return $this->pregenerateData();




        // Verificar si el flag de quota excedido está activo
//        $config = Config::query()->where('tipo', 'services.openai.quota_exceeded')->first();
//
//        if (isset($config) && $config->value === 'true') {
//            $payload['client'] !== 'medisearch';
//        }
//        try {
            $payload['include_articles'] = $payload['include_articles'] ? 'true' : 'false';
            $request = Http::asForm()->withHeaders([
                'Authorization' => 'Bearer ' . config('services.ai_api.token'),
            ])->post(config('services.ai_api.base_url'), $payload);
//            $data = json_decode($request->body(), true);
//            dd($data);
//            // Detectar error 429 en la estructura real
//            if (isset($data['data']['resultados'][0]['respuesta'])) {
//                $errorMessage = $data['data']['resultados'][0]['respuesta'];
//                if (str_contains($errorMessage, 'Error code: 429') &&
//                    str_contains($errorMessage, 'insufficient_quota')) {
//                    // 1. Actualizar flag en base de datos
//                    Config::query()->updateOrCreate(
//                        ['tipo' => 'services.MBAI.openai_quota_exceeded'],
//                        ['value' => 'true']
//                    );
//                    // 2. Registrar en logs
//                    Log::critical('OpenAI quota exceeded - Fallback activated', [
//                        'error' => $errorMessage,
//                        'query' => $payload['query'] ?? ''
//                    ]);
//                    // 3. Notificar por email (opcional)
//                    if (config('services.notifications.admin_email') !== ''){
//                        Mail::to(config('services.notifications.admin_email'))
//                            ->send(new QuotaExceededNotification($payload));
//                    }
//                    Mail::to(config('services.notifications.admin2_email'))
//                        ->send(new QuotaExceededNotification($payload));
//
//                    if ($payload['client'] !== 'medisearch'){
//                        $payload['client'] = 'medisearch';
//                        return $this->search($payload);
//                    }
//                }
//            }
            return json_decode($request->body(), true);
//        } catch (\Illuminate\Http\Client\RequestException $e) {
//            throw $e;
//        }
    }

    public function pregenerateData ()
    {
        return [
            "data" => [
                "conversation_id" => "",
                "query" => "¿El deporte aumenta la esperanza de vida?",
                "resultados" => [
                    0 => [
                        "respuesta" => $this->responseHTML(),
                        "tipo" => "llm_response",
                    ],
                    1 => [
                        "articulos" => [
                            0 => [
                                "autores" => "Tan, Xiaohuan; Jiang, Guiping; Zhang, Lei; Wang, Dandan; Wu, Xueping",
                                "doi" => <<<HTML
                                    10.7717/peerj.19263
                                    10.3390/ijerph17041450
                                    10.33549/physiolres.930000.55.S1.129
                                    10.1177/0269215512446314
                                    10.1249/MSS.0b013e3181a0c95c
                                    10.4324/9780203771587
                                    10.5312/wjo.v9.i9.156
                                    10.3389/fneur.2019.00627
                                    10.1016/j.maturitas.2009.03.013
                                    10.1080/0361073X.2018.1449591
                                    10.12965/jer.1735024.512
                                    10.1016/j.heliyon.2024.e35822
                                    10.3390/healthcare9060652
                                    10.1080/02701367.1999.10608028
                                    10.1111/j.1447-0594.2012.00923.x
                                    10.1111/ggi.12722
                                    10.1249/MSS.0000000000002093
                                    10.1016/j.cell.2013.05.039
                                    10.1177/0898264314556987
                                    10.1111/j.1600-0838.2009.00919.x
                                    10.12974/2309-6128.2015.03.01.2
                                    10.3109/09638288.2013.825333
                                    10.1136/bjsm.2007.038554
                                    10.3390/ijerph17113940
                                    10.1177/0269215511435688
                                    10.3389/fphys.2023.1202613
                                    10.1007/s00421-009-1303-3
                                    10.1007/s00421-013-2654-3
                                    10.1519/JSC.0b013e3182a20f2c
                                    10.1371/journal.pone.0225670
                                    10.14283/jarlife.2021.7
                                    10.1007/s40520-014-0206-2
                                    10.1016/j.jamda.2014.07.018
                                    10.1007/s40520-013-0157-z
                                    10.1080/09593985.2019.1606372
                                    10.3389/fbioe.2021.601747
                                    10.1007/s12603-020-1456-7
                                    10.1016/j.apmr.2023.04.002
                                    10.1016/j.archger.2019.103987
                                    10.1016/j.ijge.2016.03.010
                                    10.3390/ijerph182211942
                                    10.1519/JPT.0b013e318295dacd
                                    10.1093/gerona/glp033
                                    10.1016/j.apmr.2020.02.009
                                    10.1111/ggi.12878
                                    10.1111/ggi.13938
                                    10.1007/s10433-019-00498-x
                                HTML,
                                "fecha" => "2025-04-21",
                                "fuente" => "PubMed",
                                "resumen" => "
                                    As life expectancy rises, age-related decline in mobility and physical function poses challenges for older adults. While traditional exercise can help, limitati
                                    ",
                                "tipo_estudio" => "Clinical Trials",
                                "titulo" => "Effects of low-frequency vibration training on walking ability and body composition among older adults: a randomized controlled trial.",
                                "url" => <<<HTML
                                    https://pubmed.ncbi.nlm.nih.gov/40256731
                                    32102379
                                    17177621
                                    23035004
                                    19516148
                                    24160189
                                    30254972
                                    31316447
                                    19386449
                                    29558320
                                    29114533
                                    39224285
                                    34072657
                                    10380242
                                    22935006
                                    27018279
                                    31343522
                                    23746838
                                    25376604
                                    19422657
                                    27200366
                                    23962193
                                    18182623
                                    32498351
                                    22324058
                                    38028790
                                    20012646
                                    23657766
                                    23820565
                                    31794552
                                    36923511
                                    24633589
                                    25282631
                                    24158788
                                    31025583
                                    33644013
                                    33367474
                                    37169245
                                    32163796
                                    34831698
                                    23838625
                                    19349593
                                    32145279
                                    27578535
                                    29608259
                                    32432835
                                    31543722
                                HTML,
                            ],
                            1 => [
                                "autores" => "Tan, Xiaohuan; Jiang, Guiping; Zhang, Lei; Wang, Dandan; Wu, Xueping",
                                "doi" => <<<HTML
                                    10.7717/peerj.19263
                                    10.3390/ijerph17041450
                                    10.33549/physiolres.930000.55.S1.129
                                    10.1177/0269215512446314
                                    10.1249/MSS.0b013e3181a0c95c
                                    10.4324/9780203771587
                                    10.5312/wjo.v9.i9.156
                                    10.3389/fneur.2019.00627
                                    10.1016/j.maturitas.2009.03.013
                                    10.1080/0361073X.2018.1449591
                                    10.12965/jer.1735024.512
                                    10.1016/j.heliyon.2024.e35822
                                    10.3390/healthcare9060652
                                    10.1080/02701367.1999.10608028
                                    10.1111/j.1447-0594.2012.00923.x
                                    10.1111/ggi.12722
                                    10.1249/MSS.0000000000002093
                                    10.1016/j.cell.2013.05.039
                                    10.1177/0898264314556987
                                    10.1111/j.1600-0838.2009.00919.x
                                    10.12974/2309-6128.2015.03.01.2
                                    10.3109/09638288.2013.825333
                                    10.1136/bjsm.2007.038554
                                    10.3390/ijerph17113940
                                    10.1177/0269215511435688
                                    10.3389/fphys.2023.1202613
                                    10.1007/s00421-009-1303-3
                                    10.1007/s00421-013-2654-3
                                    10.1519/JSC.0b013e3182a20f2c
                                    10.1371/journal.pone.0225670
                                    10.14283/jarlife.2021.7
                                    10.1007/s40520-014-0206-2
                                    10.1016/j.jamda.2014.07.018
                                    10.1007/s40520-013-0157-z
                                    10.1080/09593985.2019.1606372
                                    10.3389/fbioe.2021.601747
                                    10.1007/s12603-020-1456-7
                                    10.1016/j.apmr.2023.04.002
                                    10.1016/j.archger.2019.103987
                                    10.1016/j.ijge.2016.03.010
                                    10.3390/ijerph182211942
                                    10.1519/JPT.0b013e318295dacd
                                    10.1093/gerona/glp033
                                    10.1016/j.apmr.2020.02.009
                                    10.1111/ggi.12878
                                    10.1111/ggi.13938
                                    10.1007/s10433-019-00498-x
                                HTML,
                                "fecha" => "2025-04-21",
                                "fuente" => "PubMed",
                                "resumen" => "
                                    As life expectancy rises, age-related decline in mobility and physical function poses challenges for older adults. While traditional exercise can help, limitati
                                    ",
                                "tipo_estudio" => "Clinical Trials",
                                "titulo" => "Effects of low-frequency vibration training on walking ability and body composition among older adults: a randomized controlled trial.",
                                "url" => <<<HTML
                                    https://pubmed.ncbi.nlm.nih.gov/40256731
                                    32102379
                                    17177621
                                    23035004
                                    19516148
                                    24160189
                                    30254972
                                    31316447
                                    19386449
                                    29558320
                                    29114533
                                    39224285
                                    34072657
                                    10380242
                                    22935006
                                    27018279
                                    31343522
                                    23746838
                                    25376604
                                    19422657
                                    27200366
                                    23962193
                                    18182623
                                    32498351
                                    22324058
                                    38028790
                                    20012646
                                    23657766
                                    23820565
                                    31794552
                                    36923511
                                    24633589
                                    25282631
                                    24158788
                                    31025583
                                    33644013
                                    33367474
                                    37169245
                                    32163796
                                    34831698
                                    23838625
                                    19349593
                                    32145279
                                    27578535
                                    29608259
                                    32432835
                                    31543722
                                HTML,
                            ],
                            2 => [
                                "autores" => "Tan, Xiaohuan; Jiang, Guiping; Zhang, Lei; Wang, Dandan; Wu, Xueping",
                                "doi" => <<<HTML
                                    10.7717/peerj.19263
                                    10.3390/ijerph17041450
                                    10.33549/physiolres.930000.55.S1.129
                                    10.1177/0269215512446314
                                    10.1249/MSS.0b013e3181a0c95c
                                    10.4324/9780203771587
                                    10.5312/wjo.v9.i9.156
                                    10.3389/fneur.2019.00627
                                    10.1016/j.maturitas.2009.03.013
                                    10.1080/0361073X.2018.1449591
                                    10.12965/jer.1735024.512
                                    10.1016/j.heliyon.2024.e35822
                                    10.3390/healthcare9060652
                                    10.1080/02701367.1999.10608028
                                    10.1111/j.1447-0594.2012.00923.x
                                    10.1111/ggi.12722
                                    10.1249/MSS.0000000000002093
                                    10.1016/j.cell.2013.05.039
                                    10.1177/0898264314556987
                                    10.1111/j.1600-0838.2009.00919.x
                                    10.12974/2309-6128.2015.03.01.2
                                    10.3109/09638288.2013.825333
                                    10.1136/bjsm.2007.038554
                                    10.3390/ijerph17113940
                                    10.1177/0269215511435688
                                    10.3389/fphys.2023.1202613
                                    10.1007/s00421-009-1303-3
                                    10.1007/s00421-013-2654-3
                                    10.1519/JSC.0b013e3182a20f2c
                                    10.1371/journal.pone.0225670
                                    10.14283/jarlife.2021.7
                                    10.1007/s40520-014-0206-2
                                    10.1016/j.jamda.2014.07.018
                                    10.1007/s40520-013-0157-z
                                    10.1080/09593985.2019.1606372
                                    10.3389/fbioe.2021.601747
                                    10.1007/s12603-020-1456-7
                                    10.1016/j.apmr.2023.04.002
                                    10.1016/j.archger.2019.103987
                                    10.1016/j.ijge.2016.03.010
                                    10.3390/ijerph182211942
                                    10.1519/JPT.0b013e318295dacd
                                    10.1093/gerona/glp033
                                    10.1016/j.apmr.2020.02.009
                                    10.1111/ggi.12878
                                    10.1111/ggi.13938
                                    10.1007/s10433-019-00498-x
                                HTML,
                                "fecha" => "2025-04-21",
                                "fuente" => "PubMed",
                                "resumen" => "
                                    As life expectancy rises, age-related decline in mobility and physical function poses challenges for older adults. While traditional exercise can help, limitati
                                    ",
                                "tipo_estudio" => "Clinical Trials",
                                "titulo" => "Effects of low-frequency vibration training on walking ability and body composition among older adults: a randomized controlled trial.",
                                "url" => <<<HTML
                                    https://pubmed.ncbi.nlm.nih.gov/40256731
                                    32102379
                                    17177621
                                    23035004
                                    19516148
                                    24160189
                                    30254972
                                    31316447
                                    19386449
                                    29558320
                                    29114533
                                    39224285
                                    34072657
                                    10380242
                                    22935006
                                    27018279
                                    31343522
                                    23746838
                                    25376604
                                    19422657
                                    27200366
                                    23962193
                                    18182623
                                    32498351
                                    22324058
                                    38028790
                                    20012646
                                    23657766
                                    23820565
                                    31794552
                                    36923511
                                    24633589
                                    25282631
                                    24158788
                                    31025583
                                    33644013
                                    33367474
                                    37169245
                                    32163796
                                    34831698
                                    23838625
                                    19349593
                                    32145279
                                    27578535
                                    29608259
                                    32432835
                                    31543722
                                HTML,
                            ],
                            3 => [
                                "autores" => "Tan, Xiaohuan; Jiang, Guiping; Zhang, Lei; Wang, Dandan; Wu, Xueping",
                                "doi" => <<<HTML
                                    10.7717/peerj.19263
                                    10.3390/ijerph17041450
                                    10.33549/physiolres.930000.55.S1.129
                                    10.1177/0269215512446314
                                    10.1249/MSS.0b013e3181a0c95c
                                    10.4324/9780203771587
                                    10.5312/wjo.v9.i9.156
                                    10.3389/fneur.2019.00627
                                    10.1016/j.maturitas.2009.03.013
                                    10.1080/0361073X.2018.1449591
                                    10.12965/jer.1735024.512
                                    10.1016/j.heliyon.2024.e35822
                                    10.3390/healthcare9060652
                                    10.1080/02701367.1999.10608028
                                    10.1111/j.1447-0594.2012.00923.x
                                    10.1111/ggi.12722
                                    10.1249/MSS.0000000000002093
                                    10.1016/j.cell.2013.05.039
                                    10.1177/0898264314556987
                                    10.1111/j.1600-0838.2009.00919.x
                                    10.12974/2309-6128.2015.03.01.2
                                    10.3109/09638288.2013.825333
                                    10.1136/bjsm.2007.038554
                                    10.3390/ijerph17113940
                                    10.1177/0269215511435688
                                    10.3389/fphys.2023.1202613
                                    10.1007/s00421-009-1303-3
                                    10.1007/s00421-013-2654-3
                                    10.1519/JSC.0b013e3182a20f2c
                                    10.1371/journal.pone.0225670
                                    10.14283/jarlife.2021.7
                                    10.1007/s40520-014-0206-2
                                    10.1016/j.jamda.2014.07.018
                                    10.1007/s40520-013-0157-z
                                    10.1080/09593985.2019.1606372
                                    10.3389/fbioe.2021.601747
                                    10.1007/s12603-020-1456-7
                                    10.1016/j.apmr.2023.04.002
                                    10.1016/j.archger.2019.103987
                                    10.1016/j.ijge.2016.03.010
                                    10.3390/ijerph182211942
                                    10.1519/JPT.0b013e318295dacd
                                    10.1093/gerona/glp033
                                    10.1016/j.apmr.2020.02.009
                                    10.1111/ggi.12878
                                    10.1111/ggi.13938
                                    10.1007/s10433-019-00498-x
                                HTML,
                                "fecha" => "2025-04-21",
                                "fuente" => "PubMed",
                                "resumen" => "
                                    As life expectancy rises, age-related decline in mobility and physical function poses challenges for older adults. While traditional exercise can help, limitati
                                    ",
                                "tipo_estudio" => "Clinical Trials",
                                "titulo" => "Effects of low-frequency vibration training on walking ability and body composition among older adults: a randomized controlled trial.",
                                "url" => <<<HTML
                                    https://pubmed.ncbi.nlm.nih.gov/40256731
                                    32102379
                                    17177621
                                    23035004
                                    19516148
                                    24160189
                                    30254972
                                    31316447
                                    19386449
                                    29558320
                                    29114533
                                    39224285
                                    34072657
                                    10380242
                                    22935006
                                    27018279
                                    31343522
                                    23746838
                                    25376604
                                    19422657
                                    27200366
                                    23962193
                                    18182623
                                    32498351
                                    22324058
                                    38028790
                                    20012646
                                    23657766
                                    23820565
                                    31794552
                                    36923511
                                    24633589
                                    25282631
                                    24158788
                                    31025583
                                    33644013
                                    33367474
                                    37169245
                                    32163796
                                    34831698
                                    23838625
                                    19349593
                                    32145279
                                    27578535
                                    29608259
                                    32432835
                                    31543722
                                HTML,
                            ],
                        ],
                    ],
                ],
            ],
            "meta" => [
                "provider" => "MBAI",
                "timestamp" => "2025-05-04T10:39:44.229915",
            ],
            "status" => "success",
        ];
    }

    private function responseHTML()
    {
        return <<<HTML
                            <html>
                                <head>
                                    <style>
                                        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f9f9f9; color: #222; }
                                        h1 { color: #2a7ae2; }
                                        h2 { color: #205080; }
                                        ul { margin-left: 20px; }
                                        .referencias { margin-top: 30px; font-size: 0.95em; }
                                        .referencias li { margin-bottom: 8px; }
                                        .nota { color: #888; font-size: 0.95em; }
                                    </style>
                                </head>
                                <body>
                                    <h1>¿El deporte aumenta la esperanza de vida?</h1>
                                    <h2>Respuesta médica</h2>
                                    <p>
                                        Sí, la práctica regular de deporte o actividad física está asociada con un aumento significativo de la esperanza de vida. Numerosos estudios científicos han demostrado que las personas físicamente activas tienen menor riesgo de mortalidad por todas las causas, incluyendo enfermedades cardiovasculares, diabetes tipo 2, ciertos tipos de cáncer y enfermedades respiratorias.
                                    </p>
                                    <h2>¿Por qué el deporte aumenta la esperanza de vida?</h2>
                                    <ul>
                                        <li>
                                            <strong>Mejora la salud cardiovascular:</strong>
                                            Reduce la presión arterial, mejora el perfil lipídico y disminuye el riesgo de infarto y accidente cerebrovascular.
                                        </li>
                                        <li>
                                            <strong>Control del peso corporal:</strong>
                                            Ayuda a prevenir la obesidad y sus complicaciones metabólicas.
                                        </li>
                                        <li>
                                            <strong>Reducción del riesgo de cáncer:</strong>
                                            Se ha asociado con menor incidencia de cáncer de colon, mama y otros tipos.
                                        </li>
                                        <li>
                                            <strong>Mejora la salud mental:</strong>
                                            Disminuye el riesgo de depresión y ansiedad, mejorando la calidad de vida.
                                        </li>
                                        <li>
                                            <strong>Fortalece huesos y músculos:</strong>
                                            Previene la osteoporosis y reduce el riesgo de caídas en adultos mayores.
                                        </li>
                                    </ul>
                                    <h2>¿Cuánto ejercicio es recomendable?</h2>
                                    <p>
                                        Las guías internacionales, como las de la Organización Mundial de la Salud (OMS), recomiendan al menos 150-300 minutos semanales de actividad física aeróbica moderada, o 75-150 minutos de actividad vigorosa, combinados con ejercicios de fortalecimiento muscular al menos dos veces por semana.
                                    </p>
                                    <h2>Conclusión</h2>
                                    <p>
                                        La evidencia científica respalda que el deporte y la actividad física regular son factores clave para aumentar la esperanza de vida y mejorar la calidad de la misma.
                                    </p>
                                    <div class='referencias'>
                                        <strong>Referencias:</strong>
                                        <ol>
                                            <li>
                                                Wen CP, Wu X. Stressing harms of physical inactivity to promote exercise. The Lancet. 2012;380(9838):192-193.
                                                <a href='https://doi.org/10.1016/S0140-6736(12)61031-2' target='_blank'>
                                                    https://doi.org/10.1016/S0140-6736(12)61031-2
                                                </a>
                                            </li>
                                            <li>
                                                Arem H, Moore SC, Patel A, et al. Leisure Time Physical Activity and Mortality. JAMA Intern Med. 2015;175(6):959-967.
                                                <a href="https://jamanetwork.com/journals/jamainternalmedicine/fullarticle/2299754" target="_blank">
                                                    https://jamanetwork.com/journals/jamainternalmedicine/fullarticle/2299754
                                                </a>
                                            </li>
                                        </ol>
                                        <div class="nota">
                                            Nota: Soy MedByAgentInformation, una IA creada por MedByStudent y desarrollada por GambitoCode.
                                        </div>
                                    </div>
                                </body>
                            </html>
HTML;
    }

    // New Implementation For OpenAI WhitStream

    public function newFunction($payload)
    {
        $payload['include_articles'] = $payload['include_articles'] ? 'true' : 'false';
        $request = Http::asForm()->withHeaders([
            'Authorization' => 'Bearer ' . config('services.ai_api.token'),
        ])->post(config('services.ai_api.base_url_v2'), $payload);
    }

    private function makeResponse()
    {

    }
}
