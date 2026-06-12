<?php

namespace App\Runner;

use App\Enums\Lang;

class CodeRunnerFactory
{
    /**
     * Creates a new instance of the code runner for the given language.
     *
     * @param  Lang  $lang  The language to create a runner for.
     * @return CodeRunnerInterface The code runner instance.
     */
    public function make(Lang $lang): CodeRunnerInterface
    {
        return match ($lang) {
            Lang::Javascript => new JavascriptRunner,
            Lang::Python => new PythonRunner,
            Lang::Php => new PhpRunner,
        };
    }
}
