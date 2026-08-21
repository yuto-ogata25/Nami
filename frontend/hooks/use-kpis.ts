import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";

import { createKpi, deleteKpi, fetchKpis, updateKpi } from "@/lib/api/kpis";
import type { KpiPayload } from "@/types/kpi";

export function useKpis() {
  return useQuery({
    queryKey: ["kpis"],
    queryFn: fetchKpis,
    select: (response) => response.data,
  });
}

export function useCreateKpi() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createKpi,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["kpis"] });
    },
  });
}

export function useUpdateKpi() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, payload }: { id: number; payload: KpiPayload }) => updateKpi(id, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["kpis"] });
    },
  });
}

export function useDeleteKpi() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deleteKpi,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["kpis"] });
    },
  });
}
