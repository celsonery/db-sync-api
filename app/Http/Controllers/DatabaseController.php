<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatabaseRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseController extends Controller
{
    public function index(): string
    {
        $process = Process::fromShellCommandline("sudo -u evelyn whoami | base64"); //postgres psql -l | egrep -v List|Name|--|CTc|rows|template?|_dev|__|_hml|-hml' | awk '{print $1}' | grep -v '|'");
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    public function sync(DatabaseRequest $request): JsonResponse
    {
        return response()->json(['message' => "Base {$request->database} sincronizada com sucesso!"], 200);
    }
}
