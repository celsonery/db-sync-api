<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatabaseRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseController extends Controller
{
    protected array $databases = [];
    protected string $baseProd = '';
    protected string $baseDev = '';

    public function index(): JsonResponse
    {
        $process = Process::fromShellCommandline("sudo -u postgres psql -l | awk '{print $1}' | egrep -v 'List|Name|--|\||\(|dev|hml'");
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
        $this->baseDev = $request->database . '_dev';

        $process = Process::fromShellCommandline("sudo -u postgres psql -l | awk '{print $1}' | grep {$this->baseDev}");
        $process->run();

        if (!$process->isSuccessful()) {
            return $this->sinc(false);
        } else {
            return $this->sinc( true);
        }
    }

    private function sinc(bool $drop): JsonResponse
    {
        if ($drop) {
            $process = Process::fromShellCommandline("sudo -u postgres psql -c \"DROP DATABASE {$this->baseDev} with (force)\"");
            $process->run();

            if (!$process->isSuccessful()) {
                return response()->json(['message' => "Não foi possível deletar {$this->baseDev}!"], 404);
            }
        }

        // Recria base de dados
        $process = Process::fromShellCommandline("sudo -u postgres psql -c \"CREATE DATABASE {$this->baseDev}\"");
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json(['message' => "Não foi possível criar {$this->baseDev}!"], 404);
        }

        // Realiza o sincronismo

        $process = Process::fromShellCommandline("sudo -u postgres pg_dump {$this->baseProd} | psql {$this->baseDev}\"");
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json(['message' => "Não foi possível sincronizar {$this->baseDev}!"], 404);
        }

        return response()->json(['message' => "Base {$this->baseDev} sincronizada com sucesso!"], 200);
    }

//    private function createDatabase($baseDev)
//    {
//        $process = Process::fromShellCommandline("sudo -u postgres psql -c \"CREATE DATABASE {$baseDev}\"");
//        $process->run();
//
//        if (!$process->isSuccessful()) {
//            return response()->json(['message' => "Não foi possível criar {$baseDev}!"], 404);
//        }
//
//        $this->sinc($this->baseProd, $this->baseDev);
//    }

//    private function dropDatabase($baseDev)
//    {
//        $process = Process::fromShellCommandline("sudo -u postgres psql -c \"DROP DATABASE {$baseDev} with \(force\)\"");
//        $process->run();
//
//        if (!$process->isSuccessful()) {
//            return response()->json(['message' => "Não foi possível deletar {$baseDev}!"], 404);
//        }
//
//        $this->sinc($this->baseProd, $this->baseDev);
//    }
}
