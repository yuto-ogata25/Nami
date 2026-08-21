<?php

namespace App\Http\Controllers;

use App\Http\Requests\KpiRequest;
use App\Http\Resources\KpiResource;
use App\Models\Kpi;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class KpiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $kpis = Kpi::with(['strategyGoal.department', 'owner'])
            ->orderBy('strategy_goal_id')
            ->orderByDesc('importance')
            ->get();

        return KpiResource::collection($kpis);
    }

    public function store(KpiRequest $request): KpiResource
    {
        // measurement_cycle はMVP期間中フォームに出さず、常にmonthly固定でサーバー側から設定する。
        $kpi = Kpi::create([...$request->validated(), 'measurement_cycle' => 'monthly']);

        return new KpiResource($kpi->load(['strategyGoal.department', 'owner']));
    }

    public function show(Kpi $kpi): KpiResource
    {
        return new KpiResource($kpi->load(['strategyGoal.department', 'owner']));
    }

    public function update(KpiRequest $request, Kpi $kpi): KpiResource
    {
        $kpi->update($request->validated());

        return new KpiResource($kpi->load(['strategyGoal.department', 'owner']));
    }

    public function destroy(Kpi $kpi): Response
    {
        $kpi->delete();

        return response()->noContent();
    }
}
