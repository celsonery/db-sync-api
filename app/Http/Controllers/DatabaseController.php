<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatabaseRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseController extends Controller
{
    public function index(): JsonResponse
    {
        $process = Process::fromShellCommandline("sudo -u postgres psql -l | awk '{print $1}' | egrep -v 'List|Name|--|\||\('");
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $total = preg_split('/\n/',$process->getOutput());
        $arr_database = [];

        foreach($total as $p) {
            if (strlen($p)) {
                $arr_database[] = $p;
            }
        }

        return response()->json(['databases' => $arr_database], 200);
    }

    public function sync(DatabaseRequest $request): JsonResponse
    {
        return response()->json(['message' => "Base {$request->database} sincronizada com sucesso!"], 200);
    }
}
