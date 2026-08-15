import { apiFetch } from "@/lib/api/client";
import type { LoginPayload, Operator, User } from "@/types/auth";

export function fetchCurrentUser(): Promise<{ data: User }> {
  return apiFetch("/api/user");
}

export function loginCustomer(payload: LoginPayload): Promise<{ message: string }> {
  return apiFetch("/api/login", { method: "POST", body: payload });
}

export function logoutCustomer(): Promise<{ message: string }> {
  return apiFetch("/api/logout", { method: "POST" });
}

export function fetchCurrentOperator(): Promise<{ data: Operator }> {
  return apiFetch("/api/operator/me");
}

export function loginOperator(payload: LoginPayload): Promise<{ message: string }> {
  return apiFetch("/api/operator/login", { method: "POST", body: payload });
}

export function logoutOperator(): Promise<{ message: string }> {
  return apiFetch("/api/operator/logout", { method: "POST" });
}
