import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";

import {
  createStrategyGoal,
  deleteStrategyGoal,
  fetchStrategyGoals,
  updateStrategyGoal,
} from "@/lib/api/strategy-goals";
import type { StrategyGoalPayload } from "@/types/strategy-goal";

export function useStrategyGoals(perspective?: string) {
  return useQuery({
    queryKey: ["strategy-goals", perspective ?? "all"],
    queryFn: () => fetchStrategyGoals(perspective),
    select: (response) => response.data,
  });
}

export function useCreateStrategyGoal() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createStrategyGoal,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["strategy-goals"] });
    },
  });
}

export function useUpdateStrategyGoal() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, payload }: { id: number; payload: StrategyGoalPayload }) =>
      updateStrategyGoal(id, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["strategy-goals"] });
    },
  });
}

export function useDeleteStrategyGoal() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deleteStrategyGoal,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["strategy-goals"] });
    },
  });
}
