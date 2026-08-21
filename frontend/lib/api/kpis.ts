import { apiFetch } from "@/lib/api/client";
import type { Kpi, KpiPayload } from "@/types/kpi";

export function fetchKpis(): Promise<{ data: Kpi[] }> {
  return apiFetch("/api/kpis");
}

export function createKpi(payload: KpiPayload): Promise<{ data: Kpi }> {
  return apiFetch("/api/kpis", { method: "POST", body: payload });
}

export function updateKpi(id: number, payload: KpiPayload): Promise<{ data: Kpi }> {
  return apiFetch(`/api/kpis/${id}`, { method: "PUT", body: payload });
}

export function deleteKpi(id: number): Promise<void> {
  return apiFetch(`/api/kpis/${id}`, { method: "DELETE" });
}
