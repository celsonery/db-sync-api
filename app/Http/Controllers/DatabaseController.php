<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatabaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseController extends Controller
{
    protected $databases = [];
    protected $baseProd = '';
    protected $baseDev = '';

    public function index(): JsonResponse
    {
//        $process = Process::fromShellCommandline("psql -h 10.0.10.129 -U postgres -l | awk '{print $1}' | egrep -v 'List|Name|--|\||\(|dev|hml'");
        Log::debug("Listando bancos...");

        $process = Process::fromShellCommandline("/usr/bin/psql -h 10.0.10.129 -U postgres -l | awk '{print $1}' | egrep -v 'List|Name|--|\||\(|dev|hml'");
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $total = preg_split('/\n/', $process->getOutput());

        foreach ($total as $p) {
            if (strlen($p)) {
                $this->databases[] = $p;
            }
        }

        return response()->json(['databases' => $this->databases], 200);
    }

    public function verify(DatabaseRequest $request): JsonResponse
    {
        $this->baseProd = $request->database;
        $this->baseDev = $request->database . '_dev';

        Log::debug("Procurando se banco {$this->baseDev} já existe...");

        $process = Process::fromShellCommandline("/usr/bin/psql -h 10.0.10.129 -U postgres -l | grep {$this->baseDev}");
        $process->run();

        if (!$process->isSuccessful()) {
            return $this->sinc(false);
        } else {
            return $this->sinc(true);
        }
    }

    private function sinc(bool $drop): JsonResponse
    {
        if ($drop) {
            Log::debug("Banco {$this->baseDev} já existe removendo...");

            $process = Process::fromShellCommandline("/usr/bin/psql -h 10.0.10.129 -U postgres -c \"DROP DATABASE {$this->baseDev} with (force)\"");
            $process->run();

            if (!$process->isSuccessful()) {
                return response()->json(['message' => "Não foi possível deletar {$this->baseDev}!"], 404);
            }
        }

        // Recria base de dados
        $drop ? Log::debug("Recriando banco {$this->baseDev}...") : Log::debug("Banco {$this->baseDev} ainda não existe, criando...");

        $process = Process::fromShellCommandline("/usr/bin/psql -h 10.0.10.129 -U postgres -c \"CREATE DATABASE {$this->baseDev}\"");
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json(['message' => "Não foi possível criar {$this->baseDev}!"], 404);
        }

        Log::debug("Sincronizando: {$this->baseProd} para {$this->baseDev}");

        // Realiza o sincronismo
        $process = Process::fromShellCommandline("/usr/bin/pg_dump -h 10.0.10.129 -U postgres -v {$this->baseProd} | /usr/bin/psql -h 10.0.10.129 -U postgres {$this->baseDev}");
        $process->setTimeout(0)->run();

        if (!$process->isSuccessful()) {
            return response()->json(['message' => "Não foi possível sincronizar {$this->baseDev}! - {$process->getErrorOutput()}"], 404);
        }

        Log::debug("Sincronizado: {$this->baseProd} para {$this->baseDev} com sucesso!");
        Log::debug("---");

        return response()->json(['message' => "Base {$this->baseDev} sincronizada com sucesso!"], 200);
    }
}
