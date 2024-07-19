<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatabaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseController extends Controller
{
    public function __construct(
        private array $databases = [],
        private string $baseProd = '',
        private string $baseDev = '',
        private string $pg_host = ''
    )
    {
        $this->pg_host = env('PG_HOST');
    }

    public function index(): JsonResponse
    {
        Log::debug("Listando bancos em {$this->pg_host}...");

        $process = Process::fromShellCommandline("/usr/bin/psql -h {$this->pg_host} -U postgres -l | awk '{print $1}' | egrep -v 'List|Name|--|\||\(|dev|hml'");
        $process->run();

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

    public function sync(DatabaseRequest $request): JsonResponse
    {
        $this->baseProd = $request->database;
        $this->baseDev = $request->database . '_dev';

        Log::debug("Procurando se banco {$this->baseDev} já existe...");

        $process = Process::fromShellCommandline("/usr/bin/psql -h {$this->pg_host} -U postgres -l | grep {$this->baseDev}");
        $process->run();

        if (!$process->isSuccessful()) {
            return $this->execute(false);
        } else {
            return $this->execute(true);
        }
    }

    private function execute(bool $drop): JsonResponse
    {
        if ($drop) {
            Log::debug("Banco {$this->baseDev} já existe removendo...");

            $process = Process::fromShellCommandline("/usr/bin/psql -h {$this->pg_host} -U postgres -c \"DROP DATABASE {$this->baseDev} with (force)\"");
            $process->run();

            if (!$process->isSuccessful()) {
                Log::debug("Erro removendo {$this->baseDev}! - {$process->getErrorOutput()}");
                Log::debug("---");

                return response()->json(['message' => "Não foi possível deletar {$this->baseDev}!"], 404);
            }
        }

        // Cris ou Recria base de dados
        $drop ? Log::debug("Recriando banco {$this->baseDev}...") : Log::debug("Banco {$this->baseDev} ainda não existe, criando...");

        $process = Process::fromShellCommandline("/usr/bin/psql -h {$this->pg_host} -U postgres -c \"CREATE DATABASE {$this->baseDev}\"");
        $process->run();

        if (!$process->isSuccessful()) {
            Log::debug("Erro criando {$this->baseDev}! - {$process->getErrorOutput()}");
            Log::debug("---");

            return response()->json(['message' => "Não foi possível criar {$this->baseDev}!"], 404);
        }

        Log::debug("Sincronizando: {$this->baseProd} para {$this->baseDev}");

        // Realiza o sincronismo
        $process = Process::fromShellCommandline("/usr/bin/pg_dump -h {$this->pg_host} -U postgres -v {$this->baseProd} | /usr/bin/psql -h {$this->pg_host} -U postgres {$this->baseDev}");
        $process->setTimeout(0)->run();

        if (!$process->isSuccessful()) {
            Log::debug("Erro sincronizando {$this->baseProd} para {$this->baseDev}! - {$process->getErrorOutput()}");
            Log::debug("---");

            return response()->json(['message' => "Não foi possível sincronizar {$this->baseDev}! - {$process->getErrorOutput()}"], 404);
        }

        Log::debug("Sincronização de {$this->baseProd} para {$this->baseDev} realizada com sucesso!");
        Log::debug("---");

        return response()->json(['message' => "Base {$this->baseDev} sincronizada com sucesso!"], 200);
    }
}
