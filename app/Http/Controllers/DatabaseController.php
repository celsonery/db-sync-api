<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatabaseRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseController extends Controller
{
    protected array $databases = [];

    public function index(): JsonResponse
    {
        $process = Process::fromShellCommandline("sudo -u postgres psql -l | awk '{print $1}' | egrep -v 'List|Name|--|\||\(|dev|hml'");
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $total = preg_split('/\n/',$process->getOutput());

        foreach($total as $p) {
            if (strlen($p)) {
                $this->databases[] = $p;
            }
        }

        return response()->json(['databases' => $this->databases], 200);
    }

    public function verify(DatabaseRequest $request)
    {
        if (!$request->validated()) {
            return response()->json(['message' => 'Erro: parametro enviado é inváido!'], 404);
        }

        $baseDev = $request->database . '_dev';

        $process = Process::fromShellCommandline("sudo -u postgres psql -l | awk '{print $1}' | grep {$baseDev}");
        $process->run();

        return gettype($process->isSuccessful());

//        if (!$process->isSuccessful()) {
//            // Base não encontrada - cria base dev
//
//        } else {
//            $process = Process::fromShellCommandline("sudo -u postgres psql -l | awk '{print $1}' | grep {$baseDev}");
//            $process->run();
//
//            return response()->json(['message' => "Base {$request->database} sincronizada com sucesso!"], 200);
//        }

//        if ($process->getOutput()) {
//            return response()->json(['message' => "Base {$request->database} sincronizada com sucesso!"], 200);
//        }
    }

    private function sinc($baseProd, $baseDev): JsonResponse
    {
        $process = Process::fromShellCommandline("sudo -u postgres psql -c \"CREATE DATABASE {$baseDev}\"");
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json(['message' => "Não foi possível recriar {$baseDev}!"], 404);
        }

        // Realiza o sincronismo
        $process = Process::fromShellCommandline("sudo -u postgres pg_dump -v -d {$baseProd} | psql {$baseDev}\"");
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json(['message' => "Não foi possível sincronizar {$baseDev}!"], 404);
        }

        return response()->json(['message' => "Base {$baseDev} sincronizada com sucesso!"], 200);
    }

    private function createDatabase($baseDev): void
    {
        $process = Process::fromShellCommandline("sudo -u postgres psql -c \"CREATE DATABASE {$baseDev}\"");
        $process->run();

        if (!$process->isSuccessful()) {
//            return response()->json(['message' => "Não foi possível sincronizar {$baseDev}!"], 404);
        }
    }

    private function dropDatabase($baseDev): void
    {
        $process = Process::fromShellCommandline("sudo -u postgres psql -c \"CREATE DATABASE {$baseDev}\"");
        $process->run();

        if (!$process->isSuccessful()) {
//            return response()->json(['message' => "Não foi possível sincronizar {$baseDev}!"], 404);
        }
    }
}
