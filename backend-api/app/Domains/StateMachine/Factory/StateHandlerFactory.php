<?php

namespace App\Domains\StateMachine\Factory;

use App\Domains\StateMachine\StageHandlers\ApproachStage;
use App\Domains\StateMachine\StageHandlers\ClarifyStage;
use App\Domains\StateMachine\StageHandlers\PseudocodeStage;
use App\Domains\StateMachine\StageHandlers\BruteForceStage;
use App\Domains\StateMachine\StageHandlers\OptimizeStage;
use App\Domains\StateMachine\StageHandlers\DoneStage;
use App\Domains\StateMachine\Contracts\StageHandler;
use App\Enums\Stage;

class StateHandlerFactory {
    public function for(Stage $stage): StageHandler {
        return match ($stage) {
            Stage::Clarify => app(ClarifyStage::class),
            Stage::Approach => app(ApproachStage::class),
            Stage::Pseudocode => app(PseudocodeStage::class),
            Stage::BruteForce => app(BruteForceStage::class),
            Stage::Optimize => app(OptimizeStage::class),
            Stage::Done => app(DoneStage::class),
        };
    }
}
