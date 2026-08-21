import { apiFetch } from "@/lib/api/client";
import type { User } from "@/types/auth";

export type Department = {
  id: number;
  name: string;
};

export type FiscalYear = {
  id: number;
  year: number;
  start_month: number;
  closing_day: number;
  status: string;
};

export function fetchDepartments(): Promise<{ data: Department[] }> {
  return apiFetch("/api/departments");
}

export function fetchFiscalYears(): Promise<{ data: FiscalYear[] }> {
  return apiFetch("/api/fiscal-years");
}

export function fetchUsers(): Promise<{ data: User[] }> {
  return apiFetch("/api/users");
}
