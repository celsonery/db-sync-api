<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatabaseRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseController extends Controller
{
    protected $arr_database = [];

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
                $this->arr_database[] = $p;
            }
        }

        return response()->json(['databases' => $this->arr_database], 200);
    }

    public function sync(DatabaseRequest $request): JsonResponse
    {
        if (!$request->validated()) {
            return response()->json(['message' => 'Erro: parametro enviado é inváido!'], 404);
        }

        $baseDev = $request->database . '_dev';
        $process = Process::fromShellCommandline("sudo -u postgres psql -l | awk '{print $1}' | grep {$baseDev}");
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            return response()->json(['message' => "Base {$request->database} encontrada!"], 200);

//            throw new ProcessFailedException($process);
        }

        if ($process->getOutput()) {
            return response()->json(['message' => "Base {$request->database} encontrada!"], 200);
        }

        return response()->json(['message' => "Base {$request->database} sincronizada com sucesso!"], 200);
    }
}
