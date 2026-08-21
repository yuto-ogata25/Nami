<?php

namespace App\Http\Controllers;

use App\Http\Requests\StrategyGoalRequest;
use App\Http\Resources\StrategyGoalResource;
use App\Models\StrategyGoal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StrategyGoalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = StrategyGoal::with(['department', 'owner'])
            ->orderBy('perspective')
            ->orderByDesc('importance');

        if ($request->filled('perspective')) {
            $query->where('perspective', $request->string('perspective'));
        }

        return StrategyGoalResource::collection($query->get());
    }

    public function store(StrategyGoalRequest $request): StrategyGoalResource
    {
        $strategyGoal = StrategyGoal::create($request->validated());

        return new StrategyGoalResource($strategyGoal->load(['department', 'owner']));
    }

    public function show(StrategyGoal $strategyGoal): StrategyGoalResource
    {
        return new StrategyGoalResource($strategyGoal->load(['department', 'owner']));
    }

    public function update(StrategyGoalRequest $request, StrategyGoal $strategyGoal): StrategyGoalResource
    {
        $strategyGoal->update($request->validated());

        return new StrategyGoalResource($strategyGoal->load(['department', 'owner']));
    }

    public function destroy(StrategyGoal $strategyGoal): Response
    {
        $strategyGoal->delete();

        return response()->noContent();
    }
}
