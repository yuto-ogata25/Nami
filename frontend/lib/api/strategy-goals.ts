import { apiFetch } from "@/lib/api/client";
import type { StrategyGoal, StrategyGoalPayload } from "@/types/strategy-goal";

export function fetchStrategyGoals(perspective?: string): Promise<{ data: StrategyGoal[] }> {
  const query = perspective ? `?perspective=${encodeURIComponent(perspective)}` : "";
  return apiFetch(`/api/strategy-goals${query}`);
}

export function createStrategyGoal(payload: StrategyGoalPayload): Promise<{ data: StrategyGoal }> {
  return apiFetch("/api/strategy-goals", { method: "POST", body: payload });
}

export function updateStrategyGoal(
  id: number,
  payload: StrategyGoalPayload
): Promise<{ data: StrategyGoal }> {
  return apiFetch(`/api/strategy-goals/${id}`, { method: "PUT", body: payload });
}

export function deleteStrategyGoal(id: number): Promise<void> {
  return apiFetch(`/api/strategy-goals/${id}`, { method: "DELETE" });
}
